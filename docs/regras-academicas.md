# Regras acadêmicas

Toda a lógica de frequência, faltas, média e situação está em uma única classe, [`App\Support\SituacaoAcademica`](../app/Support/SituacaoAcademica.php). Ela é usada:

- no painel do aluno ([`DashboardController@alunoIndex`](../app/Http/Controllers/DashboardController.php));
- na tela de notas do professor ([`professor/gerenciar/materia.blade.php`](../resources/views/professor/gerenciar/materia.blade.php)), cujo JavaScript repete a mesma regra ao digitar uma nota;
- na central de presenças do Master ([`master/presenca.blade.php`](../resources/views/master/presenca.blade.php) e [`MasterSearchController`](../app/Http/Controllers/MasterSearchController.php)).

Assim, as três telas mostram sempre os mesmos números. Ao mudar uma regra, mude a classe e o JavaScript da tela de notas, e atualize os testes em [`tests/Unit/SituacaoAcademicaTest.php`](../tests/Unit/SituacaoAcademicaTest.php).

## Definições

| Termo | Definição |
|---|---|
| Aulas previstas | `materias.total_aulas`, informado no cadastro da matéria |
| Aulas realizadas | Quantidade de chamadas diferentes (`codigo_aula`) já feitas na matéria |
| Presenças | Registros do aluno na matéria |
| Faltas | Aulas realizadas − presenças |
| Limite de faltas | 25% das aulas previstas, arredondado para baixo |
| Faltas restantes | Limite − faltas |
| Frequência | Presenças ÷ aulas realizadas (100% se nenhuma aula foi dada) |
| Média | Média simples das notas lançadas entre P1, T1, T2 e P2 |

A frequência é calculada sobre as aulas **já realizadas**, e não sobre as previstas. Assim, no meio do semestre, um aluno que foi a todas as aulas aparece com 100%, e não como reprovado.

## Situação

As regras são aplicadas nesta ordem:

| Situação | Quando |
|---|---|
| Reprovado por falta | As faltas **passaram** do limite |
| Aprovado | As quatro notas lançadas e média ≥ 5,0 |
| Reprovado por nota | As quatro notas lançadas e média < 5,0 |
| Atenção | Faltas em 75% ou mais do limite, ou média parcial < 5,0, ou frequência < 75% depois de pelo menos 3 aulas |
| Em dia | Nenhum dos casos acima |

Na tela de notas do professor, *Atenção* e *Em dia* aparecem juntas como **Em andamento**: o professor vê o resultado final só depois de todas as notas lançadas.

## Exemplos

Matéria com 40 aulas previstas, ou seja, limite de 10 faltas:

| Aulas realizadas | Presenças | Notas | Faltas | Frequência | Situação |
|---|---|---|---|---|---|
| 15 | 15 | 8,5 · 9,0 · 7,5 · — | 0 de 10 | 100% | Em dia (média parcial 8,3) |
| 20 | 12 | — | 8 de 10 | 60% | Atenção (restam 2 faltas) |
| 21 | 10 | — | 11 de 10 | 48% | Reprovado por falta |
| 30 | 28 | 6,0 · 5,0 · 4,0 · 7,0 | 2 de 10 | 93% | Aprovado (média 5,5) |
| 30 | 28 | 3,0 · 4,0 · 5,0 · 4,0 | 2 de 10 | 93% | Reprovado por nota (média 4,0) |
| 10 | 10 | 4,5 · 4,0 · — · — | 0 de 10 | 100% | Atenção (média parcial abaixo de 5,0) |

## Mensagens para o aluno

Cada situação vem com uma explicação curta no painel:

| Situação | Exemplo de mensagem |
|---|---|
| Em dia | "7 faltas restantes até o limite." |
| Atenção por faltas | "Restam só 1 falta até o limite." |
| Atenção por frequência | "Frequência de 50%, abaixo dos 75% exigidos. Restam 4 faltas." |
| Atenção por média | "Média parcial abaixo de 5,0." |
| Aprovado | "Média final 8,3 e frequência suficiente." |
| Reprovado por nota | "Média final 4,0, abaixo de 5,0." |
| Reprovado por falta | "Você passou do limite de 10 faltas." |
