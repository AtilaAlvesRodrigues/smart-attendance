@extends('emails.layout')

@php
    $j = $justificativa;
    $aprovada = $j->status === \App\Models\Justificativa::STATUS_APROVADA;
@endphp

@section('titulo', $aprovada ? 'Falta abonada' : 'Justificativa recusada')

@section('conteudo')
    <p style="margin:0 0 6px; font-size:12px; font-weight:700; letter-spacing:2px; text-transform:uppercase; color:#666666;">{{ $j->materia->nome }}</p>
    <h1 style="margin:0 0 20px; font-size:24px; font-weight:900; letter-spacing:-0.5px;">
        {{ $aprovada ? 'Sua falta foi abonada' : 'Sua justificativa foi recusada' }}
    </h1>
    <p style="margin:0 0 16px;">Olá, {{ $j->aluno->nome }}.</p>
    <p style="margin:0 0 16px;">
        O professor analisou a justificativa da falta do dia <strong>{{ $j->data_aula->format('d/m/Y') }}</strong>.
        @if($aprovada)
            Ela foi <strong style="color:#16a34a;">aprovada</strong> e a falta não conta mais na sua frequência.
        @else
            Ela foi <strong style="color:#dc2626;">recusada</strong> e a falta continua contando. Se achar que houve engano, fale com o professor.
        @endif
    </p>
    @if($j->comentario_professor)
        <p style="margin:0 0 16px; padding:12px 16px; background:#f5f5f7; border-left:3px solid #111111;">
            <strong>Comentário do professor:</strong><br>{{ $j->comentario_professor }}
        </p>
    @endif
    <p style="margin:24px 0 0;">
        <a href="{{ route('aluno.justificativas') }}" style="display:inline-block; background:#050505; color:#efefef; text-decoration:none; font-weight:700; padding:12px 20px; border-radius:4px;">Ver minhas justificativas</a>
    </p>
@endsection
