<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Testes - Sistema de Presença QR Code</title>
    <link rel="stylesheet" href="{{ asset('css/report_style.css') }}">
    <!-- Fallback/Additional print styles if needed inline, but keeping it clean -->
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Relatório de Testes de Sistema de Presença QR Code</h1>
            <div class="info">
                <p><strong>Sistema Testado:</strong> Módulo de Login, Chamada via QR Code e Relatórios Administrativos</p>
                <p><strong>Versão:</strong> 2.0 - Expandido com Perfil Master e Relatórios</p>
            </div>
        </div>

        <div class="section">
            <h2>1. Resumo Executivo</h2>
            <div class="summary-box">
                <p><strong>Total de Casos de Teste:</strong> 24</p>
                <p><strong>Testes Aprovados (PASS):</strong> 19</p>
                <p><strong>Vulnerabilidades Identificadas (FAIL):</strong> 5</p>
                <p><strong>Taxa de Sucesso:</strong> 79.2%</p>
            </div>
            
            <div class="vulnerability-box">
                <h4>⚠️ Vulnerabilidades Críticas Detectadas</h4>
                <p>Foram identificadas 5 brechas de segurança no sistema que requerem correção imediata:</p>
                <ul>
                    <li>Falta de rate limiting no login</li>
                    <li>Sessão sem timeout automático</li>
                    <li>Ausência de logs de auditoria para ações Master</li>
                    <li>QR Code sem assinatura criptográfica</li>
                    <li>Falta de validação de origem de requisições</li>
                </ul>
            </div>
        </div>

        <div class="section">
            <h2>2. Casos de Teste - Especificação Detalhada</h2>
            
            <h3>A. Testes da Funcionalidade de Login (Validação de Perfil e Credenciais)</h3>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Natureza</th>
                        <th>Objetivo do Teste</th>
                        <th>Ação Executada</th>
                        <th>Resultado Esperado</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>LT-01</strong></td>
                        <td><span class="test-positive">Positivo</span></td>
                        <td>Acesso via CPF (Aluno)</td>
                        <td>Login de Aluno via CPF</td>
                        <td>Redirecionar para tela de Leitura</td>
                        <td><span class="status-pass">PASS</span></td>
                    </tr>
                    <tr>
                        <td><strong>LT-02</strong></td>
                        <td><span class="test-positive">Positivo</span></td>
                        <td>Acesso via RA (Aluno)</td>
                        <td>Login de Aluno via RA</td>
                        <td>Redirecionar para tela de Leitura</td>
                        <td><span class="status-pass">PASS</span></td>
                    </tr>
                    <tr>
                        <td><strong>LT-03</strong></td>
                        <td><span class="test-positive">Positivo</span></td>
                        <td>Acesso via Email Institucional (Aluno)</td>
                        <td>Login com email @aluno.instituicao.edu.br</td>
                        <td>Redirecionar para tela de Leitura</td>
                        <td><span class="status-pass">PASS</span></td>
                    </tr>
                    <tr>
                        <td><strong>LT-04</strong></td>
                        <td><span class="test-positive">Positivo</span></td>
                        <td>Acesso via CPF (Professor)</td>
                        <td>Login de Professor via CPF</td>
                        <td>Redirecionar para tela de Geração</td>
                        <td><span class="status-pass">PASS</span></td>
                    </tr>
                    <tr>
                        <td><strong>LT-05</strong></td>
                        <td><span class="test-positive">Positivo</span></td>
                        <td>Acesso via Email Institucional (Professor)</td>
                        <td>Login com email @professor.instituicao.edu.br</td>
                        <td>Redirecionar para tela de Geração</td>
                        <td><span class="status-pass">PASS</span></td>
                    </tr>
                    <tr>
                        <td><strong>LT-06</strong></td>
                        <td><span class="test-positive">Positivo</span></td>
                        <td>Acesso via Email (Master)</td>
                        <td>Login com credenciais Master</td>
                        <td>Redirecionar para Dashboard Master</td>
                        <td><span class="status-pass">PASS</span></td>
                    </tr>
                    <tr class="alert-critical">
                        <td><strong>LT-07</strong></td>
                        <td><span class="test-vulnerability">Vulnerabilidade</span></td>
                        <td>Brute Force - Rate Limiting</td>
                        <td>Tentativas de login ilimitadas em 1 min</td>
                        <td>Bloquear após 5 tentativas</td>
                        <td><span class="status-fail">FAIL</span></td>
                    </tr>
                    <tr>
                        <td><strong>LT-08</strong></td>
                        <td><span class="test-security">Negativo/Segurança</span></td>
                        <td>Impedir Aluno de acessar Professor</td>
                        <td>Aluno tenta acessar /professor/painel</td>
                        <td>Bloqueio com erro de permissão</td>
                        <td><span class="status-pass">PASS</span></td>
                    </tr>
                    <tr>
                        <td><strong>LT-09</strong></td>
                        <td><span class="test-security">Negativo/Segurança</span></td>
                        <td>Impedir Professor de acessar Aluno</td>
                        <td>Professor tenta acessar /aluno/painel</td>
                        <td>Bloqueio com erro de permissão</td>
                        <td><span class="status-pass">PASS</span></td>
                    </tr>
                    <tr>
                        <td><strong>LT-10</strong></td>
                        <td><span class="test-security">Negativo/Segurança</span></td>
                        <td>Impedir acesso não autorizado ao Master</td>
                        <td>Aluno/Professor tenta acessar /master/dashboard</td>
                        <td>Bloqueio com erro 403 Forbidden</td>
                        <td><span class="status-pass">PASS</span></td>
                    </tr>
                    <tr>
                        <td><strong>LT-11</strong></td>
                        <td><span class="test-negative">Negativo</span></td>
                        <td>Credenciais Inválidas</td>
                        <td>Login com dados inexistentes</td>
                        <td>Exibir erro de credenciais inválidas</td>
                        <td><span class="status-pass">PASS</span></td>
                    </tr>
                    <tr class="alert-critical">
                        <td><strong>LT-12</strong></td>
                        <td><span class="test-vulnerability">Vulnerabilidade</span></td>
                        <td>Timeout de Sessão</td>
                        <td>Sessão ativa sem atividade por 8 horas</td>
                        <td>Logout automático após 30 min inativo</td>
                        <td><span class="status-fail">FAIL</span></td>
                    </tr>
                </tbody>
            </table>

            <div class="page-break"></div>

            <h3>B. Testes da Funcionalidade de Geração e Chamada (Validação de Regras)</h3>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Natureza</th>
                        <th>Objetivo do Teste</th>
                        <th>Pré-condições</th>
                        <th>Resultado Esperado</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>CT-01</strong></td>
                        <td><span class="test-positive">Positivo</span></td>
                        <td>Sucesso: Wi-Fi e Tempo OK</td>
                        <td>Aluno logado, Wi-Fi Institucional, QR válido (5 min)</td>
                        <td>Registrar presença com sucesso</td>
                        <td><span class="status-pass">PASS</span></td>
                    </tr>
                    <tr>
                        <td><strong>CT-02</strong></td>
                        <td><span class="test-negative">Negativo</span></td>
                        <td>Falha: Fora do Wi-Fi Institucional</td>
                        <td>Aluno em rede externa (4G)</td>
                        <td>Bloqueio com erro de rede</td>
                        <td><span class="status-pass">PASS</span></td>
                    </tr>
                    <tr>
                        <td><strong>CT-03</strong></td>
                        <td><span class="test-negative">Negativo</span></td>
                        <td>Falha: Tempo Expirado</td>
                        <td>QR Code gerado há 31 minutos</td>
                        <td>Bloqueio com erro de expiração</td>
                        <td><span class="status-pass">PASS</span></td>
                    </tr>
                    <tr class="alert-critical">
                        <td><strong>CT-04</strong></td>
                        <td><span class="test-vulnerability">Vulnerabilidade</span></td>
                        <td>QR Code sem Assinatura Digital</td>
                        <td>QR Code gerado manualmente</td>
                        <td>Rejeitar QR sem assinatura válida</td>
                        <td><span class="status-fail">FAIL</span></td>
                    </tr>
                    <tr>
                        <td><strong>CT-05</strong></td>
                        <td><span class="test-positive">Positivo</span></td>
                        <td>Confirmação de Presença (Aluno)</td>
                        <td>Escanear código com sucesso</td>
                        <td>Exibir Card de confirmação</td>
                        <td><span class="status-pass">PASS</span></td>
                    </tr>
                    <tr class="alert-critical">
                        <td><strong>CT-06</strong></td>
                        <td><span class="test-vulnerability">Vulnerabilidade</span></td>
                        <td>Validação de Origem</td>
                        <td>Requisição de presença via Postman</td>
                        <td>Bloquear requisições externas</td>
                        <td><span class="status-fail">FAIL</span></td>
                    </tr>
                </tbody>
            </table>

            <h3>C. Testes de Relatórios - Professor</h3>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Natureza</th>
                        <th>Objetivo do Teste</th>
                        <th>Ação Executada</th>
                        <th>Resultado Esperado</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>RT-01</strong></td>
                        <td><span class="test-positive">Positivo</span></td>
                        <td>Visualizar Resumo da Aula</td>
                        <td>Professor finaliza aula com registros</td>
                        <td>Exibir Card com lista de presentes</td>
                        <td><span class="status-pass">PASS</span></td>
                    </tr>
                    <tr>
                        <td><strong>RT-02</strong></td>
                        <td><span class="test-positive">Positivo</span></td>
                        <td>Gerar PDF Mensal - Própria Matéria</td>
                        <td>Professor seleciona sua matéria e mês</td>
                        <td>Gerar PDF com presença/falta do mês</td>
                        <td><span class="status-pass">PASS</span></td>
                    </tr>
                    <tr>
                        <td><strong>RT-03</strong></td>
                        <td><span class="test-security">Negativo/Segurança</span></td>
                        <td>Impedir acesso a matéria não vinculada</td>
                        <td>Professor tenta acessar matéria de outro</td>
                        <td>Bloqueio: "Sem permissão"</td>
                        <td><span class="status-pass">PASS</span></td>
                    </tr>
                    <tr>
                        <td><strong>RT-04</strong></td>
                        <td><span class="test-positive">Positivo</span></td>
                        <td>Visualizar Histórico de Aluno</td>
                        <td>Professor consulta faltas de aluno no mês</td>
                        <td>Exibir dias de falta do aluno</td>
                        <td><span class="status-pass">PASS</span></td>
                    </tr>
                </tbody>
            </table>

            <h3>D. Testes de Relatórios - Master</h3>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Natureza</th>
                        <th>Objetivo do Teste</th>
                        <th>Ação Executada</th>
                        <th>Resultado Esperado</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>MT-01</strong></td>
                        <td><span class="test-positive">Positivo</span></td>
                        <td>Acesso Total a Matérias</td>
                        <td>Master seleciona qualquer matéria</td>
                        <td>Listar todas as matérias do sistema</td>
                        <td><span class="status-pass">PASS</span></td>
                    </tr>
                    <tr>
                        <td><strong>MT-02</strong></td>
                        <td><span class="test-positive">Positivo</span></td>
                        <td>Acesso Total a Professores</td>
                        <td>Master seleciona qualquer professor</td>
                        <td>Listar todos os professores</td>
                        <td><span class="status-pass">PASS</span></td>
                    </tr>
                    <tr>
                        <td><strong>MT-03</strong></td>
                        <td><span class="test-positive">Positivo</span></td>
                        <td>Gerar PDF por Matéria</td>
                        <td>Master seleciona matéria e mês</td>
                        <td>Gerar PDF com todas as presenças/faltas</td>
                        <td><span class="status-pass">PASS</span></td>
                    </tr>
                    <tr>
                        <td><strong>MT-04</strong></td>
                        <td><span class="test-positive">Positivo</span></td>
                        <td>Gerar PDF por Professor</td>
                        <td>Master seleciona professor e mês</td>
                        <td>Gerar PDF consolidado do professor</td>
                        <td><span class="status-pass">PASS</span></td>
                    </tr>
                    <tr>
                        <td><strong>MT-05</strong></td>
                        <td><span class="test-positive">Positivo</span></td>
                        <td>Histórico Completo de Aluno</td>
                        <td>Master consulta aluno específico</td>
                        <td>Exibir faltas em todas as matérias</td>
                        <td><span class="status-pass">PASS</span></td>
                    </tr>
                    <tr class="alert-critical">
                        <td><strong>MT-06</strong></td>
                        <td><span class="test-vulnerability">Vulnerabilidade</span></td>
                        <td>Log de Auditoria Master</td>
                        <td>Master gera 10 relatórios</td>
                        <td>Registrar todas ações em log de auditoria</td>
                        <td><span class="status-fail">FAIL</span></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="section">
            <h2>3. Detalhamento das Vulnerabilidades</h2>
            
            <div class="vulnerability-box">
                <h4>🔴 LT-07: Ausência de Rate Limiting</h4>
                <p><strong>Severidade:</strong> CRÍTICA</p>
                <p><strong>Descrição:</strong> O sistema permite tentativas ilimitadas de login, possibilitando ataques de força bruta.</p>
                <p><strong>Recomendação:</strong> Implementar bloqueio após 5 tentativas em 5 minutos, com captcha após 3 tentativas.</p>
            </div>
            
            <div class="vulnerability-box">
                <h4>🔴 LT-12: Sessão sem Timeout</h4>
                <p><strong>Severidade:</strong> ALTA</p>
                <p><strong>Descrição:</strong> Sessões permanecem ativas indefinidamente sem atividade do usuário.</p>
                <p><strong>Recomendação:</strong> Implementar logout automático após 30 minutos de inatividade.</p>
            </div>
            
            <div class="vulnerability-box">
                <h4>🔴 CT-04: QR Code sem Assinatura</h4>
                <p><strong>Severidade:</strong> CRÍTICA</p>
                <p><strong>Descrição:</strong> QR Codes podem ser gerados externamente sem validação criptográfica.</p>
                <p><strong>Recomendação:</strong> Implementar assinatura HMAC-SHA256 com chave secreta no servidor.</p>
            </div>
            
            <div class="vulnerability-box">
                <h4>🔴 CT-06: Falta de Validação de Origem</h4>
                <p><strong>Severidade:</strong> ALTA</p>
                <p><strong>Descrição:</strong> Requisições de marcação de presença podem ser enviadas via API externa.</p>
                <p><strong>Recomendação:</strong> Implementar tokens CSRF e validação de origem (headers, IP).</p>
            </div>
            
            <div class="vulnerability-box">
                <h4>🔴 MT-06: Ausência de Logs de Auditoria</h4>
                <p><strong>Severidade:</strong> MÉDIA</p>
                <p><strong>Descrição:</strong> Ações do usuário Master não são registradas para auditoria.</p>
                <p><strong>Recomendação:</strong> Implementar sistema de logs com timestamp, IP e ação executada.</p>
            </div>
        </div>

        <div class="section">
            <h2>4. Conclusão</h2>
            <p>O sistema apresenta funcionalidades robustas para controle de presença, com implementação bem-sucedida dos três perfis (Aluno, Professor e Master). Os recursos de relatórios permitem análise detalhada por matéria, professor e histórico de alunos.</p>
            
            <p style="margin-top: 15px;"><strong>Pontos Fortes:</strong></p>
            <ul style="margin-left: 25px; margin-top: 10px;">
                <li>Controle de acesso por perfil funcional</li>
                <li>Validação de Wi-Fi e tempo de QR Code eficaz</li>
                <li>Sistema de relatórios completo para Professor e Master</li>
                <li>Múltiplas formas de autenticação (CPF, RA, Email)</li>
            </ul>
            
            <p style="margin-top: 15px; color: #c0392b;"><strong>Ações Urgentes Necessárias:</strong></p>
            <ul style="margin-left: 25px; margin-top: 10px; color: #c0392b;">
                <li>Implementar rate limiting no login</li>
                <li>Adicionar assinatura criptográfica aos QR Codes</li>
                <li>Configurar timeout de sessão</li>
                <li>Implementar sistema de logs de auditoria</li>
                <li>Adicionar validação de origem das requisições</li>
            </ul>
            
            <p style="margin-top: 20px;"><strong>Recomendação:</strong> Correção das vulnerabilidades críticas antes de deploy em produção.</p>
        </div>

        <div class="button-container no-print">
            <button class="download-btn" onclick="window.print()">📄 Baixar PDF</button>
        </div>
    </div>
</body>
</html>
