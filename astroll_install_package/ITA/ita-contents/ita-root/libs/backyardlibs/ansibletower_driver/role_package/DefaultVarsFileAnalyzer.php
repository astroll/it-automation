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
//    ・AnsibleTower用 defalte変数ファイルに登録されている変数を解析
//
/////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////
// ルートディレクトリを取得
////////////////////////////////
if (empty($root_dir_path)) {
    $root_dir_temp = array();
    $root_dir_temp = explode("ita-root", dirname(__FILE__));
    $root_dir_path = $root_dir_temp[0] . "ita-root";
}

require_once ($root_dir_path . "/libs/backyardlibs/ansibletower_driver/role_package/varsStructureAnalyzeLib.php");

class DefaultVarsFileAnalyzer{

    // 変数定義を判定する正規表記  /^VAR_(\S+):/ => /^VAR_(\S+)(\s*):/
    const LC_VARNAME_MATCHING                = "/^VAR_(\S+)(\s*):/";
    const LC_USER_VARNAME_MATCHING           = "/^[a-zA-Z0-9_]*(\s*):/";

    // 変数属性(ロジック内) ※DBの値と同期させること
    const LC_VARS_ATTR_STD                    = "1";   // 一般変数
    const LC_VARS_ATTR_LIST                   = "2";   // 複数具体値変数
    const LC_VARS_ATTR_STRUCT                 = "3";   // 多段変数

    // 変数タイプ ※ロジックのみ
    const LC_VAR_TYPE_ITA                    = "0";   // ITA (VAR_)
    const LC_VAR_TYPE_USER                   = "1";   // ユーザー
    const LC_VAR_TYPE_USER_ITA               = "2";   // ユーザー読替(LCA_)

    protected   $lv_objMTS;

    ////////////////////////////////////////////////////////////////////////////////
    // F1001
    // 処理内容
    //   コンストラクタ
    //
    // パラメータ
    //   なし
    //
    // 戻り値
    //   なし
    //
    ////////////////////////////////////////////////////////////////////////////////
    function __construct(&$in_objMTS){
        $this->lv_objMTS = $in_objMTS;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F1008
    // 処理内容
    //   defalte変数ファイルを親変数毎に分解
    //
    // パラメータ
    //   $in_string:              defalte変数ファイルの内容
    //   $ina_parent_vars_list:   defalte変数ファイルの親変数毎に分解配列
    //                            []['LINE']                   親変数の行番号
    //                              ['VAR_NAME']               変数名
    //                              ['VAR_TYPE']               変数タイプ
    //                                                           self::LC_VAR_TYPE_ITA / self::LC_VAR_TYPE_USER / self::LC_VAR_TYPE_USER_ITA
    //                              ['VAR_STYLE']              変数型
    //                                                           self::LC_VAR_STYLE_STD etc
    //                              ['DATA'][][LINE]           行番号
    //                                        ['EDIT_SKIP']      編集により不要となった行(true)/default(false)
    //                                        [MARK_POS]       先頭からの - の位置　-がない場合は-1
    //                                        [VAR_POS]        先頭からの変数文字列の位置
    //                                        [VAL_POS]        先頭からの具体値文字列の位置
    //                                        [DATA]           行データ
    //                                        [VAR_NAME]       変数名
    //                                        [VAR_VAL]        具体値
    //                                        [VAR_DEF]        変数定義有無(true)/(false)
    //                                        [VAL_DEF]        具体値定義有無(true)/(false)
    //                                        [ARRAY_VAR_DEF]  配列変数定義有無(true)/(false)
    //                                                         - { }
    //                                        [ZERO_LIST_DEF]  複数具体値初期値定義(true)/(false)
    //                                                         []
    //                                        [ZERO_ARRAY_DEF] 配列変数初期値定義(true)/(false)
    //                                                         {}
    //   $in_role_name:           ロール名
    //   $in_file_name:           defalte変数ファイル名
    //   $ina_ITA2User_var_list   読替表の変数リスト　ITA変数=>ユーザ変数
    //   $ina_User2ITA_var_list   読替表の変数リスト　ユーザ変数=>ITA変数
    //   $in_errmsg:              エラー時のメッセージ格納
    //
    // 戻り値
    //   true:   正常
    //   false:  異常
    ////////////////////////////////////////////////////////////////////////////////
    function FirstAnalysis($in_string,
                          &$ina_parent_vars_list,
                           $in_role_name,
                           $in_file_name, 
                           $ina_ITA2User_var_list,
                           $ina_User2ITA_var_list,
                          &$in_errmsg,
                           $in_msg_role_pkg_name){
        $ina_parent_vars_list = array();
        // ファイル名
        $in_f_name = __FILE__;
        // 行番号
        $line = 0;
        // 変数定義のインデント数
        $var_pos = -1;
        // 変数解析結果退避
        $parentVarInfo = array();
        // 入力データを行単位に分解
        $arry_list = explode("\n",$in_string);
        foreach($arry_list as $in_string){
            // 行番号
            $line = $line + 1;
            // コメント行解析
            if(mb_strpos($in_string,"#",0,"UTF-8") === 0){
                continue;
            }
            $wstr = $in_string;
            // コメント( #)マーク以降の文字列を削除する。
            // #の前の文字がスペースの場合にコメントとして扱う
            $wspstr = explode(" #",$wstr);
            $strRemainString = $wspstr[0];
            if( is_string($strRemainString)===true ){
                // 空行をスキップ
                if(strlen(trim($strRemainString))===0){
                    continue;
                }

                // ---は記載可能にする。
                $ret = preg_match('/^---(\s*)$/',$strRemainString,$matchi,PREG_OFFSET_CAPTURE);
                if ($ret == 1){
                    continue;
                }

                $error_code = "";
                // 読込んだ行の変数のインデントを取得
                $ret = $this->VarPosAnalysis($strRemainString,$wk_var_pos,$wk_mark_pos,$wk_val_pos,
                                      $wk_var_name,
                                      $wk_var_val,
                                      $wk_var_def,
                                      $wk_var_val_def,
                                      $wk_array_var_def,
                                      $wk_zero_list_def,
                                      $wk_zero_array_def,
                                      $error_code);
                if($ret === false){
                    if($error_code == ""){
                        $error_code = "ITAANSTWRH-ERR-70044";
                    }

                    $in_errmsg = $this->lv_objMTS->getSomeMessage($error_code,
                                    array($in_msg_role_pkg_name, $in_role_name, basename($in_file_name), $line))
                                . "\n(" . __FILE__ . ":" . __LINE__ . ")";
                    return false;
                }

                // 読込んだ行の情報を退避
                $info = array();
                $info['LINE']           = $line;
                $info['EDIT_SKIP']      = false;
                $info['MARK_POS']       = $wk_mark_pos;
                $info['VAR_POS']        = $wk_var_pos;
                $info['VAL_POS']        = $wk_val_pos;
                $info['DATA']           = $strRemainString;
                $info['VAR_NAME']       = $wk_var_name;
                $info['VAR_VAL']        = $wk_var_val;
                $info['VAR_DEF']        = $wk_var_def;
                $info['VAL_DEF']        = $wk_var_val_def;
                $info['ARRAY_VAR_DEF']  = $wk_array_var_def;
                $info['ZERO_LIST_DEF']  = $wk_zero_list_def;
                $info['ZERO_ARRAY_DEF'] = $wk_zero_array_def;

                // 最初の変数定義の判定
                if($var_pos == -1){
                    // 変数解析結果初期化
                    $parentVarInfo = array();
                    // 先頭からの文字数を退避
                    $var_pos = $wk_var_pos;

                    // 変数タイプと変数名を取得
                    $ret = $this->ParentVarAnalysis($strRemainString,$parentVarInfo,
                                                    $ina_ITA2User_var_list,
                                                    $ina_User2ITA_var_list,
                                                    $line);
                    if($ret === false){
                        $in_errmsg = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70044",
                                    array($in_msg_role_pkg_name, $in_role_name, basename($in_file_name), $line))
                                . "\n(" . __FILE__ . ":" . __LINE__ . ")";
                        return false;
                    }
                }
                // インデント不正の判定
                //  xxxx: xxxx
                // xxxx: xxx
                else if(($var_pos > $wk_var_pos) && ($wk_var_pos != -1)){
                    $in_errmsg = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70044",
                                    array($in_msg_role_pkg_name, $in_role_name, basename($in_file_name), $line))
                                . "\n(" . __FILE__ . ":" . __LINE__ . ")";
                    return false;
                }
                // 別変数定義の判定
                else if($var_pos == $wk_var_pos){
                    // 変数解析結果退避
                    array_push($ina_parent_vars_list, $parentVarInfo);
                    // 変数解析結果初期化
                    $parentVarInfo = array();

                    // 変数タイプと変数名を取得
                    $ret = $this->ParentVarAnalysis($strRemainString,$parentVarInfo,
                                                    $ina_ITA2User_var_list,
                                                    $ina_User2ITA_var_list,
                                                    $line);
                    if($ret === false){
                        $in_errmsg = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70044",
                                    array($in_msg_role_pkg_name, $in_role_name, basename($in_file_name), $line))
                                . "\n(" . __FILE__ . ":" . __LINE__ . ")";
                        return false;
                    }
                }
                // 変数定義を退避
                array_push($parentVarInfo['DATA'],$info);
            }
        }
        // 最終定義の変数の情報を登録
        if(count($parentVarInfo) != 0){
            // 変数解析結果退避
            array_push($ina_parent_vars_list, $parentVarInfo);
        }
        return true;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F1009
    // 処理内容
    //   ITA変数かユーザー変数かを判定
    //
    // パラメータ
    //   $in_string:              変数定義文字列
    //   $in_var_name:            定義されている変数名
    //   $in_match:               正規表記結果
    //   $ina_ITA2User_var_list:  読替表の変数リスト　ITA変数=>ユーザ変数
    //   $ina_User2ITA_var_list:  読替表の変数リスト　ユーザ変数=>ITA変数
    //
    // 戻り値
    //   ITA変数:    self::LC_VAR_TYPE_ITA
    //   USER変数:   self::LC_VAR_TYPE_USER
    //   読替変数:   self::LC_VAR_TYPE_USER_ITA
    //   false:      異常
    ////////////////////////////////////////////////////////////////////////////////
    function VarTypeAnalysis($in_string,&$in_var_name,&$in_match,
                             $ina_ITA2User_var_list,
                             $ina_User2ITA_var_list){
        $in_var_name = "";
        // ITA変数かユーザー変数かを判定
        // ITA変数か判定　　　　 /^VAR_(\S+)(\s*):/
        $ret = preg_match_all(self::LC_VARNAME_MATCHING,trim($in_string),$in_match);
        if($ret == 1){
            // 変数名退避
            $in_var_name = preg_replace("/(\s*):(\s*)$/","",$in_match[0][0]); 

            // ITA変数
            return self::LC_VAR_TYPE_ITA;
        }
        else{
            // ユーザー変数か判定　　/^[a-zA-Z0-9_]*(\s*):/
            $ret = preg_match_all(self::LC_USER_VARNAME_MATCHING,trim($in_string),$in_match);
            if($ret == 1){
                // 変数名退避
                $in_var_name = preg_replace("/(\s*):(\s*)$/","",$in_match[0][0]); 

                // 読替表にある変数はITA変数として扱う
                if(@count($ina_User2ITA_var_list[$in_var_name]) != 0){
                    // 読替変数
                    return self::LC_VAR_TYPE_USER_ITA;
                }
                else{
                    // USER変数
                    return self::LC_VAR_TYPE_USER;
                }
            }
        }
        return false;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F1010
    // 処理内容
    //   行先頭から-までの文字数と変数名までの文字数を取得
    //
    // パラメータ
    //   $in_string:              変数定義文字列
    //   $in_var_pos:             行先頭から変数名までの文字数
    //                            -がない場合は -1 を返す。
    //   $in_mark_pos:            行先頭から-までの文字数
    //                            -がない場合は -1 を返す。
    //   $in_val_pos:             先頭からの具体値文字列の位置
    //                            -がない場合は -1 を返す。
    //   $in_var_name:            変数名
    //   $in_var_val:             具体値
    //   $in_var_def:             変数定義の有(true)/無(false)
    //   $in_var_val_def:          具体値定義の有(true)/無(false)
    //   $in_array_var_def:       配列変数定義有無(true)/(false)      - {}
    //   $in_zero_list_def:       複数具体値初期値定義(true)/(false)  xxx: []
    //   $in_zero_array_def:      配列変数初期値定義(true)/(false)    xxx: {}
    //                            現在未使用
    //   $in_error_code:          エラーコード
    // 戻り値
    //   false:      異常
    ////////////////////////////////////////////////////////////////////////////////
    function VarPosAnalysis( $in_string,&$in_var_pos,&$in_mark_pos,&$in_val_pos,
                            &$in_var_name,
                            &$in_var_val,
                            &$in_var_def,
                            &$in_var_val_def,
                            &$in_array_var_def,
                            &$in_zero_list_def,
                            &$in_zero_array_def,
                            &$in_error_code){
        $in_mark_pos       = -1;
        $in_var_pos        = -1;
        $in_val_pos        = -1;
        $in_var_name       = "";
        $in_var_val        = "";
        $in_var_def        = false;
        $in_var_val_def    = false;
        $in_array_var_def  = false;
        $in_zero_list_def  = false;
        $in_zero_array_def = false;
        $in_error_code     = "";

        // Spycが誤解析する文法をはじく
        // - のみの定義か判定
        $ret = preg_match('/^(\s*)-(\s+)$/',$in_string,$matchi,PREG_OFFSET_CAPTURE);
        if($ret == 1){
            // Spycが - のみの定義は誤解釈するので禁止
            $in_error_code = "ITAANSTWRH-ERR-70076"; 
            return false;
        }
        // - {} の定義か判定
        $ret = preg_match_all('/^(\s*)-(\s+){(\s*)}(\s*)$/',$in_string,$matchi);
        if($ret == 1){
            // Spycが - {} の定義は誤解釈するので禁止
            $in_error_code = "ITAANSTWRH-ERR-70080"; 
            return false;
        }
        // - {} の定義か判定
        $ret = preg_match_all('/^(\s*)-(\s+)\[(\s*)\](\s*)$/',$in_string,$matchi);
        if($ret == 1){
            // Spycが - {} の定義は誤解釈するので禁止
            $in_error_code = "ITAANSTWRH-ERR-70081"; 
            return false;
        }

        $ret = preg_match('/^(\s*)-(\s*)$/',$in_string,$matchi,PREG_OFFSET_CAPTURE);
        if($ret == 1){
            // Spycが - のみの定義は誤解釈するので禁止
            $in_error_code = "ITAANSTWRH-ERR-70076"; 
            return false;
        }
        // 配列変数 - { ... } の定義か判定
        $ret = preg_match('/^(\s*)-(\s*){/',$in_string,$haifun_matchi,PREG_OFFSET_CAPTURE);
        if($ret == 1){
            // -の後ろにスペースがないとエラー
            $ret = preg_match('/^(\s*)-(\s+){/',$in_string,$matchi,PREG_OFFSET_CAPTURE);
            if($ret == 0){
                return false;
            }
            // } で終わっていないとエラー
            $ret = preg_match('/}(\s*)$/',$in_string,$matchi,PREG_OFFSET_CAPTURE);
            if($ret == 0){
                return false;
            }
            $ret = preg_match('/^(\s*)-(\s+){(\s*)}(\s*)$/',$in_string,$matchi,PREG_OFFSET_CAPTURE);
            if($ret == 1){
                // Spycが - {} の定義は誤解釈するので禁止
                $in_error_code = "ITAANSTWRH-ERR-70080"; 
                return false;
            }
            $in_array_var_def = true;
            // 行先頭から-までの文字数取得
            $in_mark_pos = strpos($haifun_matchi[0][0],"-",0);
            // Spycが配列の具体値を省略すると(xxx: ,)誤解釈をするので禁止
            $ret = preg_match_all('/[a-zA-Z0-9_]*(\s*):(\s*),/',$in_string,$matchi);
            if($ret > 0){
                $in_error_code = "ITAANSTWRH-ERR-70077"; 
                return false;
            }
            $ret = preg_match_all('/[a-zA-Z0-9_]*(\s*):(\s*)}/',$in_string,$matchi);
            if($ret > 0){
                $in_error_code = "ITAANSTWRH-ERR-70077"; 
                return false;
            }
            $ret = preg_match_all('/[a-zA-Z0-9_]*(\s*):(\s*),(\s*)}/',$in_string,$matchi);
            if($ret > 0){
                $in_error_code = "ITAANSTWRH-ERR-70077"; 
                return false;
            }
            $ret = preg_match_all('/,(\s*)}(\s*)/',$in_string,$matchi);
            if($ret > 0){
                $in_error_code = "ITAANSTWRH-ERR-70079"; 
                return false;
            }
            return true;
        }
        $ret = preg_match('/^(\s*)-(\s*)/',$in_string,$haifun_matchi,PREG_OFFSET_CAPTURE);
        if($ret == 1){
            // 行先頭から-までの文字数取得
            $in_mark_pos = strpos($haifun_matchi[0][0],"-",0);
            // 変数定義か判定
            $ret = preg_match('/^(\s*)-(\s*)[a-zA-Z0-9_]*(\s*):/',$in_string,$var_matchi,PREG_OFFSET_CAPTURE);
            if($ret == 1){
                // : の後ろはスペースがなにいとダメ
                $ret = preg_match('/^(\s*)-(\s*)[a-zA-Z0-9_]*(\s*):(\S)/',$in_string,$matchi,PREG_OFFSET_CAPTURE);
                if($ret == 1){
                    return false;
                }
                // - の後ろにスペースがないとエラー
                $ret = preg_match('/^(\s*)-[a-zA-Z0-9_]*(\s*):/',$in_string,$matchi,PREG_OFFSET_CAPTURE);
                if($ret == 1){
                    return false;
                }

                // 行先頭から変数名までの文字数取得
                $in_var_pos  = strlen($haifun_matchi[0][0]);
                // 変数名を取り出す
                $in_var_name = preg_replace("/(\s*):(\s*)$/","",$var_matchi[0][0]);
                $in_var_name = preg_replace("/^(\s*)-(\s+)/","",$in_var_name);
                $in_var_def = true;
                // 具体値取り出し
                $in_var_val = preg_replace("/^(\s*)-(\s+)[a-zA-Z0-9_]*(\s*):(\s*)/","",$in_string);
                $in_var_val = trim($in_var_val);
                if(strlen($in_var_val) != 0){
                    // 具体値定義あり
                    $in_var_val_def = true;
                }
            }
            else{
                $ret = preg_match('/^(\s*)-(\s+)(\S)/',$in_string,$val_matchi,PREG_OFFSET_CAPTURE);
                if($ret == 1){
                    $in_var_val = preg_replace("/^(\s*)-(\s+)/","",$in_string);
                    $in_var_val = trim($in_var_val);
                    if(strlen($in_var_val) != 0){
                        // 具体値定義あり
                        $in_var_val_def = true;
                    }
                    // 具体値定義位置取得
                    $in_val_pos  = strlen($val_matchi[0][0]) - 1;
                }
                else{
                    return false;
                }
            }
        }
        else{
            $ret = preg_match('/^(\s*)[a-zA-Z0-9_]*(\s*):(\S)/',$in_string,$matchi,PREG_OFFSET_CAPTURE);
            if($ret == 1){
                return false;
            }
            $ret = preg_match('/^(\s*)[a-zA-Z0-9_]*(\s*):(\s*)/',$in_string,$var_matchi,PREG_OFFSET_CAPTURE);
            if($ret == 1){
                // 行先頭から変数名までの文字数取得
                $ret = preg_match('/(\S)/',$in_string,$matchi,PREG_OFFSET_CAPTURE);
                if($ret == 1){
                    $in_var_pos  = $matchi[0][1];
                }
                // 変数名を取り出す
                $in_var_name = preg_replace("/(\s*):(\s*)$/","",$var_matchi[0][0]);
                $in_var_name = trim($in_var_name);
                $in_var_def = true;
                // 具体値取り出し
                $in_var_val = preg_replace("/^(\s*)[a-zA-Z0-9_]*(\s*):(\s*)/","",$in_string);
                $in_var_val = trim($in_var_val);
                if(strlen($in_var_val) != 0){
                    // 具体値定義あり
                    $in_var_val_def = true;
                }
            }
            else{
                $ret = preg_match('/^(\s*)(\S)/',$in_string,$val_matchi,PREG_OFFSET_CAPTURE);
                if($ret == 1){
                    $in_var_val = trim($in_string);
                    if(strlen($in_var_val) != 0){
                        // 具体値定義あり
                        $in_var_val_def = true;
                    }
                    // 具体値定義位置取得
                    $in_val_pos  = strlen($val_matchi[0][0]) - 1;
                }
                else{
                    return false;
                }
            }
        }
        if($in_var_val_def === true){
            // 具体値が特殊か判定  []  {}
            $ret = preg_match('/^(\s*)\[(\s*)\](\s*)$/',$in_var_val,$matchi,PREG_OFFSET_CAPTURE);
            if($ret == 1){
                $in_zero_list_def = true;
            }
            $ret = preg_match('/^(\s*){(\s*)}(\s*)$/',$in_var_val,$matchi,PREG_OFFSET_CAPTURE);
            if($ret == 1){
                $in_error_code = "ITAANSTWRH-ERR-70078"; 
                return false;
            }
        }
        return true;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F1011
    // 処理内容
    //   行先頭から-までの文字数と変数名までの文字数を取得
    //
    // パラメータ
    //   $in_string:              変数定義文字列
    //   $ina_var_Info_list:      defalte変数ファイルの親変数毎に分解配列
    //                            ['LINE']                   親変数の行番号
    //                            ['VAR_NAME']               変数名
    //                            ['VAR_TYPE']               変数タイプ
    //                                                         self::LC_VAR_TYPE_ITA / self::LC_VAR_TYPE_USER / self::LC_VAR_TYPE_USER_ITA
    //                            ['DATA'][][LINE]           行番号
    //                                      [MARK_POS]       先頭からの - の位置　-がない場合は-1
    //                                      [VAR_POS]        先頭からの変数文字列の位置
    //                                      [VAL_POS]        先頭からの具体値文字列の位置
    //                                      [DATA]           行データ
    //                                      [VAR_NAME]       変数名
    //                                      [VAR_VAL]        具体値
    //                                      [VAR_DEF]        変数定義有無(true)/(false)
    //                                      [VAL_DEF]        具体値定義有無(true)/(false)
    //                                      [ARRAY_VAR_DEF]  配列変数定義有無(true)/(false)
    //                                                       - { }
    //                                      [ZERO_LIST_DEF]  複数具体値初期値定義(true)/(false)
    //                                                       []
    //                                      [ZERO_ARRAY_DEF] 配列変数初期値定義(true)/(false)
    //                                                       {}
    //                                                       現在未使用
    //   $ina_ITA2User_var_list   読替表の変数リスト　ITA変数=>ユーザ変数
    //   $ina_User2ITA_var_list   読替表の変数リスト　ユーザ変数=>ITA変数
    //   $in_line:                親変数の行番号
    //
    // 戻り値
    //   false:      異常
    ////////////////////////////////////////////////////////////////////////////////
    function ParentVarAnalysis($in_string,
                              &$ina_var_Info_list,
                               $ina_ITA2User_var_list,
                               $ina_User2ITA_var_list,
                               $in_line) {

        // 変数タイプと変数名を取得
        $match = array();
        $ret = $this->VarTypeAnalysis($in_string,
                                      $var_name,
                                      $match,
                                      $ina_ITA2User_var_list,
                                      $ina_User2ITA_var_list);
        if($ret === false) {
            return false;
        }
        // 行番号
        $ina_var_Info_list = array();
        $ina_var_Info_list['LINE'] =     $in_line;
        // 変数タイプ   ITA変数(VAR_)/ユーザー変数
        $ina_var_Info_list['VAR_TYPE'] = $ret;
        // 変数名
        $ina_var_Info_list['VAR_NAME'] = $var_name;
        $ina_var_Info_list['DATA']     = array();

        return true;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F1012
    // 処理内容
    //   defalte変数ファイルに定義さけている変数の情報で、
    //   ITAの文法に反する定義をエラーにする。
    //   Spyc.phpで誤解析する可能性がある文法を調整する。
    //
    // パラメータ
    //   $ina_parent_vars_list:   defalte変数ファイルの親変数毎に分解配列
    //                            []['LINE']                     親変数の行番号
    //                              ['VAR_NAME']                 変数名
    //                              ['VAR_TYPE']                 変数タイプ
    //                                                           self::LC_VAR_TYPE_ITA / self::LC_VAR_TYPE_USER / self::LC_VAR_TYPE_USER_ITA
    //                              ['DATA'][]['LINE]            行番号
    //                                        ['EDIT_SKIP']      編集により不要となった行(true)/default(false)
    //                                        ['MARK_POS']       先頭からの - の位置　-がない場合は-1
    //                                        ['VAR_POS']        先頭からの変数文字列の位置
    //                                        ['VAL_POS']        先頭からの具体値文字列の位置
    //                                        ['DATA']           行データ
    //                                        ['VAR_NAME']       変数名
    //                                        ['VAR_VAL']        具体値
    //                                        ['VAR_DEF']        変数定義有無(true)/(false)
    //                                        ['VAL_DEF']        具体値定義有無(true)/(false)
    //                                        ['ARRAY_VAR_DEF']  配列変数定義有無(true)/(false)
    //                                                         - { }
    //                                        ['ZERO_LIST_DEF']  複数具体値初期値定義(true)/(false)
    //                                                         []
    //                                        ['ZERO_ARRAY_DEF'] 配列変数初期値定義(true)/(false)
    //                                                         {}
    //                                                         現在未使用
    //
    //   $in_role_name:           ロール名
    //   $in_file_name:           defalte変数ファイル名
    //   $in_errmsg:              エラー時のメッセージ格納
    //   $in_f_name:              ファイル名
    //   $in_f_line:              エラー発生行番号格納
    //
    // 戻り値
    //   true:   正常
    //   false:  異常
    ////////////////////////////////////////////////////////////////////////////////
    function MiddleAnalysis(&$ina_parent_vars_list,
                             $in_role_name,
                             $in_file_name,
                            &$in_errmsg,
                             $in_msg_role_pkg_name) {
        $array_line = 0;
        foreach($ina_parent_vars_list as $parentVarInfo){
            ///////////////////////////////////////////////
            // ITAの文法に反する定義をエラーにする。
            ///////////////////////////////////////////////
            // 具体値が複数行にまたがっている場合はエラー
            if(is_array($parentVarInfo['DATA'])){
                $row_count = count($parentVarInfo['DATA']);
                for($idx = 0;$idx < $row_count;$idx++){
                    // 具体値定義がある行か判定
                    if($parentVarInfo['DATA'][$idx]['VAL_DEF'] === true){
                        if($idx != ($row_count - 1)){
                            // 次の行が具体値のみの定義か判定
                            if(($parentVarInfo['DATA'][$idx + 1]['VAL_DEF'] === true) &&
                               ($parentVarInfo['DATA'][$idx + 1]['MARK_POS'] === -1 ) &&
                               ($parentVarInfo['DATA'][$idx + 1]['VAR_DEF'] === false)) {
                                $in_errmsg = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70075",
                                    array($in_msg_role_pkg_name,   $in_role_name,
                                          basename($in_file_name), $parentVarInfo['DATA'][$idx]['LINE']))
                                    . "\n(" . __FILE__ . ":" . __LINE__ . ")";
                                return false;
                            }
                        }
                    }
                }
            }
            ///////////////////////////////////////////////
            // Spycで誤解析する可能性がある文法を調整する。
            ///////////////////////////////////////////////
            // 配列変数のカンマの位置を調整 VAR1: VAL.VAR2: ...  => VAR1: VAL. VAR2: ...
            if(is_array($parentVarInfo['DATA'])){
                $row_count = count($parentVarInfo['DATA']);
                for($idx = 0;$idx < $row_count;$idx++){
                    if($ina_parent_vars_list[$array_line]['DATA'][$idx]['ARRAY_VAR_DEF'] === true){
                        $string = $ina_parent_vars_list[$array_line]['DATA'][$idx]['DATA'];
                        $ret = preg_match_all('/,[a-zA-Z0-9_]*(\s*):(\s+)/',$string,$match);
                        if($ret > 0){
                            foreach($match[0] as $var_string){
                                $upd_var_string = preg_replace('/^,/',', ',$var_string);
                                $string = str_replace($var_string,$upd_var_string,$string);
                            }
                        }
                        $ina_parent_vars_list[$array_line]['DATA'][$idx]['DATA'] = $string;
                    }
                }
            }
            // 変数と具体値が別々の行にまたがっている場合に1行にまとめる
            if(is_array($parentVarInfo['DATA'])){
                $row_count = count($parentVarInfo['DATA']);
                for($idx = 0;$idx < $row_count;$idx++){
                    // 変数のみの定義
                    if(($parentVarInfo['DATA'][$idx]['VAR_DEF'] === true) &&
                       ($parentVarInfo['DATA'][$idx]['VAL_DEF'] === false)){
                        if($idx != ($row_count - 1)){
                            // 具体値のみの定義か判定
                            if(($parentVarInfo['DATA'][$idx + 1]['VAL_DEF']  === true ) &&
                               ($parentVarInfo['DATA'][$idx + 1]['MARK_POS'] === -1   ) &&
                               ($parentVarInfo['DATA'][$idx + 1]['VAR_DEF']  === false)){
                                // 変数と具体値を結合する
                                $ina_parent_vars_list[$array_line]['DATA'][$idx]['DATA'] =
                                $ina_parent_vars_list[$array_line]['DATA'][$idx]['DATA'] . " " . 
                                $ina_parent_vars_list[$array_line]['DATA'][$idx + 1]['VAR_VAL'];

                                $ina_parent_vars_list[$array_line]['DATA'][$idx]['VAL_DEF'] = true;
                                $ina_parent_vars_list[$array_line]['DATA'][$idx]['VAR_VAL'] = 
                                $ina_parent_vars_list[$array_line]['DATA'][$idx + 1]['VAR_VAL'];

                                // 具体値の行を無効にする
                                $ina_parent_vars_list[$array_line]['DATA'][$idx + 1]['EDIT_SKIP'] = true;
                            }
                        }
                    }
                }
            }
            $array_line++;
        }
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F1013
    // 処理内容
    //   ITAで加工したdefalte変数ファイルの内容を
    //   一時ファイルに出力する。
    //
    // パラメータ
    //   $in_tmp_file_name:           一時ファイル名
    //   $ina_parent_vars_list:   defalte変数ファイルの親変数毎に分解配列
    //   $in_role_name:           ロール名
    //   $in_file_name:           defalte変数ファイル名
    //   $in_errmsg:              エラー時のメッセージ格納
    //   $in_f_name:              ファイル名
    //   $in_f_line:              エラー発生行番号格納
    //
    // 戻り値
    //   true:   正常
    //   false:  異常
    ////////////////////////////////////////////////////////////////////////////////
    function CreateTempDefaultsVarsFile($in_tmp_file_name,$ina_parent_vars_list,
                                        $in_role_name, $in_file_name, &$in_errmsg,
                                        $in_msg_role_pkg_name) {
        $fd = fopen($in_tmp_file_name, "w");
        if($fd == null){
            $in_errmsg = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70082",
                                    array($in_msg_role_pkg_name, $in_role_name, basename($in_file_name)))
                                    . "\n(" . __FILE__ . ":" . __LINE__ . ")";

            return false;
        }

        // 加工されたdefalte変数ファイルの情報を一時ファイルに出力
        $row_count = @count($ina_parent_vars_list['DATA']);
        for($idx = 0;$idx < $row_count;$idx++){
            // 無効データか判定
            if($ina_parent_vars_list['DATA'][$idx]['EDIT_SKIP'] === true){
                continue;
            }
            // 改行を付けて一時ファイルに出力
            $string = $ina_parent_vars_list['DATA'][$idx]['DATA'] . "\n";
            if( @fputs($fd, $string) === false ){
                fclose($fd);

                $in_errmsg = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70083",
                                    array($in_msg_role_pkg_name, $in_role_name, basename($in_file_name)))
                                    . "\n(" . __FILE__ . ":" . __LINE__ . ")";

                return false;
            }
        }
        fclose($fd);
        return true;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F1014
    // 処理内容
    //   Spycモジュールの読み込み
    //
    // パラメータ
    //   $in_errmsg:              エラー時のメッセージ格納
    //
    // 戻り値
    //   true:   正常
    //   false:  異常
    ////////////////////////////////////////////////////////////////////////////////
    function loadSpycModule(&$in_errmsg){
        global $root_dir_path;

        // Spycモジュールのパスを取得
        $spyc_path = @file_get_contents($root_dir_path . "/confs/commonconfs/path_PHPSpyc_Classes.txt");
        if($spyc_path === false){
            $in_errmsg = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70084");
            return false;
        }
        // 改行コードが付いている場合に取り除く
        $spyc_path = str_replace("\n","",$spyc_path);
        $spyc_path = $spyc_path . "/Spyc.php";
        if( file_exists($spyc_path) === false ){
            $in_errmsg = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70085");
            return false;
        }
        require ($spyc_path);

        return true;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F1015
    //
    //
    //
    ////////////////////////////////////////////////////////////////////////////////
    function LastAnalysis($in_tmp_file_name,
                          $ina_parent_vars_list,
                         &$ina_vars_list,
                         &$ina_varsval_list,
                         &$ina_array_vars_list,
                          $in_role_name,
                          $in_file_name,
                         &$in_errmsg,
                          $in_msg_role_pkg_name) {

        // 加工されたdefalte変数ファイルの情報を一時ファイルに出力
        foreach($ina_parent_vars_list as $parentVarInfo){
            $ret = $this->CreateTempDefaultsVarsFile($in_tmp_file_name,
                                                     $parentVarInfo,
                                                     $in_role_name,
                                                     $in_file_name,
                                                     $in_errmsg,
                                                     $in_msg_role_pkg_name);
            if($ret === false){
                return false;
            }
            $var_array = array();
            $var_array = Spyc::YAMLLoad($in_tmp_file_name);

            @unlink($in_tmp_file_name);

            // 汎用的に使われるエラーメッセージ用のデータ
            $dataArrayForMsg = array($in_msg_role_pkg_name, $in_role_name, basename($in_file_name),
                                                                  $parentVarInfo['LINE']);

            if(is_array($var_array)){
                if(@count($var_array) != 1){
                    $in_errmsg = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70086", $dataArrayForMsg)
                                    . "\n(" . __FILE__ . ":" . __LINE__ . ")";

                    return false;
                }
                foreach($var_array as $parent_var=>$val1);
            }
            else{
                $in_errmsg = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70086", $dataArrayForMsg)
                                    . "\n(" . __FILE__ . ":" . __LINE__ . ")";

                return false;
            }

            // 一般変数か判定
            $ret = $this->checkAndListUpStandardVariable($parent_var,
                                                        $var_array[$parent_var],
                                                        $ina_vars_list,
                                                        $ina_varsval_list,
                                                        $parentVarInfo['VAR_TYPE']);
            if($ret === true) {
                continue;
            }
            // 複数具体値変数か判定
            $ret = $this->checkAndListUpMultiValueVariable($parent_var,
                                                            $var_array[$parent_var],
                                                            $ina_vars_list,
                                                            $ina_varsval_list,
                                                            $parentVarInfo['VAR_TYPE']);
            if($ret === true) {
                continue;
            }
            // 多次元配列変数か判定　配列変数も多次元配列として扱う
            $ret = $this->checkAndListUpNestedVariable($parent_var,
                                                        $var_array[$parent_var],
                                                        $ina_array_vars_list,
                                                        $parentVarInfo['VAR_TYPE'],
                                                        $in_role_name,
                                                        $in_errmsg,
                                                        $dataArrayForMsg);
            if($ret === true) {
                continue;
            }
            return false;
        }
    }

    //
    //  [VAR_top1] => var_top1
    //
    ////////////////////////////////////////////////////////////////////////////////
    // F1016
    // 処理内容
    //   Spycから取得した配列構造が一般変数か判定
    // パラメータ
    //   $in_var_array:               Spycから取得した配列構造
    // 戻り値
    //   true: 正常　false:異常
    ////////////////////////////////////////////////////////////////////////////////
    function checkAndListUpStandardVariable($in_var, $in_var_array, &$ina_vars_list, &$ina_varsval_list, $in_var_type) {

        if(is_array($in_var_array)) {
            return false;
        }

        // VAR_か読替変数のみ変数情報退避
        if(($in_var_type == self::LC_VAR_TYPE_ITA) ||
           ($in_var_type == self::LC_VAR_TYPE_USER_ITA)) {

            $ina_vars_list[$in_var] = self::LC_VARS_ATTR_STD;
            $ina_varsval_list[$in_var][self::LC_VARS_ATTR_STD] = $in_var_array;
        }
        return true;
    }

    //
    //  [VAR_top3] => Array
    //    (
    //        [0] => VAR_top3_1
    //        [1] => VAR_top3_2
    //    )
    //  [VAR_top3] => Array
    //    (
    //    )
    //
    // F1017
    ////////////////////////////////////////////////////////////////////////////////
    // F1017
    // 処理内容
    //   Spycから取得した配列構造が複数具体値の変数か判定
    // パラメータ
    //   $in_var_array:               Spycから取得した配列構造
    // 戻り値
    //   true: 正常　false:異常
    ////////////////////////////////////////////////////////////////////////////////
    function checkAndListUpMultiValueVariable($in_var, $in_var_array, &$ina_vars_list, &$ina_varsval_list, $in_var_type) {

        $ret = $this->validateMultiValueVariable($in_var_array);
        if($ret === false) {
            return false;
        }

        // VAR_か読替変数のみ変数情報退避
        if(($in_var_type == self::LC_VAR_TYPE_ITA) ||
           ($in_var_type == self::LC_VAR_TYPE_USER_ITA)) {

            $ina_vars_list[$in_var] = self::LC_VARS_ATTR_LIST;
            $assign_seq = 1;
            foreach($in_var_array as $chk_array) {
                $ina_varsval_list[$in_var][self::LC_VARS_ATTR_LIST][$assign_seq] = $chk_array;
                $assign_seq++;
            }
        }
        return true;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F1018
    // 処理内容
    //   配列構造が複数具体値の変数か判定
    // パラメータ
    //   $in_var_array:               Spycから取得した配列構造
    // 戻り値
    //   true: 正常　false:異常
    ////////////////////////////////////////////////////////////////////////////////
    function validateMultiValueVariable($in_var_array){
        if(is_array($in_var_array)){
            if(count($in_var_array) == 0){
                return true;
            }
            foreach($in_var_array as $key => $chk_array){
                if( ! is_numeric($key)){
                    return false;
                }
                if(is_array($chk_array)){
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F1019
    // 処理内容
    //   Spycから取得した配列構造を解析する。
    // パラメータ
    //   $in_var:                     親変数名
    //   $in_var_array:               Spycから取得した配列構造
    //   $ina_array_vars_list:        配列構造の解析結果
    //   $in_var_type:                変数区分 VAR_かどうか
    //   $in_role_name:               対象のロール名
    //   $in_errmsg:                  エラー時のエラーメッセージ
    //   
    // 戻り値
    //   true: 正常　false:異常
    ////////////////////////////////////////////////////////////////////////////////    
    function checkAndListUpNestedVariable($in_var,
                                          $in_var_array,
                                         &$ina_array_vars_list,
                                          $in_var_type,
                                          $in_role_name,
                                         &$in_errmsg,
                                          $dataArrayForMsg) {
        if(is_array($in_var_array)){
            $ret = is_assoc($in_var_array);
            if($ret == -1){
                $in_errmsg = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70087", $dataArrayForMsg)
                                    . "\n(" . __FILE__ . ":" . __LINE__ . ")";
                return false;
            }

            $col_count    = 0;
            $assign_count = 0;
            $error_code   = "";
            $src_line     = "";
            $diff_vars_list = array();
            $varval_list = array();
            $array_col_count_list = array();
            // Spycが取得した多次元配列の構造から具体値を排除する。また配列階層の配列数と具体値を取得する。
            $ret = $this->MakeMultiArrayToDiffMultiArray($in_var_array,
                                                         $diff_vars_list,
                                                         $varval_list,
                                                         "",
                                                         $array_col_count_list,
                                                         "", //配列要素番号
                                                         $error_code,
                                                         $src_line,
                                                         $col_count,
                                                         $assign_count);
            if($ret === false){
                $in_errmsg = $this->lv_objMTS->getSomeMessage($error_code, $dataArrayForMsg)
                                    . "\n(" . __FILE__ . ":" . __LINE__ . ")<=(" . $src_line . ")";
                return false;
            }

            $error_code = "";
            $src_line   = "";
            $ret = chkInnerArrayStructure($diff_vars_list, $error_code, $src_line);
            if($ret === false){
                $in_errmsg = $this->lv_objMTS->getSomeMessage($error_code, $dataArrayForMsg)
                                    . "\n(" . __FILE__ . ":" . __LINE__ . ")<=(" . $src_line . ")";
                return false;
            }

            $col_count      = 0;
            $assign_count   = 0;
            $error_code     = "";
            $src_line       = "";
            $parent_var_key = 0;
            $chl_var_key    = 0;
            $nest_lvl       = 0;
            $vars_chain_list = array();
            
            $chain_make_array = $in_var_array;

            $ret = $this->MakeMultiArrayToFirstVarChainArray(false,
                                                             "",
                                                             "",
                                                             $chain_make_array,
                                                             $vars_chain_list,
                                                             $error_code,
                                                             $src_line,
                                                             $col_count,
                                                             $assign_count,
                                                             $parent_var_key,
                                                             $chl_var_key,
                                                             $nest_lvl);
            if($ret === false){
                $in_errmsg = $this->lv_objMTS->getSomeMessage($error_code, $dataArrayForMsg)
                                    . "\n(" . __FILE__ . ":" . __LINE__ . ")<=(" . $src_line . ")";
                return false;
            }

            // VAR_か読替変数のみ変数情報退避
            if(($in_var_type == self::LC_VAR_TYPE_ITA) ||
               ($in_var_type == self::LC_VAR_TYPE_USER_ITA)) {

                $vars_last_chain_list = array();
                $ret = $this->MakeMultiArrayToLastVarChainArray($vars_chain_list,
                                                                $array_col_count_list,
                                                                $vars_last_chain_list,
                                                                $error_code,
                                                                $src_line);
                if($ret === false) {
                     $in_errmsg = $this->lv_objMTS->getSomeMessage($error_code, $dataArrayForMsg)
                                    . "\n(" . __FILE__ . ":" . __LINE__ . ")<=(" . $src_line . ")";
                     return false;
                }

                // 多次元変数構造比較用配列を退避
                $ina_array_vars_list[$in_var]['DIFF_ARRAY']     = $diff_vars_list;

                // 多次元変数親子関係のチェーン構造を退避
                $ina_array_vars_list[$in_var]['CHAIN_ARRAY']    = $vars_last_chain_list;

                // 配列階層の配列数を退避
                $ina_array_vars_list[$in_var]['COL_COUNT_LIST'] = $array_col_count_list;

                // 各変数の具体値を退避
                $ina_array_vars_list[$in_var]['VAR_VALUE']      = $varval_list;
            }
        }
        return true;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F1020
    // 処理内容
    //   Spycから取得し配列構造にはメンバー変数の具体値が含まれているので、
    //   具体値を取り除き配列数が1の配列構造(ina_vars_list)を作成する。
    //   各メンバー変数の具体値をina_varval_listに退避する。
    //   各配列階層の配列数退避をina_array_col_count_listに退避する。
    // パラメータ
    //   $ina_parent_var_array:       Spycから取得し配列構造
    //   $ina_vars_list:              具体値を取り除き配列数が1の配列構造退避
    //   $ina_varval_list:            各メンバー変数の具体値退避
    //   $in_var_name_path:           1つ前の階層までのメンバー変数のパス
    //   $ina_array_col_count_list:   配列階層の配列数退避
    //   $in_col_index_str:           各メンバー変数が属している配列の位置(1配列毎に3桁で位置を表した文字列)
    //   $in_error_code:              エラー時のエラーコード
    //   $in_line:                    エラー時の行番号格納
    //   $in_col_count:               現在未使用
    //   $in_assign_count:            現在未使用
    //   
    // 戻り値
    //   true: 正常　false:異常
    ////////////////////////////////////////////////////////////////////////////////    
    function MakeMultiArrayToDiffMultiArray($ina_parent_var_array,&$ina_vars_list,&$ina_varval_list,$in_var_name_path,&$ina_array_col_count_list,$in_col_index_str,&$in_error_code,&$in_line,&$in_col_count,&$in_assign_count){
        $demiritta_ch = ".";

        // 配列階層か判定
        $array_f = is_assoc($ina_parent_var_array);
        if($array_f == -1){
            $in_error_code = "ITAANSTWRH-ERR-70087";
            $in_line       = __LINE__;
            return false;
        }
        foreach($ina_parent_var_array as $var => $val) {
            // 具体値の配列の場合の判定
            // 具体値の配列の場合は具体値が全てとれない模様
            // - xxxx1
            //   - xxxx2
            // - xxxx3
            // array(2) {
            //    [0]=>
            //      array(1) {
            //      [0]=>
            //        string(5) "xxxxx2"
            if(is_numeric($var)) {
                // 具体値の配列の場合の判定
                $ret = is_assoc($val);
                if($ret == "I"){
                    $in_error_code = "ITAANSTWRH-ERR-70090";
                    $in_line       = __LINE__;
                    return false;
                }
                // 配列階層の配列数または複数具体値の具体値数が999以上あった場合はエラーにする。
                if($var >= 999)   // 0からなので $var >= 999
                {
                    // 配列階層の配列数が999以上あった
                    if($array_f == "I"){
                        $in_error_code = "ITAANSTWRH-ERR-90218";
                        $in_line       = __LINE__;
                        return false;
                    }
                    else{
                        // 具体値数は制限しない
                        // 具体値数が999以上あった
                        //$ary[90219] = "｛｝ロール:(｛｝) ファイル:(｛｝) の｛｝行目の変数は一つの変数に999を超える具体値が定義されています。";
                        //$in_error_code = "ITAANSTWRH-ERR-90219";
                        //$in_line       = __LINE__;
                        //return false;
                    }
                }
            }
            // 複数具体値か判定する。
            if(is_numeric($var)) {
                // 具体値がある場合は排除する。
                if( ! is_array($val)){
                    // 代入順序を1オリジンにする。
                    $ina_varval_list[$in_var_name_path][self::LC_VARS_ATTR_LIST][$in_col_index_str][($var + 1)]=$val;
                    continue;
                }
            }
            // 配列階層か判定
            if($array_f == 'I'){
                // 配列階層の列番号を退避 各配列の位置を3桁の数値文字列で結合していく 
                $wk_col_index_str = $in_col_index_str . sprintf("%03d",$var);

                // 配列階層の場合の変数名を設定 変数名を0に設定する。
                if($in_var_name_path == ""){
                    $wk_var_name_path = '0';
                }
                else{
                    $wk_var_name_path = $in_var_name_path . $demiritta_ch . '0';
                }
                if(@count($ina_array_col_count_list[$wk_var_name_path]) == 0){
                    // 配列階層の配列数を退避
                    $ina_array_col_count_list[$wk_var_name_path] = count($ina_parent_var_array);
                }
            }
            else{
                // 配列階層の列番号を退避
                $wk_col_index_str = $in_col_index_str;

                // 配列階層の以外の場合の変数名を設定
                if($in_var_name_path == ""){
                    $wk_var_name_path = $var;
                }
                else{
                    $wk_var_name_path = $in_var_name_path . $demiritta_ch . $var;
                }
            }
            $ina_vars_list[$var] = array();
            // Key-Value変数か判定
            if( ! is_array($val)) {
                // 具体値がある場合は排除する。
                $ina_varval_list[$wk_var_name_path][self::LC_VARS_ATTR_STD][$wk_col_index_str]=$val;
                continue;
            }
            $ret = $this->MakeMultiArrayToDiffMultiArray($val,$ina_vars_list[$var],$ina_varval_list,$wk_var_name_path,$ina_array_col_count_list,$wk_col_index_str,$in_error_code,$in_line,$in_col_count,$in_assign_count);
            if($ret === false){
                return false;
            }
        }
        return true;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F1025
    // 処理内容
    //   多次元変数の構造を解析。ina_vars_chain_listに解析データを退避する。
    // パラメータ
    //   $in_fastarry_f:              配列定義内かを判定
    //   $in_var_name:                1つ前の階層のメンバー変数
    //   $in_var_name_path:           1つ前の階層のメンバー変数のパス
    //   $ina_parent_var_array:       多次元変数の階層構造
    //   $ina_vars_chain_list:        多次元変数の解析データ格納
    //   $in_error_code:              エラー時のエラーコード
    //   $in_line:                    エラー時の行番号格納
    //   $in_col_count:               未使用
    //   $in_assign_count:            未使用
    //   $ina_parent_var_key:         1つ前の階層のメンバー変数のID（0～）
    //   $in_chl_var_key:             同一階層の1つ前のメンバー変数のID（0～）
    //   $in_nest_lvl:                階層レベル（1～）
    //   
    // 戻り値
    //   true: 正常　false:異常
    ////////////////////////////////////////////////////////////////////////////////    
    function MakeMultiArrayToFirstVarChainArray($in_fastarry_f,
                                                $in_var_name,
                                                $in_var_name_path,
                                                $ina_parent_var_array,
                                               &$ina_vars_chain_list,
                                               &$in_error_code,
                                               &$in_line,
                                               &$in_col_count,
                                               &$in_assign_count,
                                                $ina_parent_var_key,
                                               &$in_chl_var_key,
                                                $in_nest_lvl){
        $demiritta_ch = ".";
        $in_nest_lvl++;
        $parent_var_key = $ina_parent_var_key;
        $ret = is_assoc($ina_parent_var_array);
        if($ret == -1){
            $in_error_code = "ITAANSTWRH-ERR-70087";
            $in_line       = __LINE__;
            return false;
        }
        $fastarry_f_on = false;
        foreach($ina_parent_var_array as $var => $val) {
            $col_array_f = "";
            // 複数具体値の場合
            if(is_numeric($var)) {
                if( ! is_array($val)){
                    continue;
                }
                else{
                    $col_array_f = "I";
                }
            }
            $MultiValueVar_f = $this->validateMultiValueVariable($val);
            if(strlen($in_var_name) != 0){
                $wk_var_name_path = $in_var_name_path . $demiritta_ch . $var;
                if(is_numeric($var) === false)
                    $wk_var_name = $in_var_name . $demiritta_ch . $var;
                else
                    $wk_var_name = $in_var_name;
            }
            else{
                $wk_var_name_path = $var;
                if(is_numeric($var) === false)
                    $wk_var_name = $var;
                else
                    $wk_var_name = $var;
            }
            // 配列の開始かを判定する。
            if($col_array_f == "I"){
                if($in_fastarry_f === false){
                    $in_fastarry_f = true;
                    $fastarry_f_on = true;
                }
            }               
            $in_chl_var_key++;
            $ina_vars_chain_list[$parent_var_key][$in_chl_var_key]['VAR_NAME']       = $var;
            $ina_vars_chain_list[$parent_var_key][$in_chl_var_key]['NEST_LEVEL']     = $in_nest_lvl;
            $ina_vars_chain_list[$parent_var_key][$in_chl_var_key]['LIST_STYLE']     = "0";
            $ina_vars_chain_list[$parent_var_key][$in_chl_var_key]['VAR_NAME_PATH']  = $wk_var_name_path;
            $ina_vars_chain_list[$parent_var_key][$in_chl_var_key]['VAR_NAME_ALIAS'] = $wk_var_name;
            $ina_vars_chain_list[$parent_var_key][$in_chl_var_key]['ARRAY_STYLE']    = "0";
            $MultiValueVar_f = $this->validateMultiValueVariable($val);
            if($MultiValueVar_f===true){
                $ina_vars_chain_list[$parent_var_key][$in_chl_var_key]['LIST_STYLE'] = "5";
            }
            // 配列の中の変数の場合
            if($in_fastarry_f === true){
                $ina_vars_chain_list[$parent_var_key][$in_chl_var_key]['ARRAY_STYLE'] = "1";
            }
            if( ! is_array($val)) {
                continue;
            }
            $ret = $this->MakeMultiArrayToFirstVarChainArray($in_fastarry_f,
                                                             $wk_var_name,
                                                             $wk_var_name_path,
                                                             $val,
                                                             $ina_vars_chain_list,
                                                             $in_error_code,
                                                             $in_line,
                                                             $in_col_count,
                                                             $in_assign_count,
                                                             $in_chl_var_key,
                                                             $in_chl_var_key,
                                                             $in_nest_lvl);
            if($ret === false){
                return false;
            }
            // 配列開始のマークを外す
            if($fastarry_f_on === true){
                $in_fastarry_f = false;
            }               
            if(is_numeric($var)){
                if($var === 0){
                    break;
                }
            }
        }
        return true;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F1026
    // 処理内容
    //   多次元変数の各メンバー構造で代入値管理系で列順序と代入順序が必要となる変数をマークする。
    //   配列の場合に配列数を設定する。
    // パラメータ
    //   $ina_first_vars_chain_list:    
    //   $array_col_count_list:
    //   $ina_vars_chain_list:
    //   $in_error_code:              エラー時のエラーコード
    //   
    // 戻り値
    //   true: 正常　false:異常
    ////////////////////////////////////////////////////////////////////////////////    
    function MakeMultiArrayToLastVarChainArray($ina_first_vars_chain_list,$array_col_count_list,&$ina_vars_chain_list,&$in_error_code){
        // 代入値管理系で列順序になる変数の候補にマークする。と代入順序が必要な変数を設定する。
        $ina_vars_chain_list = array();
        foreach($ina_first_vars_chain_list as $parent_vars_key_id=>$chl_vars_list){
            foreach($chl_vars_list as $vars_key_id=>$vars_info){
                $info_array = array();
                $info_array['PARENT_VARS_KEY_ID'] = $parent_vars_key_id;
                $info_array['VARS_KEY_ID']        = $vars_key_id;
                $info_array['VARS_NAME']          = $vars_info["VAR_NAME"];
                $info_array['ARRAY_NEST_LEVEL']   = $vars_info["NEST_LEVEL"];
                // 複数具体値なので代入順序が必要なのでマークする。
                if($vars_info['LIST_STYLE'] != 0){
                    $info_array['ASSIGN_SEQ_NEED']      = "1";
                }
                else{
                    $info_array['ASSIGN_SEQ_NEED']      = "0";
                }
                // 配列変数より下の階層にある変数なので列順序になる変数の候補にマークする。
                if($vars_info['ARRAY_STYLE'] != 0){
                    $info_array['COL_SEQ_MEMBER']       = "1";
                }
                else{
                    $info_array['COL_SEQ_MEMBER']       = "0";
                }
                $info_array['COL_SEQ_NEED']         = "0";
                $info_array['MEMBER_DISP']          = "0";
                $info_array['VRAS_NAME_PATH']       = $vars_info["VAR_NAME_PATH"];
                $info_array['VRAS_NAME_ALIAS']      = $vars_info["VAR_NAME_ALIAS"];

                // 配列階層(変数名が0)の場合に配列数を設定する。
                if($info_array['VARS_NAME'] == "0"){
                    if(@count($array_col_count_list[$info_array['VRAS_NAME_PATH']]) == 0){
                        $in_error_code = "ITAANSTWRH-ERR-90220"; 
                        return false;
                    }
                    else{
                        $info_array['MAX_COL_SEQ']     = $array_col_count_list[$info_array['VRAS_NAME_PATH']];
                    }
                }
                else{
                    $info_array['MAX_COL_SEQ']         = "0";
                }
                $ina_vars_chain_list[] = $info_array;
                unset($info_array);
            }
        }
        // 代入値管理系で表示する変数をマークする。列順序が必要な変数をマークする。
        $row_count = count($ina_vars_chain_list);
        $var_key_list = array();
        for($idx=0;$idx<$row_count;$idx++){
            $var_key_list[] = $ina_vars_chain_list[$idx]['VARS_KEY_ID'];
        }
        // 自分より下の階層がない変数を表示対象にする。
        for($key_idx=0;$key_idx < count($var_key_list);$key_idx++){
            $hit = false;
            for($idx=0;$idx<$row_count;$idx++){
                // 自分より下の階層がある。
                if($var_key_list[$key_idx] == $ina_vars_chain_list[$idx]['PARENT_VARS_KEY_ID']){
                     $hit = true;
                     break;
                }
            }
            // 自分より下の階層がなかった。
            if($hit === false){
                // 代入値管理系に表示する変数なのでマークする。
                $ina_vars_chain_list[$key_idx]['MEMBER_DISP'] = "1";
                // 代入値管理系で列順序が必要なのでマークする。
                if($ina_vars_chain_list[$key_idx]['COL_SEQ_MEMBER'] == "1"){
                    $ina_vars_chain_list[$key_idx]['COL_SEQ_NEED'] = "1";
                }
            }
        }
        return true;
    }
}

