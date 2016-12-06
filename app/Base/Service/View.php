<?php

namespace App\Base\Service;

class View {

    private $_obj = [];

    function render($dados, $file_view, $file_template, $_template = true) {

        ob_start();
        extract($dados);
        require $file_view;
        $_content = ob_get_clean();

        if ($_template) {
            require $file_template;
        } else {
            echo $_content;
        }
    }

    function __call($name, $arguments) {
        global $e;
        
        return $e->trigger('view.' . $name, $arguments);
    }

}
