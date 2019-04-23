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
//   ・AnsibleTower ロール管理
//
//////////////////////////////////////////////////////////////////////

$tmpFx = function (&$aryVariant=array(),&$arySetting=array()){
	global $g;

	$arrayWebSetting = array();
	$arrayWebSetting['page_info'] = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7302001");

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

	/* 画面と１体１で紐付けるテーブルを指定する */
	$table = new TableControlAgent('B_ANSTWR_ROLE','ROLE_ID', $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7392001"), 'B_ANSTWR_ROLE_JNL', $tmpAry);
	$tmpAryColumn = $table->getColumns();
	$tmpAryColumn['ROLE_ID']->setSequenceID('B_ANSTWR_ROLE_RIC');
	$tmpAryColumn['JOURNAL_SEQ_NO']->setSequenceID('B_ANSTWR_ROLE_JSQ');
	unset($tmpAryColumn);

	/* QMファイル名プレフィックス */
	$table->setDBMainTableLabel($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7362001"));
	/* エクセルのシート名 */
	$table->getFormatter('excel')->setGeneValue('sheetNameForEditByFile',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7342001"));

	/* 検索機能の制御 */
	$table->setGeneObject('AutoSearchStart',true);

	/* ロールパッケージ名 */
	$c = new IDColumn('ROLE_PACKAGE_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7392002"),'B_ANSTWR_ROLE_PACKAGE','ROLE_PACKAGE_ID','ROLE_PACKAGE_NAME','');
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7352002"));//エクセル・ヘッダでの説明
	$c->setJournalTableOfMaster('B_ANSTWR_ROLE_PACKAGE_JNL');
	$c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c->setJournalKeyIDOfMaster('ROLE_PACKAGE_ID');
	$c->setJournalDispIDOfMaster('ROLE_PACKAGE_NAME');
	$c->setRequired(true);//登録/更新時には、入力必須
	$table->addColumn($c);

	/* ロール名 */
	$objVldt = new SingleTextValidator(1,128,false);
	$c = new TextColumn('ROLE_NAME',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7392003"));
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7352003"));//エクセル・ヘッダでの説明
	$c->setValidator($objVldt);
	$c->setRequired(true);//登録/更新時には、入力必須
	$table->addColumn($c);

	$table->addUniqueColumnSet(array('ROLE_PACKAGE_ID','ROLE_NAME'));

	/* カラムを確定する */
	$table->fixColumn();
	$table->setGeneObject('webSetting', $arrayWebSetting);

	return $table;
};
loadTableFunctionAdd($tmpFx,__FILE__);

unset($tmpFx);
?>
