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
//   ・AnsibleTower 認証情報タイプリスト
//
//////////////////////////////////////////////////////////////////////

$tmpFx = function (&$aryVariant=array(),&$arySetting=array()){
	global $g;

	$arrayWebSetting = array();
	$arrayWebSetting['page_info'] = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7300201"); // MessageID_SecoundSuffix：02

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
	$table = new TableControlAgent('B_ANSTWR_CREDENTIAL_TYPE','CREDENTIAL_TYPE_ITA_MANAGED_ID', $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390201"), 'B_ANSTWR_CREDENTIAL_TYPE_JNL', $tmpAry);
	$tmpAryColumn = $table->getColumns();
	$tmpAryColumn['CREDENTIAL_TYPE_ITA_MANAGED_ID']->setSequenceID('B_ANSTWR_CREDENTIAL_TYPE_RIC');
	$tmpAryColumn['JOURNAL_SEQ_NO']->setSequenceID('B_ANSTWR_CREDENTIAL_TYPE_JSQ');
	unset($tmpAryColumn);

	/* QMファイル名プレフィックス */
	$table->setDBMainTableLabel($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7360201"));
	/* エクセルのシート名 */
	$table->getFormatter('excel')->setGeneValue('sheetNameForEditByFile',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7340201"));

	/* 検索機能の制御 */
	$table->setGeneObject('AutoSearchStart',true);  //('',true,false)

	/* 認証情報タイプID */
	$c = new NumColumn('CREDENTIAL_TYPE_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390202"));
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350202"));//エクセル・ヘッダでの説明
	$c->setSubtotalFlag(false);
	$c->setValidator(new IntNumValidator(null,null));
	$table->addColumn($c);

	/* 認証情報タイプ名 */
	$objVldt = new SingleTextValidator(1,512,false);
	$c = new TextColumn('CREDENTIAL_TYPE_NAME',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390203"));
	$c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350203"));//エクセル・ヘッダでの説明
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
