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
//   ・AnsibleTowerのロールパッケージ管理
//
//////////////////////////////////////////////////////////////////////

/* ルートディレクトリの取得 */
if ( empty($root_dir_path) ){
	$root_dir_temp = array();
	$root_dir_temp = explode( "ita-root", dirname(__FILE__) );
	$root_dir_path = $root_dir_temp[0] . "ita-root";
}

/* 共通モジュールをロード */
require_once ($root_dir_path . '/libs/backyardlibs/ansibletower_driver/AnsibleTowerCommonLib.php');

$tmpFx = function (&$aryVariant=array(),&$arySetting=array()){
	global $g;

	$arrayWebSetting = array();
	$arrayWebSetting['page_info'] = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7302101"); 
	
	/* 履歴管理用のカラムを配列に格納する */
	$tmpAry = array
	  (
		 'TT_SYS_01_JNL_SEQ_ID'           => 'JOURNAL_SEQ_NO'
		,'TT_SYS_02_JNL_TIME_ID'          => 'JOURNAL_REG_DATETIME'
		,'TT_SYS_03_JNL_CLASS_ID'         => 'JOURNAL_ACTION_CLASS'
		,'TT_SYS_04_NOTE_ID'              => 'NOTE'
		,'TT_SYS_04_DISUSE_FLAG_ID'       => 'DISUSE_FLAG'
		,'TT_SYS_05_LUP_TIME_ID'          => 'LAST_UPDATE_TIMESTAMP'
		,'TT_SYS_06_LUP_USER_ID'          => 'LAST_UPDATE_USER'
		,'TT_SYS_NDB_ROW_EDIT_BY_FILE_ID' => 'ROW_EDIT_BY_FILE'
		,'TT_SYS_NDB_UPDATE_ID'           => 'WEB_BUTTON_UPDATE'
		,'TT_SYS_NDB_LUP_TIME_ID'         => 'UPD_UPDATE_TIMESTAMP'
	  );

	/* 画面と１体１で紐付けるテーブルを指定する */
	$table = new TableControlAgent('B_ANSTWR_ROLE_PACKAGE','ROLE_PACKAGE_ID', $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7392101"), 'B_ANSTWR_ROLE_PACKAGE_JNL', $tmpAry);
	$tmpAryColumn = $table->getColumns();
	$tmpAryColumn['ROLE_PACKAGE_ID']->setSequenceID('B_ANSTWR_ROLE_PACKAGE_RIC');
	$tmpAryColumn['JOURNAL_SEQ_NO']->setSequenceID('B_ANSTWR_ROLE_PACKAGE_JSQ');
	unset($tmpAryColumn);

	/* QMファイル名プレフィックス */
	$table->setDBMainTableLabel($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7362101"));
	/* エクセルのシート名 */
	$table->getFormatter('excel')->setGeneValue('sheetNameForEditByFile',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7342101"));

	/* 検索機能の制御 */
	$table->setGeneObject('AutoSearchStart',true);  //('',true,false)

	/* ロールパッケージ名 */
	$objVldt = new SingleTextValidator(1,128,false);
	$c = new TextColumn('ROLE_PACKAGE_NAME',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7392102"));
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7352102"));//エクセル・ヘッダでの説明
	$c->setValidator($objVldt);
	$c->setRequired(true); // 登録/更新時には、入力必須
	$c->setUnique(true); // UX上の一意キー
	$table->addColumn($c);

	/* FileUpload時にZIPファイルの内容をチェック */
	$objFunction = function($objColumn, $functionCaller, $strTempFileFullname, $strOrgFileName, $aryVariant, $arySetting){
		global $g;
		if ( empty($root_dir_path) ){
			$root_dir_temp = array();
			$root_dir_temp = explode( "ita-root", dirname(__FILE__) );
			$root_dir_path = $root_dir_temp[0] . "ita-root";
		}

		/* 定義情報の取得 */
		require_once ($root_dir_path . '/libs/backyardlibs/ansibletower_driver/setenv.php' );
		require_once ($root_dir_path . '/libs/backyardlibs/ansibletower_driver/role_package/WrappedStringReplaceAdmin.php' );
		require_once ($root_dir_path . '/libs/backyardlibs/ansibletower_driver/role_package/chkCPFVarsLib.php' );

		/* ディレクトリの内容をチェック */
		require_once ($root_dir_path . '/libs/backyardlibs/ansibletower_driver/role_package/ItaAnsibleRoleStructure.php' );

		$boolRet       = true;
		$intErrorType  = null;
		$aryErrMsgBody = array();
		$strErrMsg     = null;
		$arysystemvars = array();

		/* ロールパッケージファイル(ZIP)の解凍先 */
		$outdir  = "/tmp/AnsibleTowerZipFileUpload_" . getmypid();

		/* ロールパッケージファイル(ZIP)を解析するクラス生成 */
		$roleObj = new ItaAnsibleRoleStructure("current_tmp", $outdir, array(), true);

		// ロールパッケージファイル名(ZIP)を取得
		$ret = $roleObj->getAnsible_RolePackage_filePath_tmp($strTempFileFullname);

		// ロールパッケージファイル名(ZIP)の存在確認
		if($ret === false) {
			$lastError = $objRole->getLastError();
			$logger->debug($lastError[1]);
			return false;
		}

		/* ロールパッケージファイル(ZIP)の解凍 */
		if($roleObj->zipExtractTo() === false){
			$boolRet = false;
			$arryErrMsg = $roleObj->getLastError();
			$strErrMsg = $arryErrMsg[0];

		}
		else{

			/* ロールパッケージファイル(ZIP)の解析 */
			$ret = $roleObj->chkRolesDirectory();

			if($ret === false){
				/* ロール内の読替表で読替変数と任意変数の組合せが一致していない */
				if(@count($roleObj->getTranslateCombErrVars()) !== 0){
					$strErrMsg  = $roleObj->getTranslationTableCombinationErrMsg(true);
					$boolRet = false;
				}

				/* defaults定義ファイルに定義されている変数で形式が違う変数がある場合 */
				else if(@count($roleObj->getStructErrVars()) !== 0){
					// エラーメッセージ編集
					$strErrMsg = $roleObj->getVarsStructErrMsg();
					$boolRet   = false;
				}
				else{
					$arryErrMsg = $roleObj->getLastError();
					$strErrMsg  = $arryErrMsg[0];
					$boolRet    = false;
				}
			}
			exec("/bin/rm -rf " . $outdir);

			if($boolRet === true){
				$strErrMsg = "";;
				$strErrDetailMsg = "";
				/* copy変数がファイル管理に登録されているか判定 */
                $boolRet = chkCPFVarsMasterReg($g['objMTS'],$g['objDBCA'],$roleObj->getCopyVars(),$strErrMsg,$strErrDetailMsg);
				if($boolRet === false){
					if($strErrDetailMsg != ""){
						web_log($strErrDetailMsg);
					}
				}
			}
		}
		unset($roleObj);

		$retArray = array($boolRet,$intErrorType,$aryErrMsgBody,$strErrMsg);

		return $retArray;
	};

	/* ロールパッケージファイル(ZIP形式) */
	$c = new FileUploadColumn('ROLE_PACKAGE_FILE',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7392103"));
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7352103"));//エクセル・ヘッダでの説明
	$c->setMaxFileSize(268435456);//単位はバイト
	$c->setAllowSendFromFile(false);//エクセル/CSVからのアップロードを禁止する。
	$c->setRequired(true);//登録/更新時には、入力必須
	$c->setFileHideMode(true);
	$c->setFunctionForEvent('checkTempFileBeforeMoveOnPreLoad',$objFunction); // FileUpload時にZIPファイルの内容をチェックするモジュール登録
	$c->setAllowUploadColmnSendRestApi(true);   //REST APIからのアップロード可否。FileUploadColumnのみ有効(default:false)
	$table->addColumn($c);

	/* カラムを確定させる */
	$table->fixColumn();
	$table->setGeneObject('webSetting', $arrayWebSetting);

	return $table;
};
loadTableFunctionAdd($tmpFx,__FILE__);

unset($tmpFx);