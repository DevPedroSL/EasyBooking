<?php

namespace App\Mail;

use App\Models\BarbershopRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BarbershopRequestApproved extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public BarbershopRequest $barbershopRequest)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Solicitud de barberia aceptada - ' . $this->barbershopRequest->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.barbershop_request_approved',
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
