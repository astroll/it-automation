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
//	・AnsibleTower 作業対象ホスト管理
//
//////////////////////////////////////////////////////////////////////

$tmpFx = function (&$aryVariant=array(),&$arySetting=array()){
	global $g;

	$arrayWebSetting = array();
	$arrayWebSetting['page_info'] = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7301601");

	/* 履歴管理用のカラムを配列に格納する */
	$tmpAry = array(
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

	/* 画面と１対１で紐付けるテーブルを指定する */
	$table = new TableControlAgent('B_ANSTWR_PHO_LINK','PHO_LINK_ID', $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7391601"), 'B_ANSTWR_PHO_LINK_JNL', $tmpAry);
	$tmpAryColumn = $table->getColumns();
	$tmpAryColumn['PHO_LINK_ID']->setSequenceID('B_ANSTWR_PHO_LINK_RIC');
	$tmpAryColumn['JOURNAL_SEQ_NO']->setSequenceID('B_ANSTWR_PHO_LINK_JSQ');
	unset($tmpAryColumn);

	$table->setDBMainTableLabel($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7361601")); // QMファイル名プレフィックス
	$table->getFormatter('excel')->setGeneValue('sheetNameForEditByFile', $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7341601")); // エクセルのシート名

	/* 検索機能の制御 */
	$table->setGeneObject('AutoSearchStart',true);  //('',true,false)


	/* オペレーション */
	$c = new IDColumn('OPERATION_NO_UAPK',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7391602"),'E_OPERATION_LIST','OPERATION_NO_UAPK','OPERATION','',array('OrderByThirdColumn'=>'OPERATION_NO_UAPK'));
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7351602"));//エクセル・ヘッダでの説明

	//$c->setJournalTableOfMaster('C_OPERATION_LIST_JNL');
	$c->setJournalTableOfMaster('E_OPERATION_LIST_JNL');
	$c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c->setJournalKeyIDOfMaster('OPERATION_NO_UAPK');
	$c->setJournalDispIDOfMaster('OPERATION');
	$c->setRequired(true);//登録/更新時には、入力必須
	$table->addColumn($c);


	/* Movement */
	$c = new IDColumn('PATTERN_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7391603"),'E_ANSTWR_PATTERN','PATTERN_ID','PATTERN','',array('OrderByThirdColumn'=>'PATTERN_ID'));
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7351603"));//エクセル・ヘッダでの説明
	$c->setJournalTableOfMaster('E_ANSTWR_PATTERN_JNL');
	$c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c->setJournalKeyIDOfMaster('PATTERN_ID');
	$c->setJournalDispIDOfMaster('PATTERN');
	$c->setRequired(true);//登録/更新時には、入力必須
	$table->addColumn($c);


	/* ホスト */
	$c = new IDColumn('SYSTEM_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7391604"),'C_STM_LIST','SYSTEM_ID','HOSTNAME','',array('OrderByThirdColumn'=>'SYSTEM_ID'));
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7351604"));//エクセル・ヘッダでの説明
	$c->setJournalTableOfMaster('C_STM_LIST_JNL');
	$c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c->setJournalKeyIDOfMaster('SYSTEM_ID');
	$c->setJournalDispIDOfMaster('HOSTNAME');
	$c->setRequired(true);//登録/更新時には、入力必須
	$table->addColumn($c);


	/* 複合主キーを設定する */
	$table->addUniqueColumnSet(array('OPERATION_NO_UAPK','PATTERN_ID','SYSTEM_ID'));

	/* カラムを確定する */
	$table->fixColumn();
	$table->setGeneObject('webSetting', $arrayWebSetting);

	return $table;
};
loadTableFunctionAdd($tmpFx,__FILE__);

unset($tmpFx);
