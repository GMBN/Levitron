<?php

setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese') or die('Locale not installed');
date_default_timezone_set('America/Sao_Paulo');

//Variaveis Globais
$APP_CONFIG = require ROOT . '/config/db.local.php';

if (DEBUG) {
    ini_set('display_errors', true);
} else {
    ini_set('display_errors', false);
}
