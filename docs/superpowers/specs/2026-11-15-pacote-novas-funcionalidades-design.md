# Design: pacote de 7 funcionalidades (nov/2026)

## Intenção

Fechar as lacunas que mais aparecem no uso real do Smart Attendance: falta que tinha motivo, presença que não registrou, e-mail que não sai, conta sem segunda camada de proteção, professor sem visão da turma, Master sem conseguir corrigir cadastro, e relatório que não sai em planilha. Prazo: entrega para 16/11/2026.

## Objetivos

1. **Justificativa de faltas**: o aluno escolhe uma aula em que faltou, escreve o motivo e anexa um atestado (PDF, JPG ou PNG, até 5 MB). O professor da matéria aprova ou recusa, com comentário opcional. Quando aprovada, a falta deixa de contar (abono).
2. **Correção de presença**: o professor marca ou desmarca a presença de um aluno numa aula já realizada da sua matéria, informando o motivo (obrigatório).
3. **E-mail institucional**: SMTP configurável por variáveis de ambiente, com exemplos para Microsoft 365 e Google Workspace. O Master ganha um botão "Enviar e-mail de teste". Os e-mails de primeiro acesso, de recuperação de senha e de decisão da justificativa usam esse servidor.
4. **2FA (TOTP)**: com app autenticador (Google Authenticator, Authy, Microsoft Authenticator). **Obrigatório para o Master**, opcional para professor e aluno, que ativam no próprio painel. São gerados 8 códigos de recuperação de uso único.
5. **Dashboard com gráficos**:
   - Professor, por matéria: frequência por aula (linha), situação da turma (rosca) e alunos em risco (lista).
   - Master: frequência média por turma (barras) e distribuição de situações.
6. **Editar e excluir no Master**: alunos, professores e matérias. A exclusão é reversível (soft delete, já existente), com confirmação. Ao editar e-mail, CPF ou RA, o blind index é recalculado e a unicidade é validada.
7. **Exportar em planilha (.xlsx)**: relatório de presenças do professor (respeita os filtros) e Central de Presenças do Master. Colunas: aluno, RA, matéria, data, horário e origem; e, na aba de resumo, frequência, faltas e situação de cada aluno.

## Fora do escopo

- Notificações por push ou WhatsApp.
- Abono parcial (meia falta) e justificativa de várias aulas de uma vez.
- 2FA por SMS ou por e-mail.
- Exclusão definitiva de cadastros pela tela (continua só por comando, por causa da LGPD).
- Restaurar cadastros excluídos pela tela (fica para depois).

## O que não pode acontecer (mesmo que "funcione")

- Uma tela mostrar número de faltas diferente de outra.
- Professor ver ou alterar presença, justificativa ou atestado de matéria que não é dele (IDOR).
- O atestado ficar acessível por URL pública.
- O Master ficar trancado fora do sistema ao ativar o 2FA.
- Os PII irem para a planilha ou para os logs sem necessidade: a planilha só traz nome e RA, nunca CPF nem e-mail.

## Restrições

- Laravel 12, Blade, Tailwind 4, PostgreSQL. Produção na Vercel (disco somente leitura, exceto `/tmp`) com Supabase.
- CSP com nonce e sem CDN: toda biblioteca JS vai para `public/js/vendor/`, com o hash conferido em `VendorIntegrityTest`.
- Campos PII criptografados: buscas só via `*_search`.
- Toda conta de frequência e situação passa pelo `App\Support\SituacaoAcademica`.
- Visual: glassmorphism, variáveis `--pal-*`, modo claro e escuro, conforme o `.cursorrules`.
- Docs em `docs/` e entrada em "Não lançado" no `CHANGELOG.md`.

## Abordagens consideradas

**A. Abono e correção na própria tabela `presencas`** (escolhida). Coluna nova `origem` (`qr` | `correcao` | `justificativa`). Abonar ou corrigir cria um registro de presença com o mesmo `codigo_aula` da aula. Desmarcar apaga o registro. Toda alteração fica na tabela `presenca_ajustes` (quem, quando, ação, motivo). Prós: o `SituacaoAcademica` e as quatro telas continuam certos sem mudar a regra. Contras: o professor pode "desfazer" uma presença registrada por QR, o que é intencional e fica auditado.

**B. Tabela `abonos` subtraída das faltas.** Mantém `presencas` intacta, mas cada consulta de presenças precisaria descontar os abonos: quatro lugares para errar. Descartada.

**C. Atestado gravado no banco (bytea).** Não precisa de configuração, mas ocupa a cota de 500 MB do Supabase gratuito. Descartada em favor de um disco do Laravel (local privado em desenvolvimento, Supabase Storage via S3 em produção).

Bibliotecas: `pragmarx/google2fa` (2FA), `openspout/openspout` (xlsx), `league/flysystem-aws-s3-v3` (disco S3 do Supabase) e Chart.js 4 (vendorizado).

## Desenho

### Banco (migrations novas, todas reversíveis)
- `presencas`: `origem` string(20), padrão `'qr'`, NOT NULL. Os registros existentes viram `qr`.
- `presenca_ajustes`: `id`, `presenca_id` (nullable, porque a presença pode ter sido apagada), `materia_id` FK, `aluno_id` FK, `codigo_aula`, `acao` (`marcou` | `desmarcou` | `abonou`), `motivo` text, `autor_tipo` + `autor_id`, `created_at`. Índice em (`materia_id`, `codigo_aula`).
- `justificativas`: `id`, `aluno_id` FK, `materia_id` FK, `codigo_aula`, `data_aula`, `motivo` text, `anexo_path` (nullable), `anexo_nome`, `anexo_mime`, `status` (`pendente` | `aprovada` | `recusada`), `comentario_professor` (nullable), `decidida_por` (nullable), `decidida_em` (nullable), timestamps. Índice único em (`aluno_id`, `codigo_aula`), ou seja, uma justificativa por falta.
- `alunos`, `professores`, `usuario_masters`: `two_factor_secret` (text, cast encrypted), `two_factor_recovery_codes` (text, cast encrypted), `two_factor_confirmed_at` (timestamp, nullable).

### Fluxos
- **Aulas em que o aluno faltou** = os `codigo_aula` distintos da matéria em que ele não tem presença. Serviço `App\Support\AulasDaMateria` (lista de aulas com data e horário, a partir de `presencas`).
- **Justificativa**: o aluno abre "Justificar falta" no painel → escolhe a matéria e a aula → envia. O professor vê "Justificativas pendentes" em `/professor/justificativas` → aprova (cria a presença `origem=justificativa` e o ajuste `abonou`) ou recusa. O aluno recebe e-mail com a decisão (fila `sync` se não houver worker). O anexo é baixado só por rota autenticada (aluno dono ou professor da matéria), via `Storage::download`.
- **Correção**: na tela da matéria do professor, aba "Chamadas" → lista das aulas → cada aula mostra os alunos com uma chave liga/desliga e um campo de motivo → POST `/professor/gerenciar/{materia}/aulas/{codigo}/presenca`.
- **2FA**: depois da senha correta, se a conta tem 2FA confirmado, o login não é concluído: o id fica na sessão (`2fa:pendente`, por 5 minutos) e o usuário vai para `/2fa/desafio` (código de 6 dígitos ou código de recuperação; limite de 5 tentativas por minuto). Master sem 2FA confirmado é levado a `/2fa/configurar` antes de entrar no painel (middleware `exigir-2fa` no grupo do Master). A configuração mostra o QR e o segredo em texto, e pede um código para confirmar antes de ativar.
- **E-mail de teste**: POST `/dashboard/master/email/teste` envia para o e-mail do Master logado e mostra sucesso ou o erro do SMTP, sem a senha.
- **Gráficos**: endpoints JSON `/professor/gerenciar/{materia}/graficos` e `/dashboard/master/graficos`; os números vêm do `SituacaoAcademica`.
- **Planilha**: GET `/professor/gerenciar/relatorios/exportar` (mesmos filtros) e `/dashboard/master/presenca/exportar`. Stream com OpenSpout, nome `presencas-AAAA-MM-DD.xlsx`.
- **Master editar/excluir**: `GET/PUT /dashboard/master/{alunos|professores|materias}/{id}/editar` e `DELETE .../{id}`. Botões nas listagens existentes. Confirmação com `<dialog>` acessível.

### Segurança
- Toda rota do professor verifica se a matéria é dele (padrão já usado: `$professor->materias()->where(...)`).
- Upload: `mimes:pdf,jpg,jpeg,png`, `max:5120`, nome gerado (UUID), disco privado.
- Validação por Form Request com `$fillable` explícito (sem mass assignment de `role` e campos de 2FA).
- Rate limit em: desafio 2FA, envio de justificativa, e-mail de teste e exportação.

## Decisões minhas (para você conferir)

- Gráficos para professor (por matéria) e Master (todas as turmas); o aluno não ganha gráficos novos.
- O professor pode desmarcar uma presença registrada por QR, com motivo obrigatório e auditoria.
- Uma justificativa por falta. Se for recusada, o aluno não reenvia: fala com o professor, que pode corrigir direto.
- Prazo para justificar: sem limite nesta versão.
- O 2FA do aluno e do professor é opcional; o Master pode "resetar" o 2FA de um usuário pela tela de edição.
- Sem o Supabase Storage configurado em produção, o envio de anexo fica desligado e a justificativa vai só com texto, com um aviso na tela, em vez de quebrar.

## O que fica para quem implementar

Nomes de classes e views, organização dos testes, textos de interface (seguindo `design:ux-copy`), layout dos gráficos dentro do design system atual.
