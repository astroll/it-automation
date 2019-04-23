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
    //    RedMineチケット#1033に伴いansibleモジュールの実行に必要な共通定数を定義
    //
    //////////////////////////////////////////////////////////////////////

    // ドライバ識別子
    define("DF_LEGACY_DRIVER_ID"           ,"L");
    define("DF_LEGACY_ROLE_DRIVER_ID"      ,"R");
    define("DF_PIONEER_DRIVER_ID"          ,"P");

    // ユーザーホスト変数名の先頭文字
    define("DF_HOST_VAR_HED"               ,"VAR_");
    // テンプレートファイル変数名の先頭文字
    define("DF_HOST_TPF_HED"               ,"TPF_");
    // copyファイル変数名の先頭文字
    define("DF_HOST_CPF_HED"               ,"CPF_");
    // グローバル変数名の先頭文字
    define("DF_HOST_GBL_HED"               ,"GBL_");
    // テンプレートファイルからグローバル変数を取り出す場合の区分
    define("DF_HOST_TEMP_GBL_HED"          ,"TEMP_GBL_");

    // ロールパッケージ管理 ロールパッケージファイル(ZIP)格納先ディレクトリ
    define("DF_ROLE_PACKAGE_FILE_CONTENTS_DIR"  ,"/uploadfiles/2100020303/ROLE_PACKAGE_FILE");
    // ITA側で管理している legacy用 子playbookファイル格納先ディレクトリ
    $vg_legacy_playbook_contents_dir  = $root_dir_path . "/uploadfiles/2100020104/PLAYBOOK_MATTER_FILE";
    // ITA側で管理している legacy用 テンプレートファイル格納先ディレクトリ
    $vg_legacy_template_contents_dir = $root_dir_path . "/uploadfiles/2100020110/ANS_TEMPLATE_FILE";
    // ITA側で管理している pioneer用 対話ファイル格納先ディレクトリ
    $vg_pioneer_playbook_contents_dir = $root_dir_path . "/uploadfiles/2100020205/DIALOG_MATTER_FILE";
    // ITA側で管理している pioneer用 テンプレートファイル格納先ディレクトリ
    $vg_pioneer_template_contents_dir = $root_dir_path . "/uploadfiles/2100020216/ANS_TEMPLATE_FILE";
?>
