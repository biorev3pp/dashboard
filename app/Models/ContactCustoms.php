<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Haruncpi\LaravelUserActivity\Traits\Loggable;

class ContactCustoms extends Model
{
    use Loggable, HasFactory;
    
    protected $guarded = [];
    protected $table = 'contact_customs';

}
