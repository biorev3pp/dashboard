<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contacts;
use App\Models\DatasetGroups;
use Illuminate\Support\Facades\DB;
use App\Models\FivenineCallLogs;

class DatasetsTestController extends Controller
{
    public function __construct()
    {
       // $this->middleware('auth');
    }
    public function rundatasets(){ 
                
        $recordPerPage = 10;
        $conditionArray = [
        				    [
                              "type" =>  "textbox",
                              "condition" =>  "first_name",
                              "conditionText" =>  "First Name",
                              "formula" =>  "is",
                              "textCondition" =>  "chad",
                              "oldformula" =>  "is,is not,starts with,is empty,is not empty,contains,ends with",
                              "textConditionLabel" =>  "First Name is chad",
                              "api" =>  ""
                            ],
                            [
                              "type" =>  "email",
                              "condition" =>  "bemails",
                              "conditionText" =>  "Business Email",
                              "formula" =>  "all",
                              "textCondition" =>  "delivered, clicked, opened, bounced, replied",
                              "oldformula" =>  "delivered,clicked,opened,bounced,replied",
                              "textConditionLabel" =>  "Business Email with delivered,  and clicked,  and opened,  and bounced,  and replied"
                            ]
                        ];
        $search = '';
        $q = Contacts::select('contacts.id', 'contacts.record_id', 'contacts.first_name', 'contacts.last_name', 'contacts.designation', 'contacts.company', 'contacts.mobilePhones', 'contacts.workPhones', 'contacts.otherPhones', 'contacts.email', 'contacts.supplemental_email', 'contacts.emails', 'contacts.outreach_touched_at', 'contacts.outreach_created_at', 'contacts.last_agent_dispo_time', 'contacts.last_agent', 'stages.id as stage', 'stages.stage as stage_name', 'stages.css as stage_css');
            //   $q->whereHas('calllogs', function($query){
            //       $query->where('call_received', 1)->where('w_dial_attempts', '>=', 1);
            //   }); 
               //'record_id', 'record_id')->where('call_received', 1)->where('w_dial_attempts', '>=', 1);
                //$q->select(DB::raw('count(*) from `outreach_mailings` where `contacts`.`record_id` = `outreach_mailings`.`contact_id` and `deliveredAt` is not null as totalemail'));
                //$q->withCount(['totalemail','totalclick']);
                // $q->has('totalemail', '>=', 1);
                // $q->has('totalclick', '>=', 1);
                $q->leftjoin('stages', 'stages.oid', '=', 'contacts.stage');
                //$q->leftJoin('fivenine_call_logs', 'fivenine_call_logs.record_id', '=', 'contacts.record_id');
                //$q->dd();
                //dd($q->get());
                dd($q->limit(5)->get());
                        // ->with(['totalcall', 'totalwcall', 'calllogs'])
                        // ->leftjoin('stages', 'stages.oid', '=', 'contacts.stage');
            
            
           
        
 
        /* $records->withCount(['totalemail', 'totalopen', 'totalclick', 'totalreply', 'totalrcall', 'totalwrcall', 'totalbounced'])
                ->with(['totalcall', 'totalwcall', 'calllogs'])
                ->join('stages', 'stages.oid', '=', 'contacts.stage')
                ->orderBy('contacts.outreach_touched_at', 'desc')
                ->paginate($recordPerPage);
        return $records; */
        
        return $records;
    }

    

}
