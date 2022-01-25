<?php

namespace App\Http\Controllers;

use App\Events\ListInfoUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Contacts;
use App\Models\CroneHistories;
use App\Models\JobHistory;
use App\Models\Settings;
use App\Models\Stages;
use App\Models\Templates;
use App\Models\OutreachAccounts;
use App\Models\OutreachTasks;
use App\Models\OutreachSequences;
use App\Models\OutreachSequenceStates;
use App\Models\OutreachMailings;
use App\Models\FivenineCallLogs;
use App\Models\ContactCustoms;

class HomeController extends Controller
{

    public function __construct()
    {
       // $this->middleware('auth');
    }

    public function manualUpdate()
    {
        ini_set('memory_limit', '8192M');
        echo '<pre>';
        $calls = FivenineCallLogs::whereNull('record_id')->get()->pluck('dnis')->toArray();
        print_r($calls);
        die;
    }


    public function index()
    {
        return view('home');
    }

    public function outreachsession()
    {
        $curl = curl_init();
        $token_expire = Settings::where('id', '=', 7)->first();
        if($token_expire['value'] <= strtotime("now")):
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.outreach.io/oauth/token?client_id=eUBHpxrv-UgUE_NloPqbbcHsJn0VpV1mj9JEraoh_jg&client_secret=D6e9SmqUTDcQLna_BhZst97JonI7wnZAE_Y5KuCcqFE&redirect_uri=https://www.biorev.us/oauth/outreach&grant_type=refresh_token&refresh_token=sNLqOEE0j9Pd7rhv6HnY23YzaJHzql0bVKAF58B-enA",
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

    //stage update 
    public function stageUpdateInOutreach(){
        $stages = Stages::get();
        foreach ($stages as $key => $value) {
            Contacts::where('stage', '=', $value->stage)->update(['stage' => $value->oid]);
        }
        Contacts::whereNull('stage')->update(['stage' => 0]);
        echo 'all done'; die;
    }

    public function getOutreachRecords(){
        // this function import all outreach contacts in database table named 'contacts'
        for($i = 0; $i < 45; $i++){
            if($i == 44){
                echo 'all done'; die;
            }
            ini_set('max_execution_time', 3600);
            $accessToken = $this->outreachsession();  
            $recordUpdated = []; $c = 0; $u = 0; $id = '';        
            $per_page_record = 1000;
            $startId = $i*$per_page_record;
            $endId = ($i+1)*$per_page_record-1;
            $curl = curl_init();
            $query = "https://api.outreach.io/api/v2/prospects?sort=id&filter[id]=$startId..$endId&count=true&page[limit]=1000&&include=persona,stage,opportunities,owner";
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
                foreach($results["included"] as $ikey => $ivalue):
                    $ivalue = get_object_vars($ivalue);
                    if( ($ivalue["type"] == 'stage') && ($ivalue["id"]) == $stageId ):
                        $stageDetail = get_object_vars($ivalue["attributes"]);
                        $stageName = $stageDetail["name"];
                    endif;
                    if( ($ivalue["type"] == 'persona') && ($ivalue["id"]) == $personaId ):
                        $personaDetail = get_object_vars($ivalue["attributes"]);
                        $personaName = $personaDetail["name"];
                    endif;
                    if( ($ivalue["type"] == 'user') && ($ivalue["id"]) == $ownerId ):
                        $ownerDetail = get_object_vars($ivalue["attributes"]);
                        $ownerName = $ownerDetail["name"];
                    endif;
                endforeach;
                
                $count = Contacts::where('record_id', '=', $value->id)->count();
                if($count == 0):  
                    Contacts::create([
                        "record_id" => $value->id,
                        "account_id" => $accountId,
                        "designation" => $attributes['title'],
                        "first_name" => $attributes['firstName'],
                        "last_name" => $attributes['lastName'],
                        "emails" => implode(',', $attributes['emails']),
                        "company" => $attributes['company'],
                        "address" => $attributes["addressStreet"]." ".$attributes["addressStreet2"],
                        "city" => $attributes['addressCity'],
                        "state" => $attributes['addressState'],
                        "zip" => $attributes['addressZip'],
                        "country" => $attributes['addressCountry'],
                        "outreach_tag" => implode(',', $attributes['tags']),
                        "engage_score" => $attributes['engagedScore'],
                        "last_outreach_engage" => $attributes['engagedAt'],
                        "stage" => $stageId,
                        "last_outreach_email" => $attributes['emailsOptedAt'],
                        "outreach_touched_at" => $attributes['touchedAt'],
                        "personal_onote" => $attributes['personalNote1'],
                        "last_update_at" => $attributes['updatedAt'],
                        "outreach_created_at" => $attributes['createdAt'],
                        "linkedInUrl" => $attributes['linkedInUrl'],
                        "websiteUrl1" => $attributes['websiteUrl1'],
                        "source" => $attributes['source'],
                        "title" => $attributes['title'],
                        "outreach_owner" => $ownerName,
                        "outreach_persona" => $personaName,
                        "purchase_authorization" => $attributes['custom1'],
                        "department" => $attributes['custom2'],
                        "job_function" => $attributes['custom3'],
                        "supplemental_email" => $attributes['custom4'],
                        "company_hq_phone" => $attributes['custom5'],
                        "custom6" => $attributes['custom6'],
                        "custom7" => $attributes['custom7'],
                        "industry" => $attributes['custom9'],
                        "primary_industry" => $attributes['custom10'],
                        "company_revenue" => $attributes['custom11'],
                        "zoominfo_accuracy" => $attributes['custom19'],
                        "subsource" => $attributes['custom35'],
                        "secondary_industry" => $attributes['custom38'],
                        "mobilePhones" => implode(',', $attributes['mobilePhones']),
                        "workPhones" => implode(',', $attributes['workPhones']),
                        "homePhones" => implode(',', $attributes['homePhones']),
                        "otherPhones" => implode(',', $attributes['otherPhones']),
                        "voipPhones" => implode(',', $attributes['voipPhones']),
                        "occupation" => $attributes['occupation'],
                    ]);
                    $c++; 
                else:
                    $prospect = Contacts::where('record_id', '=', $value->id)->first();  
                    $prospect->account_id = $accountId;
                    $prospect->emails = implode(',', $attributes['emails']);
                    $prospect->outreach_tag = implode(',', $attributes['tags']);
                    $prospect->personal_note = $attributes['personalNote1'];
                    $prospect->stage =$stageId;
                    $prospect->last_outreach_email = $attributes['emailsOptedAt'];
                    $prospect->outreach_touched_at = $attributes['touchedAt'];
                    $prospect->engage_score =  $attributes['engagedScore'];
                    $prospect->last_outreach_engage = $attributes['engagedAt'];
                    $prospect->last_update_at = $attributes['updatedAt'];
                    $prospect->outreach_created_at = $attributes['createdAt'];
                    $prospect->linkedInUrl = $attributes['linkedInUrl'];
                    $prospect->websiteUrl1 = $attributes['websiteUrl1'];
                    $prospect->source = $attributes['source'];
                    $prospect->title = $attributes['title'];
                    $prospect->outreach_owner = $ownerName;
                    $prospect->purchase_authorization = $attributes['custom1'];
                    $prospect->department = $attributes['custom2'];
                    $prospect->job_function = $attributes['custom3'];
                    $prospect->supplemental_email = $attributes['custom4'];
                    $prospect->company_hq_phone = $attributes['custom5'];
                    $prospect->custom6 = $attributes['custom6'];
                    $prospect->custom7 = $attributes['custom7'];
                    $prospect->industry = $attributes['custom9'];
                    $prospect->primary_industry = $attributes['custom10'];
                    $prospect->company_revenue = $attributes['custom11'];
                    $prospect->zoominfo_accuracy = $attributes['custom19'];
                    $prospect->subsource = $attributes['custom35'];
                    $prospect->secondary_industry = $attributes['custom38'];
                    $prospect->mobilePhones = implode(',', $attributes['mobilePhones']);
                    $prospect->workPhones = implode(',', $attributes['workPhones']);
                    $prospect->homePhones = implode(',', $attributes['homePhones']);
                    $prospect->otherPhones = implode(',', $attributes['otherPhones']);
                    $prospect->voipPhones = implode(',', $attributes['voipPhones']);
                    $prospect->occupation = $attributes['occupation'];
                    $prospect->save();    
                    $u++;
                endif;
                $id = $value->id;     
                echo 'all done';die;       
            }
        }
        //return view('csyncb', compact('c', 'u', 'i', 'id'));
    }

    public function updateAll()
    {
        ini_set('max_execution_time', 3600);
        // outreach starts
        $accessToken = $this->outreachsession();
        $recordUpdated = 0;
        $recordCreated = 0;
        $jobHistoryOutreach = [];  
        $jobHistoryOutreachNew = [];
        //created
        $now = strtotime("now");
        $nowEnd = strtotime("now") - 12*3600;
        $end = date("Y-m-d", $now).'T'.date("H:i:s", $now).'.000Z';
        $start = date("Y-m-d", $nowEnd).'T'.date("H:i:s", $nowEnd).'.000Z';
        $queryStrings = 'sort=id';
        $queryStrings .= "&filter[createdAt]=$start..$end"; //dd($queryStrings);
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
        if($res2["count"] > 0):
            $page = intval(ceil(intval($res2["count"])/50));                   
            for ($i=0; $i < $page; $i++) { 
                $queryString = '';
                $queryString = 'sort=id';
                $queryString .= "&filter[createdAt]=$start..$end";
                $curl = curl_init();
                if($i == 0):
                    $query = "https://api.outreach.io/api/v2/prospects?$queryString&count=true&page[offset]=0&include=persona,stage,owner";
                else:
                    $offset = $i*50;
                    $query = "https://api.outreach.io/api/v2/prospects?$queryString&count=true&page[offset]=$offset&include=persona,stage,owner";
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
                    $ownerName = null;
                    $ownerId = null;
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
                    if(isset($owner["data"]) && is_object($owner["data"])):
                        $owner = get_object_vars($owner["data"]);
                        $ownerId = $owner["id"];
                    endif;
                    foreach($results["included"] as $ikey => $ivalue):
                        $ivalue = get_object_vars($ivalue);
                        if( ($ivalue["type"] == 'stage') && ($ivalue["id"]) == $stageId ):
                            $stageDetail = get_object_vars($ivalue["attributes"]);
                            $stageName = $stageDetail["name"];
                        endif;
                        if( ($ivalue["type"] == 'persona') && ($ivalue["id"]) == $personaId ):
                            $personaDetail = get_object_vars($ivalue["attributes"]);
                            $personaName = $personaDetail["name"];
                        endif;
                        if( ($ivalue["type"] == 'user') && ($ivalue["id"]) == $ownerId ):
                            $ownerDetail = get_object_vars($ivalue["attributes"]);
                            $ownerName = $ownerDetail["name"];
                        endif;
                    endforeach;
                    $count = Contacts::where('record_id', '=', $value->id)->count();
                    if($count == 0){
                        $jobHistoryOutreachNew[] = $value->id;
                        $contact = Contacts::create([
                            "record_id" => $value->id,
                            "account_id" => $accountId,
                            "designation" => $attributes['title'],
                            "first_name" => $attributes['firstName'],
                            "last_name" => $attributes['lastName'],
                            "emails" => implode(',', $attributes['emails']),
                            "company" => $attributes['company'],
                            "address" => $attributes["addressStreet"]." ".$attributes["addressStreet2"],
                            "city" => $attributes['addressCity'],
                            "state" => $attributes['addressState'],
                            "zip" => $attributes['addressZip'],
                            "country" => $attributes['addressCountry'],
                            "outreach_tag" => implode(',', $attributes['tags']),
                            "engage_score" => $attributes['engagedScore'],
                            "last_outreach_engage" => $attributes['engagedAt'],
                            "stage" => $stageId,
                            "last_outreach_email" => $attributes['emailsOptedAt'],
                            "outreach_touched_at" => $attributes['touchedAt'],
                            "personal_onote" => $attributes['personalNote1'],
                            "last_update_at" => $attributes['updatedAt'],
                            "outreach_created_at" => $attributes['createdAt'],
                            "linkedInUrl" => $attributes['linkedInUrl'],
                            "websiteUrl1" => $attributes['websiteUrl1'],
                            "source" => $attributes['source'],
                            "title" => $attributes['title'],
                            "outreach_owner" => $ownerName,
                            "outreach_persona" => $personaName,
                            "purchase_authorization" => $attributes['custom1'],
                            "department" => $attributes['custom2'],
                            "job_function" => $attributes['custom3'],
                            "supplemental_email" => $attributes['custom4'],
                            "company_hq_phone" => $attributes['custom5'],
                            "custom6" => $attributes['custom6'],
                            "custom7" => $attributes['custom7'],
                            "industry" => $attributes['custom9'],
                            "primary_industry" => $attributes['custom10'],
                            "company_revenue" => $attributes['custom11'],
                            "zoominfo_accuracy" => $attributes['custom19'],
                            "subsource" => $attributes['custom35'],
                            "secondary_industry" => $attributes['custom38'],
                            "mobilePhones" => implode(',', $attributes['mobilePhones']),
                            "workPhones" => implode(',', $attributes['workPhones']),
                            "homePhones" => implode(',', $attributes['homePhones']),
                            "otherPhones" => implode(',', $attributes['otherPhones']),
                            "voipPhones" => implode(',', $attributes['voipPhones']),
                            "occupation" => $attributes['occupation'],
                        ]);
                        $data = [];
                        $data["contact_id"] = $value->id;
                        for($j = 1 ; $j < 151; $j++){
                            $data["custom".$j] = $attributes["custom".$j];
                        }
                        ContactCustoms::create($data);
                        //$recordCreated++;
                    }else{
                        $prospect = Contacts::where('record_id', '=', $value->id)->first();  
                        $prospect->account_id = $accountId;
                        $prospect->emails = implode(',', $attributes['emails']);
                        $prospect->outreach_tag = implode(',', $attributes['tags']);
                        $prospect->personal_note = $attributes['personalNote1'];
                        $prospect->stage =$stageId;
                        $prospect->last_outreach_email = $attributes['emailsOptedAt'];
                        $prospect->outreach_touched_at = $attributes['touchedAt'];
                        $prospect->engage_score =  $attributes['engagedScore'];
                        $prospect->last_outreach_engage = $attributes['engagedAt'];
                        $prospect->last_update_at = $attributes['updatedAt'];
                        $prospect->outreach_created_at = $attributes['createdAt'];
                        $prospect->linkedInUrl = $attributes['linkedInUrl'];
                        $prospect->websiteUrl1 = $attributes['websiteUrl1'];
                        $prospect->source = $attributes['source'];
                        $prospect->title = $attributes['title'];
                        $prospect->outreach_owner = $ownerName;
                        $prospect->purchase_authorization = $attributes['custom1'];
                        $prospect->department = $attributes['custom2'];
                        $prospect->job_function = $attributes['custom3'];
                        $prospect->supplemental_email = $attributes['custom4'];
                        $prospect->company_hq_phone = $attributes['custom5'];
                        $prospect->custom6 = $attributes['custom6'];
                        $prospect->custom7 = $attributes['custom7'];
                        $prospect->industry = $attributes['custom9'];
                        $prospect->primary_industry = $attributes['custom10'];
                        $prospect->company_revenue = $attributes['custom11'];
                        $prospect->zoominfo_accuracy = $attributes['custom19'];
                        $prospect->subsource = $attributes['custom35'];
                        $prospect->secondary_industry = $attributes['custom38'];
                        $prospect->mobilePhones = implode(',', $attributes['mobilePhones']);
                        $prospect->workPhones = implode(',', $attributes['workPhones']);
                        $prospect->homePhones = implode(',', $attributes['homePhones']);
                        $prospect->otherPhones = implode(',', $attributes['otherPhones']);
                        $prospect->voipPhones = implode(',', $attributes['voipPhones']);
                        $prospect->occupation = $attributes['occupation'];
                        $prospect->save();   
                        $custome = ContactCustoms::where('contact_id', '=', $value->id)->first();  
                        $custome->contact_id = $value->id;
                        for($j = 1 ; $j < 151; $j++){
                            $custome["custom".$j] = $attributes["custom".$j];
                        }
                        $custome->save(); 
                        $jobHistoryOutreach[] = $value->id;
                    }
                }
            }
        endif;
        //engaged
        $queryStrings = 'sort=id';
        $queryStrings .= "&filter[engagedAt]=$start..$end";
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
        if($res2["count"]):
            $page = intval(ceil(intval($res2["count"])/50));
            
            for ($i=0; $i < $page; $i++) { 
                $queryString = '';
                $queryString = 'sort=id';
                $queryString .= "&filter[engagedAt]=$start..$end";
                $curl = curl_init();
                if($i == 0):
                    $query = "https://api.outreach.io/api/v2/prospects?$queryString&count=true&page[offset]=0&include=persona,stage,owner";
                else:
                    $offset = $i*50;
                    $query = "https://api.outreach.io/api/v2/prospects?$queryString&count=true&page[offset]=$offset&include=persona,stage,owner";
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
                    $ownerName = null;
                    $ownerId = null;
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
                    if(isset($owner["data"]) && is_object($owner["data"])):
                        $owner = get_object_vars($owner["data"]);
                        $ownerId = $owner["id"];
                    endif;
                    foreach($results["included"] as $ikey => $ivalue):
                        $ivalue = get_object_vars($ivalue);
                        if( ($ivalue["type"] == 'stage') && ($ivalue["id"]) == $stageId ):
                            $stageDetail = get_object_vars($ivalue["attributes"]);
                            $stageName = $stageDetail["name"];
                        endif;
                        if( ($ivalue["type"] == 'persona') && ($ivalue["id"]) == $personaId ):
                            $personaDetail = get_object_vars($ivalue["attributes"]);
                            $personaName = $personaDetail["name"];
                        endif;
                        if( ($ivalue["type"] == 'user') && ($ivalue["id"]) == $ownerId ):
                            $ownerDetail = get_object_vars($ivalue["attributes"]);
                            $ownerName = $ownerDetail["name"];
                        endif;
                    endforeach;
                    $count = Contacts::where('record_id', '=', $value->id)->count();
                    if($count == 0){
                        $jobHistoryOutreachNew[] = $value->id;
                        $contact = Contacts::create([
                            "record_id" => $value->id,
                        "account_id" => $accountId,
                        "designation" => $attributes['title'],
                        "first_name" => $attributes['firstName'],
                        "last_name" => $attributes['lastName'],
                        "emails" => implode(',', $attributes['emails']),
                        "company" => $attributes['company'],
                        "address" => $attributes["addressStreet"]." ".$attributes["addressStreet2"],
                        "city" => $attributes['addressCity'],
                        "state" => $attributes['addressState'],
                        "zip" => $attributes['addressZip'],
                        "country" => $attributes['addressCountry'],
                        "outreach_tag" => implode(',', $attributes['tags']),
                        "engage_score" => $attributes['engagedScore'],
                        "last_outreach_engage" => $attributes['engagedAt'],
                        "stage" => $stageId,
                        "last_outreach_email" => $attributes['emailsOptedAt'],
                        "outreach_touched_at" => $attributes['touchedAt'],
                        "personal_onote" => $attributes['personalNote1'],
                        "last_update_at" => $attributes['updatedAt'],
                        "outreach_created_at" => $attributes['createdAt'],
                        "linkedInUrl" => $attributes['linkedInUrl'],
                        "websiteUrl1" => $attributes['websiteUrl1'],
                        "source" => $attributes['source'],
                        "title" => $attributes['title'],
                        "outreach_owner" => $ownerName,
                        "outreach_persona" => $personaName,
                        "purchase_authorization" => $attributes['custom1'],
                        "department" => $attributes['custom2'],
                        "job_function" => $attributes['custom3'],
                        "supplemental_email" => $attributes['custom4'],
                        "company_hq_phone" => $attributes['custom5'],
                        "custom6" => $attributes['custom6'],
                        "custom7" => $attributes['custom7'],
                        "industry" => $attributes['custom9'],
                        "primary_industry" => $attributes['custom10'],
                        "company_revenue" => $attributes['custom11'],
                        "zoominfo_accuracy" => $attributes['custom19'],
                        "subsource" => $attributes['custom35'],
                        "secondary_industry" => $attributes['custom38'],
                        "mobilePhones" => implode(',', $attributes['mobilePhones']),
                        "workPhones" => implode(',', $attributes['workPhones']),
                        "homePhones" => implode(',', $attributes['homePhones']),
                        "otherPhones" => implode(',', $attributes['otherPhones']),
                        "voipPhones" => implode(',', $attributes['voipPhones']), 
                        "occupation" => $attributes['occupation'],
                        ]);
                        $data = [];
                        $data["contact_id"] = $value->id;
                        for($j = 1 ; $j < 151; $j++){
                            $data["custom".$j] = $attributes["custom".$j];
                        }
                        ContactCustoms::create($data);
                        //$recordCreated++;
                    }else{
                        $prospect = Contacts::where('record_id', '=', $value->id)->first();  
                        $prospect->account_id = $accountId;
                        $prospect->emails = implode(',', $attributes['emails']);
                        $prospect->outreach_tag = implode(',', $attributes['tags']);
                        $prospect->personal_note = $attributes['personalNote1'];
                        $prospect->stage =$stageId;
                        $prospect->last_outreach_email = $attributes['emailsOptedAt'];
                        $prospect->outreach_touched_at = $attributes['touchedAt'];
                        $prospect->engage_score =  $attributes['engagedScore'];
                        $prospect->last_outreach_engage = $attributes['engagedAt'];
                        $prospect->last_update_at = $attributes['updatedAt'];
                        $prospect->outreach_created_at = $attributes['createdAt'];
                        $prospect->linkedInUrl = $attributes['linkedInUrl'];
                        $prospect->websiteUrl1 = $attributes['websiteUrl1'];
                        $prospect->source = $attributes['source'];
                        $prospect->title = $attributes['title'];
                        $prospect->outreach_owner = $ownerName;
                        $prospect->purchase_authorization = $attributes['custom1'];
                        $prospect->department = $attributes['custom2'];
                        $prospect->job_function = $attributes['custom3'];
                        $prospect->supplemental_email = $attributes['custom4'];
                        $prospect->company_hq_phone = $attributes['custom5'];
                        $prospect->custom6 = $attributes['custom6'];
                        $prospect->custom7 = $attributes['custom7'];
                        $prospect->industry = $attributes['custom9'];
                        $prospect->primary_industry = $attributes['custom10'];
                        $prospect->company_revenue = $attributes['custom11'];
                        $prospect->zoominfo_accuracy = $attributes['custom19'];
                        $prospect->subsource = $attributes['custom35'];
                        $prospect->secondary_industry = $attributes['custom38'];
                        $prospect->mobilePhones = implode(',', $attributes['mobilePhones']);
                        $prospect->workPhones = implode(',', $attributes['workPhones']);
                        $prospect->homePhones = implode(',', $attributes['homePhones']);
                        $prospect->otherPhones = implode(',', $attributes['otherPhones']);
                        $prospect->voipPhones = implode(',', $attributes['voipPhones']);
                        $prospect->occupation = $attributes['occupation'];
                        $prospect->save();   
                        $custome = ContactCustoms::where('contact_id', '=', $value->id)->first();  
                        $custome->contact_id = $value->id;
                        for($j = 1 ; $j < 151; $j++){
                            $custome["custom".$j] = $attributes["custom".$j];
                        }
                        $custome->save();  
                        $jobHistoryOutreach[] = $value->id;
                    }
                }
            }
        endif;
        //touchedAt
        $queryStrings = 'sort=id';
        
        $queryStrings .= "&filter[touchedAt]=$start..$end";
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
        if($res2["count"]):
            $page = intval(ceil(intval($res2["count"])/50));
                    
            for ($i=0; $i < $page; $i++) { 
                $queryString = '';
                $queryString = 'sort=id';
                $queryString .= "&filter[touchedAt]=$start..$end";
                $curl = curl_init();
                if($i == 0):
                    $query = "https://api.outreach.io/api/v2/prospects?$queryString&count=true&page[offset]=0&include=persona,stage,owner";
                else:
                    $offset = $i*50;
                    $query = "https://api.outreach.io/api/v2/prospects?$queryString&count=true&page[offset]=$offset&include=persona,stage,owner";
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
                    if(isset($owner["data"]) && is_object($owner["data"])):
                        $owner = get_object_vars($owner["data"]);
                        $ownerId = $owner["id"];
                    endif;
                    foreach($results["included"] as $ikey => $ivalue):
                        $ivalue = get_object_vars($ivalue);
                        if( ($ivalue["type"] == 'stage') && ($ivalue["id"]) == $stageId ):
                            $stageDetail = get_object_vars($ivalue["attributes"]);
                            $stageName = $stageDetail["name"];
                        endif;
                        if( ($ivalue["type"] == 'persona') && ($ivalue["id"]) == $personaId ):
                            $personaDetail = get_object_vars($ivalue["attributes"]);
                            $personaName = $personaDetail["name"];
                        endif;
                        if( ($ivalue["type"] == 'user') && ($ivalue["id"]) == $ownerId ):
                            $ownerDetail = get_object_vars($ivalue["attributes"]);
                            $ownerName = $ownerDetail["name"];
                        endif;
                    endforeach;
                    $count = Contacts::where('record_id', '=', $value->id)->count();
                    if($count == 0){
                        $jobHistoryOutreachNew[] = $value->id;
                        $contact = Contacts::create([
                            "record_id" => $value->id,
                            "account_id" => $accountId,
                            "designation" => $attributes['title'],
                            "first_name" => $attributes['firstName'],
                            "last_name" => $attributes['lastName'],
                            "emails" => implode(',', $attributes['emails']),
                            "company" => $attributes['company'],
                            "address" => $attributes["addressStreet"]." ".$attributes["addressStreet2"],
                            "city" => $attributes['addressCity'],
                            "state" => $attributes['addressState'],
                            "zip" => $attributes['addressZip'],
                            "country" => $attributes['addressCountry'],
                            "outreach_tag" => implode(',', $attributes['tags']),
                            "engage_score" => $attributes['engagedScore'],
                            "last_outreach_engage" => $attributes['engagedAt'],
                            "stage" => $stageId,
                            "last_outreach_email" => $attributes['emailsOptedAt'],
                            "outreach_touched_at" => $attributes['touchedAt'],
                            "personal_onote" => $attributes['personalNote1'],
                            "last_update_at" => $attributes['updatedAt'],
                            "outreach_created_at" => $attributes['createdAt'],
                            "linkedInUrl" => $attributes['linkedInUrl'],
                            "websiteUrl1" => $attributes['websiteUrl1'],
                            "source" => $attributes['source'],
                            "title" => $attributes['title'],
                            "outreach_owner" => $ownerName,
                            "outreach_persona" => $personaName,
                            "purchase_authorization" => $attributes['custom1'],
                            "department" => $attributes['custom2'],
                            "job_function" => $attributes['custom3'],
                            "supplemental_email" => $attributes['custom4'],
                            "company_hq_phone" => $attributes['custom5'],
                            "custom6" => $attributes['custom6'],
                            "custom7" => $attributes['custom7'],
                            "industry" => $attributes['custom9'],
                            "primary_industry" => $attributes['custom10'],
                            "company_revenue" => $attributes['custom11'],
                            "zoominfo_accuracy" => $attributes['custom19'],
                            "subsource" => $attributes['custom35'],
                            "secondary_industry" => $attributes['custom38'],
                            "mobilePhones" => implode(',', $attributes['mobilePhones']),
                            "workPhones" => implode(',', $attributes['workPhones']),
                            "homePhones" => implode(',', $attributes['homePhones']),
                            "otherPhones" => implode(',', $attributes['otherPhones']),
                            "voipPhones" => implode(',', $attributes['voipPhones']), 
                            "occupation" => $attributes['occupation'],
                        ]);
                        $data = [];
                        $data["contact_id"] = $value->id;
                        for($j = 1 ; $j < 151; $j++){
                            $data["custom".$j] = $attributes["custom".$j];
                        }
                        ContactCustoms::create($data);
                        //$recordCreated++;
                    }else{
                        $prospect = Contacts::where('record_id', '=', $value->id)->first();  
                        $prospect->account_id = $accountId;
                        $prospect->emails = implode(',', $attributes['emails']);
                        $prospect->outreach_tag = implode(',', $attributes['tags']);
                        $prospect->personal_note = $attributes['personalNote1'];
                        $prospect->stage =$stageId;
                        $prospect->last_outreach_email = $attributes['emailsOptedAt'];
                        $prospect->outreach_touched_at = $attributes['touchedAt'];
                        $prospect->engage_score =  $attributes['engagedScore'];
                        $prospect->last_outreach_engage = $attributes['engagedAt'];
                        $prospect->last_update_at = $attributes['updatedAt'];
                        $prospect->outreach_created_at = $attributes['createdAt'];
                        $prospect->linkedInUrl = $attributes['linkedInUrl'];
                        $prospect->websiteUrl1 = $attributes['websiteUrl1'];
                        $prospect->source = $attributes['source'];
                        $prospect->title = $attributes['title'];
                        $prospect->outreach_owner = $ownerName;
                        $prospect->purchase_authorization = $attributes['custom1'];
                        $prospect->department = $attributes['custom2'];
                        $prospect->job_function = $attributes['custom3'];
                        $prospect->supplemental_email = $attributes['custom4'];
                        $prospect->company_hq_phone = $attributes['custom5'];
                        $prospect->custom6 = $attributes['custom6'];
                        $prospect->custom7 = $attributes['custom7'];
                        $prospect->industry = $attributes['custom9'];
                        $prospect->primary_industry = $attributes['custom10'];
                        $prospect->company_revenue = $attributes['custom11'];
                        $prospect->zoominfo_accuracy = $attributes['custom19'];
                        $prospect->subsource = $attributes['custom35'];
                        $prospect->secondary_industry = $attributes['custom38'];
                        $prospect->mobilePhones = implode(',', $attributes['mobilePhones']);
                        $prospect->workPhones = implode(',', $attributes['workPhones']);
                        $prospect->homePhones = implode(',', $attributes['homePhones']);
                        $prospect->otherPhones = implode(',', $attributes['otherPhones']);
                        $prospect->voipPhones = implode(',', $attributes['voipPhones']);
                        $prospect->occupation = $attributes['occupation'];
                        $prospect->save();    
                        $custome = ContactCustoms::where('contact_id', '=', $value->id)->first();  
                        $custome->contact_id = $value->id;
                        for($j = 1 ; $j < 151; $j++){
                            $custome["custom".$j] = $attributes["custom".$j];
                        }
                        $custome->save();  
                        $jobHistoryOutreach[] = $value->id;
                    }
                }
            }
        endif;
        //stage changed
        $stages = Stages::get();
        foreach ($stages as $key => $value) {
            Contacts::where('stage', '=', $value->stage)->update(['stage' => $value->oid]);
        }
        Contacts::whereNull('stage')->update(['stage' => 0]);
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
        if($totalRecord > 0):
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
        endif;
        // five9 query start
        
        // $start = date("Y-m-d", strtotime("-2 day")).'T'.date("H:i:s", strtotime("-2 days"));
        // $end = date("Y-m-d", strtotime("now")).'T'.date("H:i:s", strtotime("now"));
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
        $jobHistoryFivenine = [];$jobHistoryFivenineNew = [];
        if(isset($return['envBody']['ns2getReportResultResponse']['return']['records'])): 
            $records = $return['envBody']['ns2getReportResultResponse']['return']['records'];
            $created = 0; $updated = 0; 
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
        else:
        endif;
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

    public function getAccounts(){
        // this function import all outreach contacts in database table named 'contacts'
        for($i = 0; $i < 55; $i++){
            if($i == 50){
                echo 'all done'; die;
            }
            ini_set('max_execution_time', 1800);
            $accessToken = $this->outreachsession();  
            $recordUpdated = []; $c = 0; $u = 0; $id = '';        
            $per_page_record = 1000;
            $startId = $i*$per_page_record;
            $endId = ($i+1)*$per_page_record-1;
            $curl = curl_init();
            $query = "https://api.outreach.io/api/v2/accounts?sort=id&filter[id]=$startId..$endId&count=true&page[limit]=1000&include=tasks,owner&fields[user]=name";
            
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
                $ownerName = null;
                // $personaName = null;
                $ownerId = null;
                // $personaId = null;
                $attributes = get_object_vars($value->attributes);
                //echo '<pre>'; print_r($attributes); echo '</pre>';

                $relationships = get_object_vars($value->relationships);
                $owner = get_object_vars($relationships["owner"]);
                // $persona = get_object_vars($relationships["persona"]);
                if(is_object($owner["data"])):
                    $owner = get_object_vars($owner["data"]);
                    $ownerId = $owner["id"];
                endif;
                foreach($results["included"] as $ikey => $ivalue):
                    $ivalue = get_object_vars($ivalue);
                    if( ($ivalue["type"] == 'user') && ($ivalue["id"]) == $ownerId ):
                        $ownerDetail = get_object_vars($ivalue["attributes"]);
                        $ownerName = $ownerDetail["name"];
                    endif;
                endforeach;
                if($attributes["tags"]):
                    if(count($attributes["tags"]) == 0):
                        $tags = null;
                    else:
                        $tags = implode(",", $attributes["tags"]);
                    endif;
                else:
                    $tags = null;
                endif;
                $count = OutreachAccounts::where('account_id', '=', $value->id)->count();
                if($count == 0):
                    OutreachAccounts::create([
                        "account_id" =>   $value->id,
                        "buyerIntentScore" => $attributes["buyerIntentScore"],
                        "createdAt" => $attributes["createdAt"],
                        "custom3" => $attributes["custom3"],
                        "description" => $attributes["description"],
                        "domain" => $attributes["domain"],
                        "externalSource" => $attributes["externalSource"],
                        "foundedAt" => $attributes["foundedAt"],
                        "industry" => $attributes["industry"],
                        "linkedInEmployees" => $attributes["linkedInEmployees"],
                        "linkedInUrl" => $attributes["linkedInUrl"],
                        "name" => $attributes["name"],
                        "named" => $attributes["named"],
                        "naturalName" => $attributes["naturalName"],
                        "numberOfEmployees" => $attributes["numberOfEmployees"],
                        "sharingTeamId" => $attributes["sharingTeamId"],
                        "tags" => $tags,
                        "touchedAt" => $attributes["touchedAt"],
                        "trashedAt" => $attributes["trashedAt"],
                        "updatedAt" => $attributes["updatedAt"],
                        "websiteUrl" => $attributes["websiteUrl"],
                        "locality" => $attributes["locality"],
                        "owner" => $ownerName,
                        "custom1" => $attributes["custom1"],
                        "custom2" => $attributes["custom2"],
                        "custom3" => $attributes["custom3"],
                        "custom4" => $attributes["custom4"],
                        "custom5" => $attributes["custom5"],
                        "custom6" => $attributes["custom6"],
                        "custom7" => $attributes["custom7"],
                        "custom8" => $attributes["custom8"],
                        "custom9" => $attributes["custom9"],
                        "custom10" => $attributes["custom10"],
                        "custom11" => $attributes["custom11"],
                        "custom12" => $attributes["custom12"],
                        "custom13" => $attributes["custom13"],
                        "custom14" => $attributes["custom14"],
                        "custom15" => $attributes["custom15"],
                        "custom16" => $attributes["custom16"],
                        "custom17" => $attributes["custom17"],
                        "custom18" => $attributes["custom18"],
                        "custom19" => $attributes["custom19"],
                        "custom20" => $attributes["custom20"],
                        "custom21" => $attributes["custom21"],
                        "custom22" => $attributes["custom22"],
                        "custom23" => $attributes["custom23"],
                        "custom24" => $attributes["custom24"],
                        "custom25" => $attributes["custom25"],
                        "custom26" => $attributes["custom26"],
                        "custom27" => $attributes["custom27"],
                        "custom28" => $attributes["custom28"],
                        "custom29" => $attributes["custom29"],
                        "custom30" => $attributes["custom30"],
                        "custom31" => $attributes["custom31"],
                        "custom32" => $attributes["custom32"],
                        "custom33" => $attributes["custom33"],
                        "custom34" => $attributes["custom34"],
                        "custom35" => $attributes["custom35"],
                    ]); 
                    $c++; 
                else:
                    $record = OutreachAccounts::where('account_id', '=', $value->id)->first();
                        $record->buyerIntentScore = $attributes["buyerIntentScore"];
                        $record->createdAt = $attributes["createdAt"];
                        $record->custom3 = $attributes["custom3"];
                        $record->description = $attributes["description"];
                        $record->domain = $attributes["domain"];
                        $record->externalSource = $attributes["externalSource"];
                        $record->foundedAt = $attributes["foundedAt"];
                        $record->industry = $attributes["industry"];
                        $record->linkedInEmployees = $attributes["linkedInEmployees"];
                        $record->linkedInUrl = $attributes["linkedInUrl"];
                        $record->name = $attributes["name"];
                        $record->named = $attributes["named"];
                        $record->naturalName = $attributes["naturalName"];
                        $record->numberOfEmployees = $attributes["numberOfEmployees"];
                        $record->sharingTeamId = $attributes["sharingTeamId"];
                        $record->touchedAt = $attributes["touchedAt"];
                        $record->trashedAt = $attributes["trashedAt"];
                        $record->updatedAt = $attributes["updatedAt"];
                        $record->websiteUrl = $attributes["websiteUrl"];
                        $record->locality = $attributes["locality"];
                        $record->tags = $tags;
                        $record->owner = $ownerName;
                        $record->custom1 = $attributes["custom1"];
                        $record->custom2 = $attributes["custom2"];
                        $record->custom3 = $attributes["custom3"];
                        $record->custom4 = $attributes["custom4"];
                        $record->custom5 = $attributes["custom5"];
                        $record->custom6 = $attributes["custom6"];
                        $record->custom7 = $attributes["custom7"];
                        $record->custom8 = $attributes["custom8"];
                        $record->custom9 = $attributes["custom9"];
                        $record->custom10 = $attributes["custom10"];
                        $record->custom11 = $attributes["custom11"];
                        $record->custom12 = $attributes["custom12"];
                        $record->custom13 = $attributes["custom13"];
                        $record->custom14 = $attributes["custom14"];
                        $record->custom15 = $attributes["custom15"];
                        $record->custom16 = $attributes["custom16"];
                        $record->custom17 = $attributes["custom17"];
                        $record->custom18 = $attributes["custom18"];
                        $record->custom19 = $attributes["custom19"];
                        $record->custom20 = $attributes["custom20"];
                        $record->custom21 = $attributes["custom21"];
                        $record->custom22 = $attributes["custom22"];
                        $record->custom23 = $attributes["custom23"];
                        $record->custom24 = $attributes["custom24"];
                        $record->custom25 = $attributes["custom25"];
                        $record->custom26 = $attributes["custom26"];
                        $record->custom27 = $attributes["custom27"];
                        $record->custom28 = $attributes["custom28"];
                        $record->custom29 = $attributes["custom29"];
                        $record->custom30 = $attributes["custom30"];
                        $record->custom31 = $attributes["custom31"];
                        $record->custom32 = $attributes["custom32"];
                        $record->custom33 = $attributes["custom33"];
                        $record->custom34 = $attributes["custom34"];
                        $record->custom35 = $attributes["custom35"];
                        $record->save();
                    $u++;
                endif;
                $id = $value->id;            
            }
        }
        //return view('csyncaccounts', compact('c', 'u', 'i', 'id'));
    }

    public function getOutreachTaks(){
        // this function import all outreach tasks in database table named 'outreach_tasks'
        for($i = 0; $i < 55; $i++){
            if($i == 50){
                echo 'all done'; die;
            }
            ini_set('max_execution_time', 1800);
            $accessToken = $this->outreachsession();  
            $recordUpdated = []; $c = 0; $u = 0; $id = '';        
            $per_page_record = 1000;
            $startId = $i*$per_page_record;
            $endId = ($i+1)*$per_page_record-1;
            $curl = curl_init();
            $query = "https://api.outreach.io/api/v2/tasks?sort=id&filter[id]=$startId..$endId&count=true&page[limit]=1000&&include=prospect,prospectAccount,sequence";
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
                $prospectId = null;
                $prospectAccountId = null;
                $sequenceId = null;
                $attributes = get_object_vars($value->attributes);
                $relationships = get_object_vars($value->relationships);
                $prospect = get_object_vars($relationships["prospect"]);
                if(is_object($prospect["data"])):
                    $prospect = get_object_vars($prospect["data"]);
                    $prospectId = $prospect["id"];
                endif;
                $prospectAccount = get_object_vars($relationships["prospectAccount"]);
                if(is_object($prospectAccount["data"])):
                    $prospectAccount = get_object_vars($prospectAccount["data"]);
                    $prospectAccountId = $prospectAccount["id"];
                endif;
                $sequence = get_object_vars($relationships["sequence"]);
                if(is_object($sequence["data"])):
                    $sequence = get_object_vars($sequence["data"]);
                    $sequenceId = $sequence["id"];
                endif;
                
                $count = OutreachTasks::where('tasks_id', '=', $value->id)->count();
                if($count == 0):  
                    OutreachTasks::create([
                        "tasks_id" => $value->id,
                        "action" => $attributes['action'],
                        "autoskipAt" => $attributes['autoskipAt'],
                        "compiledSequenceTemplateHtml" => $attributes['compiledSequenceTemplateHtml'],                    
                        "completed" => $attributes['completed'],
                        "completedAt" => $attributes["completedAt"],
                        "createdAt" => $attributes['createdAt'],
                        "dueAt" => $attributes['dueAt'],
                        "note" => $attributes['note'],
                        "scheduledAt" => $attributes['scheduledAt'],
                        "state" => $attributes['state'],
                        "stateChangedAt" => $attributes['stateChangedAt'],
                        "taskType" => $attributes['taskType'],
                        "updatedAt" => $attributes['updatedAt'],
                        "contact_id" => $prospectId,
                        "outreach_account_id" => $prospectAccountId,
                        "outreach_sequence_id" => $sequenceId,
                    ]);
                    $c++; 
                else:
                    $task = OutreachTasks::where('tasks_id', '=', $value->id)->first();  
                    $task->action = $attributes['action'];
                    $task->autoskipAt = $attributes['autoskipAt'];
                    $task->compiledSequenceTemplateHtml = $attributes['compiledSequenceTemplateHtml'];
                    $task->completed = $attributes['completed'];
                    $task->completedAt = $attributes["completedAt"];
                    $task->createdAt = $attributes['createdAt'];
                    $task->dueAt = $attributes['dueAt'];
                    $task->note = $attributes['note'];
                    $task->scheduledAt = $attributes['scheduledAt'];
                    $task->state = $attributes['state'];
                    $task->stateChangedAt = $attributes['stateChangedAt'];
                    $task->taskType = $attributes['taskType'];
                    $task->updatedAt = $attributes['updatedAt'];
                    $task->contact_id = $prospectId;
                    $task->outreach_account_id = $prospectAccountId;
                    $task->outreach_sequence_id = $sequenceId;
                    $task->save();    
                    $u++;
                endif;
                $id = $value->id;            
            }
        }
        //return view('csynTask', compact('c', 'u', 'i', 'id'));
    }

    public function getOutreachSequences(){
        // this function import all outreach tasks in database table named 'outreach_tasks'
        for($i = 0; $i < 2; $i++){
            if($i == 1){
                echo 'all done'; die;
            }
            ini_set('max_execution_time', 1800);
            $accessToken = $this->outreachsession();  
            $recordUpdated = []; $c = 0; $u = 0; $id = '';        
            $per_page_record = 1000;
            $startId = $i*$per_page_record;
            $endId = ($i+1)*$per_page_record-1;
            $curl = curl_init();
            $query = "https://api.outreach.io/api/v2/sequences?sort=id&filter[id]=$startId..$endId&count=true&page[limit]=1000&";
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
                if(count($attributes["tags"]) > 0):
                    $tags = implode(",", $attributes["tags"]);
                else:
                    $tags = null;
                endif;
                $count = OutreachSequences::where('sequence_id', '=', $value->id)->count();
                if($count == 0):  

                    OutreachSequences::create([
                        "sequence_id" => $value->id,
                        "automationPercentage" => $attributes["automationPercentage"],
                        "bounceCount" => $attributes["bounceCount"],
                        "clickCount" => $attributes["clickCount"],
                        "createdAt" => $attributes["createdAt"],
                        "deliverCount" => $attributes["deliverCount"],
                        "description" => $attributes["description"],
                        "durationInDays" => $attributes["durationInDays"],
                        "enabled" => $attributes["enabled"],
                        "enabledAt" => $attributes["enabledAt"],
                        "failureCount" => $attributes["failureCount"],
                        "finishOnReply" => $attributes["finishOnReply"],
                        "lastUsedAt" => $attributes["lastUsedAt"],
                        "locked" => $attributes["locked"],
                        "lockedAt" => $attributes["lockedAt"],
                        "maxActivations" => $attributes["maxActivations"],
                        "name" => $attributes["name"],
                        "negativeReplyCount" => $attributes["negativeReplyCount"],
                        "neutralReplyCount" => $attributes["neutralReplyCount"],
                        "numContactedProspects" => $attributes["numContactedProspects"],
                        "numRepliedProspects" => $attributes["numRepliedProspects"],
                        "openCount" => $attributes["openCount"],
                        "optOutCount" => $attributes["optOutCount"],
                        "positiveReplyCount" => $attributes["positiveReplyCount"],
                        "primaryReplyAction" => $attributes["primaryReplyAction"],
                        "primaryReplyPauseDuration" => $attributes["primaryReplyPauseDuration"],
                        "replyCount" => $attributes["replyCount"],
                        "scheduleCount" => $attributes["scheduleCount"],
                        "scheduleIntervalType" => $attributes["scheduleIntervalType"],
                        "secondaryReplyAction" => $attributes["secondaryReplyAction"],
                        "secondaryReplyPauseDuration" => $attributes["secondaryReplyPauseDuration"],
                        "sequenceStepCount" => $attributes["sequenceStepCount"],
                        "sequenceType" => $attributes["sequenceType"],
                        "shareType" => $attributes["shareType"],
                        "tags" => $tags,
                        "throttleCapacity" => $attributes["throttleCapacity"],
                        "throttleMaxAddsPerDay" => $attributes["throttleMaxAddsPerDay"],
                        "throttlePaused" => $attributes["throttlePaused"],
                        "throttlePausedAt" => $attributes["throttlePausedAt"],
                        "transactional" => $attributes["transactional"],
                        "updatedAt" => $attributes["updatedAt"],
                                        
                    ]);
                    $c++; 
                else:
                    $sequence = OutreachSequences::where('sequence_id', '=', $value->id)->first();  
                    $sequence->automationPercentage = $attributes["automationPercentage"];
                    $sequence->bounceCount = $attributes["bounceCount"];
                    $sequence->clickCount = $attributes["clickCount"];
                    $sequence->createdAt = $attributes["createdAt"];
                    $sequence->deliverCount = $attributes["deliverCount"];
                    $sequence->description = $attributes["description"];
                    $sequence->durationInDays = $attributes["durationInDays"];
                    $sequence->enabled = $attributes["enabled"];
                    $sequence->enabledAt = $attributes["enabledAt"];
                    $sequence->failureCount = $attributes["failureCount"];
                    $sequence->finishOnReply = $attributes["finishOnReply"];
                    $sequence->lastUsedAt = $attributes["lastUsedAt"];
                    $sequence->locked = $attributes["locked"];
                    $sequence->lockedAt = $attributes["lockedAt"];
                    $sequence->maxActivations = $attributes["maxActivations"];
                    $sequence->name = $attributes["name"];
                    $sequence->negativeReplyCount = $attributes["negativeReplyCount"];
                    $sequence->neutralReplyCount = $attributes["neutralReplyCount"];
                    $sequence->numContactedProspects = $attributes["numContactedProspects"];
                    $sequence->numRepliedProspects = $attributes["numRepliedProspects"];
                    $sequence->openCount = $attributes["openCount"];
                    $sequence->optOutCount = $attributes["optOutCount"];
                    $sequence->positiveReplyCount = $attributes["positiveReplyCount"];
                    $sequence->primaryReplyAction = $attributes["primaryReplyAction"];
                    $sequence->primaryReplyPauseDuration = $attributes["primaryReplyPauseDuration"];
                    $sequence->replyCount = $attributes["replyCount"];
                    $sequence->scheduleCount = $attributes["scheduleCount"];
                    $sequence->scheduleIntervalType = $attributes["scheduleIntervalType"];
                    $sequence->secondaryReplyAction = $attributes["secondaryReplyAction"];
                    $sequence->secondaryReplyPauseDuration = $attributes["secondaryReplyPauseDuration"];
                    $sequence->sequenceStepCount = $attributes["sequenceStepCount"];
                    $sequence->sequenceType = $attributes["sequenceType"];
                    $sequence->shareType = $attributes["shareType"];
                    $sequence->tags = $tags;
                    $sequence->throttleCapacity = $attributes["throttleCapacity"];
                    $sequence->throttleMaxAddsPerDay = $attributes["throttleMaxAddsPerDay"];
                    $sequence->throttlePaused = $attributes["throttlePaused"];
                    $sequence->throttlePausedAt = $attributes["throttlePausedAt"];
                    $sequence->transactional = $attributes["transactional"];
                    $sequence->updatedAt = $attributes["updatedAt"];
                    $sequence->save();
                    $u++;
                endif;
                $id = $value->id;            
            }
        }
        //return view('csynSequence', compact('c', 'u', 'i', 'id'));
    }

    public function getOutreachSequenceStates(){
        // this function import all outreach tasks in database table named 'outreach_tasks'
        for($i = 0; $i < 10; $i++){
            if($i == 6){
                echo 'all done'; die;
            }
            ini_set('max_execution_time', 1800);
            $accessToken = $this->outreachsession();  
            $recordUpdated = []; $c = 0; $u = 0; $id = '';        
            $per_page_record = 1000;
            $startId = $i*$per_page_record;
            $endId = ($i+1)*$per_page_record-1;
            $curl = curl_init();
            $query = "https://api.outreach.io/api/v2/sequenceStates?sort=id&filter[id]=$startId..$endId&count=true&page[limit]=1000&include=account,prospect,sequence,";
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
                $prospectId = null;
                $sequenceId = null;

                $attributes = get_object_vars($value->attributes);   
                $relationships = get_object_vars($value->relationships);

                $account = get_object_vars($relationships["account"]);
                $prospect = get_object_vars($relationships["prospect"]);
                $sequence = get_object_vars($relationships["sequence"]);

                if(is_object($account["data"])):
                    $account = get_object_vars($account["data"]);
                    $accountId = $account["id"];
                endif;
                if(is_object($prospect["data"])):
                    $prospect = get_object_vars($prospect["data"]);
                    $prospectId = $prospect["id"];
                endif;
                if(is_object($sequence["data"])):
                    $sequence = get_object_vars($sequence["data"]);
                    $sequenceId = $sequence["id"];
                endif;
                
                $count = OutreachSequenceStates::where('sequence_id', '=', $value->id)->count();
                if($count == 0):  
                    $data = ["sequence_state_id" => $value->id,
                    "activeAt" => $attributes["activeAt"],
                    "bounceCount" => $attributes["bounceCount"],
                    "callCompletedAt" => $attributes["callCompletedAt"],
                    "clickCount" => $attributes["clickCount"],
                    "createdAt" => $attributes["createdAt"],
                    "deliverCount" => $attributes["deliverCount"],
                    "errorReason" => $attributes["errorReason"],
                    "failureCount" => $attributes["failureCount"],
                    "negativeReplyCount" => $attributes["negativeReplyCount"],
                    "neutralReplyCount" => $attributes["neutralReplyCount"],
                    "openCount" => $attributes["openCount"],
                    "optOutCount" => $attributes["optOutCount"],
                    "pauseReason" => $attributes["pauseReason"],
                    "positiveReplyCount" => $attributes["positiveReplyCount"],
                    "repliedAt" => $attributes["repliedAt"],
                    "replyCount" => $attributes["replyCount"],
                    "scheduleCount" => $attributes["scheduleCount"],
                    "state" => $attributes["state"],
                    "stateChangedAt" => $attributes["stateChangedAt"],
                    "updatedAt" => $attributes["updatedAt"],
                    "account_id" => $accountId,
                    "sequence_id" => $sequenceId,
                    "prospect_id" => $prospectId,];
                    //dd($data);
                    OutreachSequenceStates::create([
                        "sequence_state_id" => $value->id,
                        "activeAt" => $attributes["activeAt"],
                        "bounceCount" => $attributes["bounceCount"],
                        "callCompletedAt" => $attributes["callCompletedAt"],
                        "clickCount" => $attributes["clickCount"],
                        "createdAt" => $attributes["createdAt"],
                        "deliverCount" => $attributes["deliverCount"],
                        "errorReason" => $attributes["errorReason"],
                        "failureCount" => $attributes["failureCount"],
                        "negativeReplyCount" => $attributes["negativeReplyCount"],
                        "neutralReplyCount" => $attributes["neutralReplyCount"],
                        "openCount" => $attributes["openCount"],
                        "optOutCount" => $attributes["optOutCount"],
                        "pauseReason" => $attributes["pauseReason"],
                        "positiveReplyCount" => $attributes["positiveReplyCount"],
                        "repliedAt" => $attributes["repliedAt"],
                        "replyCount" => $attributes["replyCount"],
                        "scheduleCount" => $attributes["scheduleCount"],
                        "state" => $attributes["state"],
                        "stateChangedAt" => $attributes["stateChangedAt"],
                        "updatedAt" => $attributes["updatedAt"],
                        "account_id" => $accountId,
                        "sequence_id" => $sequenceId,
                        "prospect_id" => $prospectId,
                    ]);
                    $c++; 
                else:
                    $sequenceState = OutreachSequenceStates::where('sequence_id', '=', $value->id)->first();  
                    $sequenceState->activeAt = $attributes["activeAt"];
                    $sequenceState->bounceCount = $attributes["bounceCount"];
                    $sequenceState->callCompletedAt = $attributes["callCompletedAt"];
                    $sequenceState->clickCount = $attributes["clickCount"];
                    $sequenceState->createdAt = $attributes["createdAt"];
                    $sequenceState->deliverCount = $attributes["deliverCount"];
                    $sequenceState->errorReason = $attributes["errorReason"];
                    $sequenceState->failureCount = $attributes["failureCount"];
                    $sequenceState->negativeReplyCount = $attributes["negativeReplyCount"];
                    $sequenceState->neutralReplyCount = $attributes["neutralReplyCount"];
                    $sequenceState->openCount = $attributes["openCount"];
                    $sequenceState->optOutCount = $attributes["optOutCount"];
                    $sequenceState->pauseReason = $attributes["pauseReason"];
                    $sequenceState->positiveReplyCount = $attributes["positiveReplyCount"];
                    $sequenceState->repliedAt = $attributes["repliedAt"];
                    $sequenceState->replyCount = $attributes["replyCount"];
                    $sequenceState->scheduleCount = $attributes["scheduleCount"];
                    $sequenceState->state = $attributes["state"];
                    $sequenceState->stateChangedAt = $attributes["stateChangedAt"];
                    $sequenceState->updatedAt = $attributes["updatedAt"];
                    $sequenceState->account_id = $accountId;
                    $sequenceState->sequence_id = $sequenceId;
                    $sequenceState->prospect_id = $prospectId;
                    $sequence->save();
                    $u++;
                endif;
                $id = $value->id;            
            }
        }
        return view('csynSequenceState', compact('c', 'u', 'i', 'id'));
    }

    public function getOutreachMailings(){
        // this function import all outreach mailings in database table named 'outreach_mailings'
        for($i = 0; $i < 55; $i++){
            if($i == 620){
                echo 'all done'; die;
            }
            ini_set('max_execution_time', 3600);
            $accessToken = $this->outreachsession();  
            $recordUpdated = []; $c = 0; $u = 0; $id = '';        
            $per_page_record = 100;
            $startId = $i*$per_page_record;
            $endId = ($i+1)*$per_page_record-1;
            $curl = curl_init();
            $query = "https://api.outreach.io/api/v2/mailings?sort=id&filter[id]=$startId..$endId&count=true&page[limit]=100";
            // dd();
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
            //echo '<pre>';dd($responseq);echo '</pre>';
            $results = get_object_vars(json_decode($responseq)); 
            if(count($results['data']) > 0){
                foreach($results['data'] as $key => $value){
                    $attributes = get_object_vars($value->attributes);
                    $relationships = get_object_vars($value->relationships);
                    $prospect = get_object_vars($relationships["prospect"]);
                    $prospectId = null;
                    if(is_object($prospect["data"])):
                        $prospect = get_object_vars($prospect["data"]);
                        $prospectId = $prospect["id"];
                    endif;
                    $count = OutreachMailings::where('mailing_id', '=', $value->id)->count();
                    if($count == 0):
                        OutreachMailings::create([
                            "mailing_id" => $value->id,
                            "contact_id" => $prospectId,
                            "bouncedAt" => $attributes["bouncedAt"],
                            "clickCount" => $attributes["clickCount"],
                            "clickedAt" => $attributes["clickedAt"],
                            "createdAt" => $attributes["createdAt"],
                            "deliveredAt" => $attributes["deliveredAt"],
                            "errorBacktrace" => $attributes["errorBacktrace"],
                            "errorReason" => $attributes["errorReason"],
                            "followUpTaskScheduledAt" => $attributes["followUpTaskScheduledAt"],
                            "followUpTaskType" => $attributes["followUpTaskType"],
                            "mailboxAddress" => $attributes["mailboxAddress"],
                            "mailingType" => $attributes["mailingType"],
                            "markedAsSpamAt" => $attributes["markedAsSpamAt"],
                            "messageId" => $attributes["messageId"],
                            "notifyThreadCondition" => $attributes["notifyThreadCondition"],
                            "notifyThreadScheduledAt" => $attributes["notifyThreadScheduledAt"],
                            "notifyThreadStatus" => $attributes["notifyThreadStatus"],
                            "openCount" => $attributes["openCount"],
                            "openedAt" => $attributes["openedAt"],
                            "overrideSafetySettings" => $attributes["overrideSafetySettings"],
                            "repliedAt" => $attributes["repliedAt"],
                            "retryAt" => $attributes["retryAt"],
                            "retryCount" => $attributes["retryCount"],
                            "retryInterval" => $attributes["retryInterval"],
                            "scheduledAt" => $attributes["scheduledAt"],
                            "state" => $attributes["state"],
                            "stateChangedAt" => $attributes["stateChangedAt"],
                            "subject" => $attributes["subject"],
                            "trackLinks" => $attributes["trackLinks"],
                            "trackOpens" => $attributes["trackOpens"],
                            "unsubscribedAt" => $attributes["unsubscribedAt"],
                            "updatedAt" => $attributes["updatedAt"],
                        ]); 
                        $c++; 
                    else:
                        $record = OutreachMailings::where('mailing_id', '=', $value->id)->first();
                        $record->contact_id = $prospectId;
                        $record->bouncedAt = $attributes["bouncedAt"];
                        $record->clickCount = $attributes["clickCount"];
                        $record->clickedAt = $attributes["clickedAt"];
                        $record->createdAt = $attributes["createdAt"];
                        $record->deliveredAt = $attributes["deliveredAt"];
                        $record->errorBacktrace = $attributes["errorBacktrace"];
                        $record->errorReason = $attributes["errorReason"];
                        $record->followUpTaskScheduledAt = $attributes["followUpTaskScheduledAt"];
                        $record->followUpTaskType = $attributes["followUpTaskType"];
                        $record->mailboxAddress = $attributes["mailboxAddress"];
                        $record->mailingType = $attributes["mailingType"];
                        $record->markedAsSpamAt = $attributes["markedAsSpamAt"];
                        $record->messageId = $attributes["messageId"];
                        $record->notifyThreadCondition = $attributes["notifyThreadCondition"];
                        $record->notifyThreadScheduledAt = $attributes["notifyThreadScheduledAt"];
                        $record->notifyThreadStatus = $attributes["notifyThreadStatus"];
                        $record->openCount = $attributes["openCount"];
                        $record->openedAt = $attributes["openedAt"];
                        $record->overrideSafetySettings = $attributes["overrideSafetySettings"];
                        $record->repliedAt = $attributes["repliedAt"];
                        $record->retryAt = $attributes["retryAt"];
                        $record->retryCount = $attributes["retryCount"];
                        $record->retryInterval = $attributes["retryInterval"];
                        $record->scheduledAt = $attributes["scheduledAt"];
                        $record->state = $attributes["state"];
                        $record->stateChangedAt = $attributes["stateChangedAt"];
                        $record->subject = $attributes["subject"];
                        $record->trackLinks = $attributes["trackLinks"];
                        $record->trackOpens = $attributes["trackOpens"];
                        $record->unsubscribedAt = $attributes["unsubscribedAt"];
                        $record->updatedAt = $attributes["updatedAt"];
                        $record->save();
                        $u++;
                    endif;
                    $id = $value->id;            
                }
            }
        }
        //return view('csynMailings', compact('c', 'u', 'i', 'id'));
    }

    public function getLast12HrsMailing(){
        ini_set('max_execution_time', 3600);
        // outreach starts
        $accessToken = $this->outreachsession();
        $now = strtotime("now");
        $nowEnd = strtotime("now") - 12*3600;
        $start = date("Y-m-d", $now).'T'.date("H:i:s", $now).'.000Z';
        $end = date("Y-m-d", $nowEnd).'T'.date("H:i:s", $nowEnd).'.000Z';
        $queryStrings = 'sort=id';
        //$queryStrings .= "&filter[updatedAt]=$end..$start";
        $queryStrings .= "&filter[updatedAt]=$end..$start";
        $curl = curl_init();
        $query = "https://api.outreach.io/api/v2/mailings?$queryStrings&count=true&page[limit]=1000";
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
        if(count($results['data']) > 0){
            foreach($results['data'] as $key => $value){
                $attributes = get_object_vars($value->attributes);
                $relationships = get_object_vars($value->relationships);
                $prospect = get_object_vars($relationships["prospect"]);
                $prospectId = null;
                if(is_object($prospect["data"])):
                    $prospect = get_object_vars($prospect["data"]);
                    $prospectId = $prospect["id"];
                endif;
                $count = OutreachMailings::where('mailing_id', '=', $value->id)->count();
                if($count == 0):
                    //echo '<pre>created'; print_r($value->id); echo '</pre>';
                    OutreachMailings::create([
                        "mailing_id" => $value->id,
                        "contact_id" => $prospectId,
                        "bouncedAt" => $attributes["bouncedAt"],
                        "clickCount" => $attributes["clickCount"],
                        "clickedAt" => $attributes["clickedAt"],
                        "createdAt" => $attributes["createdAt"],
                        "deliveredAt" => $attributes["deliveredAt"],
                        "errorBacktrace" => $attributes["errorBacktrace"],
                        "errorReason" => $attributes["errorReason"],
                        "followUpTaskScheduledAt" => $attributes["followUpTaskScheduledAt"],
                        "followUpTaskType" => $attributes["followUpTaskType"],
                        "mailboxAddress" => $attributes["mailboxAddress"],
                        "mailingType" => $attributes["mailingType"],
                        "markedAsSpamAt" => $attributes["markedAsSpamAt"],
                        "messageId" => $attributes["messageId"],
                        "notifyThreadCondition" => $attributes["notifyThreadCondition"],
                        "notifyThreadScheduledAt" => $attributes["notifyThreadScheduledAt"],
                        "notifyThreadStatus" => $attributes["notifyThreadStatus"],
                        "openCount" => $attributes["openCount"],
                        "openedAt" => $attributes["openedAt"],
                        "overrideSafetySettings" => $attributes["overrideSafetySettings"],
                        "repliedAt" => $attributes["repliedAt"],
                        "retryAt" => $attributes["retryAt"],
                        "retryCount" => $attributes["retryCount"],
                        "retryInterval" => $attributes["retryInterval"],
                        "scheduledAt" => $attributes["scheduledAt"],
                        "state" => $attributes["state"],
                        "stateChangedAt" => $attributes["stateChangedAt"],
                        "subject" => $attributes["subject"],
                        "trackLinks" => $attributes["trackLinks"],
                        "trackOpens" => $attributes["trackOpens"],
                        "unsubscribedAt" => $attributes["unsubscribedAt"],
                        "updatedAt" => $attributes["updatedAt"],
                    ]); 
                    //$c++; 
                else:
                    //echo '<pre>updated'; print_r($value->id); echo '</pre>';
                    $record = OutreachMailings::where('mailing_id', '=', $value->id)->first();
                    $record->contact_id = $prospectId;
                    $record->bouncedAt = $attributes["bouncedAt"];
                    $record->clickCount = $attributes["clickCount"];
                    $record->clickedAt = $attributes["clickedAt"];
                    $record->createdAt = $attributes["createdAt"];
                    $record->deliveredAt = $attributes["deliveredAt"];
                    $record->errorBacktrace = $attributes["errorBacktrace"];
                    $record->errorReason = $attributes["errorReason"];
                    $record->followUpTaskScheduledAt = $attributes["followUpTaskScheduledAt"];
                    $record->followUpTaskType = $attributes["followUpTaskType"];
                    $record->mailboxAddress = $attributes["mailboxAddress"];
                    $record->mailingType = $attributes["mailingType"];
                    $record->markedAsSpamAt = $attributes["markedAsSpamAt"];
                    $record->messageId = $attributes["messageId"];
                    $record->notifyThreadCondition = $attributes["notifyThreadCondition"];
                    $record->notifyThreadScheduledAt = $attributes["notifyThreadScheduledAt"];
                    $record->notifyThreadStatus = $attributes["notifyThreadStatus"];
                    $record->openCount = $attributes["openCount"];
                    $record->openedAt = $attributes["openedAt"];
                    $record->overrideSafetySettings = $attributes["overrideSafetySettings"];
                    $record->repliedAt = $attributes["repliedAt"];
                    $record->retryAt = $attributes["retryAt"];
                    $record->retryCount = $attributes["retryCount"];
                    $record->retryInterval = $attributes["retryInterval"];
                    $record->scheduledAt = $attributes["scheduledAt"];
                    $record->state = $attributes["state"];
                    $record->stateChangedAt = $attributes["stateChangedAt"];
                    $record->subject = $attributes["subject"];
                    $record->trackLinks = $attributes["trackLinks"];
                    $record->trackOpens = $attributes["trackOpens"];
                    $record->unsubscribedAt = $attributes["unsubscribedAt"];
                    $record->updatedAt = $attributes["updatedAt"];
                    $record->save();
                    //$u++;
                endif;
                $id = $value->id;            
            }
        }
        $queryStrings .= "&filter[createdAt]=$end..$start";
        $curl = curl_init();
        $query = "https://api.outreach.io/api/v2/mailings?$queryStrings&count=true&page[limit]=1000";
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
        if(count($results['data']) > 0){
            foreach($results['data'] as $key => $value){
                $attributes = get_object_vars($value->attributes);
                $relationships = get_object_vars($value->relationships);
                $prospect = get_object_vars($relationships["prospect"]);
                $prospectId = null;
                if(is_object($prospect["data"])):
                    $prospect = get_object_vars($prospect["data"]);
                    $prospectId = $prospect["id"];
                endif;
                $count = OutreachMailings::where('mailing_id', '=', $value->id)->count();
                if($count == 0):
                    //echo '<pre>created'; print_r($value->id); echo '</pre>';
                    OutreachMailings::create([
                        "mailing_id" => $value->id,
                        "contact_id" => $prospectId,
                        "bouncedAt" => $attributes["bouncedAt"],
                        "clickCount" => $attributes["clickCount"],
                        "clickedAt" => $attributes["clickedAt"],
                        "createdAt" => $attributes["createdAt"],
                        "deliveredAt" => $attributes["deliveredAt"],
                        "errorBacktrace" => $attributes["errorBacktrace"],
                        "errorReason" => $attributes["errorReason"],
                        "followUpTaskScheduledAt" => $attributes["followUpTaskScheduledAt"],
                        "followUpTaskType" => $attributes["followUpTaskType"],
                        "mailboxAddress" => $attributes["mailboxAddress"],
                        "mailingType" => $attributes["mailingType"],
                        "markedAsSpamAt" => $attributes["markedAsSpamAt"],
                        "messageId" => $attributes["messageId"],
                        "notifyThreadCondition" => $attributes["notifyThreadCondition"],
                        "notifyThreadScheduledAt" => $attributes["notifyThreadScheduledAt"],
                        "notifyThreadStatus" => $attributes["notifyThreadStatus"],
                        "openCount" => $attributes["openCount"],
                        "openedAt" => $attributes["openedAt"],
                        "overrideSafetySettings" => $attributes["overrideSafetySettings"],
                        "repliedAt" => $attributes["repliedAt"],
                        "retryAt" => $attributes["retryAt"],
                        "retryCount" => $attributes["retryCount"],
                        "retryInterval" => $attributes["retryInterval"],
                        "scheduledAt" => $attributes["scheduledAt"],
                        "state" => $attributes["state"],
                        "stateChangedAt" => $attributes["stateChangedAt"],
                        "subject" => $attributes["subject"],
                        "trackLinks" => $attributes["trackLinks"],
                        "trackOpens" => $attributes["trackOpens"],
                        "unsubscribedAt" => $attributes["unsubscribedAt"],
                        "updatedAt" => $attributes["updatedAt"],
                    ]); 
                    //$c++; 
                else:
                    //echo '<pre>updated'; print_r($value->id); echo '</pre>';
                    $record = OutreachMailings::where('mailing_id', '=', $value->id)->first();
                    $record->contact_id = $prospectId;
                    $record->bouncedAt = $attributes["bouncedAt"];
                    $record->clickCount = $attributes["clickCount"];
                    $record->clickedAt = $attributes["clickedAt"];
                    $record->createdAt = $attributes["createdAt"];
                    $record->deliveredAt = $attributes["deliveredAt"];
                    $record->errorBacktrace = $attributes["errorBacktrace"];
                    $record->errorReason = $attributes["errorReason"];
                    $record->followUpTaskScheduledAt = $attributes["followUpTaskScheduledAt"];
                    $record->followUpTaskType = $attributes["followUpTaskType"];
                    $record->mailboxAddress = $attributes["mailboxAddress"];
                    $record->mailingType = $attributes["mailingType"];
                    $record->markedAsSpamAt = $attributes["markedAsSpamAt"];
                    $record->messageId = $attributes["messageId"];
                    $record->notifyThreadCondition = $attributes["notifyThreadCondition"];
                    $record->notifyThreadScheduledAt = $attributes["notifyThreadScheduledAt"];
                    $record->notifyThreadStatus = $attributes["notifyThreadStatus"];
                    $record->openCount = $attributes["openCount"];
                    $record->openedAt = $attributes["openedAt"];
                    $record->overrideSafetySettings = $attributes["overrideSafetySettings"];
                    $record->repliedAt = $attributes["repliedAt"];
                    $record->retryAt = $attributes["retryAt"];
                    $record->retryCount = $attributes["retryCount"];
                    $record->retryInterval = $attributes["retryInterval"];
                    $record->scheduledAt = $attributes["scheduledAt"];
                    $record->state = $attributes["state"];
                    $record->stateChangedAt = $attributes["stateChangedAt"];
                    $record->subject = $attributes["subject"];
                    $record->trackLinks = $attributes["trackLinks"];
                    $record->trackOpens = $attributes["trackOpens"];
                    $record->unsubscribedAt = $attributes["unsubscribedAt"];
                    $record->updatedAt = $attributes["updatedAt"];
                    $record->save();
                    //$u++;
                endif;
                $id = $value->id;            
            }
        }
        
    }

    public function getFive9CallLog($i){
        ini_set('max_execution_time', 3600);
        if($i < -60){
            echo 'all done'; die;
        }
        //for($i = 0; $i < $day; $i--){
            $date = date("Y-m-d", strtotime("today")+$i*3600*24);
            echo '<pre>'; echo $date; echo '</pre>';
            $startFullDate = $date.'T00:00:00';
            $endFulllDate = $date.'T23:59:59';
            $result = $this->callReportOne($startFullDate, $endFulllDate);
            if($result["id"]){
                if($this->getReportResponse($result["id"])){
                    $this->callReportResultOne($result["id"], $date);
                }
            }
        //}
        return view('csyncFivenineCallLog', compact('i'));
    }
    public function getFive9Last12hrsCallLog(){
        $date = date("Y-m-d", strtotime("now"));
        $hrs = 12;
        $startFullDate = date("Y-m-d", strtotime("now")-$hrs*3600).'T'.date("H:i:s", strtotime("now")-$hrs*3600);
        $endFulllDate = date("Y-m-d", strtotime("now")).'T'.date("H:i:s", strtotime("now"));
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
        //dd($postFields);
        $curl = curl_init();
        $setting1 = Settings::where('id', '=', 37)->first();
        $setting2 = Settings::where('id', '=', 38)->first();
        $username = $setting1->value;
        $password = $setting2->value;
        
        $code = base64_encode($username.':'.$password);
        //dd($code);
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
        $listRecords = [];
        
        $header = $return["envBody"]["ns2getReportResultResponse"]["return"]["header"]["values"]["data"];
        $headerArray = [];
        foreach($header as $key => $hvalue):
            $headerArray[$key] = str_replace('/', '', implode("_", explode(" ", strtolower($hvalue))));
        endforeach;

        if(isset($return["envBody"]["ns2getReportResultResponse"]["return"]["records"])):
            $data = $return["envBody"]["ns2getReportResultResponse"]["return"]["records"];
            $counter = 0;
            $dial_attempts = [];
            foreach($data as $key => $value){
                $records = array_combine($headerArray,$value["values"]["data"]); //echo '<pre>'; print_r($records); echo '</pre>'; die;
                if( $records["dnis"] != '-'){
                    if(is_array($records["campaign"])){
                        $campaign = implode(',', $records["campaign"]);
                    }else{
                        $campaign = $records["campaign"];
                    }
                    if(is_array($records["call_type"])){
                        $call_type = implode(',', $records["call_type"]);
                    }else{
                        $call_type = $records["call_type"];
                    }
                    if(is_array($records["agent_name"])){
                        $agent_name = implode(',', $records["agent_name"]);
                    }else{
                        $agent_name = $records["agent_name"];
                    }
                    if(is_array($records["disposition"])){
                        $disposition = implode(',', $records["disposition"]);
                    }else{
                        $disposition = $records["disposition"];
                    }
                    if(is_array($records["customer_name"])){
                        $customer_name = implode(',', $records["customer_name"]);
                    }else{
                        $customer_name = $records["customer_name"];
                    }
                    if(is_array($records["record_id"])){
                        $record_id = implode(',', $records["record_id"]);
                    }else{
                        $record_id = $records["record_id"];
                    }
                    if(is_array($records["dial_attempts"])){
                        $dial_attempts = implode(',', $records["dial_attempts"]);
                    }else{
                        $dial_attempts = $records["dial_attempts"];
                    }
                    if(is_array($records["list_name"])){
                        $list_name = implode(',', $records["list_name"]);
                    }else{
                        $list_name = $records["list_name"];
                    }                
                    if(is_array($records["talk_time"])){
                        $talk_time = implode(',', $records["talk_time"]);
                    }else{
                        $talk_time = $records["talk_time"];
                    }
                    if(is_array($records["consult_time"])){
                        $consult_time = implode(',', $records["consult_time"]);
                    }else{
                        $consult_time = $records["consult_time"];
                    }
                    if(is_array($records["hold_time"])){
                        $hold_time = implode(',', $records["hold_time"]);
                    }else{
                        $hold_time = $records["hold_time"];
                    }
                    if(is_array($records["park_time"])){
                        $park_time = implode(',', $records["park_time"]);
                    }else{
                        $park_time = $records["park_time"];
                    }
                    if(is_array($records["call_time"])){
                        $call_time = implode(',', $records["call_time"]);
                    }else{
                        $call_time = $records["call_time"];
                    }
                    if(is_array($records["cost"])){
                        $cost = implode(',', $records["cost"]);
                    }else{
                        $cost = $records["cost"];
                    }
                    if(is_array($records["handle_time"])){
                        $handle_time = implode(',', $records["handle_time"]);
                    }else{
                        $handle_time = $records["handle_time"];
                    }
                    if(is_array($records["manual_time"])){
                        $manual_time = implode(',', $records["manual_time"]);
                    }else{
                        $manual_time = $records["manual_time"];
                    }
                    if(is_array($records["ring_time"])){
                        $ring_time = implode(',', $records["ring_time"]);
                    }else{
                        $ring_time = $records["ring_time"];
                    }
                    if(is_array($records["talk_time_less_hold_and_park"])){
                        $talk_time_less_hold_and_park = implode(',', $records["talk_time_less_hold_and_park"]);
                    }else{
                        $talk_time_less_hold_and_park = $records["talk_time_less_hold_and_park"];
                    }
                    //
                    $dnis = $records["dnis"];
                    $contact = Contacts::where('record_id', '=', $record_id)->count();
                    if($contact > 0){
                        $contact = Contacts::where('record_id', '=', $record_id)->first();
                        $save = 0;
                        $m_dial_attempts = 0;
                        $w_dial_attempts  = 0;
                        if($contact->workPhones):// for workPhones
                            $workPhones = $contact->workPhones;
                            if(strpos(",", $workPhones) > 0){// containg multiple number
                                $numbers = explode(",", $workPhones);
                                foreach($numbers as $mobile){
                                    $mobile = str_replace(' ', '', $mobile);
                                    $mobile = str_replace('-', '', $mobile);
                                    $mobile = preg_replace('/[^A-Za-z0-9\-]/', '', $mobile);
                                    $mobile = intval($mobile);
                                    $mobile = substr($mobile, -10);
                                    if($mobile == $dnis){
                                        if($dial_attempts == '-'){
                                            $w_dial_attempts = 0;
                                        }else{
                                            $w_dial_attempts = $dial_attempts;
                                        }
                                        $save = 1;
                                        break;
                                    }
                                }
                            }else{
                                //containg single number
                                $mobile = $contact->workPhones;
                                $mobile = str_replace(' ', '', $mobile);
                                $mobile = str_replace('-', '', $mobile);
                                $mobile = preg_replace('/[^A-Za-z0-9\-]/', '', $mobile);
                                $mobile = intval($mobile);
                                $mobile = substr($mobile, -10);
                                if($mobile == $dnis){
                                    if($dial_attempts == '-'){
                                        $w_dial_attempts = 0;
                                    }else{
                                        $w_dial_attempts = $dial_attempts;
                                    }
                                    $save = 1;
                                }
                            }
                        endif;
                        if($save == 0):
                            if($dial_attempts == '-'){
                                $m_dial_attempts = 0;
                            }else{
                                $m_dial_attempts = $dial_attempts;
                            }
                            $save = 1;
                        endif;                   
                        if($talk_time == '00:00:00'){
                            $call_received = 0;
                        }else{
                            $call_received = 1;
                        }
                        if($w_dial_attempts == ''){
                            $w_dial_attempts = 0;
                        }
                        if($m_dial_attempts == ''){
                            $m_dial_attempts = 0;
                        }
                        $t = strtotime($records["timestamp"]);
                        FivenineCallLogs::create([
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
                            "m_dial_attempts" => $m_dial_attempts,
                            "w_dial_attempts" => $w_dial_attempts,
                            "n_timestamp" => $t,
                            "consult_time" => $consult_time,
                            "hold_time" => $hold_time,
                            "park_time" => $park_time,
                            "call_time" => $call_time,
                            "cost" => $cost,
                            "handle_time" => $handle_time,
                            "manual_time" => $manual_time,
                            "ring_time" => $ring_time,
                            "talk_time_less_hold_and_park" => $talk_time_less_hold_and_park,
                        ]);
                    }
                }
            }
            
        else:
            return ["results" => false];
        endif;
    }
    
    public function getOutreachCustomData(){
        
    }
}
