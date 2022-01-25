<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Settings;
use App\Models\Templates;
use App\Models\Contacts;
use App\Models\CroneHistories;
use App\Models\JobHistory;
use App\Models\Stages;
use App\Models\OutreachAccounts;
use App\Models\OutreachTasks;
use App\Models\OutreachSequences;
use App\Models\OutreachSequenceStates;
use App\Models\OutreachMailings;
use App\Models\FivenineCallLogs;
use App\Models\ContactCustoms;
use App\Models\ReportMailings;
use App\Models\ReportCalls;
use App\Models\ReportContacts;
use App\Models\ReportSequenceMailings;
use App\Models\TempCalls;
use App\Models\TempEmails;
use App\Models\AgentOccupancy;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use App\Jobs\ProspectUpdate;

class OutreachTokenController extends Controller

{

    public function __construct()
    {
       // $this->middleware('auth');
    }
    public function getToken(){
        $token_expire = Settings::where('id', '=', 7)->first();
        if($token_expire['value'] <= strtotime("now")){
            $curl = curl_init();
            curl_setopt_array($curl, array(
              CURLOPT_URL => 'https://api.outreach.io/oauth/token?client_id=eUBHpxrv-UgUE_NloPqbbcHsJn0VpV1mj9JEraoh_jg&client_secret=D6e9SmqUTDcQLna_BhZst97JonI7wnZAE_Y5KuCcqFE&redirect_uri=https://www.biorev.us/oauth/outreach&grant_type=refresh_token&refresh_token=VMJ9ms3Tsro4qufbv1piWkNVsLlLeeNpqpliFAOaFqU',
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
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
        }
        $access_token = Settings::where('id', '=', 8)->first();
        return $access_token['value'];
    }
    public function outreachUpdateAll() 
    { 
        $createdContacts = 0;
        $createdContactsArray = [];
        $stageUpdate = 0;
        $stageUpdateArray = [];
        $contactNoUpdate = 0;
        $contactNoUpdateArray = [];
        $customFieldUpdate = 0;
        $customFieldUpdateArray = [];
        ini_set('max_execution_time', 3600);
        // outreach starts
        $accessToken = $this->getToken();
        $allRecords = [];
        //created
        $date = date("Y-m-d", strtotime("-1 day"));
        $start = $date."T00:00:59Z";
        $end = $date."T23:59:00Z";
        
        $queryStrings = 'sort=id';
        $queryStrings .= "&filter[createdAt]=$start..$end";
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
        // echo $res2["count"]."<br>"; die;
        if($res2["count"] > 0):
            $page = intval(ceil(intval($res2["count"])/50));                   
            for ($i=0; $i < $page; $i++) { 
                $queryString = '';
                $queryString = 'sort=id';
                $queryString .= "&filter[createdAt]=$start..$end";
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
                    dd($value);
                    $accountId = null;
                    $stageName = null;
                    $personaName = null;
                    $stageId = null;
                    $personaId = null;
                    $attributes = get_object_vars($value->attributes);
                    $relationships = get_object_vars($value->relationships);
                    $account = get_object_vars($relationships["account"]);
                    $data = get_object_vars($relationships["stage"]);    
                    $stage = get_object_vars($relationships["stage"]);
                    $persona = get_object_vars($relationships["persona"]);
                    $owner = get_object_vars($relationships["owner"]);
                    if(is_object($account["data"])):
                        $account = get_object_vars($account["data"]);
                        $accountId = $account["id"];
                    endif;
                    if(is_object($stage["data"])):
                        $stage = get_object_vars($stage["data"]);
                        $stageId = $stage["id"];
                    endif;
                    if(is_object($persona["data"])):
                        $persona = get_object_vars($persona["data"]);
                        $personaId = $persona["id"];
                    endif;                    
                    ++$createdContacts;
                    $count = Contacts::where('record_id', '=', $value->id)->count();
                    if($count == 0){
                        $allRecords[] = $value->id;
                        $data = [
                            "record_id" => $value->id,
                            "account_id" => $accountId,
                            "stage" => $stageId,
                            "outreach_persona" => $personaId,
                            "attributes" => $attributes
                        ];
                        $this->__createProspect($data);
                        $createdContactsArray[] = $value->id;
                    }
                }
            }
        endif;        
        //updated
        $queryStrings = 'sort=id';
        $queryStrings .= "&filter[updatedAt]=$start..$end";
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
        //echo $res2["count"]."<br>";
        if($res2["count"]):
            $page = intval(ceil(intval($res2["count"])/50));                    
            for ($i=0; $i < $page; $i++) { 
                $queryString = '';
                $queryString = 'sort=id';
                $queryString .= "&filter[updatedAt]=$start..$end";
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
                if(is_null($responseq) || is_null(json_decode($responseq)) || is_null(get_object_vars(json_decode($responseq)))){

                }else{
                    $results = get_object_vars(json_decode($responseq));
                    foreach($results['data'] as $key => $value){ 
                        $accountId = null;
                        $stageName = null;
                        $personaName = null;
                        $stageId = null;
                        $personaId = null;
                        $ownerName = null;
                        $ownerId = null;
                        $attributes = get_object_vars($value->attributes);
                        $relationships = get_object_vars($value->relationships);
                        $account = get_object_vars($relationships["account"]);
                        $data = get_object_vars($relationships["stage"]);    
                        $stage = get_object_vars($relationships["stage"]);
                        $persona = get_object_vars($relationships["persona"]);
                        if(is_object($account["data"])):
                            $account = get_object_vars($account["data"]);
                            $accountId = $account["id"];
                        endif;
                        if(is_object($stage["data"])):
                            $stage = get_object_vars($stage["data"]);
                            $stageId = $stage["id"];
                        endif;
                        if(is_object($persona["data"])):
                            $persona = get_object_vars($persona["data"]);
                            $personaId = $persona["id"];
                        endif;
                        
                        $count = Contacts::where('record_id', '=', $value->id)->count();
                        if($count == 0){
                            $allRecords[] = $value->id;
                            $data = [
                                "record_id" => $value->id,
                                "account_id" => $accountId,
                                "stage" => $stageId,
                                "outreach_persona" => $personaId,
                                "attributes" => $attributes
                            ];
                            //$this->__createProspect($data);
                            //$createdContactsArray[] = $value->id;
                            //++$createdContacts;
                        }else{
                            $oldStateStatus = 0;
                            if($this->__checkStageUpdate($value->id, $stageId)){
                                $stageUpdateArray[] = $value->id;
                                $oldStateStatus = 1;
                                ++$stageUpdate;
                            }
                            $contactNumbers = [
                                "mobilePhones" => $attributes["mobilePhones"],
                                "homePhones" => $attributes["homePhones"],
                                "workPhones" => $attributes["workPhones"],
                                "voipPhones" => $attributes["voipPhones"],
                                "otherPhones" => $attributes["otherPhones"],
                                "company_hq_number" => $attributes["custom5"],
                            ];
                            if($this->__checkContactNumberUpdate($value->id,$contactNumbers)){
                                $contactNoUpdateArray[] = $value->id;
                                ++$contactNoUpdate;        
                            }
                            if($this->__checkCustomFieldUpdate($value->id, $attributes)){
                                $customFieldUpdateArray[] = $value->id;
                                ++$customFieldUpdate;
                            }
                            $data = [
                                "record_id" => $value->id,
                                "account_id" => $accountId,
                                "stage" => $stageId,
                                "outreach_persona" => $personaId,
                                "attributes" => $attributes,
                                "old_state_status" => $oldStateStatus
                            ];
                            $this->__updateProspect($data);
                            $allRecords[] = $value->id;
                        }
                    }
                }
            }
        endif;
        
        if(count($createdContactsArray) > 0){
            $createdContactsArray = json_encode($createdContactsArray);
        }else{
            $createdContactsArray = null;
        }
        if(count($stageUpdateArray) > 0){
            $stageUpdateArray = json_encode($stageUpdateArray);
        }else{
            $stageUpdateArray = null;
        }
        if(count($contactNoUpdateArray) > 0){
            $contactNoUpdateArray = json_encode($contactNoUpdateArray);
        }else{
            $contactNoUpdateArray = null;
        }
        if(count($customFieldUpdateArray) > 0){
            $customFieldUpdateArray = json_encode($customFieldUpdateArray);
        }else{
            $customFieldUpdateArray = null;
        }
        $count = ReportContacts::where("date", '=', $date)->count();
        if($count == 0){
            ReportContacts::create([
                "date" => $date,
                "total_created" => $createdContacts,
                "total_created_ids" => $createdContactsArray,
                "total_stage_update" => $stageUpdate,
                "total_stage_update_ids" => $stageUpdateArray,
                "total_contact_no_update" => $contactNoUpdate,
                "total_contact_no_update_ids" => $contactNoUpdateArray,
                "total_custom_field_update" => $customFieldUpdate,
                "total_custom_field_update_ids" => $customFieldUpdateArray
            ]);
        }else{
            $record = ReportContacts::where("date", '=', $date)->first();
            $record->total_created = $createdContacts;
            $record->total_created_ids = $createdContactsArray;
            $record->total_stage_update = $stageUpdate;
            $record->total_stage_update_ids = $stageUpdateArray;
            $record->total_contact_no_update = $contactNoUpdate;
            $record->total_contact_no_update_ids = $contactNoUpdateArray;
            $record->total_custom_field_update = $customFieldUpdate;
            $record->total_custom_field_update_ids = $customFieldUpdateArray;
            $record->save();
        }
        echo $date.' all prospects done'; die;
    }
    public function getPersonaFromOutreach(){
        $curl = curl_init();
        $accessToken = $this->getToken();
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.outreach.io/api/v2/personas',
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
        curl_close($curl);
        if($response){
            $res = get_object_vars(json_decode($response));
            $data = $res['data'];
            // echo '<table border="1"><thead>
            // <tr>
            // <th>RID</th>
            // <th>NAME</th>
            // </tr></thead><tbody>';
            foreach($data as $persona){
                // dd($persona);
                $persona = get_object_vars($persona);
                $id = $persona['id'];
                $attributes = get_object_vars($persona['attributes']);
                $name = $attributes['name'];
                $count = DB::table('outreach_personas')->where('oid', $id)->where('name', 'like', $name)->count();
                if($count == 0){
                    DB::table('outreach_personas')->insert([
                        'oid' => $id,
                        'name' => $name
                    ]);
                }
                // echo '<tr><td>'.$id.'</td><td>'.$name.'</td></tr>';
            }
            // echo '</tbody></table>';
        }else{
            echo 'no data available';
        }

    }
    public function getProspectInfo($record_id) 
    {   $accessToken = $this->getToken();
        $curl = curl_init();
        $query = "https://api.outreach.io/api/v2/prospects/$record_id";
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
        echo '<pre>';print_r(get_object_vars(json_decode($responseq))); echo '</pre>';
    }
}