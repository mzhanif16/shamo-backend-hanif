<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\NewAccessToken;

class UserController extends Controller
{
   public function register(Request $request){
    try {
        $request->validate([
            'name' => ['required','string','max:255'],
            'username' => ['required','string','max:255','unique:users'],
            'email' => ['required','string','email','max:255','unique:users'],
            'phone' => ['nullable','string','max:255'],
            'password' => ['required','string', Password::min(8)],
        ]);
        User::create([
            'name'=>$request->name,
            'username'=>$request->username,
            'email'=>$request->email,
            'phone'=>$request->phone,
            'password'=>Hash::make($request->password)
        ]);

        $user = User::where('email',$request->email)->first();
        $tokenResult = $user->createToken('authToken')->plainTextToken;
        return ResponseFormatter::success([
            'access_token' => $tokenResult,
            'token_type'=>'Bearer',
            'user' => $user
        ],'User Registered');
    } catch (\Throwable $error) {
        return ResponseFormatter::error([
            'message'=> 'Something went wrong',
            'error'=> $error
        ],'Authentication Failed',500);
    }
   }
   
   public function login(Request $request){
    try {
        $request->validate([
            'email' => 'email|required',
            'password' => 'required'
        ]);
        $credentials = request(['email','password']);
        if(!Auth::attempt($credentials)){
            $errorMessage = 'Unauthorized';
    
        // Periksa apakah email atau password yang salah
        $user = User::where('email', $credentials['email'])->first();
        if (!$user) {
            $errorMessage = 'Email not found';
        } elseif (!Hash::check($credentials['password'], $user->password, [])) {
            $errorMessage = 'Incorrect password';
        }

            return ResponseFormatter::error(null,$errorMessage,400);
        }
        $user = User::where('email',$request->email)->first();

        // $tokenResult = $this->createCustomToken();
        
        $tokenResult = $user->createToken('authToken')->plainTextToken;

        return ResponseFormatter::success([
            'access_token' => $tokenResult,
            'token_type' => 'Bearer',
            'user' => $user
        ],'Login Berhasil');
    } catch (\Exception $error) {
        return ResponseFormatter::error([
            'message' => 'Something went wrong',
            'error' => $error
        ],'Authentication Failed',500);
    }
   }

   public function fetch(Request $request){
    return ResponseFormatter::success($request->user(), 'Data profile user berhasil diambil');
   }
   public function updateProfile(Request $request){
    $data = $request->all();
    $user = Auth::user();
    $user->update($data);
    return ResponseFormatter::success($user,'Profile Updated');
   }

   public function logout(Request $request){
    $token = $request->user()->currentAccessToken()->delete();
    return ResponseFormatter::success($token,'Token Revoked');
   }
}
