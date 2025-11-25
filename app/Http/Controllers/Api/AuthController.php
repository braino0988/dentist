<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log as Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $atts = $request->validate([
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'name' => 'required|string|max:255|unique:users,name'
            ]);
        Log::info('User Data Before Insert:', ['userData' => [
            'name' => $atts['name'],
            'email' => $atts['email'],
        ]]);
        $user = User::create([
            'name' => $atts['name'],
            'email' => $atts['email'],
            //here i am using the builr in hash function to hash the password
            'password' => Hash::make($atts['password'])
        ]);
        $user->sendEmailVerificationNotification();
        return response()->json([
            'message' => 'Registration successful. Please check your email to verify your account.'
        ]);
        // to be done letter
        // $user_name = $atts['name'];
        // $user_email = $atts['email'];
        // Mail::to($atts['email'])->send(new WelcomMail($user_name, $user_email));
        // return response()->json([
        //     'message' => 'user instance has been created successfully',
        //     'user' => UserResource::make($user),
        // ]);
    }
    public function login(Request $request){
        $atts=$request->validate([
            'email'=>'required|email|exists:users,email',
            'password'=>'required'
        ]);
        $user = User::where('email', $atts['email'])->first();
        if (!$user || !Hash::check($atts['password'], $user->password)) {
            return response()->json(['message' => 'wrong cardinatials'], 400);
        }
        $token=$user->createToken($user->name);
        return response()->json([
            'user' => $user,
            'token' => $token->plainTextToken
        ]);
    }
    public function logout(Request $request){
        //to ask louy
        //$request->user()->tokens()->delete();
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message'=>'logged out successfully']);
    }
    public function verifyEmail($id,$hash){
        $user = User::findOrFail($id);
        // Validate hash
        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            return response()->json(['message' => 'Invalid verification link'], 403);
        }

        // Already verified?
        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified']);
        }

        // Mark as verified
        $user->markEmailAsVerified();
        return view('success');
    }
}
