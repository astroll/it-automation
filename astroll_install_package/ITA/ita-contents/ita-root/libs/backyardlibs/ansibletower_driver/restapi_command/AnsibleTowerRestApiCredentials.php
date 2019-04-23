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
//////////////////////////////////////////////////////////////////////
//
//  【概要】
//      AnsibleTower RestApi Credential系を呼ぶ クラス
//
//////////////////////////////////////////////////////////////////////

////////////////////////////////
// ルートディレクトリを取得
////////////////////////////////
if(empty($root_dir_path)) {
    $root_dir_temp = array();
    $root_dir_temp = explode("ita-root", dirname(__FILE__));
    $root_dir_path = $root_dir_temp[0] . "ita-root";
}

require_once($root_dir_path . "/libs/backyardlibs/ansibletower_driver/restapi_command/AnsibleTowerRestApiBase.php");

class AnsibleTowerRestApiCredentials extends AnsibleTowerRestApiBase {

    const API_PATH  = "credentials/";
    const IDENTIFIED_NAME_PREFIX = "ita_executions_credential_";

    const PREPARE_BUILD_CREDENTIAL_NAME = "ita_executions_local";

    const MACHINE = 1;

    // static only
    private function __construct() {
    }

    static function getAll($RestApiCaller, $query = "") {

        // REST APIアクセス
        $method = "GET";
        $response_array = $RestApiCaller->restCall($method, self::API_PATH . $query);

        // REST失敗
        if($response_array['statusCode'] != 200) {
            $response_array['success'] = false;
            return $response_array;
        }

        // REST成功
        $response_array['success'] = true;
        $response_array['responseContents'] = $response_array['responseContents']['results'];

        return $response_array;
    }

    static function get($RestApiCaller, $id) {

        // REST APIアクセス
        $method = "GET";
        $response_array = $RestApiCaller->restCall($method, self::API_PATH . $id . "/");

        // REST失敗
        if($response_array['statusCode'] != 200) {
            $response_array['success'] = false;
            return $response_array;
        }

        // REST成功
        $response_array['success'] = true;

        return $response_array;
    }

    static function post($RestApiCaller, $param) {

        // content生成
        $content = array();

        if(!empty($param['execution_no']) && !empty($param['loopCount'])) {
            $content['name'] = self::createName(self::IDENTIFIED_NAME_PREFIX, $param['execution_no'], $param['loopCount']);
        } else {
            // 必須のためNG返す
            $response_array['success'] = false;
            $response_array['responseContents']['errorMessage'] = "Need 'execution_no' and 'loopCount'.";
            return $response_array;
        }

        $content['credential_type'] = self::MACHINE; // 現在[Machine]固定

        // organization/user/team どれかひとつの指定が必要
        $content['organization'] = self::ORG_DEFAULT; // 現在[Default]固定

        if(!empty($param['username'])) {
            $content['inputs']['username'] = $param['username'];
        } // 任意パラメータは無くてもNG返さない

        if(!empty($param['password'])) {
            $content['inputs']['password'] = ky_decrypt($param['password']);
        } // 任意パラメータは無くてもNG返さない

        if(!empty($param['ssh_private_key'])) {
            $content['inputs']['ssh_key_data'] = $param['ssh_private_key'];
        } // 任意パラメータは無くてもNG返さない

        // REST APIアクセス
        $method = "POST";
        $response_array = $RestApiCaller->restCall($method, self::API_PATH, $content);

        // REST失敗
        if($response_array['statusCode'] != 201) {
            $response_array['success'] = false;
            if(!array_key_exists("errorMessage", $response_array['responseContents'])) {
                $response_array['responseContents']['errorMessage'] = "status_code not 201. =>" . $response_array['statusCode'];
            }
            return $response_array;
        }

        // REST成功
        $response_array['success'] = true;

        return $response_array;
    }

    static function delete($RestApiCaller, $id) {

        // REST APIアクセス
        $method = "DELETE";
        $response_array = $RestApiCaller->restCall($method, self::API_PATH . $id . "/");

        // REST失敗
        if($response_array['statusCode'] != 204) {
            $response_array['success'] = false;
            if(!array_key_exists("errorMessage", $response_array['responseContents'])) {
                $response_array['responseContents']['errorMessage'] = "status_code not 204. =>" . $response_array['statusCode'];
            }
            return $response_array;
        }

        // REST成功
        $response_array['success'] = true;

        return $response_array;
    }

    static function deleteRelatedCurrnetExecution($RestApiCaller, $execution_no) {

        // データ絞り込み
        $filteringName = self::createName(self::IDENTIFIED_NAME_PREFIX, $execution_no) . "_";
        $query = "?name__startswith=" . $filteringName;
        $pickup_response_array = self::getAll($RestApiCaller, $query);
        if($pickup_response_array['success'] == false) {
            return $pickup_response_array;
        }

        foreach($pickup_response_array['responseContents'] as $credentialData) {

            $response_array = self::delete($RestApiCaller, $credentialData['id']);
            if($response_array['success'] == false) {
                return $response_array;
            }
        }

        return $pickup_response_array; // データ不足しているが、後続の処理はsuccessしか確認しないためこのまま
    }

}

?>
