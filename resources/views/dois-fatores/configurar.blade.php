@extends('layouts.theme')

@section('title', 'Segurança da conta - Smart Attendance')
@section('body-class', 'gradient-bg relative min-h-screen flex flex-col')

@section('nav-right')
    <a href="{{ $painel }}" class="pal-nav-btn pal-nav-btn-ghost">⬅ Painel</a>
    <form action="{{ route('logout') }}" method="POST" style="margin:0">
        @csrf
        <button type="submit" class="pal-nav-btn pal-nav-btn-danger">Sair</button>
    </form>
@endsection
@section('hide-nav-defaults', true)

@section('content')
<div class="flex-grow flex flex-col relative overflow-hidden">
    <div class="blob top-[-100px] left-[-100px]"></div>
    <div class="blob-2"></div>

    <main class="max-w-3xl mx-auto w-full p-6 mt-8 relative z-10 flex-grow">
        <div class="mb-8 animate-reveal">
            <p class="pal-eyebrow" style="margin-bottom:0.55rem">Segurança da conta</p>
            <h1 class="pal-title">Verificação em duas etapas</h1>
            <p class="pal-subtitle">Além da senha, o login pede um código que muda a cada 30 segundos no seu celular. Mesmo que alguém descubra sua senha, não consegue entrar.</p>
        </div>

        @if(session('status'))
            <div class="rc-alert {{ $usuario->temDoisFatores() ? 'rc-alert-ok' : 'rc-alert-info' }}" role="status">{{ session('status') }}</div>
        @endif

        @if($codigos)
            <section class="rc-card mb-8" aria-labelledby="titulo-codigos" style="border-color:rgba(234,179,8,0.45)">
                <div class="rc-card-header">
                    <h2 id="titulo-codigos" class="rc-card-title">Guarde seus códigos de recuperação</h2>
                </div>
                <div style="padding:1.25rem 1.5rem">
                    <p class="rc-hint" style="margin:0 0 1rem">Se perder o celular, use um destes códigos no lugar do código do app. Cada um vale uma vez. <strong style="color:var(--pal-white)">Eles não serão mostrados de novo.</strong></p>
                    <ul role="list" id="lista-codigos" class="grid gap-2" style="grid-template-columns:repeat(auto-fill, minmax(150px, 1fr)); font-family:ui-monospace, monospace; font-size:1rem; font-weight:700; color:var(--pal-white)">
                        @foreach($codigos as $codigo)
                            <li style="padding:0.5rem 0.75rem; border:1px dashed color-mix(in srgb, var(--pal-white) 20%, transparent); border-radius:4px; text-align:center">{{ $codigo }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="rc-btn rc-btn-neutro rc-btn-sm mt-4" id="copiar-codigos">Copiar códigos</button>
                </div>
            </section>
        @endif

        @if($usuario->temDoisFatores())
            <section class="rc-card animate-reveal pal-card-delay-1" aria-labelledby="titulo-ativo">
                <div class="rc-card-header">
                    <h2 id="titulo-ativo" class="rc-card-title">Ativada</h2>
                    <span class="rc-chip rc-chip-ok">Protegida desde {{ $usuario->two_factor_confirmed_at->format('d/m/Y') }}</span>
                </div>
                <div style="padding:1.25rem 1.5rem">
                    <p class="rc-hint" style="margin:0 0 1.25rem">Restam {{ $usuario->codigosRecuperacaoRestantes() }} códigos de recuperação.</p>
                    @if($obrigatorio)
                        <p class="rc-hint" style="margin:0">Para o Master, a verificação em duas etapas é obrigatória e não pode ser desligada. Se trocar de celular, peça a outro Master para remover a verificação da sua conta.</p>
                    @else
                        <form method="POST" action="{{ route('dois-fatores.desativar') }}" class="flex flex-wrap gap-3 items-end">
                            @csrf
                            <div class="rc-field" style="flex:1; min-width:220px">
                                <label for="senha" class="login-label">Sua senha, para desativar</label>
                                <input id="senha" name="senha" type="password" class="rc-input" autocomplete="current-password" required>
                                @error('senha')<span class="login-error" style="margin-top:0.4rem">{{ $message }}</span>@enderror
                            </div>
                            <button type="submit" class="rc-btn rc-btn-perigo">Desativar</button>
                        </form>
                    @endif
                </div>
            </section>
        @else
            <section class="rc-card animate-reveal pal-card-delay-1" aria-labelledby="titulo-ativar">
                <div class="rc-card-header"><h2 id="titulo-ativar" class="rc-card-title">Ativar em 3 passos</h2></div>
                <ol style="padding:1.5rem; display:grid; gap:1.75rem; list-style:none; margin:0">
                    <li>
                        <p class="rc-strong" style="color:var(--pal-white); font-weight:800; margin:0 0 0.35rem">1. Instale um app autenticador</p>
                        <p class="rc-hint" style="margin:0">Google Authenticator, Microsoft Authenticator ou Authy, gratuitos na loja do celular.</p>
                    </li>
                    <li>
                        <p class="rc-strong" style="color:var(--pal-white); font-weight:800; margin:0 0 0.75rem">2. Escaneie este QR Code com o app</p>
                        <div class="flex flex-wrap gap-6 items-center">
                            <div id="qr-2fa" data-otpauth="{{ $otpauth }}" role="img" aria-label="QR Code para configurar o app autenticador"
                                style="background:#ffffff; padding:12px; border-radius:6px; width:204px; height:204px"></div>
                            <div class="rc-hint" style="flex:1; min-width:200px">
                                Não consegue escanear? No app, escolha “inserir chave” e digite:
                                <code style="display:block; margin-top:0.5rem; padding:0.6rem; border-radius:4px; background:var(--pal-black); color:var(--pal-white); font-size:0.95rem; letter-spacing:0.08em; word-break:break-all">{{ trim(chunk_split($segredo, 4, ' ')) }}</code>
                            </div>
                        </div>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('dois-fatores.confirmar') }}">
                            @csrf
                            <label for="codigo" class="rc-strong" style="display:block; color:var(--pal-white); font-weight:800; margin:0 0 0.75rem">3. Digite o código de 6 números que aparece no app</label>
                            @error('codigo')<div class="login-error" role="alert" style="margin-bottom:0.75rem">{{ $message }}</div>@enderror
                            @error('senha')<div class="login-error" role="alert" style="margin-bottom:0.75rem">{{ $message }}</div>@enderror
                            <div class="flex flex-wrap gap-3 items-end">
                                <input id="codigo" name="codigo" class="rc-input" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9 ]{6,7}" maxlength="7" required
                                    style="max-width:200px; font-size:1.3rem; letter-spacing:0.3em; text-align:center; font-weight:800" placeholder="000000" aria-label="Código de 6 números do app">
                                <div class="rc-field" style="flex:1; min-width:200px">
                                    <label for="senha-ativar" class="rc-hint">Sua senha atual, para confirmar</label>
                                    <input id="senha-ativar" name="senha" type="password" class="rc-input" autocomplete="current-password" required>
                                </div>
                                <button type="submit" class="rc-btn rc-btn-confirmar">Ativar verificação</button>
                            </div>
                        </form>
                    </li>
                </ol>
            </section>
        @endif
    </main>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/vendor/qrcode.min.js') }}"></script>
<script @nonce>
    (function () {
        var alvo = document.getElementById('qr-2fa');
        if (alvo && window.QRCode) {
            new QRCode(alvo, { text: alvo.dataset.otpauth, width: 180, height: 180, correctLevel: QRCode.CorrectLevel.M });
        }
        var copiar = document.getElementById('copiar-codigos');
        if (copiar && navigator.clipboard) {
            copiar.addEventListener('click', function () {
                var texto = Array.from(document.querySelectorAll('#lista-codigos li')).map(function (li) { return li.textContent.trim(); }).join('\n');
                navigator.clipboard.writeText(texto).then(function () { copiar.textContent = 'Copiado ✓'; });
            });
        }
    })();
</script>
@endpush
