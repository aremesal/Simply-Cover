<?php

// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Álvaro Remesal <contacto at alvaroremesal dot net>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class CoverController extends Controller {
	
	private $config = NULL;
	
    public function  __construct() {						
        parent::__construct();		
		
		$this->config = Config::singleton();
    }

    public function index() {
	
        include_once '../lang/'.$this->config->get('lang').'.php';
        	
        //$vars = compact('user', 'url');
        $vars = array(
            'title' => $this->webdata['Sitedata']['title'],
            'webtitle' => $this->webdata['Sitedata']['title'],
            'subtitle' => $this->webdata['Sitedata']['subtitle'],
            'description' => $this->webdata['Sitedata']['description'],
            'businessdata' => nl2br($this->webdata['Sitedata']['businessdata']),
            'mainImage' => $this->webdata['Sitedata']['mainImage'],
            'logoImage' => $this->webdata['Sitedata']['logoImage'],
            'Images' => $this->webdata['Sitedata']['Images'],
            'Maps' => $this->webdata['Admindata']['Maps'],
			'lang' => $lang
        );

        // Check for new installation
        if( $this->webdata['Admindata']['pass'] == md5($this->config->get('defaultPass'))) {
                $vars['install'] = true;
                Session::notice($lang['msgchangedefaultpass'], 1);
        }

        $this->load('cover.html', $vars);

    }

    public function sendContact() {
        // Validation

        $mail = new phpmailer();

        $mail->PluginDir    = "../lib/";
        $mail->Mailer       = "sendmail";

        $mail->Host         = $this->webdata['Admindata']['fromsmtp'];
        if( $this->webdata['Admindata']['fromssl'] )
            $mail->SMTPAuth = true;
        //$mail->SMTPSecure   = 'ssl';//$this->webdata['Admindata']['fromssl'];
        $mail->Port         = $this->webdata['Admindata']['fromport'];
        $mail->Username     = $this->webdata['Admindata']['fromemail'];
        $mail->Password     = $this->webdata['Admindata']['frompass'];
        $mail->From         = $this->webdata['Admindata']['fromemail'];
        $mail->FromName     = $this->webdata['Sitedata']['title'];

        $mail->IsHTML(true); 

        $mail->Timeout      = 30;

        $mail->AddAddress( $this->webdata['Sitedata']['contactEmail'] );

        $mail->Subject      = $_POST['subject'] != '' ? "Contacto desde la web: ".$_POST['subject'] : "Formulario de contacto de " . $this->webdata['Sitedata']['title'];

        $body = "<b>Mensaje enviado desde el formulario de contacto de ".$this->webdata['Sitedata']['title']."</b>";
        if( $_POST['name'] != '' )
            $body .= "<br /><br /><b>Nombre</b>: " . $_POST['name'];
        if( $_POST['email'] != '' )
            $body .= "<br /><br /><b>Forma de contacto</b>: " . $_POST['email'];
        if( $_POST['subject'] != '' )
            $body .= "<br /><br /><b>Asunto</b>: " . $_POST['subject'];
        if( $_POST['comment'] != '' )
            $body .= "<br /><br /><b>Comentario</b>:<br /><em> " . nl2br($_POST['comment']) . '</em><br />';

        $mail->Body = $body;

        $body = "Mensaje enviado desde el formulario de contacto de " . $this->webdata['Sitedata']['title'];
        if( $_POST['name'] != '' )
            $body .= "

Nombre: " . $_POST['name'];
        if( $_POST['email'] != '' )
            $body .= "

Forma de contacto: " . $_POST['email'];
        if( $_POST['subject'] != '' )
            $body .= "

Asunto: " . $_POST['subject'];
        if( $_POST['comment'] != '' )
            $body .= "

Comentario:
" . $_POST['subject'];

        $mail->AltBody = $body;

        $success = $mail->Send();

        $tries = 1;
        while ((!$success) && ($tries < 5)) {
            sleep(5);
            $success = $mail->Send();
            $tries++;
        }

        if(!$success)
        {
            Session::notice("No se pudo enviar el mensaje: <br/>".$mail->ErrorInfo, 1);
            $this->_redirect('Cover');
        }
        else
        {
            Session::notice("Mensaje enviado correctamente", 0);
            $this->_redirect('index');
        }

    }

    public function notFound($controller) {
        echo "No encontrado: ".$controller;
    }
}

?>
