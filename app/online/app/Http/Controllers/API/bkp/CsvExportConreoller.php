<?php

namespace App\Http\Controllers\API;



use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Http\Controllers\Biorev;

use App\Models\Settings;

use App\Models\Templates;

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

            $data = [];

            foreach($request->fdata as $key => $record) 

            {

                //if(in_array($key, $request->fdata)) {

                $nrc = str_replace(',', '-', $record);  

                $data[] = $this->__setF9Record($nrc, $request->fields, $request->efields, $request->formatter);

               // }

            }

            foreach ($data as $key => $value) {

                // do nothing

                if($value['number1'] == 0) {

                    if(in_array('number2', array_keys($value)) && ($value['number2'] != 0)) {

                        $data[$key]['number1'] = $data[$key]['number2'];

                    }elseif(in_array('number3', array_keys($value)) && ($value['number3'] != 0)) {

                        $data[$key]['number1'] = $data[$key]['number3'];

                    }else {

                        unset($data[$key]);

                    }

                }

            }

            //$campaign = $this->__createCampaignF9($request->campaign);

            //return ['data' => $data, 'fields' => $request->fields];

            if($request->lid): $list = $request->lid;

            else: $list =  $this->__createList($request->name); endif;

            if($request->cid): $campaign = $request->cid;

            else: $campaign =  $this->__createCampaignF9($request->campaign); endif;

            if($list && $campaign):

                $this->__addListToCampaign($list, $campaign);

            endif;

            $contact =  $this->__createContactsInList($data,  $request->fields, $list);

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

            $data = [];            

            foreach($request->fdata as $key => $record) 

            {

                //if(in_array($key, $request->fdata)) {  

                $nrc = str_replace(',', '-', $record);                  

                $data[] = $this->__setF9Record($nrc, $request->efields, $request->efields, $request->formatter);

               // }

            }            

            foreach ($data as $key => $value) {

                // do nothing

                if($value['number1'] == 0) {

                    if(in_array('number2', array_keys($value)) && ($value['number2'] != 0)) {

                        $data[$key]['number1'] = $data[$key]['number2'];

                    }elseif(in_array('number3', array_keys($value)) && ($value['number3'] != 0)) {

                        $data[$key]['number1'] = $data[$key]['number3'];

                    }else {

                        unset($data[$key]);

                    }

                }

            }            

            //$campaign = $this->__createCampaignF9($request->campaign);

            //return ['data' => $data, 'fields' => $request->fields];

            if($request->lid): $list = $request->lid;

            else: $list =  $this->__createList($request->name); endif;

            if($request->cid): $campaign = $request->cid;

            else: $campaign =  $this->__createCampaignF9($request->campaign); endif;

            if($list && $campaign):                

                $this->__addListToCampaign($list['name'], $campaign['name']);

            endif;

            

            $contact =  $this->__createContactsInList($data,  $request->efields, $list["name"]);

            return $contact;

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



    private function __setF9Record($record, $field, $fields)

    {

        $response = [];

        foreach($field as $key => $keyName){

            

            if(!is_null($key) && !array_key_exists($keyName, $record)):

                $record[$keyName]='';

            endif;

        }

        return $record;

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

            <!--Optional:-->

            <description>this campaign is created by Biorev dashboard</description>

            <!--Optional:-->

            <mode>BASIC</mode>

            <!--Optional:-->

            <name>'.$name.'</name>

            <!--Optional Applies only to the advanced campaign mode:-->

            <profileName></profileName>

            <!--Optional:-->

            <state>RUNNING</state>

            <!--Optional:-->

            <trainingMode>false</trainingMode>

            <!--Optional:-->

            <type>OUTBOUND</type>

            <!--Optional:-->

            <autoRecord></autoRecord>

            <!--Optional:-->

            <callWrapup>

               <!--Optional:-->

               <agentNotReady>true</agentNotReady>

               <!--Optional:-->

               <dispostionName>No Disposition</dispostionName>

               <!--Optional:-->

               <enabled>false</enabled>

               <!--Optional:-->

               <reasonCodeName>Not Ready</reasonCodeName>

               <!--Optional remove this if enable is set to false:-->

               <timeout>

                  <days>0</days>

                  <hours>0</hours>

                  <minutes>15</minutes>

                  <seconds>0</seconds>

               </timeout>

            </callWrapup>

            <!--Optional:-->

            <recordingNameAsSid></recordingNameAsSid>

            <!--Optional:-->

            <useFtp>0</useFtp>

            <!--Optional:-->

            <analyzeLevel>20</analyzeLevel>

            <!--Optional:-->

            <CRMRedialTimeout>

               <days>0</days>

               <hours>0</hours>

               <minutes>15</minutes>

               <seconds>0</seconds>

            </CRMRedialTimeout>

            <!--Optional:-->

            <dnisAsAni>false</dnisAsAni>

            <!--Optional:-->

            <enableListDialingRatios>0</enableListDialingRatios>

            <!--Optional:-->

            <listDialingMode>VERTICAL_DIALING</listDialingMode>

            <!--Optional:-->

            <noOutOfNumbersAlert>true</noOutOfNumbersAlert>

            <!--Optional:-->

            <stateDialingRule>REGION</stateDialingRule>

            <!--Optional:-->

            <timeZoneAssignment>0</timeZoneAssignment>

            <!--Optional:-->

            <!--Optional:-->

            <!--Optional:-->

            <callAnalysisMode>NO_ANALYSIS</callAnalysisMode>

            <!--Optional:-->

            <!--Optional:-->

            <dialNumberOnTimeout>true</dialNumberOnTimeout>

            <!--Optional:-->

            <dialingMode>PREVIEW</dialingMode>

            <!--Optional:-->

            <dialingPriority>3</dialingPriority>

            <!--Optional:-->

            <dialingRatio>50</dialingRatio>

            <!--Optional:-->

            <distributionTimeFrame>minutes15</distributionTimeFrame>

            <!--Optional:-->

            <!--Optional:-->

            <maxDroppedCallsPercentage>5</maxDroppedCallsPercentage>

            <!--Optional:-->

            <maxPreviewTime>

               <days>0</days>

               <hours>0</hours>

               <minutes>15</minutes>

               <seconds></seconds>

            </maxPreviewTime>

            <!--Optional:-->

            <maxQueueTime>

               <days>0</days>

               <hours>0</hours>

               <minutes>30</minutes>

               <seconds>0</seconds>

            </maxQueueTime>

            <!--Optional:-->

            <monitorDroppedCalls>true</monitorDroppedCalls>

            <!--Optional:-->

            <previewDialImmediately>false</previewDialImmediately>

            <!--Optional:-->            

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

        $efields = '';// dd($fields);

        $cfields = implode(',', array_filter($fields));

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



    private function __createContactsInListExport($data, $fields, $name)

    {

        $listname = $name;

        $efields = '';// dd($fields);

        $cfields = implode(',', array_filter($fields)); //dd($cfields);

        foreach (array_filter($fields) as $key => $value) {

            if($value == 'number1') : $k = true; else: $k = false; endif;

            $efields.='<fieldsMapping><columnNumber>'.($key+1).'</columnNumber><fieldName>'.$value.'</fieldName><key>'.$k.'</key></fieldsMapping>';

        }

        dd($data);

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



    private function __XMLtoJSON($response) {

        $xml = $response;

        // SimpleXML seems to have problems with the colon ":" in the <xxx:yyy> response tags, so take them out

        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $xml);

        $xml = simplexml_load_string($xml);

        $json = json_encode($xml);

        $return = json_decode($json,true);

        return $return['envBody']['ns2getListsInfoResponse']['return'];

    }



    private function __XMLtoJSON2($response) {

        $xml = $response;

        // SimpleXML seems to have problems with the colon ":" in the <xxx:yyy> response tags, so take them out

        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $xml);

        $xml = simplexml_load_string($xml);

        $json = json_encode($xml);

        $return = json_decode($json,true);

        return $return['envBody']['ns2getCampaignsResponse']['return'];

    }

}