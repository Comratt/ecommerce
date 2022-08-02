<?php

namespace App\Http\Controllers;

use App\OrderHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Order;
use App\OrderProduct;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * @param string $message
     * @param int $status
     * @return \Illuminate\Http\JsonResponse
     */
    private function showMessage(string $message, int $status)
    {
        return response()->json([
            'message' => $message
        ], $status);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $countPerPage = $request->input('count') ?: 20;
            $orderId = $request->input('orderId');
            $status = $request->input('status');
            $createdAt = $request->input('createdAt');
            $updatedAt = $request->input('updatedAt');

            $ordersQuery = Order::select(DB::raw('*, SUM(`order_products`.`total`) as order_total_sum, orders.order_id as order_id, orders.created_at as created_at, orders.updated_at as updated_at'))
                ->leftJoin('promocodes', 'orders.promocode_id', '=', 'promocodes.promocodes_id')
                ->leftJoin('order_products', 'order_products.order_id', '=', 'orders.order_id');

            if ($orderId) {
                $ordersQuery->where('orders.order_id', 'LIKE', "%{$orderId}%");
            }
            if ($status) {
                $ordersQuery->where('orders.status_id', '=', $status);
            }
            if ($createdAt) {
                $ordersQuery->whereDate('orders.created_at', '=', Carbon::createFromFormat('Y-m-d', $createdAt)->toDateString());
            }
            if ($updatedAt) {
                $ordersQuery->whereDate('orders.updated_at', '=', Carbon::createFromFormat('Y-m-d', $updatedAt)->toDateString());
            }

            $orders = $ordersQuery->orderBy('orders.created_at', 'desc')
                ->groupBy('orders.order_id')
                ->paginate($countPerPage);

            return response()->json($orders, 200);
        } catch (\Exception $exception) {
            return $this->showMessage('Ошибка при загрузки заказов!', 400);
        }
    }

    public function getByEmail(Request $request)
    {
//        try {
            $countPerPage = $request->input('count') ?: 20;
            $email = $request->input('email');

            $ordersQuery = Order::select(DB::raw('*, order_products.price as price, orders.created_at as created_at, orders.updated_at as updated_at'))
                ->leftJoin('order_products', 'order_products.order_id', '=', 'orders.order_id')
                ->leftJoin('products', 'order_products.product_id', '=', 'products.product_id');

            if ($email) {
                $ordersQuery->where('orders.email', '=', $email);
            }

            $orders = $ordersQuery->orderBy('orders.created_at', 'desc')
                ->paginate($countPerPage);

            return response()->json($orders, 200);
//        } catch (\Exception $exception) {
//            return $this->showMessage('Ошибка при загрузки заказов!', 400);
//        }
    }

    public function show(Request $request, $id)
    {
        try {
            $order = Order::select(DB::raw('*, SUM(`order_products`.`total`) as order_total_sum'))
                ->leftJoin('promocodes', 'orders.promocode_id', '=', 'promocodes.promocodes_id')
                ->leftJoin('order_products', 'order_products.order_id', '=', 'orders.order_id')
                ->where(['orders.order_id' => $id])
                ->groupBy('orders.order_id')
                ->first();

            $findOrder = Order::where(['order_id' => $id])->first();
            if ($findOrder->viewed == 0) {
                $findOrder->viewed = 1;

                $findOrder->save();
            }

            $orderProducts = DB::table('order_products')
                ->select(DB::raw('*, color_size_product.quantity as color_size_quantity, order_products.quantity as quantity, products.price as price'))
                ->leftJoin('products', 'order_products.product_id', 'products.product_id')
                ->leftJoin('color_size_product', 'order_products.product_option_id', '=', 'color_size_product.color_size_product_id')
                ->where('order_products.order_id', $id)
                ->get();
            $orderHistory = DB::table('order_history')
                ->where('order_history.order_id', $id)
                ->get();

            $order['products'] = $orderProducts;
            $order['history'] = $orderHistory;

            return response()->json($order, 200);
        } catch (\Exception $exception) {
            return $this->showMessage('Ошибка при загрузки заказов!', 400);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'firstName' => 'required|string',
            'lastName' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required',
            'shippingCity' => 'required|string',
            'shippingAddress' => 'required|string',
            'areaName' => 'required|string',
        ]);

//        try {
                if ($request->products) {
                    foreach ($request->products as $product) {
                        $findRelated = DB::table('color_size_product')
                            ->where([
                                'product_id' => $product['id'],
                                'color_id' => $product['colorId'],
                                'size_id' => $product['sizeId'],
                            ])->first();

                        if ($findRelated) {
                            if ($findRelated->quantity - $product['quantity'] < 0) {
                                return $this->showMessage('Товара с такими параметрами нет!', 400);
                            }
                        }
                    }
                } else {
                    return $this->showMessage('Товарів нема!', 400);
                }
                $token = "5489467175:AAG_KRuxmEUR6d4Jo6auFh2RuxNRdYRjJIU";
                $chat_id = "-694122577";
                $order = Order::create([
                    'status_id' => 1,
                    'first_name' => $request->firstName,
                    'last_name' => $request->lastName,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'shipping_city' => $request->shippingCity,
                    'shipping_area' => $request->areaName,
                    'shipping_address' => $request->shippingAddress,
                    'comment' => $request->comment ?: '',
                    'promocode_id' => $request->discount ? $request->discount['id'] : null,
                    'promocode_discount' => $request->discount ? $request->discount['total'] : 0,
                ]);

                $botTextOrder = '<b>Інформація про замовлення</b>%0A%0A';
                foreach ($request->products as $product) {
                    try {
                        fopen("https://api.telegram.org/bot{$token}/sendPhoto?chat_id={$chat_id}&photo={$product['image']}", "r");
                    } catch (\Exception $telegramException) {

                    }
                    $price = $product['purePrice'] * $product['quantity'];
                    $botTextOrder .= "<i>{$product['name']} - колір({$product['color']}), розмір({$product['size']}) - {$product['quantity']} * {$product['purePrice']}₴ = {$price}₴</i> %0A";
                    $orderProduct = OrderProduct::create([
                        'order_id' => $order->order_id,
                        'product_id' => $product['id'],
                        'quantity' => $product['quantity'],
                        'price' => $product['purePrice'],
                        'total' => $product['purePrice'] * $product['quantity'],
                        'size' => $product['size'],
                        'color' => $product['color'],
                    ]);
                    DB::table('color_size_product')
                        ->where([
                            'product_id' => $product['id'],
                            'color_id' => $product['colorId'],
                            'size_id' => $product['sizeId'],
                        ])
                        ->update([
                            'quantity' => DB::raw("quantity - {$product['quantity']}"),
                            'updated_at' => Carbon::now(),
                        ]);
                    $findSizeColor = DB::table('color_size_product')
                        ->where([
                            'product_id' => $product['id'],
                            'color_id' => $product['colorId'],
                            'size_id' => $product['sizeId'],
                        ])->first();
                    $orderProduct->product_option_id = $findSizeColor->color_size_product_id;
                    $orderProduct->save();
                }

                OrderHistory::create([
                    'order_id' => $order->order_id,
                    'notify_customer' => 1,
                    'history_comment' => '',
                    'history_status' => 1,
                ]);
                $botTextOrder .= '%0A<b>Замовник:</b>%0A';
                $botTextOrder .= '<i>'. $request->firstName . ' ' . $request->lastName .': </i><a herf="tel:' . $request->phone . '">' . $request->phone . '</a>%0A';
                $botTextOrder .= '%0A<a href="http://www.demo-storee.manager-app.xyz/admin/order/' . "{$order->order_id}" . '">Посилання на замовлення</a>';
                fopen("https://api.telegram.org/bot{$token}/sendMessage?chat_id={$chat_id}&parse_mode=html&text={$botTextOrder}", "r");

                return response()->json($token);
//        } catch (\Exception $exception) {
//            return $this->showMessage('Ошибка при оформлении заказа!', 400);
//        }
    }

    public function addHistory(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'shippingCode' => 'required'
        ]);

        try {
            $order_history = OrderHistory::create([
                'order_id' => $request->input('id'),
                'notify_customer' => $request->input('notify'),
                'history_comment' => $request->input('comment'),
                'history_status' => $request->input('shippingCode'),
            ]);

            $order = Order::find($request->input('id'));
            if ($order) {
                $order->status_id = $request->input('shippingCode');
                $order->save();
            }

            return response()->json($order_history);
        } catch (\Exception $exception) {
            return $this->showMessage('Ошибка при оформлении заказа!', 400);
        }
    }
}
