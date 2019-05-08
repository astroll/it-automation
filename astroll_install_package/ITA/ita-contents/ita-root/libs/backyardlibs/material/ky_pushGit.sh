#!/bin/sh
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

GIT_OPTION=$1
REMOTE_PASSWORD=$2

expect -c "

set timeout 5
spawn git ${GIT_OPTION} push

expect {
    -re \"assword.*: \" {
        send \"${REMOTE_PASSWORD}\n\"
        expect {
            default {
                catch wait result

                set OS_ERROR [ lindex \$result 2 ]
                if { \$OS_ERROR == -1 } {
                        exit 255
                }
                set STATUS [ lindex \$result 3 ]
                exit \$STATUS
            }
            \"Permission denied\" {
                exit 253
            }
            timeout {exit 254}
        }
    }
    default {
        catch wait result

        set OS_ERROR [ lindex \$result 2 ]
        if { \$OS_ERROR == -1 } {
                exit 255
        }
        set STATUS [ lindex \$result 3 ]
        exit \$STATUS
    }
    timeout {exit 254}
}
"

exit $?

