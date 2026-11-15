@extends('emails.layout')

@section('titulo', 'Teste de envio')

@section('conteudo')
    <h1 style="margin:0 0 20px; font-size:24px; font-weight:900; letter-spacing:-0.5px;">O envio de e-mails está funcionando</h1>
    <p style="margin:0 0 16px;">Olá, {{ $nome }}.</p>
    <p style="margin:0 0 16px;">Se você recebeu esta mensagem, o Smart Attendance está conectado ao servidor de e-mail da instituição. Os e-mails de primeiro acesso, de recuperação de senha e de decisão das justificativas vão sair por este servidor.</p>
    <p style="margin:0; color:#666666; font-size:13px;">Enviado em {{ now()->format('d/m/Y \à\s H:i') }}.</p>
@endsection
