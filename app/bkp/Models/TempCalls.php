<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempCalls extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    protected $table = 'temp_calls';

}
