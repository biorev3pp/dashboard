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

    public function setColumn()
    {
        echo 'Invalid Path'; die;
        //$notAllowedFiedls = ['id', 'record_id', 'contact_id', 'account_id', 'f_first_name', 'f_last_name', 'number1', 'number2', 'number3', 'number1type', 'number2type', 'number3type', 'number1call', 'number2call', 'number3call', 'ext1', 'ext2', 'ext3', 'hnumber', 'wnumber', 'mnumber',  'stage', 'old_stage', 'disposition', 'last_outreach_email', 'last_outreach_activity', 'last_campaign', 'last_export', 'last_agent_dispo_time', 'dial_attempts', 'dial_attempts', 'last_update_at', 'fivenine_created_at', 'outreach_touched_at', 'mcall_attempts', 'mcall_received', 'hcall_attempts', 'hcall_received', 'wcall_attempts', 'wcall_received', 'email_delivered', 'email_opened', 'email_clicked', 'email_replied', 'email_bounced', 'dataset', 'custom1', 'custom2', 'custom9', 'custom10', 'custom11', 'custom12', 'custom29', 'created_at', 'updated_at'];
        //$fields = DB::getSchemaBuilder()->getColumnListing("contacts");

        //$labels = ['', 'Purchase Authorization', 'Department', 'Job Function', 'Supplemental Email', 'Company HQ Phone', 'Education', 'Employment History', 'Interested In', 'Industry', 'Primary Industry', 'Company Revenue', 'Company Rev Range', 'Marketing Budget', 'VR / AR', 'Options & Selection Sys', 'Home Tech Challenge', 'Follow Up', 'ZoomInfo Contact ID', 'ZoomInfo Accuracy', 'ZoomInfo Score', '', '', '', '', '', '', '', '', 'Timezone Group', 'Number Swapping Status', 'Custom tag', 'OLD Direct Phone', 'OLD Company HQ', 'OLD Work Phone', 'ZoomInfo Listing'];
        for ($i=36; $i <= 150; $i++) { 
            DB::table("temp_contacts")->insert(['field' => 'custom'.$i, 'label' => 'Custom Field '.$i, 'group_name' => 'Outreach Additional Fields']);
        }
        echo 'all done'; die;
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

}