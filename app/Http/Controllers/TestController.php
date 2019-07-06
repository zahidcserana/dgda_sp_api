<?php


namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use DB;
use Illuminate\Support\Facades\Config;

class TestController extends Controller
{
    public function medicineScript()
    {
        $dgda_pharmaceuticals_company_lists = DB::table('dgda_pharmaceuticals_company_lists')->get();
        foreach ($dgda_pharmaceuticals_company_lists as $company_list) {
            DB::table('dgda_medicines_list')->where('manufacturer_name', 'like', '%' . $company_list->manufacturer_name . '%')->update(array('manufacturer_id' => $company_list->id));
        }

    }

    public function medicineTypeScript()
    {
        $dosages_desc_lists = DB::table('dosages_desc_lists')->get();
        foreach ($dosages_desc_lists as $desc_list) {
            DB::table('dgda_medicines_list')->where('dosage_description', 'like', '%' . $desc_list->dosage_desc . '%')->update(array('dosage_description' => $desc_list->id));
        }

    }

    public function test()
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
