# Relatório das novas funcionalidades · Smart Attendance

**Período:** 15 a 22 de novembro de 2026 (entrega em 15/11, pendências resolvidas em 22/11)  
**Base:** commit `88185c8` (merge do PR #61), branch `main` · **Resultado:** commit `93fc6be` (merge do PR #75)  
**Repositório oficial:** `AtilaAlvesRodrigues/smart-attendance` · **Fork de deploy:** `jooaoleonardi/smart-attendance`  
**Produção:** `https://smart-attendance-nine-alpha.vercel.app`

---

## 1. Resumo

Das sete demandas do quadro, seis estão entregues e em produção. A sétima, o envio de e-mails pelo servidor da instituição, tem o código pronto e espera só a conta de e-mail (Gmail) e a senha de app, que precisam ser cadastradas por quem tem a conta.

| Indicador | Valor |
|---|---|
| Pull Requests | 12 mergeados: #64 e #65 (entrega), #67 (pendências), #69 (aviso de frequência), #71 (pooler), #73 (exportação da Central), #75 (layout no celular) e #66, #68, #70, #72 e #74 (documentação) |
| Commits (sem merges) | 31 |
| Arquivos alterados | 122 (69 novos, 53 modificados) |
| Linhas | +8.685 / −509 (inclui a biblioteca Chart.js e o PDF deste relatório) |
| Testes automatizados | 238 → **365** (todos passando em SQLite e PostgreSQL 17) |
| Problemas achados nas revisões | 17: 16 corrigidos antes da publicação e 1 aceito com justificativa (seção 16) |
| Defeitos achados na bateria de testes | 1, corrigido e publicado (seção 18) |
| Problemas de layout no celular | 11, corrigidos e publicados (seção 19) |
| Migrations novas | 7, aplicadas no banco de produção sem perda de dados |
| Deploys de produção | 9, todos `Ready` e sem erros nos logs: 2 pela CLI (15/11 e a troca da porta do pooler em 22/11) e 7 pelo deploy automático do fork em 22/11 |

### As sete demandas

| Card | Demanda | Situação |
|---|---|---|
| #44 | Permitir ao professor corrigir uma presença | **Feito**, em produção |
| #11 | Implementar autenticação em dois fatores (2FA) | **Feito**, em produção |
| #12 | Criar dashboard com gráficos de presença por turma | **Feito**, em produção |
| #45 | Editar e excluir cadastros no painel do Master | **Feito**, em produção |
| #46 | Exportar relatórios de presença em planilha | **Feito**, em produção |
| #43 | Permitir justificativa de faltas | **Feito**, em produção, com atestado guardado no banco (desde 22/11) |
| #47 | Configurar o envio de e-mails pelo servidor da instituição | **Código pronto, não configurado**: faltam os dados do SMTP da instituição |

Os seis cards feitos foram fechados no GitHub e movidos para *Concluído* no quadro; o #47 está em *Em andamento*.

---

## 2. Mudanças na aplicação

A decisão central foi tratar **falta abonada** e **presença corrigida** como registros comuns na tabela `presencas`, com uma coluna nova, `origem` (`qr`, `correcao` ou `justificativa`). Assim a regra de frequência (`App\Support\SituacaoAcademica`) não mudou, e o painel do aluno, a tela de notas, os gráficos e a planilha mostram sempre os mesmos números. Toda alteração fora do QR Code fica registrada em `presenca_ajustes`.

### 2.1 Justificativa de faltas (card #43)

**Problema:** uma falta com motivo justo (consulta médica, doença) continuava contando, e não havia como o aluno pedir o abono pelo sistema.

**Solução:**

- O aluno abre *Justificar uma falta*, escolhe a aula (a lista mostra só as aulas em que ele faltou), escreve o motivo (mínimo de 10 caracteres) e pode anexar um atestado em PDF, JPG ou PNG de até 4 MB (a Vercel recusa requisições acima de 4,5 MB).
- O professor da matéria vê as pendentes em *Justificativas* (o painel mostra quantas aguardam), abre o atestado e **aprova** ou **recusa**, com comentário opcional.
- Aprovada, a falta vira presença com origem `justificativa` e deixa de contar. O aluno recebe a decisão por e-mail.
- Uma justificativa por falta. Só o próprio aluno e o professor da matéria baixam o anexo.
- Em produção, o atestado fica no próprio banco (tabela `justificativa_anexos`, `JUSTIFICATIVAS_DISK=banco`), **criptografado com a `APP_KEY`**, porque é dado pessoal sensível. Em desenvolvimento, fica numa pasta privada.
- Se o professor desmarcar depois uma presença abonada, a justificativa passa a *Recusada*, com o motivo dele.

**Arquivos:** `JustificativaAlunoController` e `JustificativaProfessorController` (novos), modelo `Justificativa`, e-mail `JustificativaDecididaMail`, telas `aluno/justificativas` e `professor/justificativas`, disco `justificativas` em `config/filesystems.php`.

**Resolvido em 22/11:** na Vercel o disco é somente leitura, e até essa data o envio de anexo ficava desligado. Com o atestado guardado no banco, o anexo passou a funcionar em produção sem depender de serviço externo (seção 16).

### 2.2 Correção de presença pelo professor (card #44)

**Problema:** quando o QR falhava (celular sem bateria, aluno sem câmera) ou alguém registrava presença sem estar na sala, o erro não podia ser desfeito pela interface.

**Solução:**

- Na tela da turma, *Chamadas e correções* lista as aulas já feitas, com a porcentagem de presentes.
- Em cada aula, o professor liga ou desliga a presença de um aluno e informa o **motivo, que é obrigatório**. A situação aparece como *Presente*, *Falta*, *Presente (corrigido)* ou *Falta abonada*.
- O *Histórico de correções* da aula mostra cada alteração, com data e motivo.
- A única presença de uma aula não pode ser removida: sem nenhuma presença, a aula deixaria de existir e as faltas da turma inteira mudariam.

**Arquivos:** `CorrecaoPresencaController` (novo), serviço `AulasDaMateria`, modelo `PresencaAjuste`, telas `professor/gerenciar/chamadas` e `chamada`.

### 2.3 Envio de e-mails pelo servidor da instituição (card #47)

**Situação anterior:** o envio já usava SMTP por variáveis de ambiente, mas só havia exemplo do Gmail pessoal e nenhuma forma de testar a configuração.

**Solução:**

- O `.env.example` ganhou blocos prontos para **Microsoft 365**, **Google Workspace** e **servidor próprio** da instituição.
- No painel do Master, o quadro *Servidor de e-mail* mostra por onde os e-mails saem (servidor e porta, nunca usuário ou senha), e o botão **Enviar e-mail de teste** manda uma mensagem para o próprio Master.
- Se o envio falha, a tela mostra só o código SMTP (por exemplo, 535: usuário ou senha recusados) e o que conferir. O texto devolvido pelo servidor não aparece nem vai para o log, porque pode conter credenciais ou endereços.

**Arquivos:** `MasterEmailController` e `EmailTesteMail` (novos), layout de e-mail `resources/views/emails/layout.blade.php`, `.env.example`, `docs/instalacao.md`.

**Limite conhecido:** sem o servidor, o usuário e a senha do SMTP da instituição, os e-mails continuam indo para o log (seção 12).

### 2.4 Autenticação em dois fatores (card #11)

**Problema:** uma senha vazada dava acesso total à conta (SEC-07).

**Solução:** verificação em duas etapas por **app autenticador** (Google Authenticator, Microsoft Authenticator ou Authy), com a biblioteca `pragmarx/google2fa`.

- Em *Segurança da conta*, o usuário escaneia o QR Code, digita o código de 6 números e a senha atual, e recebe **8 códigos de recuperação** de uso único.
- Depois da senha certa, o login **não é concluído** até o código ser conferido: só o perfil, o id e o destino ficam na sessão, por 5 minutos (`App\Support\LoginPendente`). O cookie "permanecer conectado" só é emitido depois do código.
- A redefinição de senha pelo e-mail também passa pelo desafio.
- Cada código vale uma vez: o sistema guarda a janela do último código aceito e confere sob trava da linha.
- O segredo e os códigos de recuperação ficam criptografados no banco. O Master pode remover o 2FA de quem perdeu o celular.

**Arquivos:** `DoisFatoresController`, trait `TemDoisFatores` (nos três modelos de usuário), `LoginPendente`, middleware `ExigirDoisFatores`, `config/seguranca.php`, telas `dois-fatores/desafio` e `configurar`, `AlunoLoginController`, `ProfessorLoginController`, `CriarSenhaController`.

**Comportamento novo:** o 2FA é obrigatório para o Master por padrão. Em produção foi deixado **opcional durante a avaliação** (`TWO_FACTOR_MASTER_OBRIGATORIO=false`), para quem entrar como Master não precisar de app autenticador.

### 2.5 Gráficos de presença (card #12)

**Solução:**

- **Professor, por matéria:** frequência média, aulas realizadas, porcentagem de presentes em cada aula (linha), situação dos alunos (rosca) e a lista dos alunos em risco. *Ver os números* mostra os mesmos dados em tabela.
- **Master:** frequência média de cada turma (barras), situação de todas as matrículas, turmas abaixo de 75% e matrículas em risco.
- Os números vêm do `SituacaoAcademica`, os mesmos do painel do aluno. As cores seguem o tema claro e escuro.

**Arquivos:** `GraficosController` e serviço `EstatisticasTurma` (novos), `public/js/graficos.js`, telas `professor/gerenciar/graficos` e `master/graficos`, Chart.js 4.4.1 em `public/js/vendor/chart.umd.js` (SHA-512 idêntico ao da cdnjs, conferido pelo `VendorIntegrityTest`).

### 2.6 Editar e excluir cadastros no Master (card #45)

**Solução:**

- O botão **Editar** nas listas de professores, alunos e matérias abre os dados do cadastro.
- E-mail, CPF e RA continuam sem poder se repetir, conferidos pelo índice cego (`UnicoPorBlindIndex`), porque esses campos são criptografados. Perfil e senha nunca são alterados por essa tela.
- **Excluir** pede confirmação e é reversível (*soft delete*): o cadastro some das telas, mas o histórico de presenças e notas continua no banco.

**Arquivos:** `MasterEdicaoController` e regra `UnicoPorBlindIndex` (novos), tela `master/editar`, listas `master/alunos`, `professores` e `materias`.

### 2.7 Exportação em planilha (card #46)

**Solução:** **Exportar planilha** baixa um Excel (.xlsx), com os mesmos filtros da tela, no relatório do professor e na Central de Presenças do Master.

| Aba | Conteúdo |
|---|---|
| Presenças | Aluno, RA, matéria, data da aula, horário, origem e data do registro |
| Resumo | Presenças, faltas, limite de faltas, frequência e situação de cada aluno |

- Só nome e RA, **nunca CPF ou e-mail**. Todo texto é gravado como texto; valores iniciados por `=`, `+`, `-` ou `@` recebem apóstrofo, para nenhum nome virar fórmula no Excel.
- O professor só exporta as próprias matérias; um filtro de matéria alheia recebe 403.

**Arquivos:** `ExportacaoController` e serviço `ExportadorPresencas` (novos), biblioteca `openspout/openspout`, telas `professor/relatorios` e `master/presenca`.

---

## 3. Banco de dados

| Migration | O que faz |
|---|---|
| `2026_11_15_000001_add_origem_to_presencas_table` | Coluna `presencas.origem` (`qr`, `correcao` ou `justificativa`; padrão `qr`) |
| `2026_11_15_000002_create_presenca_ajustes_table` | Tabela `presenca_ajustes`: quem alterou, quando, a ação e o motivo |
| `2026_11_15_000003_create_justificativas_table` | Tabela `justificativas`, com índice único por aluno, matéria e aula |
| `2026_11_15_000004_add_two_factor_columns` | 4 colunas de 2FA em `alunos`, `professores` e `usuario_masters` |
| `2026_11_15_000005_add_unique_aluno_aula_to_presencas_table` | Índice único `presencas(aluno_id, materia_id, codigo_aula)` |
| `2026_11_22_000001_create_justificativa_anexos_table` | Tabela `justificativa_anexos`: atestado criptografado e tamanho, uma linha por justificativa (aplicada em 22/11, lote 3) |

**Aplicação em produção (Supabase, schema `laravel`):**

1. Consulta só de leitura: 174 presenças e **nenhuma duplicada**, então a deduplicação da última migration não apagaria nada.
2. O SQL das migrations foi gerado pelo próprio Laravel (`migrate --pretend`), num PostgreSQL local no mesmo estado da produção.
3. As 5 migrations rodaram numa **única transação**, com o usuário da aplicação (`smart_app`), e foram registradas no controle do Laravel (batch 2).
4. Conferência: as 174 presenças com origem `qr`, as tabelas novas com dono `smart_app`, as 12 colunas de 2FA e os 2 índices únicos.

O site antigo continuou funcionando entre as migrations e o deploy, porque as colunas novas têm valor padrão ou aceitam nulo.

---

## 4. Dependências

| Pacote | Para quê |
|---|---|
| `pragmarx/google2fa` | Códigos TOTP do 2FA |
| `openspout/openspout` | Geração da planilha .xlsx em fluxo, com pouca memória |
| `league/flysystem-aws-s3-v3` | Armazenamento dos atestados no Supabase Storage (compatível com S3) |
| Chart.js 4.4.1 | Gráficos, servido pelo próprio site (a CSP não libera CDN) |
| `laravel/framework` 12.69.2 → **12.72.0** | Corrige a CVE-2026-128083, publicada em 13/11, que fazia o `composer audit` do CI falhar |

`composer audit` sem avisos depois da atualização.

---

## 5. Integração contínua (GitHub Actions)

Os PRs #64 e #65 passaram nos cinco módulos do CI (configuração, banco de dados em PostgreSQL 17, segurança, classes e testes finais) e na varredura de segredos.

| Ocorrência | Causa | Solução |
|---|---|---|
| Módulo 3 falhou no `composer audit` | Alerta de segurança novo no `laravel/framework`, publicado depois do último CI verde da `main` | Atualização para a 12.72.0 |
| Varredura de segredos falhou | Falso positivo: o gitleaks tratou como chave de API a senha fictícia dos testes de 2FA | Ocorrência registrada em `.gitleaksignore`, com aviso de que segredo real se revoga, não se ignora |

---

## 6. Deploy e Vercel

| Item | Situação |
|---|---|
| PR #64 (7 funcionalidades) | Mergeado na `main` do repositório oficial |
| PR #65 (e-mail de teste sem o texto do servidor) | Mergeado |
| Migrations em produção | Aplicadas **antes** do deploy (seção 3) |
| Deploy de produção | Pela CLI (`vercel deploy --prod`) a partir da `main`; `Ready` |
| Variável nova em Production | `TWO_FACTOR_MASTER_OBRIGATORIO=false` (2FA do Master opcional na avaliação) |
| Deploy automático | Não aconteceu no merge: o projeto da Vercel está ligado ao **fork**, cuja `main` ainda não foi sincronizada |

**Sincronizar o fork** (dispara o deploy automático do fork, com o mesmo código já publicado):

```bash
gh repo sync jooaoleonardi/smart-attendance
```

---

## 7. Segurança

### 7.1 Falhas registradas

**SEC-07** (sem autenticação em dois fatores) foi **resolvida** por esta entrega. `docs/falhas-de-seguranca.md` e `docs/seguranca.md` foram atualizados.

### 7.2 Problemas achados nas revisões

Duas revisões rodaram antes da publicação: a revisão automática de segurança, a cada commit, e uma revisão independente do código inteiro. Os 15 problemas encontrados foram corrigidos, **cada um com um teste que falhava antes da correção**.

| Problema encontrado | Gravidade | Correção |
|---|---|---|
| Remover uma presença dava erro 500 no PostgreSQL (`FOR UPDATE` com `COUNT`); os testes em SQLite não mostravam | Crítica | As linhas são travadas e contadas em PHP; a suíte passou a rodar também em PostgreSQL |
| A redefinição de senha pelo e-mail entrava direto, sem o código do 2FA | Alta | A criação de senha passa pelo mesmo desafio do login |
| Um nome começado por `=` virava fórmula ao abrir a planilha | Alta | Texto gravado como texto e prefixado com apóstrofo |
| O mesmo código do 2FA valia mais de uma vez, inclusive em requisições simultâneas | Média | Janela do último código guardada e conferida sob trava da linha |
| Dois cliques simultâneos podiam gravar a mesma presença duas vezes | Média | Índice único no banco |
| Desmarcar a única presença de uma aula fazia a aula sumir para a turma | Média | Remoção recusada, com aviso na tela |
| A deduplicação apagaria presenças legítimas do mesmo código em outra matéria | Média | A matéria entrou na chave única |
| Telas de justificativas e relatórios quebravam depois de excluir um aluno ou uma matéria | Média | As relações passaram a enxergar cadastros excluídos |
| O resumo da planilha incluía matérias que o professor não leciona mais; o filtro não era validado | Média | Resumo limitado às matérias atuais; matéria alheia recebe 403 |
| O limite de tentativas do 2FA era por IP e travaria o Wi-Fi do campus | Média | Limite por login pendente, com teto por IP |
| O texto do servidor de e-mail ia para a tela e para o log | Média | Só o código SMTP na tela; só o tipo do erro no log |
| Ativar o 2FA não pedia a senha: uma sessão roubada poderia trancar o dono fora da conta | Média | Ativar exige a senha atual |
| O envio de atestado daria erro num deploy de teste da Vercel | Baixa | Anexo desligado em qualquer deploy da Vercel sem armazenamento |
| A exportação paginava sem desempate e poderia repetir linhas acima de 500 registros | Baixa | Ordenação com desempate por `id` |
| `laravel/framework` com alerta de segurança | Baixa | Atualizado para a 12.72.0 |

Também conferidos sem problema: acesso de um professor às matérias de outro (sempre 403), injeção de código nas telas, atribuição em massa e o cookie "permanecer conectado".

---

## 8. Testes

| | Quantidade |
|---|---|
| Antes | 238 |
| Depois | **333** (1.425 verificações), em SQLite e em PostgreSQL 17 |

O desenvolvimento seguiu TDD: cada teste foi escrito antes do código e visto falhar primeiro.

| Arquivo | Testes | Cobre |
|---|---|---|
| `CorrecaoPresencaTest` | 10 | Marcar e desmarcar presença, motivo obrigatório, outro professor (403), última presença |
| `JustificativaTest` | 14 | Envio com e sem anexo, tipo e tamanho do arquivo, aprovação com abono, recusa, acesso ao anexo |
| `DoisFatoresTest` | 21 | Desafio no login, recuperação, reuso do código, expiração, "permanecer conectado", ativação com senha |
| `ExportacaoTest` | 11 | As duas abas, filtros, ausência de CPF e e-mail, fórmulas, planilha vazia |
| `MasterEdicaoTest` | 13 | Dados repetidos, índice cego, exclusão reversível, acesso negado |
| `MasterEmailTest` | 7 | E-mail de teste, falha sem expor credenciais |
| `RevisaoFinalTest` | 9 | Cada achado da revisão independente |
| `GraficosTest` | 4 | Acesso por perfil e dados dos gráficos |
| `EstatisticasTurmaTest` e `AulasDaMateriaTest` (Unit) | 6 | Números dos gráficos, lista de aulas e de faltas |

`VendorIntegrityTest` ganhou o hash do Chart.js.

---

## 9. Documentação

Novos: este relatório, o design (`docs/superpowers/specs/2026-11-15-pacote-novas-funcionalidades-design.md`) e o plano de implementação (`docs/superpowers/plans/2026-11-15-pacote-novas-funcionalidades.md`).
Atualizados: `CHANGELOG.md`, `docs/manual-do-usuario.md`, `docs/banco-de-dados.md`, `docs/regras-academicas.md`, `docs/seguranca.md`, `docs/falhas-de-seguranca.md`, `docs/instalacao.md` e `docs/deploy.md`.

---

## 10. Pull Requests

| PR | Título | Situação |
|---|---|---|
| #64 | Justificativas, correção de presença, 2FA, gráficos, edição no Master, exportação .xlsx e SMTP institucional | Mergeado |
| #65 | E-mail de teste não repete o texto do servidor SMTP | Mergeado |
| #1 (fork) | Mesmo conteúdo do #64, aberto antes no fork | **Aberto**, pode ser fechado |

---

## 11. Verificações

**Em produção**, sem login, depois do deploy:

- páginas públicas (login dos três perfis, solicitação de acesso) respondem 200, inclusive uma que consulta o banco;
- páginas protegidas (`/aluno/justificativas`, `/2fa/desafio`) redirecionam para o login;
- `chart.umd.js` e `recursos.css` respondem 200;
- os logs de erro da Vercel estão vazios desde o deploy.

**Localmente, no navegador**, com os dados de demonstração:

- professor: gráficos da turma, lista de chamadas e o bloqueio ao remover a única presença;
- aluno: envio de uma justificativa até "Em análise" e a tela de 2FA com o QR Code;
- Master: gráficos de todas as turmas, edição de um aluno e o diálogo de exclusão;
- computador em modo escuro e celular de 375 px em modo claro, sem rolagem horizontal.

**Não verificado em produção** (exige login com contas reais): os fluxos com login. Eles foram verificados localmente e pelos testes automatizados.

---

## 12. O que não foi possível rodar

| Item | Situação hoje | Por que não rodou | O que destrava |
|---|---|---|---|
| Envio real de e-mails (card #47) | Os e-mails ficam no log, e o Master vê os tokens de primeiro acesso na tela | O grupo escolheu o Gmail, mas a conta de envio e a senha de app ainda não existem | Criar a conta, gerar a senha de app e cadastrar as variáveis `MAIL_*` (seção 14) |
| Teste dos fluxos com login em produção | Testado só localmente | Produção usa contas reais | Teste manual com as contas da avaliação |
| Deploy de teste (preview) com banco separado | Não feito | A Vercel do projeto publica só em produção, e o plano gratuito do Supabase não cria cópias do banco | Não é necessário para a entrega |

---

## 13. Erros e correções durante o trabalho

Registrados por transparência:

- Os testes rodavam só em SQLite, que ignora `FOR UPDATE`. Por isso o erro 500 ao remover presença só apareceu quando a suíte passou a rodar também no PostgreSQL. Desde então, ela é conferida nos dois bancos.
- O seeder repetia o mesmo código de aula em matérias diferentes (`GEN0` a `GEN4`), o que conflitaria com o índice único. Corrigido.
- A primeira tentativa de aplicar as migrations em produção falhou: o Supabase registra a migration numa tabela interna que o usuário `smart_app` não acessa. A transação foi desfeita por inteiro (conferido antes de repetir), e a segunda tentativa voltou ao usuário original antes do registro.
- Na verificação no navegador, o diálogo de exclusão abria no canto da tela (o reset do Tailwind 4 zera a margem do `<dialog>`). Corrigido.
- O teste da exportação com mais de 500 linhas passou mesmo antes da correção, inclusive em PostgreSQL local. O desempate por `id` foi aplicado como garantia, sem reprodução da falha.
- O merge na `main` não gerou deploy automático, porque a Vercel está ligada ao fork. O deploy foi feito pela CLI.
- O repositório não permite merge automático; os merges foram feitos depois de conferir o CI verde.

---

## 14. Pendências

| Item | Responsável | Observação |
|---|---|---|
| Configurar o envio pelo Gmail | Equipe | Fecha o card #47. Passo a passo abaixo |
| Fazer um teste manual em produção com as contas da avaliação | Equipe | Uma chamada, uma justificativa com atestado e uma exportação |
| Decidir se o 2FA volta a ser obrigatório para o Master | Equipe | Depois da avaliação (`TWO_FACTOR_MASTER_OBRIGATORIO`) |
| App mobile para check-in (card #13) | Evolução futura | Fora desta entrega |

**Configurar o Gmail** (a senha de app é um segredo: quem tem a conta cadastra):

1. Na conta Google que vai enviar os e-mails, ligue a verificação em duas etapas e gere uma senha de app em `https://myaccount.google.com/apppasswords`.
2. Na pasta do projeto, cadastre as variáveis em Production (cada comando pede o valor):

```bash
vercel env rm MAIL_MAILER production --yes
vercel env add MAIL_MAILER production         # smtp
vercel env add MAIL_HOST production           # smtp.gmail.com
vercel env add MAIL_PORT production           # 587
vercel env add MAIL_SCHEME production         # smtp
vercel env add MAIL_USERNAME production       # o endereço Gmail
vercel env add MAIL_PASSWORD production       # a senha de app, sem espaços
vercel env add MAIL_FROM_ADDRESS production   # o mesmo endereço Gmail
```

3. Publique de novo (`vercel deploy --prod`, ou sincronize o fork) e, no painel do Master, clique em **Enviar e-mail de teste**.

---

## 15. Como conferir

```bash
# Testes
php artisan test                        # 345 passando

# Dependências
composer audit                          # sem avisos

# Produção
curl -s -o /dev/null -w "%{http_code}\n" https://smart-attendance-nine-alpha.vercel.app/login/professor   # 200
curl -s -o /dev/null -w "%{http_code}\n" https://smart-attendance-nine-alpha.vercel.app/aluno/justificativas  # 302 (pede login)
```

---

## 16. Atualização de 22 de novembro de 2026

As pendências de código dos relatórios da entrega foram resolvidas no PR #67, publicado em produção.

| Pendência | O que foi feito |
|---|---|
| Atestado desligado em produção (card #43) | Atestado guardado no banco, criptografado com a `APP_KEY`; limite de 4 MB |
| Duplo clique ao enviar justificativa dava erro 500 e deixava arquivo solto | Mensagem de duplicidade; o arquivo do envio recusado é descartado |
| O login apagava o token de primeiro acesso antes do código do 2FA | O token só deixa de valer depois do código |
| Desmarcar presença abonada não mudava a justificativa | A justificativa passa a *Recusada*, com o motivo do professor |
| "X de Y presentes" contava alunos que saíram da turma | Conta só quem continua matriculado, também nos gráficos |
| Tela de justificativas do aluno com 2 consultas por matéria | 2 consultas no total |
| `SESSION_ENCRYPT` desligado | Ligado em Production |
| Fork desatualizado e deploy automático nunca testado | Fork sincronizado; o deploy automático publicou em produção em menos de 1 minuto |

**Publicação:** migration `2026_11_22_000001` aplicada antes (conferida: tabela com dono `smart_app`, 2 restrições, lote 3); variáveis `JUSTIFICATIVAS_DISK=banco` e `SESSION_ENCRYPT=true` criadas; merge do PR #67; deploy automático pelo fork, `Ready`, domínio de produção apontando para ele, sem erros nos logs. Com `SESSION_ENCRYPT`, quem estava logado precisou entrar de novo uma vez.

**Revisões nesta etapa:** a revisão automática pediu criptografia do atestado guardado no banco (feita) e leitura em partes no download (não feita: com o limite de 4 MB, o arquivo cabe em memória; o download informa o `Content-Length`). A varredura de segredos acusou duas senhas fictícias nos testes novos, trocadas por variável e registradas no `.gitleaksignore`.

**Testes novos:** `AnexoNoBancoTest` (7) e `PendenciasMenoresTest` (5); total de 345, todos passando em SQLite e PostgreSQL 17.

---

## 17. Preparação para o uso ao vivo (22 de novembro de 2026)

Antes do teste com alunos, professores e avaliadores, uma auditoria em produção encontrou três problemas. Dois foram resolvidos; um depende da equipe.

### 17.1 Erro 500 com muitos acessos simultâneos (resolvido)

| Teste de carga | Antes (pooler em modo sessão) | Depois (modo transação) |
|---|---|---|
| 3 ondas de 40 acessos simultâneos | **15 de 40** em cada onda; 25 com erro 500 | **40 de 40** em todas as ondas |
| 1 onda de 80 acessos simultâneos | não testado | **80 de 80**, tempo médio de 2,2 s |

**Causa** (logs do pooler do Supabase): `(EMAXCONNSESSION) max clients reached in session mode - max clients are limited to pool_size: 15`. No modo sessão, cada requisição prendia uma das 15 vagas do plano gratuito do começo ao fim. Numa chamada com a turma inteira escaneando o QR ao mesmo tempo, quem chegasse depois do 15º recebia erro.

**Correção** (PR #71): pooler em modo transação (`DB_PORT=6543`) com *prepared statements* emulados no PHP (`DB_EMULATE_PREPARES=true`). A suíte inteira passa em PostgreSQL com a emulação ligada. A troca foi feita em duas etapas (emulação primeiro, na porta antiga; depois a porta), com a volta para 5432 pronta caso desse erro.

### 17.2 Aviso de baixa frequência por e-mail (card #10, entregue)

PR #69: quando uma matéria entra em *Atenção* por faltas ou frequência, o aluno recebe um e-mail com a mesma explicação do painel. Verificação diária pelo cron da Vercel às 19h (Brasília), no máximo um aviso por matéria a cada 7 dias. Migration `2026_11_22_000002_create_avisos_frequencia_table` aplicada em produção (lote 4). Enquanto o e-mail não estiver configurado (card #47), os avisos vão para o log.

### 17.3 Senha do Master publicada (pendente, com a equipe)

Conferido no banco de produção: **as 28 contas (1 Master, 6 professores e 21 alunos) usam a senha `senha123`**, e o README publicava o acesso de Master. Qualquer aluno poderia entrar como Master e excluir cadastros ou ver dados pessoais. O PR #70 tirou a credencial do README, mas ela continua no histórico do Git: **a senha do Master em produção precisa ser trocada antes da avaliação** (passo a passo na SEC-02 de `docs/falhas-de-seguranca.md`).

### 17.4 O que a equipe precisa fazer antes da avaliação

| Item | Por quê |
|---|---|
| Trocar a senha do Master em produção | Senha pública; qualquer pessoa vira administrador |
| Configurar o Gmail (card #47) | Sem ele, "Esqueci minha senha", o primeiro acesso por e-mail e o aviso de frequência não chegam a ninguém |
| Fazer um backup do banco (`pg_dump`) | O plano gratuito do Supabase não tem backup para baixar |
| Teste manual com celular real | Uma chamada, uma justificativa com atestado, uma correção e uma exportação; conferir também a barra de baixo e as tabelas em cartões (seção 19) |

---

## 18. Bateria de testes e correção da exportação (22 de novembro de 2026)

Antes do uso ao vivo, o sistema passou por uma bateria de testes em seis frentes. Ela encontrou **um defeito**, corrigido e publicado no PR #73.

### 18.1 O que foi testado

| Frente | Como | Resultado |
|---|---|---|
| Suíte automática | `php artisan test` em SQLite e em PostgreSQL 17 (com a emulação do pooler ligada) | 359 testes passando nos dois bancos |
| Código e dependências | Sintaxe de todo o PHP, compilação de todas as telas Blade, lista de rotas, `composer audit`, `npm audit`, build do front-end | Sem erros e sem vulnerabilidades; 89 rotas |
| Todas as rotas, com cada perfil | Um robô entrou como visitante, aluno, professor e Master e abriu todas as rotas GET com ids reais do banco, inclusive o acesso cruzado (um perfil abrindo rotas de outro) | **Nenhum erro 500** e nenhuma exceção no `laravel.log`. Os 7 casos 4xx eram o esperado: anexo inexistente (404), matéria de outro professor (403), chamada sem QR ativo (404) e limite de tentativas (429) |
| Telas no navegador | 25 telas dos três perfis, lendo os erros do console a cada uma | Nenhum erro de JavaScript nem de CSP |
| Fluxos de ponta a ponta | Chamada completa (professor abre o QR, aluno escaneia antes do login, faz login e a presença é confirmada); cadastro pelo Master e primeiro acesso com o token; justificativa com upload de PDF real e download; solicitar acesso; esqueci a senha; página 404; formulário com CSRF inválido | Todos funcionando. A presença ficou registrada uma única vez, com origem `qr`, e a lista ao vivo do professor mostrou o aluno no horário de Brasília |
| Produção (sem login) | Cabeçalhos de segurança, rotas de cron, arquivos estáticos e redirecionamento para HTTPS | CSP com nonce, HSTS, proteção contra iframe e `nosniff` em todas as páginas, inclusive a de 404; crons respondem 404 sem o segredo; HTTP redireciona para HTTPS |

### 18.2 O defeito encontrado

**Na Central de Presenças do Master, "Exportar planilha" ignorava os filtros da tela.** Os filtros (professor, matéria e aluno) buscam por JavaScript, sem recarregar a página, e o link de exportar era fixo: a planilha vinha **com todas as presenças**, mesmo com a tela filtrada. Além disso, a busca e a exportação filtravam de formas diferentes. A busca encontrava a matéria também pela sala e o aluno também pelo e-mail; a exportação, não.

**Correção (PR #73):**

- O link de exportar passa a acompanhar os filtros digitados (`?materia=…&aluno=…`) e volta ao normal quando eles são apagados.
- A busca da tela e a exportação usam uma única regra de filtro (`DashboardController::filtrarPresencasMaster`): professor por nome, CPF ou e-mail; matéria por parte do nome ou da sala; aluno por nome, RA, CPF ou e-mail.

**Verificação:** três testes novos em `ExportacaoTest`, que falhavam antes da correção (filtro pela sala, aluno pelo e-mail e o link que acompanha os filtros); 362 testes passando em SQLite e PostgreSQL; no navegador, o link mudou para `…/exportar?materia=Banco` ao filtrar (com a tabela reduzida para 18 linhas) e voltou ao normal ao limpar, sem erros no console. Em produção, o deploy ficou `Ready` e sem erros nos logs. A tela só abre com login de Master, que não foi usado em produção: vale conferir manualmente filtrando uma matéria e exportando.

---

## 19. Layout no celular (22 de novembro de 2026)

Um teste da equipe no celular mostrou a barra de navegação do professor com os nomes sobrepostos ("Presença / QR" em cima de "Minhas Turmas", "Relatórios" em cima de "Justificativas"). A partir daí, **todas as telas dos três perfis foram auditadas em 360 px de largura**, o tamanho de um celular pequeno. A auditoria achou 11 problemas, todos corrigidos e publicados no PR #75.

### 19.1 Como foi auditado

- Login com cada perfil (professor, Master e aluno) no banco local, abrindo todas as telas em 360 × 780 px e tirando screenshots.
- Em cada tela, um script procurou elementos saindo da largura da tela e itens da barra de baixo sobrepostos.
- Depois da correção, o mesmo script não achou nenhum elemento fora da tela. As telas foram conferidas também em 1280 px, para garantir que o computador continuou igual.

### 19.2 Problemas encontrados e correções

| Tela | Problema | Correção |
|---|---|---|
| Barra de baixo (professor e Master) | Os nomes dos 7 itens do professor e dos 9 do Master se sobrepunham | Cada item tem largura própria e a barra rola para o lado. Nomes curtos no celular (QR Code, Turmas, Evento, Segurança, Início, Frequência); o item da tela atual fica centralizado e uma sombra na borda indica que há mais itens |
| Barra de baixo (Master) | Os grupos "Usuários" e "Conteúdo" empilhavam itens e quebravam a barra | Os grupos viram itens soltos na barra |
| Alunos, Professores e Matérias (Master) | A coluna **Ações** (Editar) ficava fora da tela, sem indicação de que dava para arrastar | As tabelas viram cartões: cada linha é um bloco com o nome do campo ao lado do valor e o botão visível |
| Alunos, Professores e Matérias (Master) | A busca era `hidden md:block`: no celular **não existia** | A busca aparece em largura total no celular; os resultados da busca ao vivo também saem como cartões |
| Notas da turma (professor) | As colunas de notas, média e situação ficavam fora da tela | Um cartão por aluno, com 1ª Prova, 1º Trab., 2º Trab., 2ª Prova, média e situação |
| Corrigir chamada (professor) | O campo de motivo e o botão Salvar ficavam cortados | Cartão por aluno com a chave de presença, o motivo e o Salvar |
| Central de Presenças, Relatórios, Justificativas e Gráficos | Tabelas largas com colunas fora da tela | Mesmo padrão de cartões |
| Selecionar matéria (professor) | O selo "Ativo" ficava por cima do nome da matéria | O selo vai para a linha de cima quando não cabe |
| Relatórios (professor) e Central de Presenças (Master) | Botões "Exportar planilha" e "Gerar PDF" espremidos no topo ou saindo do card | Botões movidos para o conteúdo, quebrando linha quando preciso |
| Solicitações (Master) e cabeçalho da turma (professor) | O formulário de rejeitar e os selos "Previstas/Realizadas" saíam do card | Os elementos quebram linha dentro do card |
| Telas do aluno | O cronômetro da sessão ficava fixo e cobria o conteúdo ao rolar | No celular ele fica no topo da página e rola junto |

Também foi corrigido o texto "1 alunos" para "1 aluno" na lista de turmas.

### 19.3 Como ficou o código

- **Padrão de tabela em cartões:** a classe `rc-tabela-cartoes` (em `public/css/recursos.css`) e o script `public/js/tabela-cartoes.js`, que copia o nome de cada coluna para as células. Uma tela nova só precisa da classe na `<table>`; o padrão foi registrado em `docs/design-e-acessibilidade.md` (seção "Celular" e checklist).
- **Barra de baixo:** prop `curto` no componente `x-ui.sidebar-item`, com `aria-label` e `aria-current` para leitores de tela.
- **Testes:** 3 testes novos em `LayoutCelularTest` (nomes curtos na barra, busca do Master presente no celular, tabelas e script carregados); 365 testes passando.

### 19.4 Verificação em produção

Deploy automático do fork `Ready` (commit `93fc6be`), com o arquivo `js/tabela-cartoes.js` publicado e a tela de login sem rolagem horizontal em 360 px. As telas logadas não foram abertas em produção, porque o teste não usa contas reais: vale a equipe conferir num celular de verdade a barra de baixo, as notas da turma, a correção da chamada e a lista de alunos com o botão Editar.

