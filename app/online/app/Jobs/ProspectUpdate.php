<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Controllers\JobsController;
use Illuminate\Support\Facades\DB;

class ProspectUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $data;
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $pData = $this->data;
        //echo 'record id : '.$pData['record_id'].', field : '.$pData['field'];
        $fields = [
            'city' => 'addressCity',
            'country'  => 'addressCountry',
            'state' => 'addressState',
            'address' => 'addressStreet',
            'zip' => 'addressZip',
            'company' => 'company',
            'companyIndustry' => 'companyIndustry',
            'emails' => 'emails', //send as array
            'engage_score' => 'engagedScore',
            'first_name' => 'firstName',
            'homePhones' => 'homePhones',//send as array
            'last_name' => 'lastName',
            'linkedInUrl' => 'linkedInUrl',
            'mobilePhones' => 'mobilePhones',//send as array
            'otherPhones' => 'otherPhones',//send as array
            'source' => 'source',
            'outreach_tag' => 'tags', //send as array
            'name' => 'name',
            'timeZone' => 'timeZone',
            'title' => 'title',
            'voipPhones' => 'voipPhones',//send as array
            'websiteUrl1' => 'websiteUrl1',
            'workPhones' => 'workPhones'//send as array
        ];
        if(array_key_exists($pData['field'], $fields)){
            $prospectField = $fields[$pData['field']];
        }else{
            if(substr($pData['field'], 0, 6) == 'custom'){
                $prospectField = $pData['field'];
            }else{
                $prospectField = null;
            }
        }
        //echo $prospectField;
        if(in_array($prospectField,['tags', 'emails', 'mobilePhones', 'otherPhones', 'homePhones','voipPhones', 'workPhones'])){
            if($prospectField == 'tags'){
                $v = explode(",", $pData['value']);
                (new JobsController)->jobCreated($pData['record_id'], $prospectField, $v);
            }else{
                (new JobsController)->jobCreated($pData['record_id'], $prospectField, $pData['value']);
            }
        }else{
            (new JobsController)->jobCreated($pData['record_id'], $prospectField, $pData['value']);
        }
        DB::table('test_jobs')->insert([
            'record_id' => $pData['record_id'],
            'field' => $prospectField,
            'value' => $pData['value'],
            'created_at' => date('Y-m-d H:i:s', strtotime('now'))
            ]);
        // (new JobsController)->jobCreated($pData['record_id'], $pData['field']);
    }
    
}
