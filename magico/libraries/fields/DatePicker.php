<?php

/*
 Mâgico
 http://www.parleboo.com
 Copyright 2012 Leandro Garber <leandrogarber@gmail.com>
 Attribution-NonCommercial-ShareAlike 3.0 Unported (CC BY-NC-SA 3.0)
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class DatePicker extends Field {
	
	/**
	 * Aparece la fecha de hoy como default
	 * 
	 * @var type 
	 */
	public $defaultHoy = true;
	
	function render()
	{
		$data = array();
		
		
		$data['name'] = $this->name;
		$data['value'] = $this->value;
		$data['helptext'] = $this->helptext;
		
		if ( !$data['value'] && $this->defaultHoy )
			$data['value'] = date('Y-m-d');
		
		parent::render($data);
	}
}

?>
