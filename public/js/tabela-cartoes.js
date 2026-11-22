/*
 * Tabela em cartões no celular (ver .rc-tabela-cartoes em css/recursos.css).
 * Copia o texto de cada <th> para o data-rotulo das células da mesma coluna,
 * inclusive nas linhas que a busca ao vivo recria depois.
 * Colunas com data-sem-rotulo no <th> (nome da linha, ações) ficam sem rótulo.
 */
(function () {
    function rotular(tabela) {
        var cabecalhos = Array.prototype.map.call(tabela.querySelectorAll('thead th'), function (th) {
            if (th.hasAttribute('data-sem-rotulo')) return '';
            return (th.textContent || '').replace(/\s+/g, ' ').trim();
        });
        Array.prototype.forEach.call(tabela.querySelectorAll('tbody tr'), function (tr) {
            Array.prototype.forEach.call(tr.children, function (td, i) {
                if (td.tagName !== 'TD' || td.hasAttribute('colspan')) return;
                if (cabecalhos[i]) td.setAttribute('data-rotulo', cabecalhos[i]);
                else td.removeAttribute('data-rotulo');
            });
        });
    }

    function iniciar() {
        Array.prototype.forEach.call(document.querySelectorAll('table.rc-tabela-cartoes'), function (tabela) {
            rotular(tabela);
            var corpo = tabela.querySelector('tbody');
            if (corpo && 'MutationObserver' in window) {
                new MutationObserver(function () { rotular(tabela); }).observe(corpo, { childList: true });
            }
        });
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', iniciar);
    else iniciar();
})();
