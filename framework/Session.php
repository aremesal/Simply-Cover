<?php

// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Álvaro Remesal <contacto at alvaroremesal dot net>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class Session
{

    public function __construct() {
        if(!session_id()) session_start();
        if(!key_exists('user', $_SESSION)) $_SESSION['user']=null;
    }

    public static function user($user=null) {
        if($user) $_SESSION['user'] = $user;
        $user = $_SESSION['user'];
        return $user;
    }

    public static function notice($notice="",$state=0) {
        if($notice) $_SESSION['notice'] = array("notice"=>$notice,"state"=>$state);
        if(key_exists('notice', $_SESSION)) return $_SESSION['notice'];
    }

    public static function alert($alert="",$state=0) {
        if($alert) $_SESSION['alert'] = array("alert"=>$alert,"state"=>$state);
        if(key_exists('alert', $_SESSION)) return $_SESSION['alert'];
    }
    
    public static function set($item,$value) {
        $_SESSION[$item] = $value;
    }

    public static function get($item) {
        if(!key_exists($item, $_SESSION)) $_SESSION[$item] = null;
        return $_SESSION[$item];
    }

    public static function destroy() {
        session_destroy();
    }

    public static function isMobile() {
        return $_SESSION['mobile'] == '1';
    }
}

?>
