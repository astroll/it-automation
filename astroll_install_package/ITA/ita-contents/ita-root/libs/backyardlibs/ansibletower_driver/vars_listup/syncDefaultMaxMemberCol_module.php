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
//      多段変数最大繰返数メニュー反映ファイル 前準備
//          「多段変数メンバー管理」のVARS_NAMEが0のレコードのMAX_COL_SEQを
//          「多段変数最大繰返数管理」に同期させる
//
//  【その他】
//      多段変数メンバー管理    : B_ANSTWR_NESTED_MEM_VARS
//      多段変数配列組合せ管理 : B_ANSTWR_NESTEDMEM_COL_CMB
//      多段変数最大繰返数管理 : B_ANSTWR_MAX_MEMBER_COL
//
//////////////////////////////////////////////////////////////////////

/**
 * 多次元変数メンバー管理との同期処理
 */
function syncDefaultMaxMemberCol() {

    global $log_level;
    global $db_access_user_id;

    $ansibleRole_maxMemberCol_tblName = "B_ANSTWR_MAX_MEMBER_COL";
    $ansibleRole_maxMemberCol_pkName = "MAX_MEMBER_COL_ID";
    $ansibleRole_maxMemberCol_columns = array(
            // tbl共通・必須
            'JOURNAL_SEQ_NO' => "",
            'JOURNAL_REG_DATETIME' => "",
            'JOURNAL_ACTION_CLASS' => "",
            'DISP_SEQ' => "",
            'NOTE' => "",
            'DISUSE_FLAG' => "",
            'LAST_UPDATE_TIMESTAMP' => "",
            'LAST_UPDATE_USER' => "",

            // tbl個別
            'MAX_MEMBER_COL_ID' => "",
            'VARS_ID' => "",
            'NESTED_MEM_VARS_ID' => "",
            'MAX_COL_SEQ' => "",
        );

        ////////////////////////////////
        // 対象変数絞込み           //
        ////////////////////////////////

        // 変数名一覧TBLから自身が更新対象であるレコードを取得
        $varsMaster = array();
        $sql = 
              "SELECT \n"
            . "    * \n"
            . "FROM B_ANSTWR_VARS \n"
            . ";"; // 廃止レコード含む
        if(dbaccessSelect($sql, null, $varsMaster) === false) {
            return false;
        }
        $varsNameIdAlive = array();
        $varsNameIdAll = array();
        foreach($varsMaster as $master) {
            if($master['LAST_UPDATE_USER'] != $db_access_user_id) {
                // DEBUGメッセージ
                if($log_level === 'DEBUG') {
                    $vars_name = $master['VARS_NAME'];
                    $FREE_LOG = "This vars_name($vars_name) was last updated by other process.";
                    LocalLogPrint(basename(__FILE__),__LINE__,$FREE_LOG);
                }
                continue;
            }

            if($master['DISUSE_FLAG'] == "0") {
                $varsNameIdAlive[] = $master['VARS_ID'];
            }

            $varsNameIdAll[] = $master['VARS_ID'];
        }

        ////////////////////////////////
        // レコード準備       //
        ////////////////////////////////

        // 多次元変数メンバー管理TBLから繰返しを示す VARS_NAME = '0' の要素を取得
        $nominateMaxMemberCol = array();
        if(count($varsNameIdAlive) > 0) {
            $sql = 
                  "SELECT \n"
                . "    VARS_ID \n"
                . "   ,NESTED_MEM_VARS_ID \n"
                . "   ,MAX_COL_SEQ \n"
                . "FROM B_ANSTWR_NESTED_MEM_VARS \n"
                . "WHERE VARS_ID IN (" . implode(", ", $varsNameIdAlive) . ") \n" //   最終更新者が自分であり、廃止ではない変数だけ
                . "AND MEMBER_NAME = '0' \n"
                . "AND DISUSE_FLAG = '0' \n" // 廃止レコード含まない
                . ";";
            if(dbaccessSelect($sql, null, $nominateMaxMemberCol) === false) {
                return false;
            }
        }

        // 既存の多次元変数最大繰返数管理TBLのレコードを取得
        $currentMaxMemberCol = array();
        if(count($varsNameIdAll) > 0) {
            $sql = 
                  "SELECT \n"
                . "    * \n"
                . "FROM B_ANSTWR_MAX_MEMBER_COL \n"
                . "WHERE VARS_ID IN (" . implode(", ", $varsNameIdAll) . ") \n" //     最終更新者が自分である変数だけ（廃止含む）
                . ";"; // 廃止レコード含む
            if(dbaccessSelect($sql, null, $currentMaxMemberCol) === false) {
                return false;
            }
        }

        ////////////////////////////////
        // DB操作（新規／更新／廃止） //
        ////////////////////////////////

        // 新規 (nominateに有り、currentに無し)
        $insertRecords = specificArrayDiff_maxMemberCol($nominateMaxMemberCol, $currentMaxMemberCol);
        if(count($insertRecords) > 0 &&
            dbaccessInsert($ansibleRole_maxMemberCol_tblName, $ansibleRole_maxMemberCol_pkName, $ansibleRole_maxMemberCol_columns, $insertRecords) === false) {
            // 念のためロールバック
            rollbackTransaction();
            return false;
        }

        // 復活 (nominateに有り、currentで "廃止")
        $currentMaxMemberCol_disuse = extractRecords($currentMaxMemberCol, array('DISUSE_FLAG' => '1'));
        $updateRecordsRevive = specificArrayMatch_maxMemberCol($currentMaxMemberCol_disuse, $nominateMaxMemberCol);
        if(count($updateRecordsRevive) > 0 &&
            dbaccessUpdateRevive($ansibleRole_maxMemberCol_tblName, $ansibleRole_maxMemberCol_pkName, $ansibleRole_maxMemberCol_columns, $updateRecordsRevive) === false) {
            // 念のためロールバック
            rollbackTransaction();
            return false;
        }

        // 廃止 (currentに有り、nominateに無し)
        $currentMaxMemberCol_alive = extractRecords($currentMaxMemberCol, array('DISUSE_FLAG' => '0'));
        $updateRecordsDisuse = specificArrayDiff_maxMemberCol($currentMaxMemberCol_alive, $nominateMaxMemberCol);
        if(count($updateRecordsDisuse) > 0 &&
            dbaccessUpdateDisuse($ansibleRole_maxMemberCol_tblName, $ansibleRole_maxMemberCol_pkName, $ansibleRole_maxMemberCol_columns, $updateRecordsDisuse) === false) {
            // 念のためロールバック
            rollbackTransaction();
            return false;
        }


    return true;

} //----ここまで多次元変数メンバー管理との同期処理

/**
 * 配列差分レコード取得
 */
function specificArrayDiff_maxMemberCol($sourceArray, $targetArray) {

    $result = array();

    foreach($sourceArray as $sourceRecord) {
        if(!isContained_maxMemberCol($sourceRecord, $targetArray)) {
            $result[] = $sourceRecord;
        }
    }

    return $result;
}

/**
 * 配列から必要なレコードのみを抽出する
 * 本機能限定のあまり使い回せない関数
 */
function extractRecords($sourceArray, $conditionArray) {

    $workArray = $sourceArray;

    foreach($conditionArray as $key => $value) {
        $tmpArray = array();

        foreach($workArray as $workRecord) {
            if($workRecord[$key] == $value) {
                $tmpArray[] = $workRecord;
            }
        }

        $workArray = $tmpArray;
    }
    return $workArray;
}

/**
 * 配列一致レコード取得
 */
function specificArrayMatch_maxMemberCol($sourceArray, $targetArray) {

    $result = array();

    foreach($sourceArray as $sourceRecord) {
        if(isContained_maxMemberCol($sourceRecord, $targetArray)) {
            $result[] = $sourceRecord;
        }
    }

    return $result;
}

/**
 * 多段変数最大繰返数レコードの比較用
 * 'VARS_ID' と 'NESTED_MEM_VARS_ID' の2要素をキーとする
 */
function isContained_maxMemberCol($source, $targetArray) {

    foreach($targetArray as $targetRecord) {
        if($source['VARS_ID']            == $targetRecord['VARS_ID'] &&
           $source['NESTED_MEM_VARS_ID'] == $targetRecord['NESTED_MEM_VARS_ID']) {
            return true;
        }
    }
    return false;
}
?>
