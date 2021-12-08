<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Contacts;


class DataUpdateController extends Controller
{

    public function __construct()
    {
       // $this->middleware('auth');
    }

    public function index($limits = null)
    {
        $contacts = Contacts::select('id', 'record_id', 'state', 'country')->whereNull('country')->get();
        foreach ($contacts as $key => $contact) {
            echo $key++.'. '.$contact->record_id.'  --  '.$contact->state.'  --  '.$contact->country.' - ';
            /*$cntry = $this->__get_country($contact->state);
            if($cntry)
            {
                echo (Contacts::where('state', $contact->state)->update(['country' => $cntry]) == true)?' - done <br>':' - Not done <br>';
            } else {
                echo ' - Not found <br>';
            }*/
            echo '<br>';
        }
        
        die;
    }

    private function __get_country($state) 
    {
        $state = DB::table('cities')->where('city', $state)->get()->first();
        if($state && $state->country_id == 38) {
            return 'Canada';
        } elseif($state && $state->country_id == 228) {
            return 'United States';
        } elseif($state) {
            return  false;
        } else {
            return false;
        }
    }

    
    public function getOutreachCustomData(){
        
    }

}
