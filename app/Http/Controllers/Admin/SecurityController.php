<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\TotpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SecurityController extends Controller
{
    public function index(Request $request, TotpService $totpService): View
    {
        $user = $request->user();
        $pendingSecret = $request->session()->get('setup_2fa_secret');
        $qrSvg = null;

        if (!$user->hasTwoFactorEnabled()) {
            if (!$pendingSecret) {
                $pendingSecret = $totpService->generateSecret();
                $request->session()->put('setup_2fa_secret', $pendingSecret);
            }
            $qrSvg = $totpService->renderProvisioningQrSvg($user, $pendingSecret, 165);
        }

        $staffAccounts = $user->isSuperAdmin()
            ? User::orderBy('role')->orderBy('name')->get()
            : collect();

        return view('admin.security.index', [
            'user' => $user,
            'pendingSecret' => $pendingSecret,
            'qrSvg' => $qrSvg,
            'staffAccounts' => $staffAccounts,
        ]);
    }

    public function enableTwoFactor(Request $request, TotpService $totpService): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'min:6', 'max:10'],
        ]);

        $user = $request->user();
        $secret = (string) $request->session()->get('setup_2fa_secret', '');

        if ($secret === '' || !$totpService->verifyCode($secret, $validated['code'])) {
            return back()->withErrors(['code' => 'Invalid 6-digit authenticator code. Please scan again and enter a live code.']);
        }

        $recoveryCodes = $totpService->generateRecoveryCodes();

        $user->update([
            'two_factor_secret' => $secret,
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => $recoveryCodes,
        ]);

        $request->session()->forget('setup_2fa_secret');

        AuditLog::log(
            action: 'AUTH_2FA_ENABLED',
            targetType: 'User',
            targetId: $user->id,
            userId: $user->id
        );

        return back()->with([
            'success' => 'Two-Factor Authentication (TOTP) is now active on your account.',
            'new_recovery_codes' => $recoveryCodes,
        ]);
    }

    public function disableTwoFactor(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
        ]);

        $user = $request->user();
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password does not match.']);
        }

        $user->update([
            'two_factor_secret' => null,
            'two_factor_enabled' => false,
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => null,
        ]);

        AuditLog::log(
            action: 'AUTH_2FA_DISABLED',
            targetType: 'User',
            targetId: $user->id,
            userId: $user->id
        );

        return back()->with('info', 'Two-Factor Authentication has been disabled for your account.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:10', 'confirmed'],
        ]);

        $user = $request->user();
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password verification failed.']);
        }

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        AuditLog::log(
            action: 'AUTH_PASSWORD_ROTATED',
            targetType: 'User',
            targetId: $user->id,
            userId: $user->id
        );

        return back()->with('success', 'Your account password has been updated cleanly.');
    }

    public function toggleStaffStatus(Request $request, int $id): RedirectResponse
    {
        if (!$request->user()->isSuperAdmin()) {
            abort(403);
        }

        $target = User::findOrFail($id);
        if ($target->id === $request->user()->id) {
            return back()->withErrors(['staff' => 'You cannot deactivate your own active Super Admin session.']);
        }

        $old = $target->is_active;
        $target->update(['is_active' => !$old]);

        AuditLog::log(
            action: 'STAFF_STATUS_TOGGLED',
            targetType: 'User',
            targetId: $target->id,
            oldValues: ['is_active' => $old],
            newValues: ['is_active' => !$old, 'email' => $target->email],
            userId: $request->user()->id
        );

        return back()->with('success', ($target->is_active ? 'Activated' : 'Deactivated') . " staff account {$target->email}.");
    }
}
