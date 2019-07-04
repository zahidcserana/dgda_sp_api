<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class MontlyReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monthly:reminder';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update cloud database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $orders = Order::all();

//        foreach ($orders as $order) {
//            $input = array(
//                'status' => 'DELIVERED'
//            );
//            $order->update($input);
//        }
//
        Config::set('database.connections.mysql.database', 'dgda_test');
//
//        $orders = Order::all();
//
//        foreach ($orders as $order) {
//            $input = array(
//                'status' => 'DELIVERED'
//            );
//            $order->update($input);
//        }

//        Config::set('database.default', 'newConnection');
//        DB::reconnect('newConnection');
        foreach ($orders as $order) {
            DB::table('orders')->insert($order);

        }
    }
}
