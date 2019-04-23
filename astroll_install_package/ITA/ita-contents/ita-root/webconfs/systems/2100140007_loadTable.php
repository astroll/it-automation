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
//   ・AnsibleTower 多段変数最大繰返数管理
//
//////////////////////////////////////////////////////////////////////

$tmpFx = function (&$aryVariant=array(),&$arySetting=array()){
	global $g;

	$arrayWebSetting = array();
	$arrayWebSetting['page_info'] = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7300601"); //MessageID_SecondSuffix：06

	/* 履歴管理用のカラムを配列に格納 */
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

	/* 画面と１対１で紐付けるテーブルを指定 */
	$table = new TableControlAgent('B_ANSTWR_MAX_MEMBER_COL','MAX_MEMBER_COL_ID', $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390601"), 'B_ANSTWR_MAX_MEMBER_COL_JNL', $tmpAry);
	$tmpAryColumn = $table -> getColumns();
	$tmpAryColumn['MAX_MEMBER_COL_ID']->setSequenceID('B_ANSTWR_MAX_MEMBER_COL_RIC');
	$tmpAryColumn['JOURNAL_SEQ_NO']->setSequenceID('B_ANSTWR_MAX_MEMBER_COL_JSQ');

	/* ファイルアップロードで廃止／復活を無効にする。 */
	$strResultType01 = $g['objMTS']->getSomeMessage("ITAWDCH-STD-12202"); //登録
	$strResultType02 = $g['objMTS']->getSomeMessage("ITAWDCH-STD-12203"); //更新
	$strResultType03 = $g['objMTS']->getSomeMessage("ITAWDCH-STD-12204"); //廃止
	$strResultType04 = $g['objMTS']->getSomeMessage("ITAWDCH-STD-12205"); //復活
	$strResultType99 = $g['objMTS']->getSomeMessage("ITAWDCH-STD-12206"); //エラー

	$tmpAryColumn['ROW_EDIT_BY_FILE'] -> setResultCount
	  (
		array(
			//'register' => array('name'=>$strResultType01 , 'ct'=>0)
			'update'   => array('name'=>$strResultType02 , 'ct'=>0),
			'error'    => array('name'=>$strResultType99 , 'ct'=>0),
		 )
	  );

	$tmpAryColumn['ROW_EDIT_BY_FILE'] -> setCommandArrayForEdit
	  (
		array(
			//1 => $strResultType01,
			2 => $strResultType02,
		)
	  );

	/* 廃止フラグを表示しない */
	$outputType = new OutputType(new TabHFmt(), new DelTabBFmt());
	$tmpAryColumn['DISUSE_FLAG']->setOutputType("print_table", $outputType);

	unset($tmpAryColumn);

	$table -> setDBMainTableLabel($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7360601")); // QMファイル名プレフィックス
	$table -> getFormatter('excel')->setGeneValue('sheetNameForEditByFile',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7340601")); // エクセルのシート名
	$table -> setGeneObject('AutoSearchStart',true); // 検索機能の制御


	/* 変数名 */
	$c = new IDColumn('VARS_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390602"),'B_ANSTWR_VARS','VARS_ID','VARS_NAME','');
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350602"));//エクセル・ヘッダでの説明
	$c -> setJournalTableOfMaster('B_ANSTWR_VARS_JNL');
	$c -> setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c -> setJournalTableOfMaster('B_ANSTWR_VARS_JNL');
	$c -> setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c -> setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c -> setJournalKeyIDOfMaster('VARS_ID');
	$c -> setJournalDispIDOfMaster('VARS_NAME');
	$c -> setAllowSendFromFile(false);//エクセル/CSVからのアップロードを禁止する。
	$c->getOutputType('update_table')->setVisible(false);

	$table -> addColumn($c);


	/* 多段メンバ変数パス */
	$c = new IDColumn('NESTED_MEM_VARS_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390603"),'B_ANSTWR_NESTED_MEM_VARS','NESTED_MEM_VARS_ID','NESTED_MEMBER_PATH_ALIAS','');
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350603"));//エクセル・ヘッダでの説明
	$c -> setJournalTableOfMaster('B_ANSTWR_NESTED_MEM_VARS_JNL');
	$c -> setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c -> setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c -> setJournalKeyIDOfMaster('NESTED_MEM_VARS_ID');
	$c -> setJournalDispIDOfMaster('NESTED_MEMBER_PATH_ALIAS');
	$c -> setAllowSendFromFile(false);//エクセル/CSVからのアップロードを禁止する。
	$c->getOutputType('update_table')->setVisible(false);
	$table->addColumn($c);


	/* 最大繰返し数 */
	$objVldt = new IntNumValidator(1 , null);

	$c = new NumColumn('MAX_COL_SEQ',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390604"));
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390604"));//エクセル・ヘッダでの説明
	$c -> setSubtotalFlag(false);
	$c -> setValidator($objVldt);
	$c -> setRequired(true);//登録/更新時には、入力必須

	$table -> addColumn($c);


	/* 複合主キーを設定 */
	$table->addUniqueColumnSet(array('VARS_ID','NESTED_MEM_VARS_ID'));

	/* カラムを確定する */
	$table->fixColumn();
	$table->setGeneObject('webSetting', $arrayWebSetting);

	return $table;
};

loadTableFunctionAdd($tmpFx,__FILE__);

unset($tmpFx);
?>
