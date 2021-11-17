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

class ServerCronController extends Controller
{
    public function __construct()
    {

       // $this->middleware('auth');

    }
    public function __outreachsessionProspects()
    {
        $curl = curl_init();
        $token_expire = Settings::where('id', '=', 45)->first();
        if($token_expire['value'] <= strtotime("now")):
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.outreach.io/oauth/token?client_id=eUBHpxrv-UgUE_NloPqbbcHsJn0VpV1mj9JEraoh_jg&client_secret=D6e9SmqUTDcQLna_BhZst97JonI7wnZAE_Y5KuCcqFE&redirect_uri=https://www.biorev.us/oauth/outreach&grant_type=refresh_token&refresh_token=3wUSMuZqW_dkUmsIQCbNbkdw-pNzYJiuDgyW-EyFk4E",
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
            $access_token = Settings::where('id', '=', 46)->first();
            $access_token->value = $accessToken;
            $access_token->save();
            $token_expire = Settings::where('id', '=', 45)->first();
            $token_expire->value = strtotime("now")+90*60;
            $token_expire->save();
        endif;
        $access_token = Settings::where('id', '=', 46)->first();
        return $access_token['value'];
    }
    public function __outreachsessionMailings()
    {
        $curl = curl_init();
        $token_expire = Settings::where('id', '=', 47)->first();
        if($token_expire['value'] <= strtotime("now")):
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.outreach.io/oauth/token?client_id=eUBHpxrv-UgUE_NloPqbbcHsJn0VpV1mj9JEraoh_jg&client_secret=D6e9SmqUTDcQLna_BhZst97JonI7wnZAE_Y5KuCcqFE&redirect_uri=https://www.biorev.us/oauth/outreach&grant_type=refresh_token&refresh_token=8LrEpcrGWvYskfaho-kDkT6upbSBQ9FsiTzSAAVN8f0",
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
            $access_token = Settings::where('id', '=', 48)->first();
            $access_token->value = $accessToken;
            $access_token->save();
            $token_expire = Settings::where('id', '=', 47)->first();
            $token_expire->value = strtotime("now")+90*60;
            $token_expire->save();
        endif;
        $access_token = Settings::where('id', '=', 48)->first();
        return $access_token['value'];
    }
    public function __createProspect($data){        
        $contact = Contacts::create([
            "record_id" => $data["record_id"],
            "country" => $data["attributes"]['addressCountry'],
            "city" => $data["attributes"]['addressCity'],
            "state" => $data["attributes"]['addressState'],
            "zip" => $data["attributes"]['addressZip'],
            "address" => $data["attributes"]["addressStreet"]." ".$data["attributes"]["addressStreet2"],
            "company" => $data["attributes"]['company'],
            "companyIndustry" => $data["attributes"]['companyIndustry'],
            "emails" => implode(',', $data["attributes"]['emails']),
            "engage_score" => $data["attributes"]['engagedScore'],
            "first_name" => $data["attributes"]['firstName'],
            "last_name" => $data["attributes"]['lastName'],
            "name" => $data["attributes"]['firstName']." ".$data["attributes"]['lastName'],
            "mobilePhones" => implode(',', $data["attributes"]['mobilePhones']),
            "workPhones" => implode(',', $data["attributes"]['workPhones']),
            "homePhones" => implode(',', $data["attributes"]['homePhones']),
            "mnumber" => $this->__NumberFormater1(array_pop($data["attributes"]['mobilePhones'])),
            "hnumber" => $this->__NumberFormater1(array_pop($data["attributes"]['homePhones'])),
            "wnumber" => $this->__NumberFormater1(array_pop($data["attributes"]['workPhones'])),
            "otherPhones" => implode(',', $data["attributes"]['otherPhones']),
            "voipPhones" => implode(',', $data["attributes"]['voipPhones']),
            "linkedInUrl" => $data["attributes"]['linkedInUrl'],
            "source" => $data["attributes"]['source'],
            "title" => $data["attributes"]['title'],
            "account_id" => $data["account_id"],
            "outreach_tag" => implode(',', $data["attributes"]['tags']),
            "timeZone" => $data["attributes"]['timeZone'],
            "outreach_touched_at" => $data["attributes"]['touchedAt'],
            "last_update_at" => $data["attributes"]['updatedAt'],
            "websiteUrl1" => $data["attributes"]['websiteUrl1'],
            "last_outreach_engage" => $data["attributes"]['engagedAt'],
            "stage" => intval($data["stage"]),
            "last_outreach_email" => $data["attributes"]['emailsOptedAt'],
            "outreach_created_at" => $data["attributes"]['createdAt'],
            "outreach_persona" => $data["outreach_persona"],
            "custom1" => $data["attributes"]["custom1"],
            "custom2" => $data["attributes"]["custom2"],
            "custom9" => $data["attributes"]["custom9"],
            "custom10" => $data["attributes"]["custom10"],
            "custom11" => $data["attributes"]["custom11"],
            "custom12" => $data["attributes"]["custom12"],
        ]);
        $dataC = [];
        $dataC["contact_id"] = $data["record_id"];
        for($j = 1 ; $j < 151; $j++){
            $dataC["custom".$j] = $data["attributes"]["custom".$j];
        }
        ContactCustoms::create($dataC);
    }
    public function __updateProspect($data){
        $prospect = Contacts::where('record_id', '=', $data["record_id"])->first();
        if($data["old_state_status"] == 1){
            $old_stage = $prospect->stage;
        }else{
            $old_stage = null;
        }
        $prospect->country = $data["attributes"]['addressCountry'];
        $prospect->city = $data["attributes"]['addressCity'];
        $prospect->state = $data["attributes"]['addressState'];
        $prospect->zip = $data["attributes"]['addressZip'];
        $prospect->address = $data["attributes"]["addressStreet"]." ".$data["attributes"]["addressStreet2"];
        $prospect->company = $data["attributes"]['company'];
        $prospect->companyIndustry = $data["attributes"]['companyIndustry'];
        $prospect->emails = implode(',', $data["attributes"]['emails']);
        $prospect->engage_score =  $data["attributes"]['engagedScore'];
        $prospect->first_name =  $data["attributes"]['firstName'];
        $prospect->last_name =  $data["attributes"]['lastName'];
        $prospect->name = $data["attributes"]['firstName']." ".$data["attributes"]['lastName'];
        $prospect->mobilePhones = implode(',', $data["attributes"]['mobilePhones']);
        $prospect->workPhones = implode(',', $data["attributes"]['workPhones']);
        $prospect->homePhones = implode(',', $data["attributes"]['homePhones']);
        $prospect->otherPhones = implode(',', $data["attributes"]['otherPhones']);
        $prospect->voipPhones = implode(',', $data["attributes"]['voipPhones']);
        $prospect->mnumber = $this->__NumberFormater1(array_pop($data["attributes"]['mobilePhones']));
        $prospect->hnumber = $this->__NumberFormater1(array_pop($data["attributes"]['homePhones']));
        $prospect->wnumber = $this->__NumberFormater1(array_pop($data["attributes"]['workPhones']));
        $prospect->linkedInUrl = $data["attributes"]['linkedInUrl'];
        $prospect->source = $data["attributes"]['source'];
        $prospect->title = $data["attributes"]['title'];
        $prospect->account_id = $data["account_id"];
        $prospect->outreach_tag = implode(',', $data["attributes"]['tags']);
        $prospect->timeZone = $data["attributes"]["timeZone"];
        $prospect->outreach_touched_at = $data["attributes"]['touchedAt'];
        $prospect->last_update_at = $data["attributes"]['updatedAt'];
        $prospect->websiteUrl1 = $data["attributes"]['websiteUrl1'];
        $prospect->last_outreach_engage = $data["attributes"]['engagedAt'];
        $prospect->stage = intval($data["stage"]);
        $prospect->last_outreach_email = $data["attributes"]['emailsOptedAt'];
        $prospect->outreach_created_at = $data["attributes"]['createdAt'];
        $prospect->outreach_persona = $data["outreach_persona"];
        $prospect->custom1 = $data["attributes"]["custom1"];
        $prospect->custom2 = $data["attributes"]["custom2"];
        $prospect->custom9 = $data["attributes"]["custom9"];
        $prospect->custom10 = $data["attributes"]["custom10"];
        $prospect->custom11 = $data["attributes"]["custom11"];
        $prospect->custom12 = $data["attributes"]["custom12"];
        $prospect->old_stage = intval($old_stage);
        $prospect->save();   
        $customeRecocord = ContactCustoms::where('contact_id', '=', $data["record_id"])->first();  
        for($i = 1 ; $i < 151; $i++){
            if(($i != 30) && ($i != 31) && ($i != 32) && ($i != 24)){
                $custome["custom".$i] = $data["attributes"]["custom".$i];
            }
        }
        if($customeRecocord):
            $customs = $customeRecocord->update($custome);
        else:
            $custome["contact_id"] = $data["record_id"];
            ContactCustoms::create($custome);
        endif;
        $customeRecocord->save(); 
    }
    public function __checkStageUpdate($contactId, $stage){
        $contact = Contacts::where("record_id", $contactId)->first();
        //echo $contact->record_id." old stage : ". $contact->stage ." new stage : ". $stage." ";
        if(intval($stage) > 0){
            if(intval($contact->stage) == intval($stage)){
                //echo "false <br>";
                return false;
            }else{
                //echo "true <br>";
                return true;
            }
        }else{
            //echo "false <br>";
            return false;
        }
    }
    public function __checkContactNumberUpdate($contactId, $contactNumbers){
        $contact = Contacts::where("record_id", $contactId)->first();
        $contactCustome = ContactCustoms::where('contact_id', '=', $contactId)->first();
        if( 
            ($contact["mobilePhones"] == $contactNumbers["mobilePhones"]) && 
            ($contact["homePhones"] == $contactNumbers["homePhones"]) && 
            ($contact["workPhones"] == $contactNumbers["workPhones"]) && 
            ($contact["voipPhones"] == $contactNumbers["voipPhones"]) && 
            ($contact["otherPhones"] == $contactNumbers["otherPhones"]) && 
            ($contactCustome->custom5 == $contactNumbers["company_hq_number"]) ){
            return false;
        }else{
            return true;
        }
    }
    public function __checkCustomFieldUpdate($contactId, $customFields){
        $contactCustome = ContactCustoms::where('contact_id', '=', $contactId)->first();
        for($j = 1 ; $j < 151; $j++){
            $custom = 'custom'.$j;
            //echo $custom."-#-".$contactCustome->$custom."<br>";

            if($contactCustome->$custom != $customFields[$custom]){
                return true;
            }
        }
        return false;
    }
    //update outrech prospects in every 12 hrs
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
        $accessTokenProspects = $this->__outreachsessionProspects();
        $allRecords = [];
        //created
        $day = 1;
        $hrs = 24;
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
                "Authorization: Bearer $accessTokenProspects",
                "Content-Type: application/vnd.api+json"
            ),
        ));
        $responseq = curl_exec($curl);
        curl_close($curl);
        $results = get_object_vars(json_decode($responseq));
        $res1 = $results["meta"]; 
        $res2 = get_object_vars($res1);
        //echo $res2["count"]."<br>"; die;
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
                        "Authorization: Bearer $accessTokenProspects",
                        "Content-Type: application/vnd.api+json"
                    ),
                ));
                $responseq = curl_exec($curl);
                curl_close($curl);
                $results = get_object_vars(json_decode($responseq));
                foreach($results['data'] as $key => $value){ 
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
                "Authorization: Bearer $accessTokenProspects",
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
                        "Authorization: Bearer $accessTokenProspects",
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
    // update last_agent , last_agent_dispo_time , last_agent_dispo_time
    public function updateFromFive9ToContactsCrone(){        
        ini_set('max_execution_time', 3600);
        $updated = 0;
        $total = 0;
        $hrs = 30;
        $d = strtotime("now")-$hrs*3600;
        $date = date("Y-m-d", strtotime("now"));
        $date = "2021-10-11";
        $start = $date.'T00:00:00.000Z';
        $end = $date.'T23:59:59.000Z';
        //echo $start."--".$end;
        $postFields = '<env:Envelope xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:tns="http://service.admin.ws.five9.com/" xmlns:env="http://schemas.xmlsoap.org/soap/envelope/"
        xmlns:ins0="http://jaxb.dev.java.net/array">
        <env:Body>
            <tns:runReport>
                <folderName>My Reports</folderName>
                <reportName>Biorev Call Log 01</reportName>
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
        //echo '<br>'.$id.'<br>';
        $curl = curl_init();
        for ($i=0; $i < 40; $i++) {
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
            //echo '<br>'.print_r($responseRunning).'<br>';    
            if($result == 'false'){
                break;
            }
        }
        //echo '<br>'.$result.'<br>';
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
        
        //$fields = $return['envBody']['ns2getReportResultResponse']['return']['header']['values']['data'];
        $jobHistoryFivenine = [];$jobHistoryFivenineNew = [];
        if(isset($return['envBody']['ns2getReportResultResponse']['return']['records'])): 
            $records = $return['envBody']['ns2getReportResultResponse']['return']['records'];
            $total = count($records);
            $callRecords = [];
            $callRecordCounter = 0;
            foreach($records as $key => $value){
                if(isset($value["values"])){
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
                    if(is_array($value["values"]["data"][26])):
                        $record_id = null;
                    else:
                        $record_id = $value["values"]["data"][26];
                    endif;
                    $data = [
                        "last_agent" => $Last_Agent,
                        "last_agent_dispo_time" => $Last_Agent_Dispo_Timestamp,
                        "last_dispo" => $Last_Dispo,
                    ];
                    //dd($data);
                    $count = Contacts::where('record_id', '=', $record_id)->count();
                    if($count > 0):
                        //$ts = strtotime($Last_Agent_Dispo_Timestamp);
                        // $callRecords[++$callRecordCounter] = [
                        //     "record_id" => $record_id,
                        //     "last_agent" => $Last_Agent,
                        //     "last_agent_dispo_time" => $Last_Agent_Dispo_Timestamp,
                        //     "last_agent_dispo_time_timestamp" => $ts,
                        //     "last_dispo" => $Last_Dispo,
                        // ];
                        ++$updated;
                        $contact = Contacts::where('record_id', '=', $record_id)->first();
                        $contact->last_agent = $Last_Agent;
                        $contact->last_agent_dispo_time = $Last_Agent_Dispo_Timestamp;
                        $contact->last_dispo = $Last_Dispo;
                        $contact->save();    
                    endif;
                }
            }
            echo $date.' all done'; die;
        endif;
        echo $date.' no record available to update'; die;
    }
    //update outreach mailing    
    public function getOutreachMailing(){
        ini_set('max_execution_time', 3600);
        $accessTokenMailings = $this->__outreachsessionMailings();
        $date = date("Y-m-d", strtotime("-1 day"));
        //$date = "2021-10-27";
        
        $start = $date."T00:00:59Z";
        $end = $date."T23:59:00Z";

        //delivered
        $queryStrings = 'sort=id';
        $queryStrings .= "&filter[state]=delivered&filter[deliveredAt]=$start..$end";
        $query = "https://api.outreach.io/api/v2/mailings?$queryStrings&count=true";
        $curl = curl_init();
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
                "Authorization: Bearer $accessTokenMailings",
                "Content-Type: application/vnd.api+json"
            ),
        ));
        $respons = curl_exec($curl);
        curl_close($curl);
        $result = get_object_vars(json_decode($respons));
        $meta = $result["meta"];
        $meta = get_object_vars($meta);
        $count = $meta["count"];
        if($count > 0){
            $page = intval(ceil(intval($count)/50));
            for ($i=0; $i < $page; $i++) { 
                $queryStrings = 'sort=id';
                $queryStrings .= "&filter[state]=delivered&filter[deliveredAt]=$start..$end";
                if($i == 0):
                    $query = "https://api.outreach.io/api/v2/mailings?$queryStrings&count=true&page[offset]=0";
                else:
                    $offset = $i*50;
                    $query = "https://api.outreach.io/api/v2/mailings?$queryStrings&count=true&page[offset]=$offset";
                endif;
                $curl = curl_init();
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
                        "Authorization: Bearer $accessTokenMailings",
                        "Content-Type: application/vnd.api+json"
                    ),
                ));
                $respons = curl_exec($curl);
                curl_close($curl);
                $results = get_object_vars(json_decode($respons));
                foreach($results['data'] as $key => $value){
                    $exist = OutreachMailings::where('mailing_id', '=', $value->id)->count();
                    $attributes = get_object_vars($value->attributes);
                    $relationships = get_object_vars($value->relationships);
                    $prospect = get_object_vars($relationships["prospect"]);
                    $prospectId = null;
                    if(is_object($prospect["data"])):
                        $prospect = get_object_vars($prospect["data"]);
                        $prospectId = $prospect["id"];
                    endif;
                    $data = [
                        "mailing_id" => $value->id,
                        "contact_id" => $prospectId,
                        "attributes" => $attributes,
                        //"etype" => "clickedAt"
                    ];
                    if($exist == 0){
                        $this->__createOutreachEmail($data);
                    }else{
                        $this->__updateOutreachEmail($data);
                    }
                    $c = TempEmails::where("record_id", '=', $prospectId)->count();
                    if($c == 0){
                        TempEmails::create([
                            "record_id" => $prospectId
                        ]);
                    }
                }
            }
        }
        
        //opened opened
        $queryStrings = 'sort=id';
        $queryStrings .= "&filter[state]=opened&filter[deliveredAt]=$start..$end";
        $query = "https://api.outreach.io/api/v2/mailings?$queryStrings&count=true";
        $curl = curl_init();
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
                "Authorization: Bearer $accessTokenMailings",
                "Content-Type: application/vnd.api+json"
            ),
        ));
        $respons = curl_exec($curl);
        curl_close($curl);
        $result = get_object_vars(json_decode($respons));
        $meta = $result["meta"];
        $meta = get_object_vars($meta);
        $count = $meta["count"];
        if($count > 0){
            $page = intval(ceil(intval($count)/50));
            for ($i=0; $i < $page; $i++) { 
                $queryStrings = 'sort=id';
                $queryStrings .= "&filter[state]=opened&filter[deliveredAt]=$start..$end";
                if($i == 0):
                    $query = "https://api.outreach.io/api/v2/mailings?$queryStrings&count=true&page[offset]=0";
                else:
                    $offset = $i*50;
                    $query = "https://api.outreach.io/api/v2/mailings?$queryStrings&count=true&page[offset]=$offset";
                endif;
                $curl = curl_init();
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
                        "Authorization: Bearer $accessTokenMailings",
                        "Content-Type: application/vnd.api+json"
                    ),
                ));
                $respons = curl_exec($curl);
                curl_close($curl);
                $results = get_object_vars(json_decode($respons));
                foreach($results['data'] as $key => $value){
                    $exist = OutreachMailings::where('mailing_id', '=', $value->id)->count();
                    $attributes = get_object_vars($value->attributes);
                    $relationships = get_object_vars($value->relationships);
                    $prospect = get_object_vars($relationships["prospect"]);
                    $prospectId = null;
                    if(is_object($prospect["data"])):
                        $prospect = get_object_vars($prospect["data"]);
                        $prospectId = $prospect["id"];
                    endif;
                    $data = [
                        "mailing_id" => $value->id,
                        "contact_id" => $prospectId,
                        "attributes" => $attributes,
                    ];
                    if($exist == 0){
                        $this->__createOutreachEmail($data);
                    }else{
                        $this->__updateOutreachEmail($data);
                    }
                    $c = TempEmails::where("record_id", '=', $prospectId)->count();
                    if($c == 0){
                        TempEmails::create([
                            "record_id" => $prospectId
                        ]);
                    }
                }
            }
        }
        //replied
        $queryStrings = 'sort=id';
        $queryStrings .= "&filter[state]=replied&filter[deliveredAt]=$start..$end";
        $query = "https://api.outreach.io/api/v2/mailings?$queryStrings&count=true";
        $curl = curl_init();
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
                "Authorization: Bearer $accessTokenMailings",
                "Content-Type: application/vnd.api+json"
            ),
        ));
        $respons = curl_exec($curl);
        curl_close($curl);
        $result = get_object_vars(json_decode($respons));
        $meta = $result["meta"];
        $meta = get_object_vars($meta);
        $count = $meta["count"];
        if($count > 0){
            $page = intval(ceil(intval($count)/50));
            for ($i=0; $i < $page; $i++) { 
                $queryStrings = 'sort=id';
                $queryStrings .= "&filter[state]=replied&filter[deliveredAt]=$start..$end";
                if($i == 0):
                    $query = "https://api.outreach.io/api/v2/mailings?$queryStrings&count=true&page[offset]=0";
                else:
                    $offset = $i*50;
                    $query = "https://api.outreach.io/api/v2/mailings?$queryStrings&count=true&page[offset]=$offset";
                endif;
                $curl = curl_init();
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
                        "Authorization: Bearer $accessTokenMailings",
                        "Content-Type: application/vnd.api+json"
                    ),
                ));
                $respons = curl_exec($curl);
                curl_close($curl);
                $results = get_object_vars(json_decode($respons));
                foreach($results['data'] as $key => $value){
                    $exist = OutreachMailings::where('mailing_id', '=', $value->id)->count();
                    $attributes = get_object_vars($value->attributes);
                    $relationships = get_object_vars($value->relationships);
                    $prospect = get_object_vars($relationships["prospect"]);
                    $prospectId = null;
                    if(is_object($prospect["data"])):
                        $prospect = get_object_vars($prospect["data"]);
                        $prospectId = $prospect["id"];
                    endif;
                    $data = [
                        "mailing_id" => $value->id,
                        "contact_id" => $prospectId,
                        "attributes" => $attributes,
                    ];
                    if($exist == 0){
                        $this->__createOutreachEmail($data);
                    }else{
                        $this->__updateOutreachEmail($data);
                    }
                    $c = TempEmails::where("record_id", '=', $prospectId)->count();
                    if($c == 0){
                        TempEmails::create([
                            "record_id" => $prospectId
                        ]);
                    }
                }
            }
        }
        
        //bounced
        $queryStrings = 'sort=id';
        $queryStrings .= "&filter[state]=bounced&filter[bouncedAt]=$start..$end";
        $query = "https://api.outreach.io/api/v2/mailings?$queryStrings&count=true";
        $curl = curl_init();
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
                "Authorization: Bearer $accessTokenMailings",
                "Content-Type: application/vnd.api+json"
            ),
        ));
        $respons = curl_exec($curl);
        curl_close($curl);
        $result = get_object_vars(json_decode($respons));
        $meta = $result["meta"];
        $meta = get_object_vars($meta);
        $count = $meta["count"];
        if($count > 0){
            $page = intval(ceil(intval($count)/50));
            for ($i=0; $i < $page; $i++) { 
                $queryStrings = 'sort=id';
                $queryStrings .= "&filter[state]=bounced&filter[bouncedAt]=$start..$end";
                if($i == 0):
                    $query = "https://api.outreach.io/api/v2/mailings?$queryStrings&count=true&page[offset]=0";
                else:
                    $offset = $i*50;
                    $query = "https://api.outreach.io/api/v2/mailings?$queryStrings&count=true&page[offset]=$offset";
                endif;
                $curl = curl_init();
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
                        "Authorization: Bearer $accessTokenMailings",
                        "Content-Type: application/vnd.api+json"
                    ),
                ));
                $respons = curl_exec($curl);
                curl_close($curl);
                $results = get_object_vars(json_decode($respons));
                foreach($results['data'] as $key => $value){
                    $exist = OutreachMailings::where('mailing_id', '=', $value->id)->count();
                    $attributes = get_object_vars($value->attributes);
                    $relationships = get_object_vars($value->relationships);
                    $prospect = get_object_vars($relationships["prospect"]);
                    $prospectId = null;
                    if(is_object($prospect["data"])):
                        $prospect = get_object_vars($prospect["data"]);
                        $prospectId = $prospect["id"];
                    endif;
                    $data = [
                        "mailing_id" => $value->id,
                        "contact_id" => $prospectId,
                        "attributes" => $attributes,
                    ];
                    if($exist == 0){
                        $this->__createOutreachEmail($data);
                    }else{
                        $this->__updateOutreachEmail($data);
                    }
                    $c = TempEmails::where("record_id", '=', $prospectId)->count();
                    if($c == 0){
                        TempEmails::create([
                            "record_id" => $prospectId
                        ]);
                    }
                }
            }
        }
        
        //unsubscribed
        $queryStrings = 'sort=id';
        $queryStrings .= "&filter[unsubscribedAt]=$start..$end";
        $query = "https://api.outreach.io/api/v2/mailings?$queryStrings&count=true";
        $curl = curl_init();
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
                "Authorization: Bearer $accessTokenMailings",
                "Content-Type: application/vnd.api+json"
            ),
        ));
        $respons = curl_exec($curl);
        curl_close($curl);
        $result = get_object_vars(json_decode($respons));
        $meta = $result["meta"];
        $meta = get_object_vars($meta);
        $count = $meta["count"];
        if($count > 0){
            $page = intval(ceil(intval($count)/50));
            for ($i=0; $i < $page; $i++) { 
                $queryStrings = 'sort=id';
                $queryStrings .= "&filter[unsubscribedAt]=$start..$end";
                if($i == 0):
                    $query = "https://api.outreach.io/api/v2/mailings?$queryStrings&count=true&page[offset]=0";
                else:
                    $offset = $i*50;
                    $query = "https://api.outreach.io/api/v2/mailings?$queryStrings&count=true&page[offset]=$offset";
                endif;
                $curl = curl_init();
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
                        "Authorization: Bearer $accessTokenMailings",
                        "Content-Type: application/vnd.api+json"
                    ),
                ));
                $respons = curl_exec($curl);
                curl_close($curl);
                $results = get_object_vars(json_decode($respons));
                foreach($results['data'] as $key => $value){
                    $exist = OutreachMailings::where('mailing_id', '=', $value->id)->count();
                    $attributes = get_object_vars($value->attributes);
                    $relationships = get_object_vars($value->relationships);
                    $prospect = get_object_vars($relationships["prospect"]);
                    $prospectId = null;
                    if(is_object($prospect["data"])):
                        $prospect = get_object_vars($prospect["data"]);
                        $prospectId = $prospect["id"];
                    endif;
                    $data = [
                        "mailing_id" => $value->id,
                        "contact_id" => $prospectId,
                        "attributes" => $attributes,
                    ];
                    if($exist == 0){
                        $this->__createOutreachEmail($data);
                    }else{
                        $this->__updateOutreachEmail($data);
                    }
                    $c = TempEmails::where("record_id", '=', $prospectId)->count();
                    if($c == 0){
                        TempEmails::create([
                            "record_id" => $prospectId
                        ]);
                    }
                }
            }
        }
        
        echo $date.' all emails done'; die;
    }
    private function __createOutreachEmail($data){
        OutreachMailings::create([
            "mailing_id" => $data["mailing_id"],
            "contact_id" => $data["contact_id"],
            "bouncedAt" => ($data["attributes"]["bouncedAt"]) ? date("Y-m-d", strtotime($data["attributes"]["bouncedAt"])+12600)."T".date("H:i:s", strtotime($data["attributes"]["bouncedAt"])+12600) : null,
            "clickCount" => $data["attributes"]["clickCount"],
            "clickedAt" => ($data["attributes"]["clickedAt"]) ? date("Y-m-d", strtotime($data["attributes"]["clickedAt"])+12600)."T".date("H:i:s", strtotime($data["attributes"]["clickedAt"])+12600) : null,
            "createdAt" => ($data["attributes"]["createdAt"]) ? date("Y-m-d", strtotime($data["attributes"]["createdAt"])+12600)."T".date("H:i:s", strtotime($data["attributes"]["createdAt"])+12600) : null,
            "deliveredAt" => ($data["attributes"]["deliveredAt"]) ? date("Y-m-d", strtotime($data["attributes"]["deliveredAt"])+12600)."T".date("H:i:s", strtotime($data["attributes"]["deliveredAt"])+12600) : null,
            "errorBacktrace" => $data["attributes"]["errorBacktrace"],
            "errorReason" => $data["attributes"]["errorReason"],
            "followUpTaskScheduledAt" => $data["attributes"]["followUpTaskScheduledAt"],
            "followUpTaskType" => $data["attributes"]["followUpTaskType"],
            "mailboxAddress" => $data["attributes"]["mailboxAddress"],
            "mailingType" => $data["attributes"]["mailingType"],
            "markedAsSpamAt" => $data["attributes"]["markedAsSpamAt"],
            "messageId" => $data["attributes"]["messageId"],
            "notifyThreadCondition" => $data["attributes"]["notifyThreadCondition"],
            "notifyThreadScheduledAt" => $data["attributes"]["notifyThreadScheduledAt"],
            "notifyThreadStatus" => $data["attributes"]["notifyThreadStatus"],
            "openCount" => $data["attributes"]["openCount"],
            "openedAt" => ($data["attributes"]["openedAt"]) ? date("Y-m-d", strtotime($data["attributes"]["openedAt"])+12600)."T".date("H:i:s", strtotime($data["attributes"]["openedAt"])+12600) : null,
            "overrideSafetySettings" => $data["attributes"]["overrideSafetySettings"],
            "repliedAt" => ($data["attributes"]["repliedAt"]) ? date("Y-m-d", strtotime($data["attributes"]["repliedAt"])+12600)."T".date("H:i:s", strtotime($data["attributes"]["repliedAt"])+12600) : null,
            "retryAt" => ($data["attributes"]["retryAt"]) ? date("Y-m-d", strtotime($data["attributes"]["retryAt"])+12600)."T".date("H:i:s", strtotime($data["attributes"]["retryAt"])+12600) : null,
            "retryCount" => $data["attributes"]["retryCount"],
            "retryInterval" => $data["attributes"]["retryInterval"],
            "scheduledAt" => ($data["attributes"]["scheduledAt"]) ? date("Y-m-d", strtotime($data["attributes"]["scheduledAt"])+12600)."T".date("H:i:s", strtotime($data["attributes"]["scheduledAt"])+12600) : null,
            "state" => $data["attributes"]["state"],
            "stateChangedAt" => ($data["attributes"]["stateChangedAt"]) ? date("Y-m-d", strtotime($data["attributes"]["stateChangedAt"])+12600)."T".date("H:i:s", strtotime($data["attributes"]["stateChangedAt"])+12600) : null,
            "subject" => $data["attributes"]["subject"],
            "trackLinks" => $data["attributes"]["trackLinks"],
            "trackOpens" => $data["attributes"]["trackOpens"],
            "unsubscribedAt" => ($data["attributes"]["unsubscribedAt"]) ? date("Y-m-d", strtotime($data["attributes"]["unsubscribedAt"])+12600)."T".date("H:i:s", strtotime($data["attributes"]["unsubscribedAt"])+12600) : null,
            "updatedAt" => ($data["attributes"]["updatedAt"]) ? date("Y-m-d", strtotime($data["attributes"]["updatedAt"])+12600)."T".date("H:i:s", strtotime($data["attributes"]["updatedAt"])+12600) : null,
            //"etype" => $data["etype"],
        ]);
    }
    private function __updateOutreachEmail($data){
        $record = OutreachMailings::where('mailing_id', '=', $data["mailing_id"])->first();
        $record->date = $data["date"];
        $record->bouncedAt = ($data["attributes"]["bouncedAt"]) ? date("Y-m-d", strtotime($data["attributes"]["bouncedAt"])+12600)."T".date("H:i:s", strtotime($data["attributes"]["bouncedAt"])+12600) : null;
        $record->clickCount = $data["attributes"]["clickCount"];
        $record->clickedAt = ($data["attributes"]["clickedAt"]) ? date("Y-m-d", strtotime($data["attributes"]["clickedAt"])+12600)."T".date("H:i:s", strtotime($data["attributes"]["clickedAt"])+12600) : null;
        $record->createdAt = ($data["attributes"]["createdAt"]) ? date("Y-m-d", strtotime($data["attributes"]["createdAt"])+12600)."T".date("H:i:s", strtotime($data["attributes"]["createdAt"])+12600) : null;
        $record->deliveredAt = ($data["attributes"]["deliveredAt"]) ? date("Y-m-d", strtotime($data["attributes"]["deliveredAt"])+12600)."T".date("H:i:s", strtotime($data["attributes"]["deliveredAt"])+12600) : null;
        $record->errorBacktrace = $data["attributes"]["errorBacktrace"];
        $record->errorReason = $data["attributes"]["errorReason"];
        $record->followUpTaskScheduledAt = $data["attributes"]["followUpTaskScheduledAt"];
        $record->followUpTaskType = $data["attributes"]["followUpTaskType"];
        $record->mailboxAddress = $data["attributes"]["mailboxAddress"];
        $record->mailingType = $data["attributes"]["mailingType"];
        $record->markedAsSpamAt = $data["attributes"]["markedAsSpamAt"];
        $record->messageId = $data["attributes"]["messageId"];
        $record->notifyThreadCondition = $data["attributes"]["notifyThreadCondition"];
        $record->notifyThreadScheduledAt = $data["attributes"]["notifyThreadScheduledAt"];
        $record->notifyThreadStatus = $data["attributes"]["notifyThreadStatus"];
        $record->openCount = $data["attributes"]["openCount"];
        $record->openedAt = ($data["attributes"]["openedAt"]) ? date("Y-m-d", strtotime($data["attributes"]["openedAt"])+12600)."T".date("H:i:s", strtotime($data["attributes"]["openedAt"])+12600) : null;
        $record->overrideSafetySettings = $data["attributes"]["overrideSafetySettings"];
        $record->repliedAt = ($data["attributes"]["repliedAt"]) ? date("Y-m-d", strtotime($data["attributes"]["repliedAt"])+12600)."T".date("H:i:s", strtotime($data["attributes"]["repliedAt"])+12600) : null;
        $record->retryAt = ($data["attributes"]["retryAt"]) ? date("Y-m-d", strtotime($data["attributes"]["retryAt"])+12600)."T".date("H:i:s", strtotime($data["attributes"]["retryAt"])+12600) : null;
        $record->retryCount = $data["attributes"]["retryCount"];
        $record->retryInterval = $data["attributes"]["retryInterval"];
        $record->scheduledAt = ($data["attributes"]["scheduledAt"]) ? date("Y-m-d", strtotime($data["attributes"]["scheduledAt"])+12600)."T".date("H:i:s", strtotime($data["attributes"]["scheduledAt"])+12600) : null;
        $record->state = $data["attributes"]["state"];
        $record->stateChangedAt = ($data["attributes"]["stateChangedAt"]) ? date("Y-m-d", strtotime($data["attributes"]["stateChangedAt"])+12600)."T".date("H:i:s", strtotime($data["attributes"]["stateChangedAt"])+12600) : null;
        $record->subject = $data["attributes"]["subject"];
        $record->trackLinks = $data["attributes"]["trackLinks"];
        $record->trackOpens = $data["attributes"]["trackOpens"];
        $record->unsubscribedAt = ($data["attributes"]["unsubscribedAt"]) ? date("Y-m-d", strtotime($data["attributes"]["unsubscribedAt"])+12600)."T".date("H:i:s", strtotime($data["attributes"]["unsubscribedAt"])+12600) : null;
        $record->updatedAt = ($data["attributes"]["updatedAt"]) ? date("Y-m-d", strtotime($data["attributes"]["updatedAt"])+12600)."T".date("H:i:s", strtotime($data["attributes"]["updatedAt"])+12600) : null;
        $record->save();
    }
    public function emailCounter()
    {
        $count = TempEmails::count();
        for($i = 0; $i < $count; $i++){
            if($contact = TempEmails::count() > 0){
                $contact = TempEmails::first();
                if($contact->record_id > 0){
                    $totalemail = OutreachMailings::where('contact_id', $contact->record_id)->where('state', '=', 'delivered')->where('deliveredAt', '!=', null)->count();
                    $totalopen = OutreachMailings::where('contact_id', $contact->record_id)->where('state', '=', 'opened')->where('deliveredAt', '!=', null)->count();
                    $totalreply = OutreachMailings::where('contact_id', $contact->record_id)->where('state', '=', 'replied')->where('deliveredAt', '!=', null)->count();
                    $totalbounced = OutreachMailings::where('contact_id', $contact->record_id)->where('state', '=', 'bounced')->where('bouncedAt', '=', null)->count();
                    $totalclick = OutreachMailings::where('contact_id', $contact->record_id)->where('clickedAt', '!=', null)->count();
                    $ucontact = Contacts::whereId($contact->id)->first();
                    $ucontact->update([
                        'email_delivered' => $totalemail,
                        'email_opened' => $totalopen,
                        'email_clicked' => $totalclick,
                        'email_replied' => $totalreply,
                        'email_bounced' => $totalbounced,]
                    );
                    DB::table("temp_emails")->where("record_id", '=', $contact->record_id)->delete();
                }else{
                    DB::table("temp_emails")->where("record_id", '=', $contact->record_id)->delete();
                }
            }
        }
        echo 'all done'; die;
    }
    //update five9 call log in every 12 hrs
    public function getFive9Last12hrsCallLog(){
        ini_set('max_execution_time', 3600);
        $hrs = 24;
        $date = date("Y-m-d", strtotime("-1 day"));
        //$date = "2021-10-27";
        $endFulllDate = $date."T23:00:00";
        $startFullDate = $date."T00:00:01";
        $result = $this->callReportOne($startFullDate, $endFulllDate);
        if($result["id"]){
            if($this->getReportResponse($result["id"])){
                $this->callReportResultOne($result["id"], $date);
            }
        }
    }
    public function callReportOne($edate, $sdate)
    {       
        $folderName = Settings::where('name', '=', 'five9_call_report_name_folder_name_one')->first();
        $reportName = Settings::where('name', '=', 'five9_call_report_name_report_name_one')->first();
        $postFields = '<env:Envelope xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:tns="http://service.admin.ws.five9.com/" xmlns:env="http://schemas.xmlsoap.org/soap/envelope/"
        xmlns:ins0="http://jaxb.dev.java.net/array">
        <env:Body>
            <tns:runReport>
                <folderName>'.$folderName["value"].'</folderName>
                <reportName>'.$reportName["value"].'</reportName>
                    <criteria>
                        <time>
                        <end>'.$sdate.'</end>
                        <start>'.$edate.'</start>
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
        $response = curl_exec($curl); //dd($response);
        curl_close($curl); 
        $xml = $response;
        // SimpleXML seems to have problems with the colon ":" in the <xxx:yyy> response tags, so take them out
        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $xml);
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $return = json_decode($json,true); //dd($return["envBody"]["envFault"]);
        if(isset($return["envBody"]["envFault"])):
            return ['restults' => 'fault'];
        else:
            return [
                'startLength' => strpos($response, '<return>'),
                'endLength' => strrpos($response, '</return>'),
                'serverResponse' => $response,
                'idLength' => strrpos($response, '</return>') - strpos($response, '<return>'),
                'id' => substr($response, strpos($response, '<return>')+8, strrpos($response, '</return>') - strpos($response, '<return>')-8)
            ];
        endif;
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
    public function callReportResultOne($id, $date)
    {
        $curl = curl_init();
        $setting1 = Settings::where('id', '=', 37)->first();
        $setting2 = Settings::where('id', '=', 38)->first();
        $username = $setting1->value;
        $password = $setting2->value;
        $code = base64_encode($username.':'.$password);
        $postData = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.admin.ws.five9.com/">;
        <soapenv:Header/>
        <soapenv:Body>
            <ser:getReportResult>
                <!--Optional:-->
                <identifier>'.$id.'</identifier>
            </ser:getReportResult>
        </soapenv:Body>
        </soapenv:Envelope>';
        $postDataCsv = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.admin.ws.five9.com/">;
        <soapenv:Header/>
        <soapenv:Body>
            <ser:getReportResultCsv>
                <!--Optional:-->
                <identifier>'.$id.'</identifier>
            </ser:getReportResultCsv>
        </soapenv:Body>
        </soapenv:Envelope>';
        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.five9.com/wsadmin/v4/AdminWebService',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_POSTFIELDS =>$postData,
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
        $listRecords = [];
        
        $header = $return["envBody"]["ns2getReportResultResponse"]["return"]["header"]["values"]["data"];
        $headerArray = [];
        foreach($header as $key => $hvalue):
            $headerArray[$key] = str_replace('/', '', implode("_", explode(" ", strtolower($hvalue))));
        endforeach;
        //echo "<pre>"; print_r($headerArray); echo "</pre>";
        $allRecords = [];
        //dd($return["envBody"]["ns2getReportResultResponse"]["return"]);
        if(isset($return["envBody"]["ns2getReportResultResponse"]["return"]["records"])):
            //dd($return["envBody"]["ns2getReportResultResponse"]);
            //csv data call start
            $curlCsv = curl_init();
            curl_setopt_array($curlCsv, array(
                CURLOPT_URL => 'https://api.five9.com/wsadmin/v4/AdminWebService',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_POSTFIELDS =>$postDataCsv,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    "Authorization: Basic $code",
                    "Cookie: clientId=C4A452B18E5646FA9ACBE8A819154296; Authorization=Bearer-ebfa8d5a-ba2a-11eb-abfe-00505fca8def; apiRouteKey=SCLnAPIc6f; app_key=web-ui; farmId=182; uiRouteKey=SCLnUI5551"
                ),
                ));
            $responseCsv = curl_exec($curlCsv);
            curl_close($curlCsv);
            $xmlCsv = $responseCsv;
            // SimpleXML seems to have problems with the colon ":" in the <xxx:yyy> response tags, so take them out
            $xmlCsv = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $xmlCsv);
            $xmlCsv = simplexml_load_string($xmlCsv);
            $jsonCsv = json_encode($xmlCsv);
            $returnCsv = json_decode($jsonCsv,true);
            foreach(explode("\n",$returnCsv["envBody"]["ns2getReportResultCsvResponse"]["return"]) as $key => $value){
                $value = str_getcsv($value, ",", '"');
                if($key > 0 && (count($value) == count($headerArray))){
                    $records = array_combine($headerArray,$value);
                    if( intval($records["record_id"]) > 0){

                        if($records["campaign"] == '[None]'){
                            $campaign = null;
                        }else{
                            $campaign = $records["campaign"];
                        }
                        if($records["call_type"] == '[None]'){
                            $call_type = null;
                        }else{
                            $call_type = $records["call_type"];
                        }
                        if($records["agent_name"] == '[None]'){
                            $agent_name = null;
                        }else{
                            if($records["agent_name"] == ''){
                                $agent_name = null;
                            }else{
                                $agent_name = $records["agent_name"];
                            }
                        }
                        if($records["disposition"] == '[None]'){
                            $disposition = null;
                        }else{
                            $disposition = $records["disposition"];
                        }
                        if($records["customer_name"] == '[None]'){
                            $customer_name = null;
                        }else{
                            $customer_name = $records["customer_name"];
                        }
                        if($records["record_id"] == '[None]'){
                            $record_id = null;
                        }else{
                            $record_id = $records["record_id"];
                        }
                        if($records["dial_attempts"] == '[None]'){
                            $dial_attempts = null;
                        }else{
                            if(intval($records["dial_attempts"]) >= 0){
                                $dial_attempts = intval($records["dial_attempts"]);
                            }else{
                                if($records["dial_attempts"] == '-'){
                                    $dial_attempts = null;    
                                }else{
                                    $dial_attempts = 0;
                                }
                            }
                        }
                        if($records["list_name"] == '[None]'){
                            $list_name = null;
                        }else{
                            $list_name = $records["list_name"];
                        }                
                        if($records["talk_time"] == '[None]'){
                            $talk_time = null;
                        }else{
                            $talk_time = $records["talk_time"];
                        }
                        if($records["consult_time"] == '[None]'){
                            $consult_time = null;
                        }else{
                            $consult_time = $records["consult_time"];
                        }
                        if($records["hold_time"] == '[None]'){
                            $hold_time = null;
                        }else{
                            $hold_time = $records["hold_time"];
                        }
                        if($records["park_time"] == '[None]'){
                            $park_time = null;
                        }else{
                            $park_time = $records["park_time"];
                        }
                        if($records["call_time"] == '[None]'){
                            $call_time = null;
                        }else{
                            $call_time = $records["call_time"];
                        }
                        if($records["cost"] == '[None]'){
                            $cost = null;
                        }else{
                            $cost = $records["cost"];
                        }
                        if($records["handle_time"] == '[None]'){
                            $handle_time = null;
                        }else{
                            $handle_time = $records["handle_time"];
                        }
                        if($records["manual_time"] == '[None]'){
                            $manual_time = null;
                        }else{
                            $manual_time = $records["manual_time"];
                        }
                        if($records["ring_time"] == '[None]'){
                            $ring_time = null;
                        }else{
                            $ring_time = $records["ring_time"];
                        }
                        if($records["talk_time_less_hold_and_park"] == '[None]'){
                            $talk_time_less_hold_and_park = null;
                        }else{
                            $talk_time_less_hold_and_park = $records["talk_time_less_hold_and_park"];
                        }
    
                        if($records["after_call_work_time"] == '[None]'){
                            $after_call_work_time = null;
                        }else{
                            $after_call_work_time = $records["after_call_work_time"];
                        }
                        if($records["queue_wait_time"] == '[None]'){
                            $queue_wait_time = null;
                        }else{
                            $queue_wait_time = $records["queue_wait_time"];
                        }
                        $call_id = $records["call_id"];
                        $dnis = $records["dnis"];
                        $time = explode(":", $talk_time);
                        $seconds = 0;
                        if(intval($time[0]) > 0){ // hours
                            $seconds = $seconds+intval($time[0])*3600;
                        }
                        if(intval($time[1]) > 0){ //minute
                            $seconds = $seconds+intval($time[1])*60;
                        }
                        if(intval($time[2]) > 0){ // seconds
                            $seconds = $seconds+intval($time[2]);
                        }
                        if($seconds > 10){
                            $call_received = 1;
                        }else{
                            $call_received = 0;
                        }                        
                        
                        $ts = strtotime($records["timestamp"]);
                        
                        $prospect = Contacts::where('record_id', $record_id)->get()->first();
                        if($this->__NumberFormater($prospect->mobilePhones) == $dnis): $t = 'm';
                        elseif($this->__NumberFormater($prospect->homePhones) == $dnis): $t = 'hq';
                        elseif($this->__NumberFormater($prospect->workPhones) == $dnis): $t = 'd';
                        else: $t = '0';
                        endif;
                        $exist = TempCalls::where('call_id', '=', $call_id)->count();
                        if($exist == 0){
                            TempCalls::create([
                                "call_id" => $call_id,
                                "number_type" => $t,
                                "timestamp" => $records["timestamp"],
                                "campaign" => $campaign,
                                "call_type" => $call_type,
                                "agent_name" => $agent_name,
                                "disposition" => $disposition,
                                "customer_name" => $customer_name,
                                "dnis" => $dnis,
                                "record_id" => $record_id,
                                "dial_attempts" => $dial_attempts,
                                "list_name" => $list_name,
                                "talk_time" => $talk_time,
                                "date" => $date,
                                "call_received" => $call_received,
                                "n_timestamp" => $ts,
                                "consult_time" => $consult_time,
                                "hold_time" => $hold_time,
                                "park_time" => $park_time,
                                "call_time" => $call_time,
                                "cost" => $cost,
                                "handle_time" => $handle_time,
                                "manual_time" => $manual_time,
                                "ring_time" => $ring_time,
                                "talk_time_less_hold_and_park" => $talk_time_less_hold_and_park,
                                "after_call_work_time" => $after_call_work_time,
                                "queue_wait_time" => $queue_wait_time,
                            ]);
                        }
                    }
                }
                
            }
            echo 'all done'; die;
        else:
            echo 'no record available to update'; die;
        endif;
    }
    
     private function __NumberFormater($var = null)
    {
        $string = str_replace(' ', '', $var); // Replaces all spaces.
        $string = str_replace('-', '', $string); // Replaces all hyphens.
        $string = str_replace('.', '', $string); // Replaces all hyphens.
        $string = str_replace('(', '', $string); // Replaces all hyphens.
        $string = str_replace(')', '', $string); // Replaces all hyphens.
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
        if(strpos($string, 'ext') > 0){
           $d = explode("ext", $string);
           $string = $d[0];
        }
        if(strpos($string, 'EXT') > 0){
            $d = explode("EXT", $string);
            $string = $d[0];
         }
        if(strlen($string) > 10) {
            $string = strrev($string);
            $string = substr($string, 0, 10);
            $string = strrev($string);
        } else {
            $string = substr($string, 0, 10);
        }
        $string = (int) $string;
        return $string;
    }
    
    private function __NumberFormater1($var = null)
    {
        $string = str_replace(' ', '', $var); // Replaces all spaces.
        $string = str_replace('-', '', $string); // Replaces all hyphens.
        $string = str_replace('.', '', $string); // Replaces all hyphens.
        $string = str_replace('(', '', $string); // Replaces all hyphens.
        $string = str_replace(')', '', $string); // Replaces all hyphens.
        $string = preg_replace('/[^0-9\-]/', '', $string); // Removes special chars.        
        return $string;
    }

    public function callCounter(){//dd($records);
        ini_set('max_execution_time', 3600);
        $count = TempCalls::count();//dd($count);
        for($i = 0; $i < $count; $i++){
            if($contact = TempCalls::count() > 0){
                $contact = TempCalls::first();
                $date = date("'Y-m-d", strtotime("now"));
                //$date = '2021-10-14';
                $counts = FivenineCallLogs::where("call_id", "=", $contact->call_id)->count();
                if($counts == 0){
                    if($contact->agent_name == ''){
                        $agent_name = null;
                    }else{
                        $agent_name = $contact->agent_name;
                    }
                    FivenineCallLogs::create([
                        "call_id" => $contact->call_id,
                        "number_type" => $contact->number_type,
                        "timestamp" => $contact->timestamp,
                        "campaign" => $contact->campaign,
                        "call_type" => $contact->call_type,
                        "agent_name" => $agent_name,
                        "disposition" => $contact->disposition,
                        "customer_name" => $contact->customer_name,
                        "dnis" => $contact->dnis,
                        "record_id" => $contact->record_id,
                        "dial_attempts" => $contact->dial_attempts,
                        "list_name" => $contact->list_name,
                        "talk_time" => $contact->talk_time,
                        "date" => $contact->date,
                        "call_received" => $contact->call_received,
                        "n_timestamp" => $contact->n_timestamp,
                        "consult_time" => $contact->consult_time,
                        "hold_time" => $contact->hold_time,
                        "park_time" => $contact->park_time,
                        "call_time" => $contact->call_time,
                        "cost" => $contact->cost,
                        "handle_time" => $contact->handle_time,
                        "manual_time" => $contact->manual_time,
                        "ring_time" => $contact->ring_time,
                        "talk_time_less_hold_and_park" => $contact->talk_time_less_hold_and_park,
                        "after_call_work_time" => $contact->after_call_work_time,
                    ]);
                    $totalmattempt = FivenineCallLogs::where('record_id', $contact->record_id)->where('dial_attempts', '>=', 1)->where('number_type', 'm')->sum('dial_attempts');
                    $totalhattempt = FivenineCallLogs::where('record_id', $contact->record_id)->where('dial_attempts', '>=', 1)->where('number_type', 'hq')->sum('dial_attempts');
                    $totaldattempt = FivenineCallLogs::where('record_id', $contact->record_id)->where('dial_attempts', '>=', 1)->where('number_type', 'd')->sum('dial_attempts');
                    $totalmreceived = FivenineCallLogs::where('record_id', $contact->record_id)->where('call_received', 1)->where('dial_attempts', '>=', 1)->where('number_type', 'm')->count();
                    $totalhqreceived = FivenineCallLogs::where('record_id', $contact->record_id)->where('call_received', 1)->where('dial_attempts', '>=', 1)->where('number_type', 'hq')->count();
                    $totaldreceived = FivenineCallLogs::where('record_id', $contact->record_id)->where('call_received', 1)->where('dial_attempts', '>=', 1)->where('number_type', 'd')->count();      
            
                    $ucontact = Contacts::where("record_id", "=", $contact->record_id)->first();
                    $ucontact->update([
                                'mcall_attempts' => $totalmattempt,
                                'hcall_attempts' => $totalhattempt,
                                'wcall_attempts' => $totaldattempt,
                                'mcall_received' => $totalmreceived,
                                'hcall_received' => $totalhqreceived,
                                'wcall_received' => $totaldreceived]);
                }
                TempCalls::where("call_id", "=", $contact->call_id)->delete();
            }
        }
        echo 'call all done'; die;
    }
    public function callCounterA(){
        ini_set('max_execution_time', 3600);
        //$count = TempCalls::count();
        for($i = 0; $i < 25247; $i++){
            
            $contact = TempCalls::first();
            //echo "<pre>"; print_r(); echo "</pre>";
            if($contact->n_timestamp){
                $count = FivenineCallLogs::where("n_timestamp", "=", $contact->n_timestamp)->where('record_id', '=', $contact->record_id)->count();
                if($count > 0){
                    $record = FivenineCallLogs::where("n_timestamp", "=", $contact->n_timestamp)->where('record_id', '=', $contact->record_id)->first();
                    $record->after_call_work_time = $contact->after_call_work_time;
                    $record->queue_wait_time = $contact->queue_wait_time;
                    $record->save();
                }
                TempCalls::where("id", '=', $contact->id)->delete();
            }
        }
        echo 'all done'; die;
    }
    public function setFive9FieldData($t = null){
        ini_set('max_execution_time', 3600);
        $updated = 0;
        $total = 0;
        $hrs = 30;
        $date = date("Y-m-d", strtotime("now")-$t*3600*24);
        //$date = "2021-09-24";
        $start = $date.'T00:00:00.000Z';
        $end = $date.'T23:59:59.000Z';
        
        $folderName = Settings::where('name', '=', 'five9_call_report_name_folder_name_one')->first();
        $reportName = Settings::where('name', '=', 'five9_call_report_name_report_name_one')->first();

        $postFields = '<env:Envelope xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:tns="http://service.admin.ws.five9.com/" xmlns:env="http://schemas.xmlsoap.org/soap/envelope/"
        xmlns:ins0="http://jaxb.dev.java.net/array">
        <env:Body>
            <tns:runReport>
                <folderName>'.$folderName["value"].'</folderName>
                <reportName>'.$reportName["value"].'</reportName>
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
        //echo '<br>'.$id.'<br>';
        $curl = curl_init();
        for ($i=0; $i < 40; $i++) {
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
            //echo '<br>'.print_r($responseRunning).'<br>';    
            if($result == 'false'){
                break;
            }
        }
        //echo '<br>'.$result.'<br>';
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
        
        $header = $return["envBody"]["ns2getReportResultResponse"]["return"]["header"]["values"]["data"];
        $headerArray = [];
        foreach($header as $key => $hvalue):
            $headerArray[$key] = str_replace('/', '', implode("_", explode(" ", strtolower($hvalue))));
        endforeach;
        $jobHistoryFivenine = [];$jobHistoryFivenineNew = [];
        if(isset($return['envBody']['ns2getReportResultResponse']['return']['records'])): 
            $records = $return['envBody']['ns2getReportResultResponse']['return']['records'];
            $total = count($records);
            $callRecords = [];
            $callRecordCounter = 0;
            foreach($records as $key => $value){ 
                if(isset($value["values"])){
                    if(isset($value["values"]["data"])):
                        $records = array_combine($headerArray,$value["values"]["data"]);
                    else:
                        $records = array_combine($headerArray,$value["data"]);
                    endif;
                    //dd($records);
                    if( $records["dnis"] != '-'){
                        
                        if(is_array($records["record_id"])){
                            $record_id = implode(',', $records["record_id"]);
                        }else{
                            $record_id = $records["record_id"];
                        }
                        
                        if(is_array($records["after_call_work_time"])){
                            $after_call_work_time = implode(',', $records["after_call_work_time"]);
                        }else{
                            $after_call_work_time = $records["after_call_work_time"];
                        }

                        if(is_array($records["queue_wait_time"])){
                            $queue_wait_time = implode(',', $records["queue_wait_time"]);
                        }else{
                            $queue_wait_time = $records["queue_wait_time"];
                        }
                        $dnis = $records["dnis"];
                        //dd($queue_wait_time);
                        $contact = Contacts::where('record_id', '=', $record_id)->count();
                        if($contact > 0){
                            $ts = strtotime($records["timestamp"]);
                            $exist = TempCalls::where("n_timestamp", "=", $ts)->where('record_id', '=', $record_id)->count();
                            if($exist == 0){
                                TempCalls::create([
                                    "n_timestamp" => $ts,
                                    'record_id' => $record_id,
                                    'after_call_work_time' => $after_call_work_time,
                                    'queue_wait_time' => $queue_wait_time
                                    ]);
                            }
                        }
                    }
                }
            }
        endif;
            return view('setFive9FieldData', compact('t', 'date'));
    }
    public function updateFromFive9ToContacts($day){
        if($day < 0){
            echo 'all done'; die;
        }
        ini_set('max_execution_time', 3600);
        $updated = 0;
        $total = 0;
        $d = strtotime("now")-$day*24*3600;
        $start = date("Y-m-d", $d).'T00:00:00.000Z';
        $end = date("Y-m-d", $d).'T23:59:59.000Z';
        //echo $start."--".$end;
        $postFields = '<env:Envelope xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:tns="http://service.admin.ws.five9.com/" xmlns:env="http://schemas.xmlsoap.org/soap/envelope/"
        xmlns:ins0="http://jaxb.dev.java.net/array">
        <env:Body>
            <tns:runReport>
                <folderName>My Reports</folderName>
                <reportName>Biorev Call Log 01</reportName>
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
        //echo '<br>'.$id.'<br>';
        $curl = curl_init();
        for ($i=0; $i < 40; $i++) {
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
            //echo '<br>'.print_r($responseRunning).'<br>';    
            if($result == 'false'){
                break;
            }
        }
        //echo '<br>'.$result.'<br>';
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
        $jobHistoryFivenine = [];$jobHistoryFivenineNew = [];
        if(isset($return['envBody']['ns2getReportResultResponse']['return']['records'])): 
            $records = $return['envBody']['ns2getReportResultResponse']['return']['records'];
            $total = count($records);
            foreach($records as $key => $value){
                if(isset($value["values"])){

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
                        "last_agent" => $Last_Agent,
                        "last_agent_dispo_time" => $Last_Agent_Dispo_Timestamp,
                        "last_dispo" => $Last_Dispo,
                    ];
                    //dd($data);
                    $count = Contacts::where('record_id', '=', $record_id)->count();
                    if($count > 0):
                        ++$updated;
                        $contact = Contacts::where('record_id', '=', $record_id)->first();
                        $contact->last_agent = $Last_Agent;
                        $contact->last_agent_dispo_time = $Last_Agent_Dispo_Timestamp;
                        $contact->last_dispo = $Last_Dispo;
                        $contact->save();    
                    endif;
                }

            }
        endif;
        return view('update-from-five9-to-contacts', compact('day', 'total', 'updated'));
    }
    public function updateContactCustomFieldFromOutrech($page){
        if($page > 50){
            echo 'all done'; die;
        }
        ini_set('max_execution_time', 3600);
        $total = 0;
        $accessTokenProspects = $this->__outreachsessionProspects();
        $perPage = 1000;
        
        if($page > 50){
            echo 'all done'; die;
        }
        $startId = $page*$perPage;
        $endId = ($page+1)*$perPage-1;
        $curl = curl_init();
        $query = "https://api.outreach.io/api/v2/prospects?sort=id&filter[id]=$startId..$endId&count=true&page[limit]=1000&";
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
                "Authorization: Bearer $accessTokenProspects",
                "Content-Type: application/vnd.api+json"
            ),
        ));
        $responseq = curl_exec($curl);
        curl_close($curl);
        $results = get_object_vars(json_decode($responseq));    
        foreach($results['data'] as $key => $value){
            $attributes = get_object_vars($value->attributes);
            $record = Contacts::where('record_id', '=', $value->id)->count();
            if($record):
                $record = Contacts::where('record_id', '=', $value->id)->first();
                $record->custom1 = $attributes["custom1"];
                $record->custom2 = $attributes["custom2"];
                $record->custom9 = $attributes["custom9"];
                $record->custom10 = $attributes["custom10"];
                $record->custom11 = $attributes["custom11"];
                $record->custom12 = $attributes["custom12"];
                $record->save();
                ++$total;
            endif;
        }
        return view('update-contact-custom-field-from-outreach', compact('total', 'page'));
    }
    //sink single email data
    public function reportSingleEmailData(){
        ini_set('max_execution_time', 3600);
        $accessTokenMailings = $this->__outreachsessionMailings();
        $date = date("Y-m-d", strtotime("now")-24*3600);//for crone
        //$date = "2021-09-16";
        $start = $date."T00:00:00Z";
        $end = $date."T23:59:59Z";
        //delivered
        $queryStrings = 'sort=id';
        $queryStrings .= "&filter[mailingType]=single&filter[state]=delivered&filter[deliveredAt]=$start..$end";
        $query = "https://api.outreach.io/api/v2/mailings?$queryStrings&count=true";
        $curl = curl_init();
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
                "Authorization: Bearer $accessTokenMailings",
                "Content-Type: application/vnd.api+json"
            ),
        ));
        $respons = curl_exec($curl);
        curl_close($curl);
        $result = get_object_vars(json_decode($respons));
        $meta = $result["meta"];
        $meta = get_object_vars($meta);
        $count = $meta["count"];
        $deliveredEmail = $count;
        
        //opened opened
        $queryStrings = 'sort=id';
        $queryStrings .= "&filter[mailingType]=single&filter[state]=opened&filter[deliveredAt]=$start..$end";
        $query = "https://api.outreach.io/api/v2/mailings?$queryStrings&count=true";
        $curl = curl_init();
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
                "Authorization: Bearer $accessTokenMailings",
                "Content-Type: application/vnd.api+json"
            ),
        ));
        $respons = curl_exec($curl);
        curl_close($curl);
        $result = get_object_vars(json_decode($respons));
        $meta = $result["meta"];
        $meta = get_object_vars($meta);
        $count = $meta["count"];
        $openedEmail = $count;
        
        //replied
        $queryStrings = 'sort=id';
        $queryStrings .= "&filter[mailingType]=single&filter[state]=replied&filter[deliveredAt]=$start..$end";
        $query = "https://api.outreach.io/api/v2/mailings?$queryStrings&count=true";
        $curl = curl_init();
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
                "Authorization: Bearer $accessTokenMailings",
                "Content-Type: application/vnd.api+json"
            ),
        ));
        $respons = curl_exec($curl);
        curl_close($curl);
        $result = get_object_vars(json_decode($respons));
        $meta = $result["meta"];
        $meta = get_object_vars($meta);
        $count = $meta["count"];
        $repliedEmail = $count;
        
        //bounced
        $queryStrings = 'sort=id';
        $queryStrings .= "&filter[mailingType]=single&filter[state]=bounced&filter[bouncedAt]=$start..$end";
        $query = "https://api.outreach.io/api/v2/mailings?$queryStrings&count=true";
        $curl = curl_init();
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
                "Authorization: Bearer $accessTokenMailings",
                "Content-Type: application/vnd.api+json"
            ),
        ));
        $respons = curl_exec($curl);
        curl_close($curl);
        $result = get_object_vars(json_decode($respons));
        $meta = $result["meta"];
        $meta = get_object_vars($meta);
        $count = $meta["count"];
        $bouncedEmail = $count;
        
        //unsubscribed
        $queryStrings = 'sort=id';
        $queryStrings .= "&filter[mailingType]=single&filter[unsubscribedAt]=$start..$end";
        $query = "https://api.outreach.io/api/v2/mailings?$queryStrings&count=true";
        $curl = curl_init();
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
                "Authorization: Bearer $accessTokenMailings",
                "Content-Type: application/vnd.api+json"
            ),
        ));
        $respons = curl_exec($curl);
        curl_close($curl);
        $result = get_object_vars(json_decode($respons));
        $meta = $result["meta"];
        $meta = get_object_vars($meta);
        $count = $meta["count"];
        $unsubscribedEmail = $count;
        
        $count = ReportMailings::where("date", '=', $date)->count();
        if($count == 0){
            ReportMailings::create([
                "date" => $date,
                "delivered" => $deliveredEmail,
                "opened" => $openedEmail,
                "replied" => $repliedEmail,
                "bounced" => $bouncedEmail,
                "unsubscribed" => $unsubscribedEmail
            ]);
        }else{
            $record = ReportMailings::where("date", '=', $date)->first();
            $record->delivered = $deliveredEmail;
            $record->opened = $openedEmail;
            $record->replied = $repliedEmail;
            $record->bounced = $bouncedEmail;
            $record->unsubscribed = $unsubscribedEmail;
            $record->save();
        }
        echo 'all done'; die;
    }
    //sink sequence email data
    public function reportSequenceEmailData(){
        ini_set('max_execution_time', 3600);
        $accessTokenMailings = $this->__outreachsessionMailings();
        $date = date("Y-m-d", strtotime("now")-24*3600);//for crone
        //$date = "2021-09-16";
        $start = $date."T00:00:00Z";
        $end = $date."T23:59:59Z";
        //delivered
        $queryStrings = 'sort=id';
        $queryStrings .= "&filter[mailingType]=sequence&filter[state]=delivered&filter[deliveredAt]=$start..$end";
        $query = "https://api.outreach.io/api/v2/mailings?$queryStrings&count=true";
        $curl = curl_init();
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
                "Authorization: Bearer $accessTokenMailings",
                "Content-Type: application/vnd.api+json"
            ),
        ));
        $respons = curl_exec($curl);
        curl_close($curl);
        $result = get_object_vars(json_decode($respons));
        $meta = $result["meta"];
        $meta = get_object_vars($meta);
        $count = $meta["count"];
        $deliveredEmail = $count;
        
        //opened opened
        $queryStrings = 'sort=id';
        $queryStrings .= "&filter[mailingType]=sequence&filter[state]=opened&filter[deliveredAt]=$start..$end";
        $query = "https://api.outreach.io/api/v2/mailings?$queryStrings&count=true";
        $curl = curl_init();
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
                "Authorization: Bearer $accessTokenMailings",
                "Content-Type: application/vnd.api+json"
            ),
        ));
        $respons = curl_exec($curl);
        curl_close($curl);
        $result = get_object_vars(json_decode($respons));
        $meta = $result["meta"];
        $meta = get_object_vars($meta);
        $count = $meta["count"];
        $openedEmail = $count;
        
        //replied
        $queryStrings = 'sort=id';
        $queryStrings .= "&filter[mailingType]=sequence&filter[state]=replied&filter[deliveredAt]=$start..$end";
        $query = "https://api.outreach.io/api/v2/mailings?$queryStrings&count=true";
        $curl = curl_init();
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
                "Authorization: Bearer $accessTokenMailings",
                "Content-Type: application/vnd.api+json"
            ),
        ));
        $respons = curl_exec($curl);
        curl_close($curl);
        $result = get_object_vars(json_decode($respons));
        $meta = $result["meta"];
        $meta = get_object_vars($meta);
        $count = $meta["count"];
        $repliedEmail = $count;
        
        //bounced
        $queryStrings = 'sort=id';
        $queryStrings .= "&filter[mailingType]=sequence&filter[state]=bounced&filter[bouncedAt]=$start..$end";
        $query = "https://api.outreach.io/api/v2/mailings?$queryStrings&count=true";
        $curl = curl_init();
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
                "Authorization: Bearer $accessTokenMailings",
                "Content-Type: application/vnd.api+json"
            ),
        ));
        $respons = curl_exec($curl);
        curl_close($curl);
        $result = get_object_vars(json_decode($respons));
        $meta = $result["meta"];
        $meta = get_object_vars($meta);
        $count = $meta["count"];
        $bouncedEmail = $count;
        
        //unsubscribed
        $queryStrings = 'sort=id';
        $queryStrings .= "&filter[mailingType]=sequence&filter[unsubscribedAt]=$start..$end";
        $query = "https://api.outreach.io/api/v2/mailings?$queryStrings&count=true";
        $curl = curl_init();
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
                "Authorization: Bearer $accessTokenMailings",
                "Content-Type: application/vnd.api+json"
            ),
        ));
        $respons = curl_exec($curl);
        curl_close($curl);
        $result = get_object_vars(json_decode($respons));
        $meta = $result["meta"];
        $meta = get_object_vars($meta);
        $count = $meta["count"];
        $unsubscribedEmail = $count;
        
        $count = ReportSequenceMailings::where("date", '=', $date)->count();
        if($count == 0){
            ReportSequenceMailings::create([
                "date" => $date,
                "delivered" => $deliveredEmail,
                "opened" => $openedEmail,
                "replied" => $repliedEmail,
                "bounced" => $bouncedEmail,
                "unsubscribed" => $unsubscribedEmail
            ]);
        }else{
            $record = ReportSequenceMailings::where("date", '=', $date)->first();
            $record->delivered = $deliveredEmail;
            $record->opened = $openedEmail;
            $record->replied = $repliedEmail;
            $record->bounced = $bouncedEmail;
            $record->unsubscribed = $unsubscribedEmail;
            $record->save();
        }
        echo 'all done'; die;
    }
    public function changeDateFormat($i){
        ini_set('max_execution_time', 3600);
        if($i > 100){
            echo "all dates update"; die;
        }
        //for($i = 0; $i < 100; $i++){            
            $records = OutreachMailings::whereBetween("id", [500*$i, 500*($i+1)-1])->get();
            foreach($records as $value){
                $r = OutreachMailings::where("id", '=', $value["id"])->first();
                if($r->bouncedAt){
                    $r->fbouncedAt = date("Y-m-d", strtotime($r->bouncedAt)+12600)."T".date("H:i:s", strtotime($r->bouncedAt)+12600);
                }
                if($r->clickedAt){
                    $r->fclickedAt = date("Y-m-d", strtotime($r->clickedAt)+12600)."T".date("H:i:s", strtotime($r->clickedAt)+12600);
                }
                if($r->createdAt){
                    $r->fcreatedAt = date("Y-m-d", strtotime($r->createdAt)+12600)."T".date("H:i:s", strtotime($r->createdAt)+12600);
                }
                if($r->deliveredAt){
                    $r->fdeliveredAt = date("Y-m-d", strtotime($r->deliveredAt)+12600)."T".date("H:i:s", strtotime($r->deliveredAt)+12600);
                }
                if($r->openedAt){
                    $r->fopenedAt = date("Y-m-d", strtotime($r->openedAt)+12600)."T".date("H:i:s", strtotime($r->openedAt)+12600);
                }
                if($r->repliedAt){
                    $r->frepliedAt = date("Y-m-d", strtotime($r->repliedAt)+12600)."T".date("H:i:s", strtotime($r->repliedAt)+12600);
                }
                if($r->scheduledAt){
                    $r->fscheduledAt = date("Y-m-d", strtotime($r->scheduledAt)+12600)."T".date("H:i:s", strtotime($r->scheduledAt)+12600);
                }
                if($r->stateChangedAt){
                    $r->fstateChangedAt = date("Y-m-d", strtotime($r->stateChangedAt)+12600)."T".date("H:i:s", strtotime($r->stateChangedAt)+12600);
                }
                if($r->updatedAt){
                    $r->fupdatedAt = date("Y-m-d", strtotime($r->updatedAt)+12600)."T".date("H:i:s", strtotime($r->updatedAt)+12600);
                }
                if($r->unsubscribedAt){
                    $r->funsubscribedAt = date("Y-m-d", strtotime($r->unsubscribedAt)+12600)."T".date("H:i:s", strtotime($r->unsubscribedAt)+12600);
                }                
                $r->save();
            }
        //}
        return view('changeDateFormate', compact('i'));
    }
    public function changeDateFormatSwitch($i){
        ini_set('max_execution_time', 3600);
        if($i > 100){
            echo "all dates update"; die;
        }
        //for($i = 0; $i < 100; $i++){            
            $records = OutreachMailings::whereBetween("id", [500*$i, 500*($i+1)-1])->get();
            foreach($records as $value){
                $r = OutreachMailings::where("id", '=', $value["id"])->first();
                if($r->fbouncedAt){
                    $r->bouncedAt = $r->fbouncedAt;
                }
                if($r->fclickedAt){
                    $r->clickedAt = $r->fclickedAt;
                }
                if($r->fcreatedAt){
                    $r->createdAt = $r->fcreatedAt;
                }
                if($r->fdeliveredAt){
                    $r->deliveredAt = $r->fdeliveredAt;
                }
                if($r->fopenedAt){
                    $r->openedAt = $r->fopenedAt;
                }
                if($r->frepliedAt){
                    $r->repliedAt = $r->frepliedAt;
                }
                if($r->fscheduledAt){
                    $r->scheduledAt = $r->fscheduledAt;
                }
                if($r->fstateChangedAt){
                    $r->stateChangedAt = $r->fstateChangedAt;
                }
                if($r->fupdatedAt){
                    $r->updatedAt = $r->fupdatedAt;
                }
                if($r->funsubscribedAt){
                    $r->unsubscribedAt = $r->funsubscribedAt;
                }                
                $r->save();
            }
        //}
        return view('changeDateFormateSwitch', compact('i'));
    }
    //update name field in 'contacts' table
    public function updateContactName($page){
        if($page > 98){
            echo 'all done'; die;
        }
        $total = 0;
        $id = null;
        $records = Contacts::select('record_id')->whereBetween('record_id', [$page*500, ($page+1)*500-1])->get();
        foreach($records as $value){
            $record = Contacts::where('record_id', $value["record_id"])->first();
            $record->name = $record->first_name.' '.$record->last_name;
            $record->save();
            $id = $record->id;
            ++$total;
        }
        return view('updateContactName', compact('page', 'id', 'total'));
    }
    public function getOutreachRecords($i){ 
        // this function import all outreach contacts in database table named 'contacts'
        //for($i = 0; $i < 45; $i++){
            $createdContacts = 0;
            $createdContactsArray = [];
            $stageUpdate = 0;
            $stageUpdateArray = [];
            $contactNoUpdate = 0;
            $contactNoUpdateArray = [];
            $customFieldUpdate = 0;
            $customFieldUpdateArray = [];
            ini_set('max_execution_time', 3600);
            if($i > 500){
                echo 'all done'; die;
            }
            ini_set('max_execution_time', 3600);
            $accessToken = $this->__outreachsessionProspects();  
            $recordUpdated = []; $c = 0; $u = 0; $id = '';        
            $per_page_record = 100;
            $startId = $i*$per_page_record;
            $endId = ($i+1)*$per_page_record-1;
            $curl = curl_init();
            $query = "https://api.outreach.io/api/v2/prospects?sort=id&filter[id]=$startId..$endId&count=true&page[limit]=1000";
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
                if(is_object($owner["data"])):
                    $owner = get_object_vars($owner["data"]);
                    $ownerId = $owner["id"];
                endif;
                
                
                $count = Contacts::where('record_id', '=', $value->id)->count();
                if($count == 0):  
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
                    ++$c;
                else:
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
                    ++$u;
                endif;
                $id = $value->id;            
            }
        //}
        return view('csyncb', compact('c', 'u', 'i', 'id'));
    }
    public function croneAgentOccupancy(){
        ini_set('max_execution_time', 3600);
        
        $date = date("Y-m-d", strtotime("now")-1);
        //$date = "2021-09-01";
        $start = $date.'T00:00:00.000Z';
        $end = $date.'T23:59:59.000Z';
        // $start = '2021-09-01T00:00:00.000Z';
        // $end = '2021-11-01T23:59:59.000Z';
        $postFields = '<env:Envelope xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:tns="http://service.admin.ws.five9.com/" xmlns:env="http://schemas.xmlsoap.org/soap/envelope/"
        xmlns:ins0="http://jaxb.dev.java.net/array">
        <env:Body>
            <tns:runReport>
                <folderName>My Reports</folderName>
                <reportName>Biorev Agent Occupancy</reportName>
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
        $xml = curl_exec($curl);
        curl_close($curl);
        $var = 'ns2runReportResponse';
        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $xml);
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $return = json_decode($json,true);
        
        $id = $return['envBody']["ns2runReportResponse"]['return'];

        $curl = curl_init();
        for ($i=0; $i < 40; $i++) {
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
    
            $xml = curl_exec($curl);
            $var = 'ns2runReportResponse';
            $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $xml);
            $xml = simplexml_load_string($xml);
            $json = json_encode($xml);
            $return = json_decode($json,true);
            if($return['envBody']["ns2isReportRunningResponse"]['return']){
                break;
            }
        }
        curl_close($curl);
        $curl = curl_init();

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
                    <ser:getReportResultCsv>
                        <!--Optional:-->
                        <identifier>'.$id.'</identifier>
                    </ser:getReportResultCsv>
                </soapenv:Body>
                </soapenv:Envelope>',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                "Authorization: Basic $code",
                "Cookie: clientId=C4A452B18E5646FA9ACBE8A819154296; Authorization=Bearer-ebfa8d5a-ba2a-11eb-abfe-00505fca8def; apiRouteKey=SCLnAPIc6f; app_key=web-ui; farmId=182; uiRouteKey=SCLnUI5551"
            ),
            ));
    
            $responseCsv = curl_exec($curl);
            curl_close($curl);
            $xmlCsv = $responseCsv;
            // SimpleXML seems to have problems with the colon ":" in the <xxx:yyy> response tags, so take them out
            $xmlCsv = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $xmlCsv);
            $xmlCsv = simplexml_load_string($xmlCsv);
            $jsonCsv = json_encode($xmlCsv);
            $returnCsv = json_decode($jsonCsv,true);
            foreach(explode("\n",$returnCsv["envBody"]["ns2getReportResultCsvResponse"]["return"]) as $key => $value){
                $value = str_getcsv($value, ",", '"');
                if($key == 0){
                    foreach($value as $head){
                        $headerArray[] = str_replace(' ', '_', strtolower($head));
                    }
                }
            }
            foreach(explode("\n",$returnCsv["envBody"]["ns2getReportResultCsvResponse"]["return"]) as $key => $value){
                $value = str_getcsv($value, ",", '"');
                if($key > 0 && (count($value) == count($headerArray))){
                    $records = array_combine($headerArray,$value);
                    //check record existance
                    $count = AgentOccupancy::whereDate("date", $records["date"])->where("agent", "LIKE", $records["agent"])->count();
                    if($count == 0){
                        //create record
                        $data["agent"] = ($records["agent"]) ? $records["agent"] : null;
                        $data["agent_first_name"] = ($records["agent_first_name"]) ? $records["agent_first_name"] : null;
                        $data["agent_last_name"] = ($records["agent_last_name"]) ? $records["agent_last_name"] : null;
                        $data["date"] = ($records["date"]) ? $records["date"] : null;
                        $data["login_time"] = ($records["login_time"]) ? $records["login_time"] : null;
                        $data["not_ready_time"] = ($records["not_ready_time"]) ? $records["not_ready_time"] : null;
                        $data["wait_time"] = ($records["wait_time"]) ? $records["wait_time"] : null;
                        $data["ringing_time"] = ($records["ringing_time"]) ? $records["ringing_time"] : null;
                        $data["on_call_time"] = ($records["on_call_time"]) ? $records["on_call_time"] : null;
                        //$data["on_voicemail_time"] = ($records["on_voicemail_time"]) ? $records["on_voicemail_time"] : null;
                        $data["on_acw_time"] = ($records["on_acw_time"]) ? $records["on_acw_time"] : null;
                        //$data["logout_time"] = ($records["logout_time"]) ? $records["logout_time"] : null;
                        AgentOccupancy::insert($data);
                    }else{
                        //update record
                        $agentOccupancy = AgentOccupancy::whereDate("date", $records["date"])->where("agent", "LIKE", $records["agent"])->first();
                        
                        if($records["login_time"]){
                            $login = 0;
                            $login_time_new = 0;
                            if($agentOccupancy->login_time){
                                $login = $this->__getSeconds($agentOccupancy->login_time);
                            }
                            if($records["login_time"]){
                                $login_time_new = $this->__getSeconds($records["login_time"]);
                            }
                            $agentOccupancy->login_time = gmdate("H:i:s", $login + $login_time_new);
                        }
                        if($records["not_ready_time"]){
                            $not_ready_time = 0;
                            $not_ready_time_new = 0;
                            if($agentOccupancy->not_ready_time){
                                $not_ready_time = $this->__getSeconds($agentOccupancy->not_ready_time);
                            }
                            if($records["not_ready_time"]){
                                $not_ready_time_new = $this->__getSeconds($records["not_ready_time"]);
                            }
                            $agentOccupancy->not_ready_time = gmdate("H:i:s", $not_ready_time + $not_ready_time_new);
                        }
                        if($records["ringing_time"]){
                            $ringing_time = 0;
                            $ringing_time_new = 0;
                            if($agentOccupancy->ringing_time){
                                $ringing_time = $this->__getSeconds($agentOccupancy->ringing_time);
                            }
                            if($records["ringing_time"]){
                                $ringing_time_new = $this->__getSeconds($records["ringing_time"]);
                            }
                            $agentOccupancy->ringing_time = gmdate("H:i:s", $ringing_time + $ringing_time_new);
                        }
                        if($records["on_call_time"]){
                            $on_call_time = 0;
                            $on_call_time_new = 0;
                            if($agentOccupancy->on_call_time){
                                $on_call_time = $this->__getSeconds($agentOccupancy->on_call_time);
                            }
                            if($records["on_call_time"]){
                                $on_call_time_new = $this->__getSeconds($records["on_call_time"]);
                            }
                            $agentOccupancy->on_call_time = gmdate("H:i:s", $on_call_time + $on_call_time_new);
                        }
                        if($records["on_acw_time"]){
                            $on_acw_time = 0;
                            $on_acw_time_new = 0;
                            if($agentOccupancy->on_acw_time){
                                $on_acw_time = $this->__getSeconds($agentOccupancy->on_acw_time);
                            }
                            if($records["on_acw_time"]){
                                $on_acw_time_new = $this->__getSeconds($records["on_acw_time"]);
                            }
                            $agentOccupancy->on_acw_time = gmdate("H:i:s", $on_acw_time + $on_acw_time_new);
                        }
                        $agentOccupancy->save();
                        
                    }
                    // dd($records);
                }
            } 
        }
    public function setWaitTime(){
        $records = AgentOccupancy::get();
        foreach($records as $value){
            ($value["login_time"]) ? $login_time = $this->__getSeconds($value["login_time"]) : $login_time = 0;
            ($value["not_ready_time"]) ? $not_ready_time = $this->__getSeconds($value["not_ready_time"]) : $not_ready_time = 0;
            ($value["on_call_time"]) ? $on_call_time = $this->__getSeconds($value["on_call_time"]) : $on_call_time = 0;
            ($value["on_acw_time"]) ? $on_acw_time = $this->__getSeconds($value["on_acw_time"]) : $on_acw_time = 0;
            
            $wait_time = $login_time - ($not_ready_time + $on_call_time + $on_acw_time);
            $available_time = $login_time - $not_ready_time;
            $record = AgentOccupancy::where("id", $value["id"])->first();
            $record->wait_time =  gmdate("H:i:s", $wait_time);
            $record->available_time =  gmdate("H:i:s", $available_time);
            $record->save();
        }die;
    }
    private function __getSeconds($time){
        $time = Carbon::createFromFormat("H:i:s", $time)->toArray();
        return $time["hour"]*3600 + $time["minute"]*60 + $time["second"];
    }
}   
