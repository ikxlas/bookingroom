<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('username', $request->username)->first();

        // Mock behavior: auto-create user if not exist (except admin)
        if (!$user) {
            $user = User::create([
                'name' => $request->username,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'role' => 'user'
            ]);
        } else {
            if (!Hash::check($request->password, $user->password)) {
                return response()->json(['success' => false, 'message' => 'Invalid credentials'], 401);
            }
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'role' => $user->role,
            'token' => $token,
            'username' => $user->username
        ]);
    }

    public function user(Request $request)
    {
        return response()->json([
            'username' => $request->user()->username,
            'role' => $request->user()->role
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true]);
    }
}
