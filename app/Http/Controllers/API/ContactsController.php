<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Contacts;

class ContactsController extends Controller
{
    public function getShortDetails($recordID = null)
    {
        $result = DB::table('contacts')->select('name', 'mobilePhones', 'workPhones', 'homePhones', 'record_id')
                                        ->where('record_id', $recordID)->first();

        $mrecords = DB::table('fivenine_call_logs')->where('record_id', $recordID)->where('dial_attempts', '>=', 1)->where('dnis', $this->__Formatter($result->mobilePhones))->orderBy('id', 'desc')->get();
        $hrecords = DB::table('fivenine_call_logs')->where('record_id', $recordID)->where('dial_attempts', '>=', 1)->where('dnis', $this->__Formatter($result->homePhones))->orderBy('id', 'desc')->get();
        $drecords = DB::table('fivenine_call_logs')->where('record_id', $recordID)->where('dial_attempts', '>=', 1)->where('dnis', $this->__Formatter($result->workPhones))->orderBy('id', 'desc')->get();
        $totalmattempt = DB::table('fivenine_call_logs')->where('record_id', $recordID)->where('dial_attempts', '>=', 1)->where('dnis', $this->__Formatter($result->mobilePhones))->sum('dial_attempts');
        $totalhattempt = DB::table('fivenine_call_logs')->where('record_id', $recordID)->where('dial_attempts', '>=', 1)->where('dnis', $this->__Formatter($result->homePhones))->sum('dial_attempts');
        $totaldattempt = DB::table('fivenine_call_logs')->where('record_id', $recordID)->where('dial_attempts', '>=', 1)->where('dnis', $this->__Formatter($result->workPhones))->sum('dial_attempts');
        $totalmreceived = DB::table('fivenine_call_logs')->where('record_id', $recordID)->where('call_received', 1)->where('dial_attempts', '>=', 1)->where('dnis', $this->__Formatter($result->mobilePhones))->count();
        $totalhqreceived = DB::table('fivenine_call_logs')->where('record_id', $recordID)->where('call_received', 1)->where('dial_attempts', '>=', 1)->where('dnis', $this->__Formatter($result->homePhones))->count();
        $totaldreceived = DB::table('fivenine_call_logs')->where('record_id', $recordID)->where('call_received', 1)->where('dial_attempts', '>=', 1)->where('dnis', $this->__Formatter($result->workPhones))->count();
        $totalmdispo = DB::table('fivenine_call_logs')->where('record_id', $recordID)->where('call_received', 1)->where('dial_attempts', '>=', 1)->where('dnis', $this->__Formatter($result->mobilePhones))->orderBy('id', 'desc')->pluck('disposition');
        $totalhqdispo = DB::table('fivenine_call_logs')->where('record_id', $recordID)->where('call_received', 1)->where('dial_attempts', '>=', 1)->where('dnis', $this->__Formatter($result->homePhones))->orderBy('id', 'desc')->pluck('disposition');
        $totalddispo = DB::table('fivenine_call_logs')->where('record_id', $recordID)->where('call_received', 1)->where('dial_attempts', '>=', 1)->where('dnis', $this->__Formatter($result->workPhones))->orderBy('id', 'desc')->pluck('disposition');      
        return ['result' => [
                    'name' => $result->name,
                    'record_id' => $result->record_id,
                    'mobilePhone' => ($result->mobilePhones)?['records' => $mrecords, 'phone' => $result->mobilePhones, 'called' => $totalmattempt, 'received' => $totalmreceived, 'disposition' => $totalmdispo]:'',
                    'workPhone' => ($result->workPhones)?['records' => $hrecords, 'phone' => $result->workPhones, 'called' => $totaldattempt, 'received' => $totaldreceived, 'disposition' => $totalddispo]:'',
                    'homePhone' => ($result->homePhones)?['records' => $drecords, 'phone' => $result->homePhones, 'called' => $totalhattempt, 'received' => $totalhqreceived, 'disposition' => $totalhqdispo]:''
        ]];
    }

    private function __Formatter($var = null)
    {
        $string = str_replace(' ', '', $var); // Replaces all spaces.
        $string = str_replace('-', '', $string); // Replaces all hyphens.
        $string = str_replace('.', '', $string); // Replaces all hyphens.
        $string = str_replace('(', '', $string); // Replaces all hyphens.
        $string = str_replace(')', '', $string); // Replaces all hyphens.
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
        if(strpos($string, 'ext') > 0){
           $d = explode("ext", $string);
           $string = $d[0];
        }
        if(strpos($string, 'EXT') > 0){
            $d = explode("EXT", $string);
            $string = $d[0];
        }
        if(strlen($string) > 10) {
            $string = strrev($string);
            $string = substr($string, 0, 10);
            $string = strrev($string);
        } else {
            $string = substr($string, 0, 10);
        }
        $string = (int) $string;
        return $string;
    }
}
