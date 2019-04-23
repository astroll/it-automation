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
////////////////////////////////////////////////////////////////////////////////////
//
//  【処理概要】
//    ・AnsibleTower 変数解析ライブラリ
//
////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
// 処理内容
//   指定された配列の構造が一致しているか判定
//   左辺の内容が右辺に含まれているか
//   同一かどうかは左右入れ替えて確認する
// パラメータ
//   $in_arrrayLeft:       左辺の配列構造
//   $in_arrayRight:       右辺の配列構造
//   $in_diff_array:       一致していない場合の簡易エラー情報
//   
// 戻り値
//   true: 正常　false:異常
////////////////////////////////////////////////////////////////////////////////    
function nestedArrayDiff($in_arrrayLeft,$in_arrayRight,&$in_diff_array){
    $diff = false;
    if (is_array($in_arrrayLeft)){
        foreach($in_arrrayLeft as $key => $item){
            if (@is_array($in_arrayRight[$key]) === false){
                $in_diff_array[$key] = "key is not found";
                return false;
            }
            //配列なら再帰呼び出し
            if (is_array($item)){
                $ret = nestedArrayDiff($item,$in_arrayRight[$key],$in_diff_array);
                if ($ret === false){
                    return false;
                }
            }else{
                $in_diff_array[$key] = "item is not array";
                return false;
            }
        }
    }
    else{
        $in_diff_array["arrrayLeft"] = "arrrayLeft is not array";
        return false;
    }
    return true;
}

////////////////////////////////////////////////////////////////////////////////
// 処理内容
//   多次元変数で配列構造を含んでいる場合、各配列の定義が一致しているか判定
// パラメータ
//   $ina_parent_var_array:       多次元変数の構造
//   $in_error_code:              エラー時のエラーコード
//   $in_line:                    エラー時の行番号格納
//   
// 戻り値
//   true: 正常　false:異常
////////////////////////////////////////////////////////////////////////////////
function chkInnerArrayStructure($ina_parent_var_array,&$in_error_code,&$in_line){
    $diff_array = array();
    if( ! @is_array($ina_parent_var_array)){
        return true;
    }
    $is_key_array = is_assoc($ina_parent_var_array);
    if($is_key_array == -1){
        $in_error_code = "ITAANSTWRH-ERR-70087";
        $in_line       = __LINE__;
        return false;
    }
    $idx = 0;
    foreach($ina_parent_var_array as $var1 => $val1){
        if(is_numeric($var1)){
            if(is_array($val1)){
                if($idx != 0){
                    $diff_array = array();
                    $ret = nestedArrayDiff($ina_parent_var_array[0],   $ina_parent_var_array[$idx],$diff_array);
                    if($ret === false){
                        $in_error_code = "ITAANSTWRH-ERR-70089";
                        $in_line       = __LINE__;
                        return false;
                    }
                    $ret = nestedArrayDiff($ina_parent_var_array[$idx],$ina_parent_var_array[0],$diff_array);
                    if($ret === false){
                        $in_error_code = "ITAANSTWRH-ERR-70089";
                        $in_line       = __LINE__;
                        return false;
                    }
                }
            }
        }
        $ret = chkInnerArrayStructure($val1,$in_error_code,$in_line);
        if($ret === false){
            return false;
        }
        $idx++;
    }
    return true;
}

////////////////////////////////////////////////////////////////////////////////
// 処理内容
//   多次元変数の特定階層が配列か判定する。
// パラメータ
//   $in_array:                   多次元変数の特定階層
//   
// 戻り値
//   true: 正常　false:異常
////////////////////////////////////////////////////////////////////////////////    
function is_assoc($in_array) {
    $key_int  = false;
    $key_char = false;
    if (!is_array($in_array)) {
        return -1;
    }
    $keys = array_keys($in_array);
    foreach ($keys as $i => $value) {
        if (!is_int($value)) {
            $key_char = true;
        } else {
            $key_int = true;
        }
    }
    if(($key_char === true) && ($key_int === true)) {
        return -1;
    }
    if($key_char === true) {
        return "C";
    }
    return "I";
}

?>
