<?php

namespace App\Http\Controllers;

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


class MyTestCroneController extends Controller
{
    
    public function __construct()
    {
       
    }
    public function __outreachsession()
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
    public function updateOutreachMailings($page){
        //http://127.0.0.1:8888/my-test-update-outreach-mailings/175
        ini_set('max_execution_time', 3600);
        
        if($page > 1350){//66684
            echo 'all doen'; die;
        }
        $total = 0;
        $id = 0;
        $c = 0;
        $u = 0;
        // outreach starts
        $accessToken = $this->__outreachsession();
        $curl = curl_init();
        $start = $page*50;
        $end = ($page+1)*50-1;
        $query = "https://api.outreach.io/api/v2/mailings?count=true&filter[id]=$start..$end";
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
        if(isset($results['data'])  && (count($results['data']) > 0)){
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
                        ++$c;
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
                        ++$u;
                endif;
                $id = $value->id;
            }
        }
        $total = $c + $u;
        return view('my-test-update-outreach-mailings', compact('total', 'page', 'id', 'c', 'u'));
    }
    public function makeTempRecordsForProspects($page){
        ini_set("max_execution_time", 3600);
        $perPage = 500;
        $total = 0;
        $total = 0;
        $id = 0;
        if($page > 100){
            echo 'all doen'; die;
        } 
        $records = Contacts::whereBetween('record_id', [$perPage*$page, $perPage*($page+1)-1])->select('record_id')->get();
        
        foreach($records as $value):
            $record = Contacts::where('record_id', $value->record_id)->first();
            $record->mnumber = $this->__NumberFormater1($record->mobilePhones);
            $record->hnumber = $this->__NumberFormater1($record->homePhones);
            $record->wnumber = $this->__NumberFormater1($record->workPhones);
            $record->save();
            ++$total;
            $id = $record->id;
        endforeach;
        return view('make-temp-records-for-prospects', compact('total', 'page', 'id'));
    }
    private function __NumberFormater1($var = null)
    {
        $string = str_replace(' ', '', $var); // Replaces all spaces.
        $string = str_replace('-', '', $string);
        $string = str_replace('.', '', $string);
        $string = str_replace(',', '', $string);
        $string = str_replace('(', '', $string);
        $string = str_replace(')', '', $string);
        $string = preg_replace('/[^0-9\-]/', '', $string); // Removes special chars.        
        return $string;
    }
}