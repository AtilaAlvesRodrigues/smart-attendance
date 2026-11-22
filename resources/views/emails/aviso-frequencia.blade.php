@extends('emails.layout')

@php $s = $situacao; @endphp

@section('titulo', 'Atenção às faltas')

@section('conteudo')
    <p style="margin:0 0 6px; font-size:12px; font-weight:700; letter-spacing:2px; text-transform:uppercase; color:#666666;">{{ $materia->nome }}</p>
    <h1 style="margin:0 0 20px; font-size:24px; font-weight:900; letter-spacing:-0.5px;">Sua frequência precisa de atenção</h1>
    <p style="margin:0 0 16px;">Olá, {{ $aluno->nome }}.</p>
    <p style="margin:0 0 16px; padding:12px 16px; background:#fff7e6; border-left:3px solid #eab308;">
        <strong>{{ $s->explicacao() }}</strong>
    </p>
    <table role="presentation" cellpadding="0" cellspacing="0" style="width:100%; margin:0 0 16px; font-size:14px; border-collapse:collapse;">
        <tr><td style="padding:6px 0; color:#666666;">Faltas</td><td style="padding:6px 0; text-align:right;"><strong>{{ $s->faltas }} de {{ $s->limiteFaltas }}</strong> permitidas</td></tr>
        <tr><td style="padding:6px 0; color:#666666; border-top:1px solid #eeeeee;">Frequência nas aulas dadas</td><td style="padding:6px 0; text-align:right; border-top:1px solid #eeeeee;"><strong>{{ $s->frequencia }}%</strong> (mínimo de 75%)</td></tr>
    </table>
    <p style="margin:0 0 16px;">Se alguma falta teve um motivo justo, você pode enviar uma justificativa com atestado pelo sistema.</p>
    <p style="margin:24px 0 0;">
        <a href="{{ route('dashboard.aluno') }}" style="display:inline-block; background:#050505; color:#efefef; text-decoration:none; font-weight:700; padding:12px 20px; border-radius:4px;">Ver minha frequência</a>
    </p>
    <p style="margin:20px 0 0; font-size:12px; color:#666666;">Você recebe no máximo um aviso por semana para cada matéria.</p>
@endsection
