# Revisão das GitHub Actions

Análise do pipeline atual ([`.github/workflows/laravel.yml`](../.github/workflows/laravel.yml)) e levantamento de melhorias e de novas actions. Nada aqui foi ativado: os trechos de código são propostas prontas para virar Pull Request.

## Como o pipeline é hoje

Um único workflow, "Smart Attendance — CI/CD", disparado em push e Pull Request para a `main` (e manualmente), com `concurrency` cancelando execuções antigas e `permissions: contents: read`. Seis módulos:

| Módulo | O que faz |
|---|---|
| 1 · Configuração | Instala dependências, compila o Vite, gera relatórios de `composer outdated`, `npm outdated` e `npm audit` |
| 2 · Banco de dados | Migrations, seed duas vezes (idempotência), rollback e nova migração, em PostgreSQL 17 |
| 3 · Segurança | `composer audit`, testes das camadas de segurança, `route:list` |
| 4 · Classes | Testes de login, isolamento entre painéis e cadastros |
| 5 · Testes finais | Suíte completa |
| 6 · Trabalho completo | Gera o relatório em PDF (só fora de Pull Requests) |

## O que está bom

- Banco PostgreSQL 17 real em contêiner, o mesmo da produção, em vez de só SQLite.
- Sem *secrets* no pipeline: roda em Pull Requests vindos de forks sem expor nada.
- `permissions` mínimas e `concurrency` para não gastar minutos com commits já substituídos.
- Auditoria de dependências dentro do pipeline.
- Relatório em PDF por execução, útil para a entrega do Projeto Integrador.

## Problemas encontrados

| # | Problema | Efeito | Prioridade |
|---|---|---|---|
| A1 | Actions referenciadas por *tag* (`@v5`, `@v2`), inclusive a de terceiros `shivammathur/setup-php@v2` | Uma tag movida ou comprometida muda o que roda no pipeline | Alta |
| A2 | Falta um `dependabot.yml` | Dependências e as próprias actions envelhecem sem aviso; hoje há avisos abertos em `composer audit` e `npm audit` ([falhas de segurança](falhas-de-seguranca.md)) | Alta |
| A3 | `npm audit` termina com `\|\| true` e nunca falha, enquanto `composer audit` falha | Tratamento inconsistente: as vulnerabilidades npm passam sem ninguém ver | Média |
| A4 | Os módulos 2 a 5 repetem `composer install`, `npm ci` e `npm run build` quatro vezes | Minutos desperdiçados; o módulo 5 já roda a suíte inteira, que inclui os testes dos módulos 3 e 4 | Média |
| A5 | O módulo 6 escreve "PASSOU" fixo na tabela | Só roda se os módulos passaram (`needs`), mas o texto não reflete o resultado real | Baixa |
| A6 | Só PHP 8.3 no CI; o `composer.json` exige `^8.2.35` | Quebra em 8.2 ou 8.4 passa despercebida | Média |
| A7 | Sem `timeout-minutes` | Um job travado consome até 6 horas | Média |
| A8 | O Laravel Pint está instalado, mas não roda no CI (e não há `pint.json`) | Estilo de código não é verificado | Média |
| A9 | Sem análise estática (PHPStan/Larastan) | Erros de tipo e código morto só aparecem em execução | Média |
| A10 | Sem verificação de segredos vazados | Uma chave comitada por engano passaria | Média |
| A11 | O nome diz "CI/CD" mas não há *deploy* no workflow | Confusão; o deploy é feito fora (CLI da Vercel) | Baixa |
| A12 | Cache do Composer sem `restore-keys` | Qualquer mudança no `composer.lock` descarta todo o cache | Baixa |

## Melhorias no workflow atual

1. **Fixar as actions por SHA**, com o número da versão em comentário. O Dependabot atualiza os dois juntos:
   ```yaml
   - uses: actions/checkout@<sha-completo> # v5
   ```
2. **Instalar uma vez e reaproveitar.** Um job `configuracao` instala e compila; os demais baixam o resultado como artefato, ou então juntar os módulos 3, 4 e 5 num único job com passos separados (a suíte completa já cobre os filtros).
3. **Matriz de PHP:** `php-version: ['8.2', '8.3', '8.4']` no módulo de testes finais, com `fail-fast: false`.
4. **`timeout-minutes: 15`** em cada job.
5. **Tornar o `npm audit` consistente:** `npm audit --audit-level=high` e falhar, ou manter informativo mas dizer isso no relatório.
6. **Cache com `restore-keys`:**
   ```yaml
   key: composer-${{ hashFiles('composer.lock') }}
   restore-keys: composer-
   ```
7. **Relatório PDF com o resultado real:** usar `${{ needs.modulos.result }}` em vez de "PASSOU" fixo.
8. **Renomear** o workflow para "CI" enquanto não houver deploy nele.

## Novas actions sugeridas

### 1. Dependabot (alta prioridade)

`.github/dependabot.yml`:

```yaml
version: 2
updates:
  - package-ecosystem: composer
    directory: /
    schedule: { interval: weekly }
    open-pull-requests-limit: 5
    groups:
      symfony: { patterns: ["symfony/*"] }
  - package-ecosystem: npm
    directory: /
    schedule: { interval: weekly }
    groups:
      dev-tools: { dependency-type: development }
  - package-ecosystem: github-actions
    directory: /
    schedule: { interval: monthly }
```

### 2. Revisão de dependências em Pull Requests

`.github/workflows/dependency-review.yml`: bloqueia o PR que *introduz* pacote vulnerável.

```yaml
name: Revisão de dependências
on: pull_request
permissions: { contents: read }
jobs:
  revisar:
    runs-on: ubuntu-latest
    timeout-minutes: 5
    steps:
      - uses: actions/checkout@v5
      - uses: actions/dependency-review-action@v4
        with: { fail-on-severity: high }
```

### 3. Estilo de código (Pint) e análise estática

`.github/workflows/qualidade.yml`:

```yaml
name: Qualidade de código
on:
  pull_request:
  push: { branches: [main] }
permissions: { contents: read }
jobs:
  pint:
    runs-on: ubuntu-latest
    timeout-minutes: 10
    steps:
      - uses: actions/checkout@v5
      - uses: shivammathur/setup-php@v2
        with: { php-version: '8.3', tools: composer:v2 }
      - run: composer install --no-interaction --no-progress --prefer-dist
      - run: vendor/bin/pint --test
```

Para o PHPStan: `composer require --dev larastan/larastan` e um job `vendor/bin/phpstan analyse --memory-limit=1G`, começando no nível 3 e subindo aos poucos.

### 4. Verificação de segredos

```yaml
# .github/workflows/segredos.yml
name: Segredos
on: [pull_request, push]
permissions: { contents: read }
jobs:
  gitleaks:
    runs-on: ubuntu-latest
    timeout-minutes: 5
    steps:
      - uses: actions/checkout@v5
        with: { fetch-depth: 0 }
      - uses: gitleaks/gitleaks-action@v2
        env: { GITHUB_TOKEN: "${{ secrets.GITHUB_TOKEN }}" }
```

Ative também **Secret scanning** e **Push protection** em *Settings → Code security*. Elas são grátis em repositórios públicos.

### 5. Análise de código (CodeQL) só para JavaScript

O CodeQL não analisa PHP. Para o JavaScript do projeto (`public/js`, `resources/js`) vale um workflow padrão com `languages: javascript-typescript`. Para o PHP, a cobertura vem de PHPStan, Pint e dos testes de segurança já existentes.

### 6. Teste de ponta a ponta com Playwright (média prioridade)

O fluxo principal (professor abre a chamada, aluno escaneia, presença aparece) hoje só é testado por requisições HTTP. Um teste em navegador real pegaria problemas como violações de CSP e falhas do JavaScript do QR rotativo, que o PHPUnit não enxerga. Rodar no PR contra o app em SQLite (`php artisan serve`) e guardar *screenshots* de desktop e celular como artefato.

### 7. Verificação de cabeçalhos de segurança após o deploy

Depois que o deploy de *preview* estiver conectado, um job simples com `curl -sI` confere `Content-Security-Policy`, `X-Frame-Options` e `X-Content-Type-Options` na URL de preview e falha se algum sumir.

### 8. Organização do repositório

- **Título do PR em formato de commit** (`amannn/action-semantic-pull-request`), alinhado ao `CONTRIBUTING.md`.
- **Labeler** (`actions/labeler`) que aplica `docs`, `tests`, `ci` conforme os arquivos mudados, combinando com as etiquetas dos modelos de issue.
- **Release Drafter** para montar o texto do `CHANGELOG` a partir dos PRs, quando o projeto passar a numerar versões.
- **Stale** (`actions/stale`) para issues paradas, hoje são 16 abertas.

## Ordem sugerida

| Ordem | Item | Esforço |
|---|---|---|
| 1 | Dependabot | 5 minutos |
| 2 | Resolver os avisos de `composer audit` e `npm audit` | 30 minutos |
| 3 | Dependency review + segredos | 15 minutos |
| 4 | Fixar actions por SHA, `timeout-minutes`, `restore-keys` | 20 minutos |
| 5 | Pint e matriz de PHP | 1 hora (o Pint pode reformatar muito código na primeira vez) |
| 6 | Reaproveitar a instalação entre módulos | 1 hora |
| 7 | PHPStan | meio dia, em níveis |
| 8 | Playwright de ponta a ponta | meio dia |

## Deploy automático na Vercel

O projeto `smart-attendance` já existe na Vercel (conta `jooaoleonardi's projects`), com deploy de produção, mas **sem repositório Git ligado**: os deploys saem pela CLI. Ligar o repositório dá *preview* automático em cada Pull Request e deploy de produção a cada merge na `main`, sem precisar de nenhum workflow. Veja o passo a passo em [Deploy](deploy.md#deploy-automático-pelo-github).
