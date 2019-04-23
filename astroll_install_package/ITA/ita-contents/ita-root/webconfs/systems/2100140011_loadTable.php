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
//   ・AnsibleTower 代入値管理
//
//////////////////////////////////////////////////////////////////////

$tmpFx = function (&$aryVariant=array(),&$arySetting=array()){
	global $g;

	$arrayWebSetting = array();
	$arrayWebSetting['page_info'] = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7302201"); //MessageID_SecondSuffix：22

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

	$table = new TableControlAgent('D_ANSTWR_VARS_ASSIGN','VARS_ASSIGN_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7392201"), 'D_ANSTWR_VARS_ASSIGN_JNL', $tmpAry);
	$tmpAryColumn = $table->getColumns();
	$tmpAryColumn['VARS_ASSIGN_ID']->setSequenceID('B_ANSTWR_VARS_ASSIGN_RIC');
	$tmpAryColumn['JOURNAL_SEQ_NO']->setSequenceID('B_ANSTWR_VARS_ASSIGN_JSQ');
	unset($tmpAryColumn);

	// ----VIEWをコンテンツソースにする場合、構成する実体テーブルを更新するための設定
	$table->setDBMainTableHiddenID('B_ANSTWR_VARS_ASSIGN');
	$table->setDBJournalTableHiddenID('B_ANSTWR_VARS_ASSIGN_JNL');

	/* 動的プルダウン 作成用 */
	$table->setJsEventNamePrefix(true);

	/* QMファイル名プレフィックス */
	$table->setDBMainTableLabel($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7362201"));

	/* エクセルのシート名 */
	$table->getFormatter('excel')->setGeneValue('sheetNameForEditByFile',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7342201"));

	/* 検索機能の制御 */
	$table->setGeneObject('AutoSearchStart',true);  //('',true,false)

	/* オペレーション */
	$c = new IDColumn('OPERATION_NO_UAPK',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7392202"),'E_OPERATION_LIST','OPERATION_NO_UAPK','OPERATION','',array('OrderByThirdColumn'=>'OPERATION_NO_UAPK'));
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7352202"));//エクセル・ヘッダでの説明

	/* js側でonchangeイベントを発生させる為の定義 */
	$c->setEvent('update_table', 'onchange', 'operation_upd');
	$c->setEvent('register_table', 'onchange', 'operation_reg');

	$c->setJournalTableOfMaster('E_OPERATION_LIST_JNL');
	$c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c->setJournalKeyIDOfMaster('OPERATION_NO_UAPK');
	$c->setJournalDispIDOfMaster('OPERATION');
	$c->setRequired(true);//登録/更新時には、入力必須

	//コンテンツのソースがヴューの場合、登録/更新の対象とする
	$c->setHiddenMainTableColumn(true);

	$table->addColumn($c);


    /* 作業対象ホスト_REST/excel/csv入力用 */
    $c = new IDColumn('REST_SYSTEM_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7372201"),'D_ANSTWR_PHO_LINK','SYSTEM_ID','HOST_PULLDOWN','',array('OrderByThirdColumn'=>'SYSTEM_ID'));
    $c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7382201"));//エクセル・ヘッダでの説明

    //コンテンツのソースがヴューの場合、登録/更新の対象外
    $c->setHiddenMainTableColumn(false);

    //エクセル/CSVからのアップロード対象
    $c->setAllowSendFromFile(true);

    //REST/excel/csv以外は非表示
    $c->getOutputType('filter_table')->setVisible(false);
    $c->getOutputType('print_table')->setVisible(false);
    $c->getOutputType('update_table')->setVisible(false);
    $c->getOutputType('register_table')->setVisible(false);
    $c->getOutputType('delete_table')->setVisible(false);
    $c->getOutputType('print_journal_table')->setVisible(false);
    $c->getOutputType('excel')->setVisible(true);
    $c->getOutputType('csv')->setVisible(true);
    $c->getOutputType('json')->setVisible(true);

    $c->setJournalTableOfMaster('D_ANSTWR_PHO_LINK_JNL');
    $c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
    $c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
    $c->setJournalKeyIDOfMaster('SYSTEM_ID');
    $c->setJournalDispIDOfMaster('HOST_PULLDOWN');
    //登録/更新時には、必須でない
    $c->setRequired(false);

    $table->addColumn($c);

    /* Movement+変数名_REST/excel/csv入力用 */
    $c = new IDColumn('REST_VARS_LINK_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7372202"),'D_ANSTWR_PTN_VARS_LINK','VARS_LINK_ID','PTN_VAR_PULLDOWN','',array('OrderByThirdColumn'=>'VARS_LINK_ID'));
    $c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7382202"));

    //コンテンツのソースがヴューの場合、登録/更新の対象外
    $c->setHiddenMainTableColumn(false);

    //エクセル/CSVからのアップロード対象
    $c->setAllowSendFromFile(true);

    //REST/excel/csv以外は非表示
    $c->getOutputType('filter_table')->setVisible(false);
    $c->getOutputType('print_table')->setVisible(false);
    $c->getOutputType('update_table')->setVisible(false);
    $c->getOutputType('register_table')->setVisible(false);
    $c->getOutputType('delete_table')->setVisible(false);
    $c->getOutputType('print_journal_table')->setVisible(false);
    $c->getOutputType('excel')->setVisible(true);
    $c->getOutputType('csv')->setVisible(true);
    $c->getOutputType('json')->setVisible(true);

    $c->setJournalTableOfMaster('D_ANSTWR_PTN_VARS_LINK_JNL');
    $c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
    $c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
    $c->setJournalKeyIDOfMaster('VARS_LINK_ID');
    $c->setJournalDispIDOfMaster('PTN_VAR_PULLDOWN');
    //登録/更新時には、必須でない
    $c->setRequired(false);

    $table->addColumn($c);

    /* メンバー変数名_REST/excel/csv入力用 */
    $c = new IDColumn('REST_NESTEDMEM_COL_CMB_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7372203"),'D_ANSTWR_NESTEDMEM_COL_CMB','NESTEDMEM_COL_CMB_ID','VAR_MEMBER_PULLDOWN','',array('OrderByThirdColumn'=>'NESTEDMEM_COL_CMB_ID'));
    $c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7382203"));

    //コンテンツのソースがヴューの場合、登録/更新の対象外
    $c->setHiddenMainTableColumn(false);

    //エクセル/CSVからのアップロード対象
    $c->setAllowSendFromFile(true);

    //REST/excel/csv以外は非表示
    $c->getOutputType('filter_table')->setVisible(false);
    $c->getOutputType('print_table')->setVisible(false);
    $c->getOutputType('update_table')->setVisible(false);
    $c->getOutputType('register_table')->setVisible(false);
    $c->getOutputType('delete_table')->setVisible(false);
    $c->getOutputType('print_journal_table')->setVisible(false);
    $c->getOutputType('excel')->setVisible(true);
    $c->getOutputType('csv')->setVisible(true);
    $c->getOutputType('json')->setVisible(true);

    $c->setJournalTableOfMaster('D_ANSTWR_NESTEDMEM_COL_CMB_JNL');
    $c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
    $c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
    $c->setJournalKeyIDOfMaster('NESTEDMEM_COL_CMB_ID');
    $c->setJournalDispIDOfMaster('VAR_MEMBER_PULLDOWN');
    //登録/更新時には、必須でない
    $c->setRequired(false);

    $table->addColumn($c);

	/* Movement */
	$objFunction01 = function($objOutputType, $aryVariant, $arySetting, $aryOverride, $objColumn){
		global $g;
		$retBool = false;
		$intErrorType = null;
		$aryErrMsgBody = array();
		$strErrMsg = "";
		$aryDataSet = array();

		$strFxName = "";

		$strOperationNumeric = $aryVariant['OPERATION_NO_UAPK'];
		$strQuery
		 = " 			SELECT "
		  ." 			   TAB_1.PATTERN_ID AS KEY_COLUMN"
		  ." 			  ,TAB_2.PATTERN AS DISP_COLUMN"
		  ." 			FROM"
		  ." 			   B_ANSTWR_PHO_LINK AS TAB_1"
		  ." 			LEFT JOIN"
		  ." 			   E_ANSTWR_PATTERN AS TAB_2"
		  ." 			 ON"
		  ." 			   TAB_1.PATTERN_ID = TAB_2.PATTERN_ID"
		  ." 			WHERE"
		  ." 			   TAB_1.DISUSE_FLAG IN ('0')"
		  ." 			AND"
		  ." 			   TAB_2.DISUSE_FLAG IN ('0')"
		  ." 			AND"
		  ." 			   TAB_1.OPERATION_NO_UAPK = :OPERATION_NO_UAPK"
		  ." 			ORDER BY"
		  ." 			   KEY_COLUMN"
		;
		/* クエリーバインド */
		$aryForBind['OPERATION_NO_UAPK'] = $strOperationNumeric;

		if( 0 < strlen($strOperationNumeric) ){
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

		$strOperationNumeric = $rowData['OPERATION_NO_UAPK'];

		$strQuery
		 = " 			SELECT "
		  ." 			   TAB_1.PATTERN_ID AS KEY_COLUMN "
		  ." 			  ,TAB_2.PATTERN AS DISP_COLUMN "
		  ." 			FROM "
		  ." 			   B_ANSTWR_PHO_LINK AS TAB_1 "
		  ." 			LEFT JOIN"
		  ." 			   E_ANSTWR_PATTERN AS TAB_2"
		  ." 			 ON"
		  ." 			   TAB_1.PATTERN_ID = TAB_2.PATTERN_ID"
		  ." 			WHERE"
		  ." 			   TAB_1.DISUSE_FLAG IN ('0')"
		  ." 			AND"
		  ." 			   TAB_2.DISUSE_FLAG IN ('0')"
		  ." 			AND"
		  ." 			   TAB_1.OPERATION_NO_UAPK = :OPERATION_NO_UAPK"
		  ." 			ORDER BY"
		  ." 			   KEY_COLUMN"
		;
		/* クエリーバインド */
		$aryForBind['OPERATION_NO_UAPK'] = $strOperationNumeric;

		if( 0 < strlen($strOperationNumeric) ){
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

	// RestAPI/Excel/CSVからの登録の場合に組み合わせバリデータで退避したPATTERN_IDを設定する。
	$tmpObjFunction = function($objColumn, $strEventKey, &$exeQueryData, &$reqOrgData=array(), &$aryVariant=array()){

		global $g;

		$boolRet = true;
		$intErrorType = null;
		$aryErrMsgBody = array();
		$strErrMsg = "";
		$strErrorBuf = "";

		$modeValue = $aryVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_MODE"];
		if($modeValue=="DTUP_singleRecRegister" || $modeValue=="DTUP_singleRecUpdate") {
			if(strlen($g['PATTERN_ID_UPDATE_VALUE']) !== 0) {
				$exeQueryData[$objColumn->getID()] = $g['PATTERN_ID_UPDATE_VALUE'];
			}
		} else if($modeValue=="DTUP_singleRecDelete") {
		}
		$retArray = array($boolRet,$intErrorType,$aryErrMsgBody,$strErrMsg,$strErrorBuf);
		return $retArray;
	};

	$c = new IDColumn('PATTERN_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7392203"),'E_ANSTWR_PATTERN','PATTERN_ID','PATTERN','',array('OrderByThirdColumn'=>'PATTERN_ID'));
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7352203"));//エクセル・ヘッダでの説明

	$strSetInnerText = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7302202");
	$objVarBFmtUpd = new SelectTabBFmt();
	$objVarBFmtUpd->setFADJsEvent('onChange','pattern_upd');
	$objVarBFmtUpd->setNoOptionMessageText($strSetInnerText);
	$objVarBFmtUpd->setFADNoOptionMessageText($strSetInnerText);
	$objVarBFmtUpd->setFunctionForGetSelectList($objFunction03);

	$objOTForUpd = new OutputType(new ReqTabHFmt(), $objVarBFmtUpd);
	$objOTForUpd->setFunctionForGetFADSelectList($objFunction01);

	$objVarBFmtReg = new SelectTabBFmt();
	$objVarBFmtReg->setFADJsEvent('onChange','pattern_reg');
	$objVarBFmtReg->setFADNoOptionMessageText($strSetInnerText);

	$objVarBFmtReg->setSelectWaitingText($strSetInnerText);
	$objOTForReg = new OutputType(new ReqTabHFmt(), $objVarBFmtReg);
	$objOTForReg->setFunctionForGetFADSelectList($objFunction02);

	$c->setOutputType('update_table',$objOTForUpd);
	$c->setOutputType('register_table',$objOTForReg);

	$c->setEvent('update_table','onChange','pattern_upd',array());

	$c->setJournalTableOfMaster('E_ANSTWR_PATTERN_JNL');
	$c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c->setJournalKeyIDOfMaster('PATTERN_ID');
	$c->setJournalDispIDOfMaster('PATTERN');

	$c->setRequired(false); // 必須チェックは組合せバリデータで行う。

	//コンテンツのソースがヴューの場合、登録/更新の対象とする
	$c->setHiddenMainTableColumn(true);

	//エクセル/CSVからのアップロードを禁止する。
	$c->setAllowSendFromFile(false);

	// REST/excel/csvで項目無効
	$c->getOutputType('excel')->setVisible(false);
	$c->getOutputType('csv')->setVisible(false);
	$c->getOutputType('json')->setVisible(false);

	// データベース更新前のファンクション登録
	$c->setFunctionForEvent('beforeTableIUDAction', $tmpObjFunction);

	$table->addColumn($c);

	unset($objFunction01);
	unset($objFunction02);
	unset($objFunction03);


	/* 作業対象ホスト */
	$objFunction01 = function($objOutputType, $aryVariant, $arySetting, $aryOverride, $objColumn){
		global $g;
		$retBool = false;
		$intErrorType = null;
		$aryErrMsgBody = array();
		$strErrMsg = "";
		$aryDataSet = array();

		$strFxName = "";

		$strOperationNumeric = $aryVariant['OPERATION_NO_UAPK'];
		$strPatternIdNumeric = $aryVariant['PATTERN_ID'];

		$strQuery
		 = " 			SELECT"
		  ." 			   TAB_1.SYSTEM_ID AS KEY_COLUMN"
		  ." 			  ,TAB_2.HOST_PULLDOWN AS DISP_COLUMN"
		  ." 			FROM "
		  ." 			   B_ANSTWR_PHO_LINK AS TAB_1"
		  ." 			LEFT JOIN"
		  ." 			   E_STM_LIST AS TAB_2"
		  ." 			 ON"
		  ." 			   TAB_1.SYSTEM_ID = TAB_2.SYSTEM_ID"
		  ." 			WHERE "
		  ." 			   TAB_1.DISUSE_FLAG IN ('0')"
		  ." 			AND"
		  ." 			   TAB_2.DISUSE_FLAG IN ('0')"
		  ." 			AND"
		  ." 			   TAB_1.OPERATION_NO_UAPK = :OPERATION_NO_UAPK"
		  ." 			AND"
		  ." 			   TAB_1.PATTERN_ID = :PATTERN_ID"
		  ." 			ORDER BY"
		  ." 			   KEY_COLUMN ASC"
		;
		/* クエリーバインド */
		$aryForBind['OPERATION_NO_UAPK'] = $strOperationNumeric;
		$aryForBind['PATTERN_ID'] = $strPatternIdNumeric;

		if( 0 < strlen($strOperationNumeric) && 0 < strlen($strPatternIdNumeric) ){
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

		$strOperationNumeric = $rowData['OPERATION_NO_UAPK'];
		$strPatternIdNumeric = $rowData['PATTERN_ID'];

		$strQuery
		 = " 			SELECT"
		  ." 			   TAB_1.SYSTEM_ID AS KEY_COLUMN"
		  ." 			  ,TAB_2.HOST_PULLDOWN AS DISP_COLUMN"
		  ." 			FROM "
		  ." 			   B_ANSTWR_PHO_LINK AS TAB_1"
		  ." 			LEFT JOIN"
		  ." 			   E_STM_LIST AS TAB_2"
		  ." 			 ON"
		  ." 			   TAB_1.SYSTEM_ID = TAB_2.SYSTEM_ID"
		  ." 			WHERE "
		  ." 			   TAB_1.DISUSE_FLAG IN ('0')"
		  ." 			AND"
		  ." 			   TAB_2.DISUSE_FLAG IN ('0')"
		  ." 			AND"
		  ." 			   TAB_1.OPERATION_NO_UAPK = :OPERATION_NO_UAPK"
		  ." 			AND"
		  ." 			   TAB_1.PATTERN_ID = :PATTERN_ID"
		  ." 			ORDER BY"
		  ." 			   KEY_COLUMN ASC"
		;
		/* クエリーバインド */
		$aryForBind['OPERATION_NO_UAPK'] = $strOperationNumeric;
		$aryForBind['PATTERN_ID'] = $strPatternIdNumeric;

		if( 0 < strlen($strOperationNumeric) && 0 < strlen($strPatternIdNumeric) ){
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

	// RestAPI/Excel/CSVからの登録の場合に組み合わせバリデータで退避したSYSTEM_IDを設定する。
	$tmpObjFunction = function($objColumn, $strEventKey, &$exeQueryData, &$reqOrgData=array(), &$aryVariant=array()){

		global $g;

		$boolRet = true;
		$intErrorType = null;
		$aryErrMsgBody = array();
		$strErrMsg = "";
		$strErrorBuf = "";

		$modeValue = $aryVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_MODE"];
		if($modeValue=="DTUP_singleRecRegister" || $modeValue=="DTUP_singleRecUpdate") {
			if(strlen($g['SYSTEM_ID_UPDATE_VALUE']) !== 0) {
				$exeQueryData[$objColumn->getID()] = $g['SYSTEM_ID_UPDATE_VALUE'];
			}
		} else if($modeValue=="DTUP_singleRecDelete") {
		}
		$retArray = array($boolRet,$intErrorType,$aryErrMsgBody,$strErrMsg,$strErrorBuf);
		return $retArray;
	};

	$c = new IDColumn('SYSTEM_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7392204"),'E_STM_LIST','SYSTEM_ID','HOST_PULLDOWN','',array('OrderByThirdColumn'=>'SYSTEM_ID'));
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7352204"));//エクセル・ヘッダでの説明

	$strSetInnerText = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7302203");
	$objVarBFmtUpd = new SelectTabBFmt();
	$objVarBFmtUpd->setNoOptionMessageText($strSetInnerText);
	$objVarBFmtUpd->setFADNoOptionMessageText($strSetInnerText);
	$objVarBFmtUpd->setFunctionForGetSelectList($objFunction03);

	$objOTForUpd = new OutputType(new ReqTabHFmt(), $objVarBFmtUpd);
	$objOTForUpd->setFunctionForGetFADSelectList($objFunction01);

	$objVarBFmtReg = new SelectTabBFmt();
	$objVarBFmtReg->setFADNoOptionMessageText($strSetInnerText);

	$objVarBFmtReg->setSelectWaitingText($strSetInnerText);
	$objOTForReg = new OutputType(new ReqTabHFmt(), $objVarBFmtReg);
	$objOTForReg->setFunctionForGetFADSelectList($objFunction02);

	$c->setOutputType('update_table',$objOTForUpd);
	$c->setOutputType('register_table',$objOTForReg);

	$c->setJournalTableOfMaster('E_STM_LIST_JNL');
	$c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c->setJournalKeyIDOfMaster('SYSTEM_ID');
	$c->setJournalDispIDOfMaster('HOST_PULLDOWN');

	// $c->setRequired(true);//登録/更新時には、入力必須
	$c->setRequired(false); // 必須チェックは組合せバリデータで行う。

	//コンテンツのソースがヴューの場合、登録/更新の対象とする
	$c->setHiddenMainTableColumn(true);

	//エクセル/CSVからのアップロードを禁止する。
	$c->setAllowSendFromFile(false);

	// REST/excel/csvで項目無効
	$c->getOutputType('excel')->setVisible(false);
	$c->getOutputType('csv')->setVisible(false);
	$c->getOutputType('json')->setVisible(false);

	// データベース更新前のファンクション登録
	$c->setFunctionForEvent('beforeTableIUDAction', $tmpObjFunction);

	$table->addColumn($c);

	unset($objFunction01);
	unset($objFunction02);
	unset($objFunction03);


	/* 変数名 */
	$objFunction01 = function($objOutputType, $aryVariant, $arySetting, $aryOverride, $objColumn){

		global $g;
		$retBool = false;
		$intErrorType = null;
		$aryErrMsgBody = array();
		$strErrMsg = "";
		$aryDataSet = array();
		$strFxName = "";

		$strPatternIdNumeric = $aryVariant['PATTERN_ID'];

		$strQuery
		 = " 			SELECT"
		  ." 			   TAB_1.VARS_LINK_ID AS KEY_COLUMN "
		  ." 			  ,TAB_1.VARS_PULLDOWN AS DISP_COLUMN "
		  ." 			FROM "
		  ." 			   D_ANSTWR_PTN_VARS_LINK AS TAB_1 "
		  ." 			WHERE"
		  ." 			   TAB_1.DISUSE_FLAG IN ('0')"
		  ." 			AND"
		  ." 			   TAB_1.PATTERN_ID = :PATTERN_ID"
		  ." 			ORDER BY"
		  ." 			   KEY_COLUMN ASC"
		;
		/* クエリーバインド */
		$aryForBind['PATTERN_ID'] = $strPatternIdNumeric;

		if( 0 < strlen($strPatternIdNumeric) ){
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

		$strPatternIdNumeric = $rowData['PATTERN_ID'];

		$strQuery
		 = " 			SELECT"
		  ." 			   TAB_1.VARS_LINK_ID AS KEY_COLUMN"
		  ." 			  ,TAB_1.VARS_PULLDOWN AS DISP_COLUMN"
		  ." 			FROM"
		  ." 			   D_ANSTWR_PTN_VARS_LINK AS TAB_1"
		  ." 			WHERE"
		  ." 			   TAB_1.DISUSE_FLAG IN ('0')"
		  ." 			AND"
		  ." 			   TAB_1.PATTERN_ID = :PATTERN_ID"
		  ." 			ORDER BY"
		  ." 			   KEY_COLUMN ASC"
		;
		/* クエリーバインド */
		$aryForBind['PATTERN_ID'] = $strPatternIdNumeric;

		if( 0 < strlen($strPatternIdNumeric) ){
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

	// RestAPI/Excel/CSVからの登録の場合に組み合わせバリデータで退避したVARS_LINK_IDを設定する。
	$tmpObjFunction = function($objColumn, $strEventKey, &$exeQueryData, &$reqOrgData=array(), &$aryVariant=array()){

		global $g;

		$boolRet = true;
		$intErrorType = null;
		$aryErrMsgBody = array();
		$strErrMsg = "";
		$strErrorBuf = "";

		$modeValue = $aryVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_MODE"];
		if($modeValue=="DTUP_singleRecRegister" || $modeValue=="DTUP_singleRecUpdate") {
			if(strlen($g['VARS_LINK_ID_UPDATE_VALUE']) !== 0) {
				$exeQueryData[$objColumn->getID()] = $g['VARS_LINK_ID_UPDATE_VALUE'];
			}
		} else if($modeValue=="DTUP_singleRecDelete") {
		}
		$retArray = array($boolRet,$intErrorType,$aryErrMsgBody,$strErrMsg,$strErrorBuf);
		return $retArray;
	};

	$c = new IDColumn('VARS_LINK_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7392205"),'D_ANSTWR_PTN_VARS_LINK','VARS_LINK_ID','VARS_PULLDOWN','');
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7352205"));//エクセル・ヘッダでの説明

	$strSetInnerText = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7302204"); //Movementを選択してください。
	$objVarBFmtUpd = new SelectTabBFmt();

	$objVarBFmtUpd->setFADJsEvent('onChange','vars_upd');

	$objVarBFmtUpd->setNoOptionMessageText($strSetInnerText);
	$objVarBFmtUpd->setFADNoOptionMessageText($strSetInnerText);
	$objVarBFmtUpd->setFunctionForGetSelectList($objFunction03);

	$objOTForUpd = new OutputType(new ReqTabHFmt(), $objVarBFmtUpd);
	$objOTForUpd->setJsEvent('onChange','vars_upd');

	$objOTForUpd->setFunctionForGetFADSelectList($objFunction01);

	$objVarBFmtReg = new SelectTabBFmt();
	$objVarBFmtReg->setFADJsEvent('onChange','vars_reg');

	$objVarBFmtReg->setSelectWaitingText($strSetInnerText);
	$objVarBFmtReg->setFADNoOptionMessageText($strSetInnerText);
	$objOTForReg = new OutputType(new ReqTabHFmt(), $objVarBFmtReg);
	$objOTForReg->setFunctionForGetFADSelectList($objFunction02);

	$c->setOutputType('update_table',$objOTForUpd);
	$c->setOutputType('register_table',$objOTForReg);

	$c->setJournalTableOfMaster('D_ANSTWR_PTN_VARS_LINK_JNL');
	$c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c->setJournalKeyIDOfMaster('VARS_LINK_ID');
	$c->setJournalDispIDOfMaster('VARS_PULLDOWN');

	// $c->setRequired(true);//登録/更新時には、入力必須
	$c->setRequired(false); // 必須チェックは組合せバリデータで行う。

	//コンテンツのソースがヴューの場合、登録/更新の対象とする
	$c->setHiddenMainTableColumn(true);

	//エクセル/CSVからのアップロードを禁止する。
	$c->setAllowSendFromFile(false);

	// REST/excel/csvで項目無効
	$c->getOutputType('excel')->setVisible(false);
	$c->getOutputType('csv')->setVisible(false);
	$c->getOutputType('json')->setVisible(false);

	// データベース更新前のファンクション登録
	$c->setFunctionForEvent('beforeTableIUDAction', $tmpObjFunction);
	// 2018/05/28 #3084 Update End ----

	$table->addColumn($c);

	unset($objFunction01);
	unset($objFunction02);
	unset($objFunction03);


	/* メンバー変数名 */
	$objFunction01 = function($objOutputType, $aryVariant, $arySetting, $aryOverride, $objColumn){

		global $g;
		$retBool = false;
		$intErrorType = null;
		$aryErrMsgBody = array();
		$strErrMsg = "";
		$aryDataSet = array();
		$aryAddResultData = array();
		$strFxName = "";
		$strVarsLinkIdNumeric = $aryVariant['VARS_LINK_ID'];
		$strColSeqCombinationId = $aryVariant['NESTEDMEM_COL_CMB_ID'];

		/* <START> 親変数かどうか、を調べる------------------------------------------------------------------------------------------------- */
		$intVarType = -1;
		if( 0 < strlen($strVarsLinkIdNumeric) ){
			$strQuery
			 = " 			SELECT"
			  ." 			   TAB_1.VARS_LINK_ID"
			  ." 			  ,TAB_2.VARS_ATTR_ID"
			  ." 			FROM"
			  ." 			   D_ANSTWR_PTN_VARS_LINK AS TAB_1"
			  ." 			LEFT JOIN"
			  ." 			   B_ANSTWR_VARS AS TAB_2"
			  ." 			 ON"
			  ." 			   TAB_1.VARS_ID = TAB_2.VARS_ID"
			  ." 			WHERE "
			  ." 			   TAB_1.DISUSE_FLAG IN ('0')"
			  ." 			AND"
			  ." 			   TAB_1.VARS_LINK_ID = :VARS_LINK_ID"
			;
			/* クエリーバインド */
			$aryForBind['VARS_LINK_ID'] = $strVarsLinkIdNumeric;

			$aryRetBody = singleSQLExecuteAgent($strQuery, $aryForBind, $strFxName);

			if( $aryRetBody[0] === true ){
				$objQuery = $aryRetBody[1];
				$tmpAryRow = array();

				while($row = $objQuery->resultFetch()){ $tmpAryRow[]= $row ; }

				if( count($tmpAryRow) === 1 ){
					$tmpRow = $tmpAryRow[0];
					/* 子どもの場合（その１） */
					if(1 == $tmpRow['VARS_ATTR_ID']){
						$intVarType = 0;
						$aryAddResultData[] = "NORMAL_VAR_0";
					}
					/* 子どもの場合（その２） */
					else if(2 == $tmpRow['VARS_ATTR_ID']){
						$intVarType = 0;
						$aryAddResultData[] = "NORMAL_VAR_1";
					}
					/* 親の場合 */
					else if(3 == $tmpRow['VARS_ATTR_ID']){
						$intVarType = 1;
						$aryAddResultData[] = "PARENT_VAR";
					}
					else{
						$intErrorType = 501;
					}
				}
				else{
					$intErrorType = 502;
				}
				unset($objQuery);
			}
			else{
				$intErrorType = 503;
			}
		}
		/* < END > 親変数かどうか、を調べる------------------------------------------------------------------------------------------------- */

		/* <START> 親変数だった場合、リストを作成する--------------------------------------------------------------------------------------- */
		if( $intVarType === 1 ){
			$strQuery
			 = " 			SELECT "
			  ." 			   TAB_1.NESTEDMEM_COL_CMB_ID AS KEY_COLUMN"
			  ." 			  ,TAB_1.COL_COMBINATION_MEMBER_ALIAS AS DISP_COLUMN"
			  ." 			FROM"
			  ." 			   B_ANSTWR_NESTEDMEM_COL_CMB AS TAB_1"
			  ." 			LEFT JOIN"
			  ." 			   D_ANSTWR_PTN_VARS_LINK AS TAB_2"
			  ." 			 ON"
			  ." 			   TAB_1.VARS_ID = TAB_2.VARS_ID"
			  ." 			WHERE "
			  ." 			   TAB_1.DISUSE_FLAG IN ('0')"
			  ." 			AND"
			  ." 			   TAB_2.DISUSE_FLAG IN ('0')"
			  ." 			AND"
			  ." 			   TAB_2.VARS_LINK_ID = :VARS_LINK_ID"
			  ." 			ORDER BY"
			  ." 			   TAB_1.VARS_ID"
			  ." 			  ,TAB_1.COL_COMBINATION_MEMBER_ALIAS"
			;
			/* クエリーバインド */
			$aryForBind['VARS_LINK_ID'] = $strVarsLinkIdNumeric;

			if( strlen($strVarsLinkIdNumeric > 0) ){
				$aryRetBody = singleSQLExecuteAgent($strQuery, $aryForBind, $strFxName);
				if( $aryRetBody[0] === true ){
					$objQuery = $aryRetBody[1];
					while($row = $objQuery -> resultFetch() ){ $aryDataSet[] = $row ; }
					unset($objQuery);
					$retBool = true;
				}
				else{
					$intErrorType = 504;
				}
			}
			if(
				$tmpRow['VARS_ATTR_ID'] == 3
			  AND
				strlen($strColSeqCombinationId) > 0
			){

				/* function名を一意にする */
				unset($aryAddResultData);
				$aryValueMemverResult = array();
				$aryValueMemverResult = getChildVars_vars_assign_2100140011($strVarsLinkIdNumeric, $strColSeqCombinationId);

				/* 代入順序の有無（画面上の入力欄を活性させるか否や）を判定する */
				if(
					gettype($aryValueMemverResult) === "array"
				  AND
					count($aryValueMemverResult) === 1
				){
					if( $aryValueMemverResult[0]['VARS_LINK_ID'] == $strVarsLinkIdNumeric){
						if($aryValueMemverResult[0]['ASSIGN_SEQ_NEED'] == 1){
							$aryAddResultData[0] = "MEMBER_VAR_1"; // 活性化させるのはこのパターンのみ
						}
						else{
							$aryAddResultData[0] = "MEMBER_VAR_0";
						}
					}
					else{
						$aryAddResultData[0] = "MEMBER_VAR_323";
					}
				}
				else if(false === $aryValueMemverResult){
					$aryAddResultData[0] = "MEMBER_VAR_505";
					$intErrorType = 505;
				}
				else{
					$aryAddResultData[0] = "MEMBER_VAR_126";
				}
			}
		}
		/* < END > 親変数だった場合、リストを作成する--------------------------------------------------------------------------------------- */

		$retArray = array($retBool,$intErrorType,$aryErrMsgBody,$strErrMsg,$aryDataSet,$aryAddResultData);

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

		$strVarsLinkIdNumeric = $rowData['VARS_LINK_ID'];
		$strQuery
		 = " 			SELECT "
		  ." 			   TAB_1.NESTEDMEM_COL_CMB_ID AS KEY_COLUMN "
		  ." 			  ,TAB_1.COL_COMBINATION_MEMBER_ALIAS AS DISP_COLUMN "
		  ." 			FROM"
		  ." 			   B_ANSTWR_NESTEDMEM_COL_CMB AS TAB_1 "
		  ." 			LEFT JOIN"
		  ." 			   D_ANSTWR_PTN_VARS_LINK AS TAB_2"
		  ." 			 ON"
		  ." 			   TAB_1.VARS_ID = TAB_2.VARS_ID"
		  ." 			WHERE"
		  ." 			   TAB_1.DISUSE_FLAG IN ('0')"
		  ." 			AND"
		  ." 			   TAB_2.DISUSE_FLAG IN ('0')"
		  ." 			AND"
		  ." 			   TAB_2.VARS_LINK_ID = :VARS_LINK_ID"
		  ." 			ORDER BY"
		  ." 			   TAB_1.VARS_ID"
		  ." 			  ,TAB_1.COL_COMBINATION_MEMBER_ALIAS"
		;

		$aryForBind['VARS_LINK_ID'] = $strVarsLinkIdNumeric;

		if( 0 < strlen($strVarsLinkIdNumeric) ){
			$aryRetBody = singleSQLExecuteAgent($strQuery, $aryForBind, $strFxName);
			if( $aryRetBody[0] === true ){
				$objQuery = $aryRetBody[1];
				while($row = $objQuery->resultFetch() ){
					$aryDataSet[$row['KEY_COLUMN']]= $row['DISP_COLUMN'];
				}
				unset($objQuery);
				$retBool = true;
			}else{
				$intErrorType = 501;
			}
		}
		$aryRetBody = array($retBool, $intErrorType, $aryErrMsgBody, $strErrMsg, $aryDataSet);
		return $aryRetBody;
	};

	$objFunction04 = function($objCellFormatter, $arraySelectElement,$data,$boolWhiteKeyAdd,$varAddResultData,&$aryVariant,&$arySetting,&$aryOverride){
		global $g;
		$aryRetBody = array();
		$intErrorType = null;
		$aryErrMsgBody = array();
		$strErrMsg = "";

		//入力不要
		$strMsgBody01 = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7302205");

		$strOptionBodies = "";
		$strNoOptionMessageText = "";

		$strHiddenInputBody = "<input type=\"hidden\" name=\"".$objCellFormatter->getFSTNameForIdentify()."\" value=\"\"/>";

		$strNoOptionMessageText = $strHiddenInputBody.$objCellFormatter->getFADNoOptionMessageText();
		//条件付き必須なので、出現するときは、空白選択させない
		$boolWhiteKeyAdd = false;

		if( is_array($varAddResultData) === true ){
			if( array_key_exists(0,$varAddResultData) === true ){
				if(in_array($varAddResultData[0], array("PARENT_VAR"))){
					$strOptionBodies = makeSelectOption($arraySelectElement, $data, $boolWhiteKeyAdd, "", true);
				}else if(in_array($varAddResultData[0], array("NORMAL_VAR_0", "NORMAL_VAR_1"))){
					$strNoOptionMessageText = $strHiddenInputBody.$strMsgBody01;
				}
			}
		}
		$aryRetBody['optionBodies'] = $strOptionBodies;
		$aryRetBody['NoOptionMessageText'] = $strNoOptionMessageText;
		$retArray = array($aryRetBody,$intErrorType,$aryErrMsgBody,$strErrMsg);
		return $retArray;
	};

	$objFunction05 = function($objCellFormatter, $arraySelectElement,$data,$boolWhiteKeyAdd,$rowData,$aryVariant){
		global $g;
		$aryRetBody = array();
		$intErrorType = null;
		$aryErrMsgBody = array();
		$strErrMsg = "";

		//入力不要
		$strMsgBody01 = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7302206");

		$strOptionBodies = "";
		$strNoOptionMessageText = "";

		$strHiddenInputBody = "<input type=\"hidden\" name=\"".$objCellFormatter->getFSTNameForIdentify()."\" value=\"\"/>";

		$strNoOptionMessageText = $strHiddenInputBody.$objCellFormatter->getFADNoOptionMessageText();

		//条件付き必須なので、出現するときは、空白選択させない
		$boolWhiteKeyAdd = false;

		$strFxName = "";

		$aryAddResultData = array();

		$strVarsLinkIdNumeric = $rowData['VARS_LINK_ID'];

		/* <START> 親変数かどうか、を調べる------------------------------------------------------------------------------------------------- */
		$intVarType = -1;
		if( 0 < strlen($strVarsLinkIdNumeric) ){
			$strQuery
			 = " 			SELECT"
			  ." 			   TAB_1.VARS_LINK_ID"
			  ." 			  ,TAB_2.VARS_ATTR_ID"
			  ." 			FROM"
			  ." 			   D_ANSTWR_PTN_VARS_LINK AS TAB_1"
			  ." 			LEFT JOIN"
			  ." 			   B_ANSTWR_VARS AS TAB_2"
			  ." 			 ON"
			  ." 			   TAB_1.VARS_ID = TAB_2.VARS_ID"
			  ." 			WHERE "
			  ." 			   TAB_1.DISUSE_FLAG IN ('0')"
			  ." 			AND"
			  ." 			   TAB_1.VARS_LINK_ID = :VARS_LINK_ID"
			;
			/* クエリーバインド */
			$aryForBind['VARS_LINK_ID'] = $strVarsLinkIdNumeric;

			$aryRetBody = singleSQLExecuteAgent($strQuery, $aryForBind, $strFxName);
			if( $aryRetBody[0] === true ){
				$objQuery = $aryRetBody[1];

				$tmpAryRow = array();
				while($row = $objQuery->resultFetch() ){
					$tmpAryRow[]= $row;
				}
				if( count($tmpAryRow) === 1 ){
					$tmpRow = $tmpAryRow[0];
					if(3 == $tmpRow['VARS_ATTR_ID']){
						$intVarType = 1;
					}
					else {
						$intVarType = 0;
					}
			   }else{
					$intErrorType = 502;
				}
				unset($tmpRow);
				unset($tmpAryRow);
				unset($objQuery);
			}else{
				$intErrorType = 503;
			}
		}
		/* < END > 親変数かどうか、を調べる------------------------------------------------------------------------------------------------- */

		if( $intVarType == 1 ){
			$strOptionBodies = makeSelectOption($arraySelectElement, $data, $boolWhiteKeyAdd, "", true);
		}else if( $intVarType === 0 ){
			$strNoOptionMessageText = $strHiddenInputBody.$strMsgBody01;
		}
		$aryRetBody['optionBodies'] = $strOptionBodies;
		$aryRetBody['NoOptionMessageText'] = $strNoOptionMessageText;
		$retArray = array($aryRetBody,$intErrorType,$aryErrMsgBody,$strErrMsg);

		return $retArray;
	};

	// RestAPI/Excel/CSVからの登録の場合に組み合わせバリデータで退避したNESTEDMEM_COL_CMB_IDを設定する。
	$tmpObjFunction = function($objColumn, $strEventKey, &$exeQueryData, &$reqOrgData=array(), &$aryVariant=array()){

		global $g;

		$boolRet = true;
		$intErrorType = null;
		$aryErrMsgBody = array();
		$strErrMsg = "";
		$strErrorBuf = "";

		$modeValue = $aryVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_MODE"];
		if($modeValue=="DTUP_singleRecRegister" || $modeValue=="DTUP_singleRecUpdate") {
			if(strlen($g['NESTEDMEM_COL_CMB_ID_UPDATE_VALUE']) !== 0) {
				$exeQueryData[$objColumn->getID()] = $g['NESTEDMEM_COL_CMB_ID_UPDATE_VALUE'];
			}
		} else if($modeValue=="DTUP_singleRecDelete") {
		}
		$retArray = array($boolRet,$intErrorType,$aryErrMsgBody,$strErrMsg,$strErrorBuf);
		return $retArray;
	};

	$c = new IDColumn('NESTEDMEM_COL_CMB_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7392206"),'B_ANSTWR_NESTEDMEM_COL_CMB','NESTEDMEM_COL_CMB_ID','COL_COMBINATION_MEMBER_ALIAS','',array('ORDER'=>'ORDER BY VARS_ID, COL_COMBINATION_MEMBER_ALIAS'));
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7352206")); //エクセル・ヘッダでの説明

	$strSetInnerText = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7302207") ; // '変数名を選択して下さい'
	$objVarBFmtUpd = new SelectTabBFmt();

	/* 該当変数のデフォルトを表示する為のトリガー設定 */
	$objVarBFmtUpd->setFADJsEvent('onChange', 'default_val_upd');

	$objVarBFmtUpd->setNoOptionMessageText($strSetInnerText);
	$objVarBFmtUpd->setFADNoOptionMessageText($strSetInnerText);
	$objVarBFmtUpd->setFunctionForGetSelectList($objFunction03);
	$objVarBFmtUpd->setFunctionForGetFADMainDataOverride($objFunction04);
	$objVarBFmtUpd->setFunctionForGetMainDataOverride($objFunction05);

	$objOTForUpd = new OutputType(new ReqTabHFmt(), $objVarBFmtUpd);
	$objOTForUpd->setJsEvent('onChange','default_val_upd');

	$objOTForUpd->setFunctionForGetFADSelectList($objFunction01);

	/* (新規登録のみ）該当変数の具体値を表示する為のトリガー設定 */
	$objVarBFmtReg = new SelectTabBFmt();
	$objVarBFmtReg->setFADJsEvent('onChange', 'default_val_reg');

	/* DB出力および他状態を使って生成される論理情報を基に、変数に格納された文字列を整形する */
	$objVarBFmtReg->setSelectWaitingText($strSetInnerText);
	$objVarBFmtReg->setFADNoOptionMessageText($strSetInnerText);
	$objVarBFmtReg->setFunctionForGetFADMainDataOverride($objFunction04);

	/* DBから出力された論理情報を、最終出力形式への加工工程前に、事前に加工する */
	$objOTForReg = new OutputType(new ReqTabHFmt(), $objVarBFmtReg);
	$objOTForReg->setFunctionForGetFADSelectList($objFunction02);

	/* 本Functionが有効となる処理区分を定義する */
	$c->setOutputType('update_table',$objOTForUpd); // 更新時
	$c->setOutputType('register_table',$objOTForReg); // 登録時

	$c->setJournalTableOfMaster('B_ANSTWR_NESTEDMEM_COL_CMB_JNL');
	$c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c->setJournalKeyIDOfMaster('NESTEDMEM_COL_CMB_ID');
	$c->setJournalDispIDOfMaster('COL_COMBINATION_MEMBER_ALIAS');

	$c->setRequired(false); // 必須チェックは組合せバリデータで行う。

	//コンテンツのソースがヴューの場合、登録/更新の対象とする
	$c->setHiddenMainTableColumn(true);

	//エクセル/CSVからのアップロードを禁止する。
	$c->setAllowSendFromFile(false);

	// REST/excel/csvで項目無効
	$c->getOutputType('excel')->setVisible(false);
	$c->getOutputType('csv')->setVisible(false);
	$c->getOutputType('json')->setVisible(false);

	// データベース更新前のファンクション登録
	$c->setFunctionForEvent('beforeTableIUDAction', $tmpObjFunction);

	$table->addColumn($c);

	unset($objFunction01);
	unset($objFunction02);
	unset($objFunction03);
	unset($objFunction04);
	unset($objFunction05);


	/* 具体値 */
    $objVldt = new SingleTextValidator(0,1024,false);
	$c = new TextColumn('VARS_ENTRY',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7392207"));
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7352207"));//エクセル・ヘッダでの説明
	$c->setValidator($objVldt);
    $c->setValidator($objVldt);
    $c->setRequired(false);     //登録/更新時には、任意入力

	//コンテンツのソースがヴューの場合、登録/更新の対象とする
	$c->setHiddenMainTableColumn(true);

	$table->addColumn($c);


	/* 代入順序 */
	/* <START> 条件別に代入順序の入力制限をする--------------------------------------------------------------------------------------------- */
	$objFunction01 = function($strTagInnerBody,$objCellFormatter,$rowData,$aryVariant,$aryAddOnDefault,$aryOverWrite){
		global $g;

		/* 変数が一般変数の場合、代入順序の欄は入力不可とし、`入力不要`のメッセージを表示する */
		$strMsgBody01 = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7302208");
		list($strVarsLinkIdNumeric,$tmpBoolKeyExist) = isSetInArrayNestThenAssign($rowData,array('VARS_LINK_ID'),null);
		$strFxName = "";

		/* <START> 親変数かどうか、を調べる------------------------------------------------------------------------------------------------- */
		$intVarType = -1;
		if( 0 < strlen($strVarsLinkIdNumeric) ){
			
			$strQuery
			 = " 			SELECT"
			  ." 			   TAB_1.VARS_LINK_ID"
			  ." 			  ,TAB_2.VARS_ATTR_ID"
			  ." 			FROM"
			  ." 			   D_ANSTWR_PTN_VARS_LINK AS TAB_1"
			  ." 			LEFT JOIN"
			  ." 			   B_ANSTWR_VARS AS TAB_2"
			  ." 			 ON"
			  ." 			   TAB_1.VARS_ID = TAB_2.VARS_ID"
			  ." 			WHERE "
			  ." 			   TAB_1.DISUSE_FLAG IN ('0')"
			  ." 			AND"
			  ." 			   TAB_1.VARS_LINK_ID = :VARS_LINK_ID"
			;
			/* クエリーバインド */
			$aryForBind['VARS_LINK_ID'] = $strVarsLinkIdNumeric;

			$aryRetBody = singleSQLExecuteAgent($strQuery, $aryForBind, $strFxName);
			if( $aryRetBody[0] === true ){
				$objQuery = $aryRetBody[1];
				$tmpAryRow = array();

				while($row = $objQuery->resultFetch() ){
					$tmpAryRow[]= $row;
				}
				if( count($tmpAryRow) === 1 ){
					$tmpRow = $tmpAryRow[0];
					if(2 == $tmpRow['VARS_ATTR_ID']){
						$intVarType = 1;
					}
					else if(3 == $tmpRow['VARS_ATTR_ID']){
						if(0 < strlen($rowData['ASSIGN_SEQ'])){
							$intVarType = 1;
						}
						else{
							$intVarType = 0;
						}
					}
					else{
						$intVarType = 0;
					}
				}else{
					$intErrorType = 502;
				}

				unset($tmpRow);
				unset($tmpAryRow);
				unset($objQuery);
			}else{
				$intErrorType = 503;
			}
		}
		/* < END > 親変数かどうか、を調べる------------------------------------------------------------------------------------------------- */

		/* 親変数ではない場合 */
		if( $intVarType !== 1 ){ $aryOverWrite["value"] = "" ; }

		$retBody = "<input {$objCellFormatter->printAttrs($aryAddOnDefault,$aryOverWrite)} {$objCellFormatter->printJsAttrs($rowData)} {$objCellFormatter->getTextTagLastAttr()}>";
		$retBody = $retBody."<div style=\"display:none\" id=\"after_".$objCellFormatter->getFSTIDForIdentify()."\">".$strMsgBody01."</div><br/>";
		$retBody = $retBody."<div style=\"display:none\" id=\"init_var_type_".$objCellFormatter->getFSTIDForIdentify()."\">".$intVarType."</div>";

		return $retBody;
	};

	$objFunction02 = $objFunction01;

	$objVarBFmtUpd = new NumInputTabBFmt(0,false);
	$objVarBFmtUpd->setFunctionForReturnOverrideGetData($objFunction01);
	
	$objVarBFmtReg = new NumInputTabBFmt(0,false);
	$objVarBFmtReg->setFunctionForReturnOverrideGetData($objFunction02);

	$objOTForUpd = new OutputType(new ReqTabHFmt(), $objVarBFmtUpd);
	$objOTForReg = new OutputType(new ReqTabHFmt(), $objVarBFmtReg);
	/* < END > 条件別に代入順序の入力制限をする--------------------------------------------------------------------------------------------- */

	$c = new NumColumn('ASSIGN_SEQ',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7392208"));
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7352208"));//エクセル・ヘッダでの説明
	$c->setOutputType('update_table',$objOTForUpd);
	$c->setOutputType('register_table',$objOTForReg); 
	$c->setSubtotalFlag(false);
	$c->setValidator(new IntNumValidator(1,null));

	//コンテンツのソースがヴューの場合、登録/更新の対象とする
	$c->setHiddenMainTableColumn(true);

	$table->addColumn($c);


	/* デフォルト値 */
	$c = new Column('VAR_VALUE',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7392209"));
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7352209"));//エクセル・ヘッダでの説明
	$c->setDBColumn(false);
	$c->getOutputType('filter_table')->setVisible(false);
	$c->getOutputType('print_table')->setVisible(false);
	$c->setOutputType('update_table',new OutputType(new ReqTabHFmt(), new StaticTextTabBFmt('<div id="default_upd" style="width: 200px; word-wrap:break-word; white-space:pre-wrap;" ></div>')));
	$c->setOutputType('register_table',new OutputType(new ReqTabHFmt(), new StaticTextTabBFmt('<div id="default_reg" style="width: 200px; word-wrap:break-word; white-space:pre-wrap;" ></div>')));
	$c->getOutputType('delete_table')->setVisible(false);
	$c->getOutputType('excel')->setVisible(false);
	$c->getOutputType('csv')->setVisible(false);
	$c->getOutputType('json')->setVisible(false);	
	$table->addColumn($c);

	/* カラムを確定させる */
	$table->fixColumn();

	/* 組み合わせバリデータ */
	$tmpAryColumn = $table->getColumns();
	$objLU4UColumn = $tmpAryColumn[$table->getRequiredUpdateDate4UColumnID()];

	$objFunction = function($objClientValidator, $value, $strNumberForRI, $arrayRegData, $arrayVariant){
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

		if($strModeId == "DTUP_singleRecDelete"){
			//----更新前のレコードから、各カラムの値を取得
			$intOperationNoUAPK     = isset($arrayVariant['edit_target_row']['OPERATION_NO_UAPK'])
										 ? $arrayVariant['edit_target_row']['OPERATION_NO_UAPK']
										 : null;
			$intPatternId           = isset($arrayVariant['edit_target_row']['PATTERN_ID'])
										 ? $arrayVariant['edit_target_row']['PATTERN_ID']
										 : null;
			$intSystemId            = isset($arrayVariant['edit_target_row']['SYSTEM_ID'])
										 ? $arrayVariant['edit_target_row']['SYSTEM_ID']
										 : null;
			$intVarsLinkId          = isset($arrayVariant['edit_target_row']['VARS_LINK_ID'])
										 ? $arrayVariant['edit_target_row']['VARS_LINK_ID']
										 : null;
			$intColSeqCombId        = isset($arrayVariant['edit_target_row']['NESTEDMEM_COL_CMB_ID'])
										 ? $arrayVariant['edit_target_row']['NESTEDMEM_COL_CMB_ID']
										 : null;
			$intSeqOfAssign         = isset($arrayVariant['edit_target_row']['ASSIGN_SEQ'])
										 ? $arrayVariant['edit_target_row']['ASSIGN_SEQ']
										 : null;
			$intRestVarsLinkId      = isset($arrayVariant['edit_target_row']['REST_VARS_LINK_ID'])
										 ? $arrayVariant['edit_target_row']['REST_VARS_LINK_ID']
										 : null;
			$intRestSystemId        = isset($arrayVariant['edit_target_row']['REST_SYSTEM_ID'])
										 ? $arrayVariant['edit_target_row']['REST_SYSTEM_ID']
										 : null;
			$intRestColSeqCombId    = isset($arrayVariant['edit_target_row']['REST_NESTEDMEM_COL_CMB_ID'])
										 ? $arrayVariant['edit_target_row']['REST_NESTEDMEM_COL_CMB_ID']
										 : null;

			$modeValue_sub = $arrayVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_SUB_MODE"];//['mode_sub'];("on"/"off")
			if( $modeValue_sub == "on" ){
				//----廃止の場合はチェックしない
				$boolExecuteContinue = false;
				//廃止の場合はチェックしない----
			}else{
				//----復活の場合
				if( strlen($intOperationNoUAPK) === 0 || strlen($intPatternId) === 0 ||  strlen($intSystemId) === 0 || strlen($intVarsLinkId) === 0 ){
					$boolSystemErrorFlag = true;
				}
				//復活の場合----
			}
			// 廃止からの復活で画面フリーズを防ぐ為、Pkey退避
			$intAssignId = $strNumberForRI;

			//更新前のレコードから、各カラムの値を取得----
		}else if( $strModeId == "DTUP_singleRecUpdate" || $strModeId == "DTUP_singleRecRegister" ){
			$intOperationNoUAPK     = array_key_exists('OPERATION_NO_UAPK',$arrayRegData)
									 ? $arrayRegData['OPERATION_NO_UAPK']
									 : null;
			$intPatternId           = array_key_exists('PATTERN_ID',$arrayRegData)
									 ? $arrayRegData['PATTERN_ID']
									 : null;
			$intSystemId            = array_key_exists('SYSTEM_ID',$arrayRegData)
									 ? $arrayRegData['SYSTEM_ID']
									 : null;
			$intVarsLinkId          = array_key_exists('VARS_LINK_ID',$arrayRegData)
									 ? $arrayRegData['VARS_LINK_ID']
									 : null;
			$intColSeqCombId        = array_key_exists('NESTEDMEM_COL_CMB_ID',$arrayRegData)
									 ? $arrayRegData['NESTEDMEM_COL_CMB_ID']
									 : null;
			$intSeqOfAssign         = array_key_exists('ASSIGN_SEQ',$arrayRegData)
									 ? $arrayRegData['ASSIGN_SEQ']
									 : null;
			$intRestVarsLinkId      = array_key_exists('REST_VARS_LINK_ID',$arrayRegData)
									 ? $arrayRegData['REST_VARS_LINK_ID']
									 : null;
			$intRestSystemId        = array_key_exists('REST_SYSTEM_ID',$arrayRegData)
									 ? $arrayRegData['REST_SYSTEM_ID']
									 : null;
			$intRestColSeqCombId    = array_key_exists('REST_NESTEDMEM_COL_CMB_ID',$arrayRegData)
									 ? $arrayRegData['REST_NESTEDMEM_COL_CMB_ID']
									 : null;

			// 主キーの値を取得する。
			if( $strModeId == "DTUP_singleRecUpdate" ){
				// 更新処理の場合
				$intAssignId = $strNumberForRI;
			}
			else{
				// 登録処理の場合
				$intAssignId = array_key_exists('VARS_ASSIGN_ID',$arrayRegData)?$arrayRegData['VARS_ASSIGN_ID']:null;
			}
		}

		// if( strlen($intOperationNoUAPK) === 0 || strlen($intPatternId) === 0 ||  strlen($intSystemId) === 0 || strlen($intVarsLinkId) === 0 ){
		// 	$boolExecuteContinue = false;
		// }

		$g['PATTERN_ID_UPDATE_VALUE']        = "";
		$g['VARS_LINK_ID_UPDATE_VALUE']      = "";
		$rest_call = false;
		//----呼出元がUIがRestAPI/Excel/CSVかを判定
		// PATTERN_ID;未設定 VARS_LINK_ID:未設定 REST_VARS_LINK_ID:設定 => RestAPI/Excel/CSV
		// その他はUI
		if( $boolExecuteContinue === true && $boolSystemErrorFlag === false){
			if((strlen($intPatternId)          === 0) &&
			   (strlen($intVarsLinkId)         === 0) &&
			   (strlen($intRestVarsLinkId)     !== 0)){
				$rest_call = true;
				$query =  "SELECT                                             "
						 ."  TBL_A.VARS_LINK_ID,                              "
						 ."  TBL_A.PATTERN_ID,                                "
						 ."  COUNT(*) AS VARS_LINK_ID_CNT                     "
						 ."FROM                                               "
						 ."  D_ANSTWR_PTN_VARS_LINK TBL_A                     " //モード毎
						 ."WHERE                                              "
						 ."  TBL_A.VARS_LINK_ID    = :VARS_LINK_ID   AND      "
						 ."  TBL_A.DISUSE_FLAG     = '0'                      ";
				$aryForBind = array();
				$aryForBind['VARS_LINK_ID'] = $intRestVarsLinkId;
				$retArray = singleSQLExecuteAgent($query, $aryForBind, "NONAME_FUNC(VARS_MULTI_CHECK)");
				if( $retArray[0] === true ){
					$objQuery =& $retArray[1];
					$intCount = 0;
					$row = $objQuery->resultFetch();
					if( $row['VARS_LINK_ID_CNT'] == '1' ){
						$intVarsLinkId					 = $row['VARS_LINK_ID'];
						$intPatternId					  = $row['PATTERN_ID'];
						$g['PATTERN_ID_UPDATE_VALUE']	  = $intPatternId;
						$g['VARS_LINK_ID_UPDATE_VALUE']	= $intVarsLinkId;
					}else if( $row['VARS_LINK_ID_CNT'] == '0' ){
						//$ary[6602201] = "Movement詳細に登録されているロールまたはロールパッケージに変数が>未登録です。";
						$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-6602201");
						$retBool = false;
						$boolExecuteContinue = false;
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
		}
		$g['SYSTEM_ID_UPDATE_VALUE']        = "";
		//----呼出元がUIがRestAPI/Excel/CSVかを判定
		// SYSTEM_ID;未設定 REST_SYSTEM_ID:設定 => RestAPI/Excel/CSV
		// その他はUI
		if( $boolExecuteContinue === true && $boolSystemErrorFlag === false){
			if((strlen($intSystemId)         === 0) &&
			   (strlen($intRestSystemId)     !== 0)){
				$retBool = false;
				$boolExecuteContinue = false;
				$query = "SELECT "
						 ."   COUNT(*) AS HOST_CNT "
						 ."FROM "
						 ."   C_STM_LIST TBL_A  "
						 ." WHERE "
						 ."   TBL_A.SYSTEM_ID	= :SYSTEM_ID AND "
						 ."   TBL_A.DISUSE_FLAG  = '0' ";

				$aryForBind = array();
				$aryForBind['SYSTEM_ID']	 = $intRestSystemId;
				$retArray = singleSQLExecuteAgent($query, $aryForBind, "NONAME_FUNC(VARS_MULTI_CHECK)");
				if( $retArray[0] === true ){
					$objQuery =& $retArray[1];
					$intCount = 0;
					$row = $objQuery->resultFetch();
					if( $row['HOST_CNT'] == '1' ){
						$intSystemId                 = $intRestSystemId;
						$g['SYSTEM_ID_UPDATE_VALUE'] = $intRestSystemId;
						$retBool = true;
						$boolExecuteContinue = true;
					}else if( $row['HOST_CNT'] == '0' ){
						$boolExecuteContinue = false;
						//$ary[6602202] = "ホストが未登録です。";
						$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-6602202");
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
		}
		$g['NESTEDMEM_COL_CMB_ID_UPDATE_VALUE'] = "";
		//----呼出元がUIがRestAPI/Excel/CSVかを判定
		// NESTEDMEM_COL_CMB_ID;未設定 REST_NESTEDMEM_COL_CMB_ID:設定 => RestAPI/Excel/CSV
		// その他はUI  
		// REST_NESTEDMEM_COL_CMB_ID未入力のケースがあるのでMovemwnt+変数の入力有無で判定する。
		//呼出元がUIがRestAPI/Excel/CSVかを判定----
		if($rest_call === true){
			$intColSeqCombId                          = $intRestColSeqCombId;
			$g['NESTEDMEM_COL_CMB_ID_UPDATE_VALUE']   = $intColSeqCombId;
		}

		//----作業パターンのチェック
		if( $boolExecuteContinue === true && $boolSystemErrorFlag === false){
			$retBool = false;
			$boolExecuteContinue = false;
			$query =  " SELECT "
					 ."   COUNT(*) AS PATTERN_CNT "
					 ." FROM "
					 ."   $pattan_tbl TBL_A  "
					 ." WHERE "
					 ."   TBL_A.PATTERN_ID   = :PATTERN_ID   AND "
					 ."   TBL_A.DISUSE_FLAG  = '0' ";

			$aryForBind = array();
			$aryForBind['PATTERN_ID']	 = $intPatternId;
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
					$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-6602203");
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

		//----必須入力チェック
		if( $boolExecuteContinue === true && $boolSystemErrorFlag === false){
			if( strlen($intPatternId) === 0 ){
				$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-6602204");
				$boolExecuteContinue = false;
				$retBool = false;
			}
			else if( strlen($intSystemId) === 0 ){
				$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-6602205");
				$boolExecuteContinue = false;
				$retBool = false;
			}
			else if( strlen($intVarsLinkId) === 0 ){
				$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-6602206");
				$boolExecuteContinue = false;
				$retBool = false;
			}
		}

		//----オペレーションから変数名までの、組み合わせチェック
		if( $boolExecuteContinue === true && $boolSystemErrorFlag === false){
			$query
			 = " 		SELECT "
			." 			   COUNT(*) REC_COUNT"
			." 			FROM "
			." 			   B_ANSTWR_PHO_LINK AS TAB_1"
			." 			LEFT JOIN"
			." 			   D_ANSTWR_PTN_VARS_LINK AS TAB_2"
			." 			 ON"
			." 			   TAB_1.PATTERN_ID = TAB_2.PATTERN_ID"
			." 			WHERE "
			." 			   TAB_1.DISUSE_FLAG IN ('0')"
			." 			AND"
			." 			   TAB_2.DISUSE_FLAG IN ('0')"
			." 			AND"
			." 			   TAB_1.OPERATION_NO_UAPK = :OPERATION_NO_UAPK"
			." 			AND"
			." 			   TAB_1.PATTERN_ID = :PATTERN_ID"
			." 			AND"
			." 			   TAB_1.SYSTEM_ID = :SYSTEM_ID"
			." 			AND"
			." 			   TAB_2.VARS_LINK_ID = :VARS_LINK_ID";

			$aryForBind = array();
			$aryForBind['OPERATION_NO_UAPK'] = $intOperationNoUAPK;
			$aryForBind['PATTERN_ID'] = $intPatternId;
			$aryForBind['SYSTEM_ID'] = $intSystemId;
			$aryForBind['VARS_LINK_ID'] = $intVarsLinkId;

			$retArray = singleSQLExecuteAgent($query, $aryForBind, "NONAME_FUNC(VARS_MULTI_CHECK)");
			if( $retArray[0] === true ){
				$objQuery =& $retArray[1];
				$intCount = 0;
				$row = $objQuery->resultFetch();
				if( $row['REC_COUNT'] == '1' ){
					// OK
				}else if( $row['REC_COUNT'] == '0' ){
					$retBool = false;
					$boolExecuteContinue = false;
					$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7302209");
				}else{
					$boolSystemErrorFlag = true;
				}
				unset($row);
				unset($objQuery);
			}else{
				$boolSystemErrorFlag = true;
			}
			unset($retArray);
		}
		//オペレーションから変数名までの、組み合わせチェック----

		$intVarType = -1;
		//----変数タイプを取得
		if( $boolExecuteContinue === true && $boolSystemErrorFlag === false){
			$strQuery
			 = " 			SELECT"
			  ." 			   TAB_1.VARS_LINK_ID"
			  ." 			  ,TAB_2.VARS_ATTR_ID"
			  ." 			FROM"
			  ." 			   D_ANSTWR_PTN_VARS_LINK AS TAB_1"
			  ." 			LEFT JOIN"
			  ." 			   B_ANSTWR_VARS AS TAB_2"
			  ." 			 ON"
			  ." 			   TAB_1.VARS_ID = TAB_2.VARS_ID"
			  ." 			WHERE "
			  ." 			   TAB_1.DISUSE_FLAG IN ('0')"
			  ." 			AND"
			  ." 			   TAB_1.VARS_LINK_ID = :VARS_LINK_ID"
			;
			/* クエリーバインド */
			$aryForBind = array();
			$aryForBind['VARS_LINK_ID'] = $intVarsLinkId;

			$retArray = singleSQLExecuteAgent($strQuery, $aryForBind, "NONAME_FUNC(VARS_TYPE_CHECK)");
			if( $retArray[0] === true ){
				$objQuery = $retArray[1];
				$tmpAryRow = array();
				while($row = $objQuery->resultFetch()){
					$tmpAryRow[]= $row;
				}
				if( count($tmpAryRow) === 1 ){
					$tmpRow = $tmpAryRow[0];
					if(in_array($tmpRow['VARS_ATTR_ID'], array(1, 2, 3))){
						$intVarType = $tmpRow['VARS_ATTR_ID'];
					}else{
						$boolSystemErrorFlag = true;
					}
					unset($tmpRow);
				}else{
					$boolSystemErrorFlag = true;
				}
				unset($tmpAryRow);
				unset($objQuery);
			}else{
				$boolSystemErrorFlag = true;
			}
			unset($retArray);
		}

		// 変数の種類ごとに、バリデーションチェック
		// 変数タイプが「一般変数」の場合
		if(1 == $intVarType){

			// メンバー変数名のチェック
			if( $boolExecuteContinue === true && $boolSystemErrorFlag === false ){
				if( 0 < strlen($intColSeqCombId) ){
					$retBool = false;
					$boolExecuteContinue = false;
					$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7302210");
				}
			}

			// 代入順序のチェック
			if( $boolExecuteContinue === true && $boolSystemErrorFlag === false ){
				if( 0 < strlen($intSeqOfAssign) ){
					$retBool = false;
					$boolExecuteContinue = false;
					$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7302211");
				}
			}
		}

		// 変数タイプが「複数具体値変数」の場合
		else if(2 == $intVarType){

			// メンバー変数名のチェック
			if( $boolExecuteContinue === true && $boolSystemErrorFlag === false ){
				if( 0 < strlen($intColSeqCombId) ){
					$retBool = false;
					$boolExecuteContinue = false;
					$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7302212");
				}
			}

			// 代入順序のチェック
			if( $boolExecuteContinue === true && $boolSystemErrorFlag === false ){
				if( 0 === strlen($intSeqOfAssign) ){
					$retBool = false;
					$boolExecuteContinue = false;
					$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7302213");
				}
			}
		}
		// 変数タイプが「多次元変数」の場合
		else if(3 == $intVarType){

			// メンバー変数名のチェック
			if( $boolExecuteContinue === true && $boolSystemErrorFlag === false ){
				if( 0 === strlen($intColSeqCombId) ){
					$retBool = false;
					$boolExecuteContinue = false;
					$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7302214");
				}
				else {
					// メンバー変数管理テーブル取得
					// function名を一意にする。
					$aryResult = getChildVars_vars_assign_2100140011($intVarsLinkId, $intColSeqCombId);

					if("array" === gettype($aryResult) && 1 === count($aryResult)){
						$childData = $aryResult[0];
					}
					else if("array" === gettype($aryResult) && 0 === count($aryResult)){
						$retBool = false;
						$boolExecuteContinue = false;
						$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7302215");
					}
					else{
						$boolSystemErrorFlag = true;
					}
				}
			}

			// 代入順序のチェック
			if( $boolExecuteContinue === true && $boolSystemErrorFlag === false ){
				$intAssignSeqNeed = $childData['ASSIGN_SEQ_NEED'];
				// 代入順序の有無が有の場合
				if(1 ==  $intAssignSeqNeed){
					if( 0 === strlen($intSeqOfAssign) ){
						$retBool = false;
						$boolExecuteContinue = false;
						$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7302216");
					}
				}
				// 代入順序の有無が無の場合
				else{
					if( 0 < strlen($intSeqOfAssign) ){
						$retBool = false;
						$boolExecuteContinue = false;
						$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7302217");
					}
				}
			}
		}

		// 代入値管理テーブルの重複レコードチェック
		if( $boolExecuteContinue === true && $boolSystemErrorFlag === false ){
			$strQuery
			 = " 			SELECT"
			  ." 			   VARS_ASSIGN_ID"
			  ." 			FROM"
			  ." 			   B_ANSTWR_VARS_ASSIGN"
			  ." 			WHERE"
			  ." 			   VARS_ASSIGN_ID <> :VARS_ASSIGN_ID"
			  ." 			AND"
			  ." 			   OPERATION_NO_UAPK = :OPERATION_NO_UAPK"
			  ." 			AND"
			  ." 			   PATTERN_ID = :PATTERN_ID"
			  ." 			AND"
			  ." 			   SYSTEM_ID = :SYSTEM_ID"
			  ." 			AND"
			  ." 			   VARS_LINK_ID = :VARS_LINK_ID"
			  ." 			AND"
			  ." 			   DISUSE_FLAG = '0'"
			;

			$aryForBind = array();
			$aryForBind['VARS_ASSIGN_ID'] = $intAssignId;
			$aryForBind['OPERATION_NO_UAPK'] = $intOperationNoUAPK;
			$aryForBind['PATTERN_ID'] = $intPatternId;
			$aryForBind['SYSTEM_ID'] = $intSystemId;
			$aryForBind['VARS_LINK_ID'] = $intVarsLinkId;

			// メンバー変数が必須の場合
			if(3 == $intVarType){
				$strQuery .= " AND NESTEDMEM_COL_CMB_ID = :NESTEDMEM_COL_CMB_ID ";
				$aryForBind['NESTEDMEM_COL_CMB_ID'] = $intColSeqCombId;
			}
			// 代入順序が必須の場合
			if(2 == $intVarType ||
			   (3 == $intVarType && 1 ==  $intAssignSeqNeed)){
				$strQuery .= " AND ASSIGN_SEQ = :ASSIGN_SEQ ";
				$aryForBind['ASSIGN_SEQ'] = $intSeqOfAssign;
			}

			$retArray = singleSQLExecuteAgent($strQuery, $aryForBind, "NONAME_FUNC(ARRAYVARS_DUP_CHECK)");
			if( $retArray[0] === true ){
				$objQuery = $retArray[1];
				$dupnostr = "";
				while($row = $objQuery->resultFetch() ){
					$dupnostr = $dupnostr . "[" . $row['VARS_ASSIGN_ID'] . "]";
				}
				if( strlen($dupnostr) != 0 ){
					$retBool = false;
					$boolExecuteContinue = false;
					$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7302218",array($dupnostr));
					if(3 == $intVarType){
						$retStrBody .= $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7302219",array($dupnostr));
					}
					if(2 == $intVarType ||
					   (3 == $intVarType && 1 ==  $intAssignSeqNeed)){
						$retStrBody .= $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7302220",array($dupnostr));
					}
				}
				unset($objQuery);
			}
			else{
				$boolSystemErrorFlag = true;
			}
			unset($retArray);
		}

		/* 代入値管理テーブルの重複レコードチェック */
		if( $boolExecuteContinue === true && $boolSystemErrorFlag === false ){
			$ret = CheckDefaultValueSameDefine_2100140011($g['objDBCA'], $g['objMTS'], $intPatternId, $intVarsLinkId, $intColSeqCombId, $intSeqOfAssign, $strOutputStream);
			if($ret === false)
			{
				$retBool = false;
				$boolExecuteContinue = false;
				$retStrBody = $strOutputStream;
			}
		}

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
	$objVarVali->setErrShowPrefix(false);
	$objVarVali->setFunctionForIsValid($objFunction);
	$objVarVali->setVariantForIsValid(array());

	$objLU4UColumn->addValidator($objVarVali);
	//組み合わせバリデータ----

	$table->setGeneObject('webSetting', $arrayWebSetting);
	return $table;
};
loadTableFunctionAdd($tmpFx,__FILE__);
unset($tmpFx);

/* メンバー変数管理テーブル取得 */
function getChildVars_vars_assign_2100140011($strVarsLinkIdNumeric, $strColSeqCombinationId) {

	$strQuery
	 = " 			SELECT"
	  ." 			   TBL_A.VARS_LINK_ID"
	  ." 			  ,TBL_A.VARS_ID"
	  ." 			  ,TBL_X.NESTEDMEM_COL_CMB_ID"
	  ." 			  ,TBL_X.NESTED_MEM_VARS_ID"
	  ." 			  ,TBL_Y.ASSIGN_SEQ_NEED"
	  ." 			  ,TBL_Y.COL_SEQ_NEED"
	  ." 			  ,TBL_B.VARS_ATTR_ID"
	  ." 			FROM"
	  ." 			   B_ANSTWR_PTN_VARS_LINK AS TBL_A"
	  ." 			LEFT JOIN"
	  ." 			   B_ANSTWR_NESTEDMEM_COL_CMB AS TBL_X"
	  ." 			 ON"
	  ." 			   TBL_X.VARS_ID = TBL_A.VARS_ID"
	  ." 			LEFT JOIN"
	  ." 			   B_ANSTWR_NESTED_MEM_VARS AS TBL_Y"
	  ." 			 ON"
	  ." 			   TBL_Y.NESTED_MEM_VARS_ID = TBL_X.NESTED_MEM_VARS_ID"
	  ." 			LEFT JOIN"
	  ." 			   B_ANSTWR_VARS AS TBL_B"
	  ." 			 ON"
	  ." 			   TBL_B.VARS_ID = TBL_X.VARS_ID"
	  ." 			WHERE"
	  ." 			   TBL_A.DISUSE_FLAG IN ('0')"
	  ." 			 AND"
	  ." 			   TBL_X.DISUSE_FLAG IN ('0')"
	  ." 			 AND"
	  ." 			   TBL_Y.DISUSE_FLAG IN ('0')"
	  ." 			 AND"
	  ." 			   TBL_B.DISUSE_FLAG IN ('0')"
	  ." 			 AND"
	  ." 			   TBL_A.VARS_LINK_ID = :VARS_LINK_ID"
	  ." 			 AND"
	  ." 			   TBL_X.NESTEDMEM_COL_CMB_ID = :NESTEDMEM_COL_CMB_ID" ;
	/* クエリーバインド */
	$aryForBind = array();
	$aryForBind['VARS_LINK_ID'] = $strVarsLinkIdNumeric;
	$aryForBind['NESTEDMEM_COL_CMB_ID'] = $strColSeqCombinationId;

	$retArray = singleSQLExecuteAgent($strQuery, $aryForBind, "NONAME_FUNC(VARS_RELATION_CHECK)");

	if( $retArray[0] === true ){
		$objQuery = $retArray[1];
		$tmpAryRow = array();
		while($row = $objQuery -> resultFetch()){
			$tmpAryRow[]= $row ;
		}

		return $tmpAryRow;
	}
	else{
		return false;
	}
}


// 同等の処理が02_access.phpあり。修正注意
function CheckDefaultValueSameDefine_2100140011($objDBCA, $objMTS, $objPtnID, $objVarID, $objChlVarID, $objAssSeqID, &$errmsg){
	$errmsg = "";
	
	// システム設定のデフォルト値定義のチェック区分を取得
	// 未定義または 1 以外はチェック無の扱いとす。
	$strQuery
	 = " 			SELECT"
	  ." 			   VALUE"
	  ." 			FROM"
	  ." 			   A_SYSTEM_CONFIG_LIST"
	  ." 			WHERE"
	  ." 			   CONFIG_ID   = 'ANSIBLETOWER_DEF_VAL_CHK'"
	  ." 			 AND"
	  ." 			   DISUSE_FLAG = '0'";
	
	$aryForBind = array();
	$defval_chk = "";
	$retArray = singleSQLExecuteAgent($strQuery, $aryForBind, "NONAME_FUNC(VARS_RELATION_CHECK)");
	if( $retArray[0] === true ){
		$objQuery = $retArray[1];
		while($row = $objQuery->resultFetch() ){
			$defval_chk = $row['VALUE'];
		}
		unset($objQuery);
	}else{
		$errmsg = $objMTS->getSomeMessage("ITAANSTWRH-ERR-6602207",array(basename(__FILE__),__LINE__,"ExecuteAgent"));
		web_log($errmsg);
		web_log($objQuery->getLastError());
		unset($objQuery);
		return false;
	}
	
	// 未定義または 1 以外はチェック無の扱いとす。
	if($defval_chk != "1"){

		return true;
	}

	/* 条件別クエリバーツ（この条件分岐ロジックのすぐ下にあるSQL内で使用する） */
	if(strlen($objChlVarID) != 0){
		$conditionalQueryParts = " TBL_3.NESTEDMEM_COL_CMB_ID = :NESTEDMEM_COL_CMB_ID" ;
	}else{
		$conditionalQueryParts = " TBL_3.NESTEDMEM_COL_CMB_ID IS NULL";
	}


	$sql
	 = " 			SELECT"
	  ." 			   TBL_A.ROLE_PACKAGE_ID AS M_ROLE_PACKAGE_ID"
	  ." 			  ,TBL_A.ROLE_ID         AS M_ROLE_ID"
	  ." 			  ,TBL_B.ROLE_PACKAGE_ID"
	  ." 			  ,TBL_B.ROLE_ID"
	  ." 			  ,TBL_B.END_VAR_OF_VARS_ATTR_ID"
	  ." 			  ,TBL_B.VARS_ID"
	  ." 			  ,TBL_B.NESTEDMEM_COL_CMB_ID"
	  ." 			  ,TBL_B.ASSIGN_SEQ"
	  ." 			  ,TBL_B.VARS_VALUE"
	  ." 			FROM"
	  ." 			("
	  ." 			   SELECT"
	  ." 			      ROLE_PACKAGE_ID"
	  ." 			     ,ROLE_ID"
	  ." 			   FROM"
	  ." 			      B_ANSTWR_PTN_ROLE_LINK"
	  ." 			   WHERE"
	  ." 			      PATTERN_ID  = :PATTERN_ID"
	  ." 			    AND"
	  ." 			      DISUSE_FLAG = '0'"
	  ." 			)"
	  ." 			   AS TBL_A"
	  ." 			LEFT OUTER JOIN"
	  ." 			("
	  ." 			   SELECT DISTINCT"
	  ." 			      TBL_3.ROLE_PACKAGE_ID"
	  ." 			     ,TBL_3.ROLE_ID"
	  ." 			     ,TBL_3.END_VAR_OF_VARS_ATTR_ID"
	  ." 			     ,TBL_3.VARS_ID"
	  ." 			     ,TBL_3.NESTEDMEM_COL_CMB_ID"
	  ." 			     ,TBL_3.ASSIGN_SEQ"
	  ." 			     ,TBL_3.VARS_VALUE"
	  ." 			   FROM"
	  ." 			   ("
	  ." 			      SELECT"
	  ." 			         TBL_2.ROLE_PACKAGE_ID"
	  ." 			        ,TBL_2.ROLE_ID"
	  ." 			        ,TBL_2.END_VAR_OF_VARS_ATTR_ID"
	  ." 			        ,TBL_2.VARS_ID"
	  ." 			        ,TBL_2.NESTEDMEM_COL_CMB_ID"
	  ." 			        ,TBL_2.ASSIGN_SEQ"
	  ." 			        ,TBL_2.VARS_VALUE"
	  ." 			      FROM"
	  ." 			      ("
	  ." 			         SELECT"
	  ." 			            ROLE_PACKAGE_ID"
	  ." 			           ,ROLE_ID"
	  ." 			         FROM"
	  ." 			            B_ANSTWR_PTN_ROLE_LINK"
	  ." 			         WHERE"
	  ." 			            PATTERN_ID  = :PATTERN_ID"
	  ." 			          AND"
	  ." 			            DISUSE_FLAG = '0'"
	  ." 			      )"
	  ." 			         AS TBL_1"
	  ." 			      LEFT OUTER JOIN"
	  ." 			         B_ANSTWR_DEFAULT_VARSVAL AS TBL_2"
	  ." 			      ON"
	  ." 			         TBL_1.ROLE_PACKAGE_ID = TBL_2.ROLE_PACKAGE_ID"
	  ." 			       AND"
	  ." 			         TBL_1.ROLE_ID         = TBL_2.ROLE_ID"
	  ." 			      WHERE"
	  ." 			         TBL_2.DISUSE_FLAG = '0'"
	  ." 			   )"
	  ." 			      TBL_3"
	  ." 			   WHERE"
	  ." 			      TBL_3.VARS_ID IN"
	  ." 			      ("
	  ." 			         SELECT"
	  ." 			            VARS_ID"
	  ." 			         FROM"
	  ." 			            B_ANSTWR_PTN_VARS_LINK"
	  ." 			         WHERE"
	  ." 			            PATTERN_ID   = :PATTERN_ID"
	  ." 			          AND"
	  ." 			            VARS_LINK_ID = :VARS_LINK_ID"
	  ." 			          AND"
	  ." 			            DISUSE_FLAG  = '0'"
	  ." 			      )"
	  ." 			    AND"
	  ." 			      $conditionalQueryParts"
	  ." 			)"
	  ." 			   AS TBL_B"
	  ." 			ON"
	  ." 			   TBL_A.ROLE_PACKAGE_ID = TBL_B.ROLE_PACKAGE_ID"
	  ." 			 AND"
	  ." 			   TBL_A.ROLE_ID         = TBL_B.ROLE_ID" ;


	$objQuery = $objDBCA->sqlPrepare($sql);
	if($objQuery->getStatus()===false){
		$errmsg = $objMTS->getSomeMessage("ITAANSTWRH-ERR-6602207",array(basename(__FILE__),__LINE__,"Prepare"));
		web_log($errmsg);
		web_log($objQuery->getLastError());
		unset($objQuery);
		return false;
	}
	if(strlen($objChlVarID) == 0){
		$objQuery->sqlBind( array('PATTERN_ID'=>$objPtnID,
						          'VARS_LINK_ID'=>$objVarID));
	}
	else{
		$objQuery->sqlBind( array('PATTERN_ID'=>$objPtnID,
						          'VARS_LINK_ID'=>$objVarID,
						          'NESTEDMEM_COL_CMB_ID'=>$objChlVarID));
	}
	$r = $objQuery->sqlExecute();
	if (!$r){
		$errmsg = $objMTS->getSomeMessage("ITAANSTWRH-ERR-6602207",array(basename(__FILE__),__LINE__,"Execute"));
		web_log($errmsg);
		web_log($objQuery->getLastError());
		unset($objQuery);
		return false;
	}
	// FETCH行数を取得
	$num_of_rows = $objQuery->effectedRowCount();

	$var_type  = "";
	$tgt_row = array();

	$errmsg	= "";
	$undef_cnt = 0;
	$def_cnt   = 0;
	$arr_type_def_list = array();
	$pkg_id	= "";
	while ( $row = $objQuery->resultFetch() ){
		$tgt_row[] =  $row;
		// 各ロールで変数が定義されているか判定
		// 複数具体値変数で具体値が未定義の場合は該当ロールの変数情報が具体値管理に登録されない。
		if(strlen($row['ROLE_ID'])==0){
			$undef_cnt++;
		}
		else{
			$def_cnt++;
		}
		// 同じロールパッケージが紐付てあるか判定
		if($pkg_id == ""){
			$pkg_id = $row['M_ROLE_PACKAGE_ID'];
		}
		else{
			if($pkg_id != $row['M_ROLE_PACKAGE_ID']){
				// DBアクセス事後処理
				unset($objQuery);
				$errmsg = $objMTS->getSomeMessage("ITAANSTWRH-ERR-6602208");
				return false;
			}
		}
	}
	unset($objQuery);

	// 全てのロールで具体値未定義を判定
	if($def_cnt == 0){
		return true;
	}
	// 一部のロールで具体値未定義を判定
	if(($def_cnt != 0) && ($undef_cnt != 0)){
		// 一部のロールで具体値未定義
		$errmsg = $objMTS->getSomeMessage("ITAANSTWRH-ERR-6602209");
		return false;
	}
	for($idx=0;$idx<count($tgt_row);$idx++){
		// 変数の属性を判定
		if($var_type == ""){
			$var_type = $tgt_row[$idx]['END_VAR_OF_VARS_ATTR_ID'];
		}
		else{
			if($var_type != $tgt_row[$idx]['END_VAR_OF_VARS_ATTR_ID']){
				$errmsg = $objMTS->getSomeMessage("ITAANSTWRH-ERR-6602207",array(basename(__FILE__),__LINE__,"VAR_TYPE Error"));
				return false;
			}
		}
		// 複数具体値変数の場合、ロール毎の具体値をカウントしておく
		if($var_type == '2'){
			if(@count($arr_type_def_list[$tgt_row[$idx]['ROLE_ID']]) == 0){
				$arr_type_def_list[$tgt_row[$idx]['ROLE_ID']] = 1;
			}
			else{
				$arr_type_def_list[$tgt_row[$idx]['ROLE_ID']]++;
			}
		}
	}
	// 複数具体値変数の場合、ロール毎の具体値の数が一致しているか判定
	$val_cnt = "";
	foreach($arr_type_def_list as $role_id=>$role_val_cnt){
		if($val_cnt == ""){
			$val_cnt = $role_val_cnt;
		}
		else{
			if($val_cnt != $role_val_cnt){
				$errmsg = $objMTS->getSomeMessage("ITAANSTWRH-ERR-6602210");
				return false;
			}
		}
	}
	$wk_varval = array();
	$varval	= "";

	// 一般変数の場合
	if('1' == $var_type){
		if(0 === count($tgt_row)){
			return true;
		}
		else if(1 === count($tgt_row)){
			return true;
		}
		else{
			// 各ロールのデフォルト値が同じか確認する。同じ場合は表示する。
			$varval = $tgt_row[0]['VARS_VALUE'];
			for($idx=0;$idx<count($tgt_row);$idx++){
				if($varval != $tgt_row[$idx]['VARS_VALUE']){
					$errmsg = $objMTS->getSomeMessage("ITAANSTWRH-ERR-6602210");
					return false;
				}
			}
		}
	}
	// 複数具体値変数の場合
	else if('2' == $var_type){
		if(0 !== count($tgt_row)){
			foreach($tgt_row as $row){
				// 各ロールのデフォルト値が同じか判定
				if(@count($wk_varval[$row['ASSIGN_SEQ']]) != 0){
					if($wk_varval[$row['ASSIGN_SEQ']] != $row['VARS_VALUE']){
						$errmsg = $objMTS->getSomeMessage("ITAANSTWRH-ERR-6602210");
						return false;
					}
				}
				else{
					$wk_varval[$row['ASSIGN_SEQ']] = $row['VARS_VALUE'];
				}
			}
		}
	}else{
		$errmsg = $objMTS->getSomeMessage("ITAANSTWRH-ERR-6602207",array(basename(__FILE__),__LINE__,"VAR_TYPE Error"));
		return false;
	}

	return true;
}
