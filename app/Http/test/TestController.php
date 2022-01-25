<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Stages;
use App\Models\Contacts;
use App\Models\OutreachMailings;
use App\Models\FivenineCallLogs;

class TestController extends Controller
{
    public function __construct()
    {
       // $this->middleware('auth');
    }

    public function setNumber()
    {
        $contacts = Contacts::select('record_id', 'mobilePhones', 'workPhones', 'homePhones')->where('id', '<=', 100)->get();

        echo '<table border="1"><thead>
            <tr>
            <th>RID</th>
            <th>Mobile</th>
            <th>Mobile F</th>
            <th>Mobile Ext</th>
            <th>Work</th>
            <th>Work F</th>
            <th>Work Ext</th>
            <th>Home</th>
            <th>Home F</th>
            <th>Home Ext</th>
            </tr>
        </thead><tbody>';
        foreach ($contacts as $value) {
           echo '<tr><td>'.$value->record_id.'</td>
           <td>'.$value->mobilePhones.'</td><td>'. $this->__NumberFormater1($value->mobilePhones).'</td><td>'.$this->__NumberExtFormater1($value->mobilePhones).'</td>
           <td>'.$value->workPhones.'</td><td>'.$this->__NumberFormater1($value->workPhones).'</td><td>'.$this->__NumberExtFormater1($value->workPhones).'</td>
           <td>'.$value->homePhones.'</td><td>'.$this->__NumberFormater1($value->homePhones).'</td><td>'.$this->__NumberExtFormater1($value->homePhones).'</td>
           </tr>';
        }
        echo '</tbody> </table>'; die;
    }

    public function emailCounter($nom = 1)
    {
        $last = $nom+999;

        $contacts = Contacts::where('record_id', '>=', $nom)->where('record_id', '<=', $last)->get();
        foreach ($contacts as $key => $contact) {
            $totalmattempt = FivenineCallLogs::where('record_id', $contact->record_id)->where('dial_attempts', '>=', 1)->where('number_type', 'm')->sum('dial_attempts');
            $totalhattempt = FivenineCallLogs::where('record_id', $contact->record_id)->where('dial_attempts', '>=', 1)->where('number_type', 'hq')->sum('dial_attempts');
            $totaldattempt = FivenineCallLogs::where('record_id', $contact->record_id)->where('dial_attempts', '>=', 1)->where('number_type', 'd')->sum('dial_attempts');

            $totalmreceived = FivenineCallLogs::where('record_id', $contact->record_id)->where('call_received', 1)->where('dial_attempts', '>=', 1)->where('number_type', 'm')->count();
            $totalhqreceived = FivenineCallLogs::where('record_id', $contact->record_id)->where('call_received', 1)->where('dial_attempts', '>=', 1)->where('number_type', 'hq')->count();
            $totaldreceived = FivenineCallLogs::where('record_id', $contact->record_id)->where('call_received', 1)->where('dial_attempts', '>=', 1)->where('number_type', 'd')->count();
      
            $contact->update(['mcall_attempts' => $totalmattempt,
                        'hcall_attempts' => $totalhattempt,
                        'wcall_attempts' => $totaldattempt,
                        'mcall_received' => $totalmreceived,
                        'hcall_received' => $totalhqreceived,
                        'wcall_received' => $totaldreceived]);
            
        }
        return view('csync')->with(compact('last'));
        
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
    private function __NumberFormater1($var = null)
    {
        if(strpos($var, ",") > -1){
            $mob = explode(",", $var);
            $var = $mob[0];
        }
        if(strpos($var, "ext") > -1){
            $mob = explode("ext", $var);
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

    private function __NumberExtFormater1($var = null)
    {
        if(strpos($var, ",") > -1){
            $mob = explode(",", $var);
            $var = $mob[0];
        }
        if(strpos(strtolower($var), "ext") > -1){
            $mob = explode("ext", strtolower($var));
            $string = str_replace(' ', '', trim($mob[1]));
            $string = str_replace('.', '', $string);
        } else {
            $string = '';
        }
        return $string;
    }

}