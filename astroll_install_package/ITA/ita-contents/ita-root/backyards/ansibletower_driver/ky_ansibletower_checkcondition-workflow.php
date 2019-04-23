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
//      作業状態確認ファイル
//      AnsibleTower作業確認
//      1:実行中の作業を順次確認し、ログファイルを更新していく
//      2:完了した作業は対象行のステータスを変更し、終了時間を記録する。
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
$db_access_user_id  = -121002; // AnsibleTower状態確認プロシージャ

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
$ansible_create_files_php   = $root_dir_path . "/libs/backyardlibs/ansibletower_driver/role_package/CreateAnsibleExecFiles.php";
require_once($ansible_create_files_php);
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
    $logger->debug(" = Start Procedure. =");

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
    // 作業管理を参照し、実行中の作業を取得する
    ////////////////////////////////
    // 実行中・実行中(遅延)と、緊急停止中（緊急停止 && 終了日時null)
    $toProcessCondition = "("
                        . "  STATUS_ID IN (" . implode (",", array(PROCESSING, PROCESS_DELAYED)) . ") "
                        . "  OR "
                        . "  (STATUS_ID = " . SCRAM . " AND TIME_END IS NULL)"
                        . ")";
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
        $logger->trace("URL: " . $ansibleTowerIfInfo['ANSTWR_PROTOCOL'] . "://"
                                . $ansibleTowerIfInfo['ANSTWR_HOSTNAME'] . ":"
                                . $ansibleTowerIfInfo['ANSTWR_PORT'] . "\n"
                                . "TOKEN: " . $ansibleTowerIfInfo['ANSTWR_AUTH_TOKEN'] . "\n"); 

        throw new Exception("Faild to authorize to ansible_tower. " . $response_array['responseContents']['errorMessage']);
    }

    ////////////////////////////////
    // 実行中の作業を確認する
    ////////////////////////////////
    // トレースメッセージ
    $logger->debug("Checkcondition foreach. ---");

    foreach($toProcessExeInsList as $toProcessRow) {

        // データ準備
        $tgt_execution_no = $toProcessRow['EXECUTION_NO'];

        //   トランザクション開始
        $logger->debug("Begin transaction.");

        if($dbAccess->beginTransaction() === false) {
            throw new Exception("Faild to begin transaction.");
        }

        // この時点で作業実行レコードのステータス再取得して緊急停止ボタンが押されていれば、最後のレコード更新ステータスをSCRAMにする
        // ただし、処理中のステータスはTowerから取得した値を見て処理を分ける
        $record_status = "";
        if(isScrammedExecution($dbAccess, $tgt_execution_no)) {
            $record_status = SCRAM;
        }

        ////////////////////////////////////////////////////////////////
        // AnsibleTower監視
        ////////////////////////////////////////////////////////////////
        // トレースメッセージ
        $logger->debug("monitoring environment (exec_no: $tgt_execution_no)");

        $director = new ExecuteDirector($restApiCaller, $logger, $dbAccess, "");
        $status = $director->monitoring($toProcessRow, $ansibleTowerIfInfo);

        ////////////////////////////////////////////////////////////////
        // 遅延チェック                                         //
        ////////////////////////////////////////////////////////////////
        switch($status) {
            case PROCESSING:
                // 遅延を判定
                // ステータスが実行中(3)、かつ制限時間が設定されている場合のみ遅延判定する
                if($toProcessRow['STATUS_ID'] == PROCESSING && $toProcessRow['I_TIME_LIMIT'] != "") {
                    // 開始時刻(「UNIXタイム.マイクロ秒」)を生成
                    $varTimeDotMirco = convFromStrDateToUnixtime($toProcessRow['TIME_START'], true);

                    // 開始時刻(マイクロ秒)＋制限時間(分→秒)＝制限時刻(マイクロ秒)
                    $varTimeDotMirco_limit = $varTimeDotMirco + ($toProcessRow['I_TIME_LIMIT'] * 60); //単位（秒）

                    // 現在時刻(「UNIXタイム.マイクロ秒」)を生成
                    $varTimeDotNowStd = getMircotime(0);

                    // 制限時刻と現在時刻を比較
                    if( $varTimeDotMirco_limit < $varTimeDotNowStd ) {
                        $status = PROCESS_DELAYED;
                        // トレースメッセージ
                        $logger->debug("exesution_no[" . $execution_no . "] is delayed. ");
                    }

                    // トレースメッセージ
                    $logger->debug("now[" . convFromUnixtimeToStrDate($varTimeDotNowStd) . "] / " .
                                    "limit[" . convFromUnixtimeToStrDate($varTimeDotMirco_limit) . "] / " .
                                    "start[" . $toProcessRow['TIME_START'] . "]");
                // } else {
                }

                if($status == PROCESSING) {
                    // この作業実行は処理中のため、他にやることなし、スキップ
                    // トレースメッセージ
                    $logger->debug("This execution is still running. skip this. execution_no: " . $tgt_execution_no);
                    // continue;
                    continue 2; // PHPの言語仕様。switch中のcontinueはswitchに食われる
                }
                break;
            case COMPLETE:
            case FAILURE:
            case SCRAM:
            case EXCEPTION:
                // 何もしない
                break;
            default:
                $error_flag = 1;
                $status = EXCEPTION;
                break;
        }

        $process_has_error = false; // 1ループ内の処理継続監視
        $created_zipfile_flag = false; // ResultZipの作成状況
        $exeins_utn_file_dir = "";
        $zip_result_file = "";
        $execution_finished_flag = false;

        ////////////////////////////////////////////////////////////////
        // ResultZIP作成
        ////////////////////////////////////////////////////////////////
        $finishedStatusArray = array(COMPLETE, FAILURE, EXCEPTION, SCRAM);
        if(in_array($status, $finishedStatusArray)) {

            // トレースメッセージ
            $logger->debug("Create ResultZip in finished execution. status=>" . $status);

            $execution_finished_flag = true;

            // ZIP出力先Directoryパス設定
            $creater = new CreateAnsibleExecFiles($ansibleTowerIfInfo['ANSTWR_STORAGE_PATH_ITA'],
                                                $ansibleTowerIfInfo['ANSTWR_STORAGE_PATH_ANSTWR'], 
                                                $ansibleTowerIfInfo['SYMPHONY_STORAGE_PATH_ANSTWR'],
                                                 $msgTplStorage,
                                                 $GLOBALS['objDBCA']);

            $tmp_array_dirs = $creater->getAnsibleWorkingDirectories($tgt_execution_no);
            $zip_data_source_dir = $tmp_array_dirs[2]; // out direcotory

            unset($creater);

            if(count(glob($zip_data_source_dir . "/" . "*")) <= 0) {
                $process_has_error = true;
                $error_flag = 1;
                $logger->error("Faild to create role directories.");
            }

            // ----ZIPファイルを作成する
            if(!$process_has_error) {
                $zip_save_base_dir = $vg_exe_ins_result_file_dir . "/" . $file_subdir_zip_result;
                list($zip_result_file, $exeins_utn_file_dir) = createRelayedDataZIP($tgt_execution_no, "ResultData_", $zip_data_source_dir, $zip_save_base_dir);

                // トレースメッセージ
                $logger->debug("Make ZIP. (" . $exeins_utn_file_dir . "/" . $zip_result_file . ")");
                $created_zipfile_flag = true;

            } // ZIPファイルを作成する----
        }

        // 確認前に取得したステータスがSCRAMであれば、どんな結果でもSCRAMにする
        if($record_status == SCRAM) {
            $status = SCRAM;
        }

        $toProcessRow['STATUS_ID'] = $status;

        ////////////////////////////////////////////////////////////////
        // 実行結果登録                                         //
        ////////////////////////////////////////////////////////////////
        if(!$process_has_error) {
            if($execution_finished_flag == true) {
                $toProcessRow['TIME_END'] = "DATETIMEAUTO(6)";
            }
            // 正常時
            $toProcessRow['STATUS_ID']  = $status;
        } else {
            // 異常時
            $toProcessRow['TIME_END']   = "DATETIMEAUTO(6)";
            $toProcessRow['STATUS_ID']  = FAILURE;
        }
        if($created_zipfile_flag == true) {
            $toProcessRow['FILE_RESULT'] = $zip_result_file;
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

        // ResultZIPを履歴へも反映
        if($created_zipfile_flag == true) {
            // トレースメッセージ
            $logger->debug("copy ZipFile for JnlTbl");

            list($resultBool, $msg) = moveRelayedDataZIPtoJnlDir($intJournalSeqNo, $zip_result_file, $exeins_utn_file_dir);
            if($resultBool == false) {
                $warning_flag = 1;
                $logger->error($msg); // 失敗時エラーログ
            } else {
                $logger->debug($msg); // 成功時トレースログ
            }
        }

        // 成功時にAnsibleTowerの情報を消す
        if($execution_finished_flag == true && $toProcessRow['I_ANSTWR_DEL_RUNTIME_DATA'] == 1 && $director != null) {
            $ret = $director->delete($tgt_execution_no);
            if($ret == false) {
                $warning_flag = 1;
                $logger->error("Faild to clean up ansibletower environment. (exec_no: $tgt_execution_no)");
            } else {
                $logger->debug("Clean up ansibletower environment SUCCEEDED. (exec_no: $tgt_execution_no)");
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

?>
