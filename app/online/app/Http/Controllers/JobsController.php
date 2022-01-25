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

class JobsController extends Controller

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
    public function jobCreated($record_id, $field, $value){
        // DB::table('prospect_jobs')->insert([
        //     'record_id' => $record_id,
        //     'field' => $field,
        //     'value' => $value
        // ]);
        if($field == 'outreach_tag') {
            $value = explode(',', $value);
        }
        $accessTokenProspects = $this->__outreachsessionProspects();
            $curl = curl_init();
            $postfields = [
                'data' => [
                    'type' => 'prospect',
                    'id' => intval($record_id),
                    'attributes' => [
                        $field => $value
                    ]
                ]
            ];
            $postfield = json_encode($postfields); 
            // echo "<pre>"; print_r($postfield); echo "</pre>";
            $query = "https://api.outreach.io/api/v2/prospects/$record_id";
            // echo "<pre>"; print_r($query); echo "</pre>";
            curl_setopt_array($curl, array(
            CURLOPT_URL => $query,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'PATCH',
            CURLOPT_POSTFIELDS => $postfield,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $accessTokenProspects",
                'Content-Type: application/vnd.api+json'
            ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            //  echo "<pre>"; print_r($response); echo "</pre>";
            return true;
    }

}