<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Stages;
use App\Models\Contacts;
use App\Models\ContactCustoms;
use App\Models\FivenineCallLogs;

class TestController extends Controller
{
    public function __construct()
    {
       // $this->middleware('auth');
    }

    public function updateCustom()
    {
        $count = 0;
        /* $contacts = Contacts::get();
        
        foreach ($contacts as $key => $contact) {
            $custom = ContactCustoms::where('contact_id', $contact->id)->count();
            $customs = ContactCustoms::where('contact_id', $contact->id)->get();
            if($custom >= 2) {
                foreach ($customs as $key2 => $value) {
                    if($key2 >= 1) {
                        ContactCustoms::where('id', $value->id)->delete();
                        $count++;
                    }
                }
                
            }
        } */
        Contacts::whereIn('stage', [5,17,19])->update(['dataset' => 7]);
        echo ' records deleted'; die;
    }

    public function numberformating()
    {
        $logs = FivenineCallLogs::where('id', '>=', 7224)->get();
        foreach ($logs as $key => $log) {
            $contact = Contacts::where('record_id', $log->record_id)->get()->first();
            $flog = FivenineCallLogs::where('id', $log->id)->get()->first();
            if($contact->mnumber == $flog->dnis): $t = 'm';
            elseif($contact->hqnumber == $flog->dnis): $t = 'hq';
            elseif($contact->dnumber == $flog->dnis): $t = 'd';
            else: $t = '';
            endif;
            $flog->update(['number_type' => $t]);
            echo $flog->id.' is done.<br>'; 
        }
        die;
    }

    private function __NumberFormater($var = null)
    {
        $string = str_replace(' ', '', $var); // Replaces all spaces.
        $string = str_replace('-', '', $var); // Replaces all hyphens.
        $string = preg_replace('/[^0-9\-]/', '', $string); // Removes special chars.
        if(strlen($string) == 11) {
            $string = substr($string, 1, 10);
        } else {
            $string = substr($string, 0, 10);
        }
        $string = (int) $string;
        return $string;
    }

    public function calculate(Request $request)
    {
        $stages = Stages::get();
        if($request->isMethod('post')) 
        {
            $ed = $request->ed;
            $eo = $request->eo;
            $ec = $request->ec;
            $ct = $request->ct;
            $cr = $request->cr;
            $cwt = $request->cwt;
            $cwr = $request->cwr;
            $d = $request->d;

            $set = ['Unknown Group', 'Cold Lead', 'Semi-Cold Lead', 'Warm Lead', 'Semi-Hot Lead', 'Hot Lead'];
            
            if($ed == 0 || $ed == '') { 
                $condition1 = 0;
                $condition2 = 0;
            }
            else {
                $condition2 = round($ec*100/$ed, 0);
                $condition1 = round($eo*100/$ed, 0);
            } 

            if($ct == 0 || $ct == '') { 
                $condition3 = 0;
            }
            else {
                $condition3 = round($cr*100/$ct, 0);
            } 

            if($cwt == 0 || $cwt == '') { 
                $condition4 = 0;
            }
            else {
                $condition4 = round($cwr*100/$cwt, 0);
            } 

            $score = round(($condition1+$condition2+$condition3+$condition4)*100/400);
            $t = round($score/20);
            $g = $set[$t];
            if($t == 0) {
                $label = 0000;
            } else {
                $label = $this->__calculateLabel($t, $condition1, $condition2, $condition3, $condition4);
            }
            
            $pdata = $request;
            return view('test')->with(compact('stages', 'g', 'score', 'label', 'pdata'));
        }
        return view('test')->with(compact('stages'));
    }

    private function __calculateLabel($k, $c1, $c2, $c3, $c4)
    {
        $label = '';
        $set = ['',
                ['c1' =>49, 'c2' =>19, 'c3' =>19, 'c4' =>23],
                ['c1' =>50, 'c2' =>20, 'c3' =>20, 'c4' =>25],
                ['c1' =>60, 'c2' =>35, 'c3' =>35, 'c4' =>50],
                ['c1' =>70, 'c2' =>50, 'c3' =>50, 'c4' =>75],
                ['c1' =>90, 'c2' =>70, 'c3' =>70, 'c4' =>90]     
        ];
        if($k == 1) {
            if($c1 <= $set[$k]['c1']) $label.='1'; else $label.='0';
            if($c2 <= $set[$k]['c2']) $label.='1'; else $label.='0';
            if($c3 <= $set[$k]['c3']) $label.='1'; else $label.='0';
            if($c4 <= $set[$k]['c4']) $label.='1'; else $label.='0';
            return $label;
        } else {
            if($c1 >= $set[$k]['c1']) $label.='1'; else $label.='0';
            if($c2 >= $set[$k]['c2']) $label.='1'; else $label.='0';
            if($c3 >= $set[$k]['c3']) $label.='1'; else $label.='0';
            if($c4 >= $set[$k]['c4']) $label.='1'; else $label.='0';
            return $label;
        }
    }

}