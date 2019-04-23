-- -- //////////////////////////////////////////////////////////////////////
-- -- //
-- -- //  【処理概要】
-- -- //    ・インストーラー用のSQL
-- -- //
-- -- //////////////////////////////////////////////////////////////////////

CREATE TABLE B_ANSTWR_IF_INFO ( 
  ANSTWR_IF_INFO_ID               INT                               , 
  ANSTWR_STORAGE_PATH_ITA         VARCHAR (256)                     , 
  ANSTWR_STORAGE_PATH_ANSTWR      VARCHAR (256)                     , 
  SYMPHONY_STORAGE_PATH_ANSTWR    VARCHAR (256)                     , 
  ANSTWR_PROTOCOL                 VARCHAR (8)                       , 
  ANSTWR_HOSTNAME                 VARCHAR (128)                     , 
  ANSTWR_PORT                     INT                               , 
  ANSTWR_USER_ID                  VARCHAR (30)                      , 
  ANSTWR_PASSWORD                 VARCHAR (30)                      , 
  ANSTWR_AUTH_TOKEN               VARCHAR (256)                     , 
  ANSTWR_DEL_RUNTIME_DATA         INT                               , 
  ANSTWR_REFRESH_INTERVAL         INT                               , 
  ANSTWR_TAILLOG_LINES            INT                               , 
  DISP_SEQ                        INT                               , 
  NULL_DATA_HANDLING_FLG          INT                               , -- Null値の連携 1:有効　2:無効
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (ANSTWR_IF_INFO_ID) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_IF_INFO_JNL ( 
  JOURNAL_SEQ_NO                  INT                               , 
  JOURNAL_REG_DATETIME            DATETIME(6)                       , 
  JOURNAL_ACTION_CLASS            VARCHAR (8)                       , 
  ANSTWR_IF_INFO_ID               INT                               , 
  ANSTWR_STORAGE_PATH_ITA         VARCHAR (256)                     , 
  ANSTWR_STORAGE_PATH_ANSTWR      VARCHAR (256)                     , 
  SYMPHONY_STORAGE_PATH_ANSTWR    VARCHAR (256)                     , 
  ANSTWR_PROTOCOL                 VARCHAR (8)                       , 
  ANSTWR_HOSTNAME                 VARCHAR (128)                     , 
  ANSTWR_PORT                     INT                               , 
  ANSTWR_USER_ID                  VARCHAR (30)                      , 
  ANSTWR_PASSWORD                 VARCHAR (30)                      , 
  ANSTWR_AUTH_TOKEN               VARCHAR (256)                     , 
  ANSTWR_DEL_RUNTIME_DATA         INT                               , 
  ANSTWR_REFRESH_INTERVAL         INT                               , 
  ANSTWR_TAILLOG_LINES            INT                               , 
  DISP_SEQ                        INT                               , 
  NULL_DATA_HANDLING_FLG          INT                               , -- Null値の連携 1:有効　2:無効
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_GLOBAL_VARS ( 
  GLOBAL_VARS_ID                  INT                               , 
  VARS_NAME                       VARCHAR (128)                     , 
  VARS_ENTRY                      VARCHAR (1024)                    , 
  VARS_DESCRIPTION                VARCHAR (128)                     , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (GLOBAL_VARS_ID) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_GLOBAL_VARS_JNL ( 
  JOURNAL_SEQ_NO                  INT                               , 
  JOURNAL_REG_DATETIME            DATETIME(6)                       , 
  JOURNAL_ACTION_CLASS            VARCHAR (8)                       , 
  GLOBAL_VARS_ID                  INT                               , 
  VARS_NAME                       VARCHAR (128)                     , 
  VARS_ENTRY                      VARCHAR (1024)                    , 
  VARS_DESCRIPTION                VARCHAR (128)                     , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_ROLE_PACKAGE ( 
  ROLE_PACKAGE_ID                 INT                               , 
  ROLE_PACKAGE_NAME               VARCHAR (128)                     , 
  ROLE_PACKAGE_FILE               VARCHAR (256)                     , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (ROLE_PACKAGE_ID) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_ROLE_PACKAGE_JNL ( 
  JOURNAL_SEQ_NO                  INT                               , 
  JOURNAL_REG_DATETIME            DATETIME(6)                       , 
  JOURNAL_ACTION_CLASS            VARCHAR (8)                       , 
  ROLE_PACKAGE_ID                 INT                               , 
  ROLE_PACKAGE_NAME               VARCHAR (128)                     , 
  ROLE_PACKAGE_FILE               VARCHAR (256)                     , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_CONTENTS_FILE ( 
  CONTENTS_FILE_ID                INT                               , 
  CONTENTS_FILE_VARS_NAME         VARCHAR (128)                     , 
  CONTENTS_FILE                   VARCHAR (256)                     , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (CONTENTS_FILE_ID) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_CONTENTS_FILE_JNL ( 
  JOURNAL_SEQ_NO                  INT                               , 
  JOURNAL_REG_DATETIME            DATETIME(6)                       , 
  JOURNAL_ACTION_CLASS            VARCHAR (8)                       , 
  CONTENTS_FILE_ID                INT                               , 
  CONTENTS_FILE_VARS_NAME         VARCHAR (128)                     , 
  CONTENTS_FILE                   VARCHAR (256)                     , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_PTN_ROLE_LINK ( 
  PTN_ROLE_LINK_ID                INT                               , 
  PATTERN_ID                      INT                               , 
  ROLE_PACKAGE_ID                 INT                               , 
  ROLE_ID                         INT                               , 
  INCLUDE_SEQ                     INT                               , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (PTN_ROLE_LINK_ID) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_PTN_ROLE_LINK_JNL ( 
  JOURNAL_SEQ_NO                  INT                               , 
  JOURNAL_REG_DATETIME            DATETIME(6)                       , 
  JOURNAL_ACTION_CLASS            VARCHAR (8)                       , 
  PTN_ROLE_LINK_ID                INT                               , 
  PATTERN_ID                      INT                               , 
  ROLE_PACKAGE_ID                 INT                               , 
  ROLE_ID                         INT                               , 
  INCLUDE_SEQ                     INT                               , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_MAX_MEMBER_COL ( 
  MAX_MEMBER_COL_ID               INT                               , 
  VARS_ID                         INT                               , 
  NESTED_MEM_VARS_ID              INT                               , 
  MAX_COL_SEQ                     INT                               , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (MAX_MEMBER_COL_ID) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_MAX_MEMBER_COL_JNL ( 
  JOURNAL_SEQ_NO                  INT                               , 
  JOURNAL_REG_DATETIME            DATETIME(6)                       , 
  JOURNAL_ACTION_CLASS            VARCHAR (8)                       , 
  MAX_MEMBER_COL_ID               INT                               , 
  VARS_ID                         INT                               , 
  NESTED_MEM_VARS_ID              INT                               , 
  MAX_COL_SEQ                     INT                               , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_PRMCOL_VARS_LINK ( 
  PRMCOL_VARS_LINK_ID             INT                               , 
  MENU_ID                         INT                               , 
  MENU_COLUMN_ID                  INT                               , 
  PRMCOL_LINK_TYPE_ID             INT                               , 
  PATTERN_ID                      INT                               , 
  KEY_VARS_LINK_ID                INT                               , 
  KEY_NESTEDMEM_COL_CMB_ID        INT                               , 
  KEY_ASSIGN_SEQ                  INT                               , 
  VALUE_VARS_LINK_ID              INT                               , 
  VALUE_NESTEDMEM_COL_CMB_ID      INT                               , 
  VALUE_ASSIGN_SEQ                INT                               , 
  NULL_DATA_HANDLING_FLG          INT                               , -- Null値の連携
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (PRMCOL_VARS_LINK_ID) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_PRMCOL_VARS_LINK_JNL ( 
  JOURNAL_SEQ_NO                  INT                               , 
  JOURNAL_REG_DATETIME            DATETIME(6)                       , 
  JOURNAL_ACTION_CLASS            VARCHAR (8)                       , 
  PRMCOL_VARS_LINK_ID             INT                               , 
  MENU_ID                         INT                               , 
  MENU_COLUMN_ID                  INT                               , 
  PRMCOL_LINK_TYPE_ID             INT                               , 
  PATTERN_ID                      INT                               , 
  KEY_VARS_LINK_ID                INT                               , 
  KEY_NESTEDMEM_COL_CMB_ID        INT                               , 
  KEY_ASSIGN_SEQ                  INT                               , 
  VALUE_VARS_LINK_ID              INT                               , 
  VALUE_NESTEDMEM_COL_CMB_ID      INT                               , 
  VALUE_ASSIGN_SEQ                INT                               , 
  NULL_DATA_HANDLING_FLG          INT                               , -- Null値の連携
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_VARS_ASSIGN ( 
  VARS_ASSIGN_ID                  INT                               , 
  OPERATION_NO_UAPK               INT                               , 
  PATTERN_ID                      INT                               , 
  SYSTEM_ID                       INT                               , 
  VARS_LINK_ID                    INT                               , 
  NESTEDMEM_COL_CMB_ID            INT                               , 
  VARS_ENTRY                      VARCHAR (1024)                    , 
  ASSIGN_SEQ                      INT                               , 
  VARS_VALUE                      VARCHAR (1024)                    , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (VARS_ASSIGN_ID) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_VARS_ASSIGN_JNL ( 
  JOURNAL_SEQ_NO                  INT                               , 
  JOURNAL_REG_DATETIME            DATETIME(6)                       , 
  JOURNAL_ACTION_CLASS            VARCHAR (8)                       , 
  VARS_ASSIGN_ID                  INT                               , 
  OPERATION_NO_UAPK               INT                               , 
  PATTERN_ID                      INT                               , 
  SYSTEM_ID                       INT                               , 
  VARS_LINK_ID                    INT                               , 
  NESTEDMEM_COL_CMB_ID            INT                               , 
  VARS_ENTRY                      VARCHAR (1024)                    , 
  ASSIGN_SEQ                      INT                               , 
  VARS_VALUE                      VARCHAR (1024)                    , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_PHO_LINK ( 
  PHO_LINK_ID                     INT                               , 
  OPERATION_NO_UAPK               INT                               , 
  PATTERN_ID                      INT                               , 
  SYSTEM_ID                       INT                               , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (PHO_LINK_ID) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_PHO_LINK_JNL ( 
  JOURNAL_SEQ_NO                  INT                               , 
  JOURNAL_REG_DATETIME            DATETIME(6)                       , 
  JOURNAL_ACTION_CLASS            VARCHAR (8)                       , 
  PHO_LINK_ID                     INT                               , 
  OPERATION_NO_UAPK               INT                               , 
  PATTERN_ID                      INT                               , 
  SYSTEM_ID                       INT                               , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_ROLE ( 
  ROLE_ID                         INT                               , 
  ROLE_PACKAGE_ID                 INT                               , 
  ROLE_NAME                       VARCHAR (128)                     , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (ROLE_ID) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_ROLE_JNL ( 
  JOURNAL_SEQ_NO                  INT                               , 
  JOURNAL_REG_DATETIME            DATETIME(6)                       , 
  JOURNAL_ACTION_CLASS            VARCHAR (8)                       , 
  ROLE_ID                         INT                               , 
  ROLE_PACKAGE_ID                 INT                               , 
  ROLE_NAME                       VARCHAR (128)                     , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_DEFAULT_VARSVAL ( 
  VARSVAL_ID                      INT                               , 
  ROLE_PACKAGE_ID                 INT                               , 
  ROLE_ID                         INT                               , 
  END_VAR_OF_VARS_ATTR_ID         INT                               , 
  VARS_ID                         INT                               , 
  NESTEDMEM_COL_CMB_ID            INT                               , 
  ASSIGN_SEQ                      INT                               , 
  VARS_VALUE                      VARCHAR (1024)                    , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (VARSVAL_ID) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_DEFAULT_VARSVAL_JNL ( 
  JOURNAL_SEQ_NO                  INT                               , 
  JOURNAL_REG_DATETIME            DATETIME(6)                       , 
  JOURNAL_ACTION_CLASS            VARCHAR (8)                       , 
  VARSVAL_ID                      INT                               , 
  ROLE_PACKAGE_ID                 INT                               , 
  ROLE_ID                         INT                               , 
  END_VAR_OF_VARS_ATTR_ID         INT                               , 
  VARS_ID                         INT                               , 
  NESTEDMEM_COL_CMB_ID            INT                               , 
  ASSIGN_SEQ                      INT                               , 
  VARS_VALUE                      VARCHAR (1024)                    , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_VARS ( 
  VARS_ID                         INT                               , 
  VARS_NAME                       VARCHAR (128)                     , 
  VARS_ATTR_ID                    INT                               , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (VARS_ID) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_VARS_JNL ( 
  JOURNAL_SEQ_NO                  INT                               , 
  JOURNAL_REG_DATETIME            DATETIME(6)                       , 
  JOURNAL_ACTION_CLASS            VARCHAR (8)                       , 
  VARS_ID                         INT                               , 
  VARS_NAME                       VARCHAR (128)                     , 
  VARS_ATTR_ID                    INT                               , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_PTN_VARS_LINK ( 
  VARS_LINK_ID                    INT                               , 
  PATTERN_ID                      INT                               , 
  VARS_ID                         INT                               , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (VARS_LINK_ID) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_PTN_VARS_LINK_JNL ( 
  JOURNAL_SEQ_NO                  INT                               , 
  JOURNAL_REG_DATETIME            DATETIME(6)                       , 
  JOURNAL_ACTION_CLASS            VARCHAR (8)                       , 
  VARS_LINK_ID                    INT                               , 
  PATTERN_ID                      INT                               , 
  VARS_ID                         INT                               , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_ROLE_VARS ( 
  ROLE_VARS_ID                    INT                               , 
  ROLE_PACKAGE_ID                 INT                               , 
  ROLE_ID                         INT                               , 
  VARS_ID                         INT                               , 
  VARS_ATTR_ID                    INT                               , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (ROLE_VARS_ID) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_ROLE_VARS_JNL ( 
  JOURNAL_SEQ_NO                  INT                               , 
  JOURNAL_REG_DATETIME            DATETIME(6)                       , 
  JOURNAL_ACTION_CLASS            VARCHAR (8)                       , 
  ROLE_VARS_ID                    INT                               , 
  ROLE_PACKAGE_ID                 INT                               , 
  ROLE_ID                         INT                               , 
  VARS_ID                         INT                               , 
  VARS_ATTR_ID                    INT                               , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_TRANSLATE_VARS ( 
  TRANSLATE_VARS_ID               INT                               , 
  ROLE_PACKAGE_ID                 INT                               , 
  ROLE_ID                         INT                               , 
  ITA_VARS_NAME                   VARCHAR (128)                     , 
  ANY_VARS_NAME                   VARCHAR (128)                     , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (TRANSLATE_VARS_ID) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_TRANSLATE_VARS_JNL ( 
  JOURNAL_SEQ_NO                  INT                               , 
  JOURNAL_REG_DATETIME            DATETIME(6)                       , 
  JOURNAL_ACTION_CLASS            VARCHAR (8)                       , 
  TRANSLATE_VARS_ID               INT                               , 
  ROLE_PACKAGE_ID                 INT                               , 
  ROLE_ID                         INT                               , 
  ITA_VARS_NAME                   VARCHAR (128)                     , 
  ANY_VARS_NAME                   VARCHAR (128)                     , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_NESTED_MEM_VARS ( 
  NESTED_MEM_VARS_ID              INT                               , 
  VARS_ID                         INT                               , 
  PARENT_KEY_ID                   INT                               , 
  SELF_KEY_ID                     INT                               , 
  MEMBER_NAME                     VARCHAR (128)                     , 
  NESTED_LEVEL                    INT                               , 
  ASSIGN_SEQ_NEED                 INT                               , 
  COL_SEQ_NEED                    INT                               , 
  MEMBER_DISP                     INT                               , 
  MAX_COL_SEQ                     INT                               , 
  NESTED_MEMBER_PATH              VARCHAR (1024)                    , 
  NESTED_MEMBER_PATH_ALIAS        VARCHAR (1024)                    , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (NESTED_MEM_VARS_ID) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_NESTED_MEM_VARS_JNL ( 
  JOURNAL_SEQ_NO                  INT                               , 
  JOURNAL_REG_DATETIME            DATETIME(6)                       , 
  JOURNAL_ACTION_CLASS            VARCHAR (8)                       , 
  NESTED_MEM_VARS_ID              INT                               , 
  VARS_ID                         INT                               , 
  PARENT_KEY_ID                   INT                               , 
  SELF_KEY_ID                     INT                               , 
  MEMBER_NAME                     VARCHAR (128)                     , 
  NESTED_LEVEL                    INT                               , 
  ASSIGN_SEQ_NEED                 INT                               , 
  COL_SEQ_NEED                    INT                               , 
  MEMBER_DISP                     INT                               , 
  MAX_COL_SEQ                     INT                               , 
  NESTED_MEMBER_PATH              VARCHAR (1024)                    , 
  NESTED_MEMBER_PATH_ALIAS        VARCHAR (1024)                    , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_NESTEDMEM_COL_CMB ( 
  NESTEDMEM_COL_CMB_ID            INT                               , 
  VARS_ID                         INT                               , 
  NESTED_MEM_VARS_ID              INT                               , 
  COL_COMBINATION_MEMBER_ALIAS    VARCHAR (4000)                    , 
  COL_SEQ_VALUE                   VARCHAR (4000)                    , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (NESTEDMEM_COL_CMB_ID) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_NESTEDMEM_COL_CMB_JNL ( 
  JOURNAL_SEQ_NO                  INT                               , 
  JOURNAL_REG_DATETIME            DATETIME(6)                       , 
  JOURNAL_ACTION_CLASS            VARCHAR (8)                       , 
  NESTEDMEM_COL_CMB_ID            INT                               , 
  VARS_ID                         INT                               , 
  NESTED_MEM_VARS_ID              INT                               , 
  COL_COMBINATION_MEMBER_ALIAS    VARCHAR (4000)                    , 
  COL_SEQ_VALUE                   VARCHAR (4000)                    , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_CREDENTIAL_TYPE ( 
  CREDENTIAL_TYPE_ITA_MANAGED_ID  INT                               , 
  CREDENTIAL_TYPE_ID              INT                               , 
  CREDENTIAL_TYPE_NAME            VARCHAR (512)                     , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (CREDENTIAL_TYPE_ITA_MANAGED_ID) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_CREDENTIAL_TYPE_JNL ( 
  JOURNAL_SEQ_NO                  INT                               , 
  JOURNAL_REG_DATETIME            DATETIME(6)                       , 
  JOURNAL_ACTION_CLASS            VARCHAR (8)                       , 
  CREDENTIAL_TYPE_ITA_MANAGED_ID  INT                               , 
  CREDENTIAL_TYPE_ID              INT                               , 
  CREDENTIAL_TYPE_NAME            VARCHAR (512)                     , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_ORGANIZATION ( 
  ORGANIZATION_ITA_MANAGED_ID     INT                               , 
  ORGANIZATION_ID                 INT                               , 
  ORGANIZATION_NAME               VARCHAR (512)                     , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (ORGANIZATION_ITA_MANAGED_ID) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_ORGANIZATION_JNL ( 
  JOURNAL_SEQ_NO                  INT                               , 
  JOURNAL_REG_DATETIME            DATETIME(6)                       , 
  JOURNAL_ACTION_CLASS            VARCHAR (8)                       , 
  ORGANIZATION_ITA_MANAGED_ID     INT                               , 
  ORGANIZATION_ID                 INT                               , 
  ORGANIZATION_NAME               VARCHAR (512)                     , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_STATUS ( 
  STATUS_ID                       INT                               , 
  STATUS_NAME                     VARCHAR (32)                      , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (STATUS_ID) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_STATUS_JNL ( 
  JOURNAL_SEQ_NO                  INT                               , 
  JOURNAL_REG_DATETIME            DATETIME(6)                       , 
  JOURNAL_ACTION_CLASS            VARCHAR (8)                       , 
  STATUS_ID                       INT                               , 
  STATUS_NAME                     VARCHAR (32)                      , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_RUN_MODE ( 
  RUN_MODE_ID                     INT                               , 
  RUN_MODE_NAME                   VARCHAR (32)                      , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (RUN_MODE_ID) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_RUN_MODE_JNL ( 
  JOURNAL_SEQ_NO                  INT                               , 
  JOURNAL_REG_DATETIME            DATETIME(6)                       , 
  JOURNAL_ACTION_CLASS            VARCHAR (8)                       , 
  RUN_MODE_ID                     INT                               , 
  RUN_MODE_NAME                   VARCHAR (32)                      , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWER_GATHERFACTS_FLAG
(
FLAG_ID                           INT                               , -- 識別シーケンス
FLAG_NAME                         VARCHAR (32)                      , -- 表示名
DISP_SEQ                          INT                               , -- 表示順序
NOTE                              VARCHAR (4000)                    , -- 備考
DISUSE_FLAG                       VARCHAR (1)                       , -- 廃止フラグ
LAST_UPDATE_TIMESTAMP             DATETIME(6)                       , -- 最終更新日時
LAST_UPDATE_USER                  INT                               , -- 最終更新ユーザ
PRIMARY KEY (FLAG_ID)
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8;

CREATE TABLE B_ANSTWER_GATHERFACTS_FLAG_JNL
(
JOURNAL_SEQ_NO                    INT                               , -- 履歴用シーケンス
JOURNAL_REG_DATETIME              DATETIME(6)                       , -- 履歴用変更日時
JOURNAL_ACTION_CLASS              VARCHAR (8)                       , -- 履歴用変更種別
FLAG_ID                           INT                               , -- 識別シーケンス
FLAG_NAME                         VARCHAR (32)                      , -- 表示名
DISP_SEQ                          INT                               , -- 表示順序
NOTE                              VARCHAR (4000)                    , -- 備考
DISUSE_FLAG                       VARCHAR (1)                       , -- 廃止フラグ
LAST_UPDATE_TIMESTAMP             DATETIME(6)                       , -- 最終更新日時
LAST_UPDATE_USER                  INT                               , -- 最終更新ユーザ
PRIMARY KEY(JOURNAL_SEQ_NO)
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8;

CREATE TABLE B_ANSTWER_RUNDATA_DEL_FLAG
(
FLAG_ID                           INT                               , -- 識別シーケンス
FLAG_NAME                         VARCHAR (32)                      , -- 表示名
DISP_SEQ                          INT                               , -- 表示順序
NOTE                              VARCHAR (4000)                    , -- 備考
DISUSE_FLAG                       VARCHAR (1)                       , -- 廃止フラグ
LAST_UPDATE_TIMESTAMP             DATETIME(6)                       , -- 最終更新日時
LAST_UPDATE_USER                  INT                               , -- 最終更新ユーザ
PRIMARY KEY (FLAG_ID)
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8;

CREATE TABLE B_ANSTWER_RUNDATA_DEL_FLAG_JNL
(
JOURNAL_SEQ_NO                    INT                               , -- 履歴用シーケンス
JOURNAL_REG_DATETIME              DATETIME(6)                       , -- 履歴用変更日時
JOURNAL_ACTION_CLASS              VARCHAR (8)                       , -- 履歴用変更種別
FLAG_ID                           INT                               , -- 識別シーケンス
FLAG_NAME                         VARCHAR (32)                      , -- 表示名
DISP_SEQ                          INT                               , -- 表示順序
NOTE                              VARCHAR (4000)                    , -- 備考
DISUSE_FLAG                       VARCHAR (1)                       , -- 廃止フラグ
LAST_UPDATE_TIMESTAMP             DATETIME(6)                       , -- 最終更新日時
LAST_UPDATE_USER                  INT                               , -- 最終更新ユーザ
PRIMARY KEY(JOURNAL_SEQ_NO)
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8;

CREATE TABLE B_ANSTWR_VARS_ATTR ( 
  VARS_ATTR_ID                    INT                               , 
  VARS_ATTR_NAME                  VARCHAR (64)                      , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (VARS_ATTR_ID) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_VARS_ATTR_JNL ( 
  JOURNAL_SEQ_NO                  INT                               , 
  JOURNAL_REG_DATETIME            DATETIME(6)                       , 
  JOURNAL_ACTION_CLASS            VARCHAR (8)                       , 
  VARS_ATTR_ID                    INT                               , 
  VARS_ATTR_NAME                  VARCHAR (64)                      , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_INSTANCE_GROUP ( 
  INSTANCE_GROUP_ITA_MANAGED_ID   INT                               , 
  INSTANCE_GROUP_NAME             VARCHAR (512)                     , 
  INSTANCE_GROUP_ID               INT                               , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (INSTANCE_GROUP_ITA_MANAGED_ID) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE B_ANSTWR_INSTANCE_GROUP_JNL ( 
  JOURNAL_SEQ_NO                  INT                               , 
  JOURNAL_REG_DATETIME            DATETIME(6)                       , 
  JOURNAL_ACTION_CLASS            VARCHAR (8)                       , 
  INSTANCE_GROUP_ITA_MANAGED_ID   INT                               , 
  INSTANCE_GROUP_NAME             VARCHAR (512)                     , 
  INSTANCE_GROUP_ID               INT                               , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE C_ANSTWR_EXE_INS_MNG ( 
  EXECUTION_NO                    INT                               , 
  RUN_MODE_ID                     INT                               , 
  STATUS_ID                       INT                               , 
  EXECUTION_USER                  VARCHAR (80)                      , -- 実行ユーザ
  SYMPHONY_INSTANCE_NO            INT                               , 
  PATTERN_ID                      INT                               , 
  I_PATTERN_NAME                  VARCHAR (256)                     , 
  I_TIME_LIMIT                    INT                               , 
  I_ANS_HOST_DESIGNATE_TYPE_ID    INT                               , 
  I_ANS_PARALLEL_EXE              INT                               , 
  I_ANS_WINRM_ID                  INT                               , 
  I_ANS_GATHER_FACTS              INT                               , 
  OPERATION_NO_UAPK               INT                               , 
  I_OPERATION_NAME                VARCHAR (128)                     , 
  I_OPERATION_NO_IDBH             INT                               , 
  FILE_INPUT                      VARCHAR (1024)                    , 
  FILE_RESULT                     VARCHAR (1024)                    , 
  I_ANSTWR_DEL_RUNTIME_DATA       INT                               , 
  TIME_BOOK                       DATETIME(6)                       , 
  TIME_START                      DATETIME(6)                       , 
  TIME_END                        DATETIME(6)                       , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (EXECUTION_NO) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE TABLE C_ANSTWR_EXE_INS_MNG_JNL ( 
  JOURNAL_SEQ_NO                  INT                               , 
  JOURNAL_REG_DATETIME            DATETIME(6)                       , 
  JOURNAL_ACTION_CLASS            VARCHAR (8)                       , 
  EXECUTION_NO                    INT                               , 
  RUN_MODE_ID                     INT                               , 
  STATUS_ID                       INT                               , 
  EXECUTION_USER                  VARCHAR (80)                      , -- 実行ユーザ
  SYMPHONY_INSTANCE_NO            INT                               , 
  PATTERN_ID                      INT                               , 
  I_PATTERN_NAME                  VARCHAR (256)                     , 
  I_TIME_LIMIT                    INT                               , 
  I_ANS_HOST_DESIGNATE_TYPE_ID    INT                               , 
  I_ANS_PARALLEL_EXE              INT                               , 
  I_ANS_WINRM_ID                  INT                               , 
  I_ANS_GATHER_FACTS              INT                               , 
  OPERATION_NO_UAPK               INT                               , 
  I_OPERATION_NAME                VARCHAR (128)                     , 
  I_OPERATION_NO_IDBH             INT                               , 
  FILE_INPUT                      VARCHAR (1024)                    , 
  FILE_RESULT                     VARCHAR (1024)                    , 
  I_ANSTWR_DEL_RUNTIME_DATA       INT                               , 
  TIME_BOOK                       DATETIME(6)                       , 
  TIME_START                      DATETIME(6)                       , 
  TIME_END                        DATETIME(6)                       , 
  DISP_SEQ                        INT                               , 
  NOTE                            VARCHAR (4000)                    , 
  DISUSE_FLAG                     VARCHAR (1)                       , 
  LAST_UPDATE_TIMESTAMP           DATETIME(6)                       , 
  LAST_UPDATE_USER                INT                               , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)ENGINE = InnoDB, CHARSET = utf8, COLLATE = utf8_bin, ROW_FORMAT=COMPRESSED ,KEY_BLOCK_SIZE=8; 

CREATE VIEW E_ANSTWR_PATTERN AS
  select TAB_A.PATTERN_ID AS PATTERN_ID,
    TAB_A.PATTERN_NAME AS PATTERN_NAME,
    CONCAT(TAB_A.PATTERN_ID,':',TAB_A.PATTERN_NAME) AS PATTERN,
    TAB_A.ITA_EXT_STM_ID AS ITA_EXT_STM_ID,
    TAB_A.TIME_LIMIT AS TIME_LIMIT,
    TAB_A.ANS_HOST_DESIGNATE_TYPE_ID AS ANS_HOST_DESIGNATE_TYPE_ID,
    TAB_A.ANS_PARALLEL_EXE AS ANS_PARALLEL_EXE,
    TAB_A.ANS_WINRM_ID AS ANS_WINRM_ID,
    TAB_A.ANS_GATHER_FACTS AS ANS_GATHER_FACTS,
    (select count(0) from B_ANSTWR_PTN_VARS_LINK TBL_S where ((TBL_S.PATTERN_ID = TAB_A.PATTERN_ID) and (TBL_S.DISUSE_FLAG = '0'))) AS VARS_COUNT,
    TAB_A.DISP_SEQ AS DISP_SEQ,
    TAB_A.NOTE AS NOTE,
    TAB_A.DISUSE_FLAG AS DISUSE_FLAG,
    TAB_A.LAST_UPDATE_TIMESTAMP AS LAST_UPDATE_TIMESTAMP,
    TAB_A.LAST_UPDATE_USER AS LAST_UPDATE_USER
  from C_PATTERN_PER_ORCH TAB_A
  where (TAB_A.ITA_EXT_STM_ID = 12)
;

CREATE VIEW E_ANSTWR_PATTERN_JNL AS
  select TAB_A.PATTERN_ID AS PATTERN_ID,
    TAB_A.JOURNAL_SEQ_NO,
    TAB_A.JOURNAL_REG_DATETIME,
    TAB_A.JOURNAL_ACTION_CLASS,
    TAB_A.PATTERN_NAME AS PATTERN_NAME,
    CONCAT(TAB_A.PATTERN_ID,':',TAB_A.PATTERN_NAME) AS PATTERN,
    TAB_A.ITA_EXT_STM_ID AS ITA_EXT_STM_ID,
    TAB_A.TIME_LIMIT AS TIME_LIMIT,
    TAB_A.ANS_HOST_DESIGNATE_TYPE_ID AS ANS_HOST_DESIGNATE_TYPE_ID,
    TAB_A.ANS_PARALLEL_EXE AS ANS_PARALLEL_EXE,
    TAB_A.ANS_WINRM_ID AS ANS_WINRM_ID,
    TAB_A.ANS_GATHER_FACTS AS ANS_GATHER_FACTS,
    (select count(0) from B_ANSTWR_PTN_VARS_LINK TBL_S where ((TBL_S.PATTERN_ID = TAB_A.PATTERN_ID) and (TBL_S.DISUSE_FLAG = '0'))) AS VARS_COUNT,
    TAB_A.DISP_SEQ AS DISP_SEQ,
    TAB_A.NOTE AS NOTE,
    TAB_A.DISUSE_FLAG AS DISUSE_FLAG,
    TAB_A.LAST_UPDATE_TIMESTAMP AS LAST_UPDATE_TIMESTAMP,
    TAB_A.LAST_UPDATE_USER AS LAST_UPDATE_USER
  from C_PATTERN_PER_ORCH_JNL TAB_A
  where (TAB_A.ITA_EXT_STM_ID = 12)
;

CREATE VIEW D_ANSTWR_PRMCOL_VARS_LINK AS 
SELECT 
  TAB_A.PRMCOL_VARS_LINK_ID,
  TAB_B.MENU_GROUP_ID,
  TAB_C.MENU_GROUP_NAME,
  TAB_A.MENU_ID AS MENU_ID_CLONE,
  TAB_B.MENU_NAME,
  TAB_A.MENU_ID AS MENU_ID,
  TAB_A.MENU_COLUMN_ID AS REST_MENU_COLUMN_ID,
  TAB_A.MENU_COLUMN_ID,
  TAB_A.PRMCOL_LINK_TYPE_ID,
  TAB_A.PATTERN_ID,
  TAB_A.KEY_VARS_LINK_ID AS REST_KEY_VARS_LINK_ID,
  TAB_A.KEY_VARS_LINK_ID,
  TAB_A.KEY_NESTEDMEM_COL_CMB_ID AS REST_KEY_NESTEDMEM_COL_CMB_ID,
  TAB_A.KEY_NESTEDMEM_COL_CMB_ID,
  TAB_A.KEY_ASSIGN_SEQ,
  TAB_A.VALUE_VARS_LINK_ID AS REST_VAL_VARS_LINK_ID,
  TAB_A.VALUE_VARS_LINK_ID,
  TAB_A.VALUE_NESTEDMEM_COL_CMB_ID AS REST_VAL_NESTEDMEM_COL_CMB_ID,
  TAB_A.VALUE_NESTEDMEM_COL_CMB_ID,
  TAB_A.VALUE_ASSIGN_SEQ,
  TAB_A.NULL_DATA_HANDLING_FLG         , -- Null値の連携
  TAB_A.DISP_SEQ,
  TAB_A.NOTE,
  TAB_A.DISUSE_FLAG,
  TAB_A.LAST_UPDATE_TIMESTAMP,
  TAB_A.LAST_UPDATE_USER
FROM 
  (( B_ANSTWR_PRMCOL_VARS_LINK TAB_A
    LEFT JOIN A_MENU_LIST       TAB_B ON TAB_B.MENU_ID = TAB_A.MENU_ID )
      LEFT JOIN A_MENU_GROUP_LIST TAB_C ON TAB_C.MENU_GROUP_ID = TAB_B.MENU_GROUP_ID )
;

CREATE VIEW D_ANSTWR_PRMCOL_VARS_LINK_JNL AS 
SELECT 
  TAB_A.JOURNAL_SEQ_NO,
  TAB_A.JOURNAL_REG_DATETIME,
  TAB_A.JOURNAL_ACTION_CLASS,
  TAB_A.PRMCOL_VARS_LINK_ID,
  TAB_B.MENU_GROUP_ID,
  TAB_C.MENU_GROUP_NAME,
  TAB_A.MENU_ID AS MENU_ID_CLONE,
  TAB_B.MENU_NAME,
  TAB_A.MENU_ID AS MENU_ID,
  TAB_A.MENU_COLUMN_ID AS REST_MENU_COLUMN_ID,
  TAB_A.MENU_COLUMN_ID,
  TAB_A.PRMCOL_LINK_TYPE_ID,
  TAB_A.PATTERN_ID,
  TAB_A.KEY_VARS_LINK_ID AS REST_KEY_VARS_LINK_ID,
  TAB_A.KEY_VARS_LINK_ID,
  TAB_A.KEY_NESTEDMEM_COL_CMB_ID AS REST_KEY_NESTEDMEM_COL_CMB_ID,
  TAB_A.KEY_NESTEDMEM_COL_CMB_ID,
  TAB_A.KEY_ASSIGN_SEQ,
  TAB_A.VALUE_VARS_LINK_ID AS REST_VAL_VARS_LINK_ID,
  TAB_A.VALUE_VARS_LINK_ID,
  TAB_A.VALUE_NESTEDMEM_COL_CMB_ID AS REST_VAL_NESTEDMEM_COL_CMB_ID,
  TAB_A.VALUE_NESTEDMEM_COL_CMB_ID,
  TAB_A.VALUE_ASSIGN_SEQ,
  TAB_A.NULL_DATA_HANDLING_FLG         , -- Null値の連携
  TAB_A.DISP_SEQ,
  TAB_A.NOTE,
  TAB_A.DISUSE_FLAG,
  TAB_A.LAST_UPDATE_TIMESTAMP,
  TAB_A.LAST_UPDATE_USER
FROM 
  (( B_ANSTWR_PRMCOL_VARS_LINK_JNL TAB_A
    LEFT JOIN A_MENU_LIST       TAB_B ON TAB_B.MENU_ID = TAB_A.MENU_ID )
      LEFT JOIN A_MENU_GROUP_LIST TAB_C ON TAB_C.MENU_GROUP_ID = TAB_B.MENU_GROUP_ID )
;

CREATE VIEW D_ANSTWR_PHO_LINK AS 
SELECT 
  TAB_A.PHO_LINK_ID,
  TAB_A.OPERATION_NO_UAPK,
  TAB_A.PATTERN_ID,
  TAB_A.SYSTEM_ID,
  TAB_B.HOSTNAME,
  CONCAT(TAB_A.SYSTEM_ID,':',TAB_B.HOSTNAME) AS HOST_PULLDOWN,
  TAB_B.DISUSE_FLAG
FROM 
  B_ANSTWR_PHO_LINK TAB_A
  LEFT JOIN C_STM_LIST TAB_B ON TAB_B.SYSTEM_ID = TAB_A.SYSTEM_ID
;

CREATE VIEW D_ANSTWR_PHO_LINK_JNL AS 
SELECT 
  TAB_A.JOURNAL_SEQ_NO,
  TAB_A.JOURNAL_REG_DATETIME,
  TAB_A.JOURNAL_ACTION_CLASS,
  TAB_A.PHO_LINK_ID,
  TAB_A.OPERATION_NO_UAPK,
  TAB_A.PATTERN_ID,
  TAB_A.SYSTEM_ID,
  TAB_B.HOSTNAME,
  CONCAT(TAB_A.SYSTEM_ID,':',TAB_B.HOSTNAME) AS HOST_PULLDOWN,
  TAB_B.DISUSE_FLAG
FROM 
  B_ANSTWR_PHO_LINK_JNL TAB_A
  LEFT JOIN C_STM_LIST TAB_B ON TAB_B.SYSTEM_ID = TAB_A.SYSTEM_ID
;

CREATE VIEW D_ANSTWR_PTN_VARS_LINK AS 
SELECT 
  TAB_A.*,
  TAB_B.VARS_NAME,
  CONCAT(TAB_A.VARS_LINK_ID,':',TAB_B.VARS_NAME) AS VARS_PULLDOWN, 
  CONCAT(TAB_A.PATTERN_ID,':',TAB_C.PATTERN_NAME,':',TAB_A.VARS_LINK_ID,':',TAB_B.VARS_NAME) AS PTN_VAR_PULLDOWN
FROM 
  (( B_ANSTWR_PTN_VARS_LINK       TAB_A
    LEFT JOIN   B_ANSTWR_VARS     TAB_B ON TAB_B.VARS_ID = TAB_A.VARS_ID )
      LEFT JOIN E_ANSTWR_PATTERN  TAB_C ON TAB_C.PATTERN_ID = TAB_A.PATTERN_ID )
;

CREATE VIEW D_ANSTWR_PTN_VARS_LINK_JNL AS 
SELECT 
  TAB_A.*,
  TAB_B.VARS_NAME,
  CONCAT(TAB_A.VARS_LINK_ID,':',TAB_B.VARS_NAME) AS VARS_PULLDOWN, 
  CONCAT(TAB_A.PATTERN_ID,':',TAB_C.PATTERN_NAME,':',TAB_A.VARS_LINK_ID,':',TAB_B.VARS_NAME) AS PTN_VAR_PULLDOWN
FROM 
  (( B_ANSTWR_PTN_VARS_LINK_JNL   TAB_A
    LEFT JOIN   B_ANSTWR_VARS     TAB_B ON TAB_B.VARS_ID = TAB_A.VARS_ID )
      LEFT JOIN E_ANSTWR_PATTERN  TAB_C ON TAB_C.PATTERN_ID = TAB_A.PATTERN_ID )
;

CREATE VIEW D_ANSTWR_ROLE_VARS AS 
SELECT 
  TAB_A.*,
  TAB_B.VARS_NAME
FROM 
  ( B_ANSTWR_ROLE_VARS TAB_A
    LEFT JOIN B_ANSTWR_VARS TAB_B ON TAB_B.VARS_ID = TAB_A.VARS_ID )
;

CREATE VIEW D_ANSTWR_ROLE_VARS_JNL AS 
SELECT 
  TAB_A.*,
  TAB_B.VARS_NAME
FROM 
  ( B_ANSTWR_ROLE_VARS_JNL TAB_A
    LEFT JOIN B_ANSTWR_VARS TAB_B ON TAB_B.VARS_ID = TAB_A.VARS_ID )
;

CREATE VIEW D_ANSTWR_NESTEDMEM_COL_CMB AS 
SELECT 
  TAB_A.NESTEDMEM_COL_CMB_ID,
  TAB_A.COL_COMBINATION_MEMBER_ALIAS,
  TAB_A.COL_SEQ_VALUE,
  TAB_B.*,
  CONCAT(TAB_C.VARS_NAME,'.',TAB_A.NESTEDMEM_COL_CMB_ID,':',TAB_A.COL_COMBINATION_MEMBER_ALIAS) VAR_MEMBER_PULLDOWN
FROM 
  (( B_ANSTWR_NESTEDMEM_COL_CMB          TAB_A
    INNER JOIN B_ANSTWR_NESTED_MEM_VARS  TAB_B ON TAB_B.NESTED_MEM_VARS_ID = TAB_A.NESTED_MEM_VARS_ID )
      LEFT JOIN B_ANSTWR_VARS            TAB_C ON TAB_C.VARS_ID = TAB_A.VARS_ID )
WHERE
  TAB_A.DISUSE_FLAG = '0'
AND TAB_B.DISUSE_FLAG = '0'
AND TAB_C.DISUSE_FLAG = '0'
;

CREATE VIEW D_ANSTWR_NESTEDMEM_COL_CMB_JNL AS 
SELECT 
  TAB_A.NESTEDMEM_COL_CMB_ID,
  TAB_A.COL_COMBINATION_MEMBER_ALIAS,
  TAB_A.COL_SEQ_VALUE,
  TAB_B.*,
  CONCAT(TAB_C.VARS_NAME,'.',TAB_A.NESTEDMEM_COL_CMB_ID,':',TAB_A.COL_COMBINATION_MEMBER_ALIAS) VAR_MEMBER_PULLDOWN
FROM 
  (( B_ANSTWR_NESTEDMEM_COL_CMB_JNL       TAB_A
    INNER JOIN  B_ANSTWR_NESTED_MEM_VARS  TAB_B ON TAB_B.NESTED_MEM_VARS_ID = TAB_A.NESTED_MEM_VARS_ID )
      LEFT JOIN B_ANSTWR_VARS             TAB_C ON TAB_C.VARS_ID = TAB_A.VARS_ID )
WHERE
  TAB_A.DISUSE_FLAG = '0'
AND TAB_B.DISUSE_FLAG = '0'
AND TAB_C.DISUSE_FLAG = '0'
;

CREATE VIEW E_ANSTWR_EXE_INS_MNG AS
  SELECT
    TAB_A.EXECUTION_NO                   AS EXECUTION_NO,
    TAB_A.STATUS_ID                      AS STATUS_ID,
    TAB_C.STATUS_NAME                    AS STATUS_NAME,
    TAB_A.SYMPHONY_INSTANCE_NO           AS SYMPHONY_INSTANCE_NO, 
    TAB_A.PATTERN_ID                     AS PATTERN_ID,
    TAB_A.I_PATTERN_NAME                 AS I_PATTERN_NAME,
    TAB_A.EXECUTION_USER                 AS EXECUTION_USER,
    TAB_A.I_TIME_LIMIT                   AS I_TIME_LIMIT,
    TAB_A.I_ANS_HOST_DESIGNATE_TYPE_ID   AS I_ANS_HOST_DESIGNATE_TYPE_ID,
    TAB_E.HOST_DESIGNATE_TYPE_NAME       AS ANS_HOST_DESIGNATE_TYPE_NAME,
    TAB_A.I_ANS_PARALLEL_EXE             AS I_ANS_PARALLEL_EXE,
    TAB_A.I_ANS_WINRM_ID                 AS I_ANS_WINRM_ID,
    TAB_A.I_ANS_GATHER_FACTS             AS I_ANS_GATHER_FACTS,
    TAB_F.FLAG_NAME                      AS ANS_WINRM_FLAG_NAME,
    TAB_G.FLAG_NAME                      AS ANS_GATHER_FACTS_NAME,
    TAB_A.OPERATION_NO_UAPK              AS OPERATION_NO_UAPK,
    TAB_A.I_OPERATION_NAME               AS I_OPERATION_NAME,
    TAB_A.I_OPERATION_NO_IDBH            AS I_OPERATION_NO_IDBH,
    TAB_A.I_ANSTWR_DEL_RUNTIME_DATA      AS I_ANSTWR_DEL_RUNTIME_DATA,
    TAB_H.FLAG_NAME                      AS ANSTWR_DEL_RUNTIME_DATA_NAME,
    TAB_A.TIME_BOOK                      AS TIME_BOOK,
    TAB_A.TIME_START                     AS TIME_START,
    TAB_A.TIME_END                       AS TIME_END,
    TAB_A.FILE_INPUT                     AS FILE_INPUT,
    TAB_A.FILE_RESULT                    AS FILE_RESULT,
    TAB_A.RUN_MODE_ID                    AS RUN_MODE_ID,
    TAB_D.RUN_MODE_NAME                  AS RUN_MODE_NAME,
    TAB_A.DISP_SEQ                       AS DISP_SEQ,
    TAB_A.NOTE                           AS NOTE,
    TAB_A.DISUSE_FLAG                    AS DISUSE_FLAG,
    TAB_A.LAST_UPDATE_TIMESTAMP          AS LAST_UPDATE_TIMESTAMP,
    TAB_A.LAST_UPDATE_USER               AS LAST_UPDATE_USER
  FROM
    (((((((C_ANSTWR_EXE_INS_MNG           TAB_A
    LEFT JOIN E_ANSTWR_PATTERN            TAB_B ON TAB_B.PATTERN_ID = TAB_A.PATTERN_ID)
    LEFT JOIN B_ANSTWR_STATUS             TAB_C ON TAB_A.STATUS_ID = TAB_C.STATUS_ID)
    LEFT JOIN B_ANSTWR_RUN_MODE           TAB_D ON TAB_A.RUN_MODE_ID = TAB_D.RUN_MODE_ID)
    LEFT JOIN B_HOST_DESIGNATE_TYPE_LIST  TAB_E ON TAB_A.I_ANS_HOST_DESIGNATE_TYPE_ID = TAB_E.HOST_DESIGNATE_TYPE_ID)
    LEFT JOIN D_FLAG_LIST_01              TAB_F ON TAB_A.I_ANS_WINRM_ID = TAB_F.FLAG_ID)
    LEFT JOIN B_ANSTWER_GATHERFACTS_FLAG  TAB_G ON TAB_A.I_ANS_GATHER_FACTS = TAB_G.FLAG_ID)
    LEFT JOIN B_ANSTWER_RUNDATA_DEL_FLAG  TAB_H ON TAB_A.I_ANSTWR_DEL_RUNTIME_DATA = TAB_H.FLAG_ID)
;

CREATE VIEW E_ANSTWR_EXE_INS_MNG_JNL AS
  SELECT
    TAB_A.EXECUTION_NO                   AS EXECUTION_NO,
    TAB_A.STATUS_ID                      AS STATUS_ID,
    TAB_C.STATUS_NAME                    AS STATUS_NAME,
    TAB_A.SYMPHONY_INSTANCE_NO           AS SYMPHONY_INSTANCE_NO, 
    TAB_A.PATTERN_ID                     AS PATTERN_ID,
    TAB_A.I_PATTERN_NAME                 AS I_PATTERN_NAME,
    TAB_A.EXECUTION_USER                 AS EXECUTION_USER,
    TAB_A.I_TIME_LIMIT                   AS I_TIME_LIMIT,
    TAB_A.I_ANS_HOST_DESIGNATE_TYPE_ID   AS I_ANS_HOST_DESIGNATE_TYPE_ID,
    TAB_E.HOST_DESIGNATE_TYPE_NAME       AS ANS_HOST_DESIGNATE_TYPE_NAME,
    TAB_A.I_ANS_PARALLEL_EXE             AS I_ANS_PARALLEL_EXE,
    TAB_A.I_ANS_WINRM_ID                 AS I_ANS_WINRM_ID,
    TAB_A.I_ANS_GATHER_FACTS             AS I_ANS_GATHER_FACTS,
    TAB_F.FLAG_NAME                      AS ANS_WINRM_FLAG_NAME,
    TAB_G.FLAG_NAME                      AS ANS_GATHER_FACTS_NAME,
    TAB_A.OPERATION_NO_UAPK              AS OPERATION_NO_UAPK,
    TAB_A.I_OPERATION_NAME               AS I_OPERATION_NAME,
    TAB_A.I_OPERATION_NO_IDBH            AS I_OPERATION_NO_IDBH,
    TAB_A.I_ANSTWR_DEL_RUNTIME_DATA      AS I_ANSTWR_DEL_RUNTIME_DATA,
    TAB_H.FLAG_NAME                      AS ANSTWR_DEL_RUNTIME_DATA_NAME,
    TAB_A.TIME_BOOK                      AS TIME_BOOK,
    TAB_A.TIME_START                     AS TIME_START,
    TAB_A.TIME_END                       AS TIME_END,
    TAB_A.FILE_INPUT                     AS FILE_INPUT,
    TAB_A.FILE_RESULT                    AS FILE_RESULT,
    TAB_A.RUN_MODE_ID                    AS RUN_MODE_ID,
    TAB_D.RUN_MODE_NAME                  AS RUN_MODE_NAME,
    TAB_A.DISP_SEQ                       AS DISP_SEQ,
    TAB_A.NOTE                           AS NOTE,
    TAB_A.DISUSE_FLAG                    AS DISUSE_FLAG,
    TAB_A.LAST_UPDATE_TIMESTAMP          AS LAST_UPDATE_TIMESTAMP,
    TAB_A.LAST_UPDATE_USER               AS LAST_UPDATE_USER
  FROM
    (((((((C_ANSTWR_EXE_INS_MNG_JNL           TAB_A
    LEFT JOIN E_ANSTWR_PATTERN            TAB_B ON TAB_B.PATTERN_ID = TAB_A.PATTERN_ID)
    LEFT JOIN B_ANSTWR_STATUS             TAB_C ON TAB_A.STATUS_ID = TAB_C.STATUS_ID)
    LEFT JOIN B_ANSTWR_RUN_MODE           TAB_D ON TAB_A.RUN_MODE_ID = TAB_D.RUN_MODE_ID)
    LEFT JOIN B_HOST_DESIGNATE_TYPE_LIST  TAB_E ON TAB_A.I_ANS_HOST_DESIGNATE_TYPE_ID = TAB_E.HOST_DESIGNATE_TYPE_ID)
    LEFT JOIN D_FLAG_LIST_01              TAB_F ON TAB_A.I_ANS_WINRM_ID = TAB_F.FLAG_ID)
    LEFT JOIN B_ANSTWER_GATHERFACTS_FLAG  TAB_G ON TAB_A.I_ANS_GATHER_FACTS = TAB_G.FLAG_ID)
    LEFT JOIN B_ANSTWER_RUNDATA_DEL_FLAG  TAB_H ON TAB_A.I_ANSTWR_DEL_RUNTIME_DATA = TAB_H.FLAG_ID)
;

CREATE VIEW D_ANSTWR_VARS_ASSIGN AS 
SELECT 
  TAB_A.*,
  TAB_A.SYSTEM_ID AS REST_SYSTEM_ID,
  TAB_A.VARS_LINK_ID AS REST_VARS_LINK_ID,
  TAB_A.NESTEDMEM_COL_CMB_ID AS REST_NESTEDMEM_COL_CMB_ID
FROM 
  B_ANSTWR_VARS_ASSIGN TAB_A
;

CREATE VIEW D_ANSTWR_VARS_ASSIGN_JNL AS 
SELECT 
  TAB_A.*,
  TAB_A.SYSTEM_ID AS REST_SYSTEM_ID,
  TAB_A.VARS_LINK_ID AS REST_VARS_LINK_ID,
  TAB_A.NESTEDMEM_COL_CMB_ID AS REST_NESTEDMEM_COL_CMB_ID
FROM 
  B_ANSTWR_VARS_ASSIGN_JNL TAB_A
;

CREATE VIEW D_ANSTWR_PKG_ROLE_LIST AS 
SELECT 
  TAB_A.*,
  TAB_B.ROLE_PACKAGE_NAME AS ROLE_PACKAGE_NAME,
  TAB_B.ROLE_PACKAGE_FILE AS ROLE_PACKAGE_FILE,
  CONCAT(TAB_B.ROLE_PACKAGE_ID,':',TAB_B.ROLE_PACKAGE_NAME,':',TAB_A.ROLE_ID,':',TAB_A.ROLE_NAME) AS ROLE_PACKAGE_PULLDOWN
FROM 
  (B_ANSTWR_ROLE TAB_A
  LEFT JOIN B_ANSTWR_ROLE_PACKAGE TAB_B ON TAB_A.ROLE_PACKAGE_ID = TAB_B.ROLE_PACKAGE_ID)
WHERE
  TAB_A.DISUSE_FLAG = '0' 
AND TAB_B.DISUSE_FLAG = '0' 
;

CREATE VIEW D_ANSTWR_PKG_ROLE_LIST_JNL AS 
SELECT 
  TAB_A.*,
  TAB_B.ROLE_PACKAGE_NAME AS ROLE_PACKAGE_NAME,
  TAB_B.ROLE_PACKAGE_FILE AS ROLE_PACKAGE_FILE,
  CONCAT(TAB_B.ROLE_PACKAGE_ID,':',TAB_B.ROLE_PACKAGE_NAME,':',TAB_A.ROLE_ID,':',TAB_A.ROLE_NAME) AS ROLE_PACKAGE_PULLDOWN
FROM 
  (B_ANSTWR_ROLE_JNL TAB_A
  LEFT JOIN B_ANSTWR_ROLE_PACKAGE TAB_B ON TAB_A.ROLE_PACKAGE_ID = TAB_B.ROLE_PACKAGE_ID)
WHERE
  TAB_A.DISUSE_FLAG = '0' 
AND TAB_B.DISUSE_FLAG = '0' 
;

CREATE VIEW D_ANSTWR_PTN_ROLE_LINK AS 
SELECT 
  TAB_A.*, 
  TAB_A.ROLE_ID AS REST_ROLE_ID 
FROM 
  B_ANSTWR_PTN_ROLE_LINK TAB_A 
;

CREATE VIEW D_ANSTWR_PTN_ROLE_LINK_JNL AS 
SELECT 
  TAB_A.*, 
  TAB_A.ROLE_ID AS REST_ROLE_ID 
FROM 
  B_ANSTWR_PTN_ROLE_LINK_JNL TAB_A 
;

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_IF_INFO_RIC',2);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_IF_INFO_JSQ',2);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_GLOBAL_VARS_RIC',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_GLOBAL_VARS_JSQ',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_ROLE_PACKAGE_RIC',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_ROLE_PACKAGE_JSQ',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_CONTENTS_FILE_RIC',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_CONTENTS_FILE_JSQ',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_PTN_ROLE_LINK_RIC',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_PTN_ROLE_LINK_JSQ',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_MAX_MEMBER_COL_RIC',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_MAX_MEMBER_COL_JSQ',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_PRMCOL_VARS_LINK_RIC',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_PRMCOL_VARS_LINK_JSQ',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_VARS_ASSIGN_RIC',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_VARS_ASSIGN_JSQ',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_PHO_LINK_RIC',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_PHO_LINK_JSQ',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_ROLE_RIC',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_ROLE_JSQ',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_DEFAULT_VARSVAL_RIC',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_DEFAULT_VARSVAL_JSQ',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_VARS_RIC',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_VARS_JSQ',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_PTN_VARS_LINK_RIC',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_PTN_VARS_LINK_JSQ',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_ROLE_VARS_RIC',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_ROLE_VARS_JSQ',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_TRANSLATE_VARS_RIC',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_TRANSLATE_VARS_JSQ',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_NESTED_MEM_VARS_RIC',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_NESTED_MEM_VARS_JSQ',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_NESTEDMEM_COL_CMB_RIC',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_NESTEDMEM_COL_CMB_JSQ',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_CREDENTIAL_TYPE_RIC',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_CREDENTIAL_TYPE_JSQ',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_ORGANIZATION_RIC',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_ORGANIZATION_JSQ',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_STATUS_RIC',11);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_STATUS_JSQ',11);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_RUN_MODE_RIC',3);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_RUN_MODE_JSQ',3);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_VARS_ATTR_RIC',4);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_VARS_ATTR_JSQ',4);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('C_ANSTWR_EXE_INS_MNG_RIC',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('C_ANSTWR_EXE_INS_MNG_JSQ',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_INSTANCE_GROUP_RIC',1);

INSERT INTO A_SEQUENCE (NAME,VALUE) VALUES('B_ANSTWR_INSTANCE_GROUP_JSQ',1);


INSERT INTO A_MENU_GROUP_LIST (MENU_GROUP_ID,MENU_GROUP_NAME,MENU_GROUP_ICON,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140001,'AnsibleTower/AWX','anstwr.png',180,'AnsibleTower/AWX','0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_GROUP_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,MENU_GROUP_ID,MENU_GROUP_NAME,MENU_GROUP_ICON,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140001,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140001,'AnsibleTower/AWX','anstwr.png',180,'AnsibleTower/AWX','0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);

INSERT INTO A_MENU_LIST (MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140001,2100140001,'インターフェース情報',NULL,NULL,NULL,1,0,1,1,20,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140001,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140001,2100140001,'インターフェース情報',NULL,NULL,NULL,1,0,1,1,20,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST (MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140002,2100140001,'グローバル変数管理',NULL,NULL,NULL,1,0,1,2,30,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140002,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140002,2100140001,'グローバル変数管理',NULL,NULL,NULL,1,0,1,2,30,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST (MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140003,2100140001,'ロールパッケージ管理',NULL,NULL,NULL,1,0,1,2,40,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140003,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140003,2100140001,'ロールパッケージ管理',NULL,NULL,NULL,1,0,1,2,40,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST (MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140004,2100140001,'ファイル管理',NULL,NULL,NULL,1,0,1,2,50,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140004,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140004,2100140001,'ファイル管理',NULL,NULL,NULL,1,0,1,2,50,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST (MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140005,2100140001,'Movement一覧',NULL,NULL,NULL,1,0,1,1,60,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140005,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140005,2100140001,'Movement一覧',NULL,NULL,NULL,1,0,1,1,60,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST (MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140006,2100140001,'Movement詳細',NULL,NULL,NULL,1,0,1,1,70,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140006,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140006,2100140001,'Movement詳細',NULL,NULL,NULL,1,0,1,1,70,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST (MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140007,2100140001,'多段変数最大繰返数管理',NULL,NULL,NULL,1,0,1,2,80,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140007,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140007,2100140001,'多段変数最大繰返数管理',NULL,NULL,NULL,1,0,1,2,80,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST (MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140009,2100140001,'代入値自動登録設定',NULL,NULL,NULL,1,0,1,2,100,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140009,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140009,2100140001,'代入値自動登録設定',NULL,NULL,NULL,1,0,1,2,100,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST (MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140010,2100140001,'作業対象ホスト管理',NULL,NULL,NULL,1,0,1,2,110,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140010,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140010,2100140001,'作業対象ホスト管理',NULL,NULL,NULL,1,0,1,2,110,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST (MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140011,2100140001,'代入値管理',NULL,NULL,NULL,1,0,1,2,120,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140011,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140011,2100140001,'代入値管理',NULL,NULL,NULL,1,0,1,2,120,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST (MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140012,2100140001,'作業実行',NULL,NULL,NULL,1,0,1,1,130,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140012,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140012,2100140001,'作業実行',NULL,NULL,NULL,1,0,1,1,130,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST (MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140013,2100140001,'作業状態確認',NULL,NULL,NULL,1,0,2,2,140,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140013,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140013,2100140001,'作業状態確認',NULL,NULL,NULL,1,0,2,2,140,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST (MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140014,2100140001,'作業管理',NULL,NULL,NULL,1,0,1,2,150,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140014,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140014,2100140001,'作業管理',NULL,NULL,NULL,1,0,1,2,150,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST (MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140015,2100140001,'ロール管理',NULL,NULL,NULL,1,0,1,2,160,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140015,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140015,2100140001,'ロール管理',NULL,NULL,NULL,1,0,1,2,160,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST (MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140016,2100140001,'変数具体値管理',NULL,NULL,NULL,1,0,1,2,170,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140016,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140016,2100140001,'変数具体値管理',NULL,NULL,NULL,1,0,1,2,170,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST (MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140017,2100140001,'変数管理',NULL,NULL,NULL,1,0,1,2,180,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140017,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140017,2100140001,'変数管理',NULL,NULL,NULL,1,0,1,2,180,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST (MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140018,2100140001,'Movement変数紐付管理',NULL,NULL,NULL,1,0,1,2,190,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140018,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140018,2100140001,'Movement変数紐付管理',NULL,NULL,NULL,1,0,1,2,190,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST (MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140019,2100140001,'ロール変数管理',NULL,NULL,NULL,1,0,1,2,200,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140019,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140019,2100140001,'ロール変数管理',NULL,NULL,NULL,1,0,1,2,200,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST (MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140020,2100140001,'読替変数管理',NULL,NULL,NULL,1,0,1,2,210,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140020,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140020,2100140001,'読替変数管理',NULL,NULL,NULL,1,0,1,2,210,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST (MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140021,2100140001,'多段変数メンバ管理',NULL,NULL,NULL,1,0,1,2,220,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140021,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140021,2100140001,'多段変数メンバ管理',NULL,NULL,NULL,1,0,1,2,220,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST (MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140022,2100140001,'多段変数配列組合せ管理',NULL,NULL,NULL,1,0,1,2,230,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140022,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140022,2100140001,'多段変数配列組合せ管理',NULL,NULL,NULL,1,0,1,2,230,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST (MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140025,2100140001,'認証情報タイプリスト',NULL,NULL,NULL,1,0,1,1,260,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140025,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140025,2100140001,'認証情報タイプリスト',NULL,NULL,NULL,1,0,1,1,260,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST (MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140026,2100140001,'組織リスト',NULL,NULL,NULL,1,0,1,1,270,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140026,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140026,2100140001,'組織リスト',NULL,NULL,NULL,1,0,1,1,270,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST (MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140027,2100140001,'インスタンスグループリスト',NULL,NULL,NULL,1,0,1,1,280,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_MENU_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,MENU_ID,MENU_GROUP_ID,MENU_NAME,WEB_PRINT_LIMIT,WEB_PRINT_CONFIRM,XLS_PRINT_LIMIT,LOGIN_NECESSITY,SERVICE_STATUS,AUTOFILTER_FLG,INITIAL_FILTER_FLG,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140027,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140027,2100140001,'インスタンスグループリスト',NULL,NULL,NULL,1,0,1,1,280,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);

INSERT INTO A_ACCOUNT_LIST (USER_ID,USERNAME,PASSWORD,USERNAME_JP,MAIL_ADDRESS,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-121001,'a10a','5ebbc37e034d6874a2af59eb04beaa52','AnsibleTower/AWX作業実行プロシージャ','sample@xxx.bbb.ccc',NULL,'H',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ACCOUNT_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,USER_ID,USERNAME,PASSWORD,USERNAME_JP,MAIL_ADDRESS,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-121001,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',-121001,'a10a','5ebbc37e034d6874a2af59eb04beaa52','AnsibleTower/AWX作業実行プロシージャ','sample@xxx.bbb.ccc',NULL,'H',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ACCOUNT_LIST (USER_ID,USERNAME,PASSWORD,USERNAME_JP,MAIL_ADDRESS,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-121002,'a10b','5ebbc37e034d6874a2af59eb04beaa52','AnsibleTower/AWX状態確認プロシージャ','sample@xxx.bbb.ccc',NULL,'H',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ACCOUNT_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,USER_ID,USERNAME,PASSWORD,USERNAME_JP,MAIL_ADDRESS,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-121002,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',-121002,'a10b','5ebbc37e034d6874a2af59eb04beaa52','AnsibleTower/AWX状態確認プロシージャ','sample@xxx.bbb.ccc',NULL,'H',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ACCOUNT_LIST (USER_ID,USERNAME,PASSWORD,USERNAME_JP,MAIL_ADDRESS,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-121003,'a10c','5ebbc37e034d6874a2af59eb04beaa52','AnsibleTower/AWX変数解析プロシージャ','sample@xxx.bbb.ccc',NULL,'H',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ACCOUNT_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,USER_ID,USERNAME,PASSWORD,USERNAME_JP,MAIL_ADDRESS,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-121003,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',-121003,'a10c','5ebbc37e034d6874a2af59eb04beaa52','AnsibleTower/AWX変数解析プロシージャ','sample@xxx.bbb.ccc',NULL,'H',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ACCOUNT_LIST (USER_ID,USERNAME,PASSWORD,USERNAME_JP,MAIL_ADDRESS,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-121004,'a10d','5ebbc37e034d6874a2af59eb04beaa52','AnsibleTower/AWX代入値自動登録設定プロシージャ','sample@xxx.bbb.ccc',NULL,'H',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ACCOUNT_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,USER_ID,USERNAME,PASSWORD,USERNAME_JP,MAIL_ADDRESS,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-121004,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',-121004,'a10d','5ebbc37e034d6874a2af59eb04beaa52','AnsibleTower/AWX代入値自動登録設定プロシージャ','sample@xxx.bbb.ccc',NULL,'H',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ACCOUNT_LIST (USER_ID,USERNAME,PASSWORD,USERNAME_JP,MAIL_ADDRESS,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-121006,'a10f','5ebbc37e034d6874a2af59eb04beaa52','AnsibleTower/AWXサーバデータ同期プロシージャ','sample@xxx.bbb.ccc',NULL,'H',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ACCOUNT_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,USER_ID,USERNAME,PASSWORD,USERNAME_JP,MAIL_ADDRESS,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-121006,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',-121006,'a10f','5ebbc37e034d6874a2af59eb04beaa52','AnsibleTower/AWXサーバデータ同期プロシージャ','sample@xxx.bbb.ccc',NULL,'H',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);

INSERT INTO A_ROLE_MENU_LINK_LIST (LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140001,1,2100140001,1,'システム管理者','0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140001,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140001,1,2100140001,1,'システム管理者','0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST (LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140002,1,2100140002,1,'システム管理者','0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140002,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140002,1,2100140002,1,'システム管理者','0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST (LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140003,1,2100140003,1,'システム管理者','0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140003,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140003,1,2100140003,1,'システム管理者','0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST (LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140004,1,2100140004,1,'システム管理者','0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140004,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140004,1,2100140004,1,'システム管理者','0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST (LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140005,1,2100140005,1,'システム管理者','0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140005,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140005,1,2100140005,1,'システム管理者','0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST (LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140006,1,2100140006,1,'システム管理者','0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140006,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140006,1,2100140006,1,'システム管理者','0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST (LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140007,1,2100140007,1,'システム管理者','0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140007,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140007,1,2100140007,1,'システム管理者','0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST (LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140009,1,2100140009,1,'システム管理者','0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140009,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140009,1,2100140009,1,'システム管理者','0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST (LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140010,1,2100140010,1,'システム管理者','0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140010,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140010,1,2100140010,1,'システム管理者','0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST (LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140011,1,2100140011,1,'システム管理者','0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140011,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140011,1,2100140011,1,'システム管理者','0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST (LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140012,1,2100140012,1,'システム管理者','0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140012,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140012,1,2100140012,1,'システム管理者','0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST (LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140013,1,2100140013,1,'システム管理者','0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140013,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140013,1,2100140013,1,'システム管理者','0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST (LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140014,1,2100140014,1,'システム管理者','0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140014,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140014,1,2100140014,1,'システム管理者','0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST (LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140015,1,2100140015,1,'システム管理者','1',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140015,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140015,1,2100140015,1,'システム管理者','1',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST (LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140016,1,2100140016,1,'システム管理者','1',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140016,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140016,1,2100140016,1,'システム管理者','1',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST (LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140017,1,2100140017,1,'システム管理者','1',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140017,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140017,1,2100140017,1,'システム管理者','1',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST (LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140018,1,2100140018,1,'システム管理者','1',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140018,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140018,1,2100140018,1,'システム管理者','1',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST (LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140019,1,2100140019,1,'システム管理者','1',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140019,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140019,1,2100140019,1,'システム管理者','1',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST (LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140020,1,2100140020,1,'システム管理者','1',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140020,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140020,1,2100140020,1,'システム管理者','1',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST (LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140021,1,2100140021,1,'システム管理者','1',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140021,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140021,1,2100140021,1,'システム管理者','1',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST (LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140022,1,2100140022,1,'システム管理者','1',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140022,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140022,1,2100140022,1,'システム管理者','1',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST (LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140025,1,2100140025,1,'システム管理者','1',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140025,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140025,1,2100140025,1,'システム管理者','1',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST (LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140026,1,2100140026,1,'システム管理者','1',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140026,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140026,1,2100140026,1,'システム管理者','1',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST (LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100140027,1,2100140027,1,'システム管理者','1',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_ROLE_MENU_LINK_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,LINK_ID,ROLE_ID,MENU_ID,PRIVILEGE,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-140027,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100140027,1,2100140027,1,'システム管理者','1',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);

INSERT INTO A_DEL_OPERATION_LIST (ROW_ID,LG_DAYS,PH_DAYS,TABLE_NAME,PKEY_NAME,OPE_ID_COL_NAME,GET_DATA_STRAGE_SQL,DATA_PATH_1,DATA_PATH_2,DATA_PATH_3,DATA_PATH_4,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100000026,3600,7200,'B_ANSTWR_PHO_LINK','PHO_LINK_ID','OPERATION_NO_UAPK',NULL,NULL,NULL,NULL,NULL,'作業対象ホスト(AnsibleTower/AWX)','0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_DEL_OPERATION_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,ROW_ID,LG_DAYS,PH_DAYS,TABLE_NAME,PKEY_NAME,OPE_ID_COL_NAME,GET_DATA_STRAGE_SQL,DATA_PATH_1,DATA_PATH_2,DATA_PATH_3,DATA_PATH_4,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-2100000026,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100000026,3600,7200,'B_ANSTWR_PHO_LINK','PHO_LINK_ID','OPERATION_NO_UAPK',NULL,NULL,NULL,NULL,NULL,'作業対象ホスト(AnsibleTower/AWX)','0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_DEL_OPERATION_LIST (ROW_ID,LG_DAYS,PH_DAYS,TABLE_NAME,PKEY_NAME,OPE_ID_COL_NAME,GET_DATA_STRAGE_SQL,DATA_PATH_1,DATA_PATH_2,DATA_PATH_3,DATA_PATH_4,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100000027,3600,7200,'B_ANSTWR_VARS_ASSIGN','VARS_ASSIGN_ID','OPERATION_NO_UAPK',NULL,NULL,NULL,NULL,NULL,'代入値管理(AnsibleTower/AWX)','0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_DEL_OPERATION_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,ROW_ID,LG_DAYS,PH_DAYS,TABLE_NAME,PKEY_NAME,OPE_ID_COL_NAME,GET_DATA_STRAGE_SQL,DATA_PATH_1,DATA_PATH_2,DATA_PATH_3,DATA_PATH_4,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-2100000027,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100000027,3600,7200,'B_ANSTWR_VARS_ASSIGN','VARS_ASSIGN_ID','OPERATION_NO_UAPK',NULL,NULL,NULL,NULL,NULL,'代入値管理(AnsibleTower/AWX)','0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_DEL_OPERATION_LIST (ROW_ID,LG_DAYS,PH_DAYS,TABLE_NAME,PKEY_NAME,OPE_ID_COL_NAME,GET_DATA_STRAGE_SQL,DATA_PATH_1,DATA_PATH_2,DATA_PATH_3,DATA_PATH_4,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2100000028,3600,7200,'C_ANSTWR_EXE_INS_MNG','EXECUTION_NO','OPERATION_NO_UAPK','SELECT ANSTWR_STORAGE_PATH_ITA AS PATH FROM B_ANSTWR_IF_INFO WHERE DISUSE_FLAG="0"','uploadfiles/2100140014/FILE_INPUT/','uploadfiles/2100140014/FILE_RESULT/','/__data_relay_storage__/',NULL,'作業管理(AnsibleTower/AWX)','0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO A_DEL_OPERATION_LIST_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,ROW_ID,LG_DAYS,PH_DAYS,TABLE_NAME,PKEY_NAME,OPE_ID_COL_NAME,GET_DATA_STRAGE_SQL,DATA_PATH_1,DATA_PATH_2,DATA_PATH_3,DATA_PATH_4,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(-2100000028,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2100000028,3600,7200,'C_ANSTWR_EXE_INS_MNG','EXECUTION_NO','OPERATION_NO_UAPK','SELECT ANSTWR_STORAGE_PATH_ITA AS PATH FROM B_ANSTWR_IF_INFO WHERE DISUSE_FLAG="0"','uploadfiles/2100140014/FILE_INPUT/','uploadfiles/2100140014/FILE_RESULT/','/__data_relay_storage__/',NULL,'作業管理(AnsibleTower/AWX)','0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);

INSERT INTO B_ITA_EXT_STM_MASTER (ITA_EXT_STM_ID,ITA_EXT_STM_NAME,ITA_EXT_LINK_LIB_PATH,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(12,'AnsibleTower/AWX','ansibletower_driver',12,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO B_ITA_EXT_STM_MASTER_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,ITA_EXT_STM_ID,ITA_EXT_STM_NAME,ITA_EXT_LINK_LIB_PATH,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(12,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',12,'AnsibleTower/AWX','ansibletower_driver',12,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);

INSERT INTO B_ANSTWR_STATUS (STATUS_ID,STATUS_NAME,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(1,'未実行',10,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO B_ANSTWR_STATUS_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,STATUS_ID,STATUS_NAME,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(1,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',1,'未実行',10,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO B_ANSTWR_STATUS (STATUS_ID,STATUS_NAME,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2,'準備中',20,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO B_ANSTWR_STATUS_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,STATUS_ID,STATUS_NAME,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2,'準備中',20,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO B_ANSTWR_STATUS (STATUS_ID,STATUS_NAME,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(3,'実行中',30,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO B_ANSTWR_STATUS_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,STATUS_ID,STATUS_NAME,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(3,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',3,'実行中',30,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO B_ANSTWR_STATUS (STATUS_ID,STATUS_NAME,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(4,'実行中(遅延)',40,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO B_ANSTWR_STATUS_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,STATUS_ID,STATUS_NAME,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(4,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',4,'実行中(遅延)',40,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO B_ANSTWR_STATUS (STATUS_ID,STATUS_NAME,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(5,'完了',50,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO B_ANSTWR_STATUS_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,STATUS_ID,STATUS_NAME,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(5,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',5,'完了',50,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO B_ANSTWR_STATUS (STATUS_ID,STATUS_NAME,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(6,'完了(異常)',60,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO B_ANSTWR_STATUS_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,STATUS_ID,STATUS_NAME,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(6,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',6,'完了(異常)',60,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO B_ANSTWR_STATUS (STATUS_ID,STATUS_NAME,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(7,'想定外エラー',70,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO B_ANSTWR_STATUS_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,STATUS_ID,STATUS_NAME,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(7,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',7,'想定外エラー',70,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO B_ANSTWR_STATUS (STATUS_ID,STATUS_NAME,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(8,'緊急停止',80,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO B_ANSTWR_STATUS_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,STATUS_ID,STATUS_NAME,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(8,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',8,'緊急停止',80,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO B_ANSTWR_STATUS (STATUS_ID,STATUS_NAME,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(9,'未実行(予約)',90,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO B_ANSTWR_STATUS_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,STATUS_ID,STATUS_NAME,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(9,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',9,'未実行(予約)',90,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO B_ANSTWR_STATUS (STATUS_ID,STATUS_NAME,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(10,'予約取消',100,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO B_ANSTWR_STATUS_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,STATUS_ID,STATUS_NAME,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(10,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',10,'予約取消',100,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);

INSERT INTO B_ANSTWR_IF_INFO (ANSTWR_IF_INFO_ID,ANSTWR_STORAGE_PATH_ITA,ANSTWR_STORAGE_PATH_ANSTWR,SYMPHONY_STORAGE_PATH_ANSTWR,ANSTWR_PROTOCOL,ANSTWR_HOSTNAME,ANSTWR_PORT,ANSTWR_USER_ID,ANSTWR_PASSWORD,ANSTWR_DEL_RUNTIME_DATA,ANSTWR_REFRESH_INTERVAL,ANSTWR_TAILLOG_LINES,DISP_SEQ,NULL_DATA_HANDLING_FLG,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(1,'%%%%%ITA_DIRECTORY%%%%%/data_relay_storage/ansibletower_driver','%%%%%ITA_DIRECTORY%%%%%/data_relay_storage/ansibletower_driver','%%%%%ITA_DIRECTORY%%%%%/data_relay_storage/symphony','https','example.com',443,'dummy_id','dummypassword',1,3000,1000,10,'2',NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO B_ANSTWR_IF_INFO_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,ANSTWR_IF_INFO_ID,ANSTWR_STORAGE_PATH_ITA,ANSTWR_STORAGE_PATH_ANSTWR,SYMPHONY_STORAGE_PATH_ANSTWR,ANSTWR_PROTOCOL,ANSTWR_HOSTNAME,ANSTWR_PORT,ANSTWR_USER_ID,ANSTWR_PASSWORD,ANSTWR_DEL_RUNTIME_DATA,ANSTWR_REFRESH_INTERVAL,ANSTWR_TAILLOG_LINES,DISP_SEQ,NULL_DATA_HANDLING_FLG,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(1,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',1,'%%%%%ITA_DIRECTORY%%%%%/data_relay_storage/ansibletower_driver','%%%%%ITA_DIRECTORY%%%%%/data_relay_storage/ansibletower_driver','%%%%%ITA_DIRECTORY%%%%%/data_relay_storage/symphony','https','example.com',443,'dummy_id','dummypassword',1,3000,1000,10,'2',NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);

INSERT INTO B_ANSTWR_RUN_MODE (RUN_MODE_ID,RUN_MODE_NAME,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(1,'通常',10,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO B_ANSTWR_RUN_MODE_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,RUN_MODE_ID,RUN_MODE_NAME,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(1,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',1,'通常',10,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO B_ANSTWR_RUN_MODE (RUN_MODE_ID,RUN_MODE_NAME,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2,'ドライラン',20,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO B_ANSTWR_RUN_MODE_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,RUN_MODE_ID,RUN_MODE_NAME,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2,'ドライラン',20,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);

INSERT INTO B_ANSTWR_VARS_ATTR (VARS_ATTR_ID,VARS_ATTR_NAME,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(1,'一般変数',10,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO B_ANSTWR_VARS_ATTR_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,VARS_ATTR_ID,VARS_ATTR_NAME,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(1,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',1,'一般変数',10,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO B_ANSTWR_VARS_ATTR (VARS_ATTR_ID,VARS_ATTR_NAME,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2,'複数具体値変数',20,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO B_ANSTWR_VARS_ATTR_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,VARS_ATTR_ID,VARS_ATTR_NAME,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(2,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',2,'複数具体値変数',20,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO B_ANSTWR_VARS_ATTR (VARS_ATTR_ID,VARS_ATTR_NAME,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(3,'多段変数',30,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO B_ANSTWR_VARS_ATTR_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,VARS_ATTR_ID,VARS_ATTR_NAME,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(3,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',3,'多段変数',30,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);

INSERT INTO B_ANSTWER_GATHERFACTS_FLAG (FLAG_ID,FLAG_NAME,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(1,'実施',1,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO B_ANSTWER_GATHERFACTS_FLAG_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,FLAG_ID,FLAG_NAME,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(1,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',1,'実施',1,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);

INSERT INTO B_ANSTWER_RUNDATA_DEL_FLAG (FLAG_ID,FLAG_NAME,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(1,'削除する',1,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);
INSERT INTO B_ANSTWER_RUNDATA_DEL_FLAG_JNL (JOURNAL_SEQ_NO,JOURNAL_REG_DATETIME,JOURNAL_ACTION_CLASS,FLAG_ID,FLAG_NAME,DISP_SEQ,NOTE,DISUSE_FLAG,LAST_UPDATE_TIMESTAMP,LAST_UPDATE_USER) VALUES(1,STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),'INSERT',1,'削除する',1,NULL,'0',STR_TO_DATE('2015/04/01 10:00:00.000000','%Y/%m/%d %H:%i:%s.%f'),1);


COMMIT;
