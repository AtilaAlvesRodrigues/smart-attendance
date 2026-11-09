# Roteiro de apresentação

Roteiro de cerca de 10 minutos e lista de conferência para antes da apresentação. As senhas **não** estão aqui: combine-as por canal privado.

## Antes de começar (30 minutos antes)

| # | Conferir | Como |
|---|---|---|
| 1 | Site no ar | Abrir `https://smart-attendance-nine-alpha.vercel.app/login`: deve carregar com estilo |
| 2 | PHP corrigido em produção | `curl -sI https://smart-attendance-nine-alpha.vercel.app/login/professor \| grep -i x-powered-by` → `PHP/8.5.12` |
| 3 | Senha do Master trocada | Ver [SEC-02](falhas-de-seguranca.md#sec-02) |
| 4 | Login dos 3 perfis | Professor, aluno e Master entram sem erro |
| 5 | Chamada de teste | Professor abre uma matéria, o QR aparece e **muda** a cada 30 s |
| 6 | Celular logado | Aluno logado no celular, matriculado na matéria da chamada |
| 7 | Escaneou e registrou | O nome do aluno aparece na lista do professor em até 3 s |
| 8 | Tela cheia | Botão *Projetar em tela cheia* funciona no projetor |
| 9 | Relatório em PDF | Gerar um relatório de presença do professor e abrir o PDF |
| 10 | Plano B | Rodar local: `php artisan serve` (veja [Instalação](instalacao.md)); fotos de tela em `docs/img/` |

## Roteiro

**1. O problema (1 min).** A chamada em papel leva de 5 a 10 minutos por aula, depende de transcrição e aceita a assinatura de um colega.

**2. A chamada por QR Code (3 min).** Como professor: *Presença / QR* → escolher a matéria → projetar em tela cheia. Mostre a contagem "Troca em N s". Como aluno, no celular: escanear e ver "Presença confirmada". Volte à tela do professor: o nome aparece sozinho.

**3. Por que o QR muda (1 min).** Uma foto do QR enviada a quem está fora da sala deixa de valer em até 60 segundos, porque o link leva um token assinado que muda a cada 30. Mostre que o link antigo dá "QR Code expirou".

**4. Frequência e notas (2 min).** Painel do aluno (situação em texto e cor, faltas até o limite) e tela de notas do professor. Regra única em `SituacaoAcademica`.

**5. Administração (1 min).** Master: cadastros, gerenciar turma, pedidos de acesso.

**6. Segurança (2 min).** CPF, RA e e-mail criptografados; perfis isolados; limite de tentativas; **CSP com nonce**; QR rotativo; `Host` confiável. Mostre o [registro de falhas](falhas-de-seguranca.md) e diga o que está aberto (2FA, atributos inline).

## Perguntas prováveis

- **E se o aluno mandar o QR ao vivo para um colega?** Continua possível dentro dos 30 a 60 segundos. Está registrado como [SEC-05](falhas-de-seguranca.md#sec-05); a saída é restringir à rede ou localização da instituição.
- **O aluno demora para fazer login e o QR expira?** Sim: ele escaneia de novo. É o custo de a foto não valer.
- **Dados reais?** A demonstração usa dados fictícios; os pessoais são criptografados no banco.
- **Quanto custa?** Vercel e Supabase em plano gratuito; o Supabase gratuito pausa o banco sem uso, por isso há uma tarefa diária de manutenção.
- **Como sabem que está seguro?** 238 testes automáticos, auditoria de dependências no CI e relatórios de segurança. Nem tudo está resolvido, e isso está documentado.

## Se algo der errado

| Problema | O que fazer |
|---|---|
| Site fora do ar ou lento | Rodar local (plano B) |
| QR não aparece | Recarregar a tela da chamada; conferir se a matéria é do professor logado |
| Aluno não registra | Está logado? Matriculado na matéria? QR com mais de 1 minuto? Escaneie de novo |
| Tela sem estilo | Abrir o console do navegador; provável bloco sem `nonce` (`docs/seguranca.md`) |
| Erro 400 em domínio novo | Incluir o domínio em `TRUSTED_HOSTS` na Vercel |
