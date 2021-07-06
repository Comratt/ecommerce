<?php

namespace App\Http\Controllers;

use App\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class BannerController extends Controller
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
            $banners = Banner::all();

            return response()->json($banners, 200);
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
            'title' => 'required|string',
            'description' => 'required|string',
            'link' => 'required|string',
            'image' => 'mimes:jpeg,jpg,png,gif|required',
        ]);
//        try {
            $bannerPhoto = 'no-photo.jpg';
            $newBanner = new Banner;
            $newBanner->title = $request->title;
            $newBanner->link = $request->link;
            $newBanner->description = $request->description;
            if ($request->sort_order) {
                $newBanner->sort_order = $request->sort_order;
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
                $bannerPhoto = $filenameStore;
            }
            $newBanner->image = $bannerPhoto;
            $newBanner->save();

            return response()->json($newBanner);
//        } catch (\Exception $exception) {
//            return $this->showMessage('Ошибка при добавление баннера!', 400);
//        }
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
            $banner = Banner::find($id);

            return response()->json($banner);
        } catch (\Exception $exception) {
            return $this->showMessage('Такого баннера не существует!', 404);
        }
    }


    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
//        $request->validate([
//            'title' => 'required|string',
//            'description' => 'required|string',
//            'link' => 'required|string',
//            'image' => 'mimes:jpeg,jpg,png,gif|required',
//        ]);

        try {
            $banner = Banner::find($id);

            if ($banner) {
                $bannerPhoto = $banner->image;
                $banner->title = $request->title;
                $banner->link = $request->link;
                $banner->sort_order = $request->sort_order;
                $banner->description = $request->description;
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
                    $bannerPhoto = $filenameStore;
                }
                $banner->image = $bannerPhoto;
                $banner->save();

                return response()->json($banner);
            }

            return $this->showMessage('Такого баннера не существует!', 404);
        } catch (\Exception $exception) {
            return $this->showMessage('Ошибка при редактирования баннера!', 400);
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
            $banner = Banner::find($id);
            if ($banner) {
                $deletedBanner = $banner->delete();

                return response()->json($deletedBanner);
            }
            return $this->showMessage('Баннер не найден!', 400);
        } catch (\Exception $exception) {
            return $this->showMessage('Ошибка при удалении баннера!', 400);
        }
    }
}
