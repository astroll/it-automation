<?php
////////////////////////////////////////////////////////////////////////////////////
//  �y�T�v�z
//     common_functions.php (�֐�)
//     DSC RESTAPI ���ʃ��W���[��
//
//    F0001 checkAuthorizationInfo
//    F0002 checkRequestHeaderForAuth
//    F0003 getFileNameAndPath
//    F0004 outputLog
//
//  �y���l�z
//     6/8 OpenSSL�ɂ��ITA/RESTAPI�Ԃ̈Í��F�؂��������Ȃ����߁A�Í����̐���������PHP��crypt�֐��ő�ւ���B
//     
////////////////////////////////////////////////////////////////////////////////////
    // �������ʃX�e�[�^�X
    define("DSC_SUCCESS"         ,"0");
    define("DSC_ERR_HTTP_REQ"    ,"1000");  // HTTP�p�����[�^�ُ�
    define("DSC_ERR_DIR"         ,"1001");  // DSC�f�B���N�g���̔F���G���[
    define("DSC_ERR_HTTP_HEDER"  ,"1002");  // HTTP�w�b�_�[�ɕK�v�ȏ�񂪂Ȃ�
    define("DSC_ERR_AUTH"        ,"1003");  // �A�N�Z�X�L�[�F�؃G���[
    define("DSC_ERR_CONF"        ,"1004");  // DSC �R���t�B�O���[�V����(���s)�G���[
    define("DSC_ERR_STATUS"      ,"1005");  // DSC ���s�X�e�[�^�X�G���[
    define("DSC_ERR_TEST"        ,"1006");  // DSC �e�X�g(�m�F)�G���[
    define("DSC_ERR_STOP"        ,"1007");  // DSC ���s�X�e�[�^�X�G���[
    define("DSC_ERR_TAR_DEL"     ,"2000");  // Collect Command�ō쐬����ZIP�t�@�C���̍폜���s����rm�R�}���h�̖߂�l�ւ̉��Z�l

    ////////////////////////////////////////////////////////////////////////////////
    // F0001
    // �������e
    //   RESTAPI �R�[���̃A�N�Z�X�L�[�F�؂��s��
    // �p�����[�^
    //   $ina_ReqHeaderData:        HTTP�w�b�_�[���
    //   $in_ResultStatusCode:      HTTP���X�|���X�Œʒm���郌�X�|���X�R�[�h (�ُ펞�̂�)
    //   $in_Exception:             HTTP���X�|���X�Œʒm����G���[���b�Z�[�W (�ُ펞�̂�)
    //
    // ���l
    //   $root_dir_path:            C:\inetpub\wwwroot
    //   
    // �߂�l
    //   true: ���� FALSE:�ُ�
    ////////////////////////////////////////////////////////////////////////////////
    function checkAuthorizationInfo( $ina_ReqHeaderData, &$in_ResultStatusCode, &$in_Exception )
    {
        global   $root_dir_path;

        $strCRLF = "\r\n";

        $in_ResultStatusCode = 0;
        $in_Exception        = "";

        // ���N�G�X�g�ő����Ă������
        $strHeaderAuthorization     = $ina_ReqHeaderData['Authorization'];
        $strHeaderDate              = $ina_ReqHeaderData['Date'];
        $strRequestURIOnRest        = $_SERVER['PHP_SELF'];
        
        // �T�[�o�[��ɂ���F�؏��擾
        $strConfFileOfAccessKeyIdOnRest     = "\\confs\\restapiconfs\\dsc_driver\\accesskey.txt";

        $strAccessKeyIdOnRest               = file_get_contents( $root_dir_path . $strConfFileOfAccessKeyIdOnRest);

        $strAccessKeyIdOnRest               = ky_decrypt($strAccessKeyIdOnRest);

        $strConfFileOfSecretAccessKeyOnRest = "\\confs\\restapiconfs\\dsc_driver\\secret_accesskey.txt";

        $strSecretAccessKeyOnRest           = file_get_contents( $root_dir_path . $strConfFileOfSecretAccessKeyOnRest);

        $strSecretAccessKeyOnRest           = ky_decrypt($strSecretAccessKeyOnRest);

        $aryTempData = explode("SharedKeyLite {$strAccessKeyIdOnRest}:", $strHeaderAuthorization);

        if( count($aryTempData) != 2 )
        {
            $in_ResultStatusCode = 401;
            $in_Exception        = 'Authorization infomation format error';
            return FALSE;
        }

        $tmpStrStringToSignOnRest = $strHeaderDate . $strCRLF . $strRequestURIOnRest;
        
        // PHP crypt�ɂ���֏��� 6/6 saito
        $tmpStrSignatureOnRest = crypt( $tmpStrStringToSignOnRest, $strSecretAccessKeyOnRest);
        
        if( $tmpStrSignatureOnRest!==$aryTempData[1] )
        {
            $in_ResultStatusCode = 401;
            $in_Exception        = 'Authorization infomation is not correct';
            
            unset($strSecretAccessKeyOnRest);
            unset($tmpStrStringToSignOnRest);
            unset($tmpStrSignatureOnRest);
            return FALSE;
        }

        unset($strSecretAccessKeyOnRest);
        unset($tmpStrStringToSignOnRest);
        unset($tmpStrSignatureOnRest);

        return true;
    }
    ////////////////////////////////////////////////////////////////////////////////
    // F0002
    // �������e
    //   HTTP�w�b�_�[�ɕK�v�ȏ�񂪐ݒ肳��Ă��邩�m�F
    // �p�����[�^
    //   $ina_ReqHeaderData:     HTTP�w�b�_�[�����i�[
    //   $in_ResultStatusCode:   HTTP���X�|���X�Œʒm���郌�X�|���X�R�[�h (�ُ펞�̂�)
    //   $in_Exception:          HTTP���X�|���X�Œʒm����G���[�ڍ�       (�ُ펞�̂�)
    //
    // �߂�l
    //   true: ���� FALSE:�ُ�
    ////////////////////////////////////////////////////////////////////////////////
    function checkRequestHeaderForAuth(&$ina_ReqHeaderData,&$in_ResultStatusCode,&$in_Exception)
    {
        $in_ResultStatusCode = 0;
        $in_Exception        = "";

        //���N�G�X�g�w�b�_�擾
        $ina_ReqHeaderData = getallheaders();

        if( $ina_ReqHeaderData === FALSE )
        {
            $in_ResultStatusCode = 400;
            $in_Exception        = 'Request header unknown error';
            return FALSE;
        }
        //----http(s)���N�G�X�g�w�b�_�ɏ���̍��ڂ����邩���`�F�b�N
        if( array_key_exists('Host', $ina_ReqHeaderData) !== true )
        {
            $in_ResultStatusCode = 400;
            $in_Exception        = 'Required request header item[Host] is not exists';
            return FALSE;
        }
        
        if( array_key_exists('Content-Type', $ina_ReqHeaderData) !== true )
        {
            $in_ResultStatusCode = 400;
            $in_Exception        = 'Required request header item[Content-Type] is not exists';
            return FALSE;
        }

        if( array_key_exists('X-Umf-Api-Version', $ina_ReqHeaderData) !== true )
        {
            $in_ResultStatusCode = 400;
            $in_Exception        = 'Required request header item[X-UMF-API-Version] is not exists';
            return FALSE;
        }
        
        if( array_key_exists('Date', $ina_ReqHeaderData) !== true )
        {
            $in_ResultStatusCode = 400;
            $in_Exception        = 'Required request header item[Date] is not exists';
            return FALSE;
        }
        
        if( array_key_exists('Authorization', $ina_ReqHeaderData) !== true )
        {
            $in_ResultStatusCode = 400;
            $in_Exception        = 'Required request header item[Authorization] is not exists';
            return FALSE;
        }
        return true;
    }
    
    // �Ǘ����O�o�̓t�@���N�V����
    function DebugLogPrint($p1,$p2,$p3)
    {
        global $logfile;

        $tmpVarTimeStamp = time();
        $logtime = date("Y/m/d H:i:s",$tmpVarTimeStamp);
        $filepointer=fopen(  $logfile, "a");
        flock($filepointer, LOCK_EX);
        fputs($filepointer, "[" . $logtime . "]" . $p1 . ":" . $p2 . ":" . $p3 . "\n" );
        flock($filepointer, LOCK_UN);
        fclose($filepointer);
        unset($tmpVarTimeStamp);
    }
    // �Ȉ�IPv4 Preg_match
    function validateIP($Ipaddress){
        return inet_pton($Ipaddress) !== FALSE;
    }
    ////////////////////////////////////////////////////////////////////////////////
    // F0003
    // �y�������e�z
    //   �t�@�C�����݃`�F�b�N
    // �y�p�����[�^�z
    // $strSearchDirPath
    // $strFilePrefix=''
    // $strFilePostFix=''
    //
    // �y�߂�l�z
    //   $aryFileList
    ////////////////////////////////////////////////////////////////////////////////
    function getFileNameAndPath($strSearchDirPath, $strFilePrefix='', $strFilePostFix=''){
        
        $boolExecuteContinue = true;
        $aryFileList = array();
        $target_file_path = "";
        if( is_string($strSearchDirPath)===FALSE )
        {
            $boolExecuteContinue = FALSE;
        }
        if( is_string($strFilePrefix)===FALSE )
        {
            $boolExecuteContinue = FALSE;
        }
        if( is_string($strFilePostFix)===FALSE )
        {
            $boolExecuteContinue = FALSE;
        }
        if( $boolExecuteContinue===true )
        {
            $aryFile = scandir($strSearchDirPath);
            foreach($aryFile as $strFileObjectName){
                if( 0 < strlen($strFilePrefix) )
                {
                    if( strpos($strFileObjectName,$strFilePrefix)!==0 )
                    {
                        //----������Ȃ������A�܂��́A�擪�ł͂Ȃ�����
                        continue;
                        //������Ȃ������A�܂��́A�擪�ł͂Ȃ�����----
                    }
                }
                if( 0 < strlen($strFilePostFix) )
                {
                    if( strpos(strrev($strFileObjectName),strrev($strFilePostFix))!==0 )
                    {
                        //----������Ȃ������A�܂��́A�����ł͂Ȃ�����
                        continue;
                        //������Ȃ������A�܂��́A�����ł͂Ȃ�����----
                    }
                }
                $strCheckPath = $strSearchDirPath .DIRECTORY_SEPARATOR. $strFileObjectName;
                if ( is_file($strCheckPath)===true )
                {
                    $aryFileList[] = $strCheckPath;
                }
            }
        }
        return $aryFileList;
    }
    
    ////////////////////////////////////////////////////////////////////////////////
    // F0004
    // �y�������e�z
    //   RESTAPI ���O�o��
    //   Windows����
    // �y�p�����[�^�z
    //      * @param    string    $msg    �o�͂��郁�b�Z�[�W
    //
    // �y�߂�l�z
    //
    // �y���l�z
    //   RESTAPI ���O�p�X C:\inetpub\wwwroot\logs\restapilogs\dsc_driver\
    //
    ////////////////////////////////////////////////////////////////////////////////
    function outputLog($prefix, $msg)
    {
        $dt = '[' . date('Y/m/d H:i:s') . ']';
        $msg = $dt . $msg . "\r\n";
        $filePath = ROOT_DIR_PATH . LOG_DIR . $prefix . date('Ymd') . '.log';
        error_log($msg, 3, $filePath);
    }
    
?>
