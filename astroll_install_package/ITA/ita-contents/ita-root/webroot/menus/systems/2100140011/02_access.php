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
//	・WebDBCore機能を用いたWebページの、動的再描画などを行う。
//
//////////////////////////////////////////////////////////////////////

/* ディレクトリパスの取得 */
$tmpAry=explode('ita-root', dirname(__FILE__));$root_dir_path=$tmpAry[0].'ita-root';unset($tmpAry);

/* 共通パーツ「web_parts_for_template_02_access」を読み込み */
require_once ( $root_dir_path . "/libs/webcommonlibs/table_control_agent/web_parts_for_template_02_access.php");

/* 動的コンテンツを作成するクラス */
class Db_Access extends Db_Access_Core {
	/* <START> [オペレーション]をキーにして他カラムの値を動的に取得------------------------------------------------------------------------- */
	function Mix1_1_operation_upd($strOperationNumeric){
		/* グローバル変数宣言 */
		global $g;

		/* ローカル変数宣言 */
		$aryOverride = array("Mix1_1");
		
		$arrayResult = array();
		$aryVariant = array();
		$arySetting = array();

		$strResultCode = "";
		$strDetailCode = "";
		$strOutputStream = "";

		$objTable = loadTable();

		/* 本体ロジックをコール */
		$aryVariant = array('OPERATION_NO_UAPK'=>$strOperationNumeric);

		/* 動的値を取得する対象カラム（Movement） */
		$int_seq_no = 2;
		require_once ( $g['root_dir_path'] . "/libs/webcommonlibs/table_control_agent/12_singleRowTable_AddSelectTag.php");
		$arrayResult01 = AddSelectTagToDynamicSelectTab($objTable, "update_table", $int_seq_no, $aryVariant, $arySetting, $aryOverride);

		if( $arrayResult01[0]=="000" ){
			$strResultCode = "000";
			$strDetailCode = "000";
			$strResult01Stream = makeAjaxProxyResultStream(array($arrayResult01[2],$arrayResult01[3]));
			$strOutputStream = makeAjaxProxyResultStream(array($strResult01Stream));
		}else{
			$strResultCode = "500";
			$strDetailCode = "000";
		}
		$arrayResult = array($strResultCode,$strDetailCode,$strOutputStream);

		if($arrayResult[0]=="000"){
			web_log( $g['objMTS']->getSomeMessage("ITAWDCH-STD-4001",__FUNCTION__));
		}else if(intval($arrayResult[0])<500){
			web_log( $g['objMTS']->getSomeMessage("ITAWDCH-ERR-4002",__FUNCTION__));
		}else{
			web_log( $g['objMTS']->getSomeMessage("ITAWDCH-ERR-4001",__FUNCTION__));
		}

		return makeAjaxProxyResultStream($arrayResult);
	}
	
	function Mix2_1_operation_reg($strOperationNumeric){
		/* グローバル変数宣言 */
		global $g;

		/* ローカル変数宣言 */
		$aryOverride = array("Mix2_1");
		
		$arrayResult = array();
		$aryVariant = array();
		$arySetting = array();

		$strResultCode = "";
		$strDetailCode = "";
		$strOutputStream = "";

		$objTable = loadTable();

		/* 本体ロジックをコール */
		$aryVariant = array('OPERATION_NO_UAPK'=>$strOperationNumeric);

		/* 動的値を取得する対象カラム（Movement） */
		$int_seq_no = 2;
		require_once ( $g['root_dir_path'] . "/libs/webcommonlibs/table_control_agent/12_singleRowTable_AddSelectTag.php");
		$arrayResult01 = AddSelectTagToDynamicSelectTab($objTable, "register_table", $int_seq_no, $aryVariant, $arySetting, $aryOverride);

		/* 結果判定 */
		if( $arrayResult01[0]=="000"){
			$strResultCode = "000";
			$strDetailCode = "000";
			$strResult01Stream = makeAjaxProxyResultStream(array($arrayResult01[2],$arrayResult01[3]));
			$strOutputStream = makeAjaxProxyResultStream(array($strResult01Stream));
		}else{
			$strResultCode = "500";
			$strDetailCode = "000";
		}
		$arrayResult = array($strResultCode,$strDetailCode,$strOutputStream);

		if($arrayResult[0]=="000"){
			web_log($g['objMTS']->getSomeMessage("ITAWDCH-STD-4001",__FUNCTION__));
		}else if(intval($arrayResult[0])<500){
			web_log($g['objMTS']->getSomeMessage("ITAWDCH-ERR-4002",__FUNCTION__));
		}else{
			web_log( $g['objMTS']->getSomeMessage("ITAWDCH-ERR-4001",__FUNCTION__));
		}

		return makeAjaxProxyResultStream($arrayResult);
	}
	/* < END > [オペレーション]をキーにして他カラムの値を動的に取得------------------------------------------------------------------------- */
	
	/* <START> [Movement]をキーに他カラムの値を動的に取得----------------------------------------------------------------------------------- */
	function Mix1_1_pattern_upd($strOperationNumeric, $strPatternNumeric){
		/* グローバル変数宣言 */
		global $g;

		/* ローカル変数宣言 */
		$aryOverride = array("Mix1_1");
		
		$arrayResult = array();
		$aryVariant = array();
		$arySetting = array();

		$strResultCode = "";
		$strDetailCode = "";
		$strOutputStream = "";

		$objTable = loadTable();

		/* 本体ロジックをコール */
		$aryVariant = array('OPERATION_NO_UAPK'=>$strOperationNumeric, 'PATTERN_ID'=>$strPatternNumeric);

		/* 動的値を取得する対象カラム(作業対象ホスト) */
		$int_seq_no = 3;
		require_once ( $g['root_dir_path'] . "/libs/webcommonlibs/table_control_agent/12_singleRowTable_AddSelectTag.php");
		$arrayResult01 = AddSelectTagToDynamicSelectTab($objTable, "update_table", $int_seq_no, $aryVariant, $arySetting, $aryOverride);

		/* 動的値を取得する対象カラム(変数名) */
		$int_seq_no = 4;
		$arrayResult02 = AddSelectTagToDynamicSelectTab($objTable, "update_table", $int_seq_no, $aryVariant, $arySetting, $aryOverride);

		/* 結果判定 */
		if( $arrayResult01[0]=="000" && $arrayResult02[0]=="000" ){
			$strResultCode = "000";
			$strDetailCode = "000";
			$strResult01Stream = makeAjaxProxyResultStream(array($arrayResult01[2],$arrayResult01[3]));
			$strResult02Stream = makeAjaxProxyResultStream(array($arrayResult02[2],$arrayResult02[3]));
			$strOutputStream = makeAjaxProxyResultStream(array($strResult01Stream,$strResult02Stream));
		}else{
			$strResultCode = "500";
			$strDetailCode = "000";
		}
		$arrayResult = array($strResultCode,$strDetailCode,$strOutputStream);

		if($arrayResult[0]=="000"){
			web_log( $g['objMTS']->getSomeMessage("ITAWDCH-STD-4001",__FUNCTION__));
		}else if(intval($arrayResult[0])<500){
			web_log( $g['objMTS']->getSomeMessage("ITAWDCH-ERR-4002",__FUNCTION__));
		}else{
			web_log( $g['objMTS']->getSomeMessage("ITAWDCH-ERR-4001",__FUNCTION__));
		}

		return makeAjaxProxyResultStream($arrayResult);
	}

	function Mix2_1_pattern_reg($strOperationNumeric, $strPatternNumeric){
		/* グローバル変数宣言 */
		global $g;

		/* ローカル変数宣言 */
		$aryOverride = array("Mix2_1");
		
		$arrayResult = array();
		$aryVariant = array();
		$arySetting = array();

		$strResultCode = "";
		$strDetailCode = "";
		$strOutputStream = "";

		$objTable = loadTable();

		/* 本体ロジックをコール */
		$aryVariant = array('OPERATION_NO_UAPK'=>$strOperationNumeric, 'PATTERN_ID'=>$strPatternNumeric);

		/* 動的値を取得する対象カラム(作業対象ホスト) */
		$int_seq_no = 3;
		require_once ( $g['root_dir_path'] . "/libs/webcommonlibs/table_control_agent/12_singleRowTable_AddSelectTag.php");
		$arrayResult01 = AddSelectTagToDynamicSelectTab($objTable, "register_table", $int_seq_no, $aryVariant, $arySetting, $aryOverride);

		/* 動的値を取得する対象カラム(変数名) */
		$int_seq_no = 4;
		$arrayResult02 = AddSelectTagToDynamicSelectTab($objTable, "register_table", $int_seq_no, $aryVariant, $arySetting, $aryOverride);

		/* 結果判定 */
		if( $arrayResult01[0]=="000" && $arrayResult02[0]=="000" ){
			$strResultCode = "000";
			$strDetailCode = "000";
			$strResult01Stream = makeAjaxProxyResultStream(array($arrayResult01[2],$arrayResult01[3]));
			$strResult02Stream = makeAjaxProxyResultStream(array($arrayResult02[2],$arrayResult02[3]));
			$strOutputStream = makeAjaxProxyResultStream(array($strResult01Stream,$strResult02Stream));
		}else{
			$strResultCode = "500";
			$strDetailCode = "000";
		}
		$arrayResult = array($strResultCode,$strDetailCode,$strOutputStream);

		if($arrayResult[0]=="000"){
			web_log( $g['objMTS']->getSomeMessage("ITAWDCH-STD-4001",__FUNCTION__));
		}else if(intval($arrayResult[0])<500){
			web_log( $g['objMTS']->getSomeMessage("ITAWDCH-ERR-4002",__FUNCTION__));
		}else{
			web_log( $g['objMTS']->getSomeMessage("ITAWDCH-ERR-4001",__FUNCTION__));
		}

		return makeAjaxProxyResultStream($arrayResult);
	}
	/* < END > [Movement]をキーに他カラムの値を動的に取得----------------------------------------------------------------------------------- */

	/* <START> [変数名]をキーに他カラムの値を動的に取得------------------------------------------------------------------------------------- */
	function Mix1_1_vars_upd($strVarsLinkIdNumeric){
		/* グローバル変数宣言 */
		global $g;

		/* ローカル変数宣言 */
		$aryOverride = array("Mix1_1");
		
		$arrayResult = array();
		$aryVariant = array();
		$arySetting = array();

		$strResultCode = "";
		$strDetailCode = "";
		$strOutputStream = "";

		$objTable = loadTable();

		/* 本体ロジックをコール */
		$aryVariant = array('VARS_LINK_ID'=>$strVarsLinkIdNumeric, 'NESTEDMEM_COL_CMB_ID' => "");

		/* 動的値を取得する対象カラム（メンバー変数名） */
		$int_seq_no = 5;
		require_once ( $g['root_dir_path'] . "/libs/webcommonlibs/table_control_agent/12_singleRowTable_AddSelectTag.php");
		$arrayResult01 = AddSelectTagToDynamicSelectTab($objTable, "update_table", $int_seq_no, $aryVariant, $arySetting, $aryOverride);

		/* 結果判定 */
		if( $arrayResult01[0]=="000" ){
			$strResultCode = "000";
			$strDetailCode = "000";
			$strResult01Stream = makeAjaxProxyResultStream(array($arrayResult01[2],$arrayResult01[3],$arrayResult01[4]));
			$strOutputStream = makeAjaxProxyResultStream(array($strResult01Stream));
		}else{
			$strResultCode = "500";
			$strDetailCode = "000";
		}
		$arrayResult = array($strResultCode,$strDetailCode,$strOutputStream);

		if($arrayResult[0]=="000"){
			web_log( $g['objMTS']->getSomeMessage("ITAWDCH-STD-4001",__FUNCTION__));
		}else if(intval($arrayResult[0])<500){
			web_log( $g['objMTS']->getSomeMessage("ITAWDCH-ERR-4002",__FUNCTION__));
		}else{
			web_log( $g['objMTS']->getSomeMessage("ITAWDCH-ERR-4001",__FUNCTION__));
		}

		return makeAjaxProxyResultStream($arrayResult);
	}
	
	function Mix2_1_vars_reg($strVarsLinkIdNumeric){
		/* グローバル変数宣言 */
		global $g;

		/* ローカル変数宣言 */
		$aryOverride = array("Mix2_1");
		
		$arrayResult = array();
		$aryVariant = array();
		$arySetting = array();

		$strResultCode = "";
		$strDetailCode = "";
		$strOutputStream = "";

		$objTable = loadTable();

		/* 本体ロジックをコール */
		$aryVariant = array('VARS_LINK_ID'=>$strVarsLinkIdNumeric, 'NESTEDMEM_COL_CMB_ID' => "");

		/* 動的値を取得する対象カラム（メンバー変数名） */
		$int_seq_no = 5;
		require_once ( $g['root_dir_path'] . "/libs/webcommonlibs/table_control_agent/12_singleRowTable_AddSelectTag.php");
		$arrayResult01 = AddSelectTagToDynamicSelectTab($objTable, "register_table", $int_seq_no, $aryVariant, $arySetting, $aryOverride);

		if( $arrayResult01[0]=="000" ){
			$strResultCode = "000";
			$strDetailCode = "000";
			$strResult01Stream = makeAjaxProxyResultStream(array($arrayResult01[2],$arrayResult01[3],$arrayResult01[4]));
			$strOutputStream = makeAjaxProxyResultStream(array($strResult01Stream));
		}else{
			$strResultCode = "500";
			$strDetailCode = "000";
		}
		$arrayResult = array($strResultCode,$strDetailCode,$strOutputStream);

		if($arrayResult[0]=="000"){
			web_log( $g['objMTS']->getSomeMessage("ITAWDCH-STD-4001",__FUNCTION__));
		}else if(intval($arrayResult[0])<500){
			web_log( $g['objMTS']->getSomeMessage("ITAWDCH-ERR-4002",__FUNCTION__));
		}else{
			web_log( $g['objMTS']->getSomeMessage("ITAWDCH-ERR-4001",__FUNCTION__));
		}

		return makeAjaxProxyResultStream($arrayResult);
	}
	/* < END > [変数名]をキーに他カラムの値を動的に取得------------------------------------------------------------------------------------- */


	/* <START> [Movement][変数名][メンバー変数名][代入順序]をキーに他カラムの値を動的に取得------------------------------------------------- */
	function Mix1_1_default_val_upd($objPtnID, $objVarID, $objChlVarID, $objAssSeqID){
		/* グローバル変数宣言 */
		global $g;
		
		/* ローカル変数宣言 */
		$aryOverride = array("Mix1_1");
		
		$arrayResult = array();
		$aryVariant = array();
		$arySetting = array();

		$strResultCode = "";
		$strDetailCode = "";
		$strOutputStream = "";
		$strOutputStream2 = "";

		$objTable = loadTable();

		/* 本体ロジックをコール */
		$ret = $this->DBGetVarVal($g['objDBCA'], $g['objMTS'], $objPtnID, $objVarID, $objChlVarID, $objAssSeqID, $strOutputStream);
		if($ret === false){ $strOutputStream = " " ; }
		$strResultCode = "000";
		$strDetailCode = "000";
		$aryVariant = array('VARS_LINK_ID'=>$objVarID, 'NESTEDMEM_COL_CMB_ID' => $objChlVarID);

		/* 動的値を取得する対象カラム（メンバー変数名《具体値》用） */
		$int_seq_no = 5;
		require_once ( $g['root_dir_path'] . "/libs/webcommonlibs/table_control_agent/12_singleRowTable_AddSelectTag.php");
		$arrayResult01 = AddSelectTagToDynamicSelectTab($objTable, "update_table", $int_seq_no, $aryVariant, $arySetting, $aryOverride);
		/* 結果判定 */
		if( $arrayResult01[0]=="000" ){
			$strResultCode = "000";
			$strDetailCode = "000";
			$strResult01Stream = makeAjaxProxyResultStream(array($arrayResult01[2],$arrayResult01[3],$arrayResult01[4]));
			$strOutputStream2 = makeAjaxProxyResultStream(array($strResult01Stream));
		}else{
			$strResultCode = "500";
			$strDetailCode = "000";
		}

		$arrayResult = array($strResultCode,$strDetailCode,$strOutputStream, $strOutputStream2);

		return makeAjaxProxyResultStream($arrayResult);
	}

	function Mix2_1_default_val_reg($objPtnID, $objVarID, $objChlVarID, $objAssSeqID){
		/* グローバル変数宣言 */
		global $g;
		
		/* ローカル変数宣言 */
		$aryOverride = array("Mix2_1");
		
		$arrayResult = array();
		$aryVariant = array();
		$arySetting = array();

		$strResultCode = "";
		$strDetailCode = "";
		$strOutputStream = "";
		$strOutputStream2 = "";

		$objTable = loadTable();

		/* 本体ロジックをコール */
		$ret = $this->DBGetVarVal($g['objDBCA'], $g['objMTS'], $objPtnID, $objVarID, $objChlVarID, $objAssSeqID, $strOutputStream);
		if($ret === false){ $strOutputStream = " " ; }
		$strResultCode = "000";
		$strDetailCode = "000";
		$aryVariant = array('VARS_LINK_ID'=>$objVarID, 'NESTEDMEM_COL_CMB_ID' => $objChlVarID);

		/* 動的値を取得する対象カラム（メンバー変数名用） */
		$int_seq_no = 5;
		require_once ( $g['root_dir_path'] . "/libs/webcommonlibs/table_control_agent/12_singleRowTable_AddSelectTag.php");
		$arrayResult01 = AddSelectTagToDynamicSelectTab($objTable, "register_table", $int_seq_no, $aryVariant, $arySetting, $aryOverride);
		/* 結果判定 */
		if( $arrayResult01[0]=="000" ){
			$strResultCode = "000";
			$strDetailCode = "000";
			$strResult01Stream = makeAjaxProxyResultStream(array($arrayResult01[2],$arrayResult01[3],$arrayResult01[4]));
			$strOutputStream2 = makeAjaxProxyResultStream(array($strResult01Stream));
		}else{
			$strResultCode = "500";
			$strDetailCode = "000";
		}
		$arrayResult = array($strResultCode,$strDetailCode,$strOutputStream, $strOutputStream2);

		return makeAjaxProxyResultStream($arrayResult);
	}
	/* < END > [Movement][変数名][メンバー変数名][代入順序]をキーに他カラムの値を動的に取得------------------------------------------------- */


	/* <START> 具体値を取得する為の関数定義------------------------------------------------------------------------------------------------- */
	function Mix1_1_default_val_initdisp($objPkey){
		/* グローバル変数宣言 */
		global $g;
		
		$strResultCode = "";
		$strDetailCode = "";
		$strOutputStream = "";

		/* 本体ロジックをコール */
		$objPtnID = "";
		$objVarID = "";
		$objChlVarID  = "";
		$objColSeqID  = ""; 
		$objAssSeqID  = "";
		$ret = $this->DBGetVarAssData($g['objDBCA'], $g['objMTS'], $objPkey, $objPtnID, $objVarID, $objChlVarID, $objAssSeqID);
		if($ret === false){
			$strOutputStream = " " ;
		}
		else{
			$ret = $this->DBGetVarVal($g['objDBCA'], $g['objMTS'], $objPtnID, $objVarID, $objChlVarID, $objAssSeqID, $strOutputStream);
			if($ret === false){
				$strOutputStream = " ";
			}
		}
		$strResultCode = "000";
		$strDetailCode = "000";
		$arrayResult = array($strResultCode,$strDetailCode,$strOutputStream);

		return makeAjaxProxyResultStream($arrayResult);
	}


	/* ※同一のクエリー文を記述しているものがload_table側にもあるので、修正する時は横並びで修正する必要あり */
	function DBGetVarVal($objDBCA, $objMTS, $objPtnID, $objVarID, $objChlVarID, $objAssSeqID, &$varval){
		$varval = "";
		if(strlen($objPtnID)==0){
			 return false;
		}
		if(strlen($objVarID)==0){
			 return false;
		}

	/* 条件別クエリバーツ（この条件分岐ロジックのすぐ下にあるSQL内で使用する） */
	if(strlen($objChlVarID) != 0){
		$conditionalQueryParts = " TBL_3.NESTEDMEM_COL_CMB_ID = :NESTEDMEM_COL_CMB_ID" ;
	}else{
		$conditionalQueryParts = " TBL_3.NESTEDMEM_COL_CMB_ID IS NULL";
	}


	/* <START> ------------------------------------------------------------------- */
	$sql
	 = " 			SELECT"
	  ." 			   TBL_A.ROLE_PACKAGE_ID AS M_ROLE_PACKAGE_ID"
	  ." 			  ,TBL_A.ROLE_ID         AS M_ROLE_ID"
	  ." 			  ,TBL_B.ROLE_PACKAGE_ID"
	  ." 			  ,TBL_B.ROLE_ID"
	  ." 			  ,TBL_B.END_VAR_OF_VARS_ATTR_ID"
	  ." 			  ,TBL_B.VARS_ID"
	  ." 			  ,TBL_B.NESTEDMEM_COL_CMB_ID"
	  ." 			  ,TBL_B.ASSIGN_SEQ"
	  ." 			  ,TBL_B.VARS_VALUE"
	  ." 			FROM"
	  ." 			("
	  ." 			   SELECT"
	  ." 			      ROLE_PACKAGE_ID"
	  ." 			     ,ROLE_ID"
	  ." 			   FROM"
	  ." 			      B_ANSTWR_PTN_ROLE_LINK"
	  ." 			   WHERE"
	  ." 			      PATTERN_ID  = :PATTERN_ID"
	  ." 			    AND"
	  ." 			      DISUSE_FLAG = '0'"
	  ." 			)"
	  ." 			   AS TBL_A"
	  ." 			LEFT OUTER JOIN"
	  ." 			("
	  ." 			   SELECT DISTINCT"
	  ." 			      TBL_3.ROLE_PACKAGE_ID"
	  ." 			     ,TBL_3.ROLE_ID"
	  ." 			     ,TBL_3.END_VAR_OF_VARS_ATTR_ID"
	  ." 			     ,TBL_3.VARS_ID"
	  ." 			     ,TBL_3.NESTEDMEM_COL_CMB_ID"
	  ." 			     ,TBL_3.ASSIGN_SEQ"
	  ." 			     ,TBL_3.VARS_VALUE"
	  ." 			   FROM"
	  ." 			   ("
	  ." 			      SELECT"
	  ." 			         TBL_2.ROLE_PACKAGE_ID"
	  ." 			        ,TBL_2.ROLE_ID"
	  ." 			        ,TBL_2.END_VAR_OF_VARS_ATTR_ID"
	  ." 			        ,TBL_2.VARS_ID"
	  ." 			        ,TBL_2.NESTEDMEM_COL_CMB_ID"
	  ." 			        ,TBL_2.ASSIGN_SEQ"
	  ." 			        ,TBL_2.VARS_VALUE"
	  ." 			      FROM"
	  ." 			      ("
	  ." 			         SELECT"
	  ." 			            ROLE_PACKAGE_ID"
	  ." 			           ,ROLE_ID"
	  ." 			         FROM"
	  ." 			            B_ANSTWR_PTN_ROLE_LINK"
	  ." 			         WHERE"
	  ." 			            PATTERN_ID  = :PATTERN_ID"
	  ." 			          AND"
	  ." 			            DISUSE_FLAG = '0'"
	  ." 			      )"
	  ." 			         AS TBL_1"
	  ." 			      LEFT OUTER JOIN"
	  ." 			         B_ANSTWR_DEFAULT_VARSVAL AS TBL_2"
	  ." 			      ON"
	  ." 			         TBL_1.ROLE_PACKAGE_ID = TBL_2.ROLE_PACKAGE_ID"
	  ." 			       AND"
	  ." 			         TBL_1.ROLE_ID         = TBL_2.ROLE_ID"
	  ." 			      WHERE"
	  ." 			         TBL_2.DISUSE_FLAG = '0'"
	  ." 			   )"
	  ." 			      TBL_3"
	  ." 			   WHERE"
	  ." 			      TBL_3.VARS_ID IN"
	  ." 			      ("
	  ." 			         SELECT"
	  ." 			            VARS_ID"
	  ." 			         FROM"
	  ." 			            B_ANSTWR_PTN_VARS_LINK"
	  ." 			         WHERE"
	  ." 			            PATTERN_ID   = :PATTERN_ID"
	  ." 			          AND"
	  ." 			            VARS_LINK_ID = :VARS_LINK_ID"
	  ." 			          AND"
	  ." 			            DISUSE_FLAG  = '0'"
	  ." 			      )"
	  ." 			    AND"
	  ." 			      $conditionalQueryParts"
	  ." 			)"
	  ." 			   AS TBL_B"
	  ." 			ON"
	  ." 			   TBL_A.ROLE_PACKAGE_ID = TBL_B.ROLE_PACKAGE_ID"
	  ." 			 AND"
	  ." 			   TBL_A.ROLE_ID         = TBL_B.ROLE_ID" ;
	/* < END > ---------------------------------------------------------------------- */

		$objQuery = $objDBCA->sqlPrepare($sql);
		if($objQuery->getStatus()===false){
			web_log($objQuery->getLastError());

			return false;
		}

		if(strlen($objChlVarID) == 0){
			$objQuery->sqlBind
			(
				array
				(
					 'PATTERN_ID' => $objPtnID
					,'VARS_LINK_ID' => $objVarID
				)
			);
		}
		else{
			$objQuery -> sqlBind
			(
				array
				(
					 'PATTERN_ID' => $objPtnID
					,'VARS_LINK_ID' => $objVarID
					,'NESTEDMEM_COL_CMB_ID' => $objChlVarID
				)
			);
		}
		$r = $objQuery->sqlExecute();
		if (!$r){
			web_log($objQuery->getLastError());
			unset($objQuery);

			return false;
		}

		/* FETCH行数を取得 */
		$num_of_rows = $objQuery->effectedRowCount();
		/* レコード無しの場合 */
		if( $num_of_rows === 0 ){
			$varval = "undefined default value";
			unset($objQuery);
			return false;
		}
		$var_type  = "";
		$tgt_row = array();


/* <START> -------------------------------------------------------------------------------------------- */
			$errmsg    = "";
			$undef_cnt = 0;
			$def_cnt   = 0;
			$arr_type_def_list = array();
			$pkg_id    = "";
			while ( $row = $objQuery->resultFetch() ){
				$tgt_row[] =  $row;
				// 各ロールで変数が定義されているか判定
				// 複数具体値変数で具体値が未定義の場合は該当ロールの変数情報が具体値管理に登録されない。
				if(strlen($row['ROLE_ID'])==0){
					$undef_cnt++;
				}else{
					$def_cnt++;
				}
				// 同じロールパッケージが紐付てあるか判定
				if($pkg_id == ""){
					$pkg_id = $row['M_ROLE_PACKAGE_ID'];
				}else{
					if($pkg_id != $row['M_ROLE_PACKAGE_ID']){
						// DBアクセス事後処理
						unset($objQuery);

						// 複数ロールパッケージが紐付られている
						$varval   = "role packeage different";

						return false; 
					}
				}
			}
			unset($objQuery);

			// 全てのロールで具体値未定義を判定
			if($def_cnt == 0){

				return true;
			}
			// 一部のロールで具体値未定義を判定
			if(($def_cnt != 0) && ($undef_cnt != 0)){
				// 一部のロールで具体値未定義
				$varval   = "default value is undefined with some rolls";
				return false;
			}
			for($idx=0;$idx<count($tgt_row);$idx++){
				// 変数の属性を判定
				if($var_type == ""){
					$var_type = $tgt_row[$idx]['END_VAR_OF_VARS_ATTR_ID'];
				}else{
					if($var_type != $tgt_row[$idx]['END_VAR_OF_VARS_ATTR_ID']){
						$varval   = "variable type error";
						return false;
					}
				}
				// 複数具体値変数の場合、ロール毎の具体値をカウントしておく
				if($var_type == '2'){
					if(@count($arr_type_def_list[$tgt_row[$idx]['ROLE_ID']]) == 0){
						$arr_type_def_list[$tgt_row[$idx]['ROLE_ID']] = 1;
					}else{
						$arr_type_def_list[$tgt_row[$idx]['ROLE_ID']]++;
					}
				}
			}
			// 複数具体値変数の場合、ロール毎の具体値の数が一致しているか判定
			$val_cnt = "";
			foreach($arr_type_def_list as $role_id=>$role_val_cnt){
				if($val_cnt == ""){
					$val_cnt = $role_val_cnt;
				}else{
					if($val_cnt != $role_val_cnt){
						$varval   = "default value count not match";

						return false;
					}
				}
			}
/* < END > ------------------------------------------------------------- */


		$wk_varval = array();
		$wk_roles = "";
		$wk_seqs = array();
		$errmsg = "";

		/* 一般変数の場合 */
		if('1' == $var_type){
			if(0 === count($tgt_row)){
				$varval = "";
			}
			else if(1 === count($tgt_row)){
				$varval = $tgt_row[0]['VARS_VALUE'];
			}
			else{
				/* 各ロールのデフォルト値が同じか確認する。同じ場合は表示する */
				$varval = $tgt_row[0]['VARS_VALUE'];
				for($idx=0;$idx<count($tgt_row);$idx++){
					if($varval != $tgt_row[$idx]['VARS_VALUE']){
						$errmsg = "duplicate variable value" ;
					}
				}
			}
		}
		/* 複数具体値変数の場合 */
		else if('2' == $var_type){
			if(0 === count($tgt_row)){
				$varval = "";
			}
			else{
				foreach($tgt_row as $row){
					if(@count($wk_varval[$row['ASSIGN_SEQ']]) != 0){
						if($wk_varval[$row['ASSIGN_SEQ']] != $row['VARS_VALUE']){
							$errmsg = "default value is not ident";
							break;
						}
					}
					else{
						$wk_varval[$row['ASSIGN_SEQ']] = $row['VARS_VALUE'];
					}
					/* 各ロールの代入順序を退避 */
					if(@count($wk_seqs[$row['ASSIGN_SEQ']]) != 0){
						$wk_seqs[$row['ASSIGN_SEQ']] = $wk_seqs[$row['ASSIGN_SEQ']] + 1;
					}
					else{
						$wk_seqs[$row['ASSIGN_SEQ']] = 1;
					}
				}

				/* 各ロールの代入順序が一致しているか判定 */
				$seq_count = "";
				foreach($wk_seqs as $seq=>$count){
					if($seq_count == ""){
						$seq_count = $count;
					}
					else{
						if($seq_count != $count){
							$errmsg = "assign sequence is not ident";
							break;
						}
					}
				}

				/* 代入順序でソートする */
				ksort($wk_varval);
				$varval = "";
				foreach($wk_varval as $seq=>$val){
					if($varval != ""){
						$varval = $varval . "<BR>";
					}
					$varval = $varval . "(" . $seq . ")" . $val;
				}
			}
		}
		else{
			$errmsg = "variable type error" ;
			break;
		}

		if($errmsg != ""){
			$varval = $errmsg;

			return false;
		}

		return true;
	}

	/* 取得した具体値に基づいた[代入順序]の取得 */
	function DBGetVarAssData($objDBCA, $objMTS, $objPkey, &$objPtnID, &$objVarID, &$objChlVarID, &$objAssSeqID){
		$sql
		 = " \n			SELECT"
		  ." \n			   PATTERN_ID"
		  ." \n			  ,VARS_LINK_ID"
		  ." \n			  ,NESTEDMEM_COL_CMB_ID"
		  ." \n			  ,ASSIGN_SEQ"
		  ." \n			FROM"
		  ." \n			   B_ANSTWR_VARS_ASSIGN"
		  ." \n			WHERE"
		  ." \n			   VARS_ASSIGN_ID = :VARS_ASSIGN_ID"
		  ." \n"
		;

		$objQuery = $objDBCA->sqlPrepare($sql);
		if($objQuery->getStatus()===false){
			web_log($objQuery->getLastError());
			return false;
		}
		$objQuery->sqlBind( array('VARS_ASSIGN_ID'=>$objPkey));
		$r = $objQuery->sqlExecute();
		if (!$r){
			web_log($objQuery->getLastError());
			unset($objQuery);

			return false;
		}
		/* FETCH行数を取得 */
		$num_of_rows = $objQuery->effectedRowCount();

		/* レコード無しの場合 */
		if( $num_of_rows != 1 ){
			unset($objQuery);

			return false;
		}
		$row = $objQuery->resultFetch();
		$objPtnID	= $row['PATTERN_ID'];
		$objVarID	= $row['VARS_LINK_ID'];
		$objChlVarID = $row['NESTEDMEM_COL_CMB_ID'];
		$objAssSeqID = $row['ASSIGN_SEQ'];

		return true;
	}
	/* < END > 具体値を取得する為の関数定義------------------------------------------------------------------------------------------------- */
}
$server = new HTML_AJAX_Server();
$server->registerClass(new Db_Access());
$server->handleRequest();
