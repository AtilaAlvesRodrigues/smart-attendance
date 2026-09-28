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

## Limite de tentativas

| Rota | Limite |
|---|---|
| Login de aluno e de professor | 10 a cada 3 minutos |
| Criar senha | 10 a cada 3 minutos |
| Esqueci minha senha | 3 a cada 10 minutos |
| Pedido de acesso | 5 a cada 10 minutos |
| Verificação de e-mail do pedido | 20 por minuto |
| Formulário de check-in de evento | 10 por minuto |
| Envio de check-in de evento | 5 por minuto |
| Buscas do Master | 30 por minuto |

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

## Robôs no check-in de eventos

O formulário público de check-in tem um campo oculto, `hp_field`, que pessoas não veem e robôs costumam preencher. Se ele vier preenchido, o envio é recusado.

## QR Code e presença

- Cada chamada tem um código `{matéria}-{timestamp}-{aleatório}`, guardado no cache por 2 horas.
- A confirmação exige código válido, aluno matriculado na matéria e nenhum registro anterior na mesma chamada.
- O código nunca aparece em página pública: a página inicial mostra só o nome e a sala da aula em andamento.

## Exclusão lógica

Alunos, professores, administradores e matérias usam *soft delete*: o registro é marcado como excluído e o histórico de presenças é preservado.

## Configuração de produção

- `APP_DEBUG=false` e `APP_ENV=production`, para erros não mostrarem detalhes internos.
- Cookies de sessão só por HTTPS (`SESSION_SECURE_COOKIE=true`).
- Banco num schema próprio (`laravel`), fora da API REST automática do Supabase, com um usuário exclusivo da aplicação.
- Variáveis sensíveis marcadas como *sensitive* na Vercel. Veja [Deploy](deploy.md).
- A tarefa diária `/cron/manter-banco-ativo` só responde com o `CRON_SECRET` correto; sem ele, devolve 404.

---

## Limitações conhecidas

| Limitação | Impacto | Caminho de solução |
|---|---|---|
| O QR Code vale 2 horas | Uma foto do QR enviada a quem está fora da sala funciona enquanto a chamada estiver aberta | QR que muda a cada 30 segundos, ou confirmação por localização ou rede da instituição |
| Sem Content-Security-Policy | Menos uma barreira contra XSS | Definir uma CSP; hoje há scripts inline e bibliotecas de CDN a considerar |
| `/pdf-teste-vulnerabilidade` aberto a qualquer usuário logado | Um relatório interno de testes pode ser visto por alunos | Restringir ao Master ou remover da aplicação publicada |
| Sem autenticação em dois fatores | Uma senha vazada dá acesso à conta | 2FA para professores e administradores |
| O professor não corrige presenças | Um erro de registro não pode ser desfeito pela interface | Tela de correção, com registro de quem alterou |
