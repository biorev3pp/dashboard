<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Contacts;
use App\Models\ContactCustoms;
use App\Jobs\ProspectUpdate;

class UpdateDatabaseController extends Controller
{
    function getNullFields(Request $request){
        $nullFields = [];
        $fields = DB::getSchemaBuilder()->getColumnListing('contact_customs');
        foreach($fields as $field)
        {
            $count = DB::table('contact_customs')->whereNotNull($field)->count();
            if($count == 0) {
                $nullFields[] = $field;
            }
        }
        $nullFields = DB::table('contact_fields')->whereIn('field', $nullFields)->get()->groupBy('group_name');
        return $nullFields;
    }
    function getNullFieldsAll(Request $request){
        $nullFields = DB::table('contact_fields')->get()->groupBy('group_name');
        return $nullFields;
    }
    function getOverwriteInfo(Request $request){
        $allRecordsContainer = $request->input('allRecordsContainer');
        $nullField = $request->input('nullField');
        $field = $request->input('field');
        $nullFields = $request->input('nullFields');
        $nullField = $request->input('nullField');
        $transferTable = DB::table('contact_fields')->where('field', $nullField)->first();
        $transferTable = $transferTable->table_name;
        
        if($transferTable == 'contacts'){
            $records = Contacts::whereIn($field, $allRecordsContainer)->select('record_id')->get();
            $recordIds = $records->pluck('record_id');
            $nullValues = Contacts::whereIn('record_id', $recordIds)->whereNull($nullField)->count();
            $overwrite = Contacts::whereIn('record_id', $recordIds)->whereNotNull($nullField)->count();
        }
        if($transferTable == 'contact_customs'){
            $records = Contacts::whereIn($field, $allRecordsContainer)->select('record_id')->get();
            $recordIds = $records->pluck('record_id'); //dd($recordIds);
            $nullValues = ContactCustoms::whereIn('contact_id', $recordIds)->whereNull($nullField)->count();
            $overwrite = ContactCustoms::whereIn('contact_id', $recordIds)->whereNotNull($nullField)->count();
            
        }
        return ["nullValues" => $nullValues, "overwrite" => $overwrite, 'recordIds' => $recordIds];
    }
    function index(Request $request){
        $fields = DB::getSchemaBuilder()->getColumnListing($request->input("tableName"));
        $non_changable = ['id', 'record_id', 'label', 'account_id', 'first_name', 'last_name', 'f_first_name', 'f_last_name', 'mobilePhones', 'homePhones', 'workPhones', 'voipPhones', 'otherPhones', 'emails', 'hnumber', 'mnumber', 'wnumber', 'old_stage', 'name', 'created_at', 'updated_at', 'email_opened', 'email_replied', 'email_bounced', 'email_clicked', 'email_delivered', 'dataset', 'mcall_attempts', 'mcall_received', 'hcall_attempts', 'hcall_received', 'wcall_attempts', 'wcall_received', 'last_outreach_email', 'number1', 'number2', 'number3', 'number1type', 'number2type', 'number3type', 'number1call', 'number2call', 'number3call', 'ext1', 'ext2', 'ext3', "last_outreach_activity", "last_campaign", "last_export", "last_agent_dispo_time", "last_agent", "last_dispo", "dial_attempts", "last_update_at", "fivenine_created_at", "outreach_touched_at", "outreach_created_at", "websiteUrl1", "linkedInUrl"];
        return array_diff($fields, $non_changable);
    }
    function getTableData(Request $request){
        $field = $request->input("field");
        $tableName = $request->input("tableName");
        $allStage = $request->input('allStage');
        if($allStage && $field != 'stage'){
            $nullRecords = DB::table('contacts')
                ->whereNull($field)
                ->where('stage', $allStage)
                ->count();
            $emptyRecords = DB::table('contacts')
                ->where($field, "=", "")
                ->where('stage', $allStage)
                ->count();
            $records = DB::table('contacts')
                ->selectRaw("$field as field, count($field) as total")
                ->where('stage', $allStage)
                ->groupBy($field)
                ->orderBy('field', 'asc')
                ->get();
        }else{
            $nullRecords = DB::table('contacts')
                ->whereNull($field)
                ->count();
            $emptyRecords = DB::table('contacts')
                ->where($field, "=", "")
                ->count();
            $records = DB::table('contacts')
                ->selectRaw("$field as field, count($field) as total")
                ->groupBy($field)
                ->orderBy('field', 'asc')
                ->get();
        }
        
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
                    DB::table('contacts')->where($field, "LIKE", $value)->update([$field => null]);
                }
                return [];
            }
        }
        unset($meargRecords[array_search($primary, $meargRecords)]);
        foreach($meargRecords as $value){
            if($value == "Null"){
                DB::table('contacts')->whereNull($field)->update([$field => $primary]);
            }elseif($value == "Empty"){
                DB::table('contacts')->where($field, "=", "")->update([$field => $primary]);
            }else{
                DB::table('contacts')->where($field, "LIKE", $value)->update([$field => $primary]);
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
                DB::table('contacts')->whereNull($field)->update([$field => $update]);
            }elseif($value == "Empty"){
                DB::table('contacts')->where($field, "=", "")->update([$field => $update]);
            }else{
                DB::table('contacts')->where($field, "LIKE", $value)->update([$field => $update]);
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
                DB::table('contacts')->where($field, "=", "")->update([$field => null]);
            }else{
                DB::table('contacts')->where($field, "LIKE", $value)->update([$field => null]);
            }
        }
        return [];
    }
    function transferRecords(Request $request)
    {
        $tableName = $request->input("tableName");
        $field = $request->input("field");
        $meargRecords = $request->input("meargRecords");
        $nullField = $request->input("nullField");//dropdown field data
        $newNullField = $request->input("newNullField");//input field data
        $emptySet = $request->input("emptySet");
        $allSet = $request->input("allSet");
        $newSet = $request->input("newSet");
        $copyMoveType = $request->input("copyMoveType"); // move copy        
        $transferActionType = $request->input("transferActionType"); // overwrite skip
        $nullFields = $request->input("nullFields"); 
        $fields = DB::getSchemaBuilder()->getColumnListing("contact_customs");

        if(in_array($newNullField, $fields)){
            return ["status" => "exists"];
        }

        if($newNullField){
            $result = DB::statement("ALTER TABLE 'contact_customs' ADD $newNullField VARCHAR(250) NULL DEFAULT NULL");
            foreach($meargRecords as $value){
                $records = DB::table('contacts')->select("record_id")->where($field, "LIKE", $value)->pluck("record_id");
                DB::table('contacts')->whereIn('record_id', $records)->update([ 
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
        $transferTable = '';
        $transferTable = DB::table('contact_fields')->where('field', $nullField)->first();
        $transferTable = $transferTable->table_name;

        $matchContacts = ['custom1', 'custom2', 'custom9', 'custom10', 'custom11', 'custom12'];

        foreach($meargRecords as $value){
            $records = DB::table('contacts')->where($field, "LIKE", $value)->pluck("record_id");
            if($emptySet){
                if($copyMoveType == 'move'){
                    DB::table('contacts')->whereIn('record_id', $records)->update([ 
                        $field => null
                    ]);
                    $this->mulJobs($records, $field, null);
                }
                DB::table("contact_customs")->whereIn('contact_id', $records)->update([ 
                    $nullField => $value,
                ]);
                $this->mulJobs($records, $nullField, $value);
                if(in_array($nullField, $matchContacts)){
                    DB::table('contacts')->whereIn('record_id', $records)->update([ 
                        $nullField => $value,
                    ]);
                }
            }elseif($allSet){
                if($transferActionType == 'overwrite'){
                    if($copyMoveType == 'copy'){
                        if($transferTable == 'contacts'){
                            DB::table('contacts')->whereIn('record_id', $records)->update([ 
                                $nullField => $value,
                            ]);
                            $this->mulJobs($records, $nullField, $value);
                        } elseif($transferTable == 'contact_customs') {
                            DB::table("contact_customs")->whereIn('contact_id', $records)->update([ 
                                $nullField => $value,
                            ]);
                            $this->mulJobs($records, $nullField, $value);
                            if(in_array($nullField, $matchContacts)){
                                DB::table('contacts')->whereIn('record_id', $records)->update([ 
                                    $nullField => $value,
                                ]);
                            }
                        }
                    } elseif($copyMoveType == 'move') {
                        if($transferTable == 'contacts'){
                            DB::table('contacts')->whereIn('record_id', $records)->update([
                                $field => null
                            ]);
                            $this->mulJobs($records, $field, null);
                            DB::table('contacts')->whereIn('record_id', $records)->update([
                                $nullField => $value,
                            ]);
                            $this->mulJobs($records, $nullField, $value);
                            
                        } elseif($transferTable == 'contact_customs') {
                            if(in_array($nullField, $matchContacts) && in_array($field, $matchContacts)){

                                DB::table('contacts')->whereIn('record_id', $records)->update([ 
                                    $nullField => $value,
                                ]);
                                $this->mulJobs($records, $nullField, $value);
                                DB::table('contacts')->whereIn('record_id', $records)->update([ 
                                    $field => null,
                                ]);
                                $this->mulJobs($records, $field, null);
                                DB::table('contact_customs')->whereIn('contact_id', $records)->update([ 
                                    $nullField => $value,
                                ]);
                                $this->mulJobs($records, $nullField, $value);
                                DB::table('contact_customs')->whereIn('contact_id', $records)->update([ 
                                    $field => null,
                                ]);
                                $this->mulJobs($records, $field, null);
                            } elseif(in_array($nullField, $matchContacts)) {
                                DB::table('contacts')->whereIn('record_id', $records)->update([ 
                                    $field => null
                                ]);
                                $this->mulJobs($records, $field, null);
                                DB::table('contacts')->whereIn('record_id', $records)->update([ 
                                    $nullField => $value
                                ]);
                                $this->mulJobs($records, $nullField, $value);
                                DB::table('contact_customs')->whereIn('contact_id', $records)->update([ 
                                    $nullField => $value
                                ]);
                                $this->mulJobs($records, $nullField, $value);
                            } elseif(in_array($field, $matchContacts)){
                                DB::table('contacts')->whereIn('record_id', $records)->update([ 
                                    $field => null
                                ]);
                                $this->mulJobs($records, $field, null);
                                DB::table('contact_customs')->whereIn('contact_id', $records)->update([ 
                                    $field => null,
                                ]);
                                $this->mulJobs($records, $field, null);
                                DB::table('contact_customs')->whereIn('contact_id', $records)->update([ 
                                    $nullField => $value,
                                ]);
                                $this->mulJobs($records, $nullField, $value);
                            }
                        }
                    }
                }elseif($transferActionType == 'skip'){
                    if($copyMoveType == 'copy'){
                        $rids = Contacts::where($field, "LIKE", $value)->pluck('record_id')->toArray();
                        
                        if($transferTable == 'contacts'){
                            foreach($rids as $rid){
                                $c = DB::table("contacts")->where('record_id', $rid)->first();
                                if(is_null($c->$nullField)){
                                    DB::table("contacts")->where('record_id', $rid)->update([ 
                                        $nullField => $value,
                                    ]);
                                    $this->singleJobs($rid, $nullField, $value);
                                }
                            }
                        } elseif($transferTable == 'contact_customs') {
                            foreach($rids as $rid){
                                if(in_array($nullField, $matchContacts)) {
                                    $c = DB::table('contacts')->where('record_id', $rid)->first();
                                    if(is_null($c->$nullField)){
                                        DB::table('contacts')->where('record_id', $rid)->update([
                                            $nullField => $value    
                                        ]);
                                        $this->singleJobs($rid, $nullField, $value);
                                    }
                                } 
                                $cc = DB::table('contact_customs')->where('contact_id', $rid)->first();
                                if(is_null($cc->$nullField)){
                                    DB::table('contact_customs')->where('contact_id', $rid)->update([
                                        $nullField => $value    
                                    ]);
                                    $this->singleJobs($rid, $nullField, $value);
                                }
                            }
                        }
                    }elseif($copyMoveType == 'move'){
                        $rids = Contacts::where($field, "LIKE", $value)->select("record_id")->get();
                        $rids = $rids->pluck('record_id');
                        DB::table('contacts')->whereIn('record_id', $records)->update([ 
                            $field => null
                        ]);
                        $this->mulJobs($records, $field, null);
                        if($transferTable == 'contacts'){
                            foreach($rids as $rid){
                                $c = DB::table("contacts")->where('record_id', $rid)->first();
                                if(is_null($c->$nullField)){
                                    DB::table("contacts")->where('record_id', $rid)->update([ 
                                        $nullField => $value,
                                    ]);
                                    $this->singleJobs($rid, $nullField, $value);
                                }
                            }
                        } elseif($transferTable == 'contact_customs') {
                            foreach($rids as $rid){
                                if(in_array($nullField, $matchContacts) && in_array($field, $matchContacts)){
                                    $c = DB::table('contacts')->where('record_id', $rid)->first();
                                    if(is_null($c->$nullField)){
                                        DB::table('contacts')->where('record_id', $rid)->update([ 
                                            $nullField => $value,
                                        ]);
                                        $this->singleJobs($record_id, $nullField, $value);
                                    }
                                    DB::table('contacts')->where('record_id', $rid)->update([ 
                                        $field => null,
                                    ]);
                                    $this->singleJobs($record_id, $field, null);
                                    $cc = DB::table('contact_customs')->where('contact_id', $rid)->first();
                                    if(is_null($cc->$nullField)){
                                        DB::table('contact_customs')->where('contact_id', $rid)->update([ 
                                            $nullField => $value,
                                        ]);
                                        $this->singleJobs($record_id, $nullField, $value);
                                    }
                                    DB::table('contact_customs')->where('contact_id', $rid)->update([ 
                                        $field => null,
                                    ]);
                                    $this->singleJobs($record_id, $field, null);
                                } elseif(in_array($nullField, $matchContacts)) {
                                    DB::table('contacts')->where('record_id', $rid)->update([ 
                                        $field => null
                                    ]);
                                    $this->singleJobs($record_id, $field, null);
                                    $c = DB::table('contacts')->where('record_id', $rid)->first();
                                    if(is_null($c->$nullField)){
                                        DB::table('contacts')->where('record_id', $rid)->update([ 
                                            $nullField => $value
                                        ]);
                                        $this->singleJobs($record_id, $nullField, $value);
                                    }
                                    $cc = DB::table('contact_customs')->where('contact_id', $rid)->first();
                                    if(is_null($cc->$nullField)){
                                        DB::table('contact_customs')->where('contact_id', $rid)->update([ 
                                            $nullField => $value
                                        ]);
                                        $this->singleJobs($record_id, $nullField, $value);
                                    }
                                } elseif(in_array($field, $matchContacts)){
                                    DB::table('contacts')->where('record_id', $rid)->update([ 
                                        $field => null
                                    ]);
                                    $this->singleJobs($record_id, $field, null);
                                    DB::table('contact_customs')->where('contact_id', $rid)->update([ 
                                        $field => null,
                                    ]);
                                    $this->singleJobs($record_id, $field, null);
                                    $cc = DB::table('contact_customs')->where('contact_id', $rid)->first();
                                    if(is_null($cc->$nullField)){
                                        DB::table('contact_customs')->where('contact_id', $rid)->update([ 
                                            $nullField => $value,
                                        ]);
                                        $this->singleJobs($record_id, $nullField, $value);
                                    }
                                }
                            }
                        }
                    }
                }

            }
        }
        Artisan::call('queue:work --stop-when-empty', []);
        DB::table("temp_contacts")->where('field', "LIKE", $nullField)->delete();
        return ["status" => "success"];
    }
    public function singleJobs($record_id, $field, $value){
        ProspectUpdate::dispatch(['record_id' => $record_id, 'field' => $field, 'value' => $value])->onConnection('database');
    }
    public function mulJobs($records, $nullField, $value){
        foreach($records as $record_id){
            ProspectUpdate::dispatch(['record_id' => $record_id, 'field' => $nullField, 'value' => $value])->onConnection('database');
        }
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
        $allStage = $request->input('allStage');

        if($fieldName == 'Null' || $fieldName == 'Empty'){
            return DB::table('contacts')->select('company', 'first_name', 'last_name', 'emails', 'mobilePhones', 'homePhones', 'workPhones')->whereNull($field)->when($allStage, function($query, $allStage){
                return $query->where('stage', $allStage);
            })->get();
        }
        if($field == 'stage'){
            $stage = get_object_vars(DB::table('stages')->where('name', 'LIKE', $fieldName)->first());
            return DB::table('contacts')->select('company', 'first_name', 'last_name', 'emails', 'mobilePhones', 'homePhones', 'workPhones')->where($field, "=", $stage["oid"])->get();
        }
        return DB::table('contacts')->select('company', 'first_name', 'last_name', 'emails', 'mobilePhones', 'homePhones', 'workPhones')->where($field, "LIKE", $fieldName)->when($allStage, function($query, $allStage){
            return $query->where('stage', $allStage);
        })->get();
    }
    function getCountBasedData(Request $request){
        $fieldNameContainer = $request->input("fieldNameContainer");
        $fieldName = $request->input("fieldName");
        $field = $request->input("field");
        $allStage = $request->input('allStage');

        if($field == 'stage'){
            if(count($fieldNameContainer) > 0){
                return DB::table('contacts')->select('id', 'company', 'first_name', 'last_name', 'emails', 'mobilePhones', 'homePhones', 'workPhones')->whereIn($field, $fieldNameContainer)->get();
            }
            $stage = get_object_vars(DB::table('stages')->where('name', 'LIKE', $fieldName)->first());
            return DB::table('contacts')->select('company', 'first_name', 'last_name', 'emails', 'mobilePhones', 'homePhones', 'workPhones')->where($field, "=", $stage["oid"])->get();
        }
        return DB::table('contacts')->select('company', 'first_name', 'last_name', 'emails', 'mobilePhones', 'homePhones', 'workPhones')->whereIn($field, $fieldNameContainer)->when($allStage, function($query, $allStage){
            return $query->where('stage', $allStage);
        })->get();
    }

    public function refineTagRecords(Request $request) 
    {
        $newTags = $request->tagRecords;
        $field = $request->field;
        $meargRecords = $request->meargRecords;
        foreach($meargRecords as $value) {
            $oldTags = explode(',', $value);
            $finalTags = array_intersect($newTags, $oldTags);
            $rids = Contacts::where($field, "LIKE", $value)->pluck('record_id')->toArray();
            Contacts::where($field, "LIKE", $value)->update([$field => implode(',', $finalTags)]);
            $this->mulJobs($rids, $field, implode(',', $finalTags));
        }
        \Artisan::call('queue:work --stop-when-empty', []);
        return ['status' => 'success', 'mrg' => $request->meargRecords];
    }
}
