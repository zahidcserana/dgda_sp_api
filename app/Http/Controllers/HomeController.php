<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function statusSync($statusData)
    {
        foreach ($statusData as $item) {
            OrderItem::where('id', $item['server_item_id'])->update(['status' => $item['status']]);
        }
    }

    public function dataSync()
    {
        $details_data = [];
        $orders = Order::where('is_sync', 0)->get();

        foreach ($orders as $order) :
            $items = $order->items()->get();
            $details_data[] = array('order_details' => $order, 'order_items' => $items);
        endforeach;

        /** status sync start */
        $statusData = OrderItem::where(['is_status_updated' => 1, 'is_status_sync' => 0])
            ->whereNotNull('server_item_id')
            ->get();

        $itemIds = array();
        foreach ($statusData as $item) {
            $itemIds[] = $item->id;
        }
        /** status sync end */

        $data = array(
            'details_data' => $details_data,
            'status_data' => $statusData,
        );
       
        // Make Post Fields Array

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://54.214.203.243:91/data_sync",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                // Set here requred headers
                "accept: */*",
                "accept-language: en-US,en;q=0.8",
                "content-type: application/json",
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $response = json_decode($response);
            if (!empty($response)) {
                
                
                $data = $response->data;
                foreach ($data as $order) {
                    Order::find($order->local_order_id)->update(['server_order_id' => $order->server_order_id, 'is_sync' => 1]);
                    foreach ($order->items as $item) {
                        OrderItem::find($item->local_item_id)->update(['server_item_id' => $item->server_item_id]);
                    }
                }
                if(!empty($itemIds)){
                    DB::table('order_items')->whereIn('id', $itemIds)->update(array('is_status_sync' => 1));
                }
            }


            //  print_r(json_decode($response));
        }

        return response()->json([
            'success' => true,
            'data' => $details_data,
            'message' => 'Response is pending from the Server!'
        ], 200);
    }

    public function dataSyncToDB(Request $request)
    {
       
        $data = json_decode(file_get_contents('php://input'), true);
        $inserted_items = [];
        $inserted_item_ids = [];
        if(!empty($data['status_data'])){
            $statusData = $data['status_data'];
       
            $this->statusSync($statusData);
        }
       
        $all_datas = $data['details_data'];

        foreach ($all_datas as $data) :

            $order_details = $data['order_details'];
            $order_items = $data['order_items'];

            if (count($order_details)) {

                $insert_order = new Order();

                $insert_order->token = $order_details['token'];
                $insert_order->pharmacy_id = $order_details['pharmacy_id'];
                $insert_order->created_by = $order_details['created_by'];
                $insert_order->pharmacy_branch_id = $order_details['pharmacy_branch_id'];
                $insert_order->invoice = $order_details['invoice'];
                $insert_order->company_id = $order_details['company_id'];
                $insert_order->company_invoice = $order_details['company_invoice'];
                $insert_order->mr_id = $order_details['mr_id'];
                $insert_order->purchase_date = $order_details['purchase_date'];
                $insert_order->quantity = $order_details['quantity'];
                $insert_order->sub_total = $order_details['sub_total'];
                $insert_order->tax = $order_details['tax'];
                $insert_order->discount = $order_details['discount'];
                $insert_order->total_amount = $order_details['total_amount'];
                $insert_order->total_payble_amount = $order_details['total_payble_amount'];
                $insert_order->total_advance_amount = $order_details['total_advance_amount'];
                $insert_order->total_due_amount = $order_details['total_due_amount'];
                $insert_order->payment_type = $order_details['payment_type'];
                $insert_order->status = $order_details['status'];
                $insert_order->remarks = $order_details['remarks'];
                $insert_order->is_manual = $order_details['is_manual'];
                $insert_order->is_sync = $order_details['is_sync'];
                $insert_order->created_at = $order_details['created_at'];
                $insert_order->updated_at = $order_details['updated_at'];
                $insert_order->save();

                $local_order_id = $order_details['id'];
                $server_order_id = $insert_order->id;
            }

            if (count($order_items)) {

                foreach ($order_items as $item) :

                    $insert_item = new OrderItem();
                    $insert_item->medicine_id = $item['medicine_id'];
                    $insert_item->company_id = $item['company_id'];
                    $insert_item->quantity = $item['quantity'];
                    $insert_item->order_id = $server_order_id;
                    $insert_item->exp_date = $item['exp_date'];
                    $insert_item->mfg_date = $item['mfg_date'];
                    $insert_item->batch_no = $item['batch_no'];
                    $insert_item->dar_no = $item['dar_no'];
                    $insert_item->power = $item['power'];
                    $insert_item->unit_price = $item['unit_price'];
                    $insert_item->sub_total = $item['sub_total'];
                    $insert_item->discount = $item['discount'];
                    $insert_item->total = $item['total'];
                    $insert_item->tax = $item['tax'];
                    $insert_item->status = $item['status'];
                    $insert_item->created_at = $item['created_at'];
                    $insert_item->updated_at = $item['updated_at'];
                    $insert_item->save();

                    $inserted_item_ids[] = array('local_item_id' => $item['id'], 'server_item_id' => $insert_item->id);
                endforeach;
            }

            $inserted_items[] = array('local_order_id' => $local_order_id, 'server_order_id' => $server_order_id, 'items' => $inserted_item_ids);

            $local_order_id = null;
            $server_order_id = null;
            $inserted_item_ids = [];
        endforeach;

        return response()->json([
            'success' => true,
            'data' => $inserted_items,
            'message' => 'Response from the Server!'
        ], 200);
    }

    public function awsData()
    { }
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

    public function dataSync_old()
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

    public function statusSync_old()
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
