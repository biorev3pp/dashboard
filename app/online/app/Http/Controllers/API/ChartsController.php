<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contacts;
use App\Models\Stages;
use App\Models\OutreachMailings;
use App\Models\FivenineCallLogs;

class ChartsController extends Controller
{
    
    public function index(Request $request)
    {
        ini_set('max_execution_time', 3600);
        $records1 = Contacts::select('contacts.id', 'contacts.record_id', 'contacts.first_name', 'contacts.last_name', 'contacts.title', 'contacts.company', 'contacts.mobilePhones', 'contacts.workPhones', 'contacts.homePhones', 'contacts.emails', 'contacts.outreach_touched_at', 'contacts.outreach_created_at', 'contacts.last_agent_dispo_time', 'contacts.last_agent', 'contacts.email_delivered', 'contacts.email_opened', 'contacts.email_clicked', 'contacts.email_replied', 'contacts.email_bounced', 'contacts.mcall_attempts', 'contacts.mcall_received', 'contacts.wcall_attempts', 'contacts.wcall_received', 'contacts.hcall_attempts', 'contacts.hcall_received', 'contacts.dataset','contacts.outreach_tag')
        ;
        $records2 = Contacts::select(DB::raw('count(contacts.stage) as count, contacts.stage'))
        ->groupBy('contacts.stage');
        
        $stageRecords = Stages::select("oid", "name")->get();
        $stageList = [];
        foreach($stageRecords as $value){
            $stageList[$value["oid"]] = $value["name"];
        }
        $paiDataS = [];
        $maplabelsS = [];
        $paiDataE = [];
        $maplabelsE = [];
        $paiDataC = [];
        $maplabelsC = [];
        if(count($request->input('filterConditionsArray')) > 0):
            $filterConditions = true;
            $filterConditionsArray = $request->input('filterConditionsArray');
            $q1 = $records1;
            foreach($filterConditionsArray as $value){
                if( ($value['type'] == 'textbox') || ($value['type'] == 'dropdown') ):
                    if($value['formula'] == 'is'):
                        if(strpos($value["textCondition"],  ",")):
                            $ids = explode(",", $value["textCondition"]);
                            $q1->whereIn($value["condition"], $ids);
                        else:
                            if(in_array($value["condition"], ["mnumber", "wnumber", "hnumber"])){
                                $mobile = $this->__NumberFormater($value["textCondition"]);
                                if(strlen($mobile) >= 10){
                                    $q1->where($value["condition"], 'like', "%".$mobile."%");
                                }else{
                                    $q1->where($value["condition"], 'like', $mobile);
                                }
                            }else{
                                $q1->where($value["condition"], '=', $value["textCondition"]);
                            }
                        endif;
                    elseif($value['formula'] == 'is not'):
                        if(strpos($value["textCondition"],  ",")):
                            $ids = explode(",", $value["textCondition"]);
                            $q1->whereNotIn($value["condition"], $ids);
                            $q1->orWhereNull($value["condition"]);
                        else:
                            $q1->where($value["condition"], 'not like', $value["textCondition"]);
                        endif;
                    elseif($value['formula'] == 'starts with'):
                        if(strpos($value["textCondition"],  ",")):
                            $ids = explode(",", $value["textCondition"]);
                            foreach($ids as $IdValue):
                                $q1->orWhere($value["condition"], 'like', $IdValue);
                            endforeach;
                        else:
                            $q1->where($value["condition"], 'like', $value["textCondition"].'%');
                        endif;
                    elseif($value['formula'] == 'is empty'):
                        if($value["condition"] == 'stage'){
                            $q1->whereNull($value["condition"]);
                        }else{
                            $q1->where(function($inq) use($value) {
                                $inq->where($value["condition"], 0);
                                $inq->where($value["condition"], '=', '');
                                $inq->orWhere($value["condition"], null);
                            });   
                        }                
                    elseif($value['formula'] == 'is not empty'):
                        $q1->whereNotNull($value["condition"]);
                        $q1->where($value["condition"], '!=', '');
                    elseif($value['formula'] == 'contains'):
                        $q1->where($value["condition"], 'like', '%'.$value["textCondition"].'%');
                    elseif($value['formula'] == 'not contains'):
                        $q1->where($value["condition"], 'not like', '%'.$value["textCondition"].'%');
                    elseif($value['formula'] == 'ends with'):
                        if(strpos($value["textCondition"],  ",")):
                            $ids = explode(",", $value["textCondition"]);
                            foreach($ids as $IdValue):
                                $q1->orWhere($value["condition"], 'like', $IdValue);
                            endforeach;
                        else:
                            $q1->where($value["condition"], 'like', '%'.$value["textCondition"]);
                        endif;
                    endif;
                elseif($value['type'] == 'calendar'):
                    $date = explode('--', $value["textCondition"]);
                    if($date[0] == $date[1]):
                        $s = substr($date[0], 0, 10);
                        $q1->whereDate($value["condition"], $s);
                    else:
                        $s = substr($date[0], 0, 10);
                        $e = substr($date[1], 0, 10);
                        $q1->whereBetween($value["condition"], [$s, $e]);
                    endif;
                elseif($value['type'] == 'email'):
                    if($value['formula'] == 'all'):
                        foreach (explode(',', $value['textCondition']) as $tcem) {
                            if(trim($tcem) == 'delivered'): $q1->where('email_delivered', '>=', 1); endif;
                            if(trim($tcem) == 'clicked'): $q1->where('email_clicked', '>=', 1); endif;
                            if(trim($tcem) == 'opened'): $q1->where('email_opened', '>=', 1); endif;
                            if(trim($tcem) == 'bounced'): $q1->where('email_bounced', '>=', 1); endif;
                            if(trim($tcem) == 'replied'): $q1->where('email_replied', '>=', 1); endif;
                        }
                    elseif($value['formula'] == 'any'):
                        $q1->where(function($inq) use ($value) { // $term is the search term on the query string
                            $txt = explode(',', $value['textCondition']);
                            foreach ($txt as $tk => $tcem) {
                                if(count($txt) == 1 || $tk == 0):
                                    if(trim($tcem) == 'delivered'): $inq->where('email_delivered', '>=', 1); endif;
                                    if(trim($tcem) == 'clicked'): $inq->where('email_clicked', '>=', 1); endif;
                                    if(trim($tcem) == 'opened'): $inq->where('email_opened', '>=', 1); endif;
                                    if(trim($tcem) == 'bounced'): $inq->where('email_bounced', '>=', 1); endif;
                                    if(trim($tcem) == 'replied'): $inq->where('email_replied', '>=', 1); endif;
                                else:
                                    if(trim($tcem) == 'delivered'): $inq->orWhere('email_delivered', '>=', 1); endif;
                                    if(trim($tcem) == 'clicked'): $inq->orWhere('email_clicked', '>=', 1); endif;
                                    if(trim($tcem) == 'opened'): $inq->orWhere('email_opened', '>=', 1); endif;
                                    if(trim($tcem) == 'bounced'): $inq->orWhere('email_bounced', '>=', 1); endif;
                                    if(trim($tcem) == 'replied'): $inq->orWhere('email_replied', '>=', 1); endif;
                                endif;
                            }
                        });
                    elseif($value['formula'] == 'none'):
                        $q1->where(function($inq) {
                            $inq->where('email_delivered', 0);
                            $inq->orWhere('email_delivered', null);
                        });
                        $q1->where(function($inq) {
                            $inq->where('email_bounced', 0);
                            $inq->orWhere('email_bounced', null);
                        });
                    endif;
                elseif($value['type'] == 'phone' && $value['condition'] == 'dsMobilePhones'):
                    if($value['formula'] == 'all'):
                        foreach (explode(',', $value['textCondition']) as $tcem) {
                            if(trim($tcem) == 'dialed'): $q1->where('mcall_attempts', '>=', 1); endif;
                            if(trim($tcem) == 'received'): $q1->where('mcall_received', '>=', 1); endif;
                        }
                    elseif($value['formula'] == 'any'):
                        $q1->where(function($inq) use ($value) { // $term is the search term on the query string
                            $txt = explode(',', $value['textCondition']);
                            foreach ($txt as $tk => $tcem) {
                                if(count($txt) == 1 || $tk == 0):
                                    if(trim($tcem) == 'dialed'): $inq->where('mcall_attempts', '>=', 1); endif;
                                    if(trim($tcem) == 'received'): $inq->where('mcall_received', '>=', 1); endif;
                                else:
                                    if(trim($tcem) == 'dialed'): $inq->orWhere('mcall_attempts', '>=', 1); endif;
                                    if(trim($tcem) == 'received'): $inq->orWhere('mcall_received', '>=', 1); endif;
                                endif;
                            }
                        });
                    elseif($value['formula'] == 'none'):
                        $q1->where(function($inq) {
                            $inq->where('mcall_attempts', 0);
                            $inq->orWhere('mcall_attempts', null);
                        });
                        $q1->where(function($inq) {
                            $inq->where('mcall_received', 0);
                            $inq->orWhere('mcall_received', null);
                        });
                    endif;
                elseif($value['type'] == 'phone' && $value['condition'] == 'dsHomePhones'):
                    if($value['formula'] == 'all'):
                        foreach (explode(',', $value['textCondition']) as $tcem) {
                            if(trim($tcem) == 'dialed'): $q1->where('hcall_attempts', '>=', 1); endif;
                            if(trim($tcem) == 'received'): $q1->where('hcall_received', '>=', 1); endif;
                        }
                    elseif($value['formula'] == 'any'):
                        $q1->where(function($inq) use ($value) { // $term is the search term on the query string
                            $txt = explode(',', $value['textCondition']);
                            foreach ($txt as $tk => $tcem) {
                                if(count($txt) == 1 || $tk == 0):
                                    if(trim($tcem) == 'dialed'): $inq->where('hcall_attempts', '>=', 1); endif;
                                    if(trim($tcem) == 'received'): $inq->where('hcall_received', '>=', 1); endif;
                                else:
                                    if(trim($tcem) == 'dialed'): $inq->orWhere('hcall_attempts', '>=', 1); endif;
                                    if(trim($tcem) == 'received'): $inq->orWhere('hcall_received', '>=', 1); endif;
                                endif;
                            }
                        });
                    elseif($value['formula'] == 'none'):
                        $q1->where(function($inq) {
                            $inq->where('hcall_attempts', 0);
                            $inq->orWhere('hcall_attempts', null);
                        });
                        $q1->where(function($inq) {
                            $inq->where('hcall_received', 0);
                            $inq->orWhere('hcall_received', null);
                        });
                    endif;
                elseif($value['type'] == 'phone' && $value['condition'] == 'dsWorkPhones'):
                    if($value['formula'] == 'all'):
                        foreach (explode(',', $value['textCondition']) as $tcem) {
                            if(trim($tcem) == 'dialed'): $q1->where('wcall_attempts', '>=', 1); endif;
                            if(trim($tcem) == 'received'): $q1->where('wcall_received', '>=', 1); endif;
                        }
                    elseif($value['formula'] == 'any'):
                        $q1->where(function($inq) use ($value) { // $term is the search term on the query string
                            $txt = explode(',', $value['textCondition']);
                            foreach ($txt as $tk => $tcem) {
                                if(count($txt) == 1 || $tk == 0):
                                    if(trim($tcem) == 'dialed'): $inq->where('wcall_attempts', '>=', 1); endif;
                                    if(trim($tcem) == 'received'): $inq->where('wcall_received', '>=', 1); endif;
                                else:
                                    if(trim($tcem) == 'dialed'): $inq->orWhere('wcall_attempts', '>=', 1); endif;
                                    if(trim($tcem) == 'received'): $inq->orWhere('wcall_received', '>=', 1); endif;
                                endif;
                            }
                        });
                    elseif($value['formula'] == 'none'):
                        $q1->where(function($inq) {
                            $inq->where('wcall_attempts', 0);
                            $inq->orWhere('wcall_attempts', null);
                        });
                        $q1->where(function($inq) {
                            $inq->where('wcall_received', 0);
                            $inq->orWhere('wcall_received', null);
                        });
                    endif;
                endif;
            }
            $q2 = $records2;
            foreach($filterConditionsArray as $value){
                if( ($value['type'] == 'textbox') || ($value['type'] == 'dropdown') ):
                    if($value['formula'] == 'is'):
                        if(strpos($value["textCondition"],  ",")):
                            $ids = explode(",", $value["textCondition"]);
                            $q2->whereIn($value["condition"], $ids);
                        else:
                            if(in_array($value["condition"], ["mnumber", "wnumber", "hnumber"])){
                                $mobile = $this->__NumberFormater($value["textCondition"]);
                                if(strlen($mobile) >= 10){
                                    $q2->where($value["condition"], 'like', "%".$mobile."%");
                                }else{
                                    $q2->where($value["condition"], 'like', $mobile);
                                }
                            }else{
                                $q2->where($value["condition"], '=', $value["textCondition"]);
                            }
                        endif;
                    elseif($value['formula'] == 'is not'):
                        if(strpos($value["textCondition"],  ",")):
                            $ids = explode(",", $value["textCondition"]);
                            $q2->whereNotIn($value["condition"], $ids);
                            $q2->orWhereNull($value["condition"]);
                        else:
                            $q2->where($value["condition"], 'not like', $value["textCondition"]);
                        endif;
                    elseif($value['formula'] == 'starts with'):
                        if(strpos($value["textCondition"],  ",")):
                            $ids = explode(",", $value["textCondition"]);
                            foreach($ids as $IdValue):
                                $q2->orWhere($value["condition"], 'like', $IdValue);
                            endforeach;
                        else:
                            $q2->where($value["condition"], 'like', $value["textCondition"].'%');
                        endif;
                    elseif($value['formula'] == 'is empty'):
                        if($value["condition"] == 'stage'){
                            $q2->whereNull($value["condition"]);
                        }else{
                            $q2->where(function($inq) use($value) {
                                $inq->where($value["condition"], 0);
                                $inq->where($value["condition"], '=', '');
                                $inq->orWhere($value["condition"], null);
                            });   
                        }                
                    elseif($value['formula'] == 'is not empty'):
                        $q2->whereNotNull($value["condition"]);
                        $q2->where($value["condition"], '!=', '');
                    elseif($value['formula'] == 'contains'):
                        $q2->where($value["condition"], 'like', '%'.$value["textCondition"].'%');
                    elseif($value['formula'] == 'not contains'):
                        $q2->where($value["condition"], 'not like', '%'.$value["textCondition"].'%');
                    elseif($value['formula'] == 'ends with'):
                        if(strpos($value["textCondition"],  ",")):
                            $ids = explode(",", $value["textCondition"]);
                            foreach($ids as $IdValue):
                                $q2->orWhere($value["condition"], 'like', $IdValue);
                            endforeach;
                        else:
                            $q2->where($value["condition"], 'like', '%'.$value["textCondition"]);
                        endif;
                    endif;
                elseif($value['type'] == 'calendar'):
                    $date = explode('--', $value["textCondition"]);
                    if($date[0] == $date[1]):
                        $s = substr($date[0], 0, 10);
                        $q2->whereDate($value["condition"], $s);
                    else:
                        $s = substr($date[0], 0, 10);
                        $e = substr($date[1], 0, 10);
                        $q2->whereBetween($value["condition"], [$s, $e]);
                    endif;
                elseif($value['type'] == 'email'):
                    if($value['formula'] == 'all'):
                        foreach (explode(',', $value['textCondition']) as $tcem) {
                            if(trim($tcem) == 'delivered'): $q2->where('email_delivered', '>=', 1); endif;
                            if(trim($tcem) == 'clicked'): $q2->where('email_clicked', '>=', 1); endif;
                            if(trim($tcem) == 'opened'): $q2->where('email_opened', '>=', 1); endif;
                            if(trim($tcem) == 'bounced'): $q2->where('email_bounced', '>=', 1); endif;
                            if(trim($tcem) == 'replied'): $q2->where('email_replied', '>=', 1); endif;
                        }
                    elseif($value['formula'] == 'any'):
                        $q2->where(function($inq) use ($value) { // $term is the search term on the query string
                            $txt = explode(',', $value['textCondition']);
                            foreach ($txt as $tk => $tcem) {
                                if(count($txt) == 1 || $tk == 0):
                                    if(trim($tcem) == 'delivered'): $inq->where('email_delivered', '>=', 1); endif;
                                    if(trim($tcem) == 'clicked'): $inq->where('email_clicked', '>=', 1); endif;
                                    if(trim($tcem) == 'opened'): $inq->where('email_opened', '>=', 1); endif;
                                    if(trim($tcem) == 'bounced'): $inq->where('email_bounced', '>=', 1); endif;
                                    if(trim($tcem) == 'replied'): $inq->where('email_replied', '>=', 1); endif;
                                else:
                                    if(trim($tcem) == 'delivered'): $inq->orWhere('email_delivered', '>=', 1); endif;
                                    if(trim($tcem) == 'clicked'): $inq->orWhere('email_clicked', '>=', 1); endif;
                                    if(trim($tcem) == 'opened'): $inq->orWhere('email_opened', '>=', 1); endif;
                                    if(trim($tcem) == 'bounced'): $inq->orWhere('email_bounced', '>=', 1); endif;
                                    if(trim($tcem) == 'replied'): $inq->orWhere('email_replied', '>=', 1); endif;
                                endif;
                            }
                        });
                    elseif($value['formula'] == 'none'):
                        $q2->where(function($inq) {
                            $inq->where('email_delivered', 0);
                            $inq->orWhere('email_delivered', null);
                        });
                        $q2->where(function($inq) {
                            $inq->where('email_bounced', 0);
                            $inq->orWhere('email_bounced', null);
                        });
                    endif;
                elseif($value['type'] == 'phone' && $value['condition'] == 'dsMobilePhones'):
                    if($value['formula'] == 'all'):
                        foreach (explode(',', $value['textCondition']) as $tcem) {
                            if(trim($tcem) == 'dialed'): $q2->where('mcall_attempts', '>=', 1); endif;
                            if(trim($tcem) == 'received'): $q2->where('mcall_received', '>=', 1); endif;
                        }
                    elseif($value['formula'] == 'any'):
                        $q2->where(function($inq) use ($value) { // $term is the search term on the query string
                            $txt = explode(',', $value['textCondition']);
                            foreach ($txt as $tk => $tcem) {
                                if(count($txt) == 1 || $tk == 0):
                                    if(trim($tcem) == 'dialed'): $inq->where('mcall_attempts', '>=', 1); endif;
                                    if(trim($tcem) == 'received'): $inq->where('mcall_received', '>=', 1); endif;
                                else:
                                    if(trim($tcem) == 'dialed'): $inq->orWhere('mcall_attempts', '>=', 1); endif;
                                    if(trim($tcem) == 'received'): $inq->orWhere('mcall_received', '>=', 1); endif;
                                endif;
                            }
                        });
                    elseif($value['formula'] == 'none'):
                        $q2->where(function($inq) {
                            $inq->where('mcall_attempts', 0);
                            $inq->orWhere('mcall_attempts', null);
                        });
                        $q2->where(function($inq) {
                            $inq->where('mcall_received', 0);
                            $inq->orWhere('mcall_received', null);
                        });
                    endif;
                elseif($value['type'] == 'phone' && $value['condition'] == 'dsHomePhones'):
                    if($value['formula'] == 'all'):
                        foreach (explode(',', $value['textCondition']) as $tcem) {
                            if(trim($tcem) == 'dialed'): $q2->where('hcall_attempts', '>=', 1); endif;
                            if(trim($tcem) == 'received'): $q2->where('hcall_received', '>=', 1); endif;
                        }
                    elseif($value['formula'] == 'any'):
                        $q2->where(function($inq) use ($value) { // $term is the search term on the query string
                            $txt = explode(',', $value['textCondition']);
                            foreach ($txt as $tk => $tcem) {
                                if(count($txt) == 1 || $tk == 0):
                                    if(trim($tcem) == 'dialed'): $inq->where('hcall_attempts', '>=', 1); endif;
                                    if(trim($tcem) == 'received'): $inq->where('hcall_received', '>=', 1); endif;
                                else:
                                    if(trim($tcem) == 'dialed'): $inq->orWhere('hcall_attempts', '>=', 1); endif;
                                    if(trim($tcem) == 'received'): $inq->orWhere('hcall_received', '>=', 1); endif;
                                endif;
                            }
                        });
                    elseif($value['formula'] == 'none'):
                        $q2->where(function($inq) {
                            $inq->where('hcall_attempts', 0);
                            $inq->orWhere('hcall_attempts', null);
                        });
                        $q2->where(function($inq) {
                            $inq->where('hcall_received', 0);
                            $inq->orWhere('hcall_received', null);
                        });
                    endif;
                elseif($value['type'] == 'phone' && $value['condition'] == 'dsWorkPhones'):
                    if($value['formula'] == 'all'):
                        foreach (explode(',', $value['textCondition']) as $tcem) {
                            if(trim($tcem) == 'dialed'): $q2->where('wcall_attempts', '>=', 1); endif;
                            if(trim($tcem) == 'received'): $q2->where('wcall_received', '>=', 1); endif;
                        }
                    elseif($value['formula'] == 'any'):
                        $q2->where(function($inq) use ($value) { // $term is the search term on the query string
                            $txt = explode(',', $value['textCondition']);
                            foreach ($txt as $tk => $tcem) {
                                if(count($txt) == 1 || $tk == 0):
                                    if(trim($tcem) == 'dialed'): $inq->where('wcall_attempts', '>=', 1); endif;
                                    if(trim($tcem) == 'received'): $inq->where('wcall_received', '>=', 1); endif;
                                else:
                                    if(trim($tcem) == 'dialed'): $inq->orWhere('wcall_attempts', '>=', 1); endif;
                                    if(trim($tcem) == 'received'): $inq->orWhere('wcall_received', '>=', 1); endif;
                                endif;
                            }
                        });
                    elseif($value['formula'] == 'none'):
                        $q2->where(function($inq) {
                            $inq->where('wcall_attempts', 0);
                            $inq->orWhere('wcall_attempts', null);
                        });
                        $q2->where(function($inq) {
                            $inq->where('wcall_received', 0);
                            $inq->orWhere('wcall_received', null);
                        });
                    endif;
                endif;
            }
            $records1 = $q1->pluck('contacts.record_id');//get all ids
            $records2 = $q2->get();
            $totalContacts = $q1->count();
            $totalEmails = OutreachMailings::where("outreach_mailings.state", "=", "delivered")
                ->leftJoin('contacts', 'contacts.record_id', '=', 'outreach_mailings.contact_id')
                ->where('outreach_mailings.contact_id', '>', 0)
                ->where("outreach_mailings.deliveredAt", "!=", null)
                ->whereIn("contacts.record_id", $records1)
                ->count();
            $totalCalls = FivenineCallLogs::where('fivenine_call_logs.dial_attempts', '>', 0)
                ->leftJoin('contacts', 'contacts.record_id', '=', 'fivenine_call_logs.record_id')
                ->whereIn("contacts.record_id", $records1)
                ->count();
            foreach($records2 as $value){
                $paiDataS[] = $value["count"];
                $maplabelsS[] = number_format(($value["count"]*100)/$totalContacts, 1, '.', '')."% - ".$stageList[$value["stage"]];
                $stageId = intval($value["stage"]);
                
                //Email : get delivered email number on the base of stage
                $e = OutreachMailings::where("outreach_mailings.state", "=", "delivered")
                    ->leftJoin('contacts', 'contacts.record_id', '=', 'outreach_mailings.contact_id')
                    ->where('outreach_mailings.contact_id', '>', 0)
                    ->where("outreach_mailings.deliveredAt", "!=", null)
                    ->where('contacts.stage', '=', $stageId)
                    ->whereIn("contacts.record_id", $records1)
                    ->count();
                $paiDataE[] = $e;
                $maplabelsE[] = number_format(($e*100)/$totalEmails, 1, '.', '')."% - ".$stageList[$value["stage"]];

                //Call : get attempted
                $c = FivenineCallLogs::where('fivenine_call_logs.dial_attempts', '>', 0)
                    ->leftJoin('contacts', 'contacts.record_id', '=', 'fivenine_call_logs.record_id')
                    ->where('contacts.stage', '=', $stageId)
                    ->whereIn("contacts.record_id", $records1)
                    ->count();
                $paiDataC[] = $c;
                $maplabelsC[] = number_format(($c*100)/$totalCalls, 1, '.', '')."% - ".$stageList[$value["stage"]];
                // 
            }
        else:
            $totalContacts = Contacts::count();
            $records = Contacts::select(DB::raw('count(contacts.stage) as count, contacts.stage'))
                ->groupBy('contacts.stage')
                ->get();
            $totalEmails = OutreachMailings::where("outreach_mailings.state", "=", "delivered")
                ->leftJoin('contacts', 'contacts.record_id', '=', 'outreach_mailings.contact_id')
                ->where('outreach_mailings.contact_id', '>', 0)
                ->where("outreach_mailings.deliveredAt", "!=", null)        
                ->count();
            $totalCalls = FivenineCallLogs::where('fivenine_call_logs.dial_attempts', '>', 0)
                ->leftJoin('contacts', 'contacts.record_id', '=', 'fivenine_call_logs.record_id')
                ->count();
            foreach($records as $value){
                $paiDataS[] = $value["count"];
                $maplabelsS[] = number_format(($value["count"]*100)/$totalContacts, 1, '.', '')."% - ".$stageList[$value["stage"]];
                $stageId = intval($value["stage"]);
                
                //Email : get delivered email number on the base of stage
                $e = OutreachMailings::where("outreach_mailings.state", "=", "delivered")
                ->leftJoin('contacts', 'contacts.record_id', '=', 'outreach_mailings.contact_id')
                ->where('outreach_mailings.contact_id', '>', 0)
                ->where("outreach_mailings.deliveredAt", "!=", null)
                ->where('contacts.stage', '=', $stageId)
                ->count();
                $paiDataE[] = $e;
                $maplabelsE[] = number_format(($e*100)/$totalEmails, 1, '.', '')."% - ".$stageList[$value["stage"]];

                //Call : get attempted
                $c = FivenineCallLogs::where('fivenine_call_logs.dial_attempts', '>', 0)
                ->leftJoin('contacts', 'contacts.record_id', '=', 'fivenine_call_logs.record_id')
                ->where('contacts.stage', '=', $stageId)
                ->count();
                $paiDataC[] = $c;
                $maplabelsC[] = number_format(($c*100)/$totalCalls, 1, '.', '')."% - ".$stageList[$value["stage"]];
                // 
            }
        endif;

        return [
            "paiDataS" => $paiDataS, "maplabelsS" => $maplabelsS, 
            "paiDataE" => $paiDataE, "maplabelsE" => $maplabelsE, 
            "paiDataC" => $paiDataC, "maplabelsC" => $maplabelsC, 
        ];
        
        
        
    }

    public function chart1()
    {
        
        //return Contacts::selectRaw("SELECT `stage`, COUNT(*) FROM `contacts` GROUP BY `stage`")->get();
        //Stage
        $totalContacts = Contacts::count();
        $records = Contacts::select(DB::raw('count(contacts.stage) as count, contacts.stage'))
        ->groupBy('contacts.stage')
        ->get();
        $totalEmails = OutreachMailings::where("outreach_mailings.state", "=", "delivered")
        ->leftJoin('contacts', 'contacts.record_id', '=', 'outreach_mailings.contact_id')
        ->where('outreach_mailings.contact_id', '>', 0)
        ->where("outreach_mailings.deliveredAt", "!=", null)        
        ->count();
        $totalCalls = FivenineCallLogs::where('fivenine_call_logs.dial_attempts', '>', 0)
            ->leftJoin('contacts', 'contacts.record_id', '=', 'fivenine_call_logs.record_id')
            ->count();

        $stageRecords = Stages::select("oid", "name")->get();
        $stageList = [];
        foreach($stageRecords as $value){
            $stageList[$value["oid"]] = $value["name"];
        }
        $paiDataS = [];
        $maplabelsS = [];
        $paiDataE = [];
        $maplabelsE = [];
        $paiDataC = [];
        $maplabelsC = [];
        foreach($records as $value){
            $paiDataS[] = $value["count"];
            $maplabelsS[] = number_format(($value["count"]*100)/$totalContacts, 1, '.', '')."% - ".$stageList[$value["stage"]];
            $stageId = intval($value["stage"]);
            
            //Email : get delivered email number on the base of stage
            $e = OutreachMailings::where("outreach_mailings.state", "=", "delivered")
            ->leftJoin('contacts', 'contacts.record_id', '=', 'outreach_mailings.contact_id')
            ->where('outreach_mailings.contact_id', '>', 0)
            ->where("outreach_mailings.deliveredAt", "!=", null)
            ->where('contacts.stage', '=', $stageId)
            ->count();
            $paiDataE[] = $e;
            $maplabelsE[] = number_format(($e*100)/$totalEmails, 1, '.', '')."% - ".$stageList[$value["stage"]];

            //Call : get attempted
            $c = FivenineCallLogs::where('fivenine_call_logs.dial_attempts', '>', 0)
            ->leftJoin('contacts', 'contacts.record_id', '=', 'fivenine_call_logs.record_id')
            ->where('contacts.stage', '=', $stageId)
            ->count();
            $paiDataC[] = $c;
            $maplabelsC[] = number_format(($c*100)/$totalCalls, 1, '.', '')."% - ".$stageList[$value["stage"]];
        }
        
        return [
            "paiDataS" => $paiDataS, "maplabelsS" => $maplabelsS, 
            "paiDataE" => $paiDataE, "maplabelsE" => $maplabelsE, 
            "paiDataC" => $paiDataC, "maplabelsC" => $maplabelsC, 
        ];
    }

    public function getProspectChartsData(){

    }
    public function getEmailChartsData(){
        
    }
    public function getCallChartsData(){
        
    }
}
