<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExportHistories extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    protected $table = 'export_histories';

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

}
