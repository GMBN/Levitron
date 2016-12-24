<?php
namespace Teste\Controller;

class Page{
    
    function index(){
       $titulo = "Levitron";
       return ['nome'=>$titulo];
    }
}