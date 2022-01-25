<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Settings;
use App\Models\Stages;
use App\Models\Contacts;
use App\Models\DatasetGroups;
use App\Models\GraphSearchFilters;
use App\Models\FivenineCallLogs;
use App\Models\OutreachMailings;
use App\Models\OutreachSequences;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function GetConfigs()
    {
        $settings = Settings::all();
        $configs = [];
        foreach ($settings as $key => $setting) {
            $configs[$setting->name] = $setting->value;
        }
        return ['configs' => $configs];
    }

    public function MappingRange()
    {
        $data = ['first_name' => ['First Name', 'first name', 'first_name', 'FirstName', 'first-name'],
                'last_name' => ['Last Name', 'last name', 'last_name', 'LastName', 'last-name'],
                'record_id' => ['Record ID', 'id', 'ID', 'Record id', 'Id', 'Record id', 'record id'],
                'number1' => ['number1', 'Number1', 'Number 1', 'number 1', 'Mobile Phone', 'mobile phone', 'Mobile phone'],
                'number3' => ['number3', 'Number3', 'Number 3', 'number 3', 'Home Phone', 'home phone', 'Home phone'],
                'number2' => ['number2', 'Number2', 'Number 2', 'number 2', 'Work Phone', 'work phone', 'Work phone'],
                'stage' => ['Stage', 'Stages', 'stage', 'stages', 'Stage Name', 'Stage name', 'stage name'],
                'state' => ['State', 'state'],
                'street' => ['street', 'Street', 'AddressStreet'],
                'city' => ['city', 'City'],
                'company' => ['Company', 'company'],
                'email' => ['email', 'EMail', 'Email', 'emails', 'Emails', 'emails'],
                'tag' => ['Tag', 'tags', 'Tags', 'tag'],
                'zip' => ['Zip', 'zip', 'zipcode', 'Zipcode'] ,
                'country' => ['country', 'Country', 'country name', 'Country Name', 'Country name', 'country']
            ];
        return $data;
    }

    public function AllStages()
    {
        $stages = Stages::get();
        $allStages = [];
        foreach ($stages as $key => $stage) {
            $allStages[] = ['oid' => $stage->oid,
                            'stage' => $stage->name,
                            'count' => Contacts::where('stage', $stage->oid)->count()];
        }
        return $allStages;
    }

    public function AllStagesData()
    {
        $stages = Stages::get();
        $allStages = [];
        foreach ($stages as $key => $stage) {
            $allStages[] = ['oid' => $stage->oid,
                            'stage' => $stage->name];
        }
        return $allStages;
    }
    
    public function AllDatasets()
    {
        return DatasetGroups::get();
    }

    public function AllFilterDatasets(){
        return ['results' => DB::table('dataset_groups')->get()];
    }

    public function updateGraphFilterOrder(Request $request)
    {
        foreach ($request->glist as $key => $value) {
            $record = GraphSearchFilters::find($value['id']);
            $record->update(['order_no' => $value['order_no']]);
        }
        return ['status' => 'successfully updated'];
    }

    public function getGraphFilters()
    {
        return GraphSearchFilters::orderBy('order_no', 'asc')->get();
    }

    public function getCallEmailStatus(Request $request)
    {
        $return = [];
        foreach ($request->data as $key => $value) {
            $calls = FivenineCallLogs::where('record_id', $value)->count();
            $emails = OutreachMailings::where('contact_id', $value)->count();
            $return[] = ['record_id' => $value, 'calls' => $calls, 'emails' => $emails];
        }
        return $return;
    }
}
