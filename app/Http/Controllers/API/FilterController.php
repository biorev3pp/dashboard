<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
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


class FilterController extends Controller
{
    
    public function __construct()
    {
       
    }
    
    public function getAllPurchaseAuthorization(){
        $results = DB::select("SELECT COUNT(*) , `custom1` as name, `custom1` as oid FROM `contacts` WHERE `custom1` IS NOT NULL GROUP BY `custom1` ORDER BY `custom1` ASC");
        return ['results' => $results];
    }
    public function getAllDepartments(){
        $results = DB::select("SELECT COUNT(*) , `custom2` as name, `custom2` as oid FROM `contacts` WHERE `custom2` IS NOT NULL GROUP BY `custom2` ORDER BY `custom2` ASC");
        return ['results' => $results];
    }
    public function getAllIndustry(){
        $results = DB::select("SELECT COUNT(*) , `custom9` as name, `custom9` as oid FROM `contacts` WHERE `custom9` IS NOT NULL GROUP BY `custom9` ORDER BY `custom9` ASC");
        return ['results' => $results];
    }
    public function getAllPrimaryIndustry(){
        $results = DB::select("SELECT COUNT(*) , `custom10` as name, `custom10` as oid FROM `contacts` WHERE `custom10` IS NOT NULL GROUP BY `custom10` ORDER BY `custom10` ASC");
        return ['results' => $results];
    }
    public function getAllCompanyRevenue(){
        $results = DB::select("SELECT COUNT(*) , `custom11` as name, `custom11` as oid FROM `contacts` WHERE `custom11` IS NOT NULL GROUP BY `custom11` ORDER BY `custom11` DESC");
        return ['results' => $results];
    }
    public function getAllCompanyRevRange(){
        $results = DB::select("SELECT COUNT(*) , `custom12` as name, `custom12` as oid FROM `contacts` WHERE `custom12` IS NOT NULL GROUP BY `custom12` ORDER BY `custom12` DESC");
        return ['results' => $results];
    }
    public function getAllTimeZone(){
        $results = DB::select("SELECT COUNT(*) , `timeZone` as name, `timeZone` as oid FROM `contacts` WHERE `timeZone` IS NOT NULL GROUP BY `timeZone` ORDER BY `timeZone` ASC");
        return ['results' => $results];
    }
    public function getAllCities(){
        $results = DB::select("SELECT COUNT(*) , `city` as name, `city` as oid FROM `contacts` WHERE `city` IS NOT NULL GROUP BY `city` ORDER BY `city` ASC");
        return ['results' => $results];
    }
    public function getAllStates(){
        $results = DB::select("SELECT COUNT(*) , `state` as name, `state` as oid FROM `contacts` WHERE `state` IS NOT NULL GROUP BY `state` ORDER BY `state` ASC");
        return ['results' => $results];
    }
    public function getAllZipCode(){
        $results = DB::select("SELECT COUNT(*) , `zip` as name, `zip` as oid FROM `contacts` WHERE `zip` IS NOT NULL GROUP BY `zip` ORDER BY `zip` ASC");
        return ['results' => $results];
    }
    public function getAllOutreachTags(){
        $results = DB::select("SELECT COUNT(*) , `outreach_tag` as name, `outreach_tag` as oid FROM `contacts` WHERE `outreach_tag` IS NOT NULL GROUP BY `outreach_tag` ORDER BY `outreach_tag` ASC");
        return ['results' => $results];
    }
    public function getAllSources(){
        $results = DB::select("SELECT COUNT(*) , `source` as name, `source` as oid FROM `contacts` WHERE `source` IS NOT NULL GROUP BY `source` ORDER BY `source` ASC");
        return ['results' => $results];
    }
    public function getAllCountries(){
        $results = DB::select("SELECT COUNT(*) , `country` as name, `country` as oid FROM `contacts` WHERE `country` IS NOT NULL GROUP BY `country` ORDER BY `country` ASC");
        return ['results' => $results];
    }
} 