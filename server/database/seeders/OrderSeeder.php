<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        Order::factory(40)->create()->each(function ($order) {
            OrderItem::factory(random_int(1, 5))->create([
                'order_id' => $order->id,
            ]);
        });
    }
}
