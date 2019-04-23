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
//      多段変数最大繰返数メニュー反映ファイル
//          「多段変数メンバー管理」のMEMBER_DISPが1のレコードを「多段変数配列組合せ管理」に入れる。
//          その際、「多段変数最大繰返数管理」にレコードがある変数については、DEFAULT_MAX_COL_SEQの数だけ膨らませる。
//
//  【その他】
//      多段変数メンバー管理    : B_ANSTWR_NESTED_MEM_VARS
//      多段変数配列組合せ管理 : B_ANSTWR_NESTEDMEM_COL_CMB
//      多段変数最大繰返数管理 : B_ANSTWR_MAX_MEMBER_COL
//
//////////////////////////////////////////////////////////////////////

$ansible_libs_dir_path = '/libs/backyardlibs/ansibletower_driver/vars_listup';

// 多段変数メンバー管理との同期処理
require($root_dir_path . $ansible_libs_dir_path . '/syncDefaultMaxMemberCol_module.php');

$ret = syncDefaultMaxMemberCol();
if($ret === false) {
    // 異常フラグON  例外処理へ
    $error_flag = 1;
    throw new Exception( 'Error occurred ( ' . implode(array(__FILE__, __LINE__, "00001010")) . ' )' );
}

// 多段変数最大繰返数メニュー反映ファイル 本体
require($root_dir_path . $ansible_libs_dir_path . '/expandNestedVariables_module.php');
$ret = expandNestedVariables();
if($ret === false) {
    // 異常フラグON  例外処理へ
    $error_flag = 1;
    throw new Exception( 'Error occurred ( ' . implode(array(__FILE__, __LINE__, "00001010")) . ' )' );
}

// main logic ここまで、以下共通library

function rollbackTransaction() {

    global    $objDBCA;
    global    $objMTS;
    global    $log_level;

    if($objDBCA->getTransactionMode()) {
        // ロールバック
        if($objDBCA->transactionRollBack() === true) {
            //$FREE_LOG = '[処理]ロールバック';
            $traceMessage = '[Process] Rollback';
        } else {
            $traceMessage = 'Rollback has failed.';
        }
        // トレースメッセージ
        if($log_level === 'DEBUG') {
            LocalLogPrint(basename(__FILE__),__LINE__,$traceMessage);
        }
    }
}

function dbaccessSelect($sqlBody, $arrayBind, &$arrayResult) {

    global $log_level;
    if($log_level === "DEBUG") {
        $debugLog = 'PARAMS => $sqlBody: ' . $sqlBody . ', $arrayBind: ' . var_export($arrayBind, true);
        LocalLogPrint(basename(__FILE__), __LINE__, $debugLog);
    }

    global $objDBCA;
    global $objMTS;

    $objQuery = $objDBCA->sqlPrepare($sqlBody);
    if($objQuery->getStatus() === false) {
        $message = 'DB access error occurred. ( ' . implode(array(basename(__FILE__),__LINE__)) . ' )' ;
        LocalLogPrint(basename(__FILE__),__LINE__,$message);
        $errorDetail = $objQuery->getLastError();
        LocalLogPrint(basename(__FILE__),__LINE__,$errorDetail);
        unset($objQuery);
        return false;
    }

    if(isset($arrayBind) && $objQuery->sqlBind($arrayBind) != "") {
        $message = 'DB access error occurred. ( ' . implode(array(basename(__FILE__),__LINE__)) . ' )' ;
        LocalLogPrint(basename(__FILE__),__LINE__,$message);
        $errorDetail = $objQuery->getLastError();
        LocalLogPrint(basename(__FILE__),__LINE__,$errorDetail);
        unset($objQuery);
        return false;
    }

    $r = $objQuery->sqlExecute();
    if(!$r) {
        $message = 'DB access error occurred. ( ' . implode(array(basename(__FILE__),__LINE__)) . ' )' ;
        LocalLogPrint(basename(__FILE__),__LINE__,$message);
        $errorDetail = $objQuery->getLastError();
        LocalLogPrint(basename(__FILE__),__LINE__,$errorDetail);
        unset($objQuery);
        return false;
    }

    while($row = $objQuery->resultFetch()) {
        $arrayResult[] = $row;
    }
}

function dbaccessExecute($sqlBody, $arrayBind) {

    global $log_level;
    if($log_level === "DEBUG") {
        $debugLog = 'PARAMS => $sqlBody: ' . $sqlBody . ', $arrayBind: ' . var_export($arrayBind, true);
        LocalLogPrint(basename(__FILE__), __LINE__, $debugLog);
    }

    global $objDBCA;
    global $objMTS;

    $objQuery = $objDBCA->sqlPrepare($sqlBody);
    if($objQuery->getStatus() === false) {
        $message = 'DB access error occurred. ( ' . implode(array(basename(__FILE__),__LINE__)) . ' )' ;
        LocalLogPrint(basename(__FILE__),__LINE__,$message);
        $errorDetail = $objQuery->getLastError();
        LocalLogPrint(basename(__FILE__),__LINE__,$errorDetail);
        unset($objQuery);
        return false;
    }

    if(isset($arrayBind) && $objQuery->sqlBind($arrayBind) != "") {
        $message = 'DB access error occurred. ( ' . implode(array(basename(__FILE__),__LINE__)) . ' )' ;
        LocalLogPrint(basename(__FILE__),__LINE__,$message);
        $errorDetail = $objQuery->getLastError();
        LocalLogPrint(basename(__FILE__),__LINE__,$errorDetail);
        unset($objQuery);
        return false;
    }

    $r = $objQuery->sqlExecute();
    if(!$r) {
        $message = 'DB access error occurred. ( ' . implode(array(basename(__FILE__),__LINE__)) . ' )' ;
        LocalLogPrint(basename(__FILE__),__LINE__,$message);
        $errorDetail = $objQuery->getLastError();
        LocalLogPrint(basename(__FILE__),__LINE__,$errorDetail);
        unset($objQuery);
        return false;
    }

    return true;
}

function dbaccessInsert($tableName, $specificColumn, $targetColumns, $insertRecords) {

    global $log_level;

    if($log_level === "DEBUG") {
        $debugLog = 'PARAMS => $tableName: ' . $tableName . ', $specificColumn: ' . $specificColumn
            . ', $insertRecords: ' . var_export($insertRecords, true);
        LocalLogPrint(basename(__FILE__), __LINE__, $debugLog);
    }

    $insertKeyValue = null;
    foreach($insertRecords as $record) {
        $result = dbaccessInsertEach($tableName, $specificColumn, $targetColumns, $record);

        if($result == false) {
            return false;
        }
    }

    return true;
}

function dbaccessInsertEach($targetTable, $specificColumn, $targetColumns, $insertKeyValue) {

    global $objDBCA;
    global $db_access_user_id;

    $strCurTable      = $targetTable;
    $strJnlTable      = $strCurTable . "_JNL";
    $strSeqOfCurTable = $strCurTable . "_RIC";
    $strSeqOfJnlTable = $strCurTable . "_JSQ";

    $curId = dbaccessGetSequence($strSeqOfCurTable);
    $jnlId = dbaccessGetSequence($strSeqOfJnlTable);

    if(!$curId || !$jnlId) {
        return false;
    }

    // 主キーカラム
    $insertKeyValue[$specificColumn]        = $curId;

    // ロール管理ジャーナルに登録する情報設定
    $insertKeyValue['JOURNAL_SEQ_NO']       = $jnlId;

    // 共通カラム
    $insertKeyValue['DISUSE_FLAG']     = "0";
    $insertKeyValue['LAST_UPDATE_USER']     = $db_access_user_id;

    $temp_array = array();
    $retArray = makeSQLForUtnTableUpdate($objDBCA->getModelChannel(),
                                         "INSERT",
                                         $specificColumn,
                                         $strCurTable,
                                         $strJnlTable,
                                         $targetColumns,
                                         $insertKeyValue,
                                         $temp_array);

    $sqlCurBody = $retArray[1];
    $arrayCurBind = $retArray[2];

    $sqlJnlBody = $retArray[3];
    $arrayJnlBind = $retArray[4];

    if(!dbaccessExecute($sqlCurBody, $arrayCurBind) ||
        !dbaccessExecute($sqlJnlBody, $arrayJnlBind)) {
        return false;
    }

    return true;
}

function dbaccessUpdateRevive($tableName, $specificColumn, $targetColumns, $targetRecords) {

    global $log_level;
    if($log_level === "DEBUG") {
        $debugLog = 'PARAMS => $tableName: ' . $tableName . ', $specificColumn: ' . $specificColumn
            . ', $targetRecords: ' . var_export($targetRecords, true);
        LocalLogPrint(basename(__FILE__), __LINE__, $debugLog);
    }

    $updateKeyValue = null;
    foreach($targetRecords as $record) {
        $record['DISUSE_FLAG'] = "0";
        $result = dbaccessUpdateEach($tableName, $specificColumn, $targetColumns, $record);

        if($result == false) {
            return false;
        }
    }

    return true;
}

function dbaccessUpdateDisuse($tableName, $specificColumn, $targetColumns, $targetRecords) {

    global $log_level;
    if($log_level === "DEBUG") {
        $debugLog = 'PARAMS => $tableName: ' . $tableName . ', $specificColumn: ' . $specificColumn
            . ', $targetRecords: ' . var_export($targetRecords, true);
        LocalLogPrint(basename(__FILE__), __LINE__, $debugLog);
    }

    $updateKeyValue = null;
    foreach($targetRecords as $record) {
        $record['DISUSE_FLAG'] = "1";
        $result = dbaccessUpdateEach($tableName, $specificColumn, $targetColumns, $record);

        if($result == false) {
            return false;
        }
    }

    return true;
}

function dbaccessUpdateEach($targetTable, $specificColumn, $targetColumns, $updateKeyValue) {

    global $objDBCA;
    global $db_access_user_id;

    $strCurTable      = $targetTable;
    $strJnlTable      = $strCurTable . "_JNL";
    $strSeqOfCurTable = $strCurTable . "_RIC";
    $strSeqOfJnlTable = $strCurTable . "_JSQ";

    $jnlId = dbaccessGetSequence($strSeqOfJnlTable);

    if(!$jnlId) {
        return false;
    }

    // ロール管理ジャーナルに登録する情報設定
    $updateKeyValue['JOURNAL_SEQ_NO']       = $jnlId;
    $updateKeyValue['LAST_UPDATE_USER']     = $db_access_user_id;

    $temp_array = array();
    $retArray = makeSQLForUtnTableUpdate($objDBCA->getModelChannel(),
                                         "UPDATE",
                                         $specificColumn,
                                         $strCurTable,
                                         $strJnlTable,
                                         $targetColumns,
                                         $updateKeyValue,
                                         $temp_array);

    $sqlCurBody = $retArray[1];
    $arrayCurBind = $retArray[2];

    $sqlJnlBody = $retArray[3];
    $arrayJnlBind = $retArray[4];

    if(!dbaccessExecute($sqlCurBody, $arrayCurBind) ||
        !dbaccessExecute($sqlJnlBody, $arrayJnlBind)) {
        return false;
    }

    return true;
}

function dbaccessGetSequence($tableName) {

    ////////////////////////////////////////////////////////////////
    // テーブルシーケンスをロック                                 //
    ////////////////////////////////////////////////////////////////
    $retArray = getSequenceLockInTrz($tableName, 'A_SEQUENCE');
    if($retArray[1] != 0) {
        $FREE_LOG = 'DB access error occurred. ( ' . implode(array(basename(__FILE__),__LINE__)) . ' )' ;
        LocalLogPrint(basename(__FILE__),__LINE__,$FREE_LOG);

        return null;
    }

    ////////////////////////////////////////////////////////////////
    // テーブルシーケンスを採番                                   //
    ////////////////////////////////////////////////////////////////
    $retArray = getSequenceValueFromTable($tableName, 'A_SEQUENCE', FALSE);
    if($retArray[1] != 0) {
        $FREE_LOG = 'DB access error occurred. ( ' . implode(array(basename(__FILE__),__LINE__)) . ' )' ;
        LocalLogPrint(basename(__FILE__),__LINE__,$FREE_LOG);

        return null;
    }

    return $retArray[0];
}

function LocalLogPrint($p1, $p2, $p3) {
    global $logger;
    $msg = "FILE:$p1 LINE:$p2 $p3";
    $logger->debug($msg);
}

?>
