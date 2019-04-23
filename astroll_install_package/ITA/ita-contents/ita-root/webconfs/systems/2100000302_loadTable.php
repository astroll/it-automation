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
//    ・WebDBCore機能を用いたWebページの中核設定を行う。
//
//////////////////////////////////////////////////////////////////////

$tmpFx = function (&$aryVariant=array(),&$arySetting=array()){
    global $g;

    $arrayWebSetting = array();
    $arrayWebSetting['page_info'] = $g['objMTS']->getSomeMessage("ITABASEH-MNU-106020");
/*
OS種別マスタ情報
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

    $table = new TableControlAgent('B_OS_TYPE','OS_TYPE_ID', $g['objMTS']->getSomeMessage("ITABASEH-MNU-106030"), 'B_OS_TYPE_JNL', $tmpAry);

    $tmpAryColumn = $table->getColumns();
    $tmpAryColumn['OS_TYPE_ID']->setSequenceID('B_OS_TYPE_RIC');
    $tmpAryColumn['JOURNAL_SEQ_NO']->setSequenceID('B_OS_TYPE_JSQ');
    unset($tmpAryColumn);

    
    // QMファイル名プレフィックス
    $table->setDBMainTableLabel($g['objMTS']->getSomeMessage("ITABASEH-MNU-106040"));
    // エクセルのシート名
    $table->getFormatter('excel')->setGeneValue('sheetNameForEditByFile',$g['objMTS']->getSomeMessage("ITABASEH-MNU-106050"));

    //---- 検索機能の制御
    $table->setGeneObject('AutoSearchStart',true);  //('',true,false)
    // 検索機能の制御----

	$objVldt = new SingleTextValidator(1,128,false);
    $c = new TextColumn('OS_TYPE_NAME',$g['objMTS']->getSomeMessage("ITABASEH-MNU-106060"));
    $c->setDescription($g['objMTS']->getSomeMessage("ITABASEH-MNU-106070"));//エクセル・ヘッダでの説明
	$c->setValidator($objVldt);
    $c->setRequired(true);//登録/更新時には、入力必須
    $c->setUnique(true);//登録/更新時には、DB上ユニークな入力であること必須
    $table->addColumn($c);
    
    $cg = new ColumnGroup( $g['objMTS']->getSomeMessage("ITABASEH-MNU-106075") );
    
        $c = new IDColumn('HARDAWRE_TYPE_SV',$g['objMTS']->getSomeMessage("ITABASEH-MNU-106080"),'D_FLAG_LIST_01','FLAG_ID','FLAG_NAME','');
        $c->setDescription($g['objMTS']->getSomeMessage("ITABASEH-MNU-106090"));//エクセル・ヘッダでの説明
        $cg->addColumn($c);

        $c = new IDColumn('HARDAWRE_TYPE_NW',$g['objMTS']->getSomeMessage("ITABASEH-MNU-107010"),'D_FLAG_LIST_01','FLAG_ID','FLAG_NAME','');
        $c->setDescription($g['objMTS']->getSomeMessage("ITABASEH-MNU-107020"));//エクセル・ヘッダでの説明
        $cg->addColumn($c);

        $c = new IDColumn('HARDAWRE_TYPE_ST',$g['objMTS']->getSomeMessage("ITABASEH-MNU-107030"),'D_FLAG_LIST_01','FLAG_ID','FLAG_NAME','');
        $c->setDescription($g['objMTS']->getSomeMessage("ITABASEH-MNU-107040"));//エクセル・ヘッダでの説明
        $cg->addColumn($c);
        
    $table->addColumn($cg);
    

    $table->fixColumn();

    $table->setGeneObject('webSetting', $arrayWebSetting);
    return $table;
};
loadTableFunctionAdd($tmpFx,__FILE__);
unset($tmpFx);
?>
