<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tasks;

class TasksController extends Controller
{
    public function index()
    {
        return Tasks::orderBy('id', 'desc')->get();
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        $model = Tasks::where('id', $id)->get()->first();
        $request->validate([
            'title' => 'required',
            'command_line' => 'required',
            'status' => 'required',
        ]);
        $model->update(['title' => $request->title,
                        'status' =>  $request->status,
                        'command_line' => $request->command_line,
                        'month' => $request->month, 
                        'minutes' => $request->minutes, 
                        'day' => $request->day, 
                        'weekday' => $request->weekday,
                        'hour' => $request->hour]);
        return ['record updated successfully'];
    }

    public function destroy(Request $request, $id)
    {
        $model = Tasks::where('id', $id)->get()->first();
        $model->delete();
        return ['record deleted successfully'];
    }
}
