<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Biorev;
use App\Models\Settings;
use App\Models\Templates;
use App\Models\ExportHistories;
use Biorev\Fivenine\Fivenine;

class CsvExportController extends Biorev
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function addTemplate(Request $request)
    {
        $data = ['name' => $request['name'],'type' => $request['source'],
            'mapped' => json_encode(['sourced' => $request['sfields'], 'dest' => $request['dfields']])];
        Templates::create($data);
        return ['status' => 'success'];
    }

    public function getTypeTemplate($type)
    {
        return Templates::where('type', $type)->orderBy('id', 'desc')->get();
    }

    public function uploadContacts(Request $request) 
    {
        if($request->destination == 'activecampaign') {
            $data = [];
            foreach($request->records as $key => $record) {
                if(in_array($key, $request->fdata)) {
                    $data[] = $this->__setRecord($record, $request->fields, $request->efields);
                }
            }
            return $this->__activeCampaignContactsUpload($data);
        }
        elseif($request->destination == 'five9') {            
            $data = (array) $request->fdata;
            $data = $this->__setF9Record($data);            
            if($request->lid): $list = $request->lid;
            else: $this->__createList($request->name); $list = $request->name;  endif;
            if($request->cid): $campaign = $request->cid;
            else: $this->__createCampaignF9($request->campaign); $campaign = $request->campaign; endif;

            if($list && $campaign):                
                $this->__addListToCampaign($list, $campaign);
            endif;
            $contact =  $this->__createContactsInList($data, $request->efields, $list);
            return $contact;
        }
    }

    public function uploadContactsExport(Request $request) 
    {
        if($request->destination == 'activecampaign') {
            $data = [];
            foreach($request->records as $key => $record) {
                if(in_array($key, $request->fdata)) {
                    $data[] = $this->__setRecord($record, $request->fields, $request->efields);
                }
            }
            return $this->__activeCampaignContactsUpload($data);
        }
        elseif($request->destination == 'five9') {            
            $data = (array) $request->fdata;
            $data = $this->__setF9Record($data);            
            if($request->lid): $list = $request->lid;
            else: $this->__createList($request->name); $list = $request->name;  endif;
            if($request->cid): $campaign = $request->cid;
            else: $this->__createCampaignF9($request->campaign); $campaign = $request->campaign; endif;

            if($list && $campaign):                
                $this->__addListToCampaign($list, $campaign);
            endif;
            $contact =  $this->__createContactsInList($data, $request->efields, $list);
            if($contact['status'] == 'success') {
                $count = ExportHistories::whereDate('created_at', date('Y-m-d', time()))->get()->count();
                $count = $count+1;
                $count = str_pad($count, 2, "0", STR_PAD_LEFT);
                $name = 'CSV-EXPORT-'.date('ymd').$count;
                ExportHistories::create([
                    "identifier" => $contact['message']['identifier'],
                    'file_name' => $name,
                    "user_id" => \Auth::user()->id,
                    "data" => json_encode($data),
                    "arraykeys" => json_encode(array_keys($data[0]))
                ]);
                return ['status' => 'success', 'message' => '<p class="p-4">History report will be available after few minutes.<br> You can visit Export History Menu. <br>Export name is '.$name.'<br> ,<a href="/export-history">Click Here</a> to go directly to report section.'];
            } else {
                return $contact;
            }
        }
    }

    private function __addListToCampaign($listName, $compaignName){
        $curl = curl_init();
        $setting1 = Settings::where('id', '=', 37)->first();
        $setting2 = Settings::where('id', '=', 38)->first();
        $username = $setting1->value;
        $password = $setting2->value;
        $code = base64_encode($username.':'.$password);
        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.five9.com:443/wsadmin/v12/AdminWebService',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_POSTFIELDS =>'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.admin.ws.five9.com/">
        <soapenv:Header/>
        <soapenv:Body>
            <ser:addListsToCampaign>
                <!--Optional:-->
                <campaignName>'.$compaignName.'</campaignName>
                <!--Zero or more repetitions:-->
                <lists>
                    <listName>'.$listName.'</listName>
                </lists>
            </ser:addListsToCampaign>
        </soapenv:Body>
        </soapenv:Envelope>',
        CURLOPT_HTTPHEADER => array(
            "Authorization: Basic $code",
            'Content-Type: application/xml',
            'Cookie: clientId=7CEF4E5A281248448E555BB89D486459; Authorization=Bearer-f34e5614-c048-11eb-8f5a-00505fca8df6; apiRouteKey=SCLnAPIc6f; app_key=F9; farmId=182; uiRouteKey=SCLnUI5551'
        ),
        ));
        $response = curl_exec($curl);
        curl_close($curl); //print_r($response);
        return ['result' => $response];
    }

    private function __setRecord($record, $field, $fields)
    {
        $response = [];
        foreach ($record as $key => $value) {
            if(in_array($key, $field)) {
                $fk = array_search($key, $field);
                $response[$fields[$fk]] = $value;
            }
        }
        return $response;
    }

    private function __setF9Record($records)
    {
        //print_r($records); die;
        $response = [];
        foreach($records as $key => $record){
            if($record['number1'] == 0) {
                if(strlen($record['number2']) == 10): 
                    $records[$key]['number1'] = $record['number2'];
                    $records[$key]['number2'] = 0;
                elseif(strlen($record['number3']) == 10): 
                    $records[$key]['number1'] = $record['number3'];
                    $records[$key]['number3'] = 0;
                else: unset($records[$key]);
                endif;
            }
        }
        $records = array_values($records);
        return $records;
    }

    private function __phoneFormatter($var = null)
    {
        $var = str_replace(' ', '', $var);
        $var = str_replace('(', '', $var);
        $var = str_replace(')', '', $var);
        $var = str_replace('-', '', $var);
        $var = str_replace('+', '', $var);
        if(substr($var, 0, 1) == 1) {
            $var = substr($var, 1, strlen($var));
        }
        
        $var = substr($var, 0, 10);
        
        if(strlen($var) != 10) {
            return 0;
        } else {
            return $var;
        }
    }

    private function __activeCampaignContactsUpload($data = null)
    {
        $postRequest = json_encode(['contacts' => $data, 'callback' => ['requestType' => 'GET', 'url' => 'https://biorevtech.com']]);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://biorev33069.api-us1.com/api/3/import/bulk_import',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array(
                'Api-Token: 5ab82418363737bc55f771e7529c7c1f42ff0d41c623cfe874b2ec0a4455995d34493f39',
                'Cookie: __cfduid=d97b6427615f0d72feec531918ba097411618376679; PHPSESSID=56d9cad6a1d8133f1acd1d92a006cedd; cmp90303434=205ff7b29cb3bbd970014602eb9a70f0; em_acp_globalauth_cookie=f817b856-b9df-4998-9b94-ac98e20f3642'
            ),
        ));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postRequest);
        $response = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($response);
        return ['result' => $result];
    }

    private function __createList($name)
    {
        $listname = $name;
        $postFields = '<env:Envelope xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:ser="http://service.admin.ws.five9.com/" xmlns:env="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ins0="http://jaxb.dev.java.net/array">
        <env:Header/>
        <env:Body>
            <ser:createList>
                <listName>'.$listname.'</listName>
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
        $response = curl_exec($curl);        
        curl_close($curl); 
        return  json_decode(json_encode($response));
    }

    private function __createCampaignF9($name)
    {
        $postFields = '<env:Envelope xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:ser="http://service.admin.ws.five9.com/" xmlns:env="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ins0="http://jaxb.dev.java.net/array">
        <env:Header/>
        <env:Body>
            <ser:createOutboundCampaign>
               <campaign>
                  <description>this campaign is created by Biorev dashboard</description>
                  <mode>BASIC</mode>
                  <name>'.$name.'</name>
                  <callWrapup>
                     <agentNotReady>true</agentNotReady>
                     <dispostionName>No Disposition</dispostionName>
                     <enabled>false</enabled>
                     <reasonCodeName>Not Ready</reasonCodeName>
                     <timeout>
                        <days>0</days>
                        <hours>0</hours>
                        <minutes>15</minutes>
                        <seconds>0</seconds>
                     </timeout>
                  </callWrapup>
                  <recordingNameAsSid></recordingNameAsSid>
                  <useFtp>0</useFtp>
                  <analyzeLevel>20</analyzeLevel>
                  <CRMRedialTimeout>
                     <days>0</days>
                     <hours>0</hours>
                     <minutes>15</minutes>
                     <seconds>0</seconds>
                  </CRMRedialTimeout>
                  <dnisAsAni>false</dnisAsAni>
                  <enableListDialingRatios>0</enableListDialingRatios>
                  <listDialingMode>VERTICAL_DIALING</listDialingMode>
                  <noOutOfNumbersAlert>true</noOutOfNumbersAlert>
                  <profileName></profileName>
                  <state>RUNNING</state>
                  <trainingMode>false</trainingMode>
                  <type>OUTBOUND</type>
                  <autoRecord></autoRecord>
                  <callAnalysisMode>NO_ANALYSIS</callAnalysisMode>
                  <dialNumberOnTimeout>true</dialNumberOnTimeout>
                  <dialingMode>PREVIEW</dialingMode>
                  <distributionTimeFrame>minutes15</distributionTimeFrame>
                  <maxDroppedCallsPercentage>5</maxDroppedCallsPercentage>
                  <maxPreviewTime>
                     <days>0</days>
                     <hours>0</hours>
                     <minutes>15</minutes>
                     <seconds></seconds>
                  </maxPreviewTime>
                  <maxQueueTime>
                     <days>0</days>
                     <hours>0</hours>
                     <minutes>30</minutes>
                     <seconds>0</seconds>
                  </maxQueueTime>
                  <monitorDroppedCalls>true</monitorDroppedCalls>
                  <previewDialImmediately>false</previewDialImmediately>
               </campaign>
            </ser:createOutboundCampaign>
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
        $response = curl_exec($curl);        
        curl_close($curl); 
        return  $name;
    }

    private function __createContactsInList($data, $fields, $name)
    {
        $listname = $name;
        $efields = '';
        $cfields = [];
        $ck = 0;
        $postFields = array_keys($data[1]);
        foreach (array_filter($postFields) as $key => $value) {
            if( ($value != '') && ($value != 'country')) {
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
        foreach ($data as $dkey => $dvalue) {
            if($dvalue['number1'] != 0){
                foreach($postFields as $key => $value) {
                    if( ($value != '') && ($key != 'country')) {
                        $sal = ($dvalue[$value])?$dvalue[$value]:'0';
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
        $postFields = '<env:Envelope xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:ser="http://service.admin.ws.five9.com/" xmlns:env="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ins0="http://jaxb.dev.java.net/array">
        <env:Header/>
        <env:Body>
            <ser:addToListCsv>
                <listName>'.$listname.'</listName>
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
        //var_dump($postFields); die;
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
        return $this->__XMLtoJSON($response, 'addToListCsv');
    }

    private function __createContactsInListExport($data, $fields, $name)
    {
        $listname = $name;
        $efields = '';// dd($fields);
        $cfields = implode(',', array_filter($fields)); //dd($cfields);
        foreach (array_filter($fields) as $key => $value) {
            if($value == 'number1') : $k = true; else: $k = false; endif;
            $efields.='<fieldsMapping><columnNumber>'.($key+1).'</columnNumber><fieldName>'.$value.'</fieldName><key>'.$k.'</key></fieldsMapping>';
        }
        $edata = '';
        foreach ($data as $dkey => $dvalue) {
            $edata.='&#xA;';
            foreach($fields as $key => $value){
                if($value != ''):
                    $edata .= $dvalue[$value].',';
                else: 
                    $edata .= ',';
                endif;
            }
            // foreach($dvalue as $key => $value){
            //     if( ($value == '') || is_null($value) ):
            //         $edata.= ',';
            //     else:
            //         $edata.= $value.',';
            //     endif;
            // }
        }
        
        $postFields = '<env:Envelope xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:ser="http://service.admin.ws.five9.com/" xmlns:env="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ins0="http://jaxb.dev.java.net/array">
        <env:Header/>
        <env:Body>
            <ser:addToListCsv>
                <listName>'.$listname.'</listName>
                <listUpdateSettings>
                    '.$efields.'
                    <reportEmail>ankit@biorev.us</reportEmail>
                    <separator>,</separator>
                    <skipHeaderLine>true</skipHeaderLine>
                    <cleanListBeforeUpdate>false</cleanListBeforeUpdate>
                    <crmAddMode>ADD_NEW</crmAddMode>
                    <crmUpdateMode>UPDATE_ALL</crmUpdateMode>
                    <listAddMode>ADD_ALL</listAddMode>
                </listUpdateSettings>
                <csvData>'.$cfields.','.$edata.'</csvData>
            </ser:addToListCsv>
        </env:Body>
        </env:Envelope>';
        dd($postFields);
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
        $response = curl_exec($curl); //dd($response);
        if(strpos($response, '<identifier>') > 0):
            $identifier = substr($response, strpos($response, '<identifier>')+12, strrpos($response, '</identifier>') - strpos($response, '<return>')-20);
            return ['result' => 'Contacts uploaded successfully', 'status' => 'success'];
        else:
            return ['result' => 'Please try again !!', 'status' => 'error'];
        endif;
        $postFieldsResult = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.admin.ws.five9.com/">
            <soapenv:Header/>
            <soapenv:Body>
            <ser:getListImportResult>
                <!--Optional:-->
                <identifier>
                    <!--Optional:-->
                    <identifier>'.$identifier.'</identifier>
                </identifier>
            </ser:getListImportResult>
            </soapenv:Body>
        </soapenv:Envelope>';
        //sleep(5);
        $setting1 = Settings::where('id', '=', 37)->first();
        $setting2 = Settings::where('id', '=', 38)->first();
        $username = $setting1->value;
        $password = $setting2->value;
        $code = base64_encode($username.':'.$password);
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.five9.com:443/wsadmin/v12/AdminWebService',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS =>$postFieldsResult,
            CURLOPT_HTTPHEADER => array(
              "Authorization: Basic $code",
              'Content-Type: application/xml',
              'Cookie: clientId=55C66003DEBC4319B00DFE27F039082A; Authorization=Bearer-f34e5614-c048-11eb-8f5a-00505fca8df6; apiRouteKey=SCLnAPIc6f; app_key=F9; farmId=182; uiRouteKey=SCLnUI5551'
            ),
          ));
        $response = curl_exec($curl); //print_r($response);
        curl_close($curl); 
        return  json_decode(json_encode($response));
    }

    public function getFiveNineContactList()
    {
        $five9 = new Fivenine();
        $data  = $five9->GetRequestCall('GET', 'getListsInfo', '');
        return $data;
    }

    public function getFiveNineCampaignList()
    {
        $five9 = new Fivenine();
        $data  = $five9->GetRequestCall('GET', 'getCampaigns', '');
        return $data;
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
}