<?php

namespace App\Support;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SafeMail
{
    public static function send(string|array $to, Mailable $mailable, array $context = []): bool
    {
        try {
            Mail::to($to)->send($mailable);

            return true;
        } catch (\Throwable $e) {
            Log::warning('No se pudo enviar un email.', array_merge($context, [
                'to' => $to,
                'mailable' => $mailable::class,
                'exception' => $e,
            ]));

            return false;
        }
    }
}
