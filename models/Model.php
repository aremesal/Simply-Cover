<?php

// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Álvaro Remesal <contacto at alvaroremesal dot net>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class Model
{
    private static $instance = null;

    private function __construct()
    {
    }

    public static function singleton()
    {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }

        return self::$instance;
    }

    public function  __clone() {
        die('You can not clone this!');
    }

    public function getData() {
        
        $datafile = Spyc::YAMLLoad($this->readFile());

        return $datafile;
    }

    public function saveData($vars) {

        $config = Config::singleton();

        $datastring = Spyc::YAMLDump($vars);

        $fp = fopen($config->get('dataFile'), 'w');

        fputs($fp, $datastring);

        fclose($fp);

        return;
    }

    private function readFile() {
        $config = Config::singleton();

        /*
        $fp = fopen($config->get('dataFile'), 'r');

        while(!feof($fp))
            $buffer = fgets($fp);

        return $data;
        */

        return $config->get('dataFile');
    }


}
?>