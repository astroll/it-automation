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
//      多段変数最大繰返数メニュー反映ファイル 本体
//          「多段変数メンバー管理」のMEMBER_DISPが1のレコードを「多段変数配列組合せ管理」に入れる。
//          その際、「多段変数最大繰返数管理」にレコードがある変数については、MAX_COL_SEQの数だけ膨らませる。
//
//  【その他】
//      多段変数メンバー管理    : B_ANSTWR_NESTED_MEM_VARS
//      多段変数配列組合せ管理 : B_ANSTWR_NESTEDMEM_COL_CMB
//      多段変数最大繰返数管理 : B_ANSTWR_MAX_MEMBER_COL
//
//////////////////////////////////////////////////////////////////////
/**
 * 多段変数最大繰返数メニュー反映ファイル 本体
 */
function expandNestedVariables() {

    global $logger;
    global $log_level;
    global $db_access_user_id;

    $ansibleRole_memberColComb_tblName = "B_ANSTWR_NESTEDMEM_COL_CMB";
    $ansibleRole_memberColComb_pkName = "NESTEDMEM_COL_CMB_ID";
    $ansibleRole_memberColComb_columns = array(
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
            'NESTEDMEM_COL_CMB_ID' => "",
            'VARS_ID' => "",
            'NESTED_MEM_VARS_ID' => "",
            'COL_COMBINATION_MEMBER_ALIAS' => "",
            'COL_SEQ_VALUE' => ""
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
                $vars_name = $master['VARS_NAME'];
                $logger->debug("This vars_name($vars_name) was last updated by other process.");
                continue;
            }

            if($master['DISUSE_FLAG'] == "0") {
                $varsNameIdAlive[] = $master['VARS_ID'];
            }
            $varsNameIdAll[] = $master['VARS_ID'];
        }

        ////////////////////////////////
        // 多段変数最大繰返数メニュー反映用レコード準備     //
        ////////////////////////////////

        // 関連TBLから全レコードを取得
        // 多次元変数メンバー管理TBL
        $arrayMember = array();
        // 多次元変数最大繰返数管理TBL
        $maxMemberCol = array();

        if(count($varsNameIdAlive) > 0) {
            $sql = 
                  "SELECT \n"
                . "    VARS_ID \n"
                . "   ,NESTED_MEM_VARS_ID \n"
                . "   ,MEMBER_NAME \n"
                . "   ,NESTED_LEVEL \n"
                . "   ,MEMBER_DISP \n"
                . "   ,PARENT_KEY_ID \n"
                . "   ,SELF_KEY_ID \n"
                . "   ,DISUSE_FLAG \n"
                . "FROM B_ANSTWR_NESTED_MEM_VARS \n"
                . "WHERE VARS_ID IN (" . implode(", ", $varsNameIdAlive) . ") \n" // 最終更新者が自分である変数だけ
                . "AND DISUSE_FLAG = '0' \n" // 廃止レコード含まない
                . ";";
            if(dbaccessSelect($sql, null, $arrayMember) === false) {
                return false;
            }

            $sql = 
                  "SELECT \n"
                . "    VARS_ID \n"
                . "   ,NESTED_MEM_VARS_ID \n"
                . "   ,MAX_COL_SEQ \n"
                . "FROM B_ANSTWR_MAX_MEMBER_COL \n"
                . "WHERE VARS_ID IN (" . implode(", ", $varsNameIdAlive) . ") \n" // 最終更新者が自分である変数だけ
                . "AND DISUSE_FLAG = '0' \n" // 廃止レコード含まない
                . ";";
            if(dbaccessSelect($sql, null, $maxMemberCol) === false) {
                return false;
            }
        }

        // トレースメッセージ
        $logger->trace("count(nestedMemVars):" . count($arrayMember));
        $logger->trace("count(maxMemberCol):" . count($maxMemberCol));

        // 直後のソートで使う一時関数
        function arrayMemberSort($a, $b) {
            if($a['NESTED_LEVEL'] == $b['NESTED_LEVEL']) {
                return 0;
            }
            return ($a['NESTED_LEVEL'] < $b['NESTED_LEVEL']) ? -1 : 1;
        }
        usort($arrayMember, "arrayMemberSort");

        $elementArray = array();
        $containsIndex = array(); // key="elementArray['VARS_NAME_ID'] - elementArray['VARS_KEY_ID']", value="element(参照渡し)"
        foreach($arrayMember as $arrayMemberRecord) {

            // トレースメッセージ
            if($log_level === 'DEBUG') {
                LocalLogPrint(basename(__FILE__), __LINE__, '$arrayMemberRecord: ' . var_export($arrayMemberRecord, true));
            }

            $element = null;
            // 多段変数最大繰返数メニュー反映要素かどうか
            if($arrayMemberRecord['MEMBER_NAME'] == "0") {
                foreach($maxMemberCol as $maxMemberColRecord) {
                    if($arrayMemberRecord['VARS_ID'] == $maxMemberColRecord['VARS_ID'] &&
                        $arrayMemberRecord['NESTED_MEM_VARS_ID'] == $maxMemberColRecord['NESTED_MEM_VARS_ID']) {

                        $element = new ExpandableElement($arrayMemberRecord, $maxMemberColRecord['MAX_COL_SEQ']);
                        break;
                    }
                    // 最大繰返数管理TBLに存在しない＝メンバー変数で廃止されている（全レコードの構造を形成するためだけに取得されている）
                    $element = new ExpandableElement($arrayMemberRecord, 0);
                }
            } else {
                $element = new NonExpandableElement($arrayMemberRecord);
            }

            // トレースメッセージ
            if($log_level === 'DEBUG') {
                LocalLogPrint(basename(__FILE__), __LINE__, '$element: ' . var_export($element, true));
            }

            // 既に親が生成されている場合は紐付け
            $parentKey = $arrayMemberRecord['VARS_ID'] . "-" . $arrayMemberRecord['PARENT_KEY_ID'];
            if(array_key_exists($parentKey, $containsIndex)) {
                $higherElement = &$containsIndex[$parentKey]; // 参照渡し
                $higherElement->setLowerLevelElement($element);
                unset($higherElement);
            } else {
                $elementArray[] = &$element;
            }
            $parentKey = $arrayMemberRecord['VARS_ID'] . "-" . $arrayMemberRecord['SELF_KEY_ID'];
            $containsIndex[$parentKey] = &$element;
            unset($element);
        }

        // トレースメッセージ
        if($log_level === 'DEBUG') {
            LocalLogPrint(basename(__FILE__), __LINE__, '$elementArray: ' . var_export($elementArray, true));
        }

        $nominateMemberColComb = array();
        foreach($elementArray as $element) {
            $memberColCombRecordArray = $element->build();
            if(!empty($memberColCombRecordArray)) {
                $nominateMemberColComb = array_merge($nominateMemberColComb, $memberColCombRecordArray);
            }
        }
        foreach($nominateMemberColComb as &$record) {
            if(empty($record['COL_SEQ_VALUE'])) {
                $record['COL_SEQ_VALUE'] = "-";
            }
        }
        unset($record);

        // トレースメッセージ
        if($log_level === 'DEBUG') {
            $debugLog = '$nominateMemberColComb: ' . var_export($nominateMemberColComb, true);
            LocalLogPrint(basename(__FILE__), __LINE__, $debugLog);
        }

        // 既存の多次元変数配列組合せ管理のレコードを取得
        $currentMemberColComb = array();

        if(count($varsNameIdAll) > 0) {
            $sql = 
                  "SELECT \n"
                . "    * \n"
                . "FROM B_ANSTWR_NESTEDMEM_COL_CMB \n"
                . "WHERE VARS_ID IN (" . implode(", ", $varsNameIdAll) . ") \n" // 最終更新者が自分である変数だけ
                . ";"; // 廃止レコード含む
            if(dbaccessSelect($sql, null, $currentMemberColComb) === false) {
                return false;
            }
        }

        // トレースメッセージ
        if($log_level === 'DEBUG') {
            LocalLogPrint(basename(__FILE__), __LINE__, 'count($currentMemberColComb): ' . count($currentMemberColComb));
        }

        ////////////////////////////////
        // DB操作（新規／更新／廃止）   //
        ////////////////////////////////

        // 新規 (nominateに有り、currentに無し)
        $insertRecords = specificArrayDiff_memberColComb($nominateMemberColComb, $currentMemberColComb);
        if($log_level === "DEBUG") {
            LocalLogPrint(basename(__FILE__), __LINE__, 'count($insertRecords): ' . count($insertRecords));
        }
        if(count($insertRecords) > 0 &&
            dbaccessInsert($ansibleRole_memberColComb_tblName, $ansibleRole_memberColComb_pkName, $ansibleRole_memberColComb_columns, $insertRecords) === false) {
            // 念のためロールバック
            rollbackTransaction();
            return false;
        }

        // 復活 (nominateに有り、currentで "廃止")
        $currentMemberColComb_disuse = extractRecords($currentMemberColComb, array('DISUSE_FLAG' => '1'));
        $updateRecordsRevive = specificArrayMatch_memberColComb($currentMemberColComb_disuse, $nominateMemberColComb);
        if($log_level === "DEBUG") {
            LocalLogPrint(basename(__FILE__), __LINE__, 'count($updateRecordsRevive): ' . count($updateRecordsRevive));
        }
        if(count($updateRecordsRevive) > 0 &&
            dbaccessUpdateRevive($ansibleRole_memberColComb_tblName, $ansibleRole_memberColComb_pkName, $ansibleRole_memberColComb_columns, $updateRecordsRevive) === false) {
            // 念のためロールバック
            rollbackTransaction();
            return false;
        }

        // 廃止 (currentに有り、nominateに無し)
        $currentMemberColComb_alive = extractRecords($currentMemberColComb, array('DISUSE_FLAG' => '0'));
        $updateRecordsDisuse = specificArrayDiff_memberColComb($currentMemberColComb_alive, $nominateMemberColComb);
        if($log_level === "DEBUG") {
            LocalLogPrint(basename(__FILE__), __LINE__, 'count($updateRecordsDisuse): ' . count($updateRecordsDisuse));
        }
        if(count($updateRecordsDisuse) > 0 &&
            dbaccessUpdateDisuse($ansibleRole_memberColComb_tblName, $ansibleRole_memberColComb_pkName, $ansibleRole_memberColComb_columns, $updateRecordsDisuse) === false) {
            // 念のためロールバック
            rollbackTransaction();
            return false;
        }

    return true;
} //----ここまで多段変数最大繰返数メニュー反映ファイル

/**
 * 多次元変数メンバー管理から生成する要素オブジェクト
 * →buildした結果、多次元変数配列組合せ管理のレコード(候補)を吐き出す
 *   ※吐き出したレコード一覧と実際にINSERTするかを比較する処理は別
 */
abstract class MemberColCombElement {

    protected $lowerLevelElement;
    protected $arrayMemberValues;
    protected $baseColSeqValue;
    protected $baseColCombMemberAlias;
    protected $memberColCombRecord;

    function __construct($arrayMemberValues) {
        $this->lowerLevelElement = array();
        $this->arrayMemberValues = $arrayMemberValues;
        $this->baseColSeqValue = "";
        $this->baseColCombMemberAlias = "";
    }

    function setLowerLevelElement(MemberColCombElement &$lower) {
        $this->lowerLevelElement[] = $lower;
    }

    function hasLowerLevelElement() {
        return count($this->lowerLevelElement) > 0;
    }

    function setColSeqValue($colSeqValue) {
        $this->baseColSeqValue = $colSeqValue;
    }

    function setColCombMemberAlias($alias) {
        $this->baseColCombMemberAlias = $alias;
    }

    function getLowerRecords() {
        $memberColCombRecordArray = array();
        foreach($this->lowerLevelElement as $lower) {
            $lower->setColSeqValue($this->memberColCombRecord['COL_SEQ_VALUE']);
            $lower->setColCombMemberAlias($this->memberColCombRecord['COL_COMBINATION_MEMBER_ALIAS']);
            $records = $lower->build();
            if(!empty($records)) {
                $memberColCombRecordArray = array_merge($memberColCombRecordArray, $records);
            }
        }
        return $memberColCombRecordArray;
    }

    protected static function createMemberColCombRecord($arrayMemberValues) {

        $columns = [
            'NESTEDMEM_COL_CMB_ID',
            'VARS_ID',
            'NESTED_MEM_VARS_ID',
            'COL_COMBINATION_MEMBER_ALIAS',
            'COL_SEQ_VALUE'
            ];

        $result = array();

        foreach($columns as $column) {
            if(array_key_exists($column, $arrayMemberValues)) {
                $result[$column] = $arrayMemberValues[$column];
            } else {
                $result[$column] = null;
            }
        }

        return $result;
    }

    abstract function build();
}

/**
 * 膨らます要素
 */
class ExpandableElement extends MemberColCombElement {

    private $expandTimes;

    function __construct($arrayMemberValues, $expandTimes) {
       parent::__construct($arrayMemberValues);
       $this->expandTimes = $expandTimes;
    }

    function build() {

        $memberColCombRecordArray = array();

        // 廃止フラグの立っているものは何も処理しない
        if($this->arrayMemberValues['DISUSE_FLAG'] == "1") {
            return $memberColCombRecordArray;
        }

        for($seq = 0; $seq < $this->expandTimes; $seq++) {

            $this->memberColCombRecord = MemberColCombElement::createMemberColCombRecord($this->arrayMemberValues);
            $this->memberColCombRecord['COL_SEQ_VALUE'] = $this->baseColSeqValue . sprintf('%03d', $seq);
            $this->memberColCombRecord['COL_COMBINATION_MEMBER_ALIAS'] = $this->baseColCombMemberAlias . '[' . $seq .']';

            if($this->arrayMemberValues['MEMBER_DISP'] == "1") {
                $memberColCombRecordArray[] = $this->memberColCombRecord;
            }
            if($this->hasLowerLevelElement()) {
                $memberColCombRecordArray = array_merge($memberColCombRecordArray, $this->getLowerRecords());
            }
        }

        return $memberColCombRecordArray;
    }
}

/**
 * 膨らまない要素
 */
class NonExpandableElement extends MemberColCombElement {

    function __construct($arrayMemberValues) {
       parent::__construct($arrayMemberValues);
    }

    function build() {

        $memberColCombRecordArray = array();

        // 廃止フラグの立っているものは何も処理しない
        if($this->arrayMemberValues['DISUSE_FLAG'] == "1") {
            return $memberColCombRecordArray;
        }

        $this->memberColCombRecord = MemberColCombElement::createMemberColCombRecord($this->arrayMemberValues);
        $this->memberColCombRecord['COL_SEQ_VALUE'] = $this->baseColSeqValue; // . "___";
        $dot = empty($this->baseColCombMemberAlias) ? '' : '.';
        $this->memberColCombRecord['COL_COMBINATION_MEMBER_ALIAS'] = $this->baseColCombMemberAlias . $dot
            . $this->arrayMemberValues['MEMBER_NAME'];

        if($this->arrayMemberValues['MEMBER_DISP'] == "1") {
            $memberColCombRecordArray[] = $this->memberColCombRecord;
        }
        if($this->hasLowerLevelElement()) {
            $memberColCombRecordArray = array_merge($memberColCombRecordArray, $this->getLowerRecords());
        }

        return $memberColCombRecordArray;
    }
}

/**
 * 配列差分レコード取得
 */
function specificArrayDiff_memberColComb($sourceArray, $targetArray) {

    $result = array();

    foreach($sourceArray as $sourceRecord) {
        if(!isContained_memberColComb($sourceRecord, $targetArray)) {
            $result[] = $sourceRecord;
        }
    }

    return $result;
}

/**
 * 配列一致レコード取得
 */
function specificArrayMatch_memberColComb($sourceArray, $targetArray) {

    $result = array();

    foreach($sourceArray as $sourceRecord) {
        if(isContained_memberColComb($sourceRecord, $targetArray)) {
            $result[] = $sourceRecord;
        }
    }

    return $result;
}

/**
 * 多段変数レコードの比較用
 * データすべてを比較対象とする
 * 'VARS_ID' と 'NESTED_MEM_VARS_ID' と 'COL_COMBINATION_MEMBER_ALIAS' と 'COL_SEQ_VALUE' の4要素
 */
function isContained_memberColComb($source, $targetArray) {

    foreach($targetArray as $targetRecord) {
        if($source['VARS_ID']                       == $targetRecord['VARS_ID'] &&
           $source['NESTED_MEM_VARS_ID']            == $targetRecord['NESTED_MEM_VARS_ID'] &&
           $source['COL_SEQ_VALUE']                 == $targetRecord['COL_SEQ_VALUE'] &&
           $source['COL_COMBINATION_MEMBER_ALIAS']  == $targetRecord['COL_COMBINATION_MEMBER_ALIAS']) {
            return true;
        }
    }
    return false;
}
?>
