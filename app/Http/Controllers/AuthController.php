<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Helpers\ApiResponse;
use App\Notifications\EmailVerificationNotification;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // var_dump('chegou na rota');exit;
        try {

            $request->validate([
                'name' => 'required|string|max:255',
                'apelido' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
            ], [
                'name.required' => 'O campo nome é obrigatório.',
                'name.string' => 'O nome deve ser um texto válido.',
                'name.max' => 'O nome não pode ter mais de 255 caracteres.',
                
                'apelido.required' => 'O campo apelido é obrigatório.',
                'apelido.string' => 'O apelido deve ser um texto válido.',
                'apelido.max' => 'O apelido não pode ter mais de 255 caracteres.',
                
                'email.required' => 'O campo e-mail é obrigatório.',
                'email.string' => 'O e-mail deve ser um texto válido.',
                'email.email' => 'O e-mail deve ser um endereço válido.',
                'email.max' => 'O e-mail não pode ter mais de 255 caracteres.',
                'email.unique' => 'O e-mail informado já está cadastrado.',
                
                'password.required' => 'O campo senha é obrigatório.',
                'password.string' => 'A senha deve ser um texto válido.',
                'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
            ]);

            // var_dump('teste');exit;
            $user = User::create([
                'name' => $request->name,
                'apelido' => $request->apelido,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // $email = $user->notify(new EmailVerificationNotification());
            // var_dump($email);exit;
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
        try {
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
            // var_dump($token);exit;
            return response()->json(['token' => $token, 'user' => $user]);
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

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
