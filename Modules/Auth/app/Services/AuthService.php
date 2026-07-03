<?php

namespace Modules\Auth\app\Services;

use App\Exceptions\AppException;
use App\Exceptions\ConflictException;
use App\Exceptions\NotFoundAppException;
use App\Exceptions\UnauthorizedException;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\app\Interfaces\IAuthService;

class AuthService implements IAuthService
{
    public function __construct(private JwtService $jwt) {}

    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->with('roles')->first();
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw new UnauthorizedException('Wrong email or password.');
        }
        if ($user->status !== 'Active') {
            throw new UnauthorizedException('Account is not active.');
        }
        session(['user_id' => $user->id]);
        $token = $this->jwt->makeAccessToken($user->id);

        return ['user' => $user, 'token' => $token];
    }

    public function register(array $data): array
    {
        if (User::where('email', $data['email'])->exists()) {
            throw new ConflictException('Email already exists.');
        }
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => 'Active',
        ]);
        session(['user_id' => $user->id]);
        $token = $this->jwt->makeAccessToken($user->id);

        return ['user' => $user, 'token' => $token];
    }

    public function logout(string $userId, ?string $jwtToken = null): void
    {
        if ($jwtToken) {
            $this->jwt->blacklist($jwtToken);
        }
        session()->forget('user_id');
        session()->regenerate();
    }

    public function requestOtp(string $email): void
    {
        $user = User::where('email', $email)->first();
        if (! $user) {
            throw new NotFoundAppException('Email not found.');
        }
        $otp = generate_otp();
        $expiryMinutes = (int) config('laraveladmin.otp_expiry_minutes', 10);
        $user->update([
            'password_otp' => hash_otp($otp),
            'password_otp_expires' => now()->addMinutes($expiryMinutes),
        ]);
        // TODO: send email with OTP $otp
    }

    public function processOtp(array $data): bool
    {
        $user = User::where('email', $data['email'])->first();
        if (! $user || ! $user->password_otp) {
            throw new AppException('OTP is invalid.');
        }
        if (now()->isAfter($user->password_otp_expires)) {
            throw new AppException('OTP has expired.');
        }
        if (! verify_otp($data['otp'], $user->password_otp)) {
            throw new AppException('OTP is invalid.');
        }

        $user->update([
            'password' => Hash::make($data['password']),
            'password_otp' => null,
            'password_otp_expires' => null,
        ]);

        return true;
    }
}
