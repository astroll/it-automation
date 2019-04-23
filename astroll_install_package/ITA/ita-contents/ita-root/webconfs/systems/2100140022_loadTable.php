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
//   ・AnsibleTower 多段変数配列組合せ管理
//
//////////////////////////////////////////////////////////////////////

$tmpFx = function (&$aryVariant=array(),&$arySetting=array()){
	global $g;

	$arrayWebSetting = array();
	$arrayWebSetting['page_info'] = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7300701"); //MessageID_SecondSuffix：07

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

	/* 画面に１体１で紐付けるテーブルを指定 */
	$table = new TableControlAgent('B_ANSTWR_NESTEDMEM_COL_CMB','NESTEDMEM_COL_CMB_ID', $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390701"), 'B_ANSTWR_NESTEDMEM_COL_CMB_JNL', $tmpAry);
	$tmpAryColumn = $table->getColumns();
	$tmpAryColumn['NESTEDMEM_COL_CMB_ID']->setSequenceID('B_ANSTWR_NESTEDMEM_COL_CMB_RIC');
	$tmpAryColumn['JOURNAL_SEQ_NO']->setSequenceID('B_ANSTWR_NESTEDMEM_COL_CMB_JSQ');
	unset($tmpAryColumn);

	/* QMファイル名プレフィックス */
	$table->setDBMainTableLabel($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7360701"));
	/* エクセルのシート名 */
	$table->getFormatter('excel')->setGeneValue('sheetNameForEditByFile',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7340701"));

	/* 検索機能の制御 */
	$table->setGeneObject('AutoSearchStart',true);  //('',true,false)

	/* 変数名 */
	$c = new IDColumn('VARS_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390702"),'B_ANSTWR_VARS','VARS_ID','VARS_NAME','');
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350702"));//エクセル・ヘッダでの説明
	$c->setJournalTableOfMaster('B_ANSTWR_VARS_JNL');
	$c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c->setJournalKeyIDOfMaster('VARS_ID');
	$c->setJournalDispIDOfMaster('VARS_NAME');
	$c->setRequired(true);//登録/更新時には、入力必須
	$table->addColumn($c);

	/* 多段変数メンバ項番 */
	$c = new NumColumn('NESTED_MEM_VARS_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390703"));
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350703"));//エクセル・ヘッダでの説明
	$c->setSubtotalFlag(false);
	$c->setValidator(new IntNumValidator(null,null));
	$table->addColumn($c);

	/* 多段変数組み合わせ表示名 */
	$objVldt = new SingleTextValidator(1,4000,false);
	$c = new TextColumn('COL_COMBINATION_MEMBER_ALIAS',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390704"));
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350704"));//エクセル・ヘッダでの説明
	$c->setValidator($objVldt);
	$table->addColumn($c);

	/* 全体列順序 */
	$objVldt = new SingleTextValidator(1,4000,false);
	$c = new TextColumn('COL_SEQ_VALUE',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390705"));
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350705"));//エクセル・ヘッダでの説明
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
