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
//	・AnsibleTower Movement詳細 
//
//////////////////////////////////////////////////////////////////////

$tmpFx = function (&$aryVariant=array(),&$arySetting=array()){
	global $g;

	$arrayWebSetting = array();
	$arrayWebSetting['page_info'] = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7301801");

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

	$table = new TableControlAgent('D_ANSTWR_PTN_ROLE_LINK','PTN_ROLE_LINK_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7391801"), 'D_ANSTWR_PTN_ROLE_LINK_JNL', $tmpAry);
	$tmpAryColumn = $table->getColumns();
	$tmpAryColumn['PTN_ROLE_LINK_ID'] -> setSequenceID('B_ANSTWR_PTN_ROLE_LINK_RIC');
	$tmpAryColumn['JOURNAL_SEQ_NO']   -> setSequenceID('B_ANSTWR_PTN_ROLE_LINK_JSQ');

	unset($tmpAryColumn);

	// ----VIEWをコンテンツソースにする場合、構成する実体テーブルを更新するための設定
	$table->setDBMainTableHiddenID('B_ANSTWR_PTN_ROLE_LINK');
	$table->setDBJournalTableHiddenID('B_ANSTWR_PTN_ROLE_LINK_JNL');

	$table -> setJsEventNamePrefix(true);
	$table -> setDBMainTableLabel($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7361801")); // QMファイル名プレフィックス
	$table -> getFormatter('excel')->setGeneValue('sheetNameForEditByFile', $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7341801")); // エクセルのシート名
	$table -> setGeneObject('AutoSearchStart',true);  // 検索機能の制御


	/* Movement */
	$c = new IDColumn('PATTERN_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7391802"),'E_ANSTWR_PATTERN','PATTERN_ID','PATTERN','',array('OrderByThirdColumn'=>'PATTERN_ID'));
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7351802"));//エクセル・ヘッダでの説明
	$c -> setJournalTableOfMaster('E_ANSTWR_PATTERN_JNL');
	$c -> setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c -> setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c -> setJournalKeyIDOfMaster('PATTERN_ID');
	$c -> setJournalDispIDOfMaster('PATTERN');
	$c -> setRequired(true);//登録/更新時には、入力必須

	$c -> setHiddenMainTableColumn(true);

	$table->addColumn($c);


	/* ロールパッケージ名 */
	// RestAPI/Excel/CSVからの登録の場合に組み合わせバリデータで退避したROLE_PACKAGE_IDを設定する。
	$tmpObjFunction = function($objColumn, $strEventKey, &$exeQueryData, &$reqOrgData=array(), &$aryVariant=array()) {

		global    $g;

		$boolRet = true;
		$intErrorType = null;
		$aryErrMsgBody = array();
		$strErrMsg = "";
		$strErrorBuf = "";

		$modeValue = $aryVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_MODE"];
		if($modeValue=="DTUP_singleRecRegister" || $modeValue=="DTUP_singleRecUpdate") {
			if(strlen($g['ROLE_PACKAGE_ID_UPDATE_VALUE']) !== 0) {
				$exeQueryData[$objColumn->getID()] = $g['ROLE_PACKAGE_ID_UPDATE_VALUE'];
			}
		} else if($modeValue=="DTUP_singleRecDelete") {
		}
		$retArray = array($boolRet,$intErrorType,$aryErrMsgBody,$strErrMsg,$strErrorBuf);
		return $retArray;
	};

	$c = new IDColumn('ROLE_PACKAGE_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7391803"),'B_ANSTWR_ROLE_PACKAGE','ROLE_PACKAGE_ID','ROLE_PACKAGE_NAME','');
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7351803"));//エクセル・ヘッダでの説明
	$c -> setEvent('update_table', 'onchange', 'package_upd');
	$c -> setEvent('register_table', 'onchange', 'package_reg');
	$c -> setJournalTableOfMaster('B_ANSTWR_ROLE_PACKAGE_JNL');
	$c -> setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c -> setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c -> setJournalKeyIDOfMaster('ROLE_PACKAGE_ID');
	$c -> setJournalDispIDOfMaster('ROLE_PACKAGE_NAME');

	$c -> setRequired(false); // 必須チェックは組合せバリデータで行う。

	//コンテンツのソースがヴューの場合、登録/更新の対象とする
	$c -> setHiddenMainTableColumn(true);

	//エクセル/CSVからのアップロードを禁止する。
	$c -> setAllowSendFromFile(false);

	// REST/excel/csvで項目無効
	$c -> getOutputType('excel')->setVisible(false);
	$c -> getOutputType('csv')->setVisible(false);
	$c -> getOutputType('json')->setVisible(false);

	// データベース更新前のファンクション登録
	$c -> setFunctionForEvent('beforeTableIUDAction',$tmpObjFunction);

	$table -> addColumn($c);


	/* ロール名 */
	$tmpObjFunction = function($objColumn, $strEventKey, &$exeQueryData, &$reqOrgData=array(), &$aryVariant=array()) {

		global    $g;

		$boolRet = true;
		$intErrorType = null;
		$aryErrMsgBody = array();
		$strErrMsg = "";
		$strErrorBuf = "";

		$modeValue = $aryVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_MODE"];
		if($modeValue=="DTUP_singleRecRegister" || $modeValue=="DTUP_singleRecUpdate") {
			if(strlen($g['ROLE_ID_UPDATE_VALUE']) !== 0) {
				$exeQueryData[$objColumn->getID()] = $g['ROLE_ID_UPDATE_VALUE'];
			}
		} else if($modeValue=="DTUP_singleRecDelete") {
		}
		$retArray = array($boolRet,$intErrorType,$aryErrMsgBody,$strErrMsg,$strErrorBuf);
		return $retArray;
	};

	$c = new IDColumn('ROLE_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7391804"),'B_ANSTWR_ROLE','ROLE_ID','ROLE_NAME','');
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7351804"));//エクセル・ヘッダでの説明

	$objFunction01 = function($objOutputType, $aryVariant, $arySetting, $aryOverride, $objColumn){
		global $g;
		$retBool = false;
		$intErrorType = null;
		$aryErrMsgBody = array();
		$strErrMsg = "";
		$aryDataSet = array();

		$strFxName = "";

		$strPackageIdNumeric = $aryVariant['ROLE_PACKAGE_ID'];

		$strQuery
		 = " 			SELECT "
		  ." 			   TAB_1.ROLE_ID AS KEY_COLUMN"
		  ." 			  ,TAB_1.ROLE_NAME AS DISP_COLUMN"
		  ." 			FROM"
		  ." 			   B_ANSTWR_ROLE AS TAB_1"
		  ." 			WHERE"
		  ." 			   TAB_1.ROLE_PACKAGE_ID = :ROLE_PACKAGE_ID"
		  ." 			AND"
		  ." 			   TAB_1.DISUSE_FLAG IN ('0')"
		  ."			ORDER BY"
		  ." 			   KEY_COLUMN"
		;

		$aryForBind['ROLE_PACKAGE_ID'] = $strPackageIdNumeric;
		if( 0 < strlen($strPackageIdNumeric) ){
			$aryRetBody = singleSQLExecuteAgent($strQuery, $aryForBind, $strFxName);
			if( $aryRetBody[0] === true ){
				$objQuery = $aryRetBody[1];
				while($row = $objQuery->resultFetch() ){
					$aryDataSet[]= $row;
				}
				unset($objQuery);
				$retBool = true;
			}else{
				$intErrorType = 500;
				$intRowLength = -1;
			}
		}
		$retArray = array($retBool,$intErrorType,$aryErrMsgBody,$strErrMsg,$aryDataSet);
		return $retArray;
	};

	$objFunction02 = $objFunction01;

	$objFunction03 = function($objCellFormatter, $rowData, $aryVariant){
		global $g;
		$retBool = false;
		$intErrorType = null;
		$aryErrMsgBody = array();
		$strErrMsg = "";
		$aryDataSet = array();

		$strFxName = "";

		$strPackageIdNumeric = $rowData['ROLE_PACKAGE_ID'];
		
		$strQuery
		 = " 			SELECT "
		  ." 			   TAB_1.ROLE_ID AS KEY_COLUMN"
		  ." 			  ,TAB_1.ROLE_NAME AS DISP_COLUMN"
		  ." 			FROM"
		  ." 			   B_ANSTWR_ROLE AS TAB_1"
		  ." 			WHERE"
		  ." 			   TAB_1.ROLE_PACKAGE_ID = :ROLE_PACKAGE_ID"
		  ." 			AND"
		  ." 			   TAB_1.DISUSE_FLAG IN ('0')"
		  ."			ORDER BY"
		  ." 			   KEY_COLUMN"
		;

		$aryForBind['ROLE_PACKAGE_ID'] = $strPackageIdNumeric;
		if( 0 < strlen($strPackageIdNumeric) ){
			$aryRetBody = singleSQLExecuteAgent($strQuery, $aryForBind, $strFxName);
			if( $aryRetBody[0] === true ){
				$objQuery = $aryRetBody[1];
				while($row = $objQuery->resultFetch() ){
					$aryDataSet[$row['KEY_COLUMN']]= $row['DISP_COLUMN'];
				}
				unset($objQuery);
				$retBool = true;
			}else{
				$intErrorType = 500;
				$intRowLength = -1;
			}
		}
		$aryRetBody = array($retBool, $intErrorType, $aryErrMsgBody, $strErrMsg, $aryDataSet);
		return $aryRetBody;
	};

	//$strSetInnerText = 'ロールパッケージを選択して下さい'
	$strSetInnerText = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7301802");
	$objVarBFmtUpd = new SelectTabBFmt();
	$objVarBFmtUpd->setNoOptionMessageText($strSetInnerText);
	$objVarBFmtUpd->setFADNoOptionMessageText($strSetInnerText);
	$objVarBFmtUpd->setFunctionForGetSelectList($objFunction03);

	$objOTForUpd = new OutputType(new ReqTabHFmt(), $objVarBFmtUpd);
	$objOTForUpd->setFunctionForGetFADSelectList($objFunction01);

	$objVarBFmtReg = new SelectTabBFmt();
	$objVarBFmtReg -> setSelectWaitingText($strSetInnerText);
	$objVarBFmtReg -> setFADNoOptionMessageText($strSetInnerText);
	$objOTForReg   = new OutputType(new ReqTabHFmt(), $objVarBFmtReg);
	$objOTForReg   -> setFunctionForGetFADSelectList($objFunction02);

	$c -> setOutputType('update_table',$objOTForUpd);
	$c -> setOutputType('register_table',$objOTForReg);
	$c -> setJournalTableOfMaster('B_ANSTWR_ROLE_JNL');
	$c -> setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c -> setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c -> setJournalKeyIDOfMaster('ROLE_ID');
	$c -> setJournalDispIDOfMaster('ROLE_NAME');

	$c -> setRequired(false); // 必須チェックは組合せバリデータで行う。

	//コンテンツのソースがヴューの場合、登録/更新の対象とする
	$c -> setHiddenMainTableColumn(true);

	//エクセル/CSVからのアップロードを禁止する。
	$c -> setAllowSendFromFile(false);

	// REST/excel/csvで項目無効
	$c -> getOutputType('excel')->setVisible(false);
	$c -> getOutputType('csv')->setVisible(false);
	$c -> getOutputType('json')->setVisible(false);

	// データベース更新前のファンクション登録
	$c -> setFunctionForEvent('beforeTableIUDAction',$tmpObjFunction);

	$table -> addColumn($c);

	/* ロールパッケージ+ロール_REST/excel/csv入力用 */
	$c = new IDColumn('REST_ROLE_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7371801"),'D_ANSTWR_PKG_ROLE_LIST','ROLE_ID','ROLE_PACKAGE_PULLDOWN','',array('OrderByThirdColumn'=>'ROLE_ID'));
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7381801"));

	//コンテンツのソースがヴューの場合、登録/更新の対象外
	$c -> setHiddenMainTableColumn(false);

	//エクセル/CSVからのアップロード対象
	$c -> setAllowSendFromFile(true);

	//REST/excel/csv以外は非表示
	$c -> getOutputType('filter_table')->setVisible(false);
	$c -> getOutputType('print_table')->setVisible(false);
	$c -> getOutputType('update_table')->setVisible(false);
	$c -> getOutputType('register_table')->setVisible(false);
	$c -> getOutputType('delete_table')->setVisible(false);
	$c -> getOutputType('print_journal_table')->setVisible(false);
	$c -> getOutputType('excel')->setVisible(true);
	$c -> getOutputType('csv')->setVisible(true);
	$c -> getOutputType('json')->setVisible(true);

	$c -> setJournalTableOfMaster('D_ANSTWR_PKG_ROLE_LIST_JNL');
	$c -> setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c -> setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c -> setJournalKeyIDOfMaster('ROLE_ID');
	$c -> setJournalDispIDOfMaster('ROLE_PACKAGE_PULLDOWN');
	//登録/更新時には、必須でない
	$c -> setRequired(false);

	$table -> addColumn($c);


	/* インクルード順序 */
	$objVldt = new IntNumValidator(1 , null);

	$c = new NumColumn('INCLUDE_SEQ',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7391805"));
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7351805"));//エクセル・ヘッダでの説明
	$c -> setSubtotalFlag(false);
	$c -> setValidator($objVldt);
	$c -> setRequired(true);//登録/更新時には、入力必須

	//コンテンツのソースがヴューの場合、登録/更新の対象とする
	$c -> setHiddenMainTableColumn(true);

	$table -> addColumn($c);


	/* 複合主キーの設定（Movement & ロール名 & インクルード順） */
	$table->addUniqueColumnSet(array('PATTERN_ID','ROLE_ID','INCLUDE_SEQ'));

	/* カラムを確定する */
	$table->fixColumn();

	/* <START> 組み合わせバリデータ ---- */
	$tmpAryColumn = $table->getColumns();
	$objLU4UColumn = $tmpAryColumn[$table->getRequiredUpdateDate4UColumnID()];

	$objFunction = function($objClientValidator, $value, $strNumberForRI, $arrayRegData, $arrayVariant) {
		global $g;
		$retBool = true;
		$retStrBody = '';

		$strModeId = "";
		$modeValue_sub = "";

		$query = "";

		$boolExecuteContinue = true;
		$boolSystemErrorFlag = false;

		$pattan_tbl = "E_ANSTWR_PATTERN";

		$aryVariantForIsValid = $objClientValidator->getVariantForIsValid();

		if(array_key_exists("TCA_PRESERVED", $arrayVariant)){
			if(array_key_exists("TCA_ACTION", $arrayVariant["TCA_PRESERVED"])){
				$aryTcaAction = $arrayVariant["TCA_PRESERVED"]["TCA_ACTION"];
				$strModeId = $aryTcaAction["ACTION_MODE"];
			}
		}

		if($strModeId == "DTUP_singleRecDelete") {
			//----更新前のレコードから、各カラムの値を取得
			$rg_pattern_id      = isset($arrayVariant['edit_target_row']['PATTERN_ID'])
								 ? $arrayVariant['edit_target_row']['PATTERN_ID']
								 : null;
			$rg_role_package_id = isset($arrayVariant['edit_target_row']['ROLE_PACKAGE_ID'])
								 ? $arrayVariant['edit_target_row']['ROLE_PACKAGE_ID']
								 : null;
			$rg_role_id         = isset($arrayVariant['edit_target_row']['ROLE_ID'])
								 ? $arrayVariant['edit_target_row']['ROLE_ID']
								 : null;
			$rg_include_seq     = isset($arrayVariant['edit_target_row']['INCLUDE_SEQ'])
								 ? $arrayVariant['edit_target_row']['INCLUDE_SEQ']
								 : null;
			$rg_rest_role_id    = isset($arrayVariant['edit_target_row']['REST_ROLE_ID'])
								 ? $arrayVariant['edit_target_row']['REST_ROLE_ID']
								 : null;

			$modeValue_sub = $arrayVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_SUB_MODE"];//['mode_sub'];("on"/"off")
			if( $modeValue_sub == "on" ){
				//----廃止の場合はチェックしない
				$boolExecuteContinue = false;
				//廃止の場合はチェックしない----
			}else{
				//----復活の場合  REST/excelで隠していない必須項目にデータが設定されていることを確認
				//    REST_ROLE_IDはROLE_IDのクローン
				if(strlen($rg_rest_role_id) === 0 || strlen($rg_pattern_id) === 0 || strlen($rg_include_seq) === 0){
					$boolSystemErrorFlag = true;
				}
				//復活の場合----
			}
			//更新前のレコードから、各カラムの値を取得----
		} else if($strModeId == "DTUP_singleRecUpdate" || $strModeId == "DTUP_singleRecRegister") {
			$rg_pattern_id      = array_key_exists('PATTERN_ID',$arrayRegData)
								 ? $arrayRegData['PATTERN_ID']
								 : null;
			$rg_role_package_id = array_key_exists('ROLE_PACKAGE_ID',$arrayRegData)
								 ? $arrayRegData['ROLE_PACKAGE_ID']
								 : null;
			$rg_role_id         = array_key_exists('ROLE_ID',$arrayRegData)
								 ? $arrayRegData['ROLE_ID']
								 : null;
			$rg_include_seq     = array_key_exists('INCLUDE_SEQ',$arrayRegData)
								 ? $arrayRegData['INCLUDE_SEQ']
								 : null;
			$rg_rest_role_id    = array_key_exists('REST_ROLE_ID',$arrayRegData)
								 ? $arrayRegData['REST_ROLE_ID']
								 : null;
		}

		$g['ROLE_PACKAGE_ID_UPDATE_VALUE']        = "";
		$g['ROLE_ID_UPDATE_VALUE']                = "";
		//----呼出元がUIがRestAPI/Excel/CSVかを判定
		// ROLE_PACKAGE_ID;未設定 ROLE_ID:未設定 REST_ROLE_ID:設定 => RestAPI/Excel/CSV
		// その他はUI
		if($boolExecuteContinue === true && $boolSystemErrorFlag === false) {
			if((strlen($rg_role_package_id)  === 0) && 
			   (strlen($rg_role_id)          === 0) &&
			   (strlen($rg_rest_role_id)     !== 0)){
				$query =  "SELECT                                             "
						 ."  TBL_A.ROLE_PACKAGE_ID,                           "
						 ."  TBL_A.ROLE_ID,                                   "
						 ."  COUNT(*) AS ROLE_ID_CNT                          "
						 ."FROM                                               "
						 ."  D_ANSTWR_PKG_ROLE_LIST TBL_A                     "
						 ."WHERE                                              "
						 ."  TBL_A.ROLE_ID         = :ROLE_ID   AND           "
						 ."  TBL_A.DISUSE_FLAG     = '0'                      ";
				$aryForBind = array();
				$aryForBind['ROLE_ID'] = $rg_rest_role_id;
				$retArray = singleSQLExecuteAgent($query, $aryForBind, "NONAME_FUNC(VARS_MULTI_CHECK)");
				if( $retArray[0] === true ) {
					$objQuery =& $retArray[1];
					$intCount = 0;
					$row = $objQuery->resultFetch();
					if($row['ROLE_ID_CNT'] == '1') {
						$rg_role_package_id                = $row['ROLE_PACKAGE_ID'];
						$rg_role_id                        = $row['ROLE_ID'];
						$g['ROLE_PACKAGE_ID_UPDATE_VALUE'] = $rg_role_package_id;
						$g['ROLE_ID_UPDATE_VALUE']         = $rg_role_id;
					}
					else if($row['ROLE_ID_CNT'] == '0') {
						$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-6601801");
						$retBool = false;
						$boolExecuteContinue = false;
					}
					else {
						web_log("DB Access error file:" . basename(__FILE__) . " line:" . __LINE__);
						$boolSystemErrorFlag = true;
					}
					unset($row);
					unset($objQuery);
				}
				else {
					web_log("DB Access error file:" . basename(__FILE__) . " line:" . __LINE__);
					$boolSystemErrorFlag = true;
				}
				unset($retArray);
			}
		}
		//呼出元がUIがRestAPI/Excel/CSVかを判定----

		//----必須入力チェック
		if( $boolExecuteContinue === true && $boolSystemErrorFlag === false){
			if( strlen($rg_role_package_id) === 0 || strlen($rg_role_id) === 0 ) {
				$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-6601802");
				$boolExecuteContinue = false;
				$retBool = false;
			}
			else if( strlen($rg_pattern_id) === 0 ){
				$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-6601803");
				$boolExecuteContinue = false;
				$retBool = false;
			}
			else if( strlen($rg_include_seq) === 0) {
				$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-6601804");
				$boolExecuteContinue = false;
				$retBool = false;
			}
		}
		//必須入力チェック----

		//----作業パターンのチェック
		if( $boolExecuteContinue === true && $boolSystemErrorFlag === false){
			$retBool = false;
			$boolExecuteContinue = false;
			$query = " SELECT "
					 ."   COUNT(*) AS PATTERN_CNT "
					 ." FROM "
					 ."   $pattan_tbl TBL_A  "
					 ." WHERE "
					 ."   TBL_A.PATTERN_ID   = :PATTERN_ID   AND "
					 ."   TBL_A.DISUSE_FLAG  = '0' ";

			$aryForBind = array();
			$aryForBind['PATTERN_ID']     = $rg_pattern_id;

			$retArray = singleSQLExecuteAgent($query, $aryForBind, "NONAME_FUNC(VARS_MULTI_CHECK)");
			if( $retArray[0] === true ){
				$objQuery =& $retArray[1];
				$intCount = 0;
				$row = $objQuery->resultFetch();
				if( $row['PATTERN_CNT'] == '1' ){
					$retBool = true;
					$boolExecuteContinue = true;
				}else if( $row['PATTERN_CNT'] == '0' ){
					$boolExecuteContinue = false;
					$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-6601805");
				}else{
					web_log("DB Access error file:" . basename(__FILE__) . " line:" . __LINE__);
					$boolSystemErrorFlag = true;
				}
				unset($row);
				unset($objQuery);
			}else{
				web_log("DB Access error file:" . basename(__FILE__) . " line:" . __LINE__);
				$boolSystemErrorFlag = true;
			}
			unset($retArray);
		}
		//作業パターンのチェック----

		//----ロールパッケージとロールの組合せチェック
		if($boolExecuteContinue === true) {
			$retBool = false;
			$query
			 = " 			SELECT"
			  ." 			   COUNT(*) AS REC_COUNT"
			  ." 			FROM"
			  ." 			   D_ANSTWR_PKG_ROLE_LIST AS TAB_1"
			  ." 			WHERE"
			  ." 			   TAB_1.DISUSE_FLAG = '0'"
			  ." 			AND"
			  ." 			   TAB_1.ROLE_PACKAGE_ID = :ROLE_PACKAGE_ID"
			  ." 			AND"
			  ." 			   TAB_1.ROLE_ID = :ROLE_ID"
			;
			/* クエリーバインド */
			$aryForBind = array();
			$aryForBind['ROLE_PACKAGE_ID'] = $rg_role_package_id;
			$aryForBind['ROLE_ID']         = $rg_role_id;

			$retArray = singleSQLExecuteAgent($query, $aryForBind, "NONAME_FUNC(VARS_MULTI_CHECK)");
			if( $retArray[0] === true ){
				$objQuery =& $retArray[1];
				$intCount = 0;
				$aryDiscover = array();
				$row = $objQuery->resultFetch();
				unset($objQuery);
				if( $row['REC_COUNT'] == '1' ){
					$retBool = true;
				}else if( $row['REC_COUNT'] == '0' ){
					$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7301803");
				}else{
					web_log("DB Access error file:" . basename(__FILE__) . " line:" . __LINE__);
					$boolSystemErrorFlag = true;
				}
			}else{
				web_log("DB Access error file:" . basename(__FILE__) . " line:" . __LINE__);
				$boolSystemErrorFlag = true;
			}
		}
		//ロールパッケージとロールの組合せチェック----

		if( $boolSystemErrorFlag === true ){
			$retBool = false;
			//----システムエラー
			$retStrBody = $g['objMTS']->getSomeMessage("ITAWDCH-ERR-3001");
		}

		if($retBool===false){
			$objClientValidator->setValidRule($retStrBody);
		}
		return $retBool;
	};

	$objVarVali = new VariableValidator();
	$objVarVali -> setErrShowPrefix(false);
	$objVarVali -> setFunctionForIsValid($objFunction);
	$objVarVali -> setVariantForIsValid(array());

	$objLU4UColumn->addValidator($objVarVali);
	/* <END> 組み合わせバリデータ ---- */

	$table->setGeneObject('webSetting', $arrayWebSetting);

	return $table;
};
loadTableFunctionAdd($tmpFx,__FILE__);

unset($tmpFx);
