#//////////////////////////////////////////////////////////////////////
#//   
#//  �y�T�v�z
#//   Start-DSCconfigration���s��A�Ώۃm�[�h�ł̍\���K�p�������z��ǂ���s��ꂽ���m�F����
#//   Test-DSCconfiguration���s ->�^�[�Q�b�g�m�[�h�ELCM���̍\�����ƑΏۃm�[�h�̍\�����ɍ������邩�m�F 
#//
#//  �y���̓p�����[�^�z
#//      
#//    $tagtname:     �^�[�Q�b�g�m�[�h(IP)
#//    $username:     �^�[�Q�b�g�m�[�h�ڑ����̔F�؃��[�U�[���i�h���C����\���[�U�[���j
#//    $passwd:       �^�[�Q�b�g�m�[�h�ڑ����̔F�؃p�X���[�h
#//    $config_file:  Configration�t�@�C���̃t���p�X�l�[��
#//
#//  �y�ԋp�p�����[�^($return_var)�z
#//     0�F����I��  Start-DSCconfiguration�ɂ��\���K�p������ɏ������ꂽ���Ƃ��m�F�ł���
#//
#//    35: �ُ�I��  Cim�Z�b�V�����쐬�O�����̃^�[�Q�b�g�̔F�ؗp�p�X���[�h�̈Í��������Ɏ��s
#//    36: �ُ�I��  Credential �I�u�W�F�N�g�̃C���X�^���X���������Ɏ��s
#//    37: �ُ�I��  Cim�Z�b�V�����̐��������Ɏ��s(������͂̉\��)
#//    38: �ُ�I��  TEST-DSCconfiguration�i�\���K�p��`�F�b�N�j�Ɏ��s
#//                          
#//  �y���O�z     
#//    logs:    PowerShell�̎��s���O(�W���o��/�W���G���[�o�́j��z��Ƃ���exec�i$arry_out�j�֕Ԃ�
#//        
#//////////////////////////////////////////////////////////////////////

# �p�����[�^���
Param(
    [Parameter(Mandatory)][string]$tagtname,
    [string]$username,
    [string]$passwd,
    [string]$config_file
)

#Write-Output "ITA Message: $tagtname �\����Ԃ̃e�X�g���J�n���܂��B"    #ITA Messag
$ec = 0

Try{
    # �N���A�e�L�X�g�p�X���[�h���Z�L�����e�B�ŕی삳�ꂽ������ɕϊ�
    $test_sec_str = ConvertTo-SecureString -AsPlainText -Force -String $passwd -Verbose -ErrorAction Stop

}Catch{
    Write-Output $error[0] 
    # Write-Output "ITA Message: $tagtname Cim�Z�b�V���������̃p�X���[�h�̈Í����Ɏ��s���܂����B"    #ITA Message
    $ec = 35
    EXIT $ec
    
}Finally{}

Try{
    # Credential �I�u�W�F�N�g�����i�F�ؗp���[�U�[��/�p�X���[�h(�Í�����)�j
    $PSobj = New-Object System.Management.Automation.PsCredential($username, $test_sec_str) -Verbose -ErrorAction Stop

}Catch{
     
    #Write-Output "ITA Message: $tagtname Credential �I�u�W�F�N�g�����F��O�������������܂����B"   #ITA message
    Write-Output $error[0]
    #RestAPI�ʒm�G���[�R�[�h����: "Credential �I�u�W�F�N�g�����G���[" 
    $ec = 36
    EXIT $ec
    
}Finally{}

Try{
    # Cim�Z�b�V��������
    $cimsession = New-CimSession -SkipTestConnection -ComputerName $tagtname -Credential $PSobj -ErrorAction Stop 
    
}Catch{
     
    #Write-Output "ITA Message: $tagtname Cim�Z�b�V���������F��O�������������܂����B" #ITA message
    Write-Output $error[0]
    #RestAPI�ʒm�G���[�R�[�h ����: "Cim�Z�b�V�����������s   
    $ec = 37
    EXIT $ec
    
}Finally{}

#################################################
# �\�����̃^�[�Q�b�g�m�[�h���\�� �̓��ꐫ�m�F #
#################################################
$Test_result = Test-DscConfiguration -Verbose -CimSession $cimsession

# Cim�Z�b�V�������
Remove-CimSession -CimSession $cimsession

if( $Test_result -eq $true ){
    Write-Output $Test_result
    #Write-Output "ITA Message: $tagtname �\�����ƃ^�[�Q�b�g�m�[�h�̍\���͈�v���Ă��܂��B" #ITA message
    exit $ec # 0
}
elseif( $Test_result -eq $false ){
    Write-Output $Test_result
    #Write-Output "ITA Message: $tagtname �\�����ƃ^�[�Q�b�g�m�[�h�̍\���ɈႢ������܂��B" #ITA message
    $ec = 39
    exit $ec
}