<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Option;

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
            $option = Option::find($id);
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
            $option = Option::find($id);
            if ($option) {
                $option->name = $request->name;
                $option->save();

                return response()->json($option, 200);
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
            $option = Option::find($id);
            if ($option) {
                $deletedOption = $option->delete();

                return response()->json($deletedOption);
            }
            return $this->showMessage('Опция не найдена!', 404);
        } catch (\Exception $exception) {
            return $this->showMessage('Ошибка при удалении опции!', 400);
        }
    }
}
