# Pacote de 7 funcionalidades: plano de implementação

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Justificativa de faltas, correção de presença, SMTP institucional, 2FA TOTP, gráficos, edição/exclusão no Master e exportação .xlsx.

**Architecture:** Abono e correção viram linhas em `presencas` com a coluna `origem`, para que o `SituacaoAcademica` continue sendo a única regra. A auditoria fica em `presenca_ajustes`. O 2FA intercepta o login depois da senha e antes do `Auth::login`.

**Tech Stack:** Laravel 12, PHP 8.2+, PostgreSQL (testes em SQLite memória), Blade + Tailwind 4, `pragmarx/google2fa`, `openspout/openspout`, `league/flysystem-aws-s3-v3`, Chart.js 4.4.x vendorizado.

**Spec:** `docs/superpowers/specs/2026-11-15-pacote-novas-funcionalidades-design.md`

## Global Constraints

- Branch `claude/pacote-funcionalidades`; commits em Conventional Commits em português; PR do fork `jooaoleonardi/smart-attendance` para `main`.
- Nunca consultar coluna criptografada; usar `*_search` + `generateBlindIndex`.
- Todo `<script>`/`<style>` em bloco com `@nonce`; nenhuma CDN; JS novo em `public/js/vendor/` com hash em `VendorIntegrityTest`.
- Cores via `--pal-*`; classes `glass-card`, `pal-title`, `login-input`, etc.; funcionar em claro e escuro.
- Rotas do professor checam posse da matéria via `$professor->materias()->where('materias.id', $id)`; caso contrário, 403.
- Planilha nunca inclui CPF nem e-mail.
- Upload: `mimes:pdf,jpg,jpeg,png`, `max:5120`.
- `config('seguranca.2fa_obrigatorio_master')` lê `TWO_FACTOR_MASTER_OBRIGATORIO` (padrão `true`); em `phpunit.xml`, `false`, para não quebrar os testes atuais do Master; os testes de 2FA ligam explicitamente.

## Review Focus

1. Login com "lembrar-me" e 2FA ativo: o cookie de lembrar não pode ser emitido antes do código, senão o 2FA é contornado. Teste: `test_lembrar_me_so_apos_codigo_2fa`.
2. Professor A tentando corrigir presença, decidir justificativa ou baixar atestado de matéria do professor B: espera 403. Testes em cada task.
3. Aluno justificando aula em que esteve presente, ou aula de outra matéria: espera 422. Teste: `test_nao_justifica_aula_com_presenca`.
4. Edição no Master com e-mail/CPF/RA já usado por outro cadastro: espera 422, e o blind index é atualizado quando muda. Teste: `test_editar_aluno_email_duplicado`.
5. Exportação sem nenhuma presença: gera planilha só com o cabeçalho, sem erro 500. Teste: `test_exportar_vazio`.

---

### Task 1: Origem da presença, auditoria e correção pelo professor

**Files:**
- Create: `database/migrations/2026_11_15_000001_add_origem_to_presencas_table.php`, `database/migrations/2026_11_15_000002_create_presenca_ajustes_table.php`, `app/Models/PresencaAjuste.php`, `app/Support/AulasDaMateria.php`, `app/Http/Controllers/CorrecaoPresencaController.php`, `resources/views/professor/gerenciar/chamadas.blade.php`, `database/factories/PresencaFactory.php`
- Modify: `app/Models/Presenca.php` (fillable `origem`), `routes/web.php` (grupo `gerenciar`), `resources/views/professor/gerenciar/materia.blade.php` (link "Chamadas")
- Test: `tests/Feature/CorrecaoPresencaTest.php`, `tests/Unit/AulasDaMateriaTest.php`

**Interfaces:**
- Produces: `AulasDaMateria::listar(int $materiaId): Collection` de objetos `{codigo_aula, data_aula, horario, semestre, professor_id, presentes:int}` ordenados por `data_aula` desc; `AulasDaMateria::faltasDoAluno(int $materiaId, int $alunoId): Collection` (mesmo formato, sem `presentes`); `AulasDaMateria::aula(int $materiaId, string $codigo): ?object`.
- Produces: `PresencaAjuste` com campos `presenca_id, materia_id, aluno_id, codigo_aula, acao, motivo, autor_tipo, autor_id`; constantes `Presenca::ORIGEM_QR='qr'`, `ORIGEM_CORRECAO='correcao'`, `ORIGEM_JUSTIFICATIVA='justificativa'`.
- Routes: `GET professor/gerenciar/{materia_id}/chamadas` → `professor.chamadas`; `GET .../chamadas/{codigo_aula}` → `professor.chamadas.show`; `POST .../chamadas/{codigo_aula}/presenca` (`aluno_id`, `presente` bool, `motivo` required string max 500) → `professor.chamadas.presenca`.

- [ ] Step 1: testes falhando: `test_professor_marca_presenca_com_motivo` (cria `Presenca` com `origem=correcao` e `PresencaAjuste acao=marcou`), `test_professor_desmarca_presenca` (apaga a presença; ajuste `desmarcou` com `presenca_id` preservado), `test_motivo_obrigatorio` (422), `test_professor_de_outra_materia_recebe_403`, `test_aluno_nao_matriculado_422`, `test_aula_inexistente_404`, `test_correcao_altera_situacao` (faltas do `SituacaoAcademica::doAluno` caem de 1 para 0). Unit: `AulasDaMateriaTest` (lista distinct por código; faltas exclui aulas com presença).
- [ ] Step 2: `php artisan test --filter=CorrecaoPresenca` → FAIL.
- [ ] Step 3: migrations (`origem` string(20) default `qr`; ajustes com FKs `materia_id`, `aluno_id` cascade, `presenca_id` nullable sem FK, índice (`materia_id`,`codigo_aula`)); serviço; controller (marcar copia `data_aula/semestre/horario/professor_id` da aula; dentro de `DB::transaction`; ignora se já está no estado pedido); view com lista de alunos, chave e motivo.
- [ ] Step 4: `php artisan test` → todos PASS.
- [ ] Step 5: commit `feat(presenca): professor corrige presença com motivo e auditoria`.

### Task 2: Justificativa de faltas

**Files:**
- Create: migration `2026_11_15_000003_create_justificativas_table.php`, `app/Models/Justificativa.php`, `app/Http/Controllers/JustificativaAlunoController.php`, `app/Http/Controllers/JustificativaProfessorController.php`, `app/Mail/JustificativaDecididaMail.php`, `resources/views/emails/justificativa-decidida.blade.php`, `resources/views/aluno/justificativas.blade.php`, `resources/views/professor/justificativas.blade.php`
- Modify: `config/filesystems.php` (disco `justificativas`), `.env.example` (`JUSTIFICATIVAS_DISK`, `SUPABASE_S3_*`), `composer.json` (`league/flysystem-aws-s3-v3`), `routes/web.php`, `resources/views/aluno/home.blade.php` e `professor/home.blade.php` (atalhos + contador de pendentes)
- Test: `tests/Feature/JustificativaTest.php`

**Interfaces:**
- Consumes: `AulasDaMateria::faltasDoAluno`, `AulasDaMateria::aula`, `Presenca::ORIGEM_JUSTIFICATIVA`, `PresencaAjuste`.
- Produces: `Justificativa` (`status` em `pendente|aprovada|recusada`, scope `pendentes()`), `JustificativaDecididaMail(Justificativa $j)`.
- Disco: `justificativas` = `local` privado em `storage/app/private/justificativas` quando `JUSTIFICATIVAS_DISK=local`; `s3` com `endpoint=SUPABASE_S3_ENDPOINT`, `use_path_style_endpoint=true` quando `s3`. Helper `Justificativa::anexoHabilitado(): bool` (falso se o driver for `local` e `app()->environment('production')`).
- Routes aluno: `GET /aluno/justificativas` (`aluno.justificativas`), `POST /aluno/justificativas` (`materia_id`, `codigo_aula`, `motivo` 10..2000, `anexo` opcional) com `throttle:10,1`, `GET /aluno/justificativas/{justificativa}/anexo`.
- Routes professor: `GET /professor/justificativas` (`professor.justificativas`), `POST /professor/justificativas/{justificativa}/decidir` (`decisao` in `aprovar,recusar`, `comentario` nullable max 1000), `GET /professor/justificativas/{justificativa}/anexo`.

- [ ] Step 1: testes falhando: `test_aluno_envia_justificativa_com_anexo` (`Storage::fake('justificativas')`, arquivo salvo com nome UUID), `test_nao_justifica_aula_com_presenca` (422), `test_nao_justifica_duas_vezes` (422), `test_anexo_tipo_invalido_422` (`.exe`), `test_professor_aprova_cria_presenca_justificada` (presença `origem=justificativa`, ajuste `abonou`, e-mail enviado com `Mail::fake`), `test_professor_recusa_nao_cria_presenca`, `test_professor_outra_materia_403` (decidir e baixar anexo), `test_outro_aluno_nao_baixa_anexo_403`, `test_decidir_duas_vezes_422`.
- [ ] Step 2: rodar → FAIL.
- [ ] Step 3: `composer require league/flysystem-aws-s3-v3`; implementar. Aprovar dentro de `DB::transaction` com `lockForUpdate` na justificativa.
- [ ] Step 4: `php artisan test` → PASS.
- [ ] Step 5: commit `feat(justificativa): aluno justifica falta com atestado e professor decide`.

### Task 3: E-mail institucional

**Files:**
- Modify: `.env.example` (blocos comentados Microsoft 365 `smtp.office365.com:587` e Google Workspace `smtp.gmail.com:587`, `MAIL_SCHEME=smtp`), `routes/web.php`, `resources/views/master/home.blade.php` (card "E-mail")
- Create: `app/Http/Controllers/MasterEmailController.php`, `app/Mail/EmailTesteMail.php`, `resources/views/emails/teste.blade.php`
- Test: `tests/Feature/MasterEmailTest.php`

**Interfaces:**
- Route: `POST /dashboard/master/email/teste` (`master.email.teste`), `throttle:3,1`. Envia para o e-mail do Master logado com `Mail::send` síncrono; em `TransportExceptionInterface` volta com erro `Falha no envio: {mensagem sem credenciais}`; mostra `config('mail.default')`, host e porta (nunca usuário/senha).

- [ ] Step 1: testes: `test_master_envia_email_teste` (`Mail::fake`, `assertSent(EmailTesteMail::class)` para o e-mail do master), `test_professor_nao_acessa_email_teste`, `test_falha_smtp_mostra_erro_sem_senha` (mock do mailer lançando `TransportException` contendo a senha; resposta não contém a senha).
- [ ] Step 2: FAIL. Step 3: implementar. Step 4: PASS.
- [ ] Step 5: commit `feat(email): configuração SMTP institucional e e-mail de teste no Master`.

### Task 4: 2FA TOTP

**Files:**
- Create: migration `2026_11_15_000004_add_two_factor_columns.php` (3 tabelas), `config/seguranca.php`, `app/Traits/TemDoisFatores.php`, `app/Http/Controllers/DoisFatoresController.php`, `app/Http/Middleware/ExigirDoisFatores.php`, `app/Support/LoginPendente.php`, `resources/views/dois-fatores/desafio.blade.php`, `resources/views/dois-fatores/configurar.blade.php`
- Modify: `composer.json` (`pragmarx/google2fa`), modelos `AlunoModel`, `ProfessorModel`, `UsuarioMaster` (trait + casts encrypted + hidden), `AlunoLoginController`, `ProfessorLoginController`, `bootstrap/app.php` (alias `exigir-2fa`), `routes/web.php` (Master group + `/2fa/*`), `phpunit.xml` (`TWO_FACTOR_MASTER_OBRIGATORIO=false`), menus de perfil (link "Segurança da conta")
- Test: `tests/Feature/DoisFatoresTest.php`

**Interfaces:**
- `TemDoisFatores`: `temDoisFatores(): bool`, `gerarSegredoDoisFatores(): string`, `confirmarDoisFatores(string $codigo): bool`, `verificarCodigoDoisFatores(string $codigo): bool` (aceita TOTP com janela 1 ou consome código de recuperação), `desativarDoisFatores(): void`, `gerarCodigosRecuperacao(): array` (8 códigos `XXXXX-XXXXX`, guardados como JSON de hashes `hash('sha256')`).
- `LoginPendente::iniciar(string $guard, int $id, bool $lembrar, string $destino): void`, `::atual(): ?array` (expira em 300 s), `::concluir(Request $r): RedirectResponse` (faz `Auth::guard($guard)->login($user, $lembrar)`, regenera a sessão, define `login_time`).
- Login controllers: depois de `Hash::check` ok, se `$user->temDoisFatores()` → `LoginPendente::iniciar(...)` e redireciona para `dois-fatores.desafio`, sem chamar `login()`.
- Routes: `GET/POST /2fa/desafio` (`throttle:5,1`), `GET /2fa/configurar`, `POST /2fa/confirmar`, `POST /2fa/desativar` (exige senha atual), todas com `auth:professores,alunos,masters`, exceto desafio. Master: `POST /dashboard/master/{tipo}/{id}/resetar-2fa`.
- `ExigirDoisFatores`: no grupo do Master; se `config('seguranca.2fa_obrigatorio_master')` e o Master não tem 2FA confirmado → redirect `dois-fatores.configurar`.

- [ ] Step 1: testes: `test_login_com_2fa_pede_codigo`, `test_codigo_correto_conclui_login` (usa `Google2FA::getCurrentOtp`), `test_codigo_errado_nao_loga`, `test_codigo_recuperacao_funciona_uma_vez`, `test_lembrar_me_so_apos_codigo_2fa` (sem cookie `remember_*` antes do código), `test_desafio_expira_apos_5_minutos` (`travel(6)->minutes()`), `test_master_sem_2fa_redirecionado_quando_obrigatorio`, `test_configurar_exige_codigo_valido_para_ativar`, `test_desativar_exige_senha`, `test_master_reseta_2fa_de_professor`, `test_segredo_criptografado_no_banco`.
- [ ] Step 2: FAIL. Step 3: `composer require pragmarx/google2fa`; implementar; QR na tela de configurar com `qrcode.min.js` a partir da URI `otpauth://` (`Google2FA::getQRCodeUrl('Smart Attendance', $label, $secret)`, sendo `$label` o nome, nunca o e-mail).
- [ ] Step 4: `php artisan test` (suite inteira) → PASS.
- [ ] Step 5: commit `feat(seguranca): autenticação em dois fatores com app autenticador`.

### Task 5: Gráficos de presença

**Files:**
- Create: `public/js/vendor/chart.umd.min.js` (Chart.js 4.4.x, mesmo arquivo da cdnjs), `app/Support/EstatisticasTurma.php`, `resources/views/professor/gerenciar/graficos.blade.php`, `resources/views/master/graficos.blade.php`, `resources/js/graficos.js` ou script inline com `@nonce`
- Modify: `public/js/vendor/LEIA-ME.md`, `tests/Feature/VendorIntegrityTest.php` (hash), `routes/web.php`, `master/home.blade.php`, `professor/gerenciar/materia.blade.php`
- Test: `tests/Feature/GraficosTest.php`, `tests/Unit/EstatisticasTurmaTest.php`

**Interfaces:**
- Consumes: `AulasDaMateria::listar`, `SituacaoAcademica`.
- `EstatisticasTurma::daMateria(Materia $m): array{frequencia_por_aula: list<array{data:string, percentual:int}>, situacoes: array<string,int>, em_risco: list<array{nome:string, faltas:int, limite:int, status:string}>}`; `EstatisticasTurma::geral(): list<array{materia:string, frequencia_media:int, alunos:int}>`.
- Routes: `GET professor/gerenciar/{materia_id}/graficos` (view) e `.../graficos.json`; `GET dashboard/master/graficos` e `dashboard/master/graficos.json`.
- Gráficos com tabela alternativa (`<details>` com os números) para acessibilidade; cores lidas das variáveis CSS em tempo de execução.

- [ ] Step 1: testes: unit com 3 aulas e 2 alunos (frequência 100/50/50, situação e lista de risco); feature `test_json_professor_outra_materia_403`, `test_json_master_lista_turmas`, `test_vendor_chartjs_hash`.
- [ ] Step 2: FAIL. Step 3: implementar (baixar o arquivo da cdnjs e conferir SHA-512 com o publicado). Step 4: PASS.
- [ ] Step 5: commit `feat(dashboard): gráficos de frequência por turma para professor e Master`.

### Task 6: Editar e excluir cadastros no Master

**Files:**
- Create: `app/Http/Controllers/MasterEdicaoController.php`, `resources/views/master/editar.blade.php` (uma view, com `$tipo` em `aluno|professor|materia`)
- Modify: `routes/web.php`, `master/alunos.blade.php`, `master/professores.blade.php`, `master/materias.blade.php` (botões Editar/Excluir + `<dialog>` de confirmação)
- Test: `tests/Feature/MasterEdicaoTest.php`

**Interfaces:**
- Routes: `GET dashboard/master/{tipo}/{id}/editar` (`master.editar`), `PUT dashboard/master/{tipo}/{id}` (`master.atualizar`), `DELETE dashboard/master/{tipo}/{id}` (`master.excluir`); `tipo` com `whereIn(['aluno','professor','materia'])`.
- Validação de unicidade por blind index, ignorando o próprio id (regra fechada `Rule` customizada `UnicoPorBlindIndex(string $modelClass, string $coluna, ?int $ignorarId)`); campos editáveis: aluno `nome,email,cpf,ra`; professor `nome,email,cpf`; matéria `nome,total_aulas,codigo` (se existir no schema). Nunca `role`, `password` nem as colunas de 2FA.

- [ ] Step 1: testes: `test_editar_aluno_atualiza_blind_index` (busca por `email_search` novo encontra), `test_editar_aluno_email_duplicado` (422), `test_excluir_aluno_soft_delete` (`assertSoftDeleted`), `test_excluir_materia_soft_delete`, `test_professor_nao_edita` (redirect/403), `test_tipo_invalido_404`.
- [ ] Step 2: FAIL. Step 3: implementar. Step 4: PASS.
- [ ] Step 5: commit `feat(master): editar e excluir alunos, professores e matérias`.

### Task 7: Exportar relatórios em .xlsx

**Files:**
- Create: `app/Support/ExportadorPresencas.php`, `app/Http/Controllers/ExportacaoController.php`
- Modify: `composer.json` (`openspout/openspout`), `routes/web.php`, `professor/relatorios.blade.php` e `master/presenca.blade.php` (botão "Exportar planilha" que leva os filtros atuais)
- Test: `tests/Feature/ExportacaoTest.php`

**Interfaces:**
- Consumes: filtros iguais aos de `GerenciarMateriaController@relatorios` e `DashboardController@masterPresenca` (extrair a montagem da query para métodos reutilizáveis `filtrarRelatorioProfessor(Request, ProfessorModel): Builder` e `filtrarPresencasMaster(Request): Builder`).
- `ExportadorPresencas::baixar(Builder $presencas, string $nomeArquivo, ?Collection $materiasResumo = null): StreamedResponse`: aba "Presenças" (Aluno, RA, Matéria, Data da aula, Horário, Origem, Registrado em) e, se houver matérias, aba "Resumo" (Aluno, RA, Matéria, Presenças, Faltas, Limite, Frequência %, Situação) usando `SituacaoAcademica`. Lê em `chunk(500)`.
- Routes: `GET professor/gerenciar/relatorios/exportar` (`professor.relatorios.exportar`), `GET dashboard/master/presenca/exportar` (`master.presenca.exportar`), ambas com `throttle:10,1`.

- [ ] Step 1: testes: `test_professor_exporta_xlsx` (content-type `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`; abre com o `Reader` do OpenSpout e confere cabeçalho + 2 linhas), `test_exportacao_respeita_filtro_materia`, `test_exportacao_sem_cpf_email`, `test_exportar_vazio`, `test_professor_nao_exporta_presencas_de_outro`.
- [ ] Step 2: FAIL. Step 3: implementar. Step 4: PASS.
- [ ] Step 5: commit `feat(relatorios): exportar presenças em planilha Excel`.

### Task 8: Documentação, verificação e entrega

- [ ] Atualizar `docs/manual-do-usuario.md`, `docs/banco-de-dados.md`, `docs/regras-academicas.md` (abono conta como presença), `docs/seguranca.md` (2FA), `docs/deploy.md` + `docs/instalacao.md` (SMTP, Supabase Storage, `TWO_FACTOR_MASTER_OBRIGATORIO`), `CHANGELOG.md` em "Não lançado".
- [ ] `php artisan test` completo → PASS; `npm run build` sem erro; `vendor/bin/pint --test` nos arquivos novos.
- [ ] Verificação no navegador (desktop 1440 e celular 375, claro e escuro): login com 2FA, justificativa ponta a ponta, chamadas, gráficos, edição do Master, download da planilha.
- [ ] Push para o fork e PR para `main`; commit `docs: documenta justificativas, correções, 2FA, gráficos e exportação`.
