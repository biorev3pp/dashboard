<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Settings;
use App\Models\Templates;
use App\Models\Contacts;
use App\Models\Newcontacts;
use App\Models\Stages;
use App\Models\SearchCriteria;
use App\Models\Views;
use App\Models\AccountViews;
use App\Models\OutreachAccounts;
use App\Models\SearchCriteriaAccounts;
use App\Models\ContactCustoms;
use App\Models\FivenineCallLogs;

use DateTime;
use DateTimeZone;

class OutreachController extends Controller
{
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

    public function getOutreachStages()
    {
        $results = $this->__stages();
        $res = get_object_vars($results);
        $stageDetails = [];
        foreach($res["data"] as $key => $value){
            $data = get_object_vars($value);
            $attributes = get_object_vars($data["attributes"]);  
            $stageDetails[$key]['stage'] = $attributes["name"];
            $stageDetails[$key]["count"] = Contacts::where('stage', '=', $attributes["name"])->count();
        }
        return ["stageDetails" => $stageDetails];
    }

    public function outreachAllprospects(Request $request)
    {
        $this->outreachsession();
        //dd($request->all());
        $recordPerPage = 20;
        $page = $request->input('page');
        if($page == 1):
            $offset = 0;
        else:
            $offset = ($page-1)*intval($recordPerPage);        
        endif;
        $search = $request->input("textSearch");
        $sortby = $request->input('sortby');
        $sort = $request->input("sort");
        if($search):
            $q = Contacts::with('stageData')
            ->orWhere(function($q) use ($search){
                $q->orWhere('first_name', 'like', '%'.$search.'%')->orWhere('last_name', 'like', '%'.$search.'%')->orWhere('emails', 'like', '%'.$search.'%')->orWhere('company', 'like', '%'.$search.'%');
            });
        else:
            $q = Contacts::with('stageData');
        endif;
        if(count($request->input('filterConditionsArray')) > 0):
            foreach($request->input('filterConditionsArray') as $key => $value):
                if( ($value['type'] == 'textbox') || ($value['type'] == 'dropdown') ):
                    if($value['formula'] == 'is'):
                        if(strpos($value["textCondition"],  ",")):
                            $ids = explode(",", $value["textCondition"]);
                            foreach($ids as $IdValue):
                                $q->orWhere($value["condition"], 'like', $IdValue);
                            endforeach;
                        else:
                            $q->where($value["condition"], 'like', $value["textCondition"]);
                        endif;
                    elseif($value['formula'] == 'is not'):
                        if(strpos($value["textCondition"],  ",")):
                            $ids = explode(",", $value["textCondition"]);
                            foreach($ids as $IdValue):
                                $q->orWhere($value["condition"], 'not like', $IdValue);
                            endforeach;
                        else:
                            $q->where($value["condition"], 'not like', $value["textCondition"]);
                        endif;
                    elseif($value['formula'] == 'starts with'):
                        if(strpos($value["textCondition"],  ",")):
                            $ids = explode(",", $value["textCondition"]);
                            foreach($ids as $IdValue):
                                $q->orWhere($value["condition"], 'like', $IdValue);
                            endforeach;
                        else:
                            $q->where($value["condition"], 'like', $value["textCondition"].'%');
                        endif;
                    elseif($value['formula'] == 'is empty'):                        
                        $q->whereNull($value["condition"]);
                    elseif($value['formula'] == 'is not empty'):
                        $q->whereNotNull($value["condition"]);
                    elseif($value['formula'] == 'contains'):
                        $q->where($value["condition"], 'like', '%'.$value["textCondition"].'%');
                    elseif($value['formula'] == 'ends with'):
                        $q->where($value["condition"], '=', '%'.$value["textCondition"]);
                    endif;
                elseif($value['type'] == 'calendar'):                    
                    $date = explode('--', $value["textCondition"]);// dd($date);
                    if($date[0] == $date[1]):
                        $s = substr($date[0], 0, 10);
                        $q->whereDate($value["condition"], $s);
                    else:
                        $s = substr($date[0], 0, 10);
                        $e = substr($date[1], 0, 10);
                        $q->whereBetween($value["condition"], [$s, $e]);
                    endif;
                endif;
            endforeach;
        endif;
        // if($request->input('recordPerPage') == 'all'):
        // else:
        // endif;
        $recordPerPage  = $request->input('recordPerPage');
        $totalRecords = $q->count();
        if($request->input("sortBy") == 'asc'):
            $records = $q->orderBy( $request->input('sortType'))->paginate($recordPerPage); 
        else:
            $records = $q->orderByDesc($request->input('sortType'))->paginate($recordPerPage);    
        endif;
    
        $paginate = ['page' => $page, 'start' => $offset+1, 'end' => $offset+$recordPerPage, 'count' => $recordPerPage, 'total' => $totalRecords, 'pager' =>ceil($totalRecords/$recordPerPage)];
        return ['results' => $records, 'page' => $paginate, 'totalRecords' => $totalRecords];
    }
    public function getOutreachBlankStages(){
        $results = $this->__stages();
        $res = get_object_vars($results);
        $stageDetails = [];
        foreach($res["data"] as $key => $value){
            $data = get_object_vars($value);
            $attributes = get_object_vars($data["attributes"]);  
            $stageDetails[$key]['stage'] = $attributes["name"];
            $stageDetails[$key]["count"] = 0;
        }
        return ['stageDetails' => $stageDetails];
    }

    function __stages(){
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
        $results = json_decode($response);
        curl_close($curl);
        return $results;
    }

    public function outreachprospectDetailsActivities($id){
        $curl = curl_init();
        $accessToken = $this->outreachsession();
        //https://api.outreach.io/api/v2/events?filter[prospect][id]=14250&include=mailing&sort=-createdAt
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.outreach.io/api/v2/events?filter[prospect][id]=$id&include=mailing&sort=-createdAt",
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
        $results = json_decode($response);
        $data = get_object_vars($results);        
        return ['details' => $results];
    }

    public function outreachprospectDetailsCalls($id)
    {
        
        $records = FivenineCallLogs::where('record_id', '=', $id)->get();
        return ['details' => $records];
    }

    public function outreachprospectDetailsEmails($id)
    {
        $curl = curl_init();
        $accessToken = $this->outreachsession();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.outreach.io/api/v2/mailings?sort=-createdAt&filter[prospect][id]=$id&include=prospect&fields[prospect]=firstName,lastName",
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
        $results = json_decode($response);
        $data = get_object_vars($results);        
        return ['details' => $results];
    }

    public function outreachprospectDetailsEvents($id)
    {
        $curl = curl_init();
        $accessToken = $this->outreachsession();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.outreach.io/api/v2/events?filter[prospect][id]=$id",
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
        $results = json_decode($response);
        $data = get_object_vars($results);        
        return ['details' => $results];
    }

    public function outreachAllprospectToExport(Request $request)
    {
        $recordPerPage = 20;
        $page = $request->input('page');
        if($page == 1):
            $offset = 0;
        else:
            $offset = ($page-1)*intval($recordPerPage);        
        endif;
        $search = $request->input("textSearch");
        $startDate = $request->input("startDate");        
        $endDate = $request->input("endDate");
        $stage = $request->input("stage");
        $sortby = $request->input('sortby');
        $sort = $request->input("sort");
        if($search):
            $q = Contacts::with('stageData')
            ->orWhere(function($q) use ($search){
                $q->orWhere('first_name', 'like', '%'.$search.'%')->orWhere('last_name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%')->orWhere('company', 'like', '%'.$search.'%');
            });
        else:
            $q = Contacts::with('stageData');
        endif;
        $q = $q->when($startDate, function($q) use ($startDate, $endDate){
                $sDate = explode("T", $startDate);
                $s = $sDate[0];
                $eDate = explode("T", $endDate);
                $e = $eDate[0];
                if($s == $e){
                    $q->whereDate('last_outreach_engage', $s);
                }else{
                    $q->whereBetween('last_outreach_engage', [$s, $e]);
                }
            });
        if($stage != 'all'){
            $q = $q->where('stage', $stage);
        }
            $records = $q->orderBy($request->input("sort"), $request->input('sortby'))->get();    
            $totalRecords = $q->count();
        
    
        return ['results' => $records, 'totalRecords' => $totalRecords];   
    }

    public function getAllFilter(){        
        return ['items' => SearchCriteria::orderBy('filter')->where('udashboard', '=', 1)->get()];
    }
    public function getAllFilterDataset(){
        return ['items' => SearchCriteria::orderBy('filter')->where('datasets', '=', 1)->get()];
    }
    public function getAllFilterDatasetNew(){
        return ['items' => SearchCriteria::select('filter_key as key', 'filter as value', 'filter_option as options', 'filter_type as type', 'api')->orderBy('filter')->where('datasets', '=', 1)->get()];
    }

    public function getAllFilterForAccounts(){
        return ['items' => SearchCriteriaAccounts::orderBy('type')->orderBy('filter')->get()];

    }
    public function AllStages(){
        return ['results' => Stages::orderBy('name')->get()];
    }
    public function AllAgents(){
        return ['results' => DB::table('agents')->get()];
    }
    public function AllLastDispo(){
        $records = DB::table('contacts')->select('last_dispo')->distinct()->get();
        $results = [];
        $counter = 0;
        foreach($records as $value){
            $results[$counter++] = ["name" => $value->last_dispo, "oid" => $value->last_dispo];
        }
        return ['results' => $results];
    }
    public function allViews(){
        return ['results' => Views::where('admin_id', '=', \Auth::user()->id)->orderBy('view_name')->get() ];
    }

    public function allViewsAccounts(){
        return ['results' => AccountViews::where('admin_id', '=', \Auth::user()->id)->orderBy('view_name')->get() ];
    }

    public function saveView(Request $request){
        //dd($request->all());
        $result = Views::create([
            'admin_id' => \Auth::user()->id,
            'view_data' => json_encode($request->all()),
            'view_name' => $request->input("viewName")
        ]);
        return ['results' => $result];

    }

    public function saveViewAccounts(Request $request){
        //dd($request->all());
        $result = AccountViews::create([
            'admin_id' => \Auth::user()->id,
            'view_data' => json_encode($request->all()),
            'view_name' => $request->input("viewName")
        ]);
        return ['results' => $result];

    }

    public function modifidata() {
        $stages = Stages::get();
        foreach ($stages as $key => $value) {
            Contacts::where('stage', '=', $value->stage)->update(['stage' => $value->oid]);
        }
        Contacts::whereNull('stage')->update(['stage' => 0]);
        echo 'all done'; die;
    }

    public function automation(Request $request){
        
        $aid = $request->input('id');
        
        $resultsOldContact = [];
        $curl = curl_init();
        $query = 'https://biorev33069.api-us1.com/api/3/contacts/'.$aid;
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
                'Api-Token: 5ab82418363737bc55f771e7529c7c1f42ff0d41c623cfe874b2ec0a4455995d34493f39',
                'Cookie: __cfduid=d97b6427615f0d72feec531918ba097411618376679; PHPSESSID=56d9cad6a1d8133f1acd1d92a006cedd; cmp90303434=205ff7b29cb3bbd970014602eb9a70f0; em_acp_globalauth_cookie=f817b856-b9df-4998-9b94-ac98e20f3642'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $resultsOldContact = json_decode($response);
        
        $curl = curl_init();
        $resultsNewContact = [];
        $query = 'https://biorev33069.api-us1.com/admin/api.php?api_action=contact_view&api_key=5ab82418363737bc55f771e7529c7c1f42ff0d41c623cfe874b2ec0a4455995d34493f39&id='.$aid.'&api_output=json';
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
                'Api-Token: 5ab82418363737bc55f771e7529c7c1f42ff0d41c623cfe874b2ec0a4455995d34493f39',
                'Cookie: __cfduid=d97b6427615f0d72feec531918ba097411618376679; PHPSESSID=56d9cad6a1d8133f1acd1d92a006cedd; cmp90303434=205ff7b29cb3bbd970014602eb9a70f0; em_acp_globalauth_cookie=f817b856-b9df-4998-9b94-ac98e20f3642'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $resultsNewContact = json_decode($response);

        $resultsNewContactA = get_object_vars($resultsNewContact);
        $campaign_history = $resultsNewContactA["campaign_history"];
        $campaign = [];
        $resultsNewcampaign_report_totals= [];
        $resultsNewcampaign_report_open_totals= [];
        $resultsNewcampaign_report_open_list= [];
        $resultsNewcampaign_report_link_totals= [];
        $resultsNewcampaign_report_link_list= [];
        $resultsNewcampaign_report_forward_totals= [];
        $resultsNewcampaign_report_forward_list= [];
        $resultsNewcampaign_report_bounce_totals= [];
        $resultsNewcampaign_report_bounce_list= [];
        $resultsNewcampaign_list = [];
        foreach($campaign_history as $key => $value){
            $value1 = get_object_vars($value);
            $campaign[$key]['id'] = $value1['id'];
            $campaign[$key]['listid'] = $value1['listid'];
            $campaign[$key]['listname'] = $value1['listname'];
            $campaign[$key]['campaignname'] = $value1['campaignname'];
            $campaign[$key]['sdate'] = $value1['sdate'];
            $campaign[$key]['email'] = $value1['email'];
            $curl = curl_init();
            $aid = $request->input('id');

            //campaign_report_totals
            // $curl = curl_init();
            // $query = 'https://biorev33069.api-us1.com/admin/api.php?api_action=campaign_report_totals&api_key=5ab82418363737bc55f771e7529c7c1f42ff0d41c623cfe874b2ec0a4455995d34493f39&campaignid='.$value1['id'].'&api_output=json';
            // curl_setopt_array($curl, array(
            //     CURLOPT_URL => $query,
            //     CURLOPT_RETURNTRANSFER => true,
            //     CURLOPT_ENCODING => '',
            //     CURLOPT_MAXREDIRS => 10,
            //     CURLOPT_TIMEOUT => 0,
            //     CURLOPT_FOLLOWLOCATION => true,
            //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            //     CURLOPT_CUSTOMREQUEST => 'GET',
            //     CURLOPT_HTTPHEADER => array(
            //         'Api-Token: 5ab82418363737bc55f771e7529c7c1f42ff0d41c623cfe874b2ec0a4455995d34493f39',
            //         'Cookie: __cfduid=d97b6427615f0d72feec531918ba097411618376679; PHPSESSID=56d9cad6a1d8133f1acd1d92a006cedd; cmp90303434=205ff7b29cb3bbd970014602eb9a70f0; em_acp_globalauth_cookie=f817b856-b9df-4998-9b94-ac98e20f3642'
            //     ),
            // ));
            // $response = curl_exec($curl);
            // curl_close($curl);
            // $resultsNewcampaign_report_totals[$value1['id']] = json_decode($response);

            //campaign_report_bounce_list
            
            // $curl = curl_init();
            // $query = 'https://biorev33069.api-us1.com/admin/api.php?api_action=campaign_report_bounce_list&api_key=5ab82418363737bc55f771e7529c7c1f42ff0d41c623cfe874b2ec0a4455995d34493f39&campaignid='.$value1['id'].'&api_output=json';
            // curl_setopt_array($curl, array(
            //     CURLOPT_URL => $query,
            //     CURLOPT_RETURNTRANSFER => true,
            //     CURLOPT_ENCODING => '',
            //     CURLOPT_MAXREDIRS => 10,
            //     CURLOPT_TIMEOUT => 0,
            //     CURLOPT_FOLLOWLOCATION => true,
            //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            //     CURLOPT_CUSTOMREQUEST => 'GET',
            //     CURLOPT_HTTPHEADER => array(
            //         'Api-Token: 5ab82418363737bc55f771e7529c7c1f42ff0d41c623cfe874b2ec0a4455995d34493f39',
            //         'Cookie: __cfduid=d97b6427615f0d72feec531918ba097411618376679; PHPSESSID=56d9cad6a1d8133f1acd1d92a006cedd; cmp90303434=205ff7b29cb3bbd970014602eb9a70f0; em_acp_globalauth_cookie=f817b856-b9df-4998-9b94-ac98e20f3642'
            //     ),
            // ));
            // $response = curl_exec($curl);
            // curl_close($curl);
            // $resultsNewcampaign_report_bounce_list[$value1['id']] = json_decode($response);

            //campaign_report_bounce_totals
            
            // $curl = curl_init();
            // $query = 'https://biorev33069.api-us1.com/admin/api.php?api_action=campaign_report_bounce_totals&api_key=5ab82418363737bc55f771e7529c7c1f42ff0d41c623cfe874b2ec0a4455995d34493f39&campaignid='.$value1['id'].'&api_output=json';
            // curl_setopt_array($curl, array(
            //     CURLOPT_URL => $query,
            //     CURLOPT_RETURNTRANSFER => true,
            //     CURLOPT_ENCODING => '',
            //     CURLOPT_MAXREDIRS => 10,
            //     CURLOPT_TIMEOUT => 0,
            //     CURLOPT_FOLLOWLOCATION => true,
            //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            //     CURLOPT_CUSTOMREQUEST => 'GET',
            //     CURLOPT_HTTPHEADER => array(
            //         'Api-Token: 5ab82418363737bc55f771e7529c7c1f42ff0d41c623cfe874b2ec0a4455995d34493f39',
            //         'Cookie: __cfduid=d97b6427615f0d72feec531918ba097411618376679; PHPSESSID=56d9cad6a1d8133f1acd1d92a006cedd; cmp90303434=205ff7b29cb3bbd970014602eb9a70f0; em_acp_globalauth_cookie=f817b856-b9df-4998-9b94-ac98e20f3642'
            //     ),
            // ));
            // $response = curl_exec($curl);
            // curl_close($curl);
            // $resultsNewcampaign_report_bounce_totals[$value1['id']] = json_decode($response);

            //campaign_report_forward_list
            
            // $curl = curl_init();
            // $query = 'https://biorev33069.api-us1.com/admin/api.php?api_action=campaign_report_forward_list&api_key=5ab82418363737bc55f771e7529c7c1f42ff0d41c623cfe874b2ec0a4455995d34493f39&campaignid='.$value1['id'].'&api_output=json';
            // curl_setopt_array($curl, array(
            //     CURLOPT_URL => $query,
            //     CURLOPT_RETURNTRANSFER => true,
            //     CURLOPT_ENCODING => '',
            //     CURLOPT_MAXREDIRS => 10,
            //     CURLOPT_TIMEOUT => 0,
            //     CURLOPT_FOLLOWLOCATION => true,
            //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            //     CURLOPT_CUSTOMREQUEST => 'GET',
            //     CURLOPT_HTTPHEADER => array(
            //         'Api-Token: 5ab82418363737bc55f771e7529c7c1f42ff0d41c623cfe874b2ec0a4455995d34493f39',
            //         'Cookie: __cfduid=d97b6427615f0d72feec531918ba097411618376679; PHPSESSID=56d9cad6a1d8133f1acd1d92a006cedd; cmp90303434=205ff7b29cb3bbd970014602eb9a70f0; em_acp_globalauth_cookie=f817b856-b9df-4998-9b94-ac98e20f3642'
            //     ),
            // ));
            // $response = curl_exec($curl);
            // curl_close($curl);
            // $resultsNewcampaign_report_forward_list[$value1['id']] = json_decode($response);

            //campaign_report_forward_totals
            
            // $curl = curl_init();
            // $query = 'https://biorev33069.api-us1.com/admin/api.php?api_action=campaign_report_forward_totals&api_key=5ab82418363737bc55f771e7529c7c1f42ff0d41c623cfe874b2ec0a4455995d34493f39&campaignid='.$value1['id'].'&api_output=json';
            // curl_setopt_array($curl, array(
            //     CURLOPT_URL => $query,
            //     CURLOPT_RETURNTRANSFER => true,
            //     CURLOPT_ENCODING => '',
            //     CURLOPT_MAXREDIRS => 10,
            //     CURLOPT_TIMEOUT => 0,
            //     CURLOPT_FOLLOWLOCATION => true,
            //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            //     CURLOPT_CUSTOMREQUEST => 'GET',
            //     CURLOPT_HTTPHEADER => array(
            //         'Api-Token: 5ab82418363737bc55f771e7529c7c1f42ff0d41c623cfe874b2ec0a4455995d34493f39',
            //         'Cookie: __cfduid=d97b6427615f0d72feec531918ba097411618376679; PHPSESSID=56d9cad6a1d8133f1acd1d92a006cedd; cmp90303434=205ff7b29cb3bbd970014602eb9a70f0; em_acp_globalauth_cookie=f817b856-b9df-4998-9b94-ac98e20f3642'
            //     ),
            // ));
            // $response = curl_exec($curl);
            // curl_close($curl);
            // $resultsNewcampaign_report_forward_totals[$value1['id']] = json_decode($response);
            //campaign_report_link_list
            
            // $curl = curl_init();
            // $query = 'https://biorev33069.api-us1.com/admin/api.php?api_action=campaign_report_link_list&api_key=5ab82418363737bc55f771e7529c7c1f42ff0d41c623cfe874b2ec0a4455995d34493f39&campaignid='.$value1['id'].'&api_output=json';
            // curl_setopt_array($curl, array(
            //     CURLOPT_URL => $query,
            //     CURLOPT_RETURNTRANSFER => true,
            //     CURLOPT_ENCODING => '',
            //     CURLOPT_MAXREDIRS => 10,
            //     CURLOPT_TIMEOUT => 0,
            //     CURLOPT_FOLLOWLOCATION => true,
            //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            //     CURLOPT_CUSTOMREQUEST => 'GET',
            //     CURLOPT_HTTPHEADER => array(
            //         'Api-Token: 5ab82418363737bc55f771e7529c7c1f42ff0d41c623cfe874b2ec0a4455995d34493f39',
            //         'Cookie: __cfduid=d97b6427615f0d72feec531918ba097411618376679; PHPSESSID=56d9cad6a1d8133f1acd1d92a006cedd; cmp90303434=205ff7b29cb3bbd970014602eb9a70f0; em_acp_globalauth_cookie=f817b856-b9df-4998-9b94-ac98e20f3642'
            //     ),
            // ));
            // $response = curl_exec($curl);
            // curl_close($curl);
            // $resultsNewcampaign_report_link_list[$value1['id']] = json_decode($response);
            //campaign_report_link_totals
            
            // $curl = curl_init();
            // $query = 'https://biorev33069.api-us1.com/admin/api.php?api_action=campaign_report_link_totals&api_key=5ab82418363737bc55f771e7529c7c1f42ff0d41c623cfe874b2ec0a4455995d34493f39&campaignid='.$value1['id'].'&api_output=json';
            // curl_setopt_array($curl, array(
            //     CURLOPT_URL => $query,
            //     CURLOPT_RETURNTRANSFER => true,
            //     CURLOPT_ENCODING => '',
            //     CURLOPT_MAXREDIRS => 10,
            //     CURLOPT_TIMEOUT => 0,
            //     CURLOPT_FOLLOWLOCATION => true,
            //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            //     CURLOPT_CUSTOMREQUEST => 'GET',
            //     CURLOPT_HTTPHEADER => array(
            //         'Api-Token: 5ab82418363737bc55f771e7529c7c1f42ff0d41c623cfe874b2ec0a4455995d34493f39',
            //         'Cookie: __cfduid=d97b6427615f0d72feec531918ba097411618376679; PHPSESSID=56d9cad6a1d8133f1acd1d92a006cedd; cmp90303434=205ff7b29cb3bbd970014602eb9a70f0; em_acp_globalauth_cookie=f817b856-b9df-4998-9b94-ac98e20f3642'
            //     ),
            // ));
            // $response = curl_exec($curl);
            // curl_close($curl);
            // $resultsNewcampaign_report_link_totals[$value1['id']] = json_decode($response);

            //campaign_report_open_list
            
            // $curl = curl_init();
            // $query = 'https://biorev33069.api-us1.com/admin/api.php?api_action=campaign_report_open_list&filter[subscriberid]='.$aid.'&api_key=5ab82418363737bc55f771e7529c7c1f42ff0d41c623cfe874b2ec0a4455995d34493f39&campaignid='.$value1['id'].'&api_output=json';
            // curl_setopt_array($curl, array(
            //     CURLOPT_URL => $query,
            //     CURLOPT_RETURNTRANSFER => true,
            //     CURLOPT_ENCODING => '',
            //     CURLOPT_MAXREDIRS => 10,
            //     CURLOPT_TIMEOUT => 0,
            //     CURLOPT_FOLLOWLOCATION => true,
            //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            //     CURLOPT_CUSTOMREQUEST => 'GET',
            //     CURLOPT_HTTPHEADER => array(
            //         'Api-Token: 5ab82418363737bc55f771e7529c7c1f42ff0d41c623cfe874b2ec0a4455995d34493f39',
            //         'Cookie: __cfduid=d97b6427615f0d72feec531918ba097411618376679; PHPSESSID=56d9cad6a1d8133f1acd1d92a006cedd; cmp90303434=205ff7b29cb3bbd970014602eb9a70f0; em_acp_globalauth_cookie=f817b856-b9df-4998-9b94-ac98e20f3642'
            //     ),
            // ));
            // $response = curl_exec($curl);
            // curl_close($curl);
            // $resultsNewcampaign_report_open_list[$value1['id']] = json_decode($response);
            //campaign_report_open_totals
            
            // $curl = curl_init();
            // $query = 'https://biorev33069.api-us1.com/admin/api.php?api_action=campaign_report_open_totals&api_key=5ab82418363737bc55f771e7529c7c1f42ff0d41c623cfe874b2ec0a4455995d34493f39&campaignid='.$value1['id'].'&api_output=json';
            // curl_setopt_array($curl, array(
            //     CURLOPT_URL => $query,
            //     CURLOPT_RETURNTRANSFER => true,
            //     CURLOPT_ENCODING => '',
            //     CURLOPT_MAXREDIRS => 10,
            //     CURLOPT_TIMEOUT => 0,
            //     CURLOPT_FOLLOWLOCATION => true,
            //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            //     CURLOPT_CUSTOMREQUEST => 'GET',
            //     CURLOPT_HTTPHEADER => array(
            //         'Api-Token: 5ab82418363737bc55f771e7529c7c1f42ff0d41c623cfe874b2ec0a4455995d34493f39',
            //         'Cookie: __cfduid=d97b6427615f0d72feec531918ba097411618376679; PHPSESSID=56d9cad6a1d8133f1acd1d92a006cedd; cmp90303434=205ff7b29cb3bbd970014602eb9a70f0; em_acp_globalauth_cookie=f817b856-b9df-4998-9b94-ac98e20f3642'
            //     ),
            // ));
            // $response = curl_exec($curl);
            // curl_close($curl);
            // $resultsNewcampaign_report_open_totals[$value1['id']] = json_decode($response);

            //campaign_list
            // $curl = curl_init();
            // $query = 'https://biorev33069.api-us1.com/admin/api.php?api_action=campaign_list&api_key=5ab82418363737bc55f771e7529c7c1f42ff0d41c623cfe874b2ec0a4455995d34493f39&id='.$value1['id'].'&api_output=json';
            // curl_setopt_array($curl, array(
            //     CURLOPT_URL => $query,
            //     CURLOPT_RETURNTRANSFER => true,
            //     CURLOPT_ENCODING => '',
            //     CURLOPT_MAXREDIRS => 10,
            //     CURLOPT_TIMEOUT => 0,
            //     CURLOPT_FOLLOWLOCATION => true,
            //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            //     CURLOPT_CUSTOMREQUEST => 'GET',
            //     CURLOPT_HTTPHEADER => array(
            //         'Api-Token: 5ab82418363737bc55f771e7529c7c1f42ff0d41c623cfe874b2ec0a4455995d34493f39',
            //         'Cookie: __cfduid=d97b6427615f0d72feec531918ba097411618376679; PHPSESSID=56d9cad6a1d8133f1acd1d92a006cedd; cmp90303434=205ff7b29cb3bbd970014602eb9a70f0; em_acp_globalauth_cookie=f817b856-b9df-4998-9b94-ac98e20f3642'
            //     ),
            // ));
            // $response = curl_exec($curl);
            // curl_close($curl);
            // $resultsNewcampaign_list[$value1['id']] = json_decode($response);
        }

        

        

        $resultsOldEmailActivity = [];
        // $curl = curl_init();
        // $query = 'https://biorev33069.api-us1.com/api/3/emailActivities?filters[subscriberid]='.$aid.'&orders[tstamp]=DESC';
        // curl_setopt_array($curl, array(
        //     CURLOPT_URL => $query,
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_ENCODING => '',
        //     CURLOPT_MAXREDIRS => 10,
        //     CURLOPT_TIMEOUT => 0,
        //     CURLOPT_FOLLOWLOCATION => true,
        //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //     CURLOPT_CUSTOMREQUEST => 'GET',
        //     CURLOPT_HTTPHEADER => array(
        //         'Api-Token: 5ab82418363737bc55f771e7529c7c1f42ff0d41c623cfe874b2ec0a4455995d34493f39',
        //         'Cookie: __cfduid=d97b6427615f0d72feec531918ba097411618376679; PHPSESSID=56d9cad6a1d8133f1acd1d92a006cedd; cmp90303434=205ff7b29cb3bbd970014602eb9a70f0; em_acp_globalauth_cookie=f817b856-b9df-4998-9b94-ac98e20f3642'
        //     ),
        // ));
        // $response = curl_exec($curl);
        // curl_close($curl);
        // $resultsOldEmailActivity = json_decode($response);

        $resultsOldActivity = [];
        // $curl = curl_init();
        // $resultsOldActivity = [];
        // $query = 'https://biorev33069.api-us1.com/api/3/activities?filters[subscriberid]='.$aid.'&include=reference.deal,reference.deal.contact,reference.dealTasktype';
        // curl_setopt_array($curl, array(
        //     CURLOPT_URL => $query,
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_ENCODING => '',
        //     CURLOPT_MAXREDIRS => 10,
        //     CURLOPT_TIMEOUT => 0,
        //     CURLOPT_FOLLOWLOCATION => true,
        //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //     CURLOPT_CUSTOMREQUEST => 'GET',
        //     CURLOPT_HTTPHEADER => array(
        //         'Api-Token: 5ab82418363737bc55f771e7529c7c1f42ff0d41c623cfe874b2ec0a4455995d34493f39',
        //         'Cookie: __cfduid=d97b6427615f0d72feec531918ba097411618376679; PHPSESSID=56d9cad6a1d8133f1acd1d92a006cedd; cmp90303434=205ff7b29cb3bbd970014602eb9a70f0; em_acp_globalauth_cookie=f817b856-b9df-4998-9b94-ac98e20f3642'
        //     ),
        // ));
        // $response = curl_exec($curl);
        // curl_close($curl);
        // $resultsOldActivity = json_decode($response);

        //List all automations the contact is in
        $resultsOldContactContactAutomation = [];
        // $curl = curl_init();
        // $query = 'https://biorev33069.api-us1.com/api/3/contacts/'.$aid.'/contactAutomations';
        // curl_setopt_array($curl, array(
        //     CURLOPT_URL => $query,
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_ENCODING => '',
        //     CURLOPT_MAXREDIRS => 10,
        //     CURLOPT_TIMEOUT => 0,
        //     CURLOPT_FOLLOWLOCATION => true,
        //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //     CURLOPT_CUSTOMREQUEST => 'GET',
        //     CURLOPT_HTTPHEADER => array(
        //         'Api-Token: 5ab82418363737bc55f771e7529c7c1f42ff0d41c623cfe874b2ec0a4455995d34493f39',
        //         'Cookie: __cfduid=d97b6427615f0d72feec531918ba097411618376679; PHPSESSID=56d9cad6a1d8133f1acd1d92a006cedd; cmp90303434=205ff7b29cb3bbd970014602eb9a70f0; em_acp_globalauth_cookie=f817b856-b9df-4998-9b94-ac98e20f3642'
        //     ),
        // ));
        // $response = curl_exec($curl);
        // curl_close($curl);
        // $resultsOldContactContactAutomation = json_decode($response);

        //View all automations that are associated with a specific contact.
        $resultsNewContactContactAutomation = [];
        // $curl = curl_init();
        // $query = 'https://biorev33069.api-us1.com/admin/api.php?api_action=contact_automation_list&api_key=5ab82418363737bc55f771e7529c7c1f42ff0d41c623cfe874b2ec0a4455995d34493f39&contact_id='.$aid.'&api_output=json';
        // curl_setopt_array($curl, array(
        //     CURLOPT_URL => $query,
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_ENCODING => '',
        //     CURLOPT_MAXREDIRS => 10,
        //     CURLOPT_TIMEOUT => 0,
        //     CURLOPT_FOLLOWLOCATION => true,
        //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //     CURLOPT_CUSTOMREQUEST => 'GET',
        //     CURLOPT_HTTPHEADER => array(
        //         'Api-Token: 5ab82418363737bc55f771e7529c7c1f42ff0d41c623cfe874b2ec0a4455995d34493f39',
        //         'Cookie: __cfduid=d97b6427615f0d72feec531918ba097411618376679; PHPSESSID=56d9cad6a1d8133f1acd1d92a006cedd; cmp90303434=205ff7b29cb3bbd970014602eb9a70f0; em_acp_globalauth_cookie=f817b856-b9df-4998-9b94-ac98e20f3642'
        //     ),
        // ));
        // $response = curl_exec($curl);
        // curl_close($curl);
        // $resultsNewContactContactAutomation = json_decode($response);

        //List all automations the contact is in
        // $resultsOldContactAutomation = [];
        // $curl = curl_init();
        // $query = 'https://biorev33069.api-us1.com/api/3/contactAutomations?filters[subscriberid]='.$aid;
        // curl_setopt_array($curl, array(
        //     CURLOPT_URL => $query,
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_ENCODING => '',
        //     CURLOPT_MAXREDIRS => 10,
        //     CURLOPT_TIMEOUT => 0,
        //     CURLOPT_FOLLOWLOCATION => true,
        //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //     CURLOPT_CUSTOMREQUEST => 'GET',
        //     CURLOPT_HTTPHEADER => array(
        //         'Api-Token: 5ab82418363737bc55f771e7529c7c1f42ff0d41c623cfe874b2ec0a4455995d34493f39',
        //         'Cookie: __cfduid=d97b6427615f0d72feec531918ba097411618376679; PHPSESSID=56d9cad6a1d8133f1acd1d92a006cedd; cmp90303434=205ff7b29cb3bbd970014602eb9a70f0; em_acp_globalauth_cookie=f817b856-b9df-4998-9b94-ac98e20f3642'
        //     ),
        // ));
        // $response = curl_exec($curl);
        // curl_close($curl);
        // $resultsOldContactAutomation = json_decode($response);

        $resultsOldCampaign = [];
        // $curl = curl_init();
        // $query = 'https://biorev33069.api-us1.com/api/3/campaigns?filters[subscriberid]='.$aid;
        // curl_setopt_array($curl, array(
        //     CURLOPT_URL => $query,
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_ENCODING => '',
        //     CURLOPT_MAXREDIRS => 10,
        //     CURLOPT_TIMEOUT => 0,
        //     CURLOPT_FOLLOWLOCATION => true,
        //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //     CURLOPT_CUSTOMREQUEST => 'GET',
        //     CURLOPT_HTTPHEADER => array(
        //         'Api-Token: 5ab82418363737bc55f771e7529c7c1f42ff0d41c623cfe874b2ec0a4455995d34493f39',
        //         'Cookie: __cfduid=d97b6427615f0d72feec531918ba097411618376679; PHPSESSID=56d9cad6a1d8133f1acd1d92a006cedd; cmp90303434=205ff7b29cb3bbd970014602eb9a70f0; em_acp_globalauth_cookie=f817b856-b9df-4998-9b94-ac98e20f3642'
        //     ),
        // ));
        // $response = curl_exec($curl);
        // curl_close($curl);
        // $resultsOldCampaign = json_decode($response);

        
        //automatioin
        $resultsNewautomation_list = [];
        // $curl = curl_init();
        // $query = 'https://biorev33069.api-us1.com/admin/api.php?api_action=automation_list&api_key=5ab82418363737bc55f771e7529c7c1f42ff0d41c623cfe874b2ec0a4455995d34493f39&api_output=json';
        // curl_setopt_array($curl, array(
        //     CURLOPT_URL => $query,
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_ENCODING => '',
        //     CURLOPT_MAXREDIRS => 10,
        //     CURLOPT_TIMEOUT => 0,
        //     CURLOPT_FOLLOWLOCATION => true,
        //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //     CURLOPT_CUSTOMREQUEST => 'GET',
        //     CURLOPT_HTTPHEADER => array(
        //         'Api-Token: 5ab82418363737bc55f771e7529c7c1f42ff0d41c623cfe874b2ec0a4455995d34493f39',
        //         'Cookie: __cfduid=d97b6427615f0d72feec531918ba097411618376679; PHPSESSID=56d9cad6a1d8133f1acd1d92a006cedd; cmp90303434=205ff7b29cb3bbd970014602eb9a70f0; em_acp_globalauth_cookie=f817b856-b9df-4998-9b94-ac98e20f3642'
        //     ),
        // ));
        // $response = curl_exec($curl);
        // curl_close($curl);
        // $resultsNewautomation_list = json_decode($response);
        
        return [
            'resultsNewContact' => $resultsNewContact, 
            'resultsOldContact' => $resultsOldContact,
            'resultsOldEmailActivity' => $resultsOldEmailActivity,
            'resultsOldActivity' => $resultsOldActivity,
            'resultsOldContactContactAutomation' => $resultsOldContactContactAutomation,
            'resultsNewContactContactAutomation' => $resultsNewContactContactAutomation,
            'resultsOldCampaign' => $resultsOldCampaign,
            // 'resultsNewcampaign_report_totals' => $resultsNewcampaign_report_totals,
            // 'resultsNewcampaign_report_bounce_list' => $resultsNewcampaign_report_bounce_list,
            // 'resultsNewcampaign_report_bounce_totals' => $resultsNewcampaign_report_bounce_totals,
            // 'resultsNewcampaign_report_forward_list' => $resultsNewcampaign_report_forward_list,
            // 'resultsNewcampaign_report_forward_totals' => $resultsNewcampaign_report_forward_totals,
            // 'resultsNewcampaign_report_link_list' => $resultsNewcampaign_report_link_list,
            // 'resultsNewcampaign_report_link_totals' => $resultsNewcampaign_report_link_totals,
            // 'resultsNewcampaign_report_open_list' => $resultsNewcampaign_report_open_list,
            // 'resultsNewcampaign_report_open_totals' => $resultsNewcampaign_report_open_totals,
            'resultsNewautomation_list' => $resultsNewautomation_list,
            'resultsNewcampaign_list' => $resultsNewcampaign_list,
        ];
    }

    //testing
    public function getOutreachRecords(Request $request){
        ini_set('max_execution_time', 3600);
        // outreach starts
        
        $accessToken = $this->outreachsession();
        for($i = 0; $i < 50; $i++):
            $curl = curl_init();
            $per_page_record = 1000;
            $startId = $i*$per_page_record;
            $endId = ($i+1)*$per_page_record-1;
            $query = "https://api.outreach.io/api/v2/prospects?sort=id&filter[id]=$startId..$endId&count=true&page[limit]=1000&&include=persona,stage";
            //print_r($query);
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
            curl_close($curl);
            $results = get_object_vars(json_decode($response));    
            foreach($results['data'] as $key => $value){
                $stageName = null;
                $personaName = null;
                $stageId = null;
                $personaId = null;
                $attributes = get_object_vars($value->attributes);
                $relationships = get_object_vars($value->relationships);
                $stage = get_object_vars($relationships["stage"]);
                $persona = get_object_vars($relationships["persona"]);
                if(is_object($stage["data"])):
                    $stage = get_object_vars($stage["data"]);
                    $stageId = $stage["id"];
                endif;
                if(is_object($persona["data"])):
                    $persona = get_object_vars($persona["data"]);
                    $personaId = $persona["id"];
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
                endforeach;
                $count = Newcontacts::where('record_id', '=', $value->id)->count();
                                    
                Newcontacts::create([
                    "record_id" => $value->id,
                    "designation" => $attributes['title'],
                    "first_name" => $attributes['firstName'],
                    "last_name" => $attributes['lastName'],
                    "mobilePhones" => implode(',', $attributes['mobilePhones']),
                    "workPhones" => implode(',', $attributes['workPhones']),
                    "homePhones" => implode(',', $attributes['homePhones']),
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
                    "outreach_persona" => $personaName,
                ]);
            }
            echo '<pre>'; print_r($query); echo '</pre>';
                        
        endfor;
        echo 'complete';
        //return ['results' => $data];
    }

    public function getOutreachAccountLists(Request $request){
        $this->outreachsession();
        $recordPerPage = $request->input("recordPerPage");

        $page = $request->input('page');
        if($page == 1):
            $offset = 0;
        else:
            $offset = ($page-1)*intval($recordPerPage);        
        endif;
        $search = $request->input("textSearch");
        $sortby = $request->input('sortby');
        $sort = $request->input("sort");
        //$totalRecords = OutreachAccounts::count();
        $search = $request->input("textSearch");
        if($search):
            $q = OutreachAccounts::with('prospects', 'tasks', 'sequencesActive', 'sequencesInActive')
            ->orWhere(function($q) use ($search){
                $q->orWhere('name', 'like', '%'.$search.'%');
                //->orWhere('last_name', 'like', '%'.$search.'%')->orWhere('emails', 'like', '%'.$search.'%')->orWhere('company', 'like', '%'.$search.'%');
            });
        else:
            $q = OutreachAccounts::with('prospects', 'tasks', 'sequencesActive', 'sequencesInActive');
        endif;
        if($request->input("sortBy") == 'asc'):
            $records = $q->orderBy($request->input('sortType'));//->paginate($recordPerPage); 
        else:
            $records = $q->orderByDesc($request->input('sortType'));//->paginate($recordPerPage);
        endif;
        if($request->input("textSearch")):
            $records = $records->orWhere(function($q) use ($search){
                $q->orWhere('name', 'like', '%'.$search.'%');
            });
        endif;
        if(count($request->input('filterConditionsArray')) > 0):
            foreach($request->input('filterConditionsArray') as $key => $value):
                if( ($value['type'] == 'textbox') || ($value['type'] == 'dropdown') ):
                    if($value['formula'] == 'is'):
                        if(strpos($value["textCondition"],  ",")):
                            $ids = explode(",", $value["textCondition"]);
                            foreach($ids as $IdValue):
                                $records->orWhere($value["condition"], 'like', $IdValue);
                            endforeach;
                        else:
                            $records->where($value["condition"], 'like', $value["textCondition"]);
                        endif;
                    elseif($value['formula'] == 'is not'):
                        if(strpos($value["textCondition"],  ",")):
                            $ids = explode(",", $value["textCondition"]);
                            foreach($ids as $IdValue):
                                $records->orWhere($value["condition"], 'not like', $IdValue);
                            endforeach;
                        else:
                            $records->where($value["condition"], 'not like', $value["textCondition"]);
                        endif;
                    elseif($value['formula'] == 'starts with'):
                        if(strpos($value["textCondition"],  ",")):
                            $ids = explode(",", $value["textCondition"]);
                            foreach($ids as $IdValue):
                                $records->orWhere($value["condition"], 'like', $IdValue);
                            endforeach;
                        else:
                            $records->where($value["condition"], 'like', $value["textCondition"].'%');
                        endif;
                    elseif($value['formula'] == 'is empty'):                        
                        $records->whereNull($value["condition"]);
                    elseif($value['formula'] == 'is not empty'):
                        $records->whereNotNull($value["condition"]);
                    elseif($value['formula'] == 'contains'):
                        $records->where($value["condition"], 'like', '%'.$value["textCondition"].'%');
                    elseif($value['formula'] == 'ends with'):
                        $records->where($value["condition"], '=', '%'.$value["textCondition"]);
                    endif;
                elseif($value['type'] == 'calendar'):                    
                    $date = explode('--', $value["textCondition"]);// dd($date);
                    if($date[0] == $date[1]):
                        $s = substr($date[0], 0, 10);
                        $records->whereDate($value["condition"], $s);
                    else:
                        $s = substr($date[0], 0, 10);
                        $e = substr($date[1], 0, 10);
                        $records->whereBetween($value["condition"], [$s, $e]);
                    endif;
                endif;
            endforeach;
        endif;
        $totalRecords = $records->count();
        
        $records = $records->paginate($recordPerPage);
        $paginate = ['page' => $page, 'start' => $offset+1, 'end' => $offset+$recordPerPage, 'count' => $recordPerPage, 'total' => $totalRecords, 'pager' =>ceil($totalRecords/$recordPerPage)];
        return ['results' => $records, 'page' => $paginate, 'totalRecords' => $totalRecords];
    }

    public function getAccountProspects(Request $request)
    {
        $accountId = intval($request->input("account_id"));
        $this->outreachsession();
        $recordPerPage = 20;
        $page = $request->input('page');
        if($page == 1):
            $offset = 0;
        else:
            $offset = ($page-1)*intval($recordPerPage);        
        endif;
        $search = $request->input("textSearch");
        $sortby = $request->input('sortby');
        $sort = $request->input("sort");
        if($search):
            $q = Contacts::with('stageData')
            ->orWhere(function($q) use ($search){
                $q->orWhere('first_name', 'like', '%'.$search.'%')->orWhere('last_name', 'like', '%'.$search.'%')->orWhere('emails', 'like', '%'.$search.'%')->orWhere('company', 'like', '%'.$search.'%');
            });
        else:
            $q = Contacts::with('stageData');
        endif;
        if(count($request->input('filterConditionsArray')) > 0):
            foreach($request->input('filterConditionsArray') as $key => $value):
                if( ($value['type'] == 'textbox') || ($value['type'] == 'dropdown') ):
                    if($value['formula'] == 'is'):
                        if(strpos($value["textCondition"],  ",")):
                            $ids = explode(",", $value["textCondition"]);
                            foreach($ids as $IdValue):
                                $q->orWhere($value["condition"], 'like', $IdValue);
                            endforeach;
                        else:
                            $q->where($value["condition"], 'like', $value["textCondition"]);
                        endif;
                    elseif($value['formula'] == 'is not'):
                        if(strpos($value["textCondition"],  ",")):
                            $ids = explode(",", $value["textCondition"]);
                            foreach($ids as $IdValue):
                                $q->orWhere($value["condition"], 'not like', $IdValue);
                            endforeach;
                        else:
                            $q->where($value["condition"], 'not like', $value["textCondition"]);
                        endif;
                    elseif($value['formula'] == 'starts with'):
                        if(strpos($value["textCondition"],  ",")):
                            $ids = explode(",", $value["textCondition"]);
                            foreach($ids as $IdValue):
                                $q->orWhere($value["condition"], 'like', $IdValue);
                            endforeach;
                        else:
                            $q->where($value["condition"], 'like', $value["textCondition"].'%');
                        endif;
                    elseif($value['formula'] == 'is empty'):                        
                        $q->whereNull($value["condition"]);
                    elseif($value['formula'] == 'is not empty'):
                        $q->whereNotNull($value["condition"]);
                    elseif($value['formula'] == 'contains'):
                        $q->where($value["condition"], 'like', '%'.$value["textCondition"].'%');
                    elseif($value['formula'] == 'ends with'):
                        $q->where($value["condition"], '=', '%'.$value["textCondition"]);
                    endif;
                elseif($value['type'] == 'calendar'):                    
                    $date = explode('--', $value["textCondition"]);// dd($date);
                    if($date[0] == $date[1]):
                        $s = substr($date[0], 0, 10);
                        $q->whereDate($value["condition"], $s);
                    else:
                        $s = substr($date[0], 0, 10);
                        $e = substr($date[1], 0, 10);
                        $q->whereBetween($value["condition"], [$s, $e]);
                    endif;
                endif;
            endforeach;
        endif;
        if($request->input('recordPerPage') == 'all'):
        else:
            $recordPerPage  = $request->input('recordPerPage');
        endif;
        $q = $q->where('account_id', '=', $accountId);
        $totalRecords = $q->count();
        if($request->input("sortBy") == 'asc'):
            $records = $q->orderBy( $request->input('sortType'))->paginate($recordPerPage); 
        else:
            $records = $q->orderByDesc($request->input('sortType'))->paginate($recordPerPage);    
        endif;
    
        $paginate = ['page' => $page, 'start' => $offset+1, 'end' => $offset+$recordPerPage, 'count' => $recordPerPage, 'total' => $totalRecords, 'pager' =>ceil($totalRecords/$recordPerPage)];
        return ['results' => $records, 'page' => $paginate, 'totalRecords' => $totalRecords];
    }

    public function AllStagesAccount($id){
        $accountId = intval($id);
        $stages = Stages::get();
        $allStages = [];
        foreach ($stages as $key => $stage) {
            $allStages[] = ['oid' => $stage->oid,
                            'stage' => $stage->stage,
                            'count' => Contacts::where('stage', $stage->oid)->where('account_id', '=', $accountId)->get()->count()];
        }
        return $allStages;
    }

    public function getOutreachAccountInfo($id){

        $results =  OutreachAccounts::with('prospects')->where('account_id', '=', $id)->first();
        $prospectsWithCompleteTask = Contacts::with('stageData','tasksTotal', 'tasksComplete', 'tasksNotComplete' )->where('account_id', '=', $id)->get();
        return ['results' =>$results, 'prospectsWithCompleteTask' => $prospectsWithCompleteTask];
    }
    
    public function getAllProspectsEmail(Request $request){
        //https://api.outreach.io/api/v2/mailings?filter[prospect][id]=42798
        $ids = $request->input('ids');
        $curl = curl_init();
        $accessToken = $this->outreachsession();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.outreach.io/api/v2/mailings?sort=-createdAt&filter[prospect][id]=$ids&include=prospect&fields[prospect]=firstName,lastName",
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
        $results = json_decode($response);
        $data = get_object_vars($results);  
        $ids = explode(",", $ids);
        $data = [];
        foreach($ids as $value):
            $data[$value] = DB::table('contacts')->where('record_id', $value)->first();   
        endforeach;
        return ['emails' => $results, 'owner' => $data];
    }

    public function getAllProspectsCall(Request $request){
        $ids = $request->input('ids');
        $curl = curl_init();
        $accessToken = $this->outreachsession();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.outreach.io/api/v2/calls?filter[prospect][id]=$ids&include=callDisposition,callPurpose,prospect,user&fields[prospect]=firstName,lastName&fields[user]=name",
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
        $results = json_decode($response);
        return ['calls' => $results];
    }
    
    public function getCallPurpose(){
        $curl = curl_init();
        $accessToken = $this->outreachsession();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.outreach.io/api/v2/callPurposes",
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
        $results = json_decode($response);
        return ['details' => $results];
    }

    public function getAllUsers(){
        $curl = curl_init();
        $accessToken = $this->outreachsession();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.outreach.io/api/v2/users",
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
        $results = json_decode($response);
        $data = get_object_vars($results);        
        return ['users' => $results];
    }

    public function getCallDispositions(){
        $curl = curl_init();
        $accessToken = $this->outreachsession();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.outreach.io/api/v2/callDispositions",
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
        $results = json_decode($response);
        $data = get_object_vars($results);        
        return ['details' => $results];
    }
    //prospect details

    public function outreachprospectDetails($id = null)
    {
        $contact = Contacts::with('stageData', 'account', 'customfields')->where("record_id", $id)->first();
        if($contact->timeZone): 
            $contact->timediff = $this->__UTC_Offset_DST($contact->timeZone);
            $contact->localtime = $this->__UTC_LOCAL_TIME($contact->timeZone);
        else: $contact->timediff = ''; $contact->localtime = '-'; endif;
        return ['results' => $contact];
    }

    private function __UTC_Offset_DST( $time_zone = null) 
    {
        date_default_timezone_set( 'UTC' );
        $utc = new DateTime();
    
        // Calculate offset.
        $current   = timezone_open( $time_zone );
        $offset_s  = timezone_offset_get( $current, $utc ); // seconds
        $offset_h  = $offset_s / ( 60 * 60 ); // hours
    
        // Prepend “+” when positive
        $offset_h  = (string) $offset_h;
        if ( strpos( $offset_h, ' - ' ) === FALSE ) {
            $offset_h = ' + ' . $offset_h; // prepend +
        }
        return 'UTC' . $offset_h;
    }

    private function __UTC_LOCAL_TIME( $time_zone = null) 
    {
        $dt = new DateTime("now", new DateTimeZone($time_zone));
        return $dt->format('M d, Y H:i A');
    }
}