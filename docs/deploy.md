# Deploy

A demonstração roda na **Vercel**, com o runtime comunitário [`vercel-php`](https://github.com/vercel-community/php), e o banco é um **PostgreSQL no Supabase**. Os dois são gratuitos para o projeto.

Endereço: https://smart-attendance-nine-alpha.vercel.app

## Como a Vercel roda o Laravel

| Arquivo | Função |
|---|---|
| [`vercel.json`](../vercel.json) | Build do Vite, arquivos estáticos de `public/`, demais rotas para o PHP e a tarefa diária |
| [`api/index.php`](../api/index.php) | Entrada da função serverless; carrega o `public/index.php` do Laravel |
| [`.vercelignore`](../.vercelignore) | Impede o upload de `.env`, `vendor`, `node_modules` e caches locais |

Ordem das rotas no `vercel.json`:

1. `/` vai para o PHP (sem isso, a Vercel serviria o `public/index.php` como arquivo, expondo o código).
2. `/index.php` e `/.htaccess` devolvem 404.
3. Arquivos que existem em `public/` (CSS, JS, imagens, build do Vite) são servidos direto.
4. Todo o resto vai para o PHP.

## Variáveis de ambiente (Production)

| Variável | Valor | Por quê |
|---|---|---|
| `TRUSTED_HOSTS` | domínios extras, separados por vírgula (opcional) | **Obrigatória se o site tiver domínio próprio** (ex.: `chamada.escola.edu.br`). Sem ela, qualquer `Host` fora da lista recebe 400. Os domínios `*.vercel.app` do projeto já estão incluídos |
| `APP_KEY` | chave gerada por `php artisan key:generate --show` | Criptografia e *blind indexes*. Marcar como *sensitive* |
| `APP_ENV` / `APP_DEBUG` | `production` / `false` | Erros sem detalhes internos |
| `APP_LOCALE` | `pt_BR` | |
| `DB_CONNECTION` | `pgsql` | |
| `DB_HOST` / `DB_PORT` | `aws-0-<região>.pooler.supabase.com` / `6543` | Pooler do Supabase em **modo transação** (a Vercel não tem IPv6, e o endereço direto do banco é só IPv6). No modo sessão (porta `5432`), cada requisição prende uma das 15 vagas do plano gratuito do começo ao fim, e o 16º acesso simultâneo recebia erro 500 (`EMAXCONNSESSION`, medido em 22/11/2026: 15 de 40 requisições simultâneas passaram) |
| `DB_EMULATE_PREPARES` | `true` | Obrigatório com a porta `6543`: no modo transação a conexão do servidor muda entre transações, então os *prepared statements* são emulados no PHP. O `search_path` vem do papel `smart_app` (`alter role ... set search_path = laravel`), que vale em qualquer conexão |
| `DB_DATABASE` | `postgres` | |
| `DB_USERNAME` | `<usuário>.<id-do-projeto>` | Usuário exclusivo da aplicação, no formato do pooler |
| `DB_PASSWORD` | senha desse usuário | *sensitive* |
| `DB_SEARCH_PATH` | `laravel` | Tabelas fora do schema `public`, exposto pela API REST do Supabase |
| `DB_SSLMODE` | `require` | |
| `SESSION_DRIVER` / `CACHE_STORE` | `database` | Cada requisição pode cair numa instância diferente |
| `SESSION_SECURE_COOKIE` | `true` | Cookie só por HTTPS |
| `QUEUE_CONNECTION` | `sync` | Não há processo de fila na Vercel |
| `LOG_CHANNEL` | `stderr` | Os logs aparecem no painel da Vercel |
| `MAIL_MAILER` | `log`, ou `smtp` com as variáveis `MAIL_*` | Com `log`, o Master recebe o token na tela |
| `CRON_SECRET` | texto aleatório longo | Protege `/cron/manter-banco-ativo` |
| `TWO_FACTOR_MASTER_OBRIGATORIO` | `true` | Master só entra no painel com verificação em duas etapas |
| `JUSTIFICATIVAS_DISK` | `banco` | Atestados na tabela `justificativa_anexos` (sem serviço externo; o disco da Vercel é somente leitura). Sem essa variável, o envio de anexo fica desligado e a justificativa segue só com texto |
| `SESSION_ENCRYPT` | `true` | Criptografa o conteúdo da sessão no banco (inclui o segredo do 2FA durante a configuração) |

Alternativa para muitos arquivos: `JUSTIFICATIVAS_DISK=s3` com o Supabase Storage (bucket **privado** `justificativas` e as variáveis `SUPABASE_S3_ENDPOINT`, `SUPABASE_S3_REGION`, `SUPABASE_S3_BUCKET`, `SUPABASE_S3_KEY` e `SUPABASE_S3_SECRET`, as duas últimas *sensitive*, geradas em *Project Settings → Storage → S3 Connection*).
| `APP_CONFIG_CACHE`, `APP_EVENTS_CACHE`, `APP_PACKAGES_CACHE`, `APP_ROUTES_CACHE`, `APP_SERVICES_CACHE` | `/tmp/config.php`, `/tmp/events.php`, `/tmp/packages.php`, `/tmp/routes.php`, `/tmp/services.php` | O sistema de arquivos da função só permite escrita em `/tmp` |
| `VIEW_COMPILED_PATH` | `/tmp` | Idem, para as views compiladas |

## Banco no Supabase

Configuração feita uma vez, no editor SQL do Supabase:

```sql
create role smart_app login password '<senha forte>';
grant smart_app to postgres;
create schema laravel authorization smart_app;
alter role smart_app set search_path = laravel;
revoke all on schema laravel from public;
```

As migrations rodam a partir de uma máquina local, apontando para o banco de produção **com a mesma `APP_KEY` da Vercel**:

```bash
APP_ENV=production APP_KEY=... DB_HOST=... DB_USERNAME=... DB_PASSWORD=... \
DB_DATABASE=postgres DB_SEARCH_PATH=laravel DB_SSLMODE=require \
php artisan migrate --force
```

Para recriar os dados de demonstração, use `migrate:fresh --seed --force`. Isso **apaga tudo** no schema `laravel`.

> [!WARNING]
> A `APP_KEY` também é a chave dos *blind indexes*. Se o seed rodar com uma chave diferente da configurada na Vercel, os logins param de funcionar. As variáveis *sensitive* não podem ser lidas de volta pela Vercel (`vercel env pull` devolve `[Sensitive]`); guarde a chave num gerenciador de senhas ao criá-la.

## Tarefas diárias

O `vercel.json` agenda duas chamadas por dia. A Vercel envia `Authorization: Bearer <CRON_SECRET>`; sem esse cabeçalho, as rotas devolvem 404.

| Rota | Horário | O que faz |
|---|---|---|
| `/cron/manter-banco-ativo` | 12:00 UTC (9:00 em Brasília) | Consulta simples: o Supabase gratuito pausa o banco depois de cerca de 7 dias sem uso |
| `/cron/avisos-frequencia` | 22:00 UTC (19:00 em Brasília) | Avisa por e-mail os alunos em *Atenção* por faltas (no máximo 1 aviso por matéria a cada 7 dias); devolve `{"avisos": N}` |

Localmente, o aviso roda com `php artisan frequencia:avisar`.

## Deploy automático pelo GitHub

Hoje o projeto `smart-attendance` da Vercel (conta `jooaoleonardi's projects`) está ligado ao **fork** `jooaoleonardi/smart-attendance`, não ao repositório oficial. Um merge na `main` oficial **não** publica sozinho: é preciso sincronizar o fork, e a Vercel publica a `main` dele em produção.

```bash
gh repo sync jooaoleonardi/smart-attendance --branch main   # dispara o deploy de produção
```

> **Situação em novembro de 2026:** `vercel git connect` no repositório oficial respondeu "Failed to connect", porque a conta `jooaoleonardi` da Vercel não tem acesso a `AtilaAlvesRodrigues/smart-attendance`. O caminho pelo fork foi testado em 22/11/2026: a sincronização disparou o deploy de produção, que ficou `Ready` em menos de 1 minuto. A alternativa continua sendo `vercel deploy --prod` pela CLI. Para ligar o repositório oficial, siga os passos abaixo.

1. **O dono do repositório** (`AtilaAlvesRodrigues`) instala o [app da Vercel no GitHub](https://github.com/apps/vercel) com acesso a `smart-attendance`. Um *fork* só aparece se o app tiver acesso a ele.
2. Na Vercel: *Project → Settings → Git → Connect Git Repository* e escolha `AtilaAlvesRodrigues/smart-attendance`. Pela CLI: `vercel git connect https://github.com/AtilaAlvesRodrigues/smart-attendance`.
3. Em *Settings → Git*, confirme **Production Branch = `main`**.
4. Em *Settings → Environment Variables*, confira que as variáveis da tabela acima existem em **Preview** também (use um banco separado, nunca o de produção).
5. Em *Settings → Git*, mantenha **Git Fork Protection** ligada: Pull Requests de forks não rodam sem aprovação de um membro.
6. Abra um PR de teste, veja o comentário da Vercel com a URL e confira os cabeçalhos: `curl -sI <url-de-preview> | grep -i content-security`.

O pipeline do GitHub Actions continua sendo a barreira de qualidade; a Vercel só publica. Para a Vercel esperar o CI, ative em *Settings → Git* a opção de exigir *checks* ou proteja a `main` no GitHub exigindo o status do CI antes do merge.

## Previews desligados nas branches

O `vercel.json` desliga o deploy automático das branches com os prefixos do projeto (`feat/`, `fix/`, `docs/`, `melhoria/`, `melhorias/`, `chore/`, `claude/`). Motivo: as variáveis de ambiente existem só em **Production**; um preview de branch subiria sem `APP_KEY` nem banco e daria erro 500, e o PR ficaria com um check vermelho da Vercel. A `main` não está em nenhum padrão e continua publicando em produção. Para voltar a ter previews: cadastre as variáveis em *Preview* (com um banco de teste, nunca o de produção) e remova o bloco `git` do `vercel.json`. Esse bloco só vale para deploys vindos do Git; `vercel deploy` pela CLI continua funcionando.

## Publicar uma nova versão

Enquanto o repositório não estiver conectado à Vercel, a publicação é feita pela CLI, de dentro da pasta do projeto:

```bash
vercel deploy --prod
```

Antes de publicar:

1. `composer test` passando.
2. Se houver migration nova, rode-a no banco de produção **antes** do deploy.
3. Depois de publicar, teste o login dos três perfis e o ciclo da chamada.

Para conectar o repositório e publicar a cada push na `main`, o dono do repositório precisa instalar o [app da Vercel no GitHub](https://github.com/apps/vercel) com acesso a este repositório; depois, `vercel git connect` liga o projeto.
