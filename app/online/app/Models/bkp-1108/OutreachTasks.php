<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutreachTasks extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    protected $table = 'outreach_tasks';
    // public function prospects(){
    //     return $this->hasMany('App\Models\Contacts', 'record_id', 'account_id');
    // }

}
