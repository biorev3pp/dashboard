<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Contacts;
use App\Models\ContactCustoms;

class ColumnAnalysisController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $fields = DB::getSchemaBuilder()->getColumnListing('contacts');
        $non_changable = ['id', 'record_id', 'account_id', 'first_name', 'last_name', 'f_first_name', 'f_last_name', 'mobilePhones', 'homePhones', 'workPhones', 'voipPhones', 'otherPhones', 'emails', 'hnumber', 'mnumber', 'wnumber', 'old_stage', 'name', 'created_at', 'updated_at', 'email_opened', 'email_replied', 'email_bounced', 'email_clicked', 'email_delivered', 'dataset', 'mcall_attempts', 'mcall_received', 'hcall_attempts', 'hcall_received', 'wcall_attempts', 'wcall_received', 'last_outreach_email', 'number1', 'number2', 'number3', 'number1type', 'number2type', 'number3type', 'number1call', 'number2call', 'number3call', 'ext1', 'ext2', 'ext3', "last_outreach_activity", "last_campaign", "last_export", "last_agent_dispo_time", "last_agent", "last_dispo", "dial_attempts", "last_update_at", "fivenine_created_at", "outreach_touched_at", "outreach_created_at", "websiteUrl1", "linkedInUrl"];
        $data = [];
        foreach(array_diff($fields, $non_changable) as $key => $value){
            $data[$key] = [
                'field' => $value,
                'empty' => Contacts::whereNull($value)->count(),
                'notempty' => Contacts::whereNotNull($value)->count()
            ];
        }
        return $data;
    }
    public function getDuplicateColumnHealth(Request $request){
        ini_set('memory_limit', '8192M');
        $fields = $request->fields;
        array_push($fields, 'Company');
        $all_fields = ['name', 'Business Email', 'Company', 'Title', 'Mobile Phone', 'Work Phone', 'Home Phone', 'Country', 'State', 'City', 'Zipcode'];
        $all_field_values = ['name', 'emails', 'company', 'title', 'mobilePhones', 'workPhones', 'homePhones', 'country', 'state', 'city', 'zip'];
        $data = [];
        if($request->qtype == 'or') {
            foreach($fields as $nfield){
                if(in_array($nfield, $all_fields)) {
                    $ff = [];
                    $field = $all_field_values[array_search($nfield, $all_fields)];
                    $duplicates = Contacts::addSelect($field)->addSelect(DB::raw('COUNT(*) as `count`'))->whereNotNull($field)->groupBy($field)->having('count', '>', 1)->orderBy('count', 'desc')->get();
                    $data[] = ['field' => $nfield, 'dfield' => $field, 'data' => $duplicates];
                }
            }
        } else {
            $ff = [];
            $duplicates = Contacts::select(DB::raw('COUNT(*) as `count`'));
            foreach($fields as $nfield) {
                $field = $all_field_values[array_search($nfield, $all_fields)];
                $duplicates->addSelect($field);
                array_push($ff, $field);
            }
            
            foreach($fields as $nfield) {
                $field = $all_field_values[array_search($nfield, $all_fields)];
                $duplicates->where(function($inq) use($field) {
                    $inq->where($field, '!=', '');
                    $inq->where($field, '!=', ' ');
                    $inq->where($field, '!=', null);
                });  
            }

            foreach($fields as $nfield) {
                $field = $all_field_values[array_search($nfield, $all_fields)];
                $duplicates->groupBy($field);
            }
            $duplicates = $duplicates->having('count', '>', 1)->orderBy('count', 'desc')->get();
            $data = ['field' => 'All Fields', 'dfield' => 'And Conditions', 'fields' => $ff, 'data' => $duplicates->toArray()];
        }
        //  dd($data);
        return $data;
    }

    public function getNonEmptyColumnHealth(Request $request){
        //dd($request->all());
        $values = [ 'first name' => 'first_name', 'last name' => 'last_name', 'Business Email' => 'emails', 'Company' => 'company', 'Title' => 'title', 'Mobile Phone' => 'mobilePhones', 'Work Phone' => 'workPhones', 'Home Phone' => 'homePhones', 'Country' => 'country', 'State' => 'state', 'City' => 'city', 'Zipcode' => 'zip', 'Timezone' => 'timeZone', 'Timezone Group' => 'custom29', 'Purchase Authorization' => 'custom1', 'Stage' => 'stage', 'Industry' => 'custom9', 'Primary Industry' => 'custom10', 'Department' => 'custom2'];
        $fields = $request->input('fields');
        $queryType = $request->input('qtype'); // and/or
        $data = [];
        if($queryType == 'and'){
            
           $empty =  Contacts::select('record_id', 'first_name', 'last_name')->when($fields, function($query) use ($fields, $values){
                foreach($fields as $field){
                    if($values[$field] == 'state'){
                        $query->where($values[$field], '=', 0);
                    }else{
                        $query->whereNull($values[$field]);
                    }
                }
            })->get();
            return $empty;
        }else{
            $data = [];
            foreach($fields as $field){
                $data[$field] = DB::table('contacts')->select('record_id', 'first_name', 'last_name')->when($field, function($query) use ($values, $field){
                    if($values[$field] == 'stage'){
                        $query->where($values[$field], '=', 0);
                    }else{
                        $query->whereNull($values[$field]);
                    }

                })->get();
            }
            return $data;
        }
    }

    public function getProspectsFieldMissingData(){
        $fields = ['first_name', 'last_name', 'emails', 'mobilePhones', 'company', 'country', 'companyIndustry', 'timeZone', 'stage'];
        $data = [];
        $total = Contacts::count();
        foreach($fields as $field){
            if($field == 'stage'){
                $count = Contacts::where($field, '=', 0)->orWhereNull('stage')->count();
            }else{
                $count = Contacts::where(function($inq) use($field) {
                            $inq->where($field, '-');
                            $inq->orWhere($field, '=', '');
                            $inq->orWhere($field, null);
                        })->count();
            }
            $data[] = ['count' => number_format($count, 0), 'percent' => number_format(($count/$total)*100, 0)];
        }
        return $data;
    }

    public function getCompanyRevenueRange(){
        $data = DB::select("SELECT count(*) as countr, `custom12` FROM `contacts` where `custom12` is not null  group BY `custom12` ORDER BY countr DESC LIMIT 5");
        $totalContacts = Contacts::count();
        $label = [];
        $series = [];
        $total_count = 0;
        foreach($data as $value){
            $value = get_object_vars($value);
            $label[] = $value['custom12'];
            $series[] = $value["countr"];
            $total_count+= $value["countr"];
        }
        $label[count($label)] = 'Others';
        $series[count($series)] = $totalContacts - $total_count;
        return ['label' => $label, 'series' => $series];
    }
    public function getManagementLevel(){
        $data = DB::select("SELECT count(*) as countr, `custom1` FROM `contacts` where `custom1` is not null  group BY `custom1` ORDER BY countr DESC LIMIT 5");
        $totalContacts = Contacts::count();
        $label = [];
        $series = [];
        $total_count = 0;
        foreach($data as $value){
            $value = get_object_vars($value);
            $label[] = $value['custom1'];
            $series[] = $value["countr"];
            $total_count+= $value["countr"];
        }
        $label[count($label)] = 'Others';
        $series[count($series)] = $totalContacts - $total_count;
        return ['label' => $label, 'series' => $series];
    }
    public function getJobFunctions(){
        $data = DB::select("SELECT count(*) as countr, `custom2` FROM `contact_customs` where `custom2` is not null  group BY `custom2` ORDER BY countr DESC LIMIT 6");
        $totalContacts = DB::table('contact_customs')->count();
        $label = [];
        foreach($data as $value){
            $value = get_object_vars($value);
            $label[] = ['value' => $value['custom2'], 'percent' => intval(($value['countr']*100)/$totalContacts) .'%', 'count' => $value['countr']];
        }
        return $label;
    }
    public function getJobTitles(){
        $data = DB::select("SELECT count(*) as countr, `title` FROM `contacts` where `title` is not null  group BY `title` ORDER BY countr DESC LIMIT 6");
        $totalContacts = Contacts::count();
        $label = [];
        foreach($data as $value){
            $value = get_object_vars($value);
            $label[] = ['value' => $value['title'], 'percent' => intval(($value['countr']*100)/$totalContacts) .'%', 'count' => $value['countr']];
        }
        return $label;
    }
    public function getCompanyIndustry(){
        $ndata = DB::select("SELECT count(*) as countr, `companyIndustry` FROM `contacts` where `companyIndustry` is not null  group BY `companyIndustry` ORDER BY countr DESC LIMIT 6");
        $totalContacts = Contacts::count();
        $label = [];
        foreach($ndata as $value){
            $value = get_object_vars($value);
            $label[] = ['value' => $value['companyIndustry'], 'percent' => intval(($value['countr']*100)/$totalContacts) .'%', 'count' => $value['countr']];
        }
        return $label;
    }

    public function getTouchPointsData(){
        $fields = ['emails', 'mobilePhones', 'workPhones', 'homePhones', 'company'];
        $and_count = Contacts::when($fields, function($query) use ($fields){
            foreach($fields as $field){
                $query->where(function($inq) use($field) {
                    $inq->where($field, '-');
                    $inq->orWhere($field, '=', '');
                    $inq->orWhere($field, null);
                }); 
            }
        })->count();
        $or_count = Contacts::when($fields, function($query) use ($fields){
            foreach($fields as $field){
                $query->orWhere(function($inq) use($field) {
                    $inq->where($field, '-');
                    $inq->orWhere($field, '=', '');
                    $inq->orWhere($field, null);
                }); 
            }
        })->count();
        $total = Contacts::count();
        return ['and_count' => $and_count, 'or_count' => $or_count, 'total_count' => $total];
    }

    public function getTouchPointsDataOthers(){
        //Stage, Industry, Purchase Authorization, Tag, Title, Timezone, Country, State, City and zipcode
        $fields = ['stage', 'companyIndustry', 'custom1', 'outreach_tag', 'title', 'timeZone', 'country', 'state', 'city', 'zip'];
        $and_count = Contacts::when($fields, function($query) use ($fields){
            foreach($fields as $field){
                if($field == 'stage'){
                    $query->where($field, '=', 0);
                }else{
                    $query->where(function($inq) use($field) {
                        $inq->where($field, '-');
                        $inq->orWhere($field, '=', '');
                        $inq->orWhere($field, null);
                    }); 
                }
            }
        })->count();
        $or_count = Contacts::when($fields, function($query) use ($fields){
            foreach($fields as $field){
                if($field == 'stage'){
                    $query->where($field, '=', 0);
                }else{
                    $query->orWhere(function($inq) use($field) {
                        $inq->where($field, '-');
                        $inq->orWhere($field, '=', '');
                        $inq->orWhere($field, null);
                    }); 
                }
            }
        })->count();
        $total = Contacts::count();
        return ['and_count' => $and_count, 'or_count' => $or_count, 'total_count' => $total];
    }
    public function getRelatedProspects(Request $request)
    {
        $record_ids = array_column($request->data, $request->active_field);
        $records = Contacts::select('contacts.record_id', 'contacts.first_name', 'contacts.last_name', 'contacts.title', 'contacts.company', 'contacts.mobilePhones', 'contacts.workPhones', 'contacts.homePhones', 'contacts.emails', 'contacts.source', 'stages.id as stage', 'stages.name as stage_name', 'stages.css as stage_css')->leftjoin('stages', 'stages.oid', '=', 'contacts.stage')->whereIn($request->active_field, $record_ids)->orderBy($request->active_field, 'asc')->get();
        return $records;
    }
    public function getRelatedAndProspects(Request $request)
    {
        $data = $request->data;
        unset($data['count']);
        $records = Contacts::select('contacts.record_id', 'contacts.first_name', 'contacts.last_name', 'contacts.title', 'contacts.company', 'contacts.mobilePhones', 'contacts.workPhones', 'contacts.homePhones', 'contacts.emails', 'contacts.source', 'stages.id as stage', 'stages.name as stage_name', 'stages.css as stage_css')
        ->addSelect(['custom4' => DB::table('contact_customs')->selectRaw('custom4')->whereColumn('contact_customs.contact_id', 'contacts.record_id')])->leftjoin('stages', 'stages.oid', '=', 'contacts.stage');
        
        foreach ($data as $key => $value) {
            if($value == '' || $value == null) {
                $records->where(function($inq) use($key) {
                    $inq->where('contacts.'.$key, '-');
                    $inq->orWhere('contacts.'.$key, '=', '');
                    $inq->orWhere('contacts.'.$key, '=', ' ');
                    $inq->orWhere('contacts.'.$key, null);
                });  
            } else {
                $records->where('contacts.'.$key, '=', $value);
            }
        }
        $records =  $records->get();
        return $records;
    }

    public function getRelatedProspectsA(Request $request)
    {
        ini_set('memory_limit', '8192M');
        $record_ids = array_column($request->data, 'record_id');
        $records = Contacts::select('contacts.id', 'contacts.record_id', 'contacts.first_name', 'contacts.last_name', 'contacts.title', 'contacts.company', 'contacts.source', 'contacts.mobilePhones', 'contacts.workPhones', 'contacts.homePhones', 'contacts.emails', 'contacts.outreach_touched_at', 'contacts.outreach_created_at', 'stages.id as stage', 'stages.name as stage_name', 'stages.css as stage_css', 'contact_customs.custom4 as supplemental_email')->leftjoin('stages', 'stages.oid', '=', 'contacts.stage')->whereIn('contacts.record_id', $record_ids)->leftjoin('contact_customs', 'contact_customs.contact_id', '=', 'contacts.record_id')->get();
        return $records;
    }
    public function getRelatedAndProspectsA(Request $request)
    {
        ini_set('memory_limit', '8192M');
        $data = $request->data;
        unset($data['count']);
        $records = Contacts::select('contacts.id', 'contacts.record_id', 'contacts.first_name', 'contacts.last_name', 'contacts.title', 'contacts.company', 'contacts.mobilePhones', 'contacts.workPhones', 'contacts.homePhones', 'contacts.emails', 'contacts.outreach_touched_at', 'contacts.outreach_created_at', 'stages.id as stage', 'stages.name as stage_name', 'stages.css as stage_css')->leftjoin('stages', 'stages.oid', '=', 'contacts.stage');
        
        foreach ($data as $key => $value) {
            if($value == '') {
                $records->whereNull($key);
            } else {
                $records->where($key, '=', $value);
            }
        }
        $records =  $records->get();
        return $records;
    }
    public function updateEmptyProspects(Request $request){
        foreach($request->input('records')as $contact){
            $record_id = $contact['record_id'];
            unset($contact['record_id']);
            Contacts::where('record_id', $record_id)->update($contact);
            $cc = [];
            if(array_key_exists('custom1', $contact)){
                $cc['custom1'] = $contact['custom1'];
            }
            if(array_key_exists('custom2', $contact)){
                $cc['custom2'] = $contact['custom2'];
            }
            if(array_key_exists('custom9', $contact)){
                $cc['custom9'] = $contact['custom9'];
            }
            if(array_key_exists('custom10', $contact)){
                $cc['custom10'] = $contact['custom10'];
            }
            if(array_key_exists('custom29', $contact)){
                $cc['custom29'] = $contact['custom29'];
            }
            if(count($cc) > 0){
                ContactCustoms::where('contact_id', $record_id)->update($cc);
            }
        }
        return ['status' => 'success'];
    }
    public function getUploadedProspects(Request $request){
        $ddata = $this->getNonEmptyColumnHealth($request);
        if($request->input('qtype') == 'and'){
            $recordIds = [];
            foreach($ddata as $value){
                $recordIds[] = get_object_vars($value);
            }
            $record_ids = array_column($recordIds, 'record_id');
        }else{
            $recordIds = [];
            foreach($ddata[$request->input('f')] as $value){
                $recordIds[] = get_object_vars($value);
            }
            $record_ids = array_column($recordIds, 'record_id');
        }
        $pdata = Contacts::select('contacts.id', 'contacts.record_id', 'contacts.first_name', 'contacts.last_name', 'contacts.title', 'contacts.company', 'contacts.source', 'contacts.mobilePhones', 'contacts.workPhones', 'contacts.homePhones', 'contacts.emails', 'contacts.outreach_touched_at', 'contacts.outreach_created_at', 'stages.id as stage', 'stages.name as stage_name', 'stages.css as stage_css', 'contact_customs.custom4 as supplemental_email')->leftjoin('stages', 'stages.oid', '=', 'contacts.stage')->whereIn('contacts.record_id', $record_ids)->leftjoin('contact_customs', 'contact_customs.contact_id', '=', 'contacts.record_id')->get();
        return ['ddata' => $ddata, 'pdata' => $pdata] ;
    }
}
