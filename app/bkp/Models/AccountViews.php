<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountViews extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'account_views';
}
