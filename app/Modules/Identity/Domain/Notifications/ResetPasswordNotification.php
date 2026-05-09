<?php

namespace App\Modules\Identity\Domain\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/*
 * Notificació enviada quan un usuari sol·licita el reset de contrasenya.
 * Plantilla en català. Conté un link cap a admin.inclinio.localhost amb token + email.
 */
class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(public string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $adminUrl = config('inclinio.admin_url', 'http://admin.inclinio.localhost');
        $url = "{$adminUrl}/password/reset/{$this->token}?email=" . urlencode($notifiable->email);

        return (new MailMessage)
            ->subject('Restableix la teva contrasenya — Inclinio')
            ->greeting("Hola {$notifiable->name},")
            ->line('Has sol·licitat restablir la teva contrasenya al panell d\'administració d\'Inclinio.')
            ->action('Restablir contrasenya', $url)
            ->line("Aquest enllaç caduca en " . config('auth.passwords.users.expire', 60) . " minuts.")
            ->line('Si no has demanat aquest reset, pots ignorar aquest correu sense problema.');
    }
}
