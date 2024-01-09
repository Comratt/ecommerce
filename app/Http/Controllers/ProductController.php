<?php

namespace App\Http\Controllers;

use App\Order;
use App\Product;
use App\Traits\GenerateUniqueSlug;
use App\User;
use Carbon\Carbon;
use Faker\Generator as Faker;
use App\CategoryDescription;
use App\ProductCategory;
use App\ProductDescription;
use App\ProductImage;
use App\ProductOption;
use App\OptionValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ProductController extends Controller
{
    use GenerateUniqueSlug;
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
//        try {
            $countPerPage = $request->input('count') ?: 20;
            $is_available = $request->input('available') == 'true';
            $search = $request->input('search');
            $name = $request->input('name');
            $model = $request->input('model');
            $category = $request->input('category');
            $sortBy = $request->input('sortBy');
            $price = $request->input('price');
            $color = $request->input('color');
            $size = $request->input('size');
            $ids = $request->input('id');
            $products = Product::getAllProducts(
                $countPerPage,
                $is_available,
                $search,
                $category,
                $sortBy,
                $price,
                $color,
                $size,
                $ids,
                $name,
                $model
            );

            return response()->json($products, 200);
//        } catch (\Exception $exception) {
//            return $this->showMessage('Ошибка при загрузки товаров!', 400);
//        }
    }

    public function minMaxPrice(Request $request) {
        try {
            $category = $request->input('category');
            $color = $request->input('color');
            $productsQuery = Product::select(DB::raw('*'))
                ->leftJoin('color_size_product', 'products.product_id', '=', 'color_size_product.product_id')
                ->leftJoin('product_options', 'product_option_id', '=', 'color_size_product.color_id')
                ->leftJoin('option_values', 'option_values.option_value_id', '=', 'product_options.option_value_id');
            if ($color) {
                $productsQuery->whereIn('option_values.option_value_id', $color);
            }
            if ($category) {
                $productsQuery->leftJoin('product_categories', 'products.product_id', '=', 'product_categories.product_id');
                $productsQuery->leftJoin('categories', 'categories.category_id', '=', 'product_categories.category_id');
                $productsQuery->whereIn('product_categories.category_id', $category);
                $productsQuery->orWhereIn('categories.parent_id', $category);
                $productsQuery
                    ->whereNotNull('option_values.option_value_id')
                    ->where('color_size_product.quantity', '>', 0);
                if ($color) {
                    $productsQuery->whereIn('option_values.option_value_id', $color);
                }
            }
            $minPrice = $productsQuery->min('products.price');
            $maxPrice = $productsQuery->max('products.price');

            return response()->json([$minPrice, $maxPrice]);
        } catch (\Exception $exception) {
            return $this->showMessage('Ошибка при загрузки товаров!', 400);
        }
    }

    public function colors(Request $request) {
        try {
            $category = $request->input('category');
            $price = $request->input('price');
            $productsQuery = Product::select(DB::raw('option_values.name_value as name, product_options.option_value_id as id, color'))
                ->leftJoin('color_size_product', 'products.product_id', '=', 'color_size_product.product_id')
                ->leftJoin('product_options', 'product_option_id', '=', 'color_size_product.color_id')
                ->leftJoin('option_values', 'option_values.option_value_id', '=', 'product_options.option_value_id');

            if ($price) {
                $productsQuery->whereBetween('products.price', [$price]);
            }
            if ($category) {
                $productsQuery->leftJoin('product_categories', 'products.product_id', '=', 'product_categories.product_id');
                $productsQuery->leftJoin('categories', 'categories.category_id', '=', 'product_categories.category_id');
                $productsQuery->whereIn('product_categories.category_id', $category);
                $productsQuery->orWhereIn('categories.parent_id', $category);
                if ($price) {
                    $productsQuery->whereBetween('products.price', [$price]);
                }
            }
            $colors = $productsQuery
                ->whereNotNull('option_values.option_value_id')
                ->where('color_size_product.quantity', '>', 0)
                ->groupBy('option_values.option_value_id')
                ->get();

            return response()->json($colors);
        } catch (\Exception $exception) {
            return $this->showMessage('Ошибка при загрузки товаров!', 400);
        }
    }
    public function sizes(Request $request) {
        try {
            $category = $request->input('category');
            $price = $request->input('price');
            $productsQuery = Product::select(DB::raw('option_values.name_value as name, product_options.option_value_id as id'))
                ->leftJoin('color_size_product', 'products.product_id', '=', 'color_size_product.product_id')
                ->leftJoin('product_options', 'product_option_id', '=', 'color_size_product.size_id')
                ->leftJoin('option_values', 'option_values.option_value_id', '=', 'product_options.option_value_id');

            if ($price) {
                $productsQuery->whereBetween('products.price', [$price]);
            }
            if ($category) {
                $productsQuery->leftJoin('product_categories', 'products.product_id', '=', 'product_categories.product_id');
                $productsQuery->leftJoin('categories', 'categories.category_id', '=', 'product_categories.category_id');
                $productsQuery->whereIn('product_categories.category_id', $category);
                $productsQuery->orWhereIn('categories.parent_id', $category);
                if ($price) {
                    $productsQuery->whereBetween('products.price', [$price]);
                }
            }
            $sizes = $productsQuery
                ->whereNotNull('option_values.option_value_id')
                ->where('color_size_product.quantity', '>', 0)
                ->groupBy('option_values.option_value_id')
                ->get();

            return response()->json($sizes);
        } catch (\Exception $exception) {
            return $this->showMessage('Ошибка при загрузки товаров!', 400);
        }
    }

    public function listModels(Request $request)
    {
        try {
            $products = Product::getListModels();

            return response()->json($products);
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
            try {
                $product = new Product;
                if ($product) {
                    $reqProduct = json_decode($request->product);
                    $product->model = $reqProduct->model;
                    $product->name = $reqProduct->productName;
                    $product->slug = $this->generateUniqueSlug($reqProduct->productName);
                    $product->price = $reqProduct->price;
                    $product->status = $reqProduct->status ?: 1;
                    $productPhoto = 'no-photo.jpg';
                    $productSizePhoto = null;
                    if ($request->hasFile('mainImage')) {
                        $filenameStore = Str::random(8) . time() . '.' . 'webp';
                        $request->file('mainImage')->storeAs('images', $filenameStore);
                        $img = Image::make(public_path("uploads/images/$filenameStore"))->encode('webp', 60);
                        $img->orientate();
                        $img->resize(1280, null, function($constraint){
                            $constraint->upsize();
                            $constraint->aspectRatio();
                        });
                        $img->save(public_path("uploads/images/$filenameStore"), 60);
                        $productPhoto = $filenameStore;
                    }
                    if ($request->hasFile('tableSize')) {
                        $extensionSize = $request->file('tableSize')->getClientOriginalExtension();
                        $filenameStoreSize = Str::random(8) . time() . '.' . $extensionSize;
                        $request->file('tableSize')->storeAs('images', $filenameStoreSize);
                        $imgSize = Image::make(public_path("uploads/images/$filenameStoreSize"));
                        $imgSize->orientate();
                        $imgSize->resize(1280, null, function($constraint){
                            $constraint->upsize();
                            $constraint->aspectRatio();
                        });
                        $imgSize->save(public_path("uploads/images/$filenameStoreSize"));
                        $productSizePhoto = $filenameStoreSize;
                    }
                    $product->image = $productPhoto;
                    $product->table_size = $productSizePhoto;
                    $product->save();

                    $newProductDescription = new ProductDescription;
                    $newProductDescription->product_id = $product->product_id;
                    $newProductDescription->description = $reqProduct->description;
                    $newProductDescription->meta_title = $reqProduct->metaTitle;
                    $newProductDescription->meta_description = $reqProduct->metaDescription;
                    $newProductDescription->meta_keyword = $reqProduct->metaKeywords;
                    $newProductDescription->tag = $reqProduct->metaTags;
                    $newProductDescription->save();
                    if ($request->input('imagesIds')) {
                        foreach (json_decode($request->input('imagesIds')) as $imageId) {
                            $productImage = ProductImage::find($imageId);
                            if ($productImage) {
                                $productImageName = $productImage->image ?: 'no-photo.jpg';
                                if ($request->hasFile('image_' . $imageId)) {
                                    $mime = $request->file('image_' . $imageId)->getMimeType();
                                    if(strstr($mime, "video/")){
                                        // this code for video
                                        $extension = $request->file('image_' . $imageId)->getClientOriginalExtension();
                                        $filenameStore = Str::random(8) . time() . '.' . $extension;
                                        $request->file('image_' . $imageId)->storeAs('images', $filenameStore);
                                        $productImageName = $filenameStore;
                                    } elseif (strstr($mime, "image/")) {
                                        // this code for image
                                        $filenameStore = Str::random(8) . time() . '.' . 'webp';
                                        $request->file('image_' . $imageId)->storeAs('images', $filenameStore);
                                        $img = Image::make(public_path("uploads/images/$filenameStore"))->encode('webp', 60);
                                        $img->orientate();
                                        $img->resize(1280, null, function ($constraint) {
                                            $constraint->upsize();
                                            $constraint->aspectRatio();
                                        });
                                        $img->save(public_path("uploads/images/$filenameStore"), 60);
                                        $productImageName = $filenameStore;
                                    }
                                }
                                $productImage->image = $productImageName;
                                $productImage->save();
                            } else {
                                $productImageName = 'no-photo.jpg';
                                if ($request->hasFile('image_' . $imageId)) {
                                    $mime = $request->file('image_' . $imageId)->getMimeType();
                                    if(strstr($mime, "video/")){
                                        // this code for video
                                        $extension = $request->file('image_' . $imageId)->getClientOriginalExtension();
                                        $filenameStore = Str::random(8) . time() . '.' . $extension;
                                        $request->file('image_' . $imageId)->storeAs('images', $filenameStore);
                                        $productImageName = $filenameStore;
                                    } elseif (strstr($mime, "image/")) {
                                        $filenameStore = Str::random(8) . time() . '.' . 'webp';
                                        $request->file('image_' . $imageId)->storeAs('images', $filenameStore);
                                        $img = Image::make(public_path("uploads/images/$filenameStore"))->encode('webp', 60);
                                        $img->orientate();
                                        $img->resize(1280, null, function ($constraint) {
                                            $constraint->upsize();
                                            $constraint->aspectRatio();
                                        });
                                        $img->save(public_path("uploads/images/$filenameStore"), 60);
                                        $productImageName = $filenameStore;
                                    }
                                }
                                $productNewImage = new ProductImage;
                                $productNewImage->product_id = $product->product_id;
                                $productNewImage->image = $productImageName;
                                $productNewImage->save();
                            }
                        }
                    }
                    if ($request->options) {
                        foreach (json_decode($request->options) as $option) {
                            $size = OptionValue::where('option_value_id', $option->size)->first();
                            $color = OptionValue::where('option_value_id', $option->color)->first();

                            $product_option_size = ProductOption::create([
                                'product_id'      => $product->product_id,
                                'option_id'       => $size->option_id,
                                'option_value_id' => $size->option_value_id,
                                'quantity'        => $option->quantity,
                            ]);
                            $product_option_color = ProductOption::create([
                                'product_id'      => $product->product_id,
                                'option_id'       => $color->option_id,
                                'option_value_id' => $color->option_value_id,
                                'quantity'        => $option->quantity,
                            ]);

                            DB::table('color_size_product')->insert([
                                'product_id' => $product->product_id,
                                'color_id' => $product_option_color->product_option_id,
                                'size_id' => $product_option_size->product_option_id,
                                'quantity' => $option->quantity,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                            ]);
                        }
                    }
                    if ($request->discounts) {
                        foreach (json_decode($request->discounts) as $discount) {
                            DB::table('discounts')->insert([
                                'product_id' => $product->product_id,
                                'discount_price' => $discount->price,
                                'discount_quantity' => $discount->quantity,
                                'discount_priority' => $discount->priority,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                            ]);
                        }
                    }
                    if ($request->category) {
                        foreach (json_decode($request->category) as $catId) {
                            $productCategory = new ProductCategory;
                            $productCategory->product_id = $product->product_id;
                            $productCategory->category_id = $catId->value;
                            $productCategory->save();
                        }
                    }

                    if ($request->related) {
                        foreach (json_decode($request->related) as $prodId) {
                            DB::table('products_related')->insert([
                                'product_id' => $product->product_id,
                                'related_product_id' => $prodId->value,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                            ]);
                        }
                    }

                    return response()->json('OK', 200);
                }

                return $this->showMessage('Товар не найден!', 404);
            } catch (\Exception $exception) {
                return $this->showMessage('Ошибка при обновлении товара!', 400);
            }
        }, 1);
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        try {
            $productItem = Product::where(['slug' => $id]);
            if ($productItem) {
                $productItem->increment('viewed');
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
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            DB::transaction(function () use ($request, $id) {
                $product = Product::find($id);
                if ($product) {
                    $reqProduct = json_decode($request->product);
                    $product->model = $reqProduct->model;
                    $product->name = $reqProduct->productName;
                    $product->price = $reqProduct->price;
                    $product->status = $reqProduct->status ?: 0;
                    $product->slug = $this->generateUniqueSlug($reqProduct->productName);
                    $productPhoto = $product->image ?: 'no-photo.jpg';
                    if ($request->hasFile('mainImage')) {
                        $filenameStore = Str::random(8) . time() . '.' . 'webp';
                        $request->file('mainImage')->storeAs('images', $filenameStore);
                        $img = Image::make(public_path("uploads/images/$filenameStore"))->encode('webp', 60);
                        $img->orientate();
                        $img->resize(1280, null, function ($constraint) {
                            $constraint->upsize();
                            $constraint->aspectRatio();
                        });
                        $img->save(public_path("uploads/images/$filenameStore"), 60);
                        $productPhoto = $filenameStore;
                    }
                    if ($request->hasFile('tableSize')) {
                        $extensionSize = $request->file('tableSize')->getClientOriginalExtension();
                        $filenameStoreSize = Str::random(8) . time() . '.' . $extensionSize;
                        $request->file('tableSize')->storeAs('images', $filenameStoreSize);
                        $imgSize = Image::make(public_path("uploads/images/$filenameStoreSize"));
                        $imgSize->orientate();
                        $imgSize->resize(1280, null, function($constraint){
                            $constraint->upsize();
                            $constraint->aspectRatio();
                        });
                        $imgSize->save(public_path("uploads/images/$filenameStoreSize"));
                        $productSizePhoto = $filenameStoreSize;
                    } else {
                        $productSizePhoto = $product->table_size;
                    }
                    $product->image = $productPhoto;
                    $product->table_size = $productSizePhoto;
                    $product->save();
                    $productDescription = ProductDescription::where(['product_id' => $id])->first();
                    if ($productDescription) {
                        $productDescription->description = $reqProduct->description;
                        $productDescription->meta_title = $reqProduct->metaTitle;
                        $productDescription->meta_description = $reqProduct->metaDescription;
                        $productDescription->meta_keyword = $reqProduct->metaKeywords;
                        $productDescription->tag = $reqProduct->metaTags;
                        $productDescription->save();
                    } else {
                        $newProductDescription = new ProductDescription;
                        $newProductDescription->product_id = $id;
                        $newProductDescription->description = $reqProduct->description;
                        $newProductDescription->meta_title = $reqProduct->metaTitle;
                        $newProductDescription->meta_description = $reqProduct->metaDescription;
                        $newProductDescription->meta_keyword = $reqProduct->metaKeywords;
                        $newProductDescription->tag = $reqProduct->metaTags;
                        $newProductDescription->save();
                    }
                    if ($request->input('imagesIds')) {
                        $imageIds = [];
                        foreach (json_decode($request->input('imagesIds')) as $imageId) {
                            $productImage = ProductImage::find($imageId);
                            if ($productImage) {
                                array_push($imageIds, $imageId);
                                $productImageName = $productImage->image ?: 'no-photo.jpg';
                                if ($request->hasFile('image_' . $imageId)) {
                                    $mime = $request->file('image_' . $imageId)->getMimeType();
                                    if(strstr($mime, "video/")){
                                        // this code for video
                                        $extension = $request->file('image_' . $imageId)->getClientOriginalExtension();
                                        $filenameStore = Str::random(8) . time() . '.' . $extension;
                                        $request->file('image_' . $imageId)->storeAs('images', $filenameStore);
                                        $productImageName = $filenameStore;
                                    } elseif (strstr($mime, "image/")) {
                                        // this code for image
                                        $filenameStore = Str::random(8) . time() . '.' . 'webp';
                                        $request->file('image_' . $imageId)->storeAs('images', $filenameStore);
                                        $img = Image::make(public_path("uploads/images/$filenameStore"))->encode('webp', 60);
                                        $img->orientate();
                                        $img->resize(1280, null, function ($constraint) {
                                            $constraint->upsize();
                                            $constraint->aspectRatio();
                                        });
                                        $img->save(public_path("uploads/images/$filenameStore"), 60);
                                        $productImageName = $filenameStore;
                                    }
                                }
                                $productImage->image = $productImageName;
                                $productImage->save();
                            } else {
                                $productImageName = 'no-photo.jpg';
                                if ($request->hasFile('image_' . $imageId)) {
                                    $mime = $request->file('image_' . $imageId)->getMimeType();
                                    if(strstr($mime, "video/")){
                                        // this code for video
                                        $extension = $request->file('image_' . $imageId)->getClientOriginalExtension();
                                        $filenameStore = Str::random(8) . time() . '.' . $extension;
                                        $request->file('image_' . $imageId)->storeAs('images', $filenameStore);
                                        $productImageName = $filenameStore;
                                    } elseif (strstr($mime, "image/")) {
                                        $filenameStore = Str::random(8) . time() . '.' . 'webp';
                                        $request->file('image_' . $imageId)->storeAs('images', $filenameStore);
                                        $img = Image::make(public_path("uploads/images/$filenameStore"))->encode('webp', 60);
                                        $img->orientate();
                                        $img->resize(1280, null, function ($constraint) {
                                            $constraint->upsize();
                                            $constraint->aspectRatio();
                                        });
                                        $img->save(public_path("uploads/images/$filenameStore"), 60);
                                        $productImageName = $filenameStore;
                                    }
                                }
                                $productNewImage = new ProductImage;
                                $productNewImage->product_id = $id;
                                $productNewImage->image = $productImageName;
                                $productNewImage->save();
                                array_push($imageIds, $productNewImage->product_image_id);
                            }
                        }
                        ProductImage::where('product_id', $product->product_id)
                            ->whereIn('product_image_id', $imageIds, 'and', true)->delete();
                    }
                    if ($request->options) {
                        $optIds = [];

                        foreach (json_decode($request->options) as $option) {
                            $size = OptionValue::where('option_value_id', $option->size)->first();
                            $color = OptionValue::where('option_value_id', $option->color)->first();

                            $product_option_size = ProductOption::create([
                                'product_id' => $product->product_id,
                                'option_id' => $size->option_id,
                                'option_value_id' => $size->option_value_id,
                                'quantity' => $option->quantity,
                            ]);
                            $product_option_color = ProductOption::create([
                                'product_id' => $product->product_id,
                                'option_id' => $color->option_id,
                                'option_value_id' => $color->option_value_id,
                                'quantity' => $option->quantity,
                            ]);
                            if (DB::table('color_size_product')->where('color_size_product_id', '=', $option->id)->first() !== null) {
                                array_push($optIds, $option->id);
                                DB::table('color_size_product')
                                    ->where('color_size_product_id', '=', $option->id)
                                    ->update([
                                        'product_id' => $product->product_id,
                                        'color_id' => $product_option_color->product_option_id,
                                        'size_id' => $product_option_size->product_option_id,
                                        'quantity' => $option->quantity,
                                    ]);
                            } else {
                                $optId = DB::table('color_size_product')
                                    ->insertGetId([
                                        'product_id' => $product->product_id,
                                        'color_id' => $product_option_color->product_option_id,
                                        'size_id' => $product_option_size->product_option_id,
                                        'quantity' => $option->quantity,
                                    ]);
                                array_push($optIds, $optId);
                            }
                        }
                        DB::table('color_size_product')
                            ->where('product_id', $product->product_id)
                            ->whereIn('color_size_product_id', $optIds, 'and', true)->delete();
                    }
                    if ($request->discounts) {
                        $discountIds = [];

                        foreach (json_decode($request->discounts) as $discount) {
                            if (DB::table('discounts')->where('discount_id', '=', $discount->id)->first() !== null) {
                                array_push($discountIds, $discount->id);
                                DB::table('discounts')
                                    ->where('discount_id', '=', $discount->id)
                                    ->update([
                                        'product_id' => $id,
                                        'discount_price' => $discount->price,
                                        'discount_quantity' => $discount->quantity,
                                        'discount_priority' => $discount->priority,
                                ]);
                            } else {
                                $discountId = DB::table('discounts')->insertGetId([
                                    'product_id' => $id,
                                    'discount_price' => $discount->price,
                                    'discount_quantity' => $discount->quantity,
                                    'discount_priority' => $discount->priority,
                                ]);
                                array_push($discountIds, $discountId);
                            }
                        }
                        DB::table('discounts')
                            ->where('product_id', $product->product_id)
                            ->whereIn('discount_id', $discountIds, 'and', true)->delete();
                    }
                    if ($request->category) {
                        $categoryIds = [];

                        foreach (json_decode($request->category) as $catId) {
                            if (ProductCategory::where('product_category_id', '=', $catId->id)->first() !== null) {
                                array_push($categoryIds, $catId->id);
                                ProductCategory::where('product_category_id', '=', $catId->id)
                                    ->update([
                                        'product_id' => $id,
                                        'category_id' => $catId->value
                                    ]);
                            } else {
                                $productCategory = new ProductCategory;
                                $productCategory->product_id = $id;
                                $productCategory->category_id = $catId->value;
                                $productCategory->save();
                                array_push($categoryIds, $productCategory->product_category_id);
                            }
                        }
                        ProductCategory::where('product_id', $product->product_id)
                            ->whereIn('product_category_id', $categoryIds, 'and', true)->delete();
                    }

                    if ($request->related) {
                        $relatedIds = [];

                        foreach (json_decode($request->related) as $prodId) {
                            if (DB::table('products_related')->where('products_related_id', '=', $prodId->id)->first() !== null) {
                                array_push($relatedIds, $prodId->id);
                                DB::table('products_related')
                                    ->where('products_related_id', '=', $prodId->id)
                                    ->update([
                                    'product_id' => $id,
                                    'related_product_id' => $prodId->value,
                                ]);
                            } else {
                                $relatedId = DB::table('products_related')
                                    ->insertGetId([
                                        'product_id' => $id,
                                        'related_product_id' => $prodId->value,
                                ]);
                                array_push($relatedIds, $relatedId);
                            }
                        }
                        DB::table('products_related')
                            ->where('product_id', $product->product_id)
                            ->whereIn('products_related_id', $relatedIds, 'and', true)->delete();
                    }

                    return response()->json('OK', 200);
                }

                return $this->showMessage('Товар не найден!', 404);
            });
        } catch (\Exception $exception) {
            return $this->showMessage('Ошибка при обновлении товара!', 400);
        }
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

    public function generate() {
        $photos = ['65EiuuYU1626820364.jpg', 'jeans-2.jpg', 'jeans-4.jpg', 'jeans-1.jpg', 'jeans-3.jpg',];
        try {
            $product = new Product();
            $product->name = Str::words(2);
            $product->model = 'Flared high rise jeans';
            $product->price = rand(1000, 5000);
            $product->image = array_random($photos);
            $product->save();
        } catch (\Exception $exception) {
            return $this->showMessage('Ошибка при генерации товаров!', 400);
        }
    }

    public function getAnalytics() {
        try {
            $mapOrders = Order::select(DB::raw('orders.shipping_area, COUNT(orders.shipping_area) as counted, SUM(order_products.total) as total'))
                ->leftJoin('order_products', 'order_products.order_id', '=', 'orders.order_id')
                ->groupBy('orders.shipping_area')
                ->get();
            $products = Product::select(DB::raw('COUNT(*) as counted'))->first();
            $productsOld = Product::select(DB::raw('COUNT(*) as counted'))->whereDate('created_at', '=', Carbon::yesterday()->toDateString())->first();
            $productsNow = Product::select(DB::raw('COUNT(*) as counted'))->whereDate('created_at', '=', Carbon::today()->toDateString())->first();

            $ordersNotCompleted = Order::select(DB::raw('COUNT(*) as counted'))->first();
            $ordersNotCompletedOld = Order::select(DB::raw('COUNT(*) as counted'))->whereDate('updated_at', '=', Carbon::yesterday()->toDateString())->first();
            $ordersNotCompletedNow = Order::select(DB::raw('COUNT(*) as counted'))->whereDate('updated_at', '=', Carbon::today()->toDateString())->first();

            $ordersCompleted = Order::select(DB::raw('SUM(order_products.total) as counted'))
                ->leftJoin('order_products', 'order_products.order_id', 'orders.order_id')
                ->leftJoin('products', 'order_products.product_id', 'products.product_id')
                ->where('status_id', '=', '6')
                ->first();
            $ordersCompletedOld = Order::select(DB::raw('SUM(order_products.total) as counted'))
                ->leftJoin('order_products', 'order_products.order_id', 'orders.order_id')
                ->where('status_id', '=', '6')
                ->whereDate('orders.updated_at', '=', Carbon::yesterday()->toDateString())->first();
            $ordersCompletedNow = Order::select(DB::raw('SUM(order_products.total) as counted'))
                ->leftJoin('order_products', 'order_products.order_id', 'orders.order_id')
                ->where('status_id', '=', '6')
                ->whereDate('orders.updated_at', '=', Carbon::today()->toDateString())->first();

            $users = User::select(DB::raw('COUNT(*) as counted'))->where('role', '!=', 'admin')->first();
            $usersOld = User::select(DB::raw('COUNT(*) as counted'))->where('role', '!=', 'admin')->whereDate('created_at', '=', Carbon::yesterday()->toDateString())->first();
            $usersNow = User::select(DB::raw('COUNT(*) as counted'))->where('role', '!=', 'admin')->whereDate('created_at', '=', Carbon::today()->toDateString())->first();

            return response()->json([
                'ordersMap' => $mapOrders,
                'products' => [
                    'total' => $products->counted,
                    'yesterday' => $productsOld->counted,
                    'today' => $productsNow->counted,
                ],
                'orders' => [
                    'total' => $ordersNotCompleted->counted,
                    'yesterday' => $ordersNotCompletedOld->counted,
                    'today' => $ordersNotCompletedNow->counted,
                ],
                'completed' => [
                    'total' => $ordersCompleted->counted,
                    'yesterday' => $ordersCompletedOld->counted,
                    'today' => $ordersCompletedNow->counted,
                ],
                'users' => [
                    'total' => $users->counted,
                    'yesterday' => $usersOld->counted,
                    'today' => $usersNow->counted,
                ],
            ]);
        } catch (\Exception $exception) {
            return $this->showMessage('Ошибка при генерации товаров!', 400);
        }
    }

    public function getAnalyticsOrders(Request $request)
    {
        try {
            $filterBy = $request->filterBy;
            switch ($filterBy) {
                case 'day': {
                    $orders = Order::select(DB::raw('HOUR(`orders`.`updated_at`) as `value`, SUM(order_products.total) as total'))
                        ->leftJoin('order_products', 'order_products.order_id', '=', 'orders.order_id')
                        ->whereMonth('orders.created_at', '=', date('m'))
                        ->whereYear('orders.created_at', '=', date('Y'))
                        ->whereDay('orders.created_at', '=', date('d'))
                        ->groupBy('value')
                        ->get();
                    break;
                }
                case 'year': {
                    $orders = Order::select(DB::raw('MONTH(`orders`.`updated_at`) as `value`, SUM(order_products.total) as total'))
                        ->leftJoin('order_products', 'order_products.order_id', '=', 'orders.order_id')
                        ->whereYear('orders.created_at', '=', date('Y'))
                        ->groupBy('value')
                        ->get();
                    break;
                }
                case 'month': {
                    $orders = Order::select(DB::raw('DAY(`orders`.`updated_at`) as `value`, SUM(order_products.total) as total'))
                        ->leftJoin('order_products', 'order_products.order_id', '=', 'orders.order_id')
                        ->whereMonth('orders.created_at', '=', date('m'))
                        ->whereYear('orders.created_at', '=', date('Y'))
                        ->groupBy('value')
                        ->get();
                    break;
                }
                default: {
                    $orders = [];
                }
            }

            return response()->json($orders);
        } catch (\Exception $exception) {
            return $this->showMessage('Ошибка при генерации товаров!', 400);
        }
    }

    public function getAnalyticsCategories(Request $request)
    {
        try {
            $filterBy = $request->filterBy;
            switch ($filterBy) {
                case 'day': {
                    $orders = Order::select(DB::raw('categories.category_name as name, SUM(order_products.total) as value'))
                        ->leftJoin('order_products', 'order_products.order_id', '=', 'orders.order_id')
                        ->leftJoin('products', 'products.product_id', 'order_products.product_id')
                        ->leftJoin('product_categories', 'product_categories.product_id', 'products.product_id')
                        ->leftJoin('categories', 'product_categories.category_id', 'categories.category_id')
                        ->whereMonth('orders.updated_at', '=', date('m'))
                        ->whereYear('orders.updated_at', '=', date('Y'))
                        ->whereDay('orders.updated_at', '=', date('d'))
                        ->where('orders.status_id', '=', '6')
                        ->groupBy('categories.category_id')
                        ->get();
                    break;
                }
                case 'year': {
                    $orders = Order::select(DB::raw('categories.category_name as name, SUM(order_products.total) as value'))
                        ->leftJoin('order_products', 'order_products.order_id', '=', 'orders.order_id')
                        ->leftJoin('products', 'products.product_id', 'order_products.product_id')
                        ->leftJoin('product_categories', 'product_categories.product_id', 'products.product_id')
                        ->leftJoin('categories', 'product_categories.category_id', 'categories.category_id')
                        ->whereYear('orders.updated_at', '=', date('Y'))
                        ->where('orders.status_id', '=', '6')
                        ->groupBy('categories.category_id')
                        ->get();
                    break;
                }
                case 'month': {
                    $orders = Order::select(DB::raw('categories.category_name as name, SUM(order_products.total) as value'))
                        ->leftJoin('order_products', 'order_products.order_id', '=', 'orders.order_id')
                        ->leftJoin('products', 'products.product_id', 'order_products.product_id')
                        ->leftJoin('product_categories', 'product_categories.product_id', 'products.product_id')
                        ->leftJoin('categories', 'product_categories.category_id', 'categories.category_id')
                        ->whereMonth('orders.updated_at', '=', date('m'))
                        ->whereYear('orders.updated_at', '=', date('Y'))
                        ->where('orders.status_id', '=', '6')
                        ->groupBy('categories.category_id')
                        ->get();
                    break;
                }
                default: {
                    $orders = [];
                }
            }

            return response()->json($orders);
        } catch (\Exception $exception) {
            return $this->showMessage('Ошибка при генерации товаров!', 400);
        }
    }

    public function getAnalyticsProducts(Request $request)
    {
//        try {
            $filterBy = $request->filterBy;
            switch ($filterBy) {
                case 'day': {
                    $orders = Order::select(DB::raw('products.name as name, products.model as model, SUM(order_products.quantity) as value'))
                        ->leftJoin('order_products', 'order_products.order_id', '=', 'orders.order_id')
                        ->leftJoin('products', 'products.product_id', 'order_products.product_id')
                        ->whereMonth('orders.updated_at', '=', date('m'))
                        ->whereYear('orders.updated_at', '=', date('Y'))
                        ->whereDay('orders.updated_at', '=', date('d'))
                        ->where('orders.status_id', '=', '6')
                        ->groupBy('products.product_id')
                        ->orderBy('value')
                        ->limit(10)
                        ->get();
                    break;
                }
                case 'year': {
                    $orders = Order::select(DB::raw('products.name as name, products.model as model, SUM(order_products.quantity) as value'))
                        ->leftJoin('order_products', 'order_products.order_id', '=', 'orders.order_id')
                        ->leftJoin('products', 'products.product_id', 'order_products.product_id')
                        ->whereYear('orders.updated_at', '=', date('Y'))
                        ->where('orders.status_id', '=', '6')
                        ->groupBy('products.product_id')
                        ->orderBy('value')
                        ->limit(10)
                        ->get();
                    break;
                }
                case 'month': {
                    $orders = Order::select(DB::raw('products.name as name, products.model as model, SUM(order_products.quantity) as value'))
                        ->leftJoin('order_products', 'order_products.order_id', '=', 'orders.order_id')
                        ->leftJoin('products', 'products.product_id', 'order_products.product_id')
                        ->whereMonth('orders.updated_at', '=', date('m'))
                        ->whereYear('orders.updated_at', '=', date('Y'))
                        ->where('orders.status_id', '=', '6')
                        ->groupBy('products.product_id')
                        ->orderBy('value')
                        ->limit(10)
                        ->get();
                    break;
                }
                default: {
                    $orders = [];
                }
            }

            return response()->json($orders);
//        } catch (\Exception $exception) {
//            return $this->showMessage('Ошибка при генерации товаров!', 400);
//        }
    }
}
