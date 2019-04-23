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
////en_US_UTF-8_ITADSCH_STD
$ary[101020] = "Performed emergency stop. Check the status (Execution No.:{})";
$ary[102090] = "Cancelled schedule. Check the status (Execution No.:{})";
$ary[50001] = "No target record";
$ary[50002] = "Detect target record (Execution No.:{})";
$ary[50003] = "Start process loop for target record";
$ary[50004] = "[Process] Start transaction (Execution No.:{})";
$ary[50008] = "[Process] Lock record (Execution No.:{})";
$ary[50009] = "[Process] Start REST API call (Execution No.:{})";
$ary[50016] = "[Process] Get resultdata (Execution No.:{})";
$ary[50025] = "[Process] Set status as “Unexpected error” (Execution No.:{})";
$ary[50028] = "[Process] Set status as “Completed” (Execution No.:{})";
$ary[50029] = "[Process] Generate UPDATE statement for Completed or Error (Execution No.:{})";
$ary[50030] = "[Process] Detect delay (Execution No.:{})";
$ary[50031] = "[Process] No delay (Execution No.:{})";
$ary[50034] = "[Process] Set status as “Executing (delayed)” (Execution No.:{})";
$ary[50036] = "[Process] Generate UPDATE statement for executing or executing (delayed) (Execution No.:{})";
$ary[50037] = "[Process] Execute UPDATE (Execution No.:{})";
$ary[50039] = "[Process] Commit (Execution No.:{})";
$ary[50040] = "[Process] End transaction (Execution No.:{})";
$ary[50041] = "End process loop for target record (Execution No.:{})";
$ary[50042] = "Rollback (Execution No.:{})";
$ary[50043] = "Rollback";
$ary[50046] = "[Execution No.:{}] End transaction ";
$ary[50047] = "End transaction";
$ary[50051] = "[Process] Create history file (Execution No.:{} File name:{})";
$ary[51001] = "Start transaction (Process to change status to “Preparing”)";
$ary[51002] = "End transaction (Process to change status to “Preparing”)";
$ary[51003] = "No target record";
$ary[51004] = "Detect target record (EXECUTION_NO:{})";
$ary[51005] = "Start UPDATE loop for status “Preparing”";
$ary[51006] = "[Process] Execute UPDATE (Status= “Preparing”) (Execution No.:{})";
$ary[51007] = "End UPDATE loop for status “Preparing”";
$ary[51008] = "Commit (Process to change status to “Preparing”)";
$ary[51009] = "End transaction (Process to change status to “Preparing”)";
$ary[51010] = "Start process loop for target record";
$ary[51011] = "[Process] Start transaction (Execution No.:{})";
$ary[51015] = "[Process] Lock record (Execution No.:{})";
$ary[51066] = "[Process] Start REST API call (Execution No.:{})";
$ary[51067] = "[Process] End REST API call (Execution No.:{} HTTP response code:{})";
$ary[51071] = "[Process](Execution No.:{} REST API error flag:{})";
$ary[51072] = "[Process] Generate UPDATE statement for normal (Execution No.:{})";
$ary[51074] = "[Process] Generate UPDATE statement for error (Execution No.:{})";
$ary[51075] = "[Process] Execute UPDATE (Execution No.:{})";
$ary[51077] = "[Process] Commit (Execution No.:{})";
$ary[51078] = "[Process] End transaction (Execution No.:{})";
$ary[51080] = "End process loop for target record";
$ary[51081] = "Rollback (Execution No.:{})";
$ary[51082] = "Rollback";
$ary[51085] = "End transaction (Execution No.:{})";
$ary[51086] = "End transaction";
$ary[55001] = "Start procedure";
$ary[55002] = "End procedure (normal)";
$ary[55003] = "DB Connect completed";
$ary[55004] = "[Process] Start transaction";
$ary[55005] = "[Process] End transaction";
$ary[55015] = "[Process] Commit";
$ary[55016] = "[Process] Rollback";
$ary[58101] = "[Process] Zip/compress input directory ([Execution No.]:{} Zip file:{}) ";
$ary[59101] = "[Process] Compress result directory ([Execution No.]:{} Zip file:{}) ";
$ary[59801] = "[Process] Create history file ([Execution No].:{} File name:{}) ";

$ary[70000] = "[Process] Get information of the associated menu";
$ary[70001] = "[Process] Add the menu of the Associated menu table list (MENU_ID:{})";
$ary[70002] = "[Process] Restore the menu of the Associated menu table list (MENU_ID:{})";
$ary[70003] = "[Process] Update the menu of the Associated menu table list (MENU_ID:{} TBALE:{})";
$ary[70004] = "[Process] Discard the menu from the Associated menu table list (MENU_ID:{})";
$ary[70005] = "[Process] Get the menu of the associated menu column (MENU_ID:{})";
$ary[70006] = "[Process] Associated menu table list is not updated (MENU_ID:{})";
$ary[70007] = "[Process] Associated menu column list is not updated (MENU_ID:{} COLUMN:{})";
$ary[70008] = "[Process] Add the menu of the Associated menu column list (MENU_ID:{} COLUMN:{})";
$ary[70009] = "[Process] Restore the menu of the Associated menu column list (MENU_ID:{} COLUMN:{})";
$ary[70010] = "[Process] Update the menu of the Associated menu column list (MENU_ID:{} COLUMN:{})";
$ary[70011] = "[Process] Discard the menu from the Associated menu column list (MENU_ID:{} COLUMN:{})";
$ary[70012] = "[Process] Delete the unnecessary menu from the Associated menu table list";
$ary[70013] = "[Process] Delete the unnecessary menu from the Associated menu column list";
$ary[70014] = "[Process] Delete the unnecessary menu column from the Substitution value auto-registration setting";
$ary[70015] = "[Process] Get variable information of respective column, from the Substitution value auto-registration setting";
$ary[70016] = "[Process] Get specific value from associated menu";
$ary[70017] = "[Process] Get specific value from associated menu (MENU_ID:{})";
$ary[70018] = "[Process] Register the specific values of the associated menu which is associated with general variable, in the substitution value list";
$ary[70019] = "[Process] Register the specific values of the associated menu which is associated with array variables, in the substitution value list";
$ary[70020] = "[Process] Delete the unnecessary data from the substitution value list";
$ary[70021] = "[Process] Operation ID + Movement ID + host ID that is not registered in the target host registers the target host";
$ary[70022] = "[Process] Delete the unnecessary data from target host";
$ary[70023] = "[Process] Add substitution value list (OPERATION_ID:{} PATTERN_ID:{} SYSTEM_ID:{} VARS_LINK_ID:{} ASSIGN_SEQ:{})";
$ary[70024] = "[Process] Restore substitution value list (OPERATION_ID:{} PATTERN_ID:{} SYSTEM_ID:{} VARS_LINK_ID:{} ASSIGN_SEQ:{})";
$ary[70025] = "[Process] Update substitution value list (OPERATION_ID:{} PATTERN_ID:{} SYSTEM_ID:{} VARS_LINK_ID:{} ASSIGN_SEQ:{})";
$ary[70026] = "[Process] Update not required substitution value list(OPERATION_ID:{} PATTERN_ID:{} SYSTEM_ID:{} VARS_LINK_ID:{} ASSIGN_SEQ:{})";
$ary[70027] = "[Process] Add substitution value list (OPERATION_ID:{} PATTERN_ID:{} SYSTEM_ID:{} VARS_LINK_ID:{} CHILD_VARS_LINK_ID:{} CHILD_VARS_COL_SEQ:{})";
$ary[70028] = "[Process] Restore substitution value list (OPERATION_ID:{} PATTERN_ID:{} SYSTEM_ID:{} VARS_LINK_ID:{} CHILD_VARS_LINK_ID:{} CHILD_VARS_COL_SEQ:{})";
$ary[70029] = "[Process] Update substitution value list (OPERATION_ID:{} PATTERN_ID:{} SYSTEM_ID:{} VARS_LINK_ID:{} CHILD_VARS_LINK_ID:{} CHILD_VARS_COL_SEQ:{})";
$ary[70030] = "[Process] Update of the substitution value list is not required (OPERATION_ID:{} PATTERN_ID:{} SYSTEM_ID:{} VARS_LINK_ID:{} CHILD_VARS_LINK_ID:{} CHILD_VARS_COL_SEQ:{})";
$ary[70031] = "[Process] Discard substitution value list (ASSIGN_ID:{})";
$ary[70032] = "[Process] Add target host (OPERATION_ID:{} PATTERN_ID:{} SYSTEM_ID:{})";
$ary[70033] = "[Process] Restore target host (OPERATION_ID:{} PATTERN_ID:{} SYSTEM_ID:{})";
$ary[70034] = "[Process] Discard target host (PHO_LINK_ID:{})";
$ary[70035] = "[Process] Discard the substitution value auto-registration setting (Item No.:{})";
$ary[70036] = "[Process] Skip update of the substitution value list because last update was not done by the person himself (OPERATION_ID:{} PATTERN_ID:{} SYSTEM_ID:{} VARS_LINK_ID:{} COL_SEQ_COMBINATION_ID:{} ASSIGN_SEQ:{})";
$ary[70037] = "[Process]Skip update of the substitution value list because last update was not done by the person himself (OPERATION_ID:{} PATTERN_ID:{} SYSTEM_ID:{} VARS_LINK_ID:{} COL_SEQ_COMBINATION_ID:{} ASSIGN_SEQ:{})";
$ary[70038] = "[Process] Skip discard of the substitution value list because last update was not done by the person himself ASSIGN_ID:{})";
$ary[70039] = "[Process] Skip discard of the target host because last update was not done by person himself (PHO_LINK_ID:{})";
$ary[70040] = "[Process] Skip discard of the variable name list because last update was not done by the person himself \n{}";
$ary[70041] = "[Process] Skip discard of the member variable list because last update was not done by the person himself \n{}";
$ary[70042] = "[Process] Skip discard of the Movement variable association list because last update was not done by the person himself \n{}";
$ary[70043] = "[Process] Skip update of the variable name list because last update was not done by the person himself \n{}";
$ary[70044] = "[Process] Register the specific value of associated menu which is associated with generic variable / array-type variable, in the substitution value list";
$ary[70046] = "[Process] Skip update of the substitution value list because last update was not done by the person himself (OPERATION_ID:{} PATTERN_ID:{} SYSTEM_ID:{} VARS_LINK_ID:{} ASSIGN_SEQ:{})";
$ary[70047] = "[Process] Add substitution value list (OPERATION_ID:{} PATTERN_ID:{} SYSTEM_ID:{} VARS_LINK_ID:{} COL_SEQ_COMBINATION_ID:{})";
$ary[70048] = "[Process] Restore substitution value list (OPERATION_ID:{} PATTERN_ID:{} SYSTEM_ID:{} VARS_LINK_ID:{} COL_SEQ_COMBINATION_ID:{} ASSIGN_SEQ:{})";
$ary[70049] = "[Process] Update substitution value list (OPERATION_ID:{} PATTERN_ID:{} SYSTEM_ID:{} VARS_LINK_ID:{} COL_SEQ_COMBINATION_ID:{} ASSIGN_SEQ:{})";
$ary[70050] = "[Process] Update not required substitution value list is not required (OPERATION_ID:{} PATTERN_ID:{} SYSTEM_ID:{} VARS_LINK_ID:{} COL_SEQ_COMBINATION_ID:{} ASSIGN_SEQ:{})";
$ary[70051] = "[Process] Skip update of the substitution value list because last update was not done by the person himself (OPERATION_ID:{} PATTERN_ID:{} SYSTEM_ID:{} VARS_LINK_ID:{} COL_SEQ_COMBINATION_ID:{} ASSIGN_SEQ:{})";

$ary[990000] = "End transaction";
$ary[990001] = "Start procedure";
$ary[990002] = "End procedure (normal)";
$ary[990003] = "DB Connect completed";
$ary[990004] = "[Process] Start transaction";
$ary[990005] = "[Process] End transaction";
$ary[990006] = "[Process] Commit";
$ary[990007] = "[Process] Rollback";
$ary[990008] = "Discard record for which storage period has expired, from substitution value list (Table:{})";
$ary[990009] = "Discard record for which storage period has expired, from target host management (Table:{})";
$ary[990010] = "Physically delete record for which storage period has expired, from substitution value list (Table:{})";
$ary[990011] = "Physically delete record for which storage period has expired, from target host management (Table:{})";
$ary[990012] = "Physically delete expired records associated with the Operation ID, from substitution value list (Table name:{})";
$ary[990013] = "Physically delete expired records associated with the Operation ID, from target host management (Table name:{})";
$ary[990014] = "Start REST process";
$ary[990015] = "Execution process";
$ary[990016] = "Check process";
$ary[990017] = "[Process] Start REST API call(Execution No.{})";
$ary[990018] = "{} Start outputting error messages";
$ary[990019] = "[DSC Compile process] Start processing(Execution No.{} Config file:{})";
$ary[990020] = "[DSC Compile process] process completed(Execution No.{} Config file:{})";
$ary[990021] = "[DSC Start process] Start processing(Execution No.{} Config file:{})";
$ary[990022] = "[DSC Start process] Process completed(Execution No.{} Config file:{})";
$ary[990023] = "[DSC Check process] Start processing(Execution No.{} Config file:{})";
$ary[990024] = "[DSC Check process] Process completed(Execution No.{} Config file:{})";
$ary[990025] = "[DSC Check process] The target node is preconfigured or configured in a completed state.(Execution No.{} Target node:{} Status:{} Result:{})";
$ary[990026] = "[DSC Check process] Configuration is being applied.(Execution No.{} Target node:{} status:{} Result:{})";
$ary[990027] = "[DSC Check process] Target node is restarting request (Pendingreboot) .(Execution No.{} Target node:{} status:{} Result:{})";
$ary[990028] = "[DSC Test process] Start processing(Execution No.{} Config file:{})";
$ary[990029] = "[DSC Test process] Process completed(Execution No.{} Config file:{})";
$ary[990030] = "[DSC Test process] The configuration information for the LCM and the configuration of the target node were identical.(Execution No.{} Target node:{})";
$ary[990031] = "RESTAPI Emergency stop processing started";
$ary[990032] = "[DSC Emergency stop] Start processing(Execution No.{} )";
$ary[990033] = "[DSC Emergency stop] Stop target Node(Execution No.{} Target node {})";
$ary[990034] = "[DSC Emergency stop] Detection before stop processing:There is a DSC process running.Stop the process.(Execution No.{} Target node{} Status{})";
$ary[990035] = "[DSC Emergency stop] Emergency stop success (Execution No.{} Target node{} Result{} Status{})";
$ary[990036] = "[DSC Emergency stop] Stop detection:Emergency stop success (Execution No.{} Target node{} Result{} Status{})";
$ary[990037] = "[DSC Emergency stop] The emergency stop file (forced.txt) was already present and was deleted.(Execution No.{} Target node{} Result{} Status{})";
$ary[990038] = "[DSC Emergency stop] Output of emergency stop file (forced.txt)(Execution No.{} )";
$ary[990039] = "[DSC Emergency stop] Output of emergency stop file (forced.txt)(Execution No.{} )";
$ary[990040] = "[DSC LCM set process] Start processing(Execution No.{} Config file:{} Target node:{})";
$ary[990041] = "[DSC LCM set process] Process completed(Execution No.{} Config file:{} Target node:{})";
?>