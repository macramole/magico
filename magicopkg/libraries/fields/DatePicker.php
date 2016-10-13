<?php

/*
 Mâgico
 http://www.parleboo.com
 Copyright 2012 Leandro Garber <leandrogarber@gmail.com>
 Attribution-NonCommercial-ShareAlike 3.0 Unported (CC BY-NC-SA 3.0)
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Shows a jQuery UI's date picker
 */
class DatePicker extends Field {
	
	/**
	 * Is today the default date ?
	 * 
	 * @var type 
	 */
	public $defaultToday = true;
	
	function render()
	{
		$data = array();
		
		$data['name'] = $this->name;
		$data['value'] = $this->value;
		$data['helptext'] = $this->helptext;
		
		if ( !$data['value'] && $this->defaultToday )
			$data['value'] = date('Y-m-d');
		
		parent::render($data);
	}
	
	function setDatabaseFields() {
		parent::setDatabaseFields();
		
		$this->databaseFields = array (
			$this->name => array(
				'type' => 'DATE'
			)
		);
	}
}

?>
