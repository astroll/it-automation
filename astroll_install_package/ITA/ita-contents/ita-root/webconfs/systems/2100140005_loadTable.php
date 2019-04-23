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
//   ・AnsibleTower Movement一覧
//
//////////////////////////////////////////////////////////////////////
$tmpFx = function (&$aryVariant=array(),&$arySetting=array()){
	global $g;

	$arrayWebSetting = array();
	$arrayWebSetting['page_info'] = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7301701");

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

	/* 画面と１体１で紐付けるビューを指定する */
	$table = new TableControlAgent('E_ANSTWR_PATTERN','PATTERN_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7391701"), 'E_ANSTWR_PATTERN_JNL', $tmpAry);
	$tmpAryColumn = $table -> getColumns();
	$tmpAryColumn['PATTERN_ID']->setSequenceID('C_PATTERN_PER_ORCH_RIC');
	$tmpAryColumn['JOURNAL_SEQ_NO']->setSequenceID('C_PATTERN_PER_ORCH_JSQ');

	unset($tmpAryColumn);

	$table -> setDBMainTableHiddenID('C_PATTERN_PER_ORCH'); // 画面に紐付けたVIEWの元となる実体テーブルを更新する為の定義（メイン側） ※利用時は、更新対象カラムに、「$c->setHiddenMainTableColumn(true);」を付加すること
	$table -> setDBJournalTableHiddenID('C_PATTERN_PER_ORCH_JNL'); // 画面に紐付けたVIEWの元となる実体テーブルを更新する為の定義（JOURNAL側）
	$table -> setJsEventNamePrefix(true); // 作業実行で必要なのでtrue
	$table -> setDBMainTableLabel($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7361701")); // QMファイル名プレフィックス
	$table -> getFormatter('excel')->setGeneValue('sheetNameForEditByFile',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7341701")); // エクセルのシート名
	$table -> setGeneObject('AutoSearchStart',true); // 検索機能の制御
	$table -> addUniqueColumnSet(array('ITA_EXT_STM_ID','PATTERN_NAME'));


	/* Movement名 */
	$objVldt = new SingleTextValidator(1,256,false);

	$c = new TextColumn('PATTERN_NAME',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7391702"));
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7351702"));//エクセル・ヘッダでの説明
	$c -> setHiddenMainTableColumn(true);//コンテンツのソースがビューの場合、登録/更新の対象とする際に、trueとすること。setDBColumn(true)であることも必要。
	$c -> setValidator($objVldt);
	$c -> setRequired(true);//登録/更新時には、入力必須
	$c -> setUnique(true);//登録/更新時には、DB上ユニークな入力であること必須

	$table->addColumn($c);

	$tmpObjFunction = function($objColumn, $strEventKey, &$exeQueryData, &$reqOrgData=array(), &$aryVariant=array()){
		$boolRet = true;
		$intErrorType = null;
		$aryErrMsgBody = array();
		$strErrMsg = "";
		$strErrorBuf = "";

		$modeValue = $aryVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_MODE"];
		if( $modeValue=="DTUP_singleRecRegister" || $modeValue=="DTUP_singleRecUpdate" ){
			$exeQueryData[$objColumn->getID()] = 12;
		}else if( $modeValue=="DTUP_singleRecDelete" ){
			$exeQueryData[$objColumn->getID()] = 12;
		}
		$retArray = array($boolRet,$intErrorType,$aryErrMsgBody,$strErrMsg,$strErrorBuf);
		return $retArray;
	};


	/* オーケストレータ */
	$c = new IDColumn('ITA_EXT_STM_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7391703"),'B_ITA_EXT_STM_MASTER','ITA_EXT_STM_ID','ITA_EXT_STM_NAME','B_ITA_EXT_STM_MASTER');
	$c -> setDescription('プルダウン入力');//エクセル・ヘッダでの説明
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7351703"));//エクセル・ヘッダでの説明
	$c -> setHiddenMainTableColumn(true);//コンテンツのソースがビューの場合、登録/更新の対象とする際に、trueとすること。setDBColumn(true)であることも必要。
	$c -> setAllowSendFromFile(false);//エクセル/CSVからのアップロードを禁止する。
	$c -> getOutputType('update_table')->setVisible(false);
	$c -> getOutputType('register_table')->setVisible(false);
	$c -> setJournalTableOfMaster('B_ITA_EXT_STM_MASTER_JNL');
	$c -> setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c -> setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c -> setJournalKeyIDOfMaster('ITA_EXT_STM_ID');
	$c -> setJournalDispIDOfMaster('ITA_EXT_STM_NAME');
	$c -> setFunctionForEvent('beforeTableIUDAction',$tmpObjFunction);

	$table -> addColumn($c);


	/* 遅延タイマー */
	$objVldt = new IntNumValidator(1 , null);

	$c = new NumColumn('TIME_LIMIT',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7391704"));
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7351704"));//エクセル・ヘッダでの説明
	$c -> setHiddenMainTableColumn(true);//コンテンツのソースがビューの場合、登録/更新の対象とする際に、trueとすること。setDBColumn(true)であることも必要。
	$c -> setSubtotalFlag(false);
	$c -> setValidator($objVldt);

	$table -> addColumn($c);


	/* <START>カラムグループ（Ansible利用情報）--------------------------------------------------------------------------------------------- */
	$cg = new ColumnGroup( $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7311701") );

	/* ホスト指定形式 */
	$c = new IDColumn('ANS_HOST_DESIGNATE_TYPE_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7391705"),'B_HOST_DESIGNATE_TYPE_LIST','HOST_DESIGNATE_TYPE_ID','HOST_DESIGNATE_TYPE_NAME','');
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7351705"));//エクセル・ヘッダでの説明
	$c->setHiddenMainTableColumn(true);//コンテンツのソースがビューの場合、登録/更新の対象とする際に、trueとすること。setDBColumn(true)であることも必要。
	$c->setJournalTableOfMaster('B_HOST_DESIGNATE_TYPE_LIST_JNL');
	$c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c->setJournalKeyIDOfMaster('HOST_DESIGNATE_TYPE_ID');
	$c->setJournalDispIDOfMaster('HOST_DESIGNATE_TYPE_NAME');
	$c->setRequired(true);//登録/更新時には、入力必須
	$cg->addColumn($c);


	/* 並列実行数 */
	$objVldt = new IntNumValidator(1 , null);

	$c = new NumColumn('ANS_PARALLEL_EXE',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7391706"));
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7351706"));//エクセル・ヘッダでの説明
	$c -> setHiddenMainTableColumn(true);//コンテンツのソースがビューの場合、登録/更新の対象とする際に、trueとすること。setDBColumn(true)であることも必要。
	$c -> setSubtotalFlag(false);
	$c -> setValidator($objVldt);

	$cg -> addColumn($c);


	/* WinRM接続 */
	$c = new IDColumn('ANS_WINRM_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7391707"),'D_FLAG_LIST_01','FLAG_ID','FLAG_NAME','');
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7351707"));//エクセル・ヘッダでの説明
	$c->setHiddenMainTableColumn(true);//コンテンツのソースがビューの場合、登録/更新の対象とする際に、trueとすること。setDBColumn(true)であることも必要。

	$cg->addColumn($c);


	/* Gather Facts */
	$c = new IDColumn('ANS_GATHER_FACTS',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7391708"),'B_ANSTWER_GATHERFACTS_FLAG','FLAG_ID','FLAG_NAME','');
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7391708"));//エクセル・ヘッダでの説明
	$c->setHiddenMainTableColumn(true);//コンテンツのソースがビューの場合、登録/更新の対象とする際に、trueとすること。setDBColumn(true)であることも必要。
	$c->getOutputType('register_table')->setDefaultInputValue("1"); /* 作業実行に関する値の設定 */

	$cg->addColumn($c);


	$table->addColumn($cg);
	/* <END>カラムグループ（Ansible利用情報）----------------------------------------------------------------------------------------------- */


	/* 変数カウント */
	$c = new NumColumn('VARS_COUNT',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7391709"));
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7351709"));//エクセル・ヘッダで>の説明
	$c->setHiddenMainTableColumn(false);//コンテンツのソースがビューの場合、登録/更新の対象とする際に、trueとすること。setDBColumn(true)であることも必要。
	$c->setAllowSendFromFile(false);//エクセル/CSVからのアップロードを禁止する。
	$c->getOutputType('update_table')->setVisible(false);
	$c->getOutputType('register_table')->setVisible(false);
	$c->getOutputType('delete_table')->setVisible(false);
	$c->getOutputType('csv')->setVisible(false);
	$c->setSubtotalFlag(false);

	$table->addColumn($c);


	/* カラムを確定する */
	$table -> fixColumn();
	$tmpAryColumn = $table->getColumns();

	list($strTmpValue,$tmpKeyExists) = isSetInArrayNestThenAssign($aryVariant,array('callType'),null);
	if( $tmpKeyExists===true ){
		if( $strTmpValue=="insConstruct" ){
			$objRadioColumn = $tmpAryColumn['WEB_BUTTON_UPDATE'];
			$objRadioColumn->setColLabel($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7301702"));
			$objFunctionB = function ($objOutputType, $rowData, $aryVariant, $objColumn){
				$strInitedColId = $objColumn->getID();
				$aryVariant['callerClass'] = get_class($objOutputType);
				$aryVariant['callerVars'] = array('initedColumnID'=>$strInitedColId,'free'=>null);
				$strRIColId = $objColumn->getTable()->getRIColumnID();
				$rowData[$strInitedColId] = '<input type="radio" name="patternNo" onclick="javascript:patternLoadForExecute(' . $rowData[$strRIColId] . ')"/>';
				return $objOutputType->getBody()->getData($rowData,$aryVariant);
			};
			$objTTBF = new TextTabBFmt();
			$objTTHF = new TabHFmt(); 
			$objTTBF->setSafingHtmlBeforePrintAgent(false);
			$objOutputType = new VariantOutputType($objTTHF, $objTTBF);
			$objOutputType->setFunctionForGetBodyTag($objFunctionB);
			$objOutputType->setVisible(true);
			$objRadioColumn->setOutputType("print_table", $objOutputType);
			
			$table->getFormatter('print_table')->setGeneValue("linkExcelHidden",true);
			$table->getFormatter('print_table')->setGeneValue("linkCSVFormShow",false);
		}
	}
	unset($tmpAryColumn);
	$table->setGeneObject('webSetting', $arrayWebSetting);

	return $table;
};
loadTableFunctionAdd($tmpFx,__FILE__);

unset($tmpFx);
?>
