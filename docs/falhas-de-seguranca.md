# Falhas de segurança conhecidas

Registro das falhas e fraquezas de segurança **identificadas e ainda abertas** (ou só parcialmente resolvidas) no Smart Attendance. Complementa [Segurança](seguranca.md), que descreve as proteções que já existem.

- **Última revisão:** novembro de 2026, sobre o commit `6411a3e`.
- **Como foi levantado:** `composer audit`, `npm audit`, leitura do código (`bootstrap/app.php`, `routes/web.php`, `PresencaController`, views) e teste manual no navegador.
- **Gravidade:** *Alta* = explorável hoje com impacto real; *Média* = exige condição extra ou tem impacto limitado; *Baixa* = endurecimento.
- Para reportar uma falha nova, veja [Como reportar](#como-reportar).

## Resumo

| ID | Falha | Gravidade | Situação |
|---|---|---|---|
| [SEC-01](#sec-01) | Dependências PHP com 8 avisos de segurança (Symfony) | Alta | **Resolvida** |
| [SEC-02](#sec-02) | Conta Master da demonstração com senha pública | Alta | Aberta |
| [SEC-03](#sec-03) | `trustProxies('*')` sem `trustHosts` | Média | **Resolvida** |
| [SEC-04](#sec-04) | Scripts de CDN sem Subresource Integrity | Média | **Resolvida** |
| [SEC-05](#sec-05) | Link da chamada pode ser repassado ao vivo a quem está fora da sala | Média | Parcial |
| [SEC-06](#sec-06) | Dependências npm com 6 avisos (2 críticos, só no build/dev) | Média | **Resolvida** |
| [SEC-07](#sec-07) | Sem autenticação em dois fatores | Média | Aberta |
| [SEC-08](#sec-08) | CSP ainda libera `onclick=` e `style=` inline | Baixa | Parcial |
| [SEC-09](#sec-09) | Padrões inseguros no `.env.example` | Baixa | Aberta |
| [SEC-10](#sec-10) | Tempo de sessão documentado de formas diferentes | Baixa | Aberta |
| [SEC-11](#sec-11) | PHP em produção possivelmente sem a correção das CVE-2026-109618 e CVE-2026-109619 | Alta (não confirmada) | Aberta |

---

## SEC-01
### Dependências PHP com avisos de segurança

> **Resolvida** em novembro de 2026: `symfony/http-foundation`, `http-kernel`, `mime`, `routing` e `yaml` foram atualizados para a série 7.4.22/7.4.23 e o `composer audit` não aponta mais avisos. O texto abaixo registra o que foi encontrado.

`composer audit` aponta 8 avisos, todos em pacotes Symfony que o Laravel 12 usa por baixo:

| Pacote | Aviso | Resumo |
|---|---|---|
| `symfony/http-foundation` | CVE-2026-118514 | Requisição `HEAD` contorna regras de método |
| `symfony/http-foundation` | PKSA-pr6q-2w5s-kzzf | Regras de caminho aplicadas antes do PHP podem ser contornadas |
| `symfony/http-foundation` | PKSA-nxjs-s6hn-vb6m | Atalho `private_ranges` de proxies confiáveis aceita endereços IPv4-mapeados, 6to4, Teredo e NAT64 |
| `symfony/http-foundation` | PKSA-b2gg-vk1g-6d5q | URLs geradas podem apontar para um host escolhido pelo cliente |
| `symfony/http-kernel` | PKSA-3gxv-7xhq-mgmz | Fragmentos inline deixam cabeçalhos `Forwarded` não confiáveis definirem host, esquema e porta |
| `symfony/mime` | CVE-2026-45070 | Injeção de cabeçalho de e-mail por nomes de parâmetro MIME |
| `symfony/routing` | CVE-2026-118516 | Placeholders de host casam com o início do caminho e contornam controle de acesso por caminho |
| `symfony/yaml` | PKSA-nktx-f1n1-b4yd | Tempo de parsing exponencial em um único documento YAML |

**Impacto:** os dois de `http-foundation` sobre host e proxy combinam com [SEC-03](#sec-03). O de `mime` afeta os e-mails de primeiro acesso e recuperação de senha.
**Correção aplicada:** `composer update symfony/http-foundation symfony/http-kernel symfony/mime symfony/routing symfony/yaml --with-all-dependencies`, suíte completa e `composer audit` conferidos.
**No CI:** o módulo 3 roda `composer audit` e falhava enquanto o aviso estava aberto.

## SEC-02
### Conta Master da demonstração com senha pública

O README publica `master@admin.com` / `senha123` para a demonstração em `smart-attendance-nine-alpha.vercel.app`. Qualquer pessoa entra como administrador e vê o painel inteiro, inclusive o relatório de segurança e o cadastro de usuários. Os dados são fictícios, mas a mesma senha fraca vale para os três perfis.

**Impacto:** alto se a demonstração e a produção compartilharem banco, segredos ou `APP_KEY`; moderado se estiverem isoladas.
**Correção:** manter a demo num banco e numa `APP_KEY` próprios, sem dados reais; trocar a senha do Master e informá-la só por canal privado; ou publicar só os perfis Aluno e Professor; reiniciar a demo periodicamente (o README já avisa que ela pode ser reiniciada).

## SEC-03
### `trustProxies('*')` sem `trustHosts`

> **Resolvida** em novembro de 2026: [`HostsConfiaveis`](../app/Support/HostsConfiaveis.php) lista os domínios aceitos e `trustHosts` em `bootstrap/app.php` devolve **400** para qualquer outro `Host` ou `X-Forwarded-Host` (conferido com o app em `APP_ENV=production`: `evil.com` e `evil.vercel.app` recebem 400). Não há curinga `*.vercel.app`. **Domínio novo** (por exemplo, um da escola) precisa entrar na variável `TRUSTED_HOSTS`, senão o site responde 400. O texto abaixo registra o que foi encontrado.

Em [`bootstrap/app.php`](../bootstrap/app.php) o sistema confia em qualquer proxy (`trustProxies(at: '*')`) e não define hosts confiáveis. Um cliente pode enviar `X-Forwarded-Host` ou `Host` próprios, e o Laravel gera URLs com esse valor. Isso afeta os links do QR Code e os links dos e-mails de primeiro acesso e de recuperação de senha, e dá base para *host header poisoning* (um e-mail de recuperação com link para outro domínio).

**Correção:** definir `APP_URL` correto em produção e adicionar `$middleware->trustHosts(at: [...])` com os domínios reais; na Vercel, restringir a confiança de proxy aos cabeçalhos que a plataforma realmente envia. Corrigir [SEC-01](#sec-01) também.

## SEC-04
### Scripts de CDN sem Subresource Integrity

> **Resolvida** em novembro de 2026: `qrcodejs`, `jspdf` e `jspdf-autotable` agora são servidos de `public/js/vendor/`, cópias sem alteração cujo SHA-512 é idêntico ao publicado pela cdnjs. A CSP não libera mais nenhuma CDN de scripts, e o `VendorIntegrityTest` falha se um arquivo for alterado. O texto abaixo registra o que foi encontrado.

`qrcodejs`, `jspdf` e `jspdf-autotable` são carregados de `cdnjs.cloudflare.com` sem o atributo `integrity` (nenhuma view usa SRI). Se a CDN ou a conta do autor for comprometida, o script malicioso roda dentro do sistema, inclusive na tela do professor, com acesso à sessão. A CSP libera o host `cdnjs.cloudflare.com` inteiro, não um arquivo específico.

**Correção:** instalar as bibliotecas via `npm` e entregá-las pelo Vite (some a dependência externa e o host sai da CSP); ou, no mínimo, acrescentar `integrity="sha384-..."` e `crossorigin="anonymous"` a cada tag.

## SEC-05
### Link da chamada pode ser repassado ao vivo

Desde a rotação do QR Code (token que muda a cada 30 segundos), uma **foto ou print** deixa de valer em até 60 segundos. Continua possível, porém, um aluno presente mandar *ao vivo* o link ou o QR Code a um colega ausente, que confirma dentro da janela. O botão **Copiar link** (pensado para quem está sem câmera) facilita isso.

**Impacto:** presença falsa por cumplicidade, não por ataque técnico.
**Mitigação possível:** limitar a presença à rede da instituição ou à localização aproximada; reduzir `QrToken::STEP`; permitir ao professor desativar o link copiável; relatório de presenças por horário para o professor ver registros fora de padrão (muitos no mesmo segundo).

## SEC-06
### Dependências npm com avisos

> **Resolvida** em novembro de 2026 com `npm audit fix`: `npm audit` mostra 0 vulnerabilidades, o build e os testes passam. O texto abaixo registra o que foi encontrado.

`npm audit` aponta 6 avisos: `concurrently` e `shell-quote` (críticos), `axios`, `follow-redirects`, `source-map-js` e `vite` (altos). Todos têm correção por `npm audit fix`. São dependências de **build e desenvolvimento**: não rodam no servidor de produção, e o aviso do Vite vale para o servidor de desenvolvimento. O risco real é o ambiente de quem desenvolve e o CI.

**Correção aplicada:** `npm audit fix`, `npm run build` e suíte de testes.

## SEC-07
### Sem autenticação em dois fatores

Uma senha vazada dá acesso total à conta, inclusive de professores e do Master. Já listado em [Segurança](seguranca.md#limitações-conhecidas).
**Correção:** 2FA (TOTP) para Master e professores.

## SEC-08
### CSP ainda libera `onclick=` e `style=` inline

A Content-Security-Policy agora bloqueia `<script>` e `<style>` injetados (cada bloco legítimo leva um *nonce* novo por requisição). Ainda estão liberados por `script-src-attr 'unsafe-inline'` cerca de **58 atributos `onclick=`/`onchange=`** em 22 views e por `style-src-attr 'unsafe-inline'` cerca de **539 atributos `style="..."`**. Um atacante que consiga injetar HTML ainda poderia executar JavaScript por um atributo de evento.

**Correção:** trocar cada `onclick="..."` por `addEventListener` em um script com nonce (ou em `resources/js`) e mover os `style=` para classes CSS; depois remover as duas linhas `*-attr` de [`SecurityHeaders`](../app/Http/Middleware/SecurityHeaders.php).

## SEC-09
### Padrões inseguros no `.env.example`

O arquivo de exemplo vem com `APP_DEBUG=true`, `LOG_LEVEL=debug`, `SESSION_ENCRYPT=false`, `DB_SSLMODE=prefer` e sem `SESSION_SECURE_COOKIE`. Quem copiar o arquivo para produção sem revisar expõe páginas de erro detalhadas, loga demais e aceita banco sem TLS.

**Correção:** comentar cada variável de produção no arquivo; usar `DB_SSLMODE=require` como padrão; e falhar a inicialização (ou registrar alerta) se `APP_ENV=production` com `APP_DEBUG=true`.

## SEC-10
### Tempo de sessão inconsistente

O README fala em sessão de 1 hora, o `.env.example` define `SESSION_LIFETIME=30` e o `config/session.php` usa 120 como padrão. A sessão efetiva depende do `.env` de cada ambiente, o que torna o comportamento imprevisível.
**Correção:** escolher um valor, aplicá-lo nos três lugares e documentá-lo em [Segurança](seguranca.md).

## SEC-11
### PHP em produção possivelmente sem a correção das CVEs críticas

Em 21 de outubro de 2026 foram divulgadas duas vulnerabilidades **no interpretador PHP** (CVSS 9.2), que podem permitir execução remota de código, inclusive sem autenticação:

| CVE | Resumo |
|---|---|
| CVE-2026-109618 | Corrupção de memória quando o PHP ultrapassa o `memory_limit`: a limpeza dos recursos manipula estruturas parcialmente inicializadas |
| CVE-2026-109619 | Estado interno residual, deixado após a liberação de memória, é reaproveitado por uma requisição seguinte no mesmo *worker* |

Versões corrigidas: **8.2.35, 8.3.36, 8.4.27 e 8.5.12**. Fonte: CERT-EU, *Security Advisory 2026-018*, e os avisos GHSA-w8hw-w9fx-j79w e GHSA-6wmf-4rv5-p3rp do projeto PHP. A [análise de segurança](#referencias-desta-falha) do projeto concluiu que **não há falha no código do Smart Attendance**: o risco está no PHP em execução.

**Situação no projeto (verificada em novembro de 2026):**

| Onde | PHP | Corrigido? |
|---|---|---|
| `composer.json` | exige `^8.2.35` e `platform.php` 8.2.35 | Só impede instalar em PHP antigo; **não muda o PHP de produção** |
| CI (GitHub Actions) | série 8.4 (`setup-php` instala a última 8.4.x) | Provavelmente sim; versão exata não registrada |
| Máquina de desenvolvimento | 8.5.10 (conferido em `php -v`) | **Não**: abaixo de 8.5.12 |
| **Produção (Vercel)** | embutido no runtime `vercel-php@0.9.0` | **Não confirmado.** Esse runtime foi publicado pela última vez em 15/jan/2026, antes das correções |

**Impacto:** se o runtime de produção usar um PHP anterior às versões corrigidas, o sistema inteiro (dados pessoais criptografados, sessões, contas Master) fica exposto a uma falha que nenhuma proteção da aplicação impede. A exposição **não foi comprovada**: ninguém conseguiu ler a versão do PHP em produção.

**Como confirmar e corrigir:**
1. **Descobrir a versão em produção:** entre como Master e abra `/dashboard/master/ambiente/php` no site publicado. A resposta (JSON) traz `php_versao`, a primeira versão corrigida da série e `cves_corrigidas` (`true`, `false` ou `null` se a série for mais nova que as conhecidas). Só versões e flags: nenhum segredo. A rota fica disponível depois do próximo deploy de produção.
2. Se for anterior às corrigidas: procurar uma versão mais nova do runtime; se não houver, trocar a hospedagem por uma imagem oficial do PHP (por exemplo `php:8.4` em contêiner), mantendo o banco no Supabase.
3. Atualizar o PHP das máquinas de desenvolvimento (`brew upgrade php`, 8.5.12 ou superior).
4. Registrar a versão em uso, a data e os testes feitos depois da atualização.

Medidas temporárias de mitigação não substituem a atualização do PHP.

#### Referências desta falha
- *Relatório de Análise de Segurança do Smart Attendance* e *Relatório das Modificações Executadas no Smart Attendance* (Átila Alves Rodrigues, novembro de 2026).
- CERT-EU, *Critical vulnerabilities in PHP*, Security Advisory 2026-018: https://cert.europa.eu/publications/security-advisories/2026-018/

---

## Resolvidas nesta revisão

| O quê | Como |
|---|---|
| Oito avisos do `composer audit` (Symfony) | Atualização dos pacotes ([SEC-01](#sec-01)) |
| Seis avisos do `npm audit` | `npm audit fix` ([SEC-06](#sec-06)) |
| Host header poisoning | `trustHosts` com lista exata de domínios ([SEC-03](#sec-03)) |
| Scripts de CDN sem integridade | Bibliotecas servidas pelo próprio site, com teste de hash ([SEC-04](#sec-04)) |
| QR Code vivo por 2 horas e reaproveitável por foto | Token HMAC de 30 s na URL ([`QrToken`](../app/Support/QrToken.php)); tolerância de uma janela. Resta [SEC-05](#sec-05). |
| Ausência de Content-Security-Policy / CSP com `unsafe-inline` em scripts | CSP com *nonce* por requisição, `object-src 'none'`, `base-uri`, `form-action` e `frame-ancestors`. Resta [SEC-08](#sec-08). |
| Cabeçalhos de segurança fora das páginas 404 | `SecurityHeaders` agora é middleware global. |

## Como reportar

Se encontrar uma falha, **não abra uma issue pública** com detalhes de exploração. Avise a equipe em particular (e-mail do mantenedor no perfil do GitHub) com o passo a passo para reproduzir. Depois da correção, o registro entra neste documento.
