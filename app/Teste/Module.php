<?php

namespace Teste;

class Module{
    
    function getConfig(){
        return require __DIR__.'/config.php';
    }
    
    function getDir(){
        return __DIR__;
    }
}