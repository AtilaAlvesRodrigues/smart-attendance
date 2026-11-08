# Revisão do projeto · novembro de 2026

Revisão técnica do Smart Attendance sobre o commit `6411a3e` (branch `main`), com as mudanças desta revisão aplicadas numa branch à parte.

## Como foi feita

| Verificação | Resultado |
|---|---|
| Suíte de testes (`php artisan test`) | **232 passando**, 1.066 verificações (eram 209 antes: 23 testes novos) |
| `composer audit` | 8 avisos, todos Symfony → [SEC-01](../falhas-de-seguranca.md#sec-01); **corrigidos** atualizando os pacotes (hoje sem avisos) |
| `npm audit` | 6 avisos (2 críticos, 4 altos), todos de build/dev → [SEC-06](../falhas-de-seguranca.md#sec-06); **corrigidos** com `npm audit fix` (hoje 0) |
| Teste manual no navegador | Login do professor, tela de chamada, rotação real do QR Code (token trocou de janela em 30 s), console sem violações de CSP |
| Leitura de código | `PresencaController`, `SecurityHeaders`, rotas, `bootstrap/app.php`, `.env.example`, views da chamada, workflow do CI |

**O que não foi verificado:** as demais telas no navegador (Master, Aluno, Eventos, Relatórios), o comportamento no celular real, a produção na Vercel e o desempenho sob carga. A CSP foi conferida por teste automatizado em quatro páginas e à mão na tela da chamada. Conferir as outras telas antes de publicar.

## Tamanho do projeto

| Item | Quantidade |
|---|---|
| Controllers / Models / Migrations | 14 / 7 / 14 |
| Views Blade | 42 (7.463 linhas) |
| Código PHP em `app/` | 2.888 linhas |
| Testes | 32 arquivos, 4.605 linhas |
| Rotas | 58 |

O projeto é **pesado em interface**: a camada de apresentação tem mais que o dobro do código PHP do servidor. As maiores views são `sidebar.blade.php` (623 linhas), `pdf_teste_vulnerabilidade` (408), `master/cadastrar` (402) e `master/presenca` (388).

## Pontos fortes

1. **Dados pessoais protegidos de verdade.** CPF, RA e e-mail criptografados no banco, com *blind index* para busca. Raro em projeto acadêmico.
2. **Segurança testada, não só declarada.** Cada camada (CSRF, XSS, SQL injection, rate limit, isolamento de perfis, honeypot) tem teste.
3. **Regras de negócio num lugar só.** `SituacaoAcademica` é a única fonte para frequência e notas, e o painel do aluno, as notas do professor e a central do Master mostram os mesmos números.
4. **Limite de tentativas pensado para o uso real** (por pessoa + teto por IP), já que a turma inteira sai pelo mesmo Wi-Fi.
5. **Documentação acima da média:** manual, arquitetura, banco, regras, segurança, instalação, deploy e CI.
6. **Pipeline com PostgreSQL real**, o mesmo banco da produção.

## Riscos, em ordem de prioridade

| # | Risco | Onde | Ação |
|---|---|---|---|
| 1 | ~~Pacotes Symfony com avisos que tocam host, proxy e roteamento~~ | `composer.lock` | **Resolvido** ([SEC-01](../falhas-de-seguranca.md#sec-01)) |
| 2 | Conta Master pública na demonstração | README | Isolar ou trocar a senha ([SEC-02](../falhas-de-seguranca.md#sec-02)) |
| 3 | ~~`trustProxies('*')` sem `trustHosts`~~ | `bootstrap/app.php` | **Resolvido** ([SEC-03](../falhas-de-seguranca.md#sec-03)) |
| 4 | ~~Scripts de CDN sem integridade~~ | views com QR e PDF | **Resolvido**: servidos localmente ([SEC-04](../falhas-de-seguranca.md#sec-04)) |
| 5 | Chamada depende do cache | `PresencaController` | Ver abaixo |
| 6 | Presença por cumplicidade (repasse ao vivo do link) | fluxo do QR | Rede ou localização ([SEC-05](../falhas-de-seguranca.md#sec-05)) |

### Sobre o item 5: a chamada vive no cache

O estado de "chamada aberta" (código, validade e professor) fica só no cache (`aula_materia_{id}_{data}`), com o driver `database` em produção. Funciona, mas: (a) limpar o cache fecha todas as chamadas abertas; (b) a lógica de achar essa chave aparece em três métodos do controller; (c) não há registro permanente de que a chamada existiu, só das presenças. **Sugestão:** uma tabela `aulas` (matéria, professor, código, abertura, validade) e uma classe pequena `AulaAtiva` que concentra a busca. Também habilita o relatório "aulas realizadas" sem depender do cache.

## Dívida técnica

- **Atributos inline.** Cerca de 58 `onclick=`/`onchange=` em 22 views e 539 `style="..."`. Impedem uma CSP totalmente estrita ([SEC-08](../falhas-de-seguranca.md#sec-08)).
- **Views muito grandes**, com JavaScript e CSS dentro do Blade. Extrair scripts para `resources/js` e usar componentes Blade para o que se repete (a barra lateral é o maior caso).
- **Dois arquivos de teste cobrem a mesma coisa** (`PresencaTest` e `QrCodePresencaTest`) com preparos de dados duplicados. Vale unificar em *helpers* ou *factories*.
- **Horário e semestre da presença** são calculados no momento da confirmação, não pela aula. Uma presença registrada depois da meia-noite ou na virada de turno pode ficar com o turno errado.
- **Sem lint nem análise estática** no CI (Pint instalado, sem `pint.json`). Detalhes em [Revisão das Actions](../revisao-github-actions.md).
- **Configuração de ambiente inconsistente:** tempo de sessão com três valores ([SEC-10](../falhas-de-seguranca.md#sec-10)); `.env.example` com padrões de desenvolvimento ([SEC-09](../falhas-de-seguranca.md#sec-09)).
- **Sem LICENSE no repositório**, apesar de o `composer.json` declarar MIT. Resolvido nesta revisão.

## O que mudou nesta revisão

| Mudança | Arquivos principais |
|---|---|
| **QR Code que muda a cada 30 s.** Token HMAC na URL, janela atual + anterior; a tela do professor renova sozinha, com contagem regressiva | `app/Support/QrToken.php`, `PresencaController`, `professor/presenca/gerar.blade.php`, rota `professor.presenca.token` |
| **Content-Security-Policy endurecida.** *Nonce* por requisição em todo `<script>` e `<style>`, sem `unsafe-inline` em scripts e blocos de estilo, mais `object-src`, `base-uri`, `form-action`, `frame-ancestors`; middleware agora global (404 também) | `SecurityHeaders`, diretiva `@nonce` no `AppServiceProvider`, 25 views |
| **LICENSE (MIT)** | `LICENSE`, README |
| **Documentos novos** | `docs/falhas-de-seguranca.md`, este relatório, `docs/revisao-github-actions.md` |
| **Testes novos** | `QrRotativoTest` (9), `SecurityHeadersTest` (5, reescrito) e `CspCoberturaTest` (4, cerca de 30 telas) |

### Comportamento novo que a equipe precisa saber

- Quem escaneia o QR Code **tem de fazê-lo na hora**: o link vale de 30 a 60 segundos. Quem não estava logado e leva mais que isso para entrar vê "QR Code expirou" e escaneia de novo.
- O botão **Copiar link** entrega o link do momento, também de vida curta.
- Cada professor com a tela de chamada aberta faz 1 requisição a cada 30 s (limite de 60 por minuto), além das 20 por minuto da lista de presentes.

## Próximos passos recomendados

1. Conectar o deploy da Vercel ao GitHub: **o Átila precisa instalar o app da Vercel** (a conta da Vercel não enxerga o repositório dele). Antes, cadastrar variáveis de *Preview* com um banco de teste.
2. Conferir as telas num navegador real com a nova CSP: o teste automatizado `CspCoberturaTest` já cobre o nonce em cerca de 30 telas dos quatro perfis.
3. Definir `trustHosts` e `APP_URL`; empacotar `qrcodejs` e `jspdf` pelo Vite.
4. Tabela `aulas` + classe `AulaAtiva`.
5. Migrar `onclick=`/`style=` e remover as exceções `*-attr` da CSP.
6. 2FA para Master e professores.
