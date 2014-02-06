<?php
	class Magico
	{
		function __construct()
		{
			$ci =& get_instance();
			
			$ci->config->load('config_magico');
			
			$ci->load->library('masterpage');
			$ci->load->database();
			$ci->load->library('adminuser');
			$ci->load->library('form_validation');
			
			$ci->load->helper('form');
			$ci->load->helper('url');
			$ci->load->helper('language');
			$ci->load->helper('magico');
			
			$ci->lang->load('magico', $ci->lang->getAdminLanguage());
			
			
			
			/*
			 * DEFINES
			 */
			define('UPLOAD_DIR','uploads/');
			define('THUMBS_DIR',UPLOAD_DIR . 'thumbs/');

			//Thumbnail generator
			define('ZEBRA_IMAGE_BOXED', 0);
			define('ZEBRA_IMAGE_NOT_BOXED', 1);
			define('ZEBRA_IMAGE_CROP_TOPLEFT', 2);
			define('ZEBRA_IMAGE_CROP_TOPCENTER', 3);
			define('ZEBRA_IMAGE_CROP_TOPRIGHT', 4);
			define('ZEBRA_IMAGE_CROP_MIDDLELEFT', 5);
			define('ZEBRA_IMAGE_CROP_CENTER', 6);
			define('ZEBRA_IMAGE_CROP_MIDDLERIGHT', 7);
			define('ZEBRA_IMAGE_CROP_BOTTOMLEFT', 8);
			define('ZEBRA_IMAGE_CROP_BOTTOMCENTER', 9);
			define('ZEBRA_IMAGE_CROP_BOTTOMRIGHT', 10);

			//Magico setData
			define('MAGICO_DRAGGABLE', 0);
			define('MAGICO_SORTABLE', 1);
			define('MAGICO_CUSTOM', 2);
			define('MAGICO_AUTO', 'auto');
			
			define('MAGICO_PATH', APPPATH . '../magico/');
			define('MAGICO_PATH_LIB', MAGICO_PATH . 'libraries/');
			define('MAGICO_PATH_JS', 'magico/js');
			define('MAGICO_PATH_CSS', 'magico/css');
			define('MAGICO_PATH_IMG', 'magico/images/');
		}
	}
?>