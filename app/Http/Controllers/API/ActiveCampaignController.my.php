<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Biorev;
use App\Models\Settings;
use Biorev\Fivenine\Fivenine;

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
    
}