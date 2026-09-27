<?php

// Ponto de entrada da função serverless na Vercel (runtime vercel-php).
// Todo o tráfego dinâmico é encaminhado para o front controller do Laravel.
require __DIR__ . '/../public/index.php';
