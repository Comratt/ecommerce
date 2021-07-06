<?php

namespace App\Http\Controllers;

use App\Product;
use App\CategoryDescription;
use App\ProductCategory;
use App\ProductDescription;
use App\ProductImage;
use App\ProductOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ProductController extends Controller
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
            $products = Product::getAllProducts();

            return response()->json($products, 200);
        } catch (\Exception $exception) {
            return $this->showMessage('Ошибка при загрузки товаров!', 400);
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
        DB::transaction(function () use ($request) {
            $product = new Product;
            $product->model = $request->model;
            $product->price = $request->price;
            $productPhoto = 'no-photo.jpg';
            if ($request->hasFile('image')) {
                $extension = $request->file('image')->getClientOriginalExtension();
                $filenameStore = Str::random(8) . time() . '.' . $extension;
                $request->file('image')->storeAs('images', $filenameStore);
                $img = Image::make(public_path("uploads/images/$filenameStore"));
                $img->orientate();
                $img->resize(480, null, function($constraint){
                    $constraint->upsize();
                    $constraint->aspectRatio();
                });
                $img->save(public_path("uploads/images/$filenameStore"));
                $productPhoto = $filenameStore;
            }
            $product->image = $productPhoto;
            $product->save();
            foreach ($request->category_id as $category_id) {
                ProductCategory::create([
                    'product_id'  => $product->product_id,
                    'category_id' => $category_id,
                ]);
            }
            ProductDescription::create([
                'product_id' => $product->product_id,
                'description' => $request->description,
                'tag' => $request->tag,
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'meta_keyword' => $request->meta_keyword,
            ]);
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $extension = $photo->getClientOriginalExtension();
                    $filenameStore = Str::random(8) . time() . '.' . $extension;
                    $photo->storeAs('images', $filenameStore);
                    $img = Image::make(public_path("uploads/images/$filenameStore"));
                    $img->orientate();
                    $img->resize(480, null, function($constraint){
                        $constraint->upsize();
                        $constraint->aspectRatio();
                    });
                    $img->save(public_path("uploads/images/$filenameStore"));
                    ProductImage::create([
                        'product_id' => $product->product_id,
                        'image' => $filenameStore,
                    ]);
                }
            }
            foreach ($request->product_options as $product_option => $option_values) {
                foreach ($option_values as $option_value => $quantity) {
                    ProductOption::create([
                        'product_id'      => $product->product_id,
                        'option_id'       => $product_option,
                        'option_value_id' => $option_value,
                        'quantity'        => $quantity,
                    ]);
                }
            }
        }, 1);

        return response()->json('OK', 200);
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        try {
            $productItem = Product::find($id);
            if ($productItem) {
                $product = Product::showProductById($id);

                return response()->json($product, 200);
            } else {
                return $this->showMessage('Товар не найден!', 404);
            }
        } catch (\Exception $exception) {
            return $this->showMessage('Ошибка при загрузки товара!', 400);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        if ($product) {
            $product->model = $request->model;
            $product->price = $request->price;
            $productPhoto = 'no-photo.jpg';
            if ($request->hasFile('image')) {
                $extension = $request->file('image')->getClientOriginalExtension();
                $filenameStore = Str::random(8) . time() . '.' . $extension;
                $request->file('image')->storeAs('images', $filenameStore);
                $img = Image::make(public_path("uploads/images/$filenameStore"));
                $img->orientate();
                $img->resize(480, null, function($constraint){
                    $constraint->upsize();
                    $constraint->aspectRatio();
                });
                $img->save(public_path("uploads/images/$filenameStore"));
                $productPhoto = $filenameStore;
            }
            $product->image = $productPhoto;
            $product->save();
            foreach (json_decode($request->category_id) as $category_id) {
                ProductCategory::create([
                    'product_id'  => $product->product_id,
                    'category_id' => $category_id,
                ]);
            }
            ProductDescription::create([
                'product_id' => $product->product_id,
                'description' => $product->description,
                'tag' => $product->tag,
                'meta_title' => $product->meta_title,
                'meta_description' => $product->meta_description,
                'meta_keyword' => $product->meta_keyword,
            ]);
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $extension = $photo->getClientOriginalExtension();
                    $filenameStore = Str::random(8) . time() . '.' . $extension;
                    $photo->storeAs('images', $filenameStore);
                    $img = Image::make(public_path("uploads/images/$filenameStore"));
                    $img->orientate();
                    $img->resize(480, null, function($constraint){
                        $constraint->upsize();
                        $constraint->aspectRatio();
                    });
                    $img->save(public_path("uploads/images/$filenameStore"));
                    ProductImage::create([
                        'product_id' => $product->product_id,
                        'image' => $filenameStore,
                    ]);
                }
            }
            foreach (json_decode($request->product_options) as $product_option => $option_values) {
                foreach ($option_values as $option_value => $quantity) {
                    ProductOption::create([
                        'product_id'      => $product->product_id,
                        'option_id'       => $product_option,
                        'option_value_id' => $option_value,
                        'quantity'        => $quantity,
                    ]);
                }
            }

            return response()->json('OK', 200);
        }

        return $this->showMessage('Товар не найден!', 404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $product = Product::find($id);
            if ($product) {
                $deletedProduct = $product->delete();

                return response()->json($deletedProduct);
            }
            return $this->showMessage('Товар не найден!', 404);
        } catch (\Exception $exception) {
            return $this->showMessage('Ошибка при удалении товара!', 400);
        }
    }
}
