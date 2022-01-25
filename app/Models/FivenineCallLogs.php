<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Haruncpi\LaravelUserActivity\Traits\Loggable;

class FivenineCallLogs extends Model
{
	use Loggable, HasFactory;
    
    protected $guarded = [];
    protected $table = 'fivenine_call_logs';
    public function contactData(){
        return $this->hasOne('App\Models\Contacts', 'record_id', 'record_id');
    }

}
