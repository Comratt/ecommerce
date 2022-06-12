<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\User;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        $request->validate([
            'firstName' => 'required|string',
            'lastName' => 'required|string',
            'email' => 'required|string|unique:users',
            'password' => 'required|string',
        ]);
        try {

            $user = new User([
                'first_name' => $request->firstName,
                'last_name' => $request->lastName,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'real_password' => $request->password,
            ]);

            $user->save();

            return response()->json($user, 201);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => 'Ошибка на сервере!'
            ], 400);
        }
    }

    public function getPassword(Request $request)
    {
        return response()->json(Auth::user()->getAuthPassword());
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
            'remember_me' => 'boolean',
            'from_admin' => 'boolean',
        ]);

        $credentails = [
            'email' => $request->email,
            'password' => $request->password
        ];

        if(!Auth::attempt($credentails))
            return response()->json([
                'message' => 'Неверный логин или пароль'
            ], 401);

        $user = $request->user();

        if ($request->from_admin && $user->role != 'admin') {
            return response()->json([
                'message' => 'Такого админа нет'
            ], 404);
        }
        if (!$request->from_admin && $user->role == 'admin') {
            return response()->json([
                'message' => 'Логин для админа запрещен!'
            ], 404);
        }

        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;

        if ($request->from_admin) {
            $token->expires_at = Carbon::now()->addHours(5);
        }

        $token->save();

        return response()->json([
            'acces_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse($tokenResult->token->expires_at)->toDateTimeString(),
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function modifyUser(Request $request, $id)
    {
        try {
            $request->validate([
                'firstName' => 'required|string',
                'lastName' => 'required|string',
                'password' => 'required|string',
                'phone' => 'required|string',
            ]);

            $user = User::find($id);

            if ($user) {
                $user->first_name = $request->firstName;
                $user->last_name = $request->lastName;
                $user->password = bcrypt($request->password);
                $user->real_password = $request->password;
                $user->phone = $request->phone;
                $user->save();

                return response()->json($user);
            } else {
                return response()->json(['message' => 'Ошибка на сервере'], 400);
            }
        } catch (\Exception $exception) {
            return response()->json([
                'message' => 'Ошибка на сервере!'
            ], 400);
        }
    }
}