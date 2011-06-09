<?php

// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Álvaro Remesal <contacto at alvaroremesal dot net>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

define('USELANG', 'es');
/*
*	'en' -> English
*	'es' -> Espa�ol
*/

$config = Config::singleton();

define('PROJECT_NAME', 'Simply Cover');

date_default_timezone_set('Europe/Madrid');
ini_set('date.timezone','Europe/Madrid');

define('ENVIRONMENT','dev');

if (ENVIRONMENT == "prod"){
    error_reporting(30719);             //comment for error reporting
    ini_set('display_errors','off');    //set ON to see errors
}
elseif (ENVIRONMENT == "dev"){
    error_reporting(30719);
    ini_set('display_errors','on');
    error_reporting(E_ALL); ini_set('display_errors', 1);
}

/* Language */
$config->set('lang', USELANG);

/* Directory paths */
$config->set('controllersFolder', '../controllers/');
$config->set('modelsFolder', '../models/');
$config->set('viewsTemplatesFolder', '../views/templates/');
$config->set('viewsCompiledFolder', '../views/compiled/');
$config->set('dataFile', '../data/web.yaml');

$config->set('defaultPass', 'simplycoveradmin');
$config->set('customCSS', '../public/css/custom.css');
$config->set('dirImagesUploads', '../public/images/uploads/');

/* Autoload */

spl_autoload_register('autoload');

function autoload($class_name) {
    $config = Config::singleton();
    if(preg_match('|(.)*Controller|',$class_name)) {
        require_once $config->get('controllersFolder')."".$class_name . '.php';
    }
}
?>