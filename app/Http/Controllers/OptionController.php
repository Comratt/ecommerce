<?php

namespace App\Http\Controllers;

use App\OptionValue;
use Illuminate\Http\Request;
use App\Option;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class OptionController extends Controller
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
            $options = Option::all();

            return response()->json($options, 200);
        } catch (\Exception $exception) {
            return $this->showMessage('Ошибка при загрузки опций!', 400);
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
            'name' => 'required|string',
        ]);
        try {
            $option = Option::create([
                'name' => $request->name,
            ]);

            return response()->json($option, 200);
        } catch (\Exception $exception) {
            return $this->showMessage('Ошибка при добавлении опции!', 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $option = Option::where('option_id', $id)->with('values')->first();
            if ($option) {
                return response()->json($option, 200);
            }

            return $this->showMessage('Опция не найдена!', 404);
        } catch (\Exception $exception) {
            return $this->showMessage('Ошибка при загрузки опции!', 400);
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
        try {
            $option = Option::where('option_id', $id)->first();
            if ($option) {
                $option->name = $request->name;
                $option->save();

                if ($request->values) {
                    foreach (json_decode($request->input('values')) as $value) {
                        $is_new = is_string($value->id);

                        if (!$is_new) {
                            $optionValue = OptionValue::where('option_value_id', '=', "{$value->id}")->first();
                            if (!$optionValue) {
                                return $this->showMessage('Такої опції не існує!', 400);
                            }
                            $optionValuePhoto = $value->image ?: 'no-value-photo.jpg';
                            $optionValue->name_value = $value->name;
                            $optionValue->color = $value->color;
                            if ($request->hasFile('image_' . $value->id)) {
                                $extension = $request->file('image_' . $value->id)->getClientOriginalExtension();
                                $filenameStore = Str::random(8) . time() . '.' . $extension;
                                $request->file('image_' . $value->id)->storeAs('images', $filenameStore);
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
                        } else {
                            $newOptionValue = new OptionValue;
                            $optionValuePhoto = $value->image ?: 'no-value-photo.jpg';
                            $newOptionValue->name_value = $value->name;
                            $newOptionValue->option_id = $id;
                            $newOptionValue->color = $value->color;
                            if ($request->hasFile('image_' . $value->id)) {
                                $extension = $request->file('image_' . $value->id)->getClientOriginalExtension();
                                $filenameStore = Str::random(8) . time() . '.' . $extension;
                                $request->file('image_' . $value->id)->storeAs('images', $filenameStore);
                                $img = Image::make(public_path("uploads/images/$filenameStore"));
                                $img->orientate();
                                $img->resize(480, null, function ($constraint) {
                                    $constraint->upsize();
                                    $constraint->aspectRatio();
                                });
                                $img->save(public_path("uploads/images/$filenameStore"));
                                $optionValuePhoto = $filenameStore;
                            }
                            $newOptionValue->image = $optionValuePhoto;
                            $newOptionValue->save();
                        }
                    }
                }

                return response()->json(['option' => $option], 200);
            }

            return $this->showMessage('Опция не найдена!', 404);
        } catch (\Exception $exception) {
            return $this->showMessage('Ошибка при обновлении опции!', 400);
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
            $option = Option::where('option_id', $id)->with('values')->first();
            if ($option) {
                if (count($option->values) > 0) {
                    return response()->json('У вас есть значения прив\'язаные к опции!', 400);
                }
                $deletedOption = $option->delete();

                return response()->json($deletedOption);
            }
            return $this->showMessage('Опция не найдена!', 404);
        } catch (\Exception $exception) {
            return $this->showMessage('Ошибка при удалении опции!', 400);
        }
    }
}
