<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Settings;
use App\Models\FivenineCallLogs;
use App\Models\Contacts;
use App\Models\AgentOccupancy;

class AgentOccupancyController extends Controller
{
    public function index(){

    }
    public function login_time(Request $request){
        $d = $this->__getStartEndDate($request);
        $startDate = $d["startDate"];
        $endDate = $d["endDate"];
        $field = "login_time";
        $query = AgentOccupancy::select(DB::raw("sum(TIME_TO_SEC(`login_time`)) as total_login_time, sum(TIME_TO_SEC(`$field`)) as $field, CONCAT_WS(' ',`agent_first_name`, `agent_last_name`) as full_name"));
        if($startDate == $endDate){
            $query = $query->whereDate("date", $startDate);
            $days = 1;
        }else{
            $query = $query->whereBetween("date", [$startDate, $endDate]);
            $recordDate = AgentOccupancy::whereBetween("date", [$startDate, $endDate])->select("date")->distinct()->get();
            $days = 0;
            foreach($recordDate as $value){
                ++$days;
            }
        }
        $records = $query->groupBy("full_name")->get();
        $label = [];
        $series = [];
        $seriesSec = [];
        $countRecords = count($records);
        $totalLoginSeconds = array_sum(array_column($records->toArray(), "total_login_time"));
        
        $totalSeconds = $days*8*3600*count($records);
        $totalHrs =  $days*8*count($records);
        $totalSecConsumed = 0;
        $agentName = [];
        $login = [];
        $loginTime = [];
        foreach($records as $value){
            $login[] = $value["total_login_time"];
            $label[] = $value["full_name"]." : ".$this->__seconds2human($value[$field]);
            $agentName[] = $value["full_name"];
            $series[] = intval($value[$field]/100);
            $totalSecConsumed += $value[$field];
            $seriesSec[] = $value[$field];
        }
        
        // $remainingTime = $totalSeconds - $totalSecConsumed;
        $countSeries = count($series);        
        return ["label" => $label, "series" => $series, "days" => $days, "seriesSec" => $seriesSec,"agentName" => $agentName, "startDate" => date("m-d-Y", strtotime($startDate)), "endDate" => date("m-d-Y", strtotime($endDate))];
    }
    public function available_time(Request $request){
        $d = $this->__getStartEndDate($request);
        $startDate = $d["startDate"];
        $endDate = $d["endDate"];
        $field = "available_time";
        $query = AgentOccupancy::select(DB::raw("sum(TIME_TO_SEC(`not_ready_time`)) as total_not_ready_time , sum(TIME_TO_SEC(`login_time`)) as total_login_time, sum(TIME_TO_SEC(`$field`)) as $field, CONCAT_WS(' ',`agent_first_name`, `agent_last_name`) as full_name"));
        if($startDate == $endDate){
            $query = $query->whereDate("date", $startDate);
            $days = 1;
        }else{
            $query = $query->whereBetween("date", [$startDate, $endDate]);
            $recordDate = AgentOccupancy::whereBetween("date", [$startDate, $endDate])->select("date")->distinct()->get();
            $days = 0;
            foreach($recordDate as $value){
                ++$days;
            }
        }
        if($request->input("agentName")){
            $name = explode(" ",$request->input("agentName"));
            $records = $query->groupBy("full_name")->where("agent_first_name", "LIKE", $name[0])->get();
        }else{
            $records = $query->groupBy("full_name")->get();
        }
        $labelReady = '';
        $labelNotReady = '';
        $series = [];
        $seriesSec = [];
        $countRecords = count($records);
        $totalSeconds = $days*8*3600*count($records);
        $totalHrs =  $days*8*count($records);
        $totalSecConsumed = 0;
        $agentName = [];
        $login = [];
        $loginTime = [];
        $totalAvaibleSeconds = 0;
        $totalNotReadySeconds = 0;
        foreach($records as $value){
            $totalAvaibleSeconds += $value["available_time"];
            $totalNotReadySeconds += $value["total_not_ready_time"];
            $labelReady .= $value["full_name"]." : ".$this->__seconds2human($value[$field]).", <br>";
            $labelNotReady .= $value["full_name"]." : ".$this->__seconds2human($value["total_not_ready_time"]).", <br>";
        }
        
        $countSeries = count($series);
        if($request->input("agentName")){
            return ["label" => [
                "Ready : ".$this->__seconds2human($totalAvaibleSeconds), 
                "Not Ready : ".$this->__seconds2human($totalNotReadySeconds)], "series" => [$totalAvaibleSeconds, $totalNotReadySeconds], "days" => $days, "countRecords" => $countRecords, "countSeries" => $countSeries, "totalHrs" => $totalHrs, "totalSecConsumed" => $totalSecConsumed, "agentName" => $agentName, "login" => $login, "totalSeconds" => $totalSeconds, "startDate" => $startDate, "endDate" => $endDate];
        }else{
            return ["label" => [
                "Ready : ".$this->__seconds2human($totalAvaibleSeconds).", <br>".$labelReady, 
                "Not Ready : ".$this->__seconds2human($totalNotReadySeconds).", <br>".$labelNotReady], "series" => [$totalAvaibleSeconds, $totalNotReadySeconds], "days" => $days, "countRecords" => $countRecords, "countSeries" => $countSeries, "totalHrs" => $totalHrs, "totalSecConsumed" => $totalSecConsumed, "agentName" => $agentName, "login" => $login, "totalSeconds" => $totalSeconds, "startDate" => $startDate, "endDate" => $endDate];
        }
    }
    public function on_call_time(Request $request){
        $d = $this->__getStartEndDate($request);
        $startDate = $d["startDate"];
        $endDate = $d["endDate"];
        $field = "on_call_time";
        $query = AgentOccupancy::select(DB::raw("sum(TIME_TO_SEC(`wait_time`)) as total_wait_time, sum(TIME_TO_SEC(`on_acw_time`)) as total_on_acw_time, sum(TIME_TO_SEC(`$field`)) as $field, CONCAT_WS(' ',`agent_first_name`, `agent_last_name`) as full_name"));
        if($startDate == $endDate){
            $query = $query->whereDate("date", $startDate);
            $days = 1;
        }else{
            $query = $query->whereBetween("date", [$startDate, $endDate]);
            $recordDate = AgentOccupancy::whereBetween("date", [$startDate, $endDate])->select("date")->distinct()->get();
            $days = 0;
            foreach($recordDate as $value){
                ++$days;
            }
        }
        if($request->input("agentName")){
            $name = explode(" ",$request->input("agentName"));
            $records = $query->groupBy("full_name")->where("agent_first_name", "LIKE", $name[0])->get();
        }else{
            $records = $query->groupBy("full_name")->get();
        }
        $labelCall = '';
        $labelWait = '';
        $labelAcw = '';
        $totalOnCallTime = 0;
        $totalOnAcwTime = 0;
        $totalOnWaitTime = 0;
        foreach($records as $value){
            $totalOnCallTime += $value[$field];
            $totalOnAcwTime += $value["total_on_acw_time"];
            if($request->input("agentName")){
                $totalOnWaitTime = intval($value["total_wait_time"]);
                $labelCall = $this->__seconds2human($value[$field]);
                $labelWait = $this->__seconds2human($value["total_wait_time"]);
                $labelAcw = $this->__seconds2human($value["total_on_acw_time"]);
            }else{
                $labelCall .= $value["full_name"]." : ".$this->__seconds2human($value[$field]).", <br>";
                $labelAcw .= $value["full_name"]." : ".$this->__seconds2human($value["total_on_acw_time"]).", <br>";
            }
        }
        if($request->input("agentName")){
            return [
                "label" => [
                    "On Call : ".$labelCall,
                    "Wait Time : " . $labelWait,
                    "ACW : ".$labelAcw
                ], 
                "series" => [$totalOnCallTime, $totalOnWaitTime, $totalOnAcwTime]
            ];
        }else{
            return [
                "label" => [
                    "On Call : ".$this->__seconds2human($totalOnCallTime).", <br>".$labelCall,
                    "ACW : ".$this->__seconds2human($totalOnAcwTime).", <br>".$labelAcw
                ], 
                "series" => [$totalOnCallTime, $totalOnAcwTime]
            ];
        }
    }
    public function getAgentBasedTalkTime(Request $request){
        $d = $this->__getStartEndDate($request);
        $startDate = $d["startDate"];
        $endDate = $d["endDate"];
        $stime = strtotime($startDate);
        $etime = strtotime($endDate)+24*3600-1;
        if($request->input("agentName") || $request->input("disposition")){
            return $this->__getAgentAndDispositionBasedTalkTime($request->input("only_agent"), $request->input("agentName"), $request->input("disposition"), $stime, $etime, $request->input("acw"), $request->input("acw_plus"));
        }
        $agents = DB::table('fivenine_call_logs')->select('agent_name')->distinct()->get();
        $agentsName = [];
        foreach($agents as $value){
            $value = get_object_vars($value);
            if($value["agent_name"] != ''){
                $agentsName[$value["agent_name"]] = 0;
            }
        }
        $records = DB::table('fivenine_call_logs');
        $record = $records->whereBetween("n_timestamp", [$stime, $etime])->whereNotNull("agent_name")->get();
        $agentCallCount = [];
        foreach($agentsName as $key => $value){
            if(DB::table('fivenine_call_logs')->whereBetween("n_timestamp", [$stime, $etime])->where("agent_name", "LIKE", $key)->count() > 0){
                $agentCallCount[] = [
                    "name" => $key,
                    "total" => DB::table('fivenine_call_logs')->whereBetween("n_timestamp", [$stime, $etime])->where("agent_name", "LIKE", $key)->count()
                ];
            }
        }
        $totalCall = $record->count();
        $data = [];
        foreach($record as $value){ 
            $value = get_object_vars($value);
            $talk_time = $value["talk_time"];
            $time = explode(":", $talk_time);
            $seconds = 0;
            if(intval($time[0]) > 0){ // hours
                $seconds = $seconds+intval($time[0])*3600;
            }
            if(intval($time[1]) > 0){ //minute
                $seconds = $seconds+intval($time[1])*60;
            }
            if(intval($time[2]) > 0){ // seconds
                $seconds = $seconds+intval($time[2]);
            }
            if(array_key_exists($value["agent_name"], $agentsName)){
                $agentsName[$value["agent_name"]] += $seconds;
            }
        }

        $label = [];
        $series = [];
        $nagentName = [];
        foreach($agentsName as $key => $value){
            if($value > 0){
                $total =  $value;
                $hrs = 0;
                if($value >= 3600){
                    $hrs = intval($value/3600);
                    $value = $value - $hrs*3600;
                }
                $min = 0;
                if($value >= 60){
                    $min = intval($value/60);
                    $value = $value - $min*60;
                }
                $seconds = $value;
                $time = "";
                
                if($hrs < 10){
                    $time = "0".$hrs;
                }else{
                    $time = $hrs;
                }
                
                if($min < 10){
                    $time = $time.":0".$min;
                }else{
                    $time = $time.":".$min;
                }

                if($seconds < 10){
                    $time = $time.":0".$seconds;
                }else{
                    $time = $time.":".$seconds;
                }
                $series[] = $total;
                $label[] = $key." : ".$time;
                $nagentName[] = $key;
            }
        }
        return ["label" => $label, "series" => $series,
        "agentCallCount" => [
            "totalCall" => $totalCall,
            "records" => $agentCallCount
        ]];
    } 
    public function productivity_time(Request $request){
        $d = $this->__getStartEndDate($request);
        $startDate = $d["startDate"];
        $endDate = $d["endDate"];
        $field = "login_time";
        $query = AgentOccupancy::select(DB::raw("sum(TIME_TO_SEC(`login_time`)) as total_login_time, CONCAT_WS(' ',`agent_first_name`, `agent_last_name`) as full_name"));
        if($startDate == $endDate){
            $query = $query->whereDate("date", $startDate);
            $days = 1;
        }else{
            $query = $query->whereBetween("date", [$startDate, $endDate]);
            $recordDate = AgentOccupancy::whereBetween("date", [$startDate, $endDate])->select("date")->distinct()->get();
            $days = 0;
            foreach($recordDate as $value){
                ++$days;
            }
        }
        if($request->input("agentName")){
            $name = explode(" ",$request->input("agentName"));
            $records = $query->groupBy("full_name")->where("agent_first_name", "LIKE", $name[0])->get();
        }else{
            $records = $query->groupBy("full_name")->get();
        }
        $label = '';
        $labelNot = '';
        $countRecords = count($records);
        $totalLoginSeconds = 0;
        $totalSeconds = $days*8*3600*count($records);
        foreach($records as $value){
            $totalLoginSeconds += $value["total_login_time"];
            if($request->input("agentName")){
                $label = $this->__seconds2human($value["total_login_time"]);
                $labelNot = $this->__seconds2human($days*8*3600-$value["total_login_time"]);
            }else{
                $label .= $value["full_name"]." : ".$this->__seconds2human($value["total_login_time"]).", <br>";
                $labelNot .= $value["full_name"]." : ".$this->__seconds2human($days*8*3600-$value["total_login_time"]).", <br>";
            }
        }
        
        if($request->input("agentName")){
            return [
                "label" => [
                    "Five9 Time : ".$label,
                    "Offline Task : ".$labelNot,
                ], 
                "series" => [
                    $totalLoginSeconds,
                    $totalSeconds-$totalLoginSeconds
                ], 
                "days" => $days, 
                "countRecords" => $countRecords
            ];
        }else{
            return [
                "label" => [
                    "Five9 Time : ".$this->__seconds2human($totalLoginSeconds).", <br>".$label,
                    "Offline Task : ".$this->__seconds2human($totalSeconds-$totalLoginSeconds).", <br>".$labelNot,
                ], 
                "series" => [
                    $totalLoginSeconds,
                    $totalSeconds-$totalLoginSeconds
                ], 
                "days" => $days, 
                "countRecords" => $countRecords
            ];            
        }
    }
    private function __getAgentAndDispositionBasedTalkTime($only_agent, $agentName, $disposition, $stime, $etime, $acw, $acw_plus){
        $records = DB::table("fivenine_call_logs");
        $records = $records->where("agent_name", "=", $agentName)->whereBetween("n_timestamp", [$stime, $etime]);
        $disposition = DB::table("fivenine_call_logs")->select(DB::raw(" count(*) as total, disposition as name"))->where("agent_name", "=", $agentName)->whereBetween("n_timestamp", [$stime, $etime])->groupBy("disposition")->get();
        
        if($acw_plus == 1){
            $records
            ->whereNotNull('after_call_work_time')
            ->whereRaw('(MINUTE(`after_call_work_time`) > 3')
            ->orWhere(function($query){
                $query->whereRaw('MINUTE(`after_call_work_time`) = 3')->whereRaw('SECOND(`after_call_work_time`) > 0)');
            });
        }elseif($acw_plus == 0 && $acw){
            $records
            ->whereNotNull('after_call_work_time')
            ->where(function($query) use ($acw){
                $query->whereRaw('(MINUTE(`after_call_work_time`) = ?', [$acw])->whereRaw('SECOND(`after_call_work_time`) = 0');
            })            
            ->orWhere(function($query) use ($acw){
                $query->whereRaw('MINUTE(`after_call_work_time`) = ?', [$acw-1])->whereRaw('SECOND(`after_call_work_time`) > 0)');
            });
        }
        $records = $records->get();
        $totalCall = $records->count();
        $talkTime = [
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0
        ];
        $max = 0;
        foreach ($records as $key => $value) {
            $value = get_object_vars($value);
            $talk_time = $value["talk_time"];
            $time = explode(":", $talk_time);
            $seconds = 0;
            if(intval($time[0]) > 0){ // hours
                $seconds = $seconds+intval($time[0])*3600;
            }
            if(intval($time[1]) > 0){ //minute
                $seconds = $seconds+intval($time[1])*60;
            }
            if(intval($time[2]) > 0){ // seconds
                $seconds = $seconds+intval($time[2]);
            }
            if($max <= $seconds){
                $max = $seconds;
            }
            if($seconds > 20){
                $talkTime[4] += 1;
            }elseif($seconds > 10){
                $talkTime[3] += 1;
            }elseif($seconds > 5){
                $talkTime[2] += 1;
            }else{
                $talkTime[1] += 1;
            }
        }
        return ["label" => ["0-5sec : ".$talkTime[1], "6-10sec : ".$talkTime[2], "11-20sec : ".$talkTime[3], "20+sec : ".$talkTime[4]], "series" => [$talkTime[1], $talkTime[2], $talkTime[3], $talkTime[4]], "stime" => $stime, "etime" => $etime, "sdate" => date("Y-m-d", $stime), "edate" => date("Y-m-d", $etime), "talk_time" => [5, 10, 20,'20+'], "totalCall" => $totalCall, "agentCallCount" => [
            "totalCall" => $totalCall,
            "records" => $disposition
        ]];
    }
    private function __getTimeInStrtottime($dateRange){
        if($dateRange){
            $s = explode("T", $dateRange["startDate"]); 
            $e = explode("T", $dateRange["endDate"]);
            $startDate = '';
            $endDate = '';
            $startFullDate = '';
            $endFullDate = '';
            if($s[1] == $e[1]):
                //date coming form calender date selection
                if($s[0] == $e[0]):
                    // single date selected
                    $startDate = $s[0];
                    $endDate = $e[0];
                    $startFullDate = $startDate.'T00:00:00';
                    $endFulllDate = $endDate.'T23:59:59';
                else:
                    $startDate = date("Y-m-d", strtotime($s[0]) - 24*3600);
                    $endDate = $e[0];
                    $startFullDate = $startDate.'T23:59:59';
                    $endFulllDate = $endDate.'T23:59:59';
                endif;
            else:
                //date coming from calender date-tab selection
                if(strtotime($e[0]) - strtotime($s[0]) == 24*3600):
                    $endDate = $startDate = $e[0];
                    $startFullDate = $startDate.'T00:00:00';
                    $endFulllDate = $endDate.'T23:59:59';
                else:
                    $startDate = $s[0];
                    $endDate = $e[0];
                    $startFullDate = $startDate.'T23:59:59';
                    $endFulllDate = $endDate.'T23:59:59';
                endif;
            endif;
        }else{
            if(date("j", strtotime("now")) == 1){
                //select last month
                $startFullDate = date('Y-m-d', strtotime('first day of last month'))." 00:00:00";
                $endFulllDate  = date('Y-m-d', strtotime('last day of last month'))." 23:59:29";
            }else{
                //select current month
                $startFullDate = date("Y-m-", strtotime("now"))."01 00:00:00";
                $numberOfDaysInCurrentMonth = date("t", strtotime("now"));
                $numberOfDaysInCurrentMonth = ($numberOfDaysInCurrentMonth < 10) ? "0".$numberOfDaysInCurrentMonth : $numberOfDaysInCurrentMonth;
                $endFulllDate = date("Y-m-", strtotime("now")).$numberOfDaysInCurrentMonth." 23:59:59";
            }
        }
        return ["stime" => strtotime($startFullDate), "etime" => strtotime($endFulllDate)];
    }
    private function __getStartEndDate($request){
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
            $record = AgentOccupancy::whereRaw("TIME_TO_SEC(`login_time`) > 3600")->orderBy("date", "desc")->first();
            $startDate = $record["date"];
            $endDate =  $record["date"];
            // $startDate = date('Y-m-d', strtotime('first day of last month'));
            // $endDate  = date('Y-m-d', strtotime('last day of last month'));
        }
        return ["startDate" => $startDate, "endDate" => $endDate];
    }
    private function __get_field_data($field, $request){
        $d = $this->__getStartEndDate($request);        
        $startDate = $d["startDate"];
        $endDate = $d["endDate"];
        $query = AgentOccupancy::select(DB::raw("sum(TIME_TO_SEC(`login_time`)) as total_login_time, sum(TIME_TO_SEC(`$field`)) as $field, CONCAT_WS(' ',`agent_first_name`, `agent_last_name`) as full_name"));
        if($startDate == $endDate){
            $query = $query->whereDate("date", $startDate);
            $days = 1;
        }else{
            $query = $query->whereBetween("date", [$startDate, $endDate]);
            $recordDate = AgentOccupancy::whereBetween("date", [$startDate, $endDate])->select("date")->distinct()->get();
            $days = 0;
            foreach($recordDate as $value){
                ++$days;
            }
        }
        if($request->input("agentName") && ($field != 'login_time')){
            $name = explode(" ",$request->input("agentName"));
            $records = $query->groupBy("full_name")->where("agent_first_name", "LIKE", $name[0])->get();
        }else{
            $records = $query->groupBy("full_name")->get();
        }
        $label = [];
        $series = [];
        $seriesSec = [];
        $countRecords = count($records);
        $totalLoginSeconds = array_sum(array_column($records->toArray(), "total_login_time"));
        
        $totalSeconds = $days*8*3600*count($records);
        $totalHrs =  $days*8*count($records);
        $totalSecConsumed = 0;
        $agentName = [];
        $login = [];
        $loginTime = [];
        foreach($records as $value){
            $login[] = $value["total_login_time"];
            $label[] = $value["full_name"]." : ".$this->__seconds2human($value[$field]);
            $agentName[] = $value["full_name"];
            $series[] = intval($value[$field]/100);
            $totalSecConsumed += $value[$field];
            $seriesSec[] = $value[$field];
        }
        
        // $remainingTime = $totalSeconds - $totalSecConsumed;
        $countSeries = count($series);
        if($request->input("agentName")){
            if($request->input("time_limit")){
                $remainingTime = $totalSeconds - $totalSecConsumed;
                $label[] = "Extra : ".$this->__seconds2human($remainingTime);
                $series[] = intval($remainingTime/100);
                $seriesSec[] = $remainingTime;
            }else{
                $remainingTime = $totalLoginSeconds - $totalSecConsumed;
                $label[] = "Extra : ".$this->__seconds2human($remainingTime);
                $series[] = intval($remainingTime/100);
                $seriesSec[] = $remainingTime;
            }
        }else{
            if($request->input("time_limit")){
                $remainingTime = $totalSeconds - $totalSecConsumed;
                $label[] = "Extra : ".$this->__seconds2human($remainingTime);
                $series[] = intval($remainingTime/100);
                $seriesSec[] = $remainingTime;
            }else{
                // $remainingTime = $totalLoginSeconds - $totalSecConsumed;
                // $label[] = "Extra : ".$this->__seconds2human($remainingTime);
                // $series[] = intval($remainingTime/100);
                // $seriesSec[] = $remainingTime;
            }
        }
        return ["label" => $label, "series" => $series, "days" => $days, "seriesSec" => $seriesSec, "countRecords" => $countRecords, "countSeries" => $countSeries, "totalHrs" => $totalHrs, "totalSecConsumed" => $totalSecConsumed, "agentName" => $agentName, "login" => $login, "totalSeconds" => $totalSeconds];
    }
    private function __date_diff($startDate, $endDate) {
        $startDate = strtotime($startDate);
        $endDate = strtotime($endDate);
        $diff = $endDate - $startDate;
        return intval($diff / (60 * 60 * 24))+1;
    }
    private function __seconds2human($seconds) {
        $seconds = intval($seconds);
        $h = 0;
        $m = 0;
        $s = 0;
        if($seconds > 3600){
            $h = intval($seconds/3600);
            $seconds = $seconds-$h*3600;
        }
        if($seconds > 60){
            $m = intval($seconds/60);
            $seconds = $seconds-$m*60;
        }
        ($h < 10) ? $h = '0'.$h : $h;
        ($m < 10) ? $m = '0'.$m : $m;
        ($seconds < 10) ? $seconds = '0'.$seconds : $seconds;
        return "$h : $m : $seconds";
    }
}
