<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\FivenineCallLogs;
use App\Models\Contacts;
use App\Models\AgentOccupancy;

class DashboardCallController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function getAgentCallStatus(Request $request)
    {
        $gt = $this->__getTimeInStrtottime($request->input("dateRange"));
        $stime = $gt["stime"];
        $etime = $gt["etime"];
        $records = FivenineCallLogs::select(DB::raw("count(fivenine_call_logs.id) as count, fivenine_call_logs.agent_name"))
        ->groupBy("fivenine_call_logs.agent_name")
        ->whereBetween("n_timestamp", [$stime, $etime]);
        if($request->input('number_type')){
            $records = $records->whereColumn('dnis', '=', $request->input('number_type'));
        }
        if($request->input('list_name')){
            $records = $records->where('list_name', 'LIKE', ($request->input('list_name') == 'Manual')?'':$request->input('list_name'));
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
    public function getAgentDispositionStatus(Request $request)
    {
        //current month 
        $gt = $this->__getTimeInStrtottime($request->input("dateRange"));
        $stime = $gt["stime"];
        $etime = $gt["etime"];
        
        if(($request->input("talk_time") || $request->input("acw")) && is_null($request->input("disposition")) ){
            return $this->__getTalkTimeBasedDisposition($request->input("only_agent"), $request->input("agentName"), $request->input("disposition"), $stime, $etime, $request->input("talk_time"), $request->input("talk_time_plus"), $request->input("acw"), $request->input("acw_plus"), $request->input("list_name"), $request->input('number_type'));
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
        if($request->input('number_type')){
            $records = $records->whereColumn('dnis', '=', $request->input('number_type'));
        }
        if($request->input('list_name')){
            $records = $records->where('list_name', 'LIKE', ($request->input('list_name') == 'Manual')?'':$request->input('list_name'));
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
    public function getAgentListStatus(Request $request)
    {
        //current month
        $gt = $this->__getTimeInStrtottime($request->input("dateRange"));
        $stime = $gt["stime"];
        $etime = $gt["etime"];
        
        if(($request->input("talk_time") || $request->input("acw")) && is_null($request->input("disposition")) ){
            return $this->__getTalkTimeBasedList($request->input("only_agent"), $request->input("agentName"), $request->input("disposition"), $stime, $etime, $request->input("talk_time"), $request->input("talk_time_plus"), $request->input("acw"), $request->input("acw_plus"));
        }
        $records = FivenineCallLogs::select(DB::raw("count(fivenine_call_logs.id) as count, fivenine_call_logs.list_name"))->groupBy("fivenine_call_logs.list_name");
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
    private function __getTalkTimeBasedDisposition($only_agent, $agentName, $disposition, $stime, $etime, $talk_time, $talk_time_plus, $acw, $acw_plus, $list_name, $number_type)
    { 
        $records = DB::table('fivenine_call_logs')->whereBetween("n_timestamp", [$stime, $etime]);
        if($number_type){
            $records = $records->whereColumn('dnis', '=', $number_type);
        }
        if($list_name){
            $records = $records->where('list_name', 'LIKE', ($list_name == 'Manual')?'':$list_name);
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
    private function __getTalkTimeBasedList($only_agent, $agentName, $disposition, $stime, $etime, $talk_time, $talk_time_plus, $acw, $acw_plus)
    { 
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
    public function getAgentBasedTalkTime(Request $request)
    {
        $gt = $this->__getTimeInStrtottime($request->input("dateRange"));
        $stime = $gt["stime"];
        $etime = $gt["etime"];
        if($request->input("agentName") || $request->input("disposition")){
            return $this->__getAgentAndDispositionBasedTalkTime($request->input("only_agent"), $request->input("agentName"), $request->input("disposition"), $stime, $etime, $request->input("acw"), $request->input("acw_plus"), $request->input("list_name"), $request->input("dispoMul"), $request->input('number_type'));
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
            $records = $records->where('list_name', 'LIKE', ($request->input('list_name') == 'Manual')?'':$request->input('list_name'));
        }
        if($request->input('number_type')){
            $records = $records->whereColumn('dnis', '=', $request->input('number_type'));
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
    public function getNumberBasedData(Request $request)
    {
        $gt = $this->__getTimeInStrtottime($request->input("dateRange"));
        $stime = $gt["stime"];
        $etime = $gt["etime"];
        if($request->input('list_name')){
            $list_name = $request->input('list_name');
            $number1 = FivenineCallLogs::whereBetween("n_timestamp", [$stime, $etime])->where('list_name', ($list_name == 'Manual')?'':$list_name)->whereColumn('dnis', '=', 'number1')->get()->count();
            $number2 = FivenineCallLogs::whereBetween("n_timestamp", [$stime, $etime])->where('list_name', ($list_name == 'Manual')?'':$list_name)->whereColumn('dnis', '=', 'number2')->get()->count();
            $number3 = FivenineCallLogs::whereBetween("n_timestamp", [$stime, $etime])->where('list_name', ($list_name == 'Manual')?'':$list_name)->whereColumn('dnis', '=', 'number3')->get()->count();
        }
        else {
            $number1 = FivenineCallLogs::whereBetween("n_timestamp", [$stime, $etime])->whereColumn('dnis', '=', 'number1')->get()->count();
            $number2 = FivenineCallLogs::whereBetween("n_timestamp", [$stime, $etime])->whereColumn('dnis', '=', 'number2')->get()->count();
            $number3 = FivenineCallLogs::whereBetween("n_timestamp", [$stime, $etime])->whereColumn('dnis', '=', 'number3')->get()->count();
        }
        
        $label = ['Number1 : '.$number1, 'Number2 : '.$number2, 'Number3 : '.$number3];
        $series = [$number1, $number2, $number3];

        return ["label" => $label, "series" => $series, "stime" => $stime, "etime" => $etime, "sdate" => date("Y-m-d", $stime), "edate" => date("Y-m-d", $etime), 'numbers' => ['number1', 'number2', 'number3']];
    }
    public function getAgentBasedACW(Request $request)
    {
        $gt = $this->__getTimeInStrtottime($request->input("dateRange"));
        $stime = $gt["stime"];
        $etime = $gt["etime"];
        if($request->input("agentName") || $request->input("disposition")){
            return $this->__getAgentAndDispositionBasedACW($request->input("only_agent"), $request->input("agentName"), $request->input("disposition"), $stime, $etime, $request->input("talk_time"), $request->input("talk_time_plus"), $request->input("list_name"), $request->input("dispoMul"), $request->input('number_type'));
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
        if($request->input('number_type')){
            $records = $records->whereColumn('dnis', '=', $request->input('number_type'));
        }
        if($request->input("list_name")){
           $records = $records->where('list_name', 'LIKE', ($request->input('list_name') == 'Manual')?'':$request->input('list_name'));
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
    public function getAgentBasedWaitingTime(Request $request)
    {
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
    public function getAgentInfo(Request $request)
    {
        $gt = $this->__getTimeInStrtottime($request->input("dateRange"));
        $stime = $gt["stime"];
        $etime = $gt["etime"];
        if($request->input("agentName") == "None"){
            $agentName = null;
        }else{
            $agentName = $request->input("agentName");
        }
        $records = FivenineCallLogs::where("agent_name", "=", $agentName)
        ->whereBetween("n_timestamp", [$stime, $etime]);
        if($request->input('number_type')){
            $records = $records->whereColumn('dnis', '=', $request->input('number_type'));
        }
        if($request->input('list_name')){
            $records = $records->where('list_name', 'LIKE', ($request->input('list_name') == 'Manual')?'':$request->input('list_name'));
        }
        $records = $records->get();
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
    function getAgentOccupancyData(Request $request)
    {
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
    public function getAgentInformation(Request $request)
    {
        return ['results' => DB::table('agents')->where("name", "=", $request->input("name"))->first()];
    }
    public function getSearchCriteriaInfo(Request $request)
    {
        return ['results' => DB::table('search_criterias')->where("filter_key", "=", $request->input("filter_key"))->first()];
    }
    public function calledNumbers(Request $request)
    {
        $acw = $request->input('acw');
        $agentName = $request->input('agentName');
        
        $disposition = $request->input('disposition');
        
        if($disposition[0] == null) {
            unset($disposition[0]);
        }
        
        $edate = $request->input('edate');
        $sdate = $request->input('sdate');
        $talk_time = $request->input('talk_time');
        $list = $request->input('list');
        $number_type = $request->input('number_type');

        $recordPerPage  = $request->recordPerPage;
        
        $recordIds = FivenineCallLogs::whereBetween('date', [$sdate, $edate]);
       
        if(count($disposition) >= 1) {
            $recordIds = $recordIds->whereIn('disposition', $disposition);
        }
        if($agentName == 'None') {
            $recordIds = $recordIds->whereNull('agent_name');
        } elseif($agentName != '') {
            $recordIds = $recordIds->where('agent_name', $agentName);
        }
        if($list) {
            $recordIds = $recordIds->where('list_name', 'LIKE', ($list == 'Manual')?'':$list);
        }
        if($number_type) {
            $recordIds = $recordIds->whereColumn('dnis', '=', $number_type);
        }
        if($talk_time != 'null' || $talk_time != null || $talk_time != '') {
            if(strpos($talk_time, '+')):
                $recordIds->where(function($query){
                    $query
                    ->whereRaw('((MINUTE(`talk_time`) = 0')
                    ->whereRaw('SECOND(`talk_time`) > 20)');
                })
                ->orWhere(function($query){
                    $query->whereRaw('MINUTE(`talk_time`) > 0)');
                });
            else:
                if($talk_time == 5) {
                    $recordIds->whereRaw('MINUTE(`talk_time`) = 0')->whereRaw('SECOND(`talk_time`) <= 5');
                }elseif($talk_time == 10) {
                    $recordIds->whereRaw('MINUTE(`talk_time`) = 0')->whereRaw('SECOND(`talk_time`) > 5')->whereRaw('SECOND(`talk_time`) <= 10');
                }elseif($talk_time == 20) {
                    $recordIds->whereRaw('MINUTE(`talk_time`) = 0')->whereRaw('SECOND(`talk_time`) > 10')->whereRaw('SECOND(`talk_time`) <= 20');
                }
            endif;
        }
        if($acw != 'null' || $acw != null || $acw != '') {
            if(strpos($acw, '+')):
                $recordIds->where(function($query){
                    $query->whereRaw('MINUTE(`after_call_work_time`) >= 3)');
                });
            else:
                if($talk_time == 1) {
                    $recordIds->whereRaw('MINUTE(`after_call_work_time`) == 0')->whereRaw('SECOND(`after_call_work_time`) >= 0');
                }elseif($talk_time == 2) {
                    $recordIds->whereRaw('MINUTE(`after_call_work_time`) == 1')->whereRaw('SECOND(`after_call_work_time`) >= 0');
                }elseif($talk_time == 3) {
                    $recordIds->whereRaw('MINUTE(`after_call_work_time`) == 2')->whereRaw('SECOND(`after_call_work_time`) >= 0');
                }
            endif;
        }

        $recordIds = $recordIds->orderBy('dnis')->pluck('dnis')->toArray();
        
        $recordIds = array_unique($recordIds);
        $recordIds = array_filter($recordIds);
        return $recordIds;
    }
    public function getAllDataCall(Request $request)
    {
        $acw = $request->input('acw');
        $agentName = $request->input('agentName');
        
        $disposition = $request->input('disposition');
        
        if($disposition[0] == null) {
            unset($disposition[0]);
        }
        
        $edate = $request->input('edate');
        $sdate = $request->input('sdate');
        $talk_time = $request->input('talk_time');
        $list = $request->input('list');
        $number_type = $request->input('number_type');

        $recordPerPage  = $request->recordPerPage;
        
        $recordIds = FivenineCallLogs::whereBetween('date', [$sdate, $edate]);
       
        if(count($disposition) >= 1) {
            $recordIds = $recordIds->whereIn('disposition', $disposition);
        }
        if($agentName == 'None') {
            $recordIds = $recordIds->whereNull('agent_name');
        } elseif($agentName != '') {
            $recordIds = $recordIds->where('agent_name', $agentName);
        }
        if($list) {
            $recordIds = $recordIds->where('list_name', 'LIKE', ($list == 'Manual')?'':$list);
        }
        if($number_type) {
            $recordIds = $recordIds->whereColumn('dnis', '=', $number_type);
        }
        if($talk_time != 'null' || $talk_time != null || $talk_time != '') {
            if(strpos($talk_time, '+')):
                $recordIds->where(function($query){
                    $query
                    ->whereRaw('((MINUTE(`talk_time`) = 0')
                    ->whereRaw('SECOND(`talk_time`) > 20)');
                })
                ->orWhere(function($query){
                    $query->whereRaw('MINUTE(`talk_time`) > 0)');
                });
            else:
                if($talk_time == 5) {
                    $recordIds->whereRaw('MINUTE(`talk_time`) = 0')->whereRaw('SECOND(`talk_time`) <= 5');
                }elseif($talk_time == 10) {
                    $recordIds->whereRaw('MINUTE(`talk_time`) = 0')->whereRaw('SECOND(`talk_time`) > 5')->whereRaw('SECOND(`talk_time`) <= 10');
                }elseif($talk_time == 20) {
                    $recordIds->whereRaw('MINUTE(`talk_time`) = 0')->whereRaw('SECOND(`talk_time`) > 10')->whereRaw('SECOND(`talk_time`) <= 20');
                }
            endif;
        }
        if($acw != 'null' || $acw != null || $acw != '') {
            if(strpos($acw, '+')):
                $recordIds->where(function($query){
                    $query->whereRaw('MINUTE(`after_call_work_time`) >= 3)');
                });
            else:
                if($talk_time == 1) {
                    $recordIds->whereRaw('MINUTE(`after_call_work_time`) == 0')->whereRaw('SECOND(`after_call_work_time`) >= 0');
                }elseif($talk_time == 2) {
                    $recordIds->whereRaw('MINUTE(`after_call_work_time`) == 1')->whereRaw('SECOND(`after_call_work_time`) >= 0');
                }elseif($talk_time == 3) {
                    $recordIds->whereRaw('MINUTE(`after_call_work_time`) == 2')->whereRaw('SECOND(`after_call_work_time`) >= 0');
                }
            endif;
        }

        $recordIds = $recordIds->pluck('record_id')->toArray();
        
        $recordIds = array_unique($recordIds);
        $recordIds = array_filter($recordIds);

    
        $records = Contacts::select('contacts.id', 'contacts.record_id', 'contacts.first_name', 'contacts.last_name', 'contacts.title', 'contacts.company', 'contacts.mobilePhones', 'contacts.workPhones', 'contacts.homePhones', 'contacts.emails', 'contacts.outreach_touched_at', 'contacts.outreach_created_at', 'contacts.last_agent_dispo_time', 'contacts.last_agent', 'contacts.mcall_attempts', 'contacts.mcall_received', 'contacts.wcall_attempts', 'contacts.wcall_received', 'contacts.hcall_attempts', 'contacts.hcall_received', 'stages.id as stage', 'stages.name as stage_name', 'stages.css as stage_css', 'contacts.outreach_tag')
        ->addSelect(['email_delivered' => DB::table('outreach_mailings')->selectRaw('count(*)')->whereColumn('outreach_mailings.contact_id', 'contacts.record_id')])
        ->addSelect(['email_opened' => DB::table('outreach_mailings')->selectRaw('count(*)')->whereColumn('outreach_mailings.contact_id', 'contacts.record_id')])
        ->addSelect(['email_clicked' => DB::table('outreach_mailings')->selectRaw('count(*)')->whereColumn('outreach_mailings.contact_id', 'contacts.record_id')])
        ->addSelect(['email_replied' => DB::table('outreach_mailings')->selectRaw('count(*)')->whereColumn('outreach_mailings.contact_id', 'contacts.record_id')])
        ->addSelect(['email_bounced' => DB::table('outreach_mailings')->selectRaw('count(*)')->whereColumn('outreach_mailings.contact_id', 'contacts.record_id')])
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
        //$allids = implode(',', $recordIds);
        $records = $records->paginate($recordPerPage);
        return $records;
    }
    public function getFullDataCall(Request $request){
        
        $acw = $request->input('acw');
        $agentName = $request->input('agentName');
        
        $disposition = $request->input('disposition');
        
        if($disposition[0] == null) {
            unset($disposition[0]);
        }
        
        $edate = $request->input('edate');
        $sdate = $request->input('sdate');
        $talk_time = $request->input('talk_time');
        $list = $request->input('list');
        $number_type = $request->input('number_type');
        
        $recordIds = FivenineCallLogs::whereBetween('date', [$sdate, $edate]);
       
        if(count($disposition) >= 1) {
            $recordIds = $recordIds->whereIn('disposition', $disposition);
        }
        if($agentName == 'None') {
            $recordIds = $recordIds->whereNull('agent_name');
        } elseif($agentName != '') {
            $recordIds = $recordIds->where('agent_name', $agentName);
        }
        if($list) {
            $recordIds = $recordIds->where('list_name', 'LIKE', ($list == 'Manual')?'':$list);
        }
        if($number_type) {
            $recordIds = $recordIds->whereColumn('dnis', '=', $number_type);
        }
        if($talk_time != 'null' || $talk_time != null || $talk_time != '') {
            if(strpos($talk_time, '+')):
                $recordIds->where(function($query){
                    $query
                    ->whereRaw('((MINUTE(`talk_time`) = 0')
                    ->whereRaw('SECOND(`talk_time`) > 20)');
                })
                ->orWhere(function($query){
                    $query->whereRaw('MINUTE(`talk_time`) > 0)');
                });
            else:
                if($talk_time == 5) {
                    $recordIds->whereRaw('MINUTE(`talk_time`) = 0')->whereRaw('SECOND(`talk_time`) <= 5');
                }elseif($talk_time == 10) {
                    $recordIds->whereRaw('MINUTE(`talk_time`) = 0')->whereRaw('SECOND(`talk_time`) > 5')->whereRaw('SECOND(`talk_time`) <= 10');
                }elseif($talk_time == 20) {
                    $recordIds->whereRaw('MINUTE(`talk_time`) = 0')->whereRaw('SECOND(`talk_time`) > 10')->whereRaw('SECOND(`talk_time`) <= 20');
                }
            endif;
        }
        if($acw != 'null' || $acw != null || $acw != '') {
            if(strpos($acw, '+')):
                $recordIds->where(function($query){
                    $query->whereRaw('MINUTE(`after_call_work_time`) >= 3)');
                });
            else:
                if($talk_time == 1) {
                    $recordIds->whereRaw('MINUTE(`after_call_work_time`) == 0')->whereRaw('SECOND(`after_call_work_time`) >= 0');
                }elseif($talk_time == 2) {
                    $recordIds->whereRaw('MINUTE(`after_call_work_time`) == 1')->whereRaw('SECOND(`after_call_work_time`) >= 0');
                }elseif($talk_time == 3) {
                    $recordIds->whereRaw('MINUTE(`after_call_work_time`) == 2')->whereRaw('SECOND(`after_call_work_time`) >= 0');
                }
            endif;
        }

        $recordIds = $recordIds->pluck('record_id')->toArray();
        
        $recordIds = array_unique($recordIds);
        $recordIds = array_filter($recordIds);

    
        $records = Contacts::whereIn('contacts.record_id', $recordIds);

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
        $records = $records->pluck('id');
        return $records;
    }
    public function getDashboardTime(Request $request){
        return $this->__getTimeInStrtottime($request->input("dateRange"));
    }
    private function __getAgentAndDispositionBasedACW($only_agent, $agentName, $disposition, $stime, $etime, $talk_time, $talk_time_plus, $list_name, $dispoMul, $number_type){
        $records = DB::table("fivenine_call_logs");
        // if($disposition){
        //     $records->where("disposition", "=", $disposition);
        // }
        if($number_type){
            $records = $records->whereColumn('dnis', '=', $number_type);
        }
        if($list_name){
            $records = $records->where('list_name', 'LIKE', ($list_name == 'Manual')?'':$list_name);
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
    private function __getAgentAndDispositionBasedTalkTime($only_agent, $agentName, $disposition, $stime, $etime, $acw, $acw_plus, $list_name, $dispoMul, $number_type){        
        
        $records = DB::table("fivenine_call_logs");
        if($list_name){
            $records = $records->where('list_name', 'LIKE', ($list_name == 'Manual')?'':$list_name);
        }
        if($number_type){
            $records = $records->whereColumn('dnis', '=', $number_type);
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

    public function getRecordContainerInfoCall(Request $request)
    {
        $ids = Contacts::whereIn('id', $request->exports)->pluck('record_id')->toArray();
        $called = $request->called_numbers;

        $recordIds = FivenineCallLogs::whereIn('fivenine_call_logs.record_id', $ids)->whereIn('dnis', $called);

        $acw = $request->input('acw');
        $agentName = $request->input('agentName');
        
        $disposition = $request->input('disposition');
        
        if($disposition[0] == null) {
            unset($disposition[0]);
        }
        
        $edate = $request->input('edate');
        $sdate = $request->input('sdate');
        $talk_time = $request->input('talk_time');
        $list = $request->input('list');
        $number_type = $request->input('number_type');
        
        $recordIds = $recordIds->whereBetween('date', [$sdate, $edate]);
       
        if(count($disposition) >= 1) {
            $recordIds = $recordIds->whereIn('fivenine_call_logs.disposition', $disposition);
        }
        if($agentName == 'None') {
            $recordIds = $recordIds->whereNull('agent_name');
        } elseif($agentName != '') {
            $recordIds = $recordIds->where('agent_name', $agentName);
        }
        if($list) {
            $recordIds = $recordIds->where('list_name', 'LIKE', ($list == 'Manual')?'':$list);
        }
        if($talk_time != 'null' || $talk_time != null || $talk_time != '') {
            if(strpos($talk_time, '+')):
                $recordIds->where(function($query){
                    $query
                    ->whereRaw('((MINUTE(`talk_time`) = 0')
                    ->whereRaw('SECOND(`talk_time`) > 20)');
                })
                ->orWhere(function($query){
                    $query->whereRaw('MINUTE(`talk_time`) > 0)');
                });
            else:
                if($talk_time == 5) {
                    $recordIds->whereRaw('MINUTE(`talk_time`) = 0')->whereRaw('SECOND(`talk_time`) <= 5');
                }elseif($talk_time == 10) {
                    $recordIds->whereRaw('MINUTE(`talk_time`) = 0')->whereRaw('SECOND(`talk_time`) > 5')->whereRaw('SECOND(`talk_time`) <= 10');
                }elseif($talk_time == 20) {
                    $recordIds->whereRaw('MINUTE(`talk_time`) = 0')->whereRaw('SECOND(`talk_time`) > 10')->whereRaw('SECOND(`talk_time`) <= 20');
                }
            endif;
        }
        if($acw != 'null' || $acw != null || $acw != '') {
            if(strpos($acw, '+')):
                $recordIds->where(function($query){
                    $query->whereRaw('MINUTE(`after_call_work_time`) >= 3)');
                });
            else:
                if($talk_time == 1) {
                    $recordIds->whereRaw('MINUTE(`after_call_work_time`) == 0')->whereRaw('SECOND(`after_call_work_time`) >= 0');
                }elseif($talk_time == 2) {
                    $recordIds->whereRaw('MINUTE(`after_call_work_time`) == 1')->whereRaw('SECOND(`after_call_work_time`) >= 0');
                }elseif($talk_time == 3) {
                    $recordIds->whereRaw('MINUTE(`after_call_work_time`) == 2')->whereRaw('SECOND(`after_call_work_time`) >= 0');
                }
            endif;
        }

        if($number_type){
            $recordIds = $recordIds->whereColumn('dnis', '=', "fivenine_call_logs.$number_type");
        }
        $recordIds = $recordIds->select('fivenine_call_logs.customer_name', 'fivenine_call_logs.record_id', 'fivenine_call_logs.dnis', 'fivenine_call_logs.dial_attempts', 'fivenine_call_logs.timestamp', 'fivenine_call_logs.disposition', 'fivenine_call_logs.agent_name', 'fivenine_call_logs.number1', 'fivenine_call_logs.number2', 'fivenine_call_logs.number3', 'fivenine_call_logs.list_name', 'contacts.company', 'contacts.title')
                            ->join('contacts', 'contacts.record_id', '=', 'fivenine_call_logs.record_id')->orderBy('fivenine_call_logs.record_id', 'asc')->get();
        return $recordIds;
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
    function getOccupancyDataExport(Request $request){
        $gt = $this->__getTimeInStrtottime($request->input("dateRange"));
        $startDate = date("Y-m-d", $gt["stime"]);
        $endDate = date("Y-m-d", $gt["etime"]);
        $secResults = AgentOccupancy::groupBy('name')->select(DB::raw("
        sum(TIME_TO_SEC(`login_time`)) as login_time_sum, 
        sum(TIME_TO_SEC(`not_ready_time`)) as not_ready_time_sum, 
        sum(TIME_TO_SEC(`login_time`)) - sum(TIME_TO_SEC(`not_ready_time`)) - sum(TIME_TO_SEC(`on_call_time`)) - sum(TIME_TO_SEC(`on_acw_time`)) as wait_time_sum, 
        sum(TIME_TO_SEC(`on_call_time`)) as on_call_time_sum, 
        sum(TIME_TO_SEC(`on_acw_time`)) as on_acw_time_sum"), 'name');
        if($startDate == $endDate){
            $secResults = $secResults->whereDate("date", $startDate);
        }else{
            $secResults = $secResults->whereBetween("date", [$startDate, $endDate]);
        }
        $secResults = $secResults->get();
        return $secResults;
    }
    private function __getAgentInfo($name, $agentInfoArray){
        foreach($agentInfoArray as $key => $value){
            if($value['name'] == $name){
                return $value;
            }
        }
    }
    private function __getHrs($seconds){
        $hrs = '';
        if($seconds >= 3600){
            $h = intval($seconds/3600);
            if($h < 10){
                $hrs = '0'.$h;
            }else{
                $hrs = $h;
            }
            $seconds = $seconds - $h*3600;
        }else{
            $hrs = '00';
        }

        if($seconds >= 60){
            $m = intval($seconds/60);
            if($m > 10){
                $hrs = $hrs.':'.$m;
            }else{
                $hrs = $hrs.':0'.$m;
            }
            $seconds = $seconds - $m*60;
        }else{
            $hrs = $hrs.':00';
        }
        if($seconds > 10){
            $hrs = $hrs.':'.$seconds;
        }else{
            $hrs = $hrs.':0'.$seconds;
        }
        return $hrs;
    }
    public function generateExport(Request $request){
        $gt = $this->__getTimeInStrtottime($request->input("dateRange"));
        $stime = $gt["stime"];
        $etime = $gt["etime"];
        $list_name = '';
        $data = [];
        $fields = [
            'Agent Name' => 'agent_name',
            'Calls' => 'calls',
            'Login Time' => 'login_time',
            'Total ACW' => 'acw',
            'Avg ACW' => 'avacw',
            'Talk Time' => 'talk_time',
            'Avg Talk Time' => 'avg_talk_time',
            'Not Ready' => 'not_ready',
        ];
        $nfields = ['agent_name', 'calls', 'login_time', 'acw', 'avacw', 'talk_time', 'avg_talk_time', 'not_ready'];
        $sep = [
            'agent_name' => '',
            'calls' => '',
            'login_time' => '',
            'acw' => '',
            'avacw' => '',
            'talk_time' => '',
            'avg_talk_time' => '',
            'not_ready' => '',
        ];
        $col1 = 0;
        $col2 = 0;
        $col3 = 0;
        $col4 = 0;
        $col5 = 0;
        $col6 = 0;
        $col7 = 0;
        $agentCallStatus = $this->getAgentCallStatus($request);
        $sAgentCallStatus = $agentCallStatus['agentName'];
        $agentOccupandyDataExport = $this->getOccupancyDataExport($request);

        $cfieldB['agent_name'] = '<b class="highlight">Row Labels</b>';
        foreach($sAgentCallStatus as $key => $value){
            if($value == 'None'){
                $cc = FivenineCallLogs::whereNull('agent_name')->whereBetween("n_timestamp", [$stime, $etime])->count();
                $newdata = [
                    'agent_name' => 'Auto Dial',
                    'calls' => $cc,
                    'login_time' => 'NA',
                    'acw' => 'NA',
                    'avacw' => 'NA',
                    'talk_time' => 'NA',
                    'avg_talk_time' => 'NA',
                    'not_ready' => 'NA',
                ];
                $col1 = $col1+$cc;
            }
            else {
                $agentInfo = $this->__getAgentInfo($value, $agentOccupandyDataExport);

                $callCount = FivenineCallLogs::where('agent_name', 'like', $value)->whereBetween("n_timestamp", [$stime, $etime])->count();
                $d1 = intval($agentInfo['login_time_sum']);
                $d2 = intval($agentInfo['on_acw_time_sum']);
                $d3 = intval($agentInfo['on_acw_time_sum']/$callCount);
                $d4 = intval($agentInfo['on_call_time_sum']);
                $d5 = intval($agentInfo['on_call_time_sum']/$callCount);
                $d6 = intval($agentInfo['not_ready_time_sum']);
                $newdata = [
                    'agent_name' => $value,
                    'calls' => number_format($callCount, 0),
                    'login_time' => $this->__getHrs($d1),
                    'acw' => $this->__getHrs($d2),
                    'avacw' => $this->__getHrs($d3),
                    'talk_time' => $this->__getHrs($d4),
                    'avg_talk_time' => $this->__getHrs($d5),
                    'not_ready' => $this->__getHrs($d6)
                ];
                $col1 = $col1+$callCount;
                $col2 = $col2+$d1;
                $col3 = $col3+$d2;
                $col4 = $col4+$d3;
                $col5 = $col5+$d4;
                $col6 = $col6+$d5;
                $col7 = $col7+$d6;
            }   
            array_push($data, $newdata);
            $cfieldB[$nfields[$key + 1]] = '<b class="highlight">'.$value.'</b>';
        }
        $cfieldB[$nfields[count($cfieldB)]] = '<b class="highlight">Grand Total</b>';
        $total = [
            'agent_name' => '<b class="lowlight">Total</b>',
            'calls' => '<b class="lowlight">'.number_format($col1, 0).'</b>',
            'login_time' => '<b class="lowlight">'.$this->__getHrs($col2).'</b>',
            'acw' => '<b class="lowlight">'.$this->__getHrs($col3).'</b>',
            'avacw' => '<b class="lowlight">'.$this->__getHrs($col4).'</b>',
            'talk_time' => '<b class="lowlight">'.$this->__getHrs($col5).'</b>',
            'avg_talk_time' => '<b class="lowlight">'.$this->__getHrs($col6).'</b>',
            'not_ready' => '<b class="lowlight">'.$this->__getHrs($col7).'</b>'
        ];
        array_push($data, $total);
        array_push($data, $sep);

        $sep2 = [
            'agent_name' => '<b>Disposition wise call count</b>',
            'calls' => '',
            'login_time' => '',
            'acw' => '',
            'avacw' => '',
            'talk_time' => '',
            'avg_talk_time' => '',
            'not_ready' => '',
        ];
        array_push($data, $sep2);
        array_push($data, $cfieldB);
        
        $dispositions = FivenineCallLogs::select('disposition')->distinct()->get()->pluck('disposition')->toArray();

        $gdata = [
            'agent_name' => '<b>Grand Total</b>',
            'calls' => 0,
            'login_time' => 0,
            'acw' => 0,
            'avacw' => 0,
            'talk_time' => 0,
            'avg_talk_time' => 0,
            'not_ready' => 0,
        ];

        foreach($dispositions as $key => $disposition)
        {
            $newdata = [];
            $newdata['agent_name'] = $disposition;
            $grandTotal = 0;
            foreach($sAgentCallStatus as $akey => $agentName)
            {
                if($agentName == 'None') {
                    $total = DB::table('fivenine_call_logs')->whereNull('agent_name')->where('disposition', 'like', $disposition)
                                        ->when($list_name, function($query, $list_name){
                                                $query->where('list_name', 'like', $list_name);
                                            })
                                        ->whereBetween("n_timestamp", [$stime, $etime])
                                        ->count();
                }
                else {
                    $total = DB::table('fivenine_call_logs')
                        ->where('agent_name', 'like', $agentName)
                        ->where('disposition', 'like', $disposition)
                        ->when($list_name, function($query, $list_name){
                            $query->where('list_name', 'like', $list_name);
                        })
                        ->whereBetween("n_timestamp", [$stime, $etime])
                        ->count();
                }
                $newdata[$nfields[$akey + 1]] = number_format($total, 0);
                $gdata[$nfields[$akey + 1]] = $gdata[$nfields[$akey + 1]] + $total;
                $grandTotal = $grandTotal + $total;
            }
            $gdata[$nfields[count($newdata)]] = $gdata[$nfields[count($newdata)]] + $grandTotal;
            $newdata[$nfields[count($newdata)]] = number_format($grandTotal, 0);
            
            array_push($data, $newdata);
        }
        $gdata = [
            'agent_name' => '<b class="lowlight">Grand Total</b>',
            'calls' => ($gdata['calls'] == 0)?'':'<b class="lowlight">'.number_format($gdata['calls'], 0).'</b>',
            'login_time' => ($gdata['login_time'] == 0)?'':'<b class="lowlight">'.number_format($gdata['login_time'], 0).'</b>',
            'acw' => ($gdata['acw'] == 0)?'':'<b class="lowlight">'.number_format($gdata['acw'], 0).'</b>',
            'avacw' => ($gdata['avacw'] == 0)?'':'<b class="lowlight">'.number_format($gdata['avacw'], 0).'</b>',
            'talk_time' => ($gdata['talk_time'] == 0)?'':'<b class="lowlight">'.number_format($gdata['talk_time'], 0).'</b>',
            'avg_talk_time' => ($gdata['avg_talk_time'] == 0)?'':'<b class="lowlight">'.number_format($gdata['avg_talk_time'], 0).'</b>',
            'not_ready' => ($gdata['not_ready'] == 0)?'':'<b class="lowlight">'.number_format($gdata['not_ready'], 0).'</b>',
        ];
        array_push($data, $gdata);
        return ['json_fields' => $fields, 'json_data' => $data];
    }
}