<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Biorev;
use App\Models\Settings;
use Biorev\Fivenine\Fivenine;
use App\Models\Agents;

class FiveNineController extends Biorev
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getContactList()
    {
        $five9 = new Fivenine();
        $data  = $five9->GetRequestCall('GET', 'getListsInfo', '');
        return $data;
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
        $listRecords = [];
        $header = $return["envBody"]["ns2getReportResultResponse"]["return"]["header"]["values"]["data"];
        $contactIdIndex = array_search("CONTACT ID", $header);
        $number1Index = array_search("number1", $header);
        $number2Index = array_search("number2", $header);
        $number3Index = array_search("number3", $header);
        $lastNameIndex = array_search("last_name", $header);
        if(isset($return["envBody"]["ns2getReportResultResponse"]["return"]["records"])):
            $records = $return["envBody"]["ns2getReportResultResponse"]["return"]["records"];
            $recordCount = 0;
            foreach($records as $key => $value):
                if(gettype($value["values"]["data"][$number1Index]) == 'array'):
                    $value["values"]["data"][$number1Index] = implode(",", $value["values"]["data"][$number1Index]);
                endif;
                if(gettype($value["values"]["data"][$number2Index]) == 'array'):
                    $value["values"]["data"][$number2Index] = implode(",", $value["values"]["data"][$number2Index]);
                endif;
                if(gettype($value["values"]["data"][$number3Index]) == 'array'):
                    $value["values"]["data"][$number3Index] = implode(",", $value["values"]["data"][$number3Index]);
                endif;
                if(gettype($value["values"]["data"][$lastNameIndex]) == 'array'):
                    $value["values"]["data"][$lastNameIndex] = null;
                endif;
                $listRecords[$value["values"]["data"][$contactIdIndex]] = $value["values"]["data"];            
                $recordCount++;
            endforeach;
        endif;
        if(array_key_exists("ns2getReportResultResponse", $return['envBody'])):
            return [
                    "header" => $header, 
                    "records" => $listRecords,  
                    "recordsCount" => count($listRecords), 
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
        
        $efields = '';
        $cfields = [];
        $ck = 0;
        $header = $request->input("header");
        $listname = $request->input("listname");
        $fdata = $request->input("fdata");
        $data = [];
        $count = 0;
        foreach($fdata as $value):
            foreach($value as $kkey => $vvalue):
                if($kkey > 1):
                    $data[$count][$header[$kkey]] = $vvalue;
                endif;
            endforeach;
            $count++;
        endforeach;
        
        foreach (array_filter($header) as $key => $value) {
            if( ($value != '') && ($value != "LIST NAME")  && ($value != "CONTACT ID") ) {
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
        array_shift($header);
        foreach ($data as $dkey => $dvalue) {
            if($dvalue['number1'] != 0){
                foreach($header as $key => $value) {
                    if( ($value != '') && ($key != "country")) {
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
                        <reportEmail>ankit@biorev.us</reportEmail>
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
        $listname1 = $request->input("listname1");
        $fdata = $request->input("fdata");
        $data = [];
        $count = 0;
        foreach($fdata as $value):
            foreach($value as $kkey => $vvalue):
                if($kkey > 1):
                    $data[$count][$header[$kkey]] = $vvalue;
                endif;
            endforeach;
            $count++;
        endforeach;
        
        foreach (array_filter($header) as $key => $value) {
            if( ($value != '') && ($value != "country")  && ($value != "LIST NAME")  && ($value != "CONTACT ID") ) {
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
        array_shift($header);
        foreach ($data as $dkey => $dvalue) :
            if($dvalue['number1'] != 0):
                foreach($header as $key => $value):
                
                    if( ($value != '') && ($key != "country") ) : 
                    
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
        $postFields = '<env:Envelope xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:ser="http://service.admin.ws.five9.com/" xmlns:env="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ins0="http://jaxb.dev.java.net/array">
        <env:Header/>
        <env:Body>
            <ser:addToListCsv>
                <listName>'.$listname1.'</listName>
                <listUpdateSettings>
                    '.$efields.'
                    <reportEmail>ankit@biorev.us</reportEmail>
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
        if($request->input('dateRange')):
            $start = $request->input('dateRange.startDate');
            $end = $request->input('dateRange.endDate');
        else:
            $start = date("Y-m-d", strtotime("-1 days")).'T'.date("H:i:s", strtotime("-1 days"));
            $end = date("Y-m-d", strtotime("now")).'T'.date("H:i:s", strtotime("now"));
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

            
            $averageNotReadyTime = intval($totalNotReadyTime/$totalNotReadyCount);
            $anrthours = intval($averageNotReadyTime/3600);
            $anrtm = $averageNotReadyTime - $anrthours*3600;
            $anrtminutes = intval($anrtm/60);
            $anrts = $averageNotReadyTime - $anrthours*3600 - $anrtminutes*60;

            $anrthours = ($anrthours >= 10)?$anrthours:'0'.$anrthours;
            $anrtminutes = ($anrtminutes >= 10)?$anrtminutes:'0'.$anrtminutes;
            $anrts = ($anrts >= 10)?$anrts:'0'.$anrts;

            $sumOfRecords[$key]["average_not_ready_time"] = $anrthours.':'.$anrtminutes.':'.$anrts;

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
            $averageOnCallTime = intval($totalOnCallTime/$totalCallsCount);
            $aocthours = intval($averageOnCallTime/3600);
            $aoctm = $averageOnCallTime - $aocthours*3600;
            $aoctminutes = intval($aoctm/60);
            $aocts = $averageOnCallTime - $aocthours*3600 - $aoctminutes*60;

            $aocthours = ($aocthours >= 10)?$aocthours:'0'.$aocthours;
            $aoctminutes = ($aoctminutes >= 10)?$aoctminutes:'0'.$aoctminutes;
            $aocts = ($aocts >= 10)?$aocts:'0'.$aocts;

            $sumOfRecords[$key]["average_on_call_time"] = $aocthours.':'.$aoctminutes.':'.$aocts;
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
            $averageTalkTime = intval($totalTalkTime/$totalCallsCount);
            $aathours = intval($averageTalkTime/3600);
            $aatm = $averageTalkTime - $aathours*3600;
            $aatminutes = intval($aatm/60);
            $aats = $averageTalkTime - $aathours*3600 - $aatminutes*60;

            $aathours = ($aathours >= 10)?$aathours:'0'.$aathours;
            $aatminutes = ($aatminutes >= 10)?$aatminutes:'0'.$aatminutes;
            $aats = ($aats >= 10)?$aats:'0'.$aats;

            $sumOfRecords[$key]["average_talk_time"] = $aathours.':'.$aatminutes.':'.$aats;

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
            $averageAfterCallWorkTime = intval($totalAfterCallWorkTime/$totalCallsCount);
            $acwthours = intval($averageAfterCallWorkTime/3600);
            $acwtm = $averageAfterCallWorkTime - $acwthours*3600;
            $acwtminutes = intval($acwtm/60);
            $acwts = $averageAfterCallWorkTime - $acwthours*3600 - $acwtminutes*60;

            $acwthours = ($acwthours >= 10)?$acwthours:'0'.$acwthours;
            $acwtminutes = ($acwtminutes >= 10)?$acwtminutes:'0'.$acwtminutes;
            $acwts = ($acwts >= 10)?$acwts:'0'.$acwts;

            $sumOfRecords[$key]["average_after_call_work_time"] = $acwthours.':'.$acwtminutes.':'.$acwts;            
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
}