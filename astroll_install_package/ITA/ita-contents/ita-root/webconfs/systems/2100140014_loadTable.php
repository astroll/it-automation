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
//   ・AnsibleTower 作業管理
//
//////////////////////////////////////////////////////////////////////

$tmpFx = function (&$aryVariant=array(),&$arySetting=array()){
	global $g;

    $strLrWebRootToThisPageDir = substr(basename(__FILE__), 0, 10);

	$arrayWebSetting = array();
	$arrayWebSetting['page_info'] = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7300401"); //MessageID_SecoundSuffix：04

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

	/* 画面と１対１で紐付けるVIEWの指定 */
	$table = new TableControlAgent('C_ANSTWR_EXE_INS_MNG','EXECUTION_NO',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390401"), 'C_ANSTWR_EXE_INS_MNG_JNL', $tmpAry);
	$tmpAryColumn = $table->getColumns();
	$tmpAryColumn['EXECUTION_NO']->setSequenceID('C_ANSTWR_EXE_INS_MNG_RIC');
	$tmpAryColumn['JOURNAL_SEQ_NO']->setSequenceID('C_ANSTWR_EXE_INS_MNG_JSQ');

	unset($tmpAryColumn);

	/* QMファイル名プレフィックス */
	$table->setDBMainTableLabel($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7360401"));
	// エクセルのシート名
	$table->getFormatter('excel')->setGeneValue('sheetNameForEditByFile', $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7360401"));

	/* 検索機能の制御 */
	$table->setGeneObject('AutoSearchStart',true);  //('',true,false)
	$table->setDBSortKey(array("EXECUTION_NO"=>"DESC"));
	$strTextBody = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7300402");


	/* 作業状態の確認 */
	$c = new LinkButtonColumn( 'MonitorExecution', $strTextBody, $strTextBody, 'monitor_execution', array( ":EXECUTION_NO" ) );
	$c -> setDBColumn(false);
	$table -> addColumn($c);


	/* 実行種別 */
	$c = new IDColumn('RUN_MODE_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390402"),'B_ANSTWR_RUN_MODE','RUN_MODE_ID','RUN_MODE_NAME','');
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350402"));//エクセル・ヘッダでの説明
	$c -> setJournalTableOfMaster('B_ANSTWR_RUN_MODE_JNL');
	$c -> setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c -> setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c -> setJournalKeyIDOfMaster('RUN_MODE_ID');
	$c -> setJournalDispIDOfMaster('RUN_MODE_NAME');

	$table -> addColumn($c);


	/* ステータスID */
	$c = new IDColumn('STATUS_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390403"),'B_ANSTWR_STATUS','STATUS_ID','STATUS_NAME','');
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350403"));//エクセル・ヘッダでの説明
	$c -> setJournalTableOfMaster('B_ANSTWR_STATUS_JNL');
	$c -> setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c -> setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c -> setJournalKeyIDOfMaster('STATUS_ID');
	$c -> setJournalDispIDOfMaster('STATUS_NAME');

	$table -> addColumn($c);

    /* 実行ユーザ */
    $c = new TextColumn('EXECUTION_USER',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390421"));
    $c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390422"));//エクセル・ヘッダでの説明

    $table->addColumn($c);

	/* Symphony インスタンスNO */
	$objVldt = new IntNumValidator(null,null);

	$c = new NumColumn('SYMPHONY_INSTANCE_NO',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390420"));
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350420"));//エクセル・ヘッダでの説明
	$c -> setSubtotalFlag(false);
	$c -> setValidator($objVldt);
	$c -> setRequired(true);//登録/更新時には、入力必須

	$table -> addColumn($c);


	/* <START>カラムグループ（Movement）---------------------------------------------------------------------------------------------------- */
	$cg = new ColumnGroup( $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7310401") );

	/* (Movement)ID */
	$objVldt = new IntNumValidator(null,null);

	$c = new NumColumn('PATTERN_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390404"));
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350404"));//エクセル・ヘッダでの説明
	$c -> setHiddenMainTableColumn(true);
	$c -> setSubtotalFlag(false);
	$c -> setValidator($objVldt);
	$c -> setRequired(true);//登録/更新時には、入力必須

	$cg -> addColumn($c);


	/* (Movement)名称 */
	$objVldt = new SingleTextValidator(1,256,false);

	$c = new TextColumn('I_PATTERN_NAME',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390405"));
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350405"));//エクセル・ヘッダでの説明
	$c -> setHiddenMainTableColumn(true);
	$c -> setValidator($objVldt);
	$c -> setRequired(true);//登録/更新時には、入力必須

	$cg -> addColumn($c);


	/* (Movement)遅延タイマー */
	$objVldt = new IntNumValidator(null,null);

	$c = new NumColumn('I_TIME_LIMIT',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390406"));
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350406"));//エクセル・ヘッダでの説明
	$c -> setHiddenMainTableColumn(true);
	$c -> setSubtotalFlag(false);
	$c -> setValidator($objVldt);
	$c -> setRequired(true);//登録/更新時には、入力必須

	$cg -> addColumn($c);


	/* (Movement)ホスト指定形式 */
	$c = new IDColumn('I_ANS_HOST_DESIGNATE_TYPE_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390407"),'B_HOST_DESIGNATE_TYPE_LIST','HOST_DESIGNATE_TYPE_ID','HOST_DESIGNATE_TYPE_NAME','');
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350407"));//エクセル・ヘッダでの説明
	$c -> setJournalTableOfMaster('B_HOST_DESIGNATE_TYPE_LIST_JNL');
	$c -> setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c -> setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c -> setJournalKeyIDOfMaster('HOST_DESIGNATE_TYPE_ID');
	$c -> setJournalDispIDOfMaster('HOST_DESIGNATE_TYPE_NAME');

	$table -> addColumn($c);


	/* (Movement)並列実行数 */
	$objVldt = new IntNumValidator(null,null);

	$c = new NumColumn('I_ANS_PARALLEL_EXE',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390408"));
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350408"));//エクセル・ヘッダでの説明
	$c -> setHiddenMainTableColumn(true);
	$c -> setSubtotalFlag(false);
	$c -> setValidator($objVldt);
	$c -> setRequired(true);//登録/更新時には、入力必須

	$cg -> addColumn($c);


	/* (Movement)WinRM接続 */
	$c = new IDColumn('I_ANS_WINRM_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390409"),'D_FLAG_LIST_01','FLAG_ID','FLAG_NAME','');
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350409"));//エクセル・ヘッダでの説明
	$c -> setJournalTableOfMaster('D_FLAG_LIST_01_JNL');
	$c -> setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c -> setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c -> setJournalKeyIDOfMaster('FLAG_ID');
	$c -> setJournalDispIDOfMaster('FLAG_NAME');

	$cg -> addColumn($c);


	/* (Movement)gather_facts */
	$c = new IDColumn('I_ANS_GATHER_FACTS',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390410"),'B_ANSTWER_GATHERFACTS_FLAG','FLAG_ID','FLAG_NAME','');
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350410"));//エクセル・ヘッダでの説明
	$c -> setJournalTableOfMaster('B_ANSTWER_GATHERFACTS_FLAG_JNL');
	$c -> setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c -> setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c -> setJournalKeyIDOfMaster('FLAG_ID');
	$c -> setJournalDispIDOfMaster('FLAG_NAME');

	$cg -> addColumn($c);


	$table -> addColumn($cg);
	/* <END>カラムグループ（Movement）------------------------------------------------------------------------------------------------------ */

	/* <START>カラムグループ（オペレーション）---------------------------------------------------------------------------------------------- */
	$cg = new ColumnGroup( $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7310402") );

	/* (オペレーション)No. */
	$objVldt = new IntNumValidator(null,null);

	$c = new NumColumn('OPERATION_NO_UAPK',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390411"));
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350411"));//エクセル・ヘッダでの説明
	$c -> setHiddenMainTableColumn(true);
	$c -> setSubtotalFlag(false);
	$c -> setValidator($objVldt);
	$c -> setRequired(true);//登録/更新時には、入力必須

	$cg -> addColumn($c);


	/* (オペレーション)名称 */
	$objVldt = new SingleTextValidator(1,128,false);

	$c = new TextColumn('I_OPERATION_NAME',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390412"));
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390412"));//エクセル・ヘッダでの説明
	$c -> setHiddenMainTableColumn(true);
	$c -> setValidator($objVldt);
	$c -> setRequired(true);//登録/更新時には、入力必須

	$cg -> addColumn($c);


	/* (オペレーション)ID */
	$objVldt = new IntNumValidator(null,null);

	$c = new NumColumn('I_OPERATION_NO_IDBH',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390413"));
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350413"));//エクセル・ヘッダでの説明
	$c -> setHiddenMainTableColumn(true);
	$c -> setSubtotalFlag(false);
	$c -> setValidator($objVldt);
	$c -> setRequired(true);//登録/更新時には、入力必須

	$cg -> addColumn($c);


	$table -> addColumn($cg);
	/* <END>カラムグループ（オペレーション）------------------------------------------------------------------------------------------------ */


	/* 投入データ */
	$c = new FileUploadColumn( 'FILE_INPUT',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390414"), 
                               "{$g['scheme_n_authority']}/default/menu/05_preupload.php?no={$strLrWebRootToThisPageDir}");
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350414"));
	$c -> setMaxFileSize(1024*1024*20);
	$c -> setFileHideMode(true);
	$c -> setHiddenMainTableColumn(true);

	$table -> addColumn($c);


	/* 結果データ */
	$c = new FileUploadColumn( "FILE_RESULT",$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390415"), 
                               "{$g['scheme_n_authority']}/default/menu/05_preupload.php?no={$strLrWebRootToThisPageDir}");
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350415"));
	$c -> setMaxFileSize(1024*1024*20);
	$c -> setFileHideMode(true);
	$c -> setHiddenMainTableColumn(true);

	$table -> addColumn($c);


	/* <START> カラムグループ（インターフェース情報）--------------------------------------------------------------------------------------- */
	$cg = new ColumnGroup( $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7310403") );

	$c = new IDColumn('I_ANSTWR_DEL_RUNTIME_DATA',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390416"),'B_ANSTWER_RUNDATA_DEL_FLAG','FLAG_ID','FLAG_NAME','');
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350416"));//エクセル・ヘッダでの説明
	$c -> setJournalTableOfMaster('B_ANSTWER_RUNDATA_DEL_FLAG_JNL');
	$c -> setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c -> setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c -> setJournalKeyIDOfMaster('FLAG_ID');
	$c -> setJournalDispIDOfMaster('FLAG_NAME');

	$cg -> addColumn($c);


	$table -> addColumn($cg);
	/* < END > カラムグループ（インターフェース情報）--------------------------------------------------------------------------------------- */


	/* <START>カラムグループ（作業状況）---------------------------------------------------------------------------------------------------- */
	$cg = new ColumnGroup( $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7310404") );

	/* 予約日時 **/
	$c = new DateTimeColumn('TIME_BOOK',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390417"));
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350417"));//エクセル・ヘッダでの説明
	$c -> setValidator(new DateTimeValidator(null,null));

	$cg -> addColumn($c);


	/* 開始日時 */
	$c = new DateTimeColumn('TIME_START',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390418"));
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350418"));//エクセル・ヘッダでの説明
	$c -> setValidator(new DateTimeValidator(null,null));

	$cg -> addColumn($c);


	/* 終了日時 */
	$c = new DateTimeColumn('TIME_END',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390419"));
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350419"));//エクセル・ヘッダでの説明
	$c -> setValidator(new DateTimeValidator(null,null));

	$cg->addColumn($c);


	$table->addColumn($cg);
	/* <END>カラムグループ（作業状況）------------------------------------------------------------------------------------------------------ */

	/* カラムを確定する */
	$table->fixColumn();
	$table->setGeneObject('webSetting', $arrayWebSetting);

	return $table;
};
loadTableFunctionAdd($tmpFx,__FILE__);

unset($tmpFx);
?>
