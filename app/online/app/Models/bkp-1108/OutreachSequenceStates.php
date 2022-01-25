<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutreachSequenceStates extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    protected $table = 'outreach_sequence_states';
    // public function prospects(){
    //     return $this->hasMany('App\Models\Contacts', 'record_id', 'account_id');
    // }

}
