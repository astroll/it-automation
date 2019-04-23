-- *****************************************************************************
-- *** ***** CreateParameterMenu Tables                                      ***
-- *****************************************************************************
CREATE TABLE F_MATERIAL_LINKAGE_OPENSTACK
(
ROW_ID                             INT                             , -- 識別シーケンス項番

MATERIAL_LINK_NAME                 VARCHAR (128)                   ,
FILE_ID                            INT                             ,
CLOSE_REVISION_ID                  INT                             ,
OPENST_TEMPLATE_CHK                INT                             , -- HEATテンプレート
OPENST_ENVIRONMENT_CHK             INT                             , -- 環境設定ファイル

NOTE                               VARCHAR (4000)                  , -- 備考
DISUSE_FLAG                        VARCHAR (1)                     , -- 廃止フラグ
LAST_UPDATE_TIMESTAMP              DATETIME(6)                     , -- 最終更新日時
LAST_UPDATE_USER                   INT                             , -- 最終更新ユーザ

PRIMARY KEY (ROW_ID)
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8;

CREATE TABLE F_MATERIAL_LINKAGE_OPENSTACK_JNL
(
JOURNAL_SEQ_NO                     INT                             , -- 履歴用シーケンス
JOURNAL_REG_DATETIME               DATETIME(6)                     , -- 履歴用変更日時
JOURNAL_ACTION_CLASS               VARCHAR (8)                     , -- 履歴用変更種別

ROW_ID                             INT                             , -- 識別シーケンス項番

MATERIAL_LINK_NAME                 VARCHAR (128)                    ,
FILE_ID                            INT                             ,
CLOSE_REVISION_ID                  INT                             ,
OPENST_TEMPLATE_CHK                INT                             , -- HEATテンプレート
OPENST_ENVIRONMENT_CHK             INT                             , -- 環境設定ファイル

NOTE                               VARCHAR (4000)                  , -- 備考
DISUSE_FLAG                        VARCHAR (1)                     , -- 廃止フラグ
LAST_UPDATE_TIMESTAMP              DATETIME(6)                     , -- 最終更新日時
LAST_UPDATE_USER                   INT                             , -- 最終更新ユーザ
PRIMARY KEY(JOURNAL_SEQ_NO)
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8;
INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('F_MATERIAL_LINKAGE_OPENSTACK_RIC',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('F_MATERIAL_LINKAGE_OPENSTACK_JSQ',1);


INSERT INTO A_MENU_LIST (MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100150007,2100011501,'File link list (OpenStack)',NULL,NULL,NULL,1,0,1,2,10,'linkage_openstack','0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-150007,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100150007,2100011501,'File link list (OpenStack)',NULL,NULL,NULL,1,0,1,2,10,'linkage_openstack','0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);

INSERT INTO A_ROLE_MENU_LINK_LIST (LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100150007,1,2100150007,1,'System Administrator','0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-150007,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100150007,1,2100150007,1,'System Administrator','0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);


COMMIT;
