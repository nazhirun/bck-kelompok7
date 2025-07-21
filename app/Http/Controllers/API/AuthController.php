<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password as PasswordFacade;

class AuthController extends Controller
{
    /**
     * Registrasi pengguna dan mengirim OTP
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => [
                'required',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Buat user baru
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_verified' => false,
            'role' => 'user',
        ]);

        // Generate OTP
        $otp = $user->generateOTP();

        try {
            // Kirim OTP via email
            Mail::to($user->email)->send(new OtpMail($otp, $user->name));

            return response()->json([
                'success' => true,
                'message' => 'Pendaftaran berhasil. Kode OTP telah dikirim ke email Anda.',
                'user_id' => $user->id,
                'expires_at' => $user->otp_expires_at
            ], 201);
        } catch (\Exception $e) {
            $user->delete();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email OTP',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verifikasi OTP yang diinputkan oleh pengguna
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyOtp(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'otp' => 'required|string|size:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Cari user
        $user = User::findOrFail($request->user_id);

        // Cek apakah OTP valid
        if ($user->isOTPExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP telah kadaluarsa'
            ], 400);
        }

        // Verifikasi OTP
        if ($user->verifyOTP($request->otp)) {
            // Generate token jika berhasil
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Verifikasi berhasil',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_verified' => $user->is_verified
                ],
                'token' => $token
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Kode OTP tidak valid'
        ], 400);
    }

    /**
     * Kirim ulang OTP
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Cari user
        $user = User::findOrFail($request->user_id);

        // Jika sudah terverifikasi
        if ($user->is_verified) {
            return response()->json([
                'success' => false,
                'message' => 'Akun sudah terverifikasi'
            ], 400);
        }

        // Generate OTP baru
        $otp = $user->generateOTP();

        try {
            // Kirim OTP via email
            Mail::to($user->email)->send(new OtpMail($otp, $user->name));

            return response()->json([
                'success' => true,
                'message' => 'Kode OTP baru telah dikirim ke email Anda.',
                'expires_at' => $user->otp_expires_at
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email OTP',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login pengguna
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Cari user berdasarkan email
        $user = User::where('email', $request->email)->first();

        // Cek apakah user ditemukan dan password benar
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah'
            ], 401);
        }

        // Cek apakah user sudah terverifikasi
        if (!$user->is_verified) {
            // Generate OTP baru
            $otp = $user->generateOTP();
            
            try {
                // Kirim OTP via email
                Mail::to($user->email)->send(new OtpMail($otp, $user->name));
                
                return response()->json([
                    'success' => false,
                    'message' => 'Akun belum terverifikasi. Kode OTP telah dikirim ke email Anda.',
                    'user_id' => $user->id,
                    'verification_required' => true,
                    'expires_at' => $user->otp_expires_at
                ], 403);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengirim email OTP',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_verified' => $user->is_verified,
                'role' => $user->role
            ],
            'token' => $token
        ]);
    }

    /**
     * Logout pengguna
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Hapus semua token pengguna yang saat ini aktif
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    }

    /**
     * Dapatkan data profil pengguna
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile(Request $request)
    {
        return response()->json([
            'success' => true,
            'user' => $request->user()
        ]);
    }

    /**
     * Mengirim OTP untuk reset password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();
        $otp = $user->generateOTP();

        try {
            Mail::to($user->email)->send(new OtpMail($otp, $user->name, 'reset_password'));

            return response()->json([
                'success' => true,
                'message' => 'Kode OTP untuk reset password telah dikirim ke email Anda.',
                'email' => $user->email,
                'expires_at' => $user->otp_expires_at
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email reset password',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verifikasi OTP untuk reset password
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyResetOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|exists:users,email',
            'otp' => 'required|string|size:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();
        
        if ($user->otp !== $request->otp || $user->isOTPExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP tidak valid atau telah kadaluarsa'
            ], 400);
        }

        // Generate token reset password
        $token = Str::random(60);
        
        // Simpan token ke database
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'email' => $request->email,
                'token' => $token,
                'created_at' => Carbon::now()
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Verifikasi OTP berhasil',
            'reset_token' => $token
        ]);
    }

    /**
     * Reset password dengan token
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|exists:users,email',
            'token' => 'required|string',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Cek token reset password
        $tokenData = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$tokenData) {
            return response()->json([
                'success' => false,
                'message' => 'Token reset password tidak valid'
            ], 400);
        }

        // Cek apakah token sudah kadaluarsa (1 jam)
        if (Carbon::parse($tokenData->created_at)->addHour()->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Token reset password telah kadaluarsa'
            ], 400);
        }

        // Update password
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();

        // Hapus token reset password
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah'
        ]);
    }
}