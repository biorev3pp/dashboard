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
use App\Models\ReportSequenceMailings;
use App\Models\ReportMailings;
use App\Models\ReportAllMailings;
use App\Models\ReportCalls;

class LogReportsController extends Controller
{
    
    public function __construct()
    {
       
    }
    //sink log email:single
    public function sinkLogEmailSingle(){
        for($i = 1; $i < 100; $i++):
            $date = date("Y-m-d", strtotime("now")-$i*24*3600);
            $deliveredEmail = OutreachMailings::whereNotNull('contact_id')->where("mailingType", "=", "single")->whereDate("deliveredAt", "=", $date)->count();
            $openedEmail = OutreachMailings::whereNotNull('contact_id')->where("mailingType", "=", "single")->whereDate("openedAt", "=", $date)->count();
            $repliedEmail = OutreachMailings::whereNotNull('contact_id')->where("mailingType", "=", "single")->whereDate("repliedAt", "=", $date)->count();
            $bouncedEmail = OutreachMailings::whereNotNull('contact_id')->where("mailingType", "=", "single")->whereDate("bouncedAt", "=", $date)->count();
            $unsubscribedEmail = OutreachMailings::whereNotNull('contact_id')->where("mailingType", "=", "single")->whereDate("unsubscribedAt", "=", $date)->count();
            $clickedEmail = OutreachMailings::whereNotNull('contact_id')->where("mailingType", "=", "single")->whereDate("clickedAt", "=", $date)->count();
            
            $count = ReportMailings::where("date", '=', $date)->count();
            if($count == 0){
                ReportMailings::create([
                    "date" => $date,
                    "delivered" => $deliveredEmail,
                    "opened" => $openedEmail,
                    "replied" => $repliedEmail,
                    "bounced" => $bouncedEmail,
                    "unsubscribed" => $unsubscribedEmail,
                    "clicked" => $clickedEmail
                ]);
            }else{
                $record = ReportMailings::whereDate("date", '=', $date)->first();
                $record->delivered = $deliveredEmail;
                $record->opened = $openedEmail;
                $record->replied = $repliedEmail;
                $record->bounced = $bouncedEmail;
                $record->unsubscribed = $unsubscribedEmail;
                $record->clicked = $clickedEmail;
                $record->save();
            }
        endfor;
        echo "all done"; die;
    }
    //sink log email:sequence
    public function sinkLogEmailSequence(){
        for($i = 1; $i < 100; $i++):
            $date = date("Y-m-d", strtotime("now")-$i*24*3600);
            $deliveredEmail = OutreachMailings::whereNotNull('contact_id')->where("mailingType", "=", "sequence")->whereDate("deliveredAt", "=", $date)->count();
            $openedEmail = OutreachMailings::whereNotNull('contact_id')->where("mailingType", "=", "sequence")->whereDate("openedAt", "=", $date)->count();
            $repliedEmail = OutreachMailings::where("mailingType", "=", "sequence")->whereDate("repliedAt", "=", $date)->count();
            $bouncedEmail = OutreachMailings::whereNotNull('contact_id')->where("mailingType", "=", "sequence")->whereDate("bouncedAt", "=", $date)->count();
            $unsubscribedEmail = OutreachMailings::whereNotNull('contact_id')->where("mailingType", "=", "sequence")->whereDate("unsubscribedAt", "=", $date)->count();
            $clickedEmail = OutreachMailings::whereNotNull('contact_id')->where("mailingType", "=", "sequence")->whereDate("clickedAt", "=", $date)->count();

            $count = ReportSequenceMailings::whereDate("date", '=', $date)->count();
            if($count == 0){
                ReportSequenceMailings::create([
                    "date" => $date,
                    "delivered" => $deliveredEmail,
                    "opened" => $openedEmail,
                    "replied" => $repliedEmail,
                    "bounced" => $bouncedEmail,
                    "unsubscribed" => $unsubscribedEmail,
                    "clicked" => $clickedEmail
                ]);
            }else{
                $record = ReportSequenceMailings::whereDate("date", '=', $date)->first();
                $record->delivered = $deliveredEmail;
                $record->opened = $openedEmail;
                $record->replied = $repliedEmail;
                $record->bounced = $bouncedEmail;
                $record->unsubscribed = $unsubscribedEmail;
                $record->clicked = $clickedEmail;
                $record->save();
            }
        endfor;
        echo "all done"; die;
    }
    //sink log email:sequence
    public function sinkLogEmailAll(){
        for($i = 1; $i < 100; $i++):
            $date = date("Y-m-d", strtotime("now")-$i*24*3600);
            $deliveredEmail = OutreachMailings::whereNotNull('contact_id')->whereIn("mailingType", ["sequence", "single"])->whereDate("deliveredAt", "=", $date)->count();
            $openedEmail = OutreachMailings::whereNotNull('contact_id')->whereIn("mailingType", ["sequence", "single"])->whereDate("openedAt", "=", $date)->count();
            $repliedEmail = OutreachMailings::whereNotNull('contact_id')->whereIn("mailingType", ["sequence", "single"])->whereDate("repliedAt", "=", $date)->count();
            $bouncedEmail = OutreachMailings::whereNotNull('contact_id')->whereIn("mailingType", ["sequence", "single"])->whereDate("bouncedAt", "=", $date)->count();
            $unsubscribedEmail = OutreachMailings::whereNotNull('contact_id')->whereIn("mailingType", ["sequence", "single"])->whereDate("unsubscribedAt", "=", $date)->count();
            $clickedEmail = OutreachMailings::whereNotNull('contact_id')->whereIn("mailingType", ["sequence", "single"])->whereDate("clickedAt", "=", $date)->count();
            $count = ReportAllMailings::whereDate("date", '=', $date)->count();
            if($count == 0){
                ReportAllMailings::create([
                    "date" => $date,
                    "delivered" => $deliveredEmail,
                    "opened" => $openedEmail,
                    "replied" => $repliedEmail,
                    "bounced" => $bouncedEmail,
                    "unsubscribed" => $unsubscribedEmail,
                    "clicked" => $clickedEmail
                ]);
            }else{
                $record = ReportAllMailings::where("date", '=', $date)->first();
                $record->delivered = $deliveredEmail;
                $record->opened = $openedEmail;
                $record->replied = $repliedEmail;
                $record->bounced = $bouncedEmail;
                $record->unsubscribed = $unsubscribedEmail;
                $record->clicked = $clickedEmail;
                $record->save();
            }
        endfor;
        echo "all done"; die;
    }
    //sink log : call
    public function sinkLogCalls(){
        $now = strtotime("now");
        for($i = 1; $i < 100; $i++):
            $date = date("Y-m-d", $now-$i*24*3600);
            $ts = strtotime($date);
            $te = $ts + 24*3600-1;
            $total_call = FivenineCallLogs::where("n_timestamp", ">", $ts)->where("n_timestamp", "<", $te)->count();
            
            $total_mobile_calls = FivenineCallLogs::where("n_timestamp", ">", $ts)->where("n_timestamp", "<", $te)->where("number_type", '=', 'm')->count();
            $total_mobile_calls_received = FivenineCallLogs::where("n_timestamp", ">", $ts)->where("n_timestamp", "<", $te)->where("call_received", '=', 1)->where("number_type", '=', 'm')->count();
            
            $total_home_calls = FivenineCallLogs::where("n_timestamp", ">", $ts)->where("n_timestamp", "<", $te)->where("number_type", '=', 'hq')->count();
            $total_home_calls_received = FivenineCallLogs::where("n_timestamp", ">", $ts)->where("n_timestamp", "<", $te)->where("call_received", '=', 1)->where("number_type", '=', 'hq')->count();
            
            $total_work_calls = FivenineCallLogs::where("n_timestamp", ">", $ts)->where("n_timestamp", "<", $te)->where("number_type", '=', 'd')->count();
            $total_work_calls_received = FivenineCallLogs::where("n_timestamp", ">", $ts)->where("n_timestamp", "<", $te)->where("call_received", '=', 1)->where("number_type", '=', 'd')->count();

            $total_not_answered = FivenineCallLogs::where("n_timestamp", ">", $ts)->where("n_timestamp", "<", $te)->where("call_received", '=', 0)->count();
            
            $c = ReportCalls::whereDate("date", '=', $date)->count();
            if($c == 0){
                ReportCalls::create([
                    "date" => $date,
                    "total_calls" => $total_call,
                    "total_mobile_calls" => $total_mobile_calls,
                    "total_mobile_calls_received" => $total_mobile_calls_received,
                    "total_home_calls" => $total_home_calls,
                    "total_home_calls_received" => $total_home_calls_received,
                    "total_work_calls" => $total_work_calls,
                    "total_work_calls_received" => $total_work_calls_received,
                    "not_answered" => $total_not_answered
                ]);
            }else{
                $c = ReportCalls::where("date", '=', $date)->first();
                $c->total_calls = $total_call;
                $c->total_mobile_calls = $total_mobile_calls;
                $c->total_mobile_calls_received = $total_mobile_calls_received;
                $c->total_home_calls = $total_home_calls;
                $c->total_home_calls_received = $total_home_calls_received;
                $c->total_work_calls = $total_work_calls;
                $c->total_work_calls_received = $total_work_calls_received;
                $c->not_answered = $total_not_answered;
                $c->save();
            }
        endfor;
        echo "all done"; die;
    }
    public function udateEmailCounter($i)
    {
        if($i > 500){
            echo 'all done'; die;
        }
        $page = $i;
        $start = $i*100;
        $end = ($i+1)*100;
        $id = null;
        for($i = $start; $i < $end; $i++){
                $contact = Contacts::where("record_id", "=", $i)->count();
                if($contact > 0){
                    $contact = Contacts::where("record_id", "=", $i)->first();
                    $totalemail = OutreachMailings::where('contact_id', $contact->record_id)->whereDate("deliveredAt", ">", "2015-01-01")->where('deliveredAt', '!=', null)->count();
                    $totalopen = OutreachMailings::where('contact_id', $contact->record_id)->whereDate("openedAt", ">", "2015-01-01")->where('openedAt', '!=', null)->count();
                    $totalreply = OutreachMailings::where('contact_id', $contact->record_id)->whereDate("repliedAt", ">", "2015-01-01")->where('repliedAt', '!=', null)->count();
                    $totalbounced = OutreachMailings::where('contact_id', $contact->record_id)->whereDate("bouncedAt", ">", "2015-01-01")->where('bouncedAt', '!=', null)->count();            
                    $totalclick = OutreachMailings::where('contact_id', $contact->record_id)->whereDate("clickedAt", ">", "2015-01-01")->where('clickedAt', '!=', null)->count();
                    $ucontact = Contacts::whereId($contact->id)->first();
                    $ucontact->update([
                        'email_delivered' => $totalemail,
                        'email_opened' => $totalopen,
                        'email_clicked' => $totalclick,
                        'email_replied' => $totalreply,
                        'email_bounced' => $totalbounced,]
                    );
                    $id = $contact->record_id;
                }
        }
        $i = $page;
        return view('csyn-uec', compact('i', 'id'));
    }  

} 