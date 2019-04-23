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
//   ・AnsibleTowerインターフェース情報
//
//////////////////////////////////////////////////////////////////////
$tmpFx = function (&$aryVariant=array(),&$arySetting=array()){
	global $g;

	$arrayWebSetting = array();
	$arrayWebSetting['page_info'] = $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7300501"); //MessageID_SecoundSuffix:05

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

	/* 画面と１対１で紐付けるテーブルを指定 */
	$table = new TableControlAgent('B_ANSTWR_IF_INFO','ANSTWR_IF_INFO_ID', $g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390501"), 'B_ANSTWR_IF_INFO_JNL', $tmpAry);
	$tmpAryColumn = $table->getColumns();
	$tmpAryColumn['ANSTWR_IF_INFO_ID']->setSequenceID('B_ANSTWR_IF_INFO_RIC');
	$tmpAryColumn['JOURNAL_SEQ_NO']->setSequenceID('B_ANSTWR_IF_INFO_JSQ');


	/* ファイルアップロードで廃止／復活を無効にする。 */
	$strResultType01 = $g['objMTS']->getSomeMessage("ITAWDCH-STD-12202"); //登録
	$strResultType02 = $g['objMTS']->getSomeMessage("ITAWDCH-STD-12203"); //更新
	$strResultType03 = $g['objMTS']->getSomeMessage("ITAWDCH-STD-12204"); //廃止
	$strResultType04 = $g['objMTS']->getSomeMessage("ITAWDCH-STD-12205"); //復活
	$strResultType99 = $g['objMTS']->getSomeMessage("ITAWDCH-STD-12206"); //エラー

	$tmpAryColumn['ROW_EDIT_BY_FILE'] -> setResultCount
	  (
		array(
			//'register' => array('name' => $strResultType01 , 'ct' => 0),
			'update'   => array('name' => $strResultType02 , 'ct' => 0),
			'error'    => array('name' => $strResultType99 , 'ct' => 0),
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

	/* QMファイル名プレフィックス */
	$table->setDBMainTableLabel($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7360501"));
	$table->getFormatter('excel')->setGeneValue('sheetNameForEditByFile',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7340501")); //エクセルのシート名

	/* 検索機能の制御 */
	$table->setGeneObject('AutoSearchStart',true);  //('',true,false)

	/* データリレイストレージパス(ITA) */
	$objVldt = new SingleTextValidator(1,256,false);

	$c = new TextColumn('ANSTWR_STORAGE_PATH_ITA',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390502"));
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350502"));//エクセル・ヘッダでの説明
	$c -> setHiddenMainTableColumn(true);//コンテンツのソースがビューの場合、登録/更新の対象とする際に、trueとすること。setDBColumn(true)であることも必要。
	$c -> setValidator($objVldt);
	$c -> setRequired(true);//登録/更新時には、入力必須

	$table -> addColumn($c);

	/* データリレイストレージパス(ANSTWR) */
	$objVldt = new SingleTextValidator(1,256,false);

	$c = new TextColumn('ANSTWR_STORAGE_PATH_ANSTWR',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390503"));
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350503"));//エクセル・ヘッダでの説明
	$c -> setHiddenMainTableColumn(true);//コンテンツのソースがビューの場合、登録/更新の対象とする際に、trueとすること。setDBColumn(true)であることも必要。
	$c -> setValidator($objVldt);
	$c -> setRequired(true);//登録/更新時には、入力必須

	$table -> addColumn($c);

	/* Symphonyデータリレイストレージパス(ANSTWR) */
	$objVldt = new SingleTextValidator(1,256,false);

	$c = new TextColumn('SYMPHONY_STORAGE_PATH_ANSTWR',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390512"));
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350512"));//エクセル・ヘッダでの説明
	$c -> setHiddenMainTableColumn(true);//コンテンツのソースがビューの場合、登録/更新の対象とする際に、trueとすること。setDBColumn(true)であることも必要。
	$c -> setValidator($objVldt);
	$c -> setRequired(true);//登録/更新時には、入力必須

	$table -> addColumn($c);

	/* プロトコル */
	$objVldt = new SingleTextValidator(1,8,false);

	$c = new TextColumn('ANSTWR_PROTOCOL',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390504"));
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350504"));//エクセル・ヘッダでの説明
	$c -> setHiddenMainTableColumn(true);//コンテンツのソースがビューの場合、登録/更新の対象とする際に、trueとすること。setDBColumn(true)であることも必要。
	$c -> setValidator($objVldt);
	$c -> setRequired(true);//登録/更新時には、入力必須

	$table -> addColumn($c);


	/* ホスト */
	$objVldt = new SingleTextValidator(1,128,false);

	$c = new TextColumn('ANSTWR_HOSTNAME',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390505"));
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350505"));//エクセル・ヘッダでの説明
	$c -> setHiddenMainTableColumn(true);//コンテンツのソースがビューの場合、登録/更新の対象とする際に、trueとすること。setDBColumn(true)であることも必要。
	$c -> setValidator($objVldt);
	$c -> setRequired(true);//登録/更新時には、入力必須

	$table -> addColumn($c);


	/* ポート */
	$objVldt = new IntNumValidator(null , null);

	$c = new NumColumn('ANSTWR_PORT',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390506"));
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350506"));//エクセル・ヘッダでの説明
	$c -> setHiddenMainTableColumn(true);//コンテンツのソースがビューの場合、登録/更新の対象とする際に、trueとすること。setDBColumn(true)であることも必要。
	$c -> setSubtotalFlag(false);
	$c -> setValidator($objVldt);
	$c -> setRequired(true);//登録/更新時には、入力必須

	$table -> addColumn($c);


	/* ユーザー名 */
	$objVldt = new SingleTextValidator(1,30,false);

	$c = new TextColumn('ANSTWR_USER_ID',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390507"));
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350507"));//エクセル・ヘッダでの説明
	$c -> setHiddenMainTableColumn(true);//コンテンツのソースがビューの場合、登録/更新の対象とする際に、trueとすること。setDBColumn(true)であることも必要。
	$c -> setValidator($objVldt);
	$c -> setRequired(true);//登録/更新時には、入力必須

	$table -> addColumn($c);


	/* パスワード */
	$objVldt = new SingleTextValidator(1,30,false);

	$c = new PasswordColumn('ANSTWR_PASSWORD',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390508"));
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1204010")); // エクセル・ヘッダでの説明
	$c -> setHiddenMainTableColumn(true); // コンテンツのソースがビューの場合、登録/更新の対象とする際に、trueとすること。setDBColumn(true)であることも必要。
	$c -> setValidator($objVldt);
	$c -> setRequired(true); // 登録/更新時には、入力必須
	$c -> setUpdateRequireExcept(1); // 1は空白の場合は維持、それ以外はNULL扱いで更新
	$c -> setEncodeFunctionName("ky_encrypt");

	$table->addColumn($c);

	/* 接続トークン */
	$objVldt = new SingleTextValidator(1,256,false);

	$c = new PasswordColumn('ANSTWR_AUTH_TOKEN',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-10000000"));
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-10000001")); // エクセル・ヘッダでの説明
	$c -> setHiddenMainTableColumn(true); // コンテンツのソースがビューの場合、登録/更新の対象とする際に、trueとすること。setDBColumn(true)であることも必要。
	$c -> setValidator($objVldt);
	$c -> setRequired(true); // 登録/更新時には、入力必須
	$c -> setUpdateRequireExcept(1); // 1は空白の場合は維持、それ以外はNULL扱いで更新
	$c -> setEncodeFunctionName("ky_encrypt");

	$table->addColumn($c);


	/* 実行時データ削除 */
	$c = new IDColumn('ANSTWR_DEL_RUNTIME_DATA',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390509"),'B_ANSTWER_RUNDATA_DEL_FLAG','FLAG_ID','FLAG_NAME','');
	$c -> setDescription($g['objMTS']->getSomeMessage('ITAANSTWRH-MNU-7350509'));//エクセル・ヘッダでの説明
	$c -> setJournalTableOfMaster('B_ANSTWER_RUNDATA_DEL_FLAG_JNL');
	$c -> setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
	$c -> setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
	$c -> setJournalKeyIDOfMaster('FLAG_ID');
	$c -> setJournalDispIDOfMaster('FLAG_NAME');

	$table -> addColumn($c);


	/* 状態監視周期(単位ミリ秒) */
	$objVldt = new IntNumValidator(1000 , null);

	$c = new NumColumn('ANSTWR_REFRESH_INTERVAL',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390510"));
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350510"));//エクセル・ヘッダでの説明
	$c -> setHiddenMainTableColumn(true);//コンテンツのソースがビューの場合、登録/更新の対象とする際に、trueとすること。setDBColumn(true)であることも必要。
	$c -> setSubtotalFlag(false);
	$c -> setValidator($objVldt);
	$c -> setRequired(true);//登録/更新時には、入力必須

	$table -> addColumn($c);


	/* 進行状態表示行数 */
	$objVldt = new IntNumValidator(null , null);

	$c = new NumColumn('ANSTWR_TAILLOG_LINES',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390511"));
	$c -> setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350511"));//エクセル・ヘッダでの説明
	$c -> setHiddenMainTableColumn(true);//コンテンツのソースがビューの場合、登録/更新の対象とする際に、trueとすること。setDBColumn(true)であることも必要。
	$c -> setSubtotalFlag(false);
	$c -> setValidator($objVldt);
	$c -> setRequired(true);//登録/更新時には、入力必須

	$table -> addColumn($c);

    ////////////////////////////////////////////////////////////////////
    // パラメータシートの具体値がNULLでも代入値管理に登録するかのフラグ
    ////////////////////////////////////////////////////////////////////
    $c = new IDColumn('NULL_DATA_HANDLING_FLG',$g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7390513"),'B_VALID_INVALID_MASTER','FLAG_ID','FLAG_NAME','', array('OrderByThirdColumn'=>'FLAG_ID'));
    $c->setDescription($g['objMTS']->getSomeMessage("ITAANSTWRH-MNU-7350514"));
    $c->setHiddenMainTableColumn(true); //更新対象カラム

    $c->setRequired(true);

    //コンテンツのソースがヴューの場合、登録/更新の対象とする
    $c->setHiddenMainTableColumn(true);

    //エクセル/CSVからのアップロードを禁止する。
    $c->setAllowSendFromFile(true);

    $table->addColumn($c);
    
	/* カラムを確定する */
	$table->fixColumn();
	$table->setGeneObject('webSetting', $arrayWebSetting);

	return $table;
};
loadTableFunctionAdd($tmpFx,__FILE__);

unset($tmpFx);
?>
