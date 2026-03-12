<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'first_name'   => 'required|string|max:255',
            'last_name'    => 'required|string|max:255',
            'email'        => 'required|string|email|unique:users',
            'password'     => 'required|string|min:8|confirmed',
            'phone'        => 'nullable|string',
            'company_name' => 'required|string|max:255',
            'tax_id'       => 'nullable|string',
            'billing_address' => 'nullable|string',
            'contact_person'  => 'nullable|string',
        ]);

        // Create user
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'phone'      => $request->phone,
            'is_active'  => true,
        ]);

        // Assign client role
       // Assign client role
       // Assign client role
        // Assign client role
$clientRole = Role::where('name', 'client')->first();
if ($clientRole) {
    $user->roles()->attach($clientRole->id, [
        'assigned_at' => now(),
    ]);
}


        // Create client profile
        Client::create([
            'user_id'          => $user->id,
            'company_name'     => $request->company_name,
            'tax_id'           => $request->tax_id,
            'billing_address'  => $request->billing_address,
            'contact_person'   => $request->contact_person,
            'contact_email'    => $request->email,
            'contact_phone'    => $request->phone,
            'is_active'        => true,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful',
            'token'   => $token,
            'user'    => $user->load('client'),
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        $user = User::where('email', $request->email)->with('client', 'roles')->first();

        // Check if user is a client
        if (!$user->hasRole('client')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user->update(['last_login' => now()]);
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token'   => $token,
            'user'    => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user()->load('client', 'roles'));
    }
}