<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contacts extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    protected $table = 'contacts';

    public function stageData(){
        return $this->belongsTo('App\Models\Stages', 'stage', 'oid');
    }
    public function tasksTotal(){
        return $this->hasMany('App\Models\OutreachTasks', 'contact_id', 'record_id');
    }
    public function tasksComplete(){
        return $this->hasMany('App\Models\OutreachTasks', 'contact_id', 'record_id')->where('state', '=', 'complete');
    }
    
    public function tasksNotComplete(){
        return $this->hasMany('App\Models\OutreachTasks', 'contact_id', 'record_id')->where('state', '!=', 'complete');
    }
    public function account(){
        return $this->hasOne('App\Models\OutreachAccounts', 'account_id', 'account_id');
    }

    public function stagename(){
        return $this->hasOne('App\Models\Stages', 'stage', 'oid');
    }

    public function customfields(){
        return $this->hasOne('App\Models\ContactCustoms', 'contact_id', 'id');
    }

}
