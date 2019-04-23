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
//  【概要】
//      多段変数配列組合せ管理 ビュー定義クラス
//
//////////////////////////////////////////////////////////////////////

////////////////////////////////
// ルートディレクトリを取得
////////////////////////////////
if(empty($root_dir_path)) {
    $root_dir_temp = array();
    $root_dir_temp = explode("ita-root", dirname(__FILE__));
    $root_dir_path = $root_dir_temp[0] . "ita-root";
}

require_once($root_dir_path . "/libs/backyardlibs/ansibletower_driver/table_definition/TableBaseDefinition.php");

class DAnstwrNestedmemColCmb extends TableBaseDefinition {

    public static function getTableName() {
        return "D_ANSTWR_NESTEDMEM_COL_CMB";
    }

    protected static $specificColumns = array(
            "NESTEDMEM_COL_CMB_ID"          => "", 
            "COL_COMBINATION_MEMBER_ALIAS"  => "", 
            "COL_SEQ_VALUE"                 => "", 
            "NESTED_MEM_VARS_ID"            => "", 
            "VARS_ID"                       => "", 
            "PARENT_KEY_ID"                 => "", 
            "SELF_KEY_ID"                   => "", 
            "MEMBER_NAME"                   => "", 
            "NESTED_LEVEL"                  => "", 
            "ASSIGN_SEQ_NEED"               => "", 
            "COL_SEQ_NEED"                  => "", 
            "MEMBER_DISP"                   => "", 
            "MAX_COL_SEQ"                   => "", 
            "NESTED_MEMBER_PATH"            => "", 
            "NESTED_MEMBER_PATH_ALIAS"      => "", 
        );

    public static function getRowDiffKeyColumns() {
        throw new BadMethodCallException("Not implemented.");
    }

    public static function getPKColumnName() {
        return "NESTEDMEM_COL_CMB_ID";
    }

}

?>
