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
    public function account(){
        return $this->hasOne('App\Models\OutreachAccounts', 'account_id', 'account_id');
    }

}
