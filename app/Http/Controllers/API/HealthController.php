<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;

class HealthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function columnHealth(Request $request)
    {
			$response = [];
				foreach ($request->fields as $key => $value) {
						$correct = DB::table('contacts')->where($value, '!=', NULL)->where($value, '!=', 0)->where($value, '!=', '')->count();
						$empty = DB::table('contacts')->where(function ($query) use ($value) {
							$query->where($value, NULL)
										->orWhere($value, 0)->orWhere($value, '');
							})->count();
						$incorrect = 0;
						$response[$value] = [$correct, $incorrect, $empty];
				}
			return $response;
		}
    
}