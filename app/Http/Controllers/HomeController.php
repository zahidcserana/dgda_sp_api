<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
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
        $orders = Order::where('is_sync', 0)->get();

        $db_ext = \DB::connection('live');
        $itemIds = array();
        foreach ($orders as $order) {
            $items = $order->items()->get();

            $itemIds[] = $order->id;
            unset($order->id);
            $order = $order->toArray();
            $itemId = $db_ext->table('orders')->insertGetId($order);

            foreach ($items as $item) {
                $local_item_id = $item->id;
                unset($item->id);
                $item->order_id = $itemId;
                $item = $item->toArray();
                $server_item_id = $db_ext->table('order_items')->insertGetId($item);
                OrderItem::find($local_item_id)->update(['server_item_id' => $server_item_id]);
            }
        }
        DB::table('orders')->whereIn('id', $itemIds)->update(array('is_sync' => 1));
        $this->statusSync();
        return response()->json(['success' => true]);
    }

    public function statusSync()
    {
        $items = OrderItem::where(['is_status_updated' => 1, 'is_status_sync' => 0])->get();

        $db_ext = \DB::connection('live');
        $itemIds = array();
        foreach ($items as $item) {
            $db_ext->table('order_items')->where('id', $item->server_item_id)->update(['status' => $item->status]);

            $itemIds[] = $item->id;
        }
        DB::table('order_items')->whereIn('id', $itemIds)->update(array('is_status_sync' => 1));

        return true;
    }
}
