# Como contribuir

Obrigado por ajudar no Smart Attendance. Este guia resume como o time trabalha.

## Preparando o ambiente

Siga o [guia de instalação](docs/instalacao.md) e confirme que os testes passam:

```bash
composer test
```

## Fluxo de trabalho

1. **Crie uma branch a partir da `main`**, com um prefixo que diga o tipo da mudança:

   | Prefixo | Uso |
   |---|---|
   | `feat/` | Funcionalidade nova |
   | `fix/` | Correção de bug |
   | `docs/` | Documentação |
   | `melhoria/` | Ajustes de interface ou de usabilidade |
   | `chore/` | Dependências, configuração, limpeza |

2. **Faça commits pequenos**, no padrão [Conventional Commits](https://www.conventionalcommits.org/pt-br/), em português:

   ```
   fix: QR Code de presença não funcionava entre 21h e 0h

   Explique o problema e a causa, e não só o que mudou no código.
   ```

   Tipos usados: `feat`, `fix`, `docs`, `test`, `ci`, `chore`, `refactor`, `style`. Um escopo opcional ajuda: `fix(master): ...`, `feat(aluno): ...`.

3. **Abra um Pull Request para a `main`** e preencha o modelo. O pipeline do GitHub Actions precisa passar antes do merge.

## Padrões de código

- **Leia o [`.cursorrules`](.cursorrules)** antes de mexer em telas: ele traz o padrão visual, os bugs já resolvidos e o checklist de regressão.
- **Dados pessoais**: nunca busque por colunas criptografadas (`email`, `cpf`, `ra`). Use o *blind index*:
  `Model::where('email_search', Model::generateBlindIndex($valor))`.
- **Frequência, faltas e notas**: use sempre [`App\Support\SituacaoAcademica`](app/Support/SituacaoAcademica.php). Não recalcule em views ou controllers.
- **JavaScript**: dados vindos do banco entram com `textContent`, nunca com `innerHTML`.
- **Logs**: não registre e-mail, CPF, RA, senhas ou tokens.
- **Formulários**: sempre com `@csrf`; em AJAX, envie o cabeçalho `X-CSRF-TOKEN`.
- **Estilo PHP**: siga o [Laravel Pint](https://laravel.com/docs/pint) (`./vendor/bin/pint`).

## Testes

- Toda correção de bug vem com um teste que falharia sem a correção.
- Funcionalidade nova vem com teste do caminho feliz e dos principais erros.
- Testes de fluxo ficam em `tests/Feature`; regras isoladas, em `tests/Unit`.

Mais detalhes em [Testes e CI](docs/testes-e-ci.md).

## Documentação

Ao mudar um comportamento, atualize o documento correspondente em [`docs/`](docs/README.md) e registre a mudança no [`CHANGELOG.md`](CHANGELOG.md), na seção *Não lançado*.

## Reportando problemas

Use as [issues do repositório](https://github.com/AtilaAlvesRodrigues/smart-attendance/issues/new/choose), com o modelo de bug ou de sugestão. Para falhas de segurança, fale direto com o responsável pelo repositório, sem abrir issue pública.
