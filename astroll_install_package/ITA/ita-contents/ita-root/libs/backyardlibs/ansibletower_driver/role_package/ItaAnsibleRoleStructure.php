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
//    ・AnsibleTower用 AnsibleRole(Zipファイル)の内容をチェックする。
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

require_once($root_dir_path . "/libs/backyardlibs/ansibletower_driver/MessageTemplateStorageHolder.php");
require_once($root_dir_path . "/libs/backyardlibs/ansibletower_driver/AnsibleTowerCommonLib.php");
require_once($root_dir_path . "/libs/backyardlibs/ansibletower_driver/role_package/varsStructureAnalyzeLib.php");
require_once($root_dir_path . "/libs/backyardlibs/ansibletower_driver/role_package/DefaultVarsFileAnalyzer.php");
require_once($root_dir_path . "/libs/backyardlibs/ansibletower_driver/role_package/WrappedStringReplaceAdmin.php");

class ItaAnsibleRoleStructure {

    private $zipFilePath;
    // role名一覧
    // $rolenames[role名]
    private $rolenames;
    // プレイブック内変数名一覧
    // $playbookVarsName[role名][変数名]=0
    private $playbookVarsName;
    // エラーメッセージ退避
    private $lastErrMsg;
    //
    private $lv_objMTS;

    private $rolePkgName;

    private $baseDir;

    private $systemVars;

    private $lva_globalvarname ; // グローバル変数

    //   $vars_list:  各ロールのデフォルト変数ファイル内に定義されている
    //                        変数名のリスト 
    //                          一般変数
    //                            $vars_list[ロール名][変数名]=0
    //                          配列変数
    //                            $vars_list[ロール名][配列数名]=array([子供変数名]=0,...)
    private $vars_list;

    //   $varsVal_list:  
    //                        各ロールのデフォルト変数ファイル内に定義されている変数名の具体値リスト
    //                          一般変数
    //                            $varsVal_list[ロール名][変数名][0]=具体値
    //                          複数具体値変数
    //                            $varsVal_list[ロール名][変数名][1]=array(1=>具体値,2=>具体値....)
    //                          配列変数
    //                            $varsVal_list[ロール名][変数名][2][メンバー変数]=array(1=>具体値,2=>具体値....)
    private $varsVal_list;

    private $nestedVars_list;

    //   $ITA2User_var_list  読替表の変数リスト
    //       $ITA2User_var_list[rolename][]: ITA変数=>ユーザ変数
    private $ITA2User_var_list;
    //   $User2ITA_var_list  読替表の変数リスト
    //       $User2ITA_var_list[rolename][]: ユーザ変数=>ITA変数
    private $User2ITA_var_list;

    //   $useCopyVar:    Playbookからcopyモジュールの変数を取得の有無  true:取得 false:取得しない
    private $useCopyVar;

    //   $ina_copyvars_list: Playbookで使用しているcopyモジュールの変数のリスト
    //                       $ina_copyvars_list[ロール名][変数名]=1
    private $copyVars_list;

    //   $translateCombErrVarsList: 読替変数と任意変数の組合せが一意でないリスト 
    //                            array(2) {
    //                               ["USER_VAR"]=>
    //                                 array(1) {
    //                                   ["LCA_sample_02"]=>
    //                                   array(1) {
    //                                     ["dummy pkg"]=>
    //                                     array(2) {
    //                                       ["ITAOrigVar"]=>
    //                                       string(14) "user_sample_02"
    //                                       ["test"]=>
    //                                       string(14) "user_sample_05"
    //                                 } } }
    //                                 ["ITA_VAR"]=>
    //                                 array(1) {
    //                                   ["user_sample_03"]=>
    //                                   array(1) {
    //                                     ["dummy pkg"]=>
    //                                     array(2) {
    //                                       ["ITAOrigVar"]=>
    //                                       string(13) "LCA_sample_03"
    //                                       ["test"]=>
    //                                       string(13) "LCA_sample_04"
    //                               } } } }
    private $translateCombErrVarsList;

    //   $structErrVarsList: ロールパッケージ内で使用している変数で構造が違う変数のリスト
    //                             structErrVarsList[変数名][ロールパッケージ名][ロール名]
    private $structErrVarsList;

    ////////////////////////////////////////////////////////////////////////////////
    // F0001
    // 処理内容
    //   コンストラクタ
    // パラメータ
    //   $rolePkgName:    ロールパッケージ名
    //   $dirPath:        ロールパッケージ展開先パス
    //   $system_vars:    共通変数
    //   $useCopyVar:     コピー変数を解析するか
    // 戻り値
    //   なし
    ////////////////////////////////////////////////////////////////////////////////
    function __construct($rolePkgName, $dirPath, $system_vars, $useCopyVar) {
        $this->lastErrMsg   = array();
        $this->rolePkgName  = $rolePkgName;
        $this->baseDir      = $dirPath;
        $this->systemVars   = $system_vars;
        $this->useCopyVar   = $useCopyVar;

        $this->lv_objMTS    = MessageTemplateStorageHolder::getMTS();

        ///////////////////////////////////////////////////
        // 各必要変数の初期化
        ///////////////////////////////////////////////////
        // role名一覧 初期化
        $this->rolenames = array();
        // プレイブック内変数名一覧 初期化
        $this->playbookVarsName = array();
        // デフォルト変数定義一覧 初期化
        $this->vars_list = array();
        // 変数具体値一覧 初期化
        $this->varsVal_list = array();
        // 多段変数構造一覧 初期化
        $this->nestedVars_list = array();
        /* roleグローバル変数名一覧 */
        $this->lva_globalvarname = array();
        // 読替表 初期化
        $this->ITA2User_var_list = array();
        $this->User2ITA_var_list = array();
        // Playbookで使用しているcopyモジュールの変数のリスト 初期化
        $this->copyVars_list = array();
        // 読替変数と任意変数の組合せが一意でないリスト 初期化
        $this->translateCombErrVarsList = array();
        // ロールパッケージ内で使用している変数で構造が違う変数のリスト 初期化
        $this->structErrVarsList = array();
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F0002
    // 処理内容
    //   zipファイル内で定義されているプレイブック内変数名を取得
    // パラメータ
    //   なし
    // 戻り値
    //   プレイブック内変数名配列
    //   $playbookVarsName[role名][変数名]=0
    ////////////////////////////////////////////////////////////////////////////////
    function getPlaybookVarsName() {
        return $this->playbookVarsName;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F0003
    // 処理内容
    //   zipファイル内で定義されているロール名を取得
    // パラメータ
    //   なし
    // 戻り値
    //   role名配列
    //   $rolenames[role名]
    ////////////////////////////////////////////////////////////////////////////////
    function getRoleNames(){
        return $this->rolenames;
    }

    function getVars() {
        return $this->vars_list;
    }

    function getVarsVal() {
        return $this->varsVal_list;
    }

    function getNestedVars() {
        return $this->nestedVars_list;
    }

    function getITA2User_vars() {
        return $this->ITA2User_var_list;
    }

    function getCopyVars() {
        return $this->copyVars_list;
    }

    function getTranslateCombErrVars() {
        return $this->translateCombErrVarsList;
    }

    function getStructErrVars() {
        return $this->structErrVarsList;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F0004
    // 処理内容
    //   エラーメッセージ取得
    // パラメータ
    //   なし
    // 戻り値
    //   エラーメッセージ
    ////////////////////////////////////////////////////////////////////////////////
    function getLastError() {
        return $this->lastErrMsg;
    }

    function setLastError($p1, $p2, $p3) {
        $this->lastErrMsg[0] = $p3;
        $this->lastErrMsg[1] = "FILE:$p1 LINE:$p2 $p3";
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F0005
    // 処理内容
    //   zipファイルを展開する
    // パラメータ
    //   なし
    // 戻り値
    //   true: 正常 false:異常
    ////////////////////////////////////////////////////////////////////////////////
    function zipExtractTo() {
        $zip = new ZipArchive();
        if($zip->open($this->zipFilePath) === true) {
            $zip->extractTo($this->baseDir);
            $zip->close();
        } else {
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70005");
            $this->setLastError(basename(__FILE__), __LINE__, $msgstr);
            return false;
        }

        return true;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F0006
    // 処理内容
    //   rolesディレクトリ配下のディレクトリとファイルが妥当かチェックする。
    // パラメータ
    //   なし
    //
    // 戻り値
    //   true: 正常 false:異常
    ////////////////////////////////////////////////////////////////////////////////
    function chkRolesDirectory() {

        ///////////////////////////////////////////////////
        // rolesディレクトリが存在しているか判定
        ///////////////////////////////////////////////////
        $existsRolesDir = false;

        $files = scandir($this->baseDir);
        $files = array_filter($files,
                              function ($file) {
                                  return !in_array($file, array('.', '..'));
                              }
                             );
        $baseDir = rtrim($this->baseDir,'/') . '/';

        foreach($files as $file) {

            $fullpath = $baseDir . $file;

            if(is_file($fullpath)) {
                //rolesディレクトリ以外は無視
                continue;
            }

            if(is_dir($fullpath)) {

                // ディレクトリのパーミッション設定
                if(!chmod($fullpath, 0777)) {
                    $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-55203", array($file));
                    $this->setLastError(basename(__FILE__), __LINE__, $msgstr);
                    return false;
                }

                if($file == "roles") {

                    $existsRolesDir = true;

                    /////////////////////////////////////////////////////
                    // rolesディレクトリ配下のroleディレクトリをチェック
                    /////////////////////////////////////////////////////
                    $ret = $this->chkRoleDirectory($fullpath);
                    if($ret === false) {
                        return false;
                    }

                } else {
                    //rolesディレクトリ以外は無視
                    continue;
                }
            }
        }

        if($existsRolesDir === false) {
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70002");
            $this->setLastError(basename(__FILE__), __LINE__, $msgstr);
            return false;
        }

        ///////////////////////////////////////////////////
        // デフォルト変数の確認
        ///////////////////////////////////////////////////
        $this->translateCombErrVarsList = array();

        // 比較Function用にロールパッケージ名付きの配列で包む
        $withPkgName_ITA2User_var_list      = array();
        $withPkgName_User2ITA_var_list      = array();
        $withPkgName_ITA2User_var_list[$this->rolePkgName] = $this->ITA2User_var_list;
        $withPkgName_User2ITA_var_list[$this->rolePkgName] = $this->User2ITA_var_list;

        // 読替変数と任意変数の組合せを確認する。
        $ret = $this->chkTranslationTableVarsCombination(
                    $withPkgName_ITA2User_var_list,
                    $withPkgName_User2ITA_var_list,
                    $this->translateCombErrVarsList
                );
        if($ret === false) {
            // エラーメッセージは呼び元で編集
            return false;
        }

        // 読替表を元に変数名を更新
        $this->applyTranslationTable($this->vars_list,       $this->User2ITA_var_list);
        $this->applyTranslationTable($this->nestedVars_list, $this->User2ITA_var_list);
        $this->applyTranslationTable($this->varsVal_list,    $this->User2ITA_var_list);

        // ロールパッケージ内のPlaybookで定義している変数がdefalte変数定義ファイルにあるか
        // ITA独自変数はチェック対象外にする。
        $msgstr = "";
        $ret = $this->chkDefVarsListPlayBookVarsList(
                        $this->playbookVarsName,
                        $this->vars_list,
                        $this->nestedVars_list,
                        $msgstr,
                        $this->systemVars
                    );

        if($ret === false) {
            $this->setLastError(basename(__FILE__), __LINE__, $msgstr);
            return false;
        }

        /* グローバル変数管理からグローバル変数の情報を取得 */
        $global_vars_list = array();
        $getMsgstr = "";
        $ret = getDBGlobalVars($global_vars_list,$getMsgstr);
        if($ret === false){
            $this->setLastError(basename(__FILE__),__LINE__ , $getMsgstr);

            return false ;
        }

        /* グローバル変数管理において、Playbook内で定義されているグローバル変数の存在チェック（なければエラー） */
        list($ret , $getMsgstr) = $this -> chkDefVarsListPlayBookGlobalVarsList($this->lva_globalvarname, $global_vars_list);
        if($ret === false){
            $this->setLastError(basename(__FILE__),__LINE__ , $getMsgstr);

            return false ;
        }

        // ロールパッケージ内のデフォルト変数で定義されている変数の構造を確認
        $ret = $this->chkVarsStruct($this->vars_list, $this->nestedVars_list, $this->structErrVarsList);

        if($ret === false) {
            // エラーメッセージは呼び元で編集
            return false;
        }

        return true;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F0007
    // 処理内容
    //   rolesディレクトリ配下のroleディレクトリとファイルが妥当かチェックする。
    // パラメータ
    //   $rolesDir:             rolesディレクトリ
    //
    // 戻り値
    //   true: 正常 false:異常
    ////////////////////////////////////////////////////////////////////////////////
    function chkRoleDirectory($rolesDir){

        $files = scandir($rolesDir);
        $files = array_filter($files,
                              function ($file) {
                                  return !in_array($file,array('.','..'));
                              }
                             );
        $result_code = true;
        $this->rolenames = array();
        $this->lva_rolevar = array();

        /////////////////////////////////////////////////////
        // roleディレクトリを取得
        /////////////////////////////////////////////////////
        foreach ($files as $file) {

            $fullpath = rtrim($rolesDir,'/') . '/' . $file;

            if(is_file($fullpath)) {
                //roleディレクトリ以外は無視
                continue;
            }

            if(is_dir($fullpath)) {
                // ディレクトリのパーミッション設定
                if(!chmod($fullpath, 0777)) {
                    $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-55203", array('./roles/' . $file));
                    $this->setLastError(basename(__FILE__), __LINE__, $msgstr);
                    return false;
                }

                //role名退避
                $this->rolenames[] = $file;
                $ret = $this->chkRoleSubDirectory($fullpath, $file);

                if($ret === false) {
                    return false;
                }
            }
        }

        if(count($this->rolenames) == 0) {
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70004");
            $this->setLastError(basename(__FILE__),__LINE__,$msgstr);
            return false;
        }
        return true;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F0008
    // 処理内容
    //   roleディレクトリ配下のディレクトリとファイルが妥当かチェックする。
    // パラメータ
    //   $roleDir:         roleディレクトリ
    //   $rolename:    ロール名
    //
    // 戻り値
    //   true: 正常 false:異常
    ////////////////////////////////////////////////////////////////////////////////
    function chkRoleSubDirectory($roleDir, $rolename) {

        $files = scandir($roleDir);
        $files = array_filter($files,
                              function($file) {
                                  return !in_array($file,array('.','..'));
                              }
                             );
        $tasks_dir      = false;
        $defaults_dir   = false;

        ////////////////////////////////////////////////////////
        // role内のディレクトリをチェック
        ////////////////////////////////////////////////////////
        foreach ($files as $file) {
            $fullpath = rtrim($roleDir,'/') . '/' . $file;

            if(is_dir($fullpath)) {
               // ディレクトリのパーミッション設定
               if(!chmod($fullpath, 0777)) {
                   $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-55203",
                                    array('./roles/' . $rolename . '/' . $file));
                   $this->setLastError(basename(__FILE__), __LINE__, $msgstr);
                   return false;
               }

                switch($file) {
                case "tasks":
                    $tasks_dir      = true;
                    // p1:プレイブック内変数取得有(true)/無(false)
                    // p2:main.yml必須有(true)/無(false)
                    // p3:main.ymlと他ファイルの依存有(true)/無(false)
                    // p4:main.ymlファイル以外のファイルは存在不可
                    // p5:copy変数取得有(true)/無(false)
                    //                                                      p1     p2     p3     p4     p5
                    $ret = $this->chkRoleFiles($fullpath, $rolename, $file, true,  true,  true,  false, true);
                    break;
                case "handlers":
                    $ret = $this->chkRoleFiles($fullpath, $rolename, $file, true,  false, true,  false, true);
                    break;
                case "templates":
                    $ret = $this->chkRoleFiles($fullpath, $rolename, $file, true,  false, false, false, false);
                    break;
                case "files":
                    $ret = $this->chkRoleFiles($fullpath, $rolename, $file, false, false, false, false, false);
                    break;
                case "vars":
                    $ret = $this->chkRoleFiles($fullpath, $rolename, $file, false, false, false, false, false);
                    break;
                case "defaults":
                    $defaults_dir = true;
                    // defaults下はmain.ymlのみ、他ファイルがあった場合はエラー
                    $ret = $this->chkRoleFiles($fullpath, $rolename, $file, false, true,  false, true,  false);
                    // defaults=>main.ymlから変数の情報を取得
                    if($ret === true) {
                         $ret = $this->analyzeVarsStructure($fullpath, $rolename);
                    }
                    break;
                case "meta":
                    $ret = $this->chkRoleFiles($fullpath, $rolename, $file, true,  false, true,  false, false);
                    break;
                default:
                    // ベストプラクティスのディレクトリ以外はチェックしない
                    $ret = true;
                    break;
                }

               if($ret === false) {
                   return $ret; 
               }
            }
        }

        if($tasks_dir === false) {
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70006",array('./roles/' . $rolename));
            $this->setLastError(basename(__FILE__),__LINE__,$msgstr);
            return false;
        }

        if($defaults_dir === false) {
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70007",array('./roles/' . $rolename));
            $this->setLastError(basename(__FILE__),__LINE__,$msgstr);
            return false;
        }

        return true;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F0009
    // 処理内容
    //   roleの各ディレクトリとファイルが妥当かチェックする。
    // パラメータ
    //   $in_dir:         roleディレクトリ
    //   $in_rolename     ロール名
    //   $in_dirname      ディレクトリ名
    //
    //   $extractVars     プレイブック内変数取得有(true)/無(false)
    //   $in_main_yml     main.yml必須有(true)/無(false)
    //   $in_etc_yml      main.ymlと他ファイルの依存有(true)/無(false)
    //   $in_main_yml_only
    //                    main.ymlファイル以外のファイルは存在不可
    //   $in_get_copyvar_tgt_dir: copyモジュールの変数を取得対象ディレクトリ判定 true:取得 false:取得しない
    //
    // 戻り値
    //   true: 正常 false:異常
    ////////////////////////////////////////////////////////////////////////////////
    function chkRoleFiles($in_dir, $in_rolename, $in_dirname,
                          $extractVars, $in_main_yml, $in_etc_yml, $in_main_yml_only,
                          $in_get_copyvar_tgt_dir) {

        $files = array();

        // ディレクトリ配下のファイル一覧取得
        $filelist = getFileList($in_dir);
        foreach ($filelist as $file) {
            $files[] = trim(str_replace($in_dir . "/", "", $file));
        }

        $main_yml = false;
        $etc_yml = false;
        $result_code = true;

        ////////////////////////////////////////////////////////
        // roleサブディレクトリ内のファイルをチェック
        ////////////////////////////////////////////////////////
        foreach ($files as $file) {
            $fullpath = rtrim($in_dir,'/') . '/' . $file;

            if(is_dir($fullpath)) {
                // templatesおよびfiles以外のディレクトリの場合はサブディレクトリ禁止。
                if($in_dirname != "templates" && $in_dirname != "files") {
                    $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70025",
                                    array('./roles/' . $in_rolename . '/' . $in_dirname . '/' . $file));
                    $this->setLastError(basename(__FILE__), __LINE__, $msgstr);

                    return false;
                }
            }

            if(is_file($fullpath)) {
                if($file == "main.yml") {
                     $main_yml = true;
                } else {
                     $etc_yml  = true;
                }

                if(($etc_yml === true) && ($in_main_yml_only === true)) {
                    $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70051",
                                    array('./roles/' . $in_rolename . '/' . $in_dirname . '/' . $file));
                    $this->setLastError(basename(__FILE__), __LINE__, $msgstr);
                    return(false);
                }
                // 変数初期化
                $file_vars_list        = array();

                // ホスト変数の抜出が指定されている場合
                if($extractVars === true){
                    // ファイルの内容を読込む
                    $dataString = file_get_contents($fullpath);
                    if($in_dirname == "templates"){
                        // テンプレートから変数を抜出す
                        $objWSRA = new WrappedStringReplaceAdmin("", $dataString, $this->systemVars);
                        $file_vars_list = $objWSRA->getTPFVARSParsedResult();
                        unset($objWSRA);
                    }
                    else{
                        // テンプレート以外から変数を抜出す
                        $objWSRA = new WrappedStringReplaceAdmin(DF_HOST_VAR_HED, $dataString, $this->systemVars);
                        $aryResultParse = $objWSRA->getParsedResult();
                        $file_vars_list = $aryResultParse[1];
                        unset($objWSRA);
                    }

                    // ファイル内で定義されていた変数を退避
                    if(count($file_vars_list) > 0){
                         foreach ($file_vars_list as $var){
                             $this->playbookVarsName[$in_rolename][$var] = 0;
                         }
                    }

                    /* グローバル変数を抜出す */
                    $system_vars = array();
                    $objWSRA = new WrappedStringReplaceAdmin(DF_HOST_GBL_HED,$dataString,$system_vars);
                    $aryResultParse = $objWSRA->getParsedResult();
                    $file_global_vars_list = $aryResultParse[1];
                    unset($objWSRA);


                    /* ファイル内で定義されていたグローバル変数を退避 */
                    if(count($file_global_vars_list) > 0){
                        foreach ($file_global_vars_list as $var)
                        {
                            $this->lva_globalvarname[$in_rolename][$var] = 0;
                        }
                    }

                    // copyモジュールの埋め込み変数を取得するか判定
                    if(($in_get_copyvar_tgt_dir === true) &&
                       ($this->useCopyVar       === true)) {

                        SimpleVerSearch(DF_HOST_CPF_HED, $dataString, $la_cpf_vars);

                        $tgt_file = $in_rolename . "/" . $in_dirname . "/" . $file;

                        // ファイル内で定義されていたコピー変数を退避
                        if(count($la_cpf_vars) > 0){
                            foreach( $la_cpf_vars as $no => $cpf_var_list ){
                                foreach( $cpf_var_list as $line_no  => $cpf_var_name ){
                                    $this->copyVars_list[$in_rolename][$tgt_file][$line_no][$cpf_var_name] = 0;
                                }
                            }
                        }
                    }
                }
            }
        }

        // main.ymlが必要なディレクトリにmain.ymlがない場合
        if(($in_main_yml === true) && ($main_yml===false)) {
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70003",
                                    array('./roles/' . $in_rolename . '/' . $in_dirname . '/'));
            $this->setLastError(basename(__FILE__), __LINE__, $msgstr);
            return(false);
        }

        // main.ymlと他ファイルの依存有の場合でmain.ymlがない場合
        if(($in_etc_yml === true) && ($main_yml===false) && ($etc_yml === true)) {
               $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70003",
                                    array('./roles/' . $in_rolename . '/' . $in_dirname . '/'));
               $this->setLastError(basename(__FILE__), __LINE__, $msgstr);
               return(false);
        }

        return(true);
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F00
    // 処理内容
    //   default変数ファイルとastroll_readmeファイルを読み込み変数を確認（型と具体値）し、メンバ変数へ格納する
    //   読替表での変数名の変換も行う
    // パラメータ
    //   $fullpath:       roleディレクトリ
    //   $rolename:       ロール名
    //
    // 戻り値
    //   true: 正常 false:異常
    ////////////////////////////////////////////////////////////////////////////////
    function analyzeVarsStructure($fullpath, $rolename) {
        // 該当ロールの読替表の読込みに必要な変数の初期化
        $eachRole_ITA2User_var_list = array();
        $eachRole_User2ITA_var_list = array();
        $errmsg            = "";

        // 該当ロールの読替表のファイル名生成
        $translation_table_file = $this->baseDir . "/astroll_translation-table_" . $rolename . ".txt";

        // 該当ロールの読替表のファイルの有無判定
        if((file_exists($translation_table_file) === true) &&
           (is_file($translation_table_file) === true)) {
            // 該当ロールの読替表を読込
            $ret = $this->readTranslationFile($translation_table_file,
                                              $eachRole_ITA2User_var_list,
                                              $eachRole_User2ITA_var_list,
                                              $errmsg);
            if($ret === false) {
                $this->setLastError(basename(__FILE__), __LINE__, $errmsg);
                return false;
            }
        }

        // 読替変数の情報を退避
        $this->ITA2User_var_list[$rolename] = $eachRole_ITA2User_var_list;
        $this->User2ITA_var_list[$rolename] = $eachRole_User2ITA_var_list;

        // ITA読替変数がロールを跨いで問題なく登録されているか確認

        // defaults=>main.ymlのファイル名
        $defvarfile = $fullpath . "/main.yml";

        // defaults=>main.ymlのデータ読込
        $dataString = file_get_contents($defvarfile);

        // defaults=>main.ymlから変数取得
        $chkObj = new DefaultVarsFileAnalyzer($this->lv_objMTS);
        $vars_list = array();
        $errmsg = "";
        $varsval_list = array();
        $nested_vars_list    = array();

        // Spycモジュールの読み込み
        $ret = $chkObj->loadSpycModule($errmsg);
        if($ret === false) {
            $this->setLastError(basename(__FILE__), __LINE__, $errmsg);
            return false;
        }

        $parent_vars_list = array();
        $ret = $chkObj->FirstAnalysis($dataString,
                                      $parent_vars_list,
                                      $rolename,
                                      "/roles/" . $rolename . "/defaults/main.yml",
                                      $eachRole_ITA2User_var_list,
                                      $eachRole_User2ITA_var_list,
                                      $errmsg,
                                      $this->rolePkgName);
        if($ret === false) {
            // defaults=>main.ymlからの変数取得失敗
            $this->setLastError(basename(__FILE__), __LINE__, $errmsg);
            return false;
        }

        $ret = $chkObj->MiddleAnalysis($parent_vars_list,
                                       $rolename,
                                       "/roles/" . $rolename . "/defaults/main.yml",
                                       $errmsg,
                                       $this->rolePkgName);
        if($ret === false) {
            // defaults=>main.ymlからの変数取得失敗
            $this->setLastError(basename(__FILE__), __LINE__, $errmsg);
            return false;
        }

        // 一時ファイル名
        $tmp_file_name  = "/tmp/AnsibleTowerDefaultsVarsFile_" . getmypid() . ".yaml";

        $ret = $chkObj->LastAnalysis($tmp_file_name,
                                     $parent_vars_list,
                                     $vars_list,
                                     $varsval_list,
                                     $nested_vars_list,
                                     $rolename,
                                     "/roles/" . $rolename . "/defaults/main.yml",
                                     $errmsg,
                                     $this->rolePkgName);
        if($ret === false) {
            // defaults=>main.ymlからの変数取得失敗
            $this->setLastError(basename(__FILE__), __LINE__, $errmsg);
            return false;
        }

        // defaultsファイルに定義されている変数(親)を取り出す
        $all_parent_vars_list = array();
        for($idx=0; $idx < count($parent_vars_list); $idx++) {
            $all_parent_vars_list[$parent_vars_list[$idx]['VAR_NAME']] = 0;
        }

        unset($chkObj);

$this->debuglog(__LINE__,"default変数定義ファイル 変数リスト\n"    . print_r($vars_list, true));
$this->debuglog(__LINE__,"default変数定義ファイル 具体値リスト\n"  . print_r($varsval_list, true));
$this->debuglog(__LINE__,"多次元default変数定義ファイル 具体値リスト\n" . print_r($nested_vars_list, true));

        $user_vars_file = $this->baseDir . "/astroll_readme_" . $rolename . ".yml";

        // ユーザー定義変数ファイルの有無判定
        if((file_exists($user_vars_file) === true) &&
           (is_file($user_vars_file) === true)) {
            // ユーザー定義変数ファイルのデータ読込
            $dataString = file_get_contents($user_vars_file);

            // ユーザー定義変数ファイルから変数取得
            $chkObj = new DefaultVarsFileAnalyzer($this->lv_objMTS);
            $user_vars_list = array();
            $errmsg = "";
            $user_varsval_list = array();
            $user_nested_vars_list = array();

            $parent_vars_list = array();
            $ret = $chkObj->FirstAnalysis($dataString,
                                          $parent_vars_list,
                                          $rolename, 
                                          "/astroll_readme_" . $rolename . ".yml",
                                          $eachRole_ITA2User_var_list,
                                          $eachRole_User2ITA_var_list,
                                          $errmsg,
                                          $this->rolePkgName);
            if($ret === false) {
                // defaults=>main.ymlからの変数取得失敗
                $this->setLastError(basename(__FILE__), __LINE__, $errmsg);
                return false;
            }

            $ret = $chkObj->MiddleAnalysis($parent_vars_list,
                                           $rolename,
                                           "/astroll_readme_" . $rolename . ".yml",
                                           $errmsg,
                                           $this->rolePkgName);
            if($ret === false) {
                // defaults=>main.ymlからの変数取得失敗
                $this->setLastError(basename(__FILE__), __LINE__, $errmsg);
                return false;
            }

            // 一時ファイル名
            $tmp_file_name  = "/tmp/AnsibleTowerDefaultsVarsFile_" . getmypid() . ".yaml";

            $ret = $chkObj->LastAnalysis($tmp_file_name,
                                         $parent_vars_list,
                                         $user_vars_list,
                                         $user_varsval_list,
                                         $user_nested_vars_list,
                                         $rolename,
                                         "/astroll_readme_" . $rolename . ".yml",
                                         $errmsg,
                                         $this->rolePkgName);
            if($ret === false) {
                // defaults=>main.ymlからの変数取得失敗
                $this->setLastError(basename(__FILE__), __LINE__, $errmsg);
                return false;
            }

            // astroll readmeに定義されている変数(親)を取り出す。
            for($idx=0; $idx < count($parent_vars_list); $idx++) {
                $all_parent_vars_list[$parent_vars_list[$idx]['VAR_NAME']] = 0;
            }

            // 読替表の任意変数がデフォルト変数定義ファイルやastroll Readmeファイルに登録されているか判定する。
            $ret = $this->chkTranslationVars($all_parent_vars_list, $eachRole_User2ITA_var_list,
                                        basename($translation_table_file), $errmsg);
            if($ret === false) {
                $this->setLastError(basename(__FILE__), __LINE__, $errmsg);
                return false;
            }

$this->debuglog(__LINE__,"ユーザー変数定義ファイル 変数リスト\n"   . print_r($user_vars_list, true));
$this->debuglog(__LINE__,"ユーザー変数定義ファイル 具体値リスト\n" . print_r($user_varsval_list, true));
$this->debuglog(__LINE__,"多次元ユーザー変数定義ファイル 具体値リスト\n" . print_r($user_nested_vars_list, true));

            // default変数定義ファイルの変数情報とユーザー定義変数ファイルの変数情報をマージする。
            $this->margeDefaultVarsList($vars_list     , $varsval_list,
                                          $user_vars_list, $user_varsval_list,
                                          $nested_vars_list, $user_nested_vars_list );
            unset($chkObj);
        }

$this->debuglog(__LINE__,"マージ後の 変数リスト\n"    . print_r($vars_list, true));
$this->debuglog(__LINE__,"マージ後の 具体値リスト\n"  . print_r($varsval_list, true));
$this->debuglog(__LINE__,"マージ後の 多次元 具体値リスト\n" . print_r($nested_vars_list, true));

        //一般・配列変数定義 に変数の情報を登録
        $this->vars_list[$rolename] = $vars_list;

        //一般・配列変数定義 の具体値情報を登録
        $this->varsVal_list[$rolename] = $varsval_list;

        //多段変数定義一覧 の情報を登録
        $this->nestedVars_list[$rolename] = $nested_vars_list;

    }

    ////////////////////////////////////////////////////////////////////////////////
    // F0011
    // 処理内容
    //   読替表より変数の情報を取得する。
    //
    // パラメータ
    //   $in_filepath:            読替表ファイルパス
    //   $ina_ITA2User_var_list:  読替表の変数リスト ITA変数=>ユーザ変数
    //   $ina_User2ITA_var_list:  読替表の変数リスト ユーザ変数=>ITA変数
    //   $in_errmsg:              エラーメッセージリスト
    //
    // 戻り値
    //   true:   正常
    //   false:  異常
    ////////////////////////////////////////////////////////////////////////////////
    function readTranslationFile($in_filepath, &$ina_ITA2User_var_list, &$ina_User2ITA_var_list, &$in_errmsg){
        $in_errmsg = "";
        $ret_code  = true;
        $dataString = file_get_contents($in_filepath);
        $line = 0;
        // 入力データを行単位に分解
        $arry_list = explode("\n", $dataString);
        foreach($arry_list as $strSourceString){
            $line = $line + 1;
            // コメント行は読み飛ばす。
            if(mb_strpos($strSourceString, "#", 0, "UTF-8") === 0){
                continue;
            }
            // 空行を読み飛ばす。
            if(strlen(trim($strSourceString)) == 0){
                continue;
            }
            // 読替変数の構文を確認
            // LCA_[0-9,a-Z_*]($s*):($s+)playbook内で使用している変数名
            // 読替変数名の構文判定
            $ret = preg_match_all("/^(\s*)LCA_[a-zA-Z0-9_]*(\s*):(\s+)/", $strSourceString, $ita_var_match);
            if($ret == 1){
                // :を取除き、読替変数名取得
                $ita_var_name    = trim(str_replace(':','',$ita_var_match[0][0]));
                // 任意変数を取得
                $user_var_name = trim(preg_replace('/^(\s*)LCA_[a-zA-Z0-9_]*(\s*):(\s+)/', '', $strSourceString));
                if(strlen($user_var_name) != 0){
                    // 任意変数がVAR_でないことを判定
                    $ret = preg_match_all("/^VAR_/", $user_var_name, $user_var_match);
                    if($ret == 1){
                        if(strlen($in_errmsg) != 0) $in_errmsg .= "\n";
                        $in_errmsg = $in_errmsg . $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-5000000",array(basename($in_filepath),$line));
                        $ret_code = false;
                        continue;
                    }
                    // 任意変数が文字列になっているか
                    $ret = preg_match_all("/^(\S+)$/",$user_var_name ,$user_var_match);
                    if($ret != 1){
                        if(strlen($in_errmsg) != 0) $in_errmsg .= "\n";
                        $in_errmsg = $in_errmsg . $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-5000001",array(basename($in_filepath),$line));
                        $ret_code = false;
                        continue;
                    }
                }
                else{
                    if(strlen($in_errmsg) != 0) $in_errmsg .= "\n";
                    $in_errmsg = $in_errmsg . $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-5000001",array(basename($in_filepath),$line));
                    $ret_code = false;
                    continue;
                }
            }
            else{
                if(strlen($in_errmsg) != 0) $in_errmsg .= "\n";
                $in_errmsg = $in_errmsg . $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-5000002",array(basename($in_filepath),$line));
                $ret_code = false;
                continue;
            }
            // 任意変数が重複登録の二重登録判定
            if(@count($ina_User2ITA_var_list[$user_var_name]) != 0){
                if(strlen($in_errmsg) != 0) $in_errmsg .= "\n";
                $in_errmsg = $in_errmsg . $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-5000003",array(basename($in_filepath),$user_var_name));
                $ret_code = false;
                continue;
            }
            else{
                $ina_User2ITA_var_list[$user_var_name] = $ita_var_name;
            }
            // 読替変数が重複登録の二重登録判定
            if(@count($ina_ITA2User_var_list[$ita_var_name]) != 0){
                if(strlen($in_errmsg) != 0) $in_errmsg .= "\n";
                $in_errmsg = $in_errmsg . $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-5000004",array(basename($in_filepath),$ita_var_name));
                $ret_code = false;
                continue;
            }
            else{
                $ina_ITA2User_var_list[$ita_var_name] = $user_var_name;
            }
        }
        return $ret_code;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F0012
    // 処理内容
    //   読替表の任意変数がデフォルト変数定義ファイルやastroll Readmeファイルに登録されているか判定する。
    //
    // パラメータ
    //   $ina_all_parent_vars_list:  デフォルト変数定義ファイルやastroll Readmeファイルに登録されている変数リスト
    //   $ina_User2ITA_var_list:     読替表の変数リスト ユーザ変数=>ITA変数
    //   $in_translation_table_file: 読替表ファイル
    //   $in_errmsg:                 エラーメッセージリスト
    //
    // 戻り値
    //   true:   正常
    //   false:  異常
    ////////////////////////////////////////////////////////////////////////////////
    function chkTranslationVars($ina_all_parent_vars_list,$ina_User2ITA_var_list,$in_translation_table_file,&$in_errmsg){
        $ret_code   = true;
        $in_errmsg  = "";
        foreach ($ina_User2ITA_var_list as $user_var_name => $rep_var_name){
            if(@count($ina_all_parent_vars_list[$user_var_name])==0){
                if(strlen($in_errmsg) != 0) $in_errmsg .= "\n";
                {
                    $in_errmsg = $in_errmsg . $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-5000005",array(basename($in_translation_table_file),$user_var_name));
                    $ret_code = false;
                    continue;
                }
            }
        }
        return $ret_code;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // 処理内容
    //   ロールパッケージファイルパス(ZIP)を取得
    //
    // パラメータ
    //   $baseDirPath:      ロールパッケージファイルディレクトリ名
    //   $pkeyId:           項番
    //   $filename:         ロールパッケージファイル名(ZIP)
    //
    // 戻り値
    //   ZIPファイルパス
    ////////////////////////////////////////////////////////////////////////////////
    function getAnsible_RolePackage_filePath($baseDirPath, $pkeyId, $filename) {

        // sible実行時の子Playbookファイル名は Pkey(10桁)-子Playbookファイル名 する。
        $filePath = $baseDirPath . '/' .
                    addPadding($pkeyId) . '/' .
                    $filename;

        // ロールパッケージファイル名(ZIP)の存在確認
        if( file_exists($filePath) === false ) {
            $errmsg = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70005");
            $errmsg = "No such file.: $filePath";
            $this->setLastError(basename(__FILE__), __LINE__, $errmsg);
            return false;
        }

        $this->zipFilePath = $filePath;
        return true;
    }

    function getAnsible_RolePackage_filePath_tmp($tmpfileFullPath) {

        // ロールパッケージファイル名(ZIP)の存在確認
        if( file_exists($tmpfileFullPath) === false ) {
            $errmsg = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70005");
            $errmsg = "No such file.: $tmpfileFullPath";
            $this->setLastError(basename(__FILE__), __LINE__, $errmsg);
            return false;
        }

        $this->zipFilePath = $tmpfileFullPath;
        return true;
    }

    function debuglog($line,$msg) {
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F1002
    // 処理内容
    //   ロールパッケージ内のデフォルト変数ファイルで定義されている配列変数の
    //   構造が一致しているか判定
    //
    // パラメータ
    //   $ina_vars_list:            defalte変数ファイルの変数リスト格納
    //                                非配列変数 ina_vars_list[ロール名][変数名] = 0;
    //                                配列変数   ina_vars_list[ロール名][変数名] = array(配列変数名, ....)
    //   $ina_def_nested_vars_list:  defalte変数ファイルの多次元変数リスト格納
    //   $structErrVarsList:        ロールパッケージ内で使用している変数で構造が違う変数のリスト
    //                                structErrVarsList[変数名][ロールパッケージ名][ロール名]
    //
    // 戻り値
    //   true:   正常
    //   false:  異常
    ////////////////////////////////////////////////////////////////////////////////
    function chkVarsStruct( $ina_vars_list, $ina_def_nested_vars_list, &$structErrVarsList){
         $ret_code = true;
         $in_err_vars_list = array();

         // 多次元変数をKeyに他ロールに多次元変数以外の変数があるか判定
         foreach($ina_def_nested_vars_list as $role_name => $vars_list){
             foreach($vars_list as $var_name => $chl_vars_list){
                 // 他のロールで同じ変数名で構造が異なるのものがあるか確認
                 foreach($ina_vars_list as $chk_role_name => $chk_vars_list){
                     if($role_name == $chk_role_name){
                         // 同一のロール内のチェックはスキップする。
                         continue;
                     }
                     if(@count($ina_vars_list[$chk_role_name][$var_name]) != 0){
                         // エラーになった変数とロールを退避
                         $structErrVarsList[$var_name][$chk_role_name] = 0;
                         $structErrVarsList[$var_name][$role_name]     = 0;
                         $ret_code = false;
                     }
                 }
             }
         }
         // 同じ多次元変数が他ロールにある場合に構造が同じか判定する。
         foreach($ina_def_nested_vars_list as $role_name => $vars_list){
             foreach($vars_list as $var_name => $chl_vars_list){
                 foreach($ina_def_nested_vars_list as $chk_role_name => $chk_vars_list){
                     if($role_name == $chk_role_name){
                         // 同一のロール内のチェックはスキップする。
                         continue;
                     }
                     // 他ロールに同じ多次元変数がある場合
                     if(@count($ina_def_nested_vars_list[$chk_role_name][$var_name]) != 0){
                         // 多次元構造を比較する。
                         $diff_vars_list = array();
                         $diff_vars_list[0] = $ina_def_nested_vars_list[$role_name][$var_name]['DIFF_ARRAY'];
                         $diff_vars_list[1] = $ina_def_nested_vars_list[$chk_role_name][$var_name]['DIFF_ARRAY'];
                         $error_code = "";
                         $line       = "";
                         $ret = chkInnerArrayStructure($diff_vars_list, $error_code,$line);
                         if($ret === false){
                             // エラーになった変数とロールを退避
                             $structErrVarsList[$var_name][$chk_role_name] = 0;
                             $structErrVarsList[$var_name][$role_name]     = 0;
                             $ret_code = false;
                         }
                     }
                 }
             }
         }
         // 変数検索  ロール=>変数名
         foreach($ina_vars_list as $role_name=>$vars_list){
             if( is_array($vars_list) ){
                 if(@count($vars_list) !== 0){
                     // 変数名リスト=>変数名
                     foreach($vars_list as $var_name=>$var_type){
                         // 多次元配列に同じ変数名があるか判定
                         if(@count($ina_def_nested_vars_list[$chk_role_name][$var_name])!==0){
                             // エラーになった変数とロールを退避
                             $structErrVarsList[$var_name][$chk_role_name] = 0;
                             $structErrVarsList[$var_name][$role_name]     = 0;
                             $ret_code = false;
                         }
                         // 他のロールで同じ変数名で構造が異なるのものがあるか確認
                         foreach($ina_vars_list as $chk_role_name=>$chk_vars_list){
                             if($role_name == $chk_role_name){
                                 // 同一のロール内のチェックはスキップする。
                                 continue;
                             }
                             
                             if(@count($ina_vars_list[$chk_role_name][$var_name])===0){
                                 // 同じ変数名なし
                                 continue;
                             }
                             else{
                                 // 配列変数以外の場合に一般変数と複数具体値変数の違いを判定
                                 if($ina_vars_list[$chk_role_name][$var_name] != 
                                    $ina_vars_list[$role_name][$var_name]){
                                     // エラーになった変数とロールを退避
                                     $structErrVarsList[$var_name][$chk_role_name] = 0;
                                     $structErrVarsList[$var_name][$role_name]     = 0;
                                     $ret_code = false;
                                 }
                             }
                         }
                     }
                }
            }
        }
        return $ret_code;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F1003
    // 処理内容
    //   指定されているロールパッケージ内のデフォルト変数ファイルで定義されている配列変数の
    //   構造が一致しているか判定
    //
    // パラメータ
    //   $ina_vars_list:           defalte変数ファイルの変数リスト格納
    //                              非配列変数 ina_vars_list[ロールパッケージ名][ロール名][変数名] = 0;
    //                               配列変数   ina_vars_list[ロールパッケージ名][ロール名][変数名] = array(配列変数名, ....)
    //   $ina_def_nested_vars_list:  defalte変数ファイルの多次元変数リスト格納
    //   $structErrVarsList:       ロールパッケージ内で使用している変数で構造が違う変数のリスト
    //                               structErrVarsList[変数名][ロールパッケージ名][ロール名]
    //
    // 戻り値
    //   true:   正常
    //   false:  異常
    ////////////////////////////////////////////////////////////////////////////////
    static function chkAllVarsStruct($ina_vars_list, $ina_def_nested_vars_list, &$structErrVarsList) {

         $ret_code = true;

         // 多次元変数をKeyに他ロールに多次元変数以外の変数があるか判定
         foreach($ina_def_nested_vars_list as $pkg_name=>$role_list){
             foreach($role_list as $role_name=>$vars_list){
                 foreach($vars_list as $var_name=>$chl_vars_list){
                     // 他のロールで同じ変数名で構造が異なるのものがあるか確認
                     foreach($ina_vars_list as $chk_pkg_name=>$chk_role_list){
                         foreach($chk_role_list as $chk_role_name=>$chk_vars_list){
                             // 同一ロールパッケージ+ロールのチェックはスキップする。
                             if(($pkg_name == $chk_pkg_name) &&
                                ($role_name == $chk_role_name)){
                                 // 同一のロール内のチェックはスキップする。
                                 continue;
                             }
                             if(@count($ina_vars_list[$chk_pkg_name][$chk_role_name][$var_name]) != 0){
                                 // エラーになった変数とロールを退避
                                 $structErrVarsList[$var_name][$pkg_name][$role_name] = 0;
                                 $structErrVarsList[$var_name][$chk_pkg_name][$chk_role_name] = 0;
                                 $ret_code = false;
                             }
                         }
                     }
                 }
             }
         }
         // 同じ多次元変数が他ロールにある場合に構造が同じか判定する。
         foreach($ina_def_nested_vars_list as $pkg_name=>$role_list){
             foreach($role_list as $role_name=>$vars_list){
                 foreach($vars_list as $var_name=>$chl_vars_list){
                     foreach($ina_def_nested_vars_list as $chk_pkg_name=>$chk_role_list){
                         foreach($chk_role_list as $chk_role_name=>$chk_vars_list){
                             // 同一ロールパッケージ+ロールのチェックはスキップする。
                             if(($pkg_name == $chk_pkg_name) &&
                                ($role_name == $chk_role_name)){
                                 // 同一のロール内のチェックはスキップする。
                                 continue;
                             }
                             // 他ロールに同じ多次元変数がある場合
                             if(@count($ina_def_nested_vars_list[$chk_pkg_name][$chk_role_name][$var_name]) != 0){
                                 // 多次元構造を比較する。
                                 $diff_vars_list = array();
                                 $diff_vars_list[0] = $ina_def_nested_vars_list[$pkg_name][$role_name][$var_name]['DIFF_ARRAY'];
                                 $diff_vars_list[1] = $ina_def_nested_vars_list[$chk_pkg_name][$chk_role_name][$var_name]['DIFF_ARRAY'];
                                 $error_code = "";
                                 $line       = "";
                                 $ret = chkInnerArrayStructure($diff_vars_list,$error_code,$line);
                                 if($ret === false){
                                     // エラーになった変数とロールを退避
                                     $structErrVarsList[$var_name][$pkg_name][$role_name] = 0;
                                     $structErrVarsList[$var_name][$chk_pkg_name][$chk_role_name] = 0;
                                     $ret_code = false;
                                 }
                             }
                         }
                     }
                 }
             }
         }
         // 多次元変数以外をKeyに他ロールに多次元変数以外の変数があるか判定
         // 変数検索  ロールパッケージ名=>ロールリスト
         foreach($ina_vars_list as $pkg_name=>$role_list){
             // 変数検索  ロール=>変数名リスト
             foreach($role_list as $role_name=>$vars_list){
                 if( is_array($vars_list) ){
                     if(@count($vars_list) !== 0){
                         // 変数名リスト=>変数名
                         foreach($vars_list as $var_name=>$var_type){
                             // 多次元変数に同じ変数名があるか判定
                             if(@count($ina_def_nested_vars_list[$chk_pkg_name][$chk_role_name][$var_name])!=0){
                                 // エラーになった変数とロールを退避
                                 $structErrVarsList[$var_name][$pkg_name][$role_name] = 0;
                                 $structErrVarsList[$var_name][$chk_pkg_name][$chk_role_name] = 0;
                                 $ret_code = false;
                                 continue;
                             }
                             // 他ロールパッケージ変数検索  ロールパッケージ名=>ロールリスト
                             foreach($ina_vars_list as $chk_pkg_name=>$chk_role_list){
                                 // 他のロールで同じ変数名で構造が異なるのものがあるか確認
                                 foreach($chk_role_list as $chk_role_name=>$chk_vars_list){
                                     // 同一ロールパッケージ+ロールのチェックはスキップする。
                                     if(($pkg_name  == $chk_pkg_name) &&
                                        ($role_name == $chk_role_name)){
                                         continue;
                                     }
                                     if(@count($chk_vars_list[$var_name])===0){
                                         // 同じ変数名なし
                                         continue;
                                     }
                                     // 一般変数と複数具体値変数の違いを判定
                                     if($ina_vars_list[$chk_pkg_name][$chk_role_name][$var_name] !=
                                        $ina_vars_list[$pkg_name][$role_name][$var_name]){
                                         // エラーになった変数とロールを退避
                                         $structErrVarsList[$var_name][$pkg_name][$role_name] = 0;
                                         $structErrVarsList[$var_name][$chk_pkg_name][$chk_role_name] = 0;
                                         $ret_code = false;
                                     }
                                 }
                             }
                         }
                     }
                 }
             }
         }
         return $ret_code;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F1004
    // 処理内容
    //   配列変数の構造が違う場合のエラーメッセージを編集
    //
    // パラメータ
    //   $structErrVarsList:      ロールパッケージ内で使用している変数で構造が違う変数のリスト
    //                              in_err_vars_list[変数名][ロール名]
    //
    // 戻り値
    //   エラーメッセージ
    ////////////////////////////////////////////////////////////////////////////////
    function getVarsStructErrMsg(){
         $errmsg   = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70052");
         // $err_vars_list[変数名][ロール名]
         foreach($this->structErrVarsList as $err_var_name=>$err_role_list){
             $err_files = "";
             foreach($err_role_list as $err_role_name=>$dummy){
                 $err_files = $err_files . "roles/" . $err_role_name . "\n";
             }
             if($err_files != ""){
                 $errmsg = $errmsg . $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70053",
                                                                      array($err_var_name,$err_files));
             }
         }
         return $errmsg;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F1005
    // 処理内容
    //   配列変数の構造が違う場合のエラーメッセージを編集
    //
    // パラメータ
    //   $structErrVarsList:      ロールパッケージ内で使用している変数で構造が違う変数のリスト
    //                              in_err_vars_list[変数名][ロールパッケージ名][ロール名]
    //
    // 戻り値
    //   エラーメッセージ
    ////////////////////////////////////////////////////////////////////////////////
    static function getAllVarsStructErrMsg($structErrVarsList){
         $errmsg   = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70052");
         // $err_vars_list[変数名][ロールパッケージ名][ロール名]
         foreach($structErrVarsList as $err_var_name=>$err_pkg_list){
             $err_files = "";
             foreach($err_pkg_list as $err_pkg_name=>$err_role_list){
                 foreach($err_role_list as $err_role_name=>$dummy){
                     $err_files = $err_files . $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70055",
                                                                 array($err_pkg_name));

                     $err_files = $err_files . "roles/" . $err_role_name . "\n";
                 }
             }
             if($err_files != ""){
                 $errmsg = $errmsg . $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70053",
                                                             array($err_var_name,$err_files));
             }
         }
         return $errmsg;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F1006
    // 処理内容
    //   ロールパッケージ内のPlaybookで定義されている変数がデフォルト変数定義
    //   ファイルで定義されているか判定
    //
    // パラメータ
    //   $ina_play_vars_list:     ロールパッケージ内のPlaybookで定義している変数リスト
    //                              [role名][変数名]=0
    //   $ina_def_vars_list:      defalte変数ファイルの変数リスト
    //                             非配列変数 ina_vars_list[ロール名][変数名] = 0;
    //                              配列変数   ina_vars_list[ロール名][変数名] = array(配列変数名, ....)
    //
    // 戻り値
    //   true:   正常
    //   false:  異常
    ////////////////////////////////////////////////////////////////////////////////
    function chkDefVarsListPlayBookVarsList($ina_play_vars_list,
                                            $ina_def_vars_list,
                                            $ina_def_nested_vars_list,
                                            &$in_errmsg,
                                            $ina_system_vars) {

        $in_errmsg = "";
        $ret_code  = true;

        // ロールパッケージ内のPlaybookで定義している変数が無い場合は処理しない。
        if(count($ina_play_vars_list) === 0) {
            return $ret_code;
        }

        foreach($ina_play_vars_list as $role_name => $vars_list) {
            foreach($vars_list as $vars_name => $dummy) {
                // ITA独自変数はチェック対象外とする。
                if(in_array($vars_name, $ina_system_vars) === true) {
                    continue;
                }

                if(@count($ina_def_vars_list[$role_name][$vars_name]) === 0 &&
                    @count($ina_def_nested_vars_list[$role_name][$vars_name]) === 0) {
                        $in_errmsg = $in_errmsg . "\n"
                                    . $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70058", array($role_name, $vars_name));
                        $ret_code  = false;
                }
            }
        }
        return $ret_code;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F1007
    // 処理内容
    //   デフォルト定義変数ファイルとユーザー義変数ファイルに定義されている変数
    //   の情報をマージする。
    //
    //   $ina_vars_list:          デフォルト定義変数ファイル内に定義されている変数リスト
    //                             非配列変数 ina_vars_list[変数名]
    //                              配列変数   ina_vars_list[変数名] = array(配列変数名, ....)
    //   $ina_vars_val_list:      デフォルト定義変数ファイル内に定義されている変数具体値リスト
    //                              一般変数
    //                                $ina_vars_val_list[変数名][0]=具体値
    //                              複数具体値変数
    //                                $ina_vars_val_list[変数名][2][メンバー変数]=array(1=>具体値,2=>具体値....)
    //   $ina_user_vars_list:     ユーザー義変数ファイル内に定義されている変数リスト
    //                             非配列変数 ina_vars_list[変数名]
    //                              配列変数   ina_vars_list[変数名] = array(配列変数名, ....)
    //   $ina_user_vars_val_list: ユーザー義変数ファイル内に定義されている変数具体値リスト
    //                              一般変数
    //                                $ina_vars_val_list[変数名][0]=具体値
    //                              複数具体値変数
    //   $ina_nested_vars_list:    デフォルト定義変数ファイル内に定義されている多次元変数リスト
    //   $ina_user_nested_vars_list: ユーザー義変数ファイル内に定義されている多次元変数リスト
    //
    // 戻り値
    //   なし
    ////////////////////////////////////////////////////////////////////////////////
    function margeDefaultVarsList( &$ina_vars_list,     &$ina_vars_val_list,
                                   $ina_user_vars_list, $ina_user_vars_val_list,
                                   &$ina_nested_vars_list, $ina_user_nested_vars_list){
        if(@count($ina_user_vars_list) != 0){
            // ユーザー変数定義ファイルに登録されている変数をキーにループ
            foreach($ina_user_vars_list as $var_name=>$vars_list){
                // default変数定義ファイルに変数が登録されているか判定
                if(@count($ina_vars_list[$var_name]) != 0){
$this->debuglog(__LINE__,"[" . $var_name . "] ユーザー変数定義ファイルとdefault変数定義ファイルの両方にある変数のルート\n");
                    // default変数定義ファイルに変数が登録されている
                
                    // 変数の型を判定する。
                    if($ina_vars_list[$var_name] != $ina_user_vars_list[$var_name]){
                        // 変数の型が一致しない場合はdefault変数定義ファイルの変数具体値情報から該当変数の情報を削除する。
                        unset($ina_vars_val_list[$var_name]);
                    }
                    // default変数定義ファイルの変数情報から該当変数の情報を削除する。
                    unset($ina_vars_list[$var_name]);
               
                    // default変数定義ファイルの変数情報にユーザー変数定義ファイルに
                    // 登録されている変数情報を追加
                    $ina_vars_list[$var_name] = $ina_user_vars_list[$var_name];

                    // ユーザー変数定義ファイルの変数具体値情報は使わない

                    // ユーザー変数定義ファイルの変数情報から削除
                    unset($ina_user_vars_list[$var_name]);
                }
                else{
                    // default変数定義ファイルに変数が登録されていない

                    // default変数定義ファイルに多次元変数として登録されているか判定
                    if(@count($ina_nested_vars_list[$var_name]) != 0){
$this->debuglog(__LINE__,"[" . $var_name . "] ユーザー変数定義ファイルとdefault多次元変数定義ファイルの両方にある変数のルート\n");
                        // default変数定義ファイルの多次元変数の情報を削除
                        unset($ina_nested_vars_list[$var_name]);
                    }
                    else{
$this->debuglog(__LINE__,"[" . $var_name . "] ユーザー変数定義ファイルにあるがdefault変数定義ファイルのない変数のルート\n");
                    }

                    // default変数定義ファイルの変数情報にユーザー変数定義ファイルに
                    // 登録されている変数情報を追加
                    $ina_vars_list[$var_name] = $ina_user_vars_list[$var_name];

                    // ユーザー変数定義ファイルの変数情報から削除
                    unset($ina_user_vars_list[$var_name]);

                    // ユーザー変数定義ファイルの変数具体値情報は使わない
                }
            }
        }
        if(@count($ina_user_nested_vars_list) != 0){
            // ユーザー変数定義ファイルに登録されている多次元変数をキーにループ
            foreach($ina_user_nested_vars_list as $var_name=>$vars_list){
                // default変数定義ファイルに多次元変数が登録されているか判定
                if(@count($ina_nested_vars_list[$var_name]) != 0){
                    // default変数定義ファイルに多次元変数が登録されている
                
                    // 変数構造が同じか判定する。
                    // 多次元構造を比較する。
                    $diff_vars_list = array();
                    $diff_vars_list[0] = $ina_nested_vars_list[$var_name]['DIFF_ARRAY'];
                    $diff_vars_list[1] = $ina_user_nested_vars_list[$var_name]['DIFF_ARRAY'];
                    $error_code = "";
                    $line       = "";
                    $ret = chkInnerArrayStructure($diff_vars_list,$error_code,$line);
                    if($ret === false){
                        // 変数構造が一致しない
                        // ユーザー変数定義ファイルの情報をdefault変数定義ファイルに設定
                        unset($ina_nested_vars_list[$var_name]);
                        $ina_nested_vars_list[$var_name] = $ina_user_nested_vars_list[$var_name];
    
                        // 具体値はなしにする。
                        unset($ina_nested_vars_list[$var_name]['VAR_VALUE']);
                        $ina_nested_vars_list[$var_name]['VAR_VALUE'] = array();
$this->debuglog(__LINE__,"[" . $var_name . "] ユーザー多次元変数定義ファイルとdefault多次元変数定義ファイルの両方にあり型が一致しない変数のルート\n");
                    }
                    else{
$this->debuglog(__LINE__,"[" . $var_name . "] ユーザー多次元変数定義ファイルとdefault多次元変数定義ファイルの両方にあり型が一致する変数のルート\n");
                        // 変数の構造が同じなのでdefault変数定義ファイルの内容をそのまま使う
                    }
 
                    // ユーザー変数定義ファイルの変数情報を削除する。
                    unset($ina_user_nested_vars_list[$var_name]);
                }
                else{
                    // default変数定義ファイルに多次元変数が登録されていない

                    // default変数定義ファイルに変数が登録されているか判定
                    if(@count($ina_vars_list[$var_name]) != 0){
$this->debuglog(__LINE__,"[" . $var_name . "] ユーザー多次元変数定義ファイルとdefault変数定義ファイルの両方にある変数のルート\n");
                        // default変数定義ファイルに変数が登録されている
                    
                        // default変数定義ファイルの変数情報から該当変数の情報を削除する。
                        unset($ina_vars_list[$var_name]);

                        // default変数定義ファイルの変数具体値情報から該当変数の情報を削除する。
                        unset($ina_vars_val_list[$var_name]);
                
                        // default変数定義ファイルの変数情報にユーザー変数定義ファイルに
                        // 登録されている多次元変数情報を追加
                        $ina_nested_vars_list[$var_name] = $ina_user_nested_vars_list[$var_name];
    
                        // ユーザー変数定義ファイルの変数具体値情報は使わない                
                        unset($ina_nested_vars_list[$var_name]['VAR_VALUE']);
                        $ina_nested_vars_list[$var_name]['VAR_VALUE'] = array();

                        // ユーザー変数定義ファイルの変数情報から削除
                        unset($ina_user_vars_list[$var_name]);
                    }
                    else{
$this->debuglog(__LINE__,"[" . $var_name . "] ユーザー多次元変数定義ファイルにのみある変数のルート\n");

                        // default変数定義ファイルに変数が登録されていない

                        // default変数定義ファイルの変数情報にユーザー変数定義ファイルに
                        // 登録されている変数情報を追加
                        $ina_nested_vars_list[$var_name] = $ina_user_nested_vars_list[$var_name];
    
                        // 具体値はなしにする。
                        unset($ina_nested_vars_list[$var_name]['VAR_VALUE']);
                        $ina_nested_vars_list[$var_name]['VAR_VALUE'] = array();

                        // ユーザー変数定義ファイルの変数情報を削除する。
                        unset($ina_user_nested_vars_list[$var_name]);
                    }
                }
            }
        }
        return;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F1029
    // 処理内容
    //   読替表に定義されている読替変数と任意変数の組合せが一意か判定
    //
    // パラメータ
    //   $ina_ITA2User_var_list:   読替表の変数リスト ITA変数=>ユーザ変数
    //   $ina_User2ITA_var_list:   読替表の変数リスト ユーザ変数=>ITA変数
    //   $translateCombErrVarsList: ロールパッケージ内で使用している変数で構造が違う変数のリスト
    //
    // 戻り値
    //   true:   正常
    //   false:  異常
    ////////////////////////////////////////////////////////////////////////////////
    function chkTranslationTableVarsCombination($ina_ITA2User_var_list, $ina_User2ITA_var_list,&$translateCombErrVarsList){
        $ret_code = true;
        $translateCombErrVarsList = array();

        // 読替変数と任意変数の組合せが一意か確認する。
        // 読替変数をキーに読替変数と任意変数の組合せを確認
        foreach($ina_ITA2User_var_list as $pkg_name=>$role_list){
            foreach($role_list as $role_name=>$vars_list){
                foreach($vars_list as $ita_vars_name=>$user_vars_name){
                    foreach($ina_ITA2User_var_list as $chk_pkg_name=>$chk_role_list){
                        foreach($chk_role_list as $chk_role_name=>$chk_vars_list){
                            // 同一ロールパッケージ+ロールのチェックはスキップする。
                            if(($pkg_name == $chk_pkg_name) &&
                               ($role_name == $chk_role_name)){
                                // 同一のロール内のチェックはスキップする。
                                continue;
                            }
                            foreach($chk_vars_list as $chk_ita_vars_name=>$chk_user_vars_name){
                                if(($ita_vars_name == $chk_ita_vars_name) &&
                                   ($user_vars_name != $chk_user_vars_name)){
                                    // エラーになった変数とロールを退避
                                    $translateCombErrVarsList['USER_VAR'][$ita_vars_name][$pkg_name][$role_name] = $user_vars_name;
                                    $translateCombErrVarsList['USER_VAR'][$ita_vars_name][$chk_pkg_name][$chk_role_name] = $chk_user_vars_name;
                                    $ret_code = false;
                                } 
                                // 読替変数が同じ場合は、以降のチェックをスキップ
                                if($ita_vars_name == $chk_ita_vars_name){
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }
        // 任意変数をキーに読替変数と任意変数の組合せを確認
        foreach($ina_User2ITA_var_list as $pkg_name=>$role_list){
            foreach($role_list as $role_name=>$vars_list){
                foreach($vars_list as $user_vars_name=>$ita_vars_name){
                    foreach($ina_User2ITA_var_list as $chk_pkg_name=>$chk_role_list){
                        foreach($chk_role_list as $chk_role_name=>$chk_vars_list){
                            // 同一ロールパッケージ+ロールのチェックはスキップする。
                            if(($pkg_name == $chk_pkg_name) &&
                               ($role_name == $chk_role_name)){
                                // 同一のロール内のチェックはスキップする。
                                continue;
                            }
                            foreach($chk_vars_list as $chk_user_vars_name=>$chk_ita_vars_name){
                                if(($user_vars_name == $chk_user_vars_name) &&
                                   ($ita_vars_name != $chk_ita_vars_name)){
                                    // エラーになった変数とロールを退避
                                    $translateCombErrVarsList['ITA_VAR'][$user_vars_name][$pkg_name][$role_name] = $ita_vars_name;
                                    $translateCombErrVarsList['ITA_VAR'][$user_vars_name][$chk_pkg_name][$chk_role_name] = $chk_ita_vars_name;
                                    $ret_code = false;
                                }
                                // 読替変数が同じ場合は、以降のチェックをスキップ
                                if($user_vars_name == $chk_user_vars_name){
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $ret_code;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F1030
    // 処理内容
    //   読替表の読替変数と任意変数の組合せが一致していないエラーメッセージを編集
    //
    // パラメータ
    //   $in_pkg_flg:             パッケージ名表示有無
    //
    // 戻り値
    //   エラーメッセージ
    ////////////////////////////////////////////////////////////////////////////////
    function getTranslationTableCombinationErrMsg($in_pkg_flg){
         $errmsg = "";
         $errmsg   = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-5000006");

         if(@count($this->translateCombErrVarsList["USER_VAR"])!=0){
             foreach($this->translateCombErrVarsList["USER_VAR"]  as $ita_vars_name=>$pkg_list){
                 foreach($pkg_list as $pkg_name=>$role_list){
                     foreach($role_list as $role_name=>$user_vars_name){
                         if($in_pkg_flg === true){
                             $errmsg   = $errmsg . $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-5000009",array($pkg_name, $role_name, $ita_vars_name, $user_vars_name));
                         }
                         else{
                             $errmsg   = $errmsg . $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-5000007",array($role_name, $ita_vars_name, $user_vars_name));
                         }
                     }
                 }
             }
         }
         if(@count($this->translateCombErrVarsList["ITA_VAR"])!=0){
             foreach($this->translateCombErrVarsList["ITA_VAR"]  as $user_vars_name=>$pkg_list){
                 foreach($pkg_list as $pkg_name=>$role_list){
                     foreach($role_list as $role_name=>$ita_vars_name){
                         if($in_pkg_flg === true){
                             $errmsg   = $errmsg . $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-5000010",array($pkg_name, $role_name, $user_vars_name, $ita_vars_name));
                         }
                         else{
                             $errmsg   = $errmsg . $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-5000008",array($role_name, $user_vars_name, $ita_vars_name));
                         }
                     }
                 }
             }
         }
         return $errmsg;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F1031
    // 処理内容
    //   ロールパッケージから抜出した変数名を読替表の情報を置換える。
    //   任意変数=>読替変数
    //
    // パラメータ
    //   $ina_vars_list: ロールパッケージから抜出した変数情報
    //                   [ロール名][変数名].....
    //   $ina_User2ITA_var_list  読替表の変数リスト ユーザ変数=>ITA変数
    //
    // 戻り値
    //   エラーメッセージ
    ////////////////////////////////////////////////////////////////////////////////
    function applyTranslationTable(&$ina_vars_list, $ina_User2ITA_var_list) {
        $wk_ina_vars_list = array(); 
        foreach($ina_vars_list as $role_name => $var_list) {
            foreach($var_list as $vars_name => $info_list) {
                if(@count($ina_User2ITA_var_list[$role_name][$vars_name])==0) {
                    $wk_ina_vars_list[$role_name][$vars_name] = $info_list;
                    continue;
                }
                $ita_vars_name = $ina_User2ITA_var_list[$role_name][$vars_name];
                $wk_ina_vars_list[$role_name][$ita_vars_name] = $info_list;
            }
        }
        $ina_vars_list = $wk_ina_vars_list;
    }

    /* <START> ロールパッケージ内のPlaybookで定義されているグローバル変数がグローバル変数管理に存在するか判定する為の関数---------------------------------------------------------------- */
    function chkDefVarsListPlayBookGlobalVarsList(
         $ina_play_global_vars_list // ロールパッケージ内のPlaybookで定義している変数リスト （[role名][変数名]=0）
        ,$ina_global_vars_list      // グローバル変数管理の変数リスト
    ){
        global $g ;
        $in_errmsg = "";
        $ret_code  = true;
        if(count($ina_play_global_vars_list) == 0){

            return $ret_code;
        }
        foreach($ina_play_global_vars_list as $role_name=>$vars_list){
            foreach($vars_list as $vars_name=>$dummy){
                if(@count($ina_global_vars_list[$vars_name])===0){
                    $in_errmsg = $in_errmsg . "\n" . $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-6602601" , array($role_name,$vars_name));
                    $ret_code  = false;
                 }
             }
        }

        return array($ret_code , $in_errmsg);
    }
    /* < END > ロールパッケージ内のPlaybookで定義されているグローバル変数がグローバル変数管理に存在するか判定する為の関数---------------------------------------------------------------- */

}

////////////////////////////////////////////////////////////////////////////////
// F0013
// 処理内容
//   指定ディレクトリ配下のファイル一覧取得
//
// パラメータ
//   $dir:       ディレクトリ
//
// 戻り値
//   ファイル一覧
////////////////////////////////////////////////////////////////////////////////
function getFileList($dir) {
    $files = scandir($dir);
    $files = array_filter($files, function ($file) {
        return !in_array($file, array('.', '..'));
    });

    $list = array();
    foreach ($files as $file) {
        $fullpath = rtrim($dir, '/') . '/' . $file;
        $list[] = $fullpath;
        if (is_dir($fullpath)) {
            $list = array_merge($list, getFileList($fullpath));
        }
    }
    return $list;
}

