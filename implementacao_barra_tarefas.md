# Passo a Passo: Implementação da Barra de Tarefas

Este documento descreve o plano de implementação sequencial para as barras de tarefas (navegação) nas páginas de Professores e Master no projeto Smart Attendance, focando nas práticas atuais do Laravel (Componentes Blade, Layouts Unificados e Injeção de Dados).

## Passo 1: Criação dos Componentes Blade (Interface e Interatividade)

A primeira etapa foca na criação da fundação visual utilizando o padrão "Glassmorphism" (transparência) utilizado no projeto.

1.  **Gerar os arquivos de componente:** Execute os comandos no terminal para gerar três componentes principais.
    *   `php artisan make:component UI/Sidebar`
    *   `php artisan make:component UI/SidebarItem`
    *   `php artisan make:component UI/SidebarGroup` (Para tópicos retráteis)
2.  **Container da Barra (`sidebar.blade.php`):** Crie a estrutura HTML fixa do menu lateral. Utilize marcações que o mantenham no lado esquerdo no Desktop (`fixed left-0 h-screen`) e como barra inferior no Mobile (`fixed bottom-0 w-full`), aplicando o design limpo e translúcido.
3.  **Botão de Navegação (`sidebar-item.blade.php`):** Estruture o HTML do botão individual. Nele, verifique se a rota atual do sistema corresponde à rota que o botão direciona. Caso positivo, injete automaticamente classes CSS para evidenciar que o botão está "Ativo".
4.  **Botão de Abrir e Fechar Tópicos (`sidebar-group.blade.php`):** Para poder expandir e fechar tópicos/categorias da página (como Dropdowns ou Menus Sanfona na barra), utilize o **Alpine.js** (padrão em aplicações modernas). Envolva o grupo com `<div x-data="{ open: false }">`. Adicione um botão principal com o título do tópico e dê a ação `@click="open = !open"` a ele. Ocultar e exibir os sub-links só deve ser feito numa blockul com `x-show="open" x-transition`, dando um efeito bonito ao abrir/fechar.

## Passo 2: Configuração do Layout Matriz

Agora que os blocos de montar existem visualmente, precisamos inseri-los no arquivo pai que engloba todas as páginas.

1.  **Localizar/Criar o App Layout:** Abra o seu layout principal em `resources/views/layouts/app.blade.php` (ou equivalente).
2.  **Injetar a Barra:** Insira a tag `<x-ui.sidebar />` dentro deste layout, preferencialmente ao lado ou ao redor da área de conteúdo principal.
3.  **Área Dinâmica:** Garanta que todo o miolo da página seja processado utilizando a variável `{{ $slot }}`, permitindo que os links abram os formulários ou tabelas no centro sem recarregar a barra.

## Passo 3: Controle e Renderização Condicional (ACL)

Neste passo, informamos ao Laravel quem pode ver o quê, evitando menu duplicado para Master e Professor.

1.  **Editar o `sidebar.blade.php`:** Defina todos os `<x-ui.sidebar-item>` que o projeto necessita.
2.  **Envolver com Permissões:** 
    *   Para agrupar ferramentas exclusivas do Gestor, utilize a diretiva `@can('is-master')` ou `@if(auth()->user()->is_master)`.
    *   Para turmas e registro de presença (Professores), use `@can('is-professor')` ou respectiva validação do sistema.
    *   Para links globais (como "Meu Perfil" ou "Sair"), deixe os itens fora de condições isoladas.

## Passo 4: View Composers para Dados Dinâmicos (Opcional, porém Recomendado)

Se a sua barra precisar de números atualizados (ex: Aulas pendentes no dia para o professor, ou Número de alertas no master), alimentamos ela por debaixo dos panos.

1.  **Criar o Provider:** Gere um novo provedor: `php artisan make:provider ViewServiceProvider`.
2.  **Registrar a Escuta:** No método `boot()` do provedor, registre o acesso: `View::composer('components.ui.sidebar', \App\Http\View\Composers\SidebarComposer::class);`
3.  **Criar o Composer:** Crie a classe `SidebarComposer`, faça as lógicas de contagem no banco baseadas no `auth()->user()` atual e injete os resultados com `$view->with('notificacoes', $count);`.

## Passo 5: Refatoração das Views Existentes

A última etapa envolve a limpeza técnica visual dos arquivos que já existem.

1.  Navegue por todos os arquivos dentro de `resources/views/professores/` e `resources/views/master/`.
2.  Apague o "header" repetido ou links soltos de navegação em cada página (já que a Sidebar central agora faz isso).
3.  Envolva a página na estrutura unificada utilizando `<x-app-layout>` (ou diretiva `@extends`).
