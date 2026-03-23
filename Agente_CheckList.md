# Agente_CheckList — Diretrizes de Master/UI

Este arquivo serve como o "Caminho das Pedras" para a criação e modificação de páginas no Smart Attendance. Sempre consulte este checklist para garantir consistência estética e funcional (Premium Glassmorphism).

## 1. Estrutura Base (Blade Layout)
Toda página deve estender o layout principal e usar as seções corretas:
- **Base**: `@extends('layouts.theme')`
- **Título**: `@section('title', 'Nome da Página - Smart Attendance')`
- **Body Class**: `@section('body-class', 'gradient-bg relative min-h-screen flex flex-col')`
- **Nav Custom**:
    - `@section('nav-left')`: Botões de "Voltar" ou ações primárias da esquerda.
    - `@section('nav-user')`: Info do usuário logado.
        - **Padrão**: Envolver em `<div class="pal-nav-actions" style="gap:0.5rem">` contendo o bloco `.pal-nav-user` e o botão `.pal-profile-btn`.
- **Styles/Scripts**: Usar `@push('styles')` e `@push('scripts')`.

## 2. Elementos de Design Obrigatórios
Para manter o visual premium e consistente:
- **Blobs de Fundo**: Adicionar no início do `@section('content')`:
  ```html
  <div class="blob top-[-100px] left-[-100px]"></div>
  <div class="blob-2"></div>
  ```
- **Main Container**: `<main class="max-w-7xl mx-auto w-full p-6 mt-8 relative z-10 flex-grow">`
- **Header da Seção**:
  ```html
  <div class="mb-12 animate-reveal">
      <p class="pal-overline mb-2">Módulo Exemplo</p>
      <h2 class="text-4xl font-black tracking-tighter pal-always-white">Título Grande</h2>
      <p class="text-white/70 font-medium">Subtítulo explicativo ou instrução.</p>
  </div>
  ```

## 3. Sistema de Cores e Temas (Light/Dark)
- **Variáveis CSS**: Priorizar o uso de variáveis de `--pal-*` definidas em `public/css/theme.css`.
- **Mecanismo de Tema Automático (Attribute Selectors)**:
    - O sistema utiliza seletores `html.light-mode [style*="..."]` no `theme.css` para inverter cores de estilos inline automaticamente.
    - **Atenção**: Ao usar cores hexadecimais em estilos inline, utilize padrões comuns como `#efefef` ou `#111` para que o seletor consiga capturá-los e invertê-los no Light Mode.
- **Contraste no Theme Switch**:
    - **Regra de Ouro**: Sempre verifique se o fundo do componente não coincide com o fundo da página no Light Mode (`html.light-mode`).
    - **Bordas em Light Mode**: Bordas brancas semitransparentes (`rgba(255,255,255,0.1)`) desaparecem no Light Mode. Use a classe `.pal-icon-box` ou similares que possuam override para `rgba(0,0,0,0.1)`.
- **Always White**: Use `.pal-always-white` para textos que devem permanecer brancos mesmo em light mode (geralmente sobre gradientes ou blobs coloridos).

## 4. Componentes Reutilizáveis
### Glass Cards
Use a classe `.glass` com bordas sutis:
```html
<div class="glass p-8 rounded-sm border border-white/10 hover:border-white/20 transition-all">
    <!-- Conteúdo -->
</div>
```

### Tabelas (Data Display)
Estrutura padrão de listagem:
- Tabela dentro de uma `.glass` com `overflow-hidden`.
- Headers em `text-xs font-black uppercase tracking-[0.2em]`.
- Listras de divisão: `.divide-y .divide-white/5`.
- Hover nas linhas: `.hover:bg-white/5`.

### Modais
Use a estrutura de `#info-modal` com `dashboard_modal.js` para detalhes dinâmicos.
- Fundo: `backdrop-blur-md bg-black/60`.
- Conteúdo: `.glass rounded-sm border border-white/10`.

### Barras de Progresso (Frequência/Status)
Usar para indicar limites ou progresso:
```html
<div style="height:3px; background:rgba(255,255,255,0.05); border-radius:2px; overflow:hidden;">
    <div style="height:100%; width:{{ $percent }}%; background:{{ $barColor }}; transition:width 1s ease;"></div>
</div>
```
- Cores dinâmicas sugeridas: Verde (`#22c55e`), Amarelo (`#eab308`), Vermelho (`#ef4444`).

### Páginas de Feedback (Sucesso/Erro)
Layout centralizado para confirmações ou avisos:
- **Icon Container**: 64x64px, borda 12% branca, ícone centralizado.
- **Textos**: Overline (Space Grotesk) -> H1 (Font-900) -> P (Subtitle).
- **Info Card**: Bloco de informação secundária opcional com `background:rgba(18,18,18,0.98)` e borda 8%.

### Formulários e Inputs
Para páginas de login ou configurações:
- **Labels**: Classe `.login-label` (Space Grotesk, 0.75rem, bold, uppercase).
- **Inputs**: Classe `.login-input` (fundo `#111`, bordas 10%, transition no focus).
- **Mensagens de Erro**: Classe `.login-error` (fundo vermelho 8%, borda vermelha 25%, texto claro).
- **Password Toggle**: Botão absoluto à direita com ícone de olho.

### Banners e Dashboards
- **Banner Principal**: Classe `.pal-dashboard-banner` (flex-wrap, border-radius 4px, fundo `#111` ou `white`).
- **Typography Professor**: 
    - `.pal-eyebrow`: Space Grotesk, 0.75rem, uppercase, cinza.
    - `.pal-title`: Font-900, tracking-tighter, clamp(2rem-3rem).
    - `.pal-subtitle`: 0.88rem, cinza suave.

### Animações e Efeitos Premium
- **Staggered Reveal (Cards)**: Usar classes `.pal-card-delay-1`, `.pal-card-delay-2`, `.pal-card-delay-3` para que os cards apareçam em sequência.
- **Micro-interações**: Adicionar `transition: all 0.2s` em todos os elementos interativos.
- **Cursor Glow**: O sistema possui um `#pal-cursor-glow` global (não remover).
- **Status Pulsante**: Usar `.animate-pulse` em `w-2 h-2 rounded-full` para estados "On-line" ou "Ativo".

### Gestão e Tabelas Avançadas
- **Inputs em Tabelas**: Classe `.nota-input` para edição inline (Glassmorphism).
    - Estados: `.saving` (laranja), `.saved` (verde), `.error` (vermelho).
- **Situação em Badges**: Classe `.shadow-lg` com cores vibrantes (ex: `bg-green-500 shadow-green-500/30`) para estados finais (Aprovado/Reprovado).
- **Grid de Estatísticas**: Grid de 5 colunas (`grid-cols-5 gap-6`) com `.glass` e texto grande (`text-3xl font-black`) para resumos.
- **Legendas**: Bloco inferior com `flex-wrap gap-8` e pontos coloridos explicativos.

## 5. Páginas de Erro e Feedback
Para erros como 419, 404, etc:
- **Icon Container**: 64x64px (`w-16 h-16`), borda 12% branca, ícone centralizado.
- **Tipografia**: Overline (Eyebrow) -> H1 (Title) -> P (Subtitle).
- **Buttons**: Usar `.pal-btn-primary` e `.pal-btn-outline` para consistência de tema e contraste.

## 6. Perfil de Usuário (Modal)
O perfil de usuário deve seguir o padrão da página de aluno, sendo acessível via modal:
- **Trigger**: Botão na Navbar (lado direito, junto à info do usuário) usando a classe `.pal-profile-btn`.
- **Classes Globais (CSS)**:
    - `.pal-modal-overlay`: Fundo escuro fixo com `backdrop-blur-md`.
    - `.pal-modal-content`: Card glass centralizado (`max-width:640px`).
    - `.pal-profile-field`: Bloco de informação (Label + Valor).
- **Conteúdo por Role**: 
    - **Alunos**: Nome, Email, RA, CPF + Lista de Matérias (Faltas/Progresso).
    - **Professores**: Nome, Email, CPF + Lista de Disciplinas lecionadas.
    - **Masters**: Nome ("Administrador Master"), Email, Função ("Controle Total").
- **Lógica**: Utilizar JavaScript para toggle (`id="profile-modal"`, `id="open-profile"`, `id="close-profile"`). Manter consistência com `dashboard_modal.js` se for dinâmico, ou script local se fixo.

## 7. Arquitetura e Backend (Lógica de Negócio)
Para manter a integridade funcional ao criar novas rotas/controllers:
- **Guards e Roles**:
    - Alunos: `auth:alunos`, role `aluno`. PK é `ra`.
    - Professores: `auth:professores`, role `professor`. PK é `id`.
    - Masters: `auth:masters`, role `master`.
- **Relacionamentos e Pivots**:
    - Notas e frequências específicas são armazenadas na tabela pivot `aluno_materia`.
    - Use `DB::table('aluno_materia')` para updates diretos de notas para performance.
- **Sessões Temporárias (QR Code)**:
    - Utilizar `Cache::put/get` com chaves únicas (`aula_materia_{id}_{data}`) para controlar a validade dos códigos de aula.
- **Padrão de Listagem**:
    - Sempre utilizar `paginate(10)` ou `paginate(20)` em controllers de busca/master.
    - Filtros dinâmicos: Usar `$request->filled('campo')` para construir queries condicionais.

## 8. REGRAS CRÍTICAS PARA BOTÕES (Interaction)
- **Não Transparência no Hover**: Botões DEVEM ter fundo **sólido** ao passar o mouse.
- **Mudança de Cor de Texto**: No hover, a cor do texto/letra deve mudar para garantir contraste (ex: de preto para branco).
- **Acessibilidade de Tema**: Se um botão for `white` em dark mode, ele deve se tornar `black` em light mode (usar variáveis ou overrides automáticos de hex como `#efefef`).
- **Problema do "Texto Escuro em Fundo Escuro"**: No Light Mode, evite usar classes que invertem tanto o fundo quanto o texto para a mesma cor (ex: `bg-white` e `pal-text` simultaneamente podem causar invisibilidade).

## 9. Arquivos de Referência
- **CSS Global**: `public/css/theme.css`
- **CSS Professor**: `public/css/theme_professor.css` (específico para cards e dashboard).
- **Layout Blade**: `resources/views/layouts/theme.blade.php`
- **Exemplo de Página**: `resources/views/master/professores.blade.php`

---

## 🛑 10. PRECAUÇÕES CRÍTICAS (No-Go Zones)
Nunca altere a lógica fundamental de autenticação sem validação do backend (Ex: Painel de Controle invisível):
- **Cores Literais com `!important`**: Evite usar `color: #efefef !important` em classes globais sem fornecer o override correspondente em `html.light-mode`. Isso impede que o motor de inversão funcione.

- **Atributo `name` em Logins**: NÃO altere os nomes dos inputs (`cpf_email` no professor, `ra_email_cpf` no aluno). Isso quebra a captura de dados no Controller e impede o login.
  - *Exemplo de Erro a evitar*: Mudar `cpf_email` para `ra_email_cpf` no formulário de professor quebra o acesso de docentes e masters.
- **CSRF Token**: Nunca remova `@csrf` dos formulários.
- **Routes de Auth**: Não altere nomes de rotas como `login.aluno` ou `login.professor` sem atualizar todos os redirects nos Controllers.

---

## 📋 11. TABELAS E LISTAGENS DE DADOS
Para garantir legibilidade em Relatórios, Dashboards Prof/Aluno e Módulos Master:
- **Títulos de Página**: SEMPRE use `.pal-title` (ex: "Histórico de Presenças", "Visão Geral do Sistema"). Nunca use `pal-always-white`.
- **Eyebrows (Sobrancelhas)**: Use `.pal-eyebrow` para rótulos pequenos acima de títulos (ex: "Módulo Presença Digital", "Guia de Utilização").
- **Alinhamento**: Em sessões de Guia ou Introdução (ex: "Como funciona?"), centralize o título e a sobrancelha para melhor equilíbrio visual.

---

## ✨ 12. ANIMAÇÕES E INTERAÇÕES (Premium Feel)
- **Efeito Tilt**: Todos os cards de seleção ou ação (Matérias, Passos do Guia) devem usar a classe `.tilt-card` e o script de inclinação 3D para feedback interativo.
- **Transições de Tema**: Garanta que mudanças de cor (especialmente em cards com delay) usem `@keyframes` para suavizar a transição entre dark e light mode.
- **Textos em Tabelas**: 
  - Nome/Dado Principal: Classe `.pal-text` (font-bold).
  - RA/Meta-info/Subtexto: Classe `.pal-subtitle` (font-mono ou font-medium).
- **Cabeçalhos de Tabela (`th`)**: Use `.pal-subtitle` com `font-black uppercase tracking-widest`.
- **Contrast Check**: Teste sempre se as linhas da tabela (`hover:bg-white/5`) e os divisores (`divide-white/5`) são visíveis em Light Mode (o `theme.css` cuida da maioria, mas evite cores fixas no Blade).
