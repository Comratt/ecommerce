<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    protected $primaryKey = 'product_id';

    public static function getAllProducts()
    {
        $products = self::select(DB::raw('*'))
            ->leftJoin('product_descriptions', 'products.product_id', '=', 'product_descriptions.product_id')
            ->paginate(30);

        $products->map(function ($product) {
            $categories = DB::table('product_categories')
                ->where('product_id', $product->product_id)
                ->leftJoin('categories', 'categories.category_id', '=', 'product_categories.product_id')
                ->leftJoin('category_descriptions', 'category_descriptions.category_id', '=', 'product_categories.category_id')
                ->get();
            $options = DB::table('product_options')
                ->where('product_id', $product->product_id)
                ->leftJoin('options', 'product_options.option_id', 'options.option_id')
                ->leftJoin('option_values', 'product_options.option_value_id', 'option_values.option_value_id')
                ->get();
            $images = DB::table('product_images')
                ->where('product_id', $product->product_id)
                ->get();
            $product['categories'] = $categories;
            $product['options'] = $options;
            $product['images'] = $images;

            return $product;
        });

        return $products;
    }

    public static function showProductById(int $id)
    {
        $productItem = self::find($id)
            ->join('product_descriptions', 'products.product_id', '=', 'product_descriptions.product_id')
            ->first();

        $categories = DB::table('product_categories')
            ->where('product_id', $productItem->product_id)
            ->leftJoin('categories', 'categories.category_id', '=', 'product_categories.product_id')
            ->leftJoin('category_descriptions', 'category_descriptions.category_id', '=', 'product_categories.category_id')
            ->get();
        $options = DB::table('product_options')
            ->where('product_id', $productItem->product_id)
            ->leftJoin('options', 'product_options.option_id', 'options.option_id')
            ->leftJoin('option_values', 'product_options.option_value_id', 'option_values.option_value_id')
            ->get();
        $images = DB::table('product_images')
            ->where('product_id', $productItem->product_id)
            ->get();
        $productItem['categories'] = $categories;
        $productItem['options'] = $options;
        $productItem['images'] = $images;

        return $productItem;
    }


}
