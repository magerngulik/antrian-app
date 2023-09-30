<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Assignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class AuthentificationController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="Login User",
     *     description="Endpoint untuk melakukan autentikasi dan mendapatkan token akses.",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Data login yang diperlukan.",
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="admin@admin.com", description="Alamat email pengguna."),
     *             @OA\Property(property="password", type="string", example="password", description="Password pengguna."),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Berhasil login dan mendapatkan token akses.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success", description="Status response."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string", example="Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjlmNjE5NjM3YzAxZ...",
     *                     description="Token akses yang digunakan untuk otentikasi."),
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=1, description="ID pengguna."),
     *                     @OA\Property(property="name", type="string", example="John Doe", description="Nama pengguna."),
     *                     @OA\Property(property="email", type="string", format="email", example="user@example.com", description="Alamat email pengguna."),
     *                     @OA\Property(property="assignment", type="integer", example=123, description="ID tugas yang diberikan kepada pengguna."),
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Gagal autentikasi, email atau password salah.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error", description="Status response."),
     *             @OA\Property(property="message", type="string", example="Email atau password salah", description="Pesan error."),
     *         ),
     *     ),
     * )
     */

   function login(Request $request){
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ], [
        'email.required' => 'Email harus diisi.',
        'email.email' => 'Format email tidak valid.',
        'password.required' => 'Password harus diisi.',
    ]);
    if (Auth::attempt($credentials)) {
        $user = Auth::user();
        $user->tokens()->delete();
        $today = Carbon::today();
        $token = $user->createToken('auth_token')->plainTextToken;
         // Menggunakan first() untuk mengambil satu hasil pertama dari query
        $assignment = Assignment::where('user_id', $user->id)
            ->whereDate('created_at', $today)
            ->first();
        $assignedRoles = $assignment ? $assignment->id : null;
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'token' => $token,
                "user"=> [
                            "id" => $user->id,
                            "name" => $user->name,
                            "email" => $user->email,
                            "assignment" => $assignedRoles
                        ]],
        ], 200);
    }
    return response()->json([
        'status' => 'error',
        'message' => 'Email atau password salah',
    ], 401);
   }


   /**
     * @OA\Post(
     *     path="/api/auth/register",
     *     summary="Register User",
     *     description="Endpoint untuk mendaftarkan pengguna baru.",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Data yang diperlukan untuk mendaftarkan pengguna baru.",
     *         @OA\JsonContent(
     *             required={"name", "email", "password"},
     *             @OA\Property(property="name", type="string", example="John Doe", description="Nama pengguna."),
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com", description="Alamat email pengguna."),
     *             @OA\Property(property="password", type="string", example="password123", description="Password pengguna (minimal 6 karakter)."),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Pengguna berhasil terdaftar.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User registered successfully", description="Pesan sukses."),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Gagal mendaftarkan pengguna karena validasi gagal.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation error", description="Pesan error."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="name", type="array", @OA\Items(type="string"), example={"The name field is required."}, description="Daftar error validasi untuk nama."),
     *                 @OA\Property(property="email", type="array", @OA\Items(type="string"), example={"The email field is required.", "The email has already been taken."}, description="Daftar error validasi untuk email."),
     *                 @OA\Property(property="password", type="array", @OA\Items(type="string"), example={"The password field is required.", "The password must be at least 6 characters."}, description="Daftar error validasi untuk password."),
     *             ),
     *         ),
     *     ),
     * )
     */

   function register(Request $request){
    $validator = Validator::make($request->all(), [
        'name' => 'required',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:6',
    ]);
        if ($validator->fails()) {
        return response()->json([
            'message' => 'Validation error',
            'errors' => $validator->errors()
        ], 422);
    }        
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);
    if ($request->hasFile('avatar')) {
        $avatar = $request->file('avatar');
        $avatarPath = $avatar->store('avatars', 'public');
        $user->avatar = $avatarPath;
        $user->save();
    }
    return response()->json(['message' => 'User registered successfully'], 201);
   }
    /**
     * @OA\Get(
     *     path="/api/auth/logout",
     *     summary="Logout User",
     *     description="Endpoint untuk logout pengguna dan mencabut token akses saat ini.",
     *     tags={"Authentication"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Berhasil logout pengguna.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success", description="Status response."),
     *             @OA\Property(property="message", type="string", example="Berhasil logout", description="Pesan sukses."),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Gagal autentikasi, token tidak valid atau tidak ada token.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated", description="Pesan error."),
     *         ),
     *     ),
     * )
     */

   function logout(Request $request){
    $request->user()->currentAccessToken()->delete();
    return response()->json(["status" => "success", "message" => "berhasil logout"], 200);
    }

    
    function me(Request $request) {
        $user = Auth::user();
        return response()->json($user, 200);
    }
}
