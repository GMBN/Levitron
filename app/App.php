<?php

class App {

    protected $module;
    protected $currentModule;
    protected $currentTemplate;
    protected $route = array();
    protected $publicDir = array();
    protected $requestUri;
    protected $controller;
    protected $controllerName;
    protected $action;
    protected $param = array();
    protected $viewHelper = array();
    protected $template;
    protected $config = array();

    function run() {
        $e = sm::getInstance()->get('e');
        $configGlobal = array();
        $configApp = require ROOT . '/config/config.php';
        $configGlobal['app'] = $configApp;
        
       
        //Obtem todos os modulos ativos
        $modules = require __DIR__ . '/modules.php';

        foreach ($modules as $m) {

            //Obtem as configuracoes do modulo
            $class = $m . "\\Module";

            if (class_exists($class)) {
                $module = new $class();
                $config = $module->getConfig();
            } else {                
                throw new Exception('modulo '.$m.' não existe');
                continue;
            }
//            $config = require __DIR__ . '/' . $m . '/config.php';
            //atribui a configuracao global do modulo
            $configGlobal['module'][$m] = $config;

            //Obtem os listeners globais do módulo 
//            $event = __DIR__ . '/' . $m . '/event.global.php';
            $event = [];
            if (method_exists($module, 'getEventGlobal')) {
                $event = $module->getEventGlobal($e);
            }

            if (isset($config['route'])) {

                //Obtem as rotas do modulo
                foreach ($config['route'] as $alias => $controller) {
                    $this->route[$alias] = $controller;
                    $this->module[$alias] = $m;

                    //atribui a configuracao da rota global
                    $configGlobal['route'][$alias] = $m . "\\Controller\\" . $controller;
                }
            }

            if (isset($config['public_dir'])) {

                foreach ($config['public_dir'] as $alias => $folder) {
                    $this->publicDir[$alias] = $folder;

                    //atribui a configuracao do diretorio publico global
                    $configGlobal['public_dir'][$alias] = $folder;
                }
            }

            if (isset($config['viewHelper'])) {

                foreach ($config['viewHelper'] as $alias => $function) {
                    $this->viewHelper[$alias] = $function;

                    //Registra os viewHelpers
                    $configGlobal['viewHelper'][$alias] = $function;
                }
            }

            if (isset($config['template'])) {
                //Obtem o template do modulo
                $this->template[$m] = $config['template'];
            }

            if (isset($config['sm'])) {
                foreach ($config['sm'] as $name => $obj) {
                    //registra o servico no service manager
                    sm::getInstance()->set($name, $obj);
                }
            }
        }
        
        sm::getInstance()->set('config', $configGlobal);


        //Obtem os parametros da url acessada
        $request = explode('?', $_SERVER['REQUEST_URI']);
        $this->requestUri = $request[0];
        $uri = explode('/', $this->requestUri);

        $params = array();

        //verifica se existe uma diretorio publico para essa rota
        foreach ($this->publicDir as $alias => $folder) {
            if (strpos($this->requestUri, $alias) === false) {
                continue;
            }

            $filename = substr_replace($this->requestUri, '', 0, strlen($alias));
            $file = $folder . $filename;
            if (is_file($file)) {
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                if ($ext == 'php' || $ext == 'phtml') {
                    require $file;
                    die();
                }
                if ($ext == 'js') {
                    $type = 'application/javascript';
                } else if ($ext == 'css') {
                    $type = 'text/css';
                } else {
                    $type = mime_content_type($file);
                }
                header('Content-Type: ' . $type);
                echo file_get_contents($file);
                exit();
            }
        }


        //verifica se existe uma rota identica
        if (isset($this->route[$this->requestUri])) {
            $controller = $this->route[$this->requestUri];
            $alias = $this->requestUri;
            $array_controller = explode(':', $controller);
            $m = $this->module[$alias];
            $this->currentModule = $m;
            if (isset($this->template[$m])) {
                $this->currentTemplate = $this->template[$m];
            }
            $this->controller = $m . "\\Controller\\" . $array_controller[0];
            $this->controllerName = $array_controller[0];
            $this->action = $array_controller[1];
            $this->param = $params;
            return $this->exec(); //Executa o controller
        }


        foreach ($this->route as $alias => $controller) {

            $route = explode('/', $alias);

            if (count($route) == count($uri)) {

                $notFound = false;

                foreach ($uri as $key => $val) {

                    $par = $route[$key];
                    if (substr($par, 0, 1) == "$") {
                        $name = str_replace('$', '', $par);
                        $params[$name] = $val;
                    } else if ($val != $par) {
                        $notFound = true;
                    }
                }
                if (!$notFound) {
                    $array_controller = explode(':', $controller);
                    $m = $this->module[$alias];
                    $this->currentModule = $m;
                    $this->currentTemplate = $this->template[$m];
                    $this->controller = $m . "\\Controller\\" . $array_controller[0];
                    $this->controllerName = $array_controller[0];
                    $this->action = $array_controller[1];
                    $this->param = $params;
                    return $this->exec(); //Executa o controller
                }
            }
        }
        $e->trigger(e::notFound, [$this->requestUri]);
    }

    function exec() {
        global $e;

        $config = sm::getInstance()->get('config');

        $configModule = $config['module'][$this->currentModule];

        sm::getInstance()->set('configModule', $configModule);

        //Obtem os listener local do módulo 
        $classLocal = $this->currentModule . '\\Module';
        $moduleLocal = new $classLocal();

        //carrega os eventos locais
        if (method_exists($moduleLocal, 'getEventLocal')) {
            $moduleLocal->getEventLocal($e);
        }

        //carrega o diretorio atual
        if (method_exists($moduleLocal, 'getDir')) {
            $dir = $moduleLocal->getDir();
        }

//
//        $eventCont = ROOT . '/app/' . $this->currentModule . '/Event/' . $this->controllerName . '.php';
//        if (file_exists($eventCont)) {
//            require $eventCont;
//        }

        $e->trigger(e::preController, [$this->currentModule, $this->controller, $this->action, $this->param]);

        $par = array_values($this->param);
        $obj = new $this->controller();
        $dados = call_user_func_array(array($obj, $this->action), $par);

        if ($dados === 404) {
            return $e->trigger(e::notFound, [$this->requestUri]);
        }

        if (!$dados) {
            $dados = [];
        }

        return $this->render($dados, $dir);
    }

    function render($dados, $dir) {
        global $e;
        $view = $dir . '/View/' . strtolower($this->controllerName) . '/' . $this->action . '.php';
        if (file_exists($this->currentTemplate . '.php')) {
            $template = $this->currentTemplate . '.php';
        } else {
            $template = $dir . '/View/template/' . $this->currentTemplate . '.php';
        }

        $e->trigger(e::preRender, [$dados, $view, $template, $this->viewHelper]);
    }

}
