<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

// use App\Http\Requsets\Auth\LoginRequest;

use Validator;

class AuthController extends Controller
{

    public function register(Request $request)
    {   
        $validateRules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ];
        $validator = Validator::make($request->all(),$validateRules);

        if($validator->fails()){
            return response()->json([
                'message' => 'Registration failed',
                'errors' => $validator->errors()->all(),
            ],401);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        $token = $user->createToken('auth-token');

        return response()->json([
            'message' => 'User Registered',
            'data' => [
                'token' => $token->plainTextToken,
                'user' => $user
            ],
        ], 201);
    }

    public function login(Request $request)
    {   
        $validateRules = [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ];
        $validator = Validator::make($request->all(),$validateRules);

        if($validator->fails()){
            return response()->json([
                'message' => 'Bad credentials',
                'errors' => $validator->errors()->all(),
            ],401);
        }

        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'message' => 'Login Success',
                'data' => [
                    'token' => $token,
                    'user' => $user,
                ],
            ], 200);

        } else {
            return response()->json([
                'message' => 'Bad credentials',
                'errors' => ['Email or Password Incorect']
            ], 401);

        }
    }

    public function logout(Request $request)
    {   
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out','data'=>[]], 200);
    }
}
