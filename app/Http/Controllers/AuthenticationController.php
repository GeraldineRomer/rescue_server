<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Routing\Controller;


class AuthenticationController extends Controller
{
    /**
     * Create a new controller instance.
     * It will be used to define the middleware that will be applied to the controller methods.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['login']);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8|max:100',
            'device_name' => 'required|string|max:100',
        ]);

        $user = User::where('email', $request->email)->orWhereHas('secondaryEmails', function ($q) use ($request) {
            $q->where('email', $request->email);
        })->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__("auth.credentials")],
            ]);
        }

        $token = $user->createToken($request->device_name)->plainTextToken;
        return response()->json(['message' => __("auth.success"), 'token' => $token, 'user' => $user], 200);
    }

    /**
     * Destroy an authenticated session.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => __("auth.logout_success")], 200);
    }
}
