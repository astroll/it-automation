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
//      作業実行ファイル
//      AnsibleTower作業実行
//      1:対象行のステータスを準備中に変更し、開始時間を記録する。
//      2:準備中の作業を順次実行していく。
//      3:実行開始に成功した場合はステータスを実行中にする。失敗した場合は失敗にする。
//
//  【特記事項】
//      <<引数>>
//       (なし)
//      <<返却値>>
//       (なし)
//
//////////////////////////////////////////////////////////////////////

// 起動しているshellの起動判定を正常にするための待ち時間
sleep(1);

////////////////////////////////
// ルートディレクトリを取得
////////////////////////////////
if(empty($root_dir_path)) {
    $root_dir_temp = array();
    $root_dir_temp = explode("ita-root", dirname(__FILE__));
    $root_dir_path = $root_dir_temp[0] . "ita-root";
}

require_once($root_dir_path . "/libs/backyardlibs/ansibletower_driver/DBAccesser.php");
require_once($root_dir_path . "/libs/backyardlibs/ansibletower_driver/LogWriter.php");
require_once($root_dir_path . "/libs/backyardlibs/ansibletower_driver/MessageTemplateStorageHolder.php");
require_once($root_dir_path . "/libs/backyardlibs/ansibletower_driver/RestApiCaller.php");
require_once($root_dir_path . "/libs/backyardlibs/ansibletower_driver/ExecuteDirector.php");
require_once($root_dir_path . "/libs/backyardlibs/ansibletower_driver/AnsibleTowerCommonLib.php");

////////////////////////////////
// ログ出力設定
////////////////////////////////
$log_output_dir     = getenv("LOG_DIR");
if(empty($log_output_dir)) {
    $log_output_dir = $root_dir_path . "/logs/backyardlogs";
}
$log_file_prefix    = basename(__FILE__, ".php") . "_";
$tmpVarTimeStamp    = time();
$logfile            = $log_output_dir . "/" . $log_file_prefix . date("Ymd", $tmpVarTimeStamp) . ".log";
ini_set("display_errors", "stderr");
ini_set("log_errors",     1);
ini_set("error_log",      $logfile);

$log_output_php     = $root_dir_path . "/libs/backyardlibs/backyard_log_output.php";
$logger             = LogWriter::getInstance();
$log_level          = getenv("LOG_LEVEL"); // "DEBUG";
$logger->setUp($log_output_php, $log_output_dir, $log_file_prefix, $log_level);

$msgTplStorage      = MessageTemplateStorageHolder::getMTS();

////////////////////////////////
// DB接続設定
////////////////////////////////
$db_access_user_id  = -121001; // AnsibleTower作業実行プロシージャ

////////////////////////////////
// ローカル変数(全体)宣言
////////////////////////////////
$ansibletower_setenv_php = $root_dir_path . "/libs/backyardlibs/ansibletower_driver/setenv.php";
require_once($ansibletower_setenv_php);
$warning_flag       = 0; // 警告フラグ(1：警告発生)
$error_flag         = 0; // 異常フラグ(1：異常発生)

////////////////////////////////
// 共通モジュールの呼び出し
////////////////////////////////
$php_req_gate_php   = $root_dir_path . "/libs/commonlibs/common_php_req_gate.php";
$aryOrderToReqGate  = array("DBConnect" => "LATE"); // DBconnectだけ別タイミングにする
require_once($php_req_gate_php);
$ansibletower_create_files_php   = $root_dir_path . "/libs/backyardlibs/ansibletower_driver/role_package/CreateAnsibleExecFiles.php";
require_once($ansibletower_create_files_php);
$ansibletower_common_lib_php   = $root_dir_path . "/libs/backyardlibs/ansibletower_driver/AnsibleTowerCommonLib.php";
require_once($ansibletower_common_lib_php);

////////////////////////////////
// 業務処理開始
////////////////////////////////

$tgt_row_array      = array(); // 処理対象から準備中を除くレコードまるごと格納

$dbAccess = null;
$restApiCaller = null;
try {
    // 開始メッセージ
    $logger->debug(" = Start Procedure. = ");

    ////////////////////////////////
    // インターフェース情報を取得する
    ////////////////////////////////
    $dbAccess = new DBAccesser($db_access_user_id);
    $dbAccess->connect();

    $ifInfoRows = $dbAccess->selectRows("B_ANSTWR_IF_INFO");

    $num_of_rows = count($ifInfoRows);
    // 設定無しの場合
    if($num_of_rows === 0) {
        throw new Exception("No records in if_info.");
    // 重複登録の場合
    } elseif($num_of_rows > 1) {
        throw new Exception("More than one record in if_info.");
    }

    $ansibleTowerIfInfo = $ifInfoRows[0];
    $logger->trace('$ansibleTowerIfInfo : ' . var_export($ansibleTowerIfInfo, true));

    ////////////////////////////////
    // 実行対象の作業を準備中にする
    ////////////////////////////////
    //   トランザクション開始
    $logger->debug("Begin Transaction.");

    if($dbAccess->beginTransaction() === false) {
        $warning_flag = 1;
        throw new Exception("Faild to begin transaction.");
    }

    // 作業管理を参照し、実行対象の作業を取得する
    $toPrepareCondition = 
        " ( "
            . "( TIME_BOOK IS NULL AND STATUS_ID in ( " . NOT_YET . "," . PREPARE . " ) ) "
            . "OR "
            . "( TIME_BOOK <= :KY_DB_DATETIME(6): AND STATUS_ID = " . RESERVE . " ) "
        .") ";
    $toPrepareExeInsList = $dbAccess->selectRows("C_ANSTWR_EXE_INS_MNG", false, $toPrepareCondition);

    foreach($toPrepareExeInsList as $toPrepareRow) {
        $toPrepareRow['STATUS_ID'] = PREPARE;
        $dbAccess->updateRow("C_ANSTWR_EXE_INS_MNG", $toPrepareRow);
    }

    //   コミット
    $logger->debug("Commit.");

    $r = $dbAccess->commit();
    if(!$r) {
        $warning_flag = 1;
        throw new Exception("Faild to commit.");
    }

    ////////////////////////////////
    // 作業管理を参照し、準備中の作業を取得する
    ////////////////////////////////
    $toProcessCondition = "STATUS_ID IN (" . implode (",", array(PREPARE)) . ") ";
    $toProcessExeInsList = $dbAccess->selectRows("C_ANSTWR_EXE_INS_MNG", false, $toProcessCondition);

    ////////////////////////////////
    // 先にRESTの認証通しておく
    ////////////////////////////////
    // トレースメッセージ
    $logger->debug("Authorize AnsibleTower.");

    $restApiCaller = new RestApiCaller($ansibleTowerIfInfo['ANSTWR_PROTOCOL'],
                                                   $ansibleTowerIfInfo['ANSTWR_HOSTNAME'],
                                                   $ansibleTowerIfInfo['ANSTWR_PORT'],
                                                   $ansibleTowerIfInfo['ANSTWR_USER_ID'],
                                                   $ansibleTowerIfInfo['ANSTWR_PASSWORD'],
                                                   $ansibleTowerIfInfo['ANSTWR_AUTH_TOKEN']); // 暗号復号は内部処理

    $response_array = $restApiCaller->authorize();
    if($response_array['success'] != true) {
        $warning_flag = 1;
        $logger->trace("URL: " . $ansibleTowerIfInfo['ANSTWR_PROTOCOL'] . "://"
                                . $ansibleTowerIfInfo['ANSTWR_HOSTNAME'] . ":"
                                . $ansibleTowerIfInfo['ANSTWR_PORT'] . "\n"
                                . "TOKEN: " . $ansibleTowerIfInfo['ANSTWR_AUTH_TOKEN'] . "\n");

        throw new Exception("Faild to authorize to ansible_tower. " . $response_array['responseContents']['errorMessage']);
    }

    ////////////////////////////////
    // 準備中の作業を実行する
    ////////////////////////////////
    // トレースメッセージ
    $logger->debug("Execute foreach. ---");

    foreach($toProcessExeInsList as $toProcessRow) {

        // データ準備
        $tgt_execution_no = $toProcessRow['EXECUTION_NO'];

        //   トランザクション開始
        $logger->debug("Begin transaction.");

        if($dbAccess->beginTransaction() === false) {
            throw new Exception("Faild to begin transaction.");
        }

        //////////////////////////////////////////////////////////////////
        // 投入オペレーションの最終実施日を更新する。
        //////////////////////////////////////////////////////////////////
        require_once($root_dir_path . "/libs/backyardlibs/common/common_db_access.php");
        $dbaobj = new BackyardCommonDBAccessClass($dbAccess->getdbMode(),$objDBCA,$objMTS,$db_access_user_id);
        $ret = $dbaobj->OperationList_LastExecuteTimestamp_Update($toProcessRow["OPERATION_NO_UAPK"]);
        if($ret === false) {
            $FREE_LOG = $dbaobj->GetLastErrorMsg();
            require ($log_output_php );
            throw new Exception("OperationList update error.");
        }
        unset($dbaobj);

        // 実行
        $process_has_error = false; // 1ループ内の処理継続監視
        //////////////////////////////////////////////////////////////////
        // データベースからansibleで実行する情報取得
        //////////////////////////////////////////////////////////////////
        // トレースメッセージ
        $logger->debug("Create ansible exec fileZip (exec_no: $tgt_execution_no)");
        $creater = new CreateAnsibleExecFiles($ansibleTowerIfInfo['ANSTWR_STORAGE_PATH_ITA'],
                                              $ansibleTowerIfInfo['ANSTWR_STORAGE_PATH_ANSTWR'], 
                                              $ansibleTowerIfInfo['SYMPHONY_STORAGE_PATH_ANSTWR'],
                                              $msgTplStorage,
                                              $GLOBALS['objDBCA']);

        // データベースからansibleで実行する情報取得し実行ファイル作成
        $ret = CreateAnsibleExecFilesfunction($creater,
                                              $tgt_execution_no,
                                              $toProcessRow["SYMPHONY_INSTANCE_NO"],
                                              $toProcessRow["PATTERN_ID"],
                                              $toProcessRow["OPERATION_NO_UAPK"],
                                              $toProcessRow["I_ANS_HOST_DESIGNATE_TYPE_ID"],
                                              $toProcessRow["I_ANS_WINRM_ID"],
                                              $toProcessRow["I_ANS_GATHER_FACTS"]);

        $tmp_array_dirs = $creater->getAnsibleWorkingDirectories($tgt_execution_no);
        $zip_data_source_dir = $tmp_array_dirs[1]; // "in" directory
        $exec_out_dir = $tmp_array_dirs[2]; // "out" directory // エラー時対応用

        unset($creater);

        if($ret === false || count(glob($zip_data_source_dir . "/" . "*")) <= 0) {
            $process_has_error = true;
            $error_flag = 1;
            $logger->error("Faild to create role directories.");
            copyErrorLogToExecLog($exec_out_dir);
        }

        // ----ZIPファイルを作成する
        $exeins_utn_file_dir = "";
        $zip_input_file = "";
        $created_zipfile_flag = false;
        if(!$process_has_error) {
            $zip_save_base_dir = $vg_exe_ins_input_file_dir . "/" . $file_subdir_zip_input;
            list($zip_input_file, $exeins_utn_file_dir) = createRelayedDataZIP($tgt_execution_no, "InputData_", $zip_data_source_dir, $zip_save_base_dir);

            // トレースメッセージ
            $logger->debug("Make ZIP. (" . $exeins_utn_file_dir . "/" . $zip_input_file . ")");
            $created_zipfile_flag = true;

        } // ZIPファイルを作成する----

        ////////////////////////////////////////////////////////////////
        // AnsibleTowerに必要なデータを生成                           //
        ////////////////////////////////////////////////////////////////
        $workflowTplId = -1;
        $director = null;
        if(!$process_has_error) {
            // トレースメッセージ
            $logger->debug("maintenance environment (exec_no: $tgt_execution_no)");

            $director = new ExecuteDirector($restApiCaller, $logger, $dbAccess, $exec_out_dir);
            $workflowTplId = $director->build($toProcessRow, $ansibleTowerIfInfo);
            if($workflowTplId == -1) {
                // メイン処理での異常フラグをON
                $process_has_error = true;
                $error_flag = 1;
                $logger->error("Faild to create ansibletower environment. (exec_no: $tgt_execution_no)");
                copyErrorLogToExecLog($exec_out_dir);
            }
        }

        $wfId = -1;
        $process_was_scrammed = false;
        if(!$process_has_error) {
            // トレースメッセージ
            $logger->debug("launch (exec_no: $tgt_execution_no)");

            // 実行直前に緊急停止確認
            if(isScrammedExecution($dbAccess, $tgt_execution_no)) {
                $process_was_scrammed = true;
            } else {
                // ジョブワークフロー実行
                $wfId = $director->launchWorkflow($workflowTplId);
                if($wfId == -1) {
                    $process_has_error = true;
                    $error_flag = 1;
                    $logger->error("Faild to launch workflowJob. (exec_no: $tgt_execution_no)");
                    $errorMessage = $msgTplStorage->getSomeMessage("ITAANSTWRH-ERR-40008");
                    $director->errorLogOut($errorMessage);
                    copyErrorLogToExecLog($exec_out_dir);
                } else {
                    $logger->debug("execution start up complated. (exec_no: $tgt_execution_no)");
                }
            }
        }

        // 実行結果登録
        if($process_was_scrammed) {
            // 緊急停止時
            $toProcessRow['TIME_START'] = "DATETIMEAUTO(6)";
            $toProcessRow['TIME_END']   = "DATETIMEAUTO(6)";
            $toProcessRow['STATUS_ID']  = SCRAM;
            if($created_zipfile_flag == true) {
                $toProcessRow['FILE_INPUT'] = $zip_input_file;
            }
        } else if($process_has_error) {
            // 異常時
            $toProcessRow['TIME_START'] = "DATETIMEAUTO(6)";
            $toProcessRow['TIME_END']   = "DATETIMEAUTO(6)";
            $toProcessRow['STATUS_ID']  = FAILURE;
            if($created_zipfile_flag == true) {
                $toProcessRow['FILE_INPUT'] = $zip_input_file;
            }
        } else {
            // 正常時
            $toProcessRow['TIME_START'] = "DATETIMEAUTO(6)";
            $toProcessRow['STATUS_ID']  = PROCESSING;
            $toProcessRow['FILE_INPUT'] = $zip_input_file;
        }

        // トレースメッセージ
        $logger->debug("Update execution_management row. status=>" . $toProcessRow['STATUS_ID']);

        $dbAccess->updateRow("C_ANSTWR_EXE_INS_MNG", $toProcessRow);

        // JnlSeqはトランザクション内で取る
        $intJournalSeqNo = getNewestExeInsJnlId($tgt_execution_no);

        //   コミット
        $logger->debug("Commit.");

        $r = $dbAccess->commit();
        if(!$r) {
            throw new Exception("Faild to commit.");
        }

        if($created_zipfile_flag == true) {
            // トレースメッセージ
            $logger->debug("copy ZipFile for JnlTbl");

            list($resultBool, $msg) = moveRelayedDataZIPtoJnlDir($intJournalSeqNo, $zip_input_file, $exeins_utn_file_dir);
            if($resultBool == false) {
                $warning_flag = 1;
                // エラーメッセージ
                $logger->debug($msg);
            } else {
                // トレースメッセージ
                $logger->trace($msg);
            }
        }

        // 実行失敗時にはここで消す、成功時には確認君で確認して消す
        if(($process_was_scrammed || $process_has_error) &&
            $toProcessRow['I_ANSTWR_DEL_RUNTIME_DATA'] == 1 &&
            $director != null) {

            $ret = $director->delete($tgt_execution_no);
            if($ret == false) {
                $warning_flag = 1;
                $logger->error("Faild to cleanup ansibletower environment. (exec_no: $tgt_execution_no)");
            } else {
                $logger->debug("Cleanup ansibletower environment SUCCEEDED. (exec_no: $tgt_execution_no)");
            }
        }
    } // foreach($toProcessExeInsList as $toProcessRow)

} catch (Exception $e) {

    $error_flag = 1;
    $logger->error("Exception occured.");

    // 例外メッセージ出力
    $logger->error($e->getMessage());
    $logger->trace($e->getTraceAsString());

    if($dbAccess->inTransaction()) {
        // ロールバック
        if($dbAccess->rollback() === true) {
            $logger->error("Rollback.");
        } else {
            $logger->error("Faild to rollback.");
        }
    }
} finally {

    if(!empty($dbAccess)) {
        $dbAccess = null;
    }

    if(!empty($restApiCaller)) {
        $restApiCaller = null;
    }
}

////////////////////////////////
//// 結果出力
////////////////////////////////
// 処理結果コードを判定してアクセスログを出し分ける
if($error_flag != 0) {
    // 終了メッセージ
    $logger->error(" = Finished Procedure. [state: ERROR] = ");
    exit(2);
} elseif($warning_flag != 0) {
    // 終了メッセージ
    $logger->warn (" = Finished Procedure. [state: WARNING] = ");
    exit(2);
} else {
    // 終了メッセージ
    $logger->debug(" = Finished Procedure. [state: SUCCESS] = ");
    exit(0);
}

// end Main Logic

//////////////////////////////////////////////////////////////////
// データベースからansibleで実行する情報取得し実行ファイル作成
//////////////////////////////////////////////////////////////////
function CreateAnsibleExecFilesfunction($creater,
                                        $in_execution_no,
                                        $in_symphony_instance_no,
                                        $in_pattern_id,
                                        $in_operation_id,
                                        $in_hostaddres_type,
                                        $in_winrm_id,
                                        $in_gather_facts_flg){
    global $logger;
    global $root_dir_path;

    $hostlist         = array();
    $hostprotocollist = array();
    $host_vars        = array();

    $rolenamelist     = array();
    $role_rolenamelist = array();
    $role_rolevarslist = array();

    $MultiArray_vars_list = array();
    $hostinfolist      = array();

    $ret = $creater->CreateAnsibleWorkingDir($in_execution_no,
                                             $in_hostaddres_type,
                                             $in_winrm_id,
                                             $root_dir_path . '/' . DF_ROLE_PACKAGE_FILE_CONTENTS_DIR, //ロールパッケージファイルディレクトリ
                                             $in_pattern_id,
                                             $role_rolenamelist,
                                             $role_rolevarslist,
                                             $in_symphony_instance_no
                                             );
    if($ret <> true){
        $logger->error("Error. CreateAnsibleWorkingDir");
        return false;
    }

    ///////////////////////////////////////////////////////////////////////////////////////
    // データベースから処理対象ホストの情報を取得
    // $hostlist:              ホスト一覧返却配列
    //                         [管理システム項番]=[ホスト名(IP)]
    // $hostprotcollist:       ホスト毎プロトコル一覧返却配列
    //                         [ホスト名(IP)][ホスト名][PROTOCOL_NAME][LOGIN_USER]=LOGIN_PASSWD
    // 既存のデータが重なるが、今後の開発はこの変数を使用する。
    // $hostinfolist:          機器一覧ホスト情報配列
    //                         [ホスト名(IP)]=HOSTNAME=>''             ホスト名
    //                                        PROTOCOL_ID=>''          接続プロトコル
    //                                        LOGIN_USER=>''           ログインユーザー名
    //                                        LOGIN_PW_HOLD_FLAG=>''   パスワード管理フラグ
    //                                                                 1:管理(●)   0:未管理
    //                                        LOGIN_PW=>''             パスワード
    //                                                                 パスワード管理が1の場合のみ有効
    //                                        LOGIN_AUTH_TYPE=>''      Ansible認証方式
    //                                                                 2:パスワード認証 1:鍵認証
    //                                        WINRM_PORT=>''           WinRM接続プロトコル
    //                                        OS_TYPE_ID=>''           OS種別
    ///////////////////////////////////////////////////////////////////////////////////////
    $ret = $creater->getDBHostList($in_pattern_id,
                                     $in_operation_id,
                                     $hostlist,
                                     $hostprotocollist,
                                     $hostinfolist);
    if($ret <> true){
        $logger->error("Error. getDBHostList");
        return false;
    }

    /////////////////////////////////////////////////////////////////////////////
    // データベースからロール名を取得
    //   $rolenamelist:     ロール名返却配列
    //                      [実行順序][ロールID(Pkey)]=>ロール名
    /////////////////////////////////////////////////////////////////////////////
    $ret = $creater->getDBRoleList($in_pattern_id, $rolenamelist);
    if($ret <> true){
        $logger->error("Error. getDBRoleList");
        return false;
    }

    /////////////////////////////////////////////////////////////////////////////
    // データベースから変数情報を取得する。
    //   $host_vars:        変数一覧返却配列
    //                      [ホスト名(IP)][ 変数名 ]=>具体値
    /////////////////////////////////////////////////////////////////////////////
    $ret = $creater->getDBRoleVarList($in_pattern_id,
                                        $in_operation_id,
                                        $host_vars,
                                        $MultiArray_vars_list);

    if($ret <> true){
        $logger->error("Error. getDBRoleVarList");
        return false;
    }

    $ret = $creater->addSystemvars($host_vars, $hostprotocollist);

    // ansibleで実行するファイル作成
    $ret = $creater->CreateAnsibleWorkingFiles($hostlist,
                                                 $host_vars,
                                                 $rolenamelist,
                                                 $role_rolenamelist,
                                                 $role_rolevarslist,
                                                 $hostprotocollist,
                                                 $hostinfolist,
                                                 $MultiArray_vars_list,
                                                 $in_gather_facts_flg);
    if($ret <> true){
        $logger->error("Error. CreateAnsibleWorkingFiles");
        return false;
    }
    return true;
}

function copyErrorLogToExecLog($exec_out_dir) {

    $error_log = $exec_out_dir . "/" . "error.log";
    $exec_log = $exec_out_dir . "/" . "exec.log";
    if(file_exists($exec_out_dir) && file_exists($error_log) ) {

        if(file_exists($exec_log)) {
            $error_log_data = "\n" . file_get_contents($error_log);
            $ret = file_put_contents($exec_log, $error_log_data, FILE_APPEND | LOCK_EX);

            if($ret === false) {
                $logger->error("Error. Faild to copy(add) 'error.log' to 'exec.log'. Can not display browse.");
            }
        } else {
            $ret = copy($error_log, $exec_log);

            if($ret === false) {
                $logger->error("Error. Faild to copy 'error.log' to 'exec.log'. Can not display browse.");
            }
        }
    }
}
