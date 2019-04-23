<?php
//   Copyright 2019 NEC Corporation
//
//   Licensed under the Apache License, Version 2.0 (the "License");
//   you may not use this file except in compliance with the License.
//   You may obtain a copy of the License at
//
//       http://www.apache.org/licenses/LICENSE-2.0
//
//   Unless required by applicable law or agreed to in writing, software
//   distributed under the License is distributed on an "AS IS" BASIS,
//   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
//   See the License for the specific language governing permissions and
//   limitations under the License.
//
//////////////////////////////////////////////////////////////////////
//
//  【処理概要】
//   AnsinbleTowerの代入値自動登録処理
//
//////////////////////////////////////////////////////////////////////

/* 起動しているshellの起動判定を正常にするための待ち時間 */
sleep(1);

/* ルートディレクトリを取得 */
if ( empty($root_dir_path) ){
	$root_dir_temp = array();
	$root_dir_temp = explode( "ita-root", dirname(__FILE__) );
	$root_dir_path = $root_dir_temp[0] . "ita-root";
}

/* $log_output_dirを取得 */
$log_output_dir = getenv('LOG_DIR');

/* 何らかの理由で[getenv('LOG_DIR')]に値がない為、直接『$log_output_dir』の値を指定する */
if($log_output_dir == ""){
	$log_output_dir = '/nec/ita-root/logs/backyardlogs' ;
}


/* $log_file_prefixを作成 */
$log_file_prefix = basename( __FILE__, '.php' ) . "_";

/* $log_levelを取得 */
$log_level = getenv('LOG_LEVEL'); // 'DEBUG';

/* PHP エラー時のログ出力先を設定 */
$tmpVarTimeStamp = time();
$logfile = $log_output_dir . "/" . $log_file_prefix . date("Ymd",$tmpVarTimeStamp) . ".log";

ini_set('display_errors',0);
ini_set('log_errors',1);
ini_set('error_log',$logfile);


/* AnsibleTowerの代入値自動登録に必要な変数の初期値設定ファイルを読み込む */
require($root_dir_path . "/libs/backyardlibs/ansibletower_driver/ky_ansibletower_valautostup_setenv.php");

/* 定数定義 */
$log_output_php   = "/libs/backyardlibs/backyard_log_output.php";
$php_req_gate_php = "/libs/commonlibs/common_php_req_gate.php";
$db_connect_php   = "/libs/commonlibs/common_db_connect.php";

/* ローカル変数(全体)宣言 */
$warning_flag = 0; // 警告フラグ(1：警告発生)
$error_flag = 0; // 異常フラグ(1：異常発生)

$g_null_data_handling_def   = "";

try {
	/* 共通モジュールの呼び出し */
	$aryOrderToReqGate = array('DBConnect'=>'LATE');
	require_once($root_dir_path . $php_req_gate_php);

	/* 開始メッセージ */
	if($log_level === "DEBUG"){
		$traceMsg = 'Start procedure' ;
		LocalLogPrint(basename(__FILE__),__LINE__,$traceMsg);
	}

	/* DBコネクト */
	require_once($root_dir_path . $db_connect_php);

	/* トレースメッセージ */
	if($log_level === "DEBUG"){
		$traceMsg = 'DB connect complete' ;
		LocalLogPrint(basename(__FILE__),__LINE__,$traceMsg);
	}

	/* <START> トランザクション============================================================================================================= */
	if($objDBCA->transactionStart()===false) {
		/* 異常フラグON  例外処理へ */
		$error_flag = 1;
		throw new Exception('Start transaction has failed.'); // ITAANSIBLEH-ERR-80001
	}

	/* トレースメッセージ */
	if($log_level === "DEBUG") {
		$traceMsg = '[Process] Start transaction' ; //ITAANSIBLEH-STD-60001
		LocalLogPrint(basename(__FILE__),__LINE__,$traceMsg);
	}

	/* <START> [0001] 関連シーケンスをロックする（デッドロック防止の為に、昇順でロック------------------------------------------------------ */
	$aryTgtOfSequenceLock = array
	(
		/* 代入値自動登録設定 */
		$strSeqOfCurTableValAss,
		$strSeqOfJnlTableValAss,
		/* 代入値管理 */
		$strSeqOfCurTableVarsAss,
		$strSeqOfCurTableVarsAss,
		/* 作業対象ホスト管理 */
		$strSeqOfCurTablePhoLnk,
		$strSeqOfJnlTablePhoLnk,
	);

	/* 値の昇順でソート */
	asort($aryTgtOfSequenceLock);

	foreach($aryTgtOfSequenceLock as $strSeqName){
		/* JOURNALのシーケンス */
		$retArray = getSequenceLockInTrz($strSeqName, "A_SEQUENCE");

		/* 異常フラグがONならば、例外処理へ */
		if($retArray[1] != 0){
			$error_flag = 1;
			$errorMsg = 'Lock sequence has failed.' ; // ITAANSIBLEH-ERR-80002
			throw new Exception($errorMsg);
		}
	}
	/* < END > [0001] 関連シーケンスをロックする（デッドロック防止の為に、昇順でロック------------------------------------------------------ */

    //////////////////////////////////////////////////////////////////////////////////////
    // インターフェース情報からNULLデータを代入値管理に登録するかのデフォルト値を取得する。
    //////////////////////////////////////////////////////////////////////////////////////
    $lv_if_info = array();
    $error_msg   = "";
    $ret = getIFInfoDB($lv_if_info,$error_msg);
    if($ret === false) {
        $error_flag = 1;
        throw new Exception( $error_msg );
    }
    $g_null_data_handling_def = $lv_if_info["NULL_DATA_HANDLING_FLG"];

	/* P0002：代入値自動登録設定からカラム毎の変数の情報を取得 */
	if($log_level === "DEBUG"){
		$traceMsg = '[Process] Get variable information of respective column, from the Substitution value auto-registration setting' ; // ITAANSIBLEH-STD-70015
		LocalLogPrint(basename(__FILE__),__LINE__,$traceMsg);
	}

	/* テーブル名配列 */
	$lv_tableNameToMenuIdList = array();
	/* カラム情報配列 */
	$lv_tabColNameToValAssRowList = array();
	/* 代入値紐付の登録に不備がある主キーの配列 */
	$lv_errorColumnIdList = array();
	/* テーブル名と主キーの配列 */
	$lv_tableNameToPKeyNameList = array();

	$ret = readValAssign
	(
		 $lv_tableNameToMenuIdList
		,$lv_tabColNameToValAssRowList
		,$lv_errorColumnIdList
		,$lv_tableNameToPKeyNameList
	);

	if($ret === false){
		$error_flag = 1;
		$errorMsg = 'Get the variable information for each column from the Substitution value auto-registration setting has failed.' ; // ITAANSIBLEH-ERR-90032
		throw new Exception($errorMsg);
	}

	/* P0003：紐付メニューへのSELECT文を生成する */
	$lv_tableNameToSqlList = array(); // 代入値紐付メニュー毎のSELECT文配列

	createQuerySelectCMDB
	(
		 $lv_tableNameToMenuIdList
		,$lv_tabColNameToValAssRowList
		,$lv_tableNameToPKeyNameList
		,$lv_tableNameToSqlList
	);

	/* P0004：紐付メニューから具体値を取得する */
	if($log_level === "DEBUG"){
		$traceMsg = '[Process] Get specific value from associated menu' ; // ITAANSIBLEH-STD-70016
		LocalLogPrint(basename(__FILE__),__LINE__,$traceMsg);
	}

	$lv_varsAssList      = array();
	$lv_arrayVarsAssList = array();

	$ret = getCMDBdata
	(
		 $lv_tableNameToSqlList
		,$lv_tableNameToMenuIdList
		,$lv_tabColNameToValAssRowList
		,$lv_errorColumnIdList
		,$lv_varsAssList
		,$lv_arrayVarsAssList
		,$warning_flag
	);

	$lv_phoLinkList     = array();
	$lv_useAssignIdList = array();

	/* P0005：一般変数・複数具体値変数を紐付けている紐付メニューの具体値を代入値管理に登録 */
	if($log_level === "DEBUG") {
		$traceMsg = '[Process] Register the specific value of associated menu which is associated with generic variable / array-type variable, in the substitution value list' ; // ITAANSIBLEH-STD-70044
		LocalLogPrint(basename(__FILE__),__LINE__,$traceMsg);
	}

	foreach($lv_varsAssList as $varsAssRecord){
		/* 処理対象外のデータかを判定 */
		if($varsAssRecord['STATUS'] === false){
			continue;
		}

		$lv_assingId = '';
		/* 代入値管理に具体値を登録 */
		$ret = addStg1StdListVarsAssign($varsAssRecord, $lv_assingId);

		if($ret === false) {
			$error_flag = 1;
			$errorMsg = 'Register the specific value of generic variable or array-type variable for substitution value list has failed.' ; // ITAANSIBLEH-ERR-90300
			throw new Exception($errorMsg);
		}

		/* 代入値管理に登録が必要な主キーを退避 */
		$lv_useAssignIdList[$lv_assingId] = 1;

		/* 作業対象ホストに登録が必要な情報を退避 */
		$lv_phoLinkList
		   [$varsAssRecord['OPERATION_NO_UAPK']]
		   [$varsAssRecord['PATTERN_ID']]
		   [$varsAssRecord['SYSTEM_ID']]
		 = 1;
	}

	/* P0006：多段変数を紐付けている紐付メニューの具体値を代入値管理に登録 */
	if($log_level === "DEBUG") {
		$traceMsg = '[Process] Register the specific value of associated menu which is associated with nested variable, in the substitution value list' ; //ITAANSIBLEH-STD-70045
		LocalLogPrint(basename(__FILE__),__LINE__,$traceMsg);
	}

	foreach($lv_arrayVarsAssList as $varsAssRecord){
		/* 処理対象外のデータかを判定 */
		if($varsAssRecord['STATUS'] === false){
			continue;
		}

		$lv_assingId = '';
		$ret = addStg1ArrayVarsAssign($varsAssRecord, $lv_assingId);
		if($ret === false){
			$error_flag = 1;
			$errorMsg = 'Register the specific value of nested variable for substitution value list has failed.' ; // ITAANSIBLEH-ERR-90210
			throw new Exception($errorMsg);
		}

		/* 代入値管理に登録が必要な主キーを退避 */
		$lv_useAssignIdList[$lv_assingId] = 1;

		/* 作業対象ホストに登録が必要な情報を退避 */
		$lv_phoLinkList
		   [$varsAssRecord['OPERATION_NO_UAPK']]
		   [$varsAssRecord['PATTERN_ID']]
		   [$varsAssRecord['SYSTEM_ID']]
		 = 1;
	}

	/* P0007：代入値管理から不要なデータを削除する */
	if($log_level === "DEBUG") {
		$traceMsg = '[Process] Delete the unnecessary data from the substitution value list' ; // ITAANSIBLEH-STD-70020
		LocalLogPrint(basename(__FILE__),__LINE__,$traceMsg);
	}

	$ret = deleteVarsAssign($lv_useAssignIdList);
	if($ret === false) {
		$error_flag = 1;
		$errorMsg = 'Delete the unnecessary data from substitution value list has failed.' ; // ITAANSIBLEH-ERR-90053
		throw new Exception($errorMsg);
	}

	$lv_usePhoLinkIdList = array();

	/* P0008：代入値管理で登録したオペ+作業パターン+ホストが作業対象ホストに登録されているか判定し、未登録の場合は登録する。 */
	if($log_level === "DEBUG") {
		$traceMsg = '[Process] Operation ID + Movement ID + host ID that is not registered in the target host registers the target host' ; // ITAANSIBLEH-STD-70021
		LocalLogPrint(basename(__FILE__),__LINE__,$traceMsg);
	}

	foreach($lv_phoLinkList as $ope_id=>$ptn_list){
		foreach($ptn_list as $ptn_id=>$host_list){
			foreach($host_list as $host_id=>$dummy){
				$lv_phoLinkData = array
				(
					 'OPERATION_NO_UAPK' => $ope_id
					,'PATTERN_ID'        => $ptn_id
					,'SYSTEM_ID'         => $host_id
				);
				$lv_phoLinkId = '';
				$ret = addStg1PhoLink($lv_phoLinkData, $lv_phoLinkId);
				if($ret === false) {
					$error_flag = 1;
					$errorMsg = 'Register operation ID, MovementID and Host ID which are not registered in the target host has failed.' ; // ITAANSIBLEH-ERR-90054
					throw new Exception( $errorMsg );
				}
				$lv_usePhoLinkIdList[$lv_phoLinkId] = 1;
			}
		}
	}

	/* P0009：作業対象ホストから不要なデータを削除する */
	if($log_level === "DEBUG") {
		$traceMsg = '[Process] Delete the unnecessary data from target host' ; // ITAANSIBLEH-STD-70022
		LocalLogPrint(basename(__FILE__),__LINE__,$traceMsg);
	}

	$ret = deletePhoLink($lv_usePhoLinkIdList);
	if($ret === false){
		$error_flag = 1;
		$errorMsg = 'Delete the unnecessary data from target host has failed.' ; // ITAANSIBLEH-ERR-90055
		throw new Exception($errorMsg);
	}

	/* コミット(レコードロックを解除) */
	$r = $objDBCA->transactionCommit();
	if(!$r) {
		$error_flag = 1 ; // 異常フラグON
		$errorMsg = 'Commit transaction has failed' ; // ITAANSIBLEH-ERR-80003
		throw new Exception($errorMsg);
	}

	/* トランザクションの終了処理 */
	$objDBCA->transactionExit();

	/* トレースメッセージ */
	if($log_level === "DEBUG") {
		//$ary[60002] = "[処理]トランザクション終了";
		$traceMsg = '[Process] End transaction' ;
		LocalLogPrint(basename(__FILE__),__LINE__,$traceMsg);
	}

} catch(Exception $e) {

	$errorMsg = 'An exception occurred.' ; // ITAANSIBLEH-ERR-80004
	LocalLogPrint(basename(__FILE__),__LINE__,$errorMsg);
	/*例外メッセージ出力 */
	$errorMsg = $e->getMessage();
	LocalLogPrint(basename(__FILE__),__LINE__,$errorMsg);
	
	/* DBアクセス事後処理 */
	if ( isset($objQuery)	) unset($objQuery);
	if ( isset($objQueryUtn) ) unset($objQueryUtn);
	if ( isset($objQueryJnl) ) unset($objQueryJnl);
	
	/* トランザクションが発生しそうなロジックに入ってからのexceptionの場合は、念の為にロールバック */
	if($objDBCA->getTransactionMode()) {
		/* ロールバック処理 */
		if($objDBCA->transactionRollBack()=== true) {
			$errorMsg = '[Process] Rollback' ; // ITAANSIBLEH-STD-60004
		} else {
			$error_flag = 1;
			$errorMsg = 'Rollback has failed.' ; // ITAANSIBLEH-ERR-80005
		}
		LocalLogPrint(basename(__FILE__),__LINE__,$errorMsg);
		
		/* トランザクション終了 */
		if($objDBCA->transactionExit()=== true) {
			$errorMsg = '[Process] End transaction' ; // ITAANSIBLEH-STD-60002
		}
		else{
			$error_flag = 1;
			$errorMsg = 'An error occurred at the time of ending the transaction.' ; // ITAANSIBLEH-ERR-80006
		}
		LocalLogPrint(basename(__FILE__),__LINE__,$errorMsg);
	}
	/* < END > トランザクション============================================================================================================= */
}

/* 結果出力：処理結果コードを判定してアクセスログを出し分ける */
if($error_flag != 0) {
	if($log_level === "DEBUG") {
		$traceMsg = 'End procedure (error)' . "\n" ; // $ary[50001] = 'プロシージャ終了(異常)';
		LocalLogPrint(basename(__FILE__),__LINE__,$traceMsg);
	}
	exit(2); // backgroundserviceを止めるかどうかのハンドリングは別途検討
} elseif($warning_flag != 0) {
	if($log_level === "DEBUG") {
		$traceMsg = 'End procedure (warning)' . "\n" ; // ITAWDCH-ERR-50002
		LocalLogPrint(basename(__FILE__),__LINE__,$traceMsg);
	}
	exit(2); // backgroundserviceを止めるかどうかのハンドリングは別途検討
} else {
	if($log_level === "DEBUG") {
		$traceMsg = 'End procedure (normal)' . "\n" ; // ITAWDCH-STD-50002
		LocalLogPrint(basename(__FILE__),__LINE__,$traceMsg);
	}
	exit(0);
}

/* <START> F0002：代入値自動登録設定からカラム情報を取得する-------------------------------------------------------------------------------- */
function readValAssign
(
	 &$inout_tableNameToMenuIdList // テーブル名配列（[テーブル名] = MENU_ID）
	,&$inout_tabColNameToValAssRowList // カラム情報配列（[テーブル名][カラム名][]=>array("代入値自動登録設定のカラム名"=>値)）
	,&$inout_errorColumnIdList // 代入値紐付の登録に不備がある主キーの配列（[代入値紐付主キー] = 1）
	,&$inout_tableNameToPKeyNameList // テーブル主キー名配列
) {
	global	$db_model_ch;
	global	$objMTS;
	global	$objDBCA;
	global	$log_level;

	global $param_col_vars_link;
	global $pattern_role_link;
	global $vars_list;
	global $nested_member_vars_list;
	global $nested_member_col_cmb;
	global $pattern_vars_link;
	global $param_menu_column_list;
	global $param_menu_table_list;

	$sql
	 = " 			SELECT"
	  ." 			   TBL_A.PRMCOL_VARS_LINK_ID"
	  ." 			  ,TBL_A.MENU_ID"
	  ." 			  ,TBL_C.TABLE_NAME"
	  ." 			  ,TBL_C.PKEY_NAME"
	  ." 			  ,TBL_C.DISUSE_FLAG AS TBL_DISUSE_FLAG"
	  ." 			  ,TBL_A.MENU_COLUMN_ID"
	  ." 			  ,TBL_B.COL_NAME"
	  ." 			  ,TBL_B.COL_TITLE"
     ."               ,TBL_B.REF_TABLE_NAME"
     ."               ,TBL_B.REF_PKEY_NAME"
     ."               ,TBL_B.REF_COL_NAME"
	  ." 			  ,TBL_B.DISUSE_FLAG  AS COL_DISUSE_FLAG"
	  ." 			  ,TBL_A.PRMCOL_LINK_TYPE_ID"
      ." 			  ,TBL_A.NULL_DATA_HANDLING_FLG"
	  ." 			  ,TBL_A.PATTERN_ID"
	  ." 			  ,("
	  ." 			      SELECT"
	  ." 			         COUNT(*)"
	  ." 			      FROM"
	  ." 			         $pattern_role_link"  /* Movement詳細 */
	  ." 			      WHERE"
	  ." 			         PATTERN_ID = TBL_A.PATTERN_ID"
	  ." 			      AND"
	  ." 			         DISUSE_FLAG = '0'"
	  ." 			   ) AS PATTERN_CNT"
	/* (VALUE)Movement変数紐付の登録確認 */
	  ." 			  ,TBL_A.VALUE_VARS_LINK_ID"
	  ." 			  ,("
	  ." 			      SELECT"
	  ." 			         COUNT(*)"
	  ." 			      FROM"
	  ." 			          $pattern_vars_link" /* Movement変数紐付 */
	  ." 			      WHERE"
	  ." 			         PATTERN_ID = TBL_A.PATTERN_ID"
	  ." 			      AND"
	  ." 			         VARS_LINK_ID = TBL_A.VALUE_VARS_LINK_ID"
	  ." 			      AND"
	  ." 			         DISUSE_FLAG = '0'"
	  ." 			   ) AS VALUE_PTN_VARS_LINK_CNT"
	/* (VALUE)変数名一覧 */
	  ." 			  ,("
	  ." 			      SELECT"
	  ." 			         VARS_NAME"
	  ." 			      FROM"
	  ." 			         $vars_list" /* 変数一覧 */
	  ." 			      WHERE"
	  ." 			         VARS_ID IN"
	  ." 			         ("
	  ." 			            SELECT"
	  ." 			               VARS_ID"
	  ." 			            FROM"
	  ." 			               $pattern_vars_link" /* Movement変数紐付 */
	  ." 			            WHERE"
	  ." 			               PATTERN_ID = TBL_A.PATTERN_ID"
	  ." 			            AND"
	  ." 			               VARS_LINK_ID = TBL_A.VALUE_VARS_LINK_ID"
	  ." 			            AND"
	  ." 			               DISUSE_FLAG = '0'"
	  ." 			         )"
	  ." 			      AND"
	  ." 			         DISUSE_FLAG = '0'"
	  ." 			   ) AS VALUE_VARS_NAME"
	  ." 			  ,("
	  ." 			      SELECT"
	  ." 			         VARS_ATTR_ID"
	  ." 			      FROM"
	  ." 			         $vars_list" /* 変数一覧 */
	  ." 			      WHERE"
	  ." 			         VARS_ID IN"
	  ." 			         ("
	  ." 			            SELECT"
	  ." 			               VARS_ID"
	  ." 			            FROM"
	  ." 			               $pattern_vars_link" /* Movement変数紐付 */
	  ." 			            WHERE"
	  ." 			               PATTERN_ID = TBL_A.PATTERN_ID"
	  ." 			            AND"
	  ." 			               VARS_LINK_ID = TBL_A.VALUE_VARS_LINK_ID"
	  ." 			            AND"
	  ." 			               DISUSE_FLAG = '0'"
	  ." 			         )"
	  ." 			      AND"
	  ." 			         DISUSE_FLAG = '0'"
	  ." 			   ) AS VALUE_VARS_ATTR_ID"
	/* (VALUE)多段変数配列組合せ管理 */
	  ." 			  ,TBL_A.VALUE_NESTEDMEM_COL_CMB_ID"
	  ." 			  ,("
	  ." 			      SELECT"
	  ." 			         COL_COMBINATION_MEMBER_ALIAS"
	  ." 			      FROM"
	  ." 			         $nested_member_col_cmb" /* 多段変数配列組合せ管理 */
	  ." 			      WHERE"
	  ." 			         VARS_ID IN"
	  ." 			         ("
	  ." 			            SELECT"
	  ." 			               VARS_ID"
	  ." 			            FROM"
	  ." 			               $pattern_vars_link" /* Movement変数紐付 */
	  ." 			            WHERE"
	  ." 			               PATTERN_ID = TBL_A.PATTERN_ID"
	  ." 			            AND"
	  ." 			               VARS_LINK_ID = TBL_A.VALUE_VARS_LINK_ID"
	  ." 			            AND"
	  ." 			               DISUSE_FLAG = '0'"
	  ." 			         )"
	  ." 			      AND"
	  ." 			         NESTEDMEM_COL_CMB_ID = TBL_A.VALUE_NESTEDMEM_COL_CMB_ID"
	  ." 			      AND"
	  ." 			         DISUSE_FLAG = '0'"
	  ." 			   ) AS VALUE_COL_COMBINATION_MEMBER_ALIAS"
	/* (VALUE)多段変数メンバー管理 */
	  ." 			  ,TBL_A.VALUE_ASSIGN_SEQ"
	  ." 			  ,("
	  ." 			      SELECT"
	  ." 			         ASSIGN_SEQ_NEED"
	  ." 			      FROM"
	  ." 			         $nested_member_vars_list" /* 多段変数メンバー管理 */
	  ." 			      WHERE"
	  ." 			         VARS_ID IN"
	  ." 			         ("
	  ." 			            SELECT"
	  ." 			               VARS_ID"
	  ." 			            FROM"
	  ." 			               $pattern_vars_link" /* Movement変数紐付 */
	  ." 			            WHERE"
	  ." 			               PATTERN_ID = TBL_A.PATTERN_ID"
	  ." 			            AND"
	  ." 			               VARS_LINK_ID = TBL_A.VALUE_VARS_LINK_ID"
	  ." 			            AND"
	  ." 			               DISUSE_FLAG = '0'"
	  ." 			         )"
	  ." 			      AND"
	  ." 			         NESTED_MEM_VARS_ID IN"
	  ." 			         ("
	  ." 			            SELECT"
	  ." 			               NESTED_MEM_VARS_ID"
	  ." 			            FROM"
	  ." 			               $nested_member_col_cmb" /* 多段変数配列組合せ管理 */
	  ." 			            WHERE"
	  ." 			               NESTEDMEM_COL_CMB_ID = TBL_A.VALUE_NESTEDMEM_COL_CMB_ID"
	  ." 			            AND"
	  ." 			               DISUSE_FLAG = '0'"
	  ." 			         )"
	  ." 			      AND"
	  ." 			         DISUSE_FLAG = '0'"
	  ." 			   ) AS VALUE_ASSIGN_SEQ_NEED"
	/* (Key)Movement変数紐付の登録確認 */
	  ." 			  ,TBL_A.KEY_VARS_LINK_ID"
	  ." 			  ,("
	  ." 			      SELECT"
	  ." 			         COUNT(*)"
	  ." 			      FROM"
	  ." 			         $pattern_vars_link" /* Movement変数紐付 */
	  ." 			      WHERE"
	  ." 			         PATTERN_ID = TBL_A.PATTERN_ID"
	  ." 			      AND"
	  ." 			         VARS_LINK_ID = TBL_A.KEY_VARS_LINK_ID"
	  ." 			      AND"
	  ." 			         DISUSE_FLAG = '0'"
	  ." 			   ) AS KEY_PTN_VARS_LINK_CNT"
	/* (Key)変数名一覧 */
	  ." 			  ,("
	  ." 			      SELECT"
	  ." 			         VARS_NAME"
	  ." 			      FROM"
	  ." 			         $vars_list" /* 変数一覧 */
	  ." 			      WHERE"
	  ." 			         VARS_ID IN"
	  ." 			         ("
	  ." 			            SELECT"
	  ." 			               VARS_ID"
	  ." 			            FROM"
	  ." 			               $pattern_vars_link"  /* Movement変数紐付 */
	  ." 			            WHERE"
	  ." 			               PATTERN_ID = TBL_A.PATTERN_ID"
	  ." 			            AND"
	  ." 			               VARS_LINK_ID = TBL_A.KEY_VARS_LINK_ID"
	  ." 			            AND"
	  ." 			               DISUSE_FLAG = '0'"
	  ." 			         )"
	  ." 			      AND"
	  ." 			         DISUSE_FLAG = '0'"
	  ." 			   ) AS KEY_VARS_NAME"
	  ." 			  ,("
	  ." 			      SELECT"
	  ." 			         VARS_ATTR_ID"
	  ." 			      FROM"
	  ." 			         $vars_list" /* 変数一覧 */
	  ." 			      WHERE"
	  ." 			         VARS_ID IN"
	  ." 			         ("
	  ." 			            SELECT"
	  ." 			               VARS_ID"
	  ." 			            FROM"
	  ." 			               $pattern_vars_link" /* Movement変数紐付 */
	  ." 			            WHERE"
	  ." 			               PATTERN_ID = TBL_A.PATTERN_ID"
	  ." 			            AND"
	  ." 			               VARS_LINK_ID = TBL_A.KEY_VARS_LINK_ID"
	  ." 			            AND"
	  ." 			               DISUSE_FLAG = '0'"
	  ." 			         )"
	  ." 			      AND"
	  ." 			         DISUSE_FLAG = '0'"
	  ." 			   ) AS KEY_VARS_ATTR_ID"
	/* (Key)多次元変数配列組合せ管理 */
	  ." 			  ,TBL_A.KEY_NESTEDMEM_COL_CMB_ID"
	  ." 			  ,("
	  ." 			      SELECT"
	  ." 			         COL_COMBINATION_MEMBER_ALIAS"
	  ." 			      FROM"
	  ." 			         $nested_member_col_cmb" /* 多次元変数配列組合せ管理 */
	  ." 			      WHERE"
	  ." 			         VARS_ID IN"
	  ." 			         ("
	  ." 			            SELECT"
	  ." 			               VARS_ID"
	  ." 			            FROM"
	  ." 			               $pattern_vars_link" /* Movement変数紐付 */
	  ." 			            WHERE"
	  ." 			               PATTERN_ID = TBL_A.PATTERN_ID"
	  ." 			            AND"
	  ." 			               VARS_LINK_ID = TBL_A.KEY_VARS_LINK_ID"
	  ." 			            AND"
	  ." 			               DISUSE_FLAG = '0'"
	  ." 			         )"
	  ." 			      AND"
	  ." 			         NESTEDMEM_COL_CMB_ID = TBL_A.KEY_NESTEDMEM_COL_CMB_ID"
	  ." 			      AND"
	  ." 			         DISUSE_FLAG = '0'"
	  ." 			   ) AS KEY_COL_COMBINATION_MEMBER_ALIAS"
	/* (Key)多次元変数メンバー管理 */
	  ." 			  ,TBL_A.KEY_ASSIGN_SEQ"
	  ." 			  ,("
	  ." 			      SELECT"
	  ." 			         ASSIGN_SEQ_NEED"
	  ." 			      FROM"
	  ." 			         $nested_member_vars_list" /* 多次元変数メンバー管理 */
	  ." 			      WHERE"
	  ." 			         VARS_ID IN"
	  ." 			         ("
	  ." 			            SELECT"
	  ." 			               VARS_ID"
	  ." 			            FROM"
	  ." 			               $pattern_vars_link" /* Movement変数紐付 */
	  ." 			            WHERE"
	  ." 			               PATTERN_ID = TBL_A.PATTERN_ID"
	  ." 			            AND"
	  ." 			               VARS_LINK_ID = TBL_A.KEY_VARS_LINK_ID"
	  ." 			            AND"
	  ." 			               DISUSE_FLAG = '0'"
	  ." 			         )"
	  ." 			      AND"
	  ." 			         NESTED_MEM_VARS_ID IN"
	  ." 			         ("
	  ." 			            SELECT"
	  ." 			               NESTED_MEM_VARS_ID"
	  ." 			            FROM"
	  ." 			               $nested_member_col_cmb" /* 多次元変数配列組合せ管理 */
	  ." 			            WHERE"
	  ." 			               NESTEDMEM_COL_CMB_ID = TBL_A.KEY_NESTEDMEM_COL_CMB_ID"
	  ." 			            AND"
	  ." 			               DISUSE_FLAG = '0'"
	  ." 			         )"
	  ." 			      AND"
	  ." 			         DISUSE_FLAG = '0'"
	  ." 			   ) AS KEY_ASSIGN_SEQ_NEED"
	/* Top SELECT文に対するFROM句以降のロジック */
	  ." 			FROM"
	  ." 			   $param_col_vars_link AS TBL_A" /* 代入値自動登録設定 */
	  ." 			LEFT JOIN"
	  ." 			   $param_menu_column_list AS TBL_B" /* 紐付対象メニューカラム管理 */
	  ." 			 ON"
	  ." 			   TBL_A.MENU_COLUMN_ID = TBL_B.COLUMN_LIST_ID"
	  ." 			LEFT JOIN"
	  ." 			   $param_menu_table_list AS TBL_C" /* 紐付対象メニュー管理 */
	  ." 			 ON"
	  ." 			   TBL_A.MENU_ID = TBL_C.MENU_ID"
	  ." 			WHERE"
	  ." 			   TBL_A.DISUSE_FLAG = '0'"
	  ." 			ORDER BY TBL_A.MENU_COLUMN_ID"
	;

	$sqlUtnBody = $sql;
	$arrayUtnBind = array();
	$objQuery = recordSelect($sqlUtnBody, $arrayUtnBind);
	if($objQuery == null) {

		return false;
	}

	/* $lva_var_assign_seq_list{Movement][変数][代入順序]=COLUMN_LIST_ID */
	$lva_var_assign_seq_list = array();

	/* $lva_array_assign_seq_list{Movement][変数][メンバー変数][代入順序]=COLUMN_LIST_ID */
	$lva_array_assign_seq_list = array();

	/* <START> 取得データの反復確認処理----------------------------------------------------------------------------------------------------- */
	while($row = $objQuery->resultFetch()){
		/* 紐付対象メニューが廃止されているか判定 */
		if($row['TBL_DISUSE_FLAG'] != '0'){
			if($log_level === "DEBUG"){
				$msgstr ='Associated menu registered in the Substitution value auto-registration setting is discarded. This record will be out of scope of processing. (Substitution value auto-registration setting Item No.:' . implode(array($row['PRMCOL_VARS_LINK_ID'])) . ')' ; // ITAANSIBLEH-ERR-90014
				LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
			}
			continue; // 次のカラムへ
		}

		/* 紐付対象メニューカラム管理のカラムが廃止されているか判定 */
		if($row['COL_DISUSE_FLAG'] != '0'){
			if($log_level === "DEBUG"){
				$msgstr = 'Item information of associated menu registered in the Substitution value auto-registration setting is discarded. This record will be out of scope of processing. (Substitution value auto-registration setting: Item No.:' . implode(array($row['PRMCOL_VARS_LINK_ID'])) . ')' ; // ITAANSIBLEH-ERR-90016
				LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
			}
			continue; // 次のカラムへ
		}

		/* Movement詳細に作業パターンが未登録 */
		if($row['PATTERN_CNT'] == 0){
			if($log_level === "DEBUG"){
				$msgstr = 'Movement registered in the substitution value auto-registration setting is not registered in the Movement details. This record will be out of scope of processing. (Substitution value auto-registration setting Item No:' . implode(array($row['PRMCOL_VARS_LINK_ID'])) . ')' ; // ITAANSIBLEH-ERR-90013
				LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
			}
			continue ; // 次のカラムへ
		}

		/* 紐付対象メニューが登録されているか判定 */
		if(@strlen($row['TABLE_NAME']) == 0){
			if($log_level === "DEBUG"){
				$msgstr = 'Could not get the associated menu registered in the Substitution value auto-registration setting. This record will be out of scope of processing. (Substitution value auto-registration setting Item No.:' . implode(array($row['PRMCOL_VARS_LINK_ID'])) . ')' ; // ITAANSIBLEH-ERR-90015
				LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
			}
			continue; // 次のカラムへ
		}

		/* 紐付対象メニューの主キーが登録されているか判定 */
		if(@strlen($row['PKEY_NAME']) == 0){
			if($log_level === "DEBUG"){
				$msgstr = 'Could not get the primary key name of the associated menu with the Substitution value auto-registration setting. This record will be out of scope of processing. (Substitution value auto-registration setting: Item No.:' . implode(array($row['PRMCOL_VARS_LINK_ID'])) . ')'; // ITAANSIBLEH-ERR-90086
				LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
			}
			continue; // 次のカラムへ
		}

		/* 紐付対象メニューのカラムが未登録か判定 */
		if(@strlen($row['COL_NAME']) == 0){
			if($log_level === "DEBUG"){
				$msgstr = 'Could not get the item information of associated menu registered in the Substitution value auto-registration setting. This record will be out of scope of processing. (Substitution value auto-registration setting:' . implode(array($row['PRMCOL_VARS_LINK_ID'])) . ')' ; // ITAANSIBLEH-ERR-90017
				LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
			}
			continue ; // 次のカラムへ
		}

		/* 紐付対象メニューのカラムタイトルが未登録か判定 */
		if(@strlen($row['COL_TITLE']) == 0){
			if($log_level === "DEBUG"){
				$msgstr = 'Could not get the item name of associated menu registered in the Substitution value auto-registration setting. This record will be out of scope of processing. (Substitution value auto-registration setting Item No.:' . implode(array($row['PRMCOL_VARS_LINK_ID'])) . ')' ; // ITAANSIBLEH-ERR-90018
				LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
			}
			continue; // 次のカラムへ
		}

		/* カラムタイプ判定 */
		$type_chk = array
		(
			 DEFINE_COL_TYPE_VAL
			,DEFINE_COL_TYPE_KEY
			,DEFINE_COL_TYPE_KVL
		);
		$col_type = $row['PRMCOL_LINK_TYPE_ID'];
		if(!in_array($col_type, $type_chk)){
			if($log_level === "DEBUG"){
				$msgstr = 'Registration method, which is registered in the Substitution value auto-registration setting, is set with an out of range value. This record will be out of scope of processing. (Substitution value auto-registration setting Item No:' . implode(array($row['PRMCOL_VARS_LINK_ID'])) . ' Registration method :' . implode(array($row['PRMCOL_LINK_TYPE_ID'])) . ')' ; // ITAANSIBLEH-ERR-90019
				LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
			}
			continue; // 次のカラムへ
		}

		$val_vars_attr = ""; // Value型変数の変数タイプ
		$key_vars_attr = ""; // Key型変数の変数タイプ

		/* <START> Key項目・Value項目の検査（当該レコード） ※カラムタイプにより処理分岐（Key-Valueタイプは両方の検査を実行）--------------- */
		switch($col_type){
			case DEFINE_COL_TYPE_KVL:
			case DEFINE_COL_TYPE_VAL:
				/* Value型に設定されている変数設定確認 */
				$ret = valAssColumnValidate
				(
					 "Value"
					,$val_vars_attr
					,$row
					,"VALUE_VARS_LINK_ID"
					,"VALUE_VARS_NAME"
					,"VALUE_PTN_VARS_LINK_CNT"
					,"VALUE_VARS_ATTR_ID"
					,"VALUE_NESTEDMEM_COL_CMB_ID"
					,"VALUE_COL_COMBINATION_MEMBER_ALIAS"
					,"VALUE_ASSIGN_SEQ"
					,"VALUE_ASSIGN_SEQ_NEED"
				);
				/* もし、何らかのエラーならばwhile文から抜ける */
				if($ret === false) {
					continue 2 ;
				}

				/* Value型変数の代入順序の重複（他レコードとの関連）をチェック */
				$ret = valAssSeqDuplicatePickUp
				(
					 "Value"
					,$val_vars_attr
					,$row
					,$lva_var_assign_seq_list
					,$lva_array_assign_seq_list
					,$inout_errorColumnIdList
					,"PATTERN_ID"
					,"VALUE_VARS_LINK_ID"
					,"VALUE_NESTEDMEM_COL_CMB_ID"
					,"VALUE_ASSIGN_SEQ"
				);
				/* もし、何らかのエラーならばwhile文から抜ける */
				if($ret === false) {
					continue 2;
				}

				/* ValueタイプはここでBreakする。※ Key-Valueタイプは引き続きKey型の検査をさせるのでBreakしない */
				if($col_type == DEFINE_COL_TYPE_VAL){
					break ;
				}

			/* KEY型の検査 */
			case DEFINE_COL_TYPE_KEY:
				/* Key型に設定されている変数設定確認 */
				$ret = valAssColumnValidate
				(
					 "Key"
					,$key_vars_attr
					,$row
					,"KEY_VARS_LINK_ID"
					,"KEY_VARS_NAME"
					,"KEY_PTN_VARS_LINK_CNT"
					,"KEY_VARS_ATTR_ID"
					,"KEY_NESTEDMEM_COL_CMB_ID"
					,"KEY_COL_COMBINATION_MEMBER_ALIAS"
					,"KEY_ASSIGN_SEQ"
					,"KEY_ASSIGN_SEQ_NEED"
				);
				/* もし、何らかのエラーならばwhile文から抜ける */
				if($ret === false) {
					continue 2 ;
				}

				/* KEY型変数の代入順序の重複（他レコードとの関連）をチェック */
				$ret = valAssSeqDuplicatePickUp
				(
					 "Key"
					,$key_vars_attr
					,$row
					,$lva_var_assign_seq_list
					,$lva_array_assign_seq_list
					,$inout_errorColumnIdList
					,"PATTERN_ID"
					,"KEY_VARS_LINK_ID"
					,"KEY_NESTEDMEM_COL_CMB_ID"
					,"KEY_ASSIGN_SEQ"
				);
				/* もし、何らかのエラーならばwhile文から抜ける */
				if($ret === false) {
					continue 2 ;
				}
				break;
		}

		/* < END > Key項目・Value項目の検査（当該レコード） ※カラムタイプにより処理分岐（Key-Valueタイプは両方の検査を実行）--------------- */
		$inout_tableNameToMenuIdList[$row['TABLE_NAME']] = $row['MENU_ID'];

		$inout_tabColNameToValAssRowList[$row['TABLE_NAME']][$row['COL_NAME']][] = array
			(
				'PRMCOL_VARS_LINK_ID'                => $row['PRMCOL_VARS_LINK_ID'],
				'PRMCOL_LINK_TYPE_ID'                => $row['PRMCOL_LINK_TYPE_ID'],
				'COL_LABEL'                          => $row['COL_TITLE'],
                'REF_TABLE_NAME'                     =>$row['REF_TABLE_NAME'],
                'REF_PKEY_NAME'                      =>$row['REF_PKEY_NAME'],
                'REF_COL_NAME'                       =>$row['REF_COL_NAME'],
				'PATTERN_ID'                         => $row['PATTERN_ID'],
				/* Value項目 */
				'VALUE_VARS_LINK_ID'                 => $row['VALUE_VARS_LINK_ID'],
				'VALUE_VARS_NAME'                    => $row['VALUE_VARS_NAME'],
				'VALUE_VARS_ATTR_ID'                 => $val_vars_attr,
				'VALUE_NESTEDMEM_COL_CMB_ID'         => $row['VALUE_NESTEDMEM_COL_CMB_ID'],
				'VALUE_COL_COMBINATION_MEMBER_ALIAS' => $row['VALUE_COL_COMBINATION_MEMBER_ALIAS'],
				'VALUE_ASSIGN_SEQ'                   => $row['VALUE_ASSIGN_SEQ'],
				// Key項目
				'KEY_VARS_LINK_ID'                   => $row['KEY_VARS_LINK_ID'],
				'KEY_VARS_NAME'                      => $row['KEY_VARS_NAME'],
				'KEY_VARS_ATTR_ID'                   => $key_vars_attr,
				'KEY_NESTEDMEM_COL_CMB_ID'           => $row['KEY_NESTEDMEM_COL_CMB_ID'],
				'KEY_COL_COMBINATION_MEMBER_ALIAS'   => $row['KEY_COL_COMBINATION_MEMBER_ALIAS'],
				'KEY_ASSIGN_SEQ'                     => $row['KEY_ASSIGN_SEQ'],
				'NULL_DATA_HANDLING_FLG'             => $row['NULL_DATA_HANDLING_FLG']
			);

		// テーブルの主キー名退避
		$inout_tableNameToPKeyNameList[$row['TABLE_NAME']] = $row['PKEY_NAME'];
	}
	/* < END > 取得データの反復確認処理----------------------------------------------------------------------------------------------------- */

	// DBアクセス事後処理
	unset($objQuery);

	return true;
}
/* < END > F0002：代入値自動登録設定からカラム情報を取得する-------------------------------------------------------------------------------- */

/* <START> F0003：代入値自動登録設定のカラム情報を検査する---------------------------------------------------------------------------------- */
function valAssColumnValidate
(
	 $in_col_type // カラムタイプ [Value/Key]
	,&$inout_vars_attr // 変数区分 (1:一般変数, 2:複数具体値, 3:多次元変数)
	,$row // クエリ配列
	,$in_vars_link_id // クエリ配列内のKey/Value型の変数IDキー [VAL_VARS_LINK_ID/KEY_VARS_LINK_ID]
	,$in_vars_name // クエリ配列内のKey/Value型の変数名キー [VAL_VARS_NAME/KEY_VARS_NAME]
	,$in_ptn_vars_link_cnt // クエリ配列内のKey/Value型の作業パターン+変数の作業パターン変数紐付の登録件数 [VAL_PTN_VARS_LINK_CNT/KEY_PTN_VARS_LINK_CNT]
	,$in_vars_attr_id // クエリ配列内の変数タイプ(Roleの場合のみ有効) [VARS_ATTR_ID]
	,$in_col_seq_combination_id // クエリ配列内のKey/Value型のメンバー変数IDキー [VAL_NESTEDMEM_COL_CMB_ID/KEY_NESTEDMEM_COL_CMB_ID]
	,$in_col_combination_member_alias // クエリ配列内のKey/Value型のメンバー変数名キー [VAL_COL_COMBINATION_MEMBER_ARIAS/KEY_COL_COMBINATION_MEMBER_ARIAS]
	,$in_assign_seq // クエリ配列内のKey/Value型の代入順序キー [VAL_ASSIGN_SEQ/KEY_ASSIGN_SEQ]
	,$in_assign_seq_need // クエリ配列内のKey/Value型の代入順序 要・不要 [VAL_ASSIGN_SEQ_NEED/KEY_ASSIGN_SEQ_NEED]
){
	global	$objMTS;
	global	$objDBCA;
	global	$log_level;

	/* 変数の選択判定 */
	if(@strlen($row[$in_vars_link_id]) == 0) {
		if($log_level === "DEBUG") {
			/* 代入値紐付（項番:｛｝）のValue型の変数が未選択 */
			$msgstr = 'Variables are not set in the Substitution value auto-registration setting. This record will be out of scope of processing. (Substitution value auto-registration setting. Item No.:' . implode(array($row['PRMCOL_VARS_LINK_ID'])) . ' Variable classification:' . implode(array($in_col_type)) . ')' ; // ITAANSIBLEH-ERR-90033
			LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
		}

		return false;
	}

	/* 変数が作業パターン変数紐付にあるか判定 */
	if(@strlen($row[$in_ptn_vars_link_cnt]) == 0) {
		if($log_level === "DEBUG") {
			/* 代入値紐付（項番:｛｝）の｛｝型で選択している変数と作業パターンの組合せは作業パターン詳細で作業パターンを紐付けていないか作業パターン詳細で紐付けているロールでは使用されていません */
			$msgstr = 'The combination of Movement and variables registered in the Substitution value auto-registration setting is not used in the role associated with Movement details, because Movement is not associated with Movement details. This record will be out of scope of processing. (Substitution value auto-registration setting. Item No.:' . implode(array($row['PRMCOL_VARS_LINK_ID'])) . ' Variable classification:' . implode(array($in_col_type)) . ')' ; // ITAANSIBLEH-ERR-90025
			LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
		}

		return false;
	}

	/* 設定されている変数が変数一覧にあるか判定 */
	if(@strlen($row[$in_vars_name]) == 0) {
		if($log_level === "DEBUG") {
			/* 代入値紐付（項番:｛｝）の｛｝型で選択している変数はロールパッケージ管理に登録されているロールパッケージでは使用されていません */
			$msgstr = 'Variables registered in the Substitution value auto-registration setting are not used in the role package registered in the Role package list. This record will be out of scope of processing. (Substitution value auto-registration setting. Item No.:' . implode(array($row['PRMCOL_VARS_LINK_ID'])) . ' Variable classification:' . implode(array($in_col_type)) . ')' ; // ITAANSIBLEH-ERR-90022
			LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
		}

		return false;
	}

	/* 変数属性のチェック（処理可能な属性であるか否や） */
	if(
		$row[$in_vars_attr_id] == GC_VARS_ATTR_STD // 一般変数
	 OR $row[$in_vars_attr_id] == GC_VARS_ATTR_LIST // 複数具体値
	 OR $row[$in_vars_attr_id] == GC_VARS_ATTR_M_ARRAY // 多次元変数
	){
		$inout_vars_attr = $row[$in_vars_attr_id];
	}else{
		if($log_level === "DEBUG") {
			/* 代入値自動登録設定（項番:｛｝）の｛｝型で設定している変数の属性は処理出来ません。このレコードを処理対象外にします */
			$msgstr = 'Variables registered in the Substitution value auto-registration setting cannot be classified. This record will be out of scope of processing. (Substitution value auto-registration setting. Item No.:' . implode(array($row['PRMCOL_VARS_LINK_ID'])) . ' Variable classification:' . implode(array($in_col_type)) . ')' ; // ITAANSIBLEH-ERR-90204
			LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
		}

		return false;
	}

	/* 変数属性 別のバリデーション */
	switch($inout_vars_attr) {
		case GC_VARS_ATTR_M_ARRAY:
			/* メンバー変数がメンバー変数一覧にあるか判定 */
			if(@strlen($row[$in_col_seq_combination_id]) == 0) {
				if($log_level === "DEBUG") {
					/* 代入値自動登録設定（項番:｛｝）の｛｝型で設定している変数はメンバー変数の設定が必要です。このレコードを処理対象外にします */
					$msgstr = 'It is required to set member variable for the variable set in the Substitution value auto-registration setting. This record will be out of scope of processing. (Substitution value auto-registration setting. Item No.:' . implode(array($row['PRMCOL_VARS_LINK_ID'])) . ' Variable classification:' . implode(array($in_col_type)) . ')' ; // ITAANSIBLEH-ERR-90103
					LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
				}

				return false;
			}

			/* カラムタイプ型に設定されているメンバー変数がメンバー変数一覧にあるか判定 */
			if(@strlen($row[$in_col_combination_member_alias]) == 0) {
				if($log_level === "DEBUG") {
					/* 代入値紐付（項番:｛｝）の｛｝型で選択しているメンバー変数はロールパッケージ管理に登録されているロールパッケージでは使用されていません。 */
					$msgstr = 'Member variables registered in the Substitution value auto-registration setting are not used in the role package registered in the Role package list. This record will be out of scope of processing. (Substitution value auto-registration setting. Item No.:' . implode(array($row['PRMCOL_VARS_LINK_ID'])) . ' Variable classification:' . implode(array($in_col_type)) . ')' ; // ITAANSIBLEH-ERR-90026
					LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
				}

				return false;
			}

			/* メンバー変数の選択判定 */
			if(@$row[$in_assign_seq_need]===1 && @strlen($row[$in_assign_seq])===0) {
				if($log_level === "DEBUG") {
					/* 代入値紐付（項番:｛｝）の｛｝型の代入順序が設定されていません */
					$msgstr = 'Substitution order of variables registered in the Substitution value auto-registration setting is not set. This record will be out of scope of processing. (Substitution value auto-registration setting. Item No.:' . implode(array($row['PRMCOL_VARS_LINK_ID'])) . ' Variable classification:' . implode(array($in_col_type)) . ')' ; // ITAANSIBLEH-ERR-90027
					LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
				}

				return false;
			}
			break;

		default:
			/* メンバー変数の設定可否 */
			if(@strlen($row[$in_col_seq_combination_id]) != 0) {
				if($log_level === "DEBUG") {
					/* 代入値自動登録設定（項番:｛｝）の｛｝型で設定している変数はメンバー変数を設定出来ません。このレコードを処理対象外にします */
					$msgstr = 'Could not set the member variable for the variable set in the Substitution value auto-registration setting. This record will be out of scope of processing. (Substitution value auto-registration setting. Item No.:' . implode(array($row['PRMCOL_VARS_LINK_ID'])) . ' Variable classification:' . implode(array($in_col_type)) . ')' ; // ITAANSIBLEH-ERR-90102
					LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
				}

				return false;
			}

			/* 一般変数の場合は、ここでbreak */
			if($inout_vars_attr == GC_VARS_ATTR_STD){
				break ;
			}

			/* メンバー変数の選択判定 */
			if(@strlen($row[$in_assign_seq])===0) {
				if($log_level === "DEBUG") {
					/* 代入値紐付（項番:｛｝）の｛｝型の代入順序が設定されていません */
					$msgstr = 'Substitution order of variables registered in the Substitution value auto-registration setting is not set. This record will be out of scope of processing. (Substitution value auto-registration setting. Item No.:' . implode(array($row['PRMCOL_VARS_LINK_ID'])) . ' Variable classification:' . implode(array($in_col_type)) . ')' ; // ITAANSIBLEH-ERR-90027
					LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
				}

				return false;
			}

	}

	return true;
}
/* < END > F0003：代入値自動登録設定のカラム情報を検査する---------------------------------------------------------------------------------- */


/* <START> F0004：代入値自動登録設定の代入順序の重複チェック-------------------------------------------------------------------------------- */
function valAssSeqDuplicatePickUp
(
	 $in_col_type // カラムタイプ（Value/Key）
	,$in_vars_attr // 変数区分 (1:一般変数, 2:複数具体値, 3:多次元変数)
	,$row // クエリ配列
	,&$inout_varAssignSeqList // 作業パターン+変数名+代入順序の重複チェック配列
	,&$inout_arrayAssignSeqList // 作業パターン+変数名+メンバー変数+代入順序の重複チェック配列
	,&$inout_errorColumnIdList // 代入値紐付でエラーが発生している代入値紐付の主キーリスト
	,$in_pattern_id // クエリ配列内のKey/Value型の変数名キー（VAL_VARS_NAME/KEY_VARS_NAME）
	,$in_vars_link_id // クエリ配列内のKey/Value型の変数IDキー（VAL_VARS_LINK_ID/KEY_VARS_LINK_ID）
	,$in_col_seq_combination_id // クエリ配列内のKey/Value型のメンバー変数IDキー（VALUE_NESTEDMEM_COL_CMB_ID/KEY_NESTEDMEM_COL_CMB_ID）
	,$in_assign_seq // クエリ配列内のKey/Value型の代入順序キー（VAL_ASSIGN_SEQ/KEY_ASSIGN_SEQ）
){
	global	$objMTS;
	global	$objDBCA;
	global	$log_level;

	switch($in_vars_attr){
		case GC_VARS_ATTR_STD: // 一般変数
			break;
		case GC_VARS_ATTR_LIST: // 複数具体値
			if(
				@count
				(
					$inout_varAssignSeqList
						[$row[$in_pattern_id]]
						[$row[$in_vars_link_id]]
						[$row[$in_assign_seq]]
				) != 0
			){
				$column_id
				 = $inout_varAssignSeqList
					[$row[$in_pattern_id]]
					[$row[$in_vars_link_id]]
					[$row[$in_assign_seq]]
				;

				/* 重複しているのでエラーリストに代入値紐付の主キーを退避 */
				$inout_errorColumnIdList[$column_id] = 1;
				$inout_errorColumnIdList[$row['PRMCOL_VARS_LINK_ID']] = 1;

				if($log_level === "DEBUG") {
					/* 代入値自動登録設定に登録されている変数の代入順序が重複しています。重複しているレコードは処理対象外にします。（代入値自動登録設定 項番:｛｝/｛｝ 変数区分:｛｝) */
					$msgstr = 'Substitution order of variables registered in the Substitution value auto-registration setting is duplicate. Duplicate records will be out of scope of processing. (Substitution value auto-registration setting. Item No.:' . implode(array($row['PRMCOL_VARS_LINK_ID'])) . ' / ' . implode(array($column_id)) . ' Variable classification:' . implode(array($in_col_type)) . ')' ; // ITAANSIBLEH-ERR-90029
					LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
				}

				return false;
			}

			$inout_varAssignSeqList
				[$row[$in_pattern_id]]
				[$row[$in_vars_link_id]]
				[$row[$in_assign_seq]]
			 = $row['PRMCOL_VARS_LINK_ID'];

			break;

		case GC_VARS_ATTR_M_ARRAY: // 多次元変数
			if(
				@count
				(
					$inout_arrayAssignSeqList
						[$row[$in_pattern_id]]
						[$row[$in_vars_link_id]]
						[$row[$in_col_seq_combination_id]]
						[$row[$in_assign_seq]]
				) != 0
			){
				$column_id = $inout_arrayAssignSeqList
				  [$row[$in_pattern_id]]
				  [$row[$in_vars_link_id]]
				  [$row[$in_col_seq_combination_id]]
				  [$row[$in_assign_seq]]
				;

				//重複しているのでエラーリストに代入値紐付の主キーを退避
				$inout_errorColumnIdList[$column_id] = 1;
				$inout_errorColumnIdList[$row['PRMCOL_VARS_LINK_ID']] = 1;

				if($log_level === "DEBUG") {
					/* 代入値自動登録設定の代入順序が重複しています。（代入値自動登録設定 項番:｛｝/｛｝ 変数区分:｛｝） */
					$msgstr = 'Substitution order of Substitution value auto-registration setting is duplicate. (Substitution value auto-registration setting. Item No.:' . implode(array($row['PRMCOL_VARS_LINK_ID'])) . ' / ' . implode(array($column_id)) . ' Variable classification:' . implode(array($in_col_type)) . ')' ; // ITAANSIBLEH-ERR-90208
					LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
				}
				return false;
			}

			/* 代入順序退避 */
			$inout_arrayAssignSeqList
				[$row[$in_pattern_id]]
				[$row[$in_vars_link_id]]
				[$row[$in_col_seq_combination_id]]
				[$row[$in_assign_seq]]
			 = $row['PRMCOL_VARS_LINK_ID'];

			break;
	}

	return true;
}
/* < END > F0004：代入値自動登録設定の代入順序の重複チェック-------------------------------------------------------------------------------- */

/* <START> F0005：代入値紐付メニューへのSELECT文を生成する---------------------------------------------------------------------------------- */
function createQuerySelectCMDB
(
	 $in_tableNameToMenuIdList // テーブル名配列（[テーブル名]=MENU_ID）
	,$in_tabColNameToValAssRowList // テーブル名+カラム名配列（[テーブル名][カラム名]=代入値自動登録設定情報）
	,$in_tableNameToPKeyNameList // テーブル主キー名配列（[テーブル名]=主キー名）
	,&$inout_tableNameToSqlList // 代入値紐付メニュー毎のSELECT文配列（[テーブル名][SELECT文]）
){
	global	$objMTS;
	global	$objDBCA;
	global	$log_level;

	/* 投入オペレーションにオペーレーションIDが登録されているかを判定するSQL */
	$opeid_chk_sql
	 = " 		 ("
	  ." 			SELECT COUNT(*) FROM C_OPERATION_LIST"
	  ." 			WHERE"
	  ." 			   OPERATION_NO_IDBH = TBL_A.OPERATION_ID"
	  ." 			AND"
	  ." 			   DISUSE_FLAG = '0'"
	  ." 		 ) AS " . DEFINE_ITA_LOCAL_OPERATION_CNT // 「AS」の後ろのSingleSpaceは必須であることに注意
	  ." 		,("
	  ." 			SELECT OPERATION_NO_UAPK FROM C_OPERATION_LIST"
	  ." 			WHERE"
	  ." 			   OPERATION_NO_IDBH = TBL_A.OPERATION_ID"
	  ." 			AND"
	  ." 			   DISUSE_FLAG = '0'"
	  ." 		 ) AS OPERATION_ID"
	;

	/* 機器一覧にホストが登録されているかを判定するSQL */
	$hostid_chk_sql
	 = " 		,("
	  ." 			SELECT COUNT(*) FROM C_STM_LIST"
	  ." 			WHERE"
	  ." 			   SYSTEM_ID = TBL_A.HOST_ID"
	  ." 			AND"
	  ." 			   DISUSE_FLAG = '0'"
	  ." 		 ) AS " . DEFINE_ITA_LOCAL_HOST_CNT // 「AS」の後ろのSingleSpaceは必須であることに注意
	;

	/* テーブル名+カラム名配列からテーブル名と配列名を取得 */
	foreach($in_tabColNameToValAssRowList as $table_name=>$col_list) {

		$pkey_name = $in_tableNameToPKeyNameList[$table_name];

		// オペーレーションID+ホストの組合せが複数登録されているかを判定するSQL
		$dup_chk_sql
		 = " 		,("
		  ." 			SELECT COUNT(*) FROM $table_name TBL_B"
		  ." 			WHERE"
		  ." 				TBL_B.OPERATION_ID = TBL_A.OPERATION_ID"
		  ." 			AND"
		  ." 				TBL_B.HOST_ID = TBL_A.HOST_ID"
		  ." 			AND"
		  ." 				TBL_B.DISUSE_FLAG = '0'"
		  ." 		 ) AS " . DEFINE_ITA_LOCAL_DUP_CHECK_ITEM // 「AS」の後ろのSingleSpaceは必須であることに注意
		  ." 		,"
		;

		$col_sql = "";
		foreach(array_keys($col_list) as $col_name) {
			$col_sql
			 = $col_sql
			 . ",TBL_A." . $col_name . " \n";
		}

		/* SELECT対象の項目なしの場合、処理をスキップする */
		if($col_sql == "") {
			if($log_level === "DEBUG") {
				/* 紐付メニュー（MENU_ID:｛｝）は代入値自動登録設定からカラム情報を取得出来ないので処理対象外 */
				$msgstr = 'This associated menu has no column information. Associated menu is out of scope of processing. (MENU_ID:' . implode(array($in_tableNameToMenuIdList[$table_name])) . ')' ; // ITAANSIBLEH-ERR-90035
				LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
			}
			continue;
		}

		/* SELECT文を生成 */
		$make_sql
		 = " 			SELECT"
		  ." "			  .$opeid_chk_sql
		  ." "			  .$hostid_chk_sql
		  ." "			  .$dup_chk_sql
		  ." "			  ." TBL_A." . $pkey_name . " AS " . DEFINE_ITA_LOCAL_PKEY
		  ." "			  .",TBL_A.HOST_ID"
		  ." "			  .$col_sql
		  ." "			."FROM"
		  ." "			   .$table_name . " AS TBL_A"
		  ." "			."WHERE"
		  ." "			   ."DISUSE_FLAG = '0' "
		;

		//メニューテーブルのSELECT SQL文退避
		$inout_tableNameToSqlList[$table_name] = $make_sql;
	}
}
/* < END > F0005：代入値紐付メニューへのSELECT文を生成する---------------------------------------------------------------------------------- */

/* <START> F0006：CMDB代入値紐付対象メニューから具体値を取得する---------------------------------------------------------------------------- */
function getCMDBdata
(
	 $in_tableNameToSqlList // CMDB代入値紐付メニュー毎のSELECT文配列（[テーブル名][SELECT文]）
	,$in_tableNameToMenuIdList // テーブル名配列（[テーブル名]=MENU_ID）
	,$in_tabColNameToValAssRowList // カラム情報配列（[テーブル名][カラム名][]=>array("代入値紐付のカラム名"=>値)）
	,$in_errorColumnIdList // 代入値自動登録設定の登録に不備がある主キーの配列（[代入値自動登録設定主キー]=1）
	,&$ina_vars_ass_list // 一般変数・複数具体値変数用 代入値登録情報配列（多次元変数配列変数用 代入値登録情報配列）
	,&$ina_array_vars_ass_list // 警告フラグ
	,&$warning_flag
){
	global	$objMTS;
	global	$objDBCA;
	global	$log_level;

	/* オペ+作業+ホスト+変数の組合せの代入順序 重複確認用 */
	$lv_varsAssChkList = array();
	/* オペ+作業+ホスト+変数+メンバ変数の組合せの代入順序 重複確認用 */
	$lv_arrayVarsAssChkList = array();

	foreach($in_tableNameToSqlList as $table_name=>$sql){
		/* トレースメッセージ */
		if($log_level === "DEBUG"){
			/* [処理]紐付メニュー（MENU_ID:｛｝）から具体値を取得 */
			$traceMsg = '[Process] Get specific value from associated menu (MENU_ID:' . implode(array($in_tableNameToMenuIdList[$table_name])) . ')' ; // ITAANSIBLEH-STD-70017
			LocalLogPrint(basename(__FILE__),__LINE__,$traceMsg);
		}

		$sqlUtnBody = $sql;
		$arrayUtnBind = array();

		/* CMDB代入値紐付メニューのデータを取出す */
		$objQuery = recordSelect($sqlUtnBody, $arrayUtnBind);
		if($objQuery == null) {
			/* DBアクセスエラー */
			if($log_level === "DEBUG") {
				/* 紐付メニュー（MENU_ID:｛｝）からのデータ取得に失敗しました。この紐付メニューは処理対象外にします。 */
				$msgstr = 'Get information of associated menu. Associated menu is out of scope of processing has failed. (MENU_ID:' . implode(array($in_tableNameToMenuIdList[$table_name])) . ')' ; // ITAANSIBLEH-ERR-90036
				LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
			}
			$warning_flag = 1;

			continue;
		}

		/* FETCH行数を取得 */
		$total_row = array();
		while($row = $objQuery->resultFetch()) {
			$total_row[] = $row;
		}

		/* DBアクセス事後処理 */
		unset($objQuery);

		/* CMDB代入値紐付メニューに具体値の登録なし */
		if(@count($total_row) === 0) {
			if($log_level === "DEBUG") {
				/* 紐付メニュー（MENU_ID:｛｝）にデータ未登録 */
				$msgstr = 'Data is not registered in the associated menu. (MENU_ID:' . implode(array($in_tableNameToMenuIdList[$table_name])) . ')' ; // ITAANSIBLEH-ERR-90048
				LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
			}

			continue;
		}

		foreach($total_row as $row) {
			/* CMDB代入値紐付メニューに登録されているオペレーションIDの紐付確認 */
			if($row[DEFINE_ITA_LOCAL_OPERATION_CNT] == 0){
				/* オペレーションIDの紐付不正 */
				if($log_level === "DEBUG") {
					/* 紐付メニュー（MENU_ID:｛｝ 項番:｛｝）で投入オペレーション一覧に登録されていないオペレーションID（｛｝）が設定されています */
					$msgstr = 'Operation registered in the associated menu is not registered in the Input operation list. This record will be out of scope of processing. (MENU_ID:' . implode(array($in_tableNameToMenuIdList[$table_name])) . ' Associated menu: Item No..:' . implode(array($row[DEFINE_ITA_LOCAL_PKEY])) . ' Operation ID:' . implode(array($row['OPERATION_ID'])) . ')' ; // ITAANSIBLEH-ERR-90037
					LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
				}
				$warning_flag = 1;

				continue;
			}

			/* 代入値紐付メニューに登録されているオペレーションIDを確認 */
			if(@strlen($row['OPERATION_ID']) == 0) {
				/* オペレーションID未登録 */
				if($log_level === "DEBUG") {
					/* 紐付メニュー（MENU_ID:｛｝ 項番:｛｝）でオペレーションIDが登録されていません。このレコードを処理対象外とします */
					$msgstr = 'Operation ID column is not set in the associated menu. This record will be out of scope of processing. (MENU_ID:' . implode(array($in_tableNameToMenuIdList[$table_name])) . ' Associated menu item No.:' . implode(array($row[DEFINE_ITA_LOCAL_PKEY])) . ')' ; // ITAANSIBLEH-ERR-90040
					LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
				}
				$warning_flag = 1;

				continue;
			}

			$operation_id = $row['OPERATION_ID'];
			/* 代入値紐付メニューに登録されているホストIDの紐付確認 */
			if($row[DEFINE_ITA_LOCAL_HOST_CNT] == 0) {
				/* ホストIDの紐付不正 */
				if($log_level === "DEBUG") {
					/* 紐付メニュー（MENU_ID:｛｝ 項番:｛｝）で機器一覧に登録されていないホストID（｛｝）が設定されています。このレコードを処理対象外とします */
					$msgstr = 'Host registered in the associated menu is not registered in the device list. This record will be out of scope of processing. (MENU_ID:' . implode(array($in_tableNameToMenuIdList[$table_name])) . ' Associated menu Item No.:' . implode(array($row[DEFINE_ITA_LOCAL_PKEY])) . ' Host ID:' . implode(array($row['HOST_ID'])) . ')' ; // ITAANSIBLEH-ERR-90038
					LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
				}
				$warning_flag = 1;

				continue;
			}

			/* 代入値紐付メニューに登録されているホストIDを確認 */
			if(@strlen($row['HOST_ID']) == 0) {
				/* ホストID未登録 */
				if($log_level === "DEBUG") {
					/* 紐付メニュー（MENU_ID:｛｝ 項番:｛｝）でホストIDが登録が登録されていません。このレコードを処理対象外とします */
					$msgstr = 'A host ID column is not set in the associated menu. This record will be out of scope of processing. (MENU_ID:' . implode(array($in_tableNameToMenuIdList[$table_name])) . ' Associated menu item No.:' . implode(array($row[DEFINE_ITA_LOCAL_PKEY])) . ')' ; // ITAANSIBLEH-ERR-90041
					LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
				}

				$warning_flag = 1;

				continue;
			}

			$host_id = $row['HOST_ID'];

			/* 代入値自動登録設定に登録されている変数に対応する具体値を取得する */
			foreach($row as $col_name=>$col_val) {

				/* 具体値カラム以外を除外 */
				$chk_name = [
					 DEFINE_ITA_LOCAL_OPERATION_CNT
					,DEFINE_ITA_LOCAL_HOST_CNT
					,DEFINE_ITA_LOCAL_DUP_CHECK_ITEM
					,"OPERATION_ID"
					,"HOST_ID"
					,DEFINE_ITA_LOCAL_PKEY
				];
				if(in_array($col_name , $chk_name)){ continue ; }

				/* 再度カラムをチェック */
				if(
					@count(
						$in_tabColNameToValAssRowList [$table_name] [$col_name]
					) == 0
				){
					continue ;
				}

				foreach($in_tabColNameToValAssRowList[$table_name][$col_name] as $col_data) {
					// テーブル名+カラム名の情報にエラーがないか判定
					if(@count($in_errorColumnIdList[$col_data['PRMCOL_VARS_LINK_ID']]) != 0) {
						continue;
					}

                    // IDcolumnの場合は参照元から具体値を取得する
                    if("" != $col_data['REF_TABLE_NAME']){
                        $sql = "";
                        $sql = $sql . "SELECT " . $col_data['REF_COL_NAME'] . " ";
                        $sql = $sql . "FROM   " . $col_data['REF_TABLE_NAME'] . " ";
                        $sql = $sql . "WHERE " . $col_data['REF_PKEY_NAME'] . "=:" . $col_data['REF_PKEY_NAME'] . " ";
                        $sql = $sql . " AND DISUSE_FLAG='0'";

                        $objQuery = $objDBCA->sqlPrepare($sql);
                        if($objQuery->getStatus()===false){
                            //$ary[80000] = "ＤＢアクセス異常([FILE]｛｝[LINE]｛｝)"
                            $msgstr = $objMTS->getSomeMessage("ITAANSIBLEH-ERR-80000",array(basename(__FILE__),__LINE__));
                            LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
                            LocalLogPrint(basename(__FILE__),__LINE__,$sql);
                            LocalLogPrint(basename(__FILE__),__LINE__,$objQuery->getLastError());

                            unset($objQuery);
                            continue;
                        }

                        $objQuery->sqlBind(array($col_data['REF_PKEY_NAME'] => $col_val));

                        $r = $objQuery->sqlExecute();
                        if (!$r){
                            //$ary[80000] = "ＤＢアクセス異常([FILE]｛｝[LINE]｛｝)"
                            $msgstr = $objMTS->getSomeMessage("ITAANSIBLEH-ERR-80000",array(basename(__FILE__),__LINE__));
                            LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
                            LocalLogPrint(basename(__FILE__),__LINE__,$sql);
                            LocalLogPrint(basename(__FILE__),__LINE__,$objQuery->getLastError());

                            unset($objQuery);
                            continue;
                        }

                        // fetch行数を取得
                        $count = $objQuery->effectedRowCount();

                        // 1件ではない場合
                        if(1 != $count){
                            continue;
                        }
                        // fetch行を取得
                        $tgt_row = $objQuery->resultFetch();
                        $col_val = $tgt_row[$col_data['REF_COL_NAME']];
                        unset($objQuery);
                    }

					// 代入値管理の登録に必要な情報を生成
					makeVarsAssignData
					(
						 $table_name
						,$col_name
						,$col_val
						,$col_data['NULL_DATA_HANDLING_FLG']
						,$operation_id
						,$host_id
						,$col_data
						,$ina_vars_ass_list
						,$lv_varsAssChkList
						,$ina_array_vars_ass_list
						,$lv_arrayVarsAssChkList
						,$in_tableNameToMenuIdList[$table_name]
						,$row[DEFINE_ITA_LOCAL_PKEY]
					);
					/* 戻り値は判定しない ← 開発メモ：何故かは分からない。でも一旦そのまま */
				}
			}
		}
	}
}
/* < END > F0006：CMDB代入値紐付対象メニューから具体値を取得する---------------------------------------------------------------------------- */

/* <START> F0007：代入値紐付対象メニューの情報から代入値管理に登録する情報を生成------------------------------------------------------------ */
function makeVarsAssignData
(
	 $in_table_name // テーブル名
	,$in_col_name // カラム名
	,$in_col_val // カラムの具体値
	,$in_null_data_handling_flg
	,$in_operation_id // オペレーションID
	,$in_host_id // ホストID
	,$in_col_list // カラム情報配列
	,&$ina_vars_ass_list // 一般変数・複数具体値変数用 代入値登録情報配列
	,&$ina_vars_ass_chk_list // 一般変数・複数具体値変数用 代入順序重複チェック配列
	,&$ina_array_vars_ass_list // 多次元変数配列変数用 代入値登録情報配列
	,&$ina_array_vars_ass_chk_list // 多次元変数配列変数用 列順序重複チェック配列
	,$in_menu_id // 紐付メニューID
	,$in_row_id // 紐付テーブル主キー値
){
	global $log_level;
	global $objMTS;
	global $objDBCA;

	/* ロードテーブルより読み取るとColumnGroup名/ColumnTitleになる。代入値管理への登録はColumnTitleのみにする。 */
	$col_name_array = explode("/",$in_col_list['COL_LABEL']);
	if(
		($col_name_array === false)
	  OR
		(count($col_name_array) == 1)
	){
		$col_name = $in_col_list['COL_LABEL'];
	}
	else{
		$idx = count($col_name_array);
		$idx = $idx - 1;
		$col_name = $col_name_array[$idx];
	}

	/* カラムタイプを判定 */
	switch($in_col_list['PRMCOL_LINK_TYPE_ID']){
	  /* Value型カラムの場合 */
	  case DEFINE_COL_TYPE_VAL :
	  case DEFINE_COL_TYPE_KVL :
		/* 具体値が空白または1024バイト以上ないか判定 */
		$ret
		= validateValueTypeColValue
		  (
			 $in_col_val
			,$in_null_data_handling_flg
			,$in_menu_id
			,$in_row_id
			,$in_col_list['COL_LABEL']
		  );
		/* 空白の場合処理対象外 */
		if($ret === false){

			return ;
		}

		/* checkAndCreateVarsAssignDataの戻りは判定しない */
		checkAndCreateVarsAssignData
		(
			 $in_table_name
			,$in_col_name
			,$in_col_list['VALUE_VARS_ATTR_ID']
			,$in_operation_id
			,$in_host_id
			,$in_col_list['PATTERN_ID']
			,$in_col_list['VALUE_VARS_LINK_ID']
			,$in_col_list['VALUE_NESTEDMEM_COL_CMB_ID']
			,$in_col_list['VALUE_ASSIGN_SEQ']
			,$in_col_val
			,$ina_vars_ass_list
			,$ina_vars_ass_chk_list
			,$ina_array_vars_ass_list
			,$ina_array_vars_ass_chk_list
			,$in_menu_id
			,$in_col_list['PRMCOL_VARS_LINK_ID']
			,"Value"
			,$in_row_id
		);

		/* VALUEタイプは、ここでbreakする。（KEY-VALUIEタイプはBreakしない） */
		if($in_col_list['PRMCOL_LINK_TYPE_ID'] == DEFINE_COL_TYPE_VAL){ break ; }

	  /* Key型カラムの場合 */
	  case DEFINE_COL_TYPE_KEY :
		/* 具体値が空白か判定 */
		$ret
		= validateKeyTypeColValue
		  (
			 $in_col_val
			,$in_null_data_handling_flg
			,$in_menu_id
			,$in_row_id
			,$in_col_list['COL_LABEL']
		  );
		/* 空白の場合処理対象外 */
		if($ret === false) {

			return;
		}

		/* checkAndCreateVarsAssignDataの戻りは判定しない */
		checkAndCreateVarsAssignData
		(
			 $in_table_name
			,$in_col_name
			,$in_col_list['KEY_VARS_ATTR_ID']
			,$in_operation_id
			,$in_host_id
			,$in_col_list['PATTERN_ID']
			,$in_col_list['KEY_VARS_LINK_ID']
			,$in_col_list['KEY_NESTEDMEM_COL_CMB_ID']
			,$in_col_list['KEY_ASSIGN_SEQ']
			,$col_name
			,$ina_vars_ass_list
			,$ina_vars_ass_chk_list
			,$ina_array_vars_ass_list
			,$ina_array_vars_ass_chk_list
			,$in_menu_id
			,$in_col_list['PRMCOL_VARS_LINK_ID']
			,"Key"
			,$in_row_id
		);
		break ;
	}
}
/* < END > F0007：代入値紐付対象メニューの情報から代入値管理に登録する情報を生成------------------------------------------------------------ */

/* <START> F0008：代入値紐付対象メニューの情報から代入値管理に登録する情報を生成------------------------------------------------------------ */
function checkAndCreateVarsAssignData
(
	 $in_table_name  // テーブル名
	,$in_col_name // カラム名
	,$in_vars_attr  // 変数区分 (1:一般変数, 2:複数具体値, 3:多次元変数)
	,$in_operation_id // オペレーションID
	,$in_host_id // ホスト名
	,$in_patten_id // パターンID
	,$in_vars_link_id // 変数ID
	,$in_col_seq_combination_id // メンバー変数ID
	,$in_vars_assign_seq // 代入順序
	,$in_col_val // 具体値
	,&$ina_vars_ass_list // 一般変数・複数具体値変数用 代入値登録情報配列
	,&$ina_vars_ass_chk_list // 一般変数・複数具体値変数用 代入順序重複チェック配列
	,&$ina_array_vars_ass_list // 多次元変数配列変数用 代入値登録情報配列
	,&$ina_array_vars_ass_chk_list // 多次元変数配列変数用 列順序重複チェック配列
	,$in_menu_id // 紐付メニューID
	,$in_column_id // 代入値自動登録設定
	,$keyValueType // 変数の型（Value型なのかKey型なのか
	,$in_row_id // 紐付テーブル主キー値
){
	global $log_level;
	global $objMTS;
	global $objDBCA;

	$chk_status = false;

	/* 変数のタイプを判定 */
	if(
		$in_vars_attr == GC_VARS_ATTR_STD // 一般変数
	  OR
		$in_vars_attr == GC_VARS_ATTR_LIST // 複数具体値
	){
		/* （一般変数・複数具体値）オペ・作業・ホスト・変数の組合せで代入順序が重複していないか判定 */
		if(
			@count
			(
				$ina_vars_ass_chk_list
				  [$in_operation_id]
				  [$in_patten_id]
				  [$in_host_id]
				  [$in_vars_link_id]
				  [$in_vars_assign_seq]
			) != 0
		){
			/* 既に登録されている */
			if($log_level === "DEBUG"){
				$dup_info = $ina_vars_ass_chk_list
				  [$in_operation_id]
				  [$in_patten_id]
				  [$in_host_id]
				  [$in_vars_link_id]
				  [$in_vars_assign_seq]
				;
				/* 紐付メニュー（MENU_ID:｛｝ 項番:｛｝）のオペレーションID（｛｝） 作業パターンID（｛｝） ホストID（｛｝）と代入値自動登録設定（項番:｛｝）に設定されている｛｝型の変数と代入順序の組合せは、紐付 メニュー（MENU_ID:｛｝ 項番:｛｝）に紐付く代入値自動登録設定の情報と重複しています。このレコードを処理対象外とします */ 
				$msgstr = 'Combination of variable and substitution order of variable classification (' . implode(array($keyValueType)) . '), which is set in the operation ID(' . implode(array($in_operation_id)) . ') MovementID(' . implode(array($in_patten_id)) . ') Host ID(' . implode(array($in_host_id)) . ') of associated menu (MENU_ID:' . implode(array($in_menu_id)) . ' Item No.:' . implode(array($in_row_id)) . ') and Substitution value auto-registration setting (Item No.:' . implode(array($in_column_id)) . '), is duplicated with the details of Substitution value auto-registration setting with the associated menu (MENU_ID:' . implode(array($dup_info['MENU_ID'])) . ' Item No.:' . implode(array($dup_info[DEFINE_ITA_LOCAL_PKEY])) . '). This record will be out of scope of processing.' ; // ITAANSIBLEH-ERR-90050
				LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
			}
		} else {
			$chk_status = true;
			/* オペ+作業+ホスト+変数+メンバ変数の組合せの代入順序退避 */
			$ina_vars_ass_chk_list
			  [$in_operation_id]
			  [$in_patten_id]
			  [$in_host_id]
			  [$in_vars_link_id]
			  [$in_vars_assign_seq] = array
			  (
				 'MENU_ID' => $in_menu_id
				,DEFINE_ITA_LOCAL_PKEY => $in_row_id
			  );
		}
		/* 代入値管理の登録に必要な情報退避 */
		$ina_vars_ass_list[] = array
		  (
			 'TABLE_NAME'        => $in_table_name
			,'COL_NAME'          => $in_col_name
			,'OPERATION_NO_UAPK' => $in_operation_id
			,'PATTERN_ID'        => $in_patten_id
			,'SYSTEM_ID'         => $in_host_id
			,'VARS_LINK_ID'      => $in_vars_link_id
			,'ASSIGN_SEQ'        => $in_vars_assign_seq
			,'VARS_ENTRY'        => $in_col_val
			,'VAR_TYPE'          => $in_vars_attr
			,'STATUS'            => $chk_status
		  );
	}
	else if($in_vars_attr == GC_VARS_ATTR_M_ARRAY)  // 多次元変数
	{
		/* オペ・作業+ホスト・変数・メンバ変数の組合せで代入順序が重複していないか判定 */
		if(@count
			(
				$ina_array_vars_ass_chk_list
				  [$in_operation_id]
				  [$in_patten_id]
				  [$in_host_id]
				  [$in_vars_link_id]
				  [$in_col_seq_combination_id]
				  [$in_vars_assign_seq]
			) != 0
		){
			/* 既に登録されている */
			if($log_level === "DEBUG"){
				$dup_info = $ina_array_vars_ass_chk_list
				  [$in_operation_id]
				  [$in_patten_id]
				  [$in_host_id]
				  [$in_vars_link_id]
				  [$in_col_seq_combination_id]
				  [$in_vars_assign_seq]
				;
				/* 紐付対象メニュー（MENU_ID:｛｝ 項番:｛｝）のオペレーションID（｛｝） 作業パターンID（｛｝） ホストID（｛｝）と代入値自動登録設定（項番:｛｝）に設定されている｛｝型の多次元変数と代入順序と列順序の組合せは、紐付対象メニュー（MENU_ID:｛｝項番:｛｝）に紐付く代入値自動登録設定の情報と重複しています。このレコードを処理対象外とします。 */
				$msgstr = 'Combination of nested variable, substitution order and column order of ' . implode(array($keyValueType)) . ' type, which is set in the operation ID(' . implode(array($in_operation_id)) . ') Execution Pattern ID(' . implode(array($in_patten_id)) . ') Host ID(' . implode(array($in_host_id)) . ') of associated menu (MENU_ID:' . implode(array($in_menu_id)) . ' Item No.:' . implode(array($in_row_id)) . ') and Substitution value auto-registration setting (Item No.:' . implode(array($in_column_id)) . '), is duplicated with the details of Substitution value auto-registration setting associated with the menu (MENU_ID:' . implode(array($dup_info['MENU_ID'])) . ' Item No.:' . implode(array($dup_info[DEFINE_ITA_LOCAL_PKEY])) . '). This record will be out of scope of processing.' ; // ITAANSIBLEH-ERR-90209
				LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
			}
		} else {
			$chk_status = true;
			/* オペ・作業・ホスト・変数・メンバ変数の組合せの列順序退避 */
			$ina_array_vars_ass_chk_list
			  [$in_operation_id]
			  [$in_patten_id]
			  [$in_host_id]
			  [$in_vars_link_id]
			  [$in_col_seq_combination_id]
			  [$in_vars_assign_seq] = array
			  (
				 'MENU_ID' => $in_menu_id
				,DEFINE_ITA_LOCAL_PKEY => $in_row_id
			  );
		}
		/* 代入値管理の登録に必要な情報退避 */
		$ina_array_vars_ass_list[] = array
		  (
			 'TABLE_NAME'           => $in_table_name
			,'COL_NAME'             => $in_col_name
			,'OPERATION_NO_UAPK'    => $in_operation_id
			,'PATTERN_ID'           => $in_patten_id
			,'SYSTEM_ID'            => $in_host_id
			,'VARS_LINK_ID'         => $in_vars_link_id
			,'NESTEDMEM_COL_CMB_ID' => $in_col_seq_combination_id
			,'ASSIGN_SEQ'           => $in_vars_assign_seq
			,'VARS_ENTRY'           => $in_col_val
			,'VAR_TYPE'             => $in_vars_attr
			,'STATUS'               => $chk_status
		  );
	}
}
/* < END > F0008：代入値紐付対象メニューの情報から代入値管理に登録する情報を生成------------------------------------------------------------ */

/* <START> F0009：代入値管理（一般変数・複数具体値変数）を更新する-------------------------------------------------------------------------- */
function addStg1StdListVarsAssign
(
	 $in_varsAssignList // 代入値管理更新情報配列
	,&$inout_assingId // 追加/更新した代入値管理主キー値
){
	global	$db_model_ch;
	global	$objMTS;
	global	$objDBCA;
	global	$log_level;

	global $db_valautostup_user_id;
	global $strCurTableVarsAss;
	global $strJnlTableVarsAss;
	global $strSeqOfCurTableVarsAss;
	global $strSeqOfJnlTableVarsAss;
	global $arrayConfigOfVarAss;
	global $arrayValueTmplOfVarAss;

	$strCurTable = $strCurTableVarsAss;
	$strJnlTable = $strJnlTableVarsAss;

	$arrayConfig = $arrayConfigOfVarAss;
	$arrayValue  = $arrayValueTmplOfVarAss;

	$strSeqOfCurTable = $strSeqOfCurTableVarsAss;
	$strSeqOfJnlTable = $strSeqOfJnlTableVarsAss;

	$assign_bind = false;
	if(strlen($in_varsAssignList['ASSIGN_SEQ']) != 0){
		$assign_where = "ASSIGN_SEQ = :ASSIGN_SEQ";
		$assign_bind  = true;
	}
	else{
		$assign_where = "ASSIGN_SEQ IS NULL";
	}

	$temp_array = array
	  (
		  			'WHERE' =>
		 " 			   OPERATION_NO_UAPK = :OPERATION_NO_UAPK"
		." 			AND"
		." 			   PATTERN_ID        = :PATTERN_ID"
		." 			AND"
		." 			   SYSTEM_ID         = :SYSTEM_ID"
		." 			AND"
		." 			   VARS_LINK_ID      = :VARS_LINK_ID"
		." 			AND"
		." 			   DISUSE_FLAG       = '0'"
		." 			AND"
		." 			" .$assign_where
	  );

	$retArray = makeSQLForUtnTableUpdate
	  (
		 $db_model_ch
		,"SELECT FOR UPDATE"
		,"VARS_ASSIGN_ID"
		,$strCurTable
		,$strJnlTable
		,$arrayConfig
		,$arrayValue
		,$temp_array
	  );

	$sqlUtnBody = $retArray[1];

	$bind_array = array
	(
		 'OPERATION_NO_UAPK' => $in_varsAssignList['OPERATION_NO_UAPK']
		,'PATTERN_ID'        => $in_varsAssignList['PATTERN_ID']
		,'SYSTEM_ID'         => $in_varsAssignList['SYSTEM_ID']
		,'VARS_LINK_ID'      => $in_varsAssignList['VARS_LINK_ID']
	);

	if($assign_bind == true){ $bind_array['ASSIGN_SEQ'] = $in_varsAssignList['ASSIGN_SEQ']; }
	$arrayUtnBind = $bind_array;

	$objQueryUtn = recordSelect($sqlUtnBody, $arrayUtnBind);
	if($objQueryUtn == null){
		return false;
	}

	/* fetch行数を取得 */
	$count = $objQueryUtn->effectedRowCount();
	$row = $objQueryUtn->resultFetch();
	unset($objQueryUtn);

	if($count == 0){
		return addStg2StdListVarsAssign($in_varsAssignList, $inout_assingId);
	}
	else{
		$action = "UPDATE";
		$tgt_row = $row;

		/* 更新対象の代入値管理主キー値を退避 */
		$inout_assingId = $tgt_row['VARS_ASSIGN_ID'];

		/* 具体値が変更になっているか判定する */
		if($row['VARS_ENTRY'] == $in_varsAssignList['VARS_ENTRY']){
			/* トレースメッセージ */
			if($log_level === "DEBUG"){
				/* [処理]代入値管理 更新不要 OPERATION_ID:｛｝ PATTERN_ID:｛｝ SYSTEM_ID:｛｝ VARS_LINK_ID:｛｝ ASSIGN_SEQ:｛｝ */
				$traceMsg = '[Process] Update not required substitution value list(OPERATION_ID:' . implode(array($in_varsAssignList['OPERATION_NO_UAPK'])) . ' PATTERN_ID:' . implode(array($in_varsAssignList['PATTERN_ID'])) . ' SYSTEM_ID:' . implode(array($in_varsAssignList['SYSTEM_ID'])) . ' VARS_LINK_ID:' . implode(array($in_varsAssignList['VARS_LINK_ID'])) . ' ASSIGN_SEQ:{})' . implode(array($in_varsAssignList['ASSIGN_SEQ'])) ; // ITAANSIBLEH-STD-70026
				LocalLogPrint(basename(__FILE__),__LINE__,$traceMsg);
			}

			return true ; // 同一みなので処理終了
		}

		/* 最終更新者が自分でない場合、更新処理はスキップする */
		if($row['LAST_UPDATE_USER'] != $db_valautostup_user_id){
			/* トレースメッセージ */
			if($log_level === "DEBUG"){
				/* [処理]代入値管理 最終更新者が自分でないので更新スキップ OPERATION_ID:｛｝ PATTERN_ID:｛｝ SYSTEM_ID:｛｝ VARS_LINK_ID:｛｝ ASSIGN_SEQ:｛｝ */
				$traceMsg = '[Process] Skip update of the substitution value list because last update was not done by the person himself (OPERATION_ID:' . implode(array($in_varsAssignList['OPERATION_NO_UAPK'])) . ' PATTERN_ID:' . implode(array($in_varsAssignList['PATTERN_ID'])) . ' SYSTEM_ID:' . implode(array($in_varsAssignList['SYSTEM_ID'])) . ' VARS_LINK_ID:' . implode(array($in_varsAssignList['VARS_LINK_ID'])) . ' ASSIGN_SEQ:' . implode(array($in_varsAssignList['ASSIGN_SEQ'])) . ')' ; // ITAANSIBLEH-STD-70046
				LocalLogPrint(basename(__FILE__),__LINE__,$traceMsg);
			}

			return true ; // 更新処理はスキップ
		}

		/* トレースメッセージ */
		if($log_level === "DEBUG"){
			$traceMsg = '[Process] Update substitution value list (OPERATION_ID:' . $in_varsAssignList['OPERATION_NO_UAPK'] . ' PATTERN_ID:' . $in_varsAssignList['PATTERN_ID'] . ' SYSTEM_ID:' . $in_varsAssignList['SYSTEM_ID'] . ' VARS_LINK_ID:' . $in_varsAssignList['VARS_LINK_ID'] . ' ASSIGN_SEQ:' . $in_varsAssignList['ASSIGN_SEQ'] . ')' ; // ITAANSIBLEH-STD-70025
			LocalLogPrint(basename(__FILE__),__LINE__,$traceMsg);
		}
	}

	// ロール管理ジャーナルに登録する情報設定
	$seqValueOfJnlTable = getAndLockSeq($strSeqOfJnlTable);
	if($seqValueOfJnlTable == -1){
		return false;
	}

	$tgt_row['JOURNAL_SEQ_NO']       = $seqValueOfJnlTable;
	$tgt_row['VARS_ENTRY']           = $in_varsAssignList['VARS_ENTRY'];
	$tgt_row['NESTEDMEM_COL_CMB_ID'] = "";
	$tgt_row['DISUSE_FLAG']          = "0";
	$tgt_row['LAST_UPDATE_USER']     = $db_valautostup_user_id;

	$temp_array = array();
	$retArray = makeSQLForUtnTableUpdate
	  (
		 $db_model_ch
		,$action
		,"VARS_ASSIGN_ID"
		,$strCurTable
		,$strJnlTable
		,$arrayConfig
		,$tgt_row
		,$temp_array
	  );

	$sqlUtnBody = $retArray[1];
	$arrayUtnBind = $retArray[2];

	$sqlJnlBody = $retArray[3];
	$arrayJnlBind = $retArray[4];

	if(!recordUpdate($sqlUtnBody, $arrayUtnBind, $sqlJnlBody, $arrayJnlBind)){
		return false;
	}

	return true;
}
/* < END > F0009：代入値管理（一般変数・複数具体値変数）を更新する-------------------------------------------------------------------------- */

/* <START> F0010：代入値管理（多段配列変数）を更新する-------------------------------------------------------------------------------------- */
function addStg1ArrayVarsAssign
(
	 $in_varsAssignList // 代入値管理更新情報配列
	,&$inout_assingId // 追加/更新した代入値管理主キー値
){
	global	$db_model_ch;
	global	$objMTS;
	global	$objDBCA;
	global	$log_level;

	global $db_valautostup_user_id;
	global $strCurTableVarsAss;
	global $strJnlTableVarsAss;
	global $strSeqOfCurTableVarsAss;
	global $strSeqOfJnlTableVarsAss;
	global $arrayConfigOfVarAss;
	global $arrayValueTmplOfVarAss;

	$strCurTable = $strCurTableVarsAss;
	$strJnlTable = $strJnlTableVarsAss;

	$arrayConfig = $arrayConfigOfVarAss;
	$arrayValue = $arrayValueTmplOfVarAss;

	$strSeqOfCurTable = $strSeqOfCurTableVarsAss;
	$strSeqOfJnlTable = $strSeqOfJnlTableVarsAss;

	$assign_bind  = false;
	if(strlen($in_varsAssignList['ASSIGN_SEQ']) != 0){
		$assign_where = "ASSIGN_SEQ = :ASSIGN_SEQ";
		$assign_bind  = true;
	}
	else{
		$assign_where = "ASSIGN_SEQ IS NULL";
	}

	$temp_array = array
	  (
		  			'WHERE' =>
		 " 			   OPERATION_NO_UAPK    = :OPERATION_NO_UAPK "
		." 			AND"
		." 			   PATTERN_ID           = :PATTERN_ID"
		." 			AND"
		." 			   SYSTEM_ID            = :SYSTEM_ID"
		." 			AND"
		." 			   VARS_LINK_ID         = :VARS_LINK_ID"
		." 			AND"
		." 			   NESTEDMEM_COL_CMB_ID = :NESTEDMEM_COL_CMB_ID"
		." 			AND"
		." 			   DISUSE_FLAG          = '0'"
		." 			AND"
		." 			" .$assign_where
	  );

	$retArray = makeSQLForUtnTableUpdate
	(
		 $db_model_ch
		,"SELECT FOR UPDATE"
		,"VARS_ASSIGN_ID"
		,$strCurTable
		,$strJnlTable
		,$arrayConfig
		,$arrayValue
		,$temp_array
	)
	;
	$sqlUtnBody = $retArray[1];

	/* QueryのBind内容 */
	$bind_array                         = array();
	$bind_array['OPERATION_NO_UAPK']    = $in_varsAssignList['OPERATION_NO_UAPK'];
	$bind_array['PATTERN_ID']           = $in_varsAssignList['PATTERN_ID'];
	$bind_array['SYSTEM_ID']            = $in_varsAssignList['SYSTEM_ID'];
	$bind_array['VARS_LINK_ID']         = $in_varsAssignList['VARS_LINK_ID'];
	$bind_array['NESTEDMEM_COL_CMB_ID'] = $in_varsAssignList['NESTEDMEM_COL_CMB_ID'];

	if($assign_bind === true){
		$bind_array['ASSIGN_SEQ'] = $in_varsAssignList['ASSIGN_SEQ'];
	}

	$arrayUtnBind = $bind_array;

	$objQueryUtn = recordSelect($sqlUtnBody, $arrayUtnBind);
	if($objQueryUtn == null){
		return false;
	}

	/* fetch行数を取得 */
	$count = $objQueryUtn->effectedRowCount();
	$row = $objQueryUtn->resultFetch();
	unset($objQueryUtn);

	/* ログ出力用の'ASSIGN_SEQ'の定義 */
	$message_varsAssign = array();
	if($assign_bind === true){
		$message_varsAssign['ASSIGN_SEQ'] = $in_varsAssignList['ASSIGN_SEQ'];
	}
	else{
		$message_varsAssign['ASSIGN_SEQ'] = 'NONE' ;
	}

	if($count == 0){
		/* 廃止レコードの復活または新規レコード追加する。 */
		return addStg2ArrayVarsAssign($in_varsAssignList, $inout_assingId);
	}
	else{
		/* 廃止なので復活する */
		$action = "UPDATE";
		$tgt_row = $row;

		/* 生存する代入値管理主キー値を退避 */
		$inout_assingId = $tgt_row['VARS_ASSIGN_ID'];

		/* 具体値が変更になっているか判定する */
		if($row['VARS_ENTRY'] == $in_varsAssignList['VARS_ENTRY']){
			/* トレースメッセージ */
			if($log_level === "DEBUG"){
				/* [処理]代入値管理 更新不要 OPERATION_ID:｛｝ PATTERN_ID:｛｝ SYSTEM_ID:｛｝ VARS_LINK_ID:｛｝ NESTEDMEM_COL_CMB_ID:｛｝ ASSIGN_SEQ:｛｝ */
				$traceMsg = '[Process] Update not required substitution value list is not required (OPERATION_ID:' . $in_varsAssignList['OPERATION_NO_UAPK'] . ' PATTERN_ID:' . $in_varsAssignList['PATTERN_ID'] . ' SYSTEM_ID:' . $in_varsAssignList['SYSTEM_ID'] . ' VARS_LINK_ID:' . $in_varsAssignList['VARS_LINK_ID'] . ' NESTEDMEM_COL_CMB_ID:' . $in_varsAssignList['NESTEDMEM_COL_CMB_ID'] . ' ASSIGN_SEQ:' . $message_varsAssign['ASSIGN_SEQ'] . ')' ; // ITAANSIBLEH-STD-70050
				LocalLogPrint(basename(__FILE__),__LINE__,$traceMsg);
			}

			return true ; //同一みなので処理終了
		}

		/* 最終更新者が自分でない場合、更新処理はスキップする */
		if($row['LAST_UPDATE_USER'] != $db_valautostup_user_id){
			/* トレースメッセージ */
			if($log_level === "DEBUG"){
				/* [処理]代入値管理 最終更新者が自分でないので更新スキップ OPERATION_ID:｛｝ PATTERN_ID:｛｝ SYSTEM_ID:｛｝ VARS_LINK_ID:｛｝ NESTEDMEM_COL_CMB_ID:｛｝ ASSIGN_SEQ:｛｝ */
				$traceMsg = '[Process] Skip update of the substitution value list because last update was not done by the person himself (OPERATION_ID:' . $in_varsAssignList['OPERATION_NO_UAPK'] . ' PATTERN_ID:' . $in_varsAssignList['PATTERN_ID'] . ' SYSTEM_ID:' . $in_varsAssignList['SYSTEM_ID'] . ' VARS_LINK_ID:' . $in_varsAssignList['VARS_LINK_ID'] . ' NESTEDMEM_COL_CMB_ID:' . $in_varsAssignList['NESTEDMEM_COL_CMB_ID'] . ' ASSIGN_SEQ:' . $message_varsAssign['ASSIGN_SEQ'] . ')' ; // ITAANSIBLEH-STD-70051
				LocalLogPrint(basename(__FILE__),__LINE__,$traceMsg);
			}

			return true ; //更新処理はスキップ
		}

		/* トレースメッセージ */
		if($log_level === "DEBUG") {
			/* [処理]代入値管理 更新 OPERATION_ID:｛｝ PATTERN_ID:｛｝ SYSTEM_ID:｛｝ VARS_LINK_ID:｛｝ NESTEDMEM_COL_CMB_ID:｛｝ ASSIGN_SEQ:｛｝ */
			$traceMsg = '[Process] Update substitution value list (OPERATION_ID:' . $in_varsAssignList['OPERATION_NO_UAPK'] . ' PATTERN_ID:' . $in_varsAssignList['PATTERN_ID'] . ' SYSTEM_ID:' . $in_varsAssignList['SYSTEM_ID'] . ' VARS_LINK_ID:' . $in_varsAssignList['VARS_LINK_ID'] . ' NESTEDMEM_COL_CMB_ID:' . $in_varsAssignList['NESTEDMEM_COL_CMB_ID'] . ' ASSIGN_SEQ:' . $message_varsAssign['ASSIGN_SEQ'] . ')' ; // ITAANSIBLEH-STD-70049
			LocalLogPrint(basename(__FILE__),__LINE__,$traceMsg);
		}
	}

	/* ロール管理ジャーナルに登録する情報設定 */
	$seqValueOfJnlTable = getAndLockSeq($strSeqOfJnlTable);
	if($seqValueOfJnlTable == -1){
		return false;
	}

	$tgt_row['JOURNAL_SEQ_NO']   = $seqValueOfJnlTable;
	$tgt_row['VARS_ENTRY']       = $in_varsAssignList['VARS_ENTRY'];
	$tgt_row['DISUSE_FLAG']      = "0";
	$tgt_row['LAST_UPDATE_USER'] = $db_valautostup_user_id;

	$temp_array = array();
	$retArray = makeSQLForUtnTableUpdate
	  (
		 $db_model_ch
		,$action
		,"VARS_ASSIGN_ID"
		,$strCurTable
		,$strJnlTable
		,$arrayConfig
		,$tgt_row
		,$temp_array
	  );

	$sqlUtnBody = $retArray[1];
	$arrayUtnBind = $retArray[2];

	$sqlJnlBody = $retArray[3];
	$arrayJnlBind = $retArray[4];

	if(!recordUpdate($sqlUtnBody, $arrayUtnBind, $sqlJnlBody, $arrayJnlBind)){
		return false;
	}

	return true;
}
/* < END > F0010：代入値管理（多段配列変数）を更新する-------------------------------------------------------------------------------------- */

/* <START> F0011：代入値管理から不要なレコードを廃止---------------------------------------------------------------------------------------- */
function deleteVarsAssign($in_assignIdList) // 登録が必要な代入値管理の主キーリスト
{
	global	$db_model_ch;
	global	$objMTS;
	global	$objDBCA;
	global	$log_level;

	global $db_valautostup_user_id;
	global $strCurTableVarsAss;
	global $strJnlTableVarsAss;
	global $strSeqOfCurTableVarsAss;
	global $strSeqOfJnlTableVarsAss;
	global $arrayConfigOfVarAss;
	global $arrayValueTmplOfVarAss;

	$strCurTable = $strCurTableVarsAss;
	$strJnlTable = $strJnlTableVarsAss;

	$arrayConfig = $arrayConfigOfVarAss;
	$arrayValue = $arrayValueTmplOfVarAss;

	$strSeqOfCurTable = $strSeqOfCurTableVarsAss;
	$strSeqOfJnlTable = $strSeqOfJnlTableVarsAss;

	$strPkey = "VARS_ASSIGN_ID" ;

	$temp_array = array('WHERE' => "DISUSE_FLAG = '0' ");

	$retArray = makeSQLForUtnTableUpdate
	(
		 $db_model_ch
		,"SELECT FOR UPDATE"
		,$strPkey
		,$strCurTable
		,$strJnlTable
		,$arrayConfig
		,$arrayValue
		,$temp_array
	);

	$sqlUtnBody = $retArray[1];
	$arrayUtnBind = $retArray[2];

	$objQueryUtn_sel = recordSelect($sqlUtnBody, $arrayUtnBind);
	if($objQueryUtn_sel == null){

		return false;
	}

	/* fetch行数を取得 */
	while($tgt_row = $objQueryUtn_sel->resultFetch()) {

		/* メニューグループIDとメニューIDが登録されているか判定 （※登録されている場合はなにもしない） */
		if(@strlen($in_assignIdList[$tgt_row['VARS_ASSIGN_ID']]) != 0){ continue ; }

		/* 最終更新者が自分でない場合、廃止処理はスキップする */
		if($tgt_row["LAST_UPDATE_USER"] != $db_valautostup_user_id){
			/* トレースメッセージ */
			if($log_level === "DEBUG") {
				/* [処理]代入値管理 最終更新者が自分でないので廃止スキップ VARS_ASSIGN_ID：｛｝ */
				$traceMsg = '[Process] Skip discard of the substitution value list because last update was not done by the person himself VARS_ASSIGN_ID:' . $tgt_row['VARS_ASSIGN_ID'] . ')' ; // ITAANSIBLEH-STD-70038
				LocalLogPrint(basename(__FILE__),__LINE__,$traceMsg);
			}

			continue ; // 更新処理はスキップ
		}

		// 登録されていない場合は廃止レコードにする。

		/* トレースメッセージ */
		if($log_level === "DEBUG") {
			/* [処理]代入値管理 廃止 VARS_ASSIGN_ID：｛｝ */
			$traceMsg = '[Process] Discard substitution value list (VARS_ASSIGN_ID:' . $tgt_row['VARS_ASSIGN_ID'] . ')' ; // ITAANSIBLEH-STD-70031
			LocalLogPrint(basename(__FILE__),__LINE__,$traceMsg);
		}

		/* ロール管理ジャーナルに登録する情報設定 */
		$seqValueOfJnlTable = getAndLockSeq($strSeqOfJnlTable);
		if($seqValueOfJnlTable == -1) {

			return false;
		}
		$tgt_row['JOURNAL_SEQ_NO'] = $seqValueOfJnlTable ;
		$tgt_row['DISUSE_FLAG'] = '1' ;
		$tgt_row['LAST_UPDATE_USER'] = $db_valautostup_user_id ;

		$temp_array = array();
		$retArray = makeSQLForUtnTableUpdate
		(
			 $db_model_ch
			,"UPDATE"
			,$strPkey
			,$strCurTable
			,$strJnlTable
			,$arrayConfig
			,$tgt_row
			,$temp_array
		);

		$sqlUtnBody   = $retArray[1];
		$arrayUtnBind = $retArray[2];

		$sqlJnlBody   = $retArray[3];
		$arrayJnlBind = $retArray[4];

		if(!recordUpdate
			(
				 $sqlUtnBody
				,$arrayUtnBind
				,$sqlJnlBody
				,$arrayJnlBind
			)
		){
			unset($objQueryUtn_sel);

			return false;
		}
	}
	unset($objQueryUtn_sel);

	return true;
}
/* < END > F0011：代入値管理から不要なレコードを廃止---------------------------------------------------------------------------------------- */

/* <START> F0012：作業対象ホストを更新する-------------------------------------------------------------------------------------------------- */
function addStg1PhoLink
(
	 $in_phoLinkData // 作業対象ホスト更新情報配列
	,&$inout_phoLinkId // 追加/更新した作業対象ホスト主キー値
){
	global	$db_model_ch;
	global	$objMTS;
	global	$objDBCA;
	global	$log_level;

	global $db_valautostup_user_id;
	global $strCurTablePhoLnk;
	global $strJnlTablePhoLnk;
	global $strSeqOfCurTablePhoLnk;
	global $strSeqOfJnlTablePhoLnk;
	global $arrayConfigOfPhoLnk;
	global $arrayValueTmplOfPhoLnk;

	$strCurTable = $strCurTablePhoLnk;
	$strJnlTable = $strJnlTablePhoLnk;

	$arrayConfig = $arrayConfigOfPhoLnk;
	$arrayValue = $arrayValueTmplOfPhoLnk;

	$strSeqOfCurTable = $strSeqOfCurTablePhoLnk;
	$strSeqOfJnlTable = $strSeqOfJnlTablePhoLnk;

	$temp_array = array
	  (
		  			'WHERE' =>
		 " 			   OPERATION_NO_UAPK = :OPERATION_NO_UAPK"
		." 			AND"
		." 			   PATTERN_ID        = :PATTERN_ID"
		." 			AND"
		." 			   SYSTEM_ID         = :SYSTEM_ID"
		." 			AND"
		." 			   DISUSE_FLAG       = '0'"
	  );

	$retArray = makeSQLForUtnTableUpdate
	(
		 $db_model_ch
		,"SELECT FOR UPDATE"
		,"PHO_LINK_ID"
		,$strCurTable
		,$strJnlTable
		,$arrayConfig
		,$arrayValue
		,$temp_array
	);

	$sqlUtnBody = $retArray[1];

	$bind_array = array
	(
		'OPERATION_NO_UAPK' => $in_phoLinkData['OPERATION_NO_UAPK'],
		'PATTERN_ID'        => $in_phoLinkData['PATTERN_ID'],
		'SYSTEM_ID'         => $in_phoLinkData['SYSTEM_ID']
	);

	$arrayUtnBind = $bind_array;
	$objQueryUtn  = recordSelect( $sqlUtnBody , $arrayUtnBind );
	if($objQueryUtn == null){
		return false;
	}

	/* fetch行数を取得 */
	$count = $objQueryUtn -> effectedRowCount();
	$row   = $objQueryUtn -> resultFetch();
	unset($objQueryUtn);

	if($count == 0){
		return addStg2PhoLink($in_phoLinkData, $inout_phoLinkId);
	}
	else{
		/* 更新対象の作業対象ホスト管理主キー値を退避 */
		$inout_phoLinkId = $row['PHO_LINK_ID'];

		return true ; //同一なので処理終了
	}

}
/* < END > F0012：作業対象ホストを更新する-------------------------------------------------------------------------------------------------- */

/* <START> F0013：作業管理対象ホスト管理から不要なレコードを廃止---------------------------------------------------------------------------- */
function deletePhoLink
(
	$in_usePhoLinkIdList // 登録が必要な作業管理対象ホストの主キーリスト
){
	global	$db_model_ch;
	global	$objMTS;
	global	$objDBCA;
	global	$log_level;

	global $db_valautostup_user_id;
	global $strCurTablePhoLnk;
	global $strJnlTablePhoLnk;
	global $strSeqOfCurTablePhoLnk;
	global $strSeqOfJnlTablePhoLnk;
	global $arrayConfigOfPhoLnk;
	global $arrayValueTmplOfPhoLnk;

	$strCurTable = $strCurTablePhoLnk;
	$strJnlTable = $strJnlTablePhoLnk;

	$arrayConfig = $arrayConfigOfPhoLnk;
	$arrayValue = $arrayValueTmplOfPhoLnk;

	$strSeqOfCurTable = $strSeqOfCurTablePhoLnk;
	$strSeqOfJnlTable = $strSeqOfJnlTablePhoLnk;

	$strPkey = "PHO_LINK_ID" ;

	$temp_array = array('WHERE'=>"DISUSE_FLAG = '0' ") ;

	$retArray = makeSQLForUtnTableUpdate
	(
		 $db_model_ch
		,"SELECT FOR UPDATE"
		,$strPkey
		,$strCurTable
		,$strJnlTable
		,$arrayConfig
		,$arrayValue
		,$temp_array
	);

	$sqlUtnBody = $retArray[1];
	$arrayUtnBind = $retArray[2];

	$objQueryUtn_sel = recordSelect($sqlUtnBody, $arrayUtnBind);
	if($objQueryUtn_sel == null) {

		return false;
	}

	/* fetch行数を取得 */
	while($tgt_row = $objQueryUtn_sel->resultFetch()){
		/* 追加・更新した主キーリストに登録されているか判定（※登録されている場合には何もしない） */
		if(@strlen($in_usePhoLinkIdList[$tgt_row['PHO_LINK_ID']]) !== 0) { continue ; }

		/* トレースメッセージ */
		if($log_level === "DEBUG") {
			/* [処理]作業対象ホスト 廃止 PHO_LINK_ID:｛｝ */
			$traceMsg = '[Process] Discard target host (PHO_LINK_ID:' . $tgt_row['PHO_LINK_ID'] . ')' ; // ITAANSIBLEH-STD-70034
			LocalLogPrint(basename(__FILE__),__LINE__,$traceMsg);
		}

		/* 最終更新者が自分でない場合、廃止処理はスキップする */
		if($tgt_row['LAST_UPDATE_USER'] != $db_valautostup_user_id) {
			/* トレースメッセージ */
			if($log_level === "DEBUG") {
				/* [処理]作業対象ホスト 最終更新者が自分でないので廃止スキップ PHO_LINK_ID:｛｝ */
				$traceMsg = '[Process] Skip discard of the target host because last update was not done by person himself (PHO_LINK_ID:' . $tgt_row['PHO_LINK_ID'] . ')' ; // ITAANSIBLEH-STD-70039
				LocalLogPrint(basename(__FILE__),__LINE__,$traceMsg);
			}
			continue ; // 更新処理はスキップ
		}

		/* 追加・更新した主キーリストに登録されていない場合は廃止レコードにする */
		$seqValueOfJnlTable = getAndLockSeq($strSeqOfJnlTable);
		if($seqValueOfJnlTable == -1){

			return false;
		}
		$tgt_row['JOURNAL_SEQ_NO'] = $seqValueOfJnlTable;
		$tgt_row['DISUSE_FLAG'] = "1";
		$tgt_row['LAST_UPDATE_USER'] = $db_valautostup_user_id;

		$temp_array = array();
		$retArray = makeSQLForUtnTableUpdate
		(
			 $db_model_ch
			,"UPDATE"
			,$strPkey
			,$strCurTable
			,$strJnlTable
			,$arrayConfig
			,$tgt_row
			,$temp_array
		);

		$sqlUtnBody = $retArray[1];
		$arrayUtnBind = $retArray[2];

		$sqlJnlBody = $retArray[3];
		$arrayJnlBind = $retArray[4];

		if(!recordUpdate($sqlUtnBody, $arrayUtnBind, $sqlJnlBody, $arrayJnlBind)) {
			unset($objQueryUtn_sel);

			return false;
		}
	}
	unset($objQueryUtn_sel);

	return true;
}
/* < END > F0013：作業管理対象ホスト管理から不要なレコードを廃止---------------------------------------------------------------------------- */


/* <START> F0014：代入値管理（一般変数・複数具体値変数）の廃止レコードの復活またき新規レコード追加------------------------------------------ */
function addStg2StdListVarsAssign
(
	 $in_varsAssignList // 代入値管理更新情報配列
	,&$inout_assingId // 追加/更新した代入値管理主キー値
){
	global	$db_model_ch;
	global	$objMTS;
	global	$objDBCA;
	global	$log_level;

	global $db_valautostup_user_id;
	global $strCurTableVarsAss;
	global $strJnlTableVarsAss;
	global $strSeqOfCurTableVarsAss;
	global $strSeqOfJnlTableVarsAss;
	global $arrayConfigOfVarAss;
	global $arrayValueTmplOfVarAss;

	$strCurTable = $strCurTableVarsAss;
	$strJnlTable = $strJnlTableVarsAss;

	$arrayConfig = $arrayConfigOfVarAss;
	$arrayValue  = $arrayValueTmplOfVarAss;

	$strSeqOfCurTable = $strSeqOfCurTableVarsAss;
	$strSeqOfJnlTable = $strSeqOfJnlTableVarsAss;

	$assign_bind  = false;
	if(strlen($in_varsAssignList['ASSIGN_SEQ']) != 0){
		$assign_where = "ASSIGN_SEQ = :ASSIGN_SEQ";
		$assign_bind  = true;
	}
	else{
		$assign_where = "ASSIGN_SEQ IS NULL";
	}

	$temp_array = array
	  (
		  			'WHERE' =>
		 " 			   OPERATION_NO_UAPK = :OPERATION_NO_UAPK"
		." 			AND"
		." 			   PATTERN_ID        = :PATTERN_ID"
		." 			AND"
		." 			   SYSTEM_ID         = :SYSTEM_ID"
		." 			AND"
		." 			   VARS_LINK_ID      = :VARS_LINK_ID"
		." 			AND"
		." 			   DISUSE_FLAG       = '1'"
		." 			AND"
		." 			" .$assign_where
	  )
	;
	$retArray = makeSQLForUtnTableUpdate
	  (
		 $db_model_ch
		,"SELECT FOR UPDATE"
		,"VARS_ASSIGN_ID"
		,$strCurTable
		,$strJnlTable
		,$arrayConfig
		,$arrayValue
		,$temp_array
	  )
	;
	$sqlUtnBody = $retArray[1];

	$bind_array = array
	  (
		 'OPERATION_NO_UAPK' => $in_varsAssignList['OPERATION_NO_UAPK']
		,'PATTERN_ID'        => $in_varsAssignList['PATTERN_ID']
		,'SYSTEM_ID'         => $in_varsAssignList['SYSTEM_ID']
		,'VARS_LINK_ID'      => $in_varsAssignList['VARS_LINK_ID']
	  );

	if($assign_bind == true){
		$bind_array['ASSIGN_SEQ'] = $in_varsAssignList['ASSIGN_SEQ'];
	}
	$arrayUtnBind = $bind_array;

	$objQueryUtn = recordSelect($sqlUtnBody , $arrayUtnBind);

	if($objQueryUtn == null){
		return false;
	}

	// fetch行数を取得
	$count = $objQueryUtn -> effectedRowCount();
	$row   = $objQueryUtn -> resultFetch();
	unset($objQueryUtn);

	if($count == 0){
		$action  = "INSERT";
		$tgt_row = $arrayValue;

		// トレースメッセージ
		if($log_level === "DEBUG"){
			/* [処理]代入値管理 追加 OPERATION_ID:｛｝ PATTERN_ID:｛｝ SYSTEM_ID:｛｝ VARS_LINK_ID:｛｝ ASSIGN_SEQ:｛｝ */
			$traceMsg
			 = '[Process] Assignment value management addition'
			  .' OPERATION_ID:' . $in_varsAssignList['OPERATION_NO_UAPK']
			  .' PATTERN_ID:'   . $in_varsAssignList['PATTERN_ID']
			  .' SYSTEM_ID:'    . $in_varsAssignList['SYSTEM_ID']
			  .' VARS_LINK_ID:' . $in_varsAssignList['VARS_LINK_ID']
			  .' ASSIGN_SEQ:'   . $in_varsAssignList['ASSIGN_SEQ']
			;
			LocalLogPrint(basename(__FILE__),__LINE__,$traceMsg);
		 }
	}
	else{
		// 廃止レコードがあるので復活する。
		$action = "UPDATE";
		$tgt_row = $row;

		// 更新対象の代入値管理主キー値を退避
		$inout_assingId = $tgt_row['VARS_ASSIGN_ID'];

		// トレースメッセージ
		if($log_level === "DEBUG"){
			/* [処理]代入値管理 復活 OPERATION_ID:｛｝ PATTERN_ID:｛｝ SYSTEM_ID:｛｝ VARS_LINK_ID:｛｝ ASSIGN_SEQ:｛｝ */
			$traceMsg
			 = '[Process] Assignment value management revival'
			  .' OPERATION_ID:' . $in_varsAssignList['OPERATION_NO_UAPK']
			  .' PATTERN_ID:'   . $in_varsAssignList['PATTERN_ID']
			  .' SYSTEM_ID:'    . $in_varsAssignList['SYSTEM_ID']
			  .' VARS_LINK_ID:' . $in_varsAssignList['VARS_LINK_ID']
			  .' ASSIGN_SEQ:'   . $in_varsAssignList['ASSIGN_SEQ']
			;
			LocalLogPrint(basename(__FILE__),__LINE__,$traceMsg);
		}
	}

	if($action == "INSERT"){
		$seqValueOfCurTable = getAndLockSeq($strSeqOfCurTable);

		if($seqValueOfCurTable == -1){
			return false;
		}

		/* 登録する情報設定 */
		$tgt_row['VARS_ASSIGN_ID']    = $seqValueOfCurTable;
		$tgt_row['OPERATION_NO_UAPK'] = $in_varsAssignList['OPERATION_NO_UAPK'];
		$tgt_row['PATTERN_ID']        = $in_varsAssignList['PATTERN_ID'];
		$tgt_row['SYSTEM_ID']         = $in_varsAssignList['SYSTEM_ID'];
		$tgt_row['VARS_LINK_ID']      = $in_varsAssignList['VARS_LINK_ID'];
		$tgt_row['ASSIGN_SEQ']        = $in_varsAssignList['ASSIGN_SEQ'];

		/* 追加する代入値管理主キー値を退避 */
		$inout_assingId = $tgt_row['VARS_ASSIGN_ID'];
	}

	/* ロール管理ジャーナルに登録する情報設定 */
	$seqValueOfJnlTable = getAndLockSeq($strSeqOfJnlTable);
	if($seqValueOfJnlTable == -1){
		return false;
	}

	$tgt_row['JOURNAL_SEQ_NO']         = $seqValueOfJnlTable;
	$tgt_row['VARS_ENTRY']             = $in_varsAssignList['VARS_ENTRY'];
	$tgt_row['NESTEDMEM_COL_CMB_ID'] = "";
	$tgt_row['DISUSE_FLAG']            = "0";
	$tgt_row['LAST_UPDATE_USER']       = $db_valautostup_user_id;

	$temp_array = array();

	$retArray = makeSQLForUtnTableUpdate
	  (
		 $db_model_ch
		,$action
		,"VARS_ASSIGN_ID"
		,$strCurTable
		,$strJnlTable
		,$arrayConfig
		,$tgt_row
		,$temp_array
	  )
	;
	$sqlUtnBody   = $retArray[1];
	$arrayUtnBind = $retArray[2];

	$sqlJnlBody   = $retArray[3];
	$arrayJnlBind = $retArray[4];

	if(!recordUpdate($sqlUtnBody, $arrayUtnBind, $sqlJnlBody, $arrayJnlBind)){
		return false;
	}

	return true;
}
/* < END > F0014：代入値管理（一般変数・複数具体値変数）の廃止レコードの復活またき新規レコード追加------------------------------------------ */

/* <START> F0015：代入値管理（多段変数配列変数）の廃止レコードの復活またき新規レコード追加-------------------------------------------------- */
function addStg2ArrayVarsAssign
(
	 $in_varsAssignList // 代入値管理更新情報配列
	,&$inout_assingId // 追加/更新した代入値管理主キー値
){
	global	$db_model_ch;
	global	$objMTS;
	global	$objDBCA;
	global	$log_level;

	global $db_valautostup_user_id;
	global $strCurTableVarsAss;
	global $strJnlTableVarsAss;
	global $strSeqOfCurTableVarsAss;
	global $strSeqOfJnlTableVarsAss;
	global $arrayConfigOfVarAss;
	global $arrayValueTmplOfVarAss;

	$strCurTable = $strCurTableVarsAss;
	$strJnlTable = $strJnlTableVarsAss;

	$arrayConfig = $arrayConfigOfVarAss;
	$arrayValue  = $arrayValueTmplOfVarAss;

	$strSeqOfCurTable = $strSeqOfCurTableVarsAss;
	$strSeqOfJnlTable = $strSeqOfJnlTableVarsAss;

	$assign_bind  = false;
	if(strlen($in_varsAssignList['ASSIGN_SEQ']) != 0){
		$assign_where = "ASSIGN_SEQ = :ASSIGN_SEQ";
		$assign_bind  = true;
	}
	else{
		$assign_where = "ASSIGN_SEQ IS NULL";
	}

	$temp_array = array
	  (
		  			'WHERE' =>
		 " 			   OPERATION_NO_UAPK      = :OPERATION_NO_UAPK"
		." 			AND"
		." 			   PATTERN_ID             = :PATTERN_ID"
		." 			AND"
		." 			   SYSTEM_ID              = :SYSTEM_ID"
		." 			AND"
		." 			   VARS_LINK_ID           = :VARS_LINK_ID"
		." 			AND"
		." 			   NESTEDMEM_COL_CMB_ID   = :NESTEDMEM_COL_CMB_ID"
		." 			AND"
		." 			   DISUSE_FLAG            = '1'"
		." 			AND"
		." 			" .$assign_where
	  )
	;
	$retArray = makeSQLForUtnTableUpdate
	  (
		 $db_model_ch
		,"SELECT FOR UPDATE"
		,"VARS_ASSIGN_ID"
		,$strCurTable
		,$strJnlTable
		,$arrayConfig
		,$arrayValue
		,$temp_array
	  )
	;
	$sqlUtnBody = $retArray[1];

	$bind_array                           = array();
	$bind_array['OPERATION_NO_UAPK']      = $in_varsAssignList['OPERATION_NO_UAPK'];
	$bind_array['PATTERN_ID']             = $in_varsAssignList['PATTERN_ID'];
	$bind_array['SYSTEM_ID']              = $in_varsAssignList['SYSTEM_ID'];
	$bind_array['VARS_LINK_ID']           = $in_varsAssignList['VARS_LINK_ID'];
	$bind_array['NESTEDMEM_COL_CMB_ID']   = $in_varsAssignList['NESTEDMEM_COL_CMB_ID'];

	if($assign_bind === true){
		$bind_array['ASSIGN_SEQ'] = $in_varsAssignList['ASSIGN_SEQ'];
	}
	$arrayUtnBind = $bind_array;

	$objQueryUtn = recordSelect($sqlUtnBody, $arrayUtnBind);
	if($objQueryUtn == null){
		return false;
	}

	// fetch行数を取得
	$count = $objQueryUtn->effectedRowCount();
	$row = $objQueryUtn->resultFetch();
	unset($objQueryUtn);

	if($count == 0) {
		 $action  = "INSERT";
		 $tgt_row = $arrayValue;

		// トレースメッセージ
		if($log_level === "DEBUG"){
			/* [処理]代入値管理 追加 OPERATION_ID:｛｝ PATTERN_ID:｛｝ SYSTEM_ID:｛｝ VARS_LINK_ID:｛｝ NESTEDMEM_COL_CMB_ID:｛｝ ASSIGN_SEQ:｛｝ */
			$traceMsg
			 = '[Process] Assignment value management addition'
			  .' OPERATION_ID:'           . $in_varsAssignList['OPERATION_NO_UAPK']
			  .' PATTERN_ID:'             . $in_varsAssignList['PATTERN_ID']
			  .' SYSTEM_ID:'              . $in_varsAssignList['SYSTEM_ID']
			  .' VARS_LINK_ID:'           . $in_varsAssignList['VARS_LINK_ID']
			  .' NESTEDMEM_COL_CMB_ID:'   . $in_varsAssignList['NESTEDMEM_COL_CMB_ID']
			  .' ASSIGN_SEQ:'             . $in_varsAssignList['ASSIGN_SEQ']
			;
			LocalLogPrint(basename(__FILE__),__LINE__,$traceMsg);
		}
	}
	else{
		// 廃止レコードがあるので復活する。
		$action = "UPDATE";
		$tgt_row = $row;

		// 生存する代入値管理主キー値を退避
		$inout_assingId = $tgt_row['VARS_ASSIGN_ID'];

		// トレースメッセージ
		if($log_level === "DEBUG"){
			/* [処理]代入値管理 復活 OPERATION_ID:｛｝ PATTERN_ID:｛｝ SYSTEM_ID:｛｝ VARS_LINK_ID:｛｝ NESTEDMEM_COL_CMB_ID:｛｝ ASSIGN_SEQ:｛｝ */
			$traceMsg
			 = '[Processing] Assignment value management revival'
			  .' OPERATION_ID:'           . in_varsAssignList['OPERATION_NO_UAPK']
			  .' PATTERN_ID:'             . $in_varsAssignList['PATTERN_ID']
			  .' SYSTEM_ID:'              . $in_varsAssignList['SYSTEM_ID']
			  .' VARS_LINK_ID:'           . $in_varsAssignList['VARS_LINK_ID']
			  .' NESTEDMEM_COL_CMB_ID:'   . $in_varsAssignList['NESTEDMEM_COL_CMB_ID']
			  .' ASSIGN_SEQ:'             . $in_varsAssignList['ASSIGN_SEQ']
			;
			LocalLogPrint(basename(__FILE__),__LINE__,$traceMsg);
		}
	}

	if($action == "INSERT") {

		$seqValueOfCurTable = getAndLockSeq($strSeqOfCurTable);
		if($seqValueOfCurTable == -1){
			return false;
		}

		// 登録する情報設定
		$tgt_row['VARS_ASSIGN_ID']         = $seqValueOfCurTable;
		$tgt_row['OPERATION_NO_UAPK']      = $in_varsAssignList['OPERATION_NO_UAPK'];
		$tgt_row['PATTERN_ID']             = $in_varsAssignList['PATTERN_ID'];
		$tgt_row['SYSTEM_ID']              = $in_varsAssignList['SYSTEM_ID'];
		$tgt_row['VARS_LINK_ID']           = $in_varsAssignList['VARS_LINK_ID'];
		$tgt_row['ASSIGN_SEQ']             = $in_varsAssignList['ASSIGN_SEQ'];
		$tgt_row['NESTEDMEM_COL_CMB_ID']   = $in_varsAssignList['NESTEDMEM_COL_CMB_ID'];

		// 追加する代入値管理主キー値を退避
		$inout_assingId = $tgt_row['VARS_ASSIGN_ID'];
	}

	// ロール管理ジャーナルに登録する情報設定
	$seqValueOfJnlTable = getAndLockSeq($strSeqOfJnlTable);
	if($seqValueOfJnlTable == -1){
		return false;
	}

	$tgt_row['JOURNAL_SEQ_NO']   = $seqValueOfJnlTable;
	$tgt_row['VARS_ENTRY']       = $in_varsAssignList['VARS_ENTRY'];
	$tgt_row['DISUSE_FLAG']      = "0";
	$tgt_row['LAST_UPDATE_USER'] = $db_valautostup_user_id;

	$temp_array = array();

	$retArray = makeSQLForUtnTableUpdate
	  (
		 $db_model_ch
		,$action
		,"VARS_ASSIGN_ID"
		,$strCurTable
		,$strJnlTable
		,$arrayConfig
		,$tgt_row
		,$temp_array
	  )
	;
	$sqlUtnBody   = $retArray[1];
	$arrayUtnBind = $retArray[2];

	$sqlJnlBody   = $retArray[3];
	$arrayJnlBind = $retArray[4];

	if(!recordUpdate($sqlUtnBody, $arrayUtnBind, $sqlJnlBody, $arrayJnlBind)){
		return false;
	}

	return true;
}
/* < END > F0015：代入値管理（多段変数配列変数）の廃止レコードの復活またき新規レコード追加-------------------------------------------------- */

/* <START> F0016：作業対象ホストの廃止レコードを復活または新規レコード追加------------------------------------------------------------------ */
function addStg2PhoLink
(
	 $in_phoLinkData // 作業対象ホスト更新情報配列
	,&$inout_phoLinkId // 追加/更新した作業対象ホスト主キー値
){
	global	$db_model_ch;
	global	$objMTS;
	global	$objDBCA;
	global	$log_level;

	global $db_valautostup_user_id;
	global $strCurTablePhoLnk;
	global $strJnlTablePhoLnk;
	global $strSeqOfCurTablePhoLnk;
	global $strSeqOfJnlTablePhoLnk;
	global $arrayConfigOfPhoLnk;
	global $arrayValueTmplOfPhoLnk;

	$strCurTable  = $strCurTablePhoLnk;
	$strJnlTable  = $strJnlTablePhoLnk;

	$arrayConfig  = $arrayConfigOfPhoLnk;
	$arrayValue   = $arrayValueTmplOfPhoLnk;

	$strSeqOfCurTable = $strSeqOfCurTablePhoLnk;
	$strSeqOfJnlTable = $strSeqOfJnlTablePhoLnk;

	$temp_array = array
	  (
		  			'WHERE' => 
		 " 			   OPERATION_NO_UAPK = :OPERATION_NO_UAPK"
		." 			AND"
		." 			   PATTERN_ID        = :PATTERN_ID"
		." 			AND"
		." 			   SYSTEM_ID         = :SYSTEM_ID"
		." 			AND"
		." 			   DISUSE_FLAG       = '1'"
	  );

	$retArray = makeSQLForUtnTableUpdate
	  (
		 $db_model_ch
		,"SELECT FOR UPDATE"
		,"PHO_LINK_ID"
		,$strCurTable
		,$strJnlTable
		,$arrayConfig
		,$arrayValue
		,$temp_array
	  )
	;
	$sqlUtnBody = $retArray[1];

	$bind_array = array
	  (
		 'OPERATION_NO_UAPK' => $in_phoLinkData['OPERATION_NO_UAPK']
		,'PATTERN_ID'        => $in_phoLinkData['PATTERN_ID']
		,'SYSTEM_ID'         => $in_phoLinkData['SYSTEM_ID']
	  )
	;
	$arrayUtnBind = $bind_array;

	$objQueryUtn = recordSelect($sqlUtnBody, $arrayUtnBind);
	if($objQueryUtn == null){
		return false;
	}

	// fetch行数を取得
	$count = $objQueryUtn->effectedRowCount();
	$row = $objQueryUtn->resultFetch();
	unset($objQueryUtn);

	if($count == 0){
		$action  = "INSERT";
		$tgt_row = $arrayValue;

		// トレースメッセージ
		if($log_level === "DEBUG"){
			/* [処理]作業対象ホスト 追加 OPERATION_ID:｛｝ PATTERN_ID:｛｝ SYSTEM_ID:｛｝ */
			$traceMsg
			 = '[Process] Add host to be operated'
			  .' OPERATION_ID:' . $in_phoLinkData['OPERATION_NO_UAPK']
			  .' PATTERN_ID:'   . $in_phoLinkData['PATTERN_ID']
			  .' SYSTEM_ID:'    . $in_phoLinkData['SYSTEM_ID']
			;
			LocalLogPrint(basename(__FILE__),__LINE__,$traceMsg);
		}
	}
	else{
		// 更新対象の作業対象ホスト管理主キー値を退避
		$inout_phoLinkId = $row['PHO_LINK_ID'];

		// 廃止なので復活する。
		$action = "UPDATE";
		$tgt_row = $row;

		// トレースメッセージ
		if($log_level === "DEBUG"){
			/* [処理]作業対象ホスト 復活 OPERATION_ID:｛｝ PATTERN_ID:｛｝ SYSTEM_ID:｛｝ */
			$traceMsg
			 = '[Process] Operation target host restoration'
			  .' OPERATION_ID:' . $in_phoLinkData['OPERATION_NO_UAPK']
			  .' PATTERN_ID:'   . $in_phoLinkData['PATTERN_ID']
			  .' SYSTEM_ID:'    . $in_phoLinkData['SYSTEM_ID']
			;
			LocalLogPrint(basename(__FILE__),__LINE__,$traceMsg);
		}
	}

	if($action == "INSERT"){
		$seqValueOfCurTable = getAndLockSeq($strSeqOfCurTable);
		if($seqValueOfCurTable == -1){
			return false;
		}

		/* 更新対象の作業対象ホスト管理主キー値を退避 */
		$inout_phoLinkId = $seqValueOfCurTable;

		/* 登録する情報設定 */
		$tgt_row['PHO_LINK_ID']       = $seqValueOfCurTable;
		$tgt_row['OPERATION_NO_UAPK'] = $in_phoLinkData['OPERATION_NO_UAPK'];
		$tgt_row['PATTERN_ID']        = $in_phoLinkData['PATTERN_ID'];
		$tgt_row['SYSTEM_ID']         = $in_phoLinkData['SYSTEM_ID'];
	}

	$tgt_row['DISUSE_FLAG']	   = "0";
	$tgt_row['LAST_UPDATE_USER']  = $db_valautostup_user_id;

	// ロール管理ジャーナルに登録する情報設定
	$seqValueOfJnlTable = getAndLockSeq($strSeqOfJnlTable);
	if($seqValueOfJnlTable == -1) {
		return false;
	}
	$tgt_row['JOURNAL_SEQ_NO']	   = $seqValueOfJnlTable;

	$temp_array = array();
	$retArray = makeSQLForUtnTableUpdate
	  (
		 $db_model_ch
		,$action
		,"PHO_LINK_ID"
		,$strCurTable
		,$strJnlTable
		,$arrayConfig
		,$tgt_row
		,$temp_array
	  )
	;
	$sqlUtnBody   = $retArray[1];
	$arrayUtnBind = $retArray[2];

	$sqlJnlBody   = $retArray[3];
	$arrayJnlBind = $retArray[4];

	if(!recordUpdate($sqlUtnBody, $arrayUtnBind, $sqlJnlBody, $arrayJnlBind)){
		return false;
	}

	return true;
}
/* < END > F0016：作業対象ホストの廃止レコードを復活または新規レコード追加------------------------------------------------------------------ */

/* <START> F0017：その他、ValidationのFunction---------------------------------------------------------------------------------------------- */
function validateValueTypeColValue($in_col_val,
                                   $in_null_data_handling_flg,
								   $in_menu_id,
								   $in_row_id,
								   $in_menu_title) {

	global	$objMTS;
	global	$log_level;

	/* 具体値が空白の場合 */
	if(strlen($in_col_val) == 0){
		// 具体値が空でも代入値管理NULLデータ連携が有効か判定する
        if(getNullDataHandlingID($in_null_data_handling_flg) != '1')
        {
             // トレースメッセージ
             if ( $log_level === 'DEBUG' ){
                 $FREE_LOG = $objMTS->getSomeMessage("ITAANSIBLEH-ERR-90056",
                                      array($lva_table_nameTOid_list[$in_table_name],$in_row_id,$in_menu_title));
                 LocalLogPrint(basename(__FILE__),__LINE__,$FREE_LOG);
             }
             return false;
        }
	}
	/* 具体値が1024バイト以上の場合 */
	if(strlen($in_col_val) > 1024){
		/* トレースメッセージ */
		if($log_level === "DEBUG") {
			/* 紐付メニュー（MENU_ID:｛｝ 項番:｛｝ 項目名:｛｝）の具体値が規定値(最大1024バイト)を超えています。このレコードを処理対象外とします */
			$traceMsg = 'Specific value of Associated menu has exceeded the prescribed value (maximum 1024 byte). This record will be out of scope of processing. (MENU_ID:' . $in_menu_id . ' Associated menu item No.:' . $in_row_id . ' Item name:' . $in_menu_title . ')' ; // ITAANSIBLEH-ERR-90057
			LocalLogPrint(basename(__FILE__),__LINE__,$traceMsg);
		}

		return false;
	}

	return true;
}

/* 具体値が空白の場合 */
function validateKeyTypeColValue
(
	 $in_col_val
	,$in_menu_id
	,$in_row_id
	,$in_menu_title
){
	global	$objMTS;
	global	$log_level;

	if(strlen($in_col_val) == 0) {
		/* トレースメッセージ */
		if($log_level === "DEBUG"){
			/* 紐付メニュー（MENU_ID:｛｝ 項番:｛｝ 項目名:｛｝）の具体値が空白 */
			$traceMsg = 'Specific value of associated menu is blank. (MENU_ID:' . $in_menu_id . ' Associated menu item No.:' . $in_row_id . ' Item name:' . $in_menu_title . ')' ; // ITAANSIBLEH-ERR-90058
			LocalLogPrint(basename(__FILE__),__LINE__,$traceMsg);
		}

		return false;
	}

	return true;
}

/* ExecuteしてFetch前のDBアクセスオブジェクトを返却 */
function recordSelect
(
	 $sqlUtnBody
	,$arrayUtnBind
){
	global	$objMTS;
	global	$objDBCA;

	$objQueryUtn = $objDBCA->sqlPrepare($sqlUtnBody);

	if($objQueryUtn->getStatus()===false){
		/* DBアクセス異常([FILE]｛｝[LINE]｛｝ */
		$msgstr = 'DB access error occurred. (file:' . basename(__FILE__) . ' line:' . __LINE__ . ')' ; // ITAANSIBLEH-ERR-80000
		LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

		$msgstr = $objQueryUtn->getLastError();
		LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

		$msgstr = $sqlUtnBody . "\n" . $arrayUtnBind;
		LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

		return null;
	}

	$errstr = $objQueryUtn->sqlBind($arrayUtnBind);
	if($errstr != ""){
		/* DBアクセス異常([FILE]｛｝[LINE]｛｝) */
		$msgstr = 'DB access error occurred. (file:' . basename(__FILE__) . ' line:' . __LINE__ . ')' ; // ITAANSIBLEH-ERR-80000
		LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

		$msgstr = $errstr;
		LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

		$msgstr = $sqlUtnBody . "\n" . $arrayUtnBind;
		LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

		return null;
	}

	$r = $objQueryUtn->sqlExecute();
	if(!$r){
		/* DBアクセス異常([FILE]｛｝[LINE]｛｝) */
		$msgstr = 'DB access error occurred. (file:' . basename(__FILE__) . ' line:' . __LINE__ . ')' ; // ITAANSIBLEH-ERR-80000
		LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

		$msgstr = $objQueryUtn->getLastError();
		LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

		$msgstr = $sqlUtnBody . "\n" . $arrayUtnBind;
		LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

		return null;
	}

	return $objQueryUtn;
}

/* シーケンスをロックして、採番した数値を返却 */
function getAndLockSeq($strSeqOfTable){
	/* シーケンスをロック */
	$retArray = getSequenceLockInTrz( $strSeqOfTable , "A_SEQUENCE" );
	if($retArray[1] != 0) {
		/* DBアクセス異常([FILE]｛｝[LINE]｛｝) */
		$msgstr = 'DB access error occurred. (file:' . basename(__FILE__) . ' line:' . __LINE__ . ')' ; // ITAANSIBLEH-ERR-80000
		LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

		return -1;
	}

	/* シーケンスを採番 */
	$retArray = getSequenceValueFromTable( $strSeqOfTable , "A_SEQUENCE" , false );
	if($retArray[1] != 0) {
		/* DBアクセス異常([FILE]｛｝[LINE]｛｝) */
		$msgstr = 'DB access error occurred. (file:' . basename(__FILE__) . ' line:' . __LINE__ . ')' ; // ITAANSIBLEH-ERR-80000
		LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

		return -1;
	}

	return $retArray[0];
}

/* 更新対象テーブルを更新し、履歴テーブルにレコード追加 */
function recordUpdate
(
	 $sqlUtnBody
	,$arrayUtnBind
	,$sqlJnlBody
	,$arrayJnlBind
){
	global $objMTS;
	global $objDBCA;

	try{
		$objQueryUtn = $objDBCA->sqlPrepare($sqlUtnBody);
		$objQueryJnl = $objDBCA->sqlPrepare($sqlJnlBody);

		/* Bindが無い場合、nullかarray()を割り当てる */
		if(
			$objQueryUtn->getStatus() === false
		  OR
			$objQueryJnl->getStatus() === false
		){
			/* DBアクセス異常([FILE]｛｝[LINE]｛｝) */
			$msgstr = 'DB access error occurred. (file:' . basename(__FILE__) . ' line:' . __LINE__ . ')' ; // ITAANSIBLEH-ERR-80000
			LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

			$msgstr = $objQueryUtn->getLastError();
			LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

			$msgstr = $sqlUtnBody . "\n" . $arrayUtnBind;
			LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
			$msgstr = $sqlJnlBody . "\n" . $arrayJnlBind;
			LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

			return false;
		}

		if(empty($arrayUtnBind)){ $arrayUtnBind = array() ; }
		if(empty($arrayJnlBind)){ $arrayJnlBind = array() ; }

		if(
			$objQueryUtn->sqlBind($arrayUtnBind) != ""
		  OR
			$objQueryJnl->sqlBind($arrayJnlBind) != ""
		){
			/* DBアクセス異常([FILE]｛｝[LINE]｛｝) */
			$msgstr = 'DB access error occurred. (file:' . basename(__FILE__) . ' line:' . __LINE__ . ')' ; // ITAANSIBLEH-ERR-80000
			LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

			$msgstr = $objQueryUtn->getLastError();
			LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

			$msgstr = $sqlUtnBody . "\n" . $arrayUtnBind;
			LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
			$msgstr = $sqlJnlBody . "\n" . $arrayJnlBind;
			LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

			return false;
		}

		$rUtn = $objQueryUtn->sqlExecute();
		if($rUtn != true) {
			/* DBアクセス異常([FILE]｛｝[LINE]｛｝) */
			$msgstr = 'DB access error occurred. (file:' . basename(__FILE__) . ' line:' . __LINE__ . ')' ; // ITAANSIBLEH-ERR-80000
			LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

			$msgstr = $objQueryUtn->getLastError();
			LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

			$msgstr = $sqlUtnBody . "\n" . $arrayUtnBind;
			LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

			return false;
		}

		$rJnl = $objQueryJnl->sqlExecute();
		if($rJnl != true) {
			/* DBアクセス異常([FILE]｛｝[LINE]｛｝) */
			$msgstr = 'DB access error occurred. (file:' . basename(__FILE__) . ' line:' . __LINE__ . ')' ; // ITAANSIBLEH-ERR-80000
			LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

			$msgstr = $objQueryUtn->getLastError();
			LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

			$msgstr = $sqlJnlBody . "\n" . $arrayJnlBind;
			LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

			return false;
		}
	} finally {
		unset($objQueryUtn);
		unset($objQueryJnl);
	}

	return true;
}

function LocalLogPrint($p1,$p2,$p3){
	global $log_output_dir;
	global $log_file_prefix;
	global $log_level;
	global $root_dir_path;
	global $log_output_php;
	$FREE_LOG = "FILE:$p1 LINE:$p2 $p3";
	require ($root_dir_path . $log_output_php);
}
/* < END > F0017：その他、ValidationのFunction---------------------------------------------------------------------------------------------- */

/* <START> F0018：インターフェース情報を取得する。------------------------------------------------------------------------------------------ */
////////////////////////////////////////////////////////////////////////////////
// F0018
// 処理内容
//   インターフェース情報を取得する。
//
// パラメータ
//   $ina_ans_if_info:        インターフェース情報
// 戻り値
//   True:正常　　False:異常
////////////////////////////////////////////////////////////////////////////////
function getIFInfoDB(&$ina_if_info,&$in_error_msg)
{
    global    $db_model_ch;
    global    $objMTS;
    global    $objDBCA;
    global    $log_level;

    // SQL作成
    $sql = "SELECT * FROM B_ANSTWR_IF_INFO WHERE DISUSE_FLAG = '0'";

    // SQL準備
    $objQuery = $objDBCA->sqlPrepare($sql);
    if( $objQuery->getStatus()===false ){
        $msgstr = $objMTS->getSomeMessage("ITAANSTWRH-ERR-56100",array(basename(__FILE__),__LINE__));
        $in_error_msg  = $msgstr;
        LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
        LocalLogPrint(basename(__FILE__),__LINE__,$sql);
        LocalLogPrint(basename(__FILE__),__LINE__,$objQuery->getLastError());

        return false;
    }

    // SQL発行
    $r = $objQuery->sqlExecute();
    if (!$r){
        $msgstr = $objMTS->getSomeMessage("ITAANSTWRH-ERR-56100",array(basename(__FILE__),__LINE__));
        $in_error_msg  = $msgstr;
        LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
        LocalLogPrint(basename(__FILE__),__LINE__,$sql);
        LocalLogPrint(basename(__FILE__),__LINE__,$objQuery->getLastError());

        unset($objQuery);
        return false;
    }

    // レコードFETCH
    while ( $row = $objQuery->resultFetch() ){
        $ina_if_info = $row;
    }
    // FETCH行数を取得
    $num_of_rows = $objQuery->effectedRowCount();

    // レコード無しの場合は「ANSIBLETOWERインタフェース情報」が登録されていない
    if( $num_of_rows === 0 ){
        if ( $log_level === 'DEBUG' ){
            //ANSIBLETOWERインタフェース情報レコード無し
            $msgstr = $objMTS->getSomeMessage("ITAANSTWRH-ERR-56000");
            $in_error_msg  = $msgstr;
            LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
        }
        unset($objQuery);
        return false;
    }

    // DBアクセス事後処理
    unset($objQuery);

    return true;
}
/* < END > F0018：インターフェース情報を取得する。------------------------------------------------------------------------------------------ */

/* <START> F0019：パラメータシートの具体値がNULLの場合でも代入値管理ら登録するかを判定------------------------------------------------------ */
////////////////////////////////////////////////////////////////////////////////
// F0019
// 処理内容
//   パラメータシートの具体値がNULLの場合でも代入値管理ら登録するかを判定
//
// パラメータ
//   $in_null_data_handling_flg:    代入値自動登録設定のNULL登録フラグ
// 戻り値
//   '1':有効    '2':無効
////////////////////////////////////////////////////////////////////////////////
function getNullDataHandlingID($in_null_data_handling_flg)
{
    global $g_null_data_handling_def;
    //代入値自動登録設定のNULL登録フラグ判定
    switch($in_null_data_handling_flg) {
    case '1':   // 有効
        $id = '1'; break;
    case '2':   // 無効
        $id = '2'; break;
    default:    // インターフェース情報に従う
        // インターフェース情報のNULL登録フラグ判定
        switch($g_null_data_handling_def) {
        case '1':   // 有効
            $id = '1'; break;
        case '2':   // 無効
            $id = '2'; break;
        }
    }
    return($id);
}
/* < END > F0019：パラメータシートの具体値がNULLの場合でも代入値管理ら登録するかを判定------------------------------------------------------ */



?>