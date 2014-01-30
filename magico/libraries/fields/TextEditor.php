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
		$toolbar1 = $toolbar2 = $toolbar3 = array();
		
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
		
		$config['toolbar'] = array(
			  $toolbar1,
			  $toolbar2,
			  $toolbar3,
			  array( 'PasteFromWord', 'PasteText' )
		);
		
		$ci =& get_instance();
		
		// Idioma
		
		if ( $ci->config->item('language') == 'spanish' )	
			$config['language'] = 'es';
		else
			$config['language'] = 'en';
		
		return $config;
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
