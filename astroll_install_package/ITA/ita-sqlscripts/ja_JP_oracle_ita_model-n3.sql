-- *****************************************************************************
-- *** ***** CreateParameterMenu Tables                                      ***
-- *****************************************************************************
CREATE TABLE F_HG_VAR_LINK_LEGACY
(
ROW_ID                             NUMBER                          , -- 識別シーケンス項番

OPERATION_NO_UAPK                  NUMBER                          ,
PATTERN_ID                         NUMBER                          ,
SYSTEM_ID                          NUMBER                          ,
VARS_NAME                          VARCHAR2(128)                   ,

DISP_SEQ                           NUMBER                          , -- 表示順序
NOTE                               VARCHAR2(4000)                  , -- 備考
DISUSE_FLAG                        VARCHAR2(1)                     , -- 廃止フラグ
LAST_UPDATE_TIMESTAMP              TIMESTAMP                       , -- 最終更新日時
LAST_UPDATE_USER                   NUMBER                          , -- 最終更新ユーザ

PRIMARY KEY (ROW_ID)
);

CREATE TABLE F_HG_VAR_LINK_LEGACY_JNL
(
JOURNAL_SEQ_NO                     NUMBER                          , -- 履歴用シーケンス
JOURNAL_REG_DATETIME               TIMESTAMP                       , -- 履歴用変更日時
JOURNAL_ACTION_CLASS               VARCHAR2(8)                     , -- 履歴用変更種別

ROW_ID                             NUMBER                          , -- 識別シーケンス項番

OPERATION_NO_UAPK                  NUMBER                          ,
PATTERN_ID                         NUMBER                          ,
SYSTEM_ID                          NUMBER                          ,
VARS_NAME                          VARCHAR2(128)                   ,

DISP_SEQ                           NUMBER                          , -- 表示順序
NOTE                               VARCHAR2(4000)                  , -- 備考
DISUSE_FLAG                        VARCHAR2(1)                     , -- 廃止フラグ
LAST_UPDATE_TIMESTAMP              TIMESTAMP                       , -- 最終更新日時
LAST_UPDATE_USER                   NUMBER                          , -- 最終更新ユーザ
PRIMARY KEY(JOURNAL_SEQ_NO)
);

CREATE TABLE F_HG_VAR_LINK_LEGACYROLE
(
ROW_ID                             NUMBER                          , -- 識別シーケンス項番

OPERATION_NO_UAPK                  NUMBER                          ,
PATTERN_ID                         NUMBER                          ,
SYSTEM_ID                          NUMBER                          ,
VARS_NAME                          VARCHAR2(128)                   ,

DISP_SEQ                           NUMBER                          , -- 表示順序
NOTE                               VARCHAR2(4000)                  , -- 備考
DISUSE_FLAG                        VARCHAR2(1)                     , -- 廃止フラグ
LAST_UPDATE_TIMESTAMP              TIMESTAMP                       , -- 最終更新日時
LAST_UPDATE_USER                   NUMBER                          , -- 最終更新ユーザ

PRIMARY KEY (ROW_ID)
);

CREATE TABLE F_HG_VAR_LINK_LEGACYROLE_JNL
(
JOURNAL_SEQ_NO                     NUMBER                          , -- 履歴用シーケンス
JOURNAL_REG_DATETIME               TIMESTAMP                       , -- 履歴用変更日時
JOURNAL_ACTION_CLASS               VARCHAR2(8)                     , -- 履歴用変更種別

ROW_ID                             NUMBER                          , -- 識別シーケンス項番

OPERATION_NO_UAPK                  NUMBER                          ,
PATTERN_ID                         NUMBER                          ,
SYSTEM_ID                          NUMBER                          ,
VARS_NAME                          VARCHAR2(128)                   ,

DISP_SEQ                           NUMBER                          , -- 表示順序
NOTE                               VARCHAR2(4000)                  , -- 備考
DISUSE_FLAG                        VARCHAR2(1)                     , -- 廃止フラグ
LAST_UPDATE_TIMESTAMP              TIMESTAMP                       , -- 最終更新日時
LAST_UPDATE_USER                   NUMBER                          , -- 最終更新ユーザ
PRIMARY KEY(JOURNAL_SEQ_NO)
);
INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('F_HG_VAR_LINK_LEGACY_RIC',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('F_HG_VAR_LINK_LEGACY_JSQ',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('F_HG_VAR_LINK_LEGACYROLE_RIC',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('F_HG_VAR_LINK_LEGACYROLE_JSQ',1);


INSERT INTO A_MENU_LIST (MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100170006,2100011701,'ホストグループ変数紐付(Ansible-Legacy)',NULL,NULL,NULL,1,0,1,2,6,'hostgroup_var_link_legacy','0',TO_TIMESTAMP('2015/04/01 00:00:00.000000','YYYY/MM/DD/ HH24:MI:SS.FF6'),1);
INSERT INTO A_MENU_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-170006,TO_TIMESTAMP('2015/04/01 00:00:00.000000','YYYY/MM/DD/ HH24:MI:SS.FF6'),'INSERT',2100170006,2100011701,'ホストグループ変数紐付(Ansible-Legacy)',NULL,NULL,NULL,1,0,1,2,6,'hostgroup_var_link_legacy','0',TO_TIMESTAMP('2015/04/01 00:00:00.000000','YYYY/MM/DD/ HH24:MI:SS.FF6'),1);
INSERT INTO A_MENU_LIST (MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100170007,2100011701,'ホストグループ変数紐付(Ansible-LegacyRole)',NULL,NULL,NULL,1,0,1,2,7,'hostgroup_var_link_legacyrole','0',TO_TIMESTAMP('2015/04/01 00:00:00.000000','YYYY/MM/DD/ HH24:MI:SS.FF6'),1);
INSERT INTO A_MENU_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-170007,TO_TIMESTAMP('2015/04/01 00:00:00.000000','YYYY/MM/DD/ HH24:MI:SS.FF6'),'INSERT',2100170007,2100011701,'ホストグループ変数紐付(Ansible-LegacyRole)',NULL,NULL,NULL,1,0,1,2,7,'hostgroup_var_link_legacyrole','0',TO_TIMESTAMP('2015/04/01 00:00:00.000000','YYYY/MM/DD/ HH24:MI:SS.FF6'),1);

INSERT INTO A_ROLE_MENU_LINK_LIST (LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100170006,1,2100170006,1,'システム管理者','0',TO_TIMESTAMP('2015/04/01 00:00:00.000000','YYYY/MM/DD/ HH24:MI:SS.FF6'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-170006,TO_TIMESTAMP('2015/04/01 00:00:00.000000','YYYY/MM/DD/ HH24:MI:SS.FF6'),'INSERT',2100170006,1,2100170006,1,'システム管理者','0',TO_TIMESTAMP('2015/04/01 00:00:00.000000','YYYY/MM/DD/ HH24:MI:SS.FF6'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST (LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100170007,1,2100170007,1,'システム管理者','0',TO_TIMESTAMP('2015/04/01 00:00:00.000000','YYYY/MM/DD/ HH24:MI:SS.FF6'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-170007,TO_TIMESTAMP('2015/04/01 00:00:00.000000','YYYY/MM/DD/ HH24:MI:SS.FF6'),'INSERT',2100170007,1,2100170007,1,'システム管理者','0',TO_TIMESTAMP('2015/04/01 00:00:00.000000','YYYY/MM/DD/ HH24:MI:SS.FF6'),1);


COMMIT;

EXIT;
