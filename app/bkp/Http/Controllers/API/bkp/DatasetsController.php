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

        $records = Contacts::select('contacts.id', 'contacts.record_id', 'contacts.first_name', 'contacts.last_name', 'contacts.title', 'contacts.company', 'contacts.mobilePhones', 'contacts.workPhones', 'contacts.homePhones', 'contacts.emails', 'contacts.outreach_touched_at', 'contacts.outreach_created_at', 'contacts.last_agent_dispo_time', 'contacts.last_agent', 'contacts.email_delivered', 'contacts.email_opened', 'contacts.email_clicked', 'contacts.email_replied', 'contacts.email_bounced', 'contacts.mcall_attempts', 'contacts.mcall_received', 'contacts.wcall_attempts', 'contacts.wcall_received', 'contacts.hcall_attempts', 'contacts.hcall_received', 'stages.id as stage', 'stages.name as stage_name', 'stages.css as stage_css', 'contacts.dataset')
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
                            $q->whereIn($value["condition"], $ids);
                            // foreach($ids as $IdValue):
                            //     $q->orWhere($value["condition"], 'like', $IdValue);
                            // endforeach;
                        else:
                            //($value["condition"] == 'mobilePhones') || ($value["condition"] == 'workPhones') || ($value["condition"] == 'homePhones')
                            if(in_array($value["condition"], ["mobilePhones", "workPhones", "homePhones"])){
                                $string = $this->__NumberFormater($value["textCondition"]);                            
                                $v1 = substr($string, 0, 4);
                                $v2 = substr($string, 4, 3);
                                $v3 = substr($string, 7, 3);
    
                                $r1 = $v1.$v2.$v3;
                                
                                $r2 = "$v1-$v2-$v3";
    
                                $r3 = "$v1 $v2 $v3";
    
                                $r4 = "($v1)-$v2-$v3";
                                $r5 = "($v1)-$v2-$v3";
                                $r6 = "($v1)-$v2-$v3";
                                $r7 = "($v1)-$v2-$v3";
    
                                $r8 = "($v1)-($v2)-$v3";
                                $r9 = "($v1)-($v2)-$v3";
                                $r10 = "($v1)-($v2)-$v3";
                                $r11 = "($v1)-($v2)-$v3";
    
                                $r12 = "($v1)-($v2)-($v3)";
                                $r13 = "($v1)-($v2)-($v3)";
                                $r14 = "($v1)-($v2)-($v3)";
                                $r15 = "($v1)-($v2)-($v3)";
    
                                $r16 = "($v1)-$v2-($v3)";
                                $r17 = "($v1)-$v2-($v3)";
                                $r18 = "($v1)-$v2-($v3)";
                                $r19 = "($v1)-$v2-($v3)";
                                
                                //

                                $r20 = "($v1) $v2 $v3";
                                $r21 = "($v1) $v2 $v3";
                                $r22 = "($v1) $v2 $v3";
                                $r23 = "($v1) $v2 $v3";
    
                                $r24 = "($v1) ($v2) $v3";
                                $r25 = "($v1) ($v2) $v3";
                                $r26 = "($v1) ($v2) $v3";
                                $r27 = "($v1) ($v2) $v3";
    
                                $r28 = "($v1) ($v2) ($v3)";
                                $r29 = "($v1) ($v2) ($v3)";
                                $r30 = "($v1) ($v2) ($v3)";
                                $r31 = "($v1) ($v2) ($v3)";
    
                                $r32 = "($v1) $v2 ($v3)";
                                $r33 = "($v1) $v2 ($v3)";
                                $r34 = "($v1) $v2 ($v3)";
                                $r35 = "($v1) $v2 ($v3)";

                                    //dd($r1);
                                $q->orWhere($value["condition"], 'like', "%$r1%")->orWhere($value["condition"], 'like', "%$r2%")->orWhere($value["condition"], 'like', "%$r3%")->orWhere($value["condition"], 'like', "%$r4%")->orWhere($value["condition"], 'like', "%$r5%")->orWhere($value["condition"], 'like', "%$r6%")->orWhere($value["condition"], 'like', "%$r7%")->orWhere($value["condition"], 'like', "%$r8%")->orWhere($value["condition"], 'like', "%$r9%")->orWhere($value["condition"], 'like', "%$r10%")->orWhere($value["condition"], 'like', "%$r11%")->orWhere($value["condition"], 'like', "%$r12%")->orWhere($value["condition"], 'like', "%$r13%")->orWhere($value["condition"], 'like', "%$r14%")->orWhere($value["condition"], 'like', "%$r15%")->orWhere($value["condition"], 'like', "%$r16%")->orWhere($value["condition"], 'like', "%$r17%")->orWhere($value["condition"], 'like', "%$r18%")->orWhere($value["condition"], 'like', "%$r19%")->orWhere($value["condition"], 'like', "%$r20%")->orWhere($value["condition"], 'like', "%$r21%")->orWhere($value["condition"], 'like', "%$r22%")->orWhere($value["condition"], 'like', "%$r23%")->orWhere($value["condition"], 'like', "%$r24%")->orWhere($value["condition"], 'like', "%$r25%")->orWhere($value["condition"], 'like', "%$r26%")->orWhere($value["condition"], 'like', "%$r27%")->orWhere($value["condition"], 'like', "%$r27%")->orWhere($value["condition"], 'like', "%$r28%")->orWhere($value["condition"], 'like', "%$r29%")->orWhere($value["condition"], 'like', "%$r30%")->orWhere($value["condition"], 'like', "%$r31%")->orWhere($value["condition"], 'like', "%$r32%")->orWhere($value["condition"], 'like', "%$r33%")->orWhere($value["condition"], 'like', "%$r34%")->orWhere($value["condition"], 'like', "%$r35%");
                            }else{
                                $q->where($value["condition"], '=', $value["textCondition"]);
                            }
                        endif;
                    elseif($value['formula'] == 'is not'):
                        if(strpos($value["textCondition"],  ",")):
                            $ids = explode(",", $value["textCondition"]);
                            $q->whereNotIn($value["condition"], $ids);
                            // foreach($ids as $IdValue):
                            //     $q->orWhere($value["condition"], 'not like', $IdValue);
                            // endforeach;
                        else:
                            $q->where($value["condition"], 'not like', $value["textCondition"])->orWhereNull($value["condition"])->orWhere($value["condition"], '=', '');
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
                        $q->whereNull($value["condition"])->orWhere($value["condition"], '=', '');
                    elseif($value['formula'] == 'is not empty'):
                        if(($value["condition"] == "mobilePhones") || ($value["condition"] == "workPhones") || ($value["condition"] == "homePhones")){
                            $q->orWhere($value["condition"], '!=', '');
                        }else{
                            $q->whereNotNull($value["condition"])->orWhere($value["condition"], '!=', '');
                        }
                    elseif($value['formula'] == 'contains'):
                        $q->where($value["condition"], 'like', '%'.$value["textCondition"].'%');
                    elseif($value['formula'] == 'not contains'):
                        $q->where($value["condition"], 'not like', '%'.$value["textCondition"].'%');
                        //->orWhereNotNull($value["condition"])->orWhere($value["condition"], '=', '');
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
                            $inq->orWhere('email_bounced', 0);
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
                            $inq->orWhere('mcall_received', 0);
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
                            $inq->orWhere('hcall_received', 0);
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
                            $inq->orWhere('wcall_received', 0);
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
        //$records = $records->orderBy('contacts.outreach_touched_at', 'desc')->paginate($recordPerPage);
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
    public function datacheck(Request $request)
    {
        $recordPerPage  = $request->recordPerPage;
        $records = Contacts::whereNotNull('record_id')->orderBy('contacts.last_update_at', 'desc')->paginate($recordPerPage);
        return $records;
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

}
