<?php

// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Álvaro Remesal <contacto at alvaroremesal dot net>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class Config
{
    private $vars;
    private static $instance = null;
 
    private function __construct()
    {
        $this->vars = array();
    }
 
    public function set($name, $value)
    {
        if(!isset($this->vars[$name]))
        {
            $this->vars[$name] = $value;
        }
    }
 
    public function get($name)
    {
        if(isset($this->vars[$name]))
        {
            return $this->vars[$name];
        }
    }
 
    public static function singleton()
    {        
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }
 
        return self::$instance;
    }
     
 
}
/*
 Usage:
 
 $config = Config::singleton();
 $config->set('name', 'Bill');
 echo $config->get('name');
 
 $config2 = Config::singleton();
 echo $config2->get('name');
 
*/
?>