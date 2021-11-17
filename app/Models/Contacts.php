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

    public function mtotalcall()
    {
        return $this->hasOne('App\Models\FivenineCallLogs', 'record_id', 'record_id')->selectRaw('fivenine_call_logs.record_id,SUM(fivenine_call_logs.dial_attempts) as totalcall')->where('dial_attempts', '>=', 1)->where('number_type', 'm')->groupBy('fivenine_call_logs.record_id');
    }

    public function wtotalcall()
    {
        return $this->hasOne('App\Models\FivenineCallLogs', 'record_id', 'record_id')->selectRaw('fivenine_call_logs.record_id,SUM(fivenine_call_logs.dial_attempts) as totalcall')->where('dial_attempts', '>=', 1)->where('number_type', 'hq')->groupBy('fivenine_call_logs.record_id');
    }

    public function dtotalcall()
    {
        return $this->hasOne('App\Models\FivenineCallLogs', 'record_id', 'record_id')->selectRaw('fivenine_call_logs.record_id,SUM(fivenine_call_logs.dial_attempts) as totalcall')->where('dial_attempts', '>=', 1)->where('number_type', 'd')->groupBy('fivenine_call_logs.record_id');
    }

    public function mtotalrcall()
    {
        return $this->hasMany('App\Models\FivenineCallLogs', 'record_id', 'record_id')->where('call_received', 1)->where('dial_attempts', '>=', 1)->where('number_type', 'm');
    }

    public function wtotalrcall()
    {
        return $this->hasMany('App\Models\FivenineCallLogs', 'record_id', 'record_id')->where('call_received', 1)->where('dial_attempts', '>=', 1)->where('number_type', 'hq');
    }

    public function dtotalrcall()
    {
        return $this->hasMany('App\Models\FivenineCallLogs', 'record_id', 'record_id')->where('call_received', 1)->where('dial_attempts', '>=', 1)->where('number_type', 'd');
    }

    public function emaillog()
    {
        return $this->hasOne('App\Models\FivenineCallLogs', 'record_id', 'record_id')->selectRaw('fivenine_call_logs.record_id,SUM(fivenine_call_logs.w_dial_attempts) as totalwcall')->groupBy('fivenine_call_logs.record_id');
    }

    public function totalclick()
    {
        return $this->hasMany('App\Models\OutreachMailings', 'contact_id', 'record_id')->where('clickCount', '>=', 1);
    }

    public function totalemail()
    {
        return $this->hasMany('App\Models\OutreachMailings', 'contact_id', 'record_id')->where('deliveredAt', '!=', null);
    }

    public function totalbounced()
    {
        return $this->hasMany('App\Models\OutreachMailings', 'contact_id', 'record_id')->where('bouncedAt', '!=', null);
    }

    public function totalopen()
    {
        return $this->hasMany('App\Models\OutreachMailings', 'contact_id', 'record_id')->where('openCount', '>=', 1);
    }

    public function totalreply()
    {
        return $this->hasMany('App\Models\OutreachMailings', 'contact_id', 'record_id')->where('repliedAt', '!=', null);
    }

    public function calllogs(){
        return $this->hasMany('App\Models\FivenineCallLogs', 'record_id', 'record_id');
    }

    public function customfields(){
        return $this->hasOne('App\Models\ContactCustoms', 'contact_id', 'record_id');
    }

}
