<?php
/*
 Mâgico
 http://www.parleboo.com
 Copyright 2012 Leandro Garber <leandrogarber@gmail.com>
 Attribution-NonCommercial-ShareAlike 3.0 Unported (CC BY-NC-SA 3.0)
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Simple checkbox field.
 * 
 * Make sure to set a tiny int (1) with default 0 not null
 * 
 */
class Checkbox extends Field {
	
	function render()
	{
		$data = array();
		
		
		$data['name'] = $this->name;
		$data['value'] = $this->value;
		$data['helptext'] = $this->helptext;
		
		parent::render($data);
	}
}

?>
