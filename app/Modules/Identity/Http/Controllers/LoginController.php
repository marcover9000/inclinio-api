<?php

namespace App\Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Http\Requests\LoginRequest;
use App\Modules\Identity\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/*
 * Endpoint POST /login. Autentica via Sanctum SPA cookies.
 * Aplica throttling: 5 intents/min per (email, IP).
 */
class LoginController extends Controller
{
    public function __invoke(LoginRequest $request): UserResource
    {
        $this->ensureIsNotRateLimited($request);

        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey($request));
            throw ValidationException::withMessages([
                'email' => 'Les credencials no són vàlides.',
            ]);
        }

        $request->session()->regenerate();
        RateLimiter::clear($this->throttleKey($request));

        return new UserResource($request->user());
    }

    private function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));
        throw ValidationException::withMessages([
            'email' => "Massa intents. Torna a provar en {$seconds} segons.",
        ])->status(429);
    }

    /**
     * Clau de throttling: combina email i IP per evitar:
     * - DoS contra IPs compartides (NAT, oficina) si fos només email
     * - Bypass si fos només IP (atacant amb molts emails)
     */
    private function throttleKey(Request $request): string
    {
        $email = Str::lower((string) $request->input('email'));
        return "login:{$email}|{$request->ip()}";
    }
}
