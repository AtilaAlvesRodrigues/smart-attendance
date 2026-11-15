# Manual do usuário

Como usar o Smart Attendance em cada perfil. As telas funcionam no computador e no celular, nos temas escuro e claro (botão redondo no canto inferior direito).

- [Página inicial e login](#página-inicial-e-login)
- [Aluno](#aluno)
- [Professor](#professor)
- [Master (administração)](#master-administração)
- [Dúvidas frequentes](#dúvidas-frequentes)

---

## Página inicial e login

<img src="img/inicio.jpg" alt="Página inicial" width="720">

A página inicial explica o sistema e leva ao login de cada perfil. Quando há uma chamada aberta, ela mostra o nome da matéria e a sala, mas **nunca o QR Code**: ele só aparece na sala, na tela do professor.

| Perfil | Botão | Como entrar |
|---|---|---|
| Aluno | **Entrar como Aluno** | RA, e-mail ou CPF, e a senha |
| Professor | **Entrar como Professor** | E-mail ou CPF, e a senha |
| Master | **Entrar como Professor** | E-mail e senha do administrador |

<img src="img/login-aluno.jpg" alt="Login do aluno no celular" width="260">

Por segurança, a sessão expira **1 hora** depois do login. O tempo restante aparece numa pílula no topo do painel e, ao expirar, o sistema avisa e volta para a página inicial.

### Primeiro acesso

Quem é cadastrado pelo Master recebe um **token provisório**: por e-mail ou, quando o envio está desligado, entregue pelo próprio administrador.

1. Na tela de login do seu perfil, digite seu usuário e use o **token como senha**.
2. O sistema abre a tela **Criar senha**. Escolha uma senha com pelo menos 8 caracteres e confirme.
3. Pronto: dali em diante, entre com a senha nova.

### Esqueci minha senha

Na tela de login, clique em **Esqueci minha senha** e informe seu e-mail. Se ele estiver cadastrado, chega um novo token provisório. A resposta na tela é sempre a mesma, para não revelar quem tem conta no sistema.

### Solicitar acesso

<img src="img/solicitar-acesso.jpg" alt="Formulário de solicitação de acesso" width="260">

Quem ainda não tem conta clica em **Solicitar acesso** na tela de login e preenche nome, e-mail, CPF e, no caso do aluno, o RA. O pedido vai para o Master, que aprova ou rejeita. Na aprovação, a conta é criada e o token de primeiro acesso é enviado por e-mail.

---

## Aluno

### Registrar presença

1. Na sala, abra a **câmera do celular** e aponte para o QR Code que o professor projetar.
2. Toque no link que aparecer. Se não estiver logado, entre com seu RA, e-mail ou CPF: a presença é concluída logo depois do login.
3. Aparece **Presença confirmada!**, e seu nome surge na lista do professor.

<img src="img/presenca-confirmada.jpg" alt="Tela de presença confirmada" width="260">

| Mensagem | O que significa | O que fazer |
|---|---|---|
| Presença confirmada! | Tudo certo | Nada |
| Presença já registrada | Você já confirmou nesta aula | Nada |
| Você não está matriculado nesta disciplina | A coordenação ainda não fez sua matrícula | Procure a coordenação |
| Este código de presença expirou ou é inválido | A chamada foi encerrada (o QR vale 2 horas) | Avise o professor |

Sem câmera? Peça ao professor o **link da chamada**: ele copia e envia.

### Painel do aluno

<img src="img/painel-aluno.jpg" alt="Painel do aluno com cartões por matéria" width="300"> <img src="img/painel-aluno-claro.jpg" alt="Painel do aluno no tema claro" width="300">

Cada matéria aparece num cartão com:

- **Situação**: *Em dia*, *Atenção*, *Aprovado*, *Reprovado por nota* ou *Reprovado por falta*, sempre com texto e ícone, além da cor.
- **Frequência**: porcentagem de presença nas aulas já realizadas.
- **Faltas usadas**: quantas faltas você tem e quantas pode ter (o limite é 25% das aulas do semestre).
- **Média**: parcial até todas as notas serem lançadas, final depois.
- **Explicação** da situação, por exemplo "Restam só 1 falta até o limite".
- **Notas** P1, T1, T2 e P2. Um traço (—) indica nota ainda não lançada.
- **Ver histórico de aulas**: a data de cada chamada e se você esteve presente ou faltou.

O botão de perfil (ícone de pessoa, no topo) mostra seus dados: nome, e-mail, RA e CPF.

---

### Justificar uma falta

No painel, clique em **Justificar uma falta**.

1. Escolha a aula em que você faltou. A lista mostra só as aulas em que você não tem presença.
2. Escreva o motivo, com pelo menos 10 caracteres.
3. Se tiver, anexe o atestado ou comprovante (PDF, JPG ou PNG, até 5 MB). Só você e o professor da matéria veem o arquivo.
4. Clique em **Enviar justificativa**.

A justificativa aparece em **Enviadas** como *Em análise*. Quando o professor decidir, você recebe um e-mail. Se for aprovada, a falta é **abonada** e deixa de contar na frequência. É uma justificativa por falta; se ela for recusada, fale com o professor.

### Segurança da conta (verificação em duas etapas)

Em **Segurança da conta** você pode ativar a verificação em duas etapas: além da senha, o login pede um código de 6 números que muda a cada 30 segundos num app do celular (Google Authenticator, Microsoft Authenticator ou Authy).

1. Escaneie o QR Code com o app (ou digite a chave mostrada).
2. Digite o código que aparece no app e a sua senha atual, e clique em **Ativar verificação**.
3. **Guarde os 8 códigos de recuperação.** Eles não aparecem de novo. Se perder o celular, use um deles no lugar do código do app; cada um vale uma vez.

Para desativar, informe sua senha na mesma tela.

---

## Professor

<img src="img/painel-professor.jpg" alt="Painel do professor" width="720">

O painel começa pela tarefa principal, **Iniciar chamada**. Se já houver uma chamada aberta, mostra a matéria e o botão **Abrir a chamada**.

### Fazer a chamada

1. Clique em **Iniciar chamada** e escolha a matéria.

   <img src="img/escolher-materia.jpg" alt="Escolha da matéria" width="640">

2. A tela da chamada mostra o QR Code, a validade (2 horas) e a lista de **Presentes**, que se atualiza sozinha a cada 3 segundos.

   <img src="img/chamada.jpg" alt="Tela da chamada" width="640">

3. Clique em **Projetar em tela cheia** para mostrar o QR Code grande no projetor, com o número de presentes. Aperte **Esc** para sair.

   <img src="img/projecao.jpg" alt="Modo projeção" width="640">

4. Aluno sem câmera? Clique em **Copiar link** e envie o link da chamada.

Quando a validade acaba, a tela avisa **Chamada encerrada** e o QR Code para de aceitar presenças.

### Lançar notas

<img src="img/notas.jpg" alt="Tabela de notas" width="720">

1. No menu lateral, abra **Minhas Turmas** e escolha a matéria.
2. Digite a nota (de 0 a 10) na coluna desejada e aperte **Tab** ou clique fora. A nota é salva sozinha: o campo pisca em verde quando salvou.
3. A **Média** e a **Situação** mudam na hora. O aluno só aparece como *Aprovado* ou *Reprovado por nota* depois que as quatro notas estão lançadas.

Para apagar uma nota, deixe o campo vazio e aperte Tab.

### Corrigir uma chamada

Um aluno estava na aula, mas a presença não registrou? Ou alguém registrou e não estava?

1. Em **Minhas Turmas**, abra a matéria e clique em **Chamadas e correções**.
2. Escolha a aula. A tela mostra cada aluno com a situação: *Presente*, *Falta*, *Presente (corrigido)* ou *Falta abonada*.
3. Mude a chave do aluno, escreva o **motivo** (obrigatório) e clique em **Salvar**.

Toda correção fica no **Histórico de correções** da aula, com data e motivo. Não é possível remover a única presença de uma aula: sem nenhuma presença, a aula deixaria de existir e as faltas da turma inteira mudariam.

### Justificativas de falta

No menu, **Justificativas** lista os pedidos dos alunos das suas matérias. O painel avisa quantas aguardam análise.

- Abra o atestado em **Ver atestado** (quando houver).
- **Aprovar e abonar**: a falta vira presença abonada e deixa de contar.
- **Recusar**: a falta continua contando.

Você pode escrever um comentário para o aluno. Ele recebe a decisão por e-mail.

### Gráficos da turma

Na tela da matéria, **Gráficos de presença** mostra a frequência média, a porcentagem de presentes em cada aula, quantos alunos estão em cada situação e a lista de **alunos em risco** (perto do limite de faltas ou já reprovados por falta). Em **Ver os números** estão os mesmos dados em tabela.

### Relatórios

<img src="img/relatorios.jpg" alt="Relatório de presenças" width="720">

Em **Relatórios**, filtre por matéria e período para ver todas as presenças registradas. O botão **Gerar PDF** abre a versão para impressão, e **Exportar planilha** baixa um arquivo do Excel (.xlsx) com os mesmos filtros: a aba *Presenças* traz cada registro (com a origem: QR Code, correção ou falta abonada), e a aba *Resumo* traz presenças, faltas, frequência e situação de cada aluno. A planilha não inclui CPF nem e-mail.

### Palestras e eventos

<img src="img/evento.jpg" alt="Gestão de palestra" width="720">

Para eventos com público externo, que não tem login no sistema:

1. No menu, abra **Evento / Palestra**. O sistema gera um QR Code e um link de acesso.
2. O participante escaneia e preenche **nome e e-mail**. A lista de participantes se atualiza a cada 30 segundos.
3. Ao final, clique em **Gerar Lista (PDF)** e depois em **Encerrar Palestra**.

<img src="img/checkin-evento.jpg" alt="Check-in do participante" width="260">

---

## Master (administração)

<img src="img/painel-master.jpg" alt="Visão geral do Master" width="720">

A **Visão geral** mostra o total de professores, alunos e matérias, com atalhos para cada lista. Quando há pedidos de acesso esperando análise, aparece um aviso no topo.

### Cadastrar pessoas e matérias

<img src="img/cadastrar.jpg" alt="Tela de cadastro" width="720">

Em **Cadastrar**, preencha o formulário de professor, aluno ou matéria.

- **Professor e aluno**: CPF, RA e e-mail não podem se repetir. Ao cadastrar, o sistema envia o e-mail de primeiro acesso. Se o envio estiver desligado ou falhar, a tela mostra o **token de primeiro acesso** para você entregar à pessoa.
- **Matéria**: nome, sala, carga horária e total de aulas no semestre (usado no limite de faltas). Ao terminar, aparece o atalho **Montar a turma agora**.

### Montar a turma

<img src="img/turma.jpg" alt="Gerenciar turma" width="720">

Uma pessoa cadastrada só participa das aulas depois de vinculada à matéria:

1. Menu **Conteúdo → Matérias** → **Gerenciar turma** na matéria desejada.
2. Marque os **professores** que dão a matéria e os **alunos matriculados**. Use a busca por nome ou RA e os botões *Marcar visíveis* / *Desmarcar visíveis*.
3. Clique em **Salvar turma**.

Desmarcar um aluno remove a matrícula e as notas dele nesta matéria. Quem continua matriculado mantém as notas.

### Pedidos de acesso

<img src="img/solicitacoes.jpg" alt="Pedidos de acesso" width="720">

Em **Solicitações**, os pedidos pendentes aparecem primeiro. **Aprovar** cria a conta e envia o e-mail de primeiro acesso; **Rejeitar** permite informar o motivo. Se já existir uma conta com o mesmo e-mail ou CPF, o pedido é rejeitado automaticamente.

### Central de presenças

<img src="img/central-presencas.jpg" alt="Central de presenças" width="720">

Todos os registros de presença, com filtros por professor (nome ou CPF), matéria e aluno (nome ou RA). A coluna **Faltas** mostra as faltas do aluno naquela matéria em relação ao limite permitido. **Exportar planilha** baixa os registros em Excel (.xlsx).

### Gráficos de presença

Em **Conteúdo → Gráficos**, a frequência média de cada turma (verde a partir de 75%, amarelo de 50% a 74%, vermelho abaixo de 50%), quantas turmas estão abaixo de 75% e quantas matrículas estão em risco.

### Editar e excluir cadastros

Nas listas de **Professores**, **Alunos** e **Matérias**, o botão **Editar** abre os dados do cadastro.

- E-mail, CPF e RA continuam sem poder se repetir.
- Na matéria, é possível mudar nome, sala, carga horária e total de aulas previstas (que muda o limite de faltas).
- **Excluir** pede confirmação. A pessoa perde o acesso (ou a matéria some das telas), mas o histórico de presenças e notas fica guardado no banco.
- Se a pessoa usa verificação em duas etapas e perdeu o celular e os códigos de recuperação, **Remover verificação** deixa ela entrar só com a senha e configurar de novo.

### Servidor de e-mail

Na **Visão geral**, o quadro **Servidor de e-mail** mostra por onde os e-mails estão saindo (servidor e porta, nunca usuário ou senha). **Enviar e-mail de teste** manda uma mensagem para o seu e-mail; se falhar, a tela mostra o erro do servidor. Como configurar: [Instalação → E-mail](instalacao.md#e-mail-servidor-da-instituição).

### Verificação em duas etapas do Master

Para o Master, a verificação em duas etapas é **obrigatória**: sem ela ativada, o sistema leva à tela **Segurança da conta** antes de abrir o painel, e ela não pode ser desligada. Se um Master perder o celular e os códigos de recuperação, outro Master remove a verificação pela tela de edição (com um único Master, só pelo banco de dados).

---

## Dúvidas frequentes

**O aluno pode registrar presença de casa?**
Não pela página do sistema: o QR Code só aparece na tela do professor. Hoje, uma foto do QR enviada a um colega funcionaria enquanto a chamada estiver aberta; o QR que muda a cada poucos segundos está nos próximos passos.

**Um aluno registrou presença duas vezes?**
Não é possível: o segundo registro na mesma aula mostra "Presença já registrada".

**O professor consegue corrigir uma presença?**
Ainda não pelo sistema. Está nos próximos passos, junto com a justificativa de faltas.

**Apareceu "Sessão expirada".**
A sessão dura 1 hora. Entre de novo: nada é perdido.
