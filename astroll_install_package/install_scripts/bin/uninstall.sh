#!/bin/bash
#   Copyright 2019 NEC Corporation
#
#   Licensed under the Apache License, Version 2.0 (the "License");
#   you may not use this file except in compliance with the License.
#   You may obtain a copy of the License at
#
#       http://www.apache.org/licenses/LICENSE-2.0
#
#   Unless required by applicable law or agreed to in writing, software
#   distributed under the License is distributed on an "AS IS" BASIS,
#   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
#   See the License for the specific language governing permissions and
#   limitations under the License.
#
############################################################
#
# 【概要】
#    astrollをアンインストールする
#
############################################################

log 'INFO : -----MODE[UNINSTALL] START-----'

#----------------------------------------
#uninstall開始
#----------------------------------------

#/tmpにコピーしたastroll_answers.txtを削除
rm -f "$COPY_ANSWER_FILE" 2>> "$LOG_FILE"

PROCESS_CNT=1
PROCESS_TOTAL_CNT=14

#----------------------------------------
#(1/14) Apache(httpd)をストップする
#----------------------------------------
log "INFO : `printf %02d $PROCESS_CNT`/$PROCESS_TOTAL_CNT Stop Apache.(httpd)"

#SentOS6の場合
if [ ${ITA_OS} = 'RHEL6' ]; then
    #Apache停止
    /etc/init.d/httpd stop 2>> "$LOG_FILE" | tee -a "$LOG_FILE"

    #Apacheが停止しているか確認
    APACHE_ACTIVE=`/etc/init.d/httpd status | grep "stopped" -c`

    if [ ${#APACHE_ACTIVE} -eq 0 ]; then
    log "WARNING : Failed to restart Apache(httpd) service."
    fi

#SentOS7の場合
else
    #Apache停止
    systemctl stop httpd.service 2>> "$LOG_FILE"

    #Apacheが停止しているか確認
    APACHE_ACTIVE=`systemctl status httpd | grep "dead"`
    if [ ${#APACHE_ACTIVE} -eq 0 ]; then
        log "WARNING : Failed to stop Apache(httpd) service."
    fi
fi

#プロセスカウント+1
PROCESS_CNT=$((PROCESS_CNT+1))

#----------------------------------------
#(2/14) /etc/hostsからastroll-it-automationのエントリーを削除する
#----------------------------------------
log "INFO : `printf %02d $PROCESS_CNT`/$PROCESS_TOTAL_CNT Delete entry."

#hostsファイルが存在するか
if [ -e /etc/hosts ]; then

    #hostsファイルにastroll-it-automationのエントリーが存在するか
    HOSTS_ITA=`find /etc/hosts -type f | xargs grep "astroll-it-automation"`
    if [ "${#HOSTS_ITA}" -eq 0 ]; then
        log 'WARNING : There is no entry in hosts.'
    else
        #astroll-it-automationのエントリーを削除する
        sed -i -e "/astroll-it-automation/d" /etc/hosts 2>> "$LOG_FILE"
        
    fi
else
    log 'WARNING : hosts does not exist.'
fi

#hostsファイルからastroll-it-automationのエントリーが削除されているか確認
HOSTS_CHECK=`find /etc/hosts -type f | xargs grep "astroll-it-automation"`
if [ "${#HOSTS_CHECK}" -ne 0 ]; then
    log "WARNING : hosts processing failed."
fi

#プロセスカウント+1
PROCESS_CNT=$((PROCESS_CNT+1))

#----------------------------------------
#(3/14) サーバ証明書を削除する
#----------------------------------------
log "INFO : `printf %02d $PROCESS_CNT`/$PROCESS_TOTAL_CNT Delete self-signed certificate."

#astroll-it-automation.crtが存在するか
if [ -e /etc/pki/tls/certs/astroll-it-automation.crt ]; then
    #astroll-it-automation.crtを削除する
    rm -f /etc/pki/tls/certs/astroll-it-automation.crt 2>> "$LOG_FILE"
else
    log 'WARNING : astroll-it-automation.crt does not exist.'
fi

#astroll-it-automation.keyが存在するか
if [ -e /etc/pki/tls/certs/astroll-it-automation.key ]; then
    #astroll-it-automation.keyを削除する
    rm -f /etc/pki/tls/certs/astroll-it-automation.key 2>> "$LOG_FILE"
else
    log 'WARNING : astroll-it-automation.key does not exist.'
fi


#サーバ証明書が削除されているか確認
if [ -e /etc/pki/tls/certs/astroll-it-automation.crt ]; then
    log 'WARNING : Failed to delete astroll-it-automation.crt.'
fi

if [ -e /etc/pki/tls/certs/astroll-it-automation.key ]; then
    log 'WARNING : Failed to delete astroll-it-automation.key.'
fi


#プロセスカウント+1
PROCESS_CNT=$((PROCESS_CNT+1))

#----------------------------------------
#(4/14) php.iniファイルを削除する
#----------------------------------------
log "INFO : `printf %02d $PROCESS_CNT`/$PROCESS_TOTAL_CNT Delete php.ini."

#php.iniが存在するか確認
if [ -e /etc/php.ini ]; then
    #php.ini_originalが存在するか確認
    if [ -e /etc/php.ini_original ]; then
        #php.ini削除
        rm -f /etc/php.ini 2>> "$LOG_FILE"
        #php.ini_originalのファイル名をインストール前のファイル名に戻す
        mv /etc/php.ini_original /etc/php.ini 2>> "$LOG_FILE"
    else
        log 'WARNING : Failed to place /etc/php.ini_original.'
    fi
else
    log 'WARNING : Failed to place /etc/php.ini.'
fi


#プロセスカウント+1
PROCESS_CNT=$((PROCESS_CNT+1))

#----------------------------------------
#(5/14) PHPセッションファイルディレクトリを削除する
#----------------------------------------
log "INFO : `printf %02d $PROCESS_CNT`/$PROCESS_TOTAL_CNT Delete php sessions directory."

if [ -e "$ITA_DIRECTORY"/ita_sessions ]; then
    #PHPセッションファイルディレクトリを削除する
    rm -rf "$ITA_DIRECTORY"/ita_sessions 2>> "$LOG_FILE"
else
    log 'WARNING : ita_sessions does not exist.'
fi

#PHPセッションファイルディレクトリが削除されているか確認
if [ -e "$ITA_DIRECTORY"/ita_sessions ]; then
    log 'WARNING : Failed to delete ita_sessions.'
fi


#プロセスカウント+1
PROCESS_CNT=$((PROCESS_CNT+1))

#----------------------------------------
#(6/14) astrollバックヤードスクリプトを全停止し常駐起動設定を解除する
#----------------------------------------

#SentOS6の場合
if [ ${ITA_OS} = 'RHEL6' ]; then
    BACK_PROCESS=`ls /etc/init.d/ky_* 2>> "$LOG_FILE" | wc -l`
    log "INFO : `printf %02d $PROCESS_CNT`/$PROCESS_TOTAL_CNT Delete backyard script.Process count=[$BACK_PROCESS]"
    for var in `ls /etc/init.d/ky_* 2>> "$LOG_FILE"`; do
        FILE_NAME=`basename ${var}`
        
        #バックヤードスクリプトを全停止
        BACKYARD_ACTIVE=`/etc/init.d/"$FILE_NAME" stop | grep "OK" -c`
        #停止しているか確認
        if [ ${#BACKYARD_ACTIVE} -eq 0 ]; then
            log "WARNING : ${FILE_NAME} is not stopped."
        fi
        
        #常駐起動設定を解除
        chkconfig "$FILE_NAME" off; 2>> "$LOG_FILE"
        #解除されているか確認
        STARTUP_SET=`chkconfig --list "$FILE_NAME" | grep ":on" -c`
        if [ "$STARTUP_SET" -ne 0 ]; then
            log "WARNING : ${FILE_NAME} is not stopped."
        fi
        
        #解除終了ログを出す
        log "INFO :       [$FILE_NAME] stopped."

    done 2>> "$LOG_FILE"

#SentOS7の場合
else
    BACK_PROCESS=`ls /usr/lib/systemd/system/ky_* 2>> "$LOG_FILE" | wc -l`
    log "INFO : `printf %02d $PROCESS_CNT`/$PROCESS_TOTAL_CNT Delete backyard script.Process count=[$BACK_PROCESS]"
    for var in `ls /usr/lib/systemd/system/ky_* 2>> "$LOG_FILE"`; do
        FILE_NAME=`basename ${var}`

            #バックヤードスクリプトを全停止
            systemctl stop "$FILE_NAME"; 2>> "$LOG_FILE"
            #停止しているか確認
            BACKYARD_ACTIVE=`systemctl status "$FILE_NAME" | grep "running"`
            if [ ${#BACKYARD_ACTIVE} -ne 0 ]; then
                log "WARNING : ${FILE_NAME} is not stopped."
            fi

            #常駐起動設定を解除
            systemctl disable "$FILE_NAME"; 2>> "$LOG_FILE"
            #解除されているか確認
            STARTUP_SET=`systemctl is-enabled "$FILE_NAME"`
            if [ "$STARTUP_SET" != "disabled" ]; then
                log "WARNING : ${FILE_NAME} is not stopped."
            fi
            
            #解除終了ログを出す
            log "INFO :       [$FILE_NAME] stopped."
            
    done 2>> "$LOG_FILE"
    systemctl daemon-reload 2>> "$LOG_FILE"
    
fi



#プロセスカウント+1
PROCESS_CNT=$((PROCESS_CNT+1))

#----------------------------------------
#(7/14) astrollバックヤードスクリプトの起動設定ファイルを削除する
#----------------------------------------
log "INFO : `printf %02d $PROCESS_CNT`/$PROCESS_TOTAL_CNT Delete start-up configuration file."

if [ ${ITA_OS} = 'RHEL6' ]; then
    for var in `ls /etc/init.d/ky_* 2>> "$LOG_FILE"`; do
        FILE_NAME=`basename ${var}`
        #設定ファイル削除
        rm -f /etc/init.d/ky_* 2>> "$LOG_FILE"
        
        #削除されているか確認
        if [ -e /etc/init.d/"$FILE_NAME" ]; then
            log "WARNING : Failure to delete ${FILE_NAME}"
        fi
    done
else
    for var in `ls /usr/lib/systemd/system/ky_* 2>> "$LOG_FILE"`; do
        FILE_NAME=`basename ${var}`
        #設定ファイル削除
        rm -f /usr/lib/systemd/system/ky_* 2>> "$LOG_FILE"
        
        #削除されているか確認
        if [ -e /usr/lib/systemd/system/"$FILE_NAME" ]; then
            log "WARNING : Failure to delete ${FILE_NAME}"
        fi
    done
fi

#プロセスカウント+1
PROCESS_CNT=$((PROCESS_CNT+1))

#----------------------------------------
#(8/14) astrollのMySQL登録情報(テーブル構成、初期データ等)を削除する
#----------------------------------------
log "INFO : `printf %02d $PROCESS_CNT`/$PROCESS_TOTAL_CNT Delete MySQL registration information."

#sqlファイルが存在するか確認
if ! test -e "$SQL_DIR/drop_db_and_user_for_MySQL.sql" ; then
    log 'ERROR : drop_db_and_user_for_MySQL.sql does not be found.'
else
    #SQLファイルを/tmpにコピー
    cp "$SQL_DIR/drop_db_and_user_for_MySQL.sql" /tmp/ 2>> "$LOG_FILE"
    #SQLファイルを編集
    sed -i -e "s/DB_NAME/$DB_NAME/g" /tmp/drop_db_and_user_for_MySQL.sql 2>> "$LOG_FILE"
    sed -i -e "s/DB_USERNAME/$DB_USERNAME/g" /tmp/drop_db_and_user_for_MySQL.sql 2>> "$LOG_FILE"

    #rootパスワード、DB名、DBユーザ名が間違えていなければ削除処理実行
    DB_NAME_CHK=`mysql -u root -p"$DB_ROOT_PASSWORD" -e "SHOW DATABASES LIKE '$DB_NAME';" 2>> "$LOG_FILE"`
    DB_USERNAME_CHK=`mysql -u root -p"$DB_ROOT_PASSWORD" -e "SELECT user, host FROM mysql.user where user = '$DB_USERNAME';" 2>> "$LOG_FILE"`

    if [ -z "$DB_NAME_CHK" -o -z "$DB_USERNAME_CHK" ] ; then
        log 'ERROR : Should be set correct [db_root_password][db_name][db_username].'
    else
        mysql -u root -p"$DB_ROOT_PASSWORD" < /tmp/drop_db_and_user_for_MySQL.sql 2>> "$LOG_FILE"
    fi

    rm -f /tmp/drop_db_and_user_for_MySQL.sql
fi


#プロセスカウント+1
PROCESS_CNT=$((PROCESS_CNT+1))

#----------------------------------------
#(9/14) astrollルートディレクトリを削除する
#----------------------------------------
log "INFO : `printf %02d $PROCESS_CNT`/$PROCESS_TOTAL_CNT Delete ita-root directory."

if [ -e "$ITA_DIRECTORY"/ita-root ]; then
    #astrollルートディレクトリを削除する
    rm -rf "$ITA_DIRECTORY"/ita-root 2>> "$LOG_FILE"
else
    log 'WARNING : root directory does not exist.'
fi

#astrollルートディレクトリが削除されているか確認
if [ -e "$ITA_DIRECTORY"/ita-root ]; then
    log 'Failed to delete ita-root.'
fi


#プロセスカウント+1
PROCESS_CNT=$((PROCESS_CNT+1))

#----------------------------------------
#(10/14) astrollのデータリレイストレージを削除する
#----------------------------------------
log "INFO : `printf %02d $PROCESS_CNT`/$PROCESS_TOTAL_CNT Delete data relay storage."

if [ -e "$ITA_DIRECTORY"/data_relay_storage ]; then
    #astrollのデータリレイストレージを削除する
    rm -rf "$ITA_DIRECTORY"/data_relay_storage 2>> "$LOG_FILE"
else
    log 'WARNING : Data relay storage does not exist.'
fi

#astrollルートディレクトリが削除されているか確認
if [ -e "$ITA_DIRECTORY"/data_relay_storage ]; then
    log 'WARNING : Failed to delete data_relay_storage.'
fi


#プロセスカウント+1
PROCESS_CNT=$((PROCESS_CNT+1))

#----------------------------------------
#(11/14) astrollの環境ファイルを削除する
#----------------------------------------
log "INFO : `printf %02d $PROCESS_CNT`/$PROCESS_TOTAL_CNT Delete environment file."

if [ -L /etc/sysconfig/ita_env ]; then
    #astrollの環境ファイルを削除する
    rm -f /etc/sysconfig/ita_env 2>> "$LOG_FILE"
else
    log 'WARNING : environment file does not exist.'
fi

#astrollの環境ファイルが削除されているか確認
if [ -L /etc/sysconfig/ita_env ]; then
    log 'WARNING : Failed to delete ita_env.'
fi


#プロセスカウント+1
PROCESS_CNT=$((PROCESS_CNT+1))

#----------------------------------------
#(12/14) astroll用のhttpdコンフィグファイルを削除する
#----------------------------------------
log "INFO : `printf %02d $PROCESS_CNT`/$PROCESS_TOTAL_CNT Delete httpd config file."

#vhosts_astroll-it-automation.confが存在するか確認
if [ -e /etc/httpd/conf.d/vhosts_astroll-it-automation.conf ]; then
    #astroll用のhttpdコンフィグファイルを削除する
    rm -f /etc/httpd/conf.d/vhosts_astroll-it-automation.conf 2>> "$LOG_FILE"
else
    log 'WARNING : httpd config file does not exist.'
fi


#RHEL6の場合は以下を実行
if [ "$ITA_OS" = "RHEL6" ]; then
    #ssl.confが存在するか確認
    if [ -e /etc/httpd/conf.d/ssl.conf ]; then
        #ssl.conf_originalが存在するか確認
        if [ -e /etc/httpd/conf.d/ssl.conf_original ]; then
            #ssl.confを削除
            rm -f /etc/httpd/conf.d/ssl.conf 2>> "$LOG_FILE"
            #ssl.conf_originalを削除
            mv /etc/httpd/conf.d/ssl.conf_original /etc/httpd/conf.d/ssl.conf 2>> "$LOG_FILE"
        else
            log 'WARNING : Failed to place /etc/httpd/conf.d/ssl.conf_original.'
        fi
    else
        log 'WARNING : Failed to place /etc/httpd/conf.d/ssl.conf.'
    fi
fi

#vhosts_astroll-it-automation.confが削除されているか確認
if [ -e /etc/httpd/conf.d/vhosts_astroll-it-automation.conf ]; then
    log 'WARNING : Failed to delete vhosts_astroll-it-automation.conf.'
fi


#プロセスカウント+1
PROCESS_CNT=$((PROCESS_CNT+1))

#----------------------------------------
#(13/14) astroll用のcrontab設定を削除する
#----------------------------------------
log "INFO : `printf %02d $PROCESS_CNT`/$PROCESS_TOTAL_CNT Delete crontab settings."

if [ -e /var/spool/cron/root ]; then
    #astroll用のcrontab設定を削除する
    sed -i -e "/\/ky_/d" /var/spool/cron/root 2>> "$LOG_FILE"
else
    log 'WARNING : crontab settings does not exist.'
fi

#crontab設定が削除されているか確認
CRONTAB_ITA=`find /var/spool/cron/root -type f | xargs grep "ky_*"`
if [ "${#CRONTAB_ITA}" -ne 0 ]; then
    log 'WARNING : crontab processing failed.'
fi

#プロセスカウント+1
PROCESS_CNT=$((PROCESS_CNT+1))

#----------------------------------------
#(14/14) astrollが配置されているディレクトリを削除
#----------------------------------------
log "INFO : `printf %02d $PROCESS_CNT`/$PROCESS_TOTAL_CNT Delete the directory[$ITA_DIRECTORY]."
if test -d "$ITA_DIRECTORY" ; then
    rm -rf "$ITA_DIRECTORY" 2>> "$LOG_FILE"
    if test -d "$ITA_DIRECTORY" ; then
        log 'WARNING : Failed to delete directory.'
    fi
else
    log 'WARNING : There is no directory.'
fi

#astrollが配置されているディレクトリが削除されているか確認
if test -d "$ITA_DIRECTORY" ; then
    log "WARNING : Failed to delete $ITA_DIRECTORY."
fi

############################################################
#uninstall処理終了
############################################################
log "INFO : Uninstallation complete!"

exit