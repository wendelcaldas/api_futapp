<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Helpers\ApiResponse;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'apelido' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
            ]);
    
            $user = User::create([
                'name' => $request->name,
                'apelido' => $request->apelido,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
    
            $token = $user->createToken('mobile_token')->plainTextToken;
    
            return ApiResponse::success(
                200,
                'Cadastro realizado com sucesso',
                ['token' => $token, 'user' => $user]
            );
            
        } catch (ValidationException $e) {
            if ($e->errors()) {
                return ApiResponse::error(
                    400,
                    'Operação não realizada',
                    $e->errors()
                );
            }
            throw $e;
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('mobile_token')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
