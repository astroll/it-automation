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
//   ・AnsibleTower 変数具体値管理
//
//////////////////////////////////////////////////////////////////////

$tmpFx = function (&$aryVariant=array(),&$arySetting=array()){
	global $g;

	$arrayWebSetting = array();
	$arrayWebSetting['page_info'] = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7300301"); // MessageID_SecoundSuffix：03

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

	/* 画面と１対１で紐付けるテーブルの指定 */
	$table = new TableControlAgent('B_ANSTWR_DEFAULT_VARSVAL','VARSVAL_ID', $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390301"), 'B_ANSTWR_DEFAULT_VARSVAL_JNL', $tmpAry);
	$tmpAryColumn = $table->getColumns();
	$tmpAryColumn['VARSVAL_ID']->setSequenceID('B_ANSTWR_DEFAULT_VARSVAL_RIC');
	$tmpAryColumn['JOURNAL_SEQ_NO']->setSequenceID('B_ANSTWR_DEFAULT_VARSVAL_JSQ');
	unset($tmpAryColumn);

	/* QMファイル名プレフィックス */
	$table->setDBMainTableLabel($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7360301"));
	/* エクセルのシート名 */
	$table->getFormatter('excel')->setGeneValue('sheetNameForEditByFile',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7340301"));

	/* 検索機能の制御 */
	$table->setGeneObject('AutoSearchStart',true);  //('',true,false)

	/* ロールパッケージ名 */
	$c = new IDColumn('ROLE_PACKAGE_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390302"),'B_ANSTWR_ROLE_PACKAGE','ROLE_PACKAGE_ID','ROLE_PACKAGE_NAME','');
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350302"));//エクセル・ヘッダでの説明
	$c->setJournalTableOfMaster('B_ANSTWR_ROLE_PACKAGE_JNL');
	$c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c->setJournalKeyIDOfMaster('ROLE_PACKAGE_ID');
	$c->setJournalDispIDOfMaster('ROLE_PACKAGE_NAME');
	$c->setRequired(true);//登録/更新時には、入力必須
	$table->addColumn($c);

	/* ロール名 */
	$c = new IDColumn('ROLE_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390303"),'B_ANSTWR_ROLE','ROLE_ID','ROLE_NAME','');
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350303"));//エクセル・ヘッダでの説明
	$c->setJournalTableOfMaster('B_ANSTWR_ROLE_JNL');
	$c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c->setJournalKeyIDOfMaster('ROLE_ID');
	$c->setJournalDispIDOfMaster('ROLE_NAME');
	$c->setRequired(true);//登録/更新時には、入力必須
	$table->addColumn($c);

	/* 末端変数タイプ */
	$c = new IDColumn('END_VAR_OF_VARS_ATTR_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390304"),'B_ANSTWR_VARS_ATTR','VARS_ATTR_ID','VARS_ATTR_NAME','');
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350304"));//エクセル・ヘッダでの説明
	$c->setJournalTableOfMaster('B_VARS_ATTR_JNL');
	$c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c->setJournalKeyIDOfMaster('VARS_ATTR_ID');
	$c->setJournalDispIDOfMaster('VARS_ATTR_NAME');
	$c->setRequired(true);//登録/更新時には、入力必須
	$table->addColumn($c);

	/* 変数名 */
	$c = new IDColumn('VARS_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390305"),'B_ANSTWR_VARS','VARS_ID','VARS_NAME','');
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350305"));//エクセル・ヘッダでの説明
	$c->setJournalTableOfMaster('B_ANSTWR_VARS_JNL');
	$c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c->setJournalKeyIDOfMaster('VARS_ID');
	$c->setJournalDispIDOfMaster('VARS_NAME');
	$c->setRequired(true);//登録/更新時には、入力必須
	$table->addColumn($c);

	/* メンバー変数名 */
	$c = new IDColumn('NESTEDMEM_COL_CMB_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390306"),'B_ANSTWR_NESTEDMEM_COL_CMB','NESTEDMEM_COL_CMB_ID','COL_COMBINATION_MEMBER_ALIAS','');
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350306"));//エクセル・ヘッダでの説明
	$c->setJournalTableOfMaster('B_ANSTWR_NESTEDMEM_COL_CMB_JNL');
	$c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c->setJournalKeyIDOfMaster('NESTEDMEM_COL_CMB_ID');
	$c->setJournalDispIDOfMaster('COL_COMBINATION_MEMBER_ALIAS');
	$c->setRequired(true);//登録/更新時には、入力必須
	$table->addColumn($c);

	/* 代入順序 */
	$c = new NumColumn('ASSIGN_SEQ',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390307"));
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350307"));//エクセル・ヘッダでの説明
	$c->setSubtotalFlag(false);
	$c->setValidator(new IntNumValidator(null,null));
	$table->addColumn($c);

	/* 具体値 */
	$objVldt = new SingleTextValidator(1,1024,false);
	$c = new TextColumn('VARS_VALUE',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390308"));
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350308"));//エクセル・ヘッダでの説明
	$c->setValidator($objVldt);
	$table->addColumn($c);

	/* カラムを確定する */
	$table->fixColumn();
	$table->setGeneObject('webSetting', $arrayWebSetting);

	return $table;
};
loadTableFunctionAdd($tmpFx,__FILE__);

unset($tmpFx);
?>
