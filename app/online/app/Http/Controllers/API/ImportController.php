<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Settings;
use App\Models\ExportHistories;
use Biorev\Fivenine\Fivenine;

class ImportController extends SettingsController
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getHistoryExport()
    {
        $records = ExportHistories::with('user')->orderByDesc('created_at')->paginate(20);        
        return [ 'records' => $records];
    }

    public function getSingleHistoryExport($id = null)
    { 
        $record = ExportHistories::where('identifier', $id)->get()->first();
        return $record;
    }

    public function getReport($id = null){
        ini_set('max_execution_time', 1800);
        $identifier = $id; 
        $record = ExportHistories::where('identifier', '=', $id)->first();
        $total = count(json_decode($record->data)); 
        $postFieldsResult = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.admin.ws.five9.com/">
            <soapenv:Header/>
            <soapenv:Body>
            <ser:getListImportResult>
                <!--Optional:-->
                <identifier>
                    <!--Optional:-->
                    <identifier>'.$identifier.'</identifier>
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
            CURLOPT_URL => 'https://api.five9.com:443/wsadmin/v12/AdminWebService',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS =>$postFieldsResult,
            CURLOPT_HTTPHEADER => array(
            "Authorization: Basic $code",
            'Content-Type: application/xml',
            'Cookie: clientId=55C66003DEBC4319B00DFE27F039082A; Authorization=Bearer-f34e5614-c048-11eb-8f5a-00505fca8df6; apiRouteKey=SCLnAPIc6f; app_key=F9; farmId=182; uiRouteKey=SCLnUI5551'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $report = $this->__XMLtoJSON3($response);
        if($report['status'] == 'success') {
            if(isset($report["message"]["importTroubles"])):
                $error = $report["message"]["importTroubles"];
            else:
                $error = null;
            endif;
            
            $inserted = $report['message']["crmRecordsInserted"];
            $updated = $report['message']["crmRecordsUpdated"];

            $skipped = $total  - $inserted - $updated;
            $record->inserted = $inserted;
            $record->updated = $updated;
            $record->skipped = $skipped;
            $record->total = $total;
            $record->error = $error;
            $record->report = $report;
            $record->save();
            return ['status' => 'success'];
        }
        else {
            return ['status' => 'error', 'message' => 'Report can not be generated. This Identifier has been expired.'];
        }
    }

    public function getReportsDetail($id = null){
        $identifier = $id; 
        $records = ExportHistories::where('identifier', '=', $identifier)->first();
        $keyArray = json_decode($records->arraykeys);
        $newArray = [];
        foreach(json_decode($records->data) as $key => $value){
            $value = get_object_vars($value);
            foreach($value as $ikey => $ivalue){
                $newArray[$key][array_search($ikey, $keyArray)] = $ivalue;
            }
        }
        $results = get_object_vars(json_decode($records->report));
        $resultA = [];
        if($records->type == "dataset"){
            foreach($results as $value){
                $value = get_object_vars($value);
                $resultA[] = get_object_vars($value["return"]);
                $value = get_object_vars($value["return"]);
            }
            $result = [
                "keyFields" => $value["keyFields"],
                "uploadDuplicatesCount" => array_sum(array_column($resultA, "uploadDuplicatesCount")),
                "uploadErrorsCount" => array_sum(array_column($resultA, "uploadErrorsCount")),
                "warningsCount" => [],
                "callNowQueued" => array_sum(array_column($resultA, "callNowQueued")),
                "crmRecordsInserted" => array_sum(array_column($resultA, "crmRecordsInserted")),
                "crmRecordsUpdated" => array_sum(array_column($resultA, "crmRecordsUpdated")),
                "listName" => $value["listName"],
                "listRecordsDeleted" => array_sum(array_column($resultA, "listRecordsDeleted")),
                "listRecordsInserted" => array_sum(array_column($resultA, "listRecordsInserted")),
            ];
        }else{  
            $result = $results["message"];
        }
        $errorArray = []; $errorCounter = 0;
        $error = [];
        $errorArrayExport = [];
        if($records->error){ 
            if( (gettype(json_decode($records->error)) == 'object')){
                $importTroubles = get_object_vars(json_decode($records->error));
                if($importTroubles["kind"] == "DuplicateKey" ){
                    $error[0]["key"] = $importTroubles["key"];
                    $error[0]["kind"] = $importTroubles["kind"];
                    $error[0]["rowNumber"] = $importTroubles["rowNumber"];
                    $error[0]["troubleMessage"] = $importTroubles["troubleMessage"];
                }
                if($importTroubles["kind"] == "ParseError"){
                    $error[0]["kind"] = $importTroubles["kind"];
                    $error[0]["rowNumber"] = $importTroubles["rowNumber"];
                    $error[0]["troubleMessage"] = $importTroubles["troubleMessage"];  
                }     
                         
            }elseif ((gettype(json_decode($records->error)) == 'array')){
                $error = json_decode($records->error);
            }            
            
            foreach($error as $errorKey => $errorValue){
                $counter = 0;
                foreach (json_decode($records->data) as $newArraykey => $newArrayvalue) {
                    $newArrayvalue = get_object_vars($newArrayvalue);
                        if(gettype($errorValue) == 'object'):
                            $errorValueNew = get_object_vars($errorValue);
                        else:
                            $errorValueNew = $errorValue;
                        endif;
                        if((isset($errorValueNew["key"])) && ($errorValueNew["key"] == $newArrayvalue["number1"])){
                            if($counter == 0){
                                $counter++;
                            }else{                  
                                $errorArrayExport[$errorCounter] = $newArrayvalue; 
                                $newArrayvalue["errorType"] = $errorValueNew["kind"];
                                $newArrayvalue["errorDesc"] = $errorValueNew["troubleMessage"];
                                $errorArray[$errorCounter] = $newArrayvalue;
                                $errorCounter++;
                            }
                        }
                }
                if(gettype($errorValue) == 'object'):
                    $errorValueNew = get_object_vars($errorValue);
                else:
                    $errorValueNew = $errorValue;
                endif;
                if($errorValueNew["kind"] == "ParseError"){
                    foreach (json_decode($records->data) as $newArraykey => $newArrayvalue) {
                        $newArrayvalue = get_object_vars($newArrayvalue);
                        
                        if($errorValueNew["rowNumber"] == ($newArraykey+1)){
                            $errorArrayExport[$errorCounter] = $newArrayvalue;
                            $newArrayvalue["errorType"] = $errorValueNew["kind"];
                            $newArrayvalue["errorDesc"] = $errorValueNew["troubleMessage"];
                            $newArrayvalue["rowNumber"] = $errorValueNew["rowNumber"];
                            $errorArray[$errorCounter] = $newArrayvalue;                            
                            $errorCounter++;
                        }
                    }
                }
            }
        }
        return [ 
            'results' => $result, 
            'keys' =>  json_decode($records->arraykeys),
            'newArray' => json_decode($records->data),
            'errorArray' => $errorArray,
            'errorArrayExport' => $errorArrayExport
        ];
        
    }

    private function __XMLtoJSON3($response) {
        $xml = $response;
        // SimpleXML seems to have problems with the colon ":" in the <xxx:yyy> response tags, so take them out
        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $xml);
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $return = json_decode($json,true);
        $var = 'ns2getListImportResultResponse';
        if(array_key_exists($var, $return['envBody'])):
            return ['message' => $return['envBody']['ns2getListImportResultResponse']['return'], 'status' => 'success'];
        else:
            return ['message' => $return['envBody']['envFault']['faultstring'], 'status' => 'error'];
        endif;
    }
}