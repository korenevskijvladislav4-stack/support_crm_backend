<?php

namespace App\Http\Controllers;

use App\Http\Resources\AttemptsResource;
use App\Http\Resources\UserResource;
use App\Models\Attempt;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = Attempt::create([
            'name' => $request['name'],
            'surname' => $request['surname'],
            'email' => $request['email'],
            'phone' => $request['phone'] ?? null,
            'password' => Hash::make($request['password']),
        ]);


        return response()->json([
            'message' => "Заявка успешно создана"
        ], 201, [], JSON_UNESCAPED_UNICODE);
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            if (!Auth::attempt($credentials)) {
                return response()->json(['message' => 'Invalid credentials'], 401, [], JSON_UNESCAPED_UNICODE);
            }

            // Ищем пользователя, включая деактивированных (для проверки статуса)
            $user = User::withTrashed()->where('email', $request->email)->first();
            
            if (!$user) {
                return response()->json(['message' => 'User not found'], 404, [], JSON_UNESCAPED_UNICODE);
            }
            
            // Проверяем, не деактивирован ли пользователь
            if ($user->trashed()) {
                return response()->json([
                    'message' => 'Account deactivated',
                    'error' => 'Your account has been deactivated. Please contact administrator.'
                ], 403, [], JSON_UNESCAPED_UNICODE);
            }
            
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'token' => $token
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422, [], JSON_UNESCAPED_UNICODE);
        } catch (\PDOException $e) {
            \Log::error('Database error during login: ' . $e->getMessage());
            $errorMessage = config('app.debug') ? mb_convert_encoding($e->getMessage(), 'UTF-8', 'UTF-8') : 'Unable to connect to database';
            return response()->json([
                'message' => 'Database connection error',
                'error' => $errorMessage
            ], 500, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            \Log::error('Login error: ' . $e->getMessage());
            $errorMessage = config('app.debug') ? mb_convert_encoding($e->getMessage(), 'UTF-8', 'UTF-8') : 'Internal server error';
            return response()->json([
                'message' => 'An error occurred during login',
                'error' => $errorMessage
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out'], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function user(Request $request)
    {
        return response()->json($request->user(), 200, [], JSON_UNESCAPED_UNICODE);
    }
}
