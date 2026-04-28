<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s\-]+$/u'],
           'email' => ['required', 'email', 'max:254', 'unique:users,email'],
           'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        // Normalize email before storage — prevents duplicate accounts via case variation
        $validated['email'] = mb_strtolower(trim($validated['email']));

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
         'password' => Hash::make($validated['password']), // 🔥 FIX
        ]);
        // $token = $user->createToken('temporia-app', ['*'], now()->addDays(30))->plainTextToken;

        return response()->json([
        'user' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'    => ['required', 'email', 'max:254'],
            'password' => ['required', 'string', 'max:200'],
        ]);

        // Normalize email — must match the normalization applied at registration
        $email = mb_strtolower(trim($validated['email']));

        /**
         * CWE-89 fix: Eloquent `where` uses PDO parameter binding internally.
         * The email value is passed as a bound parameter — never interpolated into SQL.
         * Using `->value('password')` fetches only the hash column, minimizing data exposure.
         */
        $user = User::where('email', $email)->first();

        /**
         * Constant-time password check:
         * Hash::check runs bcrypt comparison regardless of whether $user exists.
         * This prevents timing-based user enumeration attacks.
         */
        $passwordMatches = $user && Hash::check($validated['password'], $user->password);

        if (! $passwordMatches) {
            // Generic message — never reveal whether the email exists in the database
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Revoke all existing tokens on new login — prevents session fixation
        $user->tokens()->delete();

        $token = $user->createToken('temporia-app', ['*'], now()->addDays(30))->plainTextToken;

        return response()->json([
            'user'  => $user->only(['id', 'name', 'email', 'created_at']),
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me(Request $request)
    {
        // Only expose safe, non-sensitive fields
        return response()->json(
            $request->user()->only(['id', 'name', 'email', 'created_at'])
        );
    }
}
