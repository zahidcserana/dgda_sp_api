<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function districtList()
    {
        $districts = DB::table('districts')->get();

        return response()->json($districts);
    }

    public function areaList($cityId)
    {
        $areas = DB::table('areas')->where('district_id', $cityId)->get();

        return response()->json($areas);
    }

    public function CompanyList()
    {
        $companies = DB::table('medicine_companies')->get();

        return response()->json($companies);
    }

    public function dataSync()
    {
        $data = Order::where('is_sync', 0)->get();

        $db_ext = \DB::connection('live');
        $itemIds = array();
        foreach ($data as $item) {
            $itemIds[] = $item->id;
            unset($item->id);
            $item = $item->toArray();
            $db_ext->table('orders')->insert($item);
        }
        DB::table('orders')->where('id IN', $itemIds)->update(array('is_sync' => 1));
    }
}
