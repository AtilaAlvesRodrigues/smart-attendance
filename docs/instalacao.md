# Instalação

Como rodar o Smart Attendance no seu computador, testar pelo celular e configurar o envio de e-mails.

## Pré-requisitos

| Ferramenta | Versão | Observação |
|---|---|---|
| PHP | 8.2 ou mais nova | Extensões `pdo_pgsql`, `mbstring`, `openssl`, `gd` |
| Composer | 2 | |
| Node.js | 22 | Para compilar o CSS e o JS com o Vite |
| PostgreSQL | 16 ou 17 | |

## Passo a passo

```bash
# 1. Baixar o projeto
git clone https://github.com/AtilaAlvesRodrigues/smart-attendance.git
cd smart-attendance

# 2. Dependências
composer install
npm install
npm run build

# 3. Configuração
cp .env.example .env
php artisan key:generate
```

Crie o banco e ajuste o `.env`:

```bash
createdb smart_attendance
```

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=smart_attendance
DB_USERNAME=postgres
DB_PASSWORD=sua_senha
```

Crie as tabelas com os dados de demonstração e suba o servidor:

```bash
php artisan migrate --seed
php artisan serve
```

Acesse `http://localhost:8000`. Os logins de teste estão no [README](../README.md#experimente).

### Desenvolvimento

```bash
composer dev
```

Sobe o servidor, a fila, os logs (`pail`) e o Vite em modo de observação, que recompila o CSS e o JS a cada alteração.

## Variáveis de ambiente

As principais, com os valores do [`.env.example`](../.env.example):

| Variável | Para que serve |
|---|---|
| `APP_KEY` | Criptografia dos dados e chave dos *blind indexes*. Gerada por `key:generate`; não troque depois de ter dados |
| `APP_URL` | Endereço base; use o IP da máquina para testar pelo celular |
| `APP_TIMEZONE` | Fuso da aplicação, padrão `America/Sao_Paulo`; define a data das chamadas |
| `DB_*` | Conexão com o PostgreSQL |
| `DB_SEARCH_PATH` | Schema do banco, padrão `public`; em produção, `laravel` |
| `DB_SSLMODE` | `prefer` localmente, `require` em produção |
| `SESSION_DRIVER` / `CACHE_STORE` | `database`: sessões e códigos de chamada ficam no banco |
| `MAIL_*` | Envio dos e-mails de primeiro acesso e recuperação de senha |
| `CRON_SECRET` | Só em produção: protege a rota da tarefa diária |

## Testar pelo celular

O QR Code aponta para o endereço que o professor está usando. Se o professor abrir o sistema por `localhost`, o celular não vai conseguir abrir o link.

### Mesma rede Wi-Fi

```bash
# Descubra o IP da máquina (ex.: 192.168.0.43)
ipconfig getifaddr en0      # macOS
ip a                        # Linux
ipconfig                    # Windows

# Suba o servidor aceitando conexões da rede
php artisan serve --host=0.0.0.0 --port=8000
```

No computador do professor, acesse `http://192.168.0.43:8000`. O QR Code passa a usar esse endereço, e celulares na mesma rede conseguem abri-lo.

### Redes diferentes ou 4G (Cloudflare Tunnel)

```bash
# Com o servidor rodando, em outro terminal:
cloudflared tunnel --url http://localhost:8000
```

Use o endereço `https://...trycloudflare.com` que aparecer. Coloque-o em `APP_URL` e rode `php artisan config:clear`. O endereço muda a cada vez que o túnel é reiniciado.

## E-mail (Gmail)

Sem SMTP, deixe `MAIL_MAILER=log`: os e-mails vão para `storage/logs/laravel.log`, e o Master vê na tela o token de primeiro acesso de quem cadastrar.

Para enviar de verdade pelo Gmail:

1. Ative a verificação em duas etapas da conta Google.
2. Crie uma senha de app em https://myaccount.google.com/apppasswords.
3. Configure o `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_SCHEME=smtp
MAIL_USERNAME=seu@gmail.com
MAIL_PASSWORD=senha_de_app_sem_espacos
MAIL_FROM_ADDRESS="seu@gmail.com"
MAIL_FROM_NAME="Smart Attendance"
```

4. Rode `php artisan config:clear`.

> [!IMPORTANT]
> Nunca faça commit do `.env`. Ele já está no `.gitignore`; o repositório só guarda o `.env.example`, com valores de exemplo.

## Problemas comuns

| Sintoma | Causa provável | Solução |
|---|---|---|
| `could not find driver` | Falta a extensão `pdo_pgsql` no PHP | Instale e habilite a extensão |
| `Vite manifest not found` | Assets não compilados | `npm run build` |
| Login com usuário de teste falha | Banco sem seed, ou `APP_KEY` trocada depois do seed | `php artisan migrate:fresh --seed` |
| Celular não abre o link do QR | Professor acessando por `localhost` | Use o IP da máquina ou o túnel |
| Mudança no `.env` não aparece | Configuração em cache | `php artisan config:clear` |
