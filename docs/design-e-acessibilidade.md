# Design e acessibilidade

O visual do Smart Attendance é escuro por padrão, com tema claro opcional, cartões com efeito de vidro e tipografia forte. As regras completas, com exemplos e bugs já resolvidos, estão no [`.cursorrules`](../.cursorrules); este documento é o resumo.

## Identidade visual

| Elemento | Padrão |
|---|---|
| Tipografia | Inter nos textos e títulos; Space Grotesk nos rótulos em caixa alta |
| Cores de texto | Variáveis `--pal-*` em `public/css/theme.css`; cinzas de texto a partir de `#a3a3a3` no tema escuro |
| Estados | Verde para sucesso e *Em dia*, âmbar para *Atenção*, vermelho para reprovação e erros |
| Cartões | Classe `glass`, borda sutil, sem sombras em tudo |
| Botões | A mesma ação tem a mesma cor em todas as telas: gerar QR em claro sobre escuro, confirmar e salvar em verde, cancelar e sair em contorno ou vermelho suave |
| Temas | Escuro e claro, alternados pelo botão no canto inferior direito; a escolha fica salva no navegador |

## Camadas de CSS

1. `resources/css/app.css`: Tailwind 4, compilado pelo Vite.
2. `public/css/theme.css`: identidade, tema claro e ajustes automáticos de cores inline.
3. `public/css/theme_aluno.css`, `theme_professor.css`, `theme_master.css`: componentes de cada perfil.
4. `public/css/usability.css`: correções de legibilidade e acessibilidade, **sempre carregado por último**.

Novas correções globais de legibilidade vão no `usability.css`, e não espalhadas pelos arquivos de cada perfil.

## Acessibilidade

Referência: WCAG 2.2, nível AA.

| Item | Como é atendido |
|---|---|
| Contraste | Texto com pelo menos 4,5:1 sobre o fundo nos dois temas |
| Tamanho de texto | Rótulos com pelo menos ~12,5 px; espaçamento entre letras reduzido nos rótulos em caixa alta |
| Estado não só por cor | Todo selo de situação tem texto e, quando cabe, ícone: "✓ Em dia", "! Atenção", "✕ Reprovado" |
| Teclado | Foco visível (contorno roxo) em links, botões e campos; `Esc` fecha modais e o modo projeção |
| Leitores de tela | `aria-label` em botões só com ícone; `role="timer"` no cronômetro da sessão; `aria-live` na contagem de presentes |
| Movimento | Com `prefers-reduced-motion`, animações, efeito de inclinação e brilho do cursor são desligados |
| Celular | Alvos de toque de pelo menos 44 px; campos com 16 px, para o iPhone não aplicar zoom; nada mais largo que a tela |
| Componentes nativos | Histórico de aulas com `<details>`, que funciona com teclado e leitor de tela sem JavaScript |

## Linguagem

Os textos são escritos para quem usa o sistema, e não para quem o programou:

| Evite | Prefira |
|---|---|
| Sistema em Espera · Geração de Código | Iniciar chamada |
| Transmissão Ativa | Chamada aberta |
| Aguardando leituras... | Ninguém registrou presença ainda |
| Acesso Root | Administrador |
| Ops. | Não foi possível registrar, e o que fazer em seguida |

## Checklist para uma tela nova

- [ ] Estende `layouts.theme` e define um `@section('title')` específico.
- [ ] Usa `.pal-title`, `.pal-subtitle` e `.pal-eyebrow` nos títulos.
- [ ] Funciona em 390 px de largura, sem rolagem horizontal.
- [ ] Funciona nos temas escuro e claro.
- [ ] Botões só com ícone têm `aria-label`.
- [ ] Estados comunicados por texto, e não só por cor.
- [ ] Dados do usuário inseridos no JavaScript com `textContent`.
- [ ] Textos em português simples, dizendo o que acontece e o que fazer.
