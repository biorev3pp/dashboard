<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Settings;
use App\Models\FivenineCallLogs;
use App\Models\Contacts;
use App\Models\AgentOccupancy;

class DashboardCallController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index(){
    }
    public function getAgentCallStatus(Request $request){
        $gt = $this->__getTimeInStrtottime($request->input("dateRange"));
        $stime = $gt["stime"];
        $etime = $gt["etime"];
        $records = FivenineCallLogs::select(DB::raw("count(fivenine_call_logs.id) as count, fivenine_call_logs.agent_name"))
        ->groupBy("fivenine_call_logs.agent_name")
        ->whereBetween("n_timestamp", [$stime, $etime]);
        if($request->input('list_name')){
            $list_name = $request->input('list_name');
            $records = $records->where('list_name', 'LIKE', $list_name);
        }
        $records = $records->get();
        $label = [];
        $series = [];
        $agentName = [];
        foreach($records as $value){
            if($request->input("only_agent")){
                if($value["agent_name"] != ""){
                    $agentName[] = $value["agent_name"];
                    $label[] = $value["agent_name"]." : ".$value["count"];
                    $series[] = $value["count"];
                }
            }else{
                if($value["agent_name"] == ""){
                    $agentName[] = "None";
                    $label[] = "None"." : ".$value["count"];
                    $series[] = $value["count"];
                }else{
                    $agentName[] = $value["agent_name"];
                    $label[] = $value["agent_name"]." : ".$value["count"];
                    $series[] = $value["count"];
                }
            }
        }
        return ["label" => $label, "series" => $series, "agentName" => $agentName, "stime" => $stime, "etime" => $etime, "sdate" => date("Y-m-d", $stime), "edate" => date("Y-m-d", $etime)];
    }    
    public function getAgentDispositionStatus(Request $request){
        //current month 
        $gt = $this->__getTimeInStrtottime($request->input("dateRange"));
        $stime = $gt["stime"];
        $etime = $gt["etime"];
        
        if(($request->input("talk_time") || $request->input("acw")) && is_null($request->input("disposition")) ){
            return $this->__getTalkTimeBasedDisposition($request->input("only_agent"), $request->input("agentName"), $request->input("disposition"), $stime, $etime, $request->input("talk_time"), $request->input("talk_time_plus"), $request->input("acw"), $request->input("acw_plus"), $request->input("list_name"));
        }
        $records = FivenineCallLogs::select(DB::raw("count(fivenine_call_logs.id) as count, fivenine_call_logs.disposition"))
        ->groupBy("fivenine_call_logs.disposition");
        if($request->input("only_agent")){
            if($request->input("agentName")){
                $agentName = $request->input("agentName");
                $records = $records->where("agent_name", "=", $agentName);
            }else{
                $records = $records->whereNotNull("agent_name");
            }
        }elseif($request->input("agentName")){
            if($request->input("agentName") == "None"){
                $agentName = null;
                $records = $records->whereNull("agent_name");
            }else{
                $agentName = $request->input("agentName");
                $records = $records->where("agent_name", "=", $agentName);
            }
        }
        if($request->input('list_name')){
            $list_name = $request->input('list_name');
            $records = $records->where('list_name', 'LIKE', $list_name);
        }
        $records = $records->whereBetween("n_timestamp", [$stime, $etime])->get();
        $label = [];
        $series = [];
        $dispositins = [];
        foreach($records as $value){
            if($value["disposition"] != ""){
                $dispositins[]= $value["disposition"];
                $label[] = $value["disposition"]." : ".$value["count"];
                $series[] = $value["count"];
            }
        }
        return ["label" => $label, "series" => $series, "dispositins" => $dispositins, "stime" => $stime, "etime" => $etime, "sdate" => date("Y-m-d", $stime), "edate" => date("Y-m-d", $etime)];
    }
    public function getAgentListStatus(Request $request){
        //current month
        $gt = $this->__getTimeInStrtottime($request->input("dateRange"));
        $stime = $gt["stime"];
        $etime = $gt["etime"];
        
        if(($request->input("talk_time") || $request->input("acw")) && is_null($request->input("disposition")) ){
            return $this->__getTalkTimeBasedList($request->input("only_agent"), $request->input("agentName"), $request->input("disposition"), $stime, $etime, $request->input("talk_time"), $request->input("talk_time_plus"), $request->input("acw"), $request->input("acw_plus"));
        }
        $records = FivenineCallLogs::select(DB::raw("count(fivenine_call_logs.id) as count, fivenine_call_logs.list_name"))
        ->groupBy("fivenine_call_logs.list_name");
        if($request->input("only_agent")){
            if($request->input("agentName")){
                $agentName = $request->input("agentName");
                $records = $records->where("agent_name", "=", $agentName);
            }else{
                $records = $records->whereNotNull("agent_name");
            }
        }elseif($request->input("agentName")){
            if($request->input("agentName") == "None"){
                $agentName = null;
                $records = $records->whereNull("agent_name");
            }else{
                $agentName = $request->input("agentName");
                $records = $records->where("agent_name", "=", $agentName);
            }
        }
        $records = $records->whereBetween("n_timestamp", [$stime, $etime])->get();
        $label = [];
        $series = [];
        $list_names = [];
        foreach($records as $value){
            if($request->input('only_list')){
                if($value["list_name"] != ""){
                    $list_names[]= $value["list_name"];
                    $label[] = substr($value["list_name"],0,10)." : ".$value["count"];
                    $series[] = $value["count"];
                }
            }else{
                if($value["list_name"] != ""){
                    $list_names[]= $value["list_name"];
                    $label[] = substr($value["list_name"],0,10)." : ".$value["count"];
                    $series[] = $value["count"];
                }else{
                    $list_names[]= 'Manual';
                    $label[] = "Manual : ".$value["count"];
                    $series[] = $value["count"];
                }
            }
        }
        return ["label" => $label, "series" => $series, "list_names" => $list_names, "stime" => $stime, "etime" => $etime, "sdate" => date("Y-m-d", $stime), "edate" => date("Y-m-d", $etime)];
    }
    private function __getTalkTimeBasedDisposition($only_agent, $agentName, $disposition, $stime, $etime, $talk_time, $talk_time_plus, $acw, $acw_plus, $list_name){ 
        $records = DB::table('fivenine_call_logs')->whereBetween("n_timestamp", [$stime, $etime]);
        if($list_name){
            $records = $records->where('list_name', 'LIKE', $list_name);
        }
        if($only_agent){
            if($agentName){
                if($agentName == "None"){
                    $records = $records->whereNull("agent_name");
                }else{
                    $records = $records->where("agent_name", "=", $agentName);
                }
            }else{
                $records = $records->whereNotNull("agent_name");
            }
        }else{
            if($agentName){
                if($agentName == "None"){
                    $records = $records->whereNull("agent_name");
                }else{
                    $records = $records->where("agent_name", "=", $agentName);
                }
            }
        }
        if($talk_time_plus == 1){
            $records
            ->where(function($query){
                $query
                ->whereRaw('((MINUTE(`talk_time`) = 0')
                ->whereRaw('SECOND(`talk_time`) > 20)');
            })
            ->orWhere(function($query){
                $query->whereRaw('MINUTE(`talk_time`) > 0)');
            });
        }elseif($talk_time > 0){
            if($talk_time == 5){
                $records
                ->whereRaw('MINUTE(`talk_time`) = 0')
                ->whereRaw('SECOND(`talk_time`) <= 5');
            }elseif($talk_time == 10){
                $records
                ->whereRaw('MINUTE(`talk_time`) = 0')
                ->whereRaw('SECOND(`talk_time`) > 5')
                ->whereRaw('SECOND(`talk_time`) <= 10');
            }elseif($talk_time == 20){
                $records
                ->whereRaw('MINUTE(`talk_time`) = 0')
                ->whereRaw('SECOND(`talk_time`) > 10')
                ->whereRaw('SECOND(`talk_time`) <= 20');
            }
        }
        $acw = $acw;
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
               
        // dd($nrecords);
        $label = [];
        $series = [];
        $dispositions = [];
        $ndispo = [];
        foreach($records as $value){
            $value = get_object_vars($value);
            if($value["disposition"] != ''){
                if(array_key_exists($value["disposition"], $dispositions)){
                    $dispositions[$value["disposition"]] += 1;
                }else{
                    $dispositions[$value["disposition"]] = 1;
                }
            }
        }
        foreach ($dispositions as $key => $value) {
            $label[] = $key." : ".$value;
            $series[] = $value;
            $ndispo[] = $key;
        }
        return ["label" => $label, "series" => $series, "dispositins" => $ndispo, "stime" => $stime, "etime" => $etime, "sdate" => date("Y-m-d", $stime), "edate" => date("Y-m-d", $etime)];
        //return [];
    }
    private function __getTalkTimeBasedList($only_agent, $agentName, $disposition, $stime, $etime, $talk_time, $talk_time_plus, $acw, $acw_plus){ 
        $records = DB::table('fivenine_call_logs')->whereBetween("n_timestamp", [$stime, $etime]);
        if($only_agent){
            if($agentName){
                if($agentName == "None"){
                    $records = $records->whereNull("agent_name");
                }else{
                    $records = $records->where("agent_name", "=", $agentName);
                }
            }else{
                $records = $records->whereNotNull("agent_name");
            }
        }else{
            if($agentName){
                if($agentName == "None"){
                    $records = $records->whereNull("agent_name");
                }else{
                    $records = $records->where("agent_name", "=", $agentName);
                }
            }
        }
        if($talk_time_plus == 1){
            $records
            ->where(function($query){
                $query
                ->whereRaw('((MINUTE(`talk_time`) = 0')
                ->whereRaw('SECOND(`talk_time`) > 20)');
            })
            ->orWhere(function($query){
                $query->whereRaw('MINUTE(`talk_time`) > 0)');
            });
        }elseif($talk_time > 0){
            if($talk_time == 5){
                $records
                ->whereRaw('MINUTE(`talk_time`) = 0')
                ->whereRaw('SECOND(`talk_time`) <= 5');
            }elseif($talk_time == 10){
                $records
                ->whereRaw('MINUTE(`talk_time`) = 0')
                ->whereRaw('SECOND(`talk_time`) > 5')
                ->whereRaw('SECOND(`talk_time`) <= 10');
            }elseif($talk_time == 20){
                $records
                ->whereRaw('MINUTE(`talk_time`) = 0')
                ->whereRaw('SECOND(`talk_time`) > 10')
                ->whereRaw('SECOND(`talk_time`) <= 20');
            }
        }
        $acw = $acw;
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
               
        // dd($nrecords);
        $label = [];
        $series = [];
        $list_names = [];
        $ndispo = [];
        foreach($records as $value){
            $value = get_object_vars($value);
            if($value["list_name"] != ''){
                if(array_key_exists($value["list_name"], $list_names)){
                    $list_names[$value["list_name"]] += 1;
                }else{
                    $list_names[$value["list_name"]] = 1;
                }
            }else{

            }
        }
        foreach ($list_names as $key => $value) {
            $label[] = $key." : ".$value;
            $series[] = $value;
            $ndispo[] = $key;
        }
        return ["label" => $label, "series" => $series, "list_name" => $ndispo, "stime" => $stime, "etime" => $etime, "sdate" => date("Y-m-d", $stime), "edate" => date("Y-m-d", $etime)];
        //return [];
    }
    public function getAgentBasedTalkTime(Request $request){
        $gt = $this->__getTimeInStrtottime($request->input("dateRange"));
        $stime = $gt["stime"];
        $etime = $gt["etime"];
        if($request->input("agentName") || $request->input("disposition")){
            return $this->__getAgentAndDispositionBasedTalkTime($request->input("only_agent"), $request->input("agentName"), $request->input("disposition"), $stime, $etime, $request->input("acw"), $request->input("acw_plus"), $request->input("list_name"), $request->input("dispoMul"));
        }
        $agents = DB::table('fivenine_call_logs')->select('agent_name')->distinct()->get();
        $agentsName = [];
        foreach($agents as $value){
            $value = get_object_vars($value);
            if($request->input("only_agent")){
                if($value["agent_name"] != "None"){
                    $agentsName[$value["agent_name"]] = 0;
                }
            }else{
                $agentsName[$value["agent_name"]] = 0;
            }
        }
        $records = DB::table('fivenine_call_logs');
        $dispositions = [];
        if($request->input("dispoMul")){
            $dispositions = $request->input("dispoMul");
        }
        if($request->input("disposition")){
            if(count($dispositions) > 0){
                if(!in_array($request->input("disposition"), $dispositions)){
                    $dispositions[count($dispositions)] = $request->input("disposition");
                }
                $records = $records->whereIn("disposition", $dispositions);
            }else{
                $records = $records->where("disposition", "=", $request->input("disposition"));
            }
        }else{
            if(count($dispositions) > 0){
                $records = $records->whereIn("disposition", $dispositions);
            }
        }
        if($request->input("list_name")){
            $records = $records->where("list_name", "=", $request->input("list_name"));
        }
        $record = $records->whereBetween("n_timestamp", [$stime, $etime])->get();
        $data = [];
        foreach($record as $value){
            $value = get_object_vars($value);
            if($value["agent_name"] != ""){
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
        return ["label" => $label, "series" => $series, "agentsName" => $nagentName, "stime" => $stime, "etime" => $etime, "sdate" => date("Y-m-d", $stime), "edate" => date("Y-m-d", $etime)];
    }    
    public function getAgentBasedACW(Request $request){
        $gt = $this->__getTimeInStrtottime($request->input("dateRange"));
        $stime = $gt["stime"];
        $etime = $gt["etime"];
        if($request->input("agentName") || $request->input("disposition")){
            return $this->__getAgentAndDispositionBasedACW($request->input("only_agent"), $request->input("agentName"), $request->input("disposition"), $stime, $etime, $request->input("talk_time"), $request->input("talk_time_plus"), $request->input("list_name"), $request->input("dispoMul"));
        }
        $agents = DB::table('fivenine_call_logs')->select('agent_name')->distinct()->get();
        $agentsName = [];
        foreach($agents as $value){
            $value = get_object_vars($value);
            if($request->input("only_agent")){
                if($value["agent_name"] != "None"){
                   $agentsName[$value["agent_name"]] = 0;
                }
            }else{
                $agentsName[$value["agent_name"]] = 0;
            }
        }
        $records = DB::table('fivenine_call_logs');
        if($request->input("list_name")){
            $records = $records->where("list_name", "=", $request->input("list_name"));
        }
        $dispositions = [];
        if($request->input("dispoMul")){
            $dispositions = $request->input("dispoMul");
        }
        if($request->input("disposition")){
            if(count($dispositions) > 0){
                if(!in_array($request->input("disposition"), $dispositions)){
                    $dispositions[count($dispositions)] = $request->input("disposition");
                }
                $records = $records->whereIn("disposition", $dispositions);
            }else{
                $records = $records->where("disposition", "=", $request->input("disposition"));
            }
        }else{
            if(count($dispositions) > 0){
                $records = $records->whereIn("disposition", $dispositions);
            }
        }
        $records = $records->whereBetween("n_timestamp", [$stime, $etime])->get();
        $data = [];
        foreach($records as $value){
            $value = get_object_vars($value);
            if($value["after_call_work_time"]){
                $after_call_work_time = $value["after_call_work_time"];
                $time = explode(":", $after_call_work_time);
                if( ($value["agent_name"] != "") && (intval($time[0]) > 0 || intval($time[1]) > 0 || intval($time[2]) > 0) ){
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
            }
        }

        $label = [];
        $series = [];
        $nagentName = [];
        foreach($agentsName as $key => $value){
            if($value > 0){
                $total = $value;
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
        return ["label" => $label, "series" => $series, "agentsName" => $nagentName, "stime" => $stime, "etime" => $etime, "sdate" => date("Y-m-d", $stime), "edate" => date("Y-m-d", $etime), 'dispositions' => $dispositions];
    }
    public function getAgentBasedWaitingTime(Request $request){
        $gt = $this->__getTimeInStrtottime($request->input("dateRange"));
        $stime = $gt["stime"];
        $etime = $gt["etime"];
        $agents = DB::table('fivenine_call_logs')->select('agent_name')->distinct()->get();
        $agentsName = [];
        foreach($agents as $value){
            $value = get_object_vars($value);
            if($request->input("only_agent")){
                if($value["agent_name"] != "None"){
                    $agentsName[$value["agent_name"]] = 0;
                }
            }else{
                $agentsName[$value["agent_name"]] = 0;
            }
        }
        $records = DB::table('fivenine_call_logs');
        if($request->input("disposition")){
            $records = $records->where("disposition", "=", $request->input("disposition"));
        }
        $records = $records->whereBetween("n_timestamp", [$stime, $etime])->get();
        $data = [];
        foreach($records as $value){
            $value = get_object_vars($value);
            if($value["queue_wait_time"]){
                $queue_wait_time = $value["queue_wait_time"];
                $time = explode(":", $queue_wait_time);
                if( ($value["agent_name"] != "") && (intval($time[0]) > 0 || intval($time[1]) > 0 || intval($time[2]) > 0) ){
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
            }
        }

        $label = [];
        $series = [];
        foreach($agentsName as $key => $value){
            if($value > 0){
                $total = $value;
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
            }
        }
        return ["label" => $label, "series" => $series, "agentsName" => $agentsName, "stime" => $stime, "etime" => $etime, "sdate" => date("Y-m-d", $stime), "edate" => date("Y-m-d", $etime)];
    }
    public function getAgentInfo(Request $request){
        $gt = $this->__getTimeInStrtottime($request->input("dateRange"));
        $stime = $gt["stime"];
        $etime = $gt["etime"];
        if($request->input("agentName") == "None"){
            $agentName = null;
        }else{
            $agentName = $request->input("agentName");
        }
        $records = FivenineCallLogs::where("agent_name", "=", $agentName)
        ->whereBetween("n_timestamp", [$stime, $etime])
        ->get();
        $talkTime = 0;
        $afterCallWorkTime = 0;
        $waitingTime = 0;
        foreach($records as $value){
            $tt = $value["talk_time"];
            $acwt = $value["after_call_work_time"];
            $wt = $value["queue_wait_time"];
            if($tt){
                $talkTime += $this->__getSeconds($tt);
            }
            if($acwt){
                $afterCallWorkTime += $this->__getSeconds($acwt);
            }
            if($wt){
                $waitingTime += $this->__getSeconds($wt);
            }
        }
        $label = ["Talk Time : ".$this->__getTime($talkTime), "After Call Work : ".$this->__getTime($afterCallWorkTime), "Waiting Time : ".$this->__getTime($waitingTime)];
        $series = [$talkTime, $afterCallWorkTime, $waitingTime];
        return ["label" => $label, "series" => $series, "stime" => $stime, "etime" => $etime, "sdate" => date("Y-m-d", $stime), "edate" => date("Y-m-d", $etime)];
    }
    function getAgentOccupancyData(Request $request){
        $gt = $this->__getTimeInStrtottime($request->input("dateRange"));
        $startDate = date("Y-m-d", $gt["stime"]);
        $endDate = date("Y-m-d", $gt["etime"]);
        $secResults = AgentOccupancy::select(DB::raw("
        sum(TIME_TO_SEC(`login_time`)) as login_time_sum, 
        sum(TIME_TO_SEC(`not_ready_time`)) as not_ready_time_sum, 
        sum(TIME_TO_SEC(`login_time`)) - sum(TIME_TO_SEC(`not_ready_time`)) - sum(TIME_TO_SEC(`on_call_time`)) - sum(TIME_TO_SEC(`on_acw_time`)) as wait_time_sum, 
        sum(TIME_TO_SEC(`on_call_time`)) as on_call_time_sum, 
        sum(TIME_TO_SEC(`on_acw_time`)) as on_acw_time_sum"));
        if($startDate == $endDate){
            $secResults = $secResults->whereDate("date", $startDate);
        }else{
            $secResults = $secResults->whereBetween("date", [$startDate, $endDate]);
        }
        if($request->input("agentName")){
            $arr = explode(' ',trim($request->input("agentName")));
            $secResults =  $secResults->where("agent_first_name", 'LIKE', $arr[0]);
        }
        $secResults = $secResults->get();
        $hrsResults = AgentOccupancy::select(DB::raw("
        SEC_TO_TIME(sum(TIME_TO_SEC(`login_time`))) as login_time_sum, 
        SEC_TO_TIME(sum(TIME_TO_SEC(`not_ready_time`))) as not_ready_time_sum, 
        SEC_TO_TIME(sum(TIME_TO_SEC(`login_time`)) - sum(TIME_TO_SEC(`not_ready_time`)) - sum(TIME_TO_SEC(`on_call_time`)) - sum(TIME_TO_SEC(`on_acw_time`))) as wait_time_sum, 
        SEC_TO_TIME(sum(TIME_TO_SEC(`on_call_time`))) as on_call_time_sum, 
        SEC_TO_TIME(sum(TIME_TO_SEC(`on_acw_time`))) as on_acw_time_sum"));
        if($startDate == $endDate){
            $hrsResults = $hrsResults->whereDate("date", $startDate);
        }else{
            $hrsResults = $hrsResults->whereBetween("date", [$startDate, $endDate]);
        }
        if($request->input("agentName")){
            $arr = explode(' ',trim($request->input("agentName")));
            $hrsResults =  $hrsResults->where("agent_first_name", 'LIKE', $arr[0]);
        }
        $hrsResults =  $hrsResults->get();
        // dd($secResults[0]["login_time_sum"]);
        return [ 
            "label" => ["Not Ready Time : ".$hrsResults[0]["not_ready_time_sum"], "Wait Time : ".$hrsResults[0]["wait_time_sum"], "On Call Time : ".$hrsResults[0]["on_call_time_sum"], "On ACW Time : ".$hrsResults[0]["on_acw_time_sum"]], 
            "series" => [intval($secResults[0]["not_ready_time_sum"]/100), intval($secResults[0]["wait_time_sum"]/100), intval($secResults[0]["on_call_time_sum"]/100), intval($secResults[0]["on_acw_time_sum"]/100)],
            "secResults" => $secResults, 
            "hrsResults" => $hrsResults
        ];
    }
    public function getAgentInformation(Request $request){
        return ['results' => DB::table('agents')->where("name", "=", $request->input("name"))->first()];
    }
    public function getSearchCriteriaInfo(Request $request){
        return ['results' => DB::table('search_criterias')->where("filter_key", "=", $request->input("filter_key"))->first()];
    }
    public function getAllDataCall(Request $request){
        
        $callRecords = FivenineCallLogs::query()->whereBetween("n_timestamp", [$request->input("stime"), $request->input("etime")])->orderBy("record_id");
        if($request->input("only_agent")){
            if($request->input("agentName")){
                $callRecords->where("agent_name", "=", $request->input("agentName"));
            }else{
                $callRecords->whereNotNull("agent_name");
            }
        }elseif($request->input("agentName")){
            if($request->input("agentName") == "None"){
                $callRecords->whereNull("agent_name");
            }else{
                $callRecords->where("agent_name", "=", $request->input("agentName"));
            }
        }else{

        }
        
        if($request->input("disposition")){
            $callRecords->where("disposition", "=", $request->input("disposition"));
        }
        if($request->input("talk_time_plus") == 1){
            $callRecords
            ->where(function($query){
                $query
                ->whereRaw('((MINUTE(`talk_time`) = 0')
                ->whereRaw('SECOND(`talk_time`) > 20)');
            })
            ->orWhere(function($query){
                $query->whereRaw('MINUTE(`talk_time`) > 0)');
            });
        }elseif($request->input("talk_time") > 0){
            if($request->input("talk_time") == 5){
                $callRecords
                ->whereRaw('MINUTE(`talk_time`) = 0')
                ->whereRaw('SECOND(`talk_time`) <= 5');
            }elseif($request->input("talk_time") == 10){
                $callRecords
                ->whereRaw('MINUTE(`talk_time`) = 0')
                ->whereRaw('SECOND(`talk_time`) > 5')
                ->whereRaw('SECOND(`talk_time`) <= 10');
            }elseif($request->input("talk_time") == 20){
                $callRecords
                ->whereRaw('MINUTE(`talk_time`) = 0')
                ->whereRaw('SECOND(`talk_time`) > 10')
                ->whereRaw('SECOND(`talk_time`) <= 20');
            }
        }
        
        $acw = $request->input("acw");
        if($request->input("acw_plus") == 1){
            $callRecords
            ->whereNotNull('after_call_work_time')
            ->whereRaw('(MINUTE(`after_call_work_time`) > 3')
            ->orWhere(function($query){
                $query->whereRaw('MINUTE(`after_call_work_time`) = 3')->whereRaw('SECOND(`after_call_work_time`) > 0)');
            });
        }elseif($request->input("acw_plus") == 0 && $acw){
            $callRecords
            ->whereNotNull('after_call_work_time')
            ->where(function($query) use ($acw){
                $query->whereRaw('(MINUTE(`after_call_work_time`) = ?', [$acw])->whereRaw('SECOND(`after_call_work_time`) = 0');
            })            
            ->orWhere(function($query) use ($acw){
                $query->whereRaw('MINUTE(`after_call_work_time`) = ?', [$acw-1])->whereRaw('SECOND(`after_call_work_time`) > 0)');
            });
        }
        
        DB::table("graph_fivenine_call_logs")->truncate();
        DB::table("graph_contacts")->truncate();
        // $callRecords->dd();
        foreach($callRecords->get() as $value){
            $value->replicate()->setTable('graph_fivenine_call_logs')->save();
        }
        
        $acw_plus = $request->input("acw_plus");  
        $talk_time_plus = $request->input("talk_time_plus");
                
        $callRecords = DB::table("graph_fivenine_call_logs")->get();
        foreach($callRecords as $value){
            $exists = DB::table("graph_contacts")->where("record_id", "=", $value->record_id);
            if($request->input("disposition")){
                $exists->where("last_dispo", "=", $request->input("disposition"));
            }
            $exists = $exists->count();
            if($exists == 0){
                $count = DB::table('graph_contacts')->where("record_id", "=", $value->record_id)->count();
                if($count == 0){
                    $contact = Contacts::where("record_id", "=", $value->record_id)->first();
                    $contact->mcall_attempts = 0;
                    $contact->hcall_attempts = 0;
                    $contact->wcall_attempts = 0;
                    $contact->mcall_received = 0;
                    $contact->hcall_received = 0;
                    $contact->wcall_received = 0;
                    $contact->cid = $contact->id;
                    $ocontact = $contact->replicate();
                    $ocontact->setTable('graph_contacts');
                    $ocontact->save();
                }
            }
        }
        foreach(DB::table('graph_contacts')->get() as $contactRecord){
            $totalmattempt = DB::table('graph_fivenine_call_logs')->where('record_id', $contactRecord->record_id)->where('dial_attempts', '>=', 1)->where('number_type', 'm')->sum('dial_attempts');
            $totalhattempt = DB::table('graph_fivenine_call_logs')->where('record_id', $contactRecord->record_id)->where('dial_attempts', '>=', 1)->where('number_type', 'hq')->sum('dial_attempts');
            $totaldattempt = DB::table('graph_fivenine_call_logs')->where('record_id', $contactRecord->record_id)->where('dial_attempts', '>=', 1)->where('number_type', 'd')->sum('dial_attempts');
            $totalmreceived = DB::table('graph_fivenine_call_logs')->where('record_id', $contactRecord->record_id)->where('call_received', 1)->where('dial_attempts', '>=', 1)->where('number_type', 'm')->count();
            $totalhqreceived = DB::table('graph_fivenine_call_logs')->where('record_id', $contactRecord->record_id)->where('call_received', 1)->where('dial_attempts', '>=', 1)->where('number_type', 'hq')->count();
            $totaldreceived = DB::table('graph_fivenine_call_logs')->where('record_id', $contactRecord->record_id)->where('call_received', 1)->where('dial_attempts', '>=', 1)->where('number_type', 'd')->count();      
            
            $ucontact = DB::table('graph_contacts')->where("record_id", "=", $contactRecord->record_id)->first();
            DB::table('graph_contacts')->where("record_id", "=", $contactRecord->record_id)->update([
                'mcall_attempts' => $totalmattempt,
                'hcall_attempts' => $totalhattempt,
                'wcall_attempts' => $totaldattempt,
                'mcall_received' => $totalmreceived,
                'hcall_received' => $totalhqreceived,
                'wcall_received' => $totaldreceived,
            ]);
        }
        $recordPerPage  = $request->recordPerPage;
        $search = $request->textSearch;
        $sortby = $request->sortby;
        $sort = $request->sort;
        $records = DB::table('graph_contacts')->select('graph_contacts.cid as id', 'graph_contacts.record_id', 'graph_contacts.first_name', 'graph_contacts.last_name', 'graph_contacts.title', 'graph_contacts.company', 'graph_contacts.mobilePhones', 'graph_contacts.workPhones', 'graph_contacts.homePhones', 'graph_contacts.emails', 'graph_contacts.outreach_touched_at', 'graph_contacts.outreach_created_at', 'graph_contacts.last_agent_dispo_time', 'graph_contacts.last_agent', 'graph_contacts.email_delivered', 'graph_contacts.email_opened', 'graph_contacts.email_clicked', 'graph_contacts.email_replied', 'graph_contacts.email_bounced', 'graph_contacts.mcall_attempts', 'graph_contacts.mcall_received', 'graph_contacts.wcall_attempts', 'graph_contacts.wcall_received', 'graph_contacts.hcall_attempts', 'graph_contacts.hcall_received', 'stages.id as stage', 'stages.name as stage_name', 'stages.css as stage_css', 'graph_contacts.dataset','graph_contacts.outreach_tag', 'graph_contacts.last_dispo')
            ->leftjoin('stages', 'stages.oid', '=', 'graph_contacts.stage');
        
        if($request->input("sortBy") == 'asc'):
            $records = $records->orderBy( $request->input('sortType'))->paginate($recordPerPage); 
        else:
            $records = $records->orderByDesc($request->input('sortType'))->paginate($recordPerPage);
        endif;
        return $records;
    }
    public function getFullDataCall(Request $request){
        
        $records = DB::table("graph_contacts")->get();

        return $records->pluck("cid");
    }
    public function getDashboardTime(Request $request){
        return $this->__getTimeInStrtottime($request->input("dateRange"));
    }
    private function __getAgentAndDispositionBasedACW($only_agent, $agentName, $disposition, $stime, $etime, $talk_time, $talk_time_plus, $list_name, $dispoMul){
        $records = DB::table("fivenine_call_logs");
        // if($disposition){
        //     $records->where("disposition", "=", $disposition);
        // }
        if($list_name){
            $records->where("list_name", "LIKE", $list_name);
        }
        $dispositions = [];
        if($dispoMul){
            $dispositions = $dispoMul;
        }
        if($disposition){
            if(count($dispositions) > 0){
                if(!in_array($disposition, $dispositions)){
                    $dispositions[count($dispositions)] = $disposition;
                }
                $records = $records->whereIn("disposition", $dispositions);
            }else{
                $records = $records->where("disposition", "=", $disposition);
            }
        }else{
            if(count($dispositions) > 0){
                $records = $records->whereIn("disposition", $dispositions);
            }
        }
        if($agentName){
            if($only_agent){
                $records = $records->where("agent_name", "=", $agentName);
            }else{
                if($agentName == "None"){
                    $records = $records->whereNull("agent_name");
                }else{
                    $records = $records->where("agent_name", "=", $agentName);
                }
            }
        }elseif($only_agent){
            $records = $records->whereNotNull("agent_name");
        }
        $records = $records->whereBetween("n_timestamp", [$stime, $etime]);
        if($talk_time_plus == 1){
            $records
            ->where(function($query){
                $query
                ->whereRaw('((MINUTE(`talk_time`) = 0')
                ->whereRaw('SECOND(`talk_time`) > 20)');
            })
            ->orWhere(function($query){
                $query->whereRaw('MINUTE(`talk_time`) > 0)');
            });
        }elseif($talk_time > 0){
            if($talk_time == 5){
                $records
                ->whereRaw('MINUTE(`talk_time`) = 0')
                ->whereRaw('SECOND(`talk_time`) <= 5');
            }elseif($talk_time == 10){
                $records
                ->whereRaw('MINUTE(`talk_time`) = 0')
                ->whereRaw('SECOND(`talk_time`) > 5')
                ->whereRaw('SECOND(`talk_time`) <= 10');
            }elseif($talk_time == 20){
                $records
                ->whereRaw('MINUTE(`talk_time`) = 0')
                ->whereRaw('SECOND(`talk_time`) > 10')
                ->whereRaw('SECOND(`talk_time`) <= 20');
            }
        }
        $records = $records->get();
        $talkTime = [
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0
        ];
        $max = 0;
        foreach ($records as $key => $value) {
            $value = get_object_vars($value);
            $after_call_work_time = $value["after_call_work_time"];
            $time = explode(":", $after_call_work_time);
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
            if($seconds > 180){
                $talkTime[4] += 1;
            }elseif($seconds > 120){
                $talkTime[3] += 1;
            }elseif($seconds > 60){
                $talkTime[2] += 1;
            }else{
                $talkTime[1] += 1;
            }
        }
        return ["label" => ["0-1min : ".$talkTime[1], "1-2min : ".$talkTime[2], "2-3min : ".$talkTime[3], "3+min : ".$talkTime[4]], "series" => [$talkTime[1], $talkTime[2], $talkTime[3], $talkTime[4]], "agentsName" => [], "stime" => $stime, "etime" => $etime, "sdate" => date("Y-m-d", $stime), "edate" => date("Y-m-d", $etime), 'acw' => [1, 2, 3, '3+'], 'dispositions' => $dispositions, 'dispoMul' => $dispoMul];
    }
    private function __getAgentAndDispositionBasedTalkTime($only_agent, $agentName, $disposition, $stime, $etime, $acw, $acw_plus, $list_name, $dispoMul){        
        
        $records = DB::table("fivenine_call_logs");
        if($list_name){
            $records = $records->where('list_name', "LIKE", $list_name);
        }
        $dispositions = [];
        if($dispoMul){
            $dispositions = $dispoMul;
        }
        if($disposition){
            if(count($dispositions) > 0){
                if(!in_array($disposition, $dispositions)){
                    $dispositions[count($dispositions)] = $disposition;
                }
                $records = $records->whereIn("disposition", $dispositions);
            }else{
                $records = $records->where("disposition", "=", $disposition);
            }
        }else{
            if(count($dispositions) > 0){
                $records = $records->whereIn("disposition", $dispositions);
            }
        }
        if($agentName){
            if($only_agent){
                $records = $records->where("agent_name", "=", $agentName);
            }else{
                if($agentName == "None"){
                    $records = $records->whereNull("agent_name");
                }else{
                    $records = $records->where("agent_name", "=", $agentName);
                }
            }
        }elseif($only_agent){
            $records = $records->whereNotNull("agent_name");
        }
        $records = $records->whereBetween("n_timestamp", [$stime, $etime]);
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
        return ["label" => ["0-5sec : ".$talkTime[1], "6-10sec : ".$talkTime[2], "11-20sec : ".$talkTime[3], "20+sec : ".$talkTime[4]], "series" => [$talkTime[1], $talkTime[2], $talkTime[3], $talkTime[4]], "agentsName" => [], "stime" => $stime, "etime" => $etime, "sdate" => date("Y-m-d", $stime), "edate" => date("Y-m-d", $etime), "talk_time" => [5, 10, 20,'20+'], 'dispositions' => $dispositions];
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
    private function __getSeconds($time){
        $time = explode(":", $time);
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
        return $seconds;
    }
    private function __getTime($value){
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
        return $time;
    }
}