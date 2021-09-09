<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Settings;

class Biorev extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    
}
