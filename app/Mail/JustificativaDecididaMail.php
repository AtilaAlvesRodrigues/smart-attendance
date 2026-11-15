<?php

namespace App\Mail;

use App\Models\Justificativa;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class JustificativaDecididaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Justificativa $justificativa) {}

    public function envelope(): Envelope
    {
        $aprovada = $this->justificativa->status === Justificativa::STATUS_APROVADA;

        return new Envelope(
            subject: ($aprovada ? 'Falta abonada' : 'Justificativa recusada')
                . ' · ' . $this->justificativa->materia->nome,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.justificativa-decidida');
    }
}
