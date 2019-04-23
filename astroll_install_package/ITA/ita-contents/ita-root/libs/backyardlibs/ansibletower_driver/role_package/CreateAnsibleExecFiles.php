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
//    ・AnsibleTowerの実行に必要な情報をデータベースから取得しAnsibleTower実行ファイルを生成する。
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

require_once ($root_dir_path . "/libs/backyardlibs/ansibletower_driver/setenv.php");
require_once ($root_dir_path . "/libs/backyardlibs/ansibletower_driver/AnsibleTowerCommonLib.php");
require_once ($root_dir_path . "/libs/backyardlibs/ansibletower_driver/role_package/chkCPFVarsLib.php");
require_once ($root_dir_path . "/libs/backyardlibs/ansibletower_driver/role_package/ItaAnsibleRoleStructure.php");

class CreateAnsibleExecFiles {

    // Ansible 作業ディレクトリ名
    const LC_ANS_IN_DIR                     = "in";
    const LC_ANS_OUT_DIR                    = "out";
    const LC_ANS_TMP_DIR                    = "tmp";
    const LC_ANS_HOST_VARS_DIR              = "host_vars";

    const LC_VARS_ATTR_STD                  = LC_VARS_ATTR_STD;     // 一般変数
    const LC_VARS_ATTR_LIST                 = LC_VARS_ATTR_LIST;    // 複数具体値
    const LC_VARS_ATTR_STRUCT               = LC_VARS_ATTR_STRUCT;  // 多次元変数

    // ホストグループ変数ディレクトリ
    const LC_ANS_GROUP_VARS_DIR             = "group_vars";

    // ユーザー公開用データリレイストレージパス 変数の名前
    const LC_ANS_OUTDIR_DIR                 = "user_files";

    // host_varsに埋め込まれるリモートログインのユーザー用変数の名前
    const LC_ANS_USERNAME_VAR_NAME          = "__loginuser__";
    // host_varsに埋め込まれるリモートログインのパスワード用変数の名前
    const LC_ANS_PASSWD_VAR_NAME            = "__loginpassword__";
    // host_varsに埋め込まれるホスト名用変数の名前
    const LC_ANS_LOGINHOST_VAR_NAME         = "__loginhostname__";

    // ユーザー公開用 AnsibleTower作業用データリレイストレージパス 変数の名前
    const LC_ANS_OUTDIR_VAR_NAME            = "__workflowdir__";

    // ユーザー公開用 symphonyインスタンス作業用データリレイストレージパス 変数の名前
    const LC_SYMPHONY_DIR_VAR_NAME           = "__symphony_workflowdir__";

    // 管理対象システム一覧のログイン・パスワード未登録時の内部変数値
    const LC_ANS_UNDEFINE_NAME              = "__undefinesymbol__";

    // Ansible 作業ファイル名
    const LC_ANS_HOSTS_FILE                 = "hosts";

    const LC_ANS_ROLE_PLAYBOOK_FILE         = "site.yml";

    // WINRM接続ポート デフォルト値
    const LC_WINRM_PORT                     = 5985;

    // 機器一覧 パスワード管理フラグ(LOGIN_PW_HOLD_FLAG)
    const LC_LOGIN_PW_HOLD_FLAG_OFF         = '0';         // パスワード管理なし
    const LC_LOGIN_PW_HOLD_FLAG_ON          = '1';         // パスワード管理あり
    const LC_LOGIN_PW_HOLD_FLAG_DEF         = '0';         // デフォルト値 パスワード管理なし
    // 機器一覧 Ansible認証方式(LOGIN_AUTH_TYPE)
    const LC_LOGIN_AUTH_TYPE_KEY            = '1';         // 鍵認証
    const LC_LOGIN_AUTH_TYPE_PW             = '2';         // パスワード認証
    const LC_LOGIN_AUTH_TYPE_DEF            = '1';         // デフォルト値 鍵認証

    // copyファイル格納ディレクトリ名
    const LC_ANS_COPY_FILES_DIR             = "copy_files";
    // ssh鍵ファイル格納ディレクトリ名
    const LC_ANS_SSH_KEY_FILES_DIR          = "ssh_key_files";
    // WinRMサーバー証明書ファイル格納ディレクトリ名
    const LC_ANS_WIN_CA_FILES_DIR           = "winrm_ca_files";
    const LC_ITA_SSH_KEY_FILE_PATH          = "/uploadfiles/2100000303/CONN_SSH_KEY_FILE";
    const LC_ITA_WIN_CA_FILE_PATH           = "/uploadfiles/2100000303/WINRM_SSL_CA_FILE";

    //ローカル変数定義
    private $lv_hostaddress_type;                   // null or 1:IP方式  2:ホスト名方式

    //ansible用各ディレクトリ変数
    private $lv_ansible_ita_base_Dir;               // Ansible作業ベースディレクトリ(ITA側)
    private $lv_ansible_ans_base_Dir;               // Ansible作業ベースディレクトリ(ansible側)
    private $lv_symphony_ans_base_Dir;              // Symphony作業ベースディレクトリ(ansible側)
    private $lv_Ansible_in_Dir;                     // inディレクトリ
    private $lv_Ansible_out_Dir;                    // outディレクトリ
    private $lv_Ansible_host_vars_Dir;              // host_varsディレクトリ

    private $lv_Hostvarsfile_template_file_Dir;     // inディレクトリ配下 テンプレートファイルパス

    private $lv_winrm_id;                           // 作業パターンの接続先がwindowsかを判別する項目

    // テーブル名定義
    private $lv_ansible_vars_masterDB;              // 変数管理 テーブル名
    private $lv_ansible_vars_assignDB;              // 代入値管理 テーブル名
    private $lv_ansible_pattern_vars_linkDB;        // 作業パターン変数紐付管理 テーブル名
    private $lv_ansible_pho_linkDB;                 // 作業対象ホスト テーブル名

    private $lv_ansible_pattern_linkDB;             // 作業パターン詳細 テーブル名
    private $lv_ansible_role_packageDB;             // ロールパッケージ管理 テーブル名
    private $lv_ansible_roleDB;                     // ロール管理 テーブル名
    private $lv_ansible_role_varsDB;                // ロール変数管理 テーブル名

    private $lv_ansible_array_memberDB;             // 多段変数メンバ管理 テーブル名
    private $lv_ansible_member_col_combDB;          // 多段変数配列組合せ管理 テーブル名

    private $lv_ansible_rep_var_listDB;             // 読替表 テーブル名

    //copy_filesディレクトリ(in側フルパス)
    private $lv_Ansible_copy_files_Dir; 

    //inディレクトリ配下 コピーファイルパス(相対パス)
    private $lv_Hostvarsfile_copy_file_Dir;

    //copyファイル格納ディレクトリ(ITA側フルパス)
    private $lv_ita_copy_files_Dir;

    private $run_pattern_id;

    private $lv_objMTS;
    private $lv_objDBCA;

    // グローバル変数管理
    private $lva_global_vars_list;

    // ロール内のplaybookで定義されているcopy変数のリスト
    private $lva_cpf_vars_list = array();

    // ユーザー公開用データリレイストレージパス
    private $lv_user_out_Dir;
    // ユーザー公開用symphonyインスタンスストレージパス
    private $lv_symphony_instance_Dir;

    // 読替表のデータリスト
    private $translationtable_list;

    // Ansible実行時のinディレクトリ配下のSSH秘密鍵ファイル格納ディレクトリパス
    private $lv_Ansible_ssh_key_files_Dir;

    // Ansible実行時のinディレクトリ配下のWinRMサーバー証明書格納ディレクトリパス
    private $lv_Ansible_win_ca_files_Dir;

    // 変数具体値でCPF_を設定したコピー変数リスト
    private $lv_varsval_cpf_vars_list;

    ////////////////////////////////////////////////////////////////////////////////
    // 処理内容
    //   コンストラクタ
    // パラメータ
    //   $in_ansible_ita_base_dir: ansible作業用 NFSベースディレクトリ (ITA側)
    //   $in_ansible_ans_base_dir: ansible作業用 NFSベースディレクトリ (Ansible側)
    //   $in_symphony_ans_base_dir: symphony作業用 NFSベースディレクトリ (Ansible側)
    //   &$in_objMTS:       メッセージ定義クラス変数
    //   &$in_objDBCA:      データベースアクセスクラス変数
    // 
    // 戻り値
    //   なし
    ////////////////////////////////////////////////////////////////////////////////
    // function __construct( $in_base_dir,
    function __construct( $in_ansible_ita_base_dir,
                          $in_ansible_ans_base_dir,
                          $in_symphony_ans_base_dir,
                         &$in_objMTS,
                         &$in_objDBCA){
        global $vg_copy_contents_dir;
        global $vg_ansible_vars_masterDB;
        global $vg_ansible_vars_assignDB;
        global $vg_ansible_pattern_vars_linkDB;
        global $vg_ansible_pho_linkDB;
        global $vg_ansible_pattern_linkDB;
        global $vg_ansible_role_packageDB;
        global $vg_ansible_roleDB;
        global $vg_ansible_role_varsDB;
        global $vg_ansible_array_memberDB;
        global $vg_ansible_member_col_combDB;
        global $vg_ansible_rep_var_listDB;

        //ansible用ベースディレクトリ
        $this->lv_ansible_ita_base_Dir      = $in_ansible_ita_base_dir;
        $this->lv_ansible_ans_base_Dir      = $in_ansible_ans_base_dir;
        $this->lv_symphony_ans_base_Dir     = $in_symphony_ans_base_dir;

        //ITAcopyファイル格納ディレクトリ
        $this->lv_ita_copy_files_Dir        = $vg_copy_contents_dir;

        // 変数管理 テーブル名
        $this->lv_ansible_vars_masterDB     = $vg_ansible_vars_masterDB;
        // 代入値管理 テーブル名
        $this->lv_ansible_vars_assignDB     = $vg_ansible_vars_assignDB;
        // 作業パターン変数紐付管理 テーブル名
        $this->lv_ansible_pattern_vars_linkDB = $vg_ansible_pattern_vars_linkDB;
        // 作業対象ホスト テーブル名
        $this->lv_ansible_pho_linkDB        = $vg_ansible_pho_linkDB;

        // 作業パターン詳細 テーブル名
        $this->lv_ansible_pattern_linkDB    = $vg_ansible_pattern_linkDB;
        // ロールパッケージ管理 テーブル名
        $this->lv_ansible_role_packageDB    = $vg_ansible_role_packageDB;  
        // ロール管理 テーブル名
        $this->lv_ansible_roleDB            = $vg_ansible_roleDB;
        // ロール変数管理 テーブル名
        $this->lv_ansible_role_varsDB       = $vg_ansible_role_varsDB;

        // 多段変数メンバ管理 テーブル名
        $this->lv_ansible_array_memberDB    = $vg_ansible_array_memberDB;
        // 多段変数配列組合せ管理 テーブル名
        $this->lv_ansible_member_col_combDB = $vg_ansible_member_col_combDB;

        // 読替表 テーブル名
        $this->lv_ansible_rep_var_listDB    = $vg_ansible_rep_var_listDB;

        //outディレクトリ
        $lv_Ansible_out_Dir = "";

        // 変数具体値でCPF_を設定したコピー変数リスト
        $this->lv_varsval_cpf_vars_list     = array();

        $this->lv_objMTS  = $in_objMTS;
        $this->lv_objDBCA = $in_objDBCA;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F0027
    // 処理内容
    //   ansible用作業ディレクトリを作成する。
    //   ディレクトリ階層
    //   /ベースディレクトリ/ドライバ名/作業実行番号/in
    //                                    /out
    //                                    /tmp
    // パラメータ
    //   $in_execno              作業実行番号
    // 
    // 戻り値
    //   array:  キー[0]～[2]：各種ディレクトリパス 
    //   false:  異常
    ////////////////////////////////////////////////////////////////////////////////
    function getAnsibleWorkingDirectories($in_execno){
        $aryRetAnsibleWorkingDir = array();

        $base_dir = $this->lv_ansible_ita_base_Dir;
        //ベースディレクトリの存在チェック
        if( !is_dir( $base_dir ) ){
            //ベースディレクトリが存在しない場合はエラー
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-55201") . $this->lv_ansible_ita_base_Dir;
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
            return false;
        }

        // 返し値[0] 作業実行番号付き
        $c_dir_per_exe_no           = $base_dir . "/" . addPadding($in_execno);
        $aryRetAnsibleWorkingDir[0] = $c_dir_per_exe_no;

        // 返し値[1] 作業実行番号付き+inフォルダ名
        $aryRetAnsibleWorkingDir[1] = $c_dir_per_exe_no . "/" . self::LC_ANS_IN_DIR;

        // 返し値[2] 作業実行番号付き+outフォルダ名
        $aryRetAnsibleWorkingDir[2] = $c_dir_per_exe_no . "/" . self::LC_ANS_OUT_DIR;

        return $aryRetAnsibleWorkingDir;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F0001
    // 処理内容
    //   ansible用作業ディレクトリを作成する。
    //   ディレクトリ階層
    //   /ベースディレクトリ/ドライバ名/オケストレータID/作業実行番号/in
    //                                                               /out
    //                                                               /tmp
    // パラメータ
    //   $in_execno              作業実行番号
    //   $in_hostaddress_type    ホストアドレス方式
    //                           null or 1:IP方式  2:ホスト名方式
    //   $in_winrm_id            対象ホストがwindowsかを判別
    //                           1: windows 他:windows以外
    //   $in_zipdir              Legacy-Role パッケージファイルディレクトリ
    //                           ※Legacy-Role時のみ必須
    //   $in_pattern_id          作業パターンID
    //                           ※Legacy-Role時のみ必須
    //   $ina_rolenames          Legacy-Role role名リスト
    //                           ※Legacy-Role時のみ必須
    //                             $ina_rolename[role名]
    //   $ina_rolevars           Legacy-Role role内変数リスト
    //                           ※Legacy-Role時のみ必須
    //                             $ina_rolevars[role名][変数名]=0
    //   $in_role_rolepackage_id ロールパッケージ管理 Pkey 返却
    //                           ※Legacy-Role時のみ必須
    //   $in_symphony_instance_no:  symphonyから起動された場合のsymphonyインスタンスID
    //                              作業実行の場合は空白
    // 
    // 戻り値
    //   true:   正常
    //   false:  異常
    ////////////////////////////////////////////////////////////////////////////////
    function CreateAnsibleWorkingDir($in_execno,
                                     $in_hostaddress_type,
                                     $in_winrm_id,
                                     $in_zipdir,
                                     $in_pattern_id,
                                     &$ina_rolenames,
                                     &$ina_rolevars,
                                     $in_symphony_instance_no
                                     ){
        $this->run_pattern_id = $in_pattern_id;

        // null or 1:IP方式  2:ホスト名方式
        $this->lv_hostaddress_type = $in_hostaddress_type;

        // 対象ホストがwindowsかを判別
        // 1: windows 他:windows以外
        $this->lv_winrm_id  = $in_winrm_id;

        //ドライバ区分ディレクトリ作成
        $aryRetAnsibleWorkingDir = $this->getAnsibleWorkingDirectories($in_execno);

        if( $aryRetAnsibleWorkingDir === false ){
            return false;
        }

        //作業実行番号用ディレクトリ作成
        $c_dir = $aryRetAnsibleWorkingDir[0];

        system('/bin/rm -rf ' . $c_dir . ' >/dev/null 2>&1');

        if( is_dir( $c_dir ) ){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-55238",array($in_execno,$c_dir));
            //作業実行番号用ディレクトリが存在している場合はエラー
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
            return false;
        }
        else{
            if( !mkdir( $c_dir, 0777 ) ){
                $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-55202",array(__LINE__)); 
                $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
                return false;
            }
            if( !chmod( $c_dir, 0777 ) ){
                $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-55203",array(__LINE__));
                $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
                return false;
            }
        }

        //inディレクトリ作成
        $c_indir = $aryRetAnsibleWorkingDir[1];

        if( !mkdir( $c_indir, 0777 ) ){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-55202",array(__LINE__)); 
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
            return false;
        }
        if( !chmod( $c_indir, 0777 ) ){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-55203",array(__LINE__));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
            return false;
        }
        // INディレクトリ名を記憶
        $this->lv_Ansible_in_Dir = $c_indir;

        // outディレクトリ作成
        $c_outdir = $aryRetAnsibleWorkingDir[2];
        if( !mkdir( $c_outdir, 0777 ) ){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-55202",array(__LINE__)); 
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
            return false;
        }
        if( !chmod( $c_outdir, 0777 ) ){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-55203",array(__LINE__));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
            return false;
        }
        // outディレクトリ名を記憶
        $this->lv_Ansible_out_Dir = $c_outdir;

        // ユーザー公開用データリレイストレージパス
        $this->lv_user_out_Dir = $c_outdir . "/" . self::LC_ANS_OUTDIR_DIR;
        if( !mkdir( $this->lv_user_out_Dir , 0777 ) ){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-55202",array(__LINE__)); 
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
            return false;
        }
        if( !chmod( $this->lv_user_out_Dir , 0777 ) ){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-55203",array(__LINE__));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
            return false;
        }

        // ホスト変数定義ファイルに記載するパスなのでAnsible側のストレージパスに変更
        $this->lv_user_out_Dir = str_replace($this->lv_ansible_ita_base_Dir,
                                             $this->lv_ansible_ans_base_Dir,
                                             $this->lv_user_out_Dir);

        // symphonyからの起動か判定
        if(strlen($in_symphony_instance_no) != 0) {
            // ユーザー公開用symphonyインスタンス作業用 データリレイストレージパス
            $this->lv_symphony_instance_Dir = $this->lv_symphony_ans_base_Dir . "/" . sprintf("%010s",$in_symphony_instance_no);
        }
        else {
            $this->lv_symphony_instance_Dir = $this->lv_user_out_Dir;
        }

        // copy_filesディレクトリ作成
        $c_dirwk = $c_indir . "/" . self::LC_ANS_COPY_FILES_DIR;
        if( !mkdir( $c_dirwk, 0777 ) ){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-55202",array(__LINE__)); 
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
            return false;
        }
        if( !chmod( $c_dirwk, 0777 ) ){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-55203",array(__LINE__));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
            return false;
        }

        // copy_filesディレクトリ名を記憶
        $this->lv_Ansible_copy_files_Dir = $c_dirwk;

        // ホスト変数ファイル内 copy_filesディレクトリパスを記憶
        $this->lv_Hostvarsfile_copy_file_Dir = self::LC_ANS_COPY_FILES_DIR;

        // ssh_key_filesディレクトリ作成
        $c_dirwk = $c_indir . "/" . self::LC_ANS_SSH_KEY_FILES_DIR;
        if( !mkdir( $c_dirwk, 0777 ) ){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-55202",array(__LINE__));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
            return false;
        }
        if( !chmod( $c_dirwk, 0777 ) ){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-55203",array(__LINE__));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
            return false;
        }

        // ssh_key_filesディレクトリ名を記憶
        $this->lv_Ansible_ssh_key_files_Dir = $c_dirwk;

        //win_ca_filesディレクトリ作成
        $c_dirwk = $c_indir . "/" . self::LC_ANS_WIN_CA_FILES_DIR;
        if( !mkdir( $c_dirwk, 0777 ) ){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-55202",array(__LINE__));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
            return false;
        }
        if( !chmod( $c_dirwk, 0777 ) ){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-55203",array(__LINE__));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
            return false;
        }
        // win_ca_fileディレクトリ名を記憶
        $this->lv_Ansible_win_ca_files_Dir = $c_dirwk;

        // 作業パターンIDに紐づくパッケージファイルを取得
        // パッケージファイルをZIPファイルをinディレクトリに解凍し
        // 不要なファイルを削除する。
        $in_role_rolepackage_name = "";
        $in_role_rolepackage_id = 0;
        $ret = $this->getRolePackageFile($in_pattern_id, $in_role_rolepackage_id, $in_role_rolepackage_name, $role_package_file);
        if($ret === false){
            return false;
        };

        // ローカル変数のリスト作成
        $system_vars = array();
        $system_vars[] = self::LC_ANS_USERNAME_VAR_NAME;
        $system_vars[] = self::LC_ANS_PASSWD_VAR_NAME;
        $system_vars[] = self::LC_ANS_LOGINHOST_VAR_NAME;

        // ユーザー公開用 AnsibleTower作業用データリレイストレージパス 変数の名前
        $system_vars[] = self::LC_ANS_OUTDIR_VAR_NAME;

        // ユーザー公開用 symphonyインスタンス作業用データリレイストレージパス 変数の名前
        $system_vars[] = self::LC_SYMPHONY_DIR_VAR_NAME;

        $roleObj = new ItaAnsibleRoleStructure($in_role_rolepackage_name,  $this->lv_Ansible_in_Dir,
                                                $system_vars,       true);

        // ロールパッケージファイル名(ZIP)を取得
        $ret = $roleObj->getAnsible_RolePackage_filePath($in_zipdir, $in_role_rolepackage_id, $role_package_file);
        if( $ret === false ){
            $lastError = $objRole->getLastError(); 
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$lastError);
            return false;
        }

        // inディレクトリにロールパッケージファイル(ZIP)展開
        if($roleObj->zipExtractTo() === false){
            $arryErrMsg = $roleObj->getlasterror();
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$arryErrMsg[0]);
            return false;
        }

        $ret = $roleObj->chkRolesDirectory();
        if($ret === false){
            if(@count($roleObj->getTranslateCombErrVars()) !== 0) {
                // ロール内の読替表で読替変数と任意変数の組合せが一致していない
                // TODO: LOG_LEVELによる処理の分岐
                $errmag  = $roleObj->getTranslationTableCombinationErrMsg(true);
                $this->LocalLogPrint(basename(__FILE__),__LINE__,$errmag);
                return false;

            } else if(@count($roleObj->getStructErrVars()) !== 0) {
                // defaults定義ファイルに変数定義が複数あり形式が違う変数がある場合
                // TODO: LOG_LEVELによる処理の分岐
                $errmag = $roleObj->getVarsStructErrMsg();
                $this->LocalLogPrint(basename(__FILE__),__LINE__,$errmag);
                return false;

            } else {
                $lastError = $roleObj->getLastError();
                $this->LocalLogPrint(basename(__FILE__),__LINE__,$lastError[1]);
                return false;
            }
        }

        // copy変数がファイル管理に登録されているか判定
        $this->lva_cpf_vars_list = $roleObj->getCopyVars();

        $strErrMsg = "";;
        $strErrDetailMsg = "";

        // $this->lva_cpf_vars_listの構造 CONTENTS_FILE_ID/CONTENTS_FILEはchkCPFVarsMasterRegの戻り値
        // $lva_cpf_vars_list[ロール名][ロール名/--/Playbook名][行番号][変数名]['CONTENTS_FILE_ID'] = Pkey
        // $lva_cpf_vars_list[ロール名][ロール名/--/Playbook名][行番号][変数名]['CONTENTS_FILE'] = ファイル名
        $ret = chkCPFVarsMasterReg($this->lv_objMTS,$this->lv_objDBCA,
                                             $this->lva_cpf_vars_list,
                                             $strErrMsg,$strErrDetailMsg);
        if($ret === false){
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$strErrMsg);
            if($strErrDetailMsg != ""){
                $this->DebugLogPrint(basename(__FILE__),__LINE__,$strErrDetailMsg);
            }
            return false;
        }

        // ロール名取得
        // $ina_rolename[role名]
        $ina_rolenames = $roleObj->getRoleNames();
        // ロール内の変数取得
        // $ina_varname[role名][変数名]=0
        $ina_rolevars  = $roleObj->getPlaybookVarsName();

        unset($roleObj);

        // 展開先にhostsファイルがあれば削除する。
        $wk_dir = $c_indir . "/" . self::LC_ANS_HOSTS_FILE;
        if( file_exists($wk_dir) === true ){
            exec("/bin/rm -f " . $wk_dir);
        }

        // 展開先にホスト変数ディレクトリがあれば削除する。
        $wk_dir = $c_indir . "/" . self::LC_ANS_HOST_VARS_DIR;
        if( file_exists($wk_dir) === true ){
            exec("/bin/rm -rf " . $wk_dir);
        }

        // 展開先にホストグループ変数ディレクトリがあれば削除する。
        $wk_dir = $c_indir . "/" . self::LC_ANS_GROUP_VARS_DIR;
        if( file_exists($wk_dir) === true ){
            exec("/bin/rm -rf " . $wk_dir);
        }

        // host_varsディレクトリ作成
        $c_dirwk = $c_indir . "/" . self::LC_ANS_HOST_VARS_DIR;
        if( !mkdir( $c_dirwk, 0777 ) ){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-55202",array(__LINE__)); 
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
            return false;
        }
        if( !chmod( $c_dirwk, 0777 ) ){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-55203",array(__LINE__));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
            return false;
        }

        // host_varsディレクトリ名を記憶
        $this->lv_Ansible_host_vars_Dir = $c_dirwk;

        return true;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F0002
    // 処理内容
    //   ansible用各作業ファイルを作成する。
    // 
    // パラメータ
    //   $ina_hosts:            ホスト名(IP)配列
    //                          [管理システム項番]=[ホスト名(IP)]
    //
    //   $ina_host_vars:        ホスト変数配列
    //                          [ホスト名(IP)][ 変数名 ]=>具体値
    //
    //   $ina_rolenames:        ロール名リスト配列(データベースの登録内容)
    //                          ※Legacy-Roleの場合のみ必須
    //                          [実行順序][ロールID(Pkey)]=>ロール名
    //
    //   $ina_role_rolenames:   ロール名リスト配列(Role内登録内容)
    //                          ※Legacy-Roleの場合のみ必須
    //                          [ロール名]
    //
    //   $ina_role_rolevars:    ロール内変数リスト配列(Role内登録内容)
    //                          ※Legacy-Roleの場合のみ必須
    //                          [ロール名][変数名]=0
    //
    //   $ina_hostprotcollist:  ホスト毎プロトコル一覧
    //                          [ホスト名(IP)][ホスト名][PROTOCOL_NAME][LOGIN_USER]=LOGIN_PASSWD
    //
    //   既存のデータが重なるが、今後の開発はこの変数を使用する。
    //   $ina_hostinfolist:     機器一覧ホスト情報配列
    //                          [ホスト名(IP)]=HOSTNAME=>''             ホスト名
    //                                         PROTOCOL_ID=>''          接続プロトコル
    //                                         LOGIN_USER=>''           ログインユーザー名
    //                                         LOGIN_PW_HOLD_FLAG=>''   パスワード管理フラグ
    //                                                                  1:管理(●)   0:未管理
    //                                         LOGIN_PW=>''             パスワード
    //                                                                  パスワード管理が1の場合のみ有効
    //                                         LOGIN_AUTH_TYPE=>''      Ansible認証方式
    //                                                                  2:パスワード認証 1:鍵認証
    //                                         WINRM_PORT=>''           WinRM接続プロトコル
    //                                         OS_TYPE_ID=>''           OS種別
    //
    //   $in_gather_facts_flg:   gather_factsの実施有無
    //                           1: 実施  他: 未実施
    //
    // 戻り値
    //   true:   正常
    //   false:  異常
    ////////////////////////////////////////////////////////////////////////////////
    function CreateAnsibleWorkingFiles($ina_hosts,
                                       $ina_host_vars,
                                       $ina_rolenames,
                                       $ina_role_rolenames,
                                       $ina_role_rolevars,
                                       $ina_hostprotcollist,
                                       $ina_hostinfolist,
                                       $ina_MultiArray_vars_list,
                                       $in_gather_facts_flg){
        //////////////////////////////////////
        // グローバル変数管理よりグローバル変数を取得
        //////////////////////////////////////
        $this->lva_global_vars_list = array();
        $getMsgstr = "";

        $ret = getDBGlobalVars($this->lva_global_vars_list, $getMsgstr);
        if($ret = false){
            $this->LocalLogPrint(basename(__FILE__),__LINE__, $getMsgstr);
            return false;
        }

        //////////////////////////////////////
        // 読替表のデータを取得する。
        //////////////////////////////////////
        $this->translationtable_list = array();
        $ret = $this->getDBTranslationTable($this->run_pattern_id,$this->translationtable_list);
        if($ret === false){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-5000011");
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
            return false;
        }

        //////////////////////////////////////
        // hostsファイル作成                //
        //////////////////////////////////////
        // #0001 hostsファイルにホスト名を設定可能するためにホスト毎プロトコル一覧を渡す。
        $ret = $this->CreateHostsfile("hostgroups", $ina_hosts, $ina_hostprotcollist, $ina_hostinfolist);
        if($ret === false){
            return false;
        }

        //////////////////////////////////////
        // ホスト変数定義ファイル作成       //
        //////////////////////////////////////
        // Role専用のモジュールでホスト変数定義ファイル作成
        $ret = $this->CreateRoleHostvarsfiles($ina_host_vars,
                                              $ina_hostprotcollist,
                                              $ina_MultiArray_vars_list);
        if($ret === false){
            return false;
        }

        //////////////////////////////////////////////////////////////////////////
        // Role内で使用しているcopyモジュール変数をホスト変数定義ファイルに追加 //
        //////////////////////////////////////////////////////////////////////////
        $ret = $this->CreateCopyVarsFiles($ina_hosts,
                                                $ina_hostprotcollist,
                                                $ina_rolenames,
                                                $this->lva_cpf_vars_list);
        if($ret === false){
            return false;
        }

        //////////////////////////////////////
        // Legacy-Role PlayBookファイル作成 //
        //////////////////////////////////////
        $ret = $this->CreateLegacyRolePlaybookfiles($ina_rolenames, $in_gather_facts_flg);
        if($ret === false){
            return false;
        }

        /////////////////////////////////////////////////
        // ロール内の変数定義チェック                  //
        /////////////////////////////////////////////////
        $ret = $this->CheckLegacyRolePlaybookfiles($ina_hosts,
                                                   $ina_host_vars,
                                                   $ina_rolenames,
                                                   $ina_role_rolenames,
                                                   $ina_role_rolevars);
        if($ret === false){
            return false;
        }

        return true;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F0003
    // 処理内容
    //   hostsファイルを作成する。
    // パラメータ
    //   $in_group_name:        ホストグループ名
    //   $ina_hosts:            ホスト名(IPアドレス)の配列
    //                          $ina_hosts[ホスト名(IP)]
    //   $ina_hostprotcollist:  ホスト毎プロトコル一覧
    //                          [ホスト名(IP)][ホスト名][PROTOCOL_NAME][LOGIN_USER]=LOGIN_PASSWD
    //   $ina_hostinfolist:     機器一覧ホスト情報配列
    //                          [ホスト名(IP)]=HOSTNAME=>''             ホスト名
    //                                         PROTOCOL_ID=>''          接続プロトコル
    //                                         LOGIN_USER=>''           ログインユーザー名
    //                                         LOGIN_PW_HOLD_FLAG=>''   パスワード管理フラグ
    //                                                                  1:管理(●)   0:未管理
    //                                         LOGIN_PW=>''             パスワード
    //                                                                  パスワード管理が1の場合のみ有効
    //                                         LOGIN_AUTH_TYPE=>''      Ansible認証方式
    //                                                                  2:パスワード認証 1:鍵認証
    //                                         WINRM_PORT=>''           WinRM接続プロトコル
    //                                         OS_TYPE_ID=>''           OS種別
    //                                         SYSTEM_ID=>''            機器一覧主キー
    //                                         SSH_KEY_FILE=>''         SSH秘密鍵ファイル
    // 
    // 戻り値
    //   true:   正常
    //   false:  異常
    ////////////////////////////////////////////////////////////////////////////////
    function CreateHostsfile($in_group_name,$ina_hosts,$ina_hostprotcollist,
                             $ina_hostinfolist){
        $file_name = $this->getAnsible_hosts_file();
        $fd = @fopen($file_name, "w");
        if($fd == null){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-55204",array(__LINE__));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
            return false;
        }
    
        $value = "[" . $in_group_name . "]\n";
        if( @fputs($fd, $value) === false ){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-55205",array(__LINE__));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
            return false;
        }

        foreach( $ina_hosts as $host_name ){
            $ssh_extra_args = "";
            if(strlen(trim($ina_hostinfolist[$host_name]['SSH_EXTRA_ARGS'])) != 0){
                $ssh_extra_args = trim($ina_hostinfolist[$host_name]['SSH_EXTRA_ARGS']);
                // "を\"に置き換え
                $ssh_extra_args = str_replace('"','\\"',trim($ina_hostinfolist[$host_name]['SSH_EXTRA_ARGS']));
                // hostsファイルに追加するssh_extra_argsを生成
                $ssh_extra_args = ' ansible_ssh_extra_args="' . $ssh_extra_args . '"';
            }

            $hosts_extra_args = "";
            if(strlen(trim($ina_hostinfolist[$host_name]['HOSTS_EXTRA_ARGS'])) != 0){
                $hosts_extra_args = trim($ina_hostinfolist[$host_name]['HOSTS_EXTRA_ARGS']);
            }

            $win_param = "";

            // 対象ホストがwindowsの場合またはパスワード認証か判定
            if(// 対象ホストがwindowsの場合
                ($this->lv_winrm_id == 1) ||
                // パスワード認証の場合
                ($ina_hostinfolist[$host_name]['LOGIN_AUTH_TYPE'] == self::LC_LOGIN_AUTH_TYPE_PW)){
                // sshの接続パラメータを作成する。
                // ユーザー名
                $win_param = $win_param . " ansible_ssh_user=" . $ina_hostinfolist[$host_name]['LOGIN_USER'];
                // パスワードが設定されているか(windowsの場合に有効)
                // パスワード
                if($ina_hostinfolist[$host_name]['LOGIN_PW'] != self::LC_ANS_UNDEFINE_NAME){
                    $win_param = $win_param . " ansible_ssh_pass=" . $ina_hostinfolist[$host_name]['LOGIN_PW'];
                }
                // 対象ホストがwindowsの場合
                if($this->lv_winrm_id == 1){
                    // WINRM接続プロトコルよりポート番号取得
                    $win_param = $win_param . " ansible_ssh_port=" 
                                              . $ina_hostinfolist[$host_name]['WINRM_PORT'];
                    $win_param = $win_param . " ansible_connection=winrm";
                }
            }

            $ssh_key_file = '';
            // 認証方式が鍵認証でWinRM接続でないか判定
            if(($ina_hostinfolist[$host_name]['LOGIN_AUTH_TYPE'] == self::LC_LOGIN_AUTH_TYPE_KEY ) &&
               ($this->lv_winrm_id != 1)){
                if(strlen(trim($ina_hostinfolist[$host_name]['SSH_KEY_FILE'])) != 0){
                    // 機器一覧にSSH鍵認証ファイルが登録されている場合はSSH鍵認証ファイルをinディレクトリ配下にコピーする。
                    $ret = $this->CreateSSH_key_file($ina_hostinfolist[$host_name]['SYSTEM_ID'],
                                                     $ina_hostinfolist[$host_name]['SSH_KEY_FILE'],
                                                     $ssh_key_file_path);

                    if($ret === false){
                        return false;
                    }
                    // hostsファイルに追加するSSH鍵認証ファイルのパラメータ生成
                    $ssh_key_file = ' ansible_ssh_private_key_file=' . $ssh_key_file_path;
                }
            }

            $win_ca_file = '';
            // WinRM接続か判定
            if($this->lv_winrm_id == 1){
                if(strlen(trim($ina_hostinfolist[$host_name]['WINRM_SSL_CA_FILE'])) != 0){
                    // 機器一覧にサーバー証明書ファイルが登録されている場合はサーバー証明書ファイルをinディレクトリ配下にコピーする
                    $ret = $this->createWIN_ca_file($ina_hostinfolist[$host_name]['SYSTEM_ID'],
                                                    $ina_hostinfolist[$host_name]['WINRM_SSL_CA_FILE'],
                                                    $win_ca_file_path);
                    if($ret === false){
                        return false;
                    }
                    // hostsファイルに追加するサーバー証明書ファイルのパラメータ生成
                    $win_ca_file = ' ansible_winrm_ca_trust_path=' . $win_ca_file_path;
                }
            }

            $ssh_host = '';
            // ホストアドレス方式がホスト名方式の場合はホスト名をhostsに登録する。
            if($this->lv_hostaddress_type == 2){
            }
            else{
                // ホストアドレス方式がIPアドレスの場合
                $ssh_host = ' ansible_ssh_host=' . $host_name;
            }

            $host_info = $ina_hostinfolist[$host_name]['HOSTNAME'] . ' ' . $ssh_host . ' ' .  $win_param . ' ' . $ssh_key_file . ' ' . $ssh_extra_args . ' ' . $hosts_extra_args . ' ' . $win_ca_file . "\n";

            if( @fputs($fd, $host_info) === false ){
                // $ary[55205] = "hostsファイル(｛｝)の書込みに失敗。";
                $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-55205",array(__LINE__));
                $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
                return false;
            }
        }
        if( @fclose($fd) === false ){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-55205",array(__LINE__));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
            return false;
        }
        return true;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F0004-1
    // 処理内容
    //   ホスト変数ファイルを作成する。(Role専用)
    // 
    // パラメータ
    //   $ina_host_vars:        ホスト変数配列
    //                          [ipaddress][ 変数名 ]=>具体値
    //   $ina_hostprotcollist:  ホスト毎プロトコル一覧
    //                          [ホスト名(IP)][ホスト名][PROTOCOL_NAME][LOGIN_USER]=LOGIN_PASSWD
    // 
    // 戻り値
    //   true:   正常
    //   false:  異常
    ////////////////////////////////////////////////////////////////////////////////
    function CreateRoleHostvarsfiles($ina_host_vars,
                                     $ina_hostprotcollist,
                                     $ina_MultiArray_vars_list){
        // ホスト変数配列よりホスト名(IP)を取得
        $host_list = array_keys($ina_host_vars);

        // ホスト変数配列のホスト)分繰返し
        foreach( $host_list as $host_name){
            foreach($ina_hostprotcollist[$host_name] as $hostname=>$prolist)
            $host_vars_file = $hostname;
            // ホストアドレス方式がホスト名方式の場合はhost_varsをホスト名する。
            $file_name = $this->getAnsible_host_var_file($host_vars_file);
            $vars_list = $ina_host_vars[$host_name];

            // ホスト変数定義ファイル作成
            $ret = $this->CreateRoleHostvarsfile("VAR",
                                                 $file_name,
                                                 $vars_list,
                                                 $ina_MultiArray_vars_list,
                                                 $host_name);
            if($ret === false){
                return false;
            }
        }
        return true;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F0005-1
    // 処理内容
    //   ホスト変数定義ファイル(1ホスト)を作成する。(Role専用)
    // パラメータ
    //   $in_var_type:      登録対象の変数タイプ
    //                      "VAR"/"CPF"
    //   $in_file_name:     ホスト変数定義ファイル名
    //   $ina_var_list:     ホスト変数配列 
    //                      [ 変数名 ]=>具体値
    //   $ina_MultiArray_vars_list: 
    //   $in_host_ipaddr:   
    //   $in_mode:          書込モード
    //                      "w":上書   デフォルト
    //                      "a":追加
    // 
    // 戻り値
    //   true:   正常
    //   false:  異常
    ////////////////////////////////////////////////////////////////////////////////
    function CreateRoleHostvarsfile($in_var_type,
                                    $in_file_name,
                                    $ina_var_list,
                                    $ina_MultiArray_vars_list,
                                    $in_host_ipaddr,
                                    $in_mode = "w"){
        $parent_vars_list = array();

        $var_str = "";
        foreach($ina_var_list as $var => $val){
            // 変数の重複出力防止
            if(@count($parent_vars_list[$var]) != 0) {
                continue;
            }

            // コピー変数の登録の場合に、VAR変数の具体値に
            // 使用されているコピー変数か確認する。
            if($in_var_type == "CPF"){
                if(@strlen($this->lv_varsval_cpf_vars_list[$var]) != 0){
                    continue;
                }
            }

            $parent_vars_list[$var] = 0;

            // 読替変数か判定。読替変数の場合は任意変数に置き換える
            if(@count($this->translationtable_list[$var]) != 0){
                $var = $this->translationtable_list[$var];
            }

            //ホスト変数ファイルのレコード生成
            //変数名: 具体値
            $var_str = $var_str . sprintf("%s: %s\n",$var,$val);

            // 変数の具体値に使用しているコピー変数の情報を確認
            if($in_var_type == "VAR"){
                $ret = $this->checkConcreteValueIsVar($val, $this->lv_varsval_cpf_vars_list);
                if($ret == false){
                    //エラーメッセージは出力しているので、ここでは何も出さない。
                    return false;
                }
            }
        }

        // copyモジュール変数のみ登録で呼ばれるケースの対応
        if($in_mode == "w"){
            $parent_vars_list = array();
            $MultiArrayVars_str = "";
            $ret = $this->MultiArrayVarsToYamlFormatMain($ina_MultiArray_vars_list,
                                                         $MultiArrayVars_str,
                                                         $parent_vars_list,
                                                         $in_host_ipaddr,
                                                         $this->lv_varsval_cpf_vars_list);
            if($ret === false){
                $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-90234");
                $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
                return false;
            }

            $var_str = $var_str . $MultiArrayVars_str;

            // グローバル変数をホスト変数ファイルに登録する。
            foreach( $this->lva_global_vars_list as $var=>$val ){
                if(@count($parent_vars_list[$var]) != 0){
                    continue;
                }
                $parent_vars_list[$var] = 0;
                //ホスト変数ファイルのレコード生成
                //変数名: 具体値
                $var_str = $var_str . sprintf("%s: %s\n",$var,$val);
            }

            // "VAR"でしかこないルート 多段変数と他変数と同時に出力する。
            // 変数の具体値に使用しているコピー変数の情報をホスト変数ファイルに出力
            foreach($this->lv_varsval_cpf_vars_list as $var=>$val){
                //ホスト変数ファイルのレコード生成
                //変数名: 具体値
                $var_str = $var_str . sprintf("%s: %s\n",$var,$val);
            }
        }

        if ( $var_str != "" ){
            $fd = @fopen($in_file_name, $in_mode);

            if($fd == null){
                $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-55206",array(__LINE__));
                $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
                return false;
            }

            if( @fputs($fd, $var_str) === false ){
                $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-55207",array(__LINE__));
                $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
                return false;
            }

            if( @fclose($fd) === false ){
                $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-55207",array(__LINE__));
                $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
                return false;
            }
        }
        return true;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F0006
    // 処理内容
    //   playbookファイルを作成する。
    // パラメータ
    //   $in_file_name:        playbookファイル名
    //   $ina_playbook_list:   ロール名の配列
    //                         [実行順序][role_id]=>ロール名
    //   $in_gather_facts_flg:   gather_factsの実施有無
    //                           1: 実施  他: 未実施
    // 
    // 戻り値
    //   true:   正常
    //   false:  異常
    ////////////////////////////////////////////////////////////////////////////////
    function CreatePlaybookfile($in_file_name, $ina_playbook_list, $in_gather_facts_flg){
        $fd = @fopen($in_file_name, "w");
        if($fd == null){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-55208",array(__LINE__));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
            return false;
        }

        $value =          "- hosts: all\n";

        // 対象ホストがwindowsか判別。windows以外の場合は become: yes を設定(旧:sudo)
        if($this->lv_winrm_id != 1){
            $value = $value . "  become: yes\n";
        }

        // gather_factsのyes/no設定
        if($in_gather_facts_flg == 1){
            $value = $value . "  gather_facts: true\n";
        }
        else{
            $value = $value . "  gather_facts: false\n";
        }

        $value = $value . "\n";
        $value = $value . "  roles:\n";
        if(@fputs($fd, $value) === false){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-55209",array(__LINE__));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
            return false;
        }
        $value = "";
        foreach($ina_playbook_list as $no => $file_list){
            foreach($file_list as $key => $file){
                $value = $value . "    - role: " . $file . "\n";
            }
        }
        if(@fputs($fd, $value) === false){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-55209", array(__LINE__)); 
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
            return false;
        }
        if(@fclose($fd) === false){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-55209", array(__LINE__));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
            return false;
        }
        return true;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // 処理内容
    //   hostsファイル名を取得
    // パラメータ
    //   なし
    // 
    // 戻り値
    //   hostsファイル名
    ////////////////////////////////////////////////////////////////////////////////
    function getAnsible_hosts_file(){
        $file = $this->lv_Ansible_in_Dir . "/" . self::LC_ANS_HOSTS_FILE;
        return($file);
    }
    
    ////////////////////////////////////////////////////////////////////////////////
    // 処理内容
    //   ホスト変数定義ファイル名を取得
    // パラメータ
    //   $in_hostname:       ホスト名(IPアドレス)
    // 
    // 戻り値
    //   ホスト変数定義ファイル名
    ////////////////////////////////////////////////////////////////////////////////
    function getAnsible_host_var_file($in_hostname){
        $file = sprintf("%s/%s",
                        $this->lv_Ansible_host_vars_Dir,
                        $in_hostname);
        return($file);
    }

    function DebugLogPrint($p1,$p2,$p3){
        global $logger;
        $logger->debug("FILE:$p1 LINE:$p2 $p3");
    }

    function LocalLogPrint($p1,$p2,$p3){
        global $logger;
        $logger->error("FILE:$p1 LINE:$p2 $p3");

        if($this->lv_Ansible_out_Dir != ""){
            $logfile = $this->lv_Ansible_out_Dir . "/" . "error.log";
            $filepointer=fopen(  $logfile, "a");
            flock($filepointer, LOCK_EX);
            fputs($filepointer, $p3 . "\n" );
            flock($filepointer, LOCK_UN);
            fclose($filepointer);
        }
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F0015
    // 処理内容
    //   legacyで実行するHOSTをデータベースより取得する。
    // 
    // パラメータ
    //   $in_pattern_id:        作業パターンID
    //   $in_operation_id:      オペレーションID
    //   $ina_hostlist:         ホスト一覧返却配列
    //                          [管理システム項番]=ホスト名(IP);
    //   $ina_hostprotcollist:  ホスト毎プロトコル一覧返却配列
    //                          [ホスト名(IP)][ホスト名][PROTOCOL_NAME][LOGIN_USER]=LOGIN_PASSWD
    //   既存のデータが重なるが、今後の開発はこの変数を使用する。
    //   $ina_hostinfolist:     機器一覧ホスト情報
    //                          [ホスト名(IP)]=HOSTNAME=>''             ホスト名
    //                                         PROTOCOL_ID=>''	        接続プロトコル
    //                                         LOGIN_USER=>''           ログインユーザー名
    //                                         LOGIN_PW_HOLD_FLAG=>''   パスワード管理フラグ
    //                                                                  1:管理(●)   N0:未管理
    //                                         LOGIN_PW=>''             パスワード
    //                                                                  パスワード管理が1の場合のみ有効
    //                                         LOGIN_AUTH_TYPE=>''      Ansible認証方式
    //                                                                  2:パスワード認証 1:鍵認証
    //                                         WINRM_PORT=>''           WinRM接続プロトコル
    //                                         OS_TYPE_ID=>''           OS種別
    //                                         SSH_EXTRA_ARGS=>         SSHコマンド 追加パラメータ
    //                                         SSH_KEY_FILE=>           SSH秘密鍵ファイル
    //                                         SYSTEM_ID=>              項番
    // 
    // 戻り値
    //   true:   正常
    //   false:  異常
    ////////////////////////////////////////////////////////////////////////////////
    function getDBHostList( $in_pattern_id,
                            $in_operation_id,
                           &$ina_hostlist,
                           &$ina_hostprotcollist,
                           &$ina_hostinfolist){
        global $log_output_dir;
        global $log_file_prefix;
        global $log_level;
        // C_STM_LISTに対するDISUSE_FLAG = '0'の
        // 条件はSELECT文に入れない。
        $sql = "SELECT \n" .
               "  TBL_1.PHO_LINK_ID, \n" .
               "  TBL_1.SYSTEM_ID, \n" .
               "  TBL_2.HOSTNAME, \n" .
               "  TBL_2.IP_ADDRESS, \n" .
               "  TBL_2.LOGIN_USER, \n" .
               "  TBL_2.LOGIN_PW, \n" .
               "  TBL_2.CONN_SSH_KEY_FILE, \n" .
               "  TBL_2.SSH_EXTRA_ARGS, \n" .
               "  TBL_2.WINRM_PORT, \n" .
               "  TBL_2.WINRM_SSL_CA_FILE , \n".
               "  TBL_2.HOSTS_EXTRA_ARGS, \n".
               "  TBL_2.LOGIN_PW_HOLD_FLAG, \n" .
               "  TBL_2.LOGIN_AUTH_TYPE, \n" .
               "  TBL_2.OS_TYPE_ID, \n" .
               "  TBL_2.DISUSE_FLAG, \n" .
               "  ( \n" .
               "    SELECT \n" .
               "      TBL_3.PROTOCOL_NAME \n" .
               "    FROM \n" .
               "      B_PROTOCOL TBL_3 \n" .
               "    WHERE \n" .
               "      TBL_3.PROTOCOL_ID = TBL_2.PROTOCOL_ID AND \n" .
               "      TBL_3.DISUSE_FLAG = '0' \n" .
               "  ) AS PROTOCOL_NAME \n" .
               "FROM \n" .
               "  ( \n" .
               "    SELECT \n" .
               "      TBL_4.PHO_LINK_ID, \n" .
               "      TBL_4.SYSTEM_ID \n" .
               "    FROM \n" .
               "      $this->lv_ansible_pho_linkDB TBL_4 \n" .
               "    WHERE \n" .
               "      TBL_4.OPERATION_NO_UAPK = :OPERATION_NO_UAPK AND \n" .
               "      TBL_4.PATTERN_ID   = :PATTERN_ID   AND \n" .
               "      TBL_4.DISUSE_FLAG  = '0' \n" .
               "  ) TBL_1 \n" .
               "LEFT OUTER JOIN C_STM_LIST TBL_2 ON ( TBL_1.SYSTEM_ID = TBL_2.SYSTEM_ID AND TBL_2.DISUSE_FLAG  = '0' ) \n" .
               "ORDER BY TBL_2.IP_ADDRESS; \n";

        $objQuery = $this->lv_objDBCA->sqlPrepare($sql);
        if($objQuery->getStatus()===false){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-56100",array(basename(__FILE__),__LINE__));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

            $this->DebugLogPrint(basename(__FILE__),__LINE__,$sql);
            $this->DebugLogPrint(basename(__FILE__),__LINE__,"OPERATION_NO_UAPK=>$in_operation_id");
            $this->DebugLogPrint(basename(__FILE__),__LINE__,"PATTERN_ID=>$in_pattern_id");
            $this->DebugLogPrint(basename(__FILE__),__LINE__,$objQuery->getLastError());

            return false;
        }
        $objQuery->sqlBind( array('OPERATION_NO_UAPK'=>$in_operation_id,
                                  'PATTERN_ID'=>$in_pattern_id));

        $r = $objQuery->sqlExecute();
        if (!$r){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-56100",array(basename(__FILE__),__LINE__));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

            $this->DebugLogPrint(basename(__FILE__),__LINE__,$sql);
            $this->DebugLogPrint(basename(__FILE__),__LINE__,"OPERATION_NO_UAPK=>$in_operation_id");
            $this->DebugLogPrint(basename(__FILE__),__LINE__,"PATTERN_ID=>$in_pattern_id");
            $this->DebugLogPrint(basename(__FILE__),__LINE__,$objQuery->getLastError());

            unset($objQuery);
            return false;
        }

        $ina_hostlist = array();
        $ina_hostprotcollist = array();
        $ina_hostinfolist = array();
        while ( $row = $objQuery->resultFetch() ){
            if($row['DISUSE_FLAG']=='0'){
                if(strlen($row['IP_ADDRESS'])==0){
                    $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-56205",
                                                               array($row['SYSTEM_ID']));
                    $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

                    unset($objQuery);
                    return false;
                }
                if(strlen($row['HOSTNAME'])==0){
                    $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-56202",
                                                               array($row['IP_ADDRESS']));
                    $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

                    unset($objQuery);
                    return false;
                }

                // 認証方式の設定値確認
                $login_auth_type = '';
                if(strlen($row['LOGIN_AUTH_TYPE']) === 0){
                    // 未設定なのでデフォルト値設定
                    $login_auth_type = self::LC_LOGIN_AUTH_TYPE_DEF;  // 鍵認証
                }
                else{
                    switch($row['LOGIN_AUTH_TYPE']){
                    case self::LC_LOGIN_AUTH_TYPE_KEY:               // 鍵認証
                    case self::LC_LOGIN_AUTH_TYPE_PW:                // パスワード認証
                        $login_auth_type = $row['LOGIN_AUTH_TYPE'];
                        break;
                    }
                }
                if($login_auth_type == ''){
                    $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70040",
                                                               array($row['IP_ADDRESS']));
                    $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

                    unset($objQuery);
                    return false;
                }

                // パスワード管理フラグの設定値確認
                $pw_hold_flag = '';
                if(@strlen($row['LOGIN_PW_HOLD_FLAG']) === 0){
                    // 未設定なのでデフォルト値設定
                    $pw_hold_flag = self::LC_LOGIN_PW_HOLD_FLAG_DEF;  // パスワード管理なし
                }
                else{
                    switch($row['LOGIN_PW_HOLD_FLAG']){
                    case self::LC_LOGIN_PW_HOLD_FLAG_ON: // パスワード管理あり
                        $pw_hold_flag = $row['LOGIN_PW_HOLD_FLAG']; 
                        break;
                    }
                }
                if($pw_hold_flag == ''){
                    $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70041",
                                                               array($row['IP_ADDRESS']));
                    $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
    
                    unset($objQuery);
                    return false;
                }
                // 認証方式がパスワード認証の場合に管理パスワードがありでパスワードが設定されているか判定
                if($login_auth_type === self::LC_LOGIN_AUTH_TYPE_PW){
                    // パスワード管理ありの判定
                    if($pw_hold_flag != self::LC_LOGIN_PW_HOLD_FLAG_ON){
                        $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70042",
                                                               array($row['IP_ADDRESS']));
                        $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
    
                        unset($objQuery);
                        return false;
                    }   
                    // パスワード登録の判定
                    if(strlen($row['LOGIN_PW'])==0){
                        $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70043",
                                                               array($row['IP_ADDRESS']));
                        $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

                        unset($objQuery);
                        return false;
                    }
                }
                // パスワード管理ありでパスワードが設定されているか判定
                // パスワード管理ありの判定
                if($pw_hold_flag == self::LC_LOGIN_PW_HOLD_FLAG_ON){
                    // パスワード登録の判定
                    if(strlen($row['LOGIN_PW'])==0){
                        $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70043",
                                                                   array($row['IP_ADDRESS']));
                        $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

                        unset($objQuery);
                        return false;
                    }
                    // パスワード退避
                    $login_pass = ky_decrypt($row['LOGIN_PW']);

                }
                else{
                    // パスワード未設定を退避
                    $login_pass = self::LC_ANS_UNDEFINE_NAME;
                }

                if($row['PROTOCOL_NAME']===null){
                    $protocol = self::LC_ANS_UNDEFINE_NAME;
                }
                else{
                    $protocol = $row['PROTOCOL_NAME'];
                }
                if(strlen($row['LOGIN_USER'])==0){
                    $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-56203",
                                                               array($row['IP_ADDRESS']));
                    $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

                    unset($objQuery);
                    return false;
                }
                $login_user = $row['LOGIN_USER'];

                // IPアドレスの配列作成
                $ina_hostlist[$row['SYSTEM_ID']]=$row['IP_ADDRESS'];
                // IPアドレス,ホスト名,プロトコル,ログインユーザー,パスワードの配列作成
                $ina_hostprotcollist[$row['IP_ADDRESS']][$row['HOSTNAME']][$protocol][$login_user] = $login_pass;

                // WINRM接続プロトコル配列作成
                if(strlen($row['WINRM_PORT']) === 0)
                {   //WINRM接続プロトコルが空白の場合はデフォルト値を設定
                    $winrm_port = self::LC_WINRM_PORT;
                }
                else{
                    $winrm_port = $row['WINRM_PORT'];
                }

                $ina_hostinfolist[$row['IP_ADDRESS']]['WINRM_SSL_CA_FILE']  = $row['WINRM_SSL_CA_FILE'];
                $ina_hostinfolist[$row['IP_ADDRESS']]['HOSTS_EXTRA_ARGS']   = $row['HOSTS_EXTRA_ARGS'];
                // SSH認証ファイル/SSH_EXTRA_ARGSと機器一覧の項番を退避
                $ina_hostinfolist[$row['IP_ADDRESS']]['SSH_EXTRA_ARGS']     = $row['SSH_EXTRA_ARGS'];
                $ina_hostinfolist[$row['IP_ADDRESS']]['SSH_KEY_FILE']       = $row['CONN_SSH_KEY_FILE'];
                $ina_hostinfolist[$row['IP_ADDRESS']]['SYSTEM_ID']          = $row['SYSTEM_ID'];
                $ina_hostinfolist[$row['IP_ADDRESS']]['HOSTNAME']           = $row['HOSTNAME'];  //ホスト名
                $ina_hostinfolist[$row['IP_ADDRESS']]['PROTOCOL_ID']        = $protocol;         //接続プロトコル
                $ina_hostinfolist[$row['IP_ADDRESS']]['LOGIN_USER']         = $login_user;       //ログインユーザー名
                $ina_hostinfolist[$row['IP_ADDRESS']]['LOGIN_PW']           = $login_pass;       //パスワード
                $ina_hostinfolist[$row['IP_ADDRESS']]['LOGIN_PW_HOLD_FLAG'] = $pw_hold_flag;     //パスワード管理フラグ
                $ina_hostinfolist[$row['IP_ADDRESS']]['LOGIN_AUTH_TYPE']    = $login_auth_type;  //Ansible認証方式
                $ina_hostinfolist[$row['IP_ADDRESS']]['WINRM_PORT']         = $winrm_port;       //WINRM接続プロトコル
                $ina_hostinfolist[$row['IP_ADDRESS']]['OS_TYPE_ID']         = $row['OS_TYPE_ID'];//OS種別

            }
            // 作業対象ホスト管理に登録されているホストが管理対象システム一覧(C_STM_LIST )に未登録
            elseif($row['DISUSE_FLAG']===null){
                $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-56105",
                                                           array($row['PHO_LINK_ID'],
                                                                 $row['SYSTEM_ID'] ));
                $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

                unset($objQuery);
                return false;
            }
            // DISUSE_FLAG = '1'は読み飛ばし
        }
        // fetch行数を取得
        $fetch_counter = $objQuery->effectedRowCount();
        if ($fetch_counter < 1){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-56106",
                                                       array($in_pattern_id));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
    
            unset($objQuery);
            return false;
        }
        if (count($ina_hostlist) < 1){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-56107",
                                                       array($in_pattern_id));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
    
            unset($objQuery);
            return false;
        }

        // DBアクセス事後処理
        unset($objQuery);
    
        return true;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F0016-1
    // 処理内容
    //   ansibleで実行する変数をデータベースより取得する。(Role専用)
    // 
    // パラメータ
    //   $in_pattern_id:        作業パターンID
    //   $in_operation_id:      オペレーションID
    //   $ina_host_vars:        変数一覧返却配列
    //                          [ホスト名(IP)][ 変数名 ]=>具体値
    // 
    // 戻り値
    //   true:   正常
    //   false:  異常
    ////////////////////////////////////////////////////////////////////////////////
    function getDBRoleVarList($in_pattern_id, $in_operation_id, &$ina_host_vars, &$ina_MultiArray_vars_list){
        $vars_assign_seq_list = array();
        $child_vars_list = array();
        $varerror_flg = true;
        // B_ANSIBLE_LNS_PATTERN_VARS_LINKに対するDISUSE_FLAG = '0'の
        // 条件はSELECT文に入れない。
        $sql = "SELECT                                                                                      \n" .
               "       TBL.*                                                                                \n" .
               "FROM                                                                                        \n" .
               "(                                                                                           \n" .
               "SELECT                                                                                      \n" .
               "  TBL_1.VARS_ASSIGN_ID,                                                                     \n" .
               "  TBL_1.SYSTEM_ID,                                                                          \n" .
               "  TBL_1.VARS_ENTRY,                                                                         \n" .
               "  TBL_1.ASSIGN_SEQ,                                                                         \n" .
               "  TBL_2.VARS_ID AS VARS_ID,                                                                 \n" .
               "  TBL_1.NESTEDMEM_COL_CMB_ID,                                                               \n" .
               "  TBL_3.COL_COMBINATION_MEMBER_ALIAS,                                                       \n" .
               "  TBL_3.COL_SEQ_VALUE,                                                                      \n" .
               "  TBL_4.NESTED_MEM_VARS_ID,                                                                    \n" .
               "  TBL_4.PARENT_KEY_ID,                                                                      \n" .
               "  TBL_4.SELF_KEY_ID,                                                                        \n" .
               "  TBL_4.MEMBER_NAME,                                                                        \n" .
               "  TBL_4.NESTED_LEVEL,                                                                       \n" .
               "  TBL_4.ASSIGN_SEQ_NEED,                                                                    \n" .
               "  TBL_4.COL_SEQ_NEED,                                                                       \n" .
               "  TBL_4.MEMBER_DISP,                                                                        \n" .
               "  TBL_4.MAX_COL_SEQ,                                                                        \n" .
               "  TBL_4.NESTED_MEMBER_PATH,                                                                 \n" .
               "  TBL_4.NESTED_MEMBER_PATH_ALIAS,                                                           \n" .
               "  TBL_2.DISUSE_FLAG   AS PTN_VARS_LINK_DISUSE_FLAG,                                         \n" .
               "  TBL_3.DISUSE_FLAG   AS MEMBER_COL_COMB_DISUSE_FLAG,                                       \n" .
               "  TBL_4.DISUSE_FLAG   AS ARRAY_MEMBER_DISUSE_FLAG,                                          \n" .
               "  (                                                                                         \n" .
               "    SELECT                                                                                  \n" .
               "      COUNT(*)                                                                              \n" .
               "    FROM                                                                                    \n" .
               "      $this->lv_ansible_pho_linkDB TBL_11                                                   \n" .
               "    WHERE                                                                                   \n" .
               "      TBL_11.OPERATION_NO_UAPK = TBL_1.OPERATION_NO_UAPK AND                                \n" .
               "      TBL_11.PATTERN_ID        = TBL_1.PATTERN_ID        AND                                \n" .
               "      TBL_11.SYSTEM_ID         = TBL_1.SYSTEM_ID         AND                                \n" .
               "      TBL_11.DISUSE_FLAG       = '0'                                                        \n" .
               "  ) AS PHO_LINK_HOST_COUNT,                                                                 \n" .
               "  (                                                                                         \n" .
               "    SELECT                                                                                  \n" .
               "      TBL_12.IP_ADDRESS                                                                     \n" .
               "    FROM                                                                                    \n" .
               "      C_STM_LIST TBL_12                                                                     \n" .
               "    WHERE                                                                                   \n" .
               "      TBL_12.SYSTEM_ID   = TBL_1.SYSTEM_ID  AND                                             \n" .
               "      TBL_12.DISUSE_FLAG = '0'                                                              \n" .
               "  ) AS IP_ADDRESS,                                                                          \n" .
               "  TBL_1.VARS_LINK_ID,                                                                       \n" .
               "  (                                                                                         \n" .
               "    SELECT                                                                                  \n" .
               "      TBL_13.VARS_NAME                                                                      \n" .
               "    FROM                                                                                    \n" .
               "      $this->lv_ansible_vars_masterDB TBL_13                                                \n" .
               "    WHERE                                                                                   \n" .
               "      TBL_13.VARS_ID     = TBL_2.VARS_ID    AND                                             \n" .
               "      TBL_13.DISUSE_FLAG = '0'                                                              \n" .
               "  ) AS VARS_NAME,                                                                           \n" .
               "  (                                                                                         \n" .
               "    SELECT                                                                                  \n" .
               "      COUNT(*)                                                                              \n" .
               "    FROM                                                                                    \n" .
               "      $this->lv_ansible_vars_assignDB TBL_14                                                \n" .
               "    WHERE                                                                                   \n" .
               "      TBL_14.OPERATION_NO_UAPK = TBL_1.OPERATION_NO_UAPK  AND                               \n" .
               "      TBL_14.PATTERN_ID        = TBL_1.PATTERN_ID         AND                               \n" .
               "      TBL_14.SYSTEM_ID         = TBL_1.SYSTEM_ID          AND                               \n" .
               "      TBL_14.VARS_LINK_ID      = TBL_1.VARS_LINK_ID       AND                               \n" .
               "      TBL_14.DISUSE_FLAG       = '0'                                                        \n" .
               "  ) AS VARS_NAME_COUNT,                                                                     \n" .
               "  (                                                                                         \n" .
               "    SELECT                                                                                  \n" .
               "      TBL_15.VARS_ATTR_ID                                                                   \n" .
               "    FROM                                                                                    \n" .
               "      $this->lv_ansible_vars_masterDB TBL_15                                                \n" .
               "    WHERE                                                                                   \n" .
               "      TBL_15.VARS_ID = TBL_2.VARS_ID AND                                                    \n" .
               "      TBL_15.DISUSE_FLAG = '0'                                                              \n" .
               "  ) AS VARS_ATTR_ID                                                                         \n" .
               "FROM                                                                                        \n" .
               "  (                                                                                         \n" .
               "    SELECT                                                                                  \n" .
               "      TBL_16.OPERATION_NO_UAPK,                                                             \n" .
               "      TBL_16.PATTERN_ID,                                                                    \n" .
               "      TBL_16.VARS_ASSIGN_ID,                                                                \n" .
               "      TBL_16.SYSTEM_ID,                                                                     \n" .
               "      TBL_16.VARS_LINK_ID,                                                                  \n" .
               "      TBL_16.NESTEDMEM_COL_CMB_ID,                                                          \n" .
               "      TBL_16.VARS_ENTRY,                                                                    \n" .
               "      TBL_16.ASSIGN_SEQ                                                                     \n" .
               "    FROM                                                                                    \n" .
               "      $this->lv_ansible_vars_assignDB TBL_16                                                \n" .
               "    WHERE                                                                                   \n" .
               "      TBL_16.OPERATION_NO_UAPK = :OPERATION_NO_UAPK AND                                     \n" .
               "      TBL_16.PATTERN_ID        = :PATTERN_ID        AND                                     \n" .
               "      TBL_16.DISUSE_FLAG       = '0'                                                        \n" .
               "  ) TBL_1                                                                                   \n" .
               " LEFT OUTER JOIN $this->lv_ansible_pattern_vars_linkDB TBL_2 ON ( TBL_1.VARS_LINK_ID =      \n" .
               "                                                                  TBL_2.VARS_LINK_ID )      \n" .
               " LEFT OUTER JOIN $this->lv_ansible_member_col_combDB TBL_3 ON ( TBL_1.NESTEDMEM_COL_CMB_ID = \n" .
               "                                                                TBL_3.NESTEDMEM_COL_CMB_ID ) \n" .
               " LEFT OUTER JOIN $this->lv_ansible_array_memberDB    TBL_4 ON ( TBL_3.NESTED_MEM_VARS_ID =  \n" .
               "                                                                TBL_4.NESTED_MEM_VARS_ID )  \n" .
               " ) TBL                                                                                      \n" .
               " ORDER BY IP_ADDRESS,VARS_NAME,NESTED_LEVEL,SELF_KEY_ID,COL_SEQ_VALUE,ASSIGN_SEQ          ";
        $objQuery = $this->lv_objDBCA->sqlPrepare($sql);
        if($objQuery->getStatus()===false){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-56100",array(basename(__FILE__),__LINE__));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

            $this->DebugLogPrint(basename(__FILE__),__LINE__,$sql);
            $this->DebugLogPrint(basename(__FILE__),__LINE__,"OPERATION_NO_UAPK=>$in_operation_id");
            $this->DebugLogPrint(basename(__FILE__),__LINE__,"PATTERN_ID=>$in_pattern_id");
            $this->DebugLogPrint(basename(__FILE__),__LINE__,$objQuery->getLastError());

            return false;
        }
        $objQuery->sqlBind( array('OPERATION_NO_UAPK'=>$in_operation_id,
                                  'PATTERN_ID'=>$in_pattern_id));
    
        $r = $objQuery->sqlExecute();
        if (!$r){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-56100",array(basename(__FILE__),__LINE__));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

            $this->DebugLogPrint(basename(__FILE__),__LINE__,$sql);
            $this->DebugLogPrint(basename(__FILE__),__LINE__,"OPERATION_NO_UAPK=>$in_operation_id");
            $this->DebugLogPrint(basename(__FILE__),__LINE__,"PATTERN_ID=>$in_pattern_id");
            $this->DebugLogPrint(basename(__FILE__),__LINE__,$objQuery->getLastError());

            unset($objQuery);
            return false;
        }
    
        $ina_host_vars = array();
        $tgt_row = array();
        $array_tgt_row = array();
        while ( $row = $objQuery->resultFetch() ){
            switch($row['VARS_ATTR_ID']){
            case self::LC_VARS_ATTR_STRUCT:       // 多次元変数
                array_push( $array_tgt_row, $row );
                break;
            default:
                array_push( $tgt_row, $row );
                break;
            }
        }
        foreach( $tgt_row as $row ){
            // 代入順序がブランクか判定
            $assign_seq = true;
            if(strlen($row['ASSIGN_SEQ']) === 0){
                $assign_seq = false;
            }

            if($row['PTN_VARS_LINK_DISUSE_FLAG']=='0'){
                // 代入値管理のみあるホスト変数(作業対象ホストにない)をはじく
                if($row['PHO_LINK_HOST_COUNT'] == 0){
                    continue;
                }

                if(strlen($row['IP_ADDRESS'])==0){
                    $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-56108",
                                                               array($row['VARS_ASSIGN_ID']));
                    $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
    
                    unset($objQuery);
                    return false;
                }
                if(strlen($row['VARS_NAME'])==0){
                    $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-56110",
                                                               array($row['VARS_ASSIGN_ID']));
                    $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

                    unset($objQuery);
                    return false;
                }

                // 下記予約変数が使用されているかチェックする。
                // 親playbookに埋め込まれるリモートログインのユーザー用変数の名前
                // 親playbookに埋め込まれるリモートログインのパスワード用変数の名前
                // 親playbookに埋め込まれるホスト名用変数の名前
                if(($row['VARS_NAME']==self::LC_ANS_USERNAME_VAR_NAME) ||
                   ($row['VARS_NAME']==self::LC_ANS_OUTDIR_VAR_NAME)   ||
                   ($row['VARS_NAME']==self::LC_SYMPHONY_DIR_VAR_NAME) ||
                   ($row['VARS_NAME']==self::LC_ANS_LOGINHOST_VAR_NAME) ||
                   ($row['VARS_NAME']==self::LC_ANS_PASSWD_VAR_NAME)){
                    $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-56201",
                                                                array($row['IP_ADDRESS'],
                                                                $row['VARS_NAME']));
                    $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
                    unset($objQuery);
                    return false;
                }
                // 代入値管理のみあるホスト変数(作業対象ホストにない)をはじく
                if($row['PHO_LINK_HOST_COUNT'] > 0){
                    // 多次元変数以外か判定
                    if($row['VARS_ATTR_ID'] == self::LC_VARS_ATTR_STRUCT){
                        if($row['MEMBER_COL_COMB_DISUSE_FLAG'] !='0'){
                            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-90228",
                                                                        array($row['VARS_ASSIGN_ID']));
                            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
                            unset($objQuery);
                            return false;
                        }
                        if($row['ARRAY_MEMBER_DISUSE_FLAG'] !='0'){
                            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-90229",
                                                                        array($row['VARS_ASSIGN_ID']));
                            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
                            unset($objQuery);
                            return false;
                        }
                    }
                    
                    if($row['VARS_ATTR_ID'] == self::LC_VARS_ATTR_LIST){
                        // 配列変数以外で代入順序がnullの場合はエラーにする。
                        if($assign_seq === false){
                            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-90100",
                                                                        array($row['VARS_ASSIGN_ID'],
                                                                              $row['VARS_NAME']));
                            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
                            unset($objQuery);
                            return false;
                        }
                    }
                    if($row['VARS_ATTR_ID'] == self::LC_VARS_ATTR_STD)
                    {
                        // 代入順序がnull以外の場合はエラーにする。
                        if($assign_seq === true){
                            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-90213",
                                                                       array($row['VARS_ASSIGN_ID'],
                                                                             $row['VARS_NAME']));
                            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
                            unset($objQuery);
                            return false;
                        }
                    }

                    // 多次元変数以外か判定
                    if($row['VARS_ATTR_ID'] != self::LC_VARS_ATTR_STRUCT){
                        // 配列変数以外で代入順序が重複していないか判定する。
                        if(@count($vars_assign_seq_list[$row['IP_ADDRESS']]
                                                       [$row['VARS_NAME']]
                                                       [$row['ASSIGN_SEQ']]) != 0){
                            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-90101",
                                                                array($row['VARS_ASSIGN_ID'],
                                                                      $vars_assign_seq_list[$row['IP_ADDRESS']]
                                                                                           [$row['VARS_NAME']]
                                                                                           [$row['ASSIGN_SEQ']],
                                                                      $row['VARS_NAME'],
                                                                      $row['ASSIGN_SEQ']));
                            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
                            unset($objQuery);
                            return false;
                        }
                        // 配列変数以外で代入順序の重複チェックリスト作成
                        $vars_assign_seq_list[$row['IP_ADDRESS']]
                                             [$row['VARS_NAME']]
                                             [$row['ASSIGN_SEQ']] = $row['VARS_ASSIGN_ID'];

                        if($row['VARS_ATTR_ID'] == self::LC_VARS_ATTR_STD){
                            //ホスト変数配列作成
                            $ina_host_vars[$row['IP_ADDRESS']][$row['VARS_NAME']]=$row['VARS_ENTRY'];
                        }
                        else{
                            if(@count($ina_host_vars[$row['IP_ADDRESS']][$row['VARS_NAME']])==0){
                                $ina_host_vars[$row['IP_ADDRESS']][$row['VARS_NAME']] = "\n- " . $row['VARS_ENTRY'];
                            }
                            else{
                                $ina_host_vars[$row['IP_ADDRESS']][$row['VARS_NAME']] = 
                                $ina_host_vars[$row['IP_ADDRESS']][$row['VARS_NAME']] .  "\n- " . $row['VARS_ENTRY'];
                            }
                        }
                    }
                    // 多次元変数の場合は具体値をここでは退避しない。
                }
            }
            elseif(strlen($row['PTN_VARS_LINK_DISUSE_FLAG'])==0){
                $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-56109",
                                                           array($row['VARS_ASSIGN_ID']));
                $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
    
                unset($objQuery);
                return false;
            }
            // DISUSE_FLAG = '1'は読み飛ばし
        }


        // 変数未登録の場合もあるので fetch行数などはチェックしない。

        // DBアクセス事後処理
        unset($objQuery);

        if($varerror_flg === true){
            $varerror_flg = $this->getDBVarMultiArrayVarsList($array_tgt_row, $ina_MultiArray_vars_list);
        } 
        return $varerror_flg;
    }

    function MultiArrayVarsToYamlFormatMain( $ina_MultiArray_vars_list,
                                            &$in_str_hostvars,
                                            &$ina_parent_vars_list,
                                             $in_host_ipaddr,
                                            &$ina_varsval_cpf_vars_list){
        $ina_parent_vars_list = array();

        $in_str_hostvars = "";

        foreach( $ina_MultiArray_vars_list as $parent_vars_name=>$parent_vars_list ){
            // 該当ホストの具体値が未登録か判定
            if(@count($parent_vars_list[$in_host_ipaddr]) == 0){
                 continue;
            }
            $host_vars_array = $parent_vars_list[$in_host_ipaddr];

            $ina_parent_vars_list[$parent_vars_name] = 1;

            // 読替変数か判定。読替変数の場合は任意変数に置き換える
            if(@count($this->translationtable_list[$parent_vars_name]) != 0){
                $var = $this->translationtable_list[$parent_vars_name];
                $cur_str_hostvars = $var . ":" . "\n";
            }
            else{
                $cur_str_hostvars = $parent_vars_name . ":" . "\n";
            }

            $error_code   = "";
            $line         = "";
            $before_vars  = "";
            $indent       = "";
            $nest_level   = 1;
            // 多次元配列の具体値構造体から。ホスト変数定義を生成する。
            $ret = $this->MultiArrayVarsToYamlFormatSub($host_vars_array,
                                                        $cur_str_hostvars,
                                                        $before_vars,
                                                        $indent,
                                                        $nest_level,
                                                        $error_code,
                                                        $line,
                                                        $ina_varsval_cpf_vars_list);
            if($ret === false){
                // エラーリスト
                $msgstr = $this->lv_objMTS->getSomeMessage($error_code,array($parent_vars_name));
                $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
                return false;
            }

            $in_str_hostvars = $in_str_hostvars . $cur_str_hostvars;

        }
        return true;
    }
    ////////////////////////////////////////////////////////////////////////////////
    // F0016-3
    // 処理内容
    //   ansibleで実行する多次元変数をデータベースより取得する。
    // 
    // パラメータ
    // 
    // 戻り値
    //   true:   正常
    //   false:  異常
    ////////////////////////////////////////////////////////////////////////////////
    function getDBVarMultiArrayVarsList($in_tgt_row, &$ina_MultiArray_vars_list){
        $vars_seq_list = array();
        $parent_vars_list = array();
        foreach( $in_tgt_row as $row ){
            if($row['PTN_VARS_LINK_DISUSE_FLAG']=='0'){
                // 代入値管理のみあるホスト変数(作業対象ホストにない)をはじく
                if($row['PHO_LINK_HOST_COUNT'] == 0){
                    continue;
                }

                if(strlen($row['IP_ADDRESS']) == 0){
                    $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-56108",
                                                               array($row['VARS_ASSIGN_ID']));
                    $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
    
                    unset($objQuery);
                    return false;
                }
                if(strlen($row['VARS_NAME']) == 0){
                    $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-56110",
                                                               array($row['VARS_ASSIGN_ID']));
                    $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
    
                    unset($objQuery);
                    return false;
                }

                if(($row['VARS_NAME']==self::LC_ANS_USERNAME_VAR_NAME) ||
                   ($row['VARS_NAME']==self::LC_ANS_OUTDIR_VAR_NAME)   ||
                   ($row['VARS_NAME']==self::LC_SYMPHONY_DIR_VAR_NAME) ||
                   ($row['VARS_NAME']==self::LC_ANS_LOGINHOST_VAR_NAME) ||
                   ($row['VARS_NAME']==self::LC_ANS_PASSWD_VAR_NAME)){
                    $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-56201",
                                                                array($row['IP_ADDRESS'],
                                                                $row['VARS_NAME']));
                    $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
                    unset($objQuery);
                    return false;
                }

                if(($row['ASSIGN_SEQ_NEED'] == 0) && (@strlen($row['ASSIGN_SEQ']) != 0)){
                    $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-90213",
                                                                array($row['VARS_ASSIGN_ID'],
                                                                      $row['COL_COMBINATION_MEMBER_ALIAS']));
                    $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
                    unset($objQuery);
                    return false;
                }

                if(($row['ASSIGN_SEQ_NEED'] == 1) && (@strlen($row['ASSIGN_SEQ']) == 0)){
                    $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-90100",
                                                                array($row['VARS_ASSIGN_ID'],
                                                                      $row['COL_COMBINATION_MEMBER_ALIAS']));
                    $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
                    unset($objQuery);
                    return false;
                }
                // 各変数の代入順序と列順序が重複していないか判定する。
                $dup_key = 0;
                if(@count($vars_seq_list[$row['IP_ADDRESS']]
                                         [$row['VARS_NAME']]
                                         [$row['NESTEDMEM_COL_CMB_ID']]
                                         [$row['ASSIGN_SEQ']]) != 0){
                    $dup_key = $vars_seq_list[$row['IP_ADDRESS']]
                                             [$row['VARS_NAME']]
                                             [$row['NESTEDMEM_COL_CMB_ID']]
                                             [$row['ASSIGN_SEQ']];
                }
                if($dup_key != 0){
                    $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-90216",
                                                               array($row['VARS_ASSIGN_ID'],
                                                                     $dup_key,
                                                                     $row['VARS_NAME']));

                    $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
                    unset($objQuery);
                    return false;
                }
                // 各変数の代入順序と列順序が重複リスト生成
                $dup_key = $vars_seq_list[$row['IP_ADDRESS']]
                                         [$row['VARS_NAME']]
                                         [$row['NESTEDMEM_COL_CMB_ID']]
                                         [$row['ASSIGN_SEQ']] = $row['VARS_ASSIGN_ID'];

                if(@count($ina_MultiArray_vars_list[$row['VARS_NAME']][$row['IP_ADDRESS']]) == 0){
                    $ina_MultiArray_vars_list[$row['VARS_NAME']][$row['IP_ADDRESS']] = array();
                }
                if(strlen($row['ASSIGN_SEQ']) == 0){
                    $var_type = 1;
                }
                else{
                    $var_type = 2;
                }
                // 多次元配列のメンバー変数へのパス配列を生成
                $var_path_array = array();
                $this->makeHostVarsPath($row['COL_COMBINATION_MEMBER_ALIAS'],$var_path_array);
                // 多次元配列の具体値情報をホスト変数ファイルに戻す為の配列作成
                $this->makeHostVarsArray($var_path_array,0,
                                         $ina_MultiArray_vars_list[$row['VARS_NAME']][$row['IP_ADDRESS']],
                                         $var_type,$row['VARS_ENTRY'],$row['ASSIGN_SEQ']);


            }
            elseif(strlen($row['PTN_VARS_LINK_DISUSE_FLAG'])==0){
                $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-56109",
                                                           array($row['VARS_ASSIGN_ID']));
                $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
    
                unset($objQuery);
                return false;
            }
            else{
                // DISUSE_FLAG = '1'は読み飛ばし
                continue;
            }
            if(@count($parent_vars_list[$row['VARS_ID']]) == 0){
                $parent_vars_list[$row['VARS_ID']] = 1;
            }
        }        
        return true;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F0019
    // 処理内容
    //   システム予約変数を設定する
    // 
    // パラメータ
    //   $ina_host_vars:        変数一覧
    //                          [ホスト名(IP)][ 変数名 ]=>具体値
    //   $ina_hostprotocollist:  ホスト毎プロトコル一覧返却配列
    //                          [ホスト名(IP)][ホスト名][PROTOCOL_NAME][LOGIN_USER]=LOGIN_PASSWD
    // 
    // 戻り値
    //   true:   正常
    //   false:  異常
    ////////////////////////////////////////////////////////////////////////////////
    function addSystemvars(&$ina_host_vars, $ina_hostprotocollist){
        foreach($ina_hostprotocollist as $host_ip => $hostlist){
            // 以降、foreachで第1要素のみ抽出する
            foreach($hostlist     as $host_name => $protocollist)
            foreach($protocollist as $protocol  => $userlist)
            foreach($userlist     as $user_name => $user_pass)

            //システム予約変数を設定
            // 親playbookに埋め込まれるリモートログインのユーザー用変数の名前
            $ina_host_vars[$host_ip][self::LC_ANS_USERNAME_VAR_NAME]     = $user_name;

            //リモートログインのパスワードが未登録か判定
            if($user_pass != self::LC_ANS_UNDEFINE_NAME){
                // 親playbookに埋め込まれるリモートログインのパスワード用変数の名前
                $ina_host_vars[$host_ip][self::LC_ANS_PASSWD_VAR_NAME]   = $user_pass;
            }

            // 親playbookに埋め込まれるホスト名用変数の名前
            $ina_host_vars[$host_ip][self::LC_ANS_LOGINHOST_VAR_NAME]    = $host_name;

            // ユーザー公開用データリレイストレージパス 変数の名前
            $ina_host_vars[$host_ip][self::LC_ANS_OUTDIR_VAR_NAME]       = $this->lv_user_out_Dir;

            // ユーザー公開用 symphonyインスタンス作業用データリレイストレージパス 変数の名前
            $ina_host_vars[$host_ip][self::LC_SYMPHONY_DIR_VAR_NAME]     = $this->lv_symphony_instance_Dir;

        }
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F0028
    // 処理内容
    //   作業パターン詳細の情報を取得
    // 
    // パラメータ
    //   $in_pattern_id:        作業パターンID
    //   $ina_pattern_list:     作業パターン一覧返却配列
    //                          [ロールパッケージID][ロールID]=>実行順
    //   $in_single_pkg:        ロールパッケージの複数指定有無
    //                          true: 単一　false:複数
    // 戻り値
    //   true:   正常
    //   false:  異常
    ////////////////////////////////////////////////////////////////////////////////
    function getDBPatternList($in_pattern_id, &$ina_pattern_list, &$in_single_pkg){
        $sql = "SELECT                             \n" .
               "  ROLE_PACKAGE_ID,                 \n" .
               "  ROLE_ID,                         \n" .
               "  INCLUDE_SEQ                      \n" .
               "FROM                               \n" .
               "  $this->lv_ansible_pattern_linkDB \n" .
               "WHERE                              \n" .
               "  PATTERN_ID  = :PATTERN_ID AND    \n" .
               "  DISUSE_FLAG = 0;                   ";
    
        $objQuery = $this->lv_objDBCA->sqlPrepare($sql);
        if($objQuery->getStatus()===false){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-56100",array(basename(__FILE__),__LINE__));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

            $this->DebugLogPrint(basename(__FILE__),__LINE__,$sql);
            $this->DebugLogPrint(basename(__FILE__),__LINE__,"PATTERN_ID=>$in_pattern_id");
            $this->DebugLogPrint(basename(__FILE__),__LINE__,$objQuery->getLastError());

            return false;
        }
        $objQuery->sqlBind( array('PATTERN_ID'=>$in_pattern_id));

        $r = $objQuery->sqlExecute();
        if (!$r){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-56100",array(basename(__FILE__),__LINE__));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

            $this->DebugLogPrint(basename(__FILE__),__LINE__,$sql);
            $this->DebugLogPrint(basename(__FILE__),__LINE__,"PATTERN_ID=>$in_pattern_id");
            $this->DebugLogPrint(basename(__FILE__),__LINE__,$objQuery->getLastError());

            unset($objQuery);
            return false;
        }

        // 作業パターンID登録確認
        $fetch_counter = $objQuery->effectedRowCount();
        if ($fetch_counter < 1){
            //$ary[56102] = "作業パターン詳細に作業パターンID(｛｝)が未登録。";
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-56102",array($in_pattern_id));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

            unset($objQuery);
            return false;
        }

        $pkgid = 0;
        $idx   = 0;
        $in_single_pkg = true;

        $ina_pattern_list = array();
        
        while ( $row = $objQuery->resultFetch() ){
            // 複数のロールパッケージが使用されているか判定する。
            if($idx === 0){
                $pkgid = $row['ROLE_PACKAGE_ID'];
            }
            else{
                if($pkgid <> $row['ROLE_PACKAGE_ID']){
                    $in_single_pkg = false;
                }
            }
            $idx = $idx + 1;

            //作業パターン一覧配列作成
            $ina_pattern_list[$row['ROLE_PACKAGE_ID']][$row['ROLE_ID']]=$row['INCLUDE_SEQ'];
        }

        // DBアクセス事後処理
        unset($objQuery);

        return true;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F0029
    // 処理内容
    //   ロールパッケージ管理の情報を取得
    // 
    // パラメータ
    //   $in_pattern_id:          ロールパッケージID
    //   $ina_role_package_list:  ロールパッケージリスト
    //                            [ロールパッケージID][ロールパッケージ名]=>ロールパッケージファイル
    // 戻り値
    //   true:   正常
    //   false:  異常
    ////////////////////////////////////////////////////////////////////////////////
    function getDBRolePackage($in_role_package_id, &$ina_role_package_list){
        $sql = "SELECT                                \n" .
               "  ROLE_PACKAGE_ID,                    \n" .
               "  ROLE_PACKAGE_NAME,                  \n" .
               "  ROLE_PACKAGE_FILE                   \n" .
               "FROM                                  \n" .
               "  $this->lv_ansible_role_packageDB    \n" .
               "WHERE                                 \n" .
               "  ROLE_PACKAGE_ID = :ROLE_PACKAGE_ID AND  \n" .
               "  DISUSE_FLAG = 0;                      ";
    
        $objQuery = $this->lv_objDBCA->sqlPrepare($sql);
        if($objQuery->getStatus()===false){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-56100",array(basename(__FILE__),__LINE__));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

            $this->DebugLogPrint(basename(__FILE__),__LINE__,$sql);
            $this->DebugLogPrint(basename(__FILE__),__LINE__,"ROLE_PACKAGE_ID=>$in_role_package_id");
            $this->DebugLogPrint(basename(__FILE__),__LINE__,$objQuery->getLastError());

            return false;
        }
        $objQuery->sqlBind( array('ROLE_PACKAGE_ID'=>$in_role_package_id));
    
        $r = $objQuery->sqlExecute();
        if (!$r){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-56100",array(basename(__FILE__),__LINE__));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

            $this->DebugLogPrint(basename(__FILE__),__LINE__,$sql);
            $this->DebugLogPrint(basename(__FILE__),__LINE__,"ROLE_PACKAGE_ID=>$in_role_package_id");
            $this->DebugLogPrint(basename(__FILE__),__LINE__,$objQuery->getLastError());

            unset($objQuery);
            return false;
        }

        $ina_role_package_list = array();

        // ロールパッケージID登録確認
        $fetch_counter = $objQuery->effectedRowCount();
        if ($fetch_counter < 1){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70011",array($in_role_package_id));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
    
            unset($objQuery);
            return false;
        }
    
        $row = $objQuery->resultFetch();

        if(strlen($row['ROLE_PACKAGE_FILE']) == 0){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70027",array($in_role_package_id));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
    
            unset($objQuery);
            return false;
        }
        //作業パターン一覧配列作成
        $ina_role_package_list[$row['ROLE_PACKAGE_ID']][$row['ROLE_PACKAGE_NAME']]=$row['ROLE_PACKAGE_FILE'];

        // DBアクセス事後処理
        unset($objQuery);

        return true;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F0031
    // 処理内容
    //   データベースからロール名を取得
    // 
    // パラメータ
    //   $in_pattern_id:        作業パターンID
    //   $ina_rolenamelist:     ロール名返却配列
    //                          [実行順序][ロールID(Pkey)]=>ロール名
    // 戻り値
    //   true:   正常
    //   false:  異常
    ////////////////////////////////////////////////////////////////////////////////
    function getDBRoleList($in_pattern_id,&$ina_rolenamelist){
        global $log_output_dir;
        global $log_file_prefix;
        global $log_level;

        // DISUSE_FLAG = '0'の条件はSELECT文に入れない。
        $sql = "SELECT                                           \n" .
               "TBL_1.PTN_ROLE_LINK_ID,                          \n" .
               "TBL_1.ROLE_ID,                                   \n" .
               "TBL_1.INCLUDE_SEQ,                               \n" .
               "TBL_2.ROLE_NAME,                                 \n" .
               "TBL_2.DISUSE_FLAG                                \n" .
               "FROM                                             \n" .
               "  (                                              \n" .
               "    SELECT                                       \n" .
               "      TBL3.PTN_ROLE_LINK_ID,                     \n" .
               "      TBL3.PATTERN_ID,                           \n" .
               "      TBL3.ROLE_ID,                              \n" .
               "      TBL3.INCLUDE_SEQ                           \n" .
               "    FROM                                         \n" .
               "      $this->lv_ansible_pattern_linkDB  TBL3     \n" .
               "    WHERE                                        \n" .
               "      TBL3.PATTERN_ID  = :PATTERN_ID AND         \n" .
               "      TBL3.DISUSE_FLAG = '0'                     \n" .
               "  )TBL_1                                         \n" .
               "LEFT OUTER JOIN  $this->lv_ansible_roleDB  TBL_2 ON \n" .
               "      ( TBL_1.ROLE_ID = TBL_2.ROLE_ID) \n" .
               "ORDER BY TBL_1.INCLUDE_SEQ; \n";

        $objQuery = $this->lv_objDBCA->sqlPrepare($sql);
        if($objQuery->getStatus()===false){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-56100",array(basename(__FILE__),__LINE__));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

            $this->DebugLogPrint(basename(__FILE__),__LINE__,$sql);
            $this->DebugLogPrint(basename(__FILE__),__LINE__,"PATTERN_ID=>$in_pattern_id");
            $this->DebugLogPrint(basename(__FILE__),__LINE__,$objQuery->getLastError());

            return false;
        }
        $objQuery->sqlBind( array('PATTERN_ID'=>$in_pattern_id));

        $r = $objQuery->sqlExecute();
        if (!$r){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-56100",array(basename(__FILE__),__LINE__));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

            $this->DebugLogPrint(basename(__FILE__),__LINE__,$sql);
            $this->DebugLogPrint(basename(__FILE__),__LINE__,"PATTERN_ID=>$in_pattern_id");
            $this->DebugLogPrint(basename(__FILE__),__LINE__,$objQuery->getLastError());

            unset($objQuery);
            return false;
        }

        while ( $row = $objQuery->resultFetch() ){
            if($row['DISUSE_FLAG']=='0'){
                // $ina_rolenamelist[実行順序][ロールID(Pkey)]=>ロール名
                $ina_rolenamelist[$row['INCLUDE_SEQ']][$row['ROLE_ID']]=$row['ROLE_NAME'];
            }
            // ロール管理にロールが未登録の場合
            elseif($row['DISUSE_FLAG']===null){
                $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70012",
                                                           array($row['PTN_ROLE_LINK_ID']));
                $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
            
                unset($objQuery);
                return false;
            }
            // DISUSE_FLAG = '1'は読み飛ばし
        }
        // fetch行数を取得
        $fetch_counter = $objQuery->effectedRowCount();
        if ($fetch_counter < 1){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-56102",array($in_pattern_id));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

            unset($objQuery);
            return false;
        }
        //対象ロールの数を確認
        if (count($ina_rolenamelist) < 1){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70013",array($in_pattern_id));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

            unset($objQuery);
            return false;
        }

        // DBアクセス事後処理
        unset($objQuery);

        return true;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F0032
    // 処理内容
    //   Legacy-Role用 PlayBookファイルを作成する。
    // 
    // パラメータ
    //   $ina_rolenames:    ロール名リスト配列
    //                      [実行順序][ロールID(Pkey)]=>ロール名
    //   $in_gather_facts_flg:   gather_factsの実施有無
    //                           1: 実施  他: 未実施
    // 
    // 戻り値
    //   true:   正常
    //   false:  異常
    ////////////////////////////////////////////////////////////////////////////////
    function CreateLegacyRolePlaybookfiles($ina_rolenames,$in_gather_facts_flg){
        /////////////////////////////////////////
        // 親PlayBookファイル作成(Legacy-Role) //
        /////////////////////////////////////////
        $file_name = $this->getAnsible_RolePlaybook_file();

        $ret = $this->CreatePlaybookfile($file_name, $ina_rolenames, $in_gather_facts_flg);
        if($ret === false){
            return false;
        }
        return true;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F0033
    // 処理内容
    //   Legacy Role用 Playbookのフォーマットをチェックする
    //   Playbookで使用している変数がホスト変数に登録されているかチェックする。
    // パラメータ
    //   $ina_hosts:            ホスト名(IP)配列
    //                          [管理システム項番]=ホスト名(IP)
    // 
    //   $ina_host_vars:        ホスト変数配列
    //                          [ホスト名(IP)][ 変数名 ]=>具体値
    // 
    //   $ina_rolenames:        ロール名リスト配列(データベース側)
    //                          [実行順序][ロールID(Pkey)]=>ロール名
    // 
    //   $ina_role_rolenames:   ロール名リスト配列(Role内登録内容)
    //                          [ロール名]
    // 
    //   $ina_role_rolevars:    ロール内変数リスト配列(Role内登録内容)
    //                          [ロール名][変数名]=0
    // 
    // 戻り値
    //   true:   正常
    //   false:  異常
    ////////////////////////////////////////////////////////////////////////////////
    function CheckLegacyRolePlaybookfiles($ina_hosts,
                                          $ina_host_vars,
                                          $ina_rolenames,
                                          $ina_role_rolenames,
                                          $ina_role_rolevars) {

        // #1220 /////////////////////////////////////////////////////////////////
        // グローバル変数以外の変数の具体値が未登録でもエラーにしていないので
        // グローバル変数についてもグローバル変数管理の登録の有無をチェックしない
        //////////////////////////////////////////////////////////////////////////

        $result_code = true;

        // ロール分の繰返し(データベース側)
        foreach( $ina_rolenames as $no => $rolename_list ){
            // ロール名取得(データベース側)
            foreach( $rolename_list as $rolepkey => $rolename ){
                // データベース側のロールがロール内に存在しているか判定
                if(in_array($rolename, $ina_role_rolenames) === false){
                    //存在していない
                    $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70024",array($rolename)); 
                    $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
                    $result_code = false;
                    continue;
                }
                // ロール内に変数が登録されているか
                if(@count($ina_role_rolevars[$rolename]) === 0){
                    // ロール内に変数が使用されていない場合は以降のチェックをスキップ
                    continue;
                }

                // ロールに登録されている変数のデータベース登録確認 
                foreach( $ina_role_rolevars[$rolename] as $var_name=>$dummy){
                    // ホスト配列のホスト分繰り返し
                    foreach( $ina_hosts as $no=>$host_name ){
                        // 変数配列分繰り返し
                        // $ina_host_vars[ ipaddress ][ 変数名 ]=>具体値
                        
                        if((@strlen($ina_host_vars[$host_name][$var_name])==0) &&
                           (array_key_exists($var_name,$ina_host_vars[$host_name])==false)){
                            if($var_name == self::LC_ANS_USERNAME_VAR_NAME){
                                $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70016",
                                                                        array($rolename, $var_name, $host_name));
                                $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
                            }
                            elseif($var_name == self::LC_ANS_PASSWD_VAR_NAME){
                                $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70017",
                                                                        array($rolename, $var_name, $host_name));
                                $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
                            }
                            elseif($var_name == self::LC_ANS_LOGINHOST_VAR_NAME){
                                $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70018",
                                                                        array($rolename, $var_name, $host_name));
                                $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
                            }
                            else{
                                continue;
                            }

                            // エラーリターンする
                            $result_code = false;
                        }
                        else{
                            //予約変数を使用している場合に対象システム一覧に該当データが登録されているか判定
                            if($ina_host_vars[$host_name][$var_name] == self::LC_ANS_UNDEFINE_NAME){
                                // ユーザー名未登録
                                if($var_name == self::LC_ANS_USERNAME_VAR_NAME){
                                    $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70021",
                                                                        array($rolename, $var_name, $host_name));
                                    $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
                                    $result_code = false;
                                }
                                // ログインパスワード未登録
                                elseif($var_name == self::LC_ANS_PASSWD_VAR_NAME){
                                    $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70022",
                                                                        array($rolename, $var_name, $host_name));
                                    $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
                                    $result_code = false;
                                }
                                // ホスト名未登録
                                elseif($var_name == self::LC_ANS_LOGINHOST_VAR_NAME){
                                    $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70023",
                                                                        array($rolename, $var_name, $host_name));
                                    $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
                                    $result_code = false;
                                }
                            }
                        }
                    }
                }
            }
        }
        return($result_code);
    }

    ////////////////////////////////////////////////////////////////////////////////
    // 処理内容
    //   Ansible Role playbookファイル名を取得
    // パラメータ
    //   なし
    // 
    // 戻り値
    //   playbookファイル名
    ////////////////////////////////////////////////////////////////////////////////
    function getAnsible_RolePlaybook_file(){
        $file = $this->lv_Ansible_in_Dir . "/" . self::LC_ANS_ROLE_PLAYBOOK_FILE;
        return($file);
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F0030
    // 処理内容
    //   作業パターンIDに紐づくパッケージファイルを取得
    // 
    // パラメータ
    //   $in_pattern_id:        作業パターンID
    //   $in_role_package_pkey  ロールパッケージファイル Pkey返却
    //   $in_role_package_name  ロールパッケージファイル名返却
    //   $in_role_package_file  ロールパッケージファイル(ZIP)返却
    //
    // 戻り値
    //   true:   正常
    //   false:  異常
    ////////////////////////////////////////////////////////////////////////////////
    function getRolePackageFile($in_pattern_id,
                                &$in_role_package_pkey,
                                &$in_role_package_name,
                                &$in_role_package_file){
        $in_role_package_file = "";
        $rolepackagelist  = array();
        $patternlist      = array();

        // 該当作業パターンIDに紐づく作業パターン詳細を取得する。
        $single_pkg       = true;

        /////////////////////////////////////////////////////////////////////////////
        // データベースから該当作業パターンIDに紐づく作業パターン詳細を取得
        //   $patternlist:   作業パターンリスト返却配列
        //                   [ロールパッケージID][ロールID]=>実行順
        /////////////////////////////////////////////////////////////////////////////
        $ret = $this->getDBPatternList($in_pattern_id, $patternlist, $single_pkg);
        if($ret <> true){
            // 例外処理へ
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-50003",array(__FILE__,__LINE__,"00010001"));
            // DebugLogPrint
            $this->DebugLogPrint(basename(__FILE__),__LINE__,$msgstr);
            return false;
        }
        // 作業パターン詳細に複数のロールパッケージが紐づいていないか判定する。
        if($single_pkg === false){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70010");
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
            return false;
        }
        // 作業パターンIDの紐づけがない場合のチェックはgetDBPatternListで実施済み

        // 作業パターンIDに紐づくロールパッケージIDを取出す
        foreach( $patternlist as $role_package_id=>$role_package_list )

        // ロールパッケージIDに紐づいているロールパッケージファイル(ZIP)を取得する。
        // $rolepackagelist[ロールパッケージID][ロールパッケージ名]=>ロールパッケージファイル
        $ret = $this->getDBRolePackage($role_package_id,$rolepackagelist);
        if($ret <> true){
            // 例外処理へ
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-50003",array(__FILE__,__LINE__,"00010001"));
            // DebugLogPrint
            $this->DebugLogPrint(basename(__FILE__),__LINE__,$msgstr);
            return false;
        }
        // ロールパッケージIDに紐づけがない場合のチェックはgetDBRolePackageで実施済み
        // ロールパッケージIDに紐づくロールパッケージPkeyを取出す
        foreach( $rolepackagelist as $in_role_package_pkey=>$role_package_list )
        // ロールパッケージIDに紐づくロールパッケージファイルを取出す
        foreach( $role_package_list as $in_role_package_name=>$in_role_package_file)

        return true;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // 処理内容
    //   inディレクトリ配下のcopyファイルパスを取得
    // パラメータ
    //   $in_pkey:    copyファイル Pkey 現在未使用
    //   $in_file:    copyファイル
    // 
    // 戻り値
    //   ホスト変数ファイル内のcopyファイルパス
    ////////////////////////////////////////////////////////////////////////////////
    function getHostvarsfile_copy_file_value($in_pkey,$in_file){
        $file = sprintf("%s/%s-%s",
                        $this->lv_Hostvarsfile_copy_file_Dir,
                        addPadding($in_pkey),
                        $in_file);
        return($file);
    }

    ////////////////////////////////////////////////////////////////////////////////
    // 処理内容
    //   Ansible実行時のcopyファイル名を取得
    //   
    // パラメータ
    //   $in_filename:       copyファイル名 現在未使用
    //   $in_pkey:           copyファイル Pkey
    // 
    // 戻り値
    //   Ansible実行時のcopyファイル名
    ////////////////////////////////////////////////////////////////////////////////
    function getAnsible_copy_file($in_pkey,$in_filename){
        $file = sprintf("%s/%s-%s",
                        $this->lv_Ansible_copy_files_Dir,
                        addPadding($in_pkey),
                        $in_filename);
        return($file);
    }

    ////////////////////////////////////////////////////////////////////////////////
    // 処理内容
    //   ITAが管理しているcopyファイルのパスを取得
    // パラメータ
    //   $in_key:        copyファイルのPkey(データベース)
    //   $in_filename:   copyファイル名    
    // 
    // 戻り値
    //   ホスト変数定義ファイル名名
    ////////////////////////////////////////////////////////////////////////////////
    function getITA_copy_file($in_key, $in_filename){
        $file = sprintf("%s/%s/%s",
                        $this->lv_ita_copy_files_Dir,
                        addPadding($in_key),
                        $in_filename);
        return($file);
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F0037
    // 処理内容
    //   copyファイルを所定のディレクトリにコピーする。
    // パラメータ
    //   $ina_copy_files:   copyファイル配列
    //                      [Pkey]=>copyファイル
    //                      の配列
    // 
    // 戻り値
    //   true:   正常
    //   false:  異常
    ////////////////////////////////////////////////////////////////////////////////
    function CreateCopyfiles($ina_copy_files){
        foreach( $ina_copy_files as $pkey=>$copy_file ){
            //copyファイルが存在しているか確認
            $src_file = $this->getITA_copy_file($pkey,$copy_file);

            if( file_exists($src_file) === false ){
                $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-90092",array($pkey,basename($src_file))); 
                $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
                return false;
            }
            // Ansible実行時のcopyファイル名
            $dst_file = $this->getAnsible_copy_file($pkey,$copy_file);

            //子Playbookをansible用ディレクトリにコピーする。
            if( copy($src_file,$dst_file) === false ){
                $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-90093",array(basename($src_file))); 
                $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
                return false;
            }
        }
        return true;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // 処理内容
    //   ITAが機器一覧で管理しているwinRMサーバー証明書ファイルのパスを取得
    // パラメータ
    //   $in_key:        winRMサーバー証明書ファイルのPkey(データベース)
    //   $in_filename:   winRMサーバー証明書ファイル名
    //
    // 戻り値
    //   ホスト変数定義ファイル名名
    ////////////////////////////////////////////////////////////////////////////////
    function getITA_win_ca_file($in_key, $in_filename){
        global  $root_dir_path;

        $file = sprintf("%s/%s/%s",
                        $root_dir_path . "/" . self::LC_ITA_WIN_CA_FILE_PATH,
                        addPadding($in_key),
                        $in_filename);
        return($file);
    }
    ////////////////////////////////////////////////////////////////////////////////
    // 処理内容
    //   inディレクトリからのwinRMサーバー証明書ファイルパス(win_ca_files)を取得
    // パラメータ
    //   $in_pkey:    winRMサーバー証明書ファイルのPkey(データベース)
    //   $in_file:    winRMサーバー証明書ファイル名
    //
    // 戻り値
    //   inディレクトリ内のSSH認証ファイルパス
    ////////////////////////////////////////////////////////////////////////////////
    function getAnsible_win_ca_file($in_pkey, $in_file){

        $file = sprintf("%s/%s-%s",
                        $this->lv_Ansible_win_ca_files_Dir,
                        addPadding($in_pkey),
                        $in_file);
        return($file);
    }
    ////////////////////////////////////////////////////////////////////////////////
    // 処理内容
    //   inディレクトリにwinRMサーバー証明書ファイルをコピーする。
    // パラメータ
    //   $in_pkey:    winRMサーバー証明書ファイルのPkey(データベース)
    //   $in_file:    winRMサーバー証明書ファイル名
    //
    // 戻り値
    //---
    ////////////////////////////////////////////////////////////////////////////////
    function createWIN_ca_file($in_pkey, $in_win_ca_file, &$in_dir_win_ca_file){
        //サーバー証明書が存在しているか確認
        $src_file = $this->getITA_win_ca_file($in_pkey, $in_win_ca_file);
        if( file_exists($src_file) === false ){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-5000034",array($in_pkey,basename($src_file)));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
            return false;
        }

        // Ansible実行時のサーバー証明書パス取得
        $dst_file = $this->getAnsible_win_ca_file($in_pkey, $in_win_ca_file);

        //サーバー証明書をansible用ディレクトリにコピーする。
        if( copy($src_file, $dst_file) === false ){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-5000035",array($in_pkey,basename($src_file)));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
            return false;
        }

        if( !chmod( $dst_file, 0600 ) ){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-5000036",array(__LINE__));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
            return false;
        }

        // Ansible実行時のサーバー証明書ファイルパス退避
        $in_dir_win_ca_file = $dst_file;
        return true;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F0040
    // 処理内容
    //   多次元配列のメンバー変数へのパス配列を生成
    //
    // パラメータ
    //   $in_var_name_str:     多次元配列のメンバー変数へのパス
    //   $ina_var_path_array:  多次元配列のメンバー変数へのパス配列
    //
    // 戻り値
    //   なし
    ////////////////////////////////////////////////////////////////////////////////
    function makeHostVarsPath($in_var_name_str, &$ina_var_path_array){
        $ina_var_path_array = array();
        // [3].array1.array2[0].array2_2[0].array2_2_2[0].array2_2_2_2
        // []を取り除く
        $in_var_name_str = str_replace('[','.',$in_var_name_str);
        $in_var_name_str = str_replace(']','',$in_var_name_str);
        // 先頭が配列の場合の . を取り除く
        $in_var_name_str = preg_replace('/^\./',"", $in_var_name_str);
        $ina_var_path_array = explode('.',$in_var_name_str);
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F0041
    // 処理内容
    //   多次元配列の具体値情報をホスト変数ファイルに戻す為の配列作成
    //
    // パラメータ
    //   $ina_var_path_array:     多次元配列のメンバー変数へのパス配列
    //   $in_idx:                 階層番号(0～)
    //   $in_out_array:           ホスト変数ファイルに戻す為の配列
    //   $in_var_type:            メンバー変数のタイプ
    //                              1: Key-Value変数
    //                              2: 複数具体値変数
    //   $in_var_val:             メンバー変数の具体値
    //   $in_ass_no:              複数具体値変数の場合の代入順序
    //
    // 戻り値
    //   なし
    ////////////////////////////////////////////////////////////////////////////////
    function makeHostVarsArray($in_key_array, $in_idx, &$in_out_array, $in_var_type, $in_var_val, $in_ass_no){
        // 末端の変数に達したか判定
        if(count($in_key_array) <= $in_idx){
            // 末端の変数か判定
            if(count($in_key_array) == $in_idx){
                // 具体値を埋め込む
                if($in_var_type == '1'){
                    // Key-Value変数の場合
                    $in_out_array = trim($in_var_val);
                }
                else{
                    // 複数具体値の場合
                    $in_out_array[$in_ass_no] = trim($in_var_val);
                    // 代入順序で昇順ソートする。
                    ksort($in_out_array);
                }
            }
            return;
         }
         // 該当階層の変数名を取得
         $var_name = $in_key_array[$in_idx];
         // ホスト変数配列に変数名が退避されているか判定
         if(@count($in_out_array[$var_name]) == 0){
             // 変数名をホスト変数配列に退避
             $in_out_array[$var_name] = array();
             // 配列の場合に列順序で昇順ソート
             if(is_numeric($var_name)){
                 ksort($in_out_array);
             }
         }
         $in_idx++;
         // 次の階層へ
         $this->makeHostVarsArray($in_key_array,$in_idx,$in_out_array[$var_name],$in_var_type,$in_var_val,$in_ass_no);
    }

    function MultiArrayVarsToYamlFormatSub($ina_host_vars_array,
                                          &$in_str_hostvars,
                                           $in_before_vars,
                                           $in_indent,
                                           $nest_level,
                                          &$in_error_code,
                                          &$in_line,
                                          &$ina_varsval_cpf_vars_list){
        $idx = 0;

        // 配列階層か判定
        $array_f = is_assoc($ina_host_vars_array);
        if($array_f == -1){
            $in_error_code = "ITAANSTWRH-ERR-90232";
            $in_line       = __LINE__;
            return false;
        }

        if($array_f != 'I'){
            $indent = $in_indent . "  ";
            $nest_level = $nest_level + 1;
        }
        else{
            $indent = $in_indent;
        }

        foreach($ina_host_vars_array as $var => $val) {

            // 繰返数設定
            $idx = $idx + 1;

            // 現階層の変数名退避
            $before_vars = $var;

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
                    $in_error_code = "ITAANSTWRH-ERR-90233";
                    $in_line       = __LINE__;
                    return false;
                }
            }
            // 複数具体値か判定する。
            if(is_numeric($var)){

                // 具体値があるか判定
                if( ! is_array($val)){
                    // 変数の具体値にコピー変数が使用されていないか確認
                    $ret = $this->checkConcreteValueIsVar($val, $ina_varsval_cpf_vars_list);
                    if($ret == false){
                        //エラーメッセージは出力しているので、ここでは何も出さない。
                        $in_error_code = "";
                        return false;
                    }
                    // 具体値出力
                    // - xxxxxxx
                    $vars_str = sprintf("%s- %s\n",$indent,$val);
                    $in_str_hostvars = $in_str_hostvars . $vars_str;

                    continue;
                }
                else{
                    // 具体値がないので配列階層
                    // 配列階層の場合はインデントを1つ戻す。
                    if($idx == 1){
                        $indent = substr($indent,0,-2);

                    }
                }
            }
            else{
                // 1つ前の階層が配列階層か判定
                if(is_numeric($in_before_vars)){
                    // Key-Value変数か判定
                    if( ! is_array($val)){
                        // Key-Value変数の場合
                        if($idx == 1){
                            // 変数の具体値にコピー変数が使用されていないか確認
                            $ret = $this->checkConcreteValueIsVar($val, $ina_varsval_cpf_vars_list);
                            if($ret == false){
                                //エラーメッセージは出力しているので、ここでは何も出さない。
                                $in_error_code = "";
                                return false;
                            }
                            // 変数と具体値出力 配列の先頭変数なので - を付ける
                            // - xxxxx: xxxxxxx
                            $vars_str = sprintf("%s- %s: %s\n",$indent,$var,$val);
                            $in_str_hostvars = $in_str_hostvars . $vars_str;
                            // インデント位置を加算
                            $indent = $indent . "  ";
                        }
                        else{
                            // 変数の具体値にコピー変数が使用されていないか確認
                            $ret = $this->checkConcreteValueIsVar($val, $ina_varsval_cpf_vars_list);
                            if($ret == false){
                                //エラーメッセージは出力しているので、ここでは何も出さない。
                                $in_error_code = "";
                                return false;
                            }
                            // 変数と具体値出力 配列の先頭変数ではないので - は付けない
                            //   xxxxx: xxxxxx
                            // インデント位置は加算済み
                            $vars_str = sprintf("%s%s: %s\n",$indent,$var,$val);
                            $in_str_hostvars = $in_str_hostvars . $vars_str;
                        }
                        continue;
                    }
                    else{
                        // ネスト変数の場合
                        if($idx == 1){
                            // 変数出力 配列の先頭変数なので - を付ける
                            $vars_str = sprintf("%s- %s:\n",$indent,$var);
                            $in_str_hostvars = $in_str_hostvars . $vars_str;
                            // インデント位置を加算
                            $indent = $indent . "  ";
                        }
                        else{
                            // 変数出力 配列の先頭変数ではないので - は付けない
                            $vars_str = sprintf("%s%s:\n",$indent,$var);
                            $in_str_hostvars = $in_str_hostvars . $vars_str;
                        }
                    }
                }
                else{
                    // Key-Value変数か判定
                    if( ! is_array($val)){
                        // 変数の具体値にコピー変数が使用されていないか確認
                        $ret = $this->checkConcreteValueIsVar($val, $ina_varsval_cpf_vars_list);
                        if($ret == false){
                            //エラーメッセージは出力しているので、ここでは何も出さない。
                            $in_error_code = "";
                            return false;
                        }
                        // 変数と具体値出力
                        // xxxxx: xxxxxxx
                        $vars_str = sprintf("%s%s: %s\n",$indent,$var,$val);
                        $in_str_hostvars = $in_str_hostvars . $vars_str;
                        continue;
                    }
                    else{
                        // ネスト変数として出力
                        // xxxxx:
                        $vars_str = sprintf("%s%s:\n",$indent,$var);
                        $in_str_hostvars = $in_str_hostvars . $vars_str;
                    }
                }
            }
            $ret = $this->MultiArrayVarsToYamlFormatSub($val,
                                                        $in_str_hostvars,
                                                        $before_vars,
                                                        $indent,
                                                        $nest_level,
                                                        $in_error_code,
                                                        $in_line,
                                                        $ina_varsval_cpf_vars_list);
            if($ret === false){
                return false;
            }
        }
        return true;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F1014
    // 処理内容
    //   Spycモジュールの読み込み
    //
    // パラメータ
    //   $in_errmsg:              エラー時のメッセージ格納
    //   $in_f_name:              ファイル名
    //   $in_f_line:              エラー発生行番号格納
    //
    // 戻り値
    //   true:   正常
    //   false:  異常
    ////////////////////////////////////////////////////////////////////////////////
    function LoadSpycModule(&$in_errmsg, &$in_f_name, &$in_f_line){
        global $root_dir_path;

        $in_f_name = __FILE__;

        // Spycモジュールのパスを取得
        $spyc_path = @file_get_contents($root_dir_path . "/confs/commonconfs/path_PHPSpyc_Classes.txt");
        if($spyc_path === false){
            $in_errmsg = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70084");
            $in_f_line = __LINE__;
            return false;
        }
        // 改行コードが付いている場合に取り除く
        $spyc_path = str_replace("\n", "", $spyc_path);
        $spyc_path = $spyc_path . "/Spyc.php";
        if( file_exists($spyc_path) === false ){
            $in_errmsg = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-70085");
            $in_f_line = __LINE__;
            return false;
        }
        require ($spyc_path);

        return true;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F0045
    // 処理内容
    //   Role内のcopyモジュールで使用している変数
    //   抜出しホスト変数ファイルに追加する。
    // パラメータ
    //   $ina_hosts:            ホスト名(IP)配列
    //                          [管理システム項番]=ホスト名(IP)
    //
    //   $ina_hostprotcollist:  ホスト毎プロトコル一覧
    //                          [ホスト名(IP)][ホスト名][PROTOCOL_NAME][LOGIN_USER]=LOGIN_PASSWD
    //
    //   $ina_rolenames:        処理対象ロールリスト
    //                          [INCLUDE_SEQ][ROLE_ID]=ROLE_NAME
    //
    //   $ina_cpf_vars_list:    copyモジュールで使用している変数リスト
    //                          [使用Playbookファイル名][行番号][変数名][CONTENTS_FILE_ID]
    //                          [使用Playbookファイル名][行番号][変数名][CONTENTS_FILE]
    // 戻り値
    //   true:   正常
    //   false:  異常
    ////////////////////////////////////////////////////////////////////////////////
    function CreateCopyVarsFiles($ina_hosts, $ina_hostprotcollist, $ina_rolenames, $ina_cpf_vars_list){
        ///////////////////////////////////////////////////////////////////
        // 処理対象のロール名抽出
        ///////////////////////////////////////////////////////////////////
        // 処理対象のロール名抽出
        $tgt_role_list = array();
        foreach( $ina_rolenames as $no=>$rolename_list ){
            foreach( $rolename_list as $rolepkey=>$rolename ){
                $tgt_role_list[$rolename] = 1;
            }
        }
        ///////////////////////////////////////////////////////////////////
        // copy変数に紐づくファイルの情報を取得
        ///////////////////////////////////////////////////////////////////
        $la_cpf_files = array();
        $la_cpf_path = array();
        foreach( $ina_cpf_vars_list as $role_name => $tgt_file_list ){
            if(@strlen($tgt_role_list[$role_name]) == 0){
                continue;
            }
            foreach( $tgt_file_list as $tgt_file => $line_no_list ){
                foreach( $line_no_list as $line_no => $cpf_var_name_list ){
                    foreach( $cpf_var_name_list as $cpf_var_name => $file_info_list ){
                        // inディレクトリ配下のcopyファイルバスを取得
                        $cpf_path = $this->getHostvarsfile_copy_file_value($file_info_list['CONTENTS_FILE_ID'],
                                                                           $file_info_list['CONTENTS_FILE']);
                        // $la_cpf_path[copy変数]=inディレクトリ配下ののcopyファイルパス
                        $la_cpf_path[$cpf_var_name] = $cpf_path;

                        // copyファイルのpkeyとファイル名を退避
                        $la_cpf_files[$file_info_list['CONTENTS_FILE_ID']] = $file_info_list['CONTENTS_FILE'];
                    }
                }
            }
        }
        ///////////////////////////////////////////////////////////////////
        // copyファイルを所定のディレクトリにコピー
        ///////////////////////////////////////////////////////////////////
        if(count($la_cpf_files) > 0){
            $ret = $this->CreateCopyfiles($la_cpf_files);
            if( $ret == false ){
                return false;
            }
        }
        ///////////////////////////////////////////////////////////////////
        // ホスト変数定義ファイルにcopy変数を追加
        ///////////////////////////////////////////////////////////////////
        if ( count($la_cpf_path) > 0 ){
            // ホスト変数配列のホスト)分繰返し
            foreach( $ina_hosts as $host_name){
                foreach($ina_hostprotcollist[$host_name] as $hostname => $prolist)
                //ホスト変数定義ファイル名を取得
                $file_name = $this->getAnsible_host_var_file($hostname);
                // ホスト変数定義ファイルにテンプレート変数を追加
                $ret = $this->CreateRoleHostvarsfile("CPF", $file_name, $la_cpf_path, array(), "", "a");
                if($ret === false){
                    return false;
                }
            }
        }
        return true;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F0046
    // 処理内容
    //   読替表のデータを取得する。
    // 
    // パラメータ
    //   $in_pattern_id:                 該当作業パターン
    //   $ina_translationtable_list:     読替表のデータリスト
    // 
    // 戻り値
    //   true:   正常
    //   false:  異常
    ////////////////////////////////////////////////////////////////////////////////
    function getDBTranslationTable($in_pattern_id,&$ina_translationtable_list){
        global $log_output_dir;
        global $log_file_prefix;
        global $log_level;

        $sql = "SELECT                                              \n" .
               "  TBL_2.ROLE_PACKAGE_ID,                            \n" .
               "  TBL_2.ROLE_ID,                                    \n" .
               "  TBL_2.ITA_VARS_NAME,                              \n" .
               "  TBL_2.ANY_VARS_NAME                               \n" .
               "FROM                                                \n" .
               "  $this->lv_ansible_rep_var_listDB TBL_2            \n" .
               "LEFT OUTER JOIN                                     \n" .
               "  (                                                 \n" .
               "    SELECT                                          \n" .
               "      ROLE_PACKAGE_ID,                              \n" .
               "      ROLE_ID                                       \n" .
               "    FROM                                            \n" .
               "      $this->lv_ansible_pattern_linkDB              \n" .
               "    WHERE                                           \n" .
               "      PATTERN_ID  = :PATTERN_ID AND                 \n" .
               "      DISUSE_FLAG = '0'                             \n" .
               "  ) TBL_1 ON (TBL_1.ROLE_PACKAGE_ID =               \n" .
               "              TBL_2.ROLE_PACKAGE_ID AND             \n" .
               "              TBL_1.ROLE_ID         =               \n" .
               "              TBL_2.ROLE_ID)                        \n" .
               "WHERE                                               \n" .
               "  TBL_2.DISUSE_FLAG = '0' AND                       \n" .
               "  TBL_1.ROLE_PACKAGE_ID is not NULL                 \n";

        $objQuery = $this->lv_objDBCA->sqlPrepare($sql);
        if($objQuery->getStatus()===false){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-56100",array(basename(__FILE__),__LINE__));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

            $this->DebugLogPrint(basename(__FILE__),__LINE__,$sql);
            $this->DebugLogPrint(basename(__FILE__),__LINE__,$objQuery->getLastError());

            return false;
        }
        $objQuery->sqlBind( array('PATTERN_ID'=>$in_pattern_id));

        $r = $objQuery->sqlExecute();
        if (!$r){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-56100",array(basename(__FILE__),__LINE__));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

            $this->DebugLogPrint(basename(__FILE__),__LINE__,$sql);
            $this->DebugLogPrint(basename(__FILE__),__LINE__,$objQuery->getLastError());

            unset($objQuery);
            return false;
        }

        $ina_translationtable_list = array();

        while ( $row = $objQuery->resultFetch() ){
            $ina_translationtable_list[$row['ITA_VARS_NAME']] = $row['ANY_VARS_NAME'];
        }

        // DBアクセス事後処理
        unset($objQuery);
    
        return true;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // 処理内容
    //   ITAが機器一覧で管理しているSSH秘密鍵ファイルのパスを取得
    // パラメータ
    //   $in_key:        SSH秘密鍵ファイルのPkey(データベース)
    //   $in_filename:   SSH秘密鍵ファイル名
    //
    // 戻り値
    //   ホスト変数定義ファイル名名
    ////////////////////////////////////////////////////////////////////////////////
    function getITA_ssh_key_file($in_key, $in_filename){
        global  $root_dir_path;

        $file = sprintf("%s/%s/%s",
                        $root_dir_path . "/" . self::LC_ITA_SSH_KEY_FILE_PATH,
                        addPadding($in_key),
                        $in_filename);
        return($file);
    }
    ////////////////////////////////////////////////////////////////////////////////
    // 処理内容
    //   inディレクトリからのSSH秘密鍵ファイルパス(ssh_key_files)を取得
    // パラメータ
    //   $in_pkey:    SSH秘密鍵ファイルのPkey(データベース)
    //   $in_file:    SSH秘密鍵ファイル
    //
    // 戻り値
    //   inディレクトリ内のSSH認証ファイルパス
    ////////////////////////////////////////////////////////////////////////////////
    function getIN_ssh_key_file($in_pkey, $in_file){
        $file = sprintf("%s/%s-%s",
                        $this->lv_Ansible_ssh_key_files_Dir,
                        addPadding($in_pkey),
                        $in_file);
        return($file);
    }
    ////////////////////////////////////////////////////////////////////////////////
    // 処理内容
    //   inディレクトリからのSSH秘密鍵ファイルパス(ssh_key_files)を取得
    // パラメータ
    //   $in_pkey:    SSH秘密鍵ファイルのPkey(データベース)
    //   $in_file:    SSH秘密鍵ファイル
    //
    // 戻り値
    //   inディレクトリ内のSSH秘密鍵ファイルパス
    ////////////////////////////////////////////////////////////////////////////////
    function CreateSSH_key_file($in_pkey, $in_ssh_key_file, &$in_in_dir_ssh_key_file){
        //SSH秘密鍵ファイルが存在しているか確認
        $src_file = $this->getITA_ssh_key_file($in_pkey, $in_ssh_key_file);
        if( file_exists($src_file) === false ){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-5000012",array($in_pkey,basename($src_file)));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
            return false;
        }

        // Ansible実行時のSSH秘密鍵ファイルパス取得
        $dst_file = $this->getIN_ssh_key_file($in_pkey, $in_ssh_key_file);

        //SSH認証ファイルをansible用ディレクトリにコピーする。
        if( copy($src_file,$dst_file) === false ){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-5000013",array($in_pkey,basename($src_file)));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
            return false;
        }

        if( !chmod( $dst_file, 0600 ) ){
            $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-5000014",array(__LINE__));
            $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);
            return false;
        }

        // Ansible実行時のSSH秘密鍵ファイルパス退避
        $in_in_dir_ssh_key_file = $dst_file;
        return true;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // F0049
    // 処理内容
    //   LegacyRole用 
    //   変数の具体値にコピー/テンプレート変数が使用されてるかを判定
    //   使用されている場合に各ファイルを所定のディレクトリにコピーする。
    // パラメータ
    //   $in_objLibs:                AnsibleCommonLibsクラスオブジェクト
    //
    //   $in_var_val:                変数の具体値
    //
    //   $ina_legacy_cpf_vars_list:  変数の具体値にコピー変数が使用されているコピー変数のリスト
    // 
    // 戻り値
    //   true:   正常
    //   false:  異常
    // 
    ////////////////////////////////////////////////////////////////////////////////
    function checkConcreteValueIsVar($in_var_val, &$ina_varsval_cpf_vars_list){
        $cpf_vars_list = array();
        // コピー変数　{{ CPF_[a-zA-Z0-9_] }} を取出す
        $ret = preg_match_all("/{{(\s)" . "CPF_" . "[a-zA-Z0-9_]*(\s)}}/",$in_var_val,$var_match);
        if(($ret !== false) && ($ret > 0)){
            foreach($var_match[0] as $var_name){
                $ret = preg_match_all("/CPF_" . "[a-zA-Z0-9_]*/",$var_name,$var_name_match);
                $var_name = trim($var_name_match[0][0]);
                if(@strlen($ina_varsval_cpf_vars_list[$var_name]) == 0){
                    $cpf_vars_list[$var_name] = "";
                }
            }
        }
        if(count($cpf_vars_list) == 0){
            return true;
        }

        ///////////////////////////////////////////////////////////////////
        // コピー変数の情報処理
        ///////////////////////////////////////////////////////////////////
        $la_cpf_files = array();
        foreach( $cpf_vars_list as $cpf_var_name=>$dummy){
            if(@strlen($ina_varsval_cpf_vars_list[$cpf_var_name]) == 0){
                $strErrMsg       = "";
                $strErrDetailMsg = "";
                $cpf_key         = "";
                $cpf_file_name   = "";
                ///////////////////////////////////////////////////////////////////
                // コピー変数に紐づくファイルの情報を取得
                ///////////////////////////////////////////////////////////////////
                $ret= getCopyMasterData($this->lv_objMTS,
                                        $this->lv_objDBCA,
                                        $cpf_var_name,
                                        $cpf_key,
                                        $cpf_file_name,
                                        $strErrMsg,
                                        $strErrDetailMsg);
                if($ret === false){
                    //エラーが発生した場合は処理終了
                    $this->LocalLogPrint(basename(__FILE__),__LINE__,$strErrMsg);
                    if($strErrDetailMsg != ""){
                        $this->DebugLogPrint(basename(__FILE__),__LINE__,$strErrDetailMsg);
                    }
                    return false;
                }
                else{
                    // コピーファイル名が未登録の場合
                    if($cpf_file_name == "" ){
                        $msgstr = $this->lv_objMTS->getSomeMessage("ITAANSTWRH-ERR-5000030",
                                                                    array($cpf_var_name)); 
                        $this->LocalLogPrint(basename(__FILE__),__LINE__,$msgstr);

                        return false;
                    }
                }
                // inディレクトリ配下のcopyファイルバスを取得
                $cpf_path = $this->getHostvarsfile_copy_file_value($cpf_key,$cpf_file_name);

                // $ina_varsval_cpf_vars_list[copy変数]=inディレクトリ配下ののcopyファイルパス
                $ina_varsval_cpf_vars_list[$cpf_var_name] = $cpf_path;

                // copyファイルのpkeyとファイル名を退避 
                $la_cpf_files[$cpf_key]=$cpf_file_name;
            }
        }
        ///////////////////////////////////////////////////////////////////
        // コピー変数に紐づくファイルを所定のディレクトリに配置
        ///////////////////////////////////////////////////////////////////
        if(count($la_cpf_files) > 0){
            // copyファイルを所定のディレクトリにコピーする。
            $ret = $this->CreateCopyfiles($la_cpf_files);
            if( $ret == false ){
                return false;
            }
        }
        return true;
    }

}
