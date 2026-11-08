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
| [SEC-03](#sec-03) | `trustProxies('*')` sem `trustHosts` | Média | Aberta |
| [SEC-04](#sec-04) | Scripts de CDN sem Subresource Integrity | Média | Aberta |
| [SEC-05](#sec-05) | Link da chamada pode ser repassado ao vivo a quem está fora da sala | Média | Parcial |
| [SEC-06](#sec-06) | Dependências npm com 6 avisos (2 críticos, só no build/dev) | Média | Aberta |
| [SEC-07](#sec-07) | Sem autenticação em dois fatores | Média | Aberta |
| [SEC-08](#sec-08) | CSP ainda libera `onclick=` e `style=` inline | Baixa | Parcial |
| [SEC-09](#sec-09) | Padrões inseguros no `.env.example` | Baixa | Aberta |
| [SEC-10](#sec-10) | Tempo de sessão documentado de formas diferentes | Baixa | Aberta |

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

Em [`bootstrap/app.php`](../bootstrap/app.php) o sistema confia em qualquer proxy (`trustProxies(at: '*')`) e não define hosts confiáveis. Um cliente pode enviar `X-Forwarded-Host` ou `Host` próprios, e o Laravel gera URLs com esse valor. Isso afeta os links do QR Code e os links dos e-mails de primeiro acesso e de recuperação de senha, e dá base para *host header poisoning* (um e-mail de recuperação com link para outro domínio).

**Correção:** definir `APP_URL` correto em produção e adicionar `$middleware->trustHosts(at: [...])` com os domínios reais; na Vercel, restringir a confiança de proxy aos cabeçalhos que a plataforma realmente envia. Corrigir [SEC-01](#sec-01) também.

## SEC-04
### Scripts de CDN sem Subresource Integrity

`qrcodejs`, `jspdf` e `jspdf-autotable` são carregados de `cdnjs.cloudflare.com` sem o atributo `integrity` (nenhuma view usa SRI). Se a CDN ou a conta do autor for comprometida, o script malicioso roda dentro do sistema, inclusive na tela do professor, com acesso à sessão. A CSP libera o host `cdnjs.cloudflare.com` inteiro, não um arquivo específico.

**Correção:** instalar as bibliotecas via `npm` e entregá-las pelo Vite (some a dependência externa e o host sai da CSP); ou, no mínimo, acrescentar `integrity="sha384-..."` e `crossorigin="anonymous"` a cada tag.

## SEC-05
### Link da chamada pode ser repassado ao vivo

Desde a rotação do QR Code (token que muda a cada 30 segundos), uma **foto ou print** deixa de valer em até 60 segundos. Continua possível, porém, um aluno presente mandar *ao vivo* o link ou o QR Code a um colega ausente, que confirma dentro da janela. O botão **Copiar link** (pensado para quem está sem câmera) facilita isso.

**Impacto:** presença falsa por cumplicidade, não por ataque técnico.
**Mitigação possível:** limitar a presença à rede da instituição ou à localização aproximada; reduzir `QrToken::STEP`; permitir ao professor desativar o link copiável; relatório de presenças por horário para o professor ver registros fora de padrão (muitos no mesmo segundo).

## SEC-06
### Dependências npm com avisos

`npm audit` aponta 6 avisos: `concurrently` e `shell-quote` (críticos), `axios`, `follow-redirects`, `source-map-js` e `vite` (altos). Todos têm correção por `npm audit fix`. São dependências de **build e desenvolvimento**: não rodam no servidor de produção, e o aviso do Vite vale para o servidor de desenvolvimento. O risco real é o ambiente de quem desenvolve e o CI.

**Correção:** `npm audit fix`, `npm run build` e rodar os testes.

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

---

## Resolvidas nesta revisão

| O quê | Como |
|---|---|
| Oito avisos do `composer audit` (Symfony) | Atualização dos pacotes ([SEC-01](#sec-01)) |
| QR Code vivo por 2 horas e reaproveitável por foto | Token HMAC de 30 s na URL ([`QrToken`](../app/Support/QrToken.php)); tolerância de uma janela. Resta [SEC-05](#sec-05). |
| Ausência de Content-Security-Policy / CSP com `unsafe-inline` em scripts | CSP com *nonce* por requisição, `object-src 'none'`, `base-uri`, `form-action` e `frame-ancestors`. Resta [SEC-08](#sec-08). |
| Cabeçalhos de segurança fora das páginas 404 | `SecurityHeaders` agora é middleware global. |

## Como reportar

Se encontrar uma falha, **não abra uma issue pública** com detalhes de exploração. Avise a equipe em particular (e-mail do mantenedor no perfil do GitHub) com o passo a passo para reproduzir. Depois da correção, o registro entra neste documento.
