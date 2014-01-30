<?php

/*
 Mâgico
 http://www.parleboo.com
 Copyright 2012 Leandro Garber <leandrogarber@gmail.com>
 Attribution-NonCommercial-ShareAlike 3.0 Unported (CC BY-NC-SA 3.0)
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * De esta clase heredan los fields
 */
abstract class Field {
	/**
	 * Name es el nombre en la base
	 * Label es lo que se muestra el usuario
	 */
	public $name, $label, $value, $helptext, $cssId;
	
	/**
	 * Automáticamente guarda los campos usando htmlentities
	 */
	public $safeHtml = true;
	
	/**
	 * Si aparece en el listado del backend. USO INTERNO
	 */
	public $listable = false;
	
	/**
	 * Si este campo necesita guardarse individualmente. Se necesitará que la clase implemente el método save($table, $id), el método delete($table, $id) y el setFieldValue($table, $id, $row);
	 * Por el momento los campos de este tipo no son traducibles
	 */
	public $autoSave = false;
	
	/*
	 * JS adicional para no tener que crear todo un control nuevo para cada caso
	 */
	public $additionalJs;
	
	/**
	 * Si un campo disabled no se mostrará en el abm y no se procesará para guardar sus datos
	 * 
	 * @var boolean
	 */
	public $disabled = false;
	
	/**
	 * Por ahora se utiliza cuando se construye el clean URL, si es foreign se fija el url clean en la tabla especifiacda
	 * 
	 * @var mixed False o el nombre de la tabla
	 */
	public $isForeignKey = false;
	
	private $_parent;
	
	function __construct($label = null, $helptext = '', $defaultValue = '')
	{
		$this->label = $label;
		$this->helptext = $helptext;
		$this->value = $defaultValue;
		
		//spl_autoload_register(array($this,'_autoIncludeFields'));
	}
	
	/*protected function _autoIncludeFields($name)
	{
		@include_once("application/libraries/admin/lib/fields/$name.php");
		$name = strtolower($name);
		@include_once("application/libraries/admin/models/$name.php");
	}*/
	
	
	/**
	 * Si al abm se le manda por get field=value pone ese valor 
	 */
	protected function checkForcedValue()
	{
		if ( $_GET[$this->name] )
			$this->value = $_GET[$this->name];
	}
	
	/**
	 * Esta función es llamada desde el model para establecer su parent y su nombre.
	 * También chequea si al abm se le pasó un parámetro para forzar su valor
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
		
		$this->checkForcedValue();
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
	 *  Muestra el control. Al final de la funcion overrideada debe tener parent::render(); TODO: Algunos fields no tienen esta función al final !!
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
	 * Algunos fields saben validarse a si mismos 
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
