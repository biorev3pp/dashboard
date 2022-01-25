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


class TestingController extends Controller
{
    public function getListInfo(){
        
        $listName = "Ankit-Final-Test-List";
        $identifier = "fb074b52-c731-4146-b12a-e955baadd4d5";
        $code = "iWkFQ0K2FdNQSMPWSj9PfxISm";
        
        $data = DB::table("fivenine_lists")->where("name", "LIKE", $listName)->first();
        
        $data = get_object_vars($data);
        $his = json_decode($data["history"]);
        $id = $his[count($his)-1];
        $listId = $data["id"];
        $exHistoryRecord = get_object_vars(DB::table("export_histories")->where('code',"LIKE", $code)->first());
        echo $id."<br>";
        //need to get import result 
        $curl = curl_init();
        for ($i=0; $i < 20; $i++) 
        {
            sleep(1);
            echo $i."<br>";
            $setting1 = Settings::where('id', '=', 37)->first();
            $setting2 = Settings::where('id', '=', 38)->first();
            $username = $setting1->value;
            $password = $setting2->value;
            $code = base64_encode($username.':'.$password);
            curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.five9.com/wsadmin/v4/AdminWebService',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS =>'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.admin.ws.five9.com/">;
            <soapenv:Header/>
            <soapenv:Body>
                <ser:isReportRunning>
                    <identifier>'.$id.'</identifier>
                </ser:isReportRunning>
            </soapenv:Body>
            </soapenv:Envelope>',
            CURLOPT_HTTPHEADER => array(
                "Authorization: Basic $code",
                "Content-Type: application/xml",
                "Cookie: clientId=8998A130504F4358BADF215E686E2E8B; Authorization=Bearer-ebfa8d5a-ba2a-11eb-abfe-00505fca8def; apiRouteKey=SCLnAPIc6f; app_key=web-ui; farmId=182; uiRouteKey=SCLnUI5551"
            ),
            ));
            
            $response = curl_exec($curl); 
            $result = substr($response, strpos($response, '<return>')+8, strrpos($response, '</return>') - strpos($response, '<return>')-8);
            if($result == 'false'){  
                echo 'success';
                break;
            }
        }
        curl_close($curl);
        //get import result
        $postFields = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.admin.ws.five9.com/">
            <soapenv:Header/>
            <soapenv:Body>
            <ser:getListImportResult>
                <!--Optional:-->
                <identifier>
                    <!--Optional:-->
                    <identifier>'.$id.'</identifier>
                </identifier>
            </ser:getListImportResult>
            </soapenv:Body>
        </soapenv:Envelope>';
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.five9.com/wsadmin/v4/AdminWebService',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => array(
            "Authorization: Basic $code",
                "Content-Type: application/xml",
                "Cookie: clientId=C4A452B18E5646FA9ACBE8A819154296; Authorization=Bearer-ebfa8d5a-ba2a-11eb-abfe-00505fca8def; apiRouteKey=SCLnAPIc6f; app_key=web-ui; farmId=182; uiRouteKey=SCLnUI5551"
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $name = "getListImportResult";
        $var = 'ns2'.$name.'Response';
        $xml = $response;
        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $xml);
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $return = json_decode($json,true);
        if(array_key_exists($var, $return['envBody'])):
            $results01 = $return['envBody']["$var"];
        else:
            return false;
        endif;
        //save particualr list result in export-history table
        $mul_identifier = json_decode($exHistoryRecord["mul_identifier"]);
        $mul_identifierNew = [];
        foreach($mul_identifier as $key => $mlist){
            if($key == $listName){
                $mul_identifierNew[$key] = $results01;
            }else{
                $mul_identifierNew[$key] = $mlist;
            }
        }
        $expLists = json_decode($exHistoryRecord["lists"]);
        $expListsNew = [];
        foreach($expLists as $value){
            if($value != $listName){
                $expListsNew[] = $value;
            }
        }
        $inserted = intval($exHistoryRecord["inserted"]) + intval($results01["return"]["crmRecordsInserted"]);
        $updated = intval($exHistoryRecord["updated"]) + intval($results01["return"]["crmRecordsUpdated"]);
        DB::table("export_histories")->where("id", "=", $exHistoryRecord)->update([
            "mul_identifier" => json_encode($mul_identifierNew),
            "lists" => (count($expListsNew) > 0) ? json_encode($expListsNew) : null,
            "inserted" => $inserted,
            "updated" => $updated,
        ]);
        
        //get list
        
        $start = date("Y-m-d", strtotime("-6 years")).'T'.date("H:i:s", strtotime("-6 years"));
        $end = date("Y-m-d", strtotime("now")).'T'.date("H:i:s", strtotime("now"));
        $postFields = '<env:Envelope xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:tns="http://service.admin.ws.five9.com/" xmlns:env="http://schemas.xmlsoap.org/soap/envelope/"
        xmlns:ins0="http://jaxb.dev.java.net/array">
        <env:Body>
            <tns:runReport>
                <folderName>My Reports</folderName>
                <reportName>List All '.$listName.'</reportName>
                    <criteria>
                        <time>
                        <end>'.$end.'</end>
                        <start>'.$start.'</start>
                        </time>
                    </criteria>
            </tns:runReport>
        </env:Body>
        </env:Envelope>';
        $curl = curl_init();
        $setting1 = Settings::where('id', '=', 37)->first();
        $setting2 = Settings::where('id', '=', 38)->first();
        $username = $setting1->value;
        $password = $setting2->value;
        
        $code = base64_encode($username.':'.$password);
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.five9.com/wsadmin/v4/AdminWebService',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Basic $code",
                "Content-Type: application/xml",
                "Cookie: Authorization=Bearer-ebfa8d5a-ba2a-11eb-abfe-00505fca8def; apiRouteKey=SCLnAPIc6f; app_key=web-ui; farmId=182; uiRouteKey=SCLnUI5551"
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $xml = $response;
        // SimpleXML seems to have problems with the colon ":" in the <xxx:yyy> response tags, so take them out
        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $xml);
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $return = json_decode($json,true);
        if(isset($return["envBody"]["envFault"])):
            return ['restults' => 'fault'];
        endif;
        $id = substr($response, strpos($response, '<return>')+8, strrpos($response, '</return>') - strpos($response, '<return>')-8);
    
        $curl = curl_init();
        for ($i=0; $i < 10; $i++) {
            sleep(1);     
            
            curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.five9.com/wsadmin/v4/AdminWebService',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS =>'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.admin.ws.five9.com/">;
            <soapenv:Header/>
            <soapenv:Body>
                <ser:isReportRunning>
                    <identifier>'.$id.'</identifier>
                </ser:isReportRunning>
            </soapenv:Body>
            </soapenv:Envelope>',
            CURLOPT_HTTPHEADER => array(
                "Authorization: Basic $code",
                "Content-Type: application/xml",
                "Cookie: clientId=8998A130504F4358BADF215E686E2E8B; Authorization=Bearer-ebfa8d5a-ba2a-11eb-abfe-00505fca8def; apiRouteKey=SCLnAPIc6f; app_key=web-ui; farmId=182; uiRouteKey=SCLnUI5551"
            ),
            ));
    
            $response = curl_exec($curl); 
            $result = substr($response, strpos($response, '<return>')+8, strrpos($response, '</return>') - strpos($response, '<return>')-8);
            if($result == 'false'){
                break;
            }
        }
        curl_close($curl);
    
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.five9.com/wsadmin/v4/AdminWebService',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS =>'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.admin.ws.five9.com/">;
            <soapenv:Header/>
            <soapenv:Body>
                <ser:getReportResult>
                    <!--Optional:-->
                    <identifier>'.$id.'</identifier>
                </ser:getReportResult>
            </soapenv:Body>
            </soapenv:Envelope>',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                "Authorization: Basic $code",
                "Cookie: clientId=C4A452B18E5646FA9ACBE8A819154296; Authorization=Bearer-ebfa8d5a-ba2a-11eb-abfe-00505fca8def; apiRouteKey=SCLnAPIc6f; app_key=web-ui; farmId=182; uiRouteKey=SCLnUI5551"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $xml = $response;
        // SimpleXML seems to have problems with the colon ":" in the <xxx:yyy> response tags, so take them out
        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $xml);
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $return = json_decode($json,true);
        $header = $return["envBody"]["ns2getReportResultResponse"]["return"]["header"]["values"]["data"];
        $headerOld = $header;
        foreach($header as $key => $value):
            if(strpos($value, " ")):
                $e = explode(" ", $value);
                $i = implode("_", $e);
                $header[$key] = $i;
            else:
                $header[$key] = $value;
            endif;
        endforeach; 
        $counter = 0;
        $contactIds = [];
        $allRecords = []; 
        if(isset($return["envBody"]["ns2getReportResultResponse"]["return"]["records"])):
            $records = $return["envBody"]["ns2getReportResultResponse"]["return"]["records"];
            DB::table("fivenine_list_contacts")->where("fivenine_list_id", "=", $listId)->delete();
            foreach($records as $key => $value):
                if(isset($value["data"])){
                    $record["record_id"] = is_array($value["data"][3]) ? null : intval($value["data"][3]);
                    $record["number1"] = is_array($value["data"][4]) ? null : intval($value["data"][4]);
                    $record["number2"] = is_array($value["data"][5]) ? null : intval($value["data"][5]);
                    $record["number3"] = is_array($value["data"][6]) ? null : intval($value["data"][6]);                        
                }else{
                    $record["record_id"] = is_array($value["values"]["data"][3]) ? null : intval($value["values"]["data"][3]);
                    $record["number1"] = is_array($value["values"]["data"][4]) ? null : intval($value["values"]["data"][4]);
                    $record["number2"] = is_array($value["values"]["data"][5]) ? null : intval($value["values"]["data"][5]);
                    $record["number3"] = is_array($value["values"]["data"][6]) ? null : intval($value["values"]["data"][6]);
                }
                
                $count = DB::table("fivenine_list_contacts")->where("fivenine_list_id", "=", $listId)->where("record_id", "=", $record["record_id"])->count();
                if($count == 0){
                    DB::table("fivenine_list_contacts")->insert([
                        "fivenine_list_id" => $listId,
                        "number1" => $record["number1"],
                        "record_id" => $record["record_id"],
                        "number2" => $record["number2"],
                        "number3" => $record["number3"],

                    ]);
                }
            endforeach;
            $listItemCounter = DB::table("fivenine_list_contacts")->where("fivenine_list_id", "=", $listId)->count();
            DB::table("fivenine_lists")->where("id", "=", $listId)->update([
                "size" => $listItemCounter,
                "status" => 1,
                "updated_at" => date("Y-m-d H:i:s", strtotime("now"))
            ]);

        else:
            DB::table("fivenine_lists")->where("id", "=", $listId)->update([
                "size" => 0,
                "updated_at" => date("Y-m-d H:i:s", strtotime("now"))
            ]);
            DB::table("fivenine_list_contacts")->where("fivenine_list_id", "=", $listId)->delete();
        endif;
    }
    public function updateAllProspectsBack($record_id = null){
        if($record_id > 15500){
            echo 'done'; die;
        }
        $record = Contacts::where("record_id", "=", $record_id)->first();
        $mobilePhones = $record->mobilePhones;
        $workPhones = $record->workPhones;
        $homePhones = $record->homePhones;

        $record->f_first_name = ucfirst($record->first_name);
        $record->f_last_name = ucfirst($record->last_name);
        $record->number1 = $this->__NumberFormater1($mobilePhones);
        $record->number2 = $this->__NumberFormater1($workPhones);
        $record->number3 = $this->__NumberFormater1($homePhones);
        $record->number1type = 'M';
        $record->number2type = 'W';
        $record->number3type = 'H';
        $record->number1call = $this->__NumberType($this->__NumberFormater1($mobilePhones), $record->record_id);
        $record->number2call = $this->__NumberType($this->__NumberFormater1($workPhones), $record->record_id);
        $record->number3call = $this->__NumberType($this->__NumberFormater1($homePhones), $record->record_id);
        $record->ext1 = $this->__NumberExtFormater1($mobilePhones);
        $record->ext2 = $this->__NumberExtFormater1($workPhones);
        $record->ext3 = $this->__NumberExtFormater1($homePhones);
        $record->save();

        $record = Contacts::where("record_id", ">", $record_id)->orderBy('record_id', 'asc')->first();
        $count = Contacts::where("record_id", "<", $record_id)->orderBy('record_id', 'asc')->count();
        $pre = Contacts::where("record_id", ">", $record->record_id)->orderBy('record_id', 'asc')->first();
        return view('update-all-prospects-back', ['record_id' => $record->record_id, 'count' => $count, 'pre' => $pre->record_id]);
    }
    public function updateAllProspectsBack2($record_id = null){
        if($record_id > 31000){
            echo 'done'; die;
        }
        $record = Contacts::where("record_id", "=", $record_id)->first();
        $mobilePhones = $record->mobilePhones;
        $workPhones = $record->workPhones;
        $homePhones = $record->homePhones;

        $record->f_first_name = ucfirst($record->first_name);
        $record->f_last_name = ucfirst($record->last_name);
        $record->number1 = $this->__NumberFormater1($mobilePhones);
        $record->number2 = $this->__NumberFormater1($workPhones);
        $record->number3 = $this->__NumberFormater1($homePhones);
        $record->number1type = 'M';
        $record->number2type = 'W';
        $record->number3type = 'H';
        $record->number1call = $this->__NumberType($this->__NumberFormater1($mobilePhones), $record->record_id);
        $record->number2call = $this->__NumberType($this->__NumberFormater1($workPhones), $record->record_id);
        $record->number3call = $this->__NumberType($this->__NumberFormater1($homePhones), $record->record_id);
        $record->ext1 = $this->__NumberExtFormater1($mobilePhones);
        $record->ext2 = $this->__NumberExtFormater1($workPhones);
        $record->ext3 = $this->__NumberExtFormater1($homePhones);
        $record->save();

        $record = Contacts::where("record_id", ">", $record_id)->orderBy('record_id', 'asc')->first();
        $count = Contacts::where("record_id", "<", $record_id)->orderBy('record_id', 'asc')->count();
        $pre = Contacts::where("record_id", ">", $record->record_id)->orderBy('record_id', 'asc')->first();
        return view('update-all-prospects-back2', ['record_id' => $record->record_id, 'count' => $count, 'pre' => $pre->record_id]);
    }
    public function updateAllProspects($record_id = null){
        if($record_id < 31500){
            echo 'done'; die;
        }
        $record = Contacts::where("record_id", "=", $record_id)->first();
        $mobilePhones = $record->mobilePhones;
        $workPhones = $record->workPhones;
        $homePhones = $record->homePhones;

        $record->f_first_name = ucfirst($record->first_name);
        $record->f_last_name = ucfirst($record->last_name);
        $record->number1 = $this->__NumberFormater1($mobilePhones);
        $record->number2 = $this->__NumberFormater1($workPhones);
        $record->number3 = $this->__NumberFormater1($homePhones);
        $record->number1type = 'M';
        $record->number2type = 'W';
        $record->number3type = 'H';
        $record->number1call = $this->__NumberType($this->__NumberFormater1($mobilePhones), $record->record_id);
        $record->number2call = $this->__NumberType($this->__NumberFormater1($workPhones), $record->record_id);
        $record->number3call = $this->__NumberType($this->__NumberFormater1($homePhones), $record->record_id);
        $record->ext1 = $this->__NumberExtFormater1($mobilePhones);
        $record->ext2 = $this->__NumberExtFormater1($workPhones);
        $record->ext3 = $this->__NumberExtFormater1($homePhones);
        $record->save();

        $record = Contacts::where("record_id", "<", $record_id)->orderBy('record_id', 'desc')->first();
        $count = Contacts::where("record_id", ">", $record_id)->orderBy('record_id', 'desc')->count();
        $pre = Contacts::where("record_id", "<", $record->record_id)->orderBy('record_id', 'desc')->first();
        return view('update-all-prospect', ['record_id' => $record->record_id, 'count' => $count, 'pre' => $pre->record_id]);
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
    private function __NumberType($number, $rid)
    {
        $data = DB::table('fivenine_call_logs')
        ->where('record_id', $rid)
        ->where('dnis', $number)->get();
        $total = 0;
        foreach($data as $value){
            $value = get_object_vars($value);
            $total += intval($value["dial_attempts"]);
        }
        return $total;
    }
    public function updateAllProspectsEmailCount($record_id = null){
        if($record_id < 20){
            echo 'done'; die;
        }
        $record = Contacts::where("record_id", "=", $record_id)->first();        
        $this->emailCounter($record_id);
        $record = Contacts::where("record_id", "<", $record_id)->orderBy('record_id', 'desc')->first();
        $count = Contacts::where("record_id", ">", $record_id)->orderBy('record_id', 'desc')->count();
        $pre = Contacts::where("record_id", "<", $record->record_id)->orderBy('record_id', 'desc')->first();
        return view('update-all-prospect-email-count', ['record_id' => $record->record_id, 'count' => $count, 'pre' => $pre->record_id]);
        // return view('update-all-prospect-email-count', ['record_id' => $record->record_id, 'count' => $count, 'pre' => $pre->record_id]);
    }
    public function updateAllProspectsEmailCountBack($record_id = null){
        if($record_id >50062){
            echo 'done'; die;
        }
        $record = Contacts::where("record_id", "=", $record_id)->first();        
        $this->emailCounter($record_id);
        $record = Contacts::where("record_id", ">", $record_id)->orderBy('record_id', 'desc')->first();
        $count = Contacts::where("record_id", "<", $record_id)->orderBy('record_id', 'desc')->count();
        $pre = Contacts::where("record_id", ">", $record->record_id)->orderBy('record_id', 'desc')->first();
        return view('update-all-prospect-email-count', ['record_id' => $record->record_id, 'count' => $count, 'pre' => $pre->record_id]);
        // return view('update-all-prospect-email-count', ['record_id' => $record->record_id, 'count' => $count, 'pre' => $pre->record_id]);
    }
    public function emailCounter($record_id = null)
    {
        $totalemail = OutreachMailings::where('contact_id', $record_id)->whereNotNull('deliveredAt')->count();
        $totalopen = OutreachMailings::where('contact_id', $record_id)->whereNotNull('openedAt')->count();
        $totalreply = OutreachMailings::where('contact_id', $record_id)->whereNotNull('repliedAt')->count();
        $totalbounced = OutreachMailings::where('contact_id', $record_id)->whereNotNull('bouncedAt')->count();
        $totalclick = OutreachMailings::where('contact_id', $record_id)->whereNotNull('clickedAt')->count();
        $ucontact = Contacts::where('record_id', $record_id)->first();
        $ucontact->update([
            'email_delivered' => $totalemail,
            'email_opened' => $totalopen,
            'email_clicked' => $totalclick,
            'email_replied' => $totalreply,
            'email_bounced' => $totalbounced,]
        );
    }
}