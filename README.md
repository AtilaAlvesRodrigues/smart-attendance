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

Este projeto utiliza **Laravel 8** com **PHP 8.2**. Siga as etapas abaixo para configurar o ambiente e rodar a aplicação localmente.

---

## 🧩 Pré-requisitos

* **PHP** (versão 8.2 ou compatível): [https://php.net/downloads.php](https://php.net/downloads.php)
* **Composer** (Gerenciador de dependências PHP): [https://getcomposer.org](https://getcomposer.org/)
* **Node.js & NPM** (Obrigatório para o Vite/Front-end): [https://nodejs.org/](https://nodejs.org/)
* **Postgres** (versão 17.6 recomendada): [https://www.postgresql.org/download/](https://www.postgresql.org/download/)
* **Git**: [https://git-scm.com](https://git-scm.com/)

---

## 💻 Configuração do Ambiente (Windows)

### 1. Configuração do PHP (Variáveis de Ambiente)
Se você baixou o ZIP do PHP:
1. Extraia o conteúdo para `C:\php`.
2. Pesquise por **"Configurações avançadas do sistema"** > **Variáveis de Ambiente**.
3. Em **Variáveis do Sistema**, edite a variável `Path`.
4. Clique em **Novo** e adicione: `C:\php`.

### 2. Configuração do `php.ini`
Abra o terminal (Git Bash) e digite `code C:\php\php.ini` ou abra o arquivo manualmente em `C:\php\php.ini`. Certifique-se de ativar as seguintes extensões (remova o `;` do início das linhas):

```ini
extension_dir = "C:\php\ext"
extension=fileinfo
extension=zip
extension=pdo_pgsql
extension=pgsql
```

---

## Configuração Inicial do Git (Git Bash)

 **Registrando o usuário (Faça isso apenas uma vez por máquina):**
   
    git config --global user.name "Seu Nome Aqui"
 
    git config --global user.email "Seu Email Aqui"

---
## 🛠️ Primeira Instalação do PHP (Configuração do `php.ini`)

Após extrair o PHP para `C:\php`, você precisa garantir que a extensão `fileinfo` esteja habilitada no seu `php.ini`.

 **Caso não saiba qual o arquivo `php.ini`**

<img width="614" height="564" alt="image" src="https://github.com/user-attachments/assets/a39437e1-cef9-47ad-9b3f-51f1ce05873c" />

Execute o comando abaixo no seu **Git Bash/Terminal** para **abrir o arquivo de configuração** no VS Code (assumindo que o VS Code está no PATH):

   ```bash
   code C:\php\php.ini
   ```

## 1. Abrir o Terminal ou abra o local da pasta e selecione o arquivo `php.ini` 

* **Windows:**
    * Navegue até a pasta raiz do projeto.
    * Clique com o botão direito e selecione **"Git Bash Here"** ou **"Abrir no Terminal"**.
    * Alternativamente, você pode usar **PowerShell** ou **Prompt de Comando** após configurar o PATH do PHP.

Dentro do arquivo que abrir, localize e remova o ponto e vírgula (`;`) do início da linha para ativá-lo:

**Mude em:**
```ini
extension=fileinfo
```

```ini
extension=zip
```


```ini
extension=pdo_pgsql
```

```ini
extension=pgsql
```

**Modifique de:**
```ini
;extension_dir = "ext"
```
**Para**
```ini
extension_dir = "C:\php\ext"
```

---

## 2. Instalar o Instalador Global do Laravel (Única vez)
Somente de installar o composer
```bash
composer global require laravel/installer
```

### ⚠️ Usando o Composer (Criando o projeto):

```bash
composer create-project laravel/laravel --stability=stable --prefer-dist nome-do-projeto
```

## 3. Acessar o diretório do projeto

```bash
cd nome-do-projeto/
```



## 4. Abrir o VS Code na pasta do projeto

```bash
code .
```


Ao abrir o VScode digite no terminal 

```bash
composer install
```
**Em seguida**

```bash
composer update
```
---

## Configurar Front-end (Vite) ⚠️

```ini
# Instala as dependências do Node.js
npm install

# Compila os assets e gera o manifesto em public/build
npm run build
```

---
*No Termenial Digite:* 
```bash
php artisan serve
```
```ini
npm run dev
```
---

🧪 Solução de Problemas

Erro: Vite manifest not found

Se o erro persistir, limpe o cache de visualização e gere o build novamente:

```ini
php artisan view:clear
php artisan config:clear
npm run build
```
---

### Caso tudo esteja certo esta sera a tela que deve aparecer: 
<img width="1122" height="507" alt="image" src="https://github.com/user-attachments/assets/eb97908c-2810-4a30-9b6f-6010223a5a1f" />

---
## 5. Configurando o Banco de Dados

*Depois de fazer a instalação do Postgres e Fazer a criação do Banco de Dados  `Smart-Attendance`*

Dentro do arquivo `.env` Procure e faça as seguintes alterações no codigo:
```bash
DB_CONNECTION=pgsql
DB_HOST= Já estara preenchido
DB_PORT=5432
DB_DATABASE=Smart-Attendance
DB_USERNAME=postgres
DB_PASSWORD=Sua senha Aqui
```
Em seguida digite o seguinte codigo no terminal
```bash
php artisan migrate
```

*Se tudo der certo a mensagem que deve aparecer sera esta:*

<img width="775" height="206" alt="image" src="https://github.com/user-attachments/assets/927b1423-b453-4862-ae74-bad2d12e2762" />

---

## 💾Rodando Migrações e Seeds (Recomendado para Primeira Configuração)

Para limpar o banco de dados anterior, rodar as migrações e popular o banco de dados com dados de teste/iniciais (seeds), use o comando:
```bash
php artisan migrate:fresh --seed
```


## 🧪 Testando

Acesse o projeto no navegador:

```
http://localhost:8000
```

Verifique se o front-end e a autenticação estão funcionando corretamente.
