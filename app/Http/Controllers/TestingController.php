<?php

namespace App\Http\Controllers;

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
use App\Models\DatasetGroups;


class TestingController extends Controller
{
    public function numbersyncing($page)
    {
        ini_set('max_execution_time', 10800);
        /*Contacts::whereIn('stage', [10,11,12])->update(['dataset' => 3]);
        Contacts::whereIn('stage', [7])->update(['dataset' => 5]);
        Contacts::whereIn('stage', [9])->update(['dataset' => 6]);
        Contacts::whereIn('stage', [5,17,19])->update(['dataset' => 7]);
*/
        
        
        /*$contacts = Contacts::whereNull('stage')->orderBy('id', 'desc')->get();
        foreach ($contacts as $key => $contact) {
            $dset = $this->__GetDataset($contact);
            $contact->update(['dataset' => $dset]);
        }*/
        echo 'done'; die;
        /*$contacts = Contacts::whereNotIn('stage', [5,7,9,10,11,12,17,19])->whereBetween('id', [$page, $last])->get();
        foreach ($contacts as $key => $contact) {
            $dset = $this->__GetDataset($contact);
            $contact->update(['dataset' => $dset]);
        }*/

        /*$contacts = Contacts::whereNull('stage')->orderBy('id', 'desc')->get();
        foreach ($contacts as $key => $contact) {
            $dset = $this->__GetDataset($contact);
            $contact->update(['dataset' => $dset]);
        }
        $sets = DatasetGroups::get();
        $return[0] = Contacts::where('dataset', '==', 0)->orWhere('dataset', null)->count();
        foreach ($sets as $set) {
            $return[$set->id] = Contacts::where('dataset', $set->id)->count();
        }*/
        

        
        /*$counter = 0;
        // $logs = FivenineCallLogs::get();
        $logs = FivenineCallLogs::whereBetween('id', [$page*500, ($page+1)*500])->get();
        foreach ($logs as $key => $log) {
            $contact = Contacts::where('record_id', $log->record_id)->get()->first();
            $flog = FivenineCallLogs::where('id', $log->id)->get()->first();
            if($this->__NumberFormater($contact->mobilePhones) == $flog->dnis): $t = 'm';
            elseif($this->__NumberFormater($contact->homePhones) == $flog->dnis): $t = 'hq';
            elseif($this->__NumberFormater($contact->workPhones) == $flog->dnis): $t = 'd';
            else: $t = '0';
            endif;
            //dd($t);
            $log->update(['number_type' => $t]);

            echo $flog->id.' is done.<br> '.++$counter.' records done <br>'; 
        }*/
        return view('testingsync', compact('page', 'last'));
    }
   
    public function numberformating()
    {
        $counter = 0;
        ini_set('max_execution_time', 10800);
        $contacts = Contacts::where('id', '>', 10)->get();
        foreach ($contacts as $key => $contact) {
            $mnumber = $this->__NumberFormater($contact->mobilePhones);
            $hqnumber = $this->__NumberFormater($contact->workPhones);
            $dnumber = $this->__NumberFormater($contact->otherPhones);
            $ucontact = Contacts::where('id', $contact->id)->get()->first();
            $ucontact->update(['mnumber' => $mnumber, 'hqnumber' => $hqnumber, 'dnumber' => $dnumber]);
            echo $contact->record_id.' is done.<br>'; 
            echo 'total '.++$counter.' records done';
        }
        die;
    }
    public function prospectContactSwap($page){
        ini_set('max_execution_time', 10800);
        if($page == 83){
            echo 'all done'; die;
        }
        $records  = Contacts::whereBetween('record_id' ,[$page*500, ($page+1)*500])->where('source', '=', 'DiscoverORG')->orderBy('record_id')->get();
        $contactArray = [];
        $contactCounter = 0;
        foreach($records as $record):            
            $voipC = $this->__NumberFormater($record->voipPhones); 
            $contact = Contacts::where('id', $record->id)->first();
            $cc = ContactCustoms::where('contact_id', $contact->record_id)->first();
            
            if($voipC && (strlen($voipC) == 10)):
                if((strlen($contact->homePhones) > 0) && (strlen($contact->workPhones) > 0) && ($this->__NumberFormater($contact->homePhones) == $this->__NumberFormater($contact->workPhones))):
                    $contactArray[$contactCounter++] = $record->record_id;
                    if(strpos($contact->workPhones, ',') != false):
                        $w = explode(',', $contact->workPhones);
                        $mobileW = [];
                        $counterW = 0;
                        $existW = null;
                        foreach($w as $value):
                            $mobileW[$counterW++] = $this->__NumberFormater($value);
                            if($voipC == $this->__NumberFormater($value)):
                                $existW = 1;
                            endif;
                        endforeach;
                        if($existW):                    
                            if (($key = array_search($voipC, $mobileW)) !== false) {
                                unset($mobileW[$key]);
                            }
                        endif;
                        $contact->workPhones = implode(",", $mobileW);
                        $contact->save();
                    else:
                        $workC = $this->__NumberFormater($contact->workPhones);
                        if($workC && ($voipC == $workC)):
                            $contact->workPhones = null;
                            $contact->save();
                        endif;
                    endif;

                    if(strpos($contact->homePhones, ',') != false):
                        $h = explode(',', $contact->homePhones);
                        $mobileH = [];
                        $counterH = 0;
                        $existH = null;
                        foreach($h as $value):
                            $mobileH[$counterH++] = $this->__NumberFormater($value);
                            if($voipC == $this->__NumberFormater($value)):
                                $existH = 1;
                            endif;
                        endforeach;
                        if($existH):
                            if (($key = array_search($voipC, $mobileH)) !== false) {
                                unset($mobileH[$key]);
                            }
                        endif;
                        $contact->homePhones = implode(",", $mobileH);
                        $contact->save();
                    else:
                        $homeC = $this->__NumberFormater($contact->homePhones);
                        if($homeC && ($homeC == $voipC)):
                            $contact->homePhones = null;
                            $contact->save();
                        endif;
                    endif;

                    if($contact->mobilePhones):
                        if(strpos($contact->mobilePhones, ',')):
                            $m =  explode(',', $contact->mobilePhones);
                            $mobile = [];
                            $counter = 0;
                            $exist = null;
                            foreach($m as $value):
                                $mobile[$counter++] = $this->__NumberFormater($value);
                                if($voipC == $this->__NumberFormater($value)):
                                    $exist = 1;
                                endif;
                            endforeach;
                            if($exist):                    
                                if (($key = array_search($voipC, $mobile)) !== false) {
                                    unset($mobile[$key]);
                                }
                            endif;
                            $contact->mobilePhones = $contact->voipPhones;
                            $contact->save();
                            $cc->custom150 = 'swap';
                            $cc->custom147 = implode(",", $mobile);
                            $cc->save();
                        else:
                            $mobileC = $this->__NumberFormater($contact->mobilePhones);
                            if($voipC != $mobileC):
                                $cc->custom150 = 'swap';
                                $cc->custom147 = $contact->mobilePhones;
                                $cc->save();
                                $contact->mobilePhones = $contact->voipPhones;
                                //$contact->voipPhones = null;
                                $contact->save();
                            else:
                                //$contact->voipPhones = null;
                                $contact->save();
                            endif;
                        endif;
                    else:
                        $contact->mobilePhones = $contact->voipPhones;
                        $contact->save();
                    endif;
                endif;
            endif;
        endforeach;
        $id = $contact->record_id;
        return view('testingSwap', compact('page', 'id'));    
    }
    public function prospectContactOtherSwap($page){
        ini_set('max_execution_time', 10800);
        if($page == 83){
            echo 'all done'; die;
        }
        $records  = Contacts::whereBetween('record_id' ,[$page*500, ($page+1)*500])->where('source', '=', 'DiscoverORG')->orderBy('record_id')->get();
        foreach($records as $record):  
            $contact = Contacts::where('id', $record->id)->first();
            $cc = ContactCustoms::where('contact_id', $contact->record_id)->first();
            //other with mobile home and work
            //compare with mobile
            //if other and mobile is same 
            // if($contact->id == 1243):
            //     dd('1');
            // endif;
            //dd(strlen($this->__NumberFormater($contact->workPhones)));
            if(strlen($this->__NumberFormater($contact->otherPhones)) > 0):                
                echo $contact->record_id.'<br>';
                //if mobile is blank
                //dd(strlen($contact->homePhones));
                
                if(strlen($this->__NumberFormater($contact->mobilePhones)) == 0): 
                    
                    if((strlen($contact->homePhones) == 0) && (strlen($contact->workPhones) == 0)):                       
                        $contact->mobilePhones = $contact->otherPhones;
                        $contact->save();
                    elseif((strlen($contact->homePhones) == 0) && (strlen($this->__NumberFormater($contact->workPhones)) == 10)):
                        if($this->__NumberFormater($contact->workPhones) != $this->__NumberFormater($contact->otherPhones)):
                            $contact->mobilePhones = $contact->otherPhones;
                            $contact->save();
                        endif;
                    elseif((strlen($this->__NumberFormater($contact->homePhones)) == 10) && (strlen($contact->workPhones) == 0)):
                        if($this->__NumberFormater($contact->homePhones) != $this->__NumberFormater($contact->otherPhones)):
                            $contact->mobilePhones = $contact->otherPhones;
                            $contact->save();
                        endif;
                    elseif((strlen($this->__NumberFormater($contact->homePhones)) == 10) && (strlen($this->__NumberFormater($contact->workPhones)) == 10)):
                        if($this->__NumberFormater($contact->homePhones) != $this->__NumberFormater($contact->workPhones)):
                            if(($this->__NumberFormater($contact->homePhones) == $this->__NumberFormater($contact->workPhones))):
                                if(($this->__NumberFormater($contact->otherPhones) == $this->__NumberFormater($contact->homePhones))):
                                    $contact->homePhones = null;
                                    $contact->workPhones = null;
                                    $contact->mobilePhones = $contact->otherPhones;
                                    $contact->save();
                                else:
                                    $contact->workPhones = null;
                                    $contact->mobilePhones = $contact->otherPhones;
                                    $contact->save();
                                endif;                            
                            elseif(($this->__NumberFormater($contact->homePhones) != $this->__NumberFormater($contact->otherPhones)) && ($this->__NumberFormater($contact->homePhones) != $this->__NumberFormater($contact->otherPhones)) && ($this->__NumberFormater($contact->homePhones) != $this->__NumberFormater($contact->otherPhones))):
                                //all are different
                                $cc->custom149 = $contact->workPhones;
                                $cc->costom150 = 'swap';
                                $cc->save();
                                $contact->workPhones = $contact->otherPhones;
                                $contact->save();
                            endif;
                        endif;
                    endif;
                elseif(strlen($this->__NumberFormater($contact->mobilePhones)) == 10):  
                    
                    if($this->__NumberFormater($contact->mobilePhones) != $this->__NumberFormater($contact->otherPhones)):
                        
                        if( (strlen($contact->homePhones) == 0) && (strlen($contact->workPhones) == 0) ):
                            $contact->homePhones = $contact->otherPhones;
                            $contact->save();
                        elseif( (strlen($contact->homePhones) == 0) && (strlen($this->__NumberFormater($contact->workPhones)) == 10) ):                        
                            if($this->__NumberFormater($contact->workPhones) != $this->__NumberFormater($contact->otherPhones)):                                
                                $contact->homePhones = $contact->otherPhones;
                                $contact->save();
                            endif;                                
                        elseif( (strlen($this->__NumberFormater($contact->homePhones)) == 10) && (strlen($contact->workPhones) == 0) ):
                            if($this->__NumberFormater($contact->homePhones) != $this->__NumberFormater($contact->otherPhones)):
                                $contact->workPhones = $contact->otherPhones;
                                $contact->save();
                            endif;
                        elseif( (strlen($this->__NumberFormater($contact->homePhones)) == 10) && (strlen($this->__NumberFormater($contact->workPhones)) == 10) ):
                            if($this->__NumberFormater($contact->mobilePhones) != $this->__NumberFormater($contact->otherPhones)):
                                if($this->__NumberFormater($contact->homePhones) != $this->__NumberFormater($contact->workPhones)):
                                    $cc->custom150 = 'swap';
                                    $cc->custom149 = $contact->workPhones;
                                    $cc->save();
                                    $contact->workPhones = $contact->otherPhones;
                                    $contact->save();
                                elseif($this->__NumberFormater($contact->homePhones) == $this->__NumberFormater($contact->workPhones)):
                                    $contact->workPhones = $contact->otherPhones;
                                    $contact->save();
                                endif;                            
                            endif;
                        endif;                        
                    endif;
                endif;
                if($this->__NumberFormater($contact->mobilePhones) == $this->__NumberFormater($contact->homePhones)):
                    $contact->homePhones = null;
                    $contact->save();
                endif;
                if($this->__NumberFormater($contact->mobilePhones) == $this->__NumberFormater($contact->workPhones)):
                    $contact->workPhones = null;
                    $contact->save();
                endif;
                if($this->__NumberFormater($contact->homePhones) == $this->__NumberFormater($contact->workPhones)):
                    $contact->workPhones = null;
                    $contact->save();
                endif;
            endif;
            if(strlen($this->__NumberFormater($contact->workPhones)) == 10):
                //dd(12);
                $wp = $this->__NumberFormater($contact->workPhones);
                $h = explode(',', $contact->mobilePhones);
                $mobileH = [];
                $counterH = 0;
                $existH = null;
                foreach($h as $value):
                    $mobileH[$counterH++] = $this->__NumberFormater($value);
                    if($wp == $this->__NumberFormater($value)):
                        $existH = 1;
                    endif;
                endforeach;
                if($existH):
                    if (($key = array_search($wp, $mobileH)) !== false) {
                        unset($mobileH[$key]);
                    }
                endif;
                $contact->mobilePhones = implode(",", $mobileH);
                $contact->save();
            endif;
        endforeach;
        $id = $contact->record_id;
        return view('testingOther', compact('page', 'id')); 
    }
    public function prospectContactHqSwap($page){
        ini_set('max_execution_time', 10800);
        if($page == 83){
            echo 'all done'; die;
        }
        //hq with mobile home and work
        $records  = Contacts::whereBetween('record_id' ,[$page*500, ($page+1)*500])->where('source', '=', 'DiscoverORG')->orderBy('record_id')->get();
        foreach($records as $record):  
            $contact = Contacts::where('id', $record->id)->first();
            echo $contact->record_id.'<br>';
            $cc = ContactCustoms::where('contact_id', $contact->record_id)->first();
            $hp = $contact->homePhones;
            $hpc = $this->__NumberFormater($contact->homePhones);
            $hq = $cc->custom5;
            $hqc = $this->__NumberFormater($cc->custom5);
            //compare with mobile
                //compare hq with mobile
                    //check mobile is single or multiple
                    //if mobile is single
                        if($hqc == $this->__NumberFormater($contact->mobilePhones)):
                            //if hq and mobile are same                        
                            //if hq and mobile are diff
                                //compare hq with home
                                //if home is blank, single or muptiple
                                    if(strlen($contact->homePhones) == 0):
                                        //if home is blank
                                        //compare with work
                                        //work can be blank single or multiple
                                        if(strlen($contact->workPhones) == 0):
                                            //if work is blank
                                            //put hq in home
                                            $contact->homePhones = $hq;
                                            $contact->save();
                                        elseif(strpos($contact->workPhones, ',') != false):
                                            //if work is mul
                                                //check if exist, extract, 
                                                $w = explode(',', $contact->workPhones);
                                                $mobileW = [];
                                                $counterW = 0;
                                                $existW = null;
                                                foreach($w as $value):
                                                    $mobileW[$counterW++] = $this->__NumberFormater($value);
                                                    if($hqc == $this->__NumberFormater($value)):
                                                        $existW = 1;
                                                    endif;
                                                endforeach;
                                                if($existW):                    
                                                    if (($key = array_search($hqc, $mobileW)) !== false) {
                                                        unset($mobileW[$key]);
                                                    }
                                                    $contact->workPhones = implode(",", $mobileW);
                                                    $contact->save();
                                                else:
                                                    $contact->homePhones = $hqc;
                                                    $contact->save();
                                                endif;
                                                //check if does not exist,
                                                    //put hq in home
                                        elseif(strpos($contact->workPhones, ',') == false):
                                            //if work is single
                                                //if both are diff
                                                if($hqc != $this->__NumberFormater($contact->workPhones)):
                                                    //put hq in home
                                                    $contact->homePhones = $hq;
                                                    $contact->save();
                                                endif;
                                        endif;
                                    elseif(strpos($contact->homePhones, ',') != false):
                                        //if home is multiple
                                        //check hq in home, if exist - extract it
                                        $w = explode(',', $contact->homePhones);
                                        $mobileW = [];
                                        $counterW = 0;
                                        $existW = null;
                                        foreach($w as $value):
                                            $mobileW[$counterW++] = $this->__NumberFormater($value);
                                            if($hqc == $this->__NumberFormater($value)):
                                                $existW = 1;
                                            endif;
                                        endforeach;
                                        if($existW):                    
                                            if (($key = array_search($hqc, $mobileW)) !== false) {
                                                unset($mobileW[$key]);
                                            }
                                            $contact->homePhones = implode(",", $mobileW);
                                            $contact->save();
                                        else:
                                            //check does not exist : compare with work
                                            if(strlen($contact->workPhones) == 0):
                                                //if work is blank
                                                // put hq in work
                                                $contact->workPhones = $hq;
                                                $contact->save();
                                            elseif(strpos($contact->workPhones, ',') == false):
                                                //if work is single
                                                //compare with work : if diff
                                                if($hqc != $this->__NumberFormater($contact->workPhones)):
                                                    //swap home and put hq in home
                                                    $cc->custom148 = $contact->homePhones;
                                                    $cc->custom150 = 'swap';
                                                    $cc->save();
                                                    $contact->homePhones = $hq;
                                                    $contact->save();
                                                endif;
                                            elseif(strpos($contact->workPhones, ',') != false):
                                                //if work is multiple
                                                //if hq exist in work, extract it
                                                $w = explode(',', $contact->workPhones);
                                                $mobileW = [];
                                                $counterW = 0;
                                                $existW = null;
                                                foreach($w as $value):
                                                    $mobileW[$counterW++] = $this->__NumberFormater($value);
                                                    if($hqc == $this->__NumberFormater($value)):
                                                        $existW = 1;
                                                    endif;
                                                endforeach;
                                                if($existW):                    
                                                    if (($key = array_search($hqc, $mobileW)) !== false) {
                                                        unset($mobileW[$key]);
                                                    }
                                                    $contact->workPhones = implode(",", $mobileW);
                                                    $contact->save();
                                                else:
                                                    //if hq does not exist : 
                                                    //swap home and put hq in home
                                                    $cc->custom148 = $contact->homePhones;
                                                    $cc->custom150 = 'swap';
                                                    $cc->save();
                                                    $contact->homePhones = $hq;
                                                    $contact->save();
                                                endif;
                                            endif;
                                        endif;
                                    elseif(strpos($contact->homePhones, ',') == false):
                                        //if home is single
                                        //compare home with hq
                                        //if hq and home are same
                                        //if hq and home are diff
                                        if($this->__NumberFormater($contact->homePhones) != $hqc):
                                            //compare hq with work
                                            if(strlen($contact->workPhones) == 0):
                                                //if work is blank
                                                //put hq in work
                                                if(strlen($contact->workPhones) == 0):
                                                    $contact->workPhones = $hq;
                                                    $contact->save();
                                                endif;
                                            elseif(strpos($contact->workPhones, ',') == false):
                                                //if work is single
                                                    if($hqc != $this->__NumberFormater($contact->workPhones)):
                                                        //if hq and work are diff
                                                        //swap home and put hq in home   
                                                        $cc->custom148 = $contact->homePhones;
                                                        $cc->custom150 = 'swap';
                                                        $cc->save();
                                                        $contact->homePhones = $hq;
                                                        $contact->save();
                                                    endif;
                                            elseif(strpos($contact->workPhones, ',') != false):
                                                //if work is multiple
                                                    //extract 
                                                    $w = explode(',', $contact->workPhones);
                                                    $mobileW = [];
                                                    $counterW = 0;
                                                    $existW = null;
                                                    foreach($w as $value):
                                                        $mobileW[$counterW++] = $this->__NumberFormater($value);
                                                        if($hqc == $this->__NumberFormater($value)):
                                                            $existW = 1;
                                                        endif;
                                                    endforeach;
                                                    if($existW):                    
                                                        if (($key = array_search($hqc, $mobileW)) !== false) {
                                                            unset($mobileW[$key]);
                                                        }
                                                        $contact->workPhones = implode(",", $mobileW);
                                                        $contact->save();
                                                    else:
                                                        //if does not exist 
                                                        //swap home and put hq in home
                                                        $cc->custom148 = $contact->homePhones;
                                                        $cc->custom150 = 'swap';
                                                        $cc->save();
                                                        $contact->homePhones = $hq;
                                                        $contact->save();
                                                    endif;
                                            endif;
                                        endif;
                                    endif;
                                                 
                        else:                            
                            //if hq and mobile are diff
                            //compare with work home and work 
                            //compare hq with home
                            if(strlen($contact->homePhones) == 0):
                                //if home is blank
                                    //compare with work
                                    if(strlen($contact->workPhones) == 0):
                                        //work:blank
                                            //put hq in home
                                            $contact->homePhones = $hq;
                                            $contact->save();
                                    elseif(strpos($contact->workPhones, ',') == false):
                                        //work:single
                                            //compare with work
                                            if($hqc != $this->__NumberFormater($contact->workPhones)):
                                                //if different
                                                //put hq in home
                                                $contact->homePhones = $hq;
                                                $contact->save();
                                            endif;
                                    elseif(strpos($contact->workPhones, ',') != false):
                                        //work:mul
                                            //if hq exist in work, extract it
                                            $w = explode(',', $contact->workPhones);
                                            $mobileW = [];
                                            $counterW = 0;
                                            $existW = null;
                                            foreach($w as $value):
                                                $mobileW[$counterW++] = $this->__NumberFormater($value);
                                                if($hqc == $this->__NumberFormater($value)):
                                                    $existW = 1;
                                                endif;
                                            endforeach;
                                            if($existW):                    
                                                if (($key = array_search($hqc, $mobileW)) !== false) {
                                                    unset($mobileW[$key]);
                                                }
                                                $contact->workPhones = implode(",", $mobileW);
                                                $contact->save();
                                            else:
                                                //if hq does not exist
                                                //put hq in home
                                                $contact->homePhones = $hq;
                                                $contact->save();
                                            endif;
                                    endif;
                            elseif(strpos($contact->homePhones, ',') == false):
                                //if home is single
                                    //compare with work
                                    if(strlen($contact->workPhones) == 0):
                                        //work:blank
                                        //put hq in work
                                        $contact->workPhones = $hq;
                                        $contact->save();
                                    elseif(strpos($contact->workPhones, ',') == false):
                                        //work:single
                                            if($this->__NumberFormater($contact->workPhones) != $hqc):
                                                //if work is differnt with hq
                                                //swap home and put hq in home
                                                $cc->custom148 = $contact->homePhones;
                                                $cc->custom150 = 'swap';
                                                $cc->save();
                                                $contact->homePhones = $hq;
                                                $contact->save();
                                            endif;
                                    elseif(strpos($contact->workPhones, ',') != false):
                                        //work:mul
                                            //if he exist in work, extract it
                                            $w = explode(',', $contact->workPhones);
                                            $mobileW = [];
                                            $counterW = 0;
                                            $existW = null;
                                            foreach($w as $value):
                                                $mobileW[$counterW++] = $this->__NumberFormater($value);
                                                if($hqc == $this->__NumberFormater($value)):
                                                    $existW = 1;
                                                endif;
                                            endforeach;
                                            if($existW):                    
                                                if (($key = array_search($hqc, $mobileW)) !== false) {
                                                    unset($mobileW[$key]);
                                                }
                                                $contact->workPhones = implode(",", $mobileW);
                                                $contact->save();
                                            else:
                                                //swap home and put hq in home
                                                $cc->custom148 = $contact->homePhones;
                                                $cc->custom150 = 'swap';
                                                $cc->save();
                                                $contact->homePhones = $hq;
                                                $contact->save();
                                            endif;
                                    endif;
                            elseif(strpos($contact->homePhones, ',') != false):
                                //if home is multiple
                                //if exist , extract it
                                $w = explode(',', $contact->homePhones);
                                $mobileW = [];
                                $counterW = 0;
                                $existW = null;
                                foreach($w as $value):
                                    $mobileW[$counterW++] = $this->__NumberFormater($value);
                                    if($hqc == $this->__NumberFormater($value)):
                                        $existW = 1;
                                    endif;
                                endforeach;
                                if($existW):                    
                                    if (($key = array_search($hqc, $mobileW)) !== false) {
                                        unset($mobileW[$key]);
                                    }
                                    $contact->homePhones = implode(",", $mobileW);
                                    $contact->save();
                                else:
                                    //if does not exist
                                    //compare with work
                                    if(strlen($contact->workPhones) == 0):
                                        //work:blank
                                        //put hq in work
                                        $contact->workPhones = $hq;
                                        $contact->save();
                                    elseif(strpos($contact->workPhones, ',') == false):
                                        //work:single
                                        //if different
                                        //swap home and put hq in home
                                        $cc->custom148 = $contact->homePhones;
                                        $cc->custom150 = 'swap';
                                        $cc->save();
                                        $contact->homePhones = $hq;
                                        $contact->save();
                                    elseif(strpos($contact->workPhones, ',') != false):
                                        //work:multiple
                                        //if exist , extract
                                        $w = explode(',', $contact->workPhones);
                                        $mobileW = [];
                                        $counterW = 0;
                                        $existW = null;
                                        foreach($w as $value):
                                            $mobileW[$counterW++] = $this->__NumberFormater($value);
                                            if($hqc == $this->__NumberFormater($value)):
                                                $existW = 1;
                                            endif;
                                        endforeach;
                                        if($existW):                    
                                            if (($key = array_search($hqc, $mobileW)) !== false) {
                                                unset($mobileW[$key]);
                                            }
                                            $contact->workPhones = implode(",", $mobileW);
                                            $contact->save();
                                        else:
                                            //if does not exist
                                            //swap home and put hq in home
                                            $cc->custom148 = $contact->homePhones;
                                            $cc->custom150 = 'swap';
                                            $cc->save();
                                            $contact->homePhones = $hq;
                                            $contact->save();
                                        endif;
                                    endif;
                                endif;
                            endif;
                        endif;
        endforeach;
        $id =0;
        return view('testingHq', compact('page', 'id'));
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