<?php
/*
 Mâgico
 http://www.parleboo.com
 Copyright 2012 Leandro Garber <leandrogarber@gmail.com>
 Attribution-NonCommercial-ShareAlike 3.0 Unported (CC BY-NC-SA 3.0)
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class TextEditor extends Field {
	
	/**
	 * Private use only, don't use it, use $extraTags instead
	 * 
	 * @var type 
	 */
	private $styles = array();
	
	/**
	 * Array with extra styles you want to provide for the end user. The key will be used as the styleName. Example: [ 'styleName' => 'h2' ]
	 * B, I, U and P are always accepted
	 * 
	 * @var array
	 */
	public $extraTags = array();
	
	/**
	 * Allow links
	 * 
	 * @var boolean 
	 */
	public $allowLinks = true;
	
	/**
	 * Allow lists UL and OL
	 * 
	 * @var boolean 
	 */
	public $allowLists = true;
    
    /**
	 * Allow inline images
	 * 
	 * @var boolean 
	 */
	public $allowImages = false;
	
	function __construct($label = null, $helptext = '', $defaultValue = '') {
		parent::__construct($label, $helptext, $defaultValue);
		
		$this->safeHtml = false;
	}
	
	/**
	 * Return the styles for CKEditor to work. populateConfig must be called first.
	 * 
	 * @return type 
	 */
	public function getStyles()
	{
		return $this->styles;
	}
	
	
	/**
	 * Generate the config array for CKEditor to work.
	 * 
	 * @return Array The config array 
	 */
	public function populateConfig()
	{
		//allowedContent, para que no pueda pegar tags que no esten permitidos
		$config['allowedContent'] = 'i u p strong br;';
		$toolbar1 = $toolbar2 = $toolbar3 = $toolbar4 = array();
		
		// Extra tags
		if ( count($this->extraTags) )
		{
			$config['stylesSet'] = $this->name;
			
			foreach ( $this->extraTags as $key => $tag )
			{
				$tagName = $tag;
				
				if ( !is_numeric($key) )
					$tagName = $key;
				else
				{
					if ( $tag == 'h1' )
						$tagName = 'Título';
					elseif ( $tag == 'h2' || $tag == 'h3' || $tag == 'h4' )
						$tagName = 'Subtítulo';
				}
				
				$this->styles[] = array( 'name' => $tagName, 'element' => $tag );
				$config['allowedContent'] .= "$tag;";
			}
			
			$toolbar1[] = 'Styles';
		}
		
		$toolbar1[] = 'Bold';
		$toolbar1[] = 'Italic';
		$toolbar1[] = 'Underline';
		
		if ( $this->allowLinks )
		{
			$config['allowedContent'] .= "a[!href,target];";
			$toolbar2 = array( 'Link', 'Unlink' );
		}
		
		if ( $this->allowLists )
		{
			$config['allowedContent'] .= "ul ol li;";
			$toolbar3 = array('NumberedList', 'BulletedList');
		}
        
		if ( $this->allowImages )
		{
			$config['allowedContent'] .= "img[alt,src]{border-style,border-width,float,height,margin,margin-bottom,margin-left,margin-right,margin-top,width};";
			$config['filebrowserImageUploadUrl'] = 'abm/ajaxFieldCallBack/' . get_class($this->getParent()) . '/' . $this->name ;
            $toolbar4 = array( 'Image' );
		}
		
		$config['toolbar'] = array(
			  $toolbar1,
			  $toolbar2,
			  $toolbar3,
			  array( 'PasteFromWord', 'PasteText' ),
              $toolbar4
		);
		
		$ci =& get_instance();
		
		// Idioma
		
		if ( $ci->config->item('language') == 'spanish' )	
			$config['language'] = 'es';
		else
			$config['language'] = 'en';
		        
		return $config;
	}
	
    /**
     * If image is enabled this method will take care of uploading images
     */
    public function ajaxCallBack()
    {
        $filename = $_FILES['upload']['name'];
        $ext = substr($filename, stripos($filename, '.') + 1);
        $filename = substr($filename, 0, stripos($filename, '.'));
        
        while (file_exists(UPLOAD_DIR . $filename . '.' . $ext)) {
            $filename .= rand(10, 99);
        }
        
        $filename = $filename . '.' . $ext;
        
        move_uploaded_file($_FILES['upload']['tmp_name'], UPLOAD_DIR . $filename);
        
        $funcNum = $_GET['CKEditorFuncNum'];
        $url = site_url(UPLOAD_DIR . $filename);
        echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($funcNum, '$url', '$message');</script>";
    }
    
	function render()
	{
		$data = array();
		
		$data['name'] = $this->name;
		$data['value'] = $this->value;
		$data['helptext'] = $this->helptext;
		$data['config'] = json_encode($this->populateConfig());
		$data['styles'] = json_encode($this->styles);
		
		parent::render($data);
	}
}
