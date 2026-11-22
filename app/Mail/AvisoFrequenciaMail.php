<?php

namespace App\Mail;

use App\Models\AlunoModel;
use App\Models\Materia;
use App\Support\SituacaoAcademica;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class AvisoFrequenciaMail extends Mailable
{
    use Queueable;

    public function __construct(
        public AlunoModel $aluno,
        public Materia $materia,
        public SituacaoAcademica $situacao,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Atenção às faltas · ' . $this->materia->nome);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.aviso-frequencia');
    }
}
