<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\Types\Collection;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class OrderController extends Controller
{
    /** List the authenticated user's orders with their items. */
    #[OA\Get(
        path: '/v1/orders',
        summary: 'List orders',
        security: [['bearerAuth' => []]],
        tags: ['Orders'],
        responses: [new OA\Response(response: 200, description: 'Orders retrieved successfully')]
    )]
    public function index()
    {
        // req gates for view  Orders
        Gate::authorize('view', Order::class);
        $order = Order::with('order_items')->paginate(10);
        return OrderResource::collection($order);
    }

    /** Show one order and its items by ID. */
    #[OA\Get(
        path: '/v1/orders/{id}',
        summary: 'Show an order',
        security: [['bearerAuth' => []]],
        tags: ['Orders'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Order retrieved successfully'),
            new OA\Response(response: 404, description: 'Order not found'),
        ]
    )]
    public function show($id)
    {
        // req gates for view  one single order
        Gate::authorize('view', Order::class);
        //show single order
        return new OrderResource(Order::findOrFail($id));
    }

    /** Export all orders as a CSV file. */
    #[OA\Get(
        path: '/v1/orders/export',
        summary: 'Export orders',
        security: [['bearerAuth' => []]],
        tags: ['Orders'],
        responses: [new OA\Response(response: 200, description: 'CSV export generated successfully')]
    )]
    public function export()
    {
             Gate::authorize('view', Order::class);
        // header
        $headers = [
            "Content-Type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=orders.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0",
        ];
        // callback
        $callback = function () {
            // Get orders
            $orders = Order::all();

            // Open file with write permission
            $file = fopen("php://output", "w");

            // Create header row
            fputcsv($file, ["ID", "Name", "Email", "Product Title", "Price", "Quantity"]);

            // Attach body to header row
            foreach ($orders as $order) {
                foreach ($order->order_items as $orderItem) {
                    fputcsv($file, [
                        $order->id,
                        $order->name,
                        $order->email,
                        $orderItem->product,
                        $orderItem->price,
                        $orderItem->quantity
                    ]);
                }
            }

            // Close the file after all data is written
            fclose($file);
        };

        return \Response::stream($callback, 200, $headers);
    }
}
