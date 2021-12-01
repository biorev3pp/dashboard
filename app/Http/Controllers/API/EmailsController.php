<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\OutreachMailings;

class EmailsController extends Controller
{
    public function index(Request $request){ //dd($request->all());
        $d = $this->__getStartEndDate($request);
        //dd($d);
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
                if (array_key_exists($value["mailboxAddress"], $allEmailReplied)) {                    
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
                    "Replied : " . $allEmailDelivered[$request->input("agentName")],
                    "Not Replied : " . ($allData[$request->input("agentName")]-$allEmailDelivered[$request->input("agentName")])
                ];
                $seriesDelivered = [$allEmailDelivered[$request->input("agentName")], $allData[$request->input("agentName")]-$allEmailDelivered[$request->input("agentName")]];
            }else{
                $labelDelivered = [];
                $seriesDelivered = [];
            }
            if(array_key_exists($request->input("agentName"), $allEmailBounced)){
                $labelBounced = [
                    "Bounced : " . $allEmailBounced[$request->input("agentName")],
                    "Not Bounced : " . ($allData[$request->input("agentName")]-$allEmailBounced[$request->input("agentName")])
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
    public function getEmails(Request $request){
        $recordPerPage  = $request->input("recordPerPage");
        $agentName = $request->input("agentName");
        $dateRange = $request->input("dateRange");
        $type = $request->input("type");

        $d = $this->__getStartEndDate($request);
        $startDate = $d["startDate"];
        $endDate = $d["endDate"];
        if($startDate == $endDate){
            if($type == "All"){
                $records = DB::table("outreach_mailings")->where(function($query){
                    $query->whereDate("deliveredAt", $startDate)->orWhereDate("bouncedAt", $startDate);
                });
            }if($type == "Delivered"){
                $records = DB::table("outreach_mailings")->whereDate("deliveredAt", $startDate);
            }elseif($type == "Bounced"){
                $records = DB::table("outreach_mailings")->whereDate("bouncedAt", $startDate);
            }elseif($type == "Clicked"){
                $records = DB::table("outreach_mailings")->whereDate("clickedAt", $startDate);
            }elseif($type == "Opened"){
                $records = DB::table("outreach_mailings")->whereDate("openedAt", $startDate);
            }elseif($type == "Replied"){
                $records = DB::table("outreach_mailings")->whereDate("repliedAt", $startDate);
            }
        }else{
            if($type == "All"){
                $records = DB::table("outreach_mailings")->where(function($query) use ($startDate,$endDate){
                    $query->whereBetween("deliveredAt", [$startDate, $endDate])->orWhereBetween("bouncedAt", [$startDate, $endDate]);
                });
            }elseif($type == "Delivered"){
                $records = DB::table("outreach_mailings")->whereBetween("deliveredAt", [$startDate, $endDate]);
            }elseif($type == "Bounced"){
                $records = DB::table("outreach_mailings")->whereBetween("bouncedAt", [$startDate, $endDate]);
            }elseif($type == "Clicked"){
                $records = DB::table("outreach_mailings")->whereBetween("clickedAt", [$startDate, $endDate]);
            }elseif($type == "Opened"){
                $records = DB::table("outreach_mailings")->whereBetween("openedAt", [$startDate, $endDate]);
            }elseif($type == "Replied"){
                $records = DB::table("outreach_mailings")->whereBetween("repliedAt", [$startDate, $endDate]);
            }
        }
        if($agentName){
            $records = $records->where("mailboxAddress", "LIKE", $agentName);
        }
        $records = $records->leftJoin('contacts', 'outreach_mailings.contact_id', "=", 'contacts.record_id')->paginate($recordPerPage); 
        return $records;
    }
    private function __getStartEndDate($request){// dd($request->input("dateRange"));
        if($request->input("dateRange")){
            $startDate = $request->input('dateRange.startDate');
            $endDate = $request->input('dateRange.endDate');
            $startTime = date("H:i:s", strtotime($startDate));
            $endTime = date("H:i:s", strtotime($endDate));
            if($startTime == $endTime){
                $startDate = date("Y-m-d", strtotime($startDate));
                $endDate = date("Y-m-d", strtotime($endDate));
            }else{
                $startDate = date("Y-m-d", strtotime($startDate)+5*3600+30*60);
                $endDate = date("Y-m-d", strtotime($endDate)-(5*3600+29*60+59));
            }
        }else{           
            $startDate = date('Y-m-d', strtotime('first day of last month'));
            $endDate  = date('Y-m-d', strtotime('last day of last month'));
        }
        return ["startDate" => $startDate, "endDate" => $endDate];
    }
}
