<?php

namespace App\Http\Controllers;

use App\Option;
use App\OptionValue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class OptionValueController extends Controller
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
            $optionValues = OptionValue::all();

            return response()->json($optionValues, 200);
        } catch (\Exception $exception) {
            return $this->showMessage('Ошибка при загрузки значений опций!', 400);
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
//        $request->validate([
//            'name_value' => 'required|string',
//            'name_value' => 'required|string',
//            'image' => 'mimes:jpeg,jpg,png,gif',
//            'option_id' => 'required',
//            'description' => 'string',
//        ]);
//        try {
            $option = Option::create([
                'name' => $request->name,
            ]);
            if ($option) {
                foreach (json_decode($request->input('values')) as $value) {
                    $optionValuePhoto = 'no-value-photo.jpg';
                    $optionValue = new OptionValue;
                    $optionValue->option_id = $option->option_id;
                    $optionValue->name_value = $value->name;
//                    if ($value->description) {
//                        $optionValue->description = $value->description;
//                    }
                    if ($request->hasFile('image_' . $value->id)) {
                        $extension = $request->file('image_' . $value->id)->getClientOriginalExtension();
                        $filenameStore = Str::random(8) . time() . '.' . $extension;
                        $request->file('image_' . $value->id)->storeAs('images', $filenameStore);
                        $img = Image::make(public_path("uploads/images/$filenameStore"));
                        $img->orientate();
                        $img->resize(480, null, function($constraint){
                            $constraint->upsize();
                            $constraint->aspectRatio();
                        });
                        $img->save(public_path("uploads/images/$filenameStore"));
                        $optionValuePhoto = $filenameStore;
                    }
                    $optionValue->image = $optionValuePhoto;
                    $optionValue->save();
                }

                return response()->json([
                    'option' => $option,
                    'optionValue' => $optionValue
                ], 200);
            }
//        } catch (\Exception $exception) {
//            return $this->showMessage('Ошибка при добавлении значении опции!', 400);
//        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

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
            'name_value' => 'required|string',
            'option_id' => 'number|required',
            'image' => 'mimes:jpeg,jpg,png,gif',
            'description' => 'string',
        ]);
        try {
            $optionValue = OptionValue::find($id);
            if ($optionValue) {
                $optionValuePhoto = $optionValue->image;
                $optionValue->option_id = $request->option_id;
                $optionValue->name_value = $request->name_value;
                $optionValue->description = $request->description;
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
                    $optionValuePhoto = $filenameStore;
                }
                $optionValue->image = $optionValuePhoto;
                $optionValue->save();

                return response()->json($optionValue, 200);
            }

            return $this->showMessage('значение опции не найдено!', 404);
        } catch (\Exception $exception) {
            return $this->showMessage('Ошибка при редактировании значении опции!', 400);
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
            $optionValue = OptionValue::find($id);
            if ($optionValue) {
                $deletedOption = $optionValue->delete();

                return response()->json($deletedOption);
            }
            return $this->showMessage('Значение опции не найдена!', 404);
        } catch (\Exception $exception) {
            return $this->showMessage('Ошибка при удалении значение опции!', 400);
        }
    }
}
