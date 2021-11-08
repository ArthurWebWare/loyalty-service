<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->all();
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        return response()->json(['user' => $user, 'token' => $user->createToken('apiToken')->plainTextToken], 201);
    }

    public function login(Request $request)
    {
        $data = $request->all();
        $user = User::where('email', $data['email'])->first();

        if ($user && Hash::check($data['password'], $user->password)) {
            $token = $user->createToken('apiToken')->plainTextToken;
        } else {
            return response()->json(['message' => 'Bad credentials'], 401);
        }
        echo '<pre>';   
        print_r($user);
        exit;
        return response()->json(['user' => $user, 'token' => $token], 200);
    }

    public function logout(Request $request)
    {   
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out'], 200);
    }
}
