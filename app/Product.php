<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    protected $primaryKey = 'product_id';

    public static function getAllProducts($page_count, $is_available, $search, $category, $sortBy, $price, $color, $size, $ids, $name, $model)
    {
        $productsQuery = self::select(DB::raw('*, products.product_id as p_id, products.price as price, products.product_id as product_id, products.image as image'))
            ->leftJoin('product_descriptions', 'products.product_id', '=', 'product_descriptions.product_id');

        if ($ids && count($ids)) {
            $productsQuery->whereIn('products.product_id', $ids);
        }
        if ($search) {
            $productsQuery->where('name', 'LIKE', "%{$search}%")->orWhere('model', 'LIKE', "%{$search}%");
        }
        if ($name) {
            $productsQuery->where('name', 'LIKE', "%{$name}%");
        }
        if ($model) {
            $productsQuery->where('model', 'LIKE', "%{$model}%");
        }
        if ($color && !$size) {
            $productsQuery
                ->leftJoin('color_size_product', 'products.product_id', '=', 'color_size_product.product_id')
                ->leftJoin('product_options', 'product_options.product_option_id', '=', 'color_size_product.color_id')
                ->leftJoin('option_values', 'option_values.option_value_id', '=', 'product_options.option_value_id')
                ->whereIn('option_values.option_value_id', $color);
        } else if ($size && !$color) {
            $productsQuery
                ->leftJoin('color_size_product', 'products.product_id', '=', 'color_size_product.product_id')
                ->leftJoin('product_options', 'product_options.product_option_id', '=', 'color_size_product.size_id')
                ->leftJoin('option_values', 'option_values.option_value_id', '=', 'product_options.option_value_id')
                ->whereIn('option_values.option_value_id', $size);
        } else if ($size && $color) {
            $productsQuery
                ->leftJoin('color_size_product', 'products.product_id', '=', 'color_size_product.product_id')
                ->leftJoin('product_options as product_size_t', 'product_size_t.product_option_id', '=', 'color_size_product.size_id')
                ->leftJoin('product_options as product_color_t', 'product_color_t.product_option_id', '=', 'color_size_product.color_id')
                ->leftJoin('option_values as option_size_t', 'option_size_t.option_value_id', '=', 'product_size_t.option_value_id')
                ->leftJoin('option_values as option_color_t', 'option_color_t.option_value_id', '=', 'product_color_t.option_value_id')
                ->whereIn('option_size_t.option_value_id', $size)
                ->whereIn('option_color_t.option_value_id', $color);
        }
        if ($price) {
            $productsQuery->whereBetween('products.price', [$price]);
        }
        if ($is_available && (!$size && !$color)) {
            $productsQuery
                ->leftJoin('color_size_product', 'products.product_id', '=', 'color_size_product.product_id')
                ->where([
                    ['products.status', '=', 1],
                    ['color_size_product.quantity', '>', 0]
                ]);
        }
        if ($is_available && ($size || $color)) {
            $productsQuery
                ->where([
                    ['products.status', '=', 1],
                    ['color_size_product.quantity', '>', 0]
                ]);
        }
        if ($category) {
            $productsQuery->leftJoin('product_categories', 'products.product_id', '=', 'product_categories.product_id');
            $productsQuery->leftJoin('categories', 'categories.category_id', '=', 'product_categories.category_id');
            $productsQuery->whereIn('categories.parent_id', $category);
            $productsQuery->orWhereIn('product_categories.category_id', $category);

            if ($color && !$size) {
                $productsQuery->whereIn('option_values.option_value_id', $color);
                if ($is_available) {
                    $productsQuery
                        ->where([
                            ['products.status', '=', 1],
                            ['color_size_product.quantity', '>', 0]
                        ]);
                }
            } else if ($size && !$color) {
                $productsQuery->whereIn('option_values.option_value_id', $size);
                if ($is_available) {
                    $productsQuery
                        ->where([
                            ['products.status', '=', 1],
                            ['color_size_product.quantity', '>', 0]
                        ]);
                }
            } else if ($size && $color) {
                $productsQuery->whereIn('option_size_t.option_value_id', $size);
                $productsQuery->whereIn('option_color_t.option_value_id', $color);
                if ($is_available) {
                    $productsQuery
                        ->where([
                            ['products.status', '=', 1],
                            ['color_size_product.quantity', '>', 0]
                        ]);
                }
            }
            if ($price) {
                $productsQuery->whereBetween('products.price', [$price]);
            }
            if ($is_available && (!$size && !$color)) {
                $productsQuery
                    ->where([
                        ['products.status', '=', 1],
                        ['color_size_product.quantity', '>', 0]
                    ]);
            }
        }
        if ($sortBy) {
            if ($sortBy == 'dateAsc') {
                $productsQuery->orderBy('products.created_at', 'asc');
            } elseif ($sortBy == 'dateDesc') {
                $productsQuery->orderBy('products.created_at', 'desc');
            } elseif ($sortBy == 'priceAsc') {
                $productsQuery->orderBy('products.price', 'asc');
            } elseif ($sortBy == 'priceDesc') {
                $productsQuery->orderBy('products.price', 'desc');
            } elseif ($sortBy == 'relevance') {
                $productsQuery->orderBy('products.viewed', 'desc');
            }
        } else {
            $productsQuery->orderBy('products.created_at', 'desc');
        }

        $products = $productsQuery
            ->groupBy('products.product_id')
            ->paginate($page_count);

        $products->map(function ($product) {
            $categories = DB::table('product_categories')
                ->where('product_categories.product_id', $product->product_id)
                ->leftJoin('categories', 'categories.category_id', '=', 'product_categories.product_id')
                ->leftJoin('category_descriptions', 'category_descriptions.category_id', '=', 'product_categories.category_id')
                ->get();
            $colors = DB::table('color_size_product')
                ->select(DB::raw('*, color_size_product.quantity as quantity'))
                ->where('color_size_product.product_id', $product->product_id)
                ->leftJoin('product_options', 'color_size_product.color_id', '=', 'product_options.product_option_id')
                ->leftJoin('option_values', 'option_values.option_value_id', '=', 'product_options.option_value_id')
                ->get();
            $sizes = DB::table('color_size_product')
                ->select(DB::raw('*, color_size_product.quantity as quantity'))
                ->where('color_size_product.product_id', $product->product_id)
                ->leftJoin('product_options', 'color_size_product.size_id', '=', 'product_options.product_option_id')
                ->leftJoin('option_values', 'option_values.option_value_id', '=', 'product_options.option_value_id')
                ->get();


            $discounts = DB::table('discounts')
                ->where('discounts.product_id', $product->product_id)
                ->get();
            $product['categories'] = $categories;
            $product['discounts'] = $discounts;
            $product['colors'] = $colors;
            $product['sizes'] = $sizes;

            return $product;
        });

        return $products;
    }

    public static function getListModels()
    {
        return self::select(DB::raw('product_id, model, name'))->get();
    }

    public static function showProductById(int $id)
    {
        $productItem = self::select(DB::raw('*, products.product_id as product_id'))->where(['products.product_id' => $id])
            ->leftJoin('product_descriptions', 'products.product_id', '=', 'product_descriptions.product_id')
            ->first();

        $categories = DB::table('product_categories')
            ->select(DB::raw('*, categories.category_id as category_id'))
            ->where('product_categories.product_id', $productItem->product_id)
            ->leftJoin('categories', 'categories.category_id', '=', 'product_categories.category_id')
            ->leftJoin('category_descriptions', 'category_descriptions.category_id', '=', 'product_categories.category_id')
            ->get();
        $options = DB::table('product_options')
            ->where('product_options.product_id', $productItem->product_id)
            ->leftJoin('options', 'product_options.option_id', 'options.option_id')
            ->leftJoin('option_values', 'product_options.option_value_id', 'option_values.option_value_id')
            ->get();
        $images = DB::table('product_images')
            ->where('product_images.product_id', $productItem->product_id)
            ->get();
        $related = DB::table('products_related')
            ->where('products_related.product_id', $productItem->product_id)
            ->leftJoin('products', 'products.product_id', '=', 'products_related.related_product_id')
            ->get();
        $discounts = DB::table('discounts')
            ->where('discounts.product_id', $productItem->product_id)
            ->get();
        $colors = DB::table('color_size_product')
            ->select(DB::raw('*, color_size_product.quantity as product_quantity, option_values.option_value_id as opt_val_id'))
            ->where('color_size_product.product_id', $productItem->product_id)
            ->leftJoin('product_options', 'color_size_product.color_id', '=', 'product_options.product_option_id')
            ->leftJoin('option_values', 'option_values.option_value_id', '=', 'product_options.option_value_id')
            ->leftJoin('options', 'options.option_id', '=', 'product_options.option_id')
            ->get();
        $sizes = DB::table('color_size_product')
            ->select(DB::raw('*, color_size_product.quantity as product_quantity, option_values.option_value_id as opt_val_id'))
            ->where('color_size_product.product_id', $productItem->product_id)
            ->leftJoin('product_options', 'color_size_product.size_id', '=', 'product_options.product_option_id')
            ->leftJoin('option_values', 'option_values.option_value_id', '=', 'product_options.option_value_id')
            ->leftJoin('options', 'options.option_id', '=', 'product_options.option_id')
            ->get();
        $productItem['categories'] = $categories;
        $productItem['options'] = $options;
        $productItem['images'] = $images;
        $productItem['related'] = $related;
        $productItem['discounts'] = $discounts;
        $productItem['colors'] = $colors;
        $productItem['sizes'] = $sizes;

        return $productItem;
    }


}
