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
//   ・AnsibleTower 多段変数メンバー管理
//
//////////////////////////////////////////////////////////////////////

$tmpFx = function (&$aryVariant=array(),&$arySetting=array()){
	global $g;

	$arrayWebSetting = array();
	$arrayWebSetting['page_info'] = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7300801"); // MessageID_SecoundSuffix：08

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

	/* 画面に１体１で紐付けるテーブルを指定 */
	$table = new TableControlAgent('B_ANSTWR_NESTED_MEM_VARS','NESTED_MEM_VARS_ID', $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390801"), 'B_ANSTWR_NESTED_MEM_VARS_JNL', $tmpAry);
	$tmpAryColumn = $table->getColumns();
	$tmpAryColumn['NESTED_MEM_VARS_ID']->setSequenceID('B_ANSTWR_NESTED_MEM_VARS_RIC');
	$tmpAryColumn['JOURNAL_SEQ_NO']->setSequenceID('B_ANSTWR_NESTED_MEM_VARS_JSQ');
	unset($tmpAryColumn);

	/* QMファイル名プレフィックス */
	$table->setDBMainTableLabel($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7360801"));
	/* エクセルのシート名 */
	$table->getFormatter('excel')->setGeneValue('sheetNameForEditByFile',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7340801"));

	/* 検索機能の制御 */
	$table->setGeneObject('AutoSearchStart',true);  //('',true,false)

	/* 変数名 */
	$c = new IDColumn('VARS_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390802"),'B_ANSTWR_VARS','VARS_ID','VARS_NAME','');
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350802"));//エクセル・ヘッダでの説明
	$c->setJournalTableOfMaster('B_ANSTWR_VARS_JNL');
	$c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c->setJournalKeyIDOfMaster('VARS_ID');
	$c->setJournalDispIDOfMaster('VARS_NAME');
	$c->setRequired(true);//登録/更新時には、入力必須
	$table->addColumn($c);

	/* 親メンバーキー */
	$c = new NumColumn('PARENT_KEY_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390803"));
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350803"));//エクセル・ヘッダでの説明
	$c->setSubtotalFlag(false);
	$c->setValidator(new IntNumValidator(null,null));
	$table->addColumn($c);

	/* 自キー */
	$c = new NumColumn('SELF_KEY_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390804"));
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350804"));//エクセル・ヘッダでの説明
	$c->setSubtotalFlag(false);
	$c->setValidator(new IntNumValidator(null,null));
	$table->addColumn($c);

	/* メンバー変数名 */
	$objVldt = new SingleTextValidator(1,128,false);
	$c = new TextColumn('MEMBER_NAME',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390805"));
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350805"));//エクセル・ヘッダでの説明
	$c->setValidator($objVldt);
	$table->addColumn($c);

	/* 階層 */
	$c = new NumColumn('NESTED_LEVEL',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390806"));
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390806"));//エクセル・ヘッダでの説明
	$c->setSubtotalFlag(false);
	$c->setValidator(new IntNumValidator(null,null));
	$table->addColumn($c);

	/* 代入順序有無 */
	$c = new NumColumn('ASSIGN_SEQ_NEED',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390807"));
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390807"));//エクセル・ヘッダでの説明
	$c->setSubtotalFlag(false);
	$c->setValidator(new IntNumValidator(null,null));
	$table->addColumn($c);

	/* 列順序有無 */
	$c = new NumColumn('COL_SEQ_NEED',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390808"));
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350808"));//エクセル・ヘッダでの説明
	$c->setSubtotalFlag(false);
	$c->setValidator(new IntNumValidator(null,null));
	$table->addColumn($c);

	/* 代入値管理表示有無 */
	$c = new NumColumn('MEMBER_DISP',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390809"));
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350809"));//エクセル・ヘッダでの説明
	$c->setSubtotalFlag(false);
	$c->setValidator(new IntNumValidator(null,null));
	$table->addColumn($c);

	/* 最大繰返し数 */
	$c = new NumColumn('MAX_COL_SEQ',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390810"));
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350810"));//エクセル・ヘッダでの説明
	$c->setSubtotalFlag(false);
	$c->setValidator(new IntNumValidator(null,null));
	$table->addColumn($c);

	/* メンバー変数階層パス */
	$objVldt = new SingleTextValidator(1,1024,false);
	$c = new TextColumn('NESTED_MEMBER_PATH',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390811"));
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350811"));//エクセル・ヘッダでの説明
	$c->setValidator($objVldt);
	$table->addColumn($c);

	/* 代入値管理表示メンバパス */
	$objVldt = new SingleTextValidator(1,1024,false);
	$c = new TextColumn('NESTED_MEMBER_PATH_ALIAS',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390812"));
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350812"));//エクセル・ヘッダでの説明
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
