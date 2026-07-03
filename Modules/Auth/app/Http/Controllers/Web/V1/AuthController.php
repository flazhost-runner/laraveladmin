<?php

namespace Modules\Auth\app\Http\Controllers\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Modules\Auth\app\Http\Requests\LoginRequest;
use Modules\Auth\app\Http\Requests\OtpProcessRequest;
use Modules\Auth\app\Http\Requests\OtpRequestRequest;
use Modules\Auth\app\Http\Requests\RegisterRequest;
use Modules\Auth\app\Interfaces\IAuthService;

class AuthController extends Controller
{
    public function __construct(private IAuthService $authService) {}

    public function loginForm()
    {
        return view('auth-module::be.default.login');
    }

    public function registerForm()
    {
        return view('auth-module::be.default.register');
    }

    public function resetReqForm()
    {
        return view('auth-module::be.default.reset_req');
    }

    public function resetProcForm(Request $req)
    {
        if (! $req->session()->get('otp_email')) {
            return redirect()->route('web.auth.login');
        }

        return view('auth-module::be.default.reset_proc');
    }

    public function login(LoginRequest $request)
    {
        $key = 'auth.'.$request->ip();
        $maxAttempts = (int) config('laraveladmin.rate_limit_auth', 10);
        $decaySeconds = (int) config('laraveladmin.rate_limit_auth_window', 900);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return back()->withInput()->with('error', 'Too many attempts. Please try again later.');
        }
        try {
            $this->authService->login($request->validated());
            RateLimiter::clear($key);

            return redirect()->route('admin.v1.dashboard.index');
        } catch (\Throwable $e) {
            RateLimiter::hit($key, $decaySeconds);

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function register(RegisterRequest $request)
    {
        $key = 'auth.'.$request->ip();
        $maxAttempts = (int) config('laraveladmin.rate_limit_auth', 10);
        $decaySeconds = (int) config('laraveladmin.rate_limit_auth_window', 900);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return back()->withInput()->with('error', 'Too many attempts. Please try again later.');
        }
        try {
            $this->authService->register($request->validated());
            RateLimiter::clear($key);

            return redirect()->route('admin.v1.dashboard.index')->with('success', 'Register Success.');
        } catch (\Throwable $e) {
            RateLimiter::hit($key, $decaySeconds);

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function logout(Request $request)
    {
        $userId = session('user_id');
        if ($userId) {
            $this->authService->logout($userId);
        }

        return redirect()->route('web.auth.login');
    }

    public function requestOtp(OtpRequestRequest $request)
    {
        $key = 'auth.'.$request->ip();
        $maxAttempts = (int) config('laraveladmin.rate_limit_auth', 10);
        $decaySeconds = (int) config('laraveladmin.rate_limit_auth_window', 900);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return back()->withInput()->with('error', 'Too many attempts. Please try again later.');
        }
        try {
            $this->authService->requestOtp($request->email);
            RateLimiter::hit($key, $decaySeconds);
            session(['otp_email' => $request->email]);

            return redirect()->route('admin.v1.auth.reset.proc')->with('success', 'OTP Send Success.');
        } catch (\Throwable $e) {
            RateLimiter::hit($key, $decaySeconds);

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function processOtp(OtpProcessRequest $request)
    {
        $key = 'otp.'.$request->ip();
        $maxAttempts = (int) config('laraveladmin.rate_limit_otp', 5);
        $decaySeconds = (int) config('laraveladmin.rate_limit_otp_window', 900);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return back()->with('error', 'Too many attempts. Please try again later.');
        }
        $email = session('otp_email');
        if (! $email) {
            return redirect()->route('web.auth.login');
        }
        try {
            $data = array_merge($request->validated(), ['email' => $email]);
            $this->authService->processOtp($data);
            RateLimiter::clear($key);
            session()->forget('otp_email');

            return redirect()->route('web.auth.login')->with('success', 'Reset Password Success.');
        } catch (\Throwable $e) {
            RateLimiter::hit($key, $decaySeconds);

            return back()->with('error', $e->getMessage());
        }
    }
}
