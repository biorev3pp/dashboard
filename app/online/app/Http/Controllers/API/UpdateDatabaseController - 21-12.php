<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Contacts;

class UpdateDatabaseController extends Controller
{
    function getNullFields(Request $request){
        $nullFields = [];
        if(DB::table("temp_contacts")->count() == 0){
            $fields = DB::getSchemaBuilder()->getColumnListing("contact_customs");
            foreach($fields as $field){
                $count = DB::table("contact_customs")->whereNotNull($field)->count();
                if($count == 0){
                    $nullFields[] = $field;
                    DB::table("temp_contacts")->insert([ "field" =>  $field]);
                }
            }
        }
        $nullFields = DB::table("temp_contacts")->pluck("field")->toArray();
        return $nullFields;
    }
    function index(Request $request){
        $fields = DB::getSchemaBuilder()->getColumnListing($request->input("tableName"));
        $non_changable = ['id', 'record_id', 'account_id', 'first_name', 'last_name', 'mobilePhones', 'homePhones', 'workPhones', 'voipPhones', 'otherPhones', 'emails', 'hnumber', 'mnumber', 'wnumber', 'old_stage', 'name', 'created_at', 'updated_at', 'email_opened', 'email_replied', 'email_bounced', 'email_clicked', 'email_delivered', 'dataset', 'mcall_attempts', 'mcall_received', 'hcall_attempts', 'hcall_received', 'wcall_attempts', 'wcall_received', 'last_outreach_email', "last_outreach_activity", "last_campaign", "last_export", "last_agent_dispo_time", "last_agent", "last_dispo", "dial_attempts", "last_update_at", "fivenine_created_at", "outreach_touched_at", "outreach_created_at", "websiteUrl1", "linkedInUrl"];
        return array_diff($fields, $non_changable);
    }
    function getTableData(Request $request){
        $field = $request->input("field");
        $tableName = $request->input("tableName");
        $nullRecords = DB::table($tableName)
        ->whereNull($field)
        ->count();
        $emptyRecords = DB::table($tableName)
        ->where($field, "=", "")
        ->count();
        $records = DB::table($tableName)
        ->selectRaw("$field as field, count($field) as total")
        ->groupBy($field)
        ->orderBy('field', 'asc')
        ->get();
        $nrecords = [];
        foreach($records as $key => $value){
            $value = get_object_vars($value);
            if(is_null($value["field"])){
                $value["field"] = "Null";
                $value["total"] = $nullRecords;
                $nrecords[$key] = $value;
            }elseif($value["field"] == ""){
                $value["field"] = "Empty";
                $value["total"] = $emptyRecords;
                $nrecords[$key] = $value;
            }else{
                $nrecords[$key] = $value;
            }
        }
        if($field == 'stage'){
            $records = DB::table('stages')->select('name', 'oid')->get();
            $stage = [];
            foreach($records as $value){
                $value = get_object_vars($value);
                $stage[$value["oid"]] = $value["name"];
            }
        return ['results' => $nrecords, 'stage' => $stage];
        }
        return ['results' => $nrecords];
    }
    function meargeRecords(Request $request){
        $tableName = $request->input("tableName");
        $field = $request->input("field");
        $meargRecords = $request->input("meargRecords");
        $primary = $request->input("primary");
        if($primary == "Null" || $primary == "Empty"){
            if(in_array("Null", $meargRecords) || in_array("Empty", $meargRecords)){
                unset($meargRecords[array_search("Null", $meargRecords)]);
                unset($meargRecords[array_search("Empty", $meargRecords)]);
                foreach($meargRecords as $value){
                    DB::table($tableName)->where($field, "LIKE", $value)->update([$field => null]);
                }
                return [];
            }
        }
        unset($meargRecords[array_search($primary, $meargRecords)]);
        foreach($meargRecords as $value){
            if($value == "Null"){
                DB::table($tableName)->whereNull($field)->update([$field => $primary]);
            }elseif($value == "Empty"){
                DB::table($tableName)->where($field, "=", "")->update([$field => $primary]);
            }else{
                DB::table($tableName)->where($field, "LIKE", $value)->update([$field => $primary]);
            }
        }
        return [];
    }
    function updateRecords(Request $request){
        $tableName = $request->input("tableName");
        $field = $request->input("field");
        $meargRecords = $request->input("meargRecords");
        $update = $request->input("update");
        foreach($meargRecords as $value){
            if($value == "Null"){
                DB::table($tableName)->whereNull($field)->update([$field => $update]);
            }elseif($value == "Empty"){
                DB::table($tableName)->where($field, "=", "")->update([$field => $update]);
            }else{
                DB::table($tableName)->where($field, "LIKE", $value)->update([$field => $update]);
            }
        }
        return [];
    }    
    function resetRecords(Request $request){
        $tableName = $request->input("tableName");
        $field = $request->input("field");
        $meargRecords = $request->input("meargRecords");
        $update = $request->input("update");
        foreach($meargRecords as $value){
            if($value == "Null"){

            }elseif($value == "Empty"){
                DB::table($tableName)->where($field, "=", "")->update([$field => null]);
            }else{
                DB::table($tableName)->where($field, "LIKE", $value)->update([$field => null]);
            }
        }
        return [];
    }
    function transferRecords(Request $request){
        $tableName = $request->input("tableName");
        $field = $request->input("field");
        $meargRecords = $request->input("meargRecords");
        $nullField = $request->input("nullField");
        $newNullField = $request->input("newNullField");
        $fields = DB::getSchemaBuilder()->getColumnListing("contact_customs");
        if(in_array($newNullField, $fields)){
            return ["status" => "exists"];
        }
        if($newNullField){
            $result = DB::statement("ALTER TABLE 'contact_customs' ADD $newNullField VARCHAR(250) NULL DEFAULT NULL");
            foreach($meargRecords as $value){
                $records = DB::table($tableName)->select("record_id")->where($field, "LIKE", $value)->pluck("record_id");
                DB::table($tableName)->whereIn('record_id', $records)->update([ 
                    $field => null
                ]);
                DB::table("contact_customs")->whereIn('contact_id', $records)->update([ 
                    $newNullField => $value,
                ]);
            }
            return ["status" => "success"];
        }
        if(in_array("Null", $meargRecords)){
            unset($meargRecords[array_search("Null", $meargRecords)]);
        }
        if(in_array("Empty", $meargRecords)){
            unset($meargRecords[array_search("Empty", $meargRecords)]);
        }
        foreach($meargRecords as $value){
            $records = DB::table($tableName)->select("record_id")->where($field, "LIKE", $value)->pluck("record_id");
            DB::table($tableName)->whereIn('record_id', $records)->update([ 
                $field => null
            ]);
            DB::table("contact_customs")->whereIn('contact_id', $records)->update([ 
                $nullField => $value,
            ]);
        }
        DB::table("temp_contacts")->where('field', "LIKE", $nullField)->delete();
        return ["status" => "success"];
    }
    public function displayNames()
    {
        return [
                'custom1' => 'Purchase Authorization',
                'custom2' => 'Department',
                'custom9' => 'Industry',
                'custom10' => 'Primary Industry',
                'custom11' => 'Company Revenue',
                'custom12' => 'Company Rev Range',
                'custom29' => 'Timezone Group'
            ];
    }

    function getFieldBasedData(Request $request){
        $fieldName = $request->input("fieldName");
        $field = $request->input("field");
        if($fieldName == 'Null' || $fieldName == 'Empty'){
            return DB::table('contacts')->select('company', 'first_name', 'last_name', 'emails', 'mobilePhones', 'homePhones', 'workPhones')->whereNull($field)->get();    
        }
        return DB::table('contacts')->select('company', 'first_name', 'last_name', 'emails', 'mobilePhones', 'homePhones', 'workPhones')->where($field, "LIKE", $fieldName)->get();
    }

    function getCountBasedData(Request $request){
        $fieldNameContainer = $request->input("fieldNameContainer");
        $field = $request->input("field");
        return DB::table('contacts')->select('company', 'first_name', 'last_name', 'emails', 'mobilePhones', 'homePhones', 'workPhones')->whereIn($field, $fieldNameContainer)->get();
    }
}
