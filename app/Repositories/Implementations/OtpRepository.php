<?php

namespace App\Repositories\Implementations;

use App\Repositories\Interfaces\IOtpRepository;
use Illuminate\Support\Facades\DB;

class OtpRepository implements IOtpRepository
{
    public function createOtp(string $email, string $otp, int $expiresInMinutes = 15): void
    {
        // On supprime les anciens OTP pour cet email
        $this->deleteOtpsForEmail($email);

        DB::table('password_reset_otps')->insert([
            'email' => $email,
            'otp' => $otp,
            'expires_at' => now()->addMinutes($expiresInMinutes),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function findValidOtp(string $email, string $otp): ?object
    {
        return DB::table('password_reset_otps')
            ->where('email', $email)
            ->where('otp', $otp)
            ->where('expires_at', '>', now())
            ->first();
    }

    public function deleteOtpsForEmail(string $email): void
    {
        DB::table('password_reset_otps')->where('email', $email)->delete();
    }
}