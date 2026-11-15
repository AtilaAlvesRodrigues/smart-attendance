<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'Smart Attendance')</title>
</head>
<body style="margin:0; padding:0; background:#f5f5f7; font-family:Inter, Arial, sans-serif; color:#111111;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f7; padding:32px 16px;">
        <tr><td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px; background:#ffffff; border:1px solid #e0e0e0; border-radius:6px;">
                <tr><td style="background:#050505; padding:20px 28px; border-radius:6px 6px 0 0;">
                    <span style="color:#efefef; font-size:18px; font-weight:900; letter-spacing:-0.5px;">Smart<span style="color:#888888;">Attendance</span></span>
                </td></tr>
                <tr><td style="padding:32px 28px; font-size:15px; line-height:1.6;">
                    @yield('conteudo')
                </td></tr>
                <tr><td style="padding:18px 28px; border-top:1px solid #eeeeee; font-size:12px; color:#666666;">
                    Mensagem automática do Smart Attendance. Não responda este e-mail.
                </td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
