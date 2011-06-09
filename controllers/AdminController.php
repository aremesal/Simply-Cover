<?php

// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Álvaro Remesal <contacto at alvaroremesal dot net>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class AdminController extends Controller {

    public function  __construct() {
        parent::__construct();
    }

    public function index($idForm = '') {
        $this->isLogged();
		
        $config = Config::singleton();

		include_once '../lang/'.$config->get('lang').'.php';

        $buffer = '';
        if( $fp = fopen($config->get('customCSS'), 'r') ) {
            while(!feof($fp)) {
                $buffer .= fgets($fp);
            }

            fclose($fp);
        }

        $vars = array(
            // AdminWebdata
            'title' => $this->webdata['Sitedata']['title'],
            'subtitle' => $this->webdata['Sitedata']['subtitle'],
            'description' => $this->webdata['Sitedata']['description'],
            'businessdata' => $this->webdata['Sitedata']['businessdata'],
            'logoImage' => $this->webdata['Sitedata']['logoImage'],
            'mainImage' => $this->webdata['Sitedata']['mainImage'],
            'contactEmail' => $this->webdata['Sitedata']['contactEmail'],

            // AdminAdmin
            'adminEmail' => $this->webdata['Admindata']['adminEmail'],
            'fromemail' => $this->webdata['Admindata']['fromemail'],
            'fromsmtp' => $this->webdata['Admindata']['fromsmtp'],
            'fromport' => $this->webdata['Admindata']['fromport'],
            'fromssl' => $this->webdata['Admindata']['fromssl'],
            'frompass' => $this->webdata['Admindata']['frompass'],
            'Maps' => $this->webdata['Admindata']['Maps'],

            // CSS
            'css' => $buffer,

            // AdminImages
            'Images' => $this->webdata['Sitedata']['Images'],

            // Common
            'logoImage' => $this->webdata['Sitedata']['logoImage'],
            'webtitle' => $this->webdata['Sitedata']['title'],

            'idForm' => $idForm,
			'lang' => $lang
        );

        if( ! is_writable($config->get('customCSS')) )
            Session::notice("CSS file can't be written (File: ".$config->get('customCSS').") ", 1);

        $this->load('admin.html', $vars);
    }

    public function entrance() {
        $config = Config::singleton();
		include_once '../lang/'.$config->get('lang').'.php';
		
        $vars = array(
            'title' => $this->webdata['Sitedata']['title'],
            'subtitle' => $this->webdata['Sitedata']['subtitle'],
            'description' => $this->webdata['Sitedata']['description'],
            'logoImage' => $this->webdata['Sitedata']['logoImage'],
            'webtitle' => $this->webdata['Sitedata']['title'],
            'Maps' => $this->webdata['Admindata']['Maps'],
			'lang' => $lang
        );

        $this->load('login.html', $vars);
    }

    public function login() {
	
		$config = Config::singleton();
		include_once '../lang/'.$config->get('lang').'.php';
		
        if( isset ($_POST['pass']) && md5($_POST['pass']) == $this->webdata['Admindata']['pass']) {
            Session::user(session_id());
            $this->_redirect('index');
        }
        else {
            Session::notice($lang['loginerror'], 1);
            $this->_redirect('Admin::entrance');
        }
    }

    public function logout() {
        Session::set('user','');
        Session::destroy();
        $this->_redirect('Cover::index');
    }

    public function setWebdata() {
        $this->isLogged();

        $config = Config::singleton();
		include_once '../lang/'.$config->get('lang').'.php';
		
        // Data validation


        // Set new values
        $this->webdata['Sitedata']['title'] = $_POST['title'];
        $this->webdata['Sitedata']['subtitle'] = $_POST['subtitle'];
        $this->webdata['Sitedata']['description'] = $_POST['description'];
        $this->webdata['Sitedata']['businessdata'] = $_POST['businessdata'];
        $this->webdata['Sitedata']['contactEmail'] = $_POST['contactEmail'];

        // Logo
        $imgObj = new upload($_FILES['logo']);
        if ($imgObj->uploaded) {

            $pre = '';//'80_';

            //$imgObj->file_overwrite     = true;
            $imgObj->file_name_body_pre = 'original_';
            $imgObj->process($config->get('dirImagesUploads'));
            
            // Thumbnail
            $imgObj->file_name_body_pre = 'thumb_';
            $imgObj->image_resize       = true;
            $imgObj->image_x            = 100;
            $imgObj->image_y            = 100;
            $imgObj->image_ratio_y      = true;
            $imgObj->process($config->get('dirImagesUploads'));

            // Favicon
            if( file_exists($config->get('dirImagesUploads').'favicon.gif'))
                        unlink($config->get('dirImagesUploads').'favicon.gif');
            $imgObj->file_new_name_body = 'favicon';
            $imgObj->image_resize       = true;
            $imgObj->image_x            = 16;
            $imgObj->image_y            = 16;
            $imgObj->image_convert      = 'gif';
            $imgObj->process($config->get('dirImagesUploads'));

            $imgObj->file_name_body_pre = $pre;
            $imgObj->image_resize       = true;
            $imgObj->image_y            = 80;
            $imgObj->image_ratio_x      = true;
            $imgObj->process($config->get('dirImagesUploads'));
            if ($imgObj->processed) {
                // Delete previous image, if any
                if( $this->webdata['Sitedata']['logoImage'] != '' && $this->webdata['Sitedata']['logoImage'] != $pre.$imgObj->file_dst_name) {
                    if( file_exists($config->get('dirImagesUploads').$this->webdata['Sitedata']['logoImage']))
                        unlink($config->get('dirImagesUploads').$this->webdata['Sitedata']['logoImage']);
                    if( file_exists($config->get('dirImagesUploads').'original_'.$this->webdata['Sitedata']['logoImage']))
                        unlink($config->get('dirImagesUploads').'original_'.$this->webdata['Sitedata']['logoImage']);
                    if( file_exists($config->get('dirImagesUploads').'thumb_'.$this->webdata['Sitedata']['logoImage']))
                        unlink($config->get('dirImagesUploads').'thumb_'.$this->webdata['Sitedata']['logoImage']);
                }
                // Set new image
                $this->webdata['Sitedata']['logoImage'] = $pre.$imgObj->file_dst_name;
                $imgObj->clean();
            } else {
                Session::notice('Error : ' . $imgObj->error, 1);
            }
        }

        // Main image
        $imgObj = new upload($_FILES['image']);
        if ($imgObj->uploaded) {

            $pre = '';//'400_';

            //$imgObj->file_overwrite     = true;
            $imgObj->file_name_body_pre = 'original_';
            $imgObj->process($config->get('dirImagesUploads'));

            // Thumbnail
            $imgObj->file_name_body_pre = 'thumb_';
            $imgObj->image_resize       = true;
            $imgObj->image_x            = 100;
            $imgObj->image_y            = 100;
            $imgObj->image_ratio_y      = true;
            $imgObj->process($config->get('dirImagesUploads'));

            $imgObj->file_name_body_pre = $pre;
            $imgObj->image_resize       = true;
            $imgObj->image_x            = 340;
            $imgObj->image_ratio_y      = true;

            $imgObj->process($config->get('dirImagesUploads'));
            if ($imgObj->processed) {
                // Delete previous image, if any
                if( $this->webdata['Sitedata']['mainImage'] != '' && $this->webdata['Sitedata']['mainImage'] != $pre.$imgObj->file_dst_name) {
                    if( file_exists($config->get('dirImagesUploads').$this->webdata['Sitedata']['mainImage']))
                        unlink($config->get('dirImagesUploads').$this->webdata['Sitedata']['mainImage']);
                    if( file_exists($config->get('dirImagesUploads').'original_'.$this->webdata['Sitedata']['mainImage']))
                        unlink($config->get('dirImagesUploads').'original_'.$this->webdata['Sitedata']['mainImage']);
                    if( file_exists($config->get('dirImagesUploads').'thumb_'.$this->webdata['Sitedata']['mainImage']))
                        unlink($config->get('dirImagesUploads').'thumb_'.$this->webdata['Sitedata']['mainImage']);
                }
                // Set new image
                $this->webdata['Sitedata']['mainImage'] = $pre.$imgObj->file_dst_name;
                $imgObj->clean();
            } else {
                Session::notice('Error : ' . $imgObj->error, 1);
            }
        }

        $model = Model::singleton();

        $model->saveData($this->webdata);

        // Redirect to form
        Session::notice($lang['adminchangesok'], 0);
        $this->_redirect('index','AdminWebdata');
    }

    public function setAdmindata() {
        $this->isLogged();

        $config = Config::singleton();
		include_once '../lang/'.$config->get('lang').'.php';
		
        $errorMsg = '';

        // Data validation
        if($_POST['password'] != '' && $_POST['password'] != $_POST['password2'])
            $errorMsg .= '<br />- '.$lang['adminpassdontmatch'];

        if( $errorMsg != '') {
            $errorMsg = $lang['adminerrors'] . ':' . $errorMsg;
            Session::notice($errorMsg, 1);
            $this->_redirect('index','AdminAdmin');
            return;
        }

        // Set new values
        $this->webdata['Admindata']['email'] = $_POST['email'];
        $this->webdata['Admindata']['fromemail'] = $_POST['fromemail'];
        $this->webdata['Admindata']['frompass'] = $_POST['frompass'];
        $this->webdata['Admindata']['fromsmtp'] = $_POST['fromsmtp'];
        $this->webdata['Admindata']['fromport'] = $_POST['fromport'];
        $this->webdata['Admindata']['fromssl'] = $_POST['fromssl'];

        // Maps
        $this->webdata['Admindata']['Maps']['api'] = $_POST['mapsapi'];
        $this->webdata['Admindata']['Maps']['txt'] = str_replace("\n", '<br />', $_POST['mapstxt']);
        $this->webdata['Admindata']['Maps']['lon'] = $_POST['mapslon'];
        $this->webdata['Admindata']['Maps']['lat'] = $_POST['mapslat'];
        $this->webdata['Admindata']['Maps']['zoom'] = $_POST['mapszoom'];

        if($_POST['password'] != '')
            $this->webdata['Admindata']['pass'] = md5($_POST['password']);

        $model = Model::singleton();

        $model->saveData($this->webdata);


        // Redirect to form
        Session::notice($lang['adminchangesok'], 0);
        $this->_redirect('index','AdminAdmin');
    }

    public function setCustomCSS() {
        $this->isLogged();

        
        $config = Config::singleton();
		include_once '../lang/'.$config->get('lang').'.php';

        $errorMsg = '';

        // Data validation
        if( ! is_writable($config->get('customCSS')) )
            $errorMsg .= '<br />- ' . $lang['admincantwritecss'];

        if( $errorMsg != '') {
            $errorMsg = $lang['adminerrors'] . ':' . $errorMsg;
            Session::notice($errorMsg, 1);
            $this->_redirect('index','AdminCSS');
            return;
        }

        if( $fp = fopen($config->get('customCSS'), 'w') ) {
            fputs($fp, $_POST['css']);
            fclose($fp);
        }

        // Redirect to form
        Session::notice($lang['adminchangesok'], 0);
        $this->_redirect('index','AdminCSS');
    }

    public function setImagesdata() {
        $this->isLogged();

        $config = Config::singleton();
		include_once '../lang/'.$config->get('lang').'.php';
		
        // Data validation

        $imgObj = new upload($_FILES['image']);
        if ($imgObj->uploaded) {

            $pre = '';//'600_';

            //$imgObj->file_overwrite     = true;
            $imgObj->file_name_body_pre = 'original_';
            $imgObj->process($config->get('dirImagesUploads'));

            // Thumbnail
            $imgObj->file_name_body_pre = 'thumb_';
            $imgObj->image_resize       = true;
            $imgObj->image_y            = 75;
            $imgObj->image_ratio_x      = true;
            $imgObj->process($config->get('dirImagesUploads'));

            $imgObj->file_name_body_pre = $pre;
            $imgObj->image_resize       = true;
            $imgObj->image_y            = 600;
            $imgObj->image_ratio_x      = true;

            $imgObj->process($config->get('dirImagesUploads'));
            if ($imgObj->processed) {
                if( $_POST['title'] != '' )
                    $title = $_POST['title'];
                else
                    $title = $imgObj->file_src_name_body;

                $this->webdata['Sitedata']['Images'][$title] = $pre.$imgObj->file_dst_name;
                $imgObj->clean();
            } else {
                Session::notice('Error : ' . $imgObj->error, 1);
            }


            $model = Model::singleton();

            $model->saveData($this->webdata);

            // Redirect to form
            Session::notice($lang['adminchangesok'], 0);
            $this->_redirect('index','AdminImages');
        }
        else {
            // Redirect to form
            Session::notice('Error : ' . $imgObj->error, 1);
            $this->_redirect('index','AdminImages');
        }

    }

    public function delImage($img) {
        $this->isLogged();

        $config = Config::singleton();
		include_once '../lang/'.$config->get('lang').'.php';
        
        // Delete item by value (file), not key (title), because titles can be repeated
        unlink($config->get('dirImagesUploads').$img);
        unlink($config->get('dirImagesUploads').'original_'.$img);
        unlink($config->get('dirImagesUploads').'thumb_'.$img);
        unset($this->webdata['Sitedata']['Images'][array_search($img, $this->webdata['Sitedata']['Images'])]);
        
        $model = Model::singleton();

        $model->saveData($this->webdata);

        // Redirect to form
        Session::notice($lang['adminchangesok'], 0);
        $this->_redirect('index','AdminImages');

    }

    private function thumb($file) {
        $config = Config::singleton();
        
        $imgObj = new upload($file);
        if ($imgObj->uploaded) {

            $pre = 'thumb_';

            $imgObj->file_overwrite     = true;
            $imgObj->file_name_body_pre = $pre;
            $imgObj->image_resize       = true;
            $imgObj->image_y            = 100;
            $imgObj->image_ratio_x      = true;

            $imgObj->process($config->get('dirImagesUploads'));
            if ($imgObj->processed) {
                $imgObj->clean();
            } else {
                Session::notice('Error creating thumbnail: ' . $imgObj->error, 1);
            }
        }
    }

    private function isLogged() {
        if( ! Session::user() || Session::user() != session_id() ) {
            $this->_redirect('Admin::entrance');
        }
        return;
    }

}

?>
