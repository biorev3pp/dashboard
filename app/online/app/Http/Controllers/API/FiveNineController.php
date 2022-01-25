<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Biorev;
use App\Models\Settings;
use Biorev\Fivenine\Fivenine;
use App\Models\Agents;
use App\Models\FivenineCallLogs;
use App\Models\FivenineAllCallLogs;
use Illuminate\Support\Facades\DB;
use App\Models\Contacts;
use App\Models\AgentOccupancy;


class FiveNineController extends Biorev
{
    function getFive9Record($number1 = null){
        $record = FivenineCallLogs::withCount(['disposition'])->whereNotNull('disposition_id')->where("dnis", $number1)->whereNull("agent_name")->count();
        //dd($record);
    }
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function getContactFullList()
    {
        return DB::table('fivenine_lists')->get();
    }
    public function getContactListFromF9()
    {
        $five9 = new Fivenine();
        $data  = $five9->GetRequestCall('GET', 'getListsInfo', '');
        return $data;
    }
    public function getContactList(Request $request)
    {
        if($request->input('search')){
            return DB::table("fivenine_lists")->where('name', "like", "%".$request->input('search')."%")->orderBy("name")->paginate($request->input("recordPerPage"));
        }else{
            return DB::table("fivenine_lists")->orderBy("name")->paginate($request->input("recordPerPage"));
        }
        
    }

    public function getCampaignList()
    {
        $five9 = new Fivenine();
        $data  = $five9->GetRequestCall('GET', 'getCampaigns', '');
        return $data;
    }

    public function getCountryList()
    {
        return ['countries' => Settings::where('id', '=', 39)->first()];
    }

    public function report(Request $request)
    {        
        if($request->input('dateRange')):
            $exp = explode(',',$request->input('dateRange'));
            if($exp[0] == $exp[1]):
                $end = $exp[0];
                $start = date("Y-m-d", strtotime($exp[0])- 3600*24);
            else:
                $start = $exp[0];
                $end = $exp[1];
            endif;
        else:
            $start = date("Y-m-d", strtotime("-6 years")).'T'.date("H:i:s", strtotime("-6 years"));
            $end = date("Y-m-d", strtotime("now")).'T'.date("H:i:s", strtotime("now"));
        endif;
        $postFields = '<env:Envelope xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:tns="http://service.admin.ws.five9.com/" xmlns:env="http://schemas.xmlsoap.org/soap/envelope/"
        xmlns:ins0="http://jaxb.dev.java.net/array">
        <env:Body>
            <tns:runReport>
                <folderName>My Reports</folderName>
                <reportName>List All '.$request->input("listname").'</reportName>
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
        $return = json_decode($json,true); //dd($return["envBody"]["envFault"]);
        if(isset($return["envBody"]["envFault"])):
            return ['restults' => 'fault'];
        else:
            return [
                'startLength' => strpos($response, '<return>'),
                'endLength' => strrpos($response, '</return>'),
                'serverResponse' => $response,
                'idLength' => strrpos($response, '</return>') - strpos($response, '<return>'),
                'id' => substr($response, strpos($response, '<return>')+8, strrpos($response, '</return>') - strpos($response, '<return>')-8)
            ];
        endif;
    }
    
    public function getReportResponse($id = null)
    {
        $curl = curl_init();
        for ($i=0; $i < 10; $i++) {
            sleep(1);     
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
                break;
            }
        }
        curl_close($curl);
        return ['result' => true];
    }

    public function getReportResult($id = null)
    {
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
        $dispositionArray = [];
        $dispositionCounter = 0;
        $counter = 0;
        $allRecords = [];
        if(isset($return["envBody"]["ns2getReportResultResponse"]["return"]["records"])):
            $records = $return["envBody"]["ns2getReportResultResponse"]["return"]["records"]; 
            
            $contactIdIndex = array_search('CONTACT_ID', $header);
            foreach($records as $key => $value):
                $contactIdArray = array_column($allRecords, 'CONTACT_ID');
                $contactId = $value["values"]["data"][$contactIdIndex];
                if(!in_array($contactId, $contactIdArray)):
                    foreach($value["values"]["data"] as $kkey => $vvalue):
                        if(gettype($vvalue) == 'string'):
                            if(in_array($header[$kkey], ['record_id', 'number1', 'number2', 'number3', 'dial_attempts', 'CONTACT_ID'])):
                                if(intval($vvalue) == 0):
                                    $allRecords[$counter][$header[$kkey]] = null;
                                else:
                                    $allRecords[$counter][$header[$kkey]] = intval($vvalue);                                    
                                endif;
                            else:
                                if( $header[$kkey] == 'company' ):
                                    if($vvalue === '' || $vvalue === 'None' || $vvalue === 0 || $vvalue === '0' || $vvalue === null):
                                        $allRecords[$counter][$header[$kkey]] = 'NO';
                                    else:
                                        $allRecords[$counter][$header[$kkey]] = strtoupper($vvalue);
                                    endif;
                                    //$allRecords[$counter][$header[$kkey]] = gettype($vvalue);
                                else:
                                    $allRecords[$counter][$header[$kkey]] = strtoupper($vvalue);

                                endif;
                            endif;
                        else:
                            $allRecords[$counter][$header[$kkey]] = null;
                        endif;
                    endforeach;  
                    $counter++;   
                endif;   
            endforeach;
        endif;
        $dispositionArray = array_unique(array_column($allRecords, 'Last_Dispo'));
        sort($dispositionArray);
        $dispositionArrayNew = [];
        //$dispositionArrayNew[0] = 'All';
        $dispositionArrayCounter = 0;
        foreach($dispositionArray as $value):
            if($value != null):
                $dispositionArrayNew[$dispositionArrayCounter++] = $value;
            endif;
        endforeach;
        if(array_key_exists("ns2getReportResultResponse", $return['envBody'])):
            return [
                    "headerOld" => $headerOld,
                    "header" => $header, 
                    "records" => $allRecords,  
                    "recordsCount" => count($allRecords), 
                    "dispositionArray" => $dispositionArrayNew,
                ];
        else:
            return ['message' => "Please try again"];
        endif;
    }

    public function deleteFromList(Request $request){
        //dd($request->all());
        $listName = $request->input("listName");
        $number1 = $request->input("number1");
        $curl = curl_init();
        $setting1 = Settings::where('id', '=', 37)->first();
        $setting2 = Settings::where('id', '=', 38)->first();
        $username = $setting1->value;
        $password = $setting2->value;
        $code = base64_encode($username.':'.$password);
        $postfields = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.admin.ws.five9.com/">
            <soapenv:Header/>
                <soapenv:Body>
                <ser:deleteFromList>
                    <!--Optional:-->
                    <listName>'.$listName.'</listName>
                    <!--Optional:-->
                    <listDeleteSettings>
                        <!--Optional:-->
                        <allowDataCleanup>False</allowDataCleanup>
                        <!--Optional:-->
                        <failOnFieldParseError>False</failOnFieldParseError>
                        <!--Zero or more repetitions:-->
                        <fieldsMapping>
                            <columnNumber>1</columnNumber>
                            <!--Optional:-->
                            <fieldName>number1</fieldName>
                            <key>true</key>
                        </fieldsMapping>
                        <!--Optional:-->
                        <reportEmail>?</reportEmail>
                        <!--Optional:-->
                        <separator>?</separator>
                        <skipHeaderLine>True</skipHeaderLine>
                        <!--Optional:-->
                        <listDeleteMode>DELETE_ALL</listDeleteMode>
                    </listDeleteSettings>
                    <!--Optional:-->
                    <importData>
                        <!--Zero or more repetitions:-->
                        <values>
                            <!--Zero or more repetitions:-->
                            <item>'.$number1.'</item>
                        </values>
                    </importData>
                </ser:deleteFromList>
                </soapenv:Body>
            </soapenv:Envelope>';
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.five9.com/wsadmin/v4/AdminWebService',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => $postfields,
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
        sleep(1);
        if(isset($return["envBody"]["ns2deleteFromListResponse"]["return"]["identifier"])):
            return ["status" => "success", 'id' => $return["envBody"]["ns2deleteFromListResponse"]["return"]["identifier"]];
        else:
            return ["status" => "fail"];
        endif;
    }

    public function deleteSelectedFromList(Request $request){
        //dd($request->all());
        $efields = '';
        $cfields = [];
        $ck = 0;
        $header = $request->input("header");
        $headerOld = $request->input("headerOld");
        $listname = $request->input("listname");
        $fdata = $request->input("fdata");
        $data = [];
        $count = 0;
        foreach($fdata as $value):
            foreach($value as $kkey => $vvalue):
                    $data[$count][$header[array_search($kkey, $headerOld)]] = $vvalue;
            endforeach;
            $count++;
        endforeach;
        foreach (array_filter($header) as $key => $value) {
            if( ($value != '') && ($value != "country")  && ($value != "LIST NAME")  && ($value != "Dial_Attempts")  && ($value != "Last_Dispo")  && ($value != "DIAL ATTEMPTS")  && ($value != "CONTACT ID") ) {
                if($value == 'number1'):
                    $efields.='<fieldsMapping><columnNumber>'.(++$ck).'</columnNumber><fieldName>'.$value.'</fieldName><key>true</key></fieldsMapping>';
                else:
                    $efields.='<fieldsMapping><columnNumber>'.(++$ck).'</columnNumber><fieldName>'.$value.'</fieldName><key>false</key></fieldsMapping>';
                endif;
                $cfields[] = $value; 
            }
        }
        $cfields = implode(',', $cfields);
        $edata = '';
        foreach ($data as $dkey => $dvalue) {
            if($dvalue['number1'] != 0){
                foreach($header as $key => $value) {
                    if( ($value != '') && ($value != "country") && ($value != "Dial_Attempts") && ($value != "Last_Dispo") && ($value != "DIAL ATTEMPTS") && ($value != "CONTACT ID") ) {
                        $sal = (isset($dvalue[$value]))?$dvalue[$value]:'0';
                        $sal = str_replace('&', ' and ', $sal);
                        $sal = str_replace('(', '', $sal);
                        $sal = str_replace(')', '', $sal);
                        $sal = str_replace('.', '', $sal);
                        $sal = str_replace("'", ' ', $sal);
                        $edata .= $sal.',';
                    }
                }
            }
            $edata.='&#xA;';
        }
        //dd($edata);
        $curl = curl_init();
        $deleteFromListCsv = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.admin.ws.five9.com/">
            <soapenv:Header/>
                <soapenv:Body>
                <ser:deleteFromListCsv>
                    <!--Optional:-->
                    <listName>'.$listname.'</listName>
                    <!--Optional:-->
                    <listDeleteSettings>
                        <!--Optional:-->
                        <allowDataCleanup>False</allowDataCleanup>
                        <failOnFieldParseError>False</failOnFieldParseError>
                        <!--Zero or more repetitions:-->
                        '.$efields.'
                        <reportEmail>anandprakash@biorev.studio</reportEmail>
                        <separator>,</separator>
                        <skipHeaderLine>false</skipHeaderLine>
                        <!--Optional:-->
                        <listDeleteMode>DELETE_ALL</listDeleteMode>
                    </listDeleteSettings>
                    <!--Optional:-->
                    <csvData>'.$edata.'</csvData>
                </ser:deleteFromListCsv>
                </soapenv:Body>
            </soapenv:Envelope>';
        //dd($deleteFromListCsv);
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
            CURLOPT_POSTFIELDS => $deleteFromListCsv,
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
        sleep(1);
        if(isset($return["envBody"]["ns2deleteFromListCsvResponse"]["return"]["identifier"])):
            return ["status" => "success", 'id' => $return["envBody"]["ns2deleteFromListCsvResponse"]["return"]["identifier"]];
        else:
            return ["status" => "fail"];
        endif;
    }

    public function transferContacts(Request $request)
    {
        $efields = '';
        $cfields = [];
        $ck = 0;
        $header = $request->input("header");
        $headerOld = $request->input("headerOld");
        $listname1 = $request->input("listname1");
        $fdata = $request->input("fdata");
        $data = [];
        $count = 0;
        foreach($fdata as $value):
            foreach($value as $kkey => $vvalue):
                $data[$count][$header[array_search($kkey, $headerOld)]] = $vvalue;
            endforeach;
            $count++;
        endforeach;
        foreach (array_filter($header) as $key => $value) {
            if( ($value != '') && ($value != "country")  && ($value != "LIST NAME")  && ($value != "Last_Dispo")  && ($value != "DIAL ATTEMPTS")  && ($value != "CONTACT ID") ) {
                if($value == 'number1') : $k = true; else: $k = false; endif;
                if(in_array($value,  ['stage', 'tag'])):
                    $efields.='<fieldsMapping><columnNumber>'.(++$ck).'</columnNumber><fieldName>'.ucwords($value).'</fieldName><key>'.$k.'</key></fieldsMapping>';
                    $cfields[] = ucwords($value);
                else:
                    $efields.='<fieldsMapping><columnNumber>'.(++$ck).'</columnNumber><fieldName>'.$value.'</fieldName><key>'.$k.'</key></fieldsMapping>';
                    $cfields[] = $value; 
                endif;
            }
        }
        $cfields = implode(',', $cfields);
        $edata = '';
        foreach ($data as $dkey => $dvalue) : 
            if($dvalue['number1'] != 0): //dd($header);
                foreach($header as $key => $value):                    
                    if( ($value != '') && ($value != "country") && ($value != "Last_Dispo") && ($value != "DIAL ATTEMPTS") && ($value != "CONTACT ID") ) : 
                        $sal = (isset($dvalue[$value]))?$dvalue[$value]:'0';
                        $sal = str_replace('&', ' and ', $sal);
                        $sal = str_replace('(', '', $sal);
                        $sal = str_replace(')', '', $sal);
                        $sal = str_replace('.', '', $sal);
                        $sal = str_replace("'", ' ', $sal);
                        $edata .= $sal.',';
                    endif;
                endforeach;
            endif;
            $edata.='&#xA;';
        endforeach;
        //dd($edata);
        $postFields = '<env:Envelope xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:ser="http://service.admin.ws.five9.com/" xmlns:env="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ins0="http://jaxb.dev.java.net/array">
        <env:Header/>
        <env:Body>
            <ser:addToListCsv>
                <listName>'.$listname1.'</listName>
                <listUpdateSettings>
                    '.$efields.'
                    <reportEmail>anandprakash@biorev.studio</reportEmail>
                    <separator>,</separator>
                    <skipHeaderLine>false</skipHeaderLine>
                    <cleanListBeforeUpdate>false</cleanListBeforeUpdate>
                    <crmAddMode>ADD_NEW</crmAddMode>
                    <crmUpdateMode>UPDATE_ALL</crmUpdateMode>
                    <listAddMode>ADD_ALL</listAddMode>
                </listUpdateSettings>
                <csvData>'.$edata.'</csvData>
            </ser:addToListCsv>
        </env:Body>
        </env:Envelope>';
        //dd($postFields);
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
                'Content-Type: application/xml',
                'Cookie: Authorization=Bearer-ebfa8d5a-ba2a-11eb-abfe-00505fca8def; apiRouteKey=SCLnAPIc6f; app_key=web-ui; farmId=182; uiRouteKey=SCLnUI5551'
            ),
        ));
        $response = curl_exec($curl);
        $this->deleteSelectedFromList($request);
        return $this->__XMLtoJSON($response, 'addToListCsv');
    }

    public function agentReport(Request $request)
    {
        return ['results' => Agents::orderBy('name')->get()];
    }

    public function callReport(Request $request)
    {       
        //dd($request->input('dateRange')); 
        if($request->input('dateRange')):
            $s = explode("T", $request->input('dateRange.startDate')); 
            //dd($request->input('dateRange.startDate'));
            $e = explode("T", $request->input('dateRange.endDate'));
            $startDate = '';
            $endDate = '';
            $startFullDate = '';
            $endFullDate = '';

            if($s[1] == $e[1]):
                //date coming form calender date selection
                if($s[0] == $e[0]):
                    // single date selected
                    //"2021-07-23::2021-07-23" today
                    //"2021-07-22::2021-07-22"  yesterday
                    //print_r('single date selected');
                    $startDate = $s[0];
                    $endDate = $e[0];
                    $startFullDate = $startDate.'T00:00:00';
                    $endFulllDate = $endDate.'T23:59:59';
                else:
                    //"2021-06-30::2021-07-31" this month
                    //"2021-06-01::2021-06-30" last month
                    //print_r('multiple date selected');
                    $startDate = date("Y-m-d", strtotime($s[0]) - 24*3600);
                    $endDate = $e[0];
                    $startFullDate = $startDate.'T23:59:59';
                    $endFulllDate = $endDate.'T23:59:59';
                endif;
            else:
                //date coming from calender date-tab selection
                //print_r('calender tab selection:');
                if(strtotime($e[0]) - strtotime($s[0]) == 24*3600):
                    //"2021-07-22::2021-07-23" today
                    //"2021-07-21::2021-07-22"  yesterday
                    //print_r('same day');
                    $endDate = $startDate = $e[0];
                    $startFullDate = $startDate.'T00:00:00';
                    $endFulllDate = $endDate.'T23:59:59';
                else:
                    //"2021-06-30::2021-07-31"  this month
                    //"2020-12-31::2021-12-31"  this year
                    //"2021-05-31::2021-06-30"  last month
                    //print_r('many day');
                    $startDate = $s[0];
                    $endDate = $e[0];
                    $startFullDate = $startDate.'T23:59:59';
                    $endFulllDate = $endDate.'T23:59:59';
                endif;
            endif;
            
            //dd($startFullDate."::".$endFulllDate);
        else:
            $startFullDate = date("Y-m-d", strtotime("now")).'T00:00:00';
            $endFulllDate = date("Y-m-d", strtotime("now")).'T23:59:59';
        endif;
        $folderName = Settings::where('name', '=', 'five9_call_report_name_folder_name')->first();
        $reportName = Settings::where('name', '=', 'five9_call_report_name_report_name')->first();

        $postFields = '<env:Envelope xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:tns="http://service.admin.ws.five9.com/" xmlns:env="http://schemas.xmlsoap.org/soap/envelope/"
        xmlns:ins0="http://jaxb.dev.java.net/array">
        <env:Body>
            <tns:runReport>
                <folderName>'.$folderName["value"].'</folderName>
                <reportName>'.$reportName["value"].'</reportName>
                    <criteria>
                        <time>
                        <end>'.$endFulllDate.'</end>
                        <start>'.$startFullDate.'</start>
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
        //dd($code);
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
        $response = curl_exec($curl); //dd($response);
        curl_close($curl); 
        $xml = $response;
        // SimpleXML seems to have problems with the colon ":" in the <xxx:yyy> response tags, so take them out
        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $xml);
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $return = json_decode($json,true); //dd($return["envBody"]["envFault"]);
        if(isset($return["envBody"]["envFault"])):
            return ['restults' => 'fault'];
        else:
            return [
                'startLength' => strpos($response, '<return>'),
                'endLength' => strrpos($response, '</return>'),
                'serverResponse' => $response,
                'idLength' => strrpos($response, '</return>') - strpos($response, '<return>'),
                'id' => substr($response, strpos($response, '<return>')+8, strrpos($response, '</return>') - strpos($response, '<return>')-8)
            ];
        endif;
    }

    public function callReportResult(Request $request)
    {
        //dd($request->all());
        $id = $request->input("id");
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
        $listRecords = [];
        $header = $return["envBody"]["ns2getReportResultResponse"]["return"]["header"]["values"]["data"];
        $headerArray = [];
        foreach($header as $key => $hvalue):
            $headerArray[$key] = str_replace('/', '', implode("_", explode(" ", strtolower($hvalue))));
        endforeach;
        if(isset($return["envBody"]["ns2getReportResultResponse"]["return"]["records"])):
            $records = [];
            $data = $return["envBody"]["ns2getReportResultResponse"]["return"]["records"];
            $recordCount = 0;
            $dateWiseArray = [];
            $dateCounter = 0;
            
            $agentId = $request->input("agent_id");
            
            if( $request->input("agent_id") ):
                $newdata = [];
                foreach($data as $key => $value):
                    if(in_array($value["values"]["data"]["16"], $agentId)):
                        $newdata[$key] = $value;
                    endif;
                endforeach;
                $records = $newdata;
            else:
                $records = $data;
            endif;
            foreach($records as $key => $value):
                $d = [];
                foreach($header as $key => $hvalue):
                    $d[$headerArray[$key]] = $value["values"]["data"][$key];
                endforeach;
                //echo '<pre>'; print_r($d); echo '</pre>';
                if($d["login_time"] && $d["not_ready_time"]):
                    $lt = explode(":", $d["login_time"]);
                    $loginTime = $lt[0]*3600 + $lt[1]*60 + $lt[2];
                    $nrt = explode(":", $d["not_ready_time"]);
                    $notReadyTime = $nrt[0]*3600 + $nrt[1]*60 + $nrt[2];
                    $diff = $loginTime - $notReadyTime;

                    $llhours = intval($diff/3600);
                    $llm = $diff - $llhours*3600;
                    $llminutes = intval($llm/60);
                    $lls = $diff - $llhours*3600 - $llminutes*60;

                    $llhours = ($llhours >= 10)?$llhours:'0'.$llhours;
                    $llminutes = ($llminutes >= 10)?$llminutes:'0'.$llminutes;
                    $lls = ($lls >= 10)?$lls:'0'.$lls;                
                    $d["login_less_not_ready"] = $llhours.":".$llminutes.":".$lls;
                endif;
                if( isset($dateWiseArray[$d["date"]]) ):
                    $dateWiseArray[$d["date"]][$dateCounter] = $d;
                else:
                    $dateWiseArray[$d["date"]][$dateCounter] = $d;
                endif;
                $dateCounter++;
                $listRecords[$recordCount] = $d;
                $recordCount++;
            endforeach;
        else:
            return ["results" => false];
        endif;
        $sumOfRecords = [];
        foreach($dateWiseArray as $key => $value):
            $totalLoginTime = 0;
            $totalNotReadyTime = 0;
            $averageNotReadyTime = 0;
            $totalNotReadyCount = 0;
            $totalCallsCount = 0;
            $totalOnCallTime = 0;
            $averageOnCallTime = 0;
            $totalTalkTime = 0;
            $averageTalkTime = 0;
            $totalAfterCallWorkTime = 0;
            $averageAfterCallWorkTime = 0;
            $totalLoginLessNotReady = 0;
            $averageTotalLoginLessNotReady = 0;
            foreach($value as $tkey => $tvalue):
                $loginTime = explode(":", $tvalue["login_time"]);
                $totalLoginTime += intval($loginTime[0])*3600 + intval($loginTime[1])*60 + intval($loginTime[2]);
                if(array_key_exists("login_less_not_ready", $tvalue) && $tvalue["login_less_not_ready"]):
                    $loginLessNotReady = explode(":", $tvalue["login_less_not_ready"]);
                    $totalLoginLessNotReady += intval($loginLessNotReady[0])*3600 + intval($loginLessNotReady[1])*60 + intval($loginLessNotReady[2]);
                endif;
                if(array_key_exists("not_ready_time", $tvalue) && $tvalue["not_ready_time"] ):
                    $notReadyTime = explode(":", $tvalue["not_ready_time"]);
                    $totalNotReadyTime += intval($notReadyTime[0])*3600 + intval($notReadyTime[1])*60 + intval($notReadyTime[2]);
                endif;
                $totalNotReadyCount += $tvalue["not_ready_time_count"];
                $totalCallsCount += $tvalue["calls_count"];
                if(array_key_exists("on_call_time", $tvalue) && $tvalue["on_call_time"] ):
                    $onCallTime = explode(":", $tvalue["on_call_time"]);
                    $totalOnCallTime += intval($onCallTime[0])*3600 + intval($onCallTime[1])*60 + intval($onCallTime[2]);
                endif;
                if(array_key_exists("talk_time", $tvalue) && $tvalue["talk_time"] ):
                    $talkTime = explode(":", $tvalue["talk_time"]);
                    $totalTalkTime += intval($talkTime[0])*3600 + intval($talkTime[1])*60 + intval($talkTime[2]);
                endif;

                //echo '<pre>'; print_r($tvalue["after_call_work_time"]); echo '</pre>';
                if(array_key_exists("after_call_work_time", $tvalue) && $tvalue["after_call_work_time"]):
                    $afterCallWorkTime = explode(":", $tvalue["after_call_work_time"]);
                    $totalAfterCallWorkTime += intval($afterCallWorkTime[0])*3600 + intval($afterCallWorkTime[1])*60 + intval($afterCallWorkTime[2]); 
                endif;
            endforeach;
            // Login Time
            $lhours = intval($totalLoginTime/3600);
            $lm = $totalLoginTime - $lhours*3600;
            $lminutes = intval($lm/60);
            $ls = $totalLoginTime - $lhours*3600 - $lminutes*60;
            $lhours = ($lhours >= 10)?$lhours:'0'.$lhours;
            $lminutes = ($lminutes >= 10)?$lminutes:'0'.$lminutes;
            $ls = ($ls >= 10)?$ls:'0'.$ls;

            $sumOfRecords[$key]['total'] = 'total';
            $sumOfRecords[$key]['login_time'] = $lhours.':'.$lminutes.':'.$ls;
            // Login Less Not Rady
            $llnrhours = intval($totalLoginLessNotReady/3600);
            $llnrm = $totalLoginLessNotReady - $llnrhours*3600;
            $llnrminutes = intval($llnrm/60);
            $llnrs = $totalLoginLessNotReady - $llnrhours*3600 - $llnrminutes*60;
            $llnrhours = ($llnrhours >= 10)?$llnrhours:'0'.$llnrhours;
            $llnrminutes = ($llnrminutes >= 10)?$llnrminutes:'0'.$llnrminutes;
            $llnrs = ($llnrs >= 10)?$llnrs:'0'.$llnrs;

            $sumOfRecords[$key]['login_less_not_ready'] = $llnrhours.':'.$llnrminutes.':'.$llnrs;
            //
            $nrthours = intval($totalNotReadyTime/3600);
            $nrtm = $totalNotReadyTime - $nrthours*3600;
            $nrtminutes = intval($nrtm/60);
            $nrts = $totalNotReadyTime - $nrthours*3600 - $nrtminutes*60;

            $nrthours = ($nrthours >= 10)?$nrthours:'0'.$nrthours;
            $nrtminutes = ($nrtminutes >= 10)?$nrtminutes:'0'.$nrtminutes;
            $nrts = ($nrts >= 10)?$nrts:'0'.$nrts;

            $sumOfRecords[$key]["not_ready_time_count"] = $totalNotReadyCount;
            $sumOfRecords[$key]["not_ready_time"] = $nrthours.':'.$nrtminutes.':'.$nrts;
            $sumOfRecords[$key]["calls_count"] = $totalCallsCount;

            if($totalNotReadyCount == 0):
                $sumOfRecords[$key]["average_not_ready_time"] = '00:00:00';
            else:
                $averageNotReadyTime = intval($totalNotReadyTime/$totalNotReadyCount);
                $anrthours = intval($averageNotReadyTime/3600);
                $anrtm = $averageNotReadyTime - $anrthours*3600;
                $anrtminutes = intval($anrtm/60);
                $anrts = $averageNotReadyTime - $anrthours*3600 - $anrtminutes*60;
    
                $anrthours = ($anrthours >= 10)?$anrthours:'0'.$anrthours;
                $anrtminutes = ($anrtminutes >= 10)?$anrtminutes:'0'.$anrtminutes;
                $anrts = ($anrts >= 10)?$anrts:'0'.$anrts;
    
                $sumOfRecords[$key]["average_not_ready_time"] = $anrthours.':'.$anrtminutes.':'.$anrts;
            endif;

            //On Call Time
            $octhours = intval($totalOnCallTime/3600);
            $octm = $totalOnCallTime - $octhours*3600;
            $octminutes = intval($octm/60);
            $octs = $totalOnCallTime - $octhours*3600 - $octminutes*60;

            $octhours = ($octhours >= 10)?$octhours:'0'.$octhours;
            $octminutes = ($octminutes >= 10)?$octminutes:'0'.$octminutes;
            $octs = ($octs >= 10)?$octs:'0'.$octs;

            $sumOfRecords[$key]["on_call_time"] = $octhours.':'.$octminutes.':'.$octs;
            //dd($octhours.':'.$octminutes.':'.$octs);
            //Average On Call Time
            if($totalCallsCount == 0):
                $sumOfRecords[$key]["average_on_call_time"] = '00:00:00';'';
            else:
                $averageOnCallTime = intval($totalOnCallTime/$totalCallsCount);
                $aocthours = intval($averageOnCallTime/3600);
                $aoctm = $averageOnCallTime - $aocthours*3600;
                $aoctminutes = intval($aoctm/60);
                $aocts = $averageOnCallTime - $aocthours*3600 - $aoctminutes*60;

                $aocthours = ($aocthours >= 10)?$aocthours:'0'.$aocthours;
                $aoctminutes = ($aoctminutes >= 10)?$aoctminutes:'0'.$aoctminutes;
                $aocts = ($aocts >= 10)?$aocts:'0'.$aocts;

                $sumOfRecords[$key]["average_on_call_time"] = $aocthours.':'.$aoctminutes.':'.$aocts;
            endif;
            //dd($aocthours.':'.$aoctminutes.':'.$aocts);
            //Talk Time
            $ttthours = intval($totalTalkTime/3600);
            $tttm = $totalTalkTime - $ttthours*3600;
            $tttminutes = intval($tttm/60);
            $ttts = $totalTalkTime - $ttthours*3600 - $tttminutes*60;

            $ttthours = ($ttthours >= 10)?$ttthours:'0'.$ttthours;
            $tttminutes = ($tttminutes >= 10)?$tttminutes:'0'.$tttminutes;
            $ttts = ($ttts >= 10)?$ttts:'0'.$ttts;

            $sumOfRecords[$key]["talk_time"] = $ttthours.':'.$tttminutes.':'.$ttts;
            //Average Talk Time
            if($totalCallsCount == 0):
                $sumOfRecords[$key]["average_talk_time"] = '00:00:00';
            else:
                $averageTalkTime = intval($totalTalkTime/$totalCallsCount);
                $aathours = intval($averageTalkTime/3600);
                $aatm = $averageTalkTime - $aathours*3600;
                $aatminutes = intval($aatm/60);
                $aats = $averageTalkTime - $aathours*3600 - $aatminutes*60;
    
                $aathours = ($aathours >= 10)?$aathours:'0'.$aathours;
                $aatminutes = ($aatminutes >= 10)?$aatminutes:'0'.$aatminutes;
                $aats = ($aats >= 10)?$aats:'0'.$aats;
    
                $sumOfRecords[$key]["average_talk_time"] = $aathours.':'.$aatminutes.':'.$aats;
            endif;

            //After Call Work Time
            $tacthours = intval($totalAfterCallWorkTime/3600);
            $tactm = $totalAfterCallWorkTime - $tacthours*3600;
            $tactminutes = intval($tactm/60);
            $tacts = $totalAfterCallWorkTime - $tacthours*3600 - $tactminutes*60;

            $tacthours = ($tacthours >= 10)?$tacthours:'0'.$tacthours;
            $tactminutes = ($tactminutes >= 10)?$tactminutes:'0'.$tactminutes;
            $tacts = ($tacts >= 10)?$tacts:'0'.$tacts;

            $sumOfRecords[$key]["after_call_work_time"] = $tacthours.':'.$tactminutes.':'.$tacts;
            //Average After Call Work Time
            if($totalCallsCount == 0):
                $sumOfRecords[$key]["average_after_call_work_time"] = '00:00:00';       
            else:
                $averageAfterCallWorkTime = intval($totalAfterCallWorkTime/$totalCallsCount);
                $acwthours = intval($averageAfterCallWorkTime/3600);
                $acwtm = $averageAfterCallWorkTime - $acwthours*3600;
                $acwtminutes = intval($acwtm/60);
                $acwts = $averageAfterCallWorkTime - $acwthours*3600 - $acwtminutes*60;
    
                $acwthours = ($acwthours >= 10)?$acwthours:'0'.$acwthours;
                $acwtminutes = ($acwtminutes >= 10)?$acwtminutes:'0'.$acwtminutes;
                $acwts = ($acwts >= 10)?$acwts:'0'.$acwts;
    
                $sumOfRecords[$key]["average_after_call_work_time"] = $acwthours.':'.$acwtminutes.':'.$acwts;            
            endif;
        endforeach;
        $allRecords = [];
        foreach($dateWiseArray as $key => $value):
            $count = 0;
            foreach($value as $tkey => $tvalue):
                $allRecords[$key][$count++] = $tvalue;
            endforeach;
            if($agentId && (count($agentId) == 1)):

            else:
                $allRecords[$key][count($value)] = $sumOfRecords[$key];
            endif;
        endforeach;
        if(array_key_exists("ns2getReportResultResponse", $return['envBody'])):
            return [
                    "header" => $header, 
                    "records" => $listRecords,  
                    "headerArray" => $headerArray,
                    "dateWiseArray" => $dateWiseArray,
                    "sumOfRecords" => $sumOfRecords,
                    "allRecords" => $allRecords,
                    "recordsCount" => count($allRecords), 
                ];
        else:
            return ['message' => "Please try again"];
        endif;
    }

    private function __XMLtoJSON($response, $name) {
        $var = 'ns2'.$name.'Response';
        $xml = $response;
        // SimpleXML seems to have problems with the colon ":" in the <xxx:yyy> response tags, so take them out
        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $xml);
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $return = json_decode($json,true);
        if(array_key_exists($var, $return['envBody'])):
            return ['message' => $return['envBody']["$var"]['return'], 'status' => 'success'];
        else:
            return ['message' => $return['envBody']['envFault']['faultstring'], 'status' => 'error'];
        endif;
    }

    public function getExportCallContacts(Request $request){
        $header = $request->input("header");
        $memoryToUse = 50*1024*1024*1024*1024;
        $output = fopen('php://temp/maxmemory:'.$memoryToUse, 'r+');
        $columns = array('AGENT NAME',	'LOGIN TIME',	'NOT READY TIME',	'Average NOT READY TIME',	'NOT READY TIME count',	'AVAILABLE TIME (LOGIN LESS NOT READY)',	'CALLS count',	'ON CALL TIME',	'Average ON CALL TIME',	'TALK TIME',	'Average TALK TIME',	'AFTER CALL WORK TIME',	'Average AFTER CALL WORK TIME');
        fputcsv($output, $columns);
        rewind($output);
        header('Content-type: application/octet-stream');
        header('Content-Disposition: attachment; filename="users-report.csv"');
        echo stream_get_contents($output);
        fclose($output);
        die;
    }

    public function getAllList(Request $request)
    {  
        $start = date("Y-m-d", strtotime("-6 years")).'T'.date("H:i:s", strtotime("-6 years"));
        $end = date("Y-m-d", strtotime("now")).'T'.date("H:i:s", strtotime("now"));
        $postFields = '<env:Envelope xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:tns="http://service.admin.ws.five9.com/" xmlns:env="http://schemas.xmlsoap.org/soap/envelope/"
        xmlns:ins0="http://jaxb.dev.java.net/array">
        <env:Body>
            <tns:runReport>
                <folderName>My Reports</folderName>
                <reportName>Biorev All List</reportName>
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
        //dd($code);
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
        //dd($response);
        //$this->GetCallResponse('getSkills', $response);
    }

    public function getAllListResults($id = null)
    {
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
                $header[$key] = strtolower($i);
            else:
                $header[$key] = strtolower($value);
            endif;
        endforeach;
        $dispositionArray = [];
        $dispositionCounter = 0;
        $counter = 0;
        $allRecords = [];
        if(isset($return["envBody"]["ns2getReportResultResponse"]["return"]["records"])):
            $records = $return["envBody"]["ns2getReportResultResponse"]["return"]["records"];
            echo '<pre>'; print_r($records); echo '</pre>';
        endif;
        if(array_key_exists("ns2getReportResultResponse", $return['envBody'])):
            return [
                    
                ];
        else:
            return ['message' => "Please try again"];
        endif;
    }

    public function getContactCampaigns(){
        $setting1 = Settings::where('id', '=', 37)->first();
        $setting2 = Settings::where('id', '=', 38)->first();
        $username = $setting1->value;
        $password = $setting2->value;
        $code = base64_encode($username.':'.$password);
        $postFields = '<env:Envelope xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:ser="http://service.admin.ws.five9.com/" xmlns:env="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ins0="http://jaxb.dev.java.net/array">
        <env:Header/>
        <env:Body>
            <ser:getCampaigns>            
            </ser:getCampaigns>  
        </env:Body>
        </env:Envelope>';

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
        $results = $this->GetCallResponse('getCampaigns', $response);
        $mode = array_unique(array_column($results, 'mode'));
        $state = array_unique(array_column($results, 'state'));
        $type = array_unique(array_column($results, 'type'));
        
        return [
            'results' => $results,
            'mode' => $mode,
            'state' => $state,
            'type' => $type,
        ];
    }

    public function getContactDispositions(){
        $setting1 = Settings::where('id', '=', 37)->first();
        $setting2 = Settings::where('id', '=', 38)->first();
        $username = $setting1->value;
        $password = $setting2->value;
        $code = base64_encode($username.':'.$password);
        $postFields = '<env:Envelope xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:ser="http://service.admin.ws.five9.com/" xmlns:env="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ins0="http://jaxb.dev.java.net/array">
        <env:Header/>
        <env:Body>
            <ser:getDispositions>            
            </ser:getDispositions>  
        </env:Body>
        </env:Envelope>';

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
        $results = $this->GetCallResponse('getDispositions', $response);
        $type = array_unique(array_column($results, 'type'));
        sort($type);
        return [
            'results' => $results,
            'type' => $type,
        ];
    }

    public function getContactSkills(){
        $setting1 = Settings::where('id', '=', 37)->first();
        $setting2 = Settings::where('id', '=', 38)->first();
        $username = $setting1->value;
        $password = $setting2->value;
        $code = base64_encode($username.':'.$password);
        $postFields = '<env:Envelope xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:ser="http://service.admin.ws.five9.com/" xmlns:env="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ins0="http://jaxb.dev.java.net/array">
        <env:Header/>
        <env:Body>
            <ser:getSkills>            
            </ser:getSkills>  
        </env:Body>
        </env:Envelope>';

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
        $results = $this->GetCallResponse('getSkills', $response);
        return [
            'results' => $results,
        ];
    }

    public function updateDisposition(Request $request){
        $disposition = $request->input('disposition');
        $dispositionUpdate = $request->input('dispositionUpdate');
        $setting1 = Settings::where('id', '=', 37)->first();
        $setting2 = Settings::where('id', '=', 38)->first();
        $username = $setting1->value;
        $password = $setting2->value;
        $code = base64_encode($username.':'.$password);
        $postFields = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.admin.ws.five9.com/">
        <soapenv:Header/>
            <soapenv:Body>
            <ser:renameDisposition>
                <!--Optional:-->
                <dispositionName>'.$disposition.'</dispositionName>
                <!--Optional:-->
                <dispositionNewName>'.$dispositionUpdate.'</dispositionNewName>
            </ser:renameDisposition>
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
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => array(
               "Authorization: Basic $code",
                "Content-Type: application/xml",
                "Cookie: clientId=C4A452B18E5646FA9ACBE8A819154296; Authorization=Bearer-ebfa8d5a-ba2a-11eb-abfe-00505fca8def; apiRouteKey=SCLnAPIc6f; app_key=web-ui; farmId=182; uiRouteKey=SCLnUI5551"
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        
        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response);
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $return = json_decode($json,true);
        if(array_key_exists('envFault', $return['envBody'])) {
            return ['msg' => $return['envBody']['envFault']['faultstring'], 'status' => 'fail'];
        } else {
            return ['msg' => 'Disposition updated successfully !! !!', 'status' => 'success'];
        }
    }

    public function getCampaignLists(Request $request){
        $disposition = $request->input('disposition');
        $dispositionUpdate = $request->input('dispositionUpdate');
        $setting1 = Settings::where('id', '=', 37)->first();
        $setting2 = Settings::where('id', '=', 38)->first();
        $username = $setting1->value;
        $password = $setting2->value;
        $code = base64_encode($username.':'.$password);
        $campaign = $request->input('campaign');
        $postFields = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.admin.ws.five9.com/">
        <soapenv:Header/>
            <soapenv:Body>
            <ser:getListsForCampaign>
                <!--Optional:-->
                <campaignName>'.$campaign.'</campaignName>
            </ser:getListsForCampaign>
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
        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response);
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $return = json_decode($json,true);
        $results = [];
        foreach( $return['envBody']['ns2getListsForCampaignResponse']['return'] as $key => $value):
            if(is_array($value)):
                return ['results' => $return['envBody']['ns2getListsForCampaignResponse']['return'], 'status' => 'success'];
            else:
                $results[0][$key] = $value;
            endif;
        endforeach;
        return ['results' => $results, 'status' => 'success'];
    }

    public function deleteListFromCampaign(Request $request){
        $setting1 = Settings::where('id', '=', 37)->first();
        $setting2 = Settings::where('id', '=', 38)->first();
        $username = $setting1->value;
        $password = $setting2->value;
        $code = base64_encode($username.':'.$password);
        $campaignName = $request->input("campaignName");
        $listName = $request->input("listName");
        $postFields =  '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.admin.ws.five9.com/">
        <soapenv:Header/>
            <soapenv:Body>
            <ser:removeListsFromCampaign>
                <!--Optional:-->
                <campaignName>'.$campaignName.'</campaignName>
                <!--Zero or more repetitions:-->
                <lists>'.$listName.'</lists>
            </ser:removeListsFromCampaign>
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
        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response);
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $return = json_decode($json,true);
        if(array_key_exists('envFault', $return['envBody'])) {
            return ['msg' => 'Please try again ||', 'status' => 'fail'];
        } else {
            return ['msg' => 'List removed successfully !! !!', 'status' => 'success'];
        }
    }

    public function GetCallResponse($api = null, $data = null)
    {
        $xml = $data;
        // SimpleXML seems to have problems with the colon ":" in the <xxx:yyy> response tags, so take them out
        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $xml);
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $return = json_decode($json,true);
        $responsecode = 'ns2'.$api.'Response';
        if(array_key_exists('envFault', $return['envBody'])) {
            return $return['envBody']['envFault']['faultstring'];
        } else {
            return $return['envBody'][$responsecode]['return'];
        }
    }

    //call report 01 
    public function callReportOne(Request $request)
    {       
        //dd($request->all()); 
        if($request->input('dateRange')):
            $s = explode("T", $request->input('dateRange.startDate')); 
            //dd($request->input('dateRange.startDate'));
            $e = explode("T", $request->input('dateRange.endDate'));
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
        else:
            $startFullDate = date("Y-m-d", strtotime("-16 days")).'T23:59:59';
            $endFulllDate = date("Y-m-d", strtotime("now")).'T00:00:00';
            //$endFulllDate = date("Y-m-d", strtotime("now")).'T23:59:59';
        endif;
        $folderName = Settings::where('name', '=', 'five9_call_report_name_folder_name_one')->first();
        $reportName = Settings::where('name', '=', 'five9_call_report_name_report_name_one')->first();
        $postFields = '<env:Envelope xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:tns="http://service.admin.ws.five9.com/" xmlns:env="http://schemas.xmlsoap.org/soap/envelope/"
        xmlns:ins0="http://jaxb.dev.java.net/array">
        <env:Body>
            <tns:runReport>
                <folderName>'.$folderName["value"].'</folderName>
                <reportName>'.$reportName["value"].'</reportName>
                    <criteria>
                        <time>
                        <end>'.$endFulllDate.'</end>
                        <start>'.$startFullDate.'</start>
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
        //dd($code);
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
        $response = curl_exec($curl); //dd($response);
        curl_close($curl); 
        $xml = $response;
        // SimpleXML seems to have problems with the colon ":" in the <xxx:yyy> response tags, so take them out
        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $xml);
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $return = json_decode($json,true); //dd($return["envBody"]["envFault"]);
        if(isset($return["envBody"]["envFault"])):
            return ['restults' => 'fault'];
        else:
            return [
                'startLength' => strpos($response, '<return>'),
                'endLength' => strrpos($response, '</return>'),
                'serverResponse' => $response,
                'idLength' => strrpos($response, '</return>') - strpos($response, '<return>'),
                'id' => substr($response, strpos($response, '<return>')+8, strrpos($response, '</return>') - strpos($response, '<return>')-8)
            ];
        endif;
    }
    public function callReportResultOne(Request $request)
    {
        //dd($request->all());
        $id = $request->input("id");
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
        $listRecords = [];
        
        $header = $return["envBody"]["ns2getReportResultResponse"]["return"]["header"]["values"]["data"];
        $headerArray = [];
        foreach($header as $key => $hvalue):
            $headerArray[$key] = str_replace('/', '', implode("_", explode(" ", strtolower($hvalue))));
        endforeach;
        if(isset($return["envBody"]["ns2getReportResultResponse"]["return"]["records"])):
            $records = [];
            $data = $return["envBody"]["ns2getReportResultResponse"]["return"]["records"];
            $counter = 0;
            $records = [];
            $dial_attempts = [];
            foreach($data as $key => $value){
               $records[$counter] = array_combine($headerArray,$value["values"]["data"]);
                
                $record = array_combine($headerArray,$value["values"]["data"]);
                $records[$counter]["new_timestamp"] = strtotime($record["timestamp"]);
                $dialAttempts = intval($record["dial_attempts"]);
                if($dialAttempts == 0):
                    $records[$counter]["dial_attempts"] = '-';
                else:
                    $records[$counter]["dial_attempts"] = $dialAttempts;
                endif;
                if(!in_array($dialAttempts, $dial_attempts)){
                    $dial_attempts[$dialAttempts] = $dialAttempts;
                }
                $counter++;
            }
            //$dial_attempts = array_unique(array_column($records, 'dial_attempts'));
            $campaign = array_unique(array_column($records, 'campaign'));
            $disposition = array_unique(array_column($records, 'disposition'));
            return [
                "headerArray" => $headerArray,
                "results" => true, 
                'records' => $records, 
                'dial_attempts' => $dial_attempts,
                'campaign' => $campaign,
                'disposition' => $disposition
            ];

        else:
            return ["results" => false];
        endif;
    }
    //get all list data form five-9
    public function dataFromFiveNineReport()
    {
        ini_set('max_execution_time', 3600);
        $listName = 'AC_Builder_open-Click';
        $lists = DB::table("fivenine_lists")->where("status", "=", 0)->orderBy("name")->get();
        foreach($lists as $list){
            $list = get_object_vars($list);
            $listName = $list["name"];
            $listId = $list["id"];
            echo "<pre>"; echo $listName; echo "</pre>";
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
            $return = json_decode($json,true); //dd($return["envBody"]["envFault"]);
            if(isset($return["envBody"]["envFault"])):
                return ['restults' => 'fault'];
            endif;
            $id = substr($response, strpos($response, '<return>')+8, strrpos($response, '</return>') - strpos($response, '<return>')-8);
        
            $curl = curl_init();
            for ($i=0; $i < 10; $i++) {
                sleep(1);     
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
                    break;
                }
            }
            curl_close($curl);
        
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
            $allRecords = []; //dd($return["envBody"]["ns2getReportResultResponse"]["return"]["records"]);
            if(isset($return["envBody"]["ns2getReportResultResponse"]["return"]["records"])):
                $records = $return["envBody"]["ns2getReportResultResponse"]["return"]["records"];
                foreach($records as $key => $value):
                    if(isset($value["data"])){
                        // $record["contact_id"] = is_array($value["data"][0]) ? null : intval($value["data"][0]);
                        // $record["first_name"] = is_array($value["data"][1]) ? null : $value["data"][1];
                        // $record["last_name"] = is_array($value["data"][2]) ? null : $value["data"][2];
                        $record["record_id"] = is_array($value["data"][3]) ? null : intval($value["data"][3]);
                        $record["number1"] = is_array($value["data"][4]) ? null : intval($value["data"][4]);
                        $record["number2"] = is_array($value["data"][5]) ? null : intval($value["data"][5]);
                        $record["number3"] = is_array($value["data"][6]) ? null : intval($value["data"][6]);                        
                    }else{
                        // $record["contact_id"] = is_array($value["values"]["data"][0]) ? null : intval($value["values"]["data"][0]);
                        // $record["first_name"] = is_array($value["values"]["data"][1]) ? null : $value["values"]["data"][1];
                        // $record["last_name"] = is_array($value["values"]["data"][2]) ? null : $value["values"]["data"][2];
                        $record["record_id"] = is_array($value["values"]["data"][3]) ? null : intval($value["values"]["data"][3]);
                        $record["number1"] = is_array($value["values"]["data"][4]) ? null : intval($value["values"]["data"][4]);
                        $record["number2"] = is_array($value["values"]["data"][5]) ? null : intval($value["values"]["data"][5]);
                        $record["number3"] = is_array($value["values"]["data"][6]) ? null : intval($value["values"]["data"][6]);
                    }
                    
                    $count = DB::table("fivenine_list_contacts")->where("fivenine_list_id", "=", $listId)->count();
                    if($count == 0){
                        DB::table("fivenine_list_contacts")->insert([
                            "fivenine_list_id" => $listId,
                            // "contact_id" => $record["contact_id"],
                            "number1" => $record["number1"],
                            "record_id" => $record["record_id"],
                            // "first_name" => $record["first_name"],
                            // "last_name" => $record["last_name"],
                            "number2" => $record["number2"],
                            "number3" => $record["number3"],
                        ]);
                    }
                endforeach;
                $listItemCounter = DB::table("fivenine_list_contacts")->where("fivenine_list_id", "=", $listId)->count();
                DB::table("fivenine_lists")->where("id", "=", $listId)->update([
                    "size" => $listItemCounter,
                    "status" => 1
                ]);
            endif;
        }
    }
    public function getFivenineListDetails($lid  = null){
        $result = get_object_vars(DB::table('fivenine_lists')->where("id", "=", $lid)->first());
        return $result;
    }
    public function getListBasedStages($lid  = null){
        $recordIds = DB::table('fivenine_list_contacts')->where("fivenine_list_id", "=", $lid)->get();
        $data = [];
        $allRecordIds = $recordIds->pluck("record_id");
        foreach($allRecordIds as $value){
            $record = get_object_vars(DB::table("contacts")->where("record_id", $value)->first());
            $data[$value] = intval($record["stage"]);
        }
        $results = [];
        foreach($data as $key => $value){
            //$key => $record_id, value => stage
            if(array_key_exists($value, $results)){
                $results[$value] = $results[$value] + 1;
            }else{
                $results[$value] = 1;
            }
        }
        $resultStage = [];
        foreach($results as $key => $value){
            // $key => stage, $value => count
            $stage = get_object_vars(DB::table("stages")->where("oid", "=", $key)->first());
            $resultStage[] = [
                "count" => $value,
                "stage" => $stage["name"],
                "stageOid" => $key
            ];
        }
        return $resultStage;
    }
    public function getListMatchedData(Request $request){
        ini_set('max_execution_time', 3600);
        $ac_list = DB::table("fivenine_lists")->get();
         $records = $request->records;
        $matched = 0;
        $result = [];
        $matched = [];
        $notMatched = [];
        foreach($ac_list as $valueAcList){
            $valueAcList = get_object_vars($valueAcList);
            $listId = $valueAcList["id"]; 
            $list = get_object_vars(DB::table("fivenine_lists")->where("id", "=", $listId)->first());
            $listRecord = 0;
            $listRecord = DB::table("fivenine_list_contacts")->whereIn("record_id", array_column($records, "record_id"))->where("fivenine_list_id", "=", $listId)->select('record_id')->distinct()->get()->count();
            if($listRecord == 0){
                $notMatched[] = [
                    "name" => $list["name"],
                    "size" => $list["size"],
                    "matched" => 0
                ];
            }else{
                $matched[] = [
                    "name" => $list["name"],
                    "size" => $list["size"],
                    "matched" => $listRecord,
                    "records" => $listRecord
                ];
            }
            $result[] = [
                "name" => $list["name"],
                "size" => $list["size"],
                "matched" => $listRecord
            ];
        }
        return ["result" => $result, "matched" => $matched, "notMatched" => $notMatched];
    }
    public function getListbasedProspects(Request $request)
    {
        $cids = DB::table('fivenine_list_contacts')->where('fivenine_list_id', $request->list_id)->get()->pluck('record_id');
        return Contacts::select('contacts.id', 'contacts.record_id', 'contacts.first_name', 'contacts.last_name', 'contacts.title', 'contacts.company', 'contacts.mobilePhones', 'contacts.workPhones', 'contacts.homePhones', 'contacts.emails', 'contacts.outreach_touched_at', 'contacts.outreach_created_at', 'contacts.last_agent_dispo_time', 'contacts.last_agent', 'contacts.email_delivered', 'contacts.email_opened', 'contacts.email_clicked', 'contacts.email_replied', 'contacts.email_bounced', 'contacts.mcall_attempts', 'contacts.mcall_received', 'contacts.wcall_attempts', 'contacts.wcall_received', 'contacts.hcall_attempts', 'contacts.hcall_received', 'stages.id as stage', 'stages.name as stage_name', 'stages.css as stage_css', 'contacts.dataset','contacts.outreach_tag')->leftjoin('stages', 'stages.oid', '=', 'contacts.stage')->whereIn('record_id', $cids)->orderBy('record_id', 'asc')->paginate($request->recordPerPage);
    }
    
    public function getNumberBasedOnDispositionAndNoneAgent(Request $request){
         $records = $request->records;
        $recordIds = array_column($records, "record_id"); 
        $number1 = array_column($records, "number1"); 
        $number1 = array_filter($number1, function($value) { return !is_null($value) && $value !== ''; });  
        $number2 = array_column($records, "number2"); 
        $number2 = array_filter($number2, function($value) { return !is_null($value) && $value !== ''; });  
        $number3 = array_column($records, "number3"); 
        $number3 = array_filter($number3, function($value) { return !is_null($value) && $value !== ''; });  
        $number = array_merge($number1, $number2, $number3);
        $dispostions = ["Answering Machine", "Fax", "No Answer", "Operator Intercept"];
        $results = [];
        $records = FivenineCallLogs::select('dnis','disposition')->whereIn('dnis', $number)->whereIn('disposition', $dispostions)->get();
        return $records->pluck('dnis');
    }
    
    public function deleteList(Request $request){
        $listName = $request->input("listName");
        $postFields = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.admin.ws.five9.com/">
            <soapenv:Header/>
            <soapenv:Body>
            <ser:deleteList>
                <!--Optional:-->
                <listName>'.$listName.'</listName>
            </ser:deleteList>
            </soapenv:Body>
        </soapenv:Envelope>';
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
                'Content-Type: application/xml',
                'Cookie: Authorization=Bearer-ebfa8d5a-ba2a-11eb-abfe-00505fca8def; apiRouteKey=SCLnAPIc6f; app_key=web-ui; farmId=182; uiRouteKey=SCLnUI5551'
            ),
        ));
        $response = curl_exec($curl);// dd($response);
        curl_close($curl);
        //$results = $this->__XMLtoJSON($response, 'deleteAllFromList');
        $listDetais = get_object_vars(DB::table("fivenine_lists")->where("name", "=", $listName)->first());
        DB::table("fivenine_list_contacts")->where("fivenine_list_id", "=", $listDetais["id"])->delete();
        DB::table("fivenine_lists")->where("name", "=", $listName)->delete();
        return ["status" => "success"];
    }

    public function deleteAllFromList(Request $request){
        $listName = $request->input("listName");
        $postFields = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.admin.ws.five9.com/">
            <soapenv:Header/>
            <soapenv:Body>
            <ser:deleteAllFromList>
                <!--Optional:-->
                <listName>'.$listName.'</listName>
            </ser:deleteAllFromList>
            </soapenv:Body>
        </soapenv:Envelope>';
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
                'Content-Type: application/xml',
                'Cookie: Authorization=Bearer-ebfa8d5a-ba2a-11eb-abfe-00505fca8def; apiRouteKey=SCLnAPIc6f; app_key=web-ui; farmId=182; uiRouteKey=SCLnUI5551'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $results = $this->__XMLtoJSON($response, 'deleteAllFromList');
        if(isset($results["message"]["identifier"])){
            $record = get_object_vars(DB::table("fivenine_lists")->where("name", "LIKE", $listName)->first());
            $id = $record["id"];
            $oldhistory = $record["history"];
            if(is_null($oldhistory)){
                $history = [$results["message"]["identifier"]];
            }else{
                $his = json_decode($oldhistory);
                array_push($his, $results["message"]["identifier"]);
                $history = $his;
            }
            DB::table("fivenine_lists")->where("id", "=", $id)->update([
                "history" => json_encode($history),
            ]);
        }
        $listDetais = get_object_vars(DB::table("fivenine_lists")->where("name", "=", $listName)->first());
        DB::table("fivenine_lists")->where("id", "=", $listDetais["id"])->update([
                    "size" => 0,
                    "updated_at" => date("Y-m-d H:i:s", strtotime("now"))
                ]);
        DB::table("fivenine_list_contacts")->where("fivenine_list_id", "=", $listDetais["id"])->delete();
        return ["status" => "success", "results" => $results];
    }
    public function createNewList($listName = null){
        $curl = curl_init();
        $setting1 = Settings::where('id', '=', 37)->first();
        $setting2 = Settings::where('id', '=', 38)->first();
        $username = $setting1->value;
        $password = $setting2->value;
        $code = base64_encode($username.':'.$password);
        
        $postFields = '<env:Envelope xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:ser="http://service.admin.ws.five9.com/" xmlns:env="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ins0="http://jaxb.dev.java.net/array">
        <env:Header/>
        <env:Body>
            <ser:createList>
                <listName>'.$listName.'</listName>
            </ser:createList>
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
                'Content-Type: application/xml',
                'Cookie: Authorization=Bearer-ebfa8d5a-ba2a-11eb-abfe-00505fca8def; apiRouteKey=SCLnAPIc6f; app_key=web-ui; farmId=182; uiRouteKey=SCLnUI5551'
            ),
        ));
        $response = curl_exec($curl);// dd($response);
        curl_close($curl);
    }
    public function updateList(Request $request){
        ini_set('max_execution_time', 3600);
        $listName = $request->input("listName");
        $list = DB::table("fivenine_lists")->where("name", "=", $listName)->first();
        $list = get_object_vars($list);
            $listName = $list["name"];
            $listId = $list["id"];

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
            $response = curl_exec($curl);//dd($response);
            curl_close($curl);
            $xml = $response;
            // SimpleXML seems to have problems with the colon ":" in the <xxx:yyy> response tags, so take them out
            $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $xml);
            $xml = simplexml_load_string($xml);
            $json = json_encode($xml);
            $return = json_decode($json,true); //dd($return["envBody"]["envFault"]);
            if(isset($return["envBody"]["envFault"])):
                return ['restults' => 'fault'];
            endif;
            $id = substr($response, strpos($response, '<return>')+8, strrpos($response, '</return>') - strpos($response, '<return>')-8);
        
            $curl = curl_init();
            for ($i=0; $i < 10; $i++) {
                sleep(1);     
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
                    break;
                }
            }
            curl_close($curl);
        
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
                    "updated_at" => date("Y-m-d H:i:s", strtotime("now"))
                ]);
            else:
                DB::table("fivenine_lists")->where("id", "=", $listId)->update([
                    "size" => 0,
                    "updated_at" => date("Y-m-d H:i:s", strtotime("now"))
                ]);
                DB::table("fivenine_list_contacts")->where("fivenine_list_id", "=", $listId)->delete();
            endif;
        // }
        return ["status" => "success"];
    }
    public function startCampaign(Request $request){
        $campaignName = $request->input("campaignName");
        $postFields = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.admin.ws.five9.com/">
            <soapenv:Header/>
            <soapenv:Body>
            <ser:startCampaign>
                <!--Optional:-->
                <campaignName>'.$campaignName.'</campaignName>
            </ser:startCampaign>
            </soapenv:Body>
        </soapenv:Envelope>';

        $setting1 = Settings::where('id', '=', 37)->first();
        $setting2 = Settings::where('id', '=', 38)->first();
        $username = $setting1->value;
        $password = $setting2->value;
        $code = base64_encode($username.':'.$password);

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
        $xml = $response;
        // SimpleXML seems to have problems with the colon ":" in the <xxx:yyy> response tags, so take them out
        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $xml);
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $return = json_decode($json,true); 
        //echo "<pre>"; print_r($return); print_r($return["envBody"]["envFault"]["detail"]); echo "</pre>";
        if(isset($return["envBody"]["envFault"])){
            return ["results" => "error", "message" => $return["envBody"]["envFault"]["detail"]["ns1CampaignStateUpdateFault"]];    
        }
        return ["results" => "success"];
    }
    public function stopCampaign(Request $request){
        $campaignName = $request->input("campaignName");
        $postFields = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.admin.ws.five9.com/">
            <soapenv:Header/>
            <soapenv:Body>
            <ser:stopCampaign>
                <!--Optional:-->
                <campaignName>'.$campaignName.'</campaignName>
            </ser:stopCampaign>
            </soapenv:Body>
        </soapenv:Envelope>';

        $setting1 = Settings::where('id', '=', 37)->first();
        $setting2 = Settings::where('id', '=', 38)->first();
        $username = $setting1->value;
        $password = $setting2->value;
        $code = base64_encode($username.':'.$password);

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
        return ["status" => "success"];
    }
    public function addListToCampaign(Request $request){
        $list = $request->input("list");
        $campaign = $request->input("campaign");
        $priority = $request->input("priority") +1;
        $dialingPriority = $request->input("dialingPriority") +1;
        $postFields = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.admin.ws.five9.com/">
            <soapenv:Header/>
            <soapenv:Body>
            <ser:addListsToCampaign>
                <!--Optional:-->
                <campaignName>'.$campaign.'</campaignName>
                <!--Zero or more repetitions:-->
                <lists>
                    <!--Optional:-->
                    <campaignName>'.$campaign.'</campaignName>
                    <!--Optional:-->
                    <dialingPriority>1</dialingPriority>
                    <!--Optional:-->
                    <dialingRatio>1</dialingRatio>
                    <!--Optional:-->
                    <listName>'.$list.'</listName>
                    <!--Optional:-->
                    <priority>'.$priority.'</priority>
                </lists>
            </ser:addListsToCampaign>
            </soapenv:Body>
        </soapenv:Envelope>';

        $setting1 = Settings::where('id', '=', 37)->first();
        $setting2 = Settings::where('id', '=', 38)->first();
        $username = $setting1->value;
        $password = $setting2->value;
        $code = base64_encode($username.':'.$password);

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
        return  ["status" => "success"];
    }
    public function getListHistory(Request $request){
        $listName = $request->input("listName");
        $record = get_object_vars(DB::table("fivenine_lists")->where("name", "like", $listName)->first());
        if($record["history"]){
            $history = json_decode($record["history"]);            
        }else{
            $history = [];
        }
        return $history;
    }
    public function getListImportResult(Request $request){
        $rid = $request->input("rid");
        $postFields = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.admin.ws.five9.com/">
            <soapenv:Header/>
            <soapenv:Body>
            <ser:getListImportResult>
                <!--Optional:-->
                <identifier>
                    <!--Optional:-->
                    <identifier>'.$rid.'</identifier>
                </identifier>
            </ser:getListImportResult>
            </soapenv:Body>
        </soapenv:Envelope>';
        $setting1 = Settings::where('id', '=', 37)->first();
        $setting2 = Settings::where('id', '=', 38)->first();
        $username = $setting1->value;
        $password = $setting2->value;
        $code = base64_encode($username.':'.$password);

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
        return $this->__XMLtoJSON($response, 'getListImportResult');
    }
    public function removeHistoryFromList(Request $request){
        $rid = $request->input("rid");
        $listName = $request->input("listName");
        $record = get_object_vars(DB::table("fivenine_lists")->where("name", "like", $listName)->first());
        $his = json_decode($record["history"]);
        $history = [];
        foreach($his as $value){
            if($value != $rid){
                $history[] = $value;
            }
        }
        //dd($history);
        DB::table("fivenine_lists")->where("name", "like", $listName)->update([
            "history" => json_encode($history)
        ]);
        return ["status" => "success"];
    }
    //agent occupancy functionality
    function getAgentOccupnecyReport(Request $request){
        if($request->input('dateRange')):
            $exp = explode(',',$request->input('dateRange'));
            if($exp[0] == $exp[1]):
                $end = $exp[0];
                $start = date("Y-m-d", strtotime($exp[0])- 3600*24);
            else:
                $start = $exp[0];
                $end = $exp[1];
            endif;
        else:
            $start = date("Y-m-d", strtotime("-4 month")).'T'.date("H:i:s", strtotime("-4 month"));
            $end = date("Y-m-d", strtotime("now")).'T'.date("H:i:s", strtotime("now"));
        endif;
        $postFields = '<env:Envelope xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:tns="http://service.admin.ws.five9.com/" xmlns:env="http://schemas.xmlsoap.org/soap/envelope/"
        xmlns:ins0="http://jaxb.dev.java.net/array">
        <env:Body>
            <tns:runReport>
                <folderName>My Reports</folderName>
                <reportName>Biorev Agent Occupancy</reportName>
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
        return $this->__XMLtoJSON($response, "runReport");
    }

    function getAgentOccupencyReportData($id = null){
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
        CURLOPT_POSTFIELDS =>'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.admin.ws.five9.com/">;
            <soapenv:Header/>
            <soapenv:Body>
                <ser:getReportResultCsv>
                    <!--Optional:-->
                    <identifier>'.$id.'</identifier>
                </ser:getReportResultCsv>
            </soapenv:Body>
            </soapenv:Envelope>',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            "Authorization: Basic $code",
            "Cookie: clientId=C4A452B18E5646FA9ACBE8A819154296; Authorization=Bearer-ebfa8d5a-ba2a-11eb-abfe-00505fca8def; apiRouteKey=SCLnAPIc6f; app_key=web-ui; farmId=182; uiRouteKey=SCLnUI5551"
        ),
        ));

        $responseCsv = curl_exec($curl);
        curl_close($curl);
        $xmlCsv = $responseCsv;
        // SimpleXML seems to have problems with the colon ":" in the <xxx:yyy> response tags, so take them out
        $xmlCsv = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $xmlCsv);
        $xmlCsv = simplexml_load_string($xmlCsv);
        $jsonCsv = json_encode($xmlCsv);
        $returnCsv = json_decode($jsonCsv,true);
        foreach(explode("\n",$returnCsv["envBody"]["ns2getReportResultCsvResponse"]["return"]) as $key => $value){
            $value = str_getcsv($value, ",", '"');
            if($key == 0){
                foreach($value as $head){
                    $headerArray[] = str_replace(' ', '_', strtolower($head));
                }
                //dd($headerArray);
            }
        }
        foreach(explode("\n",$returnCsv["envBody"]["ns2getReportResultCsvResponse"]["return"]) as $key => $value){
            $value = str_getcsv($value, ",", '"');
            if($key > 0 && (count($value) == count($headerArray))){
                $records = array_combine($headerArray,$value);
                // dd($records);
                $data["agent"] = ($records["agent"]) ? $records["agent"] : null;
                $data["agent_first_name"] = ($records["agent_first_name"]) ? $records["agent_first_name"] : null;
                $data["agent_last_name"] = ($records["agent_last_name"]) ? $records["agent_last_name"] : null;
                $data["date"] = ($records["date"]) ? $records["date"] : null;
                $data["login_time"] = ($records["login_time"]) ? $records["login_time"] : null;
                $data["not_ready_time"] = ($records["not_ready_time"]) ? $records["not_ready_time"] : null;
                $data["wait_time"] = ($records["wait_time"]) ? $records["wait_time"] : null;
                $data["ringing_time"] = ($records["ringing_time"]) ? $records["ringing_time"] : null;
                $data["on_call_time"] = ($records["on_call_time"]) ? $records["on_call_time"] : null;
                $data["on_voicemail_time"] = ($records["on_voicemail_time"]) ? $records["on_voicemail_time"] : null;
                $data["on_acw_time"] = ($records["on_acw_time"]) ? $records["on_acw_time"] : null;
                $data["logout_time"] = ($records["logout_time"]) ? $records["logout_time"] : null;
                $data["ready_time"] = ($records["ready_time"]) ? $records["ready_time"] : null;
                
                AgentOccupancy::insert($data);
            }
        } 
    }

    function getAgentOccupancyData(Request $request){
        if($request->input('dateRange')):
            $s = explode("T", $request->input('dateRange.startDate')); 
            //dd($request->input('dateRange.startDate'));
            $e = explode("T", $request->input('dateRange.endDate'));
            $startDate = '';
            $endDate = '';

            if($s[1] == $e[1]):
                //date coming form calender date selection
                if($s[0] == $e[0]):
                    // single date selected
                    //"2021-07-23::2021-07-23" today
                    //"2021-07-22::2021-07-22"  yesterday
                    //print_r('single date selected');
                    $startDate = $s[0];
                    $endDate = $e[0];
                else:
                    //"2021-06-30::2021-07-31" this month
                    //"2021-06-01::2021-06-30" last month
                    //print_r('multiple date selected');
                    $startDate = date("Y-m-d", strtotime($s[0]) - 24*3600);
                    $endDate = $e[0];
                endif;
            else:
                //date coming from calender date-tab selection
                //print_r('calender tab selection:');
                if(strtotime($e[0]) - strtotime($s[0]) == 24*3600):
                    //"2021-07-22::2021-07-23" today
                    //"2021-07-21::2021-07-22"  yesterday
                    //print_r('same day');
                    $endDate = $startDate = $e[0];
                else:
                    //"2020-12-31::2021-12-31"  this year
                    //"2021-06-30::2021-07-31"  this month
                    //"2021-05-31::2021-06-30"  last month
                    //print_r('many day');
                    $startDate = $s[0];
                    $endDate = $e[0];
                endif;
            endif;
                
            //dd($startFullDate."::".$endFulllDate);
        else:
            $startDate = date("Y-m-d", strtotime("now"));
            $endDate = date("Y-m-d", strtotime("now"));
        endif;
        
        return ["results" => AgentOccupancy::select(DB::raw("
        SEC_TO_TIME(sum(TIME_TO_SEC(`login_time`))) as login_time_sum, 
        SEC_TO_TIME(sum(TIME_TO_SEC(`ready_time`))) as ready_time_sum,
        SEC_TO_TIME(sum(TIME_TO_SEC(`not_ready_time`))) as not_ready_time_sum, 
        SEC_TO_TIME(
            sum(TIME_TO_SEC(`login_time`)) - sum(TIME_TO_SEC(`not_ready_time`)) - sum(TIME_TO_SEC(`on_call_time`)) - sum(TIME_TO_SEC(`on_acw_time`))
            ) as wait_time_sum, 
        SEC_TO_TIME(sum(TIME_TO_SEC(`ringing_time`))) as ringing_time_sum, 
        SEC_TO_TIME(sum(TIME_TO_SEC(`on_call_time`))) as on_call_time_sum, 
        SEC_TO_TIME(sum(TIME_TO_SEC(`on_voicemail_time`))) as on_voicemail_time_sum, 
        SEC_TO_TIME(sum(TIME_TO_SEC(`on_acw_time`))) as on_acw_time_sum, 
        SEC_TO_TIME(sum(TIME_TO_SEC(`logout_time`))) as logout_time_sum, 
         `agent`"))
        ->whereBetween("date", [$startDate, $endDate])
        ->groupBy("agent")
        ->get()];
    }

}
