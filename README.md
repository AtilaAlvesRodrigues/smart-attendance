<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).


# 🚀 Smart-Attendance — Guia de Instalação

O **Smart-Attendance** é uma solução moderna para gestão de presença, built com **Laravel 12** e **PHP 8.2**. Siga as etapas abaixo para configurar o ambiente e rodar a aplicação localmente.

---

## 🧩 Pré-requisitos

* **PHP** (versão 8.2+): [https://php.net/downloads.php](https://php.net/downloads.php)
* **Composer**: [https://getcomposer.org](https://getcomposer.org/)
* **Node.js & NPM**: [https://nodejs.org/](https://nodejs.org/)
* **PostgreSQL** (versão 17+ recomendada): [https://www.postgresql.org/download/](https://www.postgresql.org/download/)
* **Git**: [https://git-scm.com](https://git-scm.com/)

---

## 💻 Configuração do Ambiente (Windows)

### 1. Configuração do PHP (`php.ini`)
Certifique-se de que as seguintes extensões estão habilitadas no seu `php.ini` (geralmente em `C:\php\php.ini`):

```ini
extension=fileinfo
extension=zip
extension=pdo_pgsql
extension=pgsql
```

### 2. Configurações de Banco de Dados (PostgreSQL)
Crie um banco de dados chamado `smart_attendance` no seu PostgreSQL.

---

## 🛠️ Instalação Passo a Passo

```bash
# 1. Clonar o repositório
git clone https://github.com/AtilaAlvesRodrigues/smart-attendance.git
cd smart-attendance

# 2. Instalar dependências do PHP
composer install

# 3. Configurar o ambiente
cp .env.example .env
php artisan key:generate

# 4. Instalar e compilar dependências do Front-end
npm install
npm run build

# 5. Configurar o Banco de Dados no .env
# Abra o arquivo .env e ajuste as credenciais:
# DB_CONNECTION=pgsql
# DB_PORT=5432
# DB_DATABASE=smart_attendance
# DB_USERNAME=postgres
# DB_PASSWORD=sua_senha_aqui

# 6. Rodar as migrações e popular o banco (Seeds)
php artisan migrate --seed

# 7. Iniciar o servidor
php artisan serve
```

> [!TIP]
> Você também pode usar o comando de atalho: `composer setup` para automatizar boa parte deste processo.

---

## 💾 Comandos Úteis

| Comando | Descrição |
| --- | --- |
| `php artisan migrate:fresh --seed` | Limpa o banco e recria as tabelas com dados de teste. |
| `npm run dev` | Inicia o servidor de desenvolvimento do Vite. |
| `php artisan db:seed` | Apenas popula o banco (sem apagar dados). |

---

## 🧪 Usuários de Teste

Após rodar o comando `--seed`, você pode usar estas credenciais:

| Perfil | Email | Senha |
| --- | --- | --- |
| **Admin Master** | `master@admin.com` | `senha123` |
| **Professor** | `professor@teste.com` | `senha123` |
| **Aluno** | `aluno.teste@site.com` | `senha123` |

---

## 🤝 Contribuições

Este projeto faz parte de um sistema acadêmico de controle de presença. Sinta-se à vontade para abrir PRs ou Issues!
