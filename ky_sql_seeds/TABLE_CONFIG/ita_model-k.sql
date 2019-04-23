-- -- //////////////////////////////////////////////////////////////////////
-- -- //
-- -- //  【処理概要】
-- -- //    ・インストーラー用のSQL
-- -- //
-- -- //////////////////////////////////////////////////////////////////////

CREATE TABLE B_ANSTWR_IF_INFO ( 
  ANSTWR_IF_INFO_ID               %INT%                             , 
  ANSTWR_STORAGE_PATH_ITA         %VARCHR%(256)                     , 
  ANSTWR_STORAGE_PATH_ANSTWR      %VARCHR%(256)                     , 
  SYMPHONY_STORAGE_PATH_ANSTWR    %VARCHR%(256)                     , 
  ANSTWR_PROTOCOL                 %VARCHR%(8)                       , 
  ANSTWR_HOSTNAME                 %VARCHR%(128)                     , 
  ANSTWR_PORT                     %INT%                             , 
  ANSTWR_USER_ID                  %VARCHR%(30)                      , 
  ANSTWR_PASSWORD                 %VARCHR%(30)                      , 
  ANSTWR_AUTH_TOKEN               %VARCHR%(256)                     , 
  ANSTWR_DEL_RUNTIME_DATA         %INT%                             , 
  ANSTWR_REFRESH_INTERVAL         %INT%                             , 
  ANSTWR_TAILLOG_LINES            %INT%                             , 
  DISP_SEQ                        %INT%                             , 
  NULL_DATA_HANDLING_FLG          %INT%                             , -- Null値の連携 1:有効　2:無効
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (ANSTWR_IF_INFO_ID) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_IF_INFO_JNL ( 
  JOURNAL_SEQ_NO                  %INT%                             , 
  JOURNAL_REG_DATETIME            %DATETIME6%                       , 
  JOURNAL_ACTION_CLASS            %VARCHR%(8)                       , 
  ANSTWR_IF_INFO_ID               %INT%                             , 
  ANSTWR_STORAGE_PATH_ITA         %VARCHR%(256)                     , 
  ANSTWR_STORAGE_PATH_ANSTWR      %VARCHR%(256)                     , 
  SYMPHONY_STORAGE_PATH_ANSTWR    %VARCHR%(256)                     , 
  ANSTWR_PROTOCOL                 %VARCHR%(8)                       , 
  ANSTWR_HOSTNAME                 %VARCHR%(128)                     , 
  ANSTWR_PORT                     %INT%                             , 
  ANSTWR_USER_ID                  %VARCHR%(30)                      , 
  ANSTWR_PASSWORD                 %VARCHR%(30)                      , 
  ANSTWR_AUTH_TOKEN               %VARCHR%(256)                     , 
  ANSTWR_DEL_RUNTIME_DATA         %INT%                             , 
  ANSTWR_REFRESH_INTERVAL         %INT%                             , 
  ANSTWR_TAILLOG_LINES            %INT%                             , 
  DISP_SEQ                        %INT%                             , 
  NULL_DATA_HANDLING_FLG          %INT%                             , -- Null値の連携 1:有効　2:無効
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_GLOBAL_VARS ( 
  GLOBAL_VARS_ID                  %INT%                             , 
  VARS_NAME                       %VARCHR%(128)                     , 
  VARS_ENTRY                      %VARCHR%(1024)                    , 
  VARS_DESCRIPTION                %VARCHR%(128)                     , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (GLOBAL_VARS_ID) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_GLOBAL_VARS_JNL ( 
  JOURNAL_SEQ_NO                  %INT%                             , 
  JOURNAL_REG_DATETIME            %DATETIME6%                       , 
  JOURNAL_ACTION_CLASS            %VARCHR%(8)                       , 
  GLOBAL_VARS_ID                  %INT%                             , 
  VARS_NAME                       %VARCHR%(128)                     , 
  VARS_ENTRY                      %VARCHR%(1024)                    , 
  VARS_DESCRIPTION                %VARCHR%(128)                     , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_ROLE_PACKAGE ( 
  ROLE_PACKAGE_ID                 %INT%                             , 
  ROLE_PACKAGE_NAME               %VARCHR%(128)                     , 
  ROLE_PACKAGE_FILE               %VARCHR%(256)                     , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (ROLE_PACKAGE_ID) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_ROLE_PACKAGE_JNL ( 
  JOURNAL_SEQ_NO                  %INT%                             , 
  JOURNAL_REG_DATETIME            %DATETIME6%                       , 
  JOURNAL_ACTION_CLASS            %VARCHR%(8)                       , 
  ROLE_PACKAGE_ID                 %INT%                             , 
  ROLE_PACKAGE_NAME               %VARCHR%(128)                     , 
  ROLE_PACKAGE_FILE               %VARCHR%(256)                     , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_CONTENTS_FILE ( 
  CONTENTS_FILE_ID                %INT%                             , 
  CONTENTS_FILE_VARS_NAME         %VARCHR%(128)                     , 
  CONTENTS_FILE                   %VARCHR%(256)                     , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (CONTENTS_FILE_ID) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_CONTENTS_FILE_JNL ( 
  JOURNAL_SEQ_NO                  %INT%                             , 
  JOURNAL_REG_DATETIME            %DATETIME6%                       , 
  JOURNAL_ACTION_CLASS            %VARCHR%(8)                       , 
  CONTENTS_FILE_ID                %INT%                             , 
  CONTENTS_FILE_VARS_NAME         %VARCHR%(128)                     , 
  CONTENTS_FILE                   %VARCHR%(256)                     , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_PTN_ROLE_LINK ( 
  PTN_ROLE_LINK_ID                %INT%                             , 
  PATTERN_ID                      %INT%                             , 
  ROLE_PACKAGE_ID                 %INT%                             , 
  ROLE_ID                         %INT%                             , 
  INCLUDE_SEQ                     %INT%                             , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (PTN_ROLE_LINK_ID) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_PTN_ROLE_LINK_JNL ( 
  JOURNAL_SEQ_NO                  %INT%                             , 
  JOURNAL_REG_DATETIME            %DATETIME6%                       , 
  JOURNAL_ACTION_CLASS            %VARCHR%(8)                       , 
  PTN_ROLE_LINK_ID                %INT%                             , 
  PATTERN_ID                      %INT%                             , 
  ROLE_PACKAGE_ID                 %INT%                             , 
  ROLE_ID                         %INT%                             , 
  INCLUDE_SEQ                     %INT%                             , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_MAX_MEMBER_COL ( 
  MAX_MEMBER_COL_ID               %INT%                             , 
  VARS_ID                         %INT%                             , 
  NESTED_MEM_VARS_ID              %INT%                             , 
  MAX_COL_SEQ                     %INT%                             , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (MAX_MEMBER_COL_ID) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_MAX_MEMBER_COL_JNL ( 
  JOURNAL_SEQ_NO                  %INT%                             , 
  JOURNAL_REG_DATETIME            %DATETIME6%                       , 
  JOURNAL_ACTION_CLASS            %VARCHR%(8)                       , 
  MAX_MEMBER_COL_ID               %INT%                             , 
  VARS_ID                         %INT%                             , 
  NESTED_MEM_VARS_ID              %INT%                             , 
  MAX_COL_SEQ                     %INT%                             , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_PRMCOL_VARS_LINK ( 
  PRMCOL_VARS_LINK_ID             %INT%                             , 
  MENU_ID                         %INT%                             , 
  MENU_COLUMN_ID                  %INT%                             , 
  PRMCOL_LINK_TYPE_ID             %INT%                             , 
  PATTERN_ID                      %INT%                             , 
  KEY_VARS_LINK_ID                %INT%                             , 
  KEY_NESTEDMEM_COL_CMB_ID        %INT%                             , 
  KEY_ASSIGN_SEQ                  %INT%                             , 
  VALUE_VARS_LINK_ID              %INT%                             , 
  VALUE_NESTEDMEM_COL_CMB_ID      %INT%                             , 
  VALUE_ASSIGN_SEQ                %INT%                             , 
  NULL_DATA_HANDLING_FLG          %INT%                             , -- Null値の連携
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (PRMCOL_VARS_LINK_ID) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_PRMCOL_VARS_LINK_JNL ( 
  JOURNAL_SEQ_NO                  %INT%                             , 
  JOURNAL_REG_DATETIME            %DATETIME6%                       , 
  JOURNAL_ACTION_CLASS            %VARCHR%(8)                       , 
  PRMCOL_VARS_LINK_ID             %INT%                             , 
  MENU_ID                         %INT%                             , 
  MENU_COLUMN_ID                  %INT%                             , 
  PRMCOL_LINK_TYPE_ID             %INT%                             , 
  PATTERN_ID                      %INT%                             , 
  KEY_VARS_LINK_ID                %INT%                             , 
  KEY_NESTEDMEM_COL_CMB_ID        %INT%                             , 
  KEY_ASSIGN_SEQ                  %INT%                             , 
  VALUE_VARS_LINK_ID              %INT%                             , 
  VALUE_NESTEDMEM_COL_CMB_ID      %INT%                             , 
  VALUE_ASSIGN_SEQ                %INT%                             , 
  NULL_DATA_HANDLING_FLG          %INT%                             , -- Null値の連携
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_VARS_ASSIGN ( 
  VARS_ASSIGN_ID                  %INT%                             , 
  OPERATION_NO_UAPK               %INT%                             , 
  PATTERN_ID                      %INT%                             , 
  SYSTEM_ID                       %INT%                             , 
  VARS_LINK_ID                    %INT%                             , 
  NESTEDMEM_COL_CMB_ID            %INT%                             , 
  VARS_ENTRY                      %VARCHR%(1024)                    , 
  ASSIGN_SEQ                      %INT%                             , 
  VARS_VALUE                      %VARCHR%(1024)                    , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (VARS_ASSIGN_ID) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_VARS_ASSIGN_JNL ( 
  JOURNAL_SEQ_NO                  %INT%                             , 
  JOURNAL_REG_DATETIME            %DATETIME6%                       , 
  JOURNAL_ACTION_CLASS            %VARCHR%(8)                       , 
  VARS_ASSIGN_ID                  %INT%                             , 
  OPERATION_NO_UAPK               %INT%                             , 
  PATTERN_ID                      %INT%                             , 
  SYSTEM_ID                       %INT%                             , 
  VARS_LINK_ID                    %INT%                             , 
  NESTEDMEM_COL_CMB_ID            %INT%                             , 
  VARS_ENTRY                      %VARCHR%(1024)                    , 
  ASSIGN_SEQ                      %INT%                             , 
  VARS_VALUE                      %VARCHR%(1024)                    , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_PHO_LINK ( 
  PHO_LINK_ID                     %INT%                             , 
  OPERATION_NO_UAPK               %INT%                             , 
  PATTERN_ID                      %INT%                             , 
  SYSTEM_ID                       %INT%                             , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (PHO_LINK_ID) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_PHO_LINK_JNL ( 
  JOURNAL_SEQ_NO                  %INT%                             , 
  JOURNAL_REG_DATETIME            %DATETIME6%                       , 
  JOURNAL_ACTION_CLASS            %VARCHR%(8)                       , 
  PHO_LINK_ID                     %INT%                             , 
  OPERATION_NO_UAPK               %INT%                             , 
  PATTERN_ID                      %INT%                             , 
  SYSTEM_ID                       %INT%                             , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_ROLE ( 
  ROLE_ID                         %INT%                             , 
  ROLE_PACKAGE_ID                 %INT%                             , 
  ROLE_NAME                       %VARCHR%(128)                     , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (ROLE_ID) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_ROLE_JNL ( 
  JOURNAL_SEQ_NO                  %INT%                             , 
  JOURNAL_REG_DATETIME            %DATETIME6%                       , 
  JOURNAL_ACTION_CLASS            %VARCHR%(8)                       , 
  ROLE_ID                         %INT%                             , 
  ROLE_PACKAGE_ID                 %INT%                             , 
  ROLE_NAME                       %VARCHR%(128)                     , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_DEFAULT_VARSVAL ( 
  VARSVAL_ID                      %INT%                             , 
  ROLE_PACKAGE_ID                 %INT%                             , 
  ROLE_ID                         %INT%                             , 
  END_VAR_OF_VARS_ATTR_ID         %INT%                             , 
  VARS_ID                         %INT%                             , 
  NESTEDMEM_COL_CMB_ID            %INT%                             , 
  ASSIGN_SEQ                      %INT%                             , 
  VARS_VALUE                      %VARCHR%(1024)                    , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (VARSVAL_ID) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_DEFAULT_VARSVAL_JNL ( 
  JOURNAL_SEQ_NO                  %INT%                             , 
  JOURNAL_REG_DATETIME            %DATETIME6%                       , 
  JOURNAL_ACTION_CLASS            %VARCHR%(8)                       , 
  VARSVAL_ID                      %INT%                             , 
  ROLE_PACKAGE_ID                 %INT%                             , 
  ROLE_ID                         %INT%                             , 
  END_VAR_OF_VARS_ATTR_ID         %INT%                             , 
  VARS_ID                         %INT%                             , 
  NESTEDMEM_COL_CMB_ID            %INT%                             , 
  ASSIGN_SEQ                      %INT%                             , 
  VARS_VALUE                      %VARCHR%(1024)                    , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_VARS ( 
  VARS_ID                         %INT%                             , 
  VARS_NAME                       %VARCHR%(128)                     , 
  VARS_ATTR_ID                    %INT%                             , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (VARS_ID) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_VARS_JNL ( 
  JOURNAL_SEQ_NO                  %INT%                             , 
  JOURNAL_REG_DATETIME            %DATETIME6%                       , 
  JOURNAL_ACTION_CLASS            %VARCHR%(8)                       , 
  VARS_ID                         %INT%                             , 
  VARS_NAME                       %VARCHR%(128)                     , 
  VARS_ATTR_ID                    %INT%                             , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_PTN_VARS_LINK ( 
  VARS_LINK_ID                    %INT%                             , 
  PATTERN_ID                      %INT%                             , 
  VARS_ID                         %INT%                             , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (VARS_LINK_ID) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_PTN_VARS_LINK_JNL ( 
  JOURNAL_SEQ_NO                  %INT%                             , 
  JOURNAL_REG_DATETIME            %DATETIME6%                       , 
  JOURNAL_ACTION_CLASS            %VARCHR%(8)                       , 
  VARS_LINK_ID                    %INT%                             , 
  PATTERN_ID                      %INT%                             , 
  VARS_ID                         %INT%                             , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_ROLE_VARS ( 
  ROLE_VARS_ID                    %INT%                             , 
  ROLE_PACKAGE_ID                 %INT%                             , 
  ROLE_ID                         %INT%                             , 
  VARS_ID                         %INT%                             , 
  VARS_ATTR_ID                    %INT%                             , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (ROLE_VARS_ID) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_ROLE_VARS_JNL ( 
  JOURNAL_SEQ_NO                  %INT%                             , 
  JOURNAL_REG_DATETIME            %DATETIME6%                       , 
  JOURNAL_ACTION_CLASS            %VARCHR%(8)                       , 
  ROLE_VARS_ID                    %INT%                             , 
  ROLE_PACKAGE_ID                 %INT%                             , 
  ROLE_ID                         %INT%                             , 
  VARS_ID                         %INT%                             , 
  VARS_ATTR_ID                    %INT%                             , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_TRANSLATE_VARS ( 
  TRANSLATE_VARS_ID               %INT%                             , 
  ROLE_PACKAGE_ID                 %INT%                             , 
  ROLE_ID                         %INT%                             , 
  ITA_VARS_NAME                   %VARCHR%(128)                     , 
  ANY_VARS_NAME                   %VARCHR%(128)                     , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (TRANSLATE_VARS_ID) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_TRANSLATE_VARS_JNL ( 
  JOURNAL_SEQ_NO                  %INT%                             , 
  JOURNAL_REG_DATETIME            %DATETIME6%                       , 
  JOURNAL_ACTION_CLASS            %VARCHR%(8)                       , 
  TRANSLATE_VARS_ID               %INT%                             , 
  ROLE_PACKAGE_ID                 %INT%                             , 
  ROLE_ID                         %INT%                             , 
  ITA_VARS_NAME                   %VARCHR%(128)                     , 
  ANY_VARS_NAME                   %VARCHR%(128)                     , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_NESTED_MEM_VARS ( 
  NESTED_MEM_VARS_ID              %INT%                             , 
  VARS_ID                         %INT%                             , 
  PARENT_KEY_ID                   %INT%                             , 
  SELF_KEY_ID                     %INT%                             , 
  MEMBER_NAME                     %VARCHR%(128)                     , 
  NESTED_LEVEL                    %INT%                             , 
  ASSIGN_SEQ_NEED                 %INT%                             , 
  COL_SEQ_NEED                    %INT%                             , 
  MEMBER_DISP                     %INT%                             , 
  MAX_COL_SEQ                     %INT%                             , 
  NESTED_MEMBER_PATH              %VARCHR%(1024)                    , 
  NESTED_MEMBER_PATH_ALIAS        %VARCHR%(1024)                    , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (NESTED_MEM_VARS_ID) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_NESTED_MEM_VARS_JNL ( 
  JOURNAL_SEQ_NO                  %INT%                             , 
  JOURNAL_REG_DATETIME            %DATETIME6%                       , 
  JOURNAL_ACTION_CLASS            %VARCHR%(8)                       , 
  NESTED_MEM_VARS_ID              %INT%                             , 
  VARS_ID                         %INT%                             , 
  PARENT_KEY_ID                   %INT%                             , 
  SELF_KEY_ID                     %INT%                             , 
  MEMBER_NAME                     %VARCHR%(128)                     , 
  NESTED_LEVEL                    %INT%                             , 
  ASSIGN_SEQ_NEED                 %INT%                             , 
  COL_SEQ_NEED                    %INT%                             , 
  MEMBER_DISP                     %INT%                             , 
  MAX_COL_SEQ                     %INT%                             , 
  NESTED_MEMBER_PATH              %VARCHR%(1024)                    , 
  NESTED_MEMBER_PATH_ALIAS        %VARCHR%(1024)                    , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_NESTEDMEM_COL_CMB ( 
  NESTEDMEM_COL_CMB_ID            %INT%                             , 
  VARS_ID                         %INT%                             , 
  NESTED_MEM_VARS_ID              %INT%                             , 
  COL_COMBINATION_MEMBER_ALIAS    %VARCHR%(4000)                    , 
  COL_SEQ_VALUE                   %VARCHR%(4000)                    , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (NESTEDMEM_COL_CMB_ID) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_NESTEDMEM_COL_CMB_JNL ( 
  JOURNAL_SEQ_NO                  %INT%                             , 
  JOURNAL_REG_DATETIME            %DATETIME6%                       , 
  JOURNAL_ACTION_CLASS            %VARCHR%(8)                       , 
  NESTEDMEM_COL_CMB_ID            %INT%                             , 
  VARS_ID                         %INT%                             , 
  NESTED_MEM_VARS_ID              %INT%                             , 
  COL_COMBINATION_MEMBER_ALIAS    %VARCHR%(4000)                    , 
  COL_SEQ_VALUE                   %VARCHR%(4000)                    , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_CREDENTIAL_TYPE ( 
  CREDENTIAL_TYPE_ITA_MANAGED_ID  %INT%                             , 
  CREDENTIAL_TYPE_ID              %INT%                             , 
  CREDENTIAL_TYPE_NAME            %VARCHR%(512)                     , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (CREDENTIAL_TYPE_ITA_MANAGED_ID) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_CREDENTIAL_TYPE_JNL ( 
  JOURNAL_SEQ_NO                  %INT%                             , 
  JOURNAL_REG_DATETIME            %DATETIME6%                       , 
  JOURNAL_ACTION_CLASS            %VARCHR%(8)                       , 
  CREDENTIAL_TYPE_ITA_MANAGED_ID  %INT%                             , 
  CREDENTIAL_TYPE_ID              %INT%                             , 
  CREDENTIAL_TYPE_NAME            %VARCHR%(512)                     , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_ORGANIZATION ( 
  ORGANIZATION_ITA_MANAGED_ID     %INT%                             , 
  ORGANIZATION_ID                 %INT%                             , 
  ORGANIZATION_NAME               %VARCHR%(512)                     , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (ORGANIZATION_ITA_MANAGED_ID) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_ORGANIZATION_JNL ( 
  JOURNAL_SEQ_NO                  %INT%                             , 
  JOURNAL_REG_DATETIME            %DATETIME6%                       , 
  JOURNAL_ACTION_CLASS            %VARCHR%(8)                       , 
  ORGANIZATION_ITA_MANAGED_ID     %INT%                             , 
  ORGANIZATION_ID                 %INT%                             , 
  ORGANIZATION_NAME               %VARCHR%(512)                     , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_STATUS ( 
  STATUS_ID                       %INT%                             , 
  STATUS_NAME                     %VARCHR%(32)                      , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (STATUS_ID) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_STATUS_JNL ( 
  JOURNAL_SEQ_NO                  %INT%                             , 
  JOURNAL_REG_DATETIME            %DATETIME6%                       , 
  JOURNAL_ACTION_CLASS            %VARCHR%(8)                       , 
  STATUS_ID                       %INT%                             , 
  STATUS_NAME                     %VARCHR%(32)                      , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_RUN_MODE ( 
  RUN_MODE_ID                     %INT%                             , 
  RUN_MODE_NAME                   %VARCHR%(32)                      , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (RUN_MODE_ID) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_RUN_MODE_JNL ( 
  JOURNAL_SEQ_NO                  %INT%                             , 
  JOURNAL_REG_DATETIME            %DATETIME6%                       , 
  JOURNAL_ACTION_CLASS            %VARCHR%(8)                       , 
  RUN_MODE_ID                     %INT%                             , 
  RUN_MODE_NAME                   %VARCHR%(32)                      , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWER_GATHERFACTS_FLAG
(
FLAG_ID                           %INT%                             , -- 識別シーケンス
FLAG_NAME                         %VARCHR%(32)                      , -- 表示名
DISP_SEQ                          %INT%                             , -- 表示順序
NOTE                              %VARCHR%(4000)                    , -- 備考
DISUSE_FLAG                       %VARCHR%(1)                       , -- 廃止フラグ
LAST_UPDATE_TIMESTAMP             %DATETIME6%                       , -- 最終更新日時
LAST_UPDATE_USER                  %INT%                             , -- 最終更新ユーザ
PRIMARY KEY (FLAG_ID)
)%%TABLE_CREATE_OUT_TAIL%%;

CREATE TABLE B_ANSTWER_GATHERFACTS_FLAG_JNL
(
JOURNAL_SEQ_NO                    %INT%                             , -- 履歴用シーケンス
JOURNAL_REG_DATETIME              %DATETIME6%                       , -- 履歴用変更日時
JOURNAL_ACTION_CLASS              %VARCHR%(8)                       , -- 履歴用変更種別
FLAG_ID                           %INT%                             , -- 識別シーケンス
FLAG_NAME                         %VARCHR%(32)                      , -- 表示名
DISP_SEQ                          %INT%                             , -- 表示順序
NOTE                              %VARCHR%(4000)                    , -- 備考
DISUSE_FLAG                       %VARCHR%(1)                       , -- 廃止フラグ
LAST_UPDATE_TIMESTAMP             %DATETIME6%                       , -- 最終更新日時
LAST_UPDATE_USER                  %INT%                             , -- 最終更新ユーザ
PRIMARY KEY(JOURNAL_SEQ_NO)
)%%TABLE_CREATE_OUT_TAIL%%;

CREATE TABLE B_ANSTWER_RUNDATA_DEL_FLAG
(
FLAG_ID                           %INT%                             , -- 識別シーケンス
FLAG_NAME                         %VARCHR%(32)                      , -- 表示名
DISP_SEQ                          %INT%                             , -- 表示順序
NOTE                              %VARCHR%(4000)                    , -- 備考
DISUSE_FLAG                       %VARCHR%(1)                       , -- 廃止フラグ
LAST_UPDATE_TIMESTAMP             %DATETIME6%                       , -- 最終更新日時
LAST_UPDATE_USER                  %INT%                             , -- 最終更新ユーザ
PRIMARY KEY (FLAG_ID)
)%%TABLE_CREATE_OUT_TAIL%%;

CREATE TABLE B_ANSTWER_RUNDATA_DEL_FLAG_JNL
(
JOURNAL_SEQ_NO                    %INT%                             , -- 履歴用シーケンス
JOURNAL_REG_DATETIME              %DATETIME6%                       , -- 履歴用変更日時
JOURNAL_ACTION_CLASS              %VARCHR%(8)                       , -- 履歴用変更種別
FLAG_ID                           %INT%                             , -- 識別シーケンス
FLAG_NAME                         %VARCHR%(32)                      , -- 表示名
DISP_SEQ                          %INT%                             , -- 表示順序
NOTE                              %VARCHR%(4000)                    , -- 備考
DISUSE_FLAG                       %VARCHR%(1)                       , -- 廃止フラグ
LAST_UPDATE_TIMESTAMP             %DATETIME6%                       , -- 最終更新日時
LAST_UPDATE_USER                  %INT%                             , -- 最終更新ユーザ
PRIMARY KEY(JOURNAL_SEQ_NO)
)%%TABLE_CREATE_OUT_TAIL%%;

CREATE TABLE B_ANSTWR_VARS_ATTR ( 
  VARS_ATTR_ID                    %INT%                             , 
  VARS_ATTR_NAME                  %VARCHR%(64)                      , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (VARS_ATTR_ID) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_VARS_ATTR_JNL ( 
  JOURNAL_SEQ_NO                  %INT%                             , 
  JOURNAL_REG_DATETIME            %DATETIME6%                       , 
  JOURNAL_ACTION_CLASS            %VARCHR%(8)                       , 
  VARS_ATTR_ID                    %INT%                             , 
  VARS_ATTR_NAME                  %VARCHR%(64)                      , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_INSTANCE_GROUP ( 
  INSTANCE_GROUP_ITA_MANAGED_ID   %INT%                             , 
  INSTANCE_GROUP_NAME             %VARCHR%(512)                     , 
  INSTANCE_GROUP_ID               %INT%                             , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (INSTANCE_GROUP_ITA_MANAGED_ID) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE B_ANSTWR_INSTANCE_GROUP_JNL ( 
  JOURNAL_SEQ_NO                  %INT%                             , 
  JOURNAL_REG_DATETIME            %DATETIME6%                       , 
  JOURNAL_ACTION_CLASS            %VARCHR%(8)                       , 
  INSTANCE_GROUP_ITA_MANAGED_ID   %INT%                             , 
  INSTANCE_GROUP_NAME             %VARCHR%(512)                     , 
  INSTANCE_GROUP_ID               %INT%                             , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE C_ANSTWR_EXE_INS_MNG ( 
  EXECUTION_NO                    %INT%                             , 
  RUN_MODE_ID                     %INT%                             , 
  STATUS_ID                       %INT%                             , 
  EXECUTION_USER                  %VARCHR%(80)                      , -- 実行ユーザ
  SYMPHONY_INSTANCE_NO            %INT%                             , 
  PATTERN_ID                      %INT%                             , 
  I_PATTERN_NAME                  %VARCHR%(256)                     , 
  I_TIME_LIMIT                    %INT%                             , 
  I_ANS_HOST_DESIGNATE_TYPE_ID    %INT%                             , 
  I_ANS_PARALLEL_EXE              %INT%                             , 
  I_ANS_WINRM_ID                  %INT%                             , 
  I_ANS_GATHER_FACTS              %INT%                             , 
  OPERATION_NO_UAPK               %INT%                             , 
  I_OPERATION_NAME                %VARCHR%(128)                     , 
  I_OPERATION_NO_IDBH             %INT%                             , 
  FILE_INPUT                      %VARCHR%(1024)                    , 
  FILE_RESULT                     %VARCHR%(1024)                    , 
  I_ANSTWR_DEL_RUNTIME_DATA       %INT%                             , 
  TIME_BOOK                       %DATETIME6%                       , 
  TIME_START                      %DATETIME6%                       , 
  TIME_END                        %DATETIME6%                       , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (EXECUTION_NO) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE TABLE C_ANSTWR_EXE_INS_MNG_JNL ( 
  JOURNAL_SEQ_NO                  %INT%                             , 
  JOURNAL_REG_DATETIME            %DATETIME6%                       , 
  JOURNAL_ACTION_CLASS            %VARCHR%(8)                       , 
  EXECUTION_NO                    %INT%                             , 
  RUN_MODE_ID                     %INT%                             , 
  STATUS_ID                       %INT%                             , 
  EXECUTION_USER                  %VARCHR%(80)                      , -- 実行ユーザ
  SYMPHONY_INSTANCE_NO            %INT%                             , 
  PATTERN_ID                      %INT%                             , 
  I_PATTERN_NAME                  %VARCHR%(256)                     , 
  I_TIME_LIMIT                    %INT%                             , 
  I_ANS_HOST_DESIGNATE_TYPE_ID    %INT%                             , 
  I_ANS_PARALLEL_EXE              %INT%                             , 
  I_ANS_WINRM_ID                  %INT%                             , 
  I_ANS_GATHER_FACTS              %INT%                             , 
  OPERATION_NO_UAPK               %INT%                             , 
  I_OPERATION_NAME                %VARCHR%(128)                     , 
  I_OPERATION_NO_IDBH             %INT%                             , 
  FILE_INPUT                      %VARCHR%(1024)                    , 
  FILE_RESULT                     %VARCHR%(1024)                    , 
  I_ANSTWR_DEL_RUNTIME_DATA       %INT%                             , 
  TIME_BOOK                       %DATETIME6%                       , 
  TIME_START                      %DATETIME6%                       , 
  TIME_END                        %DATETIME6%                       , 
  DISP_SEQ                        %INT%                             , 
  NOTE                            %VARCHR%(4000)                    , 
  DISUSE_FLAG                     %VARCHR%(1)                       , 
  LAST_UPDATE_TIMESTAMP           %DATETIME6%                       , 
  LAST_UPDATE_USER                %INT%                             , 
  PRIMARY KEY (JOURNAL_SEQ_NO) 
)%%TABLE_CREATE_OUT_TAIL%%; 

CREATE VIEW E_ANSTWR_PATTERN AS
  select TAB_A.PATTERN_ID AS PATTERN_ID,
    TAB_A.PATTERN_NAME AS PATTERN_NAME,
    [%CONCAT_HEAD/%]TAB_A.PATTERN_ID[%CONCAT_MID/%]':'[%CONCAT_MID/%]TAB_A.PATTERN_NAME[%CONCAT_TAIL/%] AS PATTERN,
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
    [%CONCAT_HEAD/%]TAB_A.PATTERN_ID[%CONCAT_MID/%]':'[%CONCAT_MID/%]TAB_A.PATTERN_NAME[%CONCAT_TAIL/%] AS PATTERN,
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
  [%CONCAT_HEAD/%]TAB_A.SYSTEM_ID[%CONCAT_MID/%]':'[%CONCAT_MID/%]TAB_B.HOSTNAME[%CONCAT_TAIL/%] AS HOST_PULLDOWN,
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
  [%CONCAT_HEAD/%]TAB_A.SYSTEM_ID[%CONCAT_MID/%]':'[%CONCAT_MID/%]TAB_B.HOSTNAME[%CONCAT_TAIL/%] AS HOST_PULLDOWN,
  TAB_B.DISUSE_FLAG
FROM 
  B_ANSTWR_PHO_LINK_JNL TAB_A
  LEFT JOIN C_STM_LIST TAB_B ON TAB_B.SYSTEM_ID = TAB_A.SYSTEM_ID
;

CREATE VIEW D_ANSTWR_PTN_VARS_LINK AS 
SELECT 
  TAB_A.*,
  TAB_B.VARS_NAME,
  [%CONCAT_HEAD/%]TAB_A.VARS_LINK_ID[%CONCAT_MID/%]':'[%CONCAT_MID/%]TAB_B.VARS_NAME[%CONCAT_TAIL/%] AS VARS_PULLDOWN, 
  [%CONCAT_HEAD/%]TAB_A.PATTERN_ID[%CONCAT_MID/%]':'[%CONCAT_MID/%]TAB_C.PATTERN_NAME[%CONCAT_MID/%]':'[%CONCAT_MID/%]TAB_A.VARS_LINK_ID[%CONCAT_MID/%]':'[%CONCAT_MID/%]TAB_B.VARS_NAME[%CONCAT_TAIL/%] AS PTN_VAR_PULLDOWN
FROM 
  (( B_ANSTWR_PTN_VARS_LINK       TAB_A
    LEFT JOIN   B_ANSTWR_VARS     TAB_B ON TAB_B.VARS_ID = TAB_A.VARS_ID )
      LEFT JOIN E_ANSTWR_PATTERN  TAB_C ON TAB_C.PATTERN_ID = TAB_A.PATTERN_ID )
;

CREATE VIEW D_ANSTWR_PTN_VARS_LINK_JNL AS 
SELECT 
  TAB_A.*,
  TAB_B.VARS_NAME,
  [%CONCAT_HEAD/%]TAB_A.VARS_LINK_ID[%CONCAT_MID/%]':'[%CONCAT_MID/%]TAB_B.VARS_NAME[%CONCAT_TAIL/%] AS VARS_PULLDOWN, 
  [%CONCAT_HEAD/%]TAB_A.PATTERN_ID[%CONCAT_MID/%]':'[%CONCAT_MID/%]TAB_C.PATTERN_NAME[%CONCAT_MID/%]':'[%CONCAT_MID/%]TAB_A.VARS_LINK_ID[%CONCAT_MID/%]':'[%CONCAT_MID/%]TAB_B.VARS_NAME[%CONCAT_TAIL/%] AS PTN_VAR_PULLDOWN
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
  [%CONCAT_HEAD/%]TAB_C.VARS_NAME[%CONCAT_MID/%]'.'[%CONCAT_MID/%]TAB_A.NESTEDMEM_COL_CMB_ID[%CONCAT_MID/%]':'[%CONCAT_MID/%]TAB_A.COL_COMBINATION_MEMBER_ALIAS[%CONCAT_TAIL/%] VAR_MEMBER_PULLDOWN
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
  [%CONCAT_HEAD/%]TAB_C.VARS_NAME[%CONCAT_MID/%]'.'[%CONCAT_MID/%]TAB_A.NESTEDMEM_COL_CMB_ID[%CONCAT_MID/%]':'[%CONCAT_MID/%]TAB_A.COL_COMBINATION_MEMBER_ALIAS[%CONCAT_TAIL/%] VAR_MEMBER_PULLDOWN
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
  [%CONCAT_HEAD/%]TAB_B.ROLE_PACKAGE_ID[%CONCAT_MID/%]':'[%CONCAT_MID/%]TAB_B.ROLE_PACKAGE_NAME[%CONCAT_MID/%]':'[%CONCAT_MID/%]TAB_A.ROLE_ID[%CONCAT_MID/%]':'[%CONCAT_MID/%]TAB_A.ROLE_NAME[%CONCAT_TAIL/%] AS ROLE_PACKAGE_PULLDOWN
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
  [%CONCAT_HEAD/%]TAB_B.ROLE_PACKAGE_ID[%CONCAT_MID/%]':'[%CONCAT_MID/%]TAB_B.ROLE_PACKAGE_NAME[%CONCAT_MID/%]':'[%CONCAT_MID/%]TAB_A.ROLE_ID[%CONCAT_MID/%]':'[%CONCAT_MID/%]TAB_A.ROLE_NAME[%CONCAT_TAIL/%] AS ROLE_PACKAGE_PULLDOWN
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

