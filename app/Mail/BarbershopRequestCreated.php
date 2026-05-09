<?php

namespace App\Mail;

use App\Models\BarbershopRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BarbershopRequestCreated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public BarbershopRequest $barbershopRequest)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nueva solicitud de barberia - ' . $this->barbershopRequest->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.barbershop_request_created',
            with: [
                'barbershopRequest' => $this->barbershopRequest,
                'requester' => $this->barbershopRequest->requester,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
