<?php

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server') {
    $path = realpath(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if (__FILE__ !== $path && is_file($path)) {
        return false;
    }
    unset($path);
}

ob_start();

define('ROOT', __DIR__.'/..');

//Verifica se existe o loadbalancer para pegar o ip do client
if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '') {
    $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
}

require ROOT.'/debug.php';
//require ROOT.'/registra_erros.php';
require ROOT."/init_config.php";

require ROOT."/vendor/autoload.php";

$e = new \EventPHP\Event();//start global event 

$config;
$configModule;

$start = (new \App\App())->run();

//print_r($e->listeners());
