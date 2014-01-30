<?php

/*
 M�gico
 http://www.parleboo.com
 Copyright 2012 Leandro Garber <leandrogarber@gmail.com>
 Attribution-NonCommercial-ShareAlike 3.0 Unported (CC BY-NC-SA 3.0)
*/

class MY_Controller extends CI_Controller {
	private $_additionalCss = array();
	private $_additionalJs = array();
	
	/**
	 * You can't use Mâgico without the master_page lib. We specified a common name for your master_page view. You can change it below.
	 * 
	 * If you are using more than one master_page, the set this to '' and use the setMasterPage() method before the show() method.
	 * 
	 * @var string
	 */
	protected $_masterPageName = 'master_page';
	
	
	/**
	 * Page's main title. Set this from your MasterController
	 * 
	 * @var type 
	 */
	protected $_pageTitle;
	
	/**
	 * Use the setSectionTitle() method
	 * 
	 * @var type 
	 */
	private $_pageSectionTitle;
	
	/**
	 * This are used for facebook meta tags. Use setFacebookImage() and setFacebookDescription()
	 * 
	 * @var type 
	 */
	private $og_image;
	private $og_description;
	
	public function __construct()
	{
		parent::__construct();
			
		if ( $this->_masterPageName )
			$this->setMasterPage($this->_masterPageName);
		
	}
	
	/**
	 * Shows up the login form. 
	 */
	public function magico()
	{
		$this->masterpage->addContentPage('admin/admin_login', 'Adminnav');
		$this->index();
	}
	
	/**
	 * AJAX function for logging in the user.
	 * 
	 * @return An AdminUser constant
	 */
	public function magico_login()
	{
		$loginResult = $this->adminuser->login($_POST['user'], $_POST['password'], $_POST['remember']);

		if ( $loginResult == AdminUser::NOT_LOGGED_IN )
		{
			echo json_encode(array('error' => true));
		}
		elseif ( $loginResult == AdminUser::LOGGED_IN )
		{
			echo json_encode(array('success' => true));
		}
		
		return $loginResult;
	}
	
	/**
	 * Logs out the user 
	 */
	public function magico_logout()
	{
		$this->adminuser->logout();
		redirect(base_url());
	}
	
	/**
	 * If using the provided master page's head it will change the title of the page to <website's name> | <$title>
	 * 
	 * @param String $title 
	 */
	protected function setSectionTitle($title)
	{
		$this->_pageSectionTitle = $title;
	}

	/**
	 * Sets the master page name. The default name is master_page. Don't need to use this if you are using that name
	 * 
	 * This also loads the css and js that are needed for Mâgico to work.
	 * If you develop a new field that needs an external js, please use the LAB library.
	 * For example, add something like this to your field view:
	 * 
	 * <script type="text/javascript">
			$( function() {
				$LAB.script('<?= MAGICO_PATH_JS ?>/your.script.without.the.js.extension').wait( function() {
					... do stuff
				});
			});
		</script>
	 * 
	 * 
	 * @param type $view 
	 */
	protected function setMasterPage($view)
	{
		$this->_masterPageName = $view;
		$this->masterpage->setMasterPage($this->_masterPageName);
		
		if ( $this->adminuser->isLogged() )
		{
			$this->_additionalCss[] = 'magico.css';
			$this->_additionalCss[] = 'prettyPhoto.css';
			$this->_additionalCss[] = 'jquery.jgrowl.css';
			$this->_additionalCss[] = 'jquery-ui-1.8.14.custom.css';
			
			$this->_additionalJs[] = 'LAB.min.js';
			$this->_additionalJs[] = 'jquery.prettyPhoto.js';
			$this->_additionalJs[] = 'magico.js';
			$this->_additionalJs[] = 'jquery.form.js';
			$this->_additionalJs[] = 'jquery.cookie.js';
			$this->_additionalJs[] = 'jquery.animate-shadow-min.js';
			$this->_additionalJs[] = 'jquery.jgrowl.js';
			$this->_additionalJs[] = 'ckeditor/ckeditor.js';
			$this->_additionalJs[] = 'ckeditor/adapters/jquery.js';
		}
	}
	
	/**
	 * If using the provided master page's head it will set the image url to the appropiate facebook tag
	 * 
	 * @param String $image Url of the image
	 */
	public function setFacebookImage($image)
	{
		$this->og_image = $image;
	}
	
	/**
	 * If using the provided master page's head it will set the description to the appropiate facebook tag
	 * 
	 * @param String $desc 
	 */
	public function setFacebookDescription($desc)
	{
		$this->og_description = strip_tags($desc);
	}

	/**
	 * This might come in handy if you are doing a responsive site where images load depending to screen size
	 * 
	 * @param type $filename
	 * @param type $width 
	 */
	public function responsiveImage($filename, $width)
	{
		$arrMime = array( 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif', 'png' => 'image/x-png', 'bmp' => 'image/x-ms-bmp' );
		
		$file = magico_thumb(urldecode($filename), $width, 0, ZEBRA_IMAGE_CROP_CENTER, false);
		
		if ( $file )
		{
			$fileExtension = substr( $file, strrpos($file, '.') + 1 );
		
			header("Content-Type: {$arrMime[$fileExtension]}");
			readfile($file);
		}
		
	}
	
	/**
	 * Builds the head of the master page
	 * 
	 * @return type 
	 */
	private function magico_load()
	{
		$head = '';
		
		if ( $this->adminuser->isLogged() )
		{
			$jsPath = MAGICO_PATH_JS;
			$cssPath = MAGICO_PATH_CSS;
			
			foreach ($this->_additionalCss as $css )
				$head .= "<link rel='stylesheet' type='text/css' href='$cssPath/$css' />\n\r";
			
			foreach ($this->_additionalJs as $js )
				$head .= "<script type='text/javascript' src='$jsPath/$js'></script>\n\r";
		}
		
		return $head;
	}
	
	/**
	 * This is just a wrapper of the addContentPage function in the masterpage library. 
	 * Its used to add content views to the different sections
	 * 
	 * @param string $viewName
	 * @param array $data
	 * @param string $tagName 
	 */
	public function addContentPage($viewName, $data = array(), $tagName = 'Content')
	{
		$this->masterpage->addContentPage($viewName, $tagName, $data);
	}
	
	/**
	 * Shows the site with all the content added via addContentPage method.
	 * In the MasterController controller there is an override of this function if you want to do further processing.
	 * 
	 * @param type $additionalData 
	 */
	public function show($additionalData = array())
	{
		$messages = $_SESSION['messages'] ? $_SESSION['messages'] : array();
		$this->masterpage->addContentPage('admin/admin_nav', 'Magico', array('messages' => $messages));
		unset($_SESSION['messages']);
		
		$data = array( 'head' => $this->magico_load(), 'title' => $this->_pageTitle, 'sectionTitle' => $this->_pageSectionTitle, 'og_image' => site_url($this->og_image), 'og_description' => $this->og_description );
		$data = array_merge($data, $additionalData);
		
		$this->masterpage->show($data);
	}
}