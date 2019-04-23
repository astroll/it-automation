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
//	・AnsibleTower 代入値自動登録設定
//
//////////////////////////////////////////////////////////////////////

$tmpFx = function (&$aryVariant=array(),&$arySetting=array()){
	global $g;

	$arrayWebSetting = array();
	$arrayWebSetting['page_info'] = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7301201"); //MessageID_SecondSuffix：12

	/* 履歴管理用のカラムを配列に格納する */
	$tmpAry = array
	  (
		 'TT_SYS_01_JNL_SEQ_ID'			  => 'JOURNAL_SEQ_NO'
		,'TT_SYS_02_JNL_TIME_ID'		  => 'JOURNAL_REG_DATETIME'
		,'TT_SYS_03_JNL_CLASS_ID'		  => 'JOURNAL_ACTION_CLASS'
		,'TT_SYS_04_NOTE_ID'			  => 'NOTE'
		,'TT_SYS_04_DISUSE_FLAG_ID'		  => 'DISUSE_FLAG'
		,'TT_SYS_05_LUP_TIME_ID'		  => 'LAST_UPDATE_TIMESTAMP'
		,'TT_SYS_06_LUP_USER_ID'		  => 'LAST_UPDATE_USER'
		,'TT_SYS_NDB_ROW_EDIT_BY_FILE_ID' => 'ROW_EDIT_BY_FILE'
		,'TT_SYS_NDB_UPDATE_ID'			  => 'WEB_BUTTON_UPDATE'
		,'TT_SYS_NDB_LUP_TIME_ID'		  => 'UPD_UPDATE_TIMESTAMP'
	  );

	/* 画面に１対１で紐付けるビューを指定 */
	$table = new TableControlAgent('D_ANSTWR_PRMCOL_VARS_LINK','PRMCOL_VARS_LINK_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7391201"), 'D_ANSTWR_PRMCOL_VARS_LINK_JNL', $tmpAry);
	$tmpAryColumn = $table->getColumns();
	$tmpAryColumn['PRMCOL_VARS_LINK_ID']->setSequenceID('B_ANSTWR_PRMCOL_VARS_LINK_RIC');
	$tmpAryColumn['JOURNAL_SEQ_NO']->setSequenceID('B_ANSTWR_PRMCOL_VARS_LINK_JSQ');
	unset($tmpAryColumn);

	/* ----VIEWをコンテンツソースにする場合、構成する実体テーブルを更新するための設定 */
	$table->setDBMainTableHiddenID('B_ANSTWR_PRMCOL_VARS_LINK');
	$table->setDBJournalTableHiddenID('B_ANSTWR_PRMCOL_VARS_LINK_JNL');
	// 利用時は、更新対象カラムに、「$c->setHiddenMainTableColumn(true);」を付加すること

	/* 動的プルダウンの作成用 */
	$table->setJsEventNamePrefix(true);

	/* QMファイル名プレフィックス */
	$table->setDBMainTableLabel($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7361201"));

	/* エクセルのシート名 */
	$table->getFormatter('excel')->setGeneValue('sheetNameForEditByFile',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7341201"));

	/* 検索機能の制御 */
	$table->setGeneObject('AutoSearchStart',true);  //('',true,false)

    ////////////////////////////////////////////////////////////
    // ColumnGroup:パラメータシート 開始
    ////////////////////////////////////////////////////////////
    $cgg = new ColumnGroup($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7311205"));

	/* <START> （カラムグループ）メニューグループ_一覧のみ表示 ----------------------------------------------------------------------------------- */
	$cg = new ColumnGroup($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7311201"));

	/* ID */
	$c = new IDColumn('MENU_GROUP_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7391202"),'A_MENU_LIST','MENU_GROUP_ID','MENU_GROUP_ID','');
	$c -> addClass("number");
	$c -> setDescription($g['objMTS'] -> getSomeMessage("ITAANSTWRH-MNU-7351202"));
	$c -> setHiddenMainTableColumn(false);
	$c -> setAllowSendFromFile(false);
	$c -> getOutputType("update_table")   -> setVisible(false);
	$c -> getOutputType("register_table") -> setVisible(false);
	$c -> getOutputType("excel")		  -> setVisible(false);
	$c -> getOutputType("csv")			  -> setVisible(false);
	$c -> getOutputType('json')			  -> setVisible(false); // RestAPIでは隠す
	$c -> setDeleteOffBeforeCheck(false);
	$objOT = new TraceOutputType(new ReqTabHFmt(), new TextTabBFmt());
	$aryTraceQuery = array
	  (
		array
		(
			 'TRACE_TARGET_TABLE'		=> 'A_MENU_LIST_JNL'
			,'TTT_SEARCH_KEY_COLUMN_ID'  => 'MENU_ID'
			,'TTT_GET_TARGET_COLUMN_ID'  => 'MENU_GROUP_ID'
			,'TTT_JOURNAL_SEQ_NO'		=> 'JOURNAL_SEQ_NO'
			,'TTT_TIMESTAMP_COLUMN_ID'   => 'LAST_UPDATE_TIMESTAMP'
			,'TTT_DISUSE_FLAG_COLUMN_ID' => 'DISUSE_FLAG'
		)
	  );
	$c->addClass("number");
	$objOT->setTraceQuery($aryTraceQuery);
	$objOT->setFirstSearchValueOwnerColumnID('MENU_ID');
	$c->setOutputType('print_journal_table',$objOT);
	$cg->addColumn($c);


	/* 名称 */
	$c = new TextColumn('MENU_GROUP_NAME', $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7391203"));
	$c -> setHiddenMainTableColumn(false);
	$c -> setAllowSendFromFile(false);
	$c -> setDescription($g['objMTS'] -> getSomeMessage("ITAANSTWRH-MNU-7391203"));
	$c -> getOutputType("update_table")   -> setVisible(false);
	$c -> getOutputType("register_table") -> setVisible(false);
	$c -> getOutputType("excel")		  -> setVisible(false);
	$c -> getOutputType("csv")			  -> setVisible(false);
	$c -> getOutputType('json')			  -> setVisible(false); // RestAPIでは隠す

	$objOT = new TraceOutputType(new ReqTabHFmt(), new TextTabBFmt());
	$aryTraceQuery = array
	  (
		array
		(
			 'TRACE_TARGET_TABLE'		 => 'A_MENU_LIST_JNL'
			,'TTT_SEARCH_KEY_COLUMN_ID'  => 'MENU_ID'
			,'TTT_GET_TARGET_COLUMN_ID'  => 'MENU_GROUP_ID'
			,'TTT_JOURNAL_SEQ_NO'		 => 'JOURNAL_SEQ_NO'
			,'TTT_TIMESTAMP_COLUMN_ID'   => 'LAST_UPDATE_TIMESTAMP'
			,'TTT_DISUSE_FLAG_COLUMN_ID' => 'DISUSE_FLAG'
		),
		array
		(
			 'TRACE_TARGET_TABLE'		 => 'A_MENU_GROUP_LIST_JNL'
			,'TTT_SEARCH_KEY_COLUMN_ID'  => 'MENU_GROUP_ID'
			,'TTT_GET_TARGET_COLUMN_ID'  => 'MENU_GROUP_NAME'
			,'TTT_JOURNAL_SEQ_NO'		 => 'JOURNAL_SEQ_NO'
			,'TTT_TIMESTAMP_COLUMN_ID'   => 'LAST_UPDATE_TIMESTAMP'
			,'TTT_DISUSE_FLAG_COLUMN_ID' => 'DISUSE_FLAG'
		)
	  );
	$objOT->setTraceQuery($aryTraceQuery);
	$objOT->setFirstSearchValueOwnerColumnID('MENU_ID');
	$c->setOutputType('print_journal_table',$objOT);

	$cg->addColumn($c);


	//$table->addColumn($cg);
    $cgg->addColumn($cg);
	/* < END > （カラムグループ）メニューグループ_一覧のみ表示 ----------------------------------------------------------------------------------- */

	/* <START> （カラムグループ）メニュー_一覧のみ表示 --------------------------------------------------------------------------------------- */
	$cg = new ColumnGroup($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7311202"));

	/* ID */
	$c = new IDColumn('MENU_ID_CLONE',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7391204"), "D_MENU_LIST", 'MENU_ID', "MENU_ID", '', array('OrderByThirdColumn'=>'MENU_ID'));
	$c -> addClass("number");
	$c -> setDescription($g['objMTS'] -> getSomeMessage("ITAANSTWRH-MNU-7351204"));
	$c -> setHiddenMainTableColumn(false); //更新対象カラムから外す
	$c -> getOutputType("update_table")   -> setVisible(false);
	$c -> getOutputType("register_table") -> setVisible(false);
	$c -> getOutputType("excel")		  -> setVisible(false);
	$c -> getOutputType("csv")			  -> setVisible(false);
	$c -> getOutputType('json')			  -> setVisible(false); // RestAPIでは隠す
	$c -> setDeleteOffBeforeCheck(false); // 復活時に二重チェックになるので付加
	$objOT = new TraceOutputType(new ReqTabHFmt(), new TextTabBFmt());
	$aryTraceQuery = array
	  (
		array
		(
			 'TRACE_TARGET_TABLE'		 => 'A_MENU_LIST_JNL'
			,'TTT_SEARCH_KEY_COLUMN_ID'  => 'MENU_ID'
			,'TTT_GET_TARGET_COLUMN_ID'  => 'MENU_ID'
			,'TTT_JOURNAL_SEQ_NO'		 => 'JOURNAL_SEQ_NO'
			,'TTT_TIMESTAMP_COLUMN_ID'   => 'LAST_UPDATE_TIMESTAMP'
			,'TTT_DISUSE_FLAG_COLUMN_ID' => 'DISUSE_FLAG'
		)
	  );
	$objOT->setTraceQuery($aryTraceQuery);
	$objOT->setFirstSearchValueOwnerColumnID('MENU_ID');
	$c->setOutputType('print_journal_table',$objOT);

	/* 登録更新関係から隠す */
	$c->setMasterDisplayColumnType(0);
	$cg->addColumn($c);


	/* 名称 */
	$c = new TextColumn('MENU_NAME', $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7391205"));
	$c -> setHiddenMainTableColumn(false);
	$c -> setAllowSendFromFile(false);
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7351205"));
	$c -> setHiddenMainTableColumn(false);
	$c -> getOutputType("update_table")   -> setVisible(false);
	$c -> getOutputType("register_table") -> setVisible(false);
	$c -> getOutputType("excel")		  -> setVisible(false);
	$c -> getOutputType("csv")			  -> setVisible(false);
	$c -> getOutputType('json')			  -> setVisible(false); // RestAPIでは隠す

	$cg->addColumn($c);


	//$table->addColumn($cg);
    $cgg->addColumn($cg);
	/* < END > （カラムグループ）メニュー_一覧のみ表示 --------------------------------------------------------------------------------------- */


	/* メニュー_登録・更新のみ表示 */
	/* RestAPI/Excel/CSVからの登録の場合に組み合わせバリデータで退避したMENU_IDを設定する。 */
	$tmpObjFunction = function($objColumn, $strEventKey, &$exeQueryData, &$reqOrgData=array(), &$aryVariant=array()){
		global $g;
		$boolRet	   = true;
		$intErrorType  = null;
		$aryErrMsgBody = array();
		$strErrMsg	   = "";
		$strErrorBuf   = "";

		/* 登録/更新のテーブル領域に、プルダウンリストHtmlタグを、事後的に作成する為の設定 */
		$modeValue = $aryVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_MODE"];
		if(
			$modeValue=="DTUP_singleRecRegister"
		  OR
			$modeValue=="DTUP_singleRecUpdate"
		){
			if(strlen($g['MENU_ID_UPDATE_VALUE']) !== 0){
				$exeQueryData[$objColumn->getID()] = $g['MENU_ID_UPDATE_VALUE'];
			}
		}
		else if( $modeValue=="DTUP_singleRecDelete" ){}
		$retArray = array($boolRet,$intErrorType,$aryErrMsgBody,$strErrMsg,$strErrorBuf);

		return $retArray;
	};

	$c = new IDColumn('MENU_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7391206"),'D_CMDB_MENU_LIST','MENU_ID','MENU_PULLDOWN','',array('OrderByThirdColumn'=>'MENU_ID'));
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7351206")); // Excelヘッダでの説明 */

	$c -> setHiddenMainTableColumn(true); //更新対象カラム
	$c -> setAllowSendFromFile(false);//エクセル/CSVからのアップロードを禁止する。

	/* このカラムの使用を許可しない処理区分を指定 */
	$c -> getOutputType('filter_table')		   -> setVisible(false);
	$c -> getOutputType('print_table')		   -> setVisible(false);
	$c -> getOutputType('delete_table')		   -> setVisible(false);
	$c -> getOutputType('print_journal_table') -> setVisible(false);
	$c->getOutputType('excel')				   -> setVisible(false);
	$c->getOutputType('csv')				   -> setVisible(false);
	$c->getOutputType('json')				   -> setVisible(false); // RestAPIでは隠す

	$c -> setEvent('update_table', 'onchange', 'menu_upd');
	$c -> setEvent('register_table', 'onchange', 'menu_reg');

	$c -> setJournalTableOfMaster('D_CMDB_MENU_LIST_JNL');
	$c -> setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c -> setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c -> setJournalKeyIDOfMaster('MENU_ID');
	$c -> setJournalDispIDOfMaster('MENU_PULLDOWN');
	$c -> setFunctionForEvent('beforeTableIUDAction',$tmpObjFunction);

	//$table->addColumn($c);
    $cgg->addColumn($c);
	unset($tmpObjFunction);


	/* 項目_一覧のみ表示 */
	/* RestAPI/Excel/CSVからの登録の場合に組み合わせバリデータで退避したCOLUMN_LIST_IDを設定する。 */
	$tmpObjFunction = function($objColumn, $strEventKey, &$exeQueryData, &$reqOrgData=array(), &$aryVariant=array()){
		global $g;

		$boolRet	   = true;
		$intErrorType  = null;
		$aryErrMsgBody = array();
		$strErrMsg	   = "";
		$strErrorBuf   = "";

		/* 登録/更新のテーブル領域に、プルダウンリストHtmlタグを、事後的に作成する為の設定 */
		$modeValue = $aryVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_MODE"];
		if( $modeValue=="DTUP_singleRecRegister" || $modeValue=="DTUP_singleRecUpdate" ){
			if(strlen($g['COLUMN_LIST_ID_UPDATE_VALUE']) !== 0){
				$exeQueryData[$objColumn->getID()] = $g['COLUMN_LIST_ID_UPDATE_VALUE'];
			}
		}
		else if( $modeValue=="DTUP_singleRecDelete" ){}
		$retArray = array($boolRet,$intErrorType,$aryErrMsgBody,$strErrMsg,$strErrorBuf);

		return $retArray;
	};

	$c = new IDColumn('MENU_COLUMN_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7391207"),'B_CMDB_MENU_COLUMN','COLUMN_LIST_ID','COL_TITLE','',array('SELECT_ADD_FOR_ORDER'=>array('COL_TITLE_DISP_SEQ'),'ORDER'=>'ORDER BY ADD_SELECT_1') );
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7351207"));
	$c -> setHiddenMainTableColumn(true); //更新対象カラム
	$c -> setAllowSendFromFile(false);//エクセル/CSVからのアップロードを禁止する。
	$c -> getOutputType('excel') -> setVisible(false);
	$c -> getOutputType('csv')   -> setVisible(false);
	$c -> getOutputType('json')  -> setVisible(false); // RestAPIでは隠す

	$objFunction01 = function($objOutputType, $aryVariant, $arySetting, $aryOverride, $objColumn){
		global $g;
		$retBool = false;
		$intErrorType = null;
		$aryErrMsgBody = array();
		$strErrMsg = "";
		$aryDataSet = array();
		$strFxName = "";

		$strMenuIDNumeric = $aryVariant['MENU_ID'];
		$strQuery
		 = " 			SELECT"
		  ." 			   TAB_1.COLUMN_LIST_ID AS KEY_COLUMN"
		  ." 			  ,TAB_1.COL_TITLE	  AS DISP_COLUMN"
		  ." 			FROM "
		  ." 			   B_CMDB_MENU_COLUMN AS TAB_1"
		  ." 			WHERE "
		  ." 			   TAB_1.DISUSE_FLAG IN ('0') "
		  ." 			AND"
		  ." 			   TAB_1.MENU_ID = :MENU_ID"
		  ." 			ORDER BY"
		  ." 			   COL_TITLE_DISP_SEQ"
		;
		/* クエリーバインド */
		$aryForBind['MENU_ID'] = $strMenuIDNumeric;

		if( 0 < strlen($strMenuIDNumeric) ){
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

		$strMenuIDNumeric = $rowData['MENU_ID'];

		$strQuery
		 = " 			SELECT"
		  ." 			   TAB_1.COLUMN_LIST_ID AS KEY_COLUMN"
		  ." 			  ,TAB_1.COL_TITLE	  AS DISP_COLUMN "
		  ." 			FROM "
		  ." 			 B_CMDB_MENU_COLUMN TAB_1 "
		  ." 			WHERE "
		  ." 			 TAB_1.DISUSE_FLAG IN ('0') "
		  ." 			 AND TAB_1.MENU_ID = :MENU_ID "
		  ." 			ORDER BY COL_TITLE_DISP_SEQ";

		$aryForBind['MENU_ID'] = $strMenuIDNumeric;

		if( 0 < strlen($strMenuIDNumeric) ){
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

	//$strSetInnerText = 'メニューを選択して下さい';
	$strSetInnerText = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7301202");
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

	$c -> setOutputType('update_table',$objOTForUpd);
	$c -> setOutputType('register_table',$objOTForReg);
	$c -> setJournalTableOfMaster('B_CMDB_MENU_COLUMN_JNL');
	$c -> setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c -> setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c -> setJournalKeyIDOfMaster('COLUMN_LIST_ID');
	$c -> setJournalDispIDOfMaster('COL_TITLE');

	$c -> setFunctionForEvent('beforeTableIUDAction',$tmpObjFunction);

    $cgg->addColumn($c);
	unset($objFunction01);
	unset($objFunction02);
	unset($objFunction03);
	unset($tmpObjFunction);


	/* 項目_Excel/CSV/RestAPI用 */
	$c = new IDColumn('REST_MENU_COLUMN_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7371201"),'D_CMDB_MG_MU_COL_LIST','COLUMN_LIST_ID','MENU_COL_TITLE_PULLDOWN','',array('SELECT_ADD_FOR_ORDER'=>array('MENU_ID','COL_TITLE_DISP_SEQ'),'ORDER'=>'ORDER BY ADD_SELECT_1,ADD_SELECT_2') );
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7381201"));
	$c -> setHiddenMainTableColumn(false); //更新対象カラムから外す
	$c -> getOutputType('filter_table')		   -> setVisible(false);
	$c -> getOutputType('print_table')		   -> setVisible(false);
	$c -> getOutputType('update_table')		   -> setVisible(false);
	$c -> getOutputType('register_table')	   -> setVisible(false);
	$c -> getOutputType('delete_table')		   -> setVisible(false);
	$c -> getOutputType('print_journal_table') -> setVisible(false);

	$c -> getOutputType('excel')			   -> setVisible(true);
	$c -> getOutputType('csv')				   -> setVisible(true);
	$c -> getOutputType('json')				   -> setVisible(true);
	$c -> setJournalTableOfMaster('D_CMDB_MG_MU_COL_LIST_JNL');
	$c -> setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c -> setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c -> setJournalKeyIDOfMaster('COLUMN_LIST_ID');
	$c -> setJournalDispIDOfMaster('MENU_COL_TITLE_PULLDOWN');

    $cgg->addColumn($c);

    ////////////////////////////////////////////////////////////
    // ColumnGroup:パラメータシート 終了
    ////////////////////////////////////////////////////////////
    $table->addColumn($cgg);

	/* 登録方式 */
	$c = new IDColumn('PRMCOL_LINK_TYPE_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7391208"),'B_CMDB_MENU_COL_TYPE','COLUMN_TYPE_ID','COLUMN_TYPE_NAME','',array('OrderByThirdColumn'=>'COLUMN_TYPE_ID'));
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7351208"));

	$c->setHiddenMainTableColumn(true); //更新対象カラム

	$c->setJournalTableOfMaster('B_CMDB_MENU_COL_TYPE_JNL');
	$c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c->setJournalKeyIDOfMaster('COLUMN_TYPE_ID');
	$c->setJournalDispIDOfMaster('COLUMN_TYPE_NAME');
	$c->setRequired(true);//登録/更新時には、入力必須
	$table->addColumn($c);

    ////////////////////////////////////////////////////////////
    // ColumnGroup:IaC変数 開始
    ////////////////////////////////////////////////////////////
    $cgg = new ColumnGroup($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7311206"));

	$tmpObjFunction = function($objColumn, $strEventKey, &$exeQueryData, &$reqOrgData=array(), &$aryVariant=array()){
		global $g;

		$boolRet	   = true;
		$intErrorType  = null;
		$aryErrMsgBody = array();
		$strErrMsg	 = "";
		$strErrorBuf   = "";

		/* 登録/更新のテーブル領域に、プルダウンリストHtmlタグを、事後的に作成する為の設定 */
		$modeValue = $aryVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_MODE"];
		if( $modeValue=="DTUP_singleRecRegister" || $modeValue=="DTUP_singleRecUpdate" ){
			if(strlen($g['PATTERN_ID_UPDATE_VALUE']) !== 0){
				$exeQueryData[$objColumn->getID()] = $g['PATTERN_ID_UPDATE_VALUE'];
			}
		}
		else if( $modeValue=="DTUP_singleRecDelete" ){}
		$retArray = array($boolRet,$intErrorType,$aryErrMsgBody,$strErrMsg,$strErrorBuf);

		return $retArray;
	};

	/* Movement */
	$c = new IDColumn('PATTERN_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7391209"),'E_ANSTWR_PATTERN','PATTERN_ID','PATTERN','',array('OrderByThirdColumn'=>'PATTERN_ID'));
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7351209")); //Excelヘッダでの説明
	$c->setHiddenMainTableColumn(true); //更新対象カラム

	$c->setJournalTableOfMaster('E_ANSTWR_PATTERN_JNL');
	$c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c->setJournalKeyIDOfMaster('PATTERN_ID');
	$c->setJournalDispIDOfMaster('PATTERN');

	// 必須チェックは組合せバリデータで行う。
	$c->setRequired(false);

	//エクセル/CSVからのアップロードを禁止する。
	$c->setAllowSendFromFile(false);

	// REST/excel/csvで項目無効
	$c->getOutputType('excel')->setVisible(false);
	$c->getOutputType('csv')->setVisible(false);
	$c->getOutputType('json')->setVisible(false);

	// データベース更新前のファンクション登録
	$c->setFunctionForEvent('beforeTableIUDAction',$tmpObjFunction);

	$c->setEvent('update_table', 'onchange', 'pattern_upd');
	$c->setEvent('register_table', 'onchange', 'pattern_reg');

	//$table->addColumn($c);
    $cgg->addColumn($c);


	/* <START> （カラムグループ）key変数 --------------------------------------------------------------------------------------------- */
	$cg = new ColumnGroup($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7311203"));


	/* 変数名_一覧のみ表示 */
	/* RestAPI/Excel/CSVからの登録の場合に組み合わせバリデータで退避したKEY_VARS_LINK_IDを設定する。 */
	$tmpObjFunction = function($objColumn, $strEventKey, &$exeQueryData, &$reqOrgData=array(), &$aryVariant=array()){
		global $g;

		$boolRet	   = true;
		$intErrorType  = null;
		$aryErrMsgBody = array();
		$strErrMsg	 = "";
		$strErrorBuf   = "";

		/* 登録/更新のテーブル領域に、プルダウンリストHtmlタグを、事後的に作成する為の設定 */
		$modeValue = $aryVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_MODE"];
		if( $modeValue=="DTUP_singleRecRegister" || $modeValue=="DTUP_singleRecUpdate" ){
			if(strlen($g['KEY_VARS_LINK_ID_UPDATE_VALUE']) !== 0){
				$exeQueryData[$objColumn->getID()] = $g['KEY_VARS_LINK_ID_UPDATE_VALUE'];
			}
		}
		else if( $modeValue=="DTUP_singleRecDelete" ){}
		$retArray = array($boolRet,$intErrorType,$aryErrMsgBody,$strErrMsg,$strErrorBuf);

		return $retArray;
	};

	$c = new IDColumn('KEY_VARS_LINK_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7391210"),'D_ANSTWR_PTN_VARS_LINK','VARS_LINK_ID','VARS_PULLDOWN','');
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7351210"));//エクセル・ヘッダでの説明
	$c->setHiddenMainTableColumn(true); //更新対象カラム
	$c->setJournalTableOfMaster('D_ANSTWR_PTN_VARS_LINK_JNL');
	$c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c->setJournalKeyIDOfMaster('VARS_LINK_ID');
	$c->setJournalDispIDOfMaster('VARS_PULLDOWN');

	/* フォームの表示後、ユーザによりトリガーカラムが選ばれたとき、選べる選択肢リストを作成する関数 */
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
		  ." 			   TAB_1.VARS_LINK_ID AS KEY_COLUMN"
		  ." 			  ,TAB_1.VARS_PULLDOWN AS DISP_COLUMN"
		  ." 			FROM "
		  ." 			   D_ANSTWR_PTN_VARS_LINK AS TAB_1"
		  ." 			WHERE"
		  ." 			   TAB_1.DISUSE_FLAG IN ('0')"
		  ." 			AND"
		  ." 			   TAB_1.PATTERN_ID = :PATTERN_ID"
		  ." 			ORDER BY"
		  ." 			   KEY_COLUMN ASC"
		;

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

	/* フォームの表示直後、選択できる選択肢リストを作成する関数 */
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

		$aryForBind['PATTERN_ID']		= $strPatternIdNumeric;

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

	//$strSetInnerText = '作業パターンを選択して下さい'
	$strSetInnerText = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7301203");
	$objVarBFmtUpd = new SelectTabBFmt();
	$objVarBFmtUpd->setFADJsEvent('onChange','key_vars_upd');

	/* フォームの表示直後、変更反映カラムの既存値が、選べる選択肢の中になかった場合のメッセージ */
	$objVarBFmtUpd->setNoOptionMessageText($strSetInnerText);

	/* フォームの表示後、ユーザによりトリガーカラムが選ばれたが、選べる選択肢がなかった場合のメッセージ */
	$objVarBFmtUpd->setFADNoOptionMessageText($strSetInnerText);

	/* フォームの表示直後、選択できる選択肢リストを作成する関数指定 */
	$objVarBFmtUpd->setFunctionForGetSelectList($objFunction03);
	$objOTForUpd = new OutputType(new ReqTabHFmt(), $objVarBFmtUpd);
	$objOTForUpd->setJsEvent('onChange','key_vars_upd');

	/* フォームの表示後、ユーザによりトリガーカラムが選ばれたとき、選べる選択肢リストを作成する関数を指定 */
	$objOTForUpd->setFunctionForGetFADSelectList($objFunction01);
	$objVarBFmtReg = new SelectTabBFmt();
	$objVarBFmtReg->setFADJsEvent('onChange','key_vars_reg');

	/* フォームの表示直後、トリガーカラムが選ばれていない場合のメッセージ */
	$objVarBFmtReg->setSelectWaitingText($strSetInnerText);

	/* フォームの表示後、ユーザによりトリガーカラムが選ばれたが、選べる選択肢がなかった場合のメッセージ */
	$objVarBFmtReg->setFADNoOptionMessageText($strSetInnerText);
	$objOTForReg = new OutputType(new ReqTabHFmt(), $objVarBFmtReg);

	/* フォームの表示後、ユーザによりトリガーカラムが選ばれたとき、選べる選択肢リストを作成する関数を指定 */
	$objOTForReg->setFunctionForGetFADSelectList($objFunction02);

	$c->setOutputType('update_table',$objOTForUpd);
	$c->setOutputType('register_table',$objOTForReg);

	// ---- 2018/05/25 #3084 Modify Start
	//$c->setRequired(true);//登録/更新時には、入力必須
	// 必須チェックは組合せバリデータで行う。
	$c->setRequired(false);

	//エクセル/CSVからのアップロードを禁止する。
	$c->setAllowSendFromFile(false);

	// REST/excel/csvで項目無効
	$c->getOutputType('excel')->setVisible(false);
	$c->getOutputType('csv')->setVisible(false);
	$c->getOutputType('json')->setVisible(false);

	// データベース更新前のファンクション登録
	$c->setFunctionForEvent('beforeTableIUDAction',$tmpObjFunction);
	// 2018/05/25 #3084 Modify End ----

	$cg->addColumn($c);

	unset($objFunction01);
	unset($objFunction02);
	unset($objFunction03);


	/* メンバー変数名_一覧のみ表示 */
	/* RestAPI/Excel/CSVからの登録の場合に組み合わせバリデータで退避したKEY_NESTEDMEM_COL_CMB_IDを設定する。 */
	$tmpObjFunction = function($objColumn, $strEventKey, &$exeQueryData, &$reqOrgData=array(), &$aryVariant=array()){
		global $g;

		$boolRet	   = true;
		$intErrorType  = null;
		$aryErrMsgBody = array();
		$strErrMsg	 = "";
		$strErrorBuf   = "";

		/* 登録/更新のテーブル領域に、プルダウンリストHtmlタグを、事後的に作成する為の設定 */
		$modeValue = $aryVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_MODE"];
		if( $modeValue=="DTUP_singleRecRegister" || $modeValue=="DTUP_singleRecUpdate" ){
			if(strlen($g['KEY_NESTEDMEM_COL_CMB_ID_UPDATE_VALUE']) !== 0){
				$exeQueryData[$objColumn->getID()] = $g['KEY_NESTEDMEM_COL_CMB_ID_UPDATE_VALUE'];
			}
		}
		else if( $modeValue=="DTUP_singleRecDelete" ){}
		$retArray = array($boolRet,$intErrorType,$aryErrMsgBody,$strErrMsg,$strErrorBuf);

		return $retArray;
	};

	$c = new IDColumn('KEY_NESTEDMEM_COL_CMB_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7391211"),'D_ANSTWR_NESTEDMEM_COL_CMB','NESTEDMEM_COL_CMB_ID','COL_COMBINATION_MEMBER_ALIAS','');
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7351211"));
	$c -> setHiddenMainTableColumn(true); //更新対象カラム
	$c -> setJournalTableOfMaster('D_ANSTWR_NESTEDMEM_COL_CMB_JNL');
	$c -> setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c -> setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');

	/* ※※※ 不要と思うが、一応コメントアウトにしておく。問題なければ削除 ※※※
	$c -> setJournalKeyIDOfMaster('NESTEDMEM_COL_CMB_ID');
	$c -> setJournalDispIDOfMaster('COL_COMBINATION_MEMBER_ALIAS');
	*/

	/* フォームの表示後、ユーザによりトリガーカラムが選ばれたとき、選べる選択肢リストを作成する関数 */
	$objFunction01 = function($objOutputType, $aryVariant, $arySetting, $aryOverride, $objColumn){
		global $g;
		$retBool = false;
		$intErrorType = null;
		$aryErrMsgBody = array();
		$strErrMsg = "";
		$aryDataSet = array();
		$aryAddResultData = array();
		$strFxName = "";
		$strVarsLinkIdNumeric = $aryVariant['KEY_VARS_LINK_ID'];
		$strColSeqCombinationId = $aryVariant['KEY_NESTEDMEM_COL_CMB_ID'];

		/* <START> 親変数か否やを調べる----------------------------------------------------------------------------------------------------- */
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
			  ." 			WHERE"
			  ." 			   TAB_1.DISUSE_FLAG IN ('0')"
			  ." 			AND"
			  ." 			   TAB_1.VARS_LINK_ID = :KEY_VARS_LINK_ID"
			;
			/* クエリーバインド */
			$aryForBind['KEY_VARS_LINK_ID'] = $strVarsLinkIdNumeric;

			$aryRetBody = singleSQLExecuteAgent($strQuery, $aryForBind, $strFxName);
			if( $aryRetBody[0] === true ){
				$objQuery = $aryRetBody[1];
				$tmpAryRow = array();
				while($row = $objQuery->resultFetch() ){
					$tmpAryRow[]= $row;
				}
				if( count($tmpAryRow) === 1 ){
					$tmpRow = $tmpAryRow[0];
					if(1 == $tmpRow['VARS_ATTR_ID']){
						$intVarType = 0;
						$aryAddResultData[] = "NORMAL_VAR_0";
					}
					else if(2 == $tmpRow['VARS_ATTR_ID']){
						$intVarType = 0;
						$aryAddResultData[] = "NORMAL_VAR_1";
					}
					else if(3 == $tmpRow['VARS_ATTR_ID']){
						$intVarType = 1;
						$aryAddResultData[] = "PARENT_VAR";
					}
					else {
						$intErrorType = 501;
					}
				}else{
					$intErrorType = 502;
				}
				unset($tmpAryRow);
				unset($objQuery);
			}else{
				$intErrorType = 503;
			}
		}
		/* < END > 親変数か否やを調べる----------------------------------------------------------------------------------------------------- */

		/* <START> 親変数だった場合、リストを作成する--------------------------------------------------------------------------------------- */
		if( $intVarType === 1 ){
			$strQuery
			 = " 			SELECT"
			  ." 			   TAB_1.NESTEDMEM_COL_CMB_ID AS KEY_COLUMN"
			  ." 			  ,TAB_1.COL_COMBINATION_MEMBER_ALIAS AS DISP_COLUMN"
			  ." 			FROM"
			  ." 			   B_ANSTWR_NESTEDMEM_COL_CMB AS TAB_1"
			  ." 			LEFT JOIN"
			  ." 			   D_ANSTWR_PTN_VARS_LINK AS TAB_2"
			  ." 			 ON"
			  ." 			   TAB_1.VARS_ID = TAB_2.VARS_ID"
			  ." 			WHERE"
			  ." 			   TAB_1.DISUSE_FLAG IN ('0')"
			  ." 			AND"
			  ." 			   TAB_2.DISUSE_FLAG IN ('0')"
			  ." 			AND"
			  ." 			   TAB_2.VARS_LINK_ID = :KEY_VARS_LINK_ID"
			  ." 			ORDER BY"
			  ." 			   TAB_1.VARS_ID"
			  ." 			  ,TAB_1.COL_COMBINATION_MEMBER_ALIAS"
			;
			/* クエリーバインド */
			$aryForBind['KEY_VARS_LINK_ID'] = $strVarsLinkIdNumeric;

			if( 0 < strlen($strVarsLinkIdNumeric) ){
				$aryRetBody = singleSQLExecuteAgent($strQuery, $aryForBind, $strFxName);
				if( $aryRetBody[0] === true ){
					$objQuery = $aryRetBody[1];
					while($row = $objQuery->resultFetch() ){
						$aryDataSet[]= $row;
					}
					unset($objQuery);
					$retBool = true;
				}else{
					$intErrorType = 504;
				}
			}

			/* Functionを一意にする */
			if(
				$tmpRow['VARS_ATTR_ID'] == 3
			  AND
				strlen($strColSeqCombinationId) > 0
			){
				/* 変数管理テーブルから取得 */
				$aryResult = getChildVars_2100140009($strVarsLinkIdNumeric, $strColSeqCombinationId);

				/* 代入順序の有無判定処理 */
				if(
					gettype($aryResult) === "array"
				  AND
					count($aryResult) === 1
				){
					if( $aryResult[0]['VARS_LINK_ID'] == $strVarsLinkIdNumeric){
						if(1 == $aryResult[0]['ASSIGN_SEQ_NEED']){
							$aryAddResultData[0] = "MEMBER_VAR_1" ;
						}else{
							$aryAddResultData[0] = "MEMBER_VAR_0";
						}
					}
					else{
						$aryAddResultData[0] = "MEMBER_VAR_323";
					}
				}
				else if($aryResult === false){
					$intErrorType = 505 ;
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

	/* フォームの表示直後、選択できる選択肢リストを作成する関数 */
	$objFunction02 = $objFunction01;

	/* フォームの表示直後、選択できる選択肢リストを作成する関数 */
	$objFunction03 = function($objCellFormatter, $rowData, $aryVariant){
		global $g;
		$retBool = false;
		$intErrorType = null;
		$aryErrMsgBody = array();
		$strErrMsg = "";
		$aryDataSet = array();

		$strFxName = "";

		$strVarsLinkIdNumeric = $rowData['KEY_VARS_LINK_ID'];

		$strQuery
		 = " 			SELECT"
		  ." 			   TAB_1.NESTEDMEM_COL_CMB_ID AS KEY_COLUMN"
		  ." 			  ,TAB_1.COL_COMBINATION_MEMBER_ALIAS AS DISP_COLUMN"
		  ." 			FROM"
		  ." 			   B_ANSTWR_NESTEDMEM_COL_CMB AS TAB_1"
		  ." 			LEFT JOIN"
		  ." 			   D_ANSTWR_PTN_VARS_LINK AS TAB_2"
		  ." 			 ON"
		  ." 			   TAB_1.VARS_ID = TAB_2.VARS_ID"
		  ." 			WHERE"
		  ." 			   TAB_1.DISUSE_FLAG IN ('0')"
		  ." 			AND"
		  ." 			   TAB_2.DISUSE_FLAG IN ('0')"
		  ." 			AND"
		  ." 			   TAB_2.VARS_LINK_ID = :KEY_VARS_LINK_ID"
		  ." 			ORDER BY"
		  ." 			   TAB_1.VARS_ID"
		  ." 			  ,TAB_1.COL_COMBINATION_MEMBER_ALIAS"
		;
		/* クエリーバインド */
		$aryForBind['KEY_VARS_LINK_ID'] = $strVarsLinkIdNumeric;

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

	/* フォームの表示直後、選択できる選択肢リストを作成する関数 */
	$objFunction04 = function($objCellFormatter, $arraySelectElement,$data,$boolWhiteKeyAdd,$varAddResultData,&$aryVariant,&$arySetting,&$aryOverride){
		global $g;
		$aryRetBody = array();
		$intErrorType = null;
		$aryErrMsgBody = array();
		$strErrMsg = "";

		$strMsgBody01 = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7301204") ; // Message：入力不要

		$strOptionBodies = "";
		$strNoOptionMessageText = "";

		$strHiddenInputBody = "<input type=\"hidden\" name=\"".$objCellFormatter->getFSTNameForIdentify()."\" value=\"\"/>";
		$strNoOptionMessageText = $strHiddenInputBody.$objCellFormatter->getFADNoOptionMessageText();

		/* 条件付き必須処理（条件を満たす場合、空白を選択させない） */
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

	/* フォームの表示直後、選択できる選択肢リストを作成する関数 */
	$objFunction05 = function($objCellFormatter, $arraySelectElement,$data,$boolWhiteKeyAdd,$rowData,$aryVariant){
		global $g;
		$aryRetBody = array();
		$intErrorType = null;
		$aryErrMsgBody = array();
		$strErrMsg = "";

		$strMsgBody01 = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7301205") ; // 入力不要

		$strOptionBodies = "";
		$strNoOptionMessageText = "";

		$strHiddenInputBody = "<input type=\"hidden\" name=\"".$objCellFormatter->getFSTNameForIdentify()."\" value=\"\"/>";
		$strNoOptionMessageText = $strHiddenInputBody.$objCellFormatter->getFADNoOptionMessageText();

		/* 条件付き必須処理（条件を満たす場合、空白を選択させない） */
		$boolWhiteKeyAdd = false;

		$strFxName = "";
		$aryAddResultData = array();

		$strVarsLinkIdNumeric = $rowData['KEY_VARS_LINK_ID'];

		/* <START> 親変数か否やを調べる----------------------------------------------------------------------------------------------------- */
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
			  ." 			WHERE"
			  ." 			   TAB_1.DISUSE_FLAG IN ('0')"
			  ." 			AND"
			  ." 			   TAB_1.VARS_LINK_ID = :KEY_VARS_LINK_ID"
			;
			/* クエリーバインド */
			$aryForBind['KEY_VARS_LINK_ID'] = $strVarsLinkIdNumeric;

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
		/* < END > 親変数か否やを調べる----------------------------------------------------------------------------------------------------- */

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

	$strSetInnerText = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7301206") ; // Message:変数名を選択して下さい

	/* 更新時のonChange設定 */
	$objVarBFmtUpd = new SelectTabBFmt();
	$objVarBFmtUpd->setFADJsEvent('onChange','key_chlVar_upd');

	/* フォームの表示直後、変更反映カラムの既存値が、選べる選択肢の中になかった場合のメッセージ */
	$objVarBFmtUpd->setNoOptionMessageText($strSetInnerText);
	/* フォームの表示後、ユーザによりトリガーカラムが選ばれたが、選べる選択肢がなかった場合のメッセージ */
	$objVarBFmtUpd->setFADNoOptionMessageText($strSetInnerText);

	/* フォームの表示直後、選択できる選択肢リストを作成する関数指定 */
	$objVarBFmtUpd->setFunctionForGetSelectList($objFunction03);
	$objVarBFmtUpd->setFunctionForGetFADMainDataOverride($objFunction04);
	$objVarBFmtUpd->setFunctionForGetMainDataOverride($objFunction05);

	/* フォームの表示後、ユーザによりトリガーカラムが選ばれたとき、選べる選択肢リストを作成する関数を指定 */
	$objOTForUpd = new OutputType(new ReqTabHFmt(), $objVarBFmtUpd);
	$objOTForUpd->setJsEvent('onChange','key_chlVar_upd');
	$objOTForUpd->setFunctionForGetFADSelectList($objFunction01);

	/* 登録時のonChange設定 */
	$objVarBFmtReg = new SelectTabBFmt();
	$objVarBFmtReg->setFADJsEvent('onChange','key_chlVar_reg');

	/* フォームの表示直後、トリガーカラムが選ばれていない場合のメッセージ */
	$objVarBFmtReg->setSelectWaitingText($strSetInnerText);

	/* フォームの表示後、ユーザによりトリガーカラムが選ばれたが、選べる選択肢がなかった場合のメッセージ */
	$objVarBFmtReg->setFADNoOptionMessageText($strSetInnerText);
	$objVarBFmtReg->setFunctionForGetFADMainDataOverride($objFunction04);

	/* フォームの表示後、ユーザによりトリガーカラムが選ばれたとき、選べる選択肢リストを作成する関数を指定 */
	$objOTForReg = new OutputType(new ReqTabHFmt(), $objVarBFmtReg);
	$objOTForReg->setFunctionForGetFADSelectList($objFunction02);

	$c->setOutputType('update_table',$objOTForUpd);
	$c->setOutputType('register_table',$objOTForReg);

	$c->setRequired(false);

	//エクセル/CSVからのアップロードを禁止する。
	$c->setAllowSendFromFile(false);

	// REST/excel/csvで項目無効
	$c->getOutputType('excel')->setVisible(false);
	$c->getOutputType('csv')->setVisible(false);
	$c->getOutputType('json')->setVisible(false);

	// データベース更新前のファンクション登録
	$c->setFunctionForEvent('beforeTableIUDAction',$tmpObjFunction);

	$cg->addColumn($c);

	/* 関数の割当をを解放 */
	unset($objFunction01);
	unset($objFunction02);
	unset($objFunction03);
	unset($objFunction04);
	unset($objFunction05);


	/* Key変数　Movement+変数名_Excel/CSV/RestAPI用 */
	$c = new IDColumn('REST_KEY_VARS_LINK_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7371202"),'D_ANSTWR_PTN_VARS_LINK','VARS_LINK_ID','PTN_VAR_PULLDOWN','',array('OrderByThirdColumn'=>'VARS_LINK_ID'));
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7381202"));
	$c->setHiddenMainTableColumn(false); //更新対象カラムから外す
	$c->setJournalTableOfMaster('D_ANSTWR_PTN_VARS_LINK_JNL');
	$c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c->setJournalKeyIDOfMaster('VARS_LINK_ID');
	$c->setJournalDispIDOfMaster('PTN_VAR_PULLDOWN');

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

	//エクセル/CSVからのアップロード対象
	$c->setAllowSendFromFile(true);

	//登録/更新時には、必須でない
	$c->setRequired(false);

	$cg->addColumn($c);


	/* Keyメンバー変数　変数名+メンバー変数_Excel/CSV/RestAPI用 */
	$c = new IDColumn('REST_KEY_NESTEDMEM_COL_CMB_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7371203"),'D_ANSTWR_NESTEDMEM_COL_CMB','NESTEDMEM_COL_CMB_ID','VAR_MEMBER_PULLDOWN','');
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7381203"));
	$c->setHiddenMainTableColumn(false); //更新対象カラムから外す
	$c->setJournalTableOfMaster('D_ANSTWR_NESTEDMEM_COL_CMB_JNL');
	$c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c->setJournalKeyIDOfMaster('NESTEDMEM_COL_CMB_ID');
	$c->setJournalDispIDOfMaster('VAR_MEMBER_PULLDOWN');

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

	//エクセル/CSVからのアップロード対象
	$c->setAllowSendFromFile(true);

	//登録/更新時には、必須でない
	$c->setRequired(false);

	$cg->addColumn($c);
	// 2018/05/25 #3084 Add End ----


	/* 代入順序 */
	/* <START> 条件別にKeyの代入順序の入力制限をする---------------------------------------------------------------------------------------- */
	$objFunction01 = function($strTagInnerBody,$objCellFormatter,$rowData,$aryVariant,$aryAddOnDefault,$aryOverWrite){
		global $g;

		$strMsgBody01 = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7301207"); // Message：入力不要

		list($strVarsLinkIdNumeric,$tmpBoolKeyExist)=isSetInArrayNestThenAssign($rowData,array('KEY_VARS_LINK_ID'),null);
		$strFxName = "";
		/* <START> 親変数か否やを調べる----------------------------------------------------------------------------------------------------- */
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
			  ." 			WHERE"
			  ." 			   TAB_1.DISUSE_FLAG IN ('0')"
			  ." 			AND"
			  ." 			   TAB_1.VARS_LINK_ID = :KEY_VARS_LINK_ID"
			;
			/* クエリーバインド */
			$aryForBind['KEY_VARS_LINK_ID'] = $strVarsLinkIdNumeric;

			$aryRetBody = singleSQLExecuteAgent($strQuery, $aryForBind, $strFxName);
			if( $aryRetBody[0] === true ){
				$objQuery = $aryRetBody[1];

				$tmpAryRow = array();
				while($row = $objQuery->resultFetch() ){ $tmpAryRow[]= $row ; }
				if( count($tmpAryRow) === 1 ){
					$tmpRow = $tmpAryRow[0];
					if(2 == $tmpRow['VARS_ATTR_ID']){
						$intVarType = 1;
					}
					else if(3 == $tmpRow['VARS_ATTR_ID']){
						if(0 < strlen($rowData['KEY_ASSIGN_SEQ'])){
							$intVarType = 1;
						}else{
							$intVarType = 0;
						}
					}else{
						$intVarType = 0;
					}
				}else{ $intErrorType = 502 ; }
				unset($tmpRow);
				unset($tmpAryRow);
				unset($objQuery);
			}else{ $intErrorType = 503 ; }
		}
		/* < END > 親変数か否やを調べる----------------------------------------------------------------------------------------------------- */

		if( $intVarType !== 1 ){ $aryOverWrite["value"] = "" ; } 
		$retBody = "<input {$objCellFormatter->printAttrs($aryAddOnDefault,$aryOverWrite)} {$objCellFormatter->printJsAttrs($rowData)} {$objCellFormatter->getTextTagLastAttr()}>";
		$retBody = $retBody."<div style=\"display:none\" id=\"after_".$objCellFormatter->getFSTIDForIdentify()."\">".$strMsgBody01."</div><br/>";
		$retBody = $retBody."<div style=\"display:none\" id=\"init_var_type_".$objCellFormatter->getFSTIDForIdentify()."\">".$intVarType."</div>";

		return $retBody;
	};

	$objFunction02 = $objFunction01;
	/* < END > 条件別にKey代入順序の入力制限をする------------------------------------------------------------------------------------------ */

	$objVarBFmtUpd = new NumInputTabBFmt(0,false);
	$objVarBFmtUpd->setFunctionForReturnOverrideGetData($objFunction01);
	$objVarBFmtReg = new NumInputTabBFmt(0,false);
	$objVarBFmtReg->setFunctionForReturnOverrideGetData($objFunction02);

	$objOTForUpd = new OutputType(new ReqTabHFmt(), $objVarBFmtUpd);
	$objOTForReg = new OutputType(new ReqTabHFmt(), $objVarBFmtReg);

	$c = new NumColumn('KEY_ASSIGN_SEQ',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7391212"));
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7351212"));

	$c->setHiddenMainTableColumn(true); //更新対象カラム

	$c->setOutputType('update_table',$objOTForUpd);
	$c->setOutputType('register_table',$objOTForReg);
	$c->setSubtotalFlag(false);
	$c->setValidator(new IntNumValidator(1,null));

	$cg->addColumn($c);

	unset($objFunction01);
	unset($objFunction02);

    $cgg->addColumn($cg);
	/* < END > （カラムグループ）key変数 --------------------------------------------------------------------------------------------- */

	/* <START> （カラムグループ）value変数 ------------------------------------------------------------------------------------------- */
	$cg = new ColumnGroup($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7311204"));

	/* 変数名_一覧のみ表示 */
	/* RestAPI/Excel/CSVからの登録の場合に組み合わせバリデータで退避したVALUE_VARS_LINK_IDを設定する。 */
	$tmpObjFunction = function($objColumn, $strEventKey, &$exeQueryData, &$reqOrgData=array(), &$aryVariant=array()){
		global $g;

		$boolRet       = true;
		$intErrorType  = null;
		$aryErrMsgBody = array();
		$strErrMsg     = "";
		$strErrorBuf   = "";

		/* 登録/更新のテーブル領域に、プルダウンリストHtmlタグを、事後的に作成する為の設定 */
		$modeValue = $aryVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_MODE"];
		if( $modeValue=="DTUP_singleRecRegister" || $modeValue=="DTUP_singleRecUpdate" ){
			if(strlen($g['VALUE_VARS_LINK_ID_UPDATE_VALUE']) !== 0){
				$exeQueryData[$objColumn->getID()] = $g['VALUE_VARS_LINK_ID_UPDATE_VALUE'];
			}
		}
		else if( $modeValue=="DTUP_singleRecDelete" ){}
		$retArray = array($boolRet,$intErrorType,$aryErrMsgBody,$strErrMsg,$strErrorBuf);

		return $retArray;
	};

	$c = new IDColumn('VALUE_VARS_LINK_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7391213"),'D_ANSTWR_PTN_VARS_LINK','VARS_LINK_ID','VARS_PULLDOWN','');
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7351213"));
	$c->setHiddenMainTableColumn(true); //更新対象カラム
	$c->setJournalTableOfMaster('D_ANSTWR_PTN_VARS_LINK_JNL');
	$c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c->setJournalKeyIDOfMaster('VARS_LINK_ID');
	$c->setJournalDispIDOfMaster('VARS_PULLDOWN');

	/* フォームの表示後、ユーザによりトリガーカラムが選ばれたとき、選べる選択肢リストを作成する関数 */
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
		 = " 			SELECT "
		  ." 			   TAB_1.VARS_LINK_ID AS KEY_COLUMN"
		  ." 			  ,TAB_1.VARS_PULLDOWN AS DISP_COLUMN"
		  ." 			FROM"
		  ." 			   D_ANSTWR_PTN_VARS_LINK AS TAB_1"
		  ." 			WHERE"
		  ." 			   TAB_1.DISUSE_FLAG IN ('0')"
		  ." 			AND"
		  ." 			   TAB_1.PATTERN_ID = :PATTERN_ID"
		  ." 			ORDER BY"
		  ." 			   KEY_COLUMN ASC "
		  ;

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

	/* フォームの表示直後、選択できる選択肢リストを作成する関数 */
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

		$aryForBind['PATTERN_ID']		= $strPatternIdNumeric;

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

	//$strSetInnerText = '作業パターンを選択して下さい'
	$strSetInnerText = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7301208");
	$objVarBFmtUpd = new SelectTabBFmt();
	$objVarBFmtUpd->setFADJsEvent('onChange','vars_upd');

	// フォームの表示直後、変更反映カラムの既存値が、選べる選択肢の中になかった場合のメッセージ
	$objVarBFmtUpd->setNoOptionMessageText($strSetInnerText);

	// フォームの表示後、ユーザによりトリガーカラムが選ばれたが、選べる選択肢がなかった場合のメッセージ
	$objVarBFmtUpd->setFADNoOptionMessageText($strSetInnerText);

	// フォームの表示直後、選択できる選択肢リストを作成する関数指定
	$objVarBFmtUpd->setFunctionForGetSelectList($objFunction03);

	$objOTForUpd = new OutputType(new ReqTabHFmt(), $objVarBFmtUpd);

	$objOTForUpd->setJsEvent('onChange','vars_upd');

	// フォームの表示後、ユーザによりトリガーカラムが選ばれたとき、選べる選択肢リストを作成する関数を指定
	$objOTForUpd->setFunctionForGetFADSelectList($objFunction01);

	$objVarBFmtReg = new SelectTabBFmt();

	$objVarBFmtReg->setFADJsEvent('onChange','vars_reg');

	// フォームの表示直後、トリガーカラムが選ばれていない場合のメッセージ
	$objVarBFmtReg->setSelectWaitingText($strSetInnerText);

	// フォームの表示後、ユーザによりトリガーカラムが選ばれたが、選べる選択肢がなかった場合のメッセージ
	$objVarBFmtReg->setFADNoOptionMessageText($strSetInnerText);

	$objOTForReg = new OutputType(new ReqTabHFmt(), $objVarBFmtReg);

	// フォームの表示後、ユーザによりトリガーカラムが選ばれたとき、選べる選択肢リストを作成する関数を指定
	$objOTForReg->setFunctionForGetFADSelectList($objFunction02);

	$c->setOutputType('update_table',$objOTForUpd);
	$c->setOutputType('register_table',$objOTForReg);

	//$c->setRequired(true);//登録/更新時には、入力必須
	// 必須チェックは組合せバリデータで行う。
	$c->setRequired(false);

	//エクセル/CSVからのアップロードを禁止する。
	$c->setAllowSendFromFile(false);

	// REST/excel/csvで項目無効
	$c->getOutputType('excel')->setVisible(false);
	$c->getOutputType('csv')->setVisible(false);
	$c->getOutputType('json')->setVisible(false);

	// データベース更新前のファンクション登録
	$c->setFunctionForEvent('beforeTableIUDAction',$tmpObjFunction);

	$cg->addColumn($c);

	unset($objFunction01);
	unset($objFunction02);
	unset($objFunction03);


	/* メンバー変数名_一覧のみ表示 */
	/* RestAPI/Excel/CSVからの登録の場合に組み合わせバリデータで退避したVALUE_NESTEDMEM_COL_CMB_IDを設定する。 */
	$tmpObjFunction = function($objColumn, $strEventKey, &$exeQueryData, &$reqOrgData=array(), &$aryVariant=array()){
		global $g;

		$boolRet       = true;
		$intErrorType  = null;
		$aryErrMsgBody = array();
		$strErrMsg     = "";
		$strErrorBuf   = "";

		/* 登録/更新のテーブル領域に、プルダウンリストHtmlタグを、事後的に作成する為の設定 */
		$modeValue = $aryVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_MODE"];
		if( $modeValue=="DTUP_singleRecRegister" || $modeValue=="DTUP_singleRecUpdate" ){
			if(strlen($g['VALUE_NESTEDMEM_COL_CMB_ID_UPDATE_VALUE']) !== 0){
				$exeQueryData[$objColumn->getID()] = $g['VALUE_NESTEDMEM_COL_CMB_ID_UPDATE_VALUE'];
			}
		}
		else if( $modeValue=="DTUP_singleRecDelete" ){}
		$retArray = array($boolRet,$intErrorType,$aryErrMsgBody,$strErrMsg,$strErrorBuf);

		return $retArray;
	};

	$c = new IDColumn('VALUE_NESTEDMEM_COL_CMB_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7391214"),'D_ANSTWR_NESTEDMEM_COL_CMB','NESTEDMEM_COL_CMB_ID','COL_COMBINATION_MEMBER_ALIAS','');
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7351214"));
	$c -> setHiddenMainTableColumn(true); //更新対象カラム
	$c -> setJournalTableOfMaster('D_ANSTWR_NESTEDMEM_COL_CMB_JNL');
	$c -> setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c -> setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');

	/* フォームの表示後、ユーザによりトリガーカラムが選ばれたとき、選べる選択肢リストを作成する関数 */
	$objFunction01 = function($objOutputType, $aryVariant, $arySetting, $aryOverride, $objColumn){
		global $g;
		$retBool = false;
		$intErrorType = null;
		$aryErrMsgBody = array();
		$strErrMsg = "";
		$aryDataSet = array();
		$aryAddResultData = array();
		$strFxName = "";
		$strVarsLinkIdNumeric = $aryVariant['VALUE_VARS_LINK_ID'];
		$strColSeqCombinationId = $aryVariant['VALUE_NESTEDMEM_COL_CMB_ID'];

		/* <START> 親変数か否やを調べる----------------------------------------------------------------------------------------------------- */
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
			  ." 			WHERE"
			  ." 			   TAB_1.DISUSE_FLAG IN ('0')"
			  ." 			AND"
			  ." 			   TAB_1.VARS_LINK_ID = :VALUE_VARS_LINK_ID"
			;
			/* クエリーバインド */
			$aryForBind['VALUE_VARS_LINK_ID'] = $strVarsLinkIdNumeric;

			$aryRetBody = singleSQLExecuteAgent($strQuery, $aryForBind, $strFxName);

			if( $aryRetBody[0] === true ){
				$objQuery = $aryRetBody[1];

				$tmpAryRow = array();
				while($row = $objQuery->resultFetch() ){
					$tmpAryRow[]= $row;
				}
				if( count($tmpAryRow) === 1 ){
					$tmpRow = $tmpAryRow[0];
					if(1 == $tmpRow['VARS_ATTR_ID']){
						$intVarType = 0;
						$aryAddResultData[] = "NORMAL_VAR_0";
					}
					else if(2 == $tmpRow['VARS_ATTR_ID']){
						$intVarType = 0;
						$aryAddResultData[] = "NORMAL_VAR_1";
					}
					else if(3 == $tmpRow['VARS_ATTR_ID']){
						$intVarType = 1;
						$aryAddResultData[] = "PARENT_VAR";
					}
					else {
						$intErrorType = 501;
					}
				}else{
					$intErrorType = 502;
				}
				unset($tmpAryRow);
				unset($objQuery);
			}else{
				$intErrorType = 503;
			}
		}
		/* < END > 親変数か否やを調べる----------------------------------------------------------------------------------------------------- */

		/* <START> 親変数だった場合、リストを作成する--------------------------------------------------------------------------------------- */
		if( $intVarType === 1 ){
			$strQuery
			 = " 			SELECT"
			  ." 			   TAB_1.NESTEDMEM_COL_CMB_ID AS KEY_COLUMN"
			  ." 			  ,TAB_1.COL_COMBINATION_MEMBER_ALIAS AS DISP_COLUMN "
			  ." 			FROM"
			  ." 			   B_ANSTWR_NESTEDMEM_COL_CMB AS TAB_1"
			  ." 			LEFT JOIN"
			  ." 			   D_ANSTWR_PTN_VARS_LINK AS TAB_2"
			  ." 			 ON"
			  ." 			   TAB_1.VARS_ID = TAB_2.VARS_ID"
			  ." 			WHERE"
			  ." 			   TAB_1.DISUSE_FLAG IN ('0')"
			  ." 			AND"
			  ." 			   TAB_2.DISUSE_FLAG IN ('0')"
			  ." 			AND"
			  ." 			   TAB_2.VARS_LINK_ID = :VALUE_VARS_LINK_ID"
			  ." 			ORDER BY"
			  ." 			   TAB_1.VARS_ID"
			  ." 			  ,TAB_1.COL_COMBINATION_MEMBER_ALIAS"
			;
			/* クエリーバインド */
			$aryForBind['VALUE_VARS_LINK_ID'] = $strVarsLinkIdNumeric;

			if( 0 < strlen($strVarsLinkIdNumeric) ){
				$aryRetBody = singleSQLExecuteAgent($strQuery, $aryForBind, $strFxName);
				if( $aryRetBody[0] === true ){
					$objQuery = $aryRetBody[1];
					while($row = $objQuery->resultFetch() ){
						$aryDataSet[]= $row;
					}
					unset($objQuery);
					$retBool = true;
				}else{
					$intErrorType = 504;
				}
			}

			/* Functionを一意にする */
			if(
				$tmpRow['VARS_ATTR_ID'] == 3
			  AND
				strlen($strColSeqCombinationId) > 0
			){
				/* 変数管理テーブルから取得 */
				$aryResult = getChildVars_2100140009($strVarsLinkIdNumeric, $strColSeqCombinationId);

				/* 代入順序の有無判定処理 */
				if(
					gettype($aryResult) === "array"
				  AND
					count($aryResult) === 1
				){
					if( $aryResult[0]['VARS_LINK_ID'] == $strVarsLinkIdNumeric){
						if(1 == $aryResult[0]['ASSIGN_SEQ_NEED']){
							$aryAddResultData[0] = "MEMBER_VAR_1" ;
						}else{
							$aryAddResultData[0] = "MEMBER_VAR_0";
						}
					}
					else{
						$aryAddResultData[0] = "MEMBER_VAR_323";
					}
				}
				else if($aryResult === false){
					$intErrorType = 505 ;
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

	/* フォームの表示直後、選択できる選択肢リストを作成する関数 */
	$objFunction02 = $objFunction01;

	/* フォームの表示直後、選択できる選択肢リストを作成する関数 */
	$objFunction03 = function($objCellFormatter, $rowData, $aryVariant){
		global $g;
		$retBool = false;
		$intErrorType = null;
		$aryErrMsgBody = array();
		$strErrMsg = "";
		$aryDataSet = array();

		$strFxName = "";

		$strVarsLinkIdNumeric = $rowData['VALUE_VARS_LINK_ID'];

		$strQuery
		 = " 			SELECT"
		  ." 			   TAB_1.NESTEDMEM_COL_CMB_ID AS KEY_COLUMN"
		  ." 			  ,TAB_1.COL_COMBINATION_MEMBER_ALIAS AS DISP_COLUMN "
		  ." 			FROM"
		  ." 			   B_ANSTWR_NESTEDMEM_COL_CMB AS TAB_1"
		  ." 			LEFT JOIN"
		  ." 			   D_ANSTWR_PTN_VARS_LINK AS TAB_2"
		  ." 			 ON"
		  ." 			   TAB_1.VARS_ID = TAB_2.VARS_ID"
		  ." 			WHERE"
		  ." 			   TAB_1.DISUSE_FLAG IN ('0')"
		  ." 			AND"
		  ." 			   TAB_2.DISUSE_FLAG IN ('0')"
		  ." 			AND"
		  ." 			   TAB_2.VARS_LINK_ID = :VALUE_VARS_LINK_ID"
		  ." 			ORDER BY"
		  ." 			   TAB_1.VARS_ID"
		  ." 			  ,TAB_1.COL_COMBINATION_MEMBER_ALIAS"
		;
		/* クエリーバインド */
		$aryForBind['VALUE_VARS_LINK_ID'] = $strVarsLinkIdNumeric;

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

	/* フォームの表示直後、選択できる選択肢リストを作成する関数 */
	$objFunction04 = function($objCellFormatter, $arraySelectElement,$data,$boolWhiteKeyAdd,$varAddResultData,&$aryVariant,&$arySetting,&$aryOverride){
		global $g;
		$aryRetBody = array();
		$intErrorType = null;
		$aryErrMsgBody = array();
		$strErrMsg = "";

		$strMsgBody01 = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7301209") ; // Message：入力不要

		$strOptionBodies = "";
		$strNoOptionMessageText = "";

		$strHiddenInputBody = "<input type=\"hidden\" name=\"".$objCellFormatter->getFSTNameForIdentify()."\" value=\"\"/>";
		$strNoOptionMessageText = $strHiddenInputBody.$objCellFormatter->getFADNoOptionMessageText();

		/* 条件付き必須処理（条件を満たす場合、空白を選択させない） */
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

	/* フォームの表示直後、選択できる選択肢リストを作成する関数 */
	$objFunction05 = function($objCellFormatter, $arraySelectElement,$data,$boolWhiteKeyAdd,$rowData,$aryVariant){
		global $g;
		$aryRetBody = array();
		$intErrorType = null;
		$aryErrMsgBody = array();
		$strErrMsg = "";

		$strMsgBody01 = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7301210") ; // 入力不要

		$strOptionBodies = "";
		$strNoOptionMessageText = "";

		$strHiddenInputBody = "<input type=\"hidden\" name=\"".$objCellFormatter->getFSTNameForIdentify()."\" value=\"\"/>";
		$strNoOptionMessageText = $strHiddenInputBody.$objCellFormatter->getFADNoOptionMessageText();

		/* 条件付き必須処理（条件を満たす場合、空白を選択させない） */
		$boolWhiteKeyAdd = false;

		$strFxName = "";
		$aryAddResultData = array();

		$strVarsLinkIdNumeric = $rowData['VALUE_VARS_LINK_ID'];

		/* <START> 親変数か否やを調べる----------------------------------------------------------------------------------------------------- */
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
			  ." 			WHERE"
			  ." 			   TAB_1.DISUSE_FLAG IN ('0')"
			  ." 			AND"
			  ." 			   TAB_1.VARS_LINK_ID = :VALUE_VARS_LINK_ID"
			;
			/* クエリーバインド */
			$aryForBind['VALUE_VARS_LINK_ID'] = $strVarsLinkIdNumeric;

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
		/* < END > 親変数か否やを調べる----------------------------------------------------------------------------------------------------- */

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

	$strSetInnerText = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7301211") ; // Message：変数名を選択して下さい

	/* 更新時のonChange設定 */
	$objVarBFmtUpd = new SelectTabBFmt();
	$objVarBFmtUpd->setFADJsEvent('onChange','val_chlVar_upd');

	/* フォームの表示直後、変更反映カラムの既存値が、選べる選択肢の中になかった場合のメッセージ */
	$objVarBFmtUpd->setNoOptionMessageText($strSetInnerText);

	/* フォームの表示後、ユーザによりトリガーカラムが選ばれたが、選べる選択肢がなかった場合のメッセージ */
	$objVarBFmtUpd->setFADNoOptionMessageText($strSetInnerText);

	/* フォームの表示直後、選択できる選択肢リストを作成する関数指定 */
	$objVarBFmtUpd->setFunctionForGetSelectList($objFunction03);
	$objVarBFmtUpd->setFunctionForGetFADMainDataOverride($objFunction04);
	$objVarBFmtUpd->setFunctionForGetMainDataOverride($objFunction05);

	/* フォームの表示後、ユーザによりトリガーカラムが選ばれたとき、選べる選択肢リストを作成する関数を指定 */
	$objOTForUpd = new OutputType(new ReqTabHFmt(), $objVarBFmtUpd);
	$objOTForUpd->setJsEvent('onChange','val_chlVar_upd');
	$objOTForUpd->setFunctionForGetFADSelectList($objFunction01);

	/* 登録時のonChange設定 */
	$objVarBFmtReg = new SelectTabBFmt();
	$objVarBFmtReg->setFADJsEvent('onChange','val_chlVar_reg');

	/* フォームの表示直後、トリガーカラムが選ばれていない場合のメッセージ */
	$objVarBFmtReg->setSelectWaitingText($strSetInnerText);

	/* フォームの表示後、ユーザによりトリガーカラムが選ばれたが、選べる選択肢がなかった場合のメッセージ */
	$objVarBFmtReg->setFADNoOptionMessageText($strSetInnerText);
	$objVarBFmtReg->setFunctionForGetFADMainDataOverride($objFunction04);

	/* フォームの表示後、ユーザによりトリガーカラムが選ばれたとき、選べる選択肢リストを作成する関数を指定 */
	$objOTForReg = new OutputType(new ReqTabHFmt(), $objVarBFmtReg);
	$objOTForReg->setFunctionForGetFADSelectList($objFunction02);

	$c->setOutputType('update_table',$objOTForUpd);
	$c->setOutputType('register_table',$objOTForReg);

	// 必須チェックは組合せバリデータで行う。
	$c->setRequired(false);

	//エクセル/CSVからのアップロードを禁止する。
	$c->setAllowSendFromFile(false);

	// REST/excel/csvで項目無効
	$c->getOutputType('excel')->setVisible(false);
	$c->getOutputType('csv')->setVisible(false);
	$c->getOutputType('json')->setVisible(false);

	// データベース更新前のファンクション登録
	$c->setFunctionForEvent('beforeTableIUDAction',$tmpObjFunction);

	$cg->addColumn($c);

	/* 関数の割当をを解放 */
	unset($objFunction01);
	unset($objFunction02);
	unset($objFunction03);
	unset($objFunction04);
	unset($objFunction05);


	/* Value変数　Movement+変数名_Excel/CSV/RestAPI用 */
	$c = new IDColumn('REST_VAL_VARS_LINK_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7371204"),'D_ANSTWR_PTN_VARS_LINK','VARS_LINK_ID','PTN_VAR_PULLDOWN','',array('OrderByThirdColumn'=>'VARS_LINK_ID'));
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7381204"));
	$c->setHiddenMainTableColumn(false); //更新対象カラムから外す
	$c->setJournalTableOfMaster('D_ANSTWR_PTN_VARS_LINK_JNL');
	$c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c->setJournalKeyIDOfMaster('VARS_LINK_ID');
	$c->setJournalDispIDOfMaster('PTN_VAR_PULLDOWN');

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

	//エクセル/CSVからのアップロード対象
	$c->setAllowSendFromFile(true);

	//登録/更新時には、必須でない
	$c->setRequired(false);

	$cg->addColumn($c);


	/* Keyメンバー変数　変数名+メンバー変数_Excel/CSV/RestAPI用 */
	$c = new IDColumn('REST_VAL_NESTEDMEM_COL_CMB_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7371205"),'D_ANSTWR_NESTEDMEM_COL_CMB','NESTEDMEM_COL_CMB_ID','VAR_MEMBER_PULLDOWN','');
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7381205"));
	$c->setHiddenMainTableColumn(false); //更新対象カラムから外す
	$c->setJournalTableOfMaster('D_ANSTWR_NESTEDMEM_COL_CMB_JNL');
	$c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c->setJournalKeyIDOfMaster('NESTEDMEM_COL_CMB_ID');
	$c->setJournalDispIDOfMaster('VAR_MEMBER_PULLDOWN');

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

	//エクセル/CSVからのアップロード対象
	$c->setAllowSendFromFile(true);

	//登録/更新時には、必須でない
	$c->setRequired(false);

	$cg->addColumn($c);


	/* 代入順序 */
	/* <START> 条件別にValue代入順序の入力制限をする---------------------------------------------------------------------------------------- */
	$objFunction01 = function($strTagInnerBody,$objCellFormatter,$rowData,$aryVariant,$aryAddOnDefault,$aryOverWrite){
		global $g;

		$strMsgBody01 = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7301212"); // Message:入力不要

		list($strVarsLinkIdNumeric,$tmpBoolKeyExist)=isSetInArrayNestThenAssign($rowData,array('VALUE_VARS_LINK_ID'),null);
		$strFxName = "";
		/* <START> 親変数か否やを調べる----------------------------------------------------------------------------------------------------- */
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
			  ." 			WHERE"
			  ." 			   TAB_1.DISUSE_FLAG IN ('0')"
			  ." 			AND"
			  ." 			   TAB_1.VARS_LINK_ID = :VALUE_VARS_LINK_ID"
			;
			/* クエリーバインド */
			$aryForBind['VALUE_VARS_LINK_ID'] = $strVarsLinkIdNumeric;

			$aryRetBody = singleSQLExecuteAgent($strQuery, $aryForBind, $strFxName);
			if( $aryRetBody[0] === true ){
				$objQuery = $aryRetBody[1];

				$tmpAryRow = array();
				while($row = $objQuery->resultFetch() ){ $tmpAryRow[]= $row ; }
				if( count($tmpAryRow) === 1 ){
					$tmpRow = $tmpAryRow[0];
					if(2 == $tmpRow['VARS_ATTR_ID']){
						$intVarType = 1;
					}
					else if(3 == $tmpRow['VARS_ATTR_ID']){
						if(0 < strlen($rowData['VALUE_ASSIGN_SEQ'])){
							$intVarType = 1;
						}else{
							$intVarType = 0;
						}
					}else{
						$intVarType = 0;
					}
				}else{ $intErrorType = 502 ; }
				unset($tmpRow);
				unset($tmpAryRow);
				unset($objQuery);
			}else{ $intErrorType = 503 ; }
		}
		/* < END > 親変数か否やを調べる----------------------------------------------------------------------------------------------------- */

		if( $intVarType !== 1 ){ $aryOverWrite["value"] = "" ; } 
		$retBody = "<input {$objCellFormatter->printAttrs($aryAddOnDefault,$aryOverWrite)} {$objCellFormatter->printJsAttrs($rowData)} {$objCellFormatter->getTextTagLastAttr()}>";
		$retBody = $retBody."<div style=\"display:none\" id=\"after_".$objCellFormatter->getFSTIDForIdentify()."\">".$strMsgBody01."</div><br/>";
		$retBody = $retBody."<div style=\"display:none\" id=\"init_var_type_".$objCellFormatter->getFSTIDForIdentify()."\">".$intVarType."</div>";

		return $retBody;
	};

	$objFunction02 = $objFunction01;
	/* < END > 条件別にValue代入順序の入力制限をする---------------------------------------------------------------------------------------- */

	$objVarBFmtUpd = new NumInputTabBFmt(0,false);
	$objVarBFmtUpd->setFunctionForReturnOverrideGetData($objFunction01);
	$objVarBFmtReg = new NumInputTabBFmt(0,false);
	$objVarBFmtReg->setFunctionForReturnOverrideGetData($objFunction02);

	$objOTForUpd = new OutputType(new ReqTabHFmt(), $objVarBFmtUpd);
	$objOTForReg = new OutputType(new ReqTabHFmt(), $objVarBFmtReg);

	$c = new NumColumn('VALUE_ASSIGN_SEQ',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7391215"));
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7351215"));

	$c->setHiddenMainTableColumn(true); //更新対象カラム

	$c->setOutputType('update_table',$objOTForUpd);
	$c->setOutputType('register_table',$objOTForReg);
	$c->setSubtotalFlag(false);
	$c->setValidator(new IntNumValidator(1,null));

	$cg->addColumn($c);

	unset($objFunction01);
	unset($objFunction02);

	//$table->addColumn($cg);
    $cgg->addColumn($cg);
	/* < END > （カラムグループ）value変数 ------------------------------------------------------------------------------------------- */

    //////////////////////////////////////////////////
    // ColumnGroup:IaC変数 終了                     //
    //////////////////////////////////////////////////
    $table->addColumn($cgg);

    ////////////////////////////////////////////////////////////////////
    // パラメータシートの具体値がNULLでも代入値管理に登録するかのフラグ
    ////////////////////////////////////////////////////////////////////
    $c = new IDColumn('NULL_DATA_HANDLING_FLG',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390513"),'B_VALID_INVALID_MASTER','FLAG_ID','FLAG_NAME','', array('OrderByThirdColumn'=>'FLAG_ID'));
    $c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350513"));
    $c->setHiddenMainTableColumn(true); //更新対象カラム

    $c->setRequired(false);

    //コンテンツのソースがヴューの場合、登録/更新の対象とする
    $c->setHiddenMainTableColumn(true);

    //エクセル/CSVからのアップロードを禁止する。
    $c->setAllowSendFromFile(true);

    $table->addColumn($c);
    // #20181109 2018/11/09 append end

	/* カラムを確定させる */
	$table->fixColumn();

	/* <START> 組み合わせバリデータ--------------------------------------------------------------------------------------------------------- */
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

		/* UPD */
		$pattan_tbl = "E_ANSTWR_PATTERN";

		$aryVariantForIsValid = $objClientValidator->getVariantForIsValid();

		if(array_key_exists("TCA_PRESERVED", $arrayVariant)){
			if(array_key_exists("TCA_ACTION", $arrayVariant["TCA_PRESERVED"])){
				$aryTcaAction = $arrayVariant["TCA_PRESERVED"]["TCA_ACTION"];
				$strModeId = $aryTcaAction["ACTION_MODE"];
			}
		}

		/* <START> 更新前のレコードから、各カラムの値を取得--------------------------------------------------------------------------------- */
		if($strModeId == "DTUP_singleRecDelete"){
			/* (メニュー）ID */

			$rg_menu_id = isset($arrayVariant['edit_target_row']['MENU_ID'])
			 ? $arrayVariant['edit_target_row']['MENU_ID']
			 : null ;

			/* 項目 */
			$rg_column_list_id = isset($arrayVariant['edit_target_row']['MENU_COLUMN_ID'])
			 ? $arrayVariant['edit_target_row']['MENU_COLUMN_ID']
			 : null ;

			/* 登録方式 */
			$rg_col_type = isset($arrayVariant['edit_target_row']['PRMCOL_LINK_TYPE_ID'])
			 ? $arrayVariant['edit_target_row']['PRMCOL_LINK_TYPE_ID']
			 : null ;

			/* Movement */
			$rg_pattern_id = isset($arrayVariant['edit_target_row']['PATTERN_ID'])
			 ? $arrayVariant['edit_target_row']['PATTERN_ID']
			 : null ;

			/*（key）変数名 */
			$rg_key_vars_link_id = isset($arrayVariant['edit_target_row']['KEY_VARS_LINK_ID'])
			 ? $arrayVariant['edit_target_row']['KEY_VARS_LINK_ID']
			 : null ;

			/* （key）メンバー変数名 */
			$rg_key_col_seq_comb_id = isset($arrayVariant['edit_target_row']['KEY_NESTEDMEM_COL_CMB_ID'])
			 ? $arrayVariant['edit_target_row']['KEY_NESTEDMEM_COL_CMB_ID']
			 : null ;

			/* （key）代入順序 */
			$rg_key_assign_seq = isset($arrayVariant['edit_target_row']['KEY_ASSIGN_SEQ'])
			 ? $arrayVariant['edit_target_row']['KEY_ASSIGN_SEQ']
			 : null ;

			/* （value）変数名 */
			$rg_val_vars_link_id	= isset($arrayVariant['edit_target_row']['VALUE_VARS_LINK_ID'])
			 ? $arrayVariant['edit_target_row']['VALUE_VARS_LINK_ID']
			 : null ; 

			/* （valeu）メンバー変数名 */
			$rg_val_col_seq_comb_id = isset($arrayVariant['edit_target_row']['VALUE_NESTEDMEM_COL_CMB_ID'])
			 ? $arrayVariant['edit_target_row']['VALUE_NESTEDMEM_COL_CMB_ID']
			 : null ; 

			/* （value）代入順序 */
			$rg_val_assign_seq = isset($arrayVariant['edit_target_row']['VALUE_ASSIGN_SEQ'])
			 ? $arrayVariant['edit_target_row']['VALUE_ASSIGN_SEQ']
			 : null ;

			/* Excel/CSV/RestAPI 用カラムタイトル名 */
			$rg_rest_menu_column_id = isset($arrayVariant['edit_target_row']['REST_MENU_COLUMN_ID'])
			 ? $arrayVariant['edit_target_row']['REST_MENU_COLUMN_ID']
			 : null ;
			$rg_rest_key_vars_link_id = isset($arrayVariant['edit_target_row']['REST_KEY_VARS_LINK_ID'])
			 ? $arrayVariant['edit_target_row']['REST_KEY_VARS_LINK_ID']
			 : null;
			$rg_rest_key_col_seq_comb_id = isset($arrayVariant['edit_target_row']['REST_KEY_NESTEDMEM_COL_CMB_ID'])
			 ? $arrayVariant['edit_target_row']['REST_KEY_NESTEDMEM_COL_CMB_ID']
			 : null;
			$rg_rest_val_vars_link_id = isset($arrayVariant['edit_target_row']['REST_VAL_VARS_LINK_ID'])
			 ? $arrayVariant['edit_target_row']['REST_VAL_VARS_LINK_ID']
			 : null;
			$rg_rest_val_col_seq_comb_id = isset($arrayVariant['edit_target_row']['REST_VAL_NESTEDMEM_COL_CMB_ID'])
			 ? $arrayVariant['edit_target_row']['REST_VAL_NESTEDMEM_COL_CMB_ID']
			 : null;

			$modeValue_sub = $arrayVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_SUB_MODE"];//['mode_sub'];("on"/"off")
			if( $modeValue_sub == "on" ){
				//----廃止の場合はチェックしない
				$boolExecuteContinue = false;
				//廃止の場合はチェックしない----
			}
			else{
				//----復活の場合
				if(
					strlen($rg_rest_menu_column_id) === 0
				  OR
					strlen($rg_col_type) === 0
				  OR
					strlen($rg_pattern_id) === 0
				){
					$boolSystemErrorFlag = true;
				}
				//復活の場合----

				$columnId = $strNumberForRI;
			}
		/* < END > 更新前のレコードから、各カラムの値を取得--------------------------------------------------------------------------------- */
		}
		else if(
			$strModeId == "DTUP_singleRecUpdate"
		  OR
			$strModeId == "DTUP_singleRecRegister"
		){
			/* //（メニュー）ID */
			$rg_menu_id = array_key_exists('MENU_ID',$arrayRegData)
			 ? $arrayRegData['MENU_ID']
			 : null ;

			 /* 項目 */
			$rg_column_list_id = array_key_exists('MENU_COLUMN_ID',$arrayRegData)
			 ? $arrayRegData['MENU_COLUMN_ID']
			 : null ;

			/* 登録方式 */
			$rg_col_type = array_key_exists('PRMCOL_LINK_TYPE_ID',$arrayRegData)
			 ? $arrayRegData['PRMCOL_LINK_TYPE_ID']
			 : null ;

			/* Movement */
			$rg_pattern_id = array_key_exists('PATTERN_ID',$arrayRegData)
			 ? $arrayRegData['PATTERN_ID']
			 : null ;

			/* （key）変数名 */
			$rg_key_vars_link_id = array_key_exists('KEY_VARS_LINK_ID',$arrayRegData)
			 ? $arrayRegData['KEY_VARS_LINK_ID']
			 : null ;

			/* （key）メンバー変数名 */
			$rg_key_col_seq_comb_id = array_key_exists('KEY_NESTEDMEM_COL_CMB_ID',$arrayRegData)
			 ? $arrayRegData['KEY_NESTEDMEM_COL_CMB_ID']
			 : null ;

			/* （key）代入順序 */
			$rg_key_assign_seq = array_key_exists('KEY_ASSIGN_SEQ',$arrayRegData)
			 ? $arrayRegData['KEY_ASSIGN_SEQ']
			 : null ;

			/* （value）変数名 */
			$rg_val_vars_link_id = array_key_exists('VALUE_VARS_LINK_ID',$arrayRegData)
			 ? $arrayRegData['VALUE_VARS_LINK_ID']
			 : null ;

			/* （valeu）メンバー変数名 */
			$rg_val_col_seq_comb_id = array_key_exists('VALUE_NESTEDMEM_COL_CMB_ID',$arrayRegData)
			 ? $arrayRegData['VALUE_NESTEDMEM_COL_CMB_ID']
			 : null ;

			/* （valeu）代入順序 */
			$rg_val_assign_seq = array_key_exists('VALUE_ASSIGN_SEQ',$arrayRegData)
			 ? $arrayRegData['VALUE_ASSIGN_SEQ']
			 : null ;

			/* Excel/CSV/RestAPI 用カラムタイトル名 */
			$rg_rest_menu_column_id = array_key_exists('REST_MENU_COLUMN_ID',$arrayRegData)
			 ? $arrayRegData['REST_MENU_COLUMN_ID']
			 : null ;
			$rg_rest_key_vars_link_id = array_key_exists('REST_KEY_VARS_LINK_ID',$arrayRegData)
			 ? $arrayRegData['REST_KEY_VARS_LINK_ID']
			 : null ;
			$rg_rest_key_col_seq_comb_id = array_key_exists('REST_KEY_NESTEDMEM_COL_CMB_ID',$arrayRegData)
			 ? $arrayRegData['REST_KEY_NESTEDMEM_COL_CMB_ID']
			 : null ;
			$rg_rest_val_vars_link_id = array_key_exists('REST_VAL_VARS_LINK_ID',$arrayRegData)
			 ? $arrayRegData['REST_VAL_VARS_LINK_ID']
			 : null ;
			$rg_rest_val_col_seq_comb_id = array_key_exists('REST_VAL_NESTEDMEM_COL_CMB_ID',$arrayRegData)
			 ? $arrayRegData['REST_VAL_NESTEDMEM_COL_CMB_ID']
			 : null ;

			/* 主キーの値を取得する。 */
			if( $strModeId == "DTUP_singleRecUpdate" ){
				/* 更新処理の場合 */
				$columnId = $strNumberForRI;
			}
			else{
				/* 登録処理の場合 */
				$columnId = array_key_exists('PRMCOL_VARS_LINK_ID',$arrayRegData)
				 ? $arrayRegData['PRMCOL_VARS_LINK_ID']
				 : null ;
			}
		}


		$g['MENU_ID_UPDATE_VALUE']        = "";
		$g['COLUMN_LIST_ID_UPDATE_VALUE'] = "";
		//----呼出元がUIがRestAPI/Excel/CSVかを判定
		// MENU_ID;未設定 COLUMN_LIST_ID:未設定 REST_COLUMN_LIST_ID:設定 => RestAPI/Excel/CSV
		// その他はUI
		if(
			$boolExecuteContinue === true
		  AND
			$boolSystemErrorFlag === false
		){
			if(
				(strlen($rg_menu_id)        === 0)
			  AND 
				(strlen($rg_column_list_id) === 0)
			  AND
				(strlen($rg_rest_menu_column_id) !== 0)
			){
				$query
				 = " 			SELECT"
				  ." 			   TBL_A.COLUMN_LIST_ID"
				  ." 			  ,TBL_A.MENU_ID"
				  ." 			  ,COUNT(*) AS MENU_COLUMN_ID_CNT"
				  ." 			  ,("
				  ." 				  SELECT"
				  ." 					 COUNT(*)"
				  ." 				  FROM"
				  ." 					 B_CMDB_MENU_LIST AS TBL_B"
				  ." 				  WHERE"
				  ." 					 TBL_B.MENU_ID = TBL_A.MENU_ID"
				  ." 				  AND"
				  ." 					 TBL_B.DISUSE_FLAG = '0'"
				  ." 			   ) AS MENU_CNT"
				  ." 			FROM"
				  ." 			   B_CMDB_MENU_COLUMN AS TBL_A"
				  ." 			WHERE"
				  ." 			   TBL_A.COLUMN_LIST_ID = :MENU_COLUMN_ID"
				  ." 			AND"
				  ." 			   TBL_A.DISUSE_FLAG = '0'"
				;
				/* クエリーバインド */
				$aryForBind = array();
				$aryForBind['MENU_COLUMN_ID'] = $rg_rest_menu_column_id;

				$retArray = singleSQLExecuteAgent($query, $aryForBind, "NONAME_FUNC(VARS_MULTI_CHECK)");
				if( $retArray[0] === true ){
					$objQuery =& $retArray[1];
					$intCount = 0;
					$row = $objQuery->resultFetch();
					if( $row['MENU_CNT'] == '1' ){
						if( $row['MENU_COLUMN_ID_CNT'] == '1' ){
							$rg_menu_id                       = $row['MENU_ID'];
							$rg_column_list_id                = $row['COLUMN_LIST_ID'];
							$g['MENU_ID_UPDATE_VALUE']        = $rg_menu_id;
							$g['COLUMN_LIST_ID_UPDATE_VALUE'] = $rg_column_list_id;
							if($boolExecuteContinue === true){
								$boolExecuteContinue = true;
							}
						}
						else if($row['MENU_COLUMN_ID_CNT'] == '0'){
							$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-90060");
							$retBool = false;
							$boolExecuteContinue = false;
						}
						else{
							$boolSystemErrorFlag = true;
						}
					}
					else if( $row['MENU_CNT'] == '0' ){
						$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-90059");
						$retBool = false;
						$boolExecuteContinue = false;
					}else{
						$boolSystemErrorFlag = true;
					}
					unset($row);
					unset($objQuery);
				}
				else{
					$boolSystemErrorFlag = true;
				}
				unset($retArray);
			}
		}

		//登録方式のチェック
		if( $boolExecuteContinue === true && $boolSystemErrorFlag === false){
			$boolExecuteContinue = false;
			if(strlen($rg_col_type) == 0){
				$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-6601203");
				$retBool = false;
				$boolExecuteContinue = false;
			}
			else{
				switch($rg_col_type){
				  case '1':   // Value
				  case '2':   // Key
				  case '3':   // Key-Value
					$boolExecuteContinue = true;
					break;
				  default:
					$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-6601204");
					$retBool = false;
					$boolExecuteContinue = false;
					break;
				}
			}
		}

		$g['PATTERN_ID_UPDATE_VALUE']		= "";
		$g['KEY_VARS_LINK_ID_UPDATE_VALUE']  = "";
		$g['VALUE_VARS_LINK_ID_UPDATE_VALUE']  = "";
		$g['KEY_NESTEDMEM_COL_CMB_ID_UPDATE_VALUE'] = "";
		$g['VALUE_NESTEDMEM_COL_CMB_ID_UPDATE_VALUE'] = "";
		//----呼出元がUIがRestAPI/Excel/CSVかを判定
		// PATTERN_ID;未設定 KEY_VARS_LINK_ID:未設定 REST_KEY_VARS_LINK_ID:設定 => RestAPI/Excel/CSV
		// その他はUI
		$chk_pattern_id = $rg_pattern_id;
		if( $boolExecuteContinue === true && $boolSystemErrorFlag === false){
			if((strlen($chk_pattern_id)           === 0) &&
			   (strlen($rg_key_vars_link_id)      === 0) &&
			   (($rg_col_type == '2') || ($rg_col_type == '3')) &&
			   (strlen($rg_rest_key_vars_link_id) !== 0)){
				$strColType = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-6601220");
				$ret = chkVarsAssociate_2100140009($strColType ,$rg_rest_key_vars_link_id,$rg_rest_key_col_seq_comb_id,
										$rg_pattern_id, $rg_key_vars_link_id, $rg_key_col_seq_comb_id,
										$retStrBody,	$boolSystemErrorFlag);
				if($ret === false){
					$retBool = false;
					$boolExecuteContinue = false;
				}
				else{
					$g['PATTERN_ID_UPDATE_VALUE']               = $rg_pattern_id;
					$g['KEY_VARS_LINK_ID_UPDATE_VALUE']         = $rg_key_vars_link_id;
					$g['KEY_NESTEDMEM_COL_CMB_ID_UPDATE_VALUE'] = $rg_key_col_seq_comb_id;
				}
			}
		}
		if( $boolExecuteContinue === true && $boolSystemErrorFlag === false){
			if((strlen($chk_pattern_id)                === 0) &&
			   (strlen($rg_val_vars_link_id)           === 0) &&
			   (($rg_col_type == '1') || ($rg_col_type == '3')) &&
			   (strlen($rg_rest_val_vars_link_id)      !== 0)){
				$strColType = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-6601221");
				$ret = chkVarsAssociate_2100140009($strColType ,$rg_rest_val_vars_link_id,$rg_rest_val_col_seq_comb_id,
										$rg_pattern_id, $rg_val_vars_link_id, $rg_val_col_seq_comb_id,
										$retStrBody, $boolSystemErrorFlag);
				if($ret === false){
					$retBool = false;
					$boolExecuteContinue = false;
				}
				else{
					// Movementが一致しているか判定
					if(@strlen($g['PATTERN_ID_UPDATE_VALUE']) != 0){
						if($g['PATTERN_ID_UPDATE_VALUE'] != $rg_pattern_id){
							$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-6601222");
							$retBool = false;
							$boolExecuteContinue = false;
						}
					}
					$g['PATTERN_ID_UPDATE_VALUE']                 = $rg_pattern_id;
					$g['VALUE_VARS_LINK_ID_UPDATE_VALUE']         = $rg_val_vars_link_id;
					$g['VALUE_NESTEDMEM_COL_CMB_ID_UPDATE_VALUE'] = $rg_val_col_seq_comb_id;
				}
			}
		}
		// 2018/05/25 #3084 Add End ----

		if( $boolExecuteContinue === true && $boolSystemErrorFlag === false){
			if(strlen($rg_menu_id) === 0 || strlen($rg_column_list_id) === 0){
				$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-90129");
				$boolExecuteContinue = false;
				$retBool = false;
			}
			else if( strlen($rg_col_type) === 0){
				$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-90061");
				$boolExecuteContinue = false;
				$retBool = false;
			}
			else if( strlen($rg_pattern_id) === 0 ){
				$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-90130");
				$boolExecuteContinue = false;
				$retBool = false;
			}
		}

		//----メニューと項目の組み合わせチェック
		if( $boolExecuteContinue === true && $boolSystemErrorFlag === false){
			$boolExecuteContinue = false;
			$query
			 = " 			SELECT"
			  ." 			   COUNT(*) AS MENU_CNT"
			  ." 			  ,("
			  ." 				  SELECT"
			  ." 					 COUNT(*) "
			  ." 				  FROM"
			  ." 					 B_CMDB_MENU_COLUMN AS TBL_B"
			  ." 				  WHERE"
			  ." 					 TBL_B.MENU_ID = :MENU_ID"
			  ." 				  AND"
			  ." 					 TBL_B.COLUMN_LIST_ID = :MENU_COLUMN_ID"
			  ." 				  AND"
			  ." 					 TBL_B.DISUSE_FLAG = '0'"
			  ." 			   ) AS COLUMN_CNT "
			  ." 			FROM"
			  ." 			   B_CMDB_MENU_TABLE AS TBL_A"
			  ." 			WHERE "
			  ." 			   TBL_A.MENU_ID = :MENU_ID"
			  ." 			AND"
			  ." 			   TBL_A.DISUSE_FLAG = '0'"
			;

			$aryForBind = array();
			$aryForBind['MENU_ID']		= $rg_menu_id;
			$aryForBind['MENU_COLUMN_ID'] = $rg_column_list_id;

			$retArray = singleSQLExecuteAgent($query, $aryForBind, "NONAME_FUNC(VARS_MULTI_CHECK)");
			if( $retArray[0] === true ){
				$objQuery =& $retArray[1];
				$intCount = 0;
				$row = $objQuery->resultFetch();
				if( $row['MENU_CNT'] == '1' ){
					if( $row['COLUMN_CNT'] == '1' ){
						$boolExecuteContinue = true;
					}
					else if( $row['COLUMN_CNT'] == '0' ){
						$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-6601201");
						$retBool = false;
						$boolExecuteContinue = false;
					}
					else{
						$boolSystemErrorFlag = true;
					}
				}
				else if( $row['MENU_CNT'] == '0' ){
					$boolExecuteContinue = false;
					$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-6601202");
					$retBool = false;
					$boolExecuteContinue = false;
				}else{
					$boolSystemErrorFlag = true;
				}
				unset($row);
				unset($objQuery);
			}
			else{
				$boolSystemErrorFlag = true;
			}
			unset($retArray);
		}
		//メニューと項目の組み合わせチェック----

		/* <START> 作業パターンのチェック--------------------------------------------------------------------------------------------------- */
		if( $boolExecuteContinue === true && $boolSystemErrorFlag === false){
			$boolExecuteContinue = false;
			$query
			 = " 			SELECT"
			  ." 			   COUNT(*) AS PATTERN_CNT"
			  ." 			FROM"
			  ." 			   $pattan_tbl AS TBL_A"
			  ." 			WHERE"
			  ." 			   TBL_A.PATTERN_ID = :PATTERN_ID"
			  ." 			AND"
			  ." 			   TBL_A.DISUSE_FLAG = '0'"
			;
			/* クエリーバインド */
			$aryForBind = array();
			$aryForBind['PATTERN_ID'] = $rg_pattern_id;

			$retArray = singleSQLExecuteAgent($query, $aryForBind, "NONAME_FUNC(VARS_MULTI_CHECK)");
			if( $retArray[0] === true ){
				$objQuery =& $retArray[1];
				$intCount = 0;
				$row = $objQuery->resultFetch();
				if( $row['PATTERN_CNT'] == '1' ){
					$boolExecuteContinue = true;
				}
				else if( $row['PATTERN_CNT'] == '0' ){
					$boolExecuteContinue = false;
					$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-6601205") ; // "作業パターンが未登録"
					$retBool = false;
					$boolExecuteContinue = false;
				}
				else{
					$boolSystemErrorFlag = true;
				}
				unset($row);
				unset($objQuery);
			}
			else{
				$boolSystemErrorFlag = true;
			}
			unset($retArray);
		}
		/* < END > 作業パターンのチェック--------------------------------------------------------------------------------------------------- */

		/* <START> 変数部分のチェック------------------------------------------------------------------------------------------------------- */
		if( $boolExecuteContinue === true && $boolSystemErrorFlag === false){
			/* key部分とvalue部分の2回チェックを回す */
			for($i = 0; $i < 2; $i++){
				if(0 === $i && in_array($rg_col_type, array('2', '3'))){
					$intVarsLinkId	=  $rg_key_vars_link_id;
					$intColSeqCombId  =  $rg_key_col_seq_comb_id;
					$intSeqOfAssign   =  $rg_key_assign_seq;
					$strColType	   =  $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-6601206");
				}
				else if(1 === $i && in_array($rg_col_type, array('1', '3'))){
					$intVarsLinkId	=  $rg_val_vars_link_id;
					$intColSeqCombId  =  $rg_val_col_seq_comb_id;
					$intSeqOfAssign   =  $rg_val_assign_seq;
					$strColType	   =  $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-6601207");

					if(in_array($rg_col_type, array('1', '3'))){
						$strKeyVar = $rg_key_vars_link_id . "|" . $rg_key_col_seq_comb_id . "|" . $rg_key_assign_seq;
						$strValVar = $rg_val_vars_link_id . "|" . $rg_val_col_seq_comb_id . "|" . $rg_val_assign_seq;

						if($strKeyVar === $strValVar){
							$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-6601208");
							$retBool = false;
							$boolExecuteContinue = false;
							break;
						}
					}
				}
				else{
					continue;
				}

				/* <START> 変数タイプを取得------------------------------------------------------------------------------------------------- */
				$intVarType = -1;
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
				  ." 			AND"
				  ." 			   TAB_1.PATTERN_ID = :PATTERN_ID"
				;
				/* クエリーバインド */
				$aryForBind = array();
				$aryForBind['VARS_LINK_ID'] = $intVarsLinkId;
				$aryForBind['PATTERN_ID']   = $rg_pattern_id;
				$retArray = singleSQLExecuteAgent($strQuery, $aryForBind, "NONAME_FUNC(VARS_TYPE_CHECK)");
				if( $retArray[0] === true ){
					$objQuery = $retArray[1];
					$tmpAryRow = array();

					while($row = $objQuery->resultFetch()){ $tmpAryRow[] = $row ; }

					if( count($tmpAryRow) === 1 ){
						$tmpRow = $tmpAryRow[0];
						if(in_array($tmpRow['VARS_ATTR_ID'], array(1, 2, 3))){
							$intVarType = $tmpRow['VARS_ATTR_ID'];
						}
						else{
							$boolSystemErrorFlag = true;
							break;
						}
						unset($tmpRow);
					}
					else{
						$strMsgId = ($i === 0?"ITAANSTWRH-ERR-6601209":"ITAANSTWRH-ERR-6601210");
						$retStrBody = $g['objMTS']->getSomeMessage($strMsgId);
						$retBool = false;
						$boolExecuteContinue = false;
						break;
					}
					unset($tmpAryRow);
					unset($objQuery);
				}
				else{
					$boolSystemErrorFlag = true;
					break;
				}
				unset($retArray);
				/* < END > 変数タイプを取得------------------------------------------------------------------------------------------------- */

				/* <START> 変数の種類ごとに、バリデーションチェック------------------------------------------------------------------------- */

				/* 変数タイプが「一般変数」の場合 */
				if(1 == $intVarType){
					/* メンバー変数名のチェック */
					if( 0 < strlen($intColSeqCombId) ){
						$retBool = false;
						$boolExecuteContinue = false;
						$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-6601211", array($strColType));
						break;
					}
					/* 代入順序のチェック */
					if( 0 < strlen($intSeqOfAssign) ){
						$retBool = false;
						$boolExecuteContinue = false;
						$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-6601212", array($strColType));
						break;
					}
				}
				/* 変数タイプが「複数具体値変数」の場合 */
				else if(2 == $intVarType){
					/* メンバー変数名のチェック */
					if( 0 < strlen($intColSeqCombId)){
						$retBool = false;
						$boolExecuteContinue = false;
						$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-6601213", array($strColType));
						break;
					}
					/* 代入順序のチェック */
					if( 0 === strlen($intSeqOfAssign)){
						$retBool = false;
						$boolExecuteContinue = false;
						$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-6601214", array($strColType));
						break;
					}
				}
				/* 変数タイプが「多次元変数」の場合 */
				else if(3 == $intVarType){
					/* メンバー変数名のチェック */
					if( 0 === strlen($intColSeqCombId)){
						$retBool = false;
						$boolExecuteContinue = false;
						$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-6601215", array($strColType));
						break;
					}
					else{
						/* メンバー変数管理テーブル取得 */
						$aryResult = getChildVars_2100140009($intVarsLinkId, $intColSeqCombId);

						if(
							gettype($aryResult) === "array"
						  AND
							count($aryResult) === 1
						){
							$childData = $aryResult[0] ;
						}
						else if(
							gettype($aryResult) === "array"
						  AND
							count($aryResult) === 0
						){
							$retBool = false;
							$boolExecuteContinue = false;
							$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-6601216", array($strColType));
							break;
						}
						else{
							$boolSystemErrorFlag = true;
							break;
						}
					}

					/* 代入順序のチェック */
					$intAssignSeqNeed = $childData['ASSIGN_SEQ_NEED'];

					/* 代入順序の有無が『 有 』の場合 */
					if(1 ==  $intAssignSeqNeed){
						if(0 === strlen($intSeqOfAssign)){
							$retBool = false;
							$boolExecuteContinue = false;
							$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-6601217", array($strColType));
							break;
						}
					}
					// 代入順序の有無が『 無 』の場合
					else{
						if( 0 < strlen($intSeqOfAssign) ){
							$retBool = false;
							$boolExecuteContinue = false;
							$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-6601218", array($strColType));
							break;
						}
					}
				}
			}
		}
		/* < END > 変数部分のチェック------------------------------------------------------------------------------------------------------- */

		/* <START> 後述のSQLに組み込み為のロジック（「▼▼」マーク参照）-------------------------------------------------------------------- */
		$strBranchQuery = " ( ";

		/* Key変数が必須の場合 */
		if(in_array($rg_col_type, array(2, 3))){
			$strBranchQuery .= " ( ";
			$strBranchQuery .= "KEY_VARS_LINK_ID = :KEY_VARS_LINK_ID_1 ";
			$strBranchQuery .= ("" != $rg_key_col_seq_comb_id ? " AND KEY_NESTEDMEM_COL_CMB_ID = :KEY_NESTEDMEM_COL_CMB_ID_1 " : null);
			$strBranchQuery .= ("" != $rg_key_assign_seq	  ? " AND KEY_ASSIGN_SEQ		   = :KEY_ASSIGN_SEQ_1 "		   : null);
			$strBranchQuery .= " ) OR (";
			$strBranchQuery .= "VALUE_VARS_LINK_ID = :VALUE_VARS_LINK_ID_1 ";
			$strBranchQuery .= ("" != $rg_key_col_seq_comb_id ? " AND VALUE_NESTEDMEM_COL_CMB_ID = :VALUE_NESTEDMEM_COL_CMB_ID_1 " : null);
			$strBranchQuery .= ("" != $rg_key_assign_seq?	  " AND VALUE_ASSIGN_SEQ			 = :VALUE_ASSIGN_SEQ_1 "			 : null);
			$strBranchQuery .= " ) ";
		}

		if(in_array($rg_col_type, array(3))){ $strBranchQuery .= " OR " ; }

		/* Value変数が必須の場合 */
		if(in_array($rg_col_type, array(1, 3))){
			$strBranchQuery .= " ( ";
			$strBranchQuery .= "KEY_VARS_LINK_ID = :KEY_VARS_LINK_ID_2 ";
			$strBranchQuery .= ("" != $rg_val_col_seq_comb_id ? " AND KEY_NESTEDMEM_COL_CMB_ID = :KEY_NESTEDMEM_COL_CMB_ID_2 " : null);
			$strBranchQuery .= ("" != $rg_val_assign_seq? " AND KEY_ASSIGN_SEQ				 = :KEY_ASSIGN_SEQ_2 "		   : null);
			$strBranchQuery .= " ) OR (";
			$strBranchQuery .= "VALUE_VARS_LINK_ID = :VALUE_VARS_LINK_ID_2 ";
			$strBranchQuery .= ("" != $rg_val_col_seq_comb_id? " AND VALUE_NESTEDMEM_COL_CMB_ID = :VALUE_NESTEDMEM_COL_CMB_ID_2 ": null);
			$strBranchQuery .= ("" != $rg_val_assign_seq?	  " AND VALUE_ASSIGN_SEQ		   = :VALUE_ASSIGN_SEQ_2 "		  : null);
			$strBranchQuery .= " ) ";
		}
		$strBranchQuery .= " ) ";
		/* < END > 後述のSQLに組み込み為のロジック（「▼▼」マーク参照）-------------------------------------------------------------------- */

		/* <START> 代入値自動登録設定テーブルの重複レコードチェック------------------------------------------------------------------------- */

		/* ▼▼マークのSQL */
		if($boolExecuteContinue === true && $boolSystemErrorFlag === false){
			$strQuery
			 = " 			SELECT"
			  ." 			   PRMCOL_VARS_LINK_ID"
			  ." 			FROM"
			  ." 			   B_ANSTWR_PRMCOL_VARS_LINK"
			  ." 			WHERE"
			  ." 			   PRMCOL_VARS_LINK_ID <> :PRMCOL_VARS_LINK_ID"
			  ." 			AND"
			  ." 			   MENU_ID = :MENU_ID"
			  ." 			AND"
			  ." 			   PATTERN_ID = :PATTERN_ID"
			  ." 			AND"
			  ." 			   DISUSE_FLAG = '0'"
			  ." 			AND"
			  ." 			" .$strBranchQuery // 前述の「後述のSQLに組み込み為のロジック」部分の内容が格納されている。
			;
			/* クエリーバインド */
			$aryForBind = array();
			$aryForBind['PRMCOL_VARS_LINK_ID']	= $columnId;
			$aryForBind['MENU_ID']	  = $rg_menu_id;
			$aryForBind['PATTERN_ID']   = $rg_pattern_id;

			/* Key変数が必須の場合 */
			if(in_array($rg_col_type, array(2, 3))){
				$aryForBind['KEY_VARS_LINK_ID_1']	=  $rg_key_vars_link_id;
				$aryForBind['VALUE_VARS_LINK_ID_1']  =  $rg_key_vars_link_id;
				if("" != $rg_key_col_seq_comb_id){
					$aryForBind['KEY_NESTEDMEM_COL_CMB_ID_1']	=  $rg_key_col_seq_comb_id;
					$aryForBind['VALUE_NESTEDMEM_COL_CMB_ID_1']  =  $rg_key_col_seq_comb_id;
				}
				if("" != $rg_key_assign_seq){
					$aryForBind['VALUE_ASSIGN_SEQ_1']  =  $rg_key_assign_seq;
					$aryForBind['KEY_ASSIGN_SEQ_1']	=  $rg_key_assign_seq;
				}
			}

			/* Value変数が必須の場合 */
			if(in_array($rg_col_type, array(1, 3))){
				$aryForBind['KEY_VARS_LINK_ID_2']	=  $rg_val_vars_link_id;
				$aryForBind['VALUE_VARS_LINK_ID_2']  =  $rg_val_vars_link_id;
				if("" != $rg_val_col_seq_comb_id){
					$aryForBind['KEY_NESTEDMEM_COL_CMB_ID_2']	=  $rg_val_col_seq_comb_id;
					$aryForBind['VALUE_NESTEDMEM_COL_CMB_ID_2']  =  $rg_val_col_seq_comb_id;
				}
				if("" != $rg_val_assign_seq){
					$aryForBind['VALUE_ASSIGN_SEQ_2']  =  $rg_val_assign_seq;
					$aryForBind['KEY_ASSIGN_SEQ_2']	=  $rg_val_assign_seq;
				}
			}

			$retArray = singleSQLExecuteAgent($strQuery, $aryForBind, "NONAME_FUNC(VALASSIGN_DUP_CHECK)");
			if( $retArray[0] === true ){
				$objQuery = $retArray[1];
				$dupnostr = "";

				while($row = $objQuery->resultFetch()){ $dupnostr = $dupnostr . "[" . $row['PRMCOL_VARS_LINK_ID'] . "]" ; }
				if( strlen($dupnostr) != 0 ){
					$retBool = false;
					$boolExecuteContinue = false;
					$retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-6601219",array($dupnostr));
				}
				unset($objQuery);
			}
			else{
				$boolSystemErrorFlag = true;
			}
			unset($retArray);
		}
		/* < END > 代入値自動登録設定テーブルの重複レコードチェック------------------------------------------------------------------------- */

		if( $boolSystemErrorFlag === true ){
			$retBool = false;
			$retStrBody = $g['objMTS']->getSomeMessage("ITAWDCH-ERR-3001"); // システムエラー
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
	/* < END > 組み合わせバリデータ--------------------------------------------------------------------------------------------------------- */

	$table->setGeneObject('webSetting', $arrayWebSetting);
	return $table;
};
loadTableFunctionAdd($tmpFx,__FILE__);
unset($tmpFx);


/* メンバー変数管理テーブル 問い合わせクエリ */
function getChildVars_2100140009($strVarsLinkIdNumeric, $strColSeqCombinationId){
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
	  ." 			AND"
	  ." 			   TBL_X.DISUSE_FLAG IN ('0')"
	  ." 			AND"
	  ." 			   TBL_Y.DISUSE_FLAG IN ('0')"
	  ." 			AND"
	  ." 			   TBL_B.DISUSE_FLAG IN ('0')"
	  ." 			 AND"
	  ." 			   TBL_A.VARS_LINK_ID = :VARS_LINK_ID"
	  ." 			 AND"
	  ." 			   TBL_X.NESTEDMEM_COL_CMB_ID = :NESTEDMEM_COL_CMB_ID"
	;
	/* クエリーバインド */
	$aryForBind = array();
	$aryForBind['VARS_LINK_ID'] = $strVarsLinkIdNumeric;
	$aryForBind['NESTEDMEM_COL_CMB_ID'] = $strColSeqCombinationId;

	$retArray = singleSQLExecuteAgent($strQuery, $aryForBind, "NONAME_FUNC(VARS_RELATION_CHECK)");
	if( $retArray[0] === true ){
		$objQuery = $retArray[1];
		$tmpAryRow = array();
		while($row = $objQuery->resultFetch() ){
			$tmpAryRow[]= $row;
		}
		return $tmpAryRow;
	}
	else{
		return false;
	}
}

function chkVarsAssociate_2100140009($in_type,$in_vars_link_id,$in_col_seq_comb_id,
                         &$out_pattern_id, &$out_vars_link_id, &$out_col_seq_comb_id,
                         &$retStrBody, &$boolSystemErrorFlag){
    global $g;

    $query_step1 =  "SELECT                                                           "
                   ."  TBL_A.VARS_LINK_ID,                                            "
                   ."  TBL_A.PATTERN_ID,                                              "
                   ."  TBL_A.VARS_ID,                                                 "
                   ."  COUNT(*) AS VARS_LINK_ID_CNT,                                  "
                   ."  ( SELECT                                                       "
                   ."      VARS_ATTR_ID                                               "
                   ."    FROM                                                         "
                   ."      B_ANSTWR_VARS TBL_B                                        "
                   ."    WHERE                                                        "
                   ."      TBL_B.VARS_ID = TBL_A.VARS_ID AND                          "
                   ."      TBL_B.DISUSE_FLAG  = '0'                                   "
                   ."  ) AS VARS_ATTR_ID                                              "
                   ."FROM                                                             "
                   ."  D_ANSTWR_PTN_VARS_LINK TBL_A                                   "
                   ."WHERE                                                            "
                   ."  TBL_A.VARS_LINK_ID    = :VARS_LINK_ID   AND                    "
                   ."  TBL_A.DISUSE_FLAG     = '0'                                    ";

    $query_step2 =  "SELECT                                                           "
                   ."  COUNT(*) AS MEMBER_CNT                                         "
                   ."FROM                                                             "
                   ."  B_ANSTWR_NESTEDMEM_COL_CMB TBL_A                               "
                   ."WHERE                                                            "
                   ."  TBL_A.VARS_ID                = :VARS_ID                 AND    "
                   ."  TBL_A.NESTEDMEM_COL_CMB_ID   = :NESTEDMEM_COL_CMB_ID    AND    "
                   ."  TBL_A.DISUSE_FLAG     = '0'                                    ";

    $aryForBind = array();
    $aryForBind['VARS_LINK_ID'] = $in_vars_link_id;
    $retArray = singleSQLExecuteAgent($query_step1, $aryForBind, "NONAME_FUNC(VARS_MULTI_CHECK)");
    if( $retArray[0] === true ){
        $objQuery =& $retArray[1];
        $row = $objQuery->resultFetch();
        unset($objQuery);
        if( $row['VARS_LINK_ID_CNT'] == '0' ){
            $retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-6601223");
            $retBool = false;
        }
        if( $row['VARS_LINK_ID_CNT'] == '1' ){
            $out_pattern_id                       = $row['PATTERN_ID'];
            $out_vars_link_id                     = $row['VARS_LINK_ID'];
            $vars_id                              = $row['VARS_ID'];

            switch($row['VARS_ATTR_ID']){
            case "1":
            case "2":
                if($in_col_seq_comb_id != ""){
                    $retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-6601224",array($in_type));
                    return false;
                }
                break;
            case "3":
                $out_col_seq_comb_id   = $in_col_seq_comb_id;
                if($in_col_seq_comb_id == ""){
                    $retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-6601225",array($in_type));
                    return false;
                }
                // 変数とメンバー変数の組合せ判定
                $aryForBind = array();
                $aryForBind['VARS_ID']                = $vars_id;
                $aryForBind['NESTEDMEM_COL_CMB_ID']   = $in_col_seq_comb_id;
                $retMemberArray = singleSQLExecuteAgent($query_step2, $aryForBind, "NONAME_FUNC(VARS_MULTI_CHECK)");
                if( $retMemberArray[0] === true ){
                    $objMemberQuery =& $retMemberArray[1];
                    $MemberRow = $objMemberQuery->resultFetch();
                    unset($objMemberQuery);
                    if( $MemberRow['MEMBER_CNT'] == '0' ){
                         $retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-6601226",array($in_type));
                         return false;
                    }
                }else{
                    web_log("DB Access error file:" . basename(__FILE__) . " line:" . __LINE__);
                    $boolSystemErrorFlag = true;
                    return false;
                }
                break;
            default:
                $retStrBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-ERR-6601227");
                return false;
            }
        }else{
            web_log("DB Access error file:" . basename(__FILE__) . " line:" . __LINE__);
            $boolSystemErrorFlag = true;
            return false;
        }
    }else{
        web_log("DB Access error file:" . basename(__FILE__) . " line:" . __LINE__);
        $boolSystemErrorFlag = true;
        return false;
    }
    return true;
}
