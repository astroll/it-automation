#!/usr/bin/python
# -*- coding: utf-8 -*-
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

######################################################################
##
##  【概要】
##      pioneer用 対話ファイル実行スクリプト
##
##  【特記事項】
##      <<引数>>
##       username:           ユーザー名
##       protocol:           プロトコル
##       inventory_hostname: 接続先ホスト(IP/ホスト名)
##       exec_file:          対話ファイル名
##       grep_shell_dir:     文字列検索用デフォルトshell ルートディレトクリ
##       log_file_dir:       プライベートログ出力先ディレトクリ
##       ssh_key_file:       SSH秘密鍵ファイル
##       extra_args:         ssh/telnet接続時の追加パラメータ
##      <<返却値>>
##       なし
##
######################################################################
DOCUMENTATION = '''
---
module: pioneer_module
shotr_description: hoge
description:
  - hogehoge
option:
  username:
    required: false(default root)
    descriptio:
      - login user name
  protocol:
    required: false(default ssh)
    descriptio:
      - connection protocol(ssh or telnet)
  inventory_hostname:
    required: true
    description:
      - ssh(or telnet) target
  exec_file:
    required: true
    description:
      - expect/sendline list by yaml
  grep_shell_dir:
    required: true
    description:
      - default grep shell path
  extra_args:
    required: true
    description:
      - ssh/telnet extra args
  ssh_key_file:
    required: true
    description:
      - ssh key file
  log_file_dir:
    required: true
    description:
      - private log path
author: Hiroyuki Seike
'''

import yaml
import pexpect
import sys
import datetime
import signal
import subprocess
import os
import exceptions
import re
from collections import defaultdict
from collections import OrderedDict


exec_log = [] 
host_name=''

register_used_flg = 0

class SignalReceive(Exception): pass

def signal_handle(signum,frame):
  raise SignalReceive('Urgency stop (signal=' + str(signum) + ')')

def main():
  module = AnsibleModule(
    argument_spec = dict(
      username=dict(required=True),
      protocol=dict(required=True),
      inventory_hostname=dict(required=True),
      host_vars_file=dict(required=True),
      exec_file=dict(required=False, default=''),
      grep_shell_dir=dict(required=True),
      log_file_dir=dict(required=True),
      ssh_key_file=dict(required=True),
      extra_args=dict(required=True),
    ),
#  ドライランモード許可設定
    supports_check_mode=True
  )
  signal.signal(signal.SIGTERM,signal_handle)
  protocol = module.params['protocol']
  user_name = module.params['username']
  host_name = module.params['inventory_hostname']
  host_vars_file = module.params['host_vars_file']
  shell_name = module.params['grep_shell_dir']
  shell_name = shell_name + '/backyards/ansible_driver/ky_pionner_grep_side_Ansible.sh'
  log_file_name = module.params['log_file_dir'] + '/private.log'
  ssh_key_file   = module.params['ssh_key_file']
  extra_args = module.params['extra_args']
  if not module.params['exec_file']:
    #########################################################
    # normal exit
    #########################################################
    module.fail_json(msg='exec_file no found fail exit')
  config = yaml.load(open(module.params['exec_file']).read())
  private_log(log_file_name,host_name,str(config))

  timeout = config['conf']['timeout']
  exec_cmd=''
  exec_name=''
  expect_cmd=''
  expect_name=''
  parameter_cmd=''
  shell_cmd=''
  stdout_file=''
  success_exit=''
  ignore_errors=''
  register_cmd=''
  register_name=''
  with_items_count=0

  # ファイルの順序通りに読み込みさせる
  yaml.add_constructor(yaml.resolver.BaseResolver.DEFAULT_MAPPING_TAG,lambda loader, node: OrderedDict(loader.construct_pairs(node)))

  try:
    # プロセスIDをファイルに出力
    pid = os.getpid()
    pid_file_name = module.params['log_file_dir'] + "/pioneer." + str(pid)
    fp = open(pid_file_name, "w")
    fp.write(str(pid))
    fp.close()

    # telnet/sshに接続時の追加パラメータ適用

    # SSH接続でSSH秘密鍵ファイルが設定されているか判定
    append_param = ""
    if ssh_key_file != "__undefinesymbol__" and protocol == "ssh":
      ## pioneer では -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/nullはあえて入れない。
      append_param = " -o 'IdentityFile=\"" + ssh_key_file + "\"' "

    # EXTRA_ARGS(SSH/TELNET)が設定されているか判定
    if extra_args != "__undefinesymbol__":
      append_param = append_param  + " " + extra_args

    if user_name == "__undefinesymbol__":
      exec_cmd = protocol + " " + host_name + " " + append_param            
    else:
      exec_cmd = protocol + " " + host_name + " -l " + user_name  + " " + append_param
    exec_name = 'remote login:[' + exec_cmd + ']'
    private_log(log_file_name,host_name,exec_name)

    exec_log.append(exec_name)
    p = pexpect.spawn(exec_cmd)
    private_log(log_file_name,host_name,"Ok")

# ドライランモードを退避しタイムアウト値を5秒にする。
    if module.check_mode:
      chk_mode = ':exit check mode'
      timeout  = 5;
    else:
      chk_mode = ''

    # exec_list read
    for input in config['exec_list']:

      exec_cmd=''
      exec_name=''
      expect_cmd=''
      expect_name=''
      parameter_cmd=''
      shell_cmd=''
      stdout_file=''
      success_exit=str(False)
      ignore_errors=str(False)
      when_cmd = {}
      with_cmd = {}
      tmp = {}
      tmp2 = {}
      temp3 = {}
      skip_flg = 0
      with_items_flg = 0
      exec_when_flg = 0
      with_file = {}
      with_def = {}
      def_cmd = {}
      failed_cmd = {}
      register_temp = ''
      max_count = 0
      exec_when_cmd = {}
      continue_flg = 0
      register_flg = 0
      global register_used_flg
      register_used_flg = 0
      register_tmp_name = ''
      count = 0
      timeout2 = config['conf']['timeout']
      prompt_count2 = 0
      prompt_count = 0
      timeout_count = 0
      prompt_num = 256
      timeout_num = 256

      # log output
      private_log(log_file_name,host_name,'input command' + str(input))
      exec_log.append('input command' + str(input))

      # expect command ?
      if 'expect' in input:
        for cmd in input:
          if 'expect' == cmd:
            expect_cmd = str(input[cmd])
            expect_name = 'expect command:(' + expect_cmd + ')'

          elif 'exec' == cmd:
            exec_cmd = str(input[cmd])
            exec_name = 'exec command:(' + exec_cmd + ')'
          else:
            # error log
            logstr = 'command(expect->' + cmd + ') not service'
            exec_log.append(logstr)
            private_log(log_file_name,host_name,logstr)
             
            #########################################################
            # fail exit
            #########################################################
            module.fail_json(msg=host_name + ":" + logstr,exec_log=exec_log)

        # expect command log output
        private_log(log_file_name,host_name,expect_name)
        exec_log.append(expect_name)

        ####################################################
        # expect command execute
        ####################################################
        p.expect(expect_cmd, timeout=timeout)

# ドライランモードの場合は接続確認したら終了
        if module.check_mode:
          module.exit_json(msg=host_name + chk_mode,changed=False, exec_log=exec_log)

        # expect match log output
        exec_log.append('Match: [' + p.before + ']:::[' + p.after + ']:::[' + p.buffer + ']')
        private_log(log_file_name,host_name,"expect Match:before [" + p.before + "]")
        private_log(log_file_name,host_name,"expect Match:after  [" + p.after + "]")
        private_log(log_file_name,host_name,"expect Match:buffer [" + p.buffer + "]")
        private_log(log_file_name,host_name,"Ok")

        # exec command log output
        private_log(log_file_name,host_name,exec_name)
        exec_log.append(exec_name)

        ####################################################
        # exec command execute
        ####################################################
        p.sendline(exec_cmd)

        # debug
        # exec match log output
        private_log(log_file_name,host_name,"sendline Match:before [" + p.before + "]")
        private_log(log_file_name,host_name,"sendline Match:after  [" + p.after + "]")
        private_log(log_file_name,host_name,"sendline Match:buffer [" + p.buffer + "]")

        exec_log.append('Match: [' + p.before + ']:::[' + p.after + ']:::[' + p.buffer + ']')
        
        ####################################################
        # read line
        ####################################################
        p.readline()               # if no p.readline(), input(by p.sendline()) include next expec()!!

        # debug
        # readline match log output
        private_log(log_file_name,host_name,"readline Match:before [" + p.before + "]")
        private_log(log_file_name,host_name,"readline Match:after  [" + p.after + "]")
        private_log(log_file_name,host_name,"readline Match:buffer [" + p.buffer + "]")

        exec_log.append('Match: [' + p.before + ']:::[' + p.after + ']:::[' + p.buffer + ']')
        private_log(log_file_name,host_name,"Ok")

      # state command ?
      elif 'state' in input:
        # default shell set

        for cmd in input:
          if 'state' == cmd:
            exec_cmd = str(input[cmd])
            exec_name = 'state command:(' + exec_cmd + ')'
          elif 'prompt' == cmd:
            expect_cmd = str(input[cmd])
            expect_name = 'prompt:(' + expect_cmd + ')'
          elif 'parameter' == cmd:
            idx = 0
            max = len(input[cmd])
            while idx < max:
              private_log(log_file_name,host_name,str(input[cmd][idx]))
              parameter_cmd = parameter_cmd + input[cmd][idx] + ' '
              idx = idx + 1
            private_log(log_file_name,host_name,parameter_cmd)
          elif 'shell' == cmd:
            shell_cmd = str(input[cmd])
          elif 'stdout_file' == cmd:
            stdout_file = str(input[cmd])
          elif 'success_exit' == cmd:
            success_exit = str(input[cmd])
            if success_exit != str(False) and success_exit != str(True):
              logstr = 'success_exit=(' + str(input[cmd]) + '): only yes or no set'
              exec_log.append(logstr)
              private_log(log_file_name,host_name,logstr)
              #########################################################
              # fail exit
              #########################################################
              module.fail_json(msg=host_name + ':' + logstr,exec_log=exec_log)
          elif 'ignore_errors' == cmd:
            ignore_errors = str(input[cmd])
            if ignore_errors != str(False) and ignore_errors != str(True):
              logstr = 'ignore_errors=(' + str(input[cmd]) + '): Only yes or no set'
              exec_log.append(logstr)
              private_log(log_file_name,host_name,logstr)
              #########################################################
              # fail exit
              #########################################################
              module.fail_json(msg=host_name + ':' + logstr,exec_log=exec_log)
          else:
            # error log
            logstr = 'command(state->' + cmd + ') not service'
            exec_log.append(logstr)
            private_log(log_file_name,host_name,logstr)
             
            #########################################################
            # fail exit
            #########################################################
            module.fail_json(msg=host_name + ':' + logstr,exec_log=exec_log)

          # debug
          # log output
          private_log(log_file_name,host_name,cmd + ':' + str(input[cmd]))

        # prompt command log output
        private_log(log_file_name,host_name,expect_name)
        exec_log.append(expect_name)

        ####################################################
        # expect(prompt) command execute
        ####################################################
        p.expect(expect_cmd, timeout=timeout)

# ドライランモードの場合は接続確認したら終了
        if module.check_mode:
          module.exit_json(msg=host_name + chk_mode,changed=False, exec_log=exec_log)

        # expect match log output
        exec_log.append('Match: [' + p.before + ']:::[' + p.after + ']:::[' + p.buffer + ']')
        private_log(log_file_name,host_name,"prompt Match:before [" + p.before + "]")
        private_log(log_file_name,host_name,"prompt Match:after  [" + p.after + "]")
        private_log(log_file_name,host_name,"prompt Match:buffer [" + p.buffer + "]")
        private_log(log_file_name,host_name,"Ok")

        # state command log output
        private_log(log_file_name,host_name,exec_name)
        exec_log.append(exec_name)

        ####################################################
        # state command execute
        ####################################################
        p.sendline(exec_cmd)

        # debug
        # state match log output
        private_log(log_file_name,host_name,"sendline Match:before [" + p.before + "]")
        private_log(log_file_name,host_name,"sendline Match:after  [" + p.after + "]")
        private_log(log_file_name,host_name,"sendline Match:buffer [" + p.buffer + "]")
        
        ####################################################
        # read line
        ####################################################
        p.readline()       # if no p.readline(), input(by p.sendline()) include next expec()!!

        # readline match log output
        private_log(log_file_name,host_name,"readline Match:before [" + p.before + "]")
        private_log(log_file_name,host_name,"readline Match:after  [" + p.after + "]")
        private_log(log_file_name,host_name,"readline Match:buffer [" + p.buffer + "]")
        private_log(log_file_name,host_name,"Ok")

        # expect(prompt) command log output
        private_log(log_file_name,host_name,expect_name)
        exec_log.append(expect_name)

        ####################################################
        # expect(prompt) command execute
        ####################################################
        p.expect(expect_cmd, timeout=timeout)

        # expect match log output
        exec_log.append('Match: [' + p.before + ']:::[' + p.after + ']:::[' + p.buffer + ']')
        private_log(log_file_name,host_name,"prompt Match:before [" + p.before + "]")
        private_log(log_file_name,host_name,"prompt Match:after  [" + p.after + "]")
        private_log(log_file_name,host_name,"prompt Match:buffer [" + p.buffer + "]")
        private_log(log_file_name,host_name,"Ok")

        # stdout log file create
        if stdout_file:
          craete_stdout_file(stdout_file,p.before)
        else:
          stdout_file="/tmp/.ita_pioneer_module_stdout." + str(os.getpid())
          craete_stdout_file(stdout_file,p.before)

        if shell_cmd:
          # user shell execute
          try:
            logstr = 'user shell(' + shell_cmd + ' ' + parameter_cmd + ') execute'
            private_log(log_file_name,host_name,logstr)
            exec_log.append(logstr)

            shell_ret = subprocess.call(shell_cmd + ' ' + parameter_cmd, shell=True)
          except:
            error_type, error_value, traceback = sys.exc_info()
            logstr='user shell execute error ' + str(error_type) + ' ' + str(error_value)
            private_log(log_file_name,host_name,logstr)
            exec_log.append(logstr)

            #########################################################
            # fail exit
            #########################################################
            module.fail_json(msg=host_name + ':' + logstr,exec_log=exec_log)
            pass
        else:
          # default shell execute
          try:
            logstr = 'default shell parameter(' + str(parameter_cmd) + ') execute'
            private_log(log_file_name,host_name,logstr)
            exec_log.append(logstr)

            def_shell_cmd=shell_name + " " + stdout_file + " " + str(parameter_cmd)
            shell_ret = subprocess.call(def_shell_cmd, shell=True)
          except:
            error_type, error_value, traceback = sys.exc_info()
            logstr='default shell execute error ' + str(error_type) + ' ' + str(error_value)
            private_log(log_file_name,host_name,logstr)
            exec_log.append(logstr)

            #########################################################
            # fail exit
            #########################################################
            module.fail_json(msg=host_name + ':' + logstr,exec_log=exec_log)
            pass

        # shell result check
        if shell_ret == 0:
          # shell result log output
          logstr = 'execute result OK'
          private_log(log_file_name,host_name,logstr)
          exec_log.append(logstr)

          # success_exit check
          if success_exit == str(True):
            logstr = 'success_exit yes dialog_file normal exit'
            private_log(log_file_name,host_name,logstr)
            exec_log.append(logstr)
          
            #########################################################
            # normal exit
            #########################################################
            module.exit_json(msg=host_name + ':' + logstr,changed=False, exec_log=exec_log)

          else:
            private_log(log_file_name,host_name,'success_exit no')

        else:
          # shell result log output
          logstr='execute result NG exit code=(' + str(shell_ret) + ')'
          private_log(log_file_name,host_name,logstr)
          exec_log.append(logstr)

          # ignore_errors check
          if ignore_errors == str(False):
            logstr = 'ignore_errors no dialog_file fail exit'
            private_log(log_file_name,host_name,logstr)
            exec_log.append(logstr)
 
            #########################################################
            # fail exit
            #########################################################
            module.fail_json(msg=host_name + ':' + logstr,exec_log=exec_log)
          else:
            logstr = 'ignore_errors yes dialog_file execut continue'
            private_log(log_file_name,host_name,logstr)
            exec_log.append(logstr)

        ####################################################
        # LF send
        ####################################################
        p.sendline('')

        # debug
        private_log(log_file_name,host_name,"LF sendline Match:before [" + p.before + "]")
        private_log(log_file_name,host_name,"LF sendline Match:after  [" + p.after + "]")
        private_log(log_file_name,host_name,"LF sendline Match:buffer [" + p.buffer + "]")
        
        ####################################################
        # dummy read line
        ####################################################
        p.readline()       # if no p.readline(), input(by p.sendline()) include next expec()!!

        # debug
        private_log(log_file_name,host_name,"LF readline Match:before [" + p.before + "]")
        private_log(log_file_name,host_name,"LF readline Match:after  [" + p.after + "]")
        private_log(log_file_name,host_name,"LF readline Match:buffer [" + p.buffer + "]")

      # command?
      elif 'command' in input:
        for cmd in input:
          if 'command' == cmd:
            exec_cmd = str(input[cmd])
            exec_name = 'command command:(' + exec_cmd + ')'
          elif 'prompt' == cmd:
            expect_cmd = str(input[cmd])
            expect_name = 'prompt:(' + expect_cmd + ')'
          elif 'timeout' == cmd:
            timeout2 = input[cmd]
          elif 'register' == cmd:
            register_tmp_name = str(input[cmd])
            register_flg = 1
          elif 'when' == cmd:
            when_max = len(input[cmd])
            for i in range(0,when_max,1):
              when_tmp = str(input[cmd][i])
              when_cmd[i] = when_tmp
          elif 'with_items' == cmd:
            with_tmp = module.params['exec_file']
            with_tmp = with_tmp.replace("/in/", "/tmp/")
            with_tmp = with_tmp.replace("/dialog_files/", "/original_dialog_files/")
            with_file = yaml.load(open(with_tmp).read())
            with_def = yaml.load(open(module.params['host_vars_file']).read())

            # playbookから変数を取得
            idx = 0
            with_cmd = defaultdict(dict)
            for input2 in with_file['exec_list']:
              for cmd2 in input2:
                if 'with_items' == cmd2:
                  max = len(input2[cmd2])
                  for i in range(0,max,1):
                    with_tmp2 = str(input2[cmd2][i])
                    tmp1 = str(with_tmp2.find(' '))
                    tmp2 = str(with_tmp2.rfind(' '))
                    with_tmp2 = with_tmp2[int(tmp1)+1:int(tmp2)]
                    with_tmp2 = with_tmp2.lstrip()
                    with_tmp2 = with_tmp2.rstrip()
                    with_cmd[idx][i] = with_tmp2
                  idx = idx + 1

            # 変数定義から値を取得
            idx = 0
            idx2 = 0
            cnt = len(with_cmd[with_items_count])
            def_cmd = defaultdict(dict)
            for i in range(0,cnt,1):
              def_temp = with_cmd[with_items_count][i]
              if def_temp.find('VAR_prompt') != -1:
                prompt_num = i
              if def_temp.find('VAR_timeout') != -1:
                timeout_num = i
              max = len(with_def[def_temp])
              for j in range(0,max,1):
                def_tmp = str(with_def[def_temp][j])
                def_cmd[i][j] = def_tmp
            with_items_count = with_items_count + 1
          elif 'failed_when' == cmd:
            failed_max = len(input[cmd])
            for i in range(0,failed_max,1):
              failed_tmp = str(input[cmd][i])
              failed_cmd[i] = failed_tmp
          elif 'exec_when' == cmd:
            exec_max = len(input[cmd])
            for i in range(0,exec_max,1):
              exec_tmp = str(input[cmd][i])
              exec_when_cmd[i] = exec_tmp

          else:
            # error log
            logstr = 'command(command->' + cmd + ') not service'
            exec_log.append(logstr)
            private_log(log_file_name,host_name,logstr)

            #########################################################
            # fail exit
            #########################################################
            module.fail_json(msg=host_name + ':' + logstr,exec_log=exec_log)

          # debug
          # log output
          private_log(log_file_name,host_name,cmd + ':' + str(input[cmd]))

        ####################################################
        # expect(prompt) command execute
        ####################################################
        # p.expect(expect_cmd, timeout=timeout2)

        #ドライランモードの場合は接続確認したら終了
        if module.check_mode:
          module.exit_json(msg=host_name + chk_mode,changed=False, exec_log=exec_log)

        # command command log output
        private_log(log_file_name,host_name,exec_name)
        exec_log.append(exec_name)

        ####################################################
        # command command execute
        ####################################################
        # whenパラメータがある場合
        if when_cmd:

          # ループ処理
          for i in range(0,when_max,1):

            temp_cmd2 = when_cmd[i]
            tmp1 = 0
            count = 0

            exec_log.append('when: [' + temp_cmd2 + ']')
            private_log(log_file_name,host_name,'when:(' + temp_cmd2 + ')')

            # ORがある場合
            if re.search( " OR ", temp_cmd2 ):

              # OR実施数分ループ
              while 1:

                temp_cmd2 = temp_cmd2[int(tmp1):]
                temp_cmd2 = temp_cmd2.lstrip()

                if re.search( " OR ", temp_cmd2 ):
                  tmp2 = temp_cmd2.find(' OR ')
                  temp_cmd3 = temp_cmd2[:int(tmp2)]
                  temp_cmd3 = temp_cmd3.lstrip()
                  temp_cmd3 = temp_cmd3.rstrip()

                  # When実施
                  temp3[count] = when_check(temp_cmd3,register_cmd,register_name,host_vars_file,log_file_name,host_name)

                  if temp3[count] == 0:
                    exec_log.append('when: [' + temp_cmd3 + '] Match')
                    private_log(log_file_name,host_name,'when:(' + temp_cmd3 + ') Match')

                  else:
                    exec_log.append('when: [' + temp_cmd3 + '] No Match')
                    private_log(log_file_name,host_name,'when:(' + temp_cmd3 + ') No Match')

                  tmp1 = int(tmp2)+3
                  count = count+1

                else:

                  temp_cmd2 = temp_cmd2.rstrip()

                  # When実施
                  temp3[count] = when_check(temp_cmd2,register_cmd,register_name,host_vars_file,log_file_name,host_name)

                  if temp3[count] == 0:
                    exec_log.append('when: [' + temp_cmd2 + '] Match')
                    private_log(log_file_name,host_name,'when:(' + temp_cmd2 + ') Match')

                  else:
                    exec_log.append('when: [' + temp_cmd2 + '] No Match')
                    private_log(log_file_name,host_name,'when:(' + temp_cmd2 + ') No Match')

                  count = count+1
                  break

              tmp[i] = 1
              for j in range(0,count,1):
                if temp3[j] == 0:
                  tmp[i] = 0
                  break

            # ORがない場合
            else:

              # When実施
              tmp[i] = when_check(temp_cmd2,register_cmd,register_name,host_vars_file,log_file_name,host_name)

              if tmp[i] == 0:
                exec_log.append('when: [' + temp_cmd2 + '] Match')
                private_log(log_file_name,host_name,'when:(' + temp_cmd2 + ') Match')

              else:
                exec_log.append('when: [' + temp_cmd2 + '] No Match')
                private_log(log_file_name,host_name,'when:(' + temp_cmd2 + ') No Match')

          # スキップフラグ確認
          for i in range(0,when_max,1):

            if tmp[i] == 1:

              skip_flg = 1
              break

        # skip_flgが0であった場合
        if skip_flg == 0:

          # with_itemsがある場合
          if with_cmd:

            with_items_flg = 1

            # with_itemsの変数の数を取得
            max = len(def_cmd)

            for i in range(0,max,1):

              # promptの場合、取得しない
              if prompt_num == i:
                continue

              # timeoutの場合、取得しない
              if timeout_num == i:
                continue

              # 変数に対して要素数を取得
              tmp_count = len(def_cmd[i])

              # 最大の要素数を取得
              if max_count < tmp_count:
                max_count = tmp_count

            for i in range(0,max,1):

              tmp_count = len(def_cmd[i])
              if max_count > tmp_count:
                for j in range(tmp_count,max_count,1):
                  def_cmd[i][j] = ''

            # 最大要素数分ループ
            for i in range(0,max_count,1):

              command_exec_flg = 0

              # コマンド文を退避
              temp_cmd = exec_cmd

              # コマンドにitem.Xの記述があるかチェック
              if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd ):

                # with_itemsの変数分ループ
                for j in range(0,max,1):

                  # item.Xチェック
                  if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd ):

                    temp = "{{ " + "item." + str(j) + " }}"

                    if re.search( temp, temp_cmd ):

                      # 空でない場合
                      if len(def_cmd[j][i]) != 0:

                        # 置換
                        temp_cmd = temp_cmd.replace( temp, def_cmd[j][i] )

                      # 空の場合
                      else:

                        command_exec_flg = 1
                        break

                  # item.Xがない場合ループから抜ける
                  else:
                    break

                if command_exec_flg == 1:

                  continue

                # exec_whenがある場合
                if exec_when_cmd:

                  exec_when_flg = 1

                  # exec_when数分ループ
                  for j in range(0,exec_max,1):

                    continue_flg = 0

                    # exec_whenにitem.Xの記述があるかチェック
                    if re.search( "{{ item.[0-9]|[1-9][0-9] }}", exec_when_cmd[j] ):

                      # exec_when文を退避
                      temp_cmd2 = exec_when_cmd[j]

                      # with_itemsの変数分ループ
                      for k in range(0,max,1):

                        # item.Xチェック
                        if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd2 ):

                          temp2 = "{{ " + "item." + str(k) + " }}"

                          if re.search( temp2, temp_cmd2 ):

                            # 置換
                            temp_cmd2 = temp_cmd2.replace( temp2, def_cmd[k][i] )

                        # item.Xがない場合ループから抜ける
                        else:
                          break

                      tmp1 = 0
                      count = 0

                      # ORがある場合
                      if re.search( " OR ", temp_cmd2 ):

                        exec_log.append('exec_when: [' + temp_cmd2 + ']')
                        private_log(log_file_name,host_name,'exec_when:(' + temp_cmd2 + ')')

                        # OR実施数分ループ
                        while 1:

                          temp_cmd2 = temp_cmd2[int(tmp1):]
                          temp_cmd2 = temp_cmd2.lstrip()

                          if re.search( " OR ", temp_cmd2 ):
                            tmp2 = temp_cmd2.find(' OR ')
                            temp_cmd3 = temp_cmd2[:int(tmp2)]
                            temp_cmd3 = temp_cmd3.lstrip()
                            temp_cmd3 = temp_cmd3.rstrip()

                            # exec_whenチェック
                            temp3[count] = when_check(temp_cmd3,register_cmd,register_name,host_vars_file,log_file_name,host_name)

                            if temp3[count] == 0:
                              exec_log.append('exec_when: [' + temp_cmd3 + '] Match')
                              private_log(log_file_name,host_name,'exec_when:(' + temp_cmd3 + ') Match')

                            else:
                              exec_log.append('exec_when: [' + temp_cmd3 + '] No Match')
                              private_log(log_file_name,host_name,'exec_when:(' + temp_cmd3 + ') No Match')

                            tmp1 = int(tmp2)+3
                            count = count+1

                          else:

                            temp_cmd2 = temp_cmd2.rstrip()

                            # exec_whenチェック
                            temp3[count] = when_check(temp_cmd2,register_cmd,register_name,host_vars_file,log_file_name,host_name)
                            
                            if temp3[count] == 0:
                              exec_log.append('exec_when: [' + temp_cmd2 + '] Match')
                              private_log(log_file_name,host_name,'exec_when:(' + temp_cmd2 + ') Match')

                            else:
                              exec_log.append('exec_when: [' + temp_cmd2 + '] No Match')
                              private_log(log_file_name,host_name,'exec_when:(' + temp_cmd2 + ') No Match')

                            count = count+1
                            break

                        tmp = 1
                        for k in range(0,count,1):
                          if temp3[k] == 0:
                            tmp = 0
                            break

                      # ORがない場合
                      else:

                        # exec_whenチェック
                        tmp = when_check(temp_cmd2,register_cmd,register_name,host_vars_file,log_file_name,host_name)

                        if tmp == 0:
                          exec_log.append('exec_when: [' + temp_cmd2 + '] Match')
                          private_log(log_file_name,host_name,'exec_when:(' + temp_cmd2 + ') Match')

                        else:
                          exec_log.append('exec_when: [' + temp_cmd2 + '] No Match')
                          private_log(log_file_name,host_name,'exec_when:(' + temp_cmd2 + ') No Match')

                      # continue確認
                      if tmp == 1:

                        continue_flg = 1
                        logstr = 'exec_when not match continue'
                        private_log(log_file_name,host_name,logstr)
                        exec_log.append(logstr)
                        break

                    # item.Xの記述がない場合、そのままexec_whenチェック
                    else:

                      temp_cmd2 = exec_when_cmd[j]
                      tmp1 = 0
                      count = 0

                      # ORがある場合
                      if re.search( " OR ", temp_cmd2 ):

                        exec_log.append('exec_when: [' + temp_cmd2 + ']')
                        private_log(log_file_name,host_name,'exec_when:(' + temp_cmd2 + ')')

                        # OR実施数分ループ
                        while 1:

                          temp_cmd2 = temp_cmd2[int(tmp1):]
                          temp_cmd2 = temp_cmd2.lstrip()

                          if re.search( " OR ", temp_cmd2 ):
                            tmp2 = temp_cmd2.find(' OR ')
                            temp_cmd3 = temp_cmd2[:int(tmp2)]
                            temp_cmd3 = temp_cmd3.lstrip()
                            temp_cmd3 = temp_cmd3.rstrip()

                            # exec_whenチェック
                            temp3[count] = when_check(temp_cmd3,register_cmd,register_name,host_vars_file,log_file_name,host_name)

                            if temp3[count] == 0:
                              exec_log.append('exec_when: [' + temp_cmd3 + '] Match')
                              private_log(log_file_name,host_name,'exec_when:(' + temp_cmd3 + ') Match')

                            else:
                              exec_log.append('exec_when: [' + temp_cmd3 + '] No Match')
                              private_log(log_file_name,host_name,'exec_when:(' + temp_cmd3 + ') No Match')

                            tmp1 = int(tmp2)+3
                            count = count+1

                          else:

                            temp_cmd2 = temp_cmd2.rstrip()

                            # exec_whenチェック
                            temp3[count] = when_check(temp_cmd2,register_cmd,register_name,host_vars_file,log_file_name,host_name)

                            if temp3[count] == 0:
                              exec_log.append('exec_when: [' + temp_cmd2 + '] Match')
                              private_log(log_file_name,host_name,'exec_when:(' + temp_cmd2 + ') Match')

                            else:
                              exec_log.append('exec_when: [' + temp_cmd2 + '] No Match')
                              private_log(log_file_name,host_name,'exec_when:(' + temp_cmd2 + ') No Match')

                            count = count+1
                            break

                        tmp = 1
                        for k in range(0,count,1):
                          if temp3[k] == 0:
                            tmp = 0
                            break

                      # ORがない場合
                      else:
                        # exec_whenチェック
                        tmp = when_check(temp_cmd2,register_cmd,register_name,host_vars_file,log_file_name,host_name)

                        if tmp == 0:
                          exec_log.append('exec_when: [' + temp_cmd2 + '] Match')
                          private_log(log_file_name,host_name,'exec_when:(' + temp_cmd2 + ') Match')

                        else:
                          exec_log.append('exec_when: [' + temp_cmd2 + '] No Match')
                          private_log(log_file_name,host_name,'exec_when:(' + temp_cmd2 + ') No Match')

                      # continue確認
                      if tmp == 1:

                        continue_flg = 1
                        logstr = 'exec_when not match continue'
                        private_log(log_file_name,host_name,logstr)
                        exec_log.append(logstr)
                        break

                  if continue_flg == 0:

                    if prompt_count2 == 0:

                      # prompt文を退避
                      temp_cmd4 = expect_cmd

                      # promptにitem.Xの記述があるかチェック
                      if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd4 ):

                        # with_itemsの変数分ループ
                        for k in range(0,max,1):

                          # item.Xチェック
                          if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd4 ):

                            temp2 = "{{ " + "item." + str(k) + " }}"

                            if re.search( temp2, temp_cmd4 ):

                              if len(def_cmd[k]) == prompt_count or def_cmd[k][prompt_count] == '':

                                logstr = 'The number of prompts is incorrect.'
                                private_log(log_file_name,host_name,logstr)
                                exec_log.append(logstr)

                                #########################################################
                                # fail exit
                                #########################################################
                                module.fail_json(msg=host_name + ":" + logstr,exec_log=exec_log)

                              # 置換
                              temp_cmd4 = temp_cmd4.replace( temp2, def_cmd[k][prompt_count] )
                              prompt_count = prompt_count + 1

                          # item.Xがない場合ループから抜ける
                          else:
                            break

                      # timeout値を退避
                      temp_cmd5 = str(timeout2)

                      # timeoutにitem.Xの記述があるかチェック
                      if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd5 ):

                        # with_itemsの変数分ループ
                        for k in range(0,max,1):

                          # item.Xチェック
                          if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd5 ):

                            temp2 = "{{ " + "item." + str(k) + " }}"

                            if re.search( temp2, temp_cmd5 ):

                              if len(def_cmd[k]) == timeout_count or def_cmd[k][timeout_count] == '':

                                logstr = 'The number of timeouts is incorrect.'
                                private_log(log_file_name,host_name,logstr)
                                exec_log.append(logstr)

                                #########################################################
                                # fail exit
                                #########################################################
                                module.fail_json(msg=host_name + ":" + logstr,exec_log=exec_log)

                              # 置換
                              temp_cmd5 = temp_cmd5.replace( temp2, def_cmd[k][timeout_count] )
                              timeout_count = timeout_count + 1

                          # item.Xがない場合ループから抜ける
                          else:
                            break

                      # プロンプト待ち
                      p.expect(temp_cmd4, timeout=int(temp_cmd5))
                      exec_log.append('prompt:(' + temp_cmd4 + ')')
                      private_log(log_file_name,host_name,'prompt:(' + temp_cmd4 + ')')
                      private_log(log_file_name,host_name,"prompt Match:before [" + p.before + "]")
                      private_log(log_file_name,host_name,"prompt Match:after  [" + p.after + "]")
                      private_log(log_file_name,host_name,"prompt Match:buffer [" + p.buffer + "]")
                      private_log(log_file_name,host_name,"Ok")
                      prompt_count2 = prompt_count2 + 1

                    exec_log.append('command: [' + temp_cmd + ']')
                    private_log(log_file_name,host_name,'command:(' + temp_cmd + ')')

                    # コマンド実行
                    p.sendline(temp_cmd)

                    # 出力結果読み込み
                    p.readline()
                    private_log(log_file_name,host_name,"readline Match:before [" + p.before + "]")
                    private_log(log_file_name,host_name,"readline Match:after  [" + p.after + "]")
                    private_log(log_file_name,host_name,"readline Match:buffer [" + p.buffer + "]")
                    private_log(log_file_name,host_name,"Ok")

                    # prompt文を退避
                    temp_cmd4 = expect_cmd

                    # promptにitem.Xの記述があるかチェック
                    if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd4 ):

                      # with_itemsの変数分ループ
                      for k in range(0,max,1):

                        # item.Xチェック
                        if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd4 ):

                          temp2 = "{{ " + "item." + str(k) + " }}"

                          if re.search( temp2, temp_cmd4 ):

                            if len(def_cmd[k]) == prompt_count or def_cmd[k][prompt_count] == '':

                              logstr = 'The number of prompts is incorrect.'
                              private_log(log_file_name,host_name,logstr)
                              exec_log.append(logstr)

                              #########################################################
                              # fail exit
                              #########################################################
                              module.fail_json(msg=host_name + ":" + logstr,exec_log=exec_log)

                            # 置換
                            temp_cmd4 = temp_cmd4.replace( temp2, def_cmd[k][prompt_count] )
                            prompt_count = prompt_count + 1

                        # item.Xがない場合ループから抜ける
                        else:
                          break

                    # timeout値を退避
                    temp_cmd5 = str(timeout2)

                    # timeoutにitem.Xの記述があるかチェック
                    if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd5 ):

                      # with_itemsの変数分ループ
                      for k in range(0,max,1):

                        # item.Xチェック
                        if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd5 ):

                          temp2 = "{{ " + "item." + str(k) + " }}"

                          if re.search( temp2, temp_cmd5 ):

                            if len(def_cmd[k]) == timeout_count or def_cmd[k][timeout_count] == '':

                              logstr = 'The number of timeouts is incorrect.'
                              private_log(log_file_name,host_name,logstr)
                              exec_log.append(logstr)

                              #########################################################
                              # fail exit
                              #########################################################
                              module.fail_json(msg=host_name + ":" + logstr,exec_log=exec_log)

                            # 置換
                            temp_cmd5 = temp_cmd5.replace( temp2, def_cmd[k][timeout_count] )
                            timeout_count = timeout_count + 1

                        # item.Xがない場合ループから抜ける
                        else:
                          break

                    # プロンプト待ち
                    p.expect(temp_cmd4, timeout=int(temp_cmd5))
                    exec_log.append('prompt:(' + temp_cmd4 + ')')
                    private_log(log_file_name,host_name,'prompt:(' + temp_cmd4 + ')')
                    private_log(log_file_name,host_name,"prompt Match:before [" + p.before + "]")
                    private_log(log_file_name,host_name,"prompt Match:after  [" + p.after + "]")
                    private_log(log_file_name,host_name,"prompt Match:buffer [" + p.buffer + "]")
                    private_log(log_file_name,host_name,"Ok")

                    if failed_cmd:

                      # failed_when数分ループ
                      for j in range(0,failed_max,1):

                        # failed_whenにitem.Xの記述があるかチェック
                        if re.search( "{{ item.[0-9]|[1-9][0-9] }}", failed_cmd[j] ):

                          # failed_when文を退避
                          temp_cmd2 = failed_cmd[j]

                          # with_itemsの変数分ループ
                          for k in range(0,max,1):

                            # item.Xチェック
                            if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd2 ):

                              temp2 = "{{ " + "item." + str(k) + " }}"

                              if re.search( temp2, temp_cmd2 ):

                                # 置換
                                temp_cmd2 = temp_cmd2.replace( temp2, def_cmd[k][i] )

                            # item.Xがない場合ループから抜ける
                            else:
                              break

                          tmp1 = 0
                          count = 0

                          # ORがある場合
                          if re.search( " OR ", temp_cmd2 ):

                            exec_log.append('failed_when: [' + temp_cmd2 + ']')
                            private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ')')

                            # OR実施数分ループ
                            while 1:

                              temp_cmd2 = temp_cmd2[int(tmp1):]
                              temp_cmd2 = temp_cmd2.lstrip()

                              if re.search( " OR ", temp_cmd2 ):
                                tmp2 = temp_cmd2.find(' OR ')
                                temp_cmd3 = temp_cmd2[:int(tmp2)]
                                temp_cmd3 = temp_cmd3.lstrip()
                                temp_cmd3 = temp_cmd3.rstrip()

                                register_temp = p.before

                                # failed_whenチェック
                                temp3[count] = failed_when_check(temp_cmd3,register_temp,log_file_name,host_name)
                                
                                if temp3[count] == 0:
                                  exec_log.append('failed_when: [' + temp_cmd3 + '] Match')
                                  private_log(log_file_name,host_name,'failed_when:(' + temp_cmd3 + ') Match')

                                else:
                                  exec_log.append('failed_when: [' + temp_cmd3 + '] No Match')
                                  private_log(log_file_name,host_name,'failed_when:(' + temp_cmd3 + ') No Match')

                                tmp1 = int(tmp2)+3
                                count = count+1

                              else:

                                temp_cmd2 = temp_cmd2.rstrip()

                                register_temp = p.before

                                # failed_whenチェック
                                temp3[count] = failed_when_check(temp_cmd2,register_temp,log_file_name,host_name)

                                if temp3[count] == 0:
                                  exec_log.append('failed_when: [' + temp_cmd2 + '] Match')
                                  private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ') Match')

                                else:
                                  exec_log.append('failed_when: [' + temp_cmd2 + '] No Match')
                                  private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ') No Match')

                                count = count+1
                                break

                            tmp = 1
                            for k in range(0,count,1):
                              if temp3[k] == 0:
                                tmp = 0
                                break

                          # ORがない場合
                          else:
                            # failed_whenチェック
                            tmp = failed_when_check(temp_cmd2,register_temp,log_file_name,host_name)

                            if tmp == 0:
                              exec_log.append('failed_when: [' + temp_cmd2 + '] Match')
                              private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ') Match')

                            else:
                              exec_log.append('failed_when: [' + temp_cmd2 + '] No Match')
                              private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ') No Match')

                          # エラー確認
                          if tmp == 1:

                            logstr = 'failed_when not match fail exit'
                            private_log(log_file_name,host_name,logstr)
                            exec_log.append(logstr)

                            #########################################################
                            # fail exit
                            #########################################################
                            module.fail_json(msg=host_name + ":" + logstr,exec_log=exec_log)

                        # with_itemsあるがitem.Xがない場合、そのままfailed_whenチェック
                        else:

                          temp_cmd2 = failed_cmd[j]
                          tmp1 = 0
                          count = 0

                          # ORがある場合
                          if re.search( " OR ", temp_cmd2 ):

                            exec_log.append('failed_when: [' + temp_cmd2 + ']')
                            private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ')')

                            # OR実施数分ループ
                            while 1:

                              temp_cmd2 = temp_cmd2[int(tmp1):]
                              temp_cmd2 = temp_cmd2.lstrip()

                              if re.search( " OR ", temp_cmd2 ):
                                tmp2 = temp_cmd2.find(' OR ')
                                temp_cmd3 = temp_cmd2[:int(tmp2)]
                                temp_cmd3 = temp_cmd3.lstrip()
                                temp_cmd3 = temp_cmd3.rstrip()

                                register_temp = p.before

                                # failed_whenチェック
                                temp3[count] = failed_when_check(temp_cmd3,register_temp,log_file_name,host_name)

                                if temp3[count] == 0:
                                  exec_log.append('failed_when: [' + temp_cmd3 + '] Match')
                                  private_log(log_file_name,host_name,'failed_when:(' + temp_cmd3 + ') Match')

                                else:
                                  exec_log.append('failed_when: [' + temp_cmd3 + '] No Match')
                                  private_log(log_file_name,host_name,'failed_when:(' + temp_cmd3 + ') No Match')

                                tmp1 = int(tmp2)+3
                                count = count+1

                              else:

                                temp_cmd2 = temp_cmd2.rstrip()

                                register_temp = p.before

                                # failed_whenチェック
                                temp3[count] = failed_when_check(temp_cmd2,register_temp,log_file_name,host_name)

                                if temp3[count] == 0:
                                  exec_log.append('failed_when: [' + temp_cmd2 + '] Match')
                                  private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ') Match')

                                else:
                                  exec_log.append('failed_when: [' + temp_cmd2 + '] No Match')
                                  private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ') No Match')

                                count = count+1
                                break

                            tmp = 1
                            for k in range(0,count,1):
                              if temp3[k] == 0:
                                tmp = 0
                                break

                          # ORがない場合
                          else:
                            register_temp = p.before

                            # failed_whenチェック
                            tmp = failed_when_check(temp_cmd2,register_temp,log_file_name,host_name)

                            if tmp == 0:
                              exec_log.append('failed_when: [' + temp_cmd2 + '] Match')
                              private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ') Match')

                            else:
                              exec_log.append('failed_when: [' + temp_cmd2 + '] No Match')
                              private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ') No Match')

                          # エラー確認
                          if tmp == 1:

                            logstr = 'failed_when not match fail exit'
                            private_log(log_file_name,host_name,logstr)
                            exec_log.append(logstr)

                            #########################################################
                            # fail exit
                            #########################################################
                            module.fail_json(msg=host_name + ":" + logstr,exec_log=exec_log)

                # exec_whenがない場合
                else:

                  if prompt_count2 == 0:

                    # prompt文を退避
                    temp_cmd4 = expect_cmd

                    # promptにitem.Xの記述があるかチェック
                    if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd4 ):

                      # with_itemsの変数分ループ
                      for k in range(0,max,1):

                        # item.Xチェック
                        if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd4 ):

                          temp2 = "{{ " + "item." + str(k) + " }}"

                          if re.search( temp2, temp_cmd4 ):

                            if len(def_cmd[k]) == prompt_count or def_cmd[k][prompt_count] == '':

                              logstr = 'The number of prompts is incorrect.'
                              private_log(log_file_name,host_name,logstr)
                              exec_log.append(logstr)

                              #########################################################
                              # fail exit
                              #########################################################
                              module.fail_json(msg=host_name + ":" + logstr,exec_log=exec_log)

                            # 置換
                            temp_cmd4 = temp_cmd4.replace( temp2, def_cmd[k][prompt_count] )
                            prompt_count = prompt_count + 1

                        # item.Xがない場合ループから抜ける
                        else:
                          break

                    # timeout値を退避
                    temp_cmd5 = str(timeout2)

                    # timeoutにitem.Xの記述があるかチェック
                    if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd5 ):

                      # with_itemsの変数分ループ
                      for k in range(0,max,1):

                        # item.Xチェック
                        if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd5 ):

                          temp2 = "{{ " + "item." + str(k) + " }}"

                          if re.search( temp2, temp_cmd5 ):

                            if len(def_cmd[k]) == timeout_count or def_cmd[k][timeout_count] == '':

                              logstr = 'The number of timeouts is incorrect.'
                              private_log(log_file_name,host_name,logstr)
                              exec_log.append(logstr)

                              #########################################################
                              # fail exit
                              #########################################################
                              module.fail_json(msg=host_name + ":" + logstr,exec_log=exec_log)

                            # 置換
                            temp_cmd5 = temp_cmd5.replace( temp2, def_cmd[k][timeout_count] )
                            timeout_count = timeout_count + 1

                        # item.Xがない場合ループから抜ける
                        else:
                          break

                    # プロンプト待ち
                    p.expect(temp_cmd4, timeout=int(temp_cmd5))
                    exec_log.append('prompt:(' + temp_cmd4 + ')')
                    private_log(log_file_name,host_name,'prompt:(' + temp_cmd4 + ')')
                    private_log(log_file_name,host_name,"prompt Match:before [" + p.before + "]")
                    private_log(log_file_name,host_name,"prompt Match:after  [" + p.after + "]")
                    private_log(log_file_name,host_name,"prompt Match:buffer [" + p.buffer + "]")
                    private_log(log_file_name,host_name,"Ok")
                    prompt_count2 = prompt_count2 + 1

                  exec_log.append('command: [' + temp_cmd + ']')
                  private_log(log_file_name,host_name,'command:(' + temp_cmd + ')')

                  # コマンド実行
                  p.sendline(temp_cmd)

                  # 出力結果読み込み
                  p.readline()
                  private_log(log_file_name,host_name,"readline Match:before [" + p.before + "]")
                  private_log(log_file_name,host_name,"readline Match:after  [" + p.after + "]")
                  private_log(log_file_name,host_name,"readline Match:buffer [" + p.buffer + "]")
                  private_log(log_file_name,host_name,"Ok")

                  # prompt文を退避
                  temp_cmd4 = expect_cmd

                  # promptにitem.Xの記述があるかチェック
                  if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd4 ):

                    # with_itemsの変数分ループ
                    for k in range(0,max,1):

                      # item.Xチェック
                      if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd4 ):

                        temp2 = "{{ " + "item." + str(k) + " }}"

                        if re.search( temp2, temp_cmd4 ):

                          if len(def_cmd[k]) == prompt_count or def_cmd[k][prompt_count] == '':

                            logstr = 'The number of prompts is incorrect.'
                            private_log(log_file_name,host_name,logstr)
                            exec_log.append(logstr)

                            #########################################################
                            # fail exit
                            #########################################################
                            module.fail_json(msg=host_name + ":" + logstr,exec_log=exec_log)

                          # 置換
                          temp_cmd4 = temp_cmd4.replace( temp2, def_cmd[k][prompt_count] )
                          prompt_count = prompt_count + 1

                      # item.Xがない場合ループから抜ける
                      else:
                        break

                  # timeout値を退避
                  temp_cmd5 = str(timeout2)

                  # timeoutにitem.Xの記述があるかチェック
                  if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd5 ):

                    # with_itemsの変数分ループ
                    for k in range(0,max,1):

                      # item.Xチェック
                      if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd5 ):

                        temp2 = "{{ " + "item." + str(k) + " }}"

                        if re.search( temp2, temp_cmd5 ):

                          if len(def_cmd[k]) == timeout_count or def_cmd[k][timeout_count] == '':

                            logstr = 'The number of timeouts is incorrect.'
                            private_log(log_file_name,host_name,logstr)
                            exec_log.append(logstr)

                            #########################################################
                            # fail exit
                            #########################################################
                            module.fail_json(msg=host_name + ":" + logstr,exec_log=exec_log)

                          # 置換
                          temp_cmd5 = temp_cmd5.replace( temp2, def_cmd[k][timeout_count] )
                          timeout_count = timeout_count + 1

                      # item.Xがない場合ループから抜ける
                      else:
                        break

                  # プロンプト待ち
                  p.expect(temp_cmd4, timeout=int(temp_cmd5))
                  exec_log.append('prompt:(' + temp_cmd4 + ')')
                  private_log(log_file_name,host_name,'prompt:(' + temp_cmd4 + ')')
                  private_log(log_file_name,host_name,"prompt Match:before [" + p.before + "]")
                  private_log(log_file_name,host_name,"prompt Match:after  [" + p.after + "]")
                  private_log(log_file_name,host_name,"prompt Match:buffer [" + p.buffer + "]")
                  private_log(log_file_name,host_name,"Ok")

                  if failed_cmd:

                    # failed_when数分ループ
                    for j in range(0,failed_max,1):

                      # failed_whenにitem.Xの記述があるかチェック
                      if re.search( "{{ item.[0-9]|[1-9][0-9] }}", failed_cmd[j] ):

                        # コマンド文を退避
                        temp_cmd2 = failed_cmd[j]

                        # with_itemsの変数分ループ
                        for k in range(0,max,1):

                          # item.Xチェック
                          if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd2 ):

                            temp2 = "{{ " + "item." + str(k) + " }}"

                            if re.search( temp2, temp_cmd2 ):

                              # 置換
                              temp_cmd2 = temp_cmd2.replace( temp2, def_cmd[k][i] )

                          # item.Xがない場合ループから抜ける
                          else:
                            break

                        tmp1 = 0
                        count = 0

                        # ORがある場合
                        if re.search( " OR ", temp_cmd2 ):

                          exec_log.append('failed_when: [' + temp_cmd2 + ']')
                          private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ')')

                          # OR実施数分ループ
                          while 1:

                            temp_cmd2 = temp_cmd2[int(tmp1):]
                            temp_cmd2 = temp_cmd2.lstrip()

                            if re.search( " OR ", temp_cmd2 ):
                              tmp2 = temp_cmd2.find(' OR ')
                              temp_cmd3 = temp_cmd2[:int(tmp2)]
                              temp_cmd3 = temp_cmd3.lstrip()
                              temp_cmd3 = temp_cmd3.rstrip()

                              register_temp = p.before

                              # failed_whenチェック
                              temp3[count] = failed_when_check(temp_cmd3,register_temp,log_file_name,host_name)

                              if temp3[count] == 0:
                                exec_log.append('failed_when: [' + temp_cmd3 + '] Match')
                                private_log(log_file_name,host_name,'failed_when:(' + temp_cmd3 + ') Match')

                              else:
                                exec_log.append('failed_when: [' + temp_cmd3 + '] No Match')
                                private_log(log_file_name,host_name,'failed_when:(' + temp_cmd3 + ') No Match')

                              tmp1 = int(tmp2)+3
                              count = count+1

                            else:

                              temp_cmd2 = temp_cmd2.rstrip()

                              register_temp = p.before

                              # failed_whenチェック
                              temp3[count] = failed_when_check(temp_cmd2,register_temp,log_file_name,host_name)

                              if temp3[count] == 0:
                                exec_log.append('failed_when: [' + temp_cmd2 + '] Match')
                                private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ') Match')

                              else:
                                exec_log.append('failed_when: [' + temp_cmd2 + '] No Match')
                                private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ') No Match')

                              count = count+1
                              break

                          tmp = 1
                          for k in range(0,count,1):
                            if temp3[k] == 0:
                              tmp = 0
                              break

                        # ORがない場合
                        else:
                          register_temp = p.before

                          # failed_whenチェック
                          tmp = failed_when_check(temp_cmd2,register_temp,log_file_name,host_name)

                          if tmp == 0:
                            exec_log.append('failed_when: [' + temp_cmd2 + '] Match')
                            private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ') Match')

                          else:
                            exec_log.append('failed_when: [' + temp_cmd2 + '] No Match')
                            private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ') No Match')

                        # エラー確認
                        if tmp == 1:

                          logstr = 'failed_when not match fail exit'
                          private_log(log_file_name,host_name,logstr)
                          exec_log.append(logstr)

                          #########################################################
                          # fail exit
                          #########################################################
                          module.fail_json(msg=host_name + ":" + logstr,exec_log=exec_log)

                      # with_itemsあるがitem.Xがない場合、そのままfailed_whenチェック
                      else:

                        temp_cmd2 = failed_cmd[j]
                        tmp1 = 0
                        count = 0

                        # ORがある場合
                        if re.search( " OR ", temp_cmd2 ):

                          exec_log.append('failed_when: [' + temp_cmd2 + ']')
                          private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ')')

                          # OR実施数分ループ
                          while 1:

                            temp_cmd2 = temp_cmd2[int(tmp1):]
                            temp_cmd2 = temp_cmd2.lstrip()

                            if re.search( " OR ", temp_cmd2 ):
                              tmp2 = temp_cmd2.find(' OR ')
                              temp_cmd3 = temp_cmd2[:int(tmp2)]
                              temp_cmd3 = temp_cmd3.lstrip()
                              temp_cmd3 = temp_cmd3.rstrip()

                              register_temp = p.before

                              # failed_whenチェック
                              temp3[count] = failed_when_check(temp_cmd3,register_temp,log_file_name,host_name)

                              if temp3[count] == 0:
                                exec_log.append('failed_when: [' + temp_cmd3 + '] Match')
                                private_log(log_file_name,host_name,'failed_when:(' + temp_cmd3 + ') Match')

                              else:
                                exec_log.append('failed_when: [' + temp_cmd3 + '] No Match')
                                private_log(log_file_name,host_name,'failed_when:(' + temp_cmd3 + ') No Match')

                              tmp1 = int(tmp2)+3
                              count = count+1

                            else:

                              temp_cmd2 = temp_cmd2.rstrip()

                              register_temp = p.before
                              
                              # failed_whenチェック
                              temp3[count] = failed_when_check(temp_cmd2,register_temp,log_file_name,host_name)

                              if temp3[count] == 0:
                                exec_log.append('failed_when: [' + temp_cmd2 + '] Match')
                                private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ') Match')

                              else:
                                exec_log.append('failed_when: [' + temp_cmd2 + '] No Match')
                                private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ') No Match')

                              count = count+1
                              break

                          tmp = 1
                          for k in range(0,count,1):
                            if temp3[k] == 0:
                              tmp = 0
                              break

                        # ORがない場合
                        else:
                          register_temp = p.before

                          # failed_whenチェック
                          tmp = failed_when_check(temp_cmd2,register_temp,log_file_name,host_name)

                          if tmp == 0:
                            exec_log.append('failed_when: [' + temp_cmd2 + '] Match')
                            private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ') Match')

                          else:
                            exec_log.append('failed_when: [' + temp_cmd2 + '] No Match')
                            private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ') No Match')

                        # エラー確認
                        if tmp == 1:

                          logstr = 'failed_when not match fail exit'
                          private_log(log_file_name,host_name,logstr)
                          exec_log.append(logstr)

                          #########################################################
                          # fail exit
                          #########################################################
                          module.fail_json(msg=host_name + ":" + logstr,exec_log=exec_log)

              # コマンドにitem.Xの記述がない場合そのまま実行
              else:

                # exec_whenがある場合
                if exec_when_cmd:

                  exec_when_flg = 1

                  # exec_when数分ループ
                  for j in range(0,exec_max,1):

                    continue_flg = 0

                    # exec_whenにitem.Xの記述があるかチェック
                    if re.search( "{{ item.[0-9]|[1-9][0-9] }}", exec_when_cmd[j] ):

                      # exec_when文を退避
                      temp_cmd2 = exec_when_cmd[j]

                      # with_itemsの変数分ループ
                      for k in range(0,max,1):

                        # item.Xチェック
                        if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd2 ):

                          temp2 = "{{ " + "item." + str(k) + " }}"

                          if re.search( temp2, temp_cmd2 ):

                            # 置換
                            temp_cmd2 = temp_cmd2.replace( temp2, def_cmd[k][i] )

                        # item.Xがない場合ループから抜ける
                        else:
                          break

                      tmp1 = 0
                      count = 0

                      # ORがある場合
                      if re.search( " OR ", temp_cmd2 ):

                        exec_log.append('exec_when: [' + temp_cmd2 + ']')
                        private_log(log_file_name,host_name,'exec_when:(' + temp_cmd2 + ')')

                        # OR実施数分ループ
                        while 1:

                          temp_cmd2 = temp_cmd2[int(tmp1):]
                          temp_cmd2 = temp_cmd2.lstrip()

                          if re.search( " OR ", temp_cmd2 ):
                            tmp2 = temp_cmd2.find(' OR ')
                            temp_cmd3 = temp_cmd2[:int(tmp2)]
                            temp_cmd3 = temp_cmd3.lstrip()
                            temp_cmd3 = temp_cmd3.rstrip()

                            # exec_whenチェック
                            temp3[count] = when_check(temp_cmd3,register_cmd,register_name,host_vars_file,log_file_name,host_name)

                            if temp3[count] == 0:
                              exec_log.append('exec_when: [' + temp_cmd3 + '] Match')
                              private_log(log_file_name,host_name,'exec_when:(' + temp_cmd3 + ') Match')

                            else:
                              exec_log.append('exec_when: [' + temp_cmd3 + '] No Match')
                              private_log(log_file_name,host_name,'exec_when:(' + temp_cmd3 + ') No Match')

                            tmp1 = int(tmp2)+3
                            count = count+1

                          else:

                            temp_cmd2 = temp_cmd2.rstrip()

                            # exec_whenチェック
                            temp3[count] = when_check(temp_cmd2,register_cmd,register_name,host_vars_file,log_file_name,host_name)

                            if temp3[count] == 0:
                              exec_log.append('exec_when: [' + temp_cmd2 + '] Match')
                              private_log(log_file_name,host_name,'exec_when:(' + temp_cmd2 + ') Match')

                            else:
                              exec_log.append('exec_when: [' + temp_cmd2 + '] No Match')
                              private_log(log_file_name,host_name,'exec_when:(' + temp_cmd2 + ') No Match')

                            count = count+1
                            break

                        tmp = 1
                        for k in range(0,count,1):
                          if temp3[k] == 0:
                            tmp = 0
                            break

                      # ORがない場合
                      else:

                        # exec_whenチェック
                        tmp = when_check(temp_cmd2,register_cmd,register_name,host_vars_file,log_file_name,host_name)

                        if tmp == 0:
                          exec_log.append('exec_when: [' + temp_cmd2 + '] Match')
                          private_log(log_file_name,host_name,'exec_when:(' + temp_cmd2 + ') Match')

                        else:
                          exec_log.append('exec_when: [' + temp_cmd2 + '] No Match')
                          private_log(log_file_name,host_name,'exec_when:(' + temp_cmd2 + ') No Match')

                      # continue確認
                      if tmp == 1:

                        continue_flg = 1
                        logstr = 'exec_when not match continue'
                        private_log(log_file_name,host_name,logstr)
                        exec_log.append(logstr)
                        break

                    # item.Xの記述がない場合、そのままexec_whenチェック
                    else:

                      temp_cmd2 = exec_when_cmd[j]
                      tmp1 = 0
                      count = 0

                      # ORがある場合
                      if re.search( " OR ", temp_cmd2 ):

                        exec_log.append('exec_when: [' + temp_cmd2 + ']')
                        private_log(log_file_name,host_name,'exec_when:(' + temp_cmd2 + ')')

                        # OR実施数分ループ
                        while 1:

                          temp_cmd2 = temp_cmd2[int(tmp1):]
                          temp_cmd2 = temp_cmd2.lstrip()

                          if re.search( " OR ", temp_cmd2 ):
                            tmp2 = temp_cmd2.find(' OR ')
                            temp_cmd3 = temp_cmd2[:int(tmp2)]
                            temp_cmd3 = temp_cmd3.lstrip()
                            temp_cmd3 = temp_cmd3.rstrip()

                            # exec_whenチェック
                            temp3[count] = when_check(temp_cmd3,register_cmd,register_name,host_vars_file,log_file_name,host_name)

                            if temp3[count] == 0:
                              exec_log.append('exec_when: [' + temp_cmd3 + '] Match')
                              private_log(log_file_name,host_name,'exec_when:(' + temp_cmd3 + ') Match')

                            else:
                              exec_log.append('exec_when: [' + temp_cmd3 + '] No Match')
                              private_log(log_file_name,host_name,'exec_when:(' + temp_cmd3 + ') No Match')

                            tmp1 = int(tmp2)+3
                            count = count+1

                          else:

                            temp_cmd2 = temp_cmd2.rstrip()

                            # exec_whenチェック
                            temp3[count] = when_check(temp_cmd2,register_cmd,register_name,host_vars_file,log_file_name,host_name)

                            if temp3[count] == 0:
                              exec_log.append('exec_when: [' + temp_cmd2 + '] Match')
                              private_log(log_file_name,host_name,'exec_when:(' + temp_cmd2 + ') Match')

                            else:
                              exec_log.append('exec_when: [' + temp_cmd2 + '] No Match')
                              private_log(log_file_name,host_name,'exec_when:(' + temp_cmd2 + ') No Match')

                            count = count+1
                            break

                        tmp = 1
                        for k in range(0,count,1):
                          if temp3[k] == 0:
                            tmp = 0
                            break

                      # ORがない場合
                      else:

                        # exec_whenチェック
                        tmp = when_check(temp_cmd2,register_cmd,register_name,host_vars_file,log_file_name,host_name)

                        if tmp == 0:
                          exec_log.append('exec_when: [' + temp_cmd2 + '] Match')
                          private_log(log_file_name,host_name,'exec_when:(' + temp_cmd2 + ') Match')

                        else:
                          exec_log.append('exec_when: [' + temp_cmd2 + '] No Match')
                          private_log(log_file_name,host_name,'exec_when:(' + temp_cmd2 + ') No Match')

                      # continue確認
                      if tmp == 1:

                        continue_flg = 1
                        logstr = 'exec_when not match continue'
                        private_log(log_file_name,host_name,logstr)
                        exec_log.append(logstr)
                        break

                  if continue_flg == 0:

                    if prompt_count2 == 0:

                      # prompt文を退避
                      temp_cmd4 = expect_cmd

                      # promptにitem.Xの記述があるかチェック
                      if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd4 ):

                        # with_itemsの変数分ループ
                        for k in range(0,max,1):

                          # item.Xチェック
                          if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd4 ):

                            temp2 = "{{ " + "item." + str(k) + " }}"

                            if re.search( temp2, temp_cmd4 ):

                              if len(def_cmd[k]) == prompt_count or def_cmd[k][prompt_count] == '':

                                logstr = 'The number of prompts is incorrect.'
                                private_log(log_file_name,host_name,logstr)
                                exec_log.append(logstr)

                                #########################################################
                                # fail exit
                                #########################################################
                                module.fail_json(msg=host_name + ":" + logstr,exec_log=exec_log)

                              # 置換
                              temp_cmd4 = temp_cmd4.replace( temp2, def_cmd[k][prompt_count] )
                              prompt_count = prompt_count + 1

                          # item.Xがない場合ループから抜ける
                          else:
                            break

                      # timeout値を退避
                      temp_cmd5 = str(timeout2)

                      # timeoutにitem.Xの記述があるかチェック
                      if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd5 ):

                        # with_itemsの変数分ループ
                        for k in range(0,max,1):

                          # item.Xチェック
                          if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd5 ):

                            temp2 = "{{ " + "item." + str(k) + " }}"

                            if re.search( temp2, temp_cmd5 ):

                              if len(def_cmd[k]) == timeout_count or def_cmd[k][timeout_count] == '':

                                logstr = 'The number of timeouts is incorrect.'
                                private_log(log_file_name,host_name,logstr)
                                exec_log.append(logstr)

                                #########################################################
                                # fail exit
                                #########################################################
                                module.fail_json(msg=host_name + ":" + logstr,exec_log=exec_log)

                              # 置換
                              temp_cmd5 = temp_cmd5.replace( temp2, def_cmd[k][timeout_count] )
                              timeout_count = timeout_count + 1

                          # item.Xがない場合ループから抜ける
                          else:
                            break

                      # プロンプト待ち
                      p.expect(temp_cmd4, timeout=int(temp_cmd5))
                      exec_log.append('prompt:(' + temp_cmd4 + ')')
                      private_log(log_file_name,host_name,'prompt:(' + temp_cmd4 + ')')
                      private_log(log_file_name,host_name,"prompt Match:before [" + p.before + "]")
                      private_log(log_file_name,host_name,"prompt Match:after  [" + p.after + "]")
                      private_log(log_file_name,host_name,"prompt Match:buffer [" + p.buffer + "]")
                      private_log(log_file_name,host_name,"Ok")
                      prompt_count2 = prompt_count2 + 1

                    exec_log.append('command: [' + exec_cmd + ']')
                    private_log(log_file_name,host_name,'command:(' + exec_cmd + ')')

                    # コマンド実行
                    p.sendline(exec_cmd)

                    # 出力結果読み込み
                    p.readline()
                    private_log(log_file_name,host_name,"readline Match:before [" + p.before + "]")
                    private_log(log_file_name,host_name,"readline Match:after  [" + p.after + "]")
                    private_log(log_file_name,host_name,"readline Match:buffer [" + p.buffer + "]")
                    private_log(log_file_name,host_name,"Ok")

                    # prompt文を退避
                    temp_cmd4 = expect_cmd

                    # promptにitem.Xの記述があるかチェック
                    if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd4 ):

                      # with_itemsの変数分ループ
                      for k in range(0,max,1):

                        # item.Xチェック
                        if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd4 ):

                          temp2 = "{{ " + "item." + str(k) + " }}"

                          if re.search( temp2, temp_cmd4 ):

                            if len(def_cmd[k]) == prompt_count or def_cmd[k][prompt_count] == '':

                              logstr = 'The number of prompts is incorrect.'
                              private_log(log_file_name,host_name,logstr)
                              exec_log.append(logstr)

                              #########################################################
                              # fail exit
                              #########################################################
                              module.fail_json(msg=host_name + ":" + logstr,exec_log=exec_log)

                            # 置換
                            temp_cmd4 = temp_cmd4.replace( temp2, def_cmd[k][prompt_count] )
                            prompt_count = prompt_count + 1

                        # item.Xがない場合ループから抜ける
                        else:
                          break

                    # timeout値を退避
                    temp_cmd5 = str(timeout2)

                    # timeoutにitem.Xの記述があるかチェック
                    if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd5 ):

                      # with_itemsの変数分ループ
                      for k in range(0,max,1):

                        # item.Xチェック
                        if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd5 ):

                          temp2 = "{{ " + "item." + str(k) + " }}"

                          if re.search( temp2, temp_cmd5 ):

                            if len(def_cmd[k]) == timeout_count or def_cmd[k][timeout_count] == '':

                              logstr = 'The number of timeouts is incorrect.'
                              private_log(log_file_name,host_name,logstr)
                              exec_log.append(logstr)

                              #########################################################
                              # fail exit
                              #########################################################
                              module.fail_json(msg=host_name + ":" + logstr,exec_log=exec_log)

                            # 置換
                            temp_cmd5 = temp_cmd5.replace( temp2, def_cmd[k][timeout_count] )
                            timeout_count = timeout_count + 1

                        # item.Xがない場合ループから抜ける
                        else:
                          break

                    # プロンプト待ち
                    p.expect(temp_cmd4, timeout=int(temp_cmd5))
                    exec_log.append('prompt:(' + temp_cmd4 + ')')
                    private_log(log_file_name,host_name,'prompt:(' + temp_cmd4 + ')')
                    private_log(log_file_name,host_name,"prompt Match:before [" + p.before + "]")
                    private_log(log_file_name,host_name,"prompt Match:after  [" + p.after + "]")
                    private_log(log_file_name,host_name,"prompt Match:buffer [" + p.buffer + "]")
                    private_log(log_file_name,host_name,"Ok")

                    if failed_cmd:

                      # failed_when数分ループ
                      for j in range(0,failed_max,1):

                        # failed_whenにitem.Xの記述があるかチェック
                        if re.search( "{{ item.[0-9]|[1-9][0-9] }}", failed_cmd[j] ):

                          # failed_when文を退避
                          temp_cmd2 = failed_cmd[j]

                          # with_itemsの変数分ループ
                          for k in range(0,max,1):

                            # item.Xチェック
                            if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd2 ):

                              temp2 = "{{ " + "item." + str(k) + " }}"

                              if re.search( temp2, temp_cmd2 ):

                                # 置換
                                temp_cmd2 = temp_cmd2.replace( temp2, def_cmd[k][i] )

                            # item.Xがない場合ループから抜ける
                            else:
                              break

                          tmp1 = 0
                          count = 0

                          # ORがある場合
                          if re.search( " OR ", temp_cmd2 ):

                            exec_log.append('failed_when: [' + temp_cmd2 + ']')
                            private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ')')

                            # OR実施数分ループ
                            while 1:

                              temp_cmd2 = temp_cmd2[int(tmp1):]
                              temp_cmd2 = temp_cmd2.lstrip()

                              if re.search( " OR ", temp_cmd2 ):
                                tmp2 = temp_cmd2.find(' OR ')
                                temp_cmd3 = temp_cmd2[:int(tmp2)]
                                temp_cmd3 = temp_cmd3.lstrip()
                                temp_cmd3 = temp_cmd3.rstrip()

                                register_temp = p.before

                                # failed_whenチェック
                                temp3[count] = failed_when_check(temp_cmd3,register_temp,log_file_name,host_name)

                                if temp3[count] == 0:
                                  exec_log.append('failed_when: [' + temp_cmd3 + '] Match')
                                  private_log(log_file_name,host_name,'failed_when:(' + temp_cmd3 + ') Match')

                                else:
                                  exec_log.append('failed_when: [' + temp_cmd3 + '] No Match')
                                  private_log(log_file_name,host_name,'failed_when:(' + temp_cmd3 + ') No Match')

                                tmp1 = int(tmp2)+3
                                count = count+1

                              else:

                                temp_cmd2 = temp_cmd2.rstrip()

                                register_temp = p.before

                                # failed_whenチェック
                                temp3[count] = failed_when_check(temp_cmd2,register_temp,log_file_name,host_name)

                                if temp3[count] == 0:
                                  exec_log.append('failed_when: [' + temp_cmd2 + '] Match')
                                  private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ') Match')

                                else:
                                  exec_log.append('failed_when: [' + temp_cmd2 + '] No Match')
                                  private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ') No Match')

                                count = count+1
                                break

                            tmp = 1
                            for k in range(0,count,1):
                              if temp3[k] == 0:
                                tmp = 0
                                break

                          # ORがない場合
                          else:

                            register_temp = p.before

                            # failed_whenチェック
                            tmp = failed_when_check(temp_cmd2,register_temp,log_file_name,host_name)

                            if tmp == 0:
                              exec_log.append('failed_when: [' + temp_cmd2 + '] Match')
                              private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ') Match')

                            else:
                              exec_log.append('failed_when: [' + temp_cmd2 + '] No Match')
                              private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ') No Match')

                          # エラー確認
                          if tmp == 1:

                            logstr = 'failed_when not match fail exit'
                            private_log(log_file_name,host_name,logstr)
                            exec_log.append(logstr)

                            #########################################################
                            # fail exit
                            #########################################################
                            module.fail_json(msg=host_name + ":" + logstr,exec_log=exec_log)

                        # with_itemsあるがitem.Xがない場合、そのままfailed_whenチェック
                        else:

                          temp_cmd2 = failed_cmd[j]
                          tmp1 = 0
                          count = 0

                          # ORがある場合
                          if re.search( " OR ", temp_cmd2 ):

                            exec_log.append('failed_when: [' + temp_cmd2 + ']')
                            private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ')')

                            # OR実施数分ループ
                            while 1:

                              temp_cmd2 = temp_cmd2[int(tmp1):]
                              temp_cmd2 = temp_cmd2.lstrip()

                              if re.search( " OR ", temp_cmd2 ):
                                tmp2 = temp_cmd2.find(' OR ')
                                temp_cmd3 = temp_cmd2[:int(tmp2)]
                                temp_cmd3 = temp_cmd3.lstrip()
                                temp_cmd3 = temp_cmd3.rstrip()

                                register_temp = p.before

                                # failed_whenチェック
                                temp3[count] = failed_when_check(temp_cmd3,register_temp,log_file_name,host_name)

                                if temp3[count] == 0:
                                  exec_log.append('failed_when: [' + temp_cmd3 + '] Match')
                                  private_log(log_file_name,host_name,'failed_when:(' + temp_cmd3 + ') Match')

                                else:
                                  exec_log.append('failed_when: [' + temp_cmd3 + '] No Match')
                                  private_log(log_file_name,host_name,'failed_when:(' + temp_cmd3 + ') No Match')

                                tmp1 = int(tmp2)+3
                                count = count+1

                              else:

                                temp_cmd2 = temp_cmd2.rstrip()

                                register_temp = p.before

                                # failed_whenチェック
                                temp3[count] = failed_when_check(temp_cmd2,register_temp,log_file_name,host_name)

                                if temp3[count] == 0:
                                  exec_log.append('failed_when: [' + temp_cmd2 + '] Match')
                                  private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ') Match')

                                else:
                                  exec_log.append('failed_when: [' + temp_cmd2 + '] No Match')
                                  private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ') No Match')

                                count = count+1
                                break

                            tmp = 1
                            for k in range(0,count,1):
                              if temp3[k] == 0:
                                tmp = 0
                                break

                          # ORがない場合
                          else:

                            register_temp = p.before

                            # failed_whenチェック
                            tmp = failed_when_check(temp_cmd2,register_temp,log_file_name,host_name)

                            if tmp == 0:
                              exec_log.append('failed_when: [' + temp_cmd2 + '] Match')
                              private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ') Match')

                            else:
                              exec_log.append('failed_when: [' + temp_cmd2 + '] No Match')
                              private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ') No Match')

                          # エラー確認
                          if tmp == 1:

                            logstr = 'failed_when not match fail exit'
                            private_log(log_file_name,host_name,logstr)
                            exec_log.append(logstr)

                            #########################################################
                            # fail exit
                            #########################################################
                            module.fail_json(msg=host_name + ":" + logstr,exec_log=exec_log)

                # exec_whenがない場合
                else:

                  if prompt_count2 == 0:

                    # prompt文を退避
                    temp_cmd4 = expect_cmd

                    # promptにitem.Xの記述があるかチェック
                    if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd4 ):

                      # with_itemsの変数分ループ
                      for k in range(0,max,1):

                        # item.Xチェック
                        if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd4 ):

                          temp2 = "{{ " + "item." + str(k) + " }}"

                          if re.search( temp2, temp_cmd4 ):

                            if len(def_cmd[k]) == prompt_count or def_cmd[k][prompt_count] == '':

                              logstr = 'The number of prompts is incorrect.'
                              private_log(log_file_name,host_name,logstr)
                              exec_log.append(logstr)

                              #########################################################
                              # fail exit
                              #########################################################
                              module.fail_json(msg=host_name + ":" + logstr,exec_log=exec_log)

                            # 置換
                            temp_cmd4 = temp_cmd4.replace( temp2, def_cmd[k][prompt_count] )
                            prompt_count = prompt_count + 1

                        # item.Xがない場合ループから抜ける
                        else:
                          break

                    # timeout値を退避
                    temp_cmd5 = str(timeout2)

                    # timeoutにitem.Xの記述があるかチェック
                    if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd5 ):

                      # with_itemsの変数分ループ
                      for k in range(0,max,1):

                        # item.Xチェック
                        if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd5 ):

                          temp2 = "{{ " + "item." + str(k) + " }}"

                          if re.search( temp2, temp_cmd5 ):

                            if len(def_cmd[k]) == timeout_count or def_cmd[k][timeout_count] == '':

                              logstr = 'The number of timeouts is incorrect.'
                              private_log(log_file_name,host_name,logstr)
                              exec_log.append(logstr)

                              #########################################################
                              # fail exit
                              #########################################################
                              module.fail_json(msg=host_name + ":" + logstr,exec_log=exec_log)

                            # 置換
                            temp_cmd5 = temp_cmd5.replace( temp2, def_cmd[k][timeout_count] )
                            timeout_count = timeout_count + 1

                        # item.Xがない場合ループから抜ける
                        else:
                          break

                    # プロンプト待ち
                    p.expect(temp_cmd4, timeout=int(temp_cmd5))
                    exec_log.append('prompt:(' + temp_cmd4 + ')')
                    private_log(log_file_name,host_name,'prompt:(' + temp_cmd4 + ')')
                    private_log(log_file_name,host_name,"prompt Match:before [" + p.before + "]")
                    private_log(log_file_name,host_name,"prompt Match:after  [" + p.after + "]")
                    private_log(log_file_name,host_name,"prompt Match:buffer [" + p.buffer + "]")
                    private_log(log_file_name,host_name,"Ok")
                    prompt_count2 = prompt_count2 + 1

                  exec_log.append('command: [' + exec_cmd + ']')
                  private_log(log_file_name,host_name,'command:(' + exec_cmd + ')')

                  # コマンド実行
                  p.sendline(exec_cmd)

                  # 出力結果読み込み
                  p.readline()
                  private_log(log_file_name,host_name,"readline Match:before [" + p.before + "]")
                  private_log(log_file_name,host_name,"readline Match:after  [" + p.after + "]")
                  private_log(log_file_name,host_name,"readline Match:buffer [" + p.buffer + "]")
                  private_log(log_file_name,host_name,"Ok")

                  # prompt文を退避
                  temp_cmd4 = expect_cmd

                  # promptにitem.Xの記述があるかチェック
                  if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd4 ):

                    # with_itemsの変数分ループ
                    for k in range(0,max,1):

                      # item.Xチェック
                      if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd4 ):

                        temp2 = "{{ " + "item." + str(k) + " }}"

                        if re.search( temp2, temp_cmd4 ):

                          if len(def_cmd[k]) == prompt_count or def_cmd[k][prompt_count] == '':

                            logstr = 'The number of prompts is incorrect.'
                            private_log(log_file_name,host_name,logstr)
                            exec_log.append(logstr)

                            #########################################################
                            # fail exit
                            #########################################################
                            module.fail_json(msg=host_name + ":" + logstr,exec_log=exec_log)

                          # 置換
                          temp_cmd4 = temp_cmd4.replace( temp2, def_cmd[k][prompt_count] )
                          prompt_count = prompt_count + 1

                      # item.Xがない場合ループから抜ける
                      else:
                        break

                  # timeout値を退避
                  temp_cmd5 = str(timeout2)

                  # timeoutにitem.Xの記述があるかチェック
                  if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd5 ):

                    # with_itemsの変数分ループ
                    for k in range(0,max,1):

                      # item.Xチェック
                      if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd5 ):

                        temp2 = "{{ " + "item." + str(k) + " }}"

                        if re.search( temp2, temp_cmd5 ):

                          if len(def_cmd[k]) == timeout_count or def_cmd[k][timeout_count] == '':

                            logstr = 'The number of timeouts is incorrect.'
                            private_log(log_file_name,host_name,logstr)
                            exec_log.append(logstr)

                            #########################################################
                            # fail exit
                            #########################################################
                            module.fail_json(msg=host_name + ":" + logstr,exec_log=exec_log)

                          # 置換
                          temp_cmd5 = temp_cmd5.replace( temp2, def_cmd[k][timeout_count] )
                          timeout_count = timeout_count + 1

                      # item.Xがない場合ループから抜ける
                      else:
                        break

                  # プロンプト待ち
                  p.expect(temp_cmd4, timeout=int(temp_cmd5))
                  exec_log.append('prompt:(' + temp_cmd4 + ')')
                  private_log(log_file_name,host_name,'prompt:(' + temp_cmd4 + ')')
                  private_log(log_file_name,host_name,"prompt Match:before [" + p.before + "]")
                  private_log(log_file_name,host_name,"prompt Match:after  [" + p.after + "]")
                  private_log(log_file_name,host_name,"prompt Match:buffer [" + p.buffer + "]")
                  private_log(log_file_name,host_name,"Ok")

                  if failed_cmd:

                    # failed_when 数分ループ
                    for j in range(0,failed_max,1):

                      # failed_whenにitem.Xの記述があるかチェック
                      if re.search( "{{ item.[0-9]|[1-9][0-9] }}", failed_cmd[j] ):

                        # コマンド文を退避
                        temp_cmd2 = failed_cmd[j]

                        # with_itemsの変数分ループ
                        for k in range(0,max,1):

                          # item.Xチェック
                          if re.search( "{{ item.[0-9]|[1-9][0-9] }}", temp_cmd2 ):

                            temp2 = "{{ " + "item." + str(k) + " }}"

                            if re.search( temp2, temp_cmd2 ):

                              # 置換
                              temp_cmd2 = temp_cmd2.replace( temp2, def_cmd[k][i] )

                          # item.Xがない場合ループから抜ける
                          else:
                            break

                        tmp1 = 0
                        count = 0

                        # ORがある場合
                        if re.search( " OR ", temp_cmd2 ):

                          exec_log.append('failed_when: [' + temp_cmd2 + ']')
                          private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ')')

                          # OR実施数分ループ
                          while 1:

                            temp_cmd2 = temp_cmd2[int(tmp1):]
                            temp_cmd2 = temp_cmd2.lstrip()

                            if re.search( " OR ", temp_cmd2 ):
                              tmp2 = temp_cmd2.find(' OR ')
                              temp_cmd3 = temp_cmd2[:int(tmp2)]
                              temp_cmd3 = temp_cmd3.lstrip()
                              temp_cmd3 = temp_cmd3.rstrip()

                              register_temp = p.before

                              # failed_whenチェック
                              temp3[count] = failed_when_check(temp_cmd3,register_temp,log_file_name,host_name)

                              if temp3[count] == 0:
                                exec_log.append('failed_when: [' + temp_cmd3 + '] Match')
                                private_log(log_file_name,host_name,'failed_when:(' + temp_cmd3 + ') Match')

                              else:
                                exec_log.append('failed_when: [' + temp_cmd3 + '] No Match')
                                private_log(log_file_name,host_name,'failed_when:(' + temp_cmd3 + ') No Match')

                              tmp1 = int(tmp2)+3
                              count = count+1

                            else:

                              temp_cmd2 = temp_cmd2.rstrip()

                              register_temp = p.before

                              # failed_whenチェック
                              temp3[count] = failed_when_check(temp_cmd2,register_temp,log_file_name,host_name)

                              if temp3[count] == 0:
                                exec_log.append('failed_when: [' + temp_cmd2 + '] Match')
                                private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ') Match')

                              else:
                                exec_log.append('failed_when: [' + temp_cmd2 + '] No Match')
                                private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ') No Match')

                              count = count+1
                              break

                          tmp = 1
                          for k in range(0,count,1):
                            if temp3[k] == 0:
                              tmp = 0
                              break

                        # ORがない場合
                        else:

                          register_temp = p.before

                          # failed_whenチェック
                          tmp = failed_when_check(temp_cmd2,register_temp,log_file_name,host_name)

                          if tmp == 0:
                            exec_log.append('failed_when: [' + temp_cmd2 + '] Match')
                            private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ') Match')

                          else:
                            exec_log.append('failed_when: [' + temp_cmd2 + '] No Match')
                            private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ') No Match')

                        # エラー確認
                        if tmp == 1:

                          logstr = 'failed_when not match fail exit'
                          private_log(log_file_name,host_name,logstr)
                          exec_log.append(logstr)

                          #########################################################
                          # fail exit
                          #########################################################
                          module.fail_json(msg=host_name + ":" + logstr,exec_log=exec_log)

                      # with_itemsあるがitem.Xがない場合、そのままwhenチェック
                      else:

                        temp_cmd2 = failed_cmd[j]
                        tmp1 = 0
                        count = 0

                        # ORがある場合
                        if re.search( " OR ", temp_cmd2 ):

                          exec_log.append('failed_when: [' + temp_cmd2 + ']')
                          private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ')')

                          # OR実施数分ループ
                          while 1:

                            temp_cmd2 = temp_cmd2[int(tmp1):]
                            temp_cmd2 = temp_cmd2.lstrip()

                            if re.search( " OR ", temp_cmd2 ):
                              tmp2 = temp_cmd2.find(' OR ')
                              temp_cmd3 = temp_cmd2[:int(tmp2)]
                              temp_cmd3 = temp_cmd3.lstrip()
                              temp_cmd3 = temp_cmd3.rstrip()

                              register_temp = p.before

                              # failed_whenチェック
                              temp3[count] = failed_when_check(temp_cmd3,register_temp,log_file_name,host_name)

                              if temp3[count] == 0:
                                exec_log.append('failed_when: [' + temp_cmd3 + '] Match')
                                private_log(log_file_name,host_name,'failed_when:(' + temp_cmd3 + ') Match')

                              else:
                                exec_log.append('failed_when: [' + temp_cmd3 + '] No Match')
                                private_log(log_file_name,host_name,'failed_when:(' + temp_cmd3 + ') No Match')

                              tmp1 = int(tmp2)+3
                              count = count+1

                            else:

                              temp_cmd2 = temp_cmd2.rstrip()

                              register_temp = p.before

                              # failed_whenチェック
                              temp3[count] = failed_when_check(temp_cmd2,register_temp,log_file_name,host_name)

                              if temp3[count] == 0:
                                exec_log.append('failed_when: [' + temp_cmd2 + '] Match')
                                private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ') Match')

                              else:
                                exec_log.append('failed_when: [' + temp_cmd2 + '] No Match')
                                private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ') No Match')

                              count = count+1
                              break

                          tmp = 1
                          for k in range(0,count,1):
                            if temp3[k] == 0:
                              tmp = 0
                              break

                        # ORがない場合
                        else:

                          register_temp = p.before

                          # failed_whenチェック
                          tmp = failed_when_check(temp_cmd2,register_temp,log_file_name,host_name)

                          if tmp == 0:
                            exec_log.append('failed_when: [' + temp_cmd2 + '] Match')
                            private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ') Match')

                          else:
                            exec_log.append('failed_when: [' + temp_cmd2 + '] No Match')
                            private_log(log_file_name,host_name,'failed_when:(' + temp_cmd2 + ') No Match')

                        # エラー確認
                        if tmp == 1:

                          logstr = 'failed_when not match fail exit'
                          private_log(log_file_name,host_name,logstr)
                          exec_log.append(logstr)

                          #########################################################
                          # fail exit
                          #########################################################
                          module.fail_json(msg=host_name + ":" + logstr,exec_log=exec_log)

          # with_itemsを使用していないので、そのまま実行
          else:

            # prompt command log output
            private_log(log_file_name,host_name,expect_name)
            exec_log.append(expect_name)

            ####################################################
            # expect(prompt) command execute
            ####################################################
            p.expect(expect_cmd, timeout=timeout2)

            exec_log.append('command: [' + exec_cmd + ']')
            private_log(log_file_name,host_name,'command:(' + exec_cmd + ')')

            # コマンド実行
            p.sendline(exec_cmd)

        # スキップフラグが1であった場合
        else:
          # スキップする。
          logstr = 'Skip ...'
          exec_log.append(logstr)
          private_log(log_file_name,host_name,logstr)

        if register_used_flg == 1:
          register_cmd == ''
          register_name == ''

        # debug
        # command match log output
        private_log(log_file_name,host_name,"sendline Match:before [" + p.before + "]")
        private_log(log_file_name,host_name,"sendline Match:after  [" + p.after + "]")
        private_log(log_file_name,host_name,"sendline Match:buffer [" + p.buffer + "]")

        ####################################################
        # read line
        ####################################################
        if skip_flg != 1 and with_items_flg != 1 and exec_when_flg != 1:
          p.readline()       # if no p.readline(), input(by p.sendline()) include next expec()!!

          # readline match log output
          private_log(log_file_name,host_name,"readline Match:before [" + p.before + "]")
          private_log(log_file_name,host_name,"readline Match:after  [" + p.after + "]")
          private_log(log_file_name,host_name,"readline Match:buffer [" + p.buffer + "]")
          private_log(log_file_name,host_name,"Ok")

        ####################################################
        # expect(prompt) command execute
        ####################################################
        if skip_flg != 1 and with_items_flg != 1 and exec_when_flg != 1:

          private_log(log_file_name,host_name,expect_name)
          exec_log.append(expect_name)

          p.expect(expect_cmd, timeout=timeout2)

          # expect match log output
          private_log(log_file_name,host_name,"prompt Match:before [" + p.before + "]")
          private_log(log_file_name,host_name,"prompt Match:after  [" + p.after + "]")
          private_log(log_file_name,host_name,"prompt Match:buffer [" + p.buffer + "]")
          private_log(log_file_name,host_name,"Ok")

        if register_flg == 1:
          register_cmd = p.before
          register_name = register_tmp_name
          private_log(log_file_name,host_name,register_cmd)
          private_log(log_file_name,host_name,register_name)

        ####################################################
        # LF send
        ####################################################
        p.sendline('')

        # debug
        private_log(log_file_name,host_name,"LF sendline Match:before [" + p.before + "]")
        private_log(log_file_name,host_name,"LF sendline Match:after  [" + p.after + "]")
        private_log(log_file_name,host_name,"LF sendline Match:buffer [" + p.buffer + "]")
        
        ####################################################
        # dummy read line
        ####################################################
        p.readline()       # if no p.readline(), input(by p.sendline()) include next expec()!!

        # debug
        private_log(log_file_name,host_name,"LF readline Match:before [" + p.before + "]")
        private_log(log_file_name,host_name,"LF readline Match:after  [" + p.after + "]")
        private_log(log_file_name,host_name,"LF readline Match:buffer [" + p.buffer + "]")

      else:
        # error log
        logstr = 'command not service fail exit'
        private_log(log_file_name,host_name,logstr)
        exec_log.append(logstr)

        #########################################################
        # fail exit
        #########################################################
        module.fail_json(msg=host_name + ":" + logstr,exec_log=exec_log)

  except pexpect.TIMEOUT:
    exec_log.append('TIMEOUT------> ' + str(p))
    private_log(log_file_name,host_name,'timout')
    #########################################################
    # fail exit
    #########################################################
    module.fail_json(msg=host_name + ':command timeout' + ' ' + chk_mode,exec_log=exec_log)
  except SignalReceive, e:
    exec_log.append(str(e))
    private_log(log_file_name,host_name,str(e))
    #########################################################
    # fail exit
    #########################################################
    module.fail_json(msg=host_name + ":" + str(e) + chk_mode,exec_log=exec_log)
  # try chuu no module.fail_json de exceptions.SystemExit 
  except exceptions.SystemExit:
    #########################################################
    # fail exit
    #########################################################
    module.fail_json(exec_log=exec_log)
  except:
    import sys
    import traceback
    error_type, error_value, tb = sys.exc_info()

    stack_trace = traceback.format_exception(error_type, error_value, tb)
    edit_trace = ''
    for line in stack_trace:
        edit_trace = edit_trace  + line
    private_log(log_file_name,host_name,"Exception-------------------------------------------")
    private_log(log_file_name,host_name, edit_trace)
    private_log(log_file_name,host_name,"----------------------------------------------------")
    exec_log.append(                    'EXCEPTION-------------------------------------------')
    exec_log.append(edit_trace)
    exec_log.append(                    '----------------------------------------------------')

    #########################################################
    # fail exit
    #########################################################
    module.fail_json(msg=host_name + ':exception' + chk_mode,exec_log=exec_log)

  #########################################################
  # normal exit
  #########################################################
  module.exit_json(msg=host_name + ':nomal exit',changed=True, exec_log=exec_log)

from ansible.module_utils.basic import *

def private_log(log_file_name,host,var):
  now = datetime.datetime.now()
  f = open(log_file_name,'a')
  f.writelines(now.strftime("%Y%m%d %H:%M:%S") + '[' + host + ']' + var + "\n")
  f.close()

def craete_stdout_file(file,data):
  now = datetime.datetime.now()
  f = open(file,'w')
  f.writelines(data)
  f.close()

def when_check(when_cmd,register_cmd,register_name,host_vars_file,log_file_name,host_name):

  global register_used_flg
  r = re.compile("(.*)(\n)(.*)")

  # whenが"no match"である場合
  if re.search( "no match", when_cmd ):

    if len(register_name) != 0:

      # whenとregister変数が一致する場合
      if re.search( register_name, when_cmd ):

        register_used_flg = 1

        # '('が何文字目か検索
        tmp1 = str(when_cmd.find('('))

        # ')'が何文字目か検索
        tmp2 = str(when_cmd.rfind(')'))

        # ( から )までの文字列を抽出
        tmp3 = when_cmd[int(tmp1)+1:int(tmp2)]
        tmp3 = tmp3.lstrip()
        tmp3 = tmp3.rstrip()

        if len(tmp3) == 0:

          # 空の場合、1を返却する
          return 1

        # registerに条件分が一致するか検索
        if re.search( tmp3, register_cmd ):

          # 一致した場合、1を返却する
          return 1

        else:

          # 一致しない場合、0を返却する
          return 0

      # whenとregister変数が一致しない場合、VAR_xxと判断
      else:

        # '('が何文字目か検索
        tmp1 = str(when_cmd.find('('))

        # ')'が何文字目か検索
        tmp2 = str(when_cmd.rfind(')'))

        # ( から )までの文字列を抽出
        tmp3 = when_cmd[int(tmp1)+1:int(tmp2)]
        tmp3 = tmp3.lstrip()
        tmp3 = tmp3.rstrip()

        if len(tmp3) == 0:

          # 空の場合、1を返却する
          return 1

        # ' no match 'が何文字目か検索
        tmp4 = str(when_cmd.find('no match'))

        # ' no match '前の文字列を抽出
        tmp5 = when_cmd[:int(tmp4)]
        tmp5 = tmp5.lstrip()
        tmp5 = tmp5.rstrip()

        if len(tmp5) == 0:

          # 空の場合、1を返却する
          return 1

        # 一致するか検索
        if re.search( tmp3, tmp5 ):

          # 一致した場合、1を返却する
          return 1

        else:

          # 一致しない場合、0を返却する
          return 0

    # register変数が空の場合
    else:

      # '('が何文字目か検索
      tmp1 = str(when_cmd.find('('))

      # ')'が何文字目か検索
      tmp2 = str(when_cmd.rfind(')'))

      # ( から )までの文字列を抽出
      tmp3 = when_cmd[int(tmp1)+1:int(tmp2)]
      tmp3 = tmp3.lstrip()
      tmp3 = tmp3.rstrip()

      if len(tmp3) == 0:

        # 空の場合、1を返却する
        return 1

      # ' no match 'が何文字目か検索
      tmp4 = str(when_cmd.find('no match'))

      # ' no match '前の文字列を抽出
      tmp5 = when_cmd[:int(tmp4)]
      tmp5 = tmp5.lstrip()
      tmp5 = tmp5.rstrip()

      if len(tmp5) == 0:

          # 空の場合、1を返却する
          return 1

      # 一致するか検索
      if re.search( tmp3, tmp5 ):

        # 一致した場合、1を返却する
        return 1

      else:
        # 一致しない場合、0を返却する
        return 0

  # whenが"match"である場合
  elif re.search( "match", when_cmd ):

    if len(register_name) != 0:

      # whenとregister変数が一致する場合
      if re.search( register_name, when_cmd ):

        register_used_flg = 1

        # '('が何文字目か検索
        tmp1 = str(when_cmd.find('('))

        # ')'が何文字目か検索
        tmp2 = str(when_cmd.rfind(')'))

        # ( から )までの文字列を抽出
        tmp3 = when_cmd[int(tmp1)+1:int(tmp2)]
        tmp3 = tmp3.lstrip()
        tmp3 = tmp3.rstrip()

        if len(tmp3) == 0:

          # 空の場合、1を返却する
          return 1

        # registerに条件分が一致するか検索
        if re.search( tmp3, register_cmd ):

          # 一致した場合、0を返却する
          return 0

        else:

          # 一致しない場合、1を返却する
          return 1

      # whenとregister変数が一致しない場合、VAR_xxと判断
      else:

        # '('が何文字目か検索
        tmp1 = str(when_cmd.find('('))

        # ')'が何文字目か検索
        tmp2 = str(when_cmd.rfind(')'))

        # ( から )までの文字列を抽出
        tmp3 = when_cmd[int(tmp1)+1:int(tmp2)]
        tmp3 = tmp3.lstrip()
        tmp3 = tmp3.rstrip()

        if len(tmp3) == 0:

          # 空の場合、1を返却する
          return 1

        # ' no match 'が何文字目か検索
        tmp4 = str(when_cmd.find('match'))

        # ' match '前の文字列を抽出
        tmp5 = when_cmd[:int(tmp4)]
        tmp5 = tmp5.lstrip()
        tmp5 = tmp5.rstrip()

        if len(tmp5) == 0:

          # 空の場合、1を返却する
          return 1

        # 一致するか検索
        if re.search( tmp3, tmp5 ):

          # 一致した場合、0を返却する
          return 0

        else:

          # 一致しない場合、1を返却する
          return 1

    # register変数が空の場合
    else:

      # '('が何文字目か検索
      tmp1 = str(when_cmd.find('('))

      # ')'が何文字目か検索
      tmp2 = str(when_cmd.rfind(')'))

      # ( から )までの文字列を抽出
      tmp3 = when_cmd[int(tmp1)+1:int(tmp2)]
      tmp3 = tmp3.lstrip()
      tmp3 = tmp3.rstrip()

      if len(tmp3) == 0:

        # 空の場合、1を返却する
        return 1

      # ' no match 'が何文字目か検索
      tmp4 = str(when_cmd.find('match'))

      # ' match '前の文字列を抽出
      tmp5 = when_cmd[:int(tmp4)]
      tmp5 = tmp5.lstrip()
      tmp5 = tmp5.rstrip()

      if len(tmp5) == 0:

        # 空の場合、1を返却する
        return 1

      # 一致するか検索
      if re.search( tmp3, tmp5 ):

        # 一致した場合、0を返却する
        return 0

      else:
        # 一致しない場合、1を返却する
        return 1

  # 比較演算子("==")の場合
  elif re.search( "==", when_cmd ):

    if len(register_name) != 0:

      # whenとregister変数が一致する場合
      if re.search( register_name, when_cmd ):

        register_used_flg = 1

        # register取得
        tmp1 = register_cmd
        m = r.match(tmp1)
        tmp1 = m.group(1)
        tmp1 = tmp1.lstrip()
        tmp1 = tmp1.rstrip()

        # 後ろの'='の位置を取得
        tmp2 = str(when_cmd.rfind('='))

        # 右辺の文字列を取得
        tmp3 = when_cmd[int(tmp2)+1:]
        tmp3 = tmp3.lstrip()
        tmp3 = tmp3.rstrip()

        if len(tmp3) == 0:

          # 空の場合、1を返却する
          return 1

        # playbookの記述通りにif文
        if tmp1 == tmp3:

          # 一致した場合、0を返却する
          return 0

        else:

          # 一致しない場合、1を返却する
          return 1

      # whenとregister変数が一致しない場合、VAR_xxと判断
      else:

        # 前の'='の位置を取得
        tmp1 = str(when_cmd.find('='))

        # 後ろの'='の位置を取得
        tmp2 = str(when_cmd.rfind('='))

        # 左辺の文字列を取得
        tmp3 = when_cmd[:int(tmp1)-1]
        tmp3 = tmp3.lstrip()
        tmp3 = tmp3.rstrip()

        if len(tmp3) == 0:

          # 空の場合、1を返却する
          return 1

        # 右辺の文字列を取得
        tmp4 = when_cmd[int(tmp2)+1:]
        tmp4 = tmp4.lstrip()
        tmp4 = tmp4.rstrip()

        if len(tmp4) == 0:

          # 空の場合、1を返却する
          return 1

        # playbookの記述通りにif文
        if tmp3 == tmp4:

          # 一致した場合、0を返却する
          return 0

        else:

          # 一致しない場合、1を返却する
          return 1

    # register変数が空の場合
    else:

      # 前の'='の位置を取得
      tmp1 = str(when_cmd.find('='))

      # 後ろの'='の位置を取得
      tmp2 = str(when_cmd.rfind('='))

      # 左辺の文字列を取得
      tmp3 = when_cmd[:int(tmp1)-1]
      tmp3 = tmp3.lstrip()
      tmp3 = tmp3.rstrip()

      if len(tmp3) == 0:

        # 空の場合、1を返却する
        return 1

      # 右辺の文字列を取得
      tmp4 = when_cmd[int(tmp2)+1:]
      tmp4 = tmp4.lstrip()
      tmp4 = tmp4.rstrip()

      if len(tmp4) == 0:

        # 空の場合、1を返却する
        return 1

      # playbookの記述通りにif文
      if tmp3 == tmp4:

        # 一致した場合、0を返却する
        return 0

      else:
        # 一致しない場合、1を返却する
        return 1

  # 比較演算子("!=")の場合
  elif re.search( "!=", when_cmd ):

    if len(register_name) != 0:

      # whenとregister変数が一致する場合
      if re.search( register_name, when_cmd ):

        register_used_flg = 1

        # register取得
        tmp1 = register_cmd
        m = r.match(tmp1)
        tmp1 = m.group(1)
        tmp1 = tmp1.lstrip()
        tmp1 = tmp1.rstrip()

        # 後ろの'='の位置を取得
        tmp2 = str(when_cmd.rfind('='))

        # 右辺の文字列を取得
        tmp3 = when_cmd[int(tmp2)+1:]
        tmp3 = tmp3.lstrip()
        tmp3 = tmp3.rstrip()

        if len(tmp3) == 0:

          # 空の場合、1を返却する
          return 1

        # playbookの記述通りにif文
        if tmp1 != tmp3:

          # 一致する場合、0を返却する
          return 0

        else:

          # 一致しない場合、1を返却する
          return 1

      # whenとregister変数が一致しない場合、VAR_xxと判断
      else:

        # 前の'!'の位置を取得
        tmp1 = str(when_cmd.find('!'))

        # 後ろの'='の位置を取得
        tmp2 = str(when_cmd.rfind('='))

        # 左辺の文字列を取得
        tmp3 = when_cmd[:int(tmp1)-1]
        tmp3 = tmp3.lstrip()
        tmp3 = tmp3.rstrip()

        if len(tmp3) == 0:

          # 空の場合、1を返却する
          return 1

        # 右辺の文字列を取得
        tmp4 = when_cmd[int(tmp2)+1:]
        tmp4 = tmp4.lstrip()
        tmp4 = tmp4.rstrip()

        if len(tmp4) == 0:

          # 空の場合、1を返却する
          return 1

        # playbookの記述通りにif文
        if tmp3 != tmp4:

          # 一致する場合、0を返却する
          return 0

        else:

          # 一致しない場合、1を返却する
          return 1

    # register変数が空の場合
    else:

      # 前の'!'の位置を取得
      tmp1 = str(when_cmd.find('!'))

      # 後ろの'='の位置を取得
      tmp2 = str(when_cmd.rfind('='))

      # 左辺の文字列を取得
      tmp3 = when_cmd[:int(tmp1)-1]
      tmp3 = tmp3.lstrip()
      tmp3 = tmp3.rstrip()

      if len(tmp3) == 0:

        # 空の場合、1を返却する
        return 1

      # 右辺の文字列を取得
      tmp4 = when_cmd[int(tmp2)+1:]
      tmp4 = tmp4.lstrip()
      tmp4 = tmp4.rstrip()

      if len(tmp4) == 0:

        # 空の場合、1を返却する
        return 1

      # playbookの記述通りにif文
      if tmp3 != tmp4:
        # 一致する場合、0を返却する
        return 0

      else:
        # 一致しない場合、1を返却する
        return 1

  # 比較演算子(">=")の場合
  elif re.search( ">=", when_cmd ):

    if len(register_name) != 0:

      # whenとregister変数が一致する場合
      if re.search( register_name, when_cmd ):

        register_used_flg = 1

        # 後ろの'='の位置を取得
        tmp1 = str(when_cmd.rfind('='))

        # 左辺の文字列を取得
        tmp2 = register_cmd
        m = r.match(tmp2)
        tmp2 = m.group(1)
        tmp2 = tmp2.lstrip()
        tmp2 = tmp2.rstrip()

        # 右辺の文字列を取得
        tmp3 = when_cmd[int(tmp1)+1:]
        tmp3 = tmp3.lstrip()
        tmp3 = tmp3.rstrip()

        if len(tmp3) == 0:

          # 空の場合、1を返却する
          return 1

        # playbookの記述通りにif文
        if int(tmp2) >= int(tmp3):

          # 一致した場合、0を返却する
          return 0

        else:
          # 一致しない場合、1を返却する
          return 1

      # whenとregister変数が一致しない場合、VAR_xxと判断
      else:

        # 前の'>'の位置を取得
        tmp1 = str(when_cmd.find('>'))

        # 後ろの'='の位置を取得
        tmp2 = str(when_cmd.rfind('='))

        # 左辺の文字列を取得
        tmp3 = when_cmd[:int(tmp1)-1]
        tmp3 = tmp3.lstrip()
        tmp3 = tmp3.rstrip()

        if len(tmp3) == 0:

          # 空の場合、1を返却する
          return 1

        # 右辺の文字列を取得
        tmp4 = when_cmd[int(tmp2)+1:]
        tmp4 = tmp4.lstrip()
        tmp4 = tmp4.rstrip()

        if len(tmp4) == 0:

          # 空の場合、1を返却する
          return 1

        # playbookの記述通りにif文
        if int(tmp3) >= int(tmp4):

          # 一致した場合、0を返却する
          return 0

        else:

          # 一致しない場合、1を返却する
          return 1

    # register変数が空の場合
    else:

      # 前の'>'の位置を取得
      tmp1 = str(when_cmd.find('>'))

      # 後ろの'='の位置を取得
      tmp2 = str(when_cmd.rfind('='))

      # 左辺の文字列を取得
      tmp3 = when_cmd[:int(tmp1)-1]
      tmp3 = tmp3.lstrip()
      tmp3 = tmp3.rstrip()

      if len(tmp3) == 0:

        # 空の場合、1を返却する
        return 1

      # 右辺の文字列を取得
      tmp4 = when_cmd[int(tmp2)+1:]
      tmp4 = tmp4.lstrip()
      tmp4 = tmp4.rstrip()

      if len(tmp4) == 0:

        # 空の場合、1を返却する
        return 1

      # playbookの記述通りにif文
      if int(tmp3) >= int(tmp4):

        # 一致した場合、0を返却する
        return 0

      else:
        # 一致しない場合、1を返却する
        return 1

  # 比較演算子(">")の場合
  elif re.search( ">", when_cmd ):

    if len(register_name) != 0:

      # whenとregister変数が一致する場合
      if re.search( register_name, when_cmd ):

        register_used_flg = 1

        # '>'の位置を取得
        tmp1 = str(when_cmd.find('>'))

        # 左辺の文字列を取得
        tmp2 = register_cmd
        m = r.match(tmp2)
        tmp2 = m.group(1)
        tmp2 = tmp2.lstrip()
        tmp2 = tmp2.rstrip()

        # 右辺の文字列を取得
        tmp3 = when_cmd[int(tmp1)+1:]
        tmp3 = tmp3.lstrip()
        tmp3 = tmp3.rstrip()

        if len(tmp3) == 0:

          # 空の場合、1を返却する
          return 1

        # playbookの記述通りにif文
        if int(tmp2) > int(tmp3):

          # 一致する場合、0を返却する
          return 0

        else:

          # 一致しない場合、1を返却する
          return 1

      # whenとregister変数が一致しない場合、VAR_xxと判断
      else:

        # '>'の位置を取得
        tmp1 = str(when_cmd.find('>'))

        # 左辺の文字列を取得
        tmp2 = when_cmd[:int(tmp1)-1]
        tmp2 = tmp2.lstrip()
        tmp2 = tmp2.rstrip()

        if len(tmp2) == 0:

          # 空の場合、1を返却する
          return 1

        # 右辺の文字列を取得
        tmp3 = when_cmd[int(tmp1)+1:]
        tmp3 = tmp3.lstrip()
        tmp3 = tmp3.rstrip()

        if len(tmp3) == 0:

          # 空の場合、1を返却する
          return 1

        # playbookの記述通りにif文
        if int(tmp2) > int(tmp3):

          # 一致する場合、0を返却する
          return 0

        else:

          # 一致しない場合、1を返却する
          return 1

    # register変数が空の場合
    else:

      # '>'の位置を取得
      tmp1 = str(when_cmd.find('>'))

      # 左辺の文字列を取得
      tmp2 = when_cmd[:int(tmp1)-1]
      tmp2 = tmp2.lstrip()
      tmp2 = tmp2.rstrip()

      if len(tmp2) == 0:

        # 空の場合、1を返却する
        return 1

      # 右辺の文字列を取得
      tmp3 = when_cmd[int(tmp1)+1:]
      tmp3 = tmp3.lstrip()
      tmp3 = tmp3.rstrip()

      if len(tmp3) == 0:

        # 空の場合、1を返却する
        return 1

      # playbookの記述通りにif文
      if int(tmp2) > int(tmp3):

        # 一致する場合、0を返却する
        return 0

      else:

        # 一致しない場合、1を返却する
        return 1

  # 比較演算子("<=")の場合
  elif re.search( "<=", when_cmd ):

    if len(register_name) != 0:

      # whenとregister変数が一致する場合
      if re.search( register_name, when_cmd ):

        register_used_flg = 1

        # 後ろの'='の位置を取得
        tmp1 = str(when_cmd.rfind('='))

        # 左辺の文字列を取得
        tmp2 = register_cmd
        m = r.match(tmp2)
        tmp2 = m.group(1)
        tmp2 = tmp2.lstrip()
        tmp2 = tmp2.rstrip()

        # 右辺の文字列を取得
        tmp3 = when_cmd[int(tmp1)+1:]
        tmp3 = tmp3.lstrip()
        tmp3 = tmp3.rstrip()

        if len(tmp3) == 0:

          # 空の場合、1を返却する
          return 1

        # playbookの記述通りにif文
        if int(tmp2) <= int(tmp3):

          # 一致した場合、0を返却する
          return 0

        else:

          # 一致しない場合、1を返却する
          return 1

      # whenとregister変数が一致しない場合、VAR_xxと判断
      else:

        # 前の'<'の位置を取得
        tmp1 = str(when_cmd.find('<'))

        # 後ろの'='の位置を取得
        tmp2 = str(when_cmd.rfind('='))

        # 左辺の文字列を取得
        tmp3 = when_cmd[:int(tmp1)-1]
        tmp3 = tmp3.lstrip()
        tmp3 = tmp3.rstrip()

        if len(tmp3) == 0:

          # 空の場合、1を返却する
          return 1

        # 右辺の文字列を取得
        tmp4 = when_cmd[int(tmp2)+1:]
        tmp4 = tmp4.lstrip()
        tmp4 = tmp4.rstrip()

        if len(tmp4) == 0:

          # 空の場合、1を返却する
          return 1

        # playbookの記述通りにif文
        if int(tmp3) <= int(tmp4):

          # 一致した場合、0を返却する
          return 0

        else:

          # 一致しない場合、1を返却する
          return 1

    # register変数が空の場合
    else:

      # 前の'<'の位置を取得
      tmp1 = str(when_cmd.find('<'))

      # 後ろの'='の位置を取得
      tmp2 = str(when_cmd.rfind('='))

      # 左辺の文字列を取得
      tmp3 = when_cmd[:int(tmp1)-1]
      tmp3 = tmp3.lstrip()
      tmp3 = tmp3.rstrip()

      if len(tmp3) == 0:

        # 空の場合、1を返却する
        return 1

      # 右辺の文字列を取得
      tmp4 = when_cmd[int(tmp2)+1:]
      tmp4 = tmp4.lstrip()
      tmp4 = tmp4.rstrip()

      if len(tmp4) == 0:

        # 空の場合、1を返却する
        return 1

      # playbookの記述通りにif文
      if int(tmp3) <= int(tmp4):

        # 一致した場合、0を返却する
        return 0

      else:
        # 一致しない場合、1を返却する
        return 1

  # 比較演算子("<")の場合
  elif re.search( "<", when_cmd ):

    if len(register_name) != 0:

      # whenとregister変数が一致する場合
      if re.search( register_name, when_cmd ):

        register_used_flg = 1

        # '<'の位置を取得
        tmp1 = str(when_cmd.find('<'))

        # 左辺の文字列を取得
        tmp2 = register_cmd
        m = r.match(tmp2)
        tmp2 = m.group(1)
        tmp2 = tmp2.lstrip()
        tmp2 = tmp2.rstrip()

        # 右辺の文字列を取得
        tmp3 = when_cmd[int(tmp1)+1:]
        tmp3 = tmp3.lstrip()
        tmp3 = tmp3.rstrip()

        if len(tmp3) == 0:

          # 空の場合、1を返却する
          return 1

        # playbookの記述通りにif文
        if int(tmp2) < int(tmp3):

          # 一致する場合、0を返却する
          return 0

        else:

          # 一致しない場合、1を返却する
          return 1

      # whenとregister変数が一致しない場合、VAR_xxと判断
      else:

        # '<'の位置を取得
        tmp1 = str(when_cmd.find('<'))

        # 左辺の文字列を取得
        tmp2 = when_cmd[:int(tmp1)-1]
        tmp2 = tmp2.lstrip()
        tmp2 = tmp2.rstrip()

        if len(tmp2) == 0:

          # 空の場合、1を返却する
          return 1

        # 右辺の文字列を取得
        tmp3 = when_cmd[int(tmp1)+1:]
        tmp3 = tmp3.lstrip()
        tmp3 = tmp3.rstrip()

        if len(tmp3) == 0:

          # 空の場合、1を返却する
          return 1

        # playbookの記述通りにif文
        if int(tmp2) < int(tmp3):

          # 一致する場合、0を返却する
          return 0

        else:

          # 一致しない場合、1を返却する
          return 1

    # register変数が空の場合
    else:

      # '<'の位置を取得
      tmp1 = str(when_cmd.find('<'))

      # 左辺の文字列を取得
      tmp2 = when_cmd[:int(tmp1)-1]
      tmp2 = tmp2.lstrip()
      tmp2 = tmp2.rstrip()

      if len(tmp2) == 0:

        # 空の場合、1を返却する
        return 1

      # 右辺の文字列を取得
      tmp3 = when_cmd[int(tmp1)+1:]
      tmp3 = tmp3.lstrip()
      tmp3 = tmp3.rstrip()

      if len(tmp3) == 0:

        # 空の場合、1を返却する
        return 1

      # playbookの記述通りにif文
      if int(tmp2) < int(tmp3):
        # 一致する場合、0を返却する
        return 0

      else:
        # 一致しない場合、1を返却する
        return 1

  # is defineの場合
  elif re.search( "is define", when_cmd ):

    # 後ろから'is'の位置を取得
    tmp1 = str(when_cmd.rfind('is'))

    # 変数の文字列を取得
    tmp2 = when_cmd[:int(tmp1)-1]
    tmp2 = tmp2.lstrip()
    tmp2 = tmp2.rstrip()

    with open(host_vars_file, "r") as f:
      tmp3 = f.read()

    if re.search( tmp2, tmp3 ):

      # 一致した場合、0を返却する
      return 0

    else:
      # 一致しない場合、1を返却する
      return 1

  # is undefineの場合
  elif re.search( "is undefine", when_cmd ):

    # 後ろから'is'の位置を取得
    tmp1 = str(when_cmd.rfind('is'))

    # 変数の文字列を取得
    tmp2 = when_cmd[:int(tmp1)-1]
    tmp2 = tmp2.lstrip()
    tmp2 = tmp2.rstrip()

    with open(host_vars_file, "r") as f:
      tmp3 = f.read()

    if re.search( tmp2, tmp3 ):

      # 一致した場合、1を返却する
      return 1

    else:
      # 一致した場合、0を返却する
      return 0

def failed_when_check(when_cmd,register_cmd,log_file_name,host_name):

  r = re.compile("(.*)(\n)(.*)")

  # whenが"no match"である場合
  if re.search( "no match", when_cmd ):

    # '('が何文字目か検索
    tmp1 = str(when_cmd.find('('))

    # ')'が何文字目か検索
    tmp2 = str(when_cmd.rfind(')'))

    # ( から )までの文字列を抽出
    tmp3 = when_cmd[int(tmp1)+1:int(tmp2)]
    tmp3 = tmp3.lstrip()
    tmp3 = tmp3.rstrip()

    if len(tmp3) == 0:

      # 空の場合、1を返却する
      return 1

    # registerに条件分が一致するか検索
    if re.search( tmp3, register_cmd ):

      # 一致した場合、1を返却する
      return 1

    else:
      # 一致しない場合、0を返却する
      return 0

  # whenが"match"である場合
  elif re.search( "match", when_cmd ):

    # '('が何文字目か検索
    tmp1 = str(when_cmd.find('('))

    # ')'が何文字目か検索
    tmp2 = str(when_cmd.rfind(')'))

    # ( から )までの文字列を抽出
    tmp3 = when_cmd[int(tmp1)+1:int(tmp2)]
    tmp3 = tmp3.lstrip()
    tmp3 = tmp3.rstrip()

    if len(tmp3) == 0:

      # 空の場合、1を返却する
      return 1

    # registerに条件分が一致するか検索
    if re.search( tmp3, register_cmd ):

      # 一致した場合、0を返却する
      return 0

    else:
      # 一致しない場合、1を返却する
      return 1

  # 比較演算子("==")の場合
  elif re.search( "==", when_cmd ):

    # 後ろの'='の位置を取得
    tmp1 = str(when_cmd.rfind('='))

    # 左辺の文字列を取得
    tmp2 = register_cmd
    m = r.match(tmp2)
    tmp2 = m.group(1)
    tmp2 = tmp2.lstrip()
    tmp2 = tmp2.rstrip()

    # 右辺の文字列を取得
    tmp3 = when_cmd[int(tmp1)+1:]
    tmp3 = tmp3.lstrip()
    tmp3 = tmp3.rstrip()

    if len(tmp3) == 0:

      # 空の場合、1を返却する
      return 1

    # playbookの記述通りにif文
    if tmp2 == tmp3:

      # 一致した場合、0を返却する
      return 0

    else:
      # 一致しない場合、1を返却する
      return 1

  # 比較演算子("!=")の場合
  elif re.search( "!=", when_cmd ):

    # 後ろの'='の位置を取得
    tmp1 = str(when_cmd.rfind('='))

    # 左辺の文字列を取得
    tmp2 = register_cmd
    m = r.match(tmp2)
    tmp2 = m.group(1)
    tmp2 = tmp2.lstrip()
    tmp2 = tmp2.rstrip()

    # 右辺の文字列を取得
    tmp3 = when_cmd[int(tmp1)+1:]
    tmp3 = tmp3.lstrip()
    tmp3 = tmp3.rstrip()

    if len(tmp3) == 0:

      # 空の場合、1を返却する
      return 1

    # playbookの記述通りにif文
    if tmp2 != tmp3:
      # 一致する場合、0を返却する
      return 0

    else:
      # 一致しない場合、1を返却する
      return 1

  # 比較演算子(">=")の場合
  elif re.search( ">=", when_cmd ):

    # 後ろの'='の位置を取得
    tmp1 = str(when_cmd.rfind('='))

    # 左辺の文字列を取得
    tmp2 = register_cmd
    m = r.match(tmp2)
    tmp2 = m.group(1)
    tmp2 = tmp2.lstrip()
    tmp2 = tmp2.rstrip()

    # 右辺の文字列を取得
    tmp3 = when_cmd[int(tmp1)+1:]
    tmp3 = tmp3.lstrip()
    tmp3 = tmp3.rstrip()

    if len(tmp3) == 0:

      # 空の場合、1を返却する
      return 1

    # playbookの記述通りにif文
    if int(tmp2) >= int(tmp3):

      # 一致した場合、0を返却する
      return 0

    else:
      # 一致しない場合、1を返却する
      return 1

  # 比較演算子(">")の場合
  elif re.search( ">", when_cmd ):

    # '>'の位置を取得
    tmp1 = str(when_cmd.find('>'))

    # 左辺の文字列を取得
    tmp2 = register_cmd
    m = r.match(tmp2)
    tmp2 = m.group(1)
    tmp2 = tmp2.lstrip()
    tmp2 = tmp2.rstrip()

    # 右辺の文字列を取得
    tmp3 = when_cmd[int(tmp1)+1:]
    tmp3 = tmp3.lstrip()
    tmp3 = tmp3.rstrip()

    if len(tmp3) == 0:

      # 空の場合、1を返却する
      return 1

    # playbookの記述通りにif文
    if int(tmp2) > int(tmp3):
      # 一致する場合、0を返却する
      return 0

    else:
      # 一致しない場合、1を返却する
      return 1

  # 比較演算子("<=")の場合
  elif re.search( "<=", when_cmd ):

    # 後ろの'='の位置を取得
    tmp1 = str(when_cmd.rfind('='))

    # 左辺の文字列を取得
    tmp2 = register_cmd
    m = r.match(tmp2)
    tmp2 = m.group(1)
    tmp2 = tmp2.lstrip()
    tmp2 = tmp2.rstrip()

    # 右辺の文字列を取得
    tmp3 = when_cmd[int(tmp1)+1:]
    tmp3 = tmp3.lstrip()
    tmp3 = tmp3.rstrip()

    if len(tmp3) == 0:

      # 空の場合、1を返却する
      return 1

    # playbookの記述通りにif文
    if int(tmp2) <= int(tmp3):

      # 一致した場合、0を返却する
      return 0

    else:
      # 一致しない場合、1を返却する
      return 1

  # 比較演算子("<")の場合
  elif re.search( "<", when_cmd ):

    # '<'の位置を取得
    tmp1 = str(when_cmd.find('<'))

    # 左辺の文字列を取得
    tmp2 = register_cmd
    m = r.match(tmp2)
    tmp2 = m.group(1)
    tmp2 = tmp2.lstrip()
    tmp2 = tmp2.rstrip()

    # 右辺の文字列を取得
    tmp3 = when_cmd[int(tmp1)+1:]
    tmp3 = tmp3.lstrip()
    tmp3 = tmp3.rstrip()

    if len(tmp3) == 0:

      # 空の場合、1を返却する
      return 1

    # playbookの記述通りにif文
    if int(tmp2) < int(tmp3):
      # 一致する場合、0を返却する
      return 0

    else:
      # 一致しない場合、1を返却する
      return 1

main()
