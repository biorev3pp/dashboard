<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;use Illuminate\Database\Eloquent\Model;
class OutreachAccounts extends Model{    use HasFactory;        protected $guarded = [];    protected $table = 'outreach_accounts';    public function prospects(){
        return $this->hasMany('App\Models\Contacts', 'account_id', 'account_id');
    }
    public function tasks(){
        return $this->hasMany('App\Models\OutreachTasks', 'outreach_account_id', 'account_id')->where('state', '=', 'incomplete');
    }
    public function sequencesActive(){
        return $this->hasMany('App\Models\OutreachSequenceStates', 'account_id', 'account_id')->where('state', '=', 'active');
    }
    public function sequencesInActive(){
        return $this->hasMany('App\Models\OutreachSequenceStates', 'account_id', 'account_id')->where('state', '!=', 'active');
    }
}
