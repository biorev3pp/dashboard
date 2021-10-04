<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportMailings extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    protected $table = 'report_mailings';

}
