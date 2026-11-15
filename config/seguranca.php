<?php

return [
    /*
    | 2FA obrigatório para o Master. Sem 2FA confirmado, o Master é levado à
    | tela de configuração antes de acessar o painel. Desligue só em
    | desenvolvimento local ou nos testes automatizados.
    */
    '2fa_obrigatorio_master' => (bool) env('TWO_FACTOR_MASTER_OBRIGATORIO', true),

    /* Tempo, em segundos, para digitar o código depois da senha. */
    '2fa_desafio_ttl' => 300,
];
