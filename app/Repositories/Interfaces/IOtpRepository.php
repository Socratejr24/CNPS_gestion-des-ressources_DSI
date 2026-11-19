<?php

namespace App\Repositories\Interfaces;

interface IOtpRepository
{
    public function createOtp(string $email, string $otp, int $expiresInMinutes = 15): void;
    public function findValidOtp(string $email, string $otp): ?object;
    public function deleteOtpsForEmail(string $email): void;
}