<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\DashboardDesigns;

class DashboardDesignsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return ['alldata' => DashboardDesigns::where('admin_id', 1)->get(),
                    'activedata' => DashboardDesigns::where('admin_id', 1)->where('is_default', 1)->get()->first()];
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'position1' => 'required',
            'position2' => 'required',
            'position3' => 'required',
            'position4' => 'required',
            'filter1' => 'required',
            'is_default' => 'nullable',
        ]);
        if($request->is_default == 1) {
            DashboardDesigns::where('admin_id', 1)->update(['is_default' => 0]);
        }
        $new_Design = DashboardDesigns::create(['admin_id' => 1,
                                    'title' => $request->title,
                                    'filter1' => $request->filter1,
                                    'position1' => $request->position1,
                                    'position2' => $request->position2,
                                    'position3' => $request->position3,
                                    'position4' => $request->position4,
                                    'is_default' => $request->is_default
                                    ]);
        if($request->activate_it == 1) {
            return ['alldata' => DashboardDesigns::where('admin_id', 1)->get(),
                    'activedata' => $new_Design];    
        } else {
            return ['alldata' => DashboardDesigns::where('admin_id', 1)->get()];
        }
    }

    public function show($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
            'position1' => 'required',
            'position2' => 'required',
            'position3' => 'required',
            'position4' => 'required',
            'filter1' => 'required',
            'is_default' => 'nullable',
        ]);
        $design = DashboardDesigns::whereId($id)->get()->first();
        if($request->is_default == 1) {
            DashboardDesigns::where('admin_id', 1)->update(['is_default' => 0]);
        }
        $design->update([
                    'title' => $request->title,
                    'filter1' => $request->filter1,
                    'position1' => $request->position1,
                    'position2' => $request->position2,
                    'position3' => $request->position3,
                    'position4' => $request->position4,
                    'is_default' => $request->is_default
                ]);

        return ['alldata' => DashboardDesigns::where('admin_id', 1)->get(),
                    'activedata' =>  $design];    

    }

    public function destroy($id)
    {
        //
    }
}
