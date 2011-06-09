<?php

// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Álvaro Remesal <contacto at alvaroremesal dot net>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".


function setMobile() {
    $es_movil = checkMobile();
    if($es_movil==TRUE) {
        Session::set('mobile', '1');
        //header('Location: http://m.' . $_SERVER['HTTP_HOST']);
    }
    else
        Session::set('mobile', '0');
}

function checkMobile() {
    $es_movil=FALSE;
    $usuario = $_SERVER['HTTP_USER_AGENT'];

    $usuarios_moviles = "iPad, iPod, Android, AvantGo, Blackberry, Blazer, Cellphone, Danger, DoCoMo, EPOC, EudoraWeb, Handspring, HTC, Kyocera, LG, MMEF20, MMP, MOT-V, Mot, Motorola, NetFront, Newt, Nokia, Opera Mini, Palm, Palm, PalmOS, PlayStation Portable, ProxiNet, Proxinet, SHARP-TQ-GX10, Samsung, Small, Smartphone, SonyEricsson, SonyEricsson, Symbian, SymbianOS, TS21i-10, UP.Browser, UP.Link, WAP, webOS, Windows CE, hiptop, iPhone, iPod, portalmmm, Elaine/3.0, OPWV";

    $navegador_usuario = explode(',',$usuarios_moviles);

    foreach($navegador_usuario AS $navegador){
            if(preg_match('#'.trim($navegador).'#i',$usuario)){
                    $es_movil=TRUE;
            }
    }

    return $es_movil;
}

function setLanguage($lang = 'es_ES') {
     // Forzar idioma
    //$lang = 'de_DE';

    //Solo para fechas
    putenv('LC_TIME='.$lang);
    setlocale(LC_TIME, $lang);


    // También se puede usar LC_ALL, pero quizá afecte a más cosas del server
    
    putenv('LC_MESSAGES='.$lang);
    setlocale(LC_MESSAGES, $lang.'.UTF8');
    bindtextdomain("tecnotransporter",  "../locale");
    textdomain("tecnotransporter");
}



/**
 * Replace arguments in a string with their values. Arguments are represented by % followed by their number.
 *
 * @param	string	Source string
 * @param	mixed	Arguments, can be passed in an array or through single variables.
 * @returns	string	Modified string
 */
function __($str)
{
        $str = gettext($str);
	$tr = array();
	$p = 0;

	for ($i=1; $i < func_num_args(); $i++) {
		$arg = func_get_arg($i);

		if (is_array($arg)) {
			foreach ($arg as $aarg) {
				$tr['%'.++$p] = $aarg;
			}
		} else {
			$tr['%'.++$p] = $arg;
		}
	}

	return strtr($str, $tr);
}

/**
 * Antiguas funciones para sobreescribir las de gettext, ya no se usan en favor de smarty-gettext

function __() {
    if( func_num_args() > 1 ) {
        $arguments = array_splice(func_get_args(), 1);

        $arguments = implode('","', $arguments);

        $action_arguments = '"' . $arguments . '"';

        $cadena = func_get_arg(0);

        eval( "printf( _(\"$cadena\"), $action_arguments);" );
    }
    else
        echo _(func_get_arg(0));
}

function _e() {
    if( func_num_args() > 1 ) {
        $arguments = array_splice(func_get_args(), 1);

        $arguments = implode('","', $arguments);

        $action_arguments = '"' . $arguments . '"';

        $cadena = func_get_arg(0);

        eval( "printf( \"_(\"$cadena\")\", $action_arguments);" );
    }
    else
        echo '"'._(func_get_arg(0)) . '"';
}
 Fin funciones antiguas gettext */

?>
