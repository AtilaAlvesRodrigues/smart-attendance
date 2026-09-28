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
| `APP_KEY` | chave gerada por `php artisan key:generate --show` | Criptografia e *blind indexes*. Marcar como *sensitive* |
| `APP_ENV` / `APP_DEBUG` | `production` / `false` | Erros sem detalhes internos |
| `APP_LOCALE` | `pt_BR` | |
| `DB_CONNECTION` | `pgsql` | |
| `DB_HOST` / `DB_PORT` | `aws-0-<região>.pooler.supabase.com` / `5432` | *Session pooler* do Supabase: a Vercel não tem IPv6, e o endereço direto do banco é só IPv6 |
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

## Tarefa diária

O Supabase gratuito pausa o banco depois de cerca de 7 dias sem uso. O `vercel.json` agenda uma chamada diária, às 12:00 UTC (9:00 em Brasília), para `/cron/manter-banco-ativo`, que faz uma consulta simples. A Vercel envia `Authorization: Bearer <CRON_SECRET>`; sem esse cabeçalho, a rota devolve 404.

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
