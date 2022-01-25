<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Models\Settings;

class ProcessFiveNineList implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $list;
    public function __construct($list)
    {
        $this->list = $list;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = $this->list;
        $listName = $data["listName"];
        $identifier = $data["identifier"];
        $code = $data["code"];
        
        $data = DB::table("fivenine_lists")->where("name", "LIKE", $listName)->first();
        
        $data = get_object_vars($data);
        $his = json_decode($data["history"]);
        $id = $his[count($his)-1];
        $listId = $data["id"];

        $exHistoryRecord = get_object_vars(DB::table("export_histories")->where('code',"LIKE", $code)->first());

        //need to get import result 
        $curl = curl_init();
        for ($i=0; $i < 10; $i++) 
        {
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
        $inserted = intval($exHistoryRecord["inserted"]) + intval($results01["return"]["crmRecordsInserted"]);
        $updated = intval($exHistoryRecord["updated"]) + intval($results01["return"]["crmRecordsUpdated"]);
        DB::table("export_histories")->where("id", "=", $exHistoryRecord)->update([
            "mul_identifier" => json_encode($mul_identifierNew),
            "lists" => (count($expListsNew) > 0) ? json_encode($expListsNew) : null,
            "inserted" => $inserted,
            "updated" => $updated,
        ]);
        
        //get list
        
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
        return true;
    }
}
