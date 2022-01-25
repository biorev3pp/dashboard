<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Settings;
use App\Models\Templates;
use App\Models\Contacts;
use App\Models\CroneHistories;
use App\Models\JobHistory;

class ServerCronController extends Controller
{
    public function outreachsession()
    {
        $curl = curl_init();
        $token_expire = Settings::where('id', '=', 7)->first();
        if($token_expire['value'] <= strtotime("now")):
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.outreach.io/oauth/token?client_id=eUBHpxrv-UgUE_NloPqbbcHsJn0VpV1mj9JEraoh_jg&client_secret=D6e9SmqUTDcQLna_BhZst97JonI7wnZAE_Y5KuCcqFE&redirect_uri=https://www.biorev.us/oauth/outreach&grant_type=refresh_token&refresh_token=tWODyzlm-Glao8PeQzOV5ugZjRq7Wz6oTxYwQyxtY0Y",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST'
            ));
            $requestTokenResponse = curl_exec($curl);
            $tokenInfo = get_object_vars(json_decode($requestTokenResponse));
            $accessToken = $tokenInfo['access_token'];
            $access_token = Settings::where('id', '=', 8)->first();
            $access_token->value = $accessToken;
            $access_token->save();
            $token_expire = Settings::where('id', '=', 7)->first();
            $token_expire->value = strtotime("now")+90*60;
            $token_expire->save();
        endif;
        $access_token = Settings::where('id', '=', 8)->first();
        return $access_token['value'];
    }
    
    public function updateall()
    {
        ini_set('max_execution_time', 1800);
        // active campaign
        $activeCampaign = [];
        $activeCampaignNew = [];
        $activeCampaignRecords = 0;
        $curl = curl_init();
        $date = date("Y-m-d", strtotime("-2 day"));
        $queryr = "https://biorev33069.api-us1.com/api/3/activities?after=$date";
        
        curl_setopt_array($curl, array(
        CURLOPT_URL => $queryr,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Api-Token: 5ab82418363737bc55f771e7529c7c1f42ff0d41c623cfe874b2ec0a4455995d34493f39',
            'Cookie: __cfduid=d97b6427615f0d72feec531918ba097411618376679; PHPSESSID=56d9cad6a1d8133f1acd1d92a006cedd; cmp90303434=205ff7b29cb3bbd970014602eb9a70f0; em_acp_globalauth_cookie=f817b856-b9df-4998-9b94-ac98e20f3642'
        ),
        ));

        $responser = curl_exec($curl);

        curl_close($curl);
        $resultr = json_decode($responser);
        $res = get_object_vars($resultr);
        $meta = get_object_vars($res["meta"]);
        $totalRecord = $meta["total"];
        for ($i=1; $i < ceil($totalRecord/20)+1; $i++) {             
            $curl = curl_init();
            $offset = ($i-1)*20;
            $queryc= "https://biorev33069.api-us1.com/api/3/activities?after=$date&limit=50&offset=".$offset;
            curl_setopt_array($curl, array(
                CURLOPT_URL => $queryc,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Api-Token: 5ab82418363737bc55f771e7529c7c1f42ff0d41c623cfe874b2ec0a4455995d34493f39',
                    'Cookie: __cfduid=d97b6427615f0d72feec531918ba097411618376679; PHPSESSID=56d9cad6a1d8133f1acd1d92a006cedd; cmp90303434=205ff7b29cb3bbd970014602eb9a70f0; em_acp_globalauth_cookie=f817b856-b9df-4998-9b94-ac98e20f3642'
                ),
            ));
            $responsec = curl_exec($curl);
            curl_close($curl);
            $resultc = json_decode($responsec);
            $resc = get_object_vars($resultc);
            $activities = $resc["activities"]; 
            
            $logs = $res["logs"];
            $activityList = [];
            foreach($activities as $akey => $avalue){
                $record =  get_object_vars($avalue);
                $record["reference"] = get_object_vars($record["reference"]);
                $activityList[] = $record;
            }
            $logsList = [];
            foreach($logs as $lkey => $lvalue){
                $logsList[] =  get_object_vars($lvalue);
            }
            for ($i=0; $i < count($activityList); $i++) { 
                $count = Contacts::where('ac_id', '=', $activityList[$i]["subscriberid"])->count();
                if($count > 0){
                    $record = Contacts::where('ac_id', '=', $activityList[$i]["subscriberid"])->first();
                    $record->ac_activity_in = $logsList[$i]["campaignid"];
                    $record->last_ac_activity = $logsList[$i]["tstamp"];
                    $record->save();
                    $activeCampaign[] = $activityList[$i]["subscriberid"];// ac_id
                    $activeCampaignRecords++;
                }else{
                    $activeCampaignNew[] = $activityList[$i]["subscriberid"];// ac_id
                }
            }
        }

        $accessToken = $this->outreachsession();
        $curl = curl_init();        
        $query = "https://api.outreach.io/api/v2/stages?sort=name";
            curl_setopt_array($curl, array(
            CURLOPT_URL => $query,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $accessToken",
                "Content-Type: application/vnd.api+json"
            ),
        ));
        $response = curl_exec($curl);
        $stages = json_decode($response);
        curl_close($curl);
        $resultsStage = get_object_vars($stages);
        $allStages = [];
        foreach($resultsStage['data'] as $key => $value){
            $attributes = get_object_vars($value->attributes);
            $allStages[$value->id] = $attributes["name"];
        }
        
            $queryStrings = 'sort=id';
            $start = date("Y-m-d", strtotime("-2 day"));
            $queryStrings .= "&filter[engagedAt]=$start..inf";
            $curl = curl_init();
            $query = "https://api.outreach.io/api/v2/prospects?$queryStrings&count=true";
            curl_setopt_array($curl, array(
                CURLOPT_URL => $query,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Bearer $accessToken",
                    "Content-Type: application/vnd.api+json"
                ),
            ));
            $responseq = curl_exec($curl);
            curl_close($curl);
            $results = get_object_vars(json_decode($responseq)); 
            $res1 = $results["meta"];
            $res2 = get_object_vars($res1);
            $page = intval(ceil(intval($res2["count"])/50));
            $recordUpdated = 0;
            $recordCreated = 0;
            $jobHistoryOutreach = [];  
            $jobHistoryOutreachNew = [];            
            for ($i=0; $i < $page; $i++) { 
                $queryString = '';
                $queryString = 'sort=id';
                $start = date("Y-m-d", strtotime("-2 day"));
                $queryString .= "&filter[engagedAt]=$start..inf";
                $curl = curl_init();
                if($i == 0):
                    $query = "https://api.outreach.io/api/v2/prospects?$queryString&count=true&page[offset]=0";
                    
                else:
                    $offset = $i*50;
                    $query = "https://api.outreach.io/api/v2/prospects?$queryString&count=true&page[offset]=$offset";
                endif;
                
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $query,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_HTTPHEADER => array(
                        "Authorization: Bearer $accessToken",
                        "Content-Type: application/vnd.api+json"
                    ),
                ));
                $responseq = curl_exec($curl);
                curl_close($curl);
                $results = get_object_vars(json_decode($responseq)); 
                
                foreach($results['data'] as $key => $value){ 
                    
                    $attributes = get_object_vars($value->attributes);
                    $relationships = get_object_vars($value->relationships);
                    $data = get_object_vars($relationships["stage"]);
                    if($data["data"] == null){
                        $pstage = null;                    
                    }else{
                        $stage = get_object_vars($data["data"]);
                        if($stage["id"] > 0){
                            $pstage = $allStages[$stage["id"]];
                        }else{
                            $pstage = null;
                        }
                    }
                    $count = Contacts::where('record_id', '=', $value->id)->count();
                    if($count == 0){
                        $jobHistoryOutreachNew[] = $value->id;
                        // $contact = Contacts::create([
                        //     'record_id' => $value->id,
                        //     'personal_note' => $attributes['personalNote1'],
                        //     'stage' =>$pstage,
                        //     'engage_score' =>  $attributes['engagedScore'],//
                        //     'last_outreach_engage' => $attributes['engagedAt'],
                        // ]);
                        //$recordCreated++;
                    }else{
                        $prospect = Contacts::where('record_id', '=', $value->id)->first();                        
                        $prospect->personal_note = $attributes['personalNote1'];
                        $prospect->stage =$pstage;
                        $prospect->engage_score =  $attributes['engagedScore'];
                        $prospect->last_outreach_engage = $attributes['engagedAt'];
                        $prospect->save();    
                        $jobHistoryOutreach[] = $value->id;            
                    }
                }
            }
            
        // five9 query start
        
        $start = date("Y-m-d", strtotime("-1 day")).'T'.date("H:i:s", strtotime("-24 hours"));
        $end = date("Y-m-d", strtotime("now")).'T'.date("H:i:s", strtotime("now"));
        $postFields = '<env:Envelope xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:tns="http://service.admin.ws.five9.com/" xmlns:env="http://schemas.xmlsoap.org/soap/envelope/"
        xmlns:ins0="http://jaxb.dev.java.net/array">
        <env:Body>
            <tns:runReport>
                <folderName>My Reports</folderName>
                <reportName>Call Log</reportName>
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
        $id =  substr($response, strpos($response, '<return>')+8, strrpos($response, '</return>') - strpos($response, '<return>')-8);
        $curl = curl_init();
        for ($i=0; $i < 20; $i++) {
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
    
            $responseRunning = curl_exec($curl);
            $result = substr($responseRunning, strpos($responseRunning, '<return>')+8, strrpos($responseRunning, '</return>') - strpos($responseRunning, '<return>')-8);
            if($result == 'false'){
                break;
            }
        }
        curl_close($curl);
        $curl = curl_init();
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
        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $xml); 
        $xml = simplexml_load_string($xml); 
        $json = json_encode($xml); 
        $return = json_decode($json,true);
        $fields = $return['envBody']['ns2getReportResultResponse']['return']['header']['values']['data'];
        $records = $return['envBody']['ns2getReportResultResponse']['return']['records'];        
        $created = 0; $updated = 0;  $jobHistoryFivenine = [];$jobHistoryFivenineNew = [];
        $title = "job-".date("Y-m-d H:i:s", strtotime("now"));
        foreach($records as $key => $value){
            if(is_array($value["values"]["data"][44])):
                $Last_Agent = null;
            else:
                $Last_Agent = $value["values"]["data"][44];
            endif;
            if(is_array($value["values"]["data"][45])):
                $Last_Agent_Dispo_Timestamp = null;
            else:
                $Last_Agent_Dispo_Timestamp = $value["values"]["data"][45];
            endif;
            if(is_array($value["values"]["data"][39])):
                $Last_Dispo = null;
            else:
                $Last_Dispo = $value["values"]["data"][39];
            endif;
            if(is_array($value["values"]["data"][46])):
                $Dial_Attempts = null;
            else:
                $Dial_Attempts = $value["values"]["data"][46];
            endif;
            if(is_array($value["values"]["data"][29])):
                $personal_note = null;
            else:
                $personal_note = $value["values"]["data"][29];
            endif;
            if(is_array($value["values"]["data"][36])):
                $stage = null;
            else:
                $stage = $value["values"]["data"][36];
            endif;
            if(is_array($value["values"]["data"][8])):
                $last_call = null;
            else:
                $last_call = $value["values"]["data"][8];
            endif;
            if(is_array($value["values"]["data"][26])):
                $record_id = null;
            else:
                $record_id = $value["values"]["data"][26];
            endif;
            $data = [                
                "personal_note" => $personal_note,                
                "stage" => $stage,
                "last_call" => $last_call,
                "last_agent" => $Last_Agent,
                "last_agent_dispo_time" => $Last_Agent_Dispo_Timestamp,
                "last_dispo" => $Last_Dispo,
                "dial_attempts" => $Dial_Attempts,
            ];
            $count = Contacts::where('record_id', '=', $record_id)->count();
            
            if($count > 0):
                $contact = Contacts::where('record_id', '=', $record_id)->first();
                $contact->personal_note = $personal_note;
                $contact->stage = $stage;
                $contact->last_call = $last_call;
                $contact->last_agent = $Last_Agent;
                $contact->last_agent_dispo_time = $Last_Agent_Dispo_Timestamp;
                $contact->last_dispo = $Last_Dispo;
                $contact->dial_attempts = $Dial_Attempts;                
                $contact->save();    
                $updated++;
                $jobHistoryFivenine[] = $record_id;
            else:
                $jobHistoryFivenineNew[] = $record_id;  //Contacts::create($data);
                $created++;
            endif;
        }
        // $activeCampaign[] = $activityList[$i]["subscriberid"];
        //             $activeCampaignRecords++;
        $result = JobHistory::create([
            "title" => "JOB-".date("Y-m-d-H:i:s", strtotime("now")),
            "five9_count" =>  count(array_unique($jobHistoryFivenine)),
            "five9_contacts" => implode(',', array_unique($jobHistoryFivenine)),
            "outreach_count" =>  count(array_unique($jobHistoryOutreach)),
            "outreach_contacts" =>implode(',', array_unique($jobHistoryOutreach)),
            "ac_count" => count(array_unique($activeCampaign)),
            "ac_contacts" => implode(',', array_unique($activeCampaign)),
        ]);
        CroneHistories::create([
            "job_history_id" => $result->id,
            "outreach" => implode(',', array_unique($jobHistoryOutreachNew)),
            "active_campaign" => implode(',', array_unique($activeCampaignNew)),
            "fivenine" => implode(',', array_unique($jobHistoryFivenineNew)),
        ]);
        echo 'checked'; die;

    }
}
