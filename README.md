<div align="center">

<img src="https://img.shields.io/badge/PHP-8.2+-7A86C8?style=for-the-badge&logo=php&logoColor=white"/>
<img src="https://img.shields.io/badge/Laravel-12+-FF2D20?style=for-the-badge&logo=laravel&logoColor=white"/>
<img src="https://img.shields.io/badge/PostgreSQL-17+-336791?style=for-the-badge&logo=postgresql&logoColor=white"/>
<img src="https://img.shields.io/badge/Status-Em%20Desenvolvimento-F59E0B?style=for-the-badge"/>

# 📋 Smart Attendance

> Sistema web de controle de presença inteligente — rápido, preciso e seguro.
> Desenvolvido como **Projeto Integrador** com PHP/Laravel.

[📖 Documentação](#-documentação) •
[⚙️ Instalação](#%EF%B8%8F-instalação) •
[📱 Teste pelo Celular](#-teste-pelo-celular) •
[🔐 Segurança](#-segurança) •
[📁 Estrutura](#-estrutura) •
[🗄️ Banco de Dados](#%EF%B8%8F-banco-de-dados)

</div>

---

## 📌 Sobre o Projeto

O **Smart Attendance** substitui listas de chamada manuais por um processo digital ágil e seguro. Desenvolvido com foco em boas práticas de segurança — criptografia AES-256, autenticação multi-guard e proteção contra os principais vetores de ataque.

---

## 👤 Perfis de Usuário

| Perfil | Acesso |
|---|---|
| 🎓 **Aluno** | Visualiza presenças e notas |
| 👨‍🏫 **Professor** | Gerencia chamadas, notas e eventos |
| 🛠️ **Master** | Painel administrativo completo |

---

## 🔐 Segurança

| Camada | Implementação |
|---|---|
| 🔒 Criptografia | AES-256 em campos PII (email, CPF, RA, remember_token) |
| 🔍 Blind Index | SHA-256 nos campos cifrados para buscas seguras |
| 👥 Autenticação | Multi-Guard independente por perfil (alunos / professores / masters) |
| 🚦 Rate Limiting | Anti-força bruta por IP em login e envio de e-mail |
| 🛡️ Headers HTTP | CSP, X-Frame-Options, Permissions-Policy |
| 🔑 CSRF | Token em todos os formulários e requisições AJAX |
| 🤖 Honeypot | Anti-bot no check-in público de eventos |
| 📋 Logging | Registro de falhas de autenticação e erros de envio de e-mail |

> 📄 [Ver relatório completo de segurança](docs/Relatorio_Seguranca_SmartAttendance.docx)

---

## 📊 Status do Projeto

| Módulo | Status |
|---|---|
| Autenticação Multi-Guard | ✅ Concluído |
| Controle de Presença (QR Code) | ✅ Concluído |
| Gerenciamento de Notas | ✅ Concluído |
| Painel Master | ✅ Concluído |
| Cadastro Direto (Master) | ✅ Concluído |
| E-mail de Primeiro Acesso | ✅ Concluído |
| Eventos / Palestras com Check-in | ✅ Concluído |
| Testes Automatizados | 📋 Pendente |
| Deploy | 📋 Pendente |

---

## 🛠️ Stack

<div align="center">

![PHP](https://img.shields.io/badge/PHP-7A86C8?style=flat-square&logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=flat-square&logo=laravel&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-336791?style=flat-square&logo=postgresql&logoColor=white)
![Blade](https://img.shields.io/badge/Blade-FF2D20?style=flat-square&logo=laravel&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind-38BDF8?style=flat-square&logo=tailwindcss&logoColor=white)

</div>

---

## ⚙️ Instalação

```bash
# 1. Clonar o repositório e entrar no branch PHP
git clone https://github.com/AtilaAlvesRodrigues/smart-attendance.git
cd smart-attendance
git checkout PHP

# 2. Instalar dependências (PHP e Node)
composer install
npm install && npm run build

# 3. Configurar o ambiente
cp .env.example .env
php artisan key:generate

# 4. Configurar o Banco de Dados no .env
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=smart_attendance
# DB_USERNAME=postgres
# DB_PASSWORD=sua_senha_aqui

# 5. Configurar e-mail SMTP no .env (ver seção E-mails abaixo)

# 6. Rodar migrações e popular o banco
php artisan migrate --seed

# 7. Iniciar o servidor (apenas PC local)
php artisan serve
```

Acesse: `http://localhost:8000`

> Para testar pelo celular, veja a seção **[Teste pelo Celular](#-teste-pelo-celular)** abaixo.

---

## 📱 Teste pelo Celular

O sistema precisa ser acessível pelo celular para que o QR Code de presença e o check-in de eventos funcionem. Há duas formas:

### Opção 1 — Rede Local (mesma WiFi)

Todos os dispositivos (PC do professor + celulares dos alunos) precisam estar **na mesma rede WiFi**.

```bash
# 1. Descubra o IP local da sua máquina
ipconfig          # Windows → procure "IPv4 Address"
ip a              # Linux/Mac → procure o IP da interface (ex: 192.168.0.43)

# 2. Suba o servidor ouvindo em todas as interfaces
php artisan serve --host=0.0.0.0 --port=8000

# 3. Acesse no navegador pelo IP local
http://192.168.0.43:8000

# 4. O QR Code gerado vai codificar automaticamente esse IP
#    → Celulares na mesma rede WiFi conseguem escanear
```

> **Importante:** O professor deve sempre acessar o sistema pelo IP local (não por `localhost`), para que a URL gerada no QR seja acessível pelos alunos.

### Opção 2 — Cloudflare Tunnel (redes diferentes / 4G)

Use quando professor e alunos **não estão na mesma rede** (ex: alunos com dados móveis).

```bash
# 1. Instalar o cloudflared (Windows)
# Baixe em: https://developers.cloudflare.com/cloudflare-one/connections/connect-networks/downloads/

# 2. Com o servidor já rodando (php artisan serve), execute em outro terminal:
cloudflared tunnel --url http://localhost:8000

# 3. Copie o URL público gerado (ex: https://xyz-example.trycloudflare.com)

# 4. Atualize o APP_URL no .env:
APP_URL=https://xyz-example.trycloudflare.com

# 5. Limpe o cache de configuração e reinicie o servidor:
php artisan config:clear
# Reinicie php artisan serve

# 6. Acesse o sistema pelo URL do cloudflare — qualquer celular (inclusive 4G) consegue escanear
```

> **Atenção:** O URL do Cloudflare Tunnel muda a cada reinicialização. Sempre atualize `APP_URL` no `.env` e rode `php artisan config:clear` ao iniciar uma nova sessão.

### Fluxo de Teste Completo (QR de Presença)

```
1. Logar como Professor  →  http://IP:8000/professor/login
2. Acessar Presença      →  Sidebar → Presença → selecionar matéria → Gerar QR
3. Celular do aluno escaneia o QR
4. Aluno faz login (ou usa token de primeiro acesso)
5. Presença registrada   →  aparece na lista do professor em tempo real
```

### Fluxo de Teste Completo (Check-in de Palestra)

```
1. Logar como Professor  →  Sidebar → Eventos → Gestão de Palestra
2. O QR Code e o link de acesso são exibidos automaticamente
3. Celular do participante escaneia o QR (ou acessa o link)
4. Preenche nome e e-mail → clica "Confirmar Presença"
5. Lista atualiza no painel do professor a cada 30 segundos
6. Professor clica "Encerrar Palestra" para fechar o acesso e gerar PDF
```

---

## 💾 Usuários de Teste

| Perfil | Email | Senha |
| --- | --- | --- |
| **Admin Master** | `master@admin.com` | `senha123` |
| **Professor** | `professor@teste.com` | `senha123` |
| **Aluno** | `aluno.teste@site.com` | `senha123` |

> Os seeders usam `firstOrCreate` — rodar `php artisan db:seed` múltiplas vezes não duplica dados.

---

## 🧩 Funcionalidades

### 🎓 Aluno
- Visualiza presenças e frequência por matéria
- Acompanha notas (prova1, trabalho1, trabalho2, prova2)
- Primeiro acesso via token enviado por e-mail

### 👨‍🏫 Professor
- Gera QR Code por sessão de aula (TTL 2h no cache)
- Monitora lista de presença em tempo real (polling automático)
- Lança e edita notas por turma
- Gerencia eventos/palestras com check-in público sem login
- Gera relatório PDF da lista de presença do evento

### 🛠️ Master
- Cadastra professores, alunos e matérias diretamente (sem fluxo de solicitação)
- Envia e-mail de primeiro acesso automaticamente ao cadastrar
- Visualiza contadores de usuários e matérias
- Acessa central de presenças
- Gerencia vínculos entre professores e matérias

---

## 📧 Envio de E-mails

O sistema envia e-mails automaticamente nos seguintes eventos:

| Evento | Destinatário | Descrição |
|---|---|---|
| Cadastro pelo Master | Aluno / Professor | Token provisório de primeiro acesso via SMTP |
| Esqueci minha senha | Aluno / Professor | Novo token de redefinição de senha |

### Configuração SMTP (Gmail)

O envio usa **Gmail SMTP** com **App Password** (não a senha da conta Google).

1. Acesse [myaccount.google.com/apppasswords](https://myaccount.google.com/apppasswords)
2. Ative a verificação em duas etapas (obrigatório)
3. Crie uma senha de app para "Smart Attendance"
4. No `.env`, configure:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=seu@gmail.com
MAIL_PASSWORD=xxxxxxxxxxxxxxxx   # App Password sem espaços
MAIL_FROM_ADDRESS="seu@gmail.com"
MAIL_FROM_NAME="Smart Attendance"
```

5. Limpe o cache: `php artisan config:clear`

> **Importante:** nunca commite o `.env` real — use apenas `.env.example` com placeholders.

---

## 🗂️ Arquitetura

### Autenticação Multi-Guard

Três guards independentes, cada um com seu próprio modelo, tabela e sessão:

| Guard | Model | Tabela | Rota base |
|---|---|---|---|
| `auth:alunos` | `AlunoModel` | `alunos` | `/aluno/*` |
| `auth:professores` | `ProfessorModel` | `professores` | `/professor/*` |
| `auth:masters` | `UsuarioMaster` | `usuario_masters` | `/master/*` |

### Criptografia e Blind Index

Campos PII (email, CPF, RA) são cifrados com AES-256 via cast `encrypted` do Laravel. Para buscas, o trait `HasBlindIndex` gera um hash SHA-256 determinístico armazenado em colunas `*_search`. O campo `nome` é armazenado em texto plano para facilitar exibição e pesquisa direta.

### Fluxo de Presença (Disciplinas)

1. Professor gera QR Code para uma sessão → armazenado no cache com chave `aula_materia_{id}_{data}` (TTL 2h)
2. O QR Code codifica a URL usando o **host real da requisição** — se o professor acessar pelo IP local, o celular dos alunos na mesma rede consegue escanear
3. Aluno escaneia → `PresencaController` valida o cache e cria registro em `presencas`
4. Professor vê lista em tempo real via polling AJAX (a cada 3s)
5. Rate limiting: 5 tentativas por minuto por usuário

### Fluxo de Eventos / Palestras

1. Professor acessa Gestão de Palestra → sistema gera um **token único de sessão** (16 chars)
2. QR Code e link de acesso são exibidos com o token embutido na URL (`/evento/checkin?token=...`)
3. Participante acessa o link (sem necessidade de login), preenche nome e e-mail
4. Check-in é registrado no **servidor** (Laravel Cache, TTL 8h), não no localStorage
5. Professor vê a lista atualizar a cada 30 segundos via AJAX
6. Ao encerrar, o cache da sessão é limpo e um PDF pode ser gerado

### Fluxo de Primeiro Acesso

1. Master cadastra usuário em `/dashboard/master/cadastrar`
2. Sistema cria conta com `remember_token` como token de acesso inicial
3. E-mail é enviado com o token provisório
4. Usuário usa o token como senha no primeiro login
5. Sistema redireciona para `/criar-senha` onde define a senha definitiva
6. `remember_token` é zerado após a criação da senha
7. Se o aluno tinha um QR Code pendente antes do login, é redirecionado para confirmar presença automaticamente

---

## 📁 Estrutura

```
smart-attendance/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AlunoLoginController.php
│   │   │   ├── ProfessorLoginController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── EventoController.php            # Eventos / palestras
│   │   │   ├── MasterCadastroController.php    # Cadastro direto pelo master
│   │   │   ├── PresencaController.php          # QR Code de disciplinas
│   │   │   ├── GerenciarMateriaController.php
│   │   │   ├── MasterSearchController.php
│   │   │   ├── CriarSenhaController.php
│   │   │   └── EsqueciSenhaController.php
│   │   ├── Middleware/
│   │   │   ├── CheckRole.php
│   │   │   ├── PrimeiroAcessoMiddleware.php
│   │   │   └── SecurityHeaders.php
│   │   └── View/Composers/SidebarComposer.php
│   ├── Models/
│   │   ├── AlunoModel.php
│   │   ├── ProfessorModel.php
│   │   ├── UsuarioMaster.php
│   │   ├── Materia.php
│   │   └── Presenca.php
│   ├── Mail/
│   │   └── PrimeiroAcessoMail.php
│   └── Traits/
│       └── HasBlindIndex.php
├── database/
│   ├── migrations/
│   └── seeders/
├── public/
│   ├── js/pages/
│   │   ├── evento-checkin.js   # Check-in de eventos (envia ao servidor)
│   │   └── evento-presenca.js  # Painel do professor (polling servidor)
│   └── css/
├── resources/views/
│   ├── aluno/
│   ├── professor/
│   ├── master/
│   │   ├── home.blade.php
│   │   └── cadastrar.blade.php
│   ├── pages/
│   │   ├── evento-checkin.blade.php
│   │   └── evento-presenca.blade.php
│   ├── emails/
│   │   └── primeiro-acesso.blade.php
│   └── layouts/theme.blade.php
├── routes/web.php
└── docs/                       # Relatórios e documentação
```

---

## 🗄️ Banco de Dados

As tabelas principais utilizam **Surrogate Keys** (nunca RA/CPF como PK), **criptografia AES-256** nas colunas sensíveis e **Blind Index SHA-256** para buscas seguras.

| Tabela | Descrição |
|---|---|
| `alunos` | Dados dos alunos (PII cifrado) |
| `professores` | Dados dos professores (PII cifrado) |
| `usuario_masters` | Administradores do sistema |
| `materias` | Disciplinas cadastradas |
| `aluno_materia` | Pivot com notas (prova1, trabalho1, trabalho2, prova2) |
| `materia_professor` | Pivot de vínculo professor ↔ matéria |
| `presencas` | Registros de check-in via QR Code de disciplina |
| `cache` | Cache da aplicação (inclui tokens de sessão de eventos) |

> 📊 [Ver diagrama e descrição completa das tabelas](../../wiki/Banco-de-Dados)

---

## 📖 Documentação

| Documento | Link |
|---|---|
| 🏠 Wiki completa | [GitHub Wiki](../../wiki) |
| ⚙️ Instalação detalhada | [Wiki · Instalação](../../wiki/Instalacao) |
| 🔐 Relatório de Segurança | [Wiki · Segurança](../../wiki/Seguranca) |
| 📁 Estrutura do Projeto | [Wiki · Estrutura](../../wiki/Estrutura) |
| 🗄️ Banco de Dados | [Wiki · Banco de Dados](../../wiki/Banco-de-Dados) |
| 📄 Relatório Word | [Download](docs/Relatorio_Seguranca_SmartAttendance.docx) |

---

<div align="center">

Desenvolvido por **Atila Alves Rodrigues** · Projeto Integrador · 2026

</div>
