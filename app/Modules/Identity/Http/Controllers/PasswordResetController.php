<?php

namespace App\Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Http\Requests\EmailPasswordRequest;
use App\Modules\Identity\Http\Requests\ResetPasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

/*
 * Gestiona el flow de password reset:
 * - sendResetEmail: envia notificació amb token (200 sempre per no revelar existència).
 * - reset: aplica la nova contrasenya a partir del token.
 */
class PasswordResetController extends Controller
{
    public function sendResetEmail(EmailPasswordRequest $request): JsonResponse
    {
        Password::sendResetLink($request->only('email'));

        // Sempre retornem el mateix missatge perquè un atacant no pugui
        // utilitzar aquest endpoint per saber si un email està registrat.
        return response()->json([
            'message' => "Si l'email existeix, t'hem enviat un correu amb les instruccions.",
        ]);
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => __($status),
            ]);
        }

        return response()->json([
            'message' => 'Contrasenya restablerta correctament.',
        ]);
    }
}
