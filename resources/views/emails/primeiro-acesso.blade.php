<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bem-vindo ao Smart Attendance</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Inter', Arial, sans-serif; background: #080812; margin: 0; padding: 0; color: #e0e0e0; }
        .wrapper { max-width: 600px; margin: 0 auto; padding: 40px 16px; }

        /* Card principal */
        .card { background: #12122a; border: 1px solid rgba(108,99,255,0.18); border-radius: 20px; overflow: hidden; }

        /* Faixa de topo */
        .card-top { height: 5px; background: linear-gradient(90deg, #6c63ff, #a855f7, #6c63ff); }

        /* Conteúdo */
        .card-body { padding: 40px 36px; }

        /* Logo */
        .logo { font-size: 20px; font-weight: 900; color: #fff; letter-spacing: -0.5px; margin-bottom: 28px; display: flex; align-items: center; gap: 10px; }
        .logo-icon { width: 34px; height: 34px; background: linear-gradient(135deg, #6c63ff, #a855f7); border-radius: 8px; display: flex; align-items: center; justify-content: center; }
        .logo span { color: #a855f7; }

        /* Título */
        .headline { font-size: 26px; font-weight: 900; color: #fff; margin: 0 0 6px; letter-spacing: -0.5px; }
        .headline-sub { color: #6b6b8a; font-size: 14px; margin: 0 0 28px; }

        /* Divisor */
        .divider { border: none; border-top: 1px solid rgba(255,255,255,0.07); margin: 28px 0; }

        /* Saudação */
        .greeting { font-size: 15px; color: #b0b0cc; margin: 0 0 20px; line-height: 1.7; }
        .greeting strong { color: #fff; }

        /* Info row */
        .info-chip { display: inline-flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 8px 14px; margin-bottom: 20px; font-size: 13px; }
        .info-chip .lbl { color: #6b6b8a; }
        .info-chip .val { color: #c084fc; font-weight: 600; }

        /* Token box */
        .token-wrap { background: rgba(108,99,255,0.08); border: 1px solid rgba(108,99,255,0.28); border-radius: 14px; padding: 22px 24px; margin: 20px 0; }
        .token-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
        .token-label { font-size: 10px; font-weight: 700; color: #6c63ff; text-transform: uppercase; letter-spacing: 1.5px; }
        .token-copy-hint { font-size: 10px; color: #4a4a6a; font-style: italic; }
        .token-value {
            font-family: 'Courier New', Courier, monospace;
            font-size: 15px;
            font-weight: 700;
            color: #e8e6ff;
            letter-spacing: 3px;
            word-break: break-all;
            line-height: 1.8;
            background: rgba(108,99,255,0.12);
            border: 1px dashed rgba(108,99,255,0.35);
            border-radius: 8px;
            padding: 14px 16px;
            display: block;
            user-select: all;
            -webkit-user-select: all;
            cursor: text;
        }
        .token-tip { font-size: 11px; color: #4a4a6a; margin-top: 10px; text-align: center; }

        /* Alerta */
        .alert { display: flex; gap: 12px; background: rgba(251,191,36,0.07); border: 1px solid rgba(251,191,36,0.22); border-radius: 12px; padding: 16px 18px; font-size: 13px; color: #d4a806; margin: 24px 0; line-height: 1.6; }
        .alert-icon { font-size: 18px; flex-shrink: 0; margin-top: 1px; }

        /* Passos */
        .steps { margin: 24px 0; }
        .step { display: flex; gap: 12px; align-items: flex-start; margin-bottom: 12px; font-size: 13px; color: #9090b0; line-height: 1.5; }
        .step-num { min-width: 22px; height: 22px; background: rgba(108,99,255,0.18); border: 1px solid rgba(108,99,255,0.3); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; color: #8b83ff; flex-shrink: 0; }

        /* Botão */
        .btn-wrap { text-align: center; margin: 28px 0 8px; }
        .btn { display: inline-block; background: linear-gradient(135deg, #6c63ff, #a855f7); color: #fff; text-decoration: none; padding: 15px 36px; border-radius: 12px; font-weight: 800; font-size: 15px; letter-spacing: -0.2px; }

        /* Footer */
        .footer { margin-top: 32px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.06); font-size: 11px; color: #3a3a5a; text-align: center; line-height: 1.8; }
        .footer strong { color: #5050a0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="card-top"></div>
            <div class="card-body">

                {{-- Logo --}}
                <div class="logo">
                    <div class="logo-icon">
                        <svg width="18" height="18" fill="none" stroke="#fff" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2
                                     M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2
                                     m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    Smart<span>Attendance</span>
                </div>

                {{-- Título --}}
                <h1 class="headline">Sua conta foi criada! 🎉</h1>
                <p class="headline-sub">Configure seu acesso para começar a usar o sistema.</p>

                <hr class="divider">

                {{-- Saudação --}}
                <p class="greeting">
                    Olá, <strong>{{ $nomeUsuario }}</strong>!<br><br>
                    Sua conta na plataforma <strong>Smart Attendance</strong> foi criada com sucesso pelo administrador.
                    Para ativar seu acesso, utilize o token provisório abaixo como sua senha no primeiro login.
                </p>

                {{-- E-mail --}}
                <div class="info-chip">
                    <span class="lbl">Login:</span>
                    <span class="val">{{ $emailUsuario }}</span>
                </div>

                {{-- Token --}}
                <div class="token-wrap">
                    <div class="token-header">
                        <span class="token-label">🔑 Senha Inicial — Token Provisório</span>
                        <span class="token-copy-hint">Clique para selecionar</span>
                    </div>
                    <span class="token-value">{{ $token }}</span>
                    <p class="token-tip">Selecione o token acima, copie e cole no campo de senha ao fazer login.</p>
                </div>

                {{-- Alerta --}}
                <div class="alert">
                    <span class="alert-icon">⚠️</span>
                    <span><strong>Token de uso único.</strong> Após inserir este token no campo de senha, você será redirecionado para criar sua senha definitiva. O token não poderá ser reutilizado.</span>
                </div>

                {{-- Passo a passo --}}
                <div class="steps">
                    <div class="step">
                        <span class="step-num">1</span>
                        <span>Clique em <strong style="color:#c084fc;">"Acessar e Ativar Conta"</strong> abaixo ou acesse a página de login do seu perfil.</span>
                    </div>
                    <div class="step">
                        <span class="step-num">2</span>
                        <span>No campo de <strong style="color:#c084fc;">senha</strong>, cole o token provisório acima.</span>
                    </div>
                    <div class="step">
                        <span class="step-num">3</span>
                        <span>Crie sua <strong style="color:#c084fc;">senha definitiva</strong> quando solicitado.</span>
                    </div>
                </div>

                {{-- Botão --}}
                <div class="btn-wrap">
                    <a href="{{ $loginUrl }}" class="btn">Acessar e Ativar Conta &rarr;</a>
                </div>

                {{-- Footer --}}
                <div class="footer">
                    <strong>Smart Attendance</strong> &mdash; Sistema Inteligente de Controle de Presença<br>
                    Este e-mail foi gerado automaticamente. Não responda.<br>
                    &copy; {{ date('Y') }} Todos os direitos reservados Smart Attendance.
                </div>

            </div>
        </div>
    </div>
</body>
</html>
