<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\OutreachMailings;
use App\Models\Contacts;

class EmailsController extends Controller
{

    public function index(Request $request)
    {
        $d = $this->__getStartEndDate($request);
        $startDate = $d["startDate"];
        $endDate = $d["endDate"];
        
        $recordsDeliveredAt = OutreachMailings::select(DB::raw('count(*) as email, `mailboxAddress`'))->whereNotNull("mailboxAddress");
        $recordsBouncedAt = OutreachMailings::select(DB::raw('count(*) as email, `mailboxAddress`'))->whereNotNull("mailboxAddress");
        $recordsClickedAt = OutreachMailings::select(DB::raw('count(*) as email, `mailboxAddress`'))->whereNotNull("mailboxAddress");
        $recordsOpenedAt = OutreachMailings::select(DB::raw('count(*) as email, `mailboxAddress`'))->whereNotNull("mailboxAddress");
        $recordsRepliedAt = OutreachMailings::select(DB::raw('count(*) as email, `mailboxAddress`'))->whereNotNull("mailboxAddress");
        $recordsRetryAt = OutreachMailings::select(DB::raw('count(*) as email, `mailboxAddress`'))->whereNotNull("mailboxAddress");
        
        if($startDate == $endDate){
            $recordsDeliveredAt = $recordsDeliveredAt->whereDate("deliveredAt", $startDate)->groupBy('mailboxAddress')->get()->toArray();
            $recordsBouncedAt = $recordsBouncedAt->whereDate("bouncedAt", $startDate)->groupBy('mailboxAddress')->get()->toArray();
            $recordsClickedAt = $recordsClickedAt->whereDate("clickedAt", $startDate)->groupBy('mailboxAddress')->get()->toArray();
            $recordsOpenedAt = $recordsOpenedAt->whereDate("openedAt", $startDate)->groupBy('mailboxAddress')->get()->toArray();
            $recordsRepliedAt = $recordsRepliedAt->whereDate("repliedAt", $startDate)->groupBy('mailboxAddress')->get()->toArray();
            $recordsRetryAt = $recordsRetryAt->whereDate("retryAt", $startDate)->groupBy('mailboxAddress')->get()->toArray();
            
        }else{
            $recordsDeliveredAt = $recordsDeliveredAt->whereBetween("deliveredAt", [$startDate, $endDate])->groupBy('mailboxAddress')->get()->toArray();
            $recordsBouncedAt = $recordsBouncedAt->whereBetween("bouncedAt", [$startDate, $endDate])->groupBy('mailboxAddress')->get()->toArray();
            $recordsClickedAt = $recordsClickedAt->whereBetween("clickedAt", [$startDate, $endDate])->groupBy('mailboxAddress')->get()->toArray();
            $recordsOpenedAt = $recordsOpenedAt->whereBetween("openedAt", [$startDate, $endDate])->groupBy('mailboxAddress')->get()->toArray();
            $recordsRepliedAt = $recordsRepliedAt->whereBetween("repliedAt", [$startDate, $endDate])->groupBy('mailboxAddress')->get()->toArray();
            $recordsRetryAt = $recordsRetryAt->whereBetween("retryAt", [$startDate, $endDate])->groupBy('mailboxAddress')->get()->toArray();
        }
        ksort($recordsDeliveredAt);
        ksort($recordsBouncedAt);
        ksort($recordsClickedAt);
        ksort($recordsOpenedAt);
        ksort($recordsRepliedAt);
        ksort($recordsRetryAt);
        
        $allData = [];
        $allEmailDelivered = [];
        if(count($recordsDeliveredAt) > 0){
            foreach($recordsDeliveredAt as $value){
                if (array_key_exists($value["mailboxAddress"], $allEmailDelivered)) {
                    $allEmailDelivered[$value["mailboxAddress"]] += intval($value["email"]);
                }else{
                    $allEmailDelivered[$value["mailboxAddress"]] = intval($value["email"]);
                }
                if (array_key_exists($value["mailboxAddress"], $allData)) {
                    $allData[$value["mailboxAddress"]] += intval($value["email"]);
                }else{
                    $allData[$value["mailboxAddress"]] = intval($value["email"]);
                }
            }
        }
        $allEmailBounced = [];
        if(count($recordsBouncedAt) > 0){
            foreach($recordsBouncedAt as $value){
                if (array_key_exists($value["mailboxAddress"], $allEmailBounced)) {
                    $allEmailBounced[$value["mailboxAddress"]] += intval($value["email"]);
                }else{
                    $allEmailBounced[$value["mailboxAddress"]] = intval($value["email"]);
                }
                if (array_key_exists($value["mailboxAddress"], $allData)) {
                    $allData[$value["mailboxAddress"]] += intval($value["email"]);
                }else{
                    $allData[$value["mailboxAddress"]] = intval($value["email"]);
                }
            }
        }
        $allEmailClicked = [];
        if(count($recordsClickedAt) > 0){
            foreach($recordsClickedAt as $value){
                if (array_key_exists($value["mailboxAddress"], $allEmailClicked)) {
                    $allEmailClicked[$value["mailboxAddress"]] += intval($value["email"]);
                }else{
                    $allEmailClicked[$value["mailboxAddress"]] = intval($value["email"]);
                }
            }
        }
        $allEmailOpened = [];
        if(count($recordsOpenedAt) > 0){
            foreach($recordsOpenedAt as $value){
                if (array_key_exists($value["mailboxAddress"], $allEmailOpened)) {
                    $allEmailOpened[$value["mailboxAddress"]] += intval($value["email"]);
                }else{
                    $allEmailOpened[$value["mailboxAddress"]] = intval($value["email"]);
                }
            }
        }
        $allEmailReplied = [];
        if(count($recordsRepliedAt) > 0){
            foreach($recordsRepliedAt as $value){
                if (array_key_exists($value["mailboxAddress"], $allEmailReplied)){
                    $allEmailReplied[$value["mailboxAddress"]] += intval($value["email"]);
                }else{
                    $allEmailReplied[$value["mailboxAddress"]] = intval($value["email"]);
                }
            }
        }
        $allEmailRetry = [];
        if(count($recordsRetryAt) > 0){
            foreach($recordsRepliedAt as $value){
                if (array_key_exists($value["mailboxAddress"], $allEmailRetry)) {
                    $allEmailRetry[$value["mailboxAddress"]] += intval($value["email"]);
                }else{
                    $allEmailRetry[$value["mailboxAddress"]] = intval($value["email"]);
                }
            }
        }
        ksort($allData);
        $allEmailsLabel = [];
        $agentName = [];
        foreach($allData as $key => $value){
            $agentName[] = $key;
            $allEmailsLabel[] = $key . " : ". $value;
        }
        $allEmailsLabelDelivered = [];
        foreach($allEmailDelivered as $key => $value){
            $allEmailsLabelDelivered[] = $key . " : ". $value;
        }
        $allEmailsLabelBounced = [];
        foreach($allEmailBounced as $key => $value){
            $allEmailsLabelBounced[] = $key . " : ". $value;
        }
        $allEmailsLabelClicked = [];
        foreach($allEmailClicked as $key => $value){
            $allEmailsLabelClicked[] = $key . " : ". $value;
        }
        $allEmailsLabelOpened = [];
        foreach($allEmailOpened as $key => $value){
            $allEmailsLabelOpened[] = $key . " : ". $value;
        }
        $allEmailsLabelReplied = [];
        foreach($allEmailReplied as $key => $value){
            $allEmailsLabelReplied[] = $key . " : ". $value;
        }
        $allEmailsLabelRetry = [];        
        foreach($allEmailRetry as $key => $value){
            $allEmailsLabelRetry[] = $key . " : ". $value;
        }
        if($request->input("agentName")){
            if(array_key_exists($request->input("agentName"), $allEmailClicked)){
                $labelClicked = [
                        "Clicked : " . $allEmailClicked[$request->input("agentName")],
                        "Not Clicked : " . ($allData[$request->input("agentName")]-$allEmailClicked[$request->input("agentName")])
                    ];
                $seriesClicked = [$allEmailClicked[$request->input("agentName")], $allData[$request->input("agentName")]-$allEmailClicked[$request->input("agentName")]];
            }else{
                $labelClicked = [];
                $seriesClicked = [];
            }
            if(array_key_exists($request->input("agentName"), $allEmailOpened)){
                $labelOpened = [
                    "Opened : " . $allEmailOpened[$request->input("agentName")],
                    "Not Opened : " . ($allData[$request->input("agentName")]-$allEmailOpened[$request->input("agentName")])
                ];
                $seriesOpened = [$allEmailOpened[$request->input("agentName")], $allData[$request->input("agentName")]-$allEmailOpened[$request->input("agentName")]];
            }else{
                $labelOpened = [];
                $seriesOpened = [];
            }
            if(array_key_exists($request->input("agentName"), $allEmailReplied)){
                $labelReplied = [
                    "Replied : " . $allEmailReplied[$request->input("agentName")],
                    "Not Replied : " . ($allData[$request->input("agentName")]-$allEmailReplied[$request->input("agentName")])
                ];
                $seriesReplied = [$allEmailReplied[$request->input("agentName")], $allData[$request->input("agentName")]-$allEmailReplied[$request->input("agentName")]];
            }else{
                $labelReplied = [];
                $seriesReplied = [];
            }
            if(array_key_exists($request->input("agentName"), $allEmailDelivered)){
                $labelDelivered = [
                    "Delivered : " . $allEmailDelivered[$request->input("agentName")],
                    "Not Delivered : " . ($allData[$request->input("agentName")]-$allEmailDelivered[$request->input("agentName")])
                ];
                $seriesDelivered = [$allEmailDelivered[$request->input("agentName")], $allData[$request->input("agentName")]-$allEmailDelivered[$request->input("agentName")]];
            }else{
                $labelDelivered = [];
                $seriesDelivered = [];
            }
            if(array_key_exists($request->input("agentName"), $allEmailBounced)){
                $labelBounced = [
                    "Bonced : " . $allEmailBounced[$request->input("agentName")],
                    "Not Bonced : " . ($allData[$request->input("agentName")]-$allEmailBounced[$request->input("agentName")])
                ];
                $seriesBounced = [$allEmailBounced[$request->input("agentName")], $allData[$request->input("agentName")]-$allEmailBounced[$request->input("agentName")]];
            }else{
                $labelBounced = [];
                $seriesBounced = [];
            }
            return [
                "label" => $allEmailsLabel, 
                "series" => array_values($allData),
                "agentName" => $agentName,
                "labelDelivered" => $labelDelivered,
                "seriesDelivered" => $seriesDelivered,
                "labelBounced" => $labelBounced,
                "seriesBounced" => $seriesBounced,
                "labelClicked" => $labelClicked,
                "seriesClicked" => $seriesClicked,
                "labelOpened" => $labelOpened,
                "seriesOpened" => $seriesOpened,
                "labelReplied" => $labelReplied,
                "seriesReplied" => $seriesReplied,
            ];
        }
        return [
            "label" => $allEmailsLabel, 
            "series" => array_values($allData),
            "agentName" => $agentName,
            "labelDelivered" => $allEmailsLabelDelivered,
            "seriesDelivered" => array_values($allEmailDelivered),
            "labelBounced" => $allEmailsLabelBounced,
            "seriesBounced" => array_values($allEmailBounced),
            "labelClicked" => $allEmailsLabelClicked,
            "seriesClicked" => array_values($allEmailClicked),
            "labelOpened" => $allEmailsLabelOpened,
            "seriesOpened" => array_values($allEmailOpened),
            "labelReplied" => $allEmailsLabelReplied,
            "seriesReplied" => array_values($allEmailReplied),
            "labelRetry" => $allEmailsLabelRetry,
            "seriesRetry" => array_values($allEmailRetry),
            "startDate" => $startDate,
            "endDate" => $endDate
        ];
    }

    public function getAllData(Request $request)
    {
        $d = $this->__getStartEndDate($request);
        $startDate = $d["startDate"];
        $endDate = $d["endDate"];
        $agentName = $request->input("agentName");
        $type = $request->input("type");
        $recordPerPage  = $request->recordPerPage;

        if($type == "All"){
            $record = DB::table("outreach_mailings")->where(function($query) use ($startDate,$endDate){
                $query->whereBetween("deliveredAt", [$startDate, $endDate])->orWhereBetween("bouncedAt", [$startDate, $endDate]);
            });
        }elseif($type == "Delivered"){
            $record = DB::table("outreach_mailings")->whereBetween("deliveredAt", [$startDate, $endDate]);
        }elseif($type == "Bounced"){
            $record = DB::table("outreach_mailings")->whereBetween("bouncedAt", [$startDate, $endDate]);
        }elseif($type == "Clicked"){
            $record = DB::table("outreach_mailings")->whereBetween("clickedAt", [$startDate, $endDate]);
        }elseif($type == "Opened"){
            $record = DB::table("outreach_mailings")->whereBetween("openedAt", [$startDate, $endDate]);
        }elseif($type == "Replied"){
            $record = DB::table("outreach_mailings")->whereBetween("repliedAt", [$startDate, $endDate]);
        }
        
        if($agentName) {
            $record = $record->select('contact_id')->where("mailboxAddress", "LIKE", $agentName);
        } else {
            $record = $record->select('contact_id');
        }

        if($request->input("sortBy") == 'asc'):
            $recordIds = $record->orderBy($request->input('sortType'), 'asc')->get(); 
        else:
            $recordIds = $record->orderBy($request->input('sortType'), 'desc')->get();    
        endif;
        
        $recordIds = $record->pluck('contact_id')->toArray();
        $recordIds = array_unique($recordIds);
        $recordIds = array_filter($recordIds);

        $records = Contacts::select('contacts.id', 'contacts.record_id', 'contacts.first_name', 'contacts.last_name', 'contacts.title', 'contacts.company', 'contacts.mobilePhones', 'contacts.workPhones', 'contacts.homePhones', 'contacts.emails', 'contacts.outreach_touched_at', 'contacts.outreach_created_at', 'contacts.last_agent_dispo_time', 'contacts.last_agent', 'contacts.mcall_attempts', 'contacts.mcall_received', 'contacts.wcall_attempts', 'contacts.wcall_received', 'contacts.hcall_attempts', 'contacts.hcall_received', 'stages.id as stage', 'stages.name as stage_name', 'stages.css as stage_css', 'contacts.dataset','contacts.outreach_tag')
        ->addSelect(['email_delivered' => DB::table('outreach_mailings')->selectRaw('count(*)')->whereBetween("outreach_mailings.deliveredAt", [$startDate, $endDate])->whereColumn('outreach_mailings.contact_id', 'contacts.record_id')])
        ->addSelect(['email_opened' => DB::table('outreach_mailings')->selectRaw('count(*)')->whereBetween("outreach_mailings.openedAt", [$startDate, $endDate])->whereColumn('outreach_mailings.contact_id', 'contacts.record_id')])
        ->addSelect(['email_clicked' => DB::table('outreach_mailings')->selectRaw('count(*)')->whereBetween("outreach_mailings.clickedAt", [$startDate, $endDate])->whereColumn('outreach_mailings.contact_id', 'contacts.record_id')])
        ->addSelect(['email_replied' => DB::table('outreach_mailings')->selectRaw('count(*)')->whereBetween("outreach_mailings.repliedAt", [$startDate, $endDate])->whereColumn('outreach_mailings.contact_id', 'contacts.record_id')])
        ->addSelect(['email_bounced' => DB::table('outreach_mailings')->selectRaw('count(*)')->whereBetween("outreach_mailings.bouncedAt", [$startDate, $endDate])->whereColumn('outreach_mailings.contact_id', 'contacts.record_id')])
        ->leftjoin('stages', 'stages.oid', '=', 'contacts.stage')->whereIn('contacts.record_id', $recordIds);

        if(count($request->input('filterConditionsArray')) > 0):
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
        $allids = implode(',', $recordIds);
        $records = $records->OrderByRaw(DB::raw("FIELD (`contacts`.`record_id`, ".$allids.")"))->paginate($recordPerPage);
        return $records;
    }

    public function getEmailsAll(Request $request){
        $recordPerPage  = $request->input("recordPerPage");
        $agentName = $request->input("agentName");
        $dateRange = $request->input("dateRange");
        $type = $request->input("type");

        $d = $this->__getStartEndDate($request);
        $startDate = $d["startDate"];
        $endDate = $d["endDate"];
        if($startDate == $endDate){
            if($type == "All"){
                $record = DB::table("outreach_mailings")->where(function($query) use ($startDate){
                    $query->whereDate("deliveredAt", $startDate)->orWhereDate("bouncedAt", $startDate);
                });
            }if($type == "Delivered"){
                $record = DB::table("outreach_mailings")->whereDate("deliveredAt", $startDate);
            }elseif($type == "Bounced"){
                $record = DB::table("outreach_mailings")->whereDate("bouncedAt", $startDate);
            }elseif($type == "Clicked"){
                $record = DB::table("outreach_mailings")->whereDate("clickedAt", $startDate);
            }elseif($type == "Opened"){
                $record = DB::table("outreach_mailings")->whereDate("openedAt", $startDate);
            }elseif($type == "Replied"){
                $record = DB::table("outreach_mailings")->whereDate("repliedAt", $startDate);
            }
        }else{
            if($type == "All"){
                $record = DB::table("outreach_mailings")->where(function($query) use ($startDate,$endDate){
                    $query->whereBetween("deliveredAt", [$startDate, $endDate])->orWhereBetween("bouncedAt", [$startDate, $endDate]);
                });
            }elseif($type == "Delivered"){
                $record = DB::table("outreach_mailings")->whereBetween("deliveredAt", [$startDate, $endDate]);
            }elseif($type == "Bounced"){
                $record = DB::table("outreach_mailings")->whereBetween("bouncedAt", [$startDate, $endDate]);
            }elseif($type == "Clicked"){
                $record = DB::table("outreach_mailings")->whereBetween("clickedAt", [$startDate, $endDate]);
            }elseif($type == "Opened"){
                $record = DB::table("outreach_mailings")->whereBetween("openedAt", [$startDate, $endDate]);
            }elseif($type == "Replied"){
                $record = DB::table("outreach_mailings")->whereBetween("repliedAt", [$startDate, $endDate]);
            }
        }
        if($agentName){
            $record = $record->where("mailboxAddress", "LIKE", $agentName);
        }
        $record = $record->select('contact_id')->get();
        $recordIds = $record->pluck('contact_id')->toArray();

        $records = Contacts::leftjoin('stages', 'stages.oid', '=', 'contacts.stage')
        ->select('contacts.id as id', 'contacts.record_id', 'contacts.first_name',  'contacts.company', 'contacts.title', 'contacts.last_name', 'contacts.title', 'contacts.company', 'contacts.mobilePhones', 'contacts.workPhones', 'contacts.homePhones', 'contacts.emails', 'contacts.outreach_touched_at', 'contacts.outreach_created_at', 'contacts.last_agent_dispo_time', 'contacts.last_agent', 'contacts.email_delivered', 'contacts.email_opened', 'contacts.email_clicked', 'contacts.email_replied', 'contacts.email_bounced', 'contacts.mcall_attempts', 'contacts.mcall_received', 'contacts.wcall_attempts', 'contacts.wcall_received', 'contacts.hcall_attempts', 'contacts.hcall_received', 'stages.id as stage', 'stages.name as stage_name', 'stages.css as stage_css', 'contacts.dataset','contacts.outreach_tag');
        
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
        $results = $records->whereIn('record_id', $recordIds)->get();
        $results = $results->pluck('id')->toArray();
        return $results;
    }

    private function __getStartEndDate($request){// dd($request->input("dateRange"));
        if($request->input("dateRange")){
            $startDate = $request->input('dateRange.startDate');
            $endDate = $request->input('dateRange.endDate');

            $startDate = date("Y-m-d 00:00:00", strtotime($startDate));
            $endDate = date("Y-m-d 23:59:59", strtotime($endDate));
            
        }else{           
            $startDate = date('Y-m-d 00:00:00', strtotime('first day of last month'));
            $endDate  = date('Y-m-d 23:59:59', strtotime('last day of last month'));
        }
        return ["startDate" => $startDate, "endDate" => $endDate];
    }
}
