<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CroneHistories extends Model
{
    use HasFactory;
    protected $table = "crone_histories";
    protected $fillable = [
        "job_history_id",
        "outreach",
        "active_campaign",
        "fivenine",
    ];

}
