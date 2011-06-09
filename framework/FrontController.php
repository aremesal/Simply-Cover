<?php

// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Álvaro Remesal <contacto at alvaroremesal dot net>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

header('Content-type: text/html; charset=utf-8');

class FrontController
{
    static private $controllerName = '';
    static private $actionName = '';
    static private $querystring = array();
    static private $defaultController = 'CoverController';
    static private $actionError404 = "notFound";
    static private $actionError500 = "error500";
    static private $params = '';

    static public function main()
    {
            include_once '../config/includes.inc';

            // For security, session closes when browser closes
            ini_set("session.use_trans_sid","0");
            ini_set("session.use_only_cookies","1");
            ini_set("session.cookie_lifetime","0");

            $session = new Session();
            
            $struri = substr($_SERVER['REQUEST_URI'], 1);

            // Clean GET params
            if( strpos($struri, 'action_for_params') === false ) {

                if( strpos($struri, '?') !== false )
                    $struri = substr($struri, 0, strpos($struri, '?'));
                if( strpos($struri, '&') !== false )
                    $struri = substr($struri, 0, strpos($struri, '&'));
            }

            if( strpos($struri, '#') !== false )
                    $struri = substr($struri, 0, strpos($struri, '#'));

            $uri = explode('/',$struri);
            $nparms = count($uri);

            // Split URI with params.
            // TODO: this doesn't work with URLs with more than one parameter
            if( $nparms > 1 && strpos($uri[1], '?') ) {
                $aux = explode('?', $uri[1]);
                if( strpos($aux[1], '&')) {
                    $params = explode('&', $aux[1]);
                    foreach( $params as $param) {
                        self::$querystring[] = $param;
                    }
                }
                else
                    self::$querystring[] = $aux[1];
                
                $uri[1] = $aux[0];
            }		

            // Controller
            if( $nparms > 0 && $uri[0] != '' )
                self::$controllerName = $uri[0] . 'Controller';

            // Action
            if( $nparms > 1 )
                self::$actionName = $uri[1];

            // Parameters
            if( $nparms > 2 ) {
                array_shift($uri);
                array_shift($uri);
                foreach( $uri as $param ) {
                    self::$querystring[] = $param;
                }
            }
            
            if( self::$controllerName == '' )
                  self::$controllerName = self::$defaultController;

            if( self::$actionName == '')
                  self::$actionName = "index";

            $controllerPath = $config->get('controllersFolder') . self::$controllerName . '.php';
              
            if(is_file($controllerPath))
                  require $controllerPath;
            else
            {
                $controller = new self::$defaultController();
                $controller->_redirect( self::$actionError404,self::$controllerName);
            }

            if (is_callable(array(self::$controllerName, self::$actionName)) == false)
            {
                $controller = new self::$defaultController();
                $controller->_redirect( self::$actionError404, implode(':',array(self::$controllerName, self::$actionName)));
            }

            
            $controller = new self::$controllerName();
            
            array_walk(self::$querystring, 'FrontController::arrvalues');

            $action_arguments = substr(self::$params, 0, strlen(self::$params) - 1);

            eval( "\$controller->{self::\$actionName}( $action_arguments );" );

            Session::set('notice', null);
    }

    private function arrvalues($val) {
        self::$params .= "\"$val\",";
    }
}

?>
