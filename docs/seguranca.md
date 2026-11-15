# Segurança

Cada proteção abaixo tem teste automatizado em [`tests/Feature`](../tests/Feature). No fim do documento estão as limitações conhecidas, que ainda não foram resolvidas.

| Camada | Onde está | Teste |
|---|---|---|
| [Dados pessoais criptografados](#dados-pessoais-criptografados) | casts `encrypted` nos models, [`HasBlindIndex`](../app/Traits/HasBlindIndex.php) | `EncryptionTest`, `BlindIndexTest` |
| [Perfis isolados](#perfis-isolados) | guards em `config/auth.php`, [`CheckRole`](../app/Http/Middleware/CheckRole.php) | `IsolamentoPaineisTest`, `RoleMiddlewareTest`, `UnauthorizedAccessTest` |
| [Senhas e primeiro acesso](#senhas-e-primeiro-acesso) | `CriarSenhaController`, [`PrimeiroAcessoMiddleware`](../app/Http/Middleware/PrimeiroAcessoMiddleware.php) | `CriarSenhaTest`, `PrimeiroAcessoCriarSenhaTest`, `PasswordRecoveryTest` |
| [Limite de tentativas](#limite-de-tentativas) | middleware `throttle` em [`routes/web.php`](../routes/web.php) | `RateLimitTest` |
| [CSRF](#csrf) | middleware padrão do Laravel | `CsrfTest` |
| [XSS](#xss) | Blade `{{ }}`, `textContent` no JavaScript | `XssTest` |
| [SQL injection](#sql-injection) | Eloquent com *bindings*, lista de colunas permitidas | `SqlInjectionTest`, `ColumnInjectionTest` |
| [Cabeçalhos HTTP](#cabeçalhos-http) | [`SecurityHeaders`](../app/Http/Middleware/SecurityHeaders.php) | `SecurityHeadersTest` |
| [Robôs no check-in de eventos](#robôs-no-check-in-de-eventos) | campo `hp_field` em `EventoController` | `HoneypotTest` |
| [QR Code e presença](#qr-code-e-presença) | `PresencaController`, `LoginRouterController` | `QrCodePresencaTest`, `PresencaTest` |
| [Exclusão lógica](#exclusão-lógica) | `SoftDeletes` nos models | `SoftDeleteTest` |

---

## Dados pessoais criptografados

CPF, RA e e-mail de alunos e professores, e nome e e-mail dos administradores e dos pedidos de acesso, são gravados criptografados com AES-256 (cast `encrypted` do Laravel, com a `APP_KEY`). Quem tiver acesso só ao banco vê textos cifrados.

Como um texto cifrado muda a cada gravação, não é possível buscar por ele. Para buscar, o trait `HasBlindIndex` grava em colunas `*_search` um *blind index*: o SHA-256 do valor normalizado (minúsculas, sem espaços nas pontas) junto com a `APP_KEY`.

```php
// Buscar um aluno pelo e-mail
AlunoModel::where('email_search', AlunoModel::generateBlindIndex($email))->first();

// Nunca: o valor gravado está cifrado e não vai bater
AlunoModel::where('email', $email)->first();
```

- A unicidade de e-mail, CPF e RA é garantida pelos índices únicos nas colunas `*_search`.
- Sem a `APP_KEY`, os hashes não permitem descobrir os dados por tentativa e erro.
- Trocar a `APP_KEY` invalida todos os índices: é preciso recriar os dados ou rodar `php artisan secure:data`.
- O nome de alunos e professores fica em texto plano, por decisão de projeto, para exibição e ordenação.

## Perfis isolados

Três *guards* independentes (`alunos`, `professores`, `masters`), cada um com tabela e sessão próprias. As rotas de cada área exigem o guard certo e o middleware `role:<papel>`. Testes cobrem, por exemplo, um aluno tentando abrir o painel do Master ou a lista de presentes de uma chamada.

As respostas em JSON só devolvem o necessário: a lista de presentes do professor traz apenas nome e RA, e só das chamadas desse professor.

## Senhas e primeiro acesso

- Senhas guardadas com bcrypt (cast `hashed`).
- Contas criadas pelo Master ou por pedido aprovado recebem um **token provisório** aleatório, gravado criptografado. O token só serve para entrar e criar a senha definitiva, com no mínimo 8 caracteres; depois é apagado.
- Mensagens de erro de login são genéricas e não dizem se o usuário existe.
- **Esqueci minha senha** responde sempre a mesma mensagem, inclusive quando o envio de e-mail falha, para não revelar quem tem conta.

## Verificação em duas etapas (2FA)

Código TOTP de 6 números (RFC 6238) gerado por app autenticador, com a biblioteca `pragmarx/google2fa`.

- **Obrigatória para o Master** (`TWO_FACTOR_MASTER_OBRIGATORIO=true`, padrão): o middleware `exigir-2fa` leva à configuração antes de abrir o painel. Opcional para professor e aluno.
- Depois da senha correta, **nada é autenticado** até o código: só o guard, o id e o destino ficam na sessão por 5 minutos (`App\Support\LoginPendente`). O cookie de "permanecer conectado" só é emitido depois do segundo fator.
- O fluxo de token (primeiro acesso / esqueci a senha) também passa pelo desafio: redefinir a senha pelo e-mail não substitui o segundo fator.
- Cada código vale **uma vez**: o timestep do último código aceito fica gravado, e a verificação relê o usuário com lock da linha, então duas requisições simultâneas com o mesmo código não passam as duas.
- Segredo e códigos de recuperação (8, uso único, guardados como hash SHA-256) ficam criptografados no banco e fora de qualquer serialização.
- O rótulo do QR usa o nome, nunca o e-mail.
- Ativar e desativar exigem a senha atual (uma sessão roubada não consegue prender o dono fora da conta); com o 2FA ativo, não é possível trocar o segredo sem desativar antes.
- Limites: 5 tentativas por minuto por login pendente no desafio (teto de 300 por IP).

## Limite de tentativas

Os limites são contados **por pessoa** (o usuário ou o e-mail informado, junto com o IP), com um teto bem mais alto por IP. Numa instituição, todos os celulares do Wi-Fi saem pelo mesmo IP: limitar só por IP barraria a turma inteira depois das primeiras pessoas. Os limites ficam em [`AppServiceProvider`](../app/Providers/AppServiceProvider.php).

| Rota | Por pessoa | Por IP |
|---|---|---|
| Login de aluno e de professor | 10 a cada 3 minutos, por usuário | 300 por minuto |
| Criar senha | 10 a cada 3 minutos, por sessão | 300 por minuto |
| Esqueci minha senha | 3 a cada 10 minutos, por e-mail | 100 a cada 10 minutos |
| Pedido de acesso | 5 a cada 10 minutos, por e-mail | 100 a cada 10 minutos |
| Check-in de evento | 5 por minuto, por e-mail no evento | 300 por minuto |
| Verificação de e-mail do pedido | | 20 por minuto |
| Buscas do Master | 30 por minuto, por administrador | |

O usuário é comparado sem diferenciar maiúsculas e sem espaços nas pontas, então `Aluno@Site.com ` conta como `aluno@site.com`.

## CSRF

Todos os formulários têm `@csrf`, e as chamadas AJAX enviam o cabeçalho `X-CSRF-TOKEN` lido da meta tag do layout. O Laravel desliga essa verificação durante os testes; por isso o `CsrfTest` reativa o middleware para provar que requisições sem token recebem 419.

## XSS

- Views usam `{{ }}`, que escapa HTML. Não há `{!! !!}` com dados de usuário.
- Onde o JavaScript insere dados vindos do banco, como na lista de presentes da chamada ou no token de primeiro acesso, o texto entra por `textContent`, nunca por `innerHTML`.

## SQL injection

- Consultas pelo Eloquent/Query Builder, sempre com *bindings*.
- O salvamento de notas aceita só as colunas `prova1`, `trabalho1`, `trabalho2` e `prova2`, validadas contra uma lista permitida, e valores de 0 a 10.

## Cabeçalhos HTTP

Enviados em todas as respostas web:

| Cabeçalho | Valor |
|---|---|
| `X-Frame-Options` | `DENY` (impede abrir o sistema dentro de outro site) |
| `X-Content-Type-Options` | `nosniff` |
| `X-XSS-Protection` | `1; mode=block` |
| `Referrer-Policy` | `strict-origin-when-cross-origin` |
| `Permissions-Policy` | `camera=(), microphone=(), geolocation=()` |
| `Content-Security-Policy` | Ver [abaixo](#content-security-policy) |

### Content-Security-Policy

Cada resposta leva uma política com um *nonce* novo (gerado em [`SecurityHeaders`](../app/Http/Middleware/SecurityHeaders.php)). Todo `<script>` e `<style>` em bloco nas views usa a diretiva `@nonce`; um bloco injetado por um atacante não tem o nonce e o navegador não o executa.

| Diretiva | Valor |
|---|---|
| `script-src` | `'self'` e o nonce. Nenhuma CDN: `qrcodejs` e `jsPDF` ficam em `public/js/vendor/` ([hashes](../public/js/vendor/LEIA-ME.md)) |
| `style-src` | `'self'`, o nonce e `https://fonts.googleapis.com` |
| `script-src-attr`, `style-src-attr` | `'unsafe-inline'`, **temporário**: ainda há atributos `onclick=` e `style=` nas views ([SEC-08](falhas-de-seguranca.md#sec-08)) |
| `object-src` | `'none'` |
| `base-uri`, `form-action` | `'self'` |
| `frame-ancestors` | `'none'` |
| `default-src`, `connect-src` | `'self'` |
| `upgrade-insecure-requests` | só em requisições HTTPS |

Escreveu um `<script>` ou `<style>` novo numa view? Use `<script @nonce>` / `<style @nonce>`; sem isso o navegador bloqueia o bloco. O `SecurityHeadersTest` falha se alguma página tiver bloco sem nonce.

## Host confiável

O cabeçalho `Host` (e `X-Forwarded-Host`) só é aceito se o domínio estiver na lista de [`HostsConfiaveis`](../app/Support/HostsConfiaveis.php): domínios do projeto na Vercel, `APP_URL`, os endereços de cada deploy (`VERCEL_URL`) e os da variável `TRUSTED_HOSTS`. Qualquer outro recebe 400. Isso impede que o link de um e-mail de recuperação de senha seja montado com o domínio de um atacante. Não vale em `APP_ENV=local` nem nos testes automáticos.

## Robôs no check-in de eventos

O formulário público de check-in tem um campo oculto, `hp_field`, que pessoas não veem e robôs costumam preencher. Se ele vier preenchido, o envio é recusado.

## QR Code e presença

- Cada chamada tem um código `{matéria}-{timestamp}-{aleatório}`, guardado no cache por 2 horas.
- O QR Code leva também um **token que muda a cada 30 segundos** (`?t={janela}.{assinatura}`): HMAC-SHA256 do código da aula e da janela, com a `APP_KEY` ([`QrToken`](../app/Support/QrToken.php)). O servidor aceita a janela atual e a anterior, então uma foto do QR Code deixa de valer em no máximo 60 segundos. A tela do professor busca o token novo em `/professor/presenca/token/{código}`, só para o dono da chamada.
- A confirmação exige código válido, token válido, aluno matriculado na matéria e nenhum registro anterior na mesma chamada.
- O código nunca aparece em página pública: a página inicial mostra só o nome e a sala da aula em andamento.

## Justificativas, correções e exportação

- Professores só veem, decidem e baixam anexos de justificativas das **próprias matérias**; o aluno só baixa o próprio anexo (403 nos outros casos).
- Atestados: `pdf`, `jpg`, `jpeg` ou `png` (extensão e tipo MIME), até 5 MB, gravados com nome aleatório no disco privado `justificativas`; o download passa por rota autenticada com `X-Content-Type-Options: nosniff`.
- Toda correção de presença exige motivo e fica em `presenca_ajustes`.
- Planilhas: só nome e RA (nunca CPF ou e-mail); todo texto é gravado como texto, e valores que começam com `=`, `+`, `-` ou `@` recebem um apóstrofo, para nenhum nome virar fórmula no Excel. O resumo do professor se limita às matérias que ele leciona hoje, e um filtro de matéria alheia recebe 403.
- E-mail de teste: a mensagem de erro do servidor SMTP é exibida sem usuário, senha nem credenciais embutidas em URL (`MAIL_URL`). Falhas de envio vão para o log só com a classe da exceção, sem o texto do servidor (que pode conter endereços de e-mail).

## Exclusão lógica

Alunos, professores, administradores e matérias usam *soft delete*: o registro é marcado como excluído e o histórico de presenças é preservado.

## Configuração de produção

- `APP_DEBUG=false` e `APP_ENV=production`, para erros não mostrarem detalhes internos.
- Cookies de sessão só por HTTPS (`SESSION_SECURE_COOKIE=true`).
- Banco num schema próprio (`laravel`), fora da API REST automática do Supabase, com um usuário exclusivo da aplicação.
- Variáveis sensíveis marcadas como *sensitive* na Vercel. Veja [Deploy](deploy.md).
- A tarefa diária `/cron/manter-banco-ativo` só responde com o `CRON_SECRET` correto; sem ele, devolve 404.
- O relatório interno de testes de segurança fica em `/dashboard/master/relatorio-seguranca`, visível só para o Master.

---

## Limitações conhecidas

A lista completa de falhas abertas, com gravidade e correção, está em [Falhas de segurança conhecidas](falhas-de-seguranca.md).

| Limitação | Impacto | Caminho de solução |
|---|---|---|
| O link do QR pode ser repassado ao vivo | Um aluno presente manda o link do momento a um colega ausente, que confirma em até 60 s ([SEC-05](falhas-de-seguranca.md#sec-05)) | Confirmação por localização ou rede da instituição |
| A CSP ainda libera `onclick=` e `style=` inline | Um HTML injetado ainda poderia executar JavaScript por atributo de evento ([SEC-08](falhas-de-seguranca.md#sec-08)) | Migrar os atributos para scripts e classes e remover as exceções `*-attr` |
| Sem autenticação em dois fatores | Uma senha vazada dá acesso à conta | 2FA para professores e administradores |
| O professor não corrige presenças | Um erro de registro não pode ser desfeito pela interface | Tela de correção, com registro de quem alterou |
