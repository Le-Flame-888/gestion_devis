<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'message' => 'Les identifiants fournis sont incorrects.'
                ], 401);
            }

            $user = User::where('email', $request->email)->firstOrFail();
            
            // Révoquer tous les tokens existants
            $user->tokens()->delete();
            
            // Créer un nouveau token
            $token = $user->createToken('auth_token')->plainTextToken;
            
            // Créer un cookie sécurisé
            $cookie = cookie(
                'auth_token',
                $token,
                config('sanctum.expiration', 60 * 24 * 7), // 1 semaine par défaut
                null,
                null,
                config('session.secure', false),  // secure
                true,  // httpOnly
                false, // sameSite
                'Lax'  // sameSite policy
            );
            
            return response()
                ->json([
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'user' => $user
                ])
                ->withCookie($cookie);
                
        } catch (\Exception $e) {
            \Log::error('Login error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Une erreur est survenue lors de la connexion.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function user(Request $request)
    {
        return $request->user();
    }
    //
}
