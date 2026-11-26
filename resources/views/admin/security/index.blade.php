@extends('layouts.admin')

@section('title', 'Security & Two-Factor Authentication')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-3 border-b border-slate-800/60">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-bold uppercase tracking-wider mb-2">
                <span>Account Hardening &amp; Access Control</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-100 font-serif-display">Security &amp; 2FA Settings</h1>
            <p class="text-xs text-slate-400 mt-1">Manage authenticator TOTP protection, password rotation, and staff permissions.</p>
        </div>
    </div>

    @if (session('new_recovery_codes'))
        <div class="p-5 rounded-xl bg-amber-950/50 border border-amber-500/40 space-y-3">
            <div class="text-sm font-bold text-amber-300">Save Your Single-Use 2FA Recovery Codes</div>
            <p class="text-xs text-slate-300">Store these emergency codes in a password manager. Each code works once if you lose access to your authenticator app.</p>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                @foreach (session('new_recovery_codes') as $code)
                    <code class="px-3 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-amber-300 font-mono text-xs text-center">{{ $code }}</code>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Two-Factor Authentication Card -->
        <div class="p-6 rounded-xl bg-[#0e1424] border border-slate-800 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-bold text-slate-100 font-serif-display">Two-Factor Authentication (TOTP)</h2>
                @if ($user->hasTwoFactorEnabled())
                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-[11px] font-bold">ENABLED</span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full bg-amber-500/15 border border-amber-500/30 text-amber-400 text-[11px] font-bold">OPTIONAL (RECOMMENDED)</span>
                @endif
            </div>

            @if ($user->hasTwoFactorEnabled())
                <p class="text-xs text-slate-300 leading-relaxed">
                    Your account is protected with a time-based 6-digit authenticator code (confirmed {{ $user->two_factor_confirmed_at?->format('d M Y, H:i') }}).
                </p>
                <form action="{{ route('admin.security.2fa.disable') }}" method="POST" class="space-y-3 pt-2 border-t border-slate-800">
                    @csrf
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-300">Confirm Current Password to Disable 2FA</label>
                        <input type="password" name="current_password" required
                            class="w-full px-3.5 py-2 rounded-lg bg-slate-950 border border-slate-800 text-slate-100 text-xs focus:outline-none focus:border-amber-400">
                    </div>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-rose-600/20 hover:bg-rose-600/30 border border-rose-500/40 text-rose-300 text-xs font-bold transition">
                        Disable 2FA Protection
                    </button>
                </form>
            @else
                <p class="text-xs text-slate-300 leading-relaxed">
                    Scan this QR code with Google Authenticator, Authy, 1Password, or Apple Passwords, then enter the generated 6-digit code to activate 2FA.
                </p>
                <div class="flex flex-col sm:flex-row items-center gap-4 p-4 rounded-xl bg-slate-950 border border-slate-800">
                    <div class="bg-white p-2 rounded-lg flex-shrink-0">
                        {!! $qrSvg !!}
                    </div>
                    <div class="space-y-2 text-xs min-w-0">
                        <div class="text-slate-400 uppercase tracking-wider text-[10px] font-bold">Manual Setup Key</div>
                        <div class="font-mono text-amber-300 break-all bg-slate-900 px-2.5 py-1.5 rounded border border-slate-800">{{ $pendingSecret }}</div>
                    </div>
                </div>
                <form action="{{ route('admin.security.2fa.enable') }}" method="POST" class="flex flex-wrap items-end gap-3 pt-1">
                    @csrf
                    <div class="space-y-1 flex-grow">
                        <label class="text-xs font-semibold text-slate-300">Enter 6-Digit Verification Code</label>
                        <input type="text" name="code" required maxlength="10" placeholder="000000"
                            class="w-full px-3.5 py-2 rounded-lg bg-slate-950 border border-slate-800 text-slate-100 font-mono text-sm tracking-widest focus:outline-none focus:border-amber-400">
                    </div>
                    <button type="submit" class="px-5 py-2.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold transition">
                        Enable 2FA
                    </button>
                </form>
            @endif
        </div>

        <!-- Password Rotation Card -->
        <div class="p-6 rounded-xl bg-[#0e1424] border border-slate-800 space-y-4">
            <h2 class="text-base font-bold text-slate-100 font-serif-display">Rotate Account Password</h2>
            <p class="text-xs text-slate-400">Replace any initial password with a unique 10+ character passphrase before sharing production links.</p>

            <form action="{{ route('admin.security.password') }}" method="POST" class="space-y-3">
                @csrf
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-300">Current Password</label>
                    <input type="password" name="current_password" required
                        class="w-full px-3.5 py-2 rounded-lg bg-slate-950 border border-slate-800 text-slate-100 text-xs focus:outline-none focus:border-amber-400">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-300">New Password (min 10 characters)</label>
                    <input type="password" name="new_password" required minlength="10"
                        class="w-full px-3.5 py-2 rounded-lg bg-slate-950 border border-slate-800 text-slate-100 text-xs focus:outline-none focus:border-amber-400">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-300">Confirm New Password</label>
                    <input type="password" name="new_password_confirmation" required minlength="10"
                        class="w-full px-3.5 py-2 rounded-lg bg-slate-950 border border-slate-800 text-slate-100 text-xs focus:outline-none focus:border-amber-400">
                </div>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-bold transition">
                    Update Password
                </button>
            </form>
        </div>
    </div>

    @if ($user->isSuperAdmin() && $staffAccounts->isNotEmpty())
        <div class="p-6 rounded-xl bg-[#0e1424] border border-slate-800 space-y-4 overflow-x-auto">
            <div>
                <h2 class="text-base font-bold text-slate-100 font-serif-display">Staff Accounts &amp; RBAC Overview</h2>
                <p class="text-xs text-slate-400">Server-enforced permissions across Admin Operations and Gate Check-In.</p>
            </div>
            <table class="w-full text-xs text-left">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px]">
                        <th class="py-2.5 px-3">Staff Member</th>
                        <th class="py-2.5 px-3">Role</th>
                        <th class="py-2.5 px-3">2FA Status</th>
                        <th class="py-2.5 px-3">Account Status</th>
                        <th class="py-2.5 px-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @foreach ($staffAccounts as $staff)
                        <tr>
                            <td class="py-3 px-3">
                                <div class="font-bold text-slate-200">{{ $staff->name }}</div>
                                <div class="text-slate-400 font-mono text-[11px]">{{ $staff->email }}</div>
                            </td>
                            <td class="py-3 px-3 font-mono text-amber-300">{{ $staff->role }}</td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $staff->hasTwoFactorEnabled() ? 'bg-emerald-500/15 text-emerald-400' : 'bg-slate-800 text-slate-400' }}">
                                    {{ $staff->hasTwoFactorEnabled() ? '2FA ACTIVE' : 'Standard' }}
                                </span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $staff->is_active ? 'bg-emerald-500/15 text-emerald-400' : 'bg-rose-500/15 text-rose-400' }}">
                                    {{ $staff->is_active ? 'Active' : 'Deactivated' }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right">
                                @if ($staff->id !== $user->id)
                                    <form action="{{ route('admin.security.staff.toggle', $staff->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold">
                                            {{ $staff->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                @else
                                    <span class="text-slate-500 text-[11px]">Current session</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
