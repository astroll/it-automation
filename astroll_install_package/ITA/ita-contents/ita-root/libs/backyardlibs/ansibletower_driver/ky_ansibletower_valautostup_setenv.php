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
//	AnsibleTowerの代入値自動登録に必要な変数の初期値設定
//
//////////////////////////////////////////////////////////////////////

/* カラムタイプ */
define("DEFINE_COL_TYPE_VAL" , "1"); // Value型
define("DEFINE_COL_TYPE_KEY" , "2"); // Key型
define("DEFINE_COL_TYPE_KVL" , "3"); // Key-Value型

/* 代入値紐付メニューSELECT時のITA独自カラム名 */
define("DEFINE_ITA_LOCAL_OPERATION_CNT" , "__ITA_LOCAL_COLUMN_1__");
define("DEFINE_ITA_LOCAL_HOST_CNT" , "__ITA_LOCAL_COLUMN_2__");
define("DEFINE_ITA_LOCAL_DUP_CHECK_ITEM" , "__ITA_LOCAL_COLUMN_3__");
define("DEFINE_ITA_LOCAL_PKEY" , "__ITA_LOCAL_COLUMN_4__");

/* VARS_ATTR_ID の定義 */
define("GC_VARS_ATTR_STD" ,"1"); // 一般変数
define("GC_VARS_ATTR_LIST" , "2"); // 複数具体値
define("GC_VARS_ATTR_M_ARRAY" , "3"); // 多次元変数

/* DB更新ユーザー設定 */
$db_valautostup_user_id = -121004 ;

/* <START> AnsibleTower機能関連テーブル一覧------------------------------------------------------------------------------------------------- */
$param_col_vars_link     = 'B_ANSTWR_PRMCOL_VARS_LINK' ; // 代入値自動登録設定
$pattern_role_link       = 'B_ANSTWR_PTN_ROLE_LINK' ; // Movement詳細
$vars_list               = 'B_ANSTWR_VARS' ; // 変数一覧テーブル
$pattern_vars_link       = 'B_ANSTWR_PTN_VARS_LINK' ; // Movement変数紐付
$nested_member_vars_list = 'B_ANSTWR_NESTED_MEM_VARS' ; // 多段変数メンバー管理
$nested_member_col_cmb   = 'B_ANSTWR_NESTEDMEM_COL_CMB' ; // 多段変数配列組合せ管理
$vars_assign             = 'B_ANSTWR_VARS_ASSIGN' ; // 代入値管理
$pattern_host_op_link    = 'B_ANSTWR_PHO_LINK'; // 作業対象ホスト管理
$param_menu_link         = 'B_CMDB_MENU_LIST'; // 紐付対象メニュー
$param_menu_table_list   = 'B_CMDB_MENU_TABLE'; // 紐付対象メニューテーブル管理
$param_menu_column_list  = 'B_CMDB_MENU_COLUMN'; // 紐付対象メニューカラム管理
/* <END> AnsibleTower機能関連テーブル一覧--------------------------------------------------------------------------------------------------- */


/* 代入値自動登録設定 */
$strCurTableValAss      = $param_col_vars_link;
$strJnlTableValAss      = $strCurTableValAss . "_JNL";
$strSeqOfCurTableValAss = $strCurTableValAss . "_RIC";
$strSeqOfJnlTableValAss = $strCurTableValAss . "_JSQ";

$arrayConfigOfValAss = array
(
	 "JOURNAL_SEQ_NO"             => ""
	,"JOURNAL_REG_DATETIME"       => ""
	,"JOURNAL_ACTION_CLASS"       => ""
	,"PRMCOL_VARS_LINK_ID"        => ""
	,"MENU_ID"                    => ""
	,"MENU_COLUMN_ID"             => ""
	,"PRMCOL_LINK_TYPE_ID"        => ""
	,"PATTERN_ID"                 => ""
	,"KEY_VARS_LINK_ID"           => ""
	,"KEY_NESTEDMEM_COL_CMB_ID"   => ""
	,"KEY_ASSIGN_SEQ"             => ""
	,"VALUE_VARS_LINK_ID"         => ""
	,"VALUE_NESTEDMEM_COL_CMB_ID" => ""
	,"VALUE_ASSIGN_SEQ"           => ""
	,"DISP_SEQ"                   => ""
	,"NOTE"                       => ""
	,"DISUSE_FLAG"                => ""
	,"LAST_UPDATE_TIMESTAMP"      => ""
	,"LAST_UPDATE_USER"           => ""
);

$arrayValueTmplOfValAss = $arrayConfigOfValAss;

/* Movement詳細 */
$strCurTablePtnLnk      = $pattern_role_link;
$strJnlTablePtnLnk      = $strCurTablePtnLnk . "_JNL";
$strSeqOfCurTablePtnLnk = $strCurTablePtnLnk . "_RIC";
$strSeqOfJnlTablePtnLnk = $strCurTablePtnLnk . "_JSQ";


/* 変数一覧テーブル名 */
$strCurTableVarsMst      = $vars_list;
$strJnlTableVarsMst      = $strCurTableVarsMst . "_JNL";
$strSeqOfCurTableVarsMst = $strCurTableVarsMst . "_RIC";
$strSeqOfJnlTableVarsMst = $strCurTableVarsMst . "_JSQ";


/* Movement変数紐付 */
$strCurTablePtnVarsLnk      = $pattern_vars_link;
$strJnlTablePtnVarsLnk      = $strCurTablePtnVarsLnk . "_JNL";
$strSeqOfCurTablePtnVarsLnk = $strCurTablePtnVarsLnk . "_RIC";
$strSeqOfJnlTablePtnVarsLnk = $strCurTablePtnVarsLnk . "_JSQ";


/* 多段変数メンバー管理 */
$strCurTableArrMem      = $nested_member_vars_list;
$strJnlTableArrMem      = $strCurTableArrMem . "_JNL";
$strSeqOfCurTableArrMem = $strCurTableArrMem . "_RIC";
$strSeqOfJnlTableArrMem = $strCurTableArrMem . "_JSQ";


/* 多段変数配列組合せ管理 */
$strCurTableMemColComb      = $nested_member_col_cmb;
$strJnlTableMemColComb      = $strCurTableMemColComb . "_JNL";
$strSeqOfCurTableMemColComb = $strCurTableMemColComb . "_RIC";
$strSeqOfJnlTableMemColComb = $strCurTableMemColComb . "_JSQ";


/* 代入値管理 */
$strCurTableVarsAss      = $vars_assign;
$strJnlTableVarsAss      = $strCurTableVarsAss . "_JNL";
$strSeqOfCurTableVarsAss = $strCurTableVarsAss . "_RIC";
$strSeqOfJnlTableVarsAss = $strCurTableVarsAss . "_JSQ";

$arrayConfigOfVarAss = array
(
	 "JOURNAL_SEQ_NO"        => ""
	,"JOURNAL_REG_DATETIME"  => ""
	,"JOURNAL_ACTION_CLASS"  => ""
	,"VARS_ASSIGN_ID"        => ""
	,"OPERATION_NO_UAPK"     => ""
	,"PATTERN_ID"            => ""
	,"SYSTEM_ID"             => ""
	,"VARS_LINK_ID"          => ""
	,"NESTEDMEM_COL_CMB_ID"  => ""
	,"VARS_ENTRY"            => ""
	,"ASSIGN_SEQ"            => ""
	,"VARS_VALUE"            => ""
	,"DISP_SEQ"              => ""
	,"NOTE"                  => ""
	,"DISUSE_FLAG"           => ""
	,"LAST_UPDATE_TIMESTAMP" => ""
	,"LAST_UPDATE_USER"      => ""
);

$arrayValueTmplOfVarAss = $arrayConfigOfVarAss;


/* 作業対象ホスト管理 */
$strCurTablePhoLnk      = $pattern_host_op_link;
$strJnlTablePhoLnk      = $strCurTablePhoLnk . "_JNL";
$strSeqOfCurTablePhoLnk = $strCurTablePhoLnk . "_RIC";
$strSeqOfJnlTablePhoLnk = $strCurTablePhoLnk . "_JSQ";

$arrayConfigOfPhoLnk = array
(
	 "JOURNAL_SEQ_NO"        => ""
	,"JOURNAL_REG_DATETIME"  => ""
	,"JOURNAL_ACTION_CLASS"  => ""
	,"PHO_LINK_ID"           => ""
	,"OPERATION_NO_UAPK"     => ""
	,"PATTERN_ID"            => ""
	,"SYSTEM_ID"             => ""
	,"DISP_SEQ"              => ""
	,"NOTE"                  => ""
	,"DISUSE_FLAG"           => ""
	,"LAST_UPDATE_TIMESTAMP" => ""
	,"LAST_UPDATE_USER"      => ""
);

$arrayValueTmplOfPhoLnk = $arrayConfigOfPhoLnk;


/* 紐付対象メニュー */
$strCurTableMenu      = $param_menu_link;
$strJnlTableMenu      = $strCurTableMenu . "_JNL";
$strSeqOfCurTableMenu = $strCurTableMenu . "_RIC";
$strSeqOfJnlTableMenu = $strCurTableMenu . "_JSQ";


/* 紐付対象メニューテーブル管理 */
$strCurTableMenuTbl        = $param_menu_table_list;
$strJnlTableMenuTbl        = $strCurTableMenuTbl . "_JNL";
$strSeqOfCurTableMenuTbl   = $strCurTableMenuTbl . "_RIC";
$strSeqOfJnlTableMenuTbl   = $strCurTableMenuTbl . "_JSQ";


/* 値紐付対象メニューカラム管理 */
$strCurTableMenuCol      = $param_menu_column_list;
$strJnlTableMenuCol      = $strCurTableMenuCol . "_JNL";
$strSeqOfCurTableMenuCol = $strCurTableMenuCol . "_RIC";
$strSeqOfJnlTableMenuCol = $strCurTableMenuCol . "_JSQ";
?>
