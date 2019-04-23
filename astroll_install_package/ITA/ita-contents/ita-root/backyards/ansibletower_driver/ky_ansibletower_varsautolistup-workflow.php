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
//      AnsibleTower変数自動更新
//
////////////////////////////////////////////////////////////////////////

// 起動しているshellの起動判定を正常にするための待ち時間
sleep(1);

const DUMMY_VALUE = 1; // HashArrayを作成し、Keyのみ使用したいためのダミーValue

////////////////////////////////
// ルートディレクトリを取得   //
////////////////////////////////
if(empty($root_dir_path)) {
    $root_dir_temp = array();
    $root_dir_temp = explode("ita-root", dirname(__FILE__));
    $root_dir_path = $root_dir_temp[0] . "ita-root";
}

require_once($root_dir_path . "/libs/backyardlibs/ansibletower_driver/setenv.php");
require_once($root_dir_path . "/libs/backyardlibs/ansibletower_driver/DBAccesser.php");
require_once($root_dir_path . "/libs/backyardlibs/ansibletower_driver/LogWriter.php");
require_once($root_dir_path . "/libs/backyardlibs/ansibletower_driver/MessageTemplateStorageHolder.php");
require_once($root_dir_path . "/libs/backyardlibs/ansibletower_driver/TableDefinitionsMaster.php");

////////////////////////////////
// ログ出力設定
////////////////////////////////
$log_output_dir = getenv('LOG_DIR');
if(empty($log_output_dir)) {
    $log_output_dir = $root_dir_path . "/logs/backyardlogs";
}
$log_file_prefix = basename( __FILE__, '.php' ) . "_";
$tmpVarTimeStamp = time();
$logfile = $log_output_dir . "/" . $log_file_prefix . date("Ymd",$tmpVarTimeStamp) . ".log";
ini_set('display_errors', "stderr");
ini_set('log_errors',     1);
ini_set('error_log',      $logfile);

$log_output_php = $root_dir_path . '/libs/backyardlibs/backyard_log_output.php';
$logger         = LogWriter::getInstance();
$log_level      = getenv('LOG_LEVEL'); // 'DEBUG';
$logger->setUp($log_output_php, $log_output_dir, $log_file_prefix, $log_level);

$msgTplStorage  = MessageTemplateStorageHolder::getMTS();

////////////////////////////////
// DB接続設定
////////////////////////////////
$db_access_user_id  = -121003;

////////////////////////////////
// ローカル変数(全体)宣言     //
////////////////////////////////
$warning_flag       = 0; // 警告フラグ(1：警告発生)
$error_flag         = 0; // 異常フラグ(1：異常発生)

////////////////////////////////
// 共通モジュールの呼び出し
////////////////////////////////
$php_req_gate_php    = '/libs/commonlibs/common_php_req_gate.php';
$aryOrderToReqGate = array('DBConnect'=>'LATE');
require ($root_dir_path . $php_req_gate_php );

$role_structure_php = '/libs/backyardlibs/ansibletower_driver/role_package/ItaAnsibleRoleStructure.php';
require_once ($root_dir_path . $role_structure_php);
$nestedVariableExpander_php = '/libs/backyardlibs/ansibletower_driver/vars_listup/nestedVariableExpander.php';

try {
    // 開始メッセージ
    $logger->debug("Start procedure.");

    ////////////////////////////////
    // DBコネクト                 //
    ////////////////////////////////
    $dbAccess = new DBAccesser($db_access_user_id);
    $dbAccess->connect();

    ////////////////////////////////
    // トランザクション開始       //
    ////////////////////////////////
    $logger->debug("Begin transaction.");
    if($dbAccess->beginTransaction() === false) {
        $warning_flag = 1;
        throw new Exception("Faild to begin transaction.");
    }

    ///////////////////////////////////////////////////
    // P0001                                         //
    // 関連シーケンスをロックする                    //
    //        デッドロック防止のために、昇順でロック //
    ///////////////////////////////////////////////////
    //----デッドロック防止のために、昇順でロック
    $aryTgtTableNames = array(
        "B_ANSTWR_VARS",
        "B_ANSTWR_PTN_VARS_LINK",
        "B_ANSTWR_ROLE",
        "B_ANSTWR_ROLE_VARS",
        "B_ANSTWR_DEFAULT_VARSVAL",
        "B_ANSTWR_NESTED_MEM_VARS",
        "B_ANSTWR_MAX_MEMBER_COL",
        "B_ANSTWR_NESTEDMEM_COL_CMB",
        "B_ANSTWR_TRANSLATE_VARS"
    );

    $dbAccess->lockRelationTableSequence($aryTgtTableNames);
    //デッドロック防止のために、昇順でロック----

    $logger->debug("Get ROLE_PACKAGE.");

    //////////////////////////////////////////////////////////////////////////////
    // P0002
    // ロールパッケージ管理からデータ取得
    //////////////////////////////////////////////////////////////////////////////
    // T0001
    $role_package_list = array();

    getRolePackageDB($role_package_list);
    // 異常時Exception

    $logger->trace("role_package_list dump.");
    $logger->trace(var_export($role_package_list, true));

    $logger->debug("Analyze each ZIP file.");

    //////////////////////////////////////////////////////////////////////////////
    // P0003
    // ロールパッケージファイル(ZIP)を解凍しロール名と変数名を取得
    //////////////////////////////////////////////////////////////////////////////
    $roleName_list = array();

    $defVars_list_byPkgId = array();
    $defNestedVars_list_byPkgId = array();

    $defVarsVal_list = array();

    $defVars_list_byPkgName = array();
    $defNestedVars_list_byPkgName = array();

    $ITA2User_TranslateVars_list = array();

    foreach($role_package_list as $role_package_id => $role_package_keyvalue) {
        $role_package_name = $role_package_keyvalue['name'];
        $role_package_file = $role_package_keyvalue['fileName'];

        $logger->trace("ROLE_PACKAGE_NAME: $role_package_name");

        // ロールパッケージファイル(ZIP)の解凍先
        $roledir  = "/tmp/AnsibleTowerZipvarget_" . getmypid();
        exec("/bin/rm -rf " . $roledir);

        /////////////////////////////////////////////////////////////////////
        // P0004
        // ロールパッケージファイルからロール名と変数名を取得
        /////////////////////////////////////////////////////////////////////
        $rolePkg_roleName_list = array();

        $rolePkg_defVars_list = array();

        $rolePkg_defVarsVal_list = array();
        $rolePkg_defNestedVars_list = array();

        $rolePkg_ITA2User_TranslateVars_list = array();

        $ret = getRolePackageData($role_package_id,
                                  $role_package_file,
                                  $role_package_name,
                                  $roledir,
                                  $rolePkg_roleName_list,
                                  $rolePkg_defVars_list,
                                  $rolePkg_defVarsVal_list,
                                  $rolePkg_defNestedVars_list,
                                  $rolePkg_ITA2User_TranslateVars_list);
        if($ret === false) {
            $logger->debug("Faild to get package infomation. Skip this package.: ID[ $role_package_id ] / NAME[ $role_package_name ]");
            $warning_flag = 1;

        } else {
            // rolesディレクトリ配下のロール名リスト返却
            $roleName_list[$role_package_id] = $rolePkg_roleName_list;

            // ロールパッケージファイル default定義 変数名リスト退避
            $defVars_list_byPkgId[$role_package_id] = $rolePkg_defVars_list;
            $defVars_list_byPkgName[$role_package_name] = $rolePkg_defVars_list;

            // ロールパッケージファイル default定義 多段変数リスト退避
            $defNestedVars_list_byPkgId[$role_package_id]  = $rolePkg_defNestedVars_list;
            $defNestedVars_list_byPkgName[$role_package_name] = $rolePkg_defNestedVars_list;

            // ロールパッケージファイル default定義変数の具体値リスト
            $defVarsVal_list[$role_package_id] = $rolePkg_defVarsVal_list;

            $ITA2User_TranslateVars_list[$role_package_id] = $rolePkg_ITA2User_TranslateVars_list;

            $logger->trace("roleName_list: " .                  var_export($roleName_list, true));
            $logger->trace("defVars_list_byPkgId: " .           var_export($defVars_list_byPkgId, true));
            $logger->trace("defNestedVars_list_byPkgId: " .     var_export($defNestedVars_list_byPkgId, true));
            $logger->trace("defVarsVal_list: " .                var_export($defVarsVal_list, true));
            $logger->trace("ITA2User_TranslateVars_list: " .    var_export($ITA2User_TranslateVars_list, true));
        }

        exec("/bin/rm -rf " . $roledir);
    }

    // 全ロールパッケージファイルで読替変数と任意変数の組合せを確認は しない （※仕様）

    ///////////////////////////////////////////////////////////////////////////
    // P0005-2
    // 全ロールパッケージファイルで定義変数で変数の構造が違うものをリストアップ
    ///////////////////////////////////////////////////////////////////////////
    $logger->trace("Vars Structure Check for All Package.");

    $structErrVarsList = array();

    $ret = ItaAnsibleRoleStructure::chkAllVarsStruct($defVars_list_byPkgName, $defNestedVars_list_byPkgName, $structErrVarsList);
    if($ret === false) {
        if($log_level === 'DEBUG') {
            // エラーになった変数情報を設定
            $errmag = ItaAnsibleRoleStructure::getAllVarsStructErrMsg($structErrVarsList);
            $logger->debug(basename(__FILE__), __LINE__, $errmag);
        }
    }

    $logger->trace("structErrVarsList: " . var_export($structErrVarsList, true));

    ///////////////////////////////////////////////////////////////////////////
    // P0006
    // ロールパッケージファイル ロール名リストのロール名をロール管理に反映
    ///////////////////////////////////////////////////////////////////////////
    $logger->trace("  *****  Roles Update  *****");
    $roleName2roleId = array();

    roleTableUpdate($roleName_list);

    $logger->trace("roleName2roleId dump");
    $logger->trace(var_export($roleName2roleId, true));

    ///////////////////////////////////////////////////////////////////////////
    // P0011-2
    // 読替表の内容をロールパッケージ毎読替表テーブルに反映する。
    ///////////////////////////////////////////////////////////////////////////
    $logger->trace("  *****  TranslateVars Update  *****");
    translateTableUpdate($ITA2User_TranslateVars_list);

    ///////////////////////////////////////////////////////////////////////////
    // P0013
    // ロールパッケージファイル内の変数名を変数管理に反映
    ///////////////////////////////////////////////////////////////////////////
    $varsName2varsId = array();
    $otherUserLastUpdate_vars_list = array();

    $logger->trace("  *****  Vars Update  *****");
    varsTableUpdate($defVars_list_byPkgId, $defNestedVars_list_byPkgId);

    $logger->trace("varsName2varsId dump");
    $logger->trace(var_export($varsName2varsId, true));
    $logger->trace("otherUserLastUpdate_vars_list dump");
    $logger->trace(var_export($otherUserLastUpdate_vars_list, true));

    ///////////////////////////////////////////////////////////////////////////
    // P0017
    // 多次元変数メンバー変数を多次元変数メンバー管理に登録する。
    ///////////////////////////////////////////////////////////////////////////
    $logger->trace("  *****  MemberVars Update  *****");
    memberVarsTableUpdate($defNestedVars_list_byPkgId);

    ///////////////////////////////////////////////////////////////////////////
    // P0022
    // 多次元変数最大繰返数管理の更新
    // P0023
    // 多次元変数配列組合せ管理の更新
    ///////////////////////////////////////////////////////////////////////////
    $logger->trace("  *****  max_member_col/nestedmem_col_cmb Update  *****");
    require_once ($root_dir_path . $nestedVariableExpander_php);

    ///////////////////////////////////////////////////////////////////////////
    // P0007
    // ロール内のPlaybookで使用している変数名をロール変数管理に反映
    ///////////////////////////////////////////////////////////////////////////
    $logger->trace("  *****  RoleVars Update  *****");
    roleVarsTableUpdate($defVars_list_byPkgId, $defNestedVars_list_byPkgId);

    ///////////////////////////////////////////////////////////////////////////
    // P0024
    // デフォルト変数定義ファイルに定義されている変数の具体値をロール変数具体値管理に反映
    ///////////////////////////////////////////////////////////////////////////
    $logger->trace("  *****  VarsVal Update  *****");
    varsValTableUpdate($defVarsVal_list, $defNestedVars_list_byPkgId);

    /////////////////////////////////////////////////////////////////////////////////
    // P0028
    // 作業パターン変数紐付管理からデータを取得
    /////////////////////////////////////////////////////////////////////////////////
    $logger->trace("  *****  PatternVars Update  *****");
    patternVarsTableUpdate();

    //   コミット
    $logger->debug('  [Process] Commit  ');

    $r = $dbAccess->commit();
    if(!$r) {
        $warning_flag = 1;
        throw new Exception('  Commit failed.  ');
    }

} catch(Exception $e) {

    $error_flag = 1;

    $logger->error('  An exception occurred  ');

    $logger->error($e->getMessage());
    $logger->trace($e->getTraceAsString());

    // トランザクションが発生しそうなロジックに入ってからのexceptionの場合は
    // 念のためロールバック/トランザクション終了
    if($dbAccess->inTransaction()) {
        // ロールバック
        if($dbAccess->rollback() === true) {
            $logger->error('  [Process] Roll back'  );
        } else {
            $logger->error('  Rollback failed.  ');
        }
    }
}

////////////////////////////////
//// 結果出力
////////////////////////////////
// 処理結果コードを判定してアクセスログを出し分ける
if($error_flag != 0) {
    // 終了メッセージ
    $logger->debug('End procedure (error)');
    exit(2);
} elseif($warning_flag != 0) {
    // 終了メッセージ
    $logger->debug('End procedure (warning)');
    exit(2);
} else {
    // 終了メッセージ
    $logger->debug('End procedure (normal)');
    exit(0);
}

// end Main Logic


////////////////////////////////////////////////////////////////////////////////
// F0001
// 処理内容
//   ロールパッケージ管理からデータ取得
//   
// パラメータ
//   $l_role_package_list:  
//            ロールパッケージ管理 データリスト
//            [ROLE_PACKAGE_ID][ROLE_PACKAGE_NAME] = ROLE_PACKAGE_FILE
// 
// 戻り値
//   True:正常　　False:異常
////////////////////////////////////////////////////////////////////////////////
function getRolePackageDB(&$l_role_package_list) {

    global $dbAccess;
    global $logger;
    global $warning_flag;

    $rows = $dbAccess->selectRows("B_ANSTWR_ROLE_PACKAGE");

    foreach($rows as $row) {
        $id       = $row["ROLE_PACKAGE_ID"];
        $name     = $row["ROLE_PACKAGE_NAME"];
        $fileName = $row['ROLE_PACKAGE_FILE'];
        if(strlen($fileName) == 0) {
            $logger->debug("Not uploaded ZIP file. Skip this record.: ID[ $id ] / NAME[ $name ]");
            $warning_flag = 1;
        } else {
            $l_role_package_list[$id] = array(
                "name" => $name,
                "fileName" => $fileName
            );
        }
    }

    return true;
}

////////////////////////////////////////////////////////////////////////////////
// F0002
// 処理内容
//   ロールパッケージファイルからロール情報取得
//
// パラメータ
//   $in_role_package_id:     ロールパッケージ管理 pkey
//   $in_role_package_file:   ロールパッケージファイル
//   $ina_role_name_list:     rolesディレクトリ配下のロール名リスト返却
//                            [role名]
//   $role_package_name:   ロールパッケージ名
//   $ina_role_def_var_list:  default変数リスト返却
//                             変数名のリスト
//                               一般変数
//                                 $ina_role_def_var_list[ロール名][変数名]=0
//                               リスト変数
//                                 $ina_role_def_var_list[ロール名][変数名]=4
//                               配列変数
//                                 $ina_role_def_var_list[ロール名][配列数名]=array([子供変数名]=0,...)
//   $ina_role_def_varsval_list:       
//                            各ロールのデフォルト変数ファイル内に定義されている変数名の具体値リスト
//                              一般変数
//                                [変数名][0]=具体値
//                              複数具体値変数
//                                [変数名][1]=array(1=>具体値,2=>具体値....)
//                              配列変数
//                                [変数名][2][メンバー変数]=array(1=>具体値,2=>具体値....)
//   $ina_role_defNestedVars_list:
//            ['CHAIN_ARRAY'][親変数のKey][自身のKey]['VAR_NAME']    = 変数名   0:リスト配列開始の意味
//                                                   ['NEST_LEVEL']  = 階層     1～
//                                                   ['LIST_STYLE']  = 5:複数具体値変数  0:初期値
//                                                   ['VAR_NAME_PATH']  = 変数名(階層:xx.xx.xx.xx.xxxx)
//                                                   ['VAR_NAME_ALIAS'] = 代入値管理に表示する変数名
//            ['VAR_VALUE']     未使用
//            ['DIFF_ARRAY']    変数構造配列
//   $ina_ITA2User_var_list  読替表の変数リスト　ITA変数=>ユーザ変数
//
// 戻り値
//   True:正常　　False:異常
////////////////////////////////////////////////////////////////////////////////
function getRolePackageData($in_role_package_id,
                            $in_role_package_file,
                            $role_package_name,
                            $roleExtractToDirPath,
                            &$ina_role_name_list,
                            &$ina_role_def_var_list,
                            &$ina_role_def_varsval_list,
                            &$ina_role_defNestedVars_list,
                            &$ina_ITA2User_var_list) {

    global $root_dir_path;
    global $logger;

    // ローカル変数のリスト作成
    $system_vars = array();

    // copyモジュールで使用している変数を取得するか？->ここでは不要
    $useCopyVar = false;

    // ロールパッケージファイル(ZIP)を解析するクラス生成
    $objRole = new ItaAnsibleRoleStructure($role_package_name,  $roleExtractToDirPath,
                                            $system_vars,       $useCopyVar);

    // ロールパッケージファイル名(ZIP)を取得
    $ret = $objRole->getAnsible_RolePackage_filePath(
                        $root_dir_path . '/' . DF_ROLE_PACKAGE_FILE_CONTENTS_DIR,
                        $in_role_package_id,
                        $in_role_package_file);

    // ロールパッケージファイル名(ZIP)の存在確認
    if($ret === false) {
        $lastError = $objRole->getLastError();
        $logger->debug($lastError[1]);
        return false;
    }

    // ロールパッケージファイル(ZIP)の解凍
    $ret = $objRole->zipExtractTo();
    if($ret === false) {
        $lastError = $objRole->getLastError();
        $logger->debug($lastError[1]);
        return false;
    }

    // chkRolesDirectoryでcopyモジュールで使用している変数を取得する処理を追加
    // しているが、ここでは不要なので取得処理をしないパラメータを設定する
    $ret = $objRole->chkRolesDirectory();
    if($ret === false) {
        if(@count($objRole->getTranslateCombErrVars()) !== 0) {
            // ロール内の読替表で読替変数と任意変数の組合せが一致していない
            // TODO: LOG_LEVELによる処理の分岐
            $errmag  = $objRole->getTranslationTableCombinationErrMsg(true);
            $logger->debug($errmag);

        } else if(@count($objRole->getStructErrVars()) !== 0) {
            // defaults定義ファイルに変数定義が複数あり形式が違う変数がある場合
            // TODO: LOG_LEVELによる処理の分岐
            $errmag = $objRole->getVarsStructErrMsg();
            $logger->debug($errmag);

        } else {
            $lastError = $objRole->getLastError();
            $logger->debug($lastError[1]);
        }

        return false;
    }

    // rolesディレクトリ内のロール名取得
    // $ina_role_name_list[role名]
    $ina_role_name_list             = $objRole->getRoleNames();
    // ロール内の変数取得
    $ina_role_def_var_list          = $objRole->getVars();
    // ロール内の変数具体値取得
    $ina_role_def_varsval_list      = $objRole->getVarsVal();
    // ロール内の多段変数構造取得
    $ina_role_defNestedVars_list    = $objRole->getNestedVars();
    // ロール用の読替表取得
    $ina_ITA2User_var_list          = $objRole->getITA2User_vars();

    //リソース解放
    unset($objRole);

    return true;
}

////////////////////////////////////////////////////////////////////////////////
// F00
// 処理内容
//   ロール名からロールIDを取得する
//   
// パラメータ
//  $role_package_id:       パッケージID
//  $role_name:             ロール名
//  $role_id:               ロールID(ref)
// 
// 戻り値
//   True:成功　　False:失敗
////////////////////////////////////////////////////////////////////////////////
function tryGetRoleIdOfRoleName($role_package_id, $role_name, &$role_id) {
    global $logger;
    global $roleName2roleId;

    $logger->trace("FUNCTION: " . __FUNCTION__ . " / " . $role_name);

    if(isset($roleName2roleId[$role_package_id][$role_name]) === false) {
        $logger->warn("Couldn't convert RoleName{$role_name} to RoleID. ");
        return false;
    }

    // ロール名からロールIDを求める
    $role_id = $roleName2roleId[$role_package_id][$role_name];
    return true;
}

////////////////////////////////////////////////////////////////////////////////
// F0011
// 処理内容
//   該当変数がdefault変数定義ファイルの変数構造エラーリストに登録されているか確認
//   
// パラメータ
//  $in_vars_name:          変数名
// 
// 戻り値
//   True:登録　　False:未登録
////////////////////////////////////////////////////////////////////////////////
function existsStructErrList($in_vars_name) {
    global $logger;
    global $structErrVarsList;

    if(@count($structErrVarsList[$in_vars_name]) !== 0) {
        $logger->debug("VarsName($in_vars_name) attributes are not identical in one package.");
        return true;
    }
    return false;
}

////////////////////////////////////////////////////////////////////////////////
// F00
// 処理内容
//   変数名から変数IDを取得する
//   
// パラメータ
//  $vars_name:             変数名
//  $vars_id:               変数ID(ref)
// 
// 戻り値
//   True:成功　　False:失敗
////////////////////////////////////////////////////////////////////////////////
function tryGetVarsIdOfVarsName($vars_name, &$vars_id) {
    global $logger;
    global $varsName2varsId;

    $logger->trace("FUNCTION: " . __FUNCTION__ . " / " . $vars_name);

    if(isset($vars_name, $varsName2varsId) === false) {
        $logger->warn("Couldn't convert VarsName{$vars_name} to VarsID. ");
        return false;
    }

    // 変数名から変数IDを求める
    $vars_id = $varsName2varsId[$vars_name];
    return true;
}

////////////////////////////////////////////////////////////////////////////////
// F00
// 処理内容
//   該当変数がdefault変数定義ファイルの変数構造エラーリストに登録されているか確認
//   
// パラメータ
//  $in_vars_name:          変数名
// 
// 戻り値
//   True:登録　　False:未登録
////////////////////////////////////////////////////////////////////////////////
function existsOtherUserLastUpdateList($vars_id) {
    global $logger;
    global $otherUserLastUpdate_vars_list;

    if(array_key_exists($vars_id, $otherUserLastUpdate_vars_list) === true) {
        $logger->debug("This var(id:$vars_id) was last updated by other process.");
        return true;
    }
    return false;
}

///////////////////////////////////////////////////////////////////////////
// P0006
// ロールパッケージファイル ロール名リストのロール名をロール管理に反映
///////////////////////////////////////////////////////////////////////////
function roleTableUpdate($roleName_list) {

    global $roleName2roleId;

    $enabledRoleIds = array();

    $role_id = 0;
    foreach($roleName_list as $role_package_id => $role_name_list) {
        // ロールパッケージファイル ロール名
        foreach($role_name_list as $role_name) {
            // ロールパッケージファイル内のロール名がロール管理に登録されているか確認
            $param = array('ROLE_PACKAGE_ID' => $role_package_id,
                                 'ROLE_NAME' => $role_name);
            updateEnabledRecord("B_ANSTWR_ROLE", $param, $role_id);

            // ロール管理に登録が必要なロール情報を退避
            $enabledRoleIds[$role_id] = DUMMY_VALUE;
            $roleName2roleId[$role_package_id][$role_name] = $role_id;
        }
    }

    // 不使用のデータを廃止
    discardDisabledRecord("B_ANSTWR_ROLE", $enabledRoleIds);
}

///////////////////////////////////////////////////////////////////////////
// P0011-2
// 読替表の内容をロールパッケージ毎読替表テーブルに反映する。
///////////////////////////////////////////////////////////////////////////
function translateTableUpdate($ITA2User_TranslateVars_list) {

    $transVarsIds = array();

    // ロールパッケージ毎読替表にデータを反映
    foreach($ITA2User_TranslateVars_list as $role_package_id => $role_list) {
        foreach($role_list as $role_name => $vars_list) {

            // ロール名からロールIDが求められるか判定
            $role_id = 0;
            $ret = tryGetRoleIdOfRoleName($role_package_id, $role_name, $role_id);
            if($ret === false) {
                $warning_flag = 1;
                continue;
            }

            $pkeyVal = 0;
            foreach($vars_list as $ita_vars_name => $user_vars_name) {
                // 該当変数がdefault変数定義ファイルの変数構造エラーリストに登録されているか確認
                $ret = existsStructErrList($ita_vars_name);
                if($ret === true) {
                    // 変数構造エラーリストの変数は変数一覧に登録しない。
                    continue;
                }

                // ロールパッケージ毎読替表にデータを反映
                $param = array("ROLE_PACKAGE_ID" => $role_package_id,
                               "ROLE_ID"         => $role_id,
                               "ITA_VARS_NAME"   => $ita_vars_name,
                               "ANY_VARS_NAME"   => $user_vars_name);

                updateEnabledRecord("B_ANSTWR_TRANSLATE_VARS", $param, $pkeyVal);

                // 登録・更新した変数情報を退避
                $transVarsIds[$pkeyVal] = DUMMY_VALUE;
            }
        }
    }

    // 不使用のデータを廃止
    discardDisabledRecord("B_ANSTWR_TRANSLATE_VARS", $transVarsIds);
}

///////////////////////////////////////////////////////////////////////////
// P0013
// ロールパッケージファイル内の変数名を変数管理に反映
///////////////////////////////////////////////////////////////////////////
function varsTableUpdate($defVars_list_byPkgId, $defNestedVars_list_byPkgId) {

    $varsIds = array();

    // 一般・配列変数
    foreach($defVars_list_byPkgId as $role_package_id => $role_list) {
        foreach($role_list as $role_name => $vars_list) {

            // ロール名からロールIDが求められるか判定
            $role_id = 0;
            $ret = tryGetRoleIdOfRoleName($role_package_id, $role_name, $role_id);
            if($ret === false) {
                $warning_flag = 1;
                continue;
            }

            $pkeyVal = 0;
            foreach($vars_list as $vars_name => $vars_attr) {

                // 該当変数がdefault変数定義ファイルの変数構造エラーリストに登録されているか確認
                $ret = existsStructErrList($vars_name);
                if($ret === true) {
                    // 変数構造エラーリストの変数は変数一覧に登録しない。
                    continue;
                }

                $selectParam = array("VARS_NAME" => $vars_name);
                $updateParam = array("VARS_NAME"    => $vars_name,
                                     "VARS_ATTR_ID" => $vars_attr);

                updateEnabledRecordSpecializedVars("B_ANSTWR_VARS", $selectParam, $updateParam, $pkeyVal);

                // 登録・更新した変数情報を退避
                $varsIds[$pkeyVal] = DUMMY_VALUE;
            }
        }
    }

    // 多段変数
    foreach($defNestedVars_list_byPkgId as $role_package_id=>$role_list) {
        foreach($role_list as $role_name => $vars_list) {

            // ロール名からロールIDが求められるか判定
            $role_id = 0;
            $ret = tryGetRoleIdOfRoleName($role_package_id, $role_name, $role_id);
            if($ret === false) {
                $warning_flag = 1;
                continue;
            }

            foreach($vars_list as $vars_name => $info_list) {

                // 該当変数がdefault変数定義ファイルの変数構造エラーリストに登録されているか確認
                $ret = existsStructErrList($vars_name);
                if($ret === true) {
                    // 変数構造エラーリストの変数は変数一覧に登録しない。
                    continue;
                }

                $selectParam = array("VARS_NAME" => $vars_name);
                $updateParam = array_merge($selectParam, array("VARS_ATTR_ID" => LC_VARS_ATTR_STRUCT));

                updateEnabledRecordSpecializedVars("B_ANSTWR_VARS", $selectParam, $updateParam, $pkeyVal);

                // 登録・更新した変数情報を退避
                $varsIds[$pkeyVal] = DUMMY_VALUE;
            }
        }
    }

    // 不使用のデータを廃止
    discardDisabledRecordSpecializedVars("B_ANSTWR_VARS", $varsIds);
}

///////////////////////////////////////////////////////////////////////////
// P0017
// 多次元変数メンバー変数を多次元変数メンバー管理に登録する。
///////////////////////////////////////////////////////////////////////////
function memberVarsTableUpdate($defNestedVars_list_byPkgId) {

    $nestedMemberVarsId_list = array();

    // パッケージ・ロールまたがりで同じ変数名の二重処理防止用
    $add_var_name_list = array();

    foreach($defNestedVars_list_byPkgId as $role_package_id => $role_list) {
        foreach($role_list as $role_name => $vars_list) {

            // ロール名からロールIDが求められるか判定
            $role_id = 0;
            $ret = tryGetRoleIdOfRoleName($role_package_id, $role_name, $role_id);
            if($ret === false) {
                $warning_flag = 1;
                continue;
            }

            // 変数毎
            foreach($vars_list as $vars_name => $info_list) {

                // パッケージ・ロールまたがりで同じ変数名の二重処理防止
                if(@count($add_var_name_list[$vars_name]) != 0) {
                    continue;
                } else {
                    $add_var_name_list[$vars_name] = 0;
                }

                // 該当変数がdefault変数定義ファイルの変数構造エラーリストに登録されているか確認
                $ret = existsStructErrList($vars_name);
                if($ret === true) {
                    // 変数構造エラーリストの変数は登録しない。
                    continue;
                }

                // 変数一覧管理のPkeyを取得する。
                $vars_id = 0;
                $ret = tryGetVarsIdOfVarsName($vars_name, $vars_id);
                if($ret === false) {
                    $warning_flag = 1;
                    continue;
                }

                // 変数一覧の該当変数の最終更新者が他プロセスの場合、多次元変数メンバー管理の更新をしない
                $ret = existsOtherUserLastUpdateList($vars_id);
                if($ret === true) {
                    continue;
                }

                // メンバ変数毎
                $pkeyVal = 0;
                foreach($info_list['CHAIN_ARRAY'] as $chl_vars_list) {
                    $param = array(
                        // 親変数へのキー
                        "VARS_ID"                   =>  $vars_id,
                        // 親メンバー変数へのキー 
                        "PARENT_KEY_ID"             =>  $chl_vars_list['PARENT_VARS_KEY_ID'],
                        // 自メンバー変数のキー
                        "SELF_KEY_ID"               =>  $chl_vars_list['VARS_KEY_ID'],
                        // メンバー変数名　　0:配列変数を示す
                        "MEMBER_NAME"               =>  $chl_vars_list['VARS_NAME'],
                        // 階層 1～
                        "NESTED_LEVEL"              =>  $chl_vars_list['ARRAY_NEST_LEVEL'],
                        // 代入順序有無　1:必要　初期値:NULL
                        "ASSIGN_SEQ_NEED"           =>  $chl_vars_list['ASSIGN_SEQ_NEED'],
                        // 列順序有無  　1:必要　初期値:NULL
                        "COL_SEQ_NEED"              =>  $chl_vars_list['COL_SEQ_NEED'],
                        // 代入値管理系の表示有無　1:必要　初期値:NULL
                        "MEMBER_DISP"               =>  $chl_vars_list['MEMBER_DISP'],
                        // メンバー変数の階層パス
                        "NESTED_MEMBER_PATH"        =>  $chl_vars_list['VRAS_NAME_PATH'],
                        // 代入値管理系の表示メンバー変数名
                        "NESTED_MEMBER_PATH_ALIAS"  =>  $chl_vars_list['VRAS_NAME_ALIAS'],
                        // 最大繰返数
                        "MAX_COL_SEQ"               =>  $chl_vars_list['MAX_COL_SEQ']
                    );

                    updateEnabledRecord("B_ANSTWR_NESTED_MEM_VARS", $param, $pkeyVal);

                    // 多次元変数メンバー管理のPkey退避
                    $nestedMemberVarsId_list[$pkeyVal] = DUMMY_VALUE;
                }
            }
        }
    }

    // 不使用のデータを廃止
    discardDisabledRecord("B_ANSTWR_NESTED_MEM_VARS", $nestedMemberVarsId_list);
}

///////////////////////////////////////////////////////////////////////////
// P0007
// ロール内のPlaybookで使用している変数名をロール変数管理に反映
///////////////////////////////////////////////////////////////////////////
function roleVarsTableUpdate($defVars_list_byPkgId, $defNestedVars_list_byPkgId) {

    $roleVarsId_list = array();

    // 一般・配列変数
    foreach($defVars_list_byPkgId as $role_package_id => $role_list) {
        foreach($role_list as $role_name => $vars_list) {

            // ロール名からロールIDが求められるか判定
            $role_id = 0;
            $ret = tryGetRoleIdOfRoleName($role_package_id, $role_name, $role_id);
            if($ret === false) {
                $warning_flag = 1;
                continue;
            }

            $pkeyVal = 0;
            foreach($vars_list as $vars_name => $vars_attr) {

                // 該当変数がdefault変数定義ファイルの変数構造エラーリストに登録されているか確認
                $ret = existsStructErrList($vars_name);
                if($ret === true) {
                    // 変数構造エラーリストの変数は登録しない。
                    continue;
                }

                // 変数一覧管理のPkeyを取得する。
                $vars_id = 0;
                $ret = tryGetVarsIdOfVarsName($vars_name, $vars_id);
                if($ret === false) {
                    $warning_flag = 1;
                    continue;
                }

                // 変数一覧の該当変数の最終更新者が他プロセスの場合、多次元変数メンバー管理の更新をしない
                $ret = existsOtherUserLastUpdateList($vars_id);
                if($ret === true) {
                    continue;
                }

                $selectParam = array("ROLE_PACKAGE_ID" => $role_package_id,
                                     "ROLE_ID"         => $role_id,
                                     "VARS_NAME"       => $vars_name);
                $updateParam = array("ROLE_PACKAGE_ID" => $role_package_id,
                                     "ROLE_ID"         => $role_id,
                                     "VARS_ID"         => $vars_id,
                                     "VARS_ATTR_ID"    => $vars_attr);

                updateEnabledRecordSpecializedRoleVars("B_ANSTWR_ROLE_VARS", $selectParam, $updateParam, $pkeyVal);

                // ロール変数管理に登録が必要なロール変数を退避
                $roleVarsId_list[$pkeyVal] = DUMMY_VALUE;
            }
        }
    }

    // 多段変数
    foreach($defNestedVars_list_byPkgId as $role_package_id => $role_list) {
        foreach($role_list as $role_name => $var_list) {

            // ロール名からロールIDが求められるか判定
            $role_id = 0;
            $ret = tryGetRoleIdOfRoleName($role_package_id, $role_name, $role_id);
            if($ret === false) {
                $warning_flag = 1;
                continue;
            }

            $pkeyVal = 0;
            foreach($var_list as $vars_name => $info_list) {

                // 該当変数がdefault変数定義ファイルの変数構造エラーリストに登録されているか確認
                $ret = existsStructErrList($vars_name);
                if($ret === true) {
                    // 変数構造エラーリストの変数は登録しない。
                    continue;
                }

                // 変数一覧管理のPkeyを取得する。
                $vars_id = 0;
                $ret = tryGetVarsIdOfVarsName($vars_name, $vars_id);
                if($ret === false) {
                    $warning_flag = 1;
                    continue;
                }

                // 変数一覧の該当変数の最終更新者が他プロセスの場合、多次元変数メンバー管理の更新をしない
                $ret = existsOtherUserLastUpdateList($vars_id);
                if($ret === true) {
                    continue;
                }

                $vars_attr = LC_VARS_ATTR_STRUCT;

                $selectParam = array("ROLE_PACKAGE_ID" => $role_package_id,
                                     "ROLE_ID"         => $role_id,
                                     "VARS_NAME"       => $vars_name);
                $updateParam = array("ROLE_PACKAGE_ID" => $role_package_id,
                                     "ROLE_ID"         => $role_id,
                                     "VARS_ID"         => $vars_id,
                                     "VARS_ATTR_ID"    => $vars_attr);

                updateEnabledRecordSpecializedRoleVars("B_ANSTWR_ROLE_VARS", $selectParam, $updateParam, $pkeyVal);

                // ロール変数管理に登録が必要なロール変数を退避
                $roleVarsId_list[$pkeyVal] = DUMMY_VALUE;
            }
        }
    }

    // 不使用のデータを廃止
    discardDisabledRecord("B_ANSTWR_ROLE_VARS", $roleVarsId_list);
}

///////////////////////////////////////////////////////////////////////////
// P0024
// デフォルト変数定義ファイルに定義されている変数の具体値をロール変数具体値管理に反映
///////////////////////////////////////////////////////////////////////////
function varsValTableUpdate($defVarsVal_list, $defNestedVars_list_byPkgId) {

    global $dbAccess;
    global $logger;

    $varsValId_list = array();

    // 一般・配列変数
    foreach($defVarsVal_list as $role_package_id => $role_list) {
        foreach($role_list as $role_name => $vars_list) {

            // ロール名からロールIDが求められるか判定
            $role_id = 0;
            $ret = tryGetRoleIdOfRoleName($role_package_id, $role_name, $role_id);
            if($ret === false) {
                $warning_flag = 1;
                continue;
            }

            foreach($vars_list as $vars_name => $vars_attr_list) {

                // 該当変数がdefault変数定義ファイルの変数構造エラーリストに登録されているか確認
                $ret = existsStructErrList($vars_name);
                if($ret === true) {
                    // 変数構造エラーリストの変数は登録しない。
                    continue;
                }

                // 変数一覧管理のPkeyを取得する。
                $vars_id = 0;
                $ret = tryGetVarsIdOfVarsName($vars_name, $vars_id);
                if($ret === false) {
                    $warning_flag = 1;
                    continue;
                }

                    // 変数一覧の該当変数の最終更新者が他プロセスの場合、多次元変数メンバー管理の更新をしない
                $ret = existsOtherUserLastUpdateList($vars_id);
                if($ret === true) {
                    continue;
                }

                // 変数タイプを取得
                $pkeyVal = 0;
                foreach($vars_attr_list as $vars_attr => $varsval_list01) {
                    // 変数属性で分岐
                    switch($vars_attr) {
                    case LC_VARS_ATTR_STD:     //一般変数
                        // 具体値取得
                        $var_val = $varsval_list01;

                        $selectParam = array("ROLE_PACKAGE_ID"          => $role_package_id,
                                             "ROLE_ID"                  => $role_id,
                                             "END_VAR_OF_VARS_ATTR_ID"  => $vars_attr,
                                             "VARS_ID"                  => $vars_id,
                                             "NESTEDMEM_COL_CMB_ID"     => DBAccesser::ISNULL_PARAM,
                                             "ASSIGN_SEQ"               => DBAccesser::ISNULL_PARAM);
                        $updateParam = array_merge($selectParam, array("VARS_VALUE" => $var_val));

                        // 変数の具体値を登録
                        updateEnabledRecordSpecializedVarsVal("B_ANSTWR_DEFAULT_VARSVAL", $selectParam, $updateParam, $pkeyVal);

                        // ロール変数具体値管理に登録が必要なPkeyを退避
                        $varsValId_list[$pkeyVal] = DUMMY_VALUE;

                        break;

                    case LC_VARS_ATTR_LIST:     //複数具体値変数
                        // 具体値の登録があるか
                        if(count($varsval_list01) == 0) {
                            // 具体値がないので次へ
                            continue 2;
                        }

                        // 代入順序毎の具体値取得
                        foreach($varsval_list01 as $assign_seq => $var_val) {
                            // 代入順序毎の具体値を登録
                            $selectParam = array("ROLE_PACKAGE_ID"          => $role_package_id,
                                                 "ROLE_ID"                  => $role_id,
                                                 "END_VAR_OF_VARS_ATTR_ID"  => $vars_attr,
                                                 "VARS_ID"                  => $vars_id,
                                                 "NESTEDMEM_COL_CMB_ID"     => DBAccesser::ISNULL_PARAM,
                                                 "ASSIGN_SEQ"               => $assign_seq);
                            $updateParam = array_merge($selectParam, array("VARS_VALUE" => $var_val));

                            // 変数の具体値を登録
                            updateEnabledRecordSpecializedVarsVal("B_ANSTWR_DEFAULT_VARSVAL", $selectParam, $updateParam, $pkeyVal);

                            // ロール変数具体値管理に登録が必要なPkeyを退避
                            $varsValId_list[$pkeyVal] = DUMMY_VALUE;

                        }
                        break;
                    }
                }
            }
        }
    }

    ////////////////////////////////////////////////////////////////////
    // P0025
    // 多次元変数配列組合せ管理からデータを取得する。
    ////////////////////////////////////////////////////////////////////
    // T0027
    $lva_MemberColComb_list = array();

    $rows = $dbAccess->selectRows("D_ANSTWR_NESTEDMEM_COL_CMB");
    foreach($rows as $row) {
        $lva_MemberColComb_list[$row["VARS_ID"]][$row["NESTED_MEMBER_PATH"]][$row["COL_SEQ_VALUE"]] = $row["NESTEDMEM_COL_CMB_ID"];
    }

    $logger->trace("T0027 lva_MemberColComb_list dump");
    $logger->trace(var_export($lva_MemberColComb_list, true));

    // 多段変数
    foreach($defNestedVars_list_byPkgId as $role_package_id => $role_list) {
        foreach($role_list as $role_name => $vars_list) {

            // ロール名からロールIDが求められるか判定
            $role_id = 0;
            $ret = tryGetRoleIdOfRoleName($role_package_id, $role_name, $role_id);
            if($ret === false) {
                $warning_flag = 1;
                continue;
            }

            foreach($vars_list as $vars_name => $vars_info_list) {

                // 該当変数がdefault変数定義ファイルの変数構造エラーリストに登録されているか確認
                $ret = existsStructErrList($vars_name);
                if($ret === true) {
                    // 変数構造エラーリストの変数は登録しない。
                    continue;
                }

                // 変数一覧管理のPkeyを取得する。
                $vars_id = 0;
                $ret = tryGetVarsIdOfVarsName($vars_name, $vars_id);
                if($ret === false) {
                    $warning_flag = 1;
                    continue;
                }

                    // 変数一覧の該当変数の最終更新者が他プロセスの場合、多次元変数メンバー管理の更新をしない
                $ret = existsOtherUserLastUpdateList($vars_id);
                if($ret === true) {
                    continue;
                }

                // メンバー変数の具体値が登録されているか判定
                if(@count($vars_info_list["VAR_VALUE"]) == 0) {
                    continue;
                }

                // メンバーを取得
                foreach($vars_info_list["VAR_VALUE"] as $nestedMemberPath => $varsval_list00) {

                    $logger->trace("varsval_list00: " . var_export($varsval_list00, true));

                    // 変数属性を取得
                    $vars_attr = array_keys($varsval_list00)[0]; // 必ず1要素のみ
                    $varsval_list01 = $varsval_list00[$vars_attr];

                    // 変数タイプで分岐
                    switch($vars_attr) {
                    case LC_VARS_ATTR_STD:     //一般変数
                        foreach($varsval_list01 as $col_seq_str => $var_val) {
                            // 多次元変数配列組合せ管理のPkeyを取得する。
                            if(strlen($col_seq_str) == 0) {
                                $col_seq_str = "-";
                            }

                            if(@count($lva_MemberColComb_list[$vars_id][$nestedMemberPath][$col_seq_str]) == 0) {
                                $logger->warn("No such data. VarsID: $vars_id/ NestedMemberPath: $nestedMemberPath/ ColSeqStr: $col_seq_str/ Value: $var_val");
                                continue 2;
                            }

                            $nestedmemColCmbId = $lva_MemberColComb_list[$vars_id][$nestedMemberPath][$col_seq_str];

                            // 変数の具体値を登録
                            $selectParam = array("ROLE_PACKAGE_ID"          => $role_package_id,
                                                 "ROLE_ID"                  => $role_id,
                                                 "END_VAR_OF_VARS_ATTR_ID"  => $vars_attr,
                                                 "VARS_ID"                  => $vars_id,
                                                 "NESTEDMEM_COL_CMB_ID"     => $nestedmemColCmbId,
                                                 "ASSIGN_SEQ"               => DBAccesser::ISNULL_PARAM);
                            $updateParam = array_merge($selectParam, array("VARS_VALUE" => $var_val));

                            // 変数の具体値を登録
                            updateEnabledRecordSpecializedVarsVal("B_ANSTWR_DEFAULT_VARSVAL", $selectParam, $updateParam, $pkeyVal);

                            // ロール変数具体値管理に登録が必要なPkeyを退避
                            $varsValId_list[$pkeyVal] = DUMMY_VALUE;
                        }
                        break;
                    case LC_VARS_ATTR_LIST:     //複数具体値変数
                        // 具体値の登録があるか
                        if(count($varsval_list01) == 0) {
                            // 具体値がないので次へ
                            continue 2;
                        }

                        foreach($varsval_list01 as $col_seq_str => $varsval_list02) {
                            foreach($varsval_list02 as $assign_seq => $var_val) {
                                // 多次元変数配列組合せ管理のPkeyを取得する。
                                if(strlen($col_seq_str) == 0) {
                                    $col_seq_str = "-";
                                }

                                if(@count($lva_MemberColComb_list[$vars_id][$nestedMemberPath][$col_seq_str]) == 0) {
                                    $logger->warn("No such data. VarsID: $vars_id/ NestedMemberPath: $nestedMemberPath/ ColSeqStr: $col_seq_str/ AssignSeq: $assign_seq/ Value: $var_val");
                                    continue 2;
                                }

                                $nestedmemColCmbId = $lva_MemberColComb_list[$vars_id][$nestedMemberPath][$col_seq_str];

                                // 代入順序毎の具体値を登録
                                $selectParam = array("ROLE_PACKAGE_ID"          => $role_package_id,
                                                     "ROLE_ID"                  => $role_id,
                                                     "END_VAR_OF_VARS_ATTR_ID"  => $vars_attr,
                                                     "VARS_ID"                  => $vars_id,
                                                     "NESTEDMEM_COL_CMB_ID"     => $nestedmemColCmbId,
                                                     "ASSIGN_SEQ"               => $assign_seq);
                                $updateParam = array_merge($selectParam, array("VARS_VALUE" => $var_val));

                                // 変数の具体値を登録
                                updateEnabledRecordSpecializedVarsVal("B_ANSTWR_DEFAULT_VARSVAL", $selectParam, $updateParam, $pkeyVal);

                                // ロール変数具体値管理に登録が必要なPkeyを退避
                                $varsValId_list[$pkeyVal] = DUMMY_VALUE;
                            }
                        }
                        break;
                    }
                }
            }
        }
    }

    // 不使用のデータを廃止
    discardDisabledRecord("B_ANSTWR_DEFAULT_VARSVAL", $varsValId_list);
}

/////////////////////////////////////////////////////////////////////////////////
// P0028
// 作業パターン変数紐付管理からデータを取得
/////////////////////////////////////////////////////////////////////////////////
function patternVarsTableUpdate() {

    global $dbAccess;

    // ロール変数管理から必要なデータを取得
    $lva_use_role_vars_name_list = array();
    $rows = $dbAccess->selectRows("B_ANSTWR_ROLE_VARS");
    foreach($rows as $row) {
        $lva_use_role_vars_name_list[$row['ROLE_PACKAGE_ID']][$row['ROLE_ID']][$row['VARS_ID']] = DUMMY_VALUE;
    }

    // 作業パターン詳細から必要なデータを取得
    // T0008
    $lta_pattern_link = array();
    $rows = $dbAccess->selectRows("B_ANSTWR_PTN_ROLE_LINK");
    foreach($rows as $row) {
        $lta_pattern_link[$row['PATTERN_ID']][$row['ROLE_PACKAGE_ID']][$row['ROLE_ID']] = DUMMY_VALUE;
    }

    // T0012
    $aryVarNameIdsPerPattern = array();

    // 作業パターンID毎の変数一覧作成
    $varsLinkId_list = array();
    $pkeyVal = 0;
    foreach($lta_pattern_link as $patten_id => $pkg_list) {
        // $aryVarNameIdsPerPattern:[パターンID][変数マスタPkey]=1
        $aryVarNameIdsPerPattern[$patten_id] = array();

        foreach($pkg_list as $role_package_id => $role_list) {
            foreach($role_list as $role_id => $dummy) {

                // ロール変数管理 登録リストに該当ロール変数があるか判定
                // ワーニングがでるので@を付ける
                if(@count($lva_use_role_vars_name_list[$role_package_id][$role_id]) === 0) {
                    continue;
                }

                // ロール変数管理 登録リストから該当ロール変数の情報を取得
                $vars_list = $lva_use_role_vars_name_list[$role_package_id][$role_id];

                //----変数名ごとにループする
                foreach($vars_list as $vars_id => $dummy) {

                    //作業パターンID毎の変数一覧にパターンIDがあるか判定
                    if(array_key_exists($patten_id, $aryVarNameIdsPerPattern) === true) {
                        //作業パターン+変数があるか判定
                        if(array_key_exists($vars_id, $aryVarNameIdsPerPattern[$patten_id]) === true) {
                            //登録スキップ
                            continue;
                        }
                    }

                    // 作業パターンID毎の変数一覧作成 
                    $aryVarNameIdsPerPattern[$patten_id][$vars_id] = DUMMY_VALUE;

                    // 変数一覧の該当変数の最終更新者が他プロセスの場合、更新をしない
                    $ret = existsOtherUserLastUpdateList($vars_id);
                    if($ret === true) {
                        continue;
                    }

                    $param = array("PATTERN_ID"     => $patten_id,
                                   "VARS_ID"        => $vars_id);

                    updateEnabledRecord("B_ANSTWR_PTN_VARS_LINK", $param, $pkeyVal);

                    $varsLinkId_list[$pkeyVal] = DUMMY_VALUE;
                }//変数ごとにループする----

            }//ロールごとにループする----

        }//ロールパッケージごとにループする----

    }//作業パターンごとにループする----

    // 不使用のデータを廃止
    discardDisabledRecord("B_ANSTWR_PTN_VARS_LINK", $varsLinkId_list);
}

/**
 * 有効データのレコード登録・更新
 *
 * @param string        $tableName      テーブル物理名
 * @param array         $param          テーブル物理名
 * @param int           $pkeyVal        主キー値(ref)
 */
function updateEnabledRecord($tableName, $param, &$pkeyVal) {

    global $dbAccess;
    global $logger;

    $logger->trace("FUNCTION: " . __FUNCTION__ . " / " . $tableName);

    $targetRows = $dbAccess->selectRowsUseBind($tableName, true, $param);
    $count = count($targetRows);

    if($count == 0) {
        $logger->trace("new record.");
        $pkeyVal = $dbAccess->insertRow($tableName, $param);
    } else {
        $row = $targetRows[0];

        $tableDefinition = TableDefinitionsMaster::getDefinition($tableName);
        $pkey = $tableDefinition::getPKColumnName();

        // 主キー退避
        $pkeyVal = $row[$pkey];
        if($row['DISUSE_FLAG'] == "1") {
            $logger->trace("update record.");
            // 廃止なので復活する。
            $row['DISUSE_FLAG'] = "0";
            $dbAccess->updateRow($tableName, $row);
        }
    }

    return true;
}

function discardDisabledRecord($tableName, $enabledIds) {

    global $dbAccess;
    global $logger;
    global $otherUserLastUpdate_vars_list;

    $logger->trace("FUNCTION: " . __FUNCTION__ . " / " . $tableName);

    $tableAllRows = $dbAccess->selectRows($tableName);

    $tableDefinition = TableDefinitionsMaster::getDefinition($tableName);
    $pkey = $tableDefinition::getPKColumnName();

    foreach($tableAllRows as $row) {

        // 親変数の更新者が本ワークフロー実行者でなければ何もしない。
        if(array_key_exists("VARS_ID", $row) &&
            array_key_exists($row['VARS_ID'], $otherUserLastUpdate_vars_list)) {
            continue;
        }

        if(array_key_exists($row[$pkey], $enabledIds)) {
            // 登録されている場合は何もしない。
            continue;
        }

        $logger->trace("discard record.");
        $row['DISUSE_FLAG'] = "1";
        $dbAccess->updateRow($tableName, $row);
    }

    return true;
}

function updateEnabledRecordSpecializedVars($tableName, $selectParam, $updateParam, &$pkeyVal) {

    global $db_access_user_id;
    global $dbAccess;
    global $logger;
    global $varsName2varsId;
    global $otherUserLastUpdate_vars_list;

    $logger->trace("FUNCTION: " . __FUNCTION__ . " / " . $tableName);

    $targetRows = $dbAccess->selectRowsUseBind($tableName, true, $selectParam);
    $count = count($targetRows);

    if($count == 0) {
        $logger->trace("new record.");
        $pkeyVal = $dbAccess->insertRow($tableName, $updateParam);
    } else {
        $row = $targetRows[0];

        // 最終更新者が自分ではない場合は、処理スキップ＆リストに格納
        if($row['LAST_UPDATE_USER'] != $db_access_user_id) {
            $otherUserLastUpdate_vars_list[$row['VARS_ID']] = DUMMY_VALUE;
            return false;
        }

        $tableDefinition = TableDefinitionsMaster::getDefinition($tableName);
        $pkey = $tableDefinition::getPKColumnName();

        // 主キー退避
        $pkeyVal = $row[$pkey];

        $updateFlag = false;
        if($row['DISUSE_FLAG'] == "1") {
            // 廃止なので復活する。
            $row['DISUSE_FLAG'] = "0";
            $updateFlag = true;
        }

        if($row['VARS_ATTR_ID'] != $updateParam['VARS_ATTR_ID']) {
            // 属性が異なれば更新する。
            $row['VARS_ATTR_ID'] = $updateParam['VARS_ATTR_ID'];
            $updateFlag = true;
        }

        if($updateFlag) {
            $logger->trace("update record.");
            $dbAccess->updateRow($tableName, $row);
        }
    }

    $varsName2varsId[$updateParam['VARS_NAME']] = $pkeyVal;
    return true;
}

function discardDisabledRecordSpecializedVars($tableName, $enabledIds) {

    global $db_access_user_id;
    global $dbAccess;
    global $logger;
    global $otherUserLastUpdate_vars_list;

    $logger->trace("FUNCTION: " . __FUNCTION__ . " / " . $tableName);

    $tableAllRows = $dbAccess->selectRows($tableName);

    $tableDefinition = TableDefinitionsMaster::getDefinition($tableName);
    $pkey = $tableDefinition::getPKColumnName();

    foreach($tableAllRows as $row) {

        // 最終更新者が自分ではない場合は、処理スキップ＆リストに格納
        if($row['LAST_UPDATE_USER'] != $db_access_user_id) {
            $otherUserLastUpdate_vars_list[$row['VARS_ID']] = DUMMY_VALUE;
            continue;
        }

        if(array_key_exists($row[$pkey], $enabledIds)) {
            // 登録されている場合はなにもしない。
            continue;
        }

        $logger->trace("discard record.");
        $row['DISUSE_FLAG'] = "1";
        $dbAccess->updateRow($tableName, $row);
    }

    return true;
}

function updateEnabledRecordSpecializedRoleVars($tableName, $selectParam, $updateParam, &$pkeyVal) {

    global $dbAccess;
    global $logger;

    $logger->trace("FUNCTION: " . __FUNCTION__ . " / " . $tableName);

    $viewName = preg_replace("/^B_/", "D_", $tableName);
    $targetRows = $dbAccess->selectRowsUseBind($viewName, true, $selectParam);
    $count = count($targetRows);

    if($count == 0) {
        $logger->trace("new record.");
        $pkeyVal = $dbAccess->insertRow($tableName, $updateParam);
    } else {
        $row = $targetRows[0];

        $tableDefinition = TableDefinitionsMaster::getDefinition($tableName);
        $pkey = $tableDefinition::getPKColumnName();

        // 主キー退避
        $pkeyVal = $row[$pkey];

        $updateFlag = false;
        if($row['DISUSE_FLAG'] == "1") {
            // 廃止なので復活する。
            $row['DISUSE_FLAG'] = "0";
            $updateFlag = true;
        }

        if($row['VARS_ID'] != $updateParam['VARS_ID']) {
            // 属性が異なれば更新する。
            $row['VARS_ID'] = $updateParam['VARS_ID'];
            $updateFlag = true;
        }

        if($row['VARS_ATTR_ID'] != $updateParam['VARS_ATTR_ID']) {
            // 属性が異なれば更新する。
            $row['VARS_ATTR_ID'] = $updateParam['VARS_ATTR_ID'];
            $updateFlag = true;
        }

        if($updateFlag) {
            $logger->trace("update record.");
            $dbAccess->updateRow($tableName, $row);
        }
    }

    return true;
}

function updateEnabledRecordSpecializedVarsVal($tableName, $selectParam, $updateParam, &$pkeyVal) {

    global $dbAccess;
    global $logger;

    $logger->trace("FUNCTION: " . __FUNCTION__ . " / " . $tableName);

    $targetRows = $dbAccess->selectRowsUseBind($tableName, true, $selectParam);
    $count = count($targetRows);

    if($count == 0) {
        $logger->trace("new record.");
        $pkeyVal = $dbAccess->insertRow($tableName, $updateParam);
    } else {
        $row = $targetRows[0];

        $tableDefinition = TableDefinitionsMaster::getDefinition($tableName);
        $pkey = $tableDefinition::getPKColumnName();

        // 主キー退避
        $pkeyVal = $row[$pkey];

        $updateFlag = false;
        if($row['DISUSE_FLAG'] == "1") {
            // 廃止なので復活する。
            $row['DISUSE_FLAG'] = "0";
            $updateFlag = true;
        }

        if($row['VARS_VALUE'] != $updateParam['VARS_VALUE']) {
            // 属性が異なれば更新する。
            $row['VARS_VALUE'] = $updateParam['VARS_VALUE'];
            $updateFlag = true;
        }

        if($updateFlag) {
            $logger->trace("update record.");
            $dbAccess->updateRow($tableName, $row);
        }
    }

    return true;
}
?>
