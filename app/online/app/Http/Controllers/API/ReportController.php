<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
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
use App\Models\ReportContacts;
use App\Models\ReportMailings;
use App\Models\ReportCalls;
use App\Models\ReportSequenceMailings;


class ReportController extends Controller
{
    
    public function __construct()
    {
       
    }
    public function index(Request $request){
        $allDate = [];
        $passDate = [];
        $totalProspect = [];
        $contactCreated = [];
        $stageUpdated = [];
        $contactNoUpdated = [];
        $customFieldUpdated = [];
        $contactData = [];

        $emailDelivered = [];
        $emailOpened = [];
        $emailClicked = [];
        $emailReplied = [];
        $emailBounced = [];
        //sequence
        $emailSequenceDelivered = [];
        $emailSequenceOpened = [];
        $emailSequenceClicked = [];
        $emailSequenceReplied = [];
        $emailSequenceBounced = [];
        //all emails
        $emailAllDelivered = [];
        $emailAllOpened = [];
        $emailAllClicked = [];
        $emailAllReplied = [];
        $emailAllBounced = [];

        $mc = [];
        $mcr = [];
        $hc = [];
        $hcr = [];
        $wc = [];
        $wcr = [];
        $nscall = [];
        $displayDate = [];
        $l = 0;
        $s = strtotime($request->input("sdate"));
        $e = strtotime($request->input("edate"));
        $diff = $e - $s;
        $day = $diff/(24*3600);
        if($day > 12)
        {
            $day = $diff/(24*3600)+1; //dd($day);
            $date = [];
            $l = 0;
            if($day > 12 && $day <=30){
                $group = intval($day/3);
                $groupRecord = [];
                $l = 3;
                for($i = 0; $i < $group; ++$i){
                    $date[$i]["s"] = date("Y-m-d", $s + $i*3*24*3600);
                    $date[$i]["e"] = date("Y-m-d", $s + ($i*3+2)*24*3600);
                    $displayDate[] = date("j", $s + $i*3*24*3600).date("M", $s + ($i*3+2)*24*3600);
                }
                if($day%3 == 1){
                    $date[$i]["s"] = date("Y-m-d", $e);
                    $date[$i]["e"] = date("Y-m-d", $e);
                    $displayDate[] = date("j M", $e);
                }elseif($day%3 == 2){
                    $date[$i]["s"] = date("Y-m-d", $e - 24*3600);
                    $date[$i]["e"] = date("Y-m-d", $e);
                    $displayDate[] = date("j M", $e - 24*3600);
                }
                
            }elseif($day > 30){
                if($day%10 == 0){
                    $l = $day/10;
                }else{
                    $l = intval($day/10)+1;
                }
                $groupRecord = [];
                $group = intval($day/$l);
                for($i = 0; $i < $group; ++$i){
                    $date[$i]["s"] = date("Y-m-d", $s + $i*$l*24*3600);
                    $date[$i]["e"] = date("Y-m-d", $s + ($i*$l+$l-1)*24*3600);
                    $displayDate[] = date("j M", $s + $i*$l*24*3600);
                    $ld = date("Y-m-d", $s + ($i*$l+$l-1)*24*3600);
                }
                if($day%$l > 0){
                    $date[$i]["s"] = date("Y-m-d", strtotime($ld)+24*3600);
                    $date[$i]["e"] = date("Y-m-d", $e);
                    $displayDate[] = date("j M", strtotime($ld)+24*3600);
                }
            }
            
            
        }
        for($i = 0; $i <= $day; $i++){
            $passDate[] = date("Y-m-d", $s+$i*24*3600);
            $allDate[date("Y-m-d", $s+$i*24*3600)] = [];
        }

        $reportCall = [];
        $reportEmail = [];
        $reportSequenceEmail = [];
        $reportContact = [];
        foreach($allDate as $key => $value){
            $date = $key;
            $reportCalls = ReportCalls::where('date', '=', $date)->count();
            if($reportCalls == 0){
                $reportCall[$date] = [
                    "total_calls" => 0,
                    "total_mobile_calls" => 0,
                    "total_mobile_calls_received" => 0,
                    "total_home_calls" => 0,
                    "total_home_calls_received" => 0,
                    "total_work_calls" => 0,
                    "total_work_calls_received" => 0,
                    "not_answered" => 0
                ];
            }elseif($reportCalls == 1){
                $reportCalls = ReportCalls::where('date', '=', $date)->first();
                $reportCall[$date] = [
                    "total_calls" => $reportCalls["total_calls"],
                    "total_mobile_calls" => $reportCalls["total_mobile_calls"],
                    "total_mobile_calls_received" => $reportCalls["total_mobile_calls_received"],
                    "total_home_calls" => $reportCalls["total_home_calls"],
                    "total_home_calls_received" => $reportCalls["total_home_calls_received"],
                    "total_work_calls" => $reportCalls["total_work_calls"],
                    "total_work_calls_received" => $reportCalls["total_work_calls_received"],
                    "not_answered" =>  $reportCalls["not_answered"]
                ];
            }
            $reportEmails = ReportMailings::where('date', '=', $date)->count();
            if($reportEmails == 0){
                $reportEmail[$date] = [
                    "delivered" => 0,
                    "opened" => 0,
                    "clicked" => 0,
                    "replied" => 0,
                    "bounced" => 0,
                ];
            }elseif($reportEmails == 1){
                $reportEmails = ReportMailings::where('date', '=', $date)->first();
                $reportEmail[$date] = [
                    "delivered" => $reportEmails["delivered"],
                    "opened" => $reportEmails["opened"],
                    "clicked" => $reportEmails["clicked"],
                    "replied" => $reportEmails["replied"],
                    "bounced" => $reportEmails["bounced"],
                ];
            }
            //sequence
            $reportSequenceEmails = ReportSequenceMailings::where('date', '=', $date)->count();
            if($reportSequenceEmails == 0){
                $reportSequenceEmail[$date] = [
                    "delivered" => 0,
                    "opened" => 0,
                    "clicked" => 0,
                    "replied" => 0,
                    "bounced" => 0,
                ];
            }elseif($reportSequenceEmails == 1){
                $reportSequenceEmails = ReportSequenceMailings::where('date', '=', $date)->first();
                $reportSequenceEmail[$date] = [
                    "delivered" => $reportSequenceEmails["delivered"],
                    "opened" => $reportSequenceEmails["opened"],
                    "clicked" => $reportSequenceEmails["clicked"],
                    "replied" => $reportSequenceEmails["replied"],
                    "bounced" => $reportSequenceEmails["bounced"],
                ];
            }
            $reportContacts = ReportContacts::where('date', '=', $date)->count();
            if($reportContacts == 0){
                $reportContact[$date] = [
                    "total_created" => 0,
                    "total_stage_update" => 0,
                    "total_contact_no_update" => 0,
                    "total_custom_field_update" => 0,
                ];
            }elseif($reportContacts == 1){
                $reportContacts = ReportContacts::where('date', '=', $date)->first();
                $reportContact[$date] = [
                    "total_created" => $reportContacts["total_created"],
                    "total_stage_update" => $reportContacts["total_stage_update"],
                    "total_contact_no_update" => $reportContacts["total_contact_no_update"],
                    "total_custom_field_update" => $reportContacts["total_custom_field_update"],
                ];
            }
        }
        
        
        foreach($passDate as $key => $value){
            $total = $reportContact[$value]["total_created"] + $reportContact[$value]["total_stage_update"] + $reportContact[$value]["total_contact_no_update"] + $reportContact[$value]["total_custom_field_update"];
            $totalProspect[] = $total;
            $contactCreated[] = $reportContact[$value]["total_created"];
            $stageUpdated[] = $reportContact[$value]["total_stage_update"];
            $contactNoUpdated[] = $reportContact[$value]["total_contact_no_update"];
            $customFieldUpdated[] = $reportContact[$value]["total_custom_field_update"];
            //email
            $emailDelivered[]  = $reportEmail[$value]["delivered"];
            $emailOpened[]  = $reportEmail[$value]["opened"];
            $emailClicked[]  = $reportEmail[$value]["clicked"];
            $emailReplied[]  = $reportEmail[$value]["replied"];
            $emailBounced[]  = $reportEmail[$value]["bounced"];
            //sequence
            $emailSequenceDelivered[]  = $reportSequenceEmail[$value]["delivered"];
            $emailSequenceOpened[]  = $reportSequenceEmail[$value]["opened"];
            $emailSequenceClicked[]  = $reportSequenceEmail[$value]["clicked"];
            $emailSequenceReplied[]  = $reportSequenceEmail[$value]["replied"];
            $emailSequenceBounced[]  = $reportSequenceEmail[$value]["bounced"];
            //all email
            $emailAllDelivered[] = $reportEmail[$value]["delivered"] + $reportSequenceEmail[$value]["delivered"];
            $emailAllOpened[] = $reportEmail[$value]["opened"] + $reportSequenceEmail[$value]["opened"];
            $emailAllClicked[] = $reportEmail[$value]["clicked"] + $reportSequenceEmail[$value]["clicked"];
            $emailAllReplied[] = $reportEmail[$value]["replied"] + $reportSequenceEmail[$value]["replied"];
            $emailAllBounced[] = $reportEmail[$value]["bounced"] + $reportSequenceEmail[$value]["bounced"];
            //call
            $mc[] = $reportCall[$value]["total_mobile_calls"];
            $mcr[] = $reportCall[$value]["total_mobile_calls_received"];
            $hc[] = $reportCall[$value]["total_home_calls"];
            $hcr[] = $reportCall[$value]["total_home_calls_received"];
            $wc[] = $reportCall[$value]["total_work_calls"];
            $wcr[] = $reportCall[$value]["total_work_calls_received"];
            $nscall[] = $reportCall[$value]["not_answered"];
        }
        $callData[] = [
            "name" => "Answered - MP",
            "values" => $mcr
        ];
        $callData[] = [
            "name" => "Answered - HP",
            "values" => $hcr
        ];
        $callData[] = [
            "name" => "Answered - WP",
            "values" => $wcr
        ];
        $callData[] = [
            "name" => "Not Answered",
            "values" => $nscall
        ];
        $mctotal = array_sum($mc);
        $mcrtotal = array_sum($mcr);
        $hctotal = array_sum($hc);
        $hcrtotal = array_sum($hcr);
        $wctotal = array_sum($wc);
        $wcrtotal = array_sum($wcr);
        $mtotal = $mctotal + $hctotal + $wctotal;
        $notAnsweredTotal = array_sum($nscall);
        //email
        $emailOData[] = [
            "name" => "Delivered",
            "values" => $emailDelivered
        ];
        $emailOData[] = [
            "name" => "Opened",
            "values" => $emailOpened
        ];
        $emailOData[] = [
            "name" => "Clicked",
            "values" => $emailClicked
        ];
        $emailOData[] = [
            "name" => "Replied",
            "values" => $emailReplied
        ];
        $emailOData[] = [
            "name" => "Bounced",
            "values" => $emailBounced
        ];
        //sequence
        $emailSData[] = [
            "name" => "Delivered",
            "values" => $emailSequenceDelivered
        ];
        $emailSData[] = [
            "name" => "Opened",
            "values" => $emailSequenceOpened
        ];
        $emailSData[] = [
            "name" => "Clicked",
            "values" => $emailSequenceClicked
        ];
        $emailSData[] = [
            "name" => "Replied",
            "values" => $emailSequenceReplied
        ];
        $emailSData[] = [
            "name" => "Bounced",
            "values" => $emailSequenceBounced
        ];
        //all emails
        $emailData[] = [
            "name" => "Delivered",
            "values" => $emailAllDelivered
        ];
        $emailData[] = [
            "name" => "Opened",
            "values" => $emailAllOpened
        ];
        $emailData[] = [
            "name" => "Clicked",
            "values" => $emailAllClicked
        ];
        $emailData[] = [
            "name" => "Replied",
            "values" => $emailAllReplied
        ];
        $emailData[] = [
            "name" => "Bounced",
            "values" => $emailAllBounced
        ];

        $totalEmailDeliveredCounter = array_sum($emailDelivered);
        $totalEmailOpenedCounter = array_sum($emailOpened);
        $totalEmailClickedCounter = array_sum($emailClicked);
        $totalEmailRepliedCounter = array_sum($emailReplied);
        $totalEmailBouncedCounter = array_sum($emailBounced);
        //sequence
        $totalSEmailDeliveredCounter = array_sum($emailSequenceDelivered);
        $totalSEmailOpenedCounter = array_sum($emailSequenceOpened);
        $totalSEmailClickedCounter = array_sum($emailSequenceClicked);
        $totalSEmailRepliedCounter = array_sum($emailSequenceReplied);
        $totalSEmailBouncedCounter = array_sum($emailSequenceBounced);
        
        //contact
        $contactData[] = [
            "name" => "Total Prospect",
            "values" => $totalProspect
        ];
        $contactData[] = [
            "name" => "New Prospect",
            "values" => $contactCreated
        ];
        $contactData[] = [
            "name" => "Stage Changed",
            "values" => $stageUpdated
        ];
        $contactData[] = [
            "name" => "Contact Changed",
            "values" => $contactNoUpdated
        ];
        $contactData[] = [
            "name" => "CF Changed",
            "values" => $customFieldUpdated
        ];
        
        $totalProspectCount = array_sum($totalProspect);
        $totalNewProspectCount = array_sum($contactCreated);
        $totalStageUpdateCount = array_sum($stageUpdated);
        $totalContactNoUpdateCount = array_sum($contactNoUpdated);
        $totalCustomFieldUpdateCount = array_sum($customFieldUpdated);
        $npassData = $passDate;
        $passDate = [];
        foreach($npassData as $key => $value){
            $passDate[] = date("j M y", strtotime($value)); //10 Sep 21 - 17 Sep 21
        }
        return [
            "totalEmailDeliveredCounter" => $totalEmailDeliveredCounter,
            "totalEmailOpenedCounter" => $totalEmailOpenedCounter,
            "totalEmailClickedCounter" => $totalEmailClickedCounter,
            "totalEmailRepliedCounter" => $totalEmailRepliedCounter,
            "totalEmailBouncedCounter" => $totalEmailBouncedCounter,

            "totalSEmailDeliveredCounter" => $totalSEmailDeliveredCounter,
            "totalSEmailOpenedCounter" => $totalSEmailOpenedCounter,
            "totalSEmailClickedCounter" => $totalSEmailClickedCounter,
            "totalSEmailRepliedCounter" => $totalSEmailRepliedCounter,
            "totalSEmailBouncedCounter" => $totalSEmailBouncedCounter,

            "totalProspectCount" => $totalProspectCount,
            "totalNewProspectCount" => $totalNewProspectCount,
            "totalStageUpdateCount" => $totalStageUpdateCount,
            "totalContactNoUpdateCount" => $totalContactNoUpdateCount,
            "totalCustomFieldUpdateCount" => $totalCustomFieldUpdateCount,

            "notAnsweredTotal" => $notAnsweredTotal,
            "mtotal" => $mtotal,
            "mctotal" => $mctotal,
            "mcrtotal" => $mcrtotal,
            "hctotal" => $hctotal,
            "hcrtotal" => $hcrtotal,
            "wctotal" => $wctotal,
            "wcrtotal" => $wcrtotal,

            "contactData" => $contactData,
            "emailSData" => $emailSData,
            "emailOData" => $emailOData,
            "emailData" => $emailData,
            "callData" => $callData,
            "reportCall" => $reportCall,
            "dates" => $passDate,
            'day' => $day];
    }
    
    public function getEmail(){
        $date = "2021-09-02";
       $count = OutreachMailings::where("mailingType", '=', "sequence")->where("state", '=', "delivered")->whereDate("deliveredAt", '=', $date)->count();
       echo $count."<br>";
       $count = OutreachMailings::where("mailingType", '=', "sequence")->where("state", '=', "opened")->whereDate("deliveredAt", '=', $date)->count();
       echo $count."<br>";
       $count = OutreachMailings::where("mailingType", '=', "sequence")->where("state", '=', "replied")->whereDate("deliveredAt", '=', $date)->count();
       echo $count."<br>";
       $count = OutreachMailings::where("mailingType", '=', "sequence")->where("state", '=', "bounced")->whereDate("bouncedAt", '=', $date)->count();
       echo $count."<br>";
       $count = OutreachMailings::where("mailingType", '=', "sequence")->whereDate("unsubscribedAt", '=', $date)->count();
       echo $count."<br>";
    }
    public function logRecords(Request $request){
        $type = $request->input("t");
        if($request->input("dateRange.startDate")){
            $startDate = substr($request->input("dateRange.startDate"), 0, 10);
            $endDate = substr($request->input("dateRange.endDate"), 0, 10);
        }else{
            $startDate = $request->input("s");
            $endDate = $request->input("e");
        }
        $logRecordType = $request->input("v");
        if($type == "call"):
            return  $this->__getCallLog($startDate, $endDate, $logRecordType);
        elseif($type == "email-all"):
            return  $this->__getEmailAllLog($startDate, $endDate, $logRecordType);
        elseif($type == "email-single"):
            return  $this->__getEmailSingleLog($startDate, $endDate, $logRecordType);
        elseif($type == "email-sequence"):
            return  $this->__getEmailSequenceLog($startDate, $endDate, $logRecordType);
        elseif($type == "contact"):
            $stages = Stages::get();
            $stageArray = [];
            foreach($stages as $value){
                $stageArray[$value["oid"]] = ["name" => $value["name"], "css" => $value["css"]];
            }
            $records = $this->__getProspects($startDate, $endDate, $logRecordType);
            if($logRecordType == 3){
                foreach($records as $record){
                    $os = intval($record["old_stage"]);
                    $sdetails = $stageArray[$os];
                    $record["old_stage_name"] = $sdetails["name"];
                    $record["old_stage_css"] = $sdetails["css"];
                }
            }
        endif;
        return $records;
    }
    private function __getProspects($startDate, $endDate, $logRecordType){
        
            $allRecords = [];
            $records = ReportContacts::whereBetween("date", [$startDate, $endDate])->get();
            if($logRecordType == 1): //1:total prospect
                foreach($records as $record):
                    $total_created_ids = ($record["total_created_ids"]) ? json_decode($record["total_created_ids"]):false;
                    $total_stage_update_ids = ($record["total_stage_update_ids"]) ? json_decode($record["total_stage_update_ids"]):false;
                    $total_contact_no_update_ids = ($record["total_contact_no_update_ids"]) ? json_decode($record["total_contact_no_update_ids"]):false;
                    $total_custom_field_update_ids = ($record["total_custom_field_update_ids"]) ? json_decode($record["total_custom_field_update_ids"]):false;
                    
                    if($total_created_ids){
                        foreach($total_created_ids as $value){
                            $allRecords[] = $value;
                        }
                    }
                    if($total_stage_update_ids){
                        foreach($total_stage_update_ids as $value){
                            $allRecords[] = $value;
                        }
                    }
                    if($total_contact_no_update_ids){
                        foreach($total_contact_no_update_ids as $value){
                            $allRecords[] = $value;
                        }
                    }
                    if($total_custom_field_update_ids){
                        foreach($total_custom_field_update_ids as $value){
                            $allRecords[] = $value;
                        }
                    }
                endforeach;
                return Contacts::select('contacts.id', 'contacts.record_id', 'contacts.first_name', 'contacts.last_name', 'contacts.title', 'contacts.company', 'contacts.mobilePhones', 'contacts.workPhones', 'contacts.homePhones', 'contacts.emails', 'contacts.outreach_touched_at', 'contacts.outreach_created_at', 'contacts.last_agent_dispo_time', 'contacts.last_agent', 'contacts.email_delivered', 'contacts.email_opened', 'contacts.email_clicked', 'contacts.email_replied', 'contacts.email_bounced', 'contacts.mcall_attempts', 'contacts.mcall_received', 'contacts.wcall_attempts', 'contacts.wcall_received', 'contacts.hcall_attempts', 'contacts.hcall_received', 'stages.id as stage', 'stages.name as stage_name', 'stages.css as stage_css', 'contacts.dataset')
                ->leftjoin('stages', 'stages.oid', '=', 'contacts.stage')
                ->whereIn("record_id", $allRecords)
                ->get();
            elseif($logRecordType == 2): //2: new prospect
                foreach($records as $record):

                    $total_created_ids = ($record["total_created_ids"]) ? json_decode($record["total_created_ids"]):false;                    
                    if($total_created_ids){
                        foreach($total_created_ids as $value){
                            $allRecords[] = $value;
                        }
                    }
                endforeach;
                return Contacts::select('contacts.id', 'contacts.record_id', 'contacts.first_name', 'contacts.last_name', 'contacts.title', 'contacts.company', 'contacts.mobilePhones', 'contacts.workPhones', 'contacts.homePhones', 'contacts.emails', 'contacts.outreach_touched_at', 'contacts.outreach_created_at', 'contacts.last_agent_dispo_time', 'contacts.last_agent', 'contacts.email_delivered', 'contacts.email_opened', 'contacts.email_clicked', 'contacts.email_replied', 'contacts.email_bounced', 'contacts.mcall_attempts', 'contacts.mcall_received', 'contacts.wcall_attempts', 'contacts.wcall_received', 'contacts.hcall_attempts', 'contacts.hcall_received', 'stages.id as stage', 'stages.name as stage_name', 'stages.css as stage_css', 'contacts.dataset')
                ->leftjoin('stages', 'stages.oid', '=', 'contacts.stage')
                ->whereIn("record_id", $allRecords)
                ->get();
            elseif($logRecordType == 3): //3: stage update
                foreach($records as $record):
                    $total_stage_update_ids = ($record["total_stage_update_ids"]) ? json_decode($record["total_stage_update_ids"]):false;
                    
                    if($total_stage_update_ids){
                        foreach($total_stage_update_ids as $value){
                            $allRecords[] = $value;
                        }
                    }

                endforeach;
                return Contacts::select('contacts.id', 'contacts.record_id', 'contacts.first_name', 'contacts.last_name', 'contacts.title', 'contacts.company', 'contacts.mobilePhones', 'contacts.workPhones', 'contacts.homePhones', 'contacts.emails', 'contacts.outreach_touched_at', 'contacts.outreach_created_at', 'contacts.last_agent_dispo_time', 'contacts.last_agent', 'contacts.email_delivered', 'contacts.email_opened', 'contacts.email_clicked', 'contacts.email_replied', 'contacts.email_bounced', 'contacts.mcall_attempts', 'contacts.mcall_received', 'contacts.wcall_attempts', 'contacts.wcall_received', 'contacts.hcall_attempts', 'contacts.hcall_received', 'stages.id as stage', 'stages.name as stage_name', 'stages.css as stage_css', 'contacts.dataset', 'contacts.old_stage')
                ->leftjoin('stages', 'stages.oid', '=', 'contacts.stage')
                ->whereIn("record_id", $allRecords)
                ->get();
            elseif($logRecordType == 4): //4:contact no update
                foreach($records as $record):
                    $total_contact_no_update_ids = ($record["total_contact_no_update_ids"]) ? json_decode($record["total_contact_no_update_ids"]):false;
                    
                    if($total_contact_no_update_ids){
                        foreach($total_contact_no_update_ids as $value){
                            $allRecords[] = $value;
                        }
                    }
                    
                endforeach;
                return Contacts::select('contacts.id', 'contacts.record_id', 'contacts.first_name', 'contacts.last_name', 'contacts.title', 'contacts.company', 'contacts.mobilePhones', 'contacts.workPhones', 'contacts.homePhones', 'contacts.emails', 'contacts.outreach_touched_at', 'contacts.outreach_created_at', 'contacts.last_agent_dispo_time', 'contacts.last_agent', 'contacts.email_delivered', 'contacts.email_opened', 'contacts.email_clicked', 'contacts.email_replied', 'contacts.email_bounced', 'contacts.mcall_attempts', 'contacts.mcall_received', 'contacts.wcall_attempts', 'contacts.wcall_received', 'contacts.hcall_attempts', 'contacts.hcall_received', 'stages.id as stage', 'stages.name as stage_name', 'stages.css as stage_css', 'contacts.dataset')
                ->leftjoin('stages', 'stages.oid', '=', 'contacts.stage')
                ->whereIn("record_id", $allRecords)
                ->get();
            elseif($logRecordType == 5): //5: custom field update
                foreach($records as $record):
                    $total_custom_field_update_ids = ($record["total_custom_field_update_ids"]) ? json_decode($record["total_custom_field_update_ids"]):false;
                    
                    if($total_custom_field_update_ids){
                        foreach($total_custom_field_update_ids as $value){
                            $allRecords[] = $value;
                        }
                    }
                    
                endforeach;
                return Contacts::select('contacts.id', 'contacts.record_id', 'contacts.first_name', 'contacts.last_name', 'contacts.title', 'contacts.company', 'contacts.mobilePhones', 'contacts.workPhones', 'contacts.homePhones', 'contacts.emails', 'contacts.outreach_touched_at', 'contacts.outreach_created_at', 'contacts.last_agent_dispo_time', 'contacts.last_agent', 'contacts.email_delivered', 'contacts.email_opened', 'contacts.email_clicked', 'contacts.email_replied', 'contacts.email_bounced', 'contacts.mcall_attempts', 'contacts.mcall_received', 'contacts.wcall_attempts', 'contacts.wcall_received', 'contacts.hcall_attempts', 'contacts.hcall_received', 'stages.id as stage', 'stages.name as stage_name', 'stages.css as stage_css', 'contacts.dataset')
                ->leftjoin('stages', 'stages.oid', '=', 'contacts.stage')
                ->whereIn("record_id", $allRecords)
                ->get();
            endif;
        
    }
    private function __getEmailSequenceLog($startDate, $endDate, $logRecordType){
        if($logRecordType == 1): //1:'Total Delivered'
            if($startDate == $endDate): // same day
                return OutreachMailings::with('prospect')->whereNotNull('contact_id')->where("mailingType", "=", "sequence")->WhereDate("deliveredAt", '=', $startDate)->get();
            else:
                return OutreachMailings::with('prospect')->where("mailingType", "=", "sequence")->WhereBetween("deliveredAt", [$startDate, $endDate])->get();
            endif;
        elseif($logRecordType == 2): //2:'Total Opened'
            if($startDate == $endDate): // same day
                return OutreachMailings::with('prospect')->whereNotNull('contact_id')->where("mailingType", "=", "sequence")->WhereDate("openedAt", '=', $startDate)->get();
            else:
                return OutreachMailings::with('prospect')->whereNotNull('contact_id')->where("mailingType", "=", "sequence")->WhereBetween("openedAt", [$startDate, $endDate])->get();
            endif;
        elseif($logRecordType == 3): //3:'Total Clicked'
            if($startDate == $endDate): // same day
                return OutreachMailings::with('prospect')->whereNotNull('contact_id')->where("mailingType", "=", "sequence")->WhereDate("clickedAt", '=', $startDate)->get();
            else:
                return OutreachMailings::with('prospect')->whereNotNull('contact_id')->where("mailingType", "=", "sequence")->WhereBetween("clickedAt", [$startDate, $endDate])->get();
            endif;
        elseif($logRecordType == 4): //4:'Total Replied'
            if($startDate == $endDate): // same day
                return OutreachMailings::with('prospect')->whereNotNull('contact_id')->where("mailingType", "=", "sequence")->WhereDate("repliedAt", '=', $startDate)->get();
            else:
                return OutreachMailings::with('prospect')->whereNotNull('contact_id')->where("mailingType", "=", "sequence")->WhereBetween("repliedAt", [$startDate, $endDate])->get();
            endif;
            elseif($logRecordType == 5): //5:'Total Bounced'
                if($startDate == $endDate): // same day
                    return OutreachMailings::with('prospect')->whereNotNull('contact_id')->where("mailingType", "=", "sequence")->WhereDate("bouncedAt", '=', $startDate)->get();
                else:
                    return OutreachMailings::with('prospect')->whereNotNull('contact_id')->where("mailingType", "=", "sequence")->WhereBetween("bouncedAt", [$startDate, $endDate])->get();
                endif;
        endif;
    }
    private function __getEmailSingleLog($startDate, $endDate, $logRecordType){        
        if($logRecordType == 1): //1:'Total Delivered'
            if($startDate == $endDate): // same day
                return OutreachMailings::with('prospect')->whereNotNull('contact_id')->where("mailingType", "=", "single")->WhereDate("deliveredAt", '=', $startDate)->get();
            else:
                return OutreachMailings::with('prospect')->whereNotNull('contact_id')->where("mailingType", "=", "single")->WhereBetween("deliveredAt", [$startDate, $endDate])->get();
            endif;
        elseif($logRecordType == 2): //2:'Total Opened'
            if($startDate == $endDate): // same day
                return OutreachMailings::with('prospect')->whereNotNull('contact_id')->where("mailingType", "=", "single")->WhereDate("openedAt", '=', $startDate)->get();
            else:
                return OutreachMailings::with('prospect')->whereNotNull('contact_id')->where("mailingType", "=", "single")->WhereBetween("openedAt", [$startDate, $endDate])->get();
            endif;
        elseif($logRecordType == 3): //3:'Total Clicked'
            if($startDate == $endDate): // same day
                return OutreachMailings::with('prospect')->whereNotNull('contact_id')->where("mailingType", "=", "single")->WhereDate("clickedAt", '=', $startDate)->get();
            else:
                return OutreachMailings::with('prospect')->whereNotNull('contact_id')->where("mailingType", "=", "single")->WhereBetween("clickedAt", [$startDate, $endDate])->get();
            endif;
        elseif($logRecordType == 4): //4:'Total Replied'
            if($startDate == $endDate): // same day
                return OutreachMailings::with('prospect')->whereNotNull('contact_id')->where("mailingType", "=", "single")->WhereDate("repliedAt", '=', $startDate)->get();
            else:
                return OutreachMailings::with('prospect')->whereNotNull('contact_id')->where("mailingType", "=", "single")->WhereBetween("repliedAt", [$startDate, $endDate])->get();
            endif;
        elseif($logRecordType == 5): //5:'Total Bounced'
            if($startDate == $endDate): // same day
                return OutreachMailings::with('prospect')->whereNotNull('contact_id')->where("mailingType", "=", "single")->WhereDate("bouncedAt", '=', $startDate)->get();
            else:
                return OutreachMailings::with('prospect')->whereNotNull('contact_id')->where("mailingType", "=", "single")->WhereBetween("bouncedAt", '=', [$startDate, $endDate])->get();
            endif;
        endif;
    }
    private function __getEmailAllLog($startDate, $endDate, $logRecordType){        
        if($logRecordType == 1): //1:'Total Delivered'
            if($startDate == $endDate): // same day
                return OutreachMailings::with('prospect')->whereNotNull('contact_id')->whereIn("mailingType", ["sequence", "single"])->WhereDate("deliveredAt", '=', $startDate)->get();
            else:
                $endDate = date("Y-m-d", strtotime($endDate) + 24*3600);
                return OutreachMailings::with('prospect')->whereNotNull('contact_id')->whereIn("mailingType", ["sequence", "single"])->WhereBetween("deliveredAt", [$startDate, $endDate])->get();
            endif;
        elseif($logRecordType == 2): //2:'Total Opened' 
            if($startDate == $endDate): // same day
                return OutreachMailings::with('prospect')->whereNotNull('contact_id')->WhereDate("openedAt", '=', $startDate)->get();
            else:
                $endDate = date("Y-m-d", strtotime($endDate) + 24*3600);
                return OutreachMailings::with('prospect')->whereNotNull('contact_id')->whereIn("mailingType", ["sequence", "single"])->WhereBetween("openedAt", [$startDate, $endDate])->get();
            endif;
        elseif($logRecordType == 3): //3:'Total Clicked'
            if($startDate == $endDate): // same day
                return OutreachMailings::with('prospect')->whereNotNull('contact_id')->whereIn("mailingType", ["sequence", "single"])->WhereDate("clickedAt", '=', $startDate)->get();
            else:
                $endDate = date("Y-m-d", strtotime($endDate) + 24*3600);
                return OutreachMailings::with('prospect')->whereNotNull('contact_id')->whereIn("mailingType", ["sequence", "single"])->WhereBetween("clickedAt", [$startDate, $endDate])->get();
            endif;
        elseif($logRecordType == 4): //4:'Total Replied'
            if($startDate == $endDate): // same day
                return OutreachMailings::with('prospect')->whereNotNull('contact_id')->whereIn("mailingType", ["sequence", "single"])->WhereDate("repliedAt", '=', $startDate)->get();
            else:
                $endDate = date("Y-m-d", strtotime($endDate) + 24*3600);
                return OutreachMailings::with('prospect')->whereNotNull('contact_id')->whereIn("mailingType", ["sequence", "single"])->WhereBetween("repliedAt", [$startDate, $endDate])->get();
            endif;
        elseif($logRecordType == 5): //5:'Total Bounced'
            if($startDate == $endDate): // same day
                return OutreachMailings::with('prospect')->whereNotNull('contact_id')->whereIn("mailingType", ["sequence", "single"])->WhereDate("bouncedAt", '=', $startDate)->get();
            else:
                $endDate = date("Y-m-d", strtotime($endDate) + 24*3600);
                return OutreachMailings::with('prospect')->whereNotNull('contact_id')->whereIn("mailingType", ["sequence", "single"])->WhereBetween("bouncedAt", [$startDate, $endDate])->get();
            endif;
        endif;
    }
    private function __getCallLog($startDate, $endDate, $logRecordType){            
        if($startDate == $endDate): // same day
            $ts = strtotime($startDate);
            $te = $ts + 24*3600;
        else:
            $ts = strtotime($startDate);
                $te = strtotime($endDate) + 24*3600;
        endif;
        if($logRecordType == 1):// ///1:'Total Calls'
            return FivenineCallLogs::with("contactData")->where("n_timestamp", ">", $ts)->where("n_timestamp", "<", $te)->get();
        elseif($logRecordType == 2)://2:'Answered - Mobile Phone'
            return FivenineCallLogs::with("contactData")->where("n_timestamp", ">", $ts)->where("n_timestamp", "<", $te)->where("call_received", '=', 1)->where("number_type", '=', 'm')->get();            
        elseif($logRecordType == 3)://3:'Answered - Home Phone'
            return FivenineCallLogs::with("contactData")->where("n_timestamp", ">", $ts)->where("n_timestamp", "<", $te)->where("call_received", '=', 1)->where("number_type", '=', 'hq')->get();
        elseif($logRecordType == 4)://4:'Answered - Work Phone'
            return FivenineCallLogs::with("contactData")->where("n_timestamp", ">", $ts)->where("n_timestamp", "<", $te)->where("call_received", '=', 1)->where("number_type", '=', 'd')->get();
        elseif($logRecordType == 5)://5:'Not Answered'
            return FivenineCallLogs::with("contactData")->where("n_timestamp", ">", $ts)->where("n_timestamp", "<", $te)->where("call_received", '=', 0)->get();
        endif;
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
    public function getEmailBody($mailing_id = null){        
        $curl = curl_init();
        $accessToken = $this->__outreachsession();
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.outreach.io/api/v2/mailings/$mailing_id",
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
        $result = get_object_vars(json_decode($response));
        return ["results" => $result] ;
        // $data = get_object_vars($result["data"]);
        // $attributes = get_object_vars($data["attributes"]);
        // echo "<pre>"; print_r($attributes["bodyHtml"]); echo "</pre>"; 
    }
}