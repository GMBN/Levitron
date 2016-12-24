<?php
//
//try{

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server') {
    $path = realpath(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if (__FILE__ !== $path && is_file($path)) {
        return false;
    }
    unset($path);
}

ob_start();

define('ROOT', __DIR__ . '/..');

//Verifica se existe o loadbalancer para pegar o ip do client
if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '') {
    $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
}

require ROOT . '/debug.php';

$composer = ROOT . "/vendor/autoload.php";
if (!file_exists($composer)) {
    throw new Exception("Composer não encontrado, Execute composer install");
    exit();
}

//require ROOT.'/registra_erros.php';
require ROOT . "/init_config.php";

require $composer;

$e = new \EventPHP\Event(); //start global event 

sm::getInstance()->set('e', $e);

require ROOT . "/app/App.php";
$start = (new App())->run();

$e->trigger('appFinish');

//}  catch (Exception $erro){
//    echo 'erro';
//}
