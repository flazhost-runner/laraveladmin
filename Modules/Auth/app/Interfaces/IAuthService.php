<?php

namespace Modules\Auth\app\Interfaces;

interface IAuthService
{
    public function login(array $credentials): array;

    public function register(array $data): array;

    public function logout(string $userId, ?string $jwtToken = null): void;

    public function requestOtp(string $email): void;

    public function processOtp(array $data): bool;
}
