<?php
class sm{
    
    protected $service = array();
    
    
     function set($name,$obj){
        $this->service[$name] = $obj;
    }
    
     function get($name){
        if(isset($this->service[$name])){
            $class = $this->service[$name];
            
            if(!is_object($class) && is_string($class) && class_exists($class)){
                $this->service[$name] = new $class;
            }
            
            return $this->service[$name];
        }
    }
    
      /**
     * Retorna uma instância única de uma classe.
     *
     * @staticvar Singleton $instance A instância única dessa classe.
     *
     * @return Singleton A Instância única.
     */
    public static function getInstance()
    {
        static $instance = null;
        if (null === $instance) {
            $instance = new sm();
        }

        return $instance;
    }

    /**
     * Construtor do tipo protegido previne que uma nova instância da
     * Classe seja criada através do operador `new` de fora dessa classe.
     */
    protected function __construct()
    {
    }

    /**
     * Método clone do tipo privado previne a clonagem dessa instância
     * da classe
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Método unserialize do tipo privado para prevenir a desserialização
     * da instância dessa classe.
     *
     * @return void
     */
    private function __wakeup()
    {
    }
}