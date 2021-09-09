<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contacts;
use App\Models\DatasetGroups;

class DatasetsController extends Controller
{
    public function getAllData(Request $request){
        $recordPerPage  = $request->recordPerPage;
        $search = $request->textSearch;
        $sortby = $request->sortby;
        $sort = $request->sort;

        $records = Contacts::select('contacts.id', 'contacts.record_id', 'contacts.first_name', 'contacts.last_name', 'contacts.title', 'contacts.company', 'contacts.mobilePhones', 'contacts.workPhones', 'contacts.homePhones', 'contacts.emails', 'contacts.outreach_touched_at', 'contacts.outreach_created_at', 'contacts.last_agent_dispo_time', 'contacts.last_agent', 'contacts.email_delivered', 'contacts.email_opened', 'contacts.email_clicked', 'contacts.email_replied', 'contacts.email_bounced', 'contacts.mcall_attempts', 'contacts.mcall_received', 'contacts.wcall_attempts', 'contacts.wcall_received', 'contacts.hcall_attempts', 'contacts.hcall_received', 'stages.id as stage', 'stages.name as stage_name', 'stages.css as stage_css')
            ->leftjoin('stages', 'stages.oid', '=', 'contacts.stage');
        if($search):
            $records = $records->when($search, function($query, $search){
                $query->orWhere(function($q) use ($search){
                    $q->orWhere('contacts.first_name', 'like', '%'.$search.'%')->orWhere('contacts.last_name', 'like', '%'.$search.'%')->orWhere('contacts.emails', 'like', '%'.$search.'%')->orWhere('contacts.company', 'like', '%'.$search.'%');
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
                        $q->where($value["condition"], '!=', '');
                    elseif($value['formula'] == 'contains'):
                        $q->where($value["condition"], 'like', '%'.$value["textCondition"].'%');
                    elseif($value['formula'] == 'ends with'):
                        $q->where($value["condition"], '=', '%'.$value["textCondition"]);
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
                    else:
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
                    endif;
                elseif($value['type'] == 'phone' && $value['condition'] == 'dsMobilePhones'):
                    if($value['formula'] == 'all'):
                        foreach (explode(',', $value['textCondition']) as $tcem) {
                            if(trim($tcem) == 'dialed'): $q->where('mcall_attempts', '>=', 1); endif;
                            if(trim($tcem) == 'received'): $q->where('mcall_received', '>=', 1); endif;
                        }
                    else:
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
                    endif;
                elseif($value['type'] == 'phone' && $value['condition'] == 'dsHomePhones'):
                    if($value['formula'] == 'all'):
                        foreach (explode(',', $value['textCondition']) as $tcem) {
                            if(trim($tcem) == 'dialed'): $q->where('hcall_attempts', '>=', 1); endif;
                            if(trim($tcem) == 'received'): $q->where('hcall_received', '>=', 1); endif;
                        }
                    else:
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
                    endif;
                elseif($value['type'] == 'phone' && $value['condition'] == 'dsWorkPhones'):
                    if($value['formula'] == 'all'):
                        foreach (explode(',', $value['textCondition']) as $tcem) {
                            if(trim($tcem) == 'dialed'): $q->where('wcall_attempts', '>=', 1); endif;
                            if(trim($tcem) == 'received'): $q->where('wcall_received', '>=', 1); endif;
                        }
                    else:
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
        
        
        $records = $records->orderBy('contacts.outreach_touched_at', 'desc')->paginate($recordPerPage);
        return $records;
    }

    public function datacheck(Request $request)
    {
        $recordPerPage  = $request->recordPerPage;
        $records = Contacts::whereNotNull('record_id')->orderBy('contacts.last_update_at', 'desc')->paginate($recordPerPage);
        return $records;
    }

    public function resetDataset()
    {
        Contacts::whereIn('stage', [10,11,12])->update(['dataset' => 3]);
        Contacts::whereIn('stage', [7])->update(['dataset' => 5]);
        Contacts::whereIn('stage', [9])->update(['dataset' => 6]);
        Contacts::whereIn('stage', [5,17,19])->update(['dataset' => 7]);
        $contacts = Contacts::whereNotIn('stage', [5,7,9,10,11,12,17,19])->get();
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

}
