<?php

namespace App\Http\Controllers;

use App\Returns;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturnController extends Controller
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

    public function store(Request $request, $id)
    {
        try {
            $return = Returns::create([
                'order_id' => $id,
                'return_price' => $request->price,
                'return_comment' => $request->comment
            ]);
            foreach ($request->products as $product) {
                $products = DB::table('return_products')->insert([
                    'return_id' => $return->return_id,
                    'products_color_size_id' => $product['id'],
                    'return_quantity' => $product['quantity'],
                ]);
                $colorSizeProduct = DB::table('color_size_product')
                    ->where('color_size_product_id', '=', $product['id'])
                    ->increment('quantity', $product['quantity']);
                $orderProducts = DB::table('order_products')
                    ->where([
                        ['product_option_id', '=', $product['id']],
                        ['order_id', '=', $id],
                    ])
                    ->update(['return_quantity' => $product['quantity']]);
            }
        } catch (\Exception $exception) {
            return $this->showMessage('Ошибка при оформлении возврата!', 400);
        }

        return response()->json('OK');
    }
}
