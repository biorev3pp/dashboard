<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $info;
    public function __construct($info)
    {
        $this->info = $info;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = $event->info;
        $listName = $data["listName"];
        $id = $data["identifier"];
        
        $curl = curl_init();
        for ($i=0; $i < 20; $i++) {
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
            $return = json_decode($json,true); //dd($return["envBody"]["envFault"]);
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
                    "updated_at" => date("Y-m-d H:i:s", strtotime("now"))
                ]);
            else:
                DB::table("fivenine_lists")->where("id", "=", $listId)->update([
                    "size" => 0,
                    "updated_at" => date("Y-m-d H:i:s", strtotime("now"))
                ]);
                DB::table("fivenine_list_contacts")->where("fivenine_list_id", "=", $listId)->delete();
            endif;
    }
}
