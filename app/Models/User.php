<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;
use Carbon\Carbon;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'otp',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'is_verified' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * Generate OTP for user
     *
     * @return string
     */
    public function generateOTP(): string
    {
        $otp = sprintf('%06d', random_int(0, 999999));
        $this->otp = $otp;
        $this->otp_expires_at = Carbon::now()->addMinutes(10);
        $this->save();

        return $otp;
    }

    /**
     * Verify OTP
     *
     * @param string $otp
     * @return boolean
     */
    public function verifyOTP(string $otp): bool
    {
        if ($this->otp === $otp && $this->otp_expires_at > Carbon::now()) {
            $this->is_verified = true;
            $this->email_verified_at = Carbon::now();
            $this->otp = null;
            $this->otp_expires_at = null;
            $this->save();

            return true;
        }

        return false;
    }

    /**
     * Check if OTP is expired
     *
     * @return boolean
     */
    public function isOTPExpired(): bool
    {
        return $this->otp_expires_at < Carbon::now();
    }
}