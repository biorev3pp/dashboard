<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Contacts;
use App\Models\Stages;
use App\Models\DatasetGroups;
use App\Models\FivenineCallLogs;

class DatasetsController extends Controller
{
    public function getAllData(Request $request){
        $recordPerPage  = $request->recordPerPage;
        $search = $request->textSearch;
        $sortby = $request->sortby;
        $sort = $request->sort;

        $records = Contacts::select('contacts.id', 'contacts.record_id', 'contacts.first_name', 'contacts.last_name', 'contacts.title', 'contacts.company', 'contacts.mobilePhones', 'contacts.workPhones', 'contacts.homePhones', 'contacts.emails', 'contacts.outreach_touched_at', 'contacts.outreach_created_at', 'contacts.last_agent_dispo_time', 'contacts.last_agent', 'contacts.mcall_attempts', 'contacts.mcall_received', 'contacts.wcall_attempts', 'contacts.wcall_received', 'contacts.hcall_attempts', 'contacts.hcall_received', 'stages.id as stage', 'stages.name as stage_name', 'stages.css as stage_css', 'contacts.dataset','contacts.outreach_tag')
        ->addSelect(['email_delivered' => DB::table('outreach_mailings')->selectRaw('count(*)')->whereNotNull("outreach_mailings.deliveredAt")->whereColumn('outreach_mailings.contact_id', 'contacts.record_id')])
        ->addSelect(['email_opened' => DB::table('outreach_mailings')->selectRaw('count(*)')->whereNotNull("outreach_mailings.openedAt")->whereColumn('outreach_mailings.contact_id', 'contacts.record_id')])
        ->addSelect(['email_clicked' => DB::table('outreach_mailings')->selectRaw('count(*)')->whereNotNull("outreach_mailings.clickedAt")->whereColumn('outreach_mailings.contact_id', 'contacts.record_id')])
        ->addSelect(['email_replied' => DB::table('outreach_mailings')->selectRaw('count(*)')->whereNotNull("outreach_mailings.repliedAt")->whereColumn('outreach_mailings.contact_id', 'contacts.record_id')])
        ->addSelect(['email_bounced' => DB::table('outreach_mailings')->selectRaw('count(*)')->whereNotNull("outreach_mailings.bouncedAt")->whereColumn('outreach_mailings.contact_id', 'contacts.record_id')])
            ->leftjoin('stages', 'stages.oid', '=', 'contacts.stage');
        if($search):
            if(preg_match('([a-zA-Z])', $search) == false) {
                $search = str_ireplace( array( '\'', '"',
                ',' , ';', '<', '>', ')', '(', '-', ' '), '', $search);
            } 
            $records = $records->when($search, function($query, $search){
                $query->where(function($q) use ($search){
                    $q->where('contacts.name', 'like', '%'.$search.'%')->orWhere('contacts.first_name', 'like', '%'.$search.'%')->orWhere('contacts.last_name', 'like', '%'.$search.'%')->orWhere('contacts.emails', 'like', '%'.$search.'%')->orWhere('contacts.company', 'like', '%'.$search.'%')->orWhere('contacts.mnumber', 'like', '%'.$search.'%')->orWhere('contacts.hnumber', 'like', '%'.$search.'%')->orWhere('contacts.wnumber', 'like', '%'.$search.'%')->orWhere('contacts.mobilePhones', 'like', '%'.$search.'%')->orWhere('contacts.workPhones', 'like', '%'.$search.'%')->orWhere('contacts.homePhones', 'like', '%'.$search.'%');
                });
            });
        endif; 

        if(count($request->input('filterConditionsArray')) > 0):
            $filterConditions = true;
            $filterConditionsArray = $request->input('filterConditionsArray');
            $q = $records;
            foreach($filterConditionsArray as $value){
                if( ($value['type'] == 'textbox') || ($value['type'] == 'dropdown') ):
                    if($value['formula'] == 'is'):
                        if(strpos($value["textCondition"],  ",")):
                            $ids = explode(",", $value["textCondition"]);
                            $q->whereIn($value["condition"], $ids);
                        else:
                            if(in_array($value["condition"], ["mnumber", "wnumber", "hnumber"])){
                                $mobile = $this->__NumberFormater($value["textCondition"]);
                                if(strlen($mobile) >= 10){
                                    $q->where($value["condition"], 'like', "%".$mobile."%");
                                }else{
                                    $q->where($value["condition"], 'like', $mobile);
                                }
                            }else{
                                //dd($value["textConditionLabel"]);
                                if($value["textCondition"] == "None"){
                                    $q->whereNull($value["condition"]);
                                }else{
                                    $q->where($value["condition"], '=', $value["textCondition"]);
                                }
                            }
                        endif;
                    elseif($value['formula'] == 'is not'):
                        if(strpos($value["textCondition"],  ",")):
                            $ids = explode(",", $value["textCondition"]);
                            $q->whereNotIn($value["condition"], $ids);
                            $q->orWhereNull($value["condition"]);
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
                        if($value["condition"] == 'stage'){
                            $q->whereNull($value["condition"]);
                        }else{
                            $q->where(function($inq) use($value) {
                                $inq->where($value["condition"], 0);
                                $inq->where($value["condition"], '=', '');
                                $inq->orWhere($value["condition"], null);
                            });   
                        }                
                    elseif($value['formula'] == 'is not empty'):
                        $q->whereNotNull($value["condition"]);
                        $q->where($value["condition"], '!=', '');
                    elseif($value['formula'] == 'contains'):
                        $q->where($value["condition"], 'like', '%'.$value["textCondition"].'%');
                    elseif($value['formula'] == 'not contains'):
                        $q->where($value["condition"], 'not like', '%'.$value["textCondition"].'%');
                    elseif($value['formula'] == 'ends with'):
                        if(strpos($value["textCondition"],  ",")):
                            $ids = explode(",", $value["textCondition"]);
                            foreach($ids as $IdValue):
                                $q->orWhere($value["condition"], 'like', $IdValue);
                            endforeach;
                        else:
                            $q->where($value["condition"], 'like', '%'.$value["textCondition"]);
                        endif;
                    endif;
                elseif($value['type'] == 'calendar'):
                    $date = explode('--', $value["textCondition"]);
                    if($date[0] == $date[1]):
                        $s = substr($date[0], 0, 10);
                        $q->whereDate($value["condition"], $s);
                    else:
                        $s = substr($date[0], 0, 10);
                        $e = substr($date[1], 0, 10);
                        $q->whereBetween($value["condition"], [$s, $e]);
                    endif;
                elseif($value['type'] == 'email'):
                    if($value['formula'] == 'all'):
                        foreach (explode(',', $value['textCondition']) as $tcem) {
                            if(trim($tcem) == 'delivered'): $q->where('email_delivered', '>=', 1); endif;
                            if(trim($tcem) == 'clicked'): $q->where('email_clicked', '>=', 1); endif;
                            if(trim($tcem) == 'opened'): $q->where('email_opened', '>=', 1); endif;
                            if(trim($tcem) == 'bounced'): $q->where('email_bounced', '>=', 1); endif;
                            if(trim($tcem) == 'replied'): $q->where('email_replied', '>=', 1); endif;
                        }
                    elseif($value['formula'] == 'any'):
                        $q->where(function($inq) use ($value) { // $term is the search term on the query string
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
                        $q->where(function($inq) {
                            $inq->where('email_delivered', 0);
                            $inq->orWhere('email_delivered', null);
                        });
                        $q->where(function($inq) {
                            $inq->where('email_bounced', 0);
                            $inq->orWhere('email_bounced', null);
                        });
                    endif;
                elseif($value['type'] == 'phone' && $value['condition'] == 'dsMobilePhones'):
                    if($value['formula'] == 'all'):
                        foreach (explode(',', $value['textCondition']) as $tcem) {
                            if(trim($tcem) == 'dialed'): $q->where('mcall_attempts', '>=', 1); endif;
                            if(trim($tcem) == 'received'): $q->where('mcall_received', '>=', 1); endif;
                        }
                    elseif($value['formula'] == 'any'):
                        $q->where(function($inq) use ($value) { // $term is the search term on the query string
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
                        $q->where(function($inq) {
                            $inq->where('mcall_attempts', 0);
                            $inq->orWhere('mcall_attempts', null);
                        });
                        $q->where(function($inq) {
                            $inq->where('mcall_received', 0);
                            $inq->orWhere('mcall_received', null);
                        });
                    endif;
                elseif($value['type'] == 'phone' && $value['condition'] == 'dsHomePhones'):
                    if($value['formula'] == 'all'):
                        foreach (explode(',', $value['textCondition']) as $tcem) {
                            if(trim($tcem) == 'dialed'): $q->where('hcall_attempts', '>=', 1); endif;
                            if(trim($tcem) == 'received'): $q->where('hcall_received', '>=', 1); endif;
                        }
                    elseif($value['formula'] == 'any'):
                        $q->where(function($inq) use ($value) { // $term is the search term on the query string
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
                        $q->where(function($inq) {
                            $inq->where('hcall_attempts', 0);
                            $inq->orWhere('hcall_attempts', null);
                        });
                        $q->where(function($inq) {
                            $inq->where('hcall_received', 0);
                            $inq->orWhere('hcall_received', null);
                        });
                    endif;
                elseif($value['type'] == 'phone' && $value['condition'] == 'dsWorkPhones'):
                    if($value['formula'] == 'all'):
                        foreach (explode(',', $value['textCondition']) as $tcem) {
                            if(trim($tcem) == 'dialed'): $q->where('wcall_attempts', '>=', 1); endif;
                            if(trim($tcem) == 'received'): $q->where('wcall_received', '>=', 1); endif;
                        }
                    elseif($value['formula'] == 'any'):
                        $q->where(function($inq) use ($value) { // $term is the search term on the query string
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
                        $q->where(function($inq) {
                            $inq->where('wcall_attempts', 0);
                            $inq->orWhere('wcall_attempts', null);
                        });
                        $q->where(function($inq) {
                            $inq->where('wcall_received', 0);
                            $inq->orWhere('wcall_received', null);
                        });
                    endif;
                endif;
            }
            $records = $q;
        endif;
 
        /* $records->withCount(['totalemail', 'totalopen', 'totalclick', 'totalreply', 'totalrcall', 'totalwrcall', 'totalbounced'])
                ->with(['totalcall', 'totalwcall', 'calllogs'])
                ->join('stages', 'stages.oid', '=', 'contacts.stage')
                ->orderBy('contacts.outreach_touched_at', 'desc')
                ->paginate($recordPerPage);
        return $records; */
        
        if($request->input("sortBy") == 'asc'):
            $records = $records->orderBy( $request->input('sortType'))->paginate($recordPerPage); 
        else:
            $records = $records->orderByDesc($request->input('sortType'))->paginate($recordPerPage);    
        endif;
        //$records = $records->orderByDesc('record_id')->paginate($recordPerPage);
        //$records = $records->orderBy('contacts.outreach_touched_at', 'desc')->paginate($recordPerPage);
        return $records;
    }
    
    public function getFullData(Request $request){
        $recordPerPage  = $request->recordPerPage;
        $search = $request->textSearch;
        $sortby = $request->sortby;
        $sort = $request->sort;

        $records = Contacts::select('contacts.id', 'contacts.record_id', 'contacts.first_name', 'contacts.last_name', 'contacts.title', 'contacts.company', 'contacts.mobilePhones', 'contacts.workPhones', 'contacts.homePhones', 'contacts.emails', 'contacts.outreach_touched_at', 'contacts.outreach_created_at', 'contacts.last_agent_dispo_time', 'contacts.last_agent', 'contacts.email_delivered', 'contacts.email_opened', 'contacts.email_clicked', 'contacts.email_replied', 'contacts.email_bounced', 'contacts.mcall_attempts', 'contacts.mcall_received', 'contacts.wcall_attempts', 'contacts.wcall_received', 'contacts.hcall_attempts', 'contacts.hcall_received', 'stages.id as stage', 'stages.name as stage_name', 'stages.css as stage_css', 'contacts.dataset','contacts.outreach_tag')
            ->leftjoin('stages', 'stages.oid', '=', 'contacts.stage');
        if($search):
            $records = $records->when($search, function($query, $search){
                $query->where(function($q) use ($search){
                    $q->where('contacts.name', 'like', '%'.$search.'%')->orWhere('contacts.first_name', 'like', '%'.$search.'%')->orWhere('contacts.last_name', 'like', '%'.$search.'%')->orWhere('contacts.emails', 'like', '%'.$search.'%')->orWhere('contacts.company', 'like', '%'.$search.'%')->orWhere('contacts.mnumber', 'like', '%'.$search.'%')->orWhere('contacts.hnumber', 'like', '%'.$search.'%')->orWhere('contacts.wnumber', 'like', '%'.$search.'%');
                });
            });
        endif; 

        if(count($request->input('filterConditionsArray')) > 0):
            $filterConditions = true;
            $filterConditionsArray = $request->input('filterConditionsArray');
            $q = $records;
            foreach($filterConditionsArray as $value){
                if( ($value['type'] == 'textbox') || ($value['type'] == 'dropdown') ):
                    if($value['formula'] == 'is'):
                        if(strpos($value["textCondition"],  ",")):
                            $ids = explode(",", $value["textCondition"]);
                            $q->whereIn($value["condition"], $ids);
                        else:
                            if(in_array($value["condition"], ["mnumber", "wnumber", "hnumber"])){
                                $mobile = $this->__NumberFormater($value["textCondition"]);
                                if(strlen($mobile) >= 10){
                                    $q->where($value["condition"], 'like', "%".$mobile."%");
                                }else{
                                    $q->where($value["condition"], 'like', $mobile);
                                }
                            }else{
                                $q->where($value["condition"], '=', $value["textCondition"]);
                            }
                        endif;
                    elseif($value['formula'] == 'is not'):
                        if(strpos($value["textCondition"],  ",")):
                            $ids = explode(",", $value["textCondition"]);
                            $q->whereNotIn($value["condition"], $ids);
                            $q->orWhereNull($value["condition"]);
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
                        if($value["condition"] == 'stage'){
                            $q->whereNull($value["condition"]);
                        }else{
                            $q->where(function($inq) use($value) {
                                $inq->where($value["condition"], 0);
                                $inq->where($value["condition"], '=', '');
                                $inq->orWhere($value["condition"], null);
                            });   
                        }                
                    elseif($value['formula'] == 'is not empty'):
                        $q->whereNotNull($value["condition"]);
                        $q->where($value["condition"], '!=', '');
                    elseif($value['formula'] == 'contains'):
                        $q->where($value["condition"], 'like', '%'.$value["textCondition"].'%');
                    elseif($value['formula'] == 'not contains'):
                        $q->where($value["condition"], 'not like', '%'.$value["textCondition"].'%');
                    elseif($value['formula'] == 'ends with'):
                        if(strpos($value["textCondition"],  ",")):
                            $ids = explode(",", $value["textCondition"]);
                            foreach($ids as $IdValue):
                                $q->orWhere($value["condition"], 'like', $IdValue);
                            endforeach;
                        else:
                            $q->where($value["condition"], 'like', '%'.$value["textCondition"]);
                        endif;
                    endif;
                elseif($value['type'] == 'calendar'):
                    $date = explode('--', $value["textCondition"]);
                    if($date[0] == $date[1]):
                        $s = substr($date[0], 0, 10);
                        $q->whereDate($value["condition"], $s);
                    else:
                        $s = substr($date[0], 0, 10);
                        $e = substr($date[1], 0, 10);
                        $q->whereBetween($value["condition"], [$s, $e]);
                    endif;
                elseif($value['type'] == 'email'):
                    if($value['formula'] == 'all'):
                        foreach (explode(',', $value['textCondition']) as $tcem) {
                            if(trim($tcem) == 'delivered'): $q->where('email_delivered', '>=', 1); endif;
                            if(trim($tcem) == 'clicked'): $q->where('email_clicked', '>=', 1); endif;
                            if(trim($tcem) == 'opened'): $q->where('email_opened', '>=', 1); endif;
                            if(trim($tcem) == 'bounced'): $q->where('email_bounced', '>=', 1); endif;
                            if(trim($tcem) == 'replied'): $q->where('email_replied', '>=', 1); endif;
                        }
                    elseif($value['formula'] == 'any'):
                        $q->where(function($inq) use ($value) { // $term is the search term on the query string
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
                        $q->where(function($inq) {
                            $inq->where('email_delivered', 0);
                            $inq->orWhere('email_delivered', null);
                        });
                        $q->where(function($inq) {
                            $inq->where('email_bounced', 0);
                            $inq->orWhere('email_bounced', null);
                        });
                    endif;
                elseif($value['type'] == 'phone' && $value['condition'] == 'dsMobilePhones'):
                    if($value['formula'] == 'all'):
                        foreach (explode(',', $value['textCondition']) as $tcem) {
                            if(trim($tcem) == 'dialed'): $q->where('mcall_attempts', '>=', 1); endif;
                            if(trim($tcem) == 'received'): $q->where('mcall_received', '>=', 1); endif;
                        }
                    elseif($value['formula'] == 'any'):
                        $q->where(function($inq) use ($value) { // $term is the search term on the query string
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
                        $q->where(function($inq) {
                            $inq->where('mcall_attempts', 0);
                            $inq->orWhere('mcall_attempts', null);
                        });
                        $q->where(function($inq) {
                            $inq->where('mcall_received', 0);
                            $inq->orWhere('mcall_received', null);
                        });
                    endif;
                elseif($value['type'] == 'phone' && $value['condition'] == 'dsHomePhones'):
                    if($value['formula'] == 'all'):
                        foreach (explode(',', $value['textCondition']) as $tcem) {
                            if(trim($tcem) == 'dialed'): $q->where('hcall_attempts', '>=', 1); endif;
                            if(trim($tcem) == 'received'): $q->where('hcall_received', '>=', 1); endif;
                        }
                    elseif($value['formula'] == 'any'):
                        $q->where(function($inq) use ($value) { // $term is the search term on the query string
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
                        $q->where(function($inq) {
                            $inq->where('hcall_attempts', 0);
                            $inq->orWhere('hcall_attempts', null);
                        });
                        $q->where(function($inq) {
                            $inq->where('hcall_received', 0);
                            $inq->orWhere('hcall_received', null);
                        });
                    endif;
                elseif($value['type'] == 'phone' && $value['condition'] == 'dsWorkPhones'):
                    if($value['formula'] == 'all'):
                        foreach (explode(',', $value['textCondition']) as $tcem) {
                            if(trim($tcem) == 'dialed'): $q->where('wcall_attempts', '>=', 1); endif;
                            if(trim($tcem) == 'received'): $q->where('wcall_received', '>=', 1); endif;
                        }
                    elseif($value['formula'] == 'any'):
                        $q->where(function($inq) use ($value) { // $term is the search term on the query string
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
                        $q->where(function($inq) {
                            $inq->where('wcall_attempts', 0);
                            $inq->orWhere('wcall_attempts', null);
                        });
                        $q->where(function($inq) {
                            $inq->where('wcall_received', 0);
                            $inq->orWhere('wcall_received', null);
                        });
                    endif;
                endif;
            }
            $records = $q;
        endif;
        
        $records = $records->orderBy( $request->input('sortType'))->get();
        return $records->pluck("id");
    }
    public function getGraphSearchCriteria(){
        return DB::table('graph_search_criterias')->orderBy('title')->get();
    }
    public function chartFilter(Request $request) 
    {
        $historyKey = [];
        $historyValue = [];
        
        if($request->input("primary_filter")) {            
            $graphFilter = $request->input("primary_filter.value");
        } else {
            $graphFilter = DB::table("graph_search_criterias")->orderBy('order_no', 'asc')->first();
            $graphFilter = $graphFilter->value;
        }

        if($request->input("mode_status") == 1) 
        {
            $hkey = [];

            if(count($request->input("historyKey")) > 0) {
                $hkey = $request->input("historyKey");
                array_push($hkey, $graphFilter);     
            }else {
                $hkey[] = $graphFilter;
            }
            $gNext = DB::table("graph_search_criterias")->whereNotIn("value",  $hkey)->orderBy('order_no', 'asc')->first();
            $graphFilter = $gNext->value;

            $historyKey = $hkey;
            if(count($historyKey) > 0){
                foreach($historyKey as $value){
                    $g = DB::table("graph_search_criterias")->where("value", "=", $value)->orderBy('order_no', 'asc')->first();
                    $historyValue[] = $g->title;
                }
            }
        }
        elseif($request->input("jump_status") == 1) 
        {
            $hkey = [];

            if(count($request->input("historyKey")) > 0) {
                $hkey = $request->input("historyKey");
            }else {
                $hkey[] = $graphFilter;
            }
            $gNext = DB::table("graph_search_criterias")->where("value",  $graphFilter)->orderBy('order_no', 'asc')->first();
            $graphFilter = $gNext->value;

            $historyKey = $hkey;
            if(count($historyKey) > 0){
                foreach($historyKey as $value){
                    $g = DB::table("graph_search_criterias")->where("value", "=", $value)->orderBy('order_no', 'asc')->first();
                    $historyValue[] = $g->title;
                }
            }
        }
        elseif($request->input("back_status") == 1) 
        {
            $old = $request->input("historyKey");
            $graphFilter = $old[count($old)-1];
            array_pop($old);

            $historyKey = $old;
            if(count($historyKey) > 0){
                foreach($historyKey as $value){
                    $g = DB::table("graph_search_criterias")->where("value", "=", $value)->first();
                    $historyValue[] = $g->title;
                }
            } else {
                $historyValue = [];
            }
        }
        else {
            $historyKey = $request->input('historyKey');
            $historyValue = $request->input('historyValue');
        }
        $records = Contacts::select(DB::raw("count(contacts.$graphFilter) as count, contacts.$graphFilter"))->groupBy("contacts.$graphFilter");
        $recordsNull = Contacts::select('contacts.*');
        if(count($request->input('filterConditionsArray')) > 0):
            $filterConditions = true;
            $filterConditionsArray = $request->input('filterConditionsArray');
            $q = $records;
            $qNull = $recordsNull;
            foreach($filterConditionsArray as $value){
                //$historyKey[] = $value["condition"];
                if( ($value['type'] == 'textbox') || ($value['type'] == 'dropdown') ):
                    if($value['formula'] == 'is'):
                        if(strpos($value["textCondition"], "None") !== false):
                            $q->whereNull($value["condition"]);
                            $qNull->whereNull($value["condition"]);
                        elseif(strpos($value["textCondition"],  ",")):
                            $ids = explode(",", $value["textCondition"]);
                            $q->whereIn($value["condition"], $ids);
                            $qNull->whereIn($value["condition"], $ids);
                        else:
                            if(in_array($value["condition"], ["mnumber", "wnumber", "hnumber"])){
                                $mobile = $this->__NumberFormater($value["textCondition"]);
                                if(strlen($mobile) >= 10){
                                    $q->where($value["condition"], 'like', "%".$mobile."%");
                                    $qNull->where($value["condition"], 'like', "%".$mobile."%");
                                }else{
                                    $q->where($value["condition"], 'like', $mobile);
                                    $qNull->where($value["condition"], 'like', $mobile);
                                }
                            }else{
                                $q->where($value["condition"], '=', $value["textCondition"]);
                                $qNull->where($value["condition"], '=', $value["textCondition"]);
                            }
                        endif;
                    elseif($value['formula'] == 'is not'):
                        if(strpos($value["textCondition"],  ",")):
                            $ids = explode(",", $value["textCondition"]);
                            $q->whereNotIn($value["condition"], $ids);
                            $qNull->whereNotIn($value["condition"], $ids);
                            $q->orWhereNull($value["condition"]);
                            $qNull->orWhereNull($value["condition"]);
                        else:
                            $q->where($value["condition"], 'not like', $value["textCondition"]);
                            $qNull->where($value["condition"], 'not like', $value["textCondition"]);
                        endif;
                    elseif($value['formula'] == 'starts with'):
                        if(strpos($value["textCondition"],  ",")):
                            $ids = explode(",", $value["textCondition"]);
                            foreach($ids as $IdValue):
                                $q->orWhere($value["condition"], 'like', $IdValue);
                                $qNull->orWhere($value["condition"], 'like', $IdValue);
                            endforeach;
                        else:
                            $q->where($value["condition"], 'like', $value["textCondition"].'%');
                            $qNull->where($value["condition"], 'like', $value["textCondition"].'%');
                        endif;
                    elseif($value['formula'] == 'is empty'):
                        if($value["condition"] == 'stage'){
                            $q->whereNull($value["condition"]);
                            $qNull->whereNull($value["condition"]);
                        }else{
                            $q->where(function($inq) use($value) {
                                $inq->where($value["condition"], 0);
                                $inq->where($value["condition"], '=', '');
                                $inq->orWhere($value["condition"], null);
                            }); 
                            $qNull->where(function($inq) use($value) {
                                $inq->where($value["condition"], 0);
                                $inq->where($value["condition"], '=', '');
                                $inq->orWhere($value["condition"], null);
                            });   
                        }                
                    elseif($value['formula'] == 'is not empty'):
                        $q->whereNotNull($value["condition"]);
                        $qNull->whereNotNull($value["condition"]);
                        $q->where($value["condition"], '!=', '');
                        $qNull->where($value["condition"], '!=', '');
                    elseif($value['formula'] == 'contains'):
                        $q->where($value["condition"], 'like', '%'.$value["textCondition"].'%');
                        $qNull->where($value["condition"], 'like', '%'.$value["textCondition"].'%');
                    elseif($value['formula'] == 'not contains'):
                        $q->where($value["condition"], 'not like', '%'.$value["textCondition"].'%');
                        $qNull->where($value["condition"], 'not like', '%'.$value["textCondition"].'%');
                    elseif($value['formula'] == 'ends with'):
                        if(strpos($value["textCondition"],  ",")):
                            $ids = explode(",", $value["textCondition"]);
                            foreach($ids as $IdValue):
                                $q->orWhere($value["condition"], 'like', $IdValue);
                                $qNull->orWhere($value["condition"], 'like', $IdValue);
                            endforeach;
                        else:
                            $q->where($value["condition"], 'like', '%'.$value["textCondition"]);
                            $qNull->where($value["condition"], 'like', '%'.$value["textCondition"]);
                        endif;
                    endif;
                elseif($value['type'] == 'calendar'):
                    $date = explode('--', $value["textCondition"]);
                    if($date[0] == $date[1]):
                        $s = substr($date[0], 0, 10);
                        $q->whereDate($value["condition"], $s);
                        $qNull->whereDate($value["condition"], $s);
                    else:
                        $s = substr($date[0], 0, 10);
                        $e = substr($date[1], 0, 10);
                        $q->whereBetween($value["condition"], [$s, $e]);
                        $qNull->whereBetween($value["condition"], [$s, $e]);
                    endif;
                elseif($value['type'] == 'email'):
                    if($value['formula'] == 'all'):
                        foreach (explode(',', $value['textCondition']) as $tcem) {
                            if(trim($tcem) == 'delivered'): 
                                $q->where('email_delivered', '>=', 1); 
                                $qNull->where('email_delivered', '>=', 1); 
                            endif;
                            if(trim($tcem) == 'clicked'): 
                                $q->where('email_clicked', '>=', 1); 
                                $qNull->where('email_clicked', '>=', 1); 
                            endif;
                            if(trim($tcem) == 'opened'): 
                                $q->where('email_opened', '>=', 1); 
                                $qNull->where('email_opened', '>=', 1); 
                            endif;
                            if(trim($tcem) == 'bounced'): 
                                $q->where('email_bounced', '>=', 1); 
                                $qNull->where('email_bounced', '>=', 1); 
                            endif;
                            if(trim($tcem) == 'replied'): 
                                $q->where('email_replied', '>=', 1); 
                                $qNull->where('email_replied', '>=', 1); 
                            endif;
                        }
                    elseif($value['formula'] == 'any'):
                        $q->where(function($inq) use ($value) { // $term is the search term on the query string
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
                        $qNull->where(function($inq) use ($value) { // $term is the search term on the query string
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
                        $q->where(function($inq) {
                            $inq->where('email_delivered', 0);
                            $inq->orWhere('email_delivered', null);
                        });
                        $qNull->where(function($inq) {
                            $inq->where('email_delivered', 0);
                            $inq->orWhere('email_delivered', null);
                        });
                        $q->where(function($inq) {
                            $inq->where('email_bounced', 0);
                            $inq->orWhere('email_bounced', null);
                        });
                        $qNull->where(function($inq) {
                            $inq->where('email_bounced', 0);
                            $inq->orWhere('email_bounced', null);
                        });
                    endif;
                elseif($value['type'] == 'phone' && $value['condition'] == 'dsMobilePhones'):
                    if($value['formula'] == 'all'):
                        foreach (explode(',', $value['textCondition']) as $tcem) {
                            if(trim($tcem) == 'dialed'): 
                                $q->where('mcall_attempts', '>=', 1); 
                                $qNull->where('mcall_attempts', '>=', 1); 
                            endif;
                            if(trim($tcem) == 'received'): 
                                $q->where('mcall_received', '>=', 1); 
                                $qNull->where('mcall_received', '>=', 1); 
                            endif;
                        }
                    elseif($value['formula'] == 'any'):
                        $q->where(function($inq) use ($value) { // $term is the search term on the query string
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
                        $qNull->where(function($inq) use ($value) { // $term is the search term on the query string
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
                        $q->where(function($inq) {
                            $inq->where('mcall_attempts', 0);
                            $inq->orWhere('mcall_attempts', null);
                        });
                        $qNull->where(function($inq) {
                            $inq->where('mcall_attempts', 0);
                            $inq->orWhere('mcall_attempts', null);
                        });
                        $q->where(function($inq) {
                            $inq->where('mcall_received', 0);
                            $inq->orWhere('mcall_received', null);
                        });
                        $qNull->where(function($inq) {
                            $inq->where('mcall_received', 0);
                            $inq->orWhere('mcall_received', null);
                        });
                    endif;
                elseif($value['type'] == 'phone' && $value['condition'] == 'dsHomePhones'):
                    if($value['formula'] == 'all'):
                        foreach (explode(',', $value['textCondition']) as $tcem) {
                            if(trim($tcem) == 'dialed'): 
                                $q->where('hcall_attempts', '>=', 1); 
                                $qNull->where('hcall_attempts', '>=', 1); 
                            endif;
                            if(trim($tcem) == 'received'): 
                                $q->where('hcall_received', '>=', 1); 
                                $qNull->where('hcall_received', '>=', 1); 
                            endif;
                        }
                    elseif($value['formula'] == 'any'):
                        $q->where(function($inq) use ($value) { // $term is the search term on the query string
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
                        $qNull->where(function($inq) use ($value) { // $term is the search term on the query string
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
                        $q->where(function($inq) {
                            $inq->where('hcall_attempts', 0);
                            $inq->orWhere('hcall_attempts', null);
                        });
                        $qNull->where(function($inq) {
                            $inq->where('hcall_attempts', 0);
                            $inq->orWhere('hcall_attempts', null);
                        });
                        $q->where(function($inq) {
                            $inq->where('hcall_received', 0);
                            $inq->orWhere('hcall_received', null);
                        });
                        $qNull->where(function($inq) {
                            $inq->where('hcall_received', 0);
                            $inq->orWhere('hcall_received', null);
                        });
                    endif;
                elseif($value['type'] == 'phone' && $value['condition'] == 'dsWorkPhones'):
                    if($value['formula'] == 'all'):
                        foreach (explode(',', $value['textCondition']) as $tcem) {
                            if(trim($tcem) == 'dialed'): 
                                $q->where('wcall_attempts', '>=', 1); 
                                $qNull->where('wcall_attempts', '>=', 1); 
                            endif;
                            if(trim($tcem) == 'received'): 
                                $q->where('wcall_received', '>=', 1); 
                                $qNull->where('wcall_received', '>=', 1); 
                            endif;
                        }
                    elseif($value['formula'] == 'any'):
                        $q->where(function($inq) use ($value) { // $term is the search term on the query string
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
                        $qNull->where(function($inq) use ($value) { // $term is the search term on the query string
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
                        $q->where(function($inq) {
                            $inq->where('wcall_attempts', 0);
                            $inq->orWhere('wcall_attempts', null);
                        });
                        $qNull->where(function($inq) {
                            $inq->where('wcall_attempts', 0);
                            $inq->orWhere('wcall_attempts', null);
                        });
                        $q->where(function($inq) {
                            $inq->where('wcall_received', 0);
                            $inq->orWhere('wcall_received', null);
                        });
                        $qNull->where(function($inq) {
                            $inq->where('wcall_received', 0);
                            $inq->orWhere('wcall_received', null);
                        });
                    endif;
                endif;
            }
            $records = $q;
            $recordsNull = $qNull;
        endif;
        
        $totalContacts = 0;
        $records = $records->get();
        $recordsNull = $recordsNull->whereNull("contacts.$graphFilter")->count();
        foreach($records as $value) {
            $totalContacts += $value["count"];
        }

        if($totalContacts == 0){
            $paiDataS = [];
            $maplabelsS = [];
            $allValues = [];
        }
        else {
            $totalContacts += $recordsNull;
            $stageRecords = Stages::select("oid", "name")->get();
            $stageList = [];
            foreach($stageRecords as $value){
                $stageList[$value["oid"]] = $value["name"];
            }
            $paiDataS = [];
            $maplabelsS = [];
            $allValues = [];
            foreach($records as $value){
                if($graphFilter == "stage"){
                    $paiDataS[] = $value["count"];
                    $maplabelsS[] = number_format(($value["count"]*100)/$totalContacts, 1, '.', '')."% : ".$stageList[$value["stage"]];
                    $allValues[] = [
                    "count" => $value["count"],
                    "field" => $graphFilter,
                    "value" => $stageList[$value["stage"]],
                    "percentage" => number_format(($value["count"]*100)/$totalContacts, 1, '.', '')."%"
                ];
                }else{
                    if($value["count"] > 0 && strlen(substr($value[$graphFilter], 0, 50)) > 1)
                    {
                        $paiDataS[] = $value["count"];
                        if($value[$graphFilter] == "0"){
                            $maplabelsS[] = number_format(($value["count"]*100)/$totalContacts, 1, '.', '')."% : None";
                        }else{
                            $maplabelsS[] = number_format(($value["count"]*100)/$totalContacts, 1, '.', '')."% : ".substr($value[$graphFilter], 0, 50);
                        }
                        $allValues[] = [
                            "count" => $value["count"],
                            "field" => $graphFilter,
                            "value" => substr($value[$graphFilter], 0, 50),
                            "percentage" => number_format(($value["count"]*100)/$totalContacts, 1, '.', '')."%",
                            "time" => ($graphFilter == "custom29") ? $this->__getTime($value[$graphFilter]) : '',
                            "callTime" => ($graphFilter == "custom29") ? $this->__getCallTime($value[$graphFilter]) : '',
                            "timezone" => ($graphFilter == "custom29") ? $this->__getCallTimeZone($value[$graphFilter]) : ''
                        ];
                    }
                }
            }

        }
        if($recordsNull > 0 && $totalContacts > 0){
            $paiDataS[] = $recordsNull;
            $maplabelsS[] = number_format(($recordsNull*100)/$totalContacts, 1, '.', '')."% : None";
            $allValues[] = [
                "count" => $recordsNull,
                "field" => $graphFilter,
                "value" => "None",
                "percentage" => number_format(($recordsNull*100)/$totalContacts, 1, '.', '')."%"
            ];
        }
        $graphFilterItems = DB::table("graph_search_criterias")->whereNotIn('value', array_merge($historyKey, [$graphFilter]))->orderBy('order_no', 'asc')->get();
        $graphFilter = DB::table("graph_search_criterias")->leftJoin("search_criterias", "search_criterias.id", "=", "graph_search_criterias.search_criteria_id")->where("value", $graphFilter)->first();
        
        return [
            "paiDataS" => $paiDataS,
            "maplabelsS" => $maplabelsS,
            "totalContacts" => $totalContacts,
            "allValues" => $allValues,
            "graphFilter" => $graphFilter,
            "graphFilterItems" => $graphFilterItems,
            "historyKey" => $historyKey,
            "historyValue" => $historyValue,
        ];
    }
    private function __getTime($timezone){
        $record = DB::table("timezones")->where("timezone_type", "LIKE", $timezone)->orderBy("id", "desc")->first();
        date_default_timezone_set($record->timezone);
        return date("h:i A", strtotime("now"));
    }
    private function __getCallTime($timezone){
        $record = DB::table("timezones")->where("timezone_type", "LIKE", $timezone)->orderBy("id", "desc")->first();
        date_default_timezone_set($record->timezone);
        $callTime = intval(date("H", strtotime("now")));
        if(($callTime >= 8) && ($callTime <= 17)){
            return 1;
        }else{
            return 0;
        }
    }
    private function __getCallTimeZone($timezone){
        $record = DB::table("timezones")->where("timezone_type", "LIKE", $timezone)->orderBy("id", "desc")->first();
        return $record->timezone;
    }
    public function getGraphSearchCriteriaRecord(Request $request){
        return["results" => DB::table("graph_search_criterias")->where("value", "=", $request->input("value"))->first()];
    }
    public function getSearchCriteria(Request $request){
        return["results" => DB::table("search_criterias")->where("filter_key", "=", $request->input("value"))->first()];
    }
    public function getStageOid(Request $request){
        $record = DB::table("stages")->where("name", "=", $request->input("stageName"))->first();
        return ["results" => $record->oid];
    }
    public function chart(Request $request)
    {
        $totalContacts = Contacts::count();
        $records = Contacts::select(DB::raw('count(contacts.stage) as count, contacts.stage'))
                    ->groupBy('contacts.stage')
                    ->get();
        $stageRecords = Stages::select("oid", "name")->get();
        $stageList = [];
        foreach($stageRecords as $value){
            $stageList[$value["oid"]] = $value["name"];
        }
        $paiDataS = [];
        $maplabelsS = [];
        foreach($records as $value){
            $paiDataS[] = $value["count"];
            $maplabelsS[] = number_format(($value["count"]*100)/$totalContacts, 1, '.', '')."%  ".$stageList[$value["stage"]];
            $stageId = intval($value["stage"]);
        }
        return [
            "paiDataS" => $paiDataS,
            "maplabelsS" => $maplabelsS
        ];
    }
    public function datacheck(Request $request)
    {
        $recordPerPage  = $request->recordPerPage;
        $records = Contacts::whereNotNull('record_id')->orderBy('contacts.last_update_at', 'desc')->paginate($recordPerPage);
        return $records;
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
    public function resetDataset()
    {
        ini_set('max_execution_time', 4*3600);
        Contacts::whereIn('stage', [10,11,12])->update(['dataset' => 3]);
        Contacts::whereIn('stage', [7])->update(['dataset' => 5]);
        Contacts::whereIn('stage', [9])->update(['dataset' => 6]);
        Contacts::whereIn('stage', [5,17,19])->update(['dataset' => 7]);
        $contacts = Contacts::whereNotIn('stage', [5,7,9,10,11,12,17,19])->orderBy('id', 'desc')->get();
        foreach ($contacts as $key => $contact) {
            $dset = $this->__GetDataset($contact);
            $contact->update(['dataset' => $dset]);
        }
        $contacts = Contacts::whereNull('stage')->orderBy('id', 'desc')->get();
        foreach ($contacts as $key => $contact) {
            $dset = $this->__GetDataset($contact);
            $contact->update(['dataset' => $dset]);
        }
        $sets = DatasetGroups::get();
        $return[0] = Contacts::where('dataset', '==', 0)->orWhere('dataset', null)->count();
        foreach ($sets as $set) {
            $return[$set->id] = Contacts::where('dataset', $set->id)->count();
        }
        
        return ['results' => $return, 'total' => $return[0]];
    }
    public function getDataset()
    {
        $sets = DatasetGroups::get();
        $return[0] = Contacts::where('dataset', '==', 0)->orWhere('dataset', null)->count();
        foreach ($sets as $set) {
            $return[$set->id] = Contacts::where('dataset', $set->id)->count();
        }
        
        return ['results' => $return, 'total' => $return[0]];
    }
    private function __GetDataset($var = null)
    {
        $c1 = 0; $c2 = 0; $c3 = 0; $c4 = 0; $c5 = 0; $t = 0; $ct = 0; $ft = 0;

        if($var->email_delivered >= 1) { 
            $c1 = $var->email_opened*100/$var->email_delivered;
            $c2 = $var->email_clicked*100/$var->email_delivered;
            $t = 200;
        } else { 
            $c1 = 0; $c2 = 0;  
            $t = 0;
        }

        if($var->mcall_attempts >= 1) { 
            $c3 = $var->mcall_received*100/$var->mcall_attempts;
            $t = $t + 100;
        } else { 
            $c3 = 0;
        }

        if($var->hcall_attempts >= 1) { 
            $c4 = $var->hcall_received*100/$var->hcall_attempts;
            $t = $t + 100;
        } else { 
            $c4 = 0;
        }

        if($var->wcall_attempts >= 1) { 
            $c5 = $var->mcall_received*100/$var->wcall_attempts;
            $t = $t + 100;
        } else { 
            $c5 = 0;
        }
                
        $ct = $c1+$c2+$c3+$c4+$c5;
        if($t == 0) {
            return 0;
        }
        $ft = $ct/$t;

        $dsg = round($ft/20);
        
        $dg = DatasetGroups::where('points', $dsg)->get()->first();
        if($dg) {
            return $dg->id;
        } else {
            return 1;
        }
    }
    public function getRecordDispositions($record_id = null){
        $record_id = intval($record_id);
        $records = FivenineCallLogs::select("dnis")->where("record_id", "=", $record_id)->distinct()->get();
        $data = [];
        foreach($records as $value){            
            $dispositions = [];
            $recordValue = FivenineCallLogs::where("record_id", "=", $record_id)->where("dnis", "=", $value["dnis"])->orderByDesc("n_timestamp")->get();
            foreach($recordValue as $CallValue){
                $dispositions[] = $CallValue->disposition;
            }
            $numberType = FivenineCallLogs::where("record_id", "=", $record_id)->where("dnis", "=", $value["dnis"])->first();
            $data[]  = [
                "dispositions" => $dispositions,
                "number_type" => $numberType["number_type"],
                "number" => $numberType["dnis"]
            ];
        } 
        return $data;
    }
    private function __NumberFormater1($var = null)
    {
        if(strpos($var, ",") > -1){
            $mob = explode(",", $var);
            $var = $mob[0];
        }
        $string = str_replace(' ', '', $var); // Replaces all spaces.
        $string = str_replace('-', '', $string); // Replaces all hyphens.
        $string = str_replace('.', '', $string); // Replaces all hyphens.
        $string = str_replace('(', '', $string); // Replaces all hyphens.
        $string = str_replace(')', '', $string); // Replaces all hyphens.
        $string = preg_replace('/[^0-9\-]/', '', $string); // Removes special chars
        $string = substr($string, -10);
        return $string;
    }
    private function __NumberType($number, $rid)
    {
        $data = DB::table('fivenine_call_logs')
        //->select(DB::raw('sum(dial_attempts) as sum_dial_attempts'))
        ->where('record_id', $rid)
        ->where('dnis', $number)->get();
        $total = 0;
        foreach($data as $value){
            $value = get_object_vars($value);
            $total += intval($value["dial_attempts"]);
        }
        return $total;
    }
    private function __NumberExtFormater1($var = null)
    {
        if(strpos(strtolower($var), "ext") > -1){
            $mob = explode("ext", strtolower($var));
            $string = str_replace(' ', '', trim($mob[1]));
            $string = str_replace('.', '', $string);
        } else {
            $string = '';
        }
        return $string;
    }
    public function getRecordContainerInfo(Request $request){
        $ids = $request->input("exports");
        $results = [];
        foreach($ids as $id){
            $record = Contacts::where("id", "=", $id)->first();
            if($record->mobilePhones != '' || $record->workPhones !== '' || $record->homePhones != ''){
                $results[] = [
                    "first_name" => $record->first_name,
                    "last_name" => $record->last_name,
                    "number1" => ($record->number1 != 0)?$record->number1:'',
                    "number2" => ($record->number2 != 0)?$record->number2:'',
                    "number3" => ($record->number3 != 0)?$record->number3:'',
                    "number1type" => ($record->number1 != 0)?$record->number1type:'',
                    "number2type" => ($record->number2 != 0)?$record->number2type:'',
                    "number3type" => ($record->number3 != 0)?$record->number3type:'',
                    "number1call" => ($record->number1 != 0)?$record->number1call:'',
                    "number2call" => ($record->number2 != 0)?$record->number2call:'',
                    "number3call" => ($record->number3 != 0)?$record->number3call:'',
                    "ext1" => $record->ext1,
                    "ext2" => $record->ext2,
                    "ext3" => $record->ext3,
                    "company" => $record->company,
                    "record_id" => $record->record_id,
                ]; 
            }
        }
        return $results;
    }

}
