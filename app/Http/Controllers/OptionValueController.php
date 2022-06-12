<?php

namespace App\Http\Controllers;

use App\Option;
use App\OptionValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            $optionValues = DB::table('option_values')
                ->leftJoin('options', 'options.option_id', 'option_values.option_id')
                ->get();

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
                    $optionValue->color = $value->color;
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
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        $optionValues = OptionValue::where('option_id', $id)->get();

        return response()->json($optionValues, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public static function update(Request $request, $id)
    {
//        $request->validate([
//            'name_value' => 'required|string',
//            'option_id' => 'number|required',
//            'image' => 'mimes:jpeg,jpg,png,gif',
//        ]);
        try {
            foreach (json_decode($request->input('values')) as $option) {
                $optionValue = OptionValue::find($option->id);
                if ($optionValue) {
                    $optionValuePhoto = $option->image ?: 'no-value-photo.jpg';
                    $optionValue->name_value = $option->name;
                    $optionValue->color = $option->color;
                    if ($request->hasFile('image_' . $option->id)) {
                        $extension = $request->file('image')->getClientOriginalExtension();
                        $filenameStore = Str::random(8) . time() . '.' . $extension;
                        $request->file('image_' . $option->id)->storeAs('images', $filenameStore);
                        $img = Image::make(public_path("uploads/images/$filenameStore"));
                        $img->orientate();
                        $img->resize(480, null, function ($constraint) {
                            $constraint->upsize();
                            $constraint->aspectRatio();
                        });
                        $img->save(public_path("uploads/images/$filenameStore"));
                        $optionValuePhoto = $filenameStore;
                    }
                    $optionValue->image = $optionValuePhoto;
                    $optionValue->save();
                }

                return response()->json('Значение опции не найдено!', 404);
            }

            return response()->json('OK', 200);
        } catch (\Exception $exception) {
            return response()->json('Ошибка при редактировании значении опции!', 400);
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
