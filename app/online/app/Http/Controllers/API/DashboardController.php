<?php



namespace App\Http\Controllers\API;



use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Models\Settings;

use App\Http\Controllers\API\SettingsController;



class DashboardController extends SettingsController

{



    public function __construct()

    {

        $this->middleware('auth');

    }



    public function getDashboard(Request $request)

    {

        $response = []; $outreach = [];

        if($request['outreach'] == 1):

            $outreach = $this->outreachprospects($request['page']);

        endif;

        $response = $outreach;

        return $response;

    }

    public function outreachsession()
    {
        $curl = curl_init();
        $token_expire = Settings::where('id', '=', 7)->first();
        if($token_expire['value'] <= strtotime("now")):
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.outreach.io/oauth/token?client_id=eUBHpxrv-UgUE_NloPqbbcHsJn0VpV1mj9JEraoh_jg&client_secret=D6e9SmqUTDcQLna_BhZst97JonI7wnZAE_Y5KuCcqFE&redirect_uri=https://www.biorev.us/oauth/outreach&grant_type=refresh_token&refresh_token=sNLqOEE0j9Pd7rhv6HnY23YzaJHzql0bVKAF58B-enA",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST'
            ));
            $requestTokenResponse = curl_exec($curl);
            $tokenInfo = get_object_vars(json_decode($requestTokenResponse));
            $accessToken = $tokenInfo['access_token'];
            $access_token = Settings::where('id', '=', 8)->first();
            $access_token->value = $accessToken;
            $access_token->save();
            $token_expire = Settings::where('id', '=', 7)->first();
            $token_expire->value = strtotime("now")+90*60;
            $token_expire->save();
        endif;
        $access_token = Settings::where('id', '=', 8)->first();
        return $access_token['value'];
    }

    public function outreachAllprospects(Request $request)
    {
        $page = $request->input('page');
        $curl = curl_init();
        $accessToken = $this->outreachsession();
        $offset = ($page-1)*20;
        if($offset):
            $query = "https://api.outreach.io/api/v2/prospects?page[limit]=20&page[offset]=$offset&sort=-engagedAt";
        else:
            $query = "https://api.outreach.io/api/v2/prospects?page[limit]=20&&sort=-engagedAt";
        endif;
        curl_setopt_array($curl, array(
            CURLOPT_URL => $query,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $accessToken",
                "Content-Type: application/vnd.api+json"
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $results = json_decode($response);
        $results1 = get_object_vars($results);
        $d1 = get_object_vars($results1["meta"]);
        $totalPage = ceil($d1['count']/20);
        if($totalPage <= 10):
            $start = 1;
            $end = $totalPage;
        else:
            if($page <= 5):
                $start = 1;
                $end = 10;
            else:
                $start = $page - 5;
                if($end+10 <= $totalPage):
                    $end = $start + 10;
                else:
                    $end = $totalPage;
                    $start = $totalPage - 10;
                endif;
            endif;
        endif;
        // $start = ($page-5 > 0) ? $page-5 : 1;
        // $end = ($page-5 > 0) ? $page+5 : 10;
        $paginate = ['page' => $page, 'start' => $offset+1, 'end' => $offset+20, 'count' => 20, 'total' => $d1['count'], 'pager' =>ceil(intval($d1['count'])/20)];
                
        return ['results' => $results1['data'], 'page' => $paginate, 'paginationArray' => range($start, $end)];
    }
    public function outreachProspectsFilter($page, Request $request)
    {
        //dd($request->all());
        $curl = curl_init();
        $accessToken = $this->outreachsession();
        $offset = ($page-1)*20;
        $queryString = '';

        if($request->input("startDate")):
            $sDate = explode("T", $request->input("startDate"));
            $s = $sDate[0];
            $eDate = explode("T", $request->input("endDate"));
            $e = $eDate[0];
            $queryString = "&filter[engagedAt]=$s..$e";
        endif;
        if($request->input("stage")):
            $queryString .= "&filter[stage][id]=".$request->input("stage");
        endif;
        //
        if($offset):
            $query = "https://api.outreach.io/api/v2/prospects?page[limit]=20&page[offset]=$offset&sort=-engagedAt$queryString";
        else:
            $query = "https://api.outreach.io/api/v2/prospects?page[size]=20&sort=-engagedAt$queryString";
        endif;
        //dd($query);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $query,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $accessToken",
                "Content-Type: application/vnd.api+json"
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $results = json_decode($response);
        $results1 = get_object_vars($results);
        $d1 = get_object_vars($results1["meta"]);
        $totalPage = ceil($d1['count']/20);
        if($totalPage <= 10):
            $start = 1;
            $end = $totalPage;
        else:
            if($page <= 5):
                $start = 1;
                $end = 10;
            else:
                $start = $page - 5;
                if($end+10 <= $totalPage):
                    $end = $start + 10;
                else:
                    $end = $totalPage;
                    $start = $totalPage - 10;
                endif;
            endif;
        endif;
        // $start = ($page-5 > 0) ? $page-5 : 1;
        // $end = ($page-5 > 0) ? $page+5 : 10;
        $paginate = ['page' => $page, 'start' => $offset+1, 'end' => $offset+20, 'count' => 20, 'total' => $d1['count'], 'pager' =>ceil(intval($d1['count'])/20)];
                
        return ['results' => $results1['data'], 'page' => $paginate, 'paginationArray' => range($start, $end)];
    }


}