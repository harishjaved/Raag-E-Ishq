<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\TotpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $email = strtolower(trim($credentials['email']));
        $throttleKey = Str::transliterate($email . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            AuditLog::log(
                action: 'AUTH_LOGIN_LOCKOUT',
                targetType: 'User',
                newValues: ['email' => $email, 'retry_after_seconds' => $seconds]
            );

            return back()->withInput($request->only('email'))->withErrors([
                'email' => "Too many sign-in attempts. Please wait {$seconds} seconds before trying again.",
            ]);
        }

        $remember = (bool) $request->boolean('remember');
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            RateLimiter::hit($throttleKey, 60);

            if (RateLimiter::attempts($throttleKey) >= 5) {
                AuditLog::log(
                    action: 'AUTH_LOGIN_LOCKOUT',
                    targetType: 'User',
                    newValues: ['email' => $email, 'attempts' => RateLimiter::attempts($throttleKey)]
                );
            }

            AuditLog::log(
                action: 'AUTH_LOGIN_FAILED',
                targetType: 'User',
                targetId: $user?->id,
                newValues: ['email' => $email]
            );

            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        if (!$user->is_active) {
            AuditLog::log(
                action: 'AUTH_INACTIVE_ACCOUNT_BLOCKED',
                targetType: 'User',
                targetId: $user->id,
                userId: $user->id
            );

            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'Your account has been deactivated. Please contact Super Admin.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        // Enforce 2FA challenge if configured for this user
        if ($user->hasTwoFactorEnabled()) {
            $request->session()->regenerate();
            $request->session()->put([
                'pending_2fa_user_id' => $user->id,
                'pending_2fa_remember' => $remember,
                'pending_2fa_expires_at' => now()->addMinutes(10)->timestamp,
            ]);

            return redirect()->route('two-factor.challenge');
        }

        Auth::login($user, $remember);
        $request->session()->regenerate();

        AuditLog::log(
            action: 'AUTH_LOGIN_SUCCESS',
            targetType: 'User',
            targetId: $user->id,
            newValues: ['role' => $user->role],
            userId: $user->id
        );

        return $this->redirectBasedOnRole($user);
    }

    public function showTwoFactorChallenge(Request $request): View|RedirectResponse
    {
        if (!$this->hasValidPendingTwoFactorSession($request)) {
            return redirect()->route('login')->withErrors([
                'email' => 'Your verification session expired. Please sign in again.',
            ]);
        }

        return view('auth.two-factor-challenge');
    }

    public function verifyTwoFactorChallenge(Request $request, TotpService $totpService): RedirectResponse
    {
        if (!$this->hasValidPendingTwoFactorSession($request)) {
            return redirect()->route('login')->withErrors([
                'email' => 'Your verification session expired. Please sign in again.',
            ]);
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'min:6', 'max:24'],
        ]);

        $user = User::find($request->session()->get('pending_2fa_user_id'));
        if (!$user || !$user->is_active || !$user->hasTwoFactorEnabled()) {
            $this->clearPendingTwoFactorSession($request);
            return redirect()->route('login')->withErrors([
                'email' => 'Account verification could not be completed.',
            ]);
        }

        $inputCode = trim($validated['code']);
        $isValidTotp = $totpService->verifyCode((string) $user->two_factor_secret, $inputCode);
        $usedRecovery = false;

        if (!$isValidTotp) {
            $usedRecovery = $totpService->consumeRecoveryCode($user, $inputCode);
        }

        if (!$isValidTotp && !$usedRecovery) {
            AuditLog::log(
                action: 'AUTH_2FA_FAILED',
                targetType: 'User',
                targetId: $user->id,
                userId: $user->id
            );

            return back()->withErrors([
                'code' => 'Invalid authenticator or recovery code. Please try again.',
            ]);
        }

        $remember = (bool) $request->session()->get('pending_2fa_remember', false);
        $this->clearPendingTwoFactorSession($request);

        Auth::login($user, $remember);
        $request->session()->regenerate();

        AuditLog::log(
            action: 'AUTH_2FA_VERIFIED',
            targetType: 'User',
            targetId: $user->id,
            newValues: ['method' => $usedRecovery ? 'recovery_code' : 'totp', 'role' => $user->role],
            userId: $user->id
        );

        return $this->redirectBasedOnRole($user);
    }

    public function logout(Request $request): RedirectResponse
    {
        $userId = Auth::id();
        if ($userId) {
            AuditLog::log(
                action: 'AUTH_LOGOUT',
                targetType: 'User',
                targetId: $userId,
                userId: $userId
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    protected function hasValidPendingTwoFactorSession(Request $request): bool
    {
        $userId = $request->session()->get('pending_2fa_user_id');
        $expiresAt = (int) $request->session()->get('pending_2fa_expires_at', 0);

        if (!$userId || $expiresAt < now()->timestamp) {
            $this->clearPendingTwoFactorSession($request);
            return false;
        }

        return true;
    }

    protected function clearPendingTwoFactorSession(Request $request): void
    {
        $request->session()->forget([
            'pending_2fa_user_id',
            'pending_2fa_remember',
            'pending_2fa_expires_at',
        ]);
    }

    protected function redirectBasedOnRole(User $user): RedirectResponse
    {
        if ($user->canAccessAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('staff.scan'));
    }
}
