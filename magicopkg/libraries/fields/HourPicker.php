<?php
/*
 Mâgico
 http://www.parleboo.com
 Copyright 2012 Leandro Garber <leandrogarber@gmail.com>
 Attribution-NonCommercial-ShareAlike 3.0 Unported (CC BY-NC-SA 3.0)
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class HourPicker extends Field {
	
	public $safeHtml = false;
	public $nullable = true;
	
	function render()
	{
		$data = array();
		
		
		$data['name'] = $this->name;
		$data['cssId'] = $this->cssId;
		$data['value'] = $this->value;
		$data['helptext'] = $this->helptext;
		
		parent::render($data);
	}
	
	function setDatabaseFields() {
		parent::setDatabaseFields();
		
		$this->databaseFields = array (
			$this->name => array(
				'type' => 'TIME'
			)
		);
	}
}

?>
