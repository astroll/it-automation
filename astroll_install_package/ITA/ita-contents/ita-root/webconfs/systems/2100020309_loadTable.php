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
//    ・Ansible（Legacy Role）作業パターン変数紐付管理
//
//////////////////////////////////////////////////////////////////////

$tmpFx = function (&$aryVariant=array(),&$arySetting=array()){
    global $g;

    $arrayWebSetting = array();
    $arrayWebSetting['page_info'] = $g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1209020");
/*
Ansible（Legacy Role）作業パターン変数紐付管理
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

    $table = new TableControlAgent('B_ANS_LRL_PTN_VARS_LINK','VARS_LINK_ID', $g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1209030"), 'B_ANS_LRL_PTN_VARS_LINK_JNL', $tmpAry);
    $tmpAryColumn = $table->getColumns();
    $tmpAryColumn['VARS_LINK_ID']->setSequenceID('B_ANS_LRL_PTN_VARS_LINK_RIC');//B_ANSIBLE_LRL_PTN_VARS_LINK_RIC
    $tmpAryColumn['JOURNAL_SEQ_NO']->setSequenceID('B_ANS_LRL_PTN_VARS_LINK_JSQ');//B_ANSIBLE_LRL_PTN_VARS_LINK_JSQ
    unset($tmpAryColumn);

    // QMファイル名プレフィックス
    $table->setDBMainTableLabel($g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1209040"));
    // エクセルのシート名
    $table->getFormatter('excel')->setGeneValue('sheetNameForEditByFile', $g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1209050"));

    //---- 検索機能の制御
    $table->setGeneObject('AutoSearchStart',true);  //('',true,false)
    // 検索機能の制御----



    $c = new IDColumn('PATTERN_ID',$g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1209060"),'E_ANSIBLE_LRL_PATTERN','PATTERN_ID','PATTERN','',array('OrderByThirdColumn'=>'PATTERN_ID'));
    $c->setDescription($g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1209070"));//エクセル・ヘッダでの説明
    $c->setJournalTableOfMaster('E_ANSIBLE_LRL_PATTERN_JNL');
    $c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
    $c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
    $c->setJournalKeyIDOfMaster('PATTERN_ID');
    $c->setJournalDispIDOfMaster('PATTERN');
    $c->setRequired(true);//登録/更新時には、入力必須
    $table->addColumn($c);

    $c = new IDColumn('VARS_NAME_ID',$g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1209080"),'B_ANSIBLE_LRL_VARS_MASTER','VARS_NAME_ID','VARS_NAME','');
    $c->setDescription($g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1209090"));//エクセル・ヘッダでの説明
    $c->setJournalTableOfMaster('B_ANSIBLE_LRL_VARS_MASTER_JNL');
    $c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
    $c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
    $c->setJournalKeyIDOfMaster('VARS_NAME_ID');
    $c->setJournalDispIDOfMaster('VARS_NAME');
    $c->setRequired(true);//登録/更新時には、入力必須
    $table->addColumn($c);

//----head of setting [multi-set-unique]
	$table->addUniqueColumnSet(array('PATTERN_ID','VARS_NAME_ID'));
//tail of setting [multi-set-unique]----

    $table->fixColumn();

    $table->setGeneObject('webSetting', $arrayWebSetting);
    return $table;
};
loadTableFunctionAdd($tmpFx,__FILE__);
unset($tmpFx);
?>
