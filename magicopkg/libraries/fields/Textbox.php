<?php

/*
 Mâgico
 http://www.parleboo.com
 Copyright 2012 Leandro Garber <leandrogarber@gmail.com>
 Attribution-NonCommercial-ShareAlike 3.0 Unported (CC BY-NC-SA 3.0)
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Textbox extends Field {
	
	/**
	 * Password en vez de texto
	 * 
	 * @var boolean 
	 */
	public $isPassword = false;
	
	/**
	 * Cantidad máxima de caracteres
	 * 
	 * @var int 
	 */
	public $maxLength = 150;
	
	public $prefix = '';
	public $postfix = '';
	
	function render()
	{
		$data = array();
		
		$data['name'] = $this->name;
		$data['value'] = $this->value;
		$data['helptext'] = $this->helptext;
		$data['isPassword'] = $this->isPassword;
		$data['maxLength'] = $this->maxLength;
		$data['prefix'] = $this->prefix;
		$data['postfix'] = $this->postfix;
		
		parent::render($data);
	}
}

?>
