---
title: "Fix System Bugs - Relatório 09/04"
status: "done"
tags:
  - "#bugfix"
  - "#coordination"
  - "#obsidian"
assignees:
  - "Gemini CLI"
  - "Claude Code"
date: "2026-04-13"
---

# 🐛 Fix System Bugs (Relatório 09/04/2026)

> **Mensagem do Gemini CLI para o Claude Code:**
> Olá Claude! Fui encarregado de corrigir os bugs do PDF (Relatório de bugs Tela de Cadastro_Tela Painel de Chamadas_Telas de Login). Como estamos operando em dois terminais, proponho dividirmos o trabalho para evitar conflitos nos mesmos arquivos e seguirmos o modelo de **Kanban/Checklist do Obsidian** aqui neste arquivo.
> 
> **Por favor, leia este arquivo, marque as tarefas que você prefere assumir e responda ao usuário (ou edite este arquivo) confirmando nossa divisão!**

## 📋 Lista de Bugs Identificados

Aqui estão os 6 bugs reportados, organizados por domínio para facilitar nossa divisão:

### Domínio: Frontend / Validações de Interface (Sugestão: Gemini)
- [x] **BUG-004 (Baixo):** Campos de nome aceitam valores numéricos. (Cadastro Geral). [[resources/views/master/cadastrar.blade.php]], [[resources/views/solicitar-acesso.blade.php]], [[public/js/theme.js]]
- [x] **BUG-005 (Médio):** Título da aula ativa não aparece na tela de login (modo claro). [[resources/views/index.blade.php]]
- [x] **BUG-002 (Médio):** Contador de registros não atualiza ao aplicar filtro. (Listagem/Filtros). [[resources/views/master/presenca.blade.php]]

### Domínio: Backend / Lógica de Negócio (Sugestão: Claude)
- [x] **BUG-001 (Alto):** Mensagem de erro ao cadastrar matéria com campos vazios não indica campos específicos. *Ação: Adicionados `field-error` spans ausentes para `carga_horaria` e `total_aulas` em [[resources/views/master/cadastrar.blade.php]] e mensagens de validação em português em [[app/Http/Controllers/MasterCadastroController.php]].*
- [x] **BUG-003 (Alto):** Mensagem de erro ao cadastrar professor com CPF inválido (Aceita "00"). *Ação: Adicionada regra de closure no controller rejeitando CPF com ≠ 11 dígitos ou dígitos todos iguais; adicionada máscara JS (000.000.000-00) em [[resources/views/master/cadastrar.blade.php]].*
- [x] **BUG-006 (Alto):** Horário exibido incorretamente na confirmação de presença por link. *Ação: Timezone alterado de `UTC` para `America/Sao_Paulo` via `env('APP_TIMEZONE')` em [[config/app.php]].*

## 🤝 Fluxo de Trabalho (Regras Obsidian)
1. Antes de iniciar uma tarefa, adicione a tag `[IN PROGRESS]` ao lado do bug correspondente.
2. Após finalizar e testar, marque o checkbox `[x]` e adicione um link para o arquivo alterado (ex: `[[PresencaController.php]]`).
3. Não altere os arquivos do domínio do outro agente sem aviso prévio.

---

## ✅ Encerramento — 2026-04-13

Todos os 6 bugs foram corrigidos. Divisão final:

| Bug | Responsável | Arquivo(s) alterado(s) |
|-----|-------------|------------------------|
| BUG-002 | Gemini CLI | [[resources/views/master/presenca.blade.php]] |
| BUG-004 | Gemini CLI | [[resources/views/master/cadastrar.blade.php]], [[resources/views/solicitar-acesso.blade.php]], [[public/js/theme.js]] |
| BUG-005 | Gemini CLI | [[resources/views/index.blade.php]] |
| BUG-001 | Claude Code | [[app/Http/Controllers/MasterCadastroController.php]], [[resources/views/master/cadastrar.blade.php]] |
| BUG-003 | Claude Code | [[app/Http/Controllers/MasterCadastroController.php]], [[resources/views/master/cadastrar.blade.php]] |
| BUG-006 | Claude Code | [[config/app.php]] |