<?php
/*
 Mâgico
 http://www.parleboo.com
 Copyright 2012 Leandro Garber <leandrogarber@gmail.com>
 Attribution-NonCommercial-ShareAlike 3.0 Unported (CC BY-NC-SA 3.0)
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SimpleSelect extends Field {
	
	public $arrValues = array(); // array( array('id' => 1, 'value' => hola) );
	public $addDefaultOption = false; //cambiar al nombre del default para que aparezca
	public $safeHtml = false;
	
	const FORCED_TITLE = '%s_forced_title';
	const FORCED_ID = '%s_forced_id';
	
	function setDatabaseFields() {
		parent::setDatabaseFields();
		
		$enum = '';
		
		foreach ( $this->arrValues as $value ) {
			$enum .= "'$value[title]',";
		}
		
		if ( strlen($enum) > 0 ) {
			$enum = substr( $enum, 0, strlen($enum) - 1 );
		}
		
		$this->databaseFields = array (
			$this->name => array(
				'type' => 'ENUM',
				'constraint' => $enum
			)
		);
	}
	
	function render()
	{
		$data = array();
		
		
		$data['name'] = $this->name;
		$data['value'] = $this->value;
		$data['helptext'] = $this->helptext;
		$data['arrValues'] = $this->arrValues;
		$data['addDefaultOption'] = $this->addDefaultOption;
		$data['forcedValue'] = $this->checkForcedValue();
		
		parent::render($data);
	}
	
	/**
	 * Override.
	 * It is used by ForeignModel to force an element that is not created yet.
	 * 
	 * @return mixed
	 */
	protected function checkForcedValue()
	{
		if ( isset($_GET[$this->name]) )
			$this->value = $_GET[$this->name];
		else if ( isset( $_GET[sprintf(self::FORCED_ID, $this->name)] ) && isset( $_GET[sprintf(self::FORCED_TITLE, $this->name)] ) )
		{
			return array('id' => $_GET[sprintf(self::FORCED_ID, $this->name)], 'title' => $_GET[sprintf(self::FORCED_TITLE, $this->name)]);
		}
		
		return false;
	}
	
	/**
	 * Three flavors of array are permitted:
	 * 
	 * array( array('id' => 1, 'title' => 'hola'), array('id' => 2, 'title' => 'chau') );
	 * 
	 * or
	 * 
	 * array( 'id' => 'title', 'id' => 'title' ); Note that the id is string not int.
	 * 
	 * or if using an enum field in the database
	 * 
	 * array('title','title');
	 * 
	 * @param array $arrValues
	 */
	function setValues($arrValues)
	{
		if ( $arrValues && count($arrValues) > 0 )
		{
			if ( is_array(current($arrValues)) )
			{
				$this->arrValues = $arrValues; // guessing array is array( array('id' => 1, 'title' => 'hola'), array('id' => 2, 'title' => 'chau') );
			}
			else
			{
				// guessing array is array( 'id' => 'title', 'id' => 'title' );
				
				$this->arrValues = array();
				
				foreach ( $arrValues as $key => $value ) {
					if ( !is_int($key) ) {
						$this->arrValues[] = array( 'id' => $key, 'title' => $value );
					} else {
						$this->arrValues[] = array( 'id' => $value, 'title' => $value );
					}
				}
			}	
		}
		else
			$this->arrValues = array();
	}
	
	/**
	 * This field knows how to validate itself
	 * 
	 * @param type $rules
	 */
	public function validate($rules = 'required')
	{	
		if ( $rules == 'required' && $_POST[$this->name] <= 0 )
		{
			$ci =& get_instance();
			$ci->form_validation->set_error( lang('required'), $this->name );
		}
	}
}

?>
