# Histórico de mudanças

Mudanças relevantes do Smart Attendance, da mais recente para a mais antiga. O formato segue o [Keep a Changelog](https://keepachangelog.com/pt-BR/1.1.0/). Como o projeto ainda não usa versões numeradas, as entradas são agrupadas por data e Pull Request.

## Não lançado

### Documentação
- Repositório reorganizado: README reescrito, pasta `docs/` com manual do usuário, arquitetura, banco de dados, regras acadêmicas, segurança, instalação, deploy, testes e design.
- Guia de contribuição, este histórico e modelos de issue e de Pull Request.
- Relatório de bugs movido da raiz para `docs/relatorios/`.

### Corrigido
- **Limite de tentativas por IP travava turmas inteiras.** No Wi-Fi da instituição todos saem pelo mesmo IP: só 10 alunos conseguiam fazer login a cada 3 minutos, e só 5 pessoas por minuto faziam check-in num evento. Agora o limite é por usuário (ou e-mail) + IP, com um teto alto por IP.
- O botão de mostrar senha não funcionava na tela Criar senha (dois scripts alternavam o campo ao mesmo tempo), e nenhum botão de senha tinha nome para leitores de tela.
- A página 404 ficava mais larga que a tela no celular; o site ganhou um ícone de aba.
- A Central de Presenças do Master calculava faltas sobre as aulas previstas (um aluno com 100% aparecia com "24 de 40") e coloria a média com corte 6. Agora usa a mesma regra do resto do sistema.
- A verificação de e-mail do pedido de acesso não funcionava: o JavaScript chamava a rota com o método errado.

## 2026-09-28 · Turmas, histórico e apresentação ([#24](https://github.com/AtilaAlvesRodrigues/smart-attendance/pull/24))

### Adicionado
- **Gerenciar turma** no Master: define professores e alunos de cada matéria. Antes, nada cadastrado pelo Master podia ser usado.
- **Histórico de aulas** do aluno, com a data de cada chamada e se esteve presente ou faltou.
- Tarefa diária da Vercel para o banco gratuito do Supabase não ser pausado.

### Corrigido
- Com o e-mail desligado, o cadastro dizia "e-mail enviado" e a pessoa nunca conseguia entrar. Agora o Master recebe o token de primeiro acesso na tela.
- A demonstração do professor mostrava "Justificativas", função que não existe.
- As aulas de exemplo do seed caíam todas no mesmo dia da semana; agora só em dias úteis.

## 2026-09-27 · Usabilidade e acessibilidade ([#23](https://github.com/AtilaAlvesRodrigues/smart-attendance/pull/23))

### Adicionado
- Painel do aluno com um cartão por matéria: situação, frequência, faltas usadas, média e notas.
- Modo **Projetar em tela cheia** na chamada e botão **Copiar link** para quem está sem câmera.
- Aviso de pedidos de acesso pendentes na visão geral do Master.
- `public/css/usability.css`: contraste acessível, textos maiores, foco visível, celular e redução de movimento.
- Classe `SituacaoAcademica`, regra única de frequência, faltas e situação.

### Corrigido
- A página inicial pública mostrava o QR Code da aula ativa, permitindo marcar presença de casa.
- A lista de presentes montava HTML a partir do nome do aluno (XSS armazenado).
- O professor via "reprovado por falta" para alunos com 100% de presença no meio do semestre, e "Aprovado" já na primeira nota digitada.
- No celular, a página inicial ficava mais larga que a tela e cortava o menu.

## 2026-09-27 · Horário noturno e e-mail ([#22](https://github.com/AtilaAlvesRodrigues/smart-attendance/pull/22))

### Corrigido
- O QR Code de presença não funcionava entre 21h e 0h: a data era calculada em UTC num ponto e no fuso de Brasília em outro.
- Uma falha de envio de e-mail quebrava a aprovação de pedidos e revelava, na recuperação de senha, quem tem conta.

## 2026-09-25 · Limpeza, testes e deploy ([#21](https://github.com/AtilaAlvesRodrigues/smart-attendance/pull/21))

### Adicionado
- Publicação na Vercel com PostgreSQL no Supabase.
- Rotas do fluxo de **Solicitação de Acesso**, que existia no código mas estava inacessível.

### Corrigido
- 24 testes falhavam (factory do Master vazia, testes de criptografia e CSRF).
- Seeders não podiam rodar duas vezes.
- A lista de presentes do professor expunha e-mail, CPF e *blind indexes* dos alunos.
- Pipeline de CI quebrado por dependência de artefato expirado e de *secrets*.
- Dependências com vulnerabilidades conhecidas (`league/commonmark`, `nanoid`).

### Removido
- Logs, scripts de depuração e PDFs de pipeline que estavam no repositório.

## Até agosto de 2026 · Construção do sistema

- Autenticação com três perfis independentes (aluno, professor, master), criptografia de dados pessoais e *blind index*.
- Chamada por QR Code, notas por turma, relatórios, eventos com check-in público e primeiro acesso por token.
- Cronômetro de sessão de 1 hora com sincronização entre abas.
- Pipeline de CI em 6 módulos e testes automatizados das camadas de segurança e de integração.
