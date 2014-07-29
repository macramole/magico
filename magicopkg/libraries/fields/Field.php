<?php

/*
 Mâgico
 http://www.parleboo.com
 Copyright 2012 Leandro Garber <leandrogarber@gmail.com>
 Attribution-NonCommercial-ShareAlike 3.0 Unported (CC BY-NC-SA 3.0)
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * All fields inherit from here
 */
abstract class Field {
	
	/**
	 * Name is usually the name of the field in the database. The setParent() method will set this automatically.
	 * Label is the name of the field that is shown to the user
	 */
	public $name, $label, $value, $helptext, $cssId;
	
	/**
	 * Automatically save the fields using htmlentities
	 * @var boolean 
	 */
	public $safeHtml = true;
	
	/**
	 * Some fields can also have fields
	 * 
	 * @var array 
	 */
	public $fields = array();
	
	/**
	 * TRUE if it shows up in the CRUD list. This is not set directly, CORE USE ONLY. Use function setListableFields() from MY_Model
	 * @var type 
	 */
	public $listable = false;
	
	/**
	 * TRUE if this field knows how to save itself to the database.
	 * The field class must implement methods save($table, $id), delete($table, $id) and setFieldValue($table, $id, $row);
	 * Right now this type of fields are not interationalized
	 * @var type 
	 */
	public $autoSave = false;
	
	/*
	 * Additional javascript you may want to add
	 * @var type 
	 */
	public $additionalJs;
	
	/**
	 * If the field is disabled it will not show up in the CRUD form and won't be processed for saving it's data
	 * 
	 * @var boolean
	 */
	public $disabled = false;
	
	/**
	 * It is used when constructing the clean URL. If its foreign it will check the URL clean of the specified table
	 * 
	 * @var mixed False or table name
	 */
	public $isForeignKey = false;
	
	/**
	 * This field should be filled when overriding method postSetParent()
	 * The format must be dbforge compatible (check codeigniter's docs)
	 * 
	 * @var array
	 */
	public $databaseFields = array();
	
	/**
	 * If the field has a table associated (beside the one from its Model) it should set it here when overriding method postSetParent()
	 * 
	 * @var string
	 */
	public $table = null;
	
	private $_parent;
	
	function __construct($label = null, $helptext = '', $defaultValue = '')
	{
		$this->label = $label;
		$this->helptext = $helptext;
		$this->value = $defaultValue;
	}
	
	/**
	 * Si al abm se le manda por get field=value pone ese valor 
	 */
	protected function checkForcedValue()
	{
		if ( isset($_GET[$this->name]) )
			$this->value = $_GET[$this->name];
	}
	
	/**
	 * This function is called from the Model to establish its parent and name.
	 * It also call to an overrideable function postSetParent()
	 * 
	 * @param type $parent 
	 */
	function setParent(&$parent)
	{
		$this->_parent = $parent;
		
		if ( !$this->name )
		{
			$name = array_search($this, $this->getParent()->fields);
			
			
			if ( $this->getParent() instanceof Field )
				$this->name = "{$this->getParent()->name}_$name";
			else
				$this->name = $name;
			
			if ( $this->label === null )
			{
				if ( substr($this->name, 0, 2) != 'id' )
					$this->label = ucfirst ($this->name);
				else
					$this->label = ucfirst ( substr($this->name, 2) );
			}
		}
		
		$this->cssId = $this->name;
		
		$this->postSetParent();
	}
	
	function postSetParent() {
		$this->checkForcedValue();
	}
	
	/**
	 * This function must be overrided in order to set $databaseFields value
	 */
	function setDatabaseFields() {
		foreach( $this->fields as $field ) {
			$field->setDatabaseFields();
		}
	}
	
	function getParent()
	{
		return $this->_parent;
	}
	
	function setListable()
	{
		$this->listable = true;
	}
	
	function isListable()
	{
		return $this->listable;
	}
	
	/**
	 *  Shows the field. It can be overrided for custom functionality. 
	 *  If it is overrided, it has to have parent::render() AT THE END
	 */
	function render($data)
	{
		$ci =& get_instance();
		
		if ( $this->additionalJs )
		{
			echo '<script type="text/javascript">';
			echo "$( function() {";
			echo $this->additionalJs;
			echo "});";
			echo '</script>';
		}
		
		$viewName = strtolower( get_class($this) );
		$ci->load->view("fields/$viewName.php", $data);
	}
	
	/**
	 * Some fields knows how to validate themselves
	 */
	function validate($rules = 'required')
	{
		
	}
	
	/**
	 * Return true if this field doesn't change between languages, false otherwise.
	 * 
	 * @return boolean 
	 */
	function isLanguageAgnostic()
	{
		$parent = $this->getParent();
		
		if ( $parent::$i18n )
		{	
			if ( $parent::$i18n === true || !in_array($this->name, $parent::$i18n) ) //if it's files are different between languages
			{
				return false;
			}
		}
		return true;
	}
}
