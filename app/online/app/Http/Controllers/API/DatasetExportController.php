<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Contacts;
use App\Models\Stages;
use App\Models\DatasetGroups;
use App\Models\FivenineCallLogs;
use App\Models\Settings;
use App\Models\FivenineList;
use App\Models\TestJob;
use App\Models\ExportHistories;
use App\Events\ListInfoUpdate;
use App\Events\UserCreated;
use App\Events\MyEvent01;
use App\Events\Five9ListUpdate;
use Illuminate\Foundation\Events\Dispatchable;
use App\Jobs\MyJob;
use App\Jobs\ListJob;
use App\Jobs\ProcessFiveNineList;
use App\Jobs\Five9ListUpdation;

class DatasetExportController extends Controller
{
    public function export(Request $request){ 
        $recordsN = $request->input("records");
        $records = [];
        $ntype = $request->input('ntype');
        if($request->input('source') == 'call'){
            foreach($recordsN as $value){
                $records[] = [
                    'first_name' => $value['first_name'],
                    'last_name' => $value['last_name'],
                    'number1' => intval($value['dnis']),
                    'number2' => (isset($value['number2']))?intval($value['number2']):'',
                    'number3' => (isset($value['number3']))?intval($value['number3']):'',
                    'record_id' => $value['record_id'],
                    'company' => $value['company'],
                ];
            }
        }elseif($request->input('source') == 'callwn'){
            foreach($recordsN as $value){
                if(isset($records[$value['record_id']])){
                    $number2 = $records[$value['record_id']]['number2'];
                    if(is_null($number2)){
                        $records[$value['record_id']]['number2'] = intval($value['dnis']);
                    }else{
                        $number3 = $records[$value['record_id']]['number3'];
                        if(is_null($number3)){
                            $records[$value['record_id']]['number3'] = intval($value['dnis']);
                        }
                    }
                }else{                    
                    $records[$value['record_id']] = [
                        'first_name' => $value['first_name'],
                        'last_name' => $value['last_name'],
                        'number1' => intval($value['dnis']),
                        'number2' => null,
                        'number3' => null,
                        'record_id' => $value['record_id'],
                        'company' => $value['company'],
                    ];
                }
            }
        }else{
            foreach($recordsN as $value){
                $records[] = [
                    'first_name' => $value['first_name'],
                    'last_name' => $value['last_name'],
                    'number1' => intval($value['number1']),
                    'number2' => (isset($value['number2']))?intval($value['number2']):'',
                    'number3' => (isset($value['number3']))?intval($value['number3']):'',
                    'record_id' => $value['record_id'],
                    'company' => $value['company'],
                ];
            }
        }
        $keys = array_keys(array_pop($records));
        if($request->input("action") == 'create'){
            $listName = $request->input("list_name");
            $resultCreate = $this->createNewList($listName);
            if($resultCreate["status"] == "error"){
                return ["status" => "error", "message" => $resultCreate["message"]];
            }else{
                $results = $this->addToListCsv($keys, $listName, $records); 
                $record = get_object_vars(DB::table("fivenine_lists")->where("name", "LIKE", $listName)->first());
                $id = $record["id"];
                $oldhistory = $record["history"];
                if(is_null($oldhistory) || $oldhistory == ''){
                    $history = [$results["message"]["return"]["identifier"]];
                }else{
                    $his = json_decode($oldhistory);
                    array_push($his, $results["message"]["return"]["identifier"]);
                    $history = $his;
                }
                DB::table("fivenine_lists")->where("id", "=", $id)->update([
                    "history" => json_encode($history),
                    "status" => 0,
                    "updated_at" => date("Y-m-d H:i:s", strtotime("now"))
                ]);
                //MyNewJob::dispatch(new MyEvent01(["listName" => $listName, "identifier" => $results["message"]["return"]["identifier"]]))->onConnection('database');
                return ["status" => "success"];
            }
        }elseif($request->input("action") == 'update'){
            $code = $request->input("code");
            if(DB::table("export_histories")->where("code", "LIKE", $request->input("code"))->count() == 0){
                //create record in history table 
                $listArray = array_column($request->input("ac_list"), "name");
                $nlistArray = [];
                $alistArray = [];
                foreach($listArray as $value){
                    $alistArray[] = $value;
                    $nlistArray[$value] = null;
                }
                DB::table("export_histories")->insert([
                    "code" => $code,
                    "type" => "dataset",
                    "total" => count($records)*count($listArray),
                    "mul_identifier" => json_encode($nlistArray),
                    "report" => json_encode($nlistArray),
                    "lists" => json_encode($alistArray),
                    "status" => 1,
                    "file_name" => "DATASET-EXPORT-".date("Ymd", strtotime("now")),
                    "created_at" => date("Y-m-d H:i:s", strtotime("now")),
                    "updated_at" => date("Y-m-d H:i:s", strtotime("now")),
                    "data" => json_encode($records),
                    "user_id" => 1,
                    "identifier" => $code,
                    "arraykeys" => json_encode($keys)
                ]);
            }
            $dataset = DB::table("export_histories")->orderBy('id', 'desc')->first();
            $listName = $request->input("acp_list");
            $results = $this->addToListCsv($keys, $listName, $records);
            if(isset($results["message"]["return"]["identifier"])){
                $record = get_object_vars(DB::table("fivenine_lists")->where("name", "LIKE", $listName)->first());
                $id = $record["id"];
                $oldhistory = $record["history"];
                if(is_null($oldhistory) || $oldhistory == ''){
                    $history = [$results["message"]["return"]["identifier"]];
                }else{
                    $his = json_decode($oldhistory);
                    array_push($his, $results["message"]["return"]["identifier"]);
                    $history = $his;
                }
                DB::table("fivenine_lists")->where("id", "=", $id)->update([
                    "history" => json_encode($history),
                    "status" => 0,
                    "updated_at" => date("Y-m-d H:i:s", strtotime("now"))
                ]);
                //ProcessFiveNineList::dispatch(["listName" => $listName, "identifier" => $results["message"]["return"]["identifier"], 'code' => $code])->onConnection('database');
                return ["status" => "success", "results" => $results, "list_name" => $listName];
            }else{
                return ["status" => "error", "results" => $results, "list_name" => $listName];
            }
        }elseif($request->input("action") == 'delete'){
            $code = $request->input("code");
            if(DB::table("export_histories")->where("code", "LIKE", $request->input("code"))->count() == 0){
                //create record in history table 
                $listArray = array_column($request->input("ac_list"), "name");
                $nlistArray = [];
                $alistArray = [];
                foreach($listArray as $value){
                    $alistArray[] = $value;
                    $nlistArray[$value] = null;
                }
                DB::table("export_histories")->insert([
                    "code" => $code,
                    "type" => "dataset",
                    "total" => count($records)*count($listArray),
                    "mul_identifier" => json_encode($nlistArray),
                    "report" => json_encode($nlistArray),
                    "lists" => json_encode($alistArray),
                    "status" => 1,
                    "file_name" => "DATASET-EXPORT-".date("Ymd", strtotime("now")),
                    "created_at" => date("Y-m-d H:i:s", strtotime("now")),
                    "updated_at" => date("Y-m-d H:i:s", strtotime("now")),
                    "data" => json_encode($records),
                    "user_id" => 1,
                    "identifier" => $code,
                    "arraykeys" => json_encode($keys)
                ]);
            }
            // now we need to delete all records from given list
            
            $listName = $request->input("acp_listDelete");
            $results = $this->deleteFromList($keys, $listName, $records);
            if(isset($results["message"]["return"]["identifier"])){
                $record = get_object_vars(DB::table("fivenine_lists")->where("name", "LIKE", $listName)->first());
                $id = $record["id"];
                $oldhistory = $record["history"];
                if(is_null($oldhistory) || $oldhistory == ''){
                    $history = [$results["message"]["return"]["identifier"]];
                }else{
                    $his = json_decode($oldhistory);
                    array_push($his, $results["message"]["return"]["identifier"]);
                    $history = $his;
                }
                DB::table("fivenine_lists")->where("id", "=", $id)->update([
                    "history" => json_encode($history),
                    "status" => 0,
                    "updated_at" => date("Y-m-d H:i:s", strtotime("now"))
                ]);
                //ProcessFiveNineList::dispatch(["listName" => $listName, "identifier" => $results["message"]["return"]["identifier"], 'code' => $code])->onConnection('database');
                return ["status" => "success", "results" => $results, "list_name" => $listName];
            }else{
                return ["status" => "error", "results" => $results, "list_name" => $listName];
            }
        }
    }
    public function getReportResponse($id = null)
    {
        $curl = curl_init();
        for ($i=0; $i < 10; $i++) {
            sleep(1);     
            $setting1 = Settings::where('id', '=', 37)->first();
            $setting2 = Settings::where('id', '=', 38)->first();
            $username = $setting1->value;
            $password = $setting2->value;
            $code = base64_encode($username.':'.$password);    
            curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.five9.com/wsadmin/v4/AdminWebService',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS =>'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.admin.ws.five9.com/">;
            <soapenv:Header/>
            <soapenv:Body>
                <ser:isReportRunning>
                    <identifier>'.$id.'</identifier>
                </ser:isReportRunning>
            </soapenv:Body>
            </soapenv:Envelope>',
            CURLOPT_HTTPHEADER => array(
                "Authorization: Basic $code",
                "Content-Type: application/xml",
                "Cookie: clientId=8998A130504F4358BADF215E686E2E8B; Authorization=Bearer-ebfa8d5a-ba2a-11eb-abfe-00505fca8def; apiRouteKey=SCLnAPIc6f; app_key=web-ui; farmId=182; uiRouteKey=SCLnUI5551"
            ),
            ));
    
            $response = curl_exec($curl); 
            $result = substr($response, strpos($response, '<return>')+8, strrpos($response, '</return>') - strpos($response, '<return>')-8);
            if($result == 'false'){
                break;
            }
        }
        curl_close($curl);
        return ['result' => true];
    }
    public function getListImportResult($id = null){
        $postFields = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.admin.ws.five9.com/">
            <soapenv:Header/>
            <soapenv:Body>
            <ser:getListImportResult>
                <!--Optional:-->
                <identifier>
                    <!--Optional:-->
                    <identifier>'.$id.'</identifier>
                </identifier>
            </ser:getListImportResult>
            </soapenv:Body>
        </soapenv:Envelope>';
        $curl = curl_init();
        $setting1 = Settings::where('id', '=', 37)->first();
        $setting2 = Settings::where('id', '=', 38)->first();
        $username = $setting1->value;
        $password = $setting2->value;
        $code = base64_encode($username.':'.$password);
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.five9.com/wsadmin/v4/AdminWebService',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Basic $code",
                'Content-Type: application/xml',
                'Cookie: Authorization=Bearer-ebfa8d5a-ba2a-11eb-abfe-00505fca8def; apiRouteKey=SCLnAPIc6f; app_key=web-ui; farmId=182; uiRouteKey=SCLnUI5551'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $this->__XMLtoJSON($response, 'getListImportResult');
    }    
    
    public function addToListCsv($keys, $listName, $data)
    {
        $k = [];
        foreach($keys as $value){
            if($value != 'company'){
                $k[] = $value;
            }
        }
        $keys = $k;
        $keys[count($keys)] = 'company';
        $efields = '';
        $ck = 0;
        foreach ($keys as $value) {
            if($value == 'number1') : $k = true; else: $k = false; endif;
            if(in_array($value,  ['stage', 'tag'])):
                $efields.='<fieldsMapping><columnNumber>'.(++$ck).'</columnNumber><fieldName>'.ucwords($value).'</fieldName><key>'.$k.'</key></fieldsMapping>';
            else:
                $efields.='<fieldsMapping><columnNumber>'.(++$ck).'</columnNumber><fieldName>'.$value.'</fieldName><key>'.$k.'</key></fieldsMapping>';
            endif;
        }
        $edata = '';
        foreach ($data as $dkey => $dvalue) : 
            if($dvalue['number1'] != 0):
                foreach($keys as $value):
                    if( ($value != '') && ($value != "country") && ($value != "Last_Dispo") && ($value != "DIAL ATTEMPTS") && ($value != "CONTACT ID") ) : 
                        $dvalue[$value] = str_replace(',', '-', $dvalue[$value]);
                        $sal = (isset($dvalue[$value]))?$dvalue[$value]:'0';
                        $sal = str_replace('&', ' and ', $sal);
                        $sal = str_replace('(', '', $sal);
                        $sal = str_replace(')', '', $sal);
                        $sal = str_replace('.', '', $sal);
                        $sal = str_replace("'", ' ', $sal);
                        $edata .= $sal.',';
                    endif;
                endforeach;
            endif;
            $edata.='&#xA;';
        endforeach;

        $postFields = '<env:Envelope xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:ser="http://service.admin.ws.five9.com/" xmlns:env="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ins0="http://jaxb.dev.java.net/array">
        <env:Header/>
        <env:Body>
            <ser:addToListCsv>
                <listName>'.$listName.'</listName>
                <listUpdateSettings>
                    '.$efields.'
                    <allowDataCleanup>true</allowDataCleanup>
                    <reportEmail>anandprakash@biorev.studio</reportEmail>
                    <separator>,</separator>
                    <skipHeaderLine>false</skipHeaderLine>
                    <cleanListBeforeUpdate>false</cleanListBeforeUpdate>
                    <crmAddMode>ADD_NEW</crmAddMode>
                    <crmUpdateMode>UPDATE_ALL</crmUpdateMode>
                    <listAddMode>ADD_ALL</listAddMode>
                </listUpdateSettings>
                <csvData>'.$edata.'</csvData>
            </ser:addToListCsv>
        </env:Body>
        </env:Envelope>';
        //dd($postFields);
        $curl = curl_init();
        $setting1 = Settings::where('id', '=', 37)->first();
        $setting2 = Settings::where('id', '=', 38)->first();
        $username = $setting1->value;
        $password = $setting2->value;
        $code = base64_encode($username.':'.$password);
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.five9.com/wsadmin/v4/AdminWebService',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Basic $code",
                'Content-Type: application/xml',
                'Cookie: Authorization=Bearer-ebfa8d5a-ba2a-11eb-abfe-00505fca8def; apiRouteKey=SCLnAPIc6f; app_key=web-ui; farmId=182; uiRouteKey=SCLnUI5551'
            ),
        ));
        $response = curl_exec($curl);
        //$this->deleteSelectedFromList($request);
        return $this->__XMLtoJSON($response, 'addToListCsv');
    }
    private function __XMLtoJSON($response, $name) {
        $var = 'ns2'.$name.'Response';
        $xml = $response;
        // SimpleXML seems to have problems with the colon ":" in the <xxx:yyy> response tags, so take them out
        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $xml);
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $return = json_decode($json,true);
        if(array_key_exists($var, $return['envBody'])):
            return ['message' => $return['envBody']["$var"], 'status' => 'success'];
        else:
            return ['message' => $return['envBody']['envFault']['faultstring'], 'status' => 'error'];
        endif;
    }
    public function deleteFromList($keys, $listName, $data){
        
        $efields = '';
        $ck = 0;

        foreach ($keys as $value) {
            if($value == 'record_id') : $k = true; else: $k = false; endif;
            if(in_array($value,  ['stage', 'tag'])):
                $efields.='<fieldsMapping><columnNumber>'.(++$ck).'</columnNumber><fieldName>'.ucwords($value).'</fieldName><key>'.$k.'</key></fieldsMapping>';
            else:
                $efields.='<fieldsMapping><columnNumber>'.(++$ck).'</columnNumber><fieldName>'.$value.'</fieldName><key>'.$k.'</key></fieldsMapping>';
            endif;
        }
        $edata = '';
        foreach ($data as $dkey => $dvalue) : 
            if($dvalue['record_id'] != 0):
                foreach($keys as $value):
                    if( ($value != '') && ($value != "country") && ($value != "Last_Dispo") && ($value != "DIAL ATTEMPTS") && ($value != "CONTACT ID") ) : 
                        $sal = (isset($dvalue[$value]))?$dvalue[$value]:'0';
                        $sal = str_replace('&', ' and ', $sal);
                        $sal = str_replace('(', '', $sal);
                        $sal = str_replace(')', '', $sal);
                        $sal = str_replace('.', '', $sal);
                        $sal = str_replace("'", ' ', $sal);
                        $edata .= $sal.',';
                    endif;
                endforeach;
            endif;
            $edata.='&#xA;';
        endforeach;
        $curl = curl_init();
        $deleteFromListCsv = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.admin.ws.five9.com/">
            <soapenv:Header/>
                <soapenv:Body>
                <ser:deleteFromListCsv>
                    <!--Optional:-->
                    <listName>'.$listName.'</listName>
                    <!--Optional:-->
                    <listDeleteSettings>
                        <!--Optional:-->
                        <allowDataCleanup>False</allowDataCleanup>
                        <failOnFieldParseError>False</failOnFieldParseError>
                        <!--Zero or more repetitions:-->
                        '.$efields.'
                        <reportEmail>anandprakash@biorev.studio</reportEmail>
                        <separator>,</separator>
                        <skipHeaderLine>false</skipHeaderLine>
                        <!--Optional:-->
                        <listDeleteMode>DELETE_ALL</listDeleteMode>
                    </listDeleteSettings>
                    <!--Optional:-->
                    <csvData>'.$edata.'</csvData>
                </ser:deleteFromListCsv>
                </soapenv:Body>
            </soapenv:Envelope>';
        $setting1 = Settings::where('id', '=', 37)->first();
        $setting2 = Settings::where('id', '=', 38)->first();
        $username = $setting1->value;
        $password = $setting2->value;
        $code = base64_encode($username.':'.$password);
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.five9.com/wsadmin/v4/AdminWebService',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => $deleteFromListCsv,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                "Authorization: Basic $code",
                "Cookie: clientId=C4A452B18E5646FA9ACBE8A819154296; Authorization=Bearer-ebfa8d5a-ba2a-11eb-abfe-00505fca8def; apiRouteKey=SCLnAPIc6f; app_key=web-ui; farmId=182; uiRouteKey=SCLnUI5551"
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $this->__XMLtoJSON($response, 'deleteFromListCsv');
        
    }
    
    public function createNewList($listName = null){
        $curl = curl_init();
        $setting1 = Settings::where('id', '=', 37)->first();
        $setting2 = Settings::where('id', '=', 38)->first();
        $username = $setting1->value;
        $password = $setting2->value;
        $code = base64_encode($username.':'.$password);
        
        $postFields = '<env:Envelope xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:ser="http://service.admin.ws.five9.com/" xmlns:env="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ins0="http://jaxb.dev.java.net/array">
        <env:Header/>
        <env:Body>
            <ser:createList>
                <listName>'.$listName.'</listName>
            </ser:createList>
        </env:Body>
        </env:Envelope>';
        
        $curl = curl_init();
        $setting1 = Settings::where('id', '=', 37)->first();
        $setting2 = Settings::where('id', '=', 38)->first();
        $username = $setting1->value;
        $password = $setting2->value;
        $code = base64_encode($username.':'.$password);
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.five9.com/wsadmin/v4/AdminWebService',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Basic $code",
                'Content-Type: application/xml',
                'Cookie: Authorization=Bearer-ebfa8d5a-ba2a-11eb-abfe-00505fca8def; apiRouteKey=SCLnAPIc6f; app_key=web-ui; farmId=182; uiRouteKey=SCLnUI5551'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        DB::table("fivenine_lists")->insert([
            "name" => $listName,
            "size" => 0
        ]);
        return $this->__XMLtoJSON($response, 'createList');
    }
    
    public function deleteList($listName = null){
        $postFields = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.admin.ws.five9.com/">
            <soapenv:Header/>
            <soapenv:Body>
            <ser:deleteList>
                <!--Optional:-->
                <listName>'.$listName.'</listName>
            </ser:deleteList>
            </soapenv:Body>
        </soapenv:Envelope>';
        $curl = curl_init();
        $setting1 = Settings::where('id', '=', 37)->first();
        $setting2 = Settings::where('id', '=', 38)->first();
        $username = $setting1->value;
        $password = $setting2->value;
        $code = base64_encode($username.':'.$password);
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.five9.com/wsadmin/v4/AdminWebService',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Basic $code",
                'Content-Type: application/xml',
                'Cookie: Authorization=Bearer-ebfa8d5a-ba2a-11eb-abfe-00505fca8def; apiRouteKey=SCLnAPIc6f; app_key=web-ui; farmId=182; uiRouteKey=SCLnUI5551'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $this->__XMLtoJSON($response, 'deleteList');
    }
    function randomString(){
        $length = 25;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomStr = '';
        for ($i = 0; $i < $length; $i++) {
            $randomStr .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomStr;
    }
    public function textlist(){
        
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 25; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        $listName = "test-ankit-01";
        $record = get_object_vars(DB::table("fivenine_lists")->where("name", "LIKE", $listName)->first());
        $id = $record["id"];
        $oldhistory = $record["history"];
        if(is_null($oldhistory)){
            $history[] = $randomString;
        }else{
            $his = json_decode($oldhistory);
            array_push($his, $randomString);
            $history =  $his;
        }
        DB::table("fivenine_lists")->where("id", "=", $id)->update([
            "history" => json_encode($history),
            "updated_at" => date("Y-m-d H:i:s", strtotime("now"))
        ]);
    }

    public function test(){
        MyJob::dispatch(new ListInfoUpdate(["listName" => "AnkitNewTest12", "identifier" => "c535a046-8dd5-48b6-a3dc-d0b0e29faf2f"]))->onConnection('database');
    }
    public function testb(){
        event(new UserCreated([
            "message" => "event fired",
            "status" => "success"
        ]));
    }
    function testjob(){
        MyNewJob::dispatch(new MyEvent01(["listName" => "AnkitNewTest1202", "identifier" => "c535a046-8dd5-48b6-a3dc-d0b0e29faf2f"]))->onConnection('database');
    }
    public function testlist()
    {
            if(DB::table("export_histories")->where('lists',"!=","")->where("status", "=", 1)->whereNotNull("lists")->orderBy("id", "desc")->count() == 0){
                return false;
            }
            //ini_set('max_execution_time', 100);
            $data = DB::table("fivenine_lists")->where("status", "=", 0)->first();
            if(DB::table("fivenine_lists")->where("status", "=", 0)->count() == 0){
                return false;
            }
            $data = DB::table("fivenine_lists")->where("status", "=", 0)->first();
            $data = get_object_vars($data);
            $listName = $data["name"];
            $his = json_decode($data["history"]);
            $id = $his[count($his)-1];
            
            $exHistoryRecord = get_object_vars(DB::table("export_histories")->where('lists',"!=","")->where("status", "=", 1)->whereNotNull("lists")->orderBy("id", "desc")->first());

            //need to get import result 
            $curl = curl_init();
            for ($i=0; $i < 10; $i++) {
                sleep(1);     
                $setting1 = Settings::where('id', '=', 37)->first();
                $setting2 = Settings::where('id', '=', 38)->first();
                $username = $setting1->value;
                $password = $setting2->value;
                $code = base64_encode($username.':'.$password);    
                curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.five9.com/wsadmin/v4/AdminWebService',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_POSTFIELDS =>'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.admin.ws.five9.com/">;
                <soapenv:Header/>
                <soapenv:Body>
                    <ser:isReportRunning>
                        <identifier>'.$id.'</identifier>
                    </ser:isReportRunning>
                </soapenv:Body>
                </soapenv:Envelope>',
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Basic $code",
                    "Content-Type: application/xml",
                    "Cookie: clientId=8998A130504F4358BADF215E686E2E8B; Authorization=Bearer-ebfa8d5a-ba2a-11eb-abfe-00505fca8def; apiRouteKey=SCLnAPIc6f; app_key=web-ui; farmId=182; uiRouteKey=SCLnUI5551"
                ),
                ));
                
                $response = curl_exec($curl); 
                $result = substr($response, strpos($response, '<return>')+8, strrpos($response, '</return>') - strpos($response, '<return>')-8);
                if($result == 'false'){                
                    break;
                }
            }
            curl_close($curl);
            //get import result
            $postFields = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.admin.ws.five9.com/">
                <soapenv:Header/>
                <soapenv:Body>
                <ser:getListImportResult>
                    <!--Optional:-->
                    <identifier>
                        <!--Optional:-->
                        <identifier>'.$id.'</identifier>
                    </identifier>
                </ser:getListImportResult>
                </soapenv:Body>
            </soapenv:Envelope>';
            $setting1 = Settings::where('id', '=', 37)->first();
            $setting2 = Settings::where('id', '=', 38)->first();
            $username = $setting1->value;
            $password = $setting2->value;
            $code = base64_encode($username.':'.$password);

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.five9.com/wsadmin/v4/AdminWebService',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_POSTFIELDS => $postFields,
                CURLOPT_HTTPHEADER => array(
                "Authorization: Basic $code",
                    "Content-Type: application/xml",
                    "Cookie: clientId=C4A452B18E5646FA9ACBE8A819154296; Authorization=Bearer-ebfa8d5a-ba2a-11eb-abfe-00505fca8def; apiRouteKey=SCLnAPIc6f; app_key=web-ui; farmId=182; uiRouteKey=SCLnUI5551"
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            $name = "getListImportResult";
            $var = 'ns2'.$name.'Response';
            $xml = $response;
            $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $xml);
            $xml = simplexml_load_string($xml);
            $json = json_encode($xml);
            $return = json_decode($json,true);
            if(array_key_exists($var, $return['envBody'])):
                $results01 = $return['envBody']["$var"];
            else:
                return false;
            endif;
            //save particualr list result in export-history table
            $mul_identifier = json_decode($exHistoryRecord["mul_identifier"]);
            $mul_identifierNew = [];
            foreach($mul_identifier as $key => $mlist){
                if($key == $listName){
                    $mul_identifierNew[$key] = $results01;
                }else{
                    $mul_identifierNew[$key] = $mlist;
                }
            }
            $expLists = json_decode($exHistoryRecord["lists"]);
            $expListsNew = [];
            foreach($expLists as $value){
                if($value != $listName){
                    $expListsNew[] = $value;
                }
            }
            DB::table("export_histories")->where("id", "=", $exHistoryRecord)->update([
                "mul_identifier" => json_encode($mul_identifierNew),
                "lists" => (count($expListsNew) > 0) ? json_encode($expListsNew) : null,
                "inserted" => $exHistoryRecord["inserted"] + $results01["message"]["return"]["crmRecordsInserted"],
                "updated" => $exHistoryRecord["updated"] + $results01["message"]["return"]["crmRecordsUpdated"],
            ]);
            
            //get list
            $list = DB::table("fivenine_lists")->where("name", "=", $listName)->first();
            $list = get_object_vars($list);
            $listName = $list["name"];
            $listId = $list["id"];
            //echo "<pre>"; print_r($listName); echo "</pre>";
            
            $start = date("Y-m-d", strtotime("-6 years")).'T'.date("H:i:s", strtotime("-6 years"));
            $end = date("Y-m-d", strtotime("now")).'T'.date("H:i:s", strtotime("now"));
            $postFields = '<env:Envelope xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:tns="http://service.admin.ws.five9.com/" xmlns:env="http://schemas.xmlsoap.org/soap/envelope/"
            xmlns:ins0="http://jaxb.dev.java.net/array">
            <env:Body>
                <tns:runReport>
                    <folderName>My Reports</folderName>
                    <reportName>List All '.$listName.'</reportName>
                        <criteria>
                            <time>
                            <end>'.$end.'</end>
                            <start>'.$start.'</start>
                            </time>
                        </criteria>
                </tns:runReport>
            </env:Body>
            </env:Envelope>';
            $curl = curl_init();
            $setting1 = Settings::where('id', '=', 37)->first();
            $setting2 = Settings::where('id', '=', 38)->first();
            $username = $setting1->value;
            $password = $setting2->value;
            
            $code = base64_encode($username.':'.$password);
            
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.five9.com/wsadmin/v4/AdminWebService',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_POSTFIELDS => $postFields,
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Basic $code",
                    "Content-Type: application/xml",
                    "Cookie: Authorization=Bearer-ebfa8d5a-ba2a-11eb-abfe-00505fca8def; apiRouteKey=SCLnAPIc6f; app_key=web-ui; farmId=182; uiRouteKey=SCLnUI5551"
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            $xml = $response;
            // SimpleXML seems to have problems with the colon ":" in the <xxx:yyy> response tags, so take them out
            $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $xml);
            $xml = simplexml_load_string($xml);
            $json = json_encode($xml);
            $return = json_decode($json,true);
            if(isset($return["envBody"]["envFault"])):
                return ['restults' => 'fault'];
            endif;
            $id = substr($response, strpos($response, '<return>')+8, strrpos($response, '</return>') - strpos($response, '<return>')-8);
        
            $curl = curl_init();
            for ($i=0; $i < 10; $i++) {
                sleep(1);     
                $setting1 = Settings::where('id', '=', 37)->first();
                $setting2 = Settings::where('id', '=', 38)->first();
                $username = $setting1->value;
                $password = $setting2->value;
                $code = base64_encode($username.':'.$password);    
                curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.five9.com/wsadmin/v4/AdminWebService',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_POSTFIELDS =>'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.admin.ws.five9.com/">;
                <soapenv:Header/>
                <soapenv:Body>
                    <ser:isReportRunning>
                        <identifier>'.$id.'</identifier>
                    </ser:isReportRunning>
                </soapenv:Body>
                </soapenv:Envelope>',
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Basic $code",
                    "Content-Type: application/xml",
                    "Cookie: clientId=8998A130504F4358BADF215E686E2E8B; Authorization=Bearer-ebfa8d5a-ba2a-11eb-abfe-00505fca8def; apiRouteKey=SCLnAPIc6f; app_key=web-ui; farmId=182; uiRouteKey=SCLnUI5551"
                ),
                ));
        
                $response = curl_exec($curl); 
                $result = substr($response, strpos($response, '<return>')+8, strrpos($response, '</return>') - strpos($response, '<return>')-8);
                if($result == 'false'){
                    break;
                }
            }
            curl_close($curl);
        
            $curl = curl_init();
            $setting1 = Settings::where('id', '=', 37)->first();
            $setting2 = Settings::where('id', '=', 38)->first();
            $username = $setting1->value;
            $password = $setting2->value;
            $code = base64_encode($username.':'.$password);
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.five9.com/wsadmin/v4/AdminWebService',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_POSTFIELDS =>'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.admin.ws.five9.com/">;
                <soapenv:Header/>
                <soapenv:Body>
                    <ser:getReportResult>
                        <!--Optional:-->
                        <identifier>'.$id.'</identifier>
                    </ser:getReportResult>
                </soapenv:Body>
                </soapenv:Envelope>',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    "Authorization: Basic $code",
                    "Cookie: clientId=C4A452B18E5646FA9ACBE8A819154296; Authorization=Bearer-ebfa8d5a-ba2a-11eb-abfe-00505fca8def; apiRouteKey=SCLnAPIc6f; app_key=web-ui; farmId=182; uiRouteKey=SCLnUI5551"
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            $xml = $response;
            // SimpleXML seems to have problems with the colon ":" in the <xxx:yyy> response tags, so take them out
            $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $xml);
            $xml = simplexml_load_string($xml);
            $json = json_encode($xml);
            $return = json_decode($json,true);
            $header = $return["envBody"]["ns2getReportResultResponse"]["return"]["header"]["values"]["data"];
            $headerOld = $header;
            foreach($header as $key => $value):
                if(strpos($value, " ")):
                    $e = explode(" ", $value);
                    $i = implode("_", $e);
                    $header[$key] = $i;
                else:
                    $header[$key] = $value;
                endif;
            endforeach; 
            $counter = 0;
            $contactIds = [];
            $allRecords = []; 
            if(isset($return["envBody"]["ns2getReportResultResponse"]["return"]["records"])):
                $records = $return["envBody"]["ns2getReportResultResponse"]["return"]["records"];
                DB::table("fivenine_list_contacts")->where("fivenine_list_id", "=", $listId)->delete();
                foreach($records as $key => $value):
                    if(isset($value["data"])){
                        $record["record_id"] = is_array($value["data"][3]) ? null : intval($value["data"][3]);
                        $record["number1"] = is_array($value["data"][4]) ? null : intval($value["data"][4]);
                        $record["number2"] = is_array($value["data"][5]) ? null : intval($value["data"][5]);
                        $record["number3"] = is_array($value["data"][6]) ? null : intval($value["data"][6]);                        
                    }else{
                        $record["record_id"] = is_array($value["values"]["data"][3]) ? null : intval($value["values"]["data"][3]);
                        $record["number1"] = is_array($value["values"]["data"][4]) ? null : intval($value["values"]["data"][4]);
                        $record["number2"] = is_array($value["values"]["data"][5]) ? null : intval($value["values"]["data"][5]);
                        $record["number3"] = is_array($value["values"]["data"][6]) ? null : intval($value["values"]["data"][6]);
                    }
                    
                    $count = DB::table("fivenine_list_contacts")->where("fivenine_list_id", "=", $listId)->where("record_id", "=", $record["record_id"])->count();
                    if($count == 0){
                        DB::table("fivenine_list_contacts")->insert([
                            "fivenine_list_id" => $listId,
                            "number1" => $record["number1"],
                            "record_id" => $record["record_id"],
                            "number2" => $record["number2"],
                            "number3" => $record["number3"],
    
                        ]);
                    }
                endforeach;
                $listItemCounter = DB::table("fivenine_list_contacts")->where("fivenine_list_id", "=", $listId)->count();
                DB::table("fivenine_lists")->where("id", "=", $listId)->update([
                    "size" => $listItemCounter,
                    "status" => 1,
                    "updated_at" => date("Y-m-d H:i:s", strtotime("now"))
                ]);

            else:
                DB::table("fivenine_lists")->where("id", "=", $listId)->update([
                    "size" => 0,
                    "updated_at" => date("Y-m-d H:i:s", strtotime("now"))
                ]);
                DB::table("fivenine_list_contacts")->where("fivenine_list_id", "=", $listId)->delete();
            endif;
        
        //}
    }

    function five9ListUpdate(){
        
        $list = FivenineList::where('id', 84)->first();
        $exportHistory = ExportHistories::where("id", 64)->first();
        event( new Five9ListUpdate($list, $exportHistory));
        // Five9ListUpdation::dispatch($list, $exportHistory);
        
    }
}
