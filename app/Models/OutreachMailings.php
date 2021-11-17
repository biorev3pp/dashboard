<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutreachMailings extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    protected $table = 'outreach_mailings';
    public function prospect(){
        return $this->hasOne('App\Models\Contacts', 'record_id', 'contact_id');
    }

}
