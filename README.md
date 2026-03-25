<div align="center">

<img src="https://img.shields.io/badge/PHP-8.1+-7A86C8?style=for-the-badge&logo=php&logoColor=white"/>
<img src="https://img.shields.io/badge/Laravel-10+-FF2D20?style=for-the-badge&logo=laravel&logoColor=white"/>
<img src="https://img.shields.io/badge/MySQL-8.0+-00758F?style=for-the-badge&logo=mysql&logoColor=white"/>
<img src="https://img.shields.io/badge/Status-Em%20Desenvolvimento-F59E0B?style=for-the-badge"/>

# 📋 Smart Attendance

> Sistema web de controle de presença inteligente — rápido, preciso e seguro.  
> Desenvolvido como **Projeto Integrador** com PHP/Laravel.

[📖 Documentação](#-documentação) •
[⚙️ Instalação](#%EF%B8%8F-instalação) •
[🔐 Segurança](#-segurança) •
[📁 Estrutura](#-estrutura) •
[🗄️ Banco de Dados](#%EF%B8%8F-banco-de-dados)

O projeto sera implementado no site https://filamentphp.com/

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
| 🔒 Criptografia | AES-256 em todos os dados pessoais (PII) |
| 👥 Autenticação | Multi-Guard independente por perfil |
| 🚦 Rate Limiting | Anti-força bruta por IP |
| 🛡️ Headers HTTP | CSP, X-Frame-Options, Permissions-Policy |
| 🔑 CSRF | Token em todos os formulários |
| 🤖 Honeypot | Anti-bot no check-in público |
| 📋 Logging | Registro de tentativas de login falhas |

> 📄 [Ver relatório completo de segurança](docs/Relatorio_Seguranca_SmartAttendance.docx)

---

## 📊 Status do Projeto

| Módulo | Status |
|---|---|
| Autenticação | ✅ Concluído |
| Controle de Presença | ✅ Concluído |
| Gerenciamento de Notas | ✅ Concluído |
| Painel Master | 🚧 Em progresso |
| Testes Automatizados | 📋 Pendente |
| Deploy | 📋 Pendente |

---

## 🛠️ Stack

<div align="center">

![PHP](https://img.shields.io/badge/PHP-7A86C8?style=flat-square&logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=flat-square&logo=laravel&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-00758F?style=flat-square&logo=mysql&logoColor=white)
![Blade](https://img.shields.io/badge/Blade-FF2D20?style=flat-square&logo=laravel&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind-38BDF8?style=flat-square&logo=tailwindcss&logoColor=white)

</div>

---

## ⚙️ Instalação
```bash
# 1. Clonar o repositório
git clone https://github.com/AtilaAlvesRodrigues/smart-attendance.git
cd smart-attendance
git checkout PHP

# 2. Instalar dependências
composer install
npm install && npm run build

# 3. Configurar ambiente
cp .env.example .env
php artisan key:generate

# 4. Configurar banco de dados no .env
# DB_DATABASE=smart_attendance
# DB_USERNAME=root
# DB_PASSWORD=sua_senha

# 5. Rodar migrations
php artisan migrate

# 6. Iniciar servidor
php artisan serve
```

Acesse: `http://localhost:8000`

---

## 📁 Estrutura
```
smart-attendance/
├── app/
│   ├── Http/
│   │   ├── Controllers/    # Lógica da aplicação
│   │   └── Middleware/     # SecurityHeaders, CheckRole
│   ├── Models/             # AlunoModel, ProfessorModel, UsuarioMaster
│   └── Traits/             # HasBlindIndex
├── database/migrations/    # Estrutura das tabelas
├── resources/views/        # Templates Blade
├── routes/web.php          # Rotas com guards e throttle
└── docs/                   # Documentação do projeto
```

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

## 🗄️ Banco de Dados

As tabelas principais utilizam **Surrogate Keys** (nunca RA/CPF como PK), **criptografia AES-256** nas colunas sensíveis e **Blind Index SHA-256** para buscas seguras.

> 📊 [Ver diagrama e descrição completa das tabelas](../../wiki/Banco-de-Dados)

---

<div align="center">

Desenvolvido por **Atila Alves Rodrigues** · Projeto Integrador · 2026

</div>
