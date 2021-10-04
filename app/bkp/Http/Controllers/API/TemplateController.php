<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Biorev;
use App\Models\Templates;

class TemplateController extends Biorev
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function addTemplate(Request $request)
    {
        $data = ['name' => $request['name'],'type' => $request['source'],
            'mapped' => json_encode(['sourced' => $request['sfields'], 'dest' => $request['dfields']])];
        $result = Templates::create($data);
        return ['status' => 'success', 'id' => $result->id];
    }

    public function getTypeTemplate($type)
    {
        return Templates::where('type', $type)->orderBy('id', 'desc')->get();
    }

    
}