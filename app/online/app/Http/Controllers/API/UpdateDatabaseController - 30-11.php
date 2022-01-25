<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Contacts;

class UpdateDatabaseController extends Controller
{
    function index(Request $request){
        $fields = DB::getSchemaBuilder()->getColumnListing($request->input("tableName"));
        $non_changable = ['id', 'record_id', 'account_id', 'first_name', 'last_name', 'mobilePhones', 'homePhones', 'workPhones', 'voipPhones', 'otherPhones', 'emails', 'hnumber', 'mnumber', 'wnumber', 'old_stage', 'name', 'created_at', 'updated_at', 'email_opened', 'email_replied', 'email_bounced', 'email_clicked', 'email_delivered', 'dataset', 'mcall_attempts', 'mcall_received', 'hcall_attempts', 'hcall_received', 'wcall_attempts', 'wcall_received', 'last_outreach_email', "last_outreach_activity", "last_campaign", "last_export", "last_agent_dispo_time", "last_agent", "last_dispo", "dial_attempts", "last_update_at", "fivenine_created_at", "outreach_touched_at", "outreach_created_at", "websiteUrl1", "linkedInUrl"];
        return array_diff($fields, $non_changable);
    }
    function getTableData(Request $request){
        $field = $request->input("field");
        $tableName = $request->input("tableName");
        return DB::table($tableName)->selectRaw("$field as field, count($field) as total")->groupBy($field)->whereNotNull($field)->orderBy('field', 'asc')->get();
    }
    function meargeRecords(Request $request){
        //dd($request->all());
        $tableName = $request->input("tableName");
        $field = $request->input("field");
        $meargRecords = $request->input("meargRecords");
        $primary = $request->input("primary");
        foreach($meargRecords as $value){
            if($value != $primary){
                DB::table($tableName)->where($field, "LIKE", $value)->update([$field => $primary]);
            }
        }
        return [];
    }
    function updateRecords(Request $request){
        $tableName = $request->input("tableName");
        $field = $request->input("field");
        $updateRecords = $request->input("updateRecords");
        $update = $request->input("update");
        DB::table($tableName)->where($field, "LIKE", $updateRecords)->update([$field => $update]);
        return [];
    }

    public function displayNames()
    {
        return ['custom1' => 'Purchase Authorization',
                'custom2' => 'Department',
                'custom9' => 'Industry',
                'custom10' => 'Primary Industry',
                'custom11' => 'Company Revenue',
                'custom12' => 'Company Rev Range',
                'custom29' => 'Timezone Group'
            ];
    }
}
