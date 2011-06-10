<?php

// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Álvaro Remesal <contacto at alvaroremesal dot net>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class Controller {

    protected $notice = '';
    protected $error = false;
    protected $webdata = null;

    private $params = '';
    
    function __construct($path = null)
    {
        if( $path === null) {
            $path = ucwords($this->getControlerName());
        }

        // Views
        $config = Config::singleton();

        Haanga::configure(array(
            'template_dir' => $config->get('viewsTemplatesFolder'),
            'cache_dir' => $config->get('viewsCompiledFolder'),
        ));

        // Data 
        $model = Model::singleton();

        $this->webdata = $model->getData();

    }

    protected function load( $template, $vars = array() ) {
        $vars['notice'] = Session::notice();

        Haanga::Load( $this->getControlerName().'/'.$template, $vars);
    }

    public function getNotice() {
        return $this->notice;
    }

    public function getError() {
        return $this->error;
    }

    /*
     * Description of _map()
     *
     * Escribe la ruta absoluta a un controlador/metodo
     *
     * @param $action Optional Acción a la que llamará
     * @param la función puede recibir n parámetros, que se pasarán como parámetros al método
     *
     * Ejemplo de uso:
     * <a href="{$_ctrl->_map( 'accion', 1234, 'juan', strftime('%d/%m/%Y', time()) )}">Siguiente</a>
     */

    public function _map($action = '') {
        return $this->mapea(func_get_args());
    }

    /*
     * Description of _redirect()
     *
     * Hace una redireccion a un controlador/metodo
     *
     * @param $action Optional Acción a la que llamará
     * @param la función puede recibir n parámetros, que se pasarán como parámetros al método
     *
     * Ejemplo de uso:
     * <a href="{$_ctrl->_map( 'accion', 1234, 'juan', strftime('%d/%m/%Y', time()) )}">Siguiente</a>
     */
    public function _redirect($action = '') {
        header('Location: '.$this->mapea(func_get_args()));
        exit();
    }

    public function _mapback($params = array()) {
        $strParams = '';

        foreach( $params as $value ) {
            $strParams .= '/' . $value;
        }
        return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] . $strParams : '/';
    }

    public function _back($params = array()) {
        if( !is_array($params) )
            $params = array($params);
        
        header('Location: ' . $this->_mapback($params));
        exit();
    }

    public function  __call($name,  $arguments) {
        
        $this->params = '';

        if( strpos($name, 'select_') >= 0 ) {
            $method = explode('_', $name);
            $viewmethod = 'select_'.$method[1];

            if( is_callable( array("View", $viewmethod) ) ) {
               $params = func_get_args();
               $params = $params[1];
               echo call_user_func_array(array("View",$viewmethod), $params);
            }
        }
    }

    public function _notice($notice) {
        
        View::_notice($notice);
    }

    public function _alert($alert) {

        View::_alert($alert);
    }


    private function arrvalues($val) {
        $this->params .= "\"$val\",";
    }
    
    /*
     * Description of mapea()
     *
     * Devuelve una cadena con la ruta absoluta a un controlador/metodo
     *
     * @param $args Array de parametros pasados a la funcion que la llama. Minimo se espera que
     *      siempre venga informado el primer parametro, que es la accion, aunque venga como cadena vacía
     *
     * Ejemplo de uso desde dentro de un controlador:
     * $this->_redirect( 'accion', 1234 );
     */
    private function mapea($args = array()) {
        $str_params = '';

        // Agregar accion
        // $args[0] siempre existe, ya que si no lo pasan viene como ''
        $controller = $this->getControlerName();
        $action = '';

        if($args[0] != '') {
            if( strpos($args[0], '::')) {
                $auxarr = explode('::', $args[0]);
                $controller = $auxarr[0];
                $action =  '/'.$auxarr[1];
            }
            else {
                $action = '/'.$args[0];
            }
        }

        // Agregar parametros, si los hay
        if( count($args) > 1 ) {
            $params = array_slice($args, 1, count($args)-1);
            foreach( $params as $param ) {
                $str_params .= '/' . $this->adecuarParam($param);
            }
        }

        // Si el controlador es MYUSER, se saca el controlador del tipo de usuario actual
        if( $controller == 'MYUSER' ) {
            $controller = get_class( Session::user() );
        }

        return '/' . $controller . $action . $str_params;
    }

    public function getControlerName() {
        return substr(get_class($this), 0, strpos(get_class($this), 'Controller'));
    }

    private function adecuarParam($txt) {
        $txt = str_replace('á', 'a', $txt);
        $txt = str_replace('é', 'e', $txt);
        $txt = str_replace('í', 'i', $txt);
        $txt = str_replace('ó', 'o', $txt);
        $txt = str_replace('ú', 'u', $txt);
        $txt = str_replace('Á', 'A', $txt);
        $txt = str_replace('É', 'E', $txt);
        $txt = str_replace('Í', 'I', $txt);
        $txt = str_replace('Ó', 'O', $txt);
        $txt = str_replace('Ú', 'U', $txt);
        $txt = str_replace('ç', 'c', $txt);
        $txt = str_replace('Ç', 'C', $txt);
        $txt = str_replace('ñ', 'n', $txt);
        $txt = str_replace('Ñ', 'N', $txt);
        $txt = str_replace('ü', 'u', $txt);
        $txt = str_replace('Ü', 'U', $txt);

        $txt = str_replace(' ', '-', $txt);
        $txt = str_replace('/', '-', $txt);
        $txt = str_replace('&', '-', $txt);

        return $txt;
    }

    /**
     * $interval can be:
     * yyyy - Number of full years
     * q - Number of full quarters
     * m - Number of full months
     * y - Difference between day numbers (eg 1st Jan 2004 is "1", the first day. 2nd Feb 2003 is "33". The datediff is "-32".)
     * d - Number of full days
     * w - Number of full weekdays
     * ww - Number of full weeks
     * h - Number of full hours
     * n - Number of full minutes
     * s - Number of full seconds (default)
     *
     * @param <type> $interval
     * @param <type> $datefrom
     * @param <type> $dateto
     * @param <type> $using_timestamps
     * @return <type>
     */
    public function _dateDiff($interval, $datefrom, $dateto, $using_timestamps = false) {
        
        if (!$using_timestamps) {
             $datefrom = strtotime($datefrom, 0);
             $dateto = strtotime($dateto, 0);
         }
         $difference = $dateto - $datefrom;
         // Difference in seconds
         switch($interval) {
             case 'yyyy':
                // Number of full years
                $years_difference = floor($difference / 31536000);
                if (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom), date("j", $datefrom), date("Y", $datefrom)+$years_difference) > $dateto) {
                    $years_difference--;
                }
                if (mktime(date("H", $dateto), date("i", $dateto), date("s", $dateto), date("n", $dateto), date("j", $dateto), date("Y", $dateto)-($years_difference+1)) > $datefrom) {
                    $years_difference++;
                    }
                $datediff = $years_difference;
                break;
            case "q":
                // Number of full quarters
                $quarters_difference = floor($difference / 8035200);
                while (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom)+($quarters_difference*3), date("j", $dateto), date("Y", $datefrom)) < $dateto) {
                    $months_difference++;

                    }
                $quarters_difference--;
                $datediff = $quarters_difference;
                break;
            case "m":
                // Number of full months
                $months_difference = floor($difference / 2678400);
                while (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom)+($months_difference), date("j", $dateto), date("Y", $datefrom)) < $dateto) {
                    $months_difference++;

                    }
                $months_difference--;
                $datediff = $months_difference;
                break;
            case 'y':
                // Difference between day numbers
                $datediff = date("z", $dateto) - date("z", $datefrom);
                break;
            case "d":
                // Number of full days
               $datediff = floor($difference / 86400);
                break;
            case "w":
                // Number of full weekdays
                $days_difference = floor($difference / 86400);
                $weeks_difference = floor($days_difference / 7);
                // Complete weeks
                $first_day = date("w", $datefrom);
                $days_remainder = floor($days_difference % 7);
                $odd_days = $first_day + $days_remainder;
                // Do we have a Saturday or Sunday in the remainder?
                if ($odd_days > 7) {
                    // Sunday
                    $days_remainder--;
                    }
                if ($odd_days > 6) {
                    // Saturday
                    $days_remainder--;

                    }
                $datediff = ($weeks_difference * 5) + $days_remainder;
                break;
            case "ww":
                // Number of full weeks
                $datediff = floor($difference / 604800);
                break;
            case "h":
                // Number of full hours
                $datediff = floor($difference / 3600);
                break;
            case "n":
                // Number of full minutes
                $datediff = floor($difference / 60);
                break;
            default:
                // Number of full seconds (default)
                $datediff = $difference;
                break;
            }

            return $datediff;

    }

    /**
     * Interval can be one of:
        yyyy	year
        q	Quarter
        m	Month
        y	Day of year
        d	Day
        w	Weekday
        ww	Week of year
        h	Hour
        n	Minute
        s	Second
     *
     * @param <type> $interval
     * @param <type> $number
     * @param <type> $date
     * @return <type>
     */
    public function _dateAdd($interval, $number, $date) {

        $date_time_array = getdate(strtotime($date));
        $hours = $date_time_array['hours'];
        $minutes = $date_time_array['minutes'];
        $seconds = $date_time_array['seconds'];
        $month = $date_time_array['mon'];
        $day = $date_time_array['mday'];
        $year = $date_time_array['year'];

        switch ($interval) {

            case 'yyyy':
                $year+=$number;
                break;
            case 'q':
                $year+=($number*3);
                break;
            case 'm':
                $month+=$number;
                break;
            case 'y':
            case 'd':
            case 'w':
                $day+=$number;
                break;
            case 'ww':
                $day+=($number*7);
                break;
            case 'h':
                $hours+=$number;
                break;
            case 'n':
                $minutes+=$number;
                break;
            case 's':
                $seconds+=$number;
                break;
        }
           $timestamp= mktime($hours,$minutes,$seconds,$month,$day,$year);
        return $timestamp;
    }

    protected function avoidCodeInjection($post = ''){
	$notAllowedExp = array(
			'/<[^>]*script.*\"?[^>]*>/','/<[^>]*style.*\"?[^>]*>/',
			'/<[^>]*object.*\"?[^>]*>/','/<[^>]*iframe.*\"?[^>]*>/',
			'/<[^>]*applet.*\"?[^>]*>/','/<[^>]*window.*\"?[^>]*>/',
			'/<[^>]*docuemnt.*\"?[^>]*>/','/<[^>]*cookie.*\"?[^>]*>/',
			'/<[^>]*meta.*\"?[^>]*>/','/<[^>]*alert.*\"?[^>]*>/',
			'/<[^>]*form.*\"?[^>]*>/','/<[^>]*php.*\"?[^>]*>/','/<[^>]*img.*\"?[^>]*>/'
			);

        if( $post == '')
            $post = $_POST;
        
	foreach ($post as $postvalue) {	
		foreach ($notAllowedExp as $exp){ 
			if ( preg_match($exp, $postvalue) ) die ("Se introdujo codigo no permitido");
		}
	}
    }

    public function truncateString($txt, $num = 100) {
        if( strlen($txt) > $num)
            $txt = substr($txt, 0, $num) . "...";

        return $txt;
    }

    /**
     * Comprueba si se trata de un robot, usando el método de campo oculto
     *
     * Se pueden rastrear intentos de spam de una IP desde el log de BBDD así:
     *
     * SELECT count(*) as total, ip FROM `TecnoTransporter`.`log` WHERE message like '[SPAM%' group by ip
     *
     *    o rasterar donde se intenta más así:
     *
     * SELECT count(*) as total, url FROM `TecnoTransporter`.`log` WHERE message like '[SPAM%' group by url
     *
     * @return boolean 
     */
    protected function chkSpam() {
        //print_r($_REQUEST);
        //if ( !( isset($_REQUEST['name_bo']) && !empty($_REQUEST['name_bo']) ) ) echo "no"; else echo "si";

        // Metodo de campo oculto
        $spam = ( isset($_REQUEST['name_bo']) && !empty($_REQUEST['name_bo']) );

        if( $spam ) Log::log('[SPAM] Detectado por método de campo oculto');
        
        return $spam;
    }
    
}


?>
