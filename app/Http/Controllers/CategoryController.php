<?php

namespace App\Http\Controllers;

use App\Category;
use App\CategoryDescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class CategoryController extends Controller
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $categories = DB::table('categories')
                ->select('*', 'categories.category_id as category_id')
                ->leftJoin('category_descriptions', 'category_descriptions.category_id', '=', 'categories.category_id')
                ->get();

            return response()->json($categories, 200);
        } catch (\Exception $exception) {
            return $this->showMessage('Ошибка на сервере!', 400);
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
            'category_name' => 'required|string',
            'parent_id' => 'string',
//            'image' => 'mimes:jpeg,jpg,png,gif',
        ]);
        try {
            $categoryPhoto = 'no-photo.jpg';
            $newCategory = new Category;
            $newCategory->category_name = $request->category_name;
            if ($request->parent_id) {
                $cat = Category::find($request->parent_id);
                if ($cat) {
                    $newCategory->parent_id = $request->parent_id;
                }
            }
            if ($request->sort_order) {
                $newCategory->sort_order = $request->sort_order;
            }
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
                $categoryPhoto = $filenameStore;
            }
            $newCategory->image = $categoryPhoto;
            $newCategory->save();

            $categoryDescription = CategoryDescription::create([
                'category_id'       => $newCategory->category_id,
                'description'       => $request->description,
                'tag'               => $request->tag ?: '',
                'meta_title'        => $request->meta_title,
                'meta_description'  => $request->meta_description,
                'meta_keywords'     => $request->meta_keywords,
            ]);

            return response()->json(array_merge($newCategory->toArray(), $categoryDescription->toArray()));
        return response()->json([
                'category'             => $newCategory,
                'category_description' => $categoryDescription,
            ]);
        } catch (\Exception $exception) {
            return $this->showMessage('Ошибка при добавлении категории!', 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $banner = Category::find($id);

            return response()->json($banner);
        } catch (\Exception $exception) {
            return $this->showMessage('Такой категории не существует!', 404);
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
        $request->validate([
            'category_name' => 'required|string',
            'parent_id' => 'string|nullable',
//            'image' => 'nullable|mimes:jpeg,jpg,png,gif',
        ]);
        try {
            $category = Category::find($id);

            if ($category) {
                $categoryPhoto = $category->image;
                $category->category_name = $request->category_name;
                if ($request->parent_id) {
                    $cat = Category::find($request->parent_id);
                    if ($cat) {
                        $category->parent_id = $request->parent_id;
                    }
                }
                if ($request->sort_order) {
                    $category->sort_order = $request->sort_order;
                }
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
                    $categoryPhoto = $filenameStore;
                }
                $category->image = $categoryPhoto;
                $category->save();

                $categoryDescription = CategoryDescription::where('category_id', '=', $id)->first();

                if ($categoryDescription) {
                    $categoryDescription->description = $request->description;
                    $categoryDescription->tag = $request->tag ?: '';
                    $categoryDescription->meta_title = $request->meta_title;
                    $categoryDescription->meta_description = $request->meta_description;
                    $categoryDescription->meta_keywords = $request->meta_keywords;

                    $categoryDescription->save();
                }

                return response()->json(array_merge($category->toArray(), $categoryDescription->toArray()));
            }

            return $this->showMessage('Такой категории не существует!', 404);
        } catch (\Exception $exception) {
            return $this->showMessage('Ошибка при редактирования категории!', 400);
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
            $category = Category::find($id);
            $categoryDescription = CategoryDescription::where('category_id', '=', $id)->first();
            if ($category && $categoryDescription) {
                $deletedCategory = $categoryDescription->delete();
                $deletedCategory = $category->delete();

                return response()->json($deletedCategory);
            } elseif ($category && !$categoryDescription) {
                $deletedCategory = $category->delete();

                return response()->json($deletedCategory);
            }
            return $this->showMessage('Категория не найдена!', 404);
        } catch (\Exception $exception) {
            return $this->showMessage('Ошибка при удалении категории!', 400);
        }
    }
}
