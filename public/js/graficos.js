/*
 * Gráficos de presença (Chart.js, vendorizado em js/vendor/chart.umd.js).
 * Os dados chegam num <script type="application/json" id="dados-graficos">.
 * As cores vêm das variáveis --pal-* e são recalculadas ao trocar o tema.
 */
(function () {
    'use strict';

    var fonte = document.getElementById('dados-graficos');
    if (!fonte || !window.Chart) return;

    var dados = JSON.parse(fonte.textContent);
    var graficos = [];

    var STATUS = {
        regular:         { rotulo: 'Em dia',              cor: '#22c55e' },
        aprovado:        { rotulo: 'Aprovado',            cor: '#16a34a' },
        atencao:         { rotulo: 'Atenção',             cor: '#eab308' },
        reprovado:       { rotulo: 'Reprovado por nota',  cor: '#f97316' },
        reprovado_falta: { rotulo: 'Reprovado por falta', cor: '#ef4444' }
    };

    function cssVar(nome) {
        return getComputedStyle(document.documentElement).getPropertyValue(nome).trim();
    }

    function corFrequencia(p) {
        return p >= 75 ? '#22c55e' : (p >= 50 ? '#eab308' : '#ef4444');
    }

    function dataBr(iso) {
        var p = iso.split('-');
        return p[2] + '/' + p[1];
    }

    function base() {
        var texto = cssVar('--pal-light-gray') || '#bbbbbb';
        var grade = (cssVar('--pal-white') || '#efefef');
        Chart.defaults.color = texto;
        Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
        Chart.defaults.font.weight = 600;
        return { texto: texto, grade: grade };
    }

    function escalaPercentual(c) {
        return {
            min: 0, max: 100,
            ticks: { callback: function (v) { return v + '%'; }, stepSize: 25 },
            grid: { color: c.grade + '14' },
            border: { display: false }
        };
    }

    function montar() {
        graficos.forEach(function (g) { g.destroy(); });
        graficos = [];
        var c = base();
        var reduzir = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var animacao = reduzir ? false : { duration: 700 };

        var linha = document.getElementById('grafico-frequencia');
        if (linha && dados.frequencia_por_aula) {
            var pontos = dados.frequencia_por_aula;
            graficos.push(new Chart(linha, {
                type: 'line',
                data: {
                    labels: pontos.map(function (p) { return dataBr(p.data); }),
                    datasets: [{
                        label: 'Presentes',
                        data: pontos.map(function (p) { return p.percentual; }),
                        borderColor: c.grade,
                        borderWidth: 2,
                        tension: 0.3,
                        fill: { target: 'origin', above: c.grade + '12' },
                        pointBackgroundColor: pontos.map(function (p) { return corFrequencia(p.percentual); }),
                        pointBorderColor: pontos.map(function (p) { return corFrequencia(p.percentual); }),
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    animation: animacao,
                    layout: { padding: { right: 12, top: 6 } },
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: function (ctx) { return ctx.parsed.y + '% da turma presente'; } } }
                    },
                    scales: { y: escalaPercentual(c), x: { grid: { display: false }, border: { display: false } } }
                }
            }));
        }

        var rosca = document.getElementById('grafico-situacoes');
        if (rosca && dados.situacoes) {
            var chaves = Object.keys(STATUS).filter(function (k) { return dados.situacoes[k] > 0; });
            graficos.push(new Chart(rosca, {
                type: 'doughnut',
                data: {
                    labels: chaves.map(function (k) { return STATUS[k].rotulo; }),
                    datasets: [{
                        data: chaves.map(function (k) { return dados.situacoes[k]; }),
                        backgroundColor: chaves.map(function (k) { return STATUS[k].cor; }),
                        borderColor: cssVar('--pal-near-black') || '#0d0d0d',
                        borderWidth: 3
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    animation: animacao,
                    cutout: '62%',
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, boxHeight: 10, padding: 14 } } }
                }
            }));
        }

        var barras = document.getElementById('grafico-turmas');
        if (barras && dados.turmas) {
            var turmas = dados.turmas.filter(function (t) { return t.aulas_realizadas > 0; });
            barras.parentElement.style.height = Math.max(240, turmas.length * 38 + 60) + 'px';
            graficos.push(new Chart(barras, {
                type: 'bar',
                data: {
                    labels: turmas.map(function (t) { return t.materia; }),
                    datasets: [{
                        label: 'Frequência média',
                        data: turmas.map(function (t) { return t.frequencia_media; }),
                        backgroundColor: turmas.map(function (t) { return corFrequencia(t.frequencia_media); }),
                        borderRadius: 3,
                        maxBarThickness: 22
                    }]
                },
                options: {
                    indexAxis: 'y',
                    layout: { padding: { right: 18 } },
                    maintainAspectRatio: false,
                    animation: animacao,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: function (ctx) {
                            var t = turmas[ctx.dataIndex];
                            return ctx.parsed.x + '% · ' + t.alunos + ' alunos · ' + t.aulas_realizadas + ' aulas';
                        } } }
                    },
                    scales: { x: escalaPercentual(c), y: { grid: { display: false }, border: { display: false } } }
                }
            }));
        }
    }

    montar();

    // Tema claro/escuro troca a classe do <html>: refaz com as novas cores
    new MutationObserver(function () { montar(); })
        .observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
})();
