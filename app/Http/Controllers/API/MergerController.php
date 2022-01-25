<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Contacts;

class MergerController extends Controller
{
    public function recordMerging(Request $request)
    {
        $deletable = Contacts::whereIn('record_id', $request->data)->where('record_id', '!=', $request->primary_data)->get();
        $keeper = Contacts::where('record_id', $request->primary_data)->first();
        foreach ($deletable as $value) {
            $mdata = [];
            if($keeper->first_name == '') {  $mdata['first_name'] = $value->first_name; }
            if($keeper->last_name == '') {  $mdata['last_name'] = $value->last_name; }
            if($keeper->company == '') {  $mdata['company'] = $value->company; }
            if($keeper->title == '') {  $mdata['title'] = $value->title; }
            if($keeper->mobilePhones == '') {  $mdata['mobilePhones'] = $value->mobilePhones; }
            if($keeper->homePhones == '') {  $mdata['homePhones'] = $value->homePhones; }
            if($keeper->workPhones == '') {  $mdata['workPhones'] = $value->workPhones; }
            if($keeper->emails == '') {  $mdata['emails'] = $value->emails; }
            if($keeper->outreach_tag == '') {  $mdata['outreach_tag'] = $value->outreach_tag; }
            if($keeper->city == '') {  $mdata['city'] = $value->city; }
            if($keeper->state == '') {  $mdata['state'] = $value->state; }
            if($keeper->country == '') {  $mdata['country'] = $value->country; }
            if($keeper->custom1 == '') {  $mdata['custom1'] = $value->custom1; }
            if($keeper->custom2 == '') {  $mdata['custom2'] = $value->custom2; }
            if($keeper->custom9 == '') {  $mdata['custom9'] = $value->custom9; }
            if($keeper->custom10 == '') {  $mdata['custom10'] = $value->custom10; }
            if($keeper->custom11 == '') {  $mdata['custom11'] = $value->custom11; }
            if($keeper->custom12 == '') {  $mdata['custom12'] = $value->custom12; }
            if($keeper->custom29 == '') {  $mdata['custom29'] = $value->custom29; }
            if($keeper->address == '') {  $mdata['address'] = $value->address; }
            if($keeper->companyIndustry == '') {  $mdata['companyIndustry'] = $value->companyIndustry; }
            if($keeper->zip == '') {  $mdata['zip'] = $value->zip; }
            if($keeper->source == '') {  $mdata['source'] = $value->source; }
            $keeper->update($mdata);
            $value->delete();
        }
        return ['status' => 'success'];
    }

    public function recordDeleting(Request $request)
    {
        $deletable = Contacts::whereIn('record_id', $request->data)->where('record_id', '!=', $request->primary_data)->get();
        foreach ($deletable as $value) {
            $value->delete();
        }
        return ['status' => 'success'];
    }

}
