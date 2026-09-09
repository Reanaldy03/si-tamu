<?php

namespace App\Http\Controllers\Auth;

use App\Application\Audit\Actions\RecordAuditEventAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        protected RecordAuditEventAction $recordAuditEvent,
    ) {}

    /**
     * Display the login view.
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->ensureIsNotRateLimited();

        $email = (string) $request->input('email');
        $password = (string) $request->input('password');

        $user = User::where('email', $email)->first();

        // 1. Verify credentials without revealing user existence
        if (! $user || ! Hash::check($password, $user->password)) {
            $request->hitRateLimiter();

            $this->recordAuditEvent->execute(
                eventType: 'auth.login_failed',
                actorType: 'PUBLIC',
                requestId: (string) $request->attributes->get('request_id'),
                ipAddress: $request->ip(),
                metadata: ['attempted_email' => $email],
            );

            throw ValidationException::withMessages([
                'email' => 'Kredensial yang diberikan tidak cocok dengan data kami.',
            ]);
        }

        // 2. Check if user account is active
        if (! $user->is_active) {
            $request->hitRateLimiter();

            $this->recordAuditEvent->execute(
                eventType: 'auth.login_blocked_inactive',
                actorType: 'USER',
                actorUserId: $user->id,
                targetType: 'user',
                targetId: $user->id,
                targetPublicId: $user->public_id,
                requestId: (string) $request->attributes->get('request_id'),
                ipAddress: $request->ip(),
                metadata: ['email' => $user->email],
            );

            throw ValidationException::withMessages([
                'email' => 'Akun Anda berstatus nonaktif. Silakan hubungi administrator.',
            ]);
        }

        // 3. Authenticate and regenerate session
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        $request->clearRateLimiter();

        $user->update(['last_login_at' => now()]);

        // 4. Record audit event
        $this->recordAuditEvent->execute(
            eventType: 'auth.login_success',
            actorType: 'USER',
            actorUserId: $user->id,
            targetType: 'user',
            targetId: $user->id,
            targetPublicId: $user->public_id,
            requestId: (string) $request->attributes->get('request_id'),
            ipAddress: $request->ip(),
        );

        return redirect()->intended(route('admin.dashboard', [], false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        if ($user = Auth::user()) {
            $this->recordAuditEvent->execute(
                eventType: 'auth.logout',
                actorType: 'USER',
                actorUserId: $user->id,
                targetType: 'user',
                targetId: $user->id,
                targetPublicId: $user->public_id,
                requestId: (string) $request->attributes->get('request_id'),
                ipAddress: $request->ip(),
            );

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()->route('login');
    }
}
