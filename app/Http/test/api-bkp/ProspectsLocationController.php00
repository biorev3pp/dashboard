<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Contacts;
use App\Models\ContactTime;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\Timezone;
use App\Models\MultiCityAreaCode;
use App\Models\NxxRecord;
use App\Models\NxxItem;
use App\Models\ContactCustoms;
use App\Models\Settings;

class ProspectsLocationController extends Controller
{
    public function index(Request $request){
        if($request->input("recordPerPage")){
            $recordPerPage = $request->input("recordPerPage");
        }else{
            $recordPerPage = 20;
        }
        $page = $request->input('page');
        if($page == 1):
            $offset = 0;
        else:
            $offset = ($page-1)*intval($recordPerPage);
        endif;
        // $q = Contacts::select(DB::raw('contacts.record_id as contact_record_id, contacts.mobilePhones as mobilePhones, contacts.homePhones as homePhones, contacts.workPhones as workPhones, contacts.name as contact_name, contacts.country as contact_country, contacts.state as contact_state, contacts.city as contact_city, contacts.timezone as contact_timezone, timezones.timezone_type as time_timezone, states.state as state_name'))
        // ->where("country", "LIKE", "united states")
        // ->whereNull("contact_times.state_id")
        // ->leftJoin("contact_times", "contacts.record_id", "=", "contact_times.contact_id")
        // ->leftJoin("states", "contact_times.state_id", "=", "states.id")
        // ->leftJoin("timezones", "contact_times.timezone_id", "=", "timezones.id");
        $q = Contacts::select(DB::raw('contacts.record_id as contact_record_id, contacts.mobilePhones as mobilePhones, contacts.homePhones as homePhones, contacts.workPhones as workPhones, contacts.name as contact_name, contacts.country as contact_country, contacts.state as contact_state, contacts.state as state, contacts.city as contact_city, contacts.timezone as contact_timezone, timezones.timezone_type as time_timezone, states.state as state_name'))
        ->where("country", "LIKE", "united states");
        if($request->input("type") == 0){
            //state is null
            $q->where(function($inq)  {
                $inq->where('contacts.state', '=', '');
                $inq->orWhere('contacts.state', null);
            });
        }elseif($request->input("type") == 1){
            //state is not null
            $q->where(function($inq)  {
                $inq->where('contacts.state', '!=', '');
                $inq->orWhereNotNull('contacts.state');
            });
        }elseif($request->input("type") == 2){
            
        }
        $q->whereNotNull("contact_times.state_id")
        ->leftJoin("contact_times", "contacts.record_id", "=", "contact_times.contact_id")
        ->leftJoin("states", "contact_times.state_id", "=", "states.id")
        ->leftJoin("timezones", "contact_times.timezone_id", "=", "timezones.id");
        $totalRecords = $q->count();
        if($request->input("sortBy") == 'asc'):
            $records = $q->orderBy( $request->input('sortType'))->paginate($recordPerPage); 
        else:
            $records = $q->orderByDesc($request->input('sortType'))->paginate($recordPerPage);    
        endif;

        $paginate = ['page' => $page, 'start' => $offset+1, 'end' => $offset+$recordPerPage, 'count' => $recordPerPage, 'total' => $totalRecords, 'pager' =>ceil($totalRecords/$recordPerPage)];
        return ['results' => $records, 'page' => $paginate, 'totalRecords' => $totalRecords];
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
    public function updateOutreachContactsOnOutreachServer($id){
        ini_set('max_execution_time', 3600);
        // $id = 20;
        $contact = Contacts::whereDate("updated_at", '2021-11-16')->where("record_id", ">" , $id)->first();
        
        $accessToken = $this->__outreachsessionProspects();
        
        $curl = curl_init();
        
        $data["data"] = [
            "type" => "prospect",
            "id" => intval($contact->record_id),
            "attributes" => [
                "addressCountry" => $contact->country,
                "addressState" => $contact->state,
            ]
        ];
        $d = json_encode($data);        
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.outreach.io/api/v2/prospects/$contact->record_id",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'PATCH',
        CURLOPT_POSTFIELDS => $d,
        CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $accessToken",
                "Content-Type: application/vnd.api+json"
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $record_id = $contact->record_id;
        return view('update-outreach-country-state-city-timezone', compact('record_id'));
    }
    public function updateTimezoneOnOutreachServer($id){
        ini_set('max_execution_time', 3600);
        // $id = 20;
        $contact = ContactCustoms::whereNotNull("custom29")->where("contact_id", ">" , $id)->first();
        
        $accessToken = $this->__outreachsessionProspects();
        
        $curl = curl_init();
        
        $data["data"] = [
            "type" => "prospect",
            "id" => intval($contact->contact_id),
            "attributes" => [
                "custom29" => $contact->custom29
            ]
        ];
        $d = json_encode($data);        
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.outreach.io/api/v2/prospects/$contact->contact_id",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'PATCH',
        CURLOPT_POSTFIELDS => $d,
        CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $accessToken",
                "Content-Type: application/vnd.api+json"
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $record_id = $contact->contact_id;
        return view('update-timezone-on-outreach-server', compact('record_id'));
    }
    function timezoneUpdate(){
        ini_set('max_execution_time', 3600);
        $states = State::get();
        foreach($states as $state){
            $recordTimezone = Timezone::where("id", $state["timezone_id"])->first();
            $contacts = Contacts::select("record_id","state")->where("state", "LIKE", $state["state"])->where("country", "LIKE", "United States")->get()->toArray();
            //dd();
            if($recordTimezone){
                ContactCustoms::whereIn("contact_id", array_column($contacts, "record_id"))->update([ 'custom29' => $recordTimezone->timezone_type]);
            }
        }
    }
    function updateStateCity(){
        $contacts  = Contacts::where("country", "LIKE", "United States")->whereNull("state")->get();
        foreach ($contacts as $contact) {
            $city_id = null;
            $code = null;
            if($contact["workPhones"]){
                $workPhones = $contact["workPhones"];
                $workPhones = $this->__NumberFormater($workPhones);
                $code = intval(substr($workPhones, 0, 3));
                if($code){
                    $city = City::where("area_code", $code)->first();
                    if($city){
                        $city_id = $city->id;
                        $state = State::where("code", "LIKE", $city["state_code"])->first();
                        if($state && is_null($contact["state"])){
                            Contacts::where("id", $contact["id"])->update([
                                "state" => $state->state,
                            ]);
                        }
                    }
                }
            }
            if(is_null($city_id) && $contact["homePhones"]){
                $homePhones = $contact["homePhones"];
                $homePhones = $this->__NumberFormater($homePhones);
                $code = intval(substr($homePhones, 0, 3));
                if($code){
                    $city = City::where("area_code", $code)->first();
                    if($city){
                        $city_id = $city->id;
                        $state = State::where("code", "LIKE", $city["state_code"])->first();
                        if($state && is_null($contact["state"])){
                            Contacts::where("id", $contact["id"])->update([
                                "state" => $state->state,
                            ]);
                        }
                    }
                }
            }
            if(is_null($city_id) && $contact["mobilePhones"]){
                $mobilePhones = $contact["mobilePhones"];
                $mobilePhones = $this->__NumberFormater($mobilePhones);
                $code = intval(substr($mobilePhones, 0, 3));
                if($code){
                    $city = City::where("area_code", $code)->first();
                    if($city){
                        $city_id = $city->id;
                        $state = State::where("code", "LIKE", $city["state_code"])->first();
                        if($state && is_null($contact["state"])){
                            Contacts::where("id", $contact["id"])->update([
                                "state" => $state->state,
                            ]);
                        }
                    }
                }
            }
        }
    }
    public function fetchNxx($id){
        $nid = $id+99;
        $count = 0;
        $records = NxxRecord::where("id", ">=", $id+1)->limit(100)->orderBy("id", "asc")->get();
        foreach($records as $nxxrecord){
            if(City::where("rate_center", "LIKE", $nxxrecord["rate_center"])->where("area_code", "LIKE", $nxxrecord["npa"])->count() == 0){
                City::create([
                    "country_id" => 228, 
                    "area_code" => $nxxrecord["npa"],
                    "rate_center" => $nxxrecord["rate_center"],
                    "state_code" => $nxxrecord["state"]
                ]);
            }
            ++$count;
        }
        $id = $nid;
        return view('fetchNxx', compact('id', 'count'));
        // $records = NxxRecord::where("id", ">", $id)->limit(500)->get();
        // $count = 0;
        // foreach($records as $city){
        //     $ratecenter = strtoupper(preg_replace( '/\s+/', '', $city["rate_center"]));
        //     NxxRecord::where("id", "=", $city["id"])->update(["rate_center" => $ratecenter]);
        //     $id = $city["id"];
        //     ++$count;
        // }
        // $records = NxxRecord::where("id", ">=", $id+1)->limit(100)->orderBy("id", "asc")->get();
        // $count = 0;
        // foreach($records as $nxxrecord){
        //     $count = NxxItem::where("state", "LIKE", $nxxrecord["state"])
        //     ->where("city", "LIKE", $nxxrecord["rate_center"])
        //     ->where("npa", "LIKE", $nxxrecord["npa"])            
        //     ->count();
        //     if($count == 0){
        //         $result = NxxItem::create([
        //             "state" => $nxxrecord["state"],
        //             "city" => $nxxrecord["rate_center"],
        //             "npa" => $nxxrecord["npa"],
        //             "nxx" => $nxxrecord["nxx"],
        //         ]);
        //         $id = $result->id;
        //     }
        //     ++$count;
        //     $lastid = $nxxrecord["id"];
        // }
        // return view('fetchNxx', compact('id', 'count', 'lastid'));
    }
    public function states($id){
        
        $contacts = Contacts::where("country", "LIKE", "united states")->where("id", ">", $id)->orderBy("id", "asc")->limit(100)->get();
        $country_id = 228;
        $count = 0;
        foreach($contacts as $contact){
            $state = $contact["state"];
            $state_id = null;
            $timezone_id = null;
            $timezone = null;
            $code = null;
                if($contact["workPhones"]){
                    $workPhones = $contact["workPhones"];
                    $workPhones = $this->__NumberFormater($workPhones);
                    $code = intval(substr($workPhones, 0, 3));
                    if($code){
                        $city = City::where("area_code", $code)->first();
                        if($city){
                            $recordState = State::where("code", "LIKE", $city["state_code"])->first();
                            $state_id = $recordState->id;
                            $timezone_id = $recordState->timezone_id;
                            $recordTimezone = Timezone::where("id", $recordState->timezone_id)->first();
                            if($recordTimezone){
                                $timezone = $recordTimezone->timezone_type;
                            }
                        }
                    }
                }
                if(is_null($state_id) && $contact["homePhones"]){
                    $homePhones = $contact["homePhones"];
                    $homePhones = $this->__NumberFormater($homePhones);
                    $code = intval(substr($homePhones, 0, 3));
                    if($code){
                        $city = City::where("area_code", $code)->first();
                        if($city){
                            $recordState = State::where("code", "LIKE", $city["state_code"])->first();
                            $state_id = $recordState->id;
                            $timezone_id = $recordState->timezone_id;
                            $recordTimezone = Timezone::where("id", $recordState->timezone_id)->first();
                            if($recordTimezone){
                                $timezone = $recordTimezone->timezone_type;
                            }
                        }
                    }
                }
                if(is_null($state_id) && $contact["mobilePhones"]){
                    $mobilePhones = $contact["mobilePhones"];
                    $mobilePhones = $this->__NumberFormater($mobilePhones);
                    $code = intval(substr($mobilePhones, 0, 3));
                    if($code){
                        $city = City::where("area_code", $code)->first();
                        if($city){
                            $recordState = State::where("code", "LIKE", $city["state_code"])->first();
                            $state_id = $recordState->id;
                            $timezone_id = $recordState->timezone_id;
                            $recordTimezone = Timezone::where("id", $recordState->timezone_id)->first();
                            if($recordTimezone){
                                $timezone = $recordTimezone->timezone_type;
                            }
                        }
                    }
                }
            $city_id = null;
            // if(is_null($state_id))
            //     $city_id = null;
            // else{
            //     $city = $contact["city"];
            //     if($city){
            //         $recordCity = City::where("city", "LIKE", $city)->first();
            //         if($recordCity){
            //             $city_id = $recordCity->id;
            //         }else{
            //             $city_id = null;
            //         }
            //     }
            // }
            if(ContactTime::where("contact_id", $contact["record_id"])->count() > 0){
                ContactTime::where("contact_id", $contact["record_id"])
                ->update([
                    "contact_id" => $contact["record_id"],
                    "state_id" => $state_id,
                    "country_id" => $country_id,
                    "city_id" => $city_id,
                    "timezone_id" => $timezone_id,
                    "timezone" => $timezone
                ]);
            }else{
                ContactTime::create([
                    "contact_id" => $contact["record_id"],
                    "state_id" => $state_id,
                    "country_id" => $country_id,
                    "city_id" => $city_id,
                    "timezone_id" => $timezone_id,
                    "timezone" => $timezone
                ]);
            }
            $id = $contact->id;
            $count++;
        }
        return view("get_state_city_timezone", compact('id', 'count'));
    }
    public function states01($id){
        
        $contacts = Contacts::where("country", "LIKE", "united states")->where("id", ">", $id)->orderBy("id", "asc")->limit(100)->get();
        $country_id = 228;
        $count = 0;
        foreach($contacts as $contact){
            $state = $contact["state"];
            $state_id = null;
            $timezone_id = null;
            $timezone = null;
            $code = null;
                if($contact["workPhones"]){
                    $workPhones = $contact["workPhones"];
                    $workPhones = $this->__NumberFormater($workPhones);
                    $code = intval(substr($workPhones, 0, 3));
                }elseif($contact["homePhones"]){
                    $homePhones = $contact["homePhones"];
                    $homePhones = $this->__NumberFormater($homePhones);
                    $code = intval(substr($homePhones, 0, 3));
                }elseif($contact["mobilePhones"]){
                    $mobilePhones = $contact["mobilePhones"];
                    $mobilePhones = $this->__NumberFormater($mobilePhones);
                    $code = intval(substr($mobilePhones, 0, 3));
                }
            if($code){
                $city = City::where("area_code", $code)->first();
                if($city){
                    $recordState = State::where("code", "LIKE", $city["state_code"])->first();
                    $state_id = $recordState->id;
                    $timezone_id = $recordState->timezone_id;
                    $recordTimezone = Timezone::where("id", $recordState->timezone_id)->first();
                    if($recordTimezone){
                        $timezone = $recordTimezone->timezone_type;
                    }
                }
            }
            $city_id = null;
            // if(is_null($state_id))
            //     $city_id = null;
            // else{
            //     $city = $contact["city"];
            //     if($city){
            //         $recordCity = City::where("city", "LIKE", $city)->first();
            //         if($recordCity){
            //             $city_id = $recordCity->id;
            //         }else{
            //             $city_id = null;
            //         }
            //     }
            // }
            if(ContactTime::where("contact_id", $contact["record_id"])->count() > 0){
                ContactTime::where("contact_id", $contact["record_id"])
                ->update([
                    "contact_id" => $contact["record_id"],
                    "state_id" => $state_id,
                    "country_id" => $country_id,
                    "city_id" => $city_id,
                    "timezone_id" => $timezone_id,
                    "timezone" => $timezone
                ]);
            }else{
                ContactTime::create([
                    "contact_id" => $contact["record_id"],
                    "state_id" => $state_id,
                    "country_id" => $country_id,
                    "city_id" => $city_id,
                    "timezone_id" => $timezone_id,
                    "timezone" => $timezone
                ]);
            }
            $id = $contact->id;
            $count++;
        }
        return view("get_state_city_timezone", compact('id', 'count'));
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
    function checkCity(){
        $records = MultiCityAreaCode::limit(100)->get();
        foreach($records as $record){
            if(City::where("city", "LIKE", $record["city"])->where("state_code", "LIKE", $record["state_code"])->count() == 0){
                City::create([
                    "area_code" => $record["area_code"],
                    "city" => $record["city"],
                    "state_code" => $record["state_code"],
                ]);
            }else{
                City::where("city", "LIKE", $record["city"])->update([
                    "area_code" => $record["area_code"]
                ]);
            }
            MultiCityAreaCode::where("id", $record["id"])->delete();
        }
        $count = City::count();
        return view("check_city", compact('count'));
    }

    function getStateId($id){
        if($id > 29100){
            die;
        }
        $records = City::where("id", ">", $id)->limit(100)->get();
        foreach($records as $record){
            $stateRecord = State::where("code", "LIKE", $record["state_code"])->first();
            if(strpos($record["city"], " ") !== false){
                $city_shortname = implode("-",explode(" ",strtolower($record["city"])));
            }else{
                $city_shortname = strtolower($record["city"]);
            }
            City::where("id", $record["id"])->update([
                "state_id" => $stateRecord["id"],
                "city_shortname" =>  $city_shortname
            ]);
            $id = $record["id"];
        }
        return view("get_state_id", compact("id"));
    }
}
