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
| 👨‍🏫 **Professor** | Gerencia chamadas e notas |
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
# DB_PORT=5432
# DB_DATABASE=smart_attendance
# DB_USERNAME=postgres
# DB_PASSWORD=sua_senha_aqui

# 5. Configurar e-mail SMTP no .env (ver seção E-mails abaixo)

# 6. Rodar migrações e popular o banco
php artisan migrate --seed

# 7. Iniciar o servidor
php artisan serve
```

Acesse: `http://localhost:8000`

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
- Gera QR Code por sessão de aula (TTL 30 min no cache)
- Monitora presença em tempo real
- Lança e edita notas por turma
- Gerencia eventos/palestras com check-in público

### 🛠️ Master
- Cadastra professores, alunos e matérias diretamente (sem fluxo de solicitação)
- Envia e-mail de primeiro acesso automaticamente ao cadastrar
- Visualiza contadores de usuários e matérias
- Acessa central de presenças
- Gerencia vínculos entre professores e matérias

---

## 📧 Envio de E-mails

O sistema envia e-mails automaticamente no seguinte evento:

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

### Fluxo de Presença

1. Professor gera QR Code para uma sessão → cache com chave `aula_materia_{id}_{data}` (TTL 30min)
2. Aluno escaneia → `PresencaController` valida o cache e cria registro em `presencas`
3. Rate limiting: 5 tentativas por minuto por usuário

### Fluxo de Primeiro Acesso

1. Master cadastra usuário em `/dashboard/master/cadastrar`
2. Sistema cria conta com senha temporária aleatória e `remember_token` como token de acesso
3. E-mail é enviado com o token provisório
4. Usuário usa o token como senha no primeiro login
5. Sistema redireciona para `/criar-senha` onde define a senha definitiva
6. `remember_token` é zerado após a criação da senha

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
│   │   │   ├── MasterCadastroController.php   # Cadastro direto pelo master
│   │   │   ├── PresencaController.php
│   │   │   ├── GerenciarMateriaController.php
│   │   │   ├── CriarSenhaController.php
│   │   │   └── EsqueciSenhaController.php
│   │   ├── Middleware/
│   │   │   ├── CheckRole.php
│   │   │   └── PrimeiroAcessoMiddleware.php
│   │   └── View/Composers/SidebarComposer.php
│   ├── Models/
│   │   ├── AlunoModel.php
│   │   ├── ProfessorModel.php
│   │   ├── UsuarioMaster.php
│   │   └── Materia.php
│   ├── Mail/
│   │   └── PrimeiroAcessoMail.php
│   └── Traits/
│       └── HasBlindIndex.php
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/views/
│   ├── master/
│   │   ├── home.blade.php
│   │   └── cadastrar.blade.php
│   ├── emails/
│   │   └── primeiro-acesso.blade.php
│   └── layouts/theme.blade.php
├── routes/web.php
└── docs/
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
| `presencas` | Registros de check-in via QR Code |

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
