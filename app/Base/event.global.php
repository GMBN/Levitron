<?php

$e->on('preRender', function($dados,$view, $template) {
    if ($dados) {
        $service = new \App\Base\Service\View();
        return $service->render($dados, $view, $template);
    }
});
