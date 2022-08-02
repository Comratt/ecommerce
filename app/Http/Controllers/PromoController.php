<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Promocode;

class PromoController extends Controller
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

    public function showAll(Request $request)
    {
        try {
            $promocodes = Promocode::paginate(20);;

            return response()->json($promocodes);
        } catch (\Exception $exception) {
            return $this->showMessage('Помилка при загрузці промокодів!', 400);
        }
    }

    public function store(Request $request)
    {
        try {
            $promocode = Promocode::create([
                'promocode_name' => $request->name,
                'promocode_price' => $request->price,
                'promocode_prefix' => $request->prefix,
            ]);

            return response()->json($promocode);
        } catch (\Exception $exception) {
            return $this->showMessage('Помилка при створенні промокоду!', 400);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $promocode = Promocode::find($id);

            if ($promocode) {
                $promocode->promocode_name = $request->name;
                $promocode->promocode_price = $request->price;
                $promocode->promocode_prefix = $request->prefix;
                $promocode->save();
            } else {
                return $this->showMessage('Промокод не знайдено!', 404);
            }

            return response()->json($promocode);
        } catch (\Exception $exception) {
            return $this->showMessage('Помилка при створенні промокоду!', 400);
        }
    }

    public function getByName(Request $request)
    {
//        try {
            $promocode = Promocode::where('promocode_name', '=', $request->input('name'))->first();

            if ($promocode) {
                return response()->json($promocode);
            } else {
                return $this->showMessage('Промокод не знайдено!', 404);
            }
//        } catch (\Exception $exception) {
//            return $this->showMessage('Помилка при створенні промокоду!', 400);
//        }
    }

    public function delete(Request $request, $id)
    {
        try {
            $promocode = Promocode::where('promocodes_id', '=', $id);

            if ($promocode) {
                $promocode->delete();
            } else {
                return $this->showMessage('Промокод не знайдено!', 404);
            }

            return response()->json($promocode);
        } catch (\Exception $exception) {
            return $this->showMessage('Помилка при створенні промокоду!', 400);
        }
    }
}
