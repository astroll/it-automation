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
//    ・Ansible(Legacy Role)インターフェース情報 
//
//////////////////////////////////////////////////////////////////////
$tmpFx = function (&$aryVariant=array(),&$arySetting=array()){
    global $g;

    $arrayWebSetting = array();
    $arrayWebSetting['page_info'] = $g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1202020");
/*
Ansible(Legacy Role)インターフェース情報
*/
    $tmpAry = array(
        'TT_SYS_01_JNL_SEQ_ID'=>'JOURNAL_SEQ_NO',
        'TT_SYS_02_JNL_TIME_ID'=>'JOURNAL_REG_DATETIME',
        'TT_SYS_03_JNL_CLASS_ID'=>'JOURNAL_ACTION_CLASS',
        'TT_SYS_04_NOTE_ID'=>'NOTE',
        'TT_SYS_04_DISUSE_FLAG_ID'=>'DISUSE_FLAG',
        'TT_SYS_05_LUP_TIME_ID'=>'LAST_UPDATE_TIMESTAMP',
        'TT_SYS_06_LUP_USER_ID'=>'LAST_UPDATE_USER',
        'TT_SYS_NDB_ROW_EDIT_BY_FILE_ID'=>'ROW_EDIT_BY_FILE',
        'TT_SYS_NDB_UPDATE_ID'=>'WEB_BUTTON_UPDATE',
        'TT_SYS_NDB_LUP_TIME_ID'=>'UPD_UPDATE_TIMESTAMP'
    );

    $table = new TableControlAgent('D_ANSIBLE_LRL_IF_INFO','ANSIBLE_IF_INFO_ID', $g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1202030"), 'D_ANSIBLE_LRL_IF_INFO_JNL', $tmpAry);
    $tmpAryColumn = $table->getColumns();
    $tmpAryColumn['ANSIBLE_IF_INFO_ID']->setSequenceID('B_ANSIBLE_IF_INFO_RIC');
    $tmpAryColumn['JOURNAL_SEQ_NO']->setSequenceID('B_ANSIBLE_IF_INFO_JSQ');
    unset($tmpAryColumn);

    // ----VIEWをコンテンツソースにする場合、構成する実体テーブルを更新するための設定
    $table->setDBMainTableHiddenID('B_ANSIBLE_IF_INFO');
    $table->setDBJournalTableHiddenID('B_ANSIBLE_IF_INFO_JNL');
    // 利用時は、更新対象カラムに、「$c->setHiddenMainTableColumn(true);」を付加すること
    // VIEWをコンテンツソースにする場合、構成する実体テーブルを更新するための設定----

    // QMファイル名プレフィックス
    $table->setDBMainTableLabel($g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1202040"));
    // エクセルのシート名
    $table->getFormatter('excel')->setGeneValue('sheetNameForEditByFile',$g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1202050"));

    //---- 検索機能の制御
    $table->setGeneObject('AutoSearchStart',true);  //('',true,false)
    // 検索機能の制御----



	$objVldt = new SingleTextValidator(1,256,false);
    $c = new TextColumn('ANSIBLE_STORAGE_PATH_LNX',$g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1202060"));
    $c->setDescription($g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1202070"));//エクセル・ヘッダでの説明
    $c->setHiddenMainTableColumn(true);//コンテンツのソースがヴューの場合、登録/更新の対象とする際に、trueとすること。setDBColumn(true)であることも必要。
	$c->setValidator($objVldt);
    $c->setRequired(true);//登録/更新時には、入力必須
    $table->addColumn($c);

	$objVldt = new SingleTextValidator(1,256,false);
    $c = new TextColumn('ANSIBLE_STORAGE_PATH_ANS',$g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1202080"));
    $c->setDescription($g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1202090"));//エクセル・ヘッダでの説明
    $c->setHiddenMainTableColumn(true);//コンテンツのソースがヴューの場合、登録/更新の対象とする際に、trueとすること。setDBColumn(true)であることも必要。
	$c->setValidator($objVldt);
    $c->setRequired(true);//登録/更新時には、入力必須
    $table->addColumn($c);

	$objVldt = new SingleTextValidator(1,256,false);
    $c = new TextColumn('SYMPHONY_STORAGE_PATH_ANS',$g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1202095"));
    $c->setDescription($g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1202096"));//エクセル・ヘッダでの説明
    $c->setHiddenMainTableColumn(true);//コンテンツのソースがヴューの場合、登録/更新の対象とする際に、trueとすること。setDBColumn(true)であることも必要。
	$c->setValidator($objVldt);
    $c->setRequired(true);//登録/更新時には、入力必須
    $table->addColumn($c);

	$objVldt = new SingleTextValidator(1,8,false);
    $c = new TextColumn('ANSIBLE_PROTOCOL',$g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1203010"));
    $c->setDescription($g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1203020"));//エクセル・ヘッダでの説明
    $c->setHiddenMainTableColumn(true);//コンテンツのソースがヴューの場合、登録/更新の対象とする際に、trueとすること。setDBColumn(true)であることも必要。
	$c->setValidator($objVldt);
    $c->setRequired(true);//登録/更新時には、入力必須
    $table->addColumn($c);

	$objVldt = new SingleTextValidator(1,128,false);
    $c = new TextColumn('ANSIBLE_HOSTNAME',$g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1203030"));
    $c->setDescription($g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1203040"));//エクセル・ヘッダでの説明
    $c->setHiddenMainTableColumn(true);//コンテンツのソースがヴューの場合、登録/更新の対象とする際に、trueとすること。setDBColumn(true)であることも必要。
	$c->setValidator($objVldt);
    $c->setRequired(true);//登録/更新時には、入力必須
    $table->addColumn($c);

    $c = new NumColumn('ANSIBLE_PORT',$g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1203050"));
    $c->setDescription($g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1203060"));//エクセル・ヘッダでの説明
    $c->setHiddenMainTableColumn(true);//コンテンツのソースがヴューの場合、登録/更新の対象とする際に、trueとすること。setDBColumn(true)であることも必要。
    $c->setSubtotalFlag(false);
	$c->setValidator(new IntNumValidator(null,null));
    $c->setRequired(true);//登録/更新時には、入力必須
    $table->addColumn($c);

    /////////////////////////////////////////////////
    // Ansible実行時のオプションパラメータ
    /////////////////////////////////////////////////
    $objVldt = new SingleTextValidator(0,512,false);
    $c = new TextColumn('ANSIBLE_EXEC_OPTIONS',$g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1204015"));
    $c->setDescription($g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1204016"));

    $c->setHiddenMainTableColumn(true);

    $objVldt = new SingleTextValidator(0,512,false);
    $c->setValidator($objVldt);

    // 必須入力ではない
    $c->setRequired(false);

    $table->addColumn($c);

	$objVldt = new SingleTextValidator(1,64,false);
    $c = new TextColumn('ANSIBLE_ACCESS_KEY_ID',$g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1203070"));
    $c->setDescription($g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1203080"));//エクセル・ヘッダでの説明
    $c->setHiddenMainTableColumn(true);//コンテンツのソースがヴューの場合、登録/更新の対象とする際に、trueとすること。setDBColumn(true)であることも必要。
	$c->setValidator($objVldt);
    $c->setRequired(true);//登録/更新時には、入力必須
    $table->addColumn($c);

	$objVldt = new SingleTextValidator(1,64,false);
    $c = new PasswordColumn('ANSIBLE_SECRET_ACCESS_KEY',$g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1203090"));
    $c->setDescription($g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1204010"));//エクセル・ヘッダでの説明
    $c->setHiddenMainTableColumn(true);
	$c->setValidator($objVldt);
    $c->setRequired(true);//登録/更新時には、入力必須
    $c->setUpdateRequireExcept(1);//1は空白の場合は維持、それ以外はNULL扱いで更新
    $c->setEncodeFunctionName("ky_encrypt");
    $table->addColumn($c);

    $c = new NumColumn('ANSIBLE_REFRESH_INTERVAL',$g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1204020"));
    $c->setDescription($g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1204030"));//エクセル・ヘッダでの説明
    $c->setHiddenMainTableColumn(true);//コンテンツのソースがヴューの場合、登録/更新の対象とする際に、trueとすること。setDBColumn(true)であることも必要。
    $c->setSubtotalFlag(false);
	$c->setValidator(new IntNumValidator(1000,null));
    $c->setRequired(true);//登録/更新時には、入力必須
    $table->addColumn($c);

    $c = new NumColumn('ANSIBLE_TAILLOG_LINES',$g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1204040"));
    $c->setDescription($g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1204050"));//エクセル・ヘッダでの説明
    $c->setHiddenMainTableColumn(true);//コンテンツのソースがヴューの場合、登録/更新の対象とする際に、trueとすること。setDBColumn(true)であることも必要。
    $c->setSubtotalFlag(false);
	$c->setValidator(new IntNumValidator(null,null));
    $c->setRequired(true);//登録/更新時には、入力必須
    $table->addColumn($c);

    ////////////////////////////////////////////////////////////////////
    // パラメータシートの具体値がNULLでも代入値管理に登録するかのフラグ
    ////////////////////////////////////////////////////////////////////
    $c = new IDColumn('NULL_DATA_HANDLING_FLG',$g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-6000000"),'B_VALID_INVALID_MASTER','FLAG_ID','FLAG_NAME','', array('OrderByThirdColumn'=>'FLAG_ID'));
    $c->setDescription($g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-6000002"));
    $c->setHiddenMainTableColumn(true); //更新対象カラム

    $c->setRequired(true);

    //コンテンツのソースがヴューの場合、登録/更新の対象とする
    $c->setHiddenMainTableColumn(true);

    $table->addColumn($c);


    $table->fixColumn();

    $table->setGeneObject('webSetting', $arrayWebSetting);

    $tmpAryColumn = $table->getColumns();
    $tmpAryColumn['ROW_EDIT_BY_FILE']->setResultCount(
        array(
         'update'  =>array('name'=>$g['objMTS']->getSomeMessage("ITAWDCH-STD-12203"), 'ct'=>0),
         'error'   =>array('name'=>$g['objMTS']->getSomeMessage("ITAWDCH-STD-12206"), 'ct'=>0)
        )
    );
    $tmpAryColumn['ROW_EDIT_BY_FILE']->setCommandArrayForEdit(
        array(
            2=>$g['objMTS']->getSomeMessage("ITAWDCH-STD-12203")
        )
    );
    //廃止・復活ボタンを隠す
    $outputType = new OutputType(new TabHFmt(), new DelTabBFmt());
    $tmpAryColumn['DISUSE_FLAG']->setOutputType("print_table", $outputType);

    return $table;
};
loadTableFunctionAdd($tmpFx,__FILE__);
unset($tmpFx);
?>
