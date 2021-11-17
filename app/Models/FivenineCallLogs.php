<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FivenineCallLogs extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    protected $table = 'fivenine_call_logs';
    public function contactData(){
        return $this->hasOne('App\Models\Contacts', 'record_id', 'record_id');
    }

}
