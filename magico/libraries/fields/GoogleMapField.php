<?php
/*
 Mâgico
 http://www.parleboo.com
 Copyright 2012 Leandro Garber <leandrogarber@gmail.com>
 Attribution-NonCommercial-ShareAlike 3.0 Unported (CC BY-NC-SA 3.0)
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Field para buscar direcciones en Google Maps. Primer field que usa a su vez fields.
 * 
 * Características:
 * 
 * Se ingresa la dirección, la zona y la subzona y aparece un mapa indicando el punto
 * Se guarda en la tabla el id de la zona, la subzona, la dirección, la latitud y la longitud
 * Deben existir los campos 'dirección', 'latitud' y 'longitud' así como idZona y idSubzona que se le establece el nombre desde el constructor
 * Integración para agregar zonas y subZonas (se les puede dar el nombre que se quiera, ej: Ciudades y Barrios) 
 */
class GoogleMapField extends Field {
	
	public $safeHtml = false;
	public $autoSave = true;
	
	public $zonaFieldName, $subZonaFieldName;
	public $fields;
	public $value_latitud, $value_longitud;
	
	/**
	 * Constructor del field
	 * 
	 * @param string $label
	 * @param Object $zonaObj El model de zona ej. Ciudad
	 * @param Object $subZonaObj El model de subzona ej. Barrio
	 * @param string $zonaFieldName El nombre del field para zona ej. idCiudad
	 * @param string $subZonaFieldName  El nombre del field para subzona ej. idBarrio
	 */
	function __construct($label, $zonaObj, $subZonaObj, $zonaFieldName, $subZonaFieldName) {
		parent::__construct($label);
		
		$this->zonaFieldName = $zonaFieldName;
		$this->subZonaFieldName = $subZonaFieldName;
		
		$this->fields['fieldBuscar'] = new Textbox();
		$this->fields['fieldCiudad'] = new DatabaseSelect($zonaObj);
		$this->fields['fieldBarrio'] = new DatabaseSelect($subZonaObj,array($this->zonaFieldName => DatabaseSelect::POST_PLACEHOLDER),true);
		
		$this->fields['fieldCiudad']->addNew = true;
		
		if ( !$_POST['where'] )
		{
			if ( count($this->fields['fieldCiudad']->arrValues) ) 
			{
				$this->fields['fieldBarrio']->where = array($this->zonaFieldName => $this->fields['fieldCiudad']->arrValues[0]['id']);
				$this->fields['fieldBarrio']->getDbValues();
			}
		}
	}
	
	/****** Campos para auto save ********/
	function save($table, $id)
	{
		$ci =& get_instance();
		$ci->db->where(array('id' => $id));
		$ci->db->update($table, array(	$this->subZonaFieldName => $_POST[$this->fields['fieldBarrio']->name], 
										$this->zonaFieldName => $_POST[$this->fields['fieldCiudad']->name], 
										'direccion' => $_POST[$this->fields['fieldBuscar']->name],
										'latitud' => $_POST[$this->name . '_latitud'],
										'longitud' => $_POST[$this->name . '_longitud']	));
	}
	
	function delete($table, $id)
	{
		
	}
	
	function setFieldValue($table, $id, $row)
	{	
		$this->fields['fieldBuscar']->value = $row['direccion'];
		$this->fields['fieldCiudad']->value = $row[$this->zonaFieldName];
		$this->fields['fieldBarrio']->value = $row[$this->subZonaFieldName];
		
		$this->value_latitud = $row['latitud'];
		$this->value_longitud = $row['longitud'];
	}
	/*************************************/
	
	function render()
	{
		$this->fields['fieldBarrio']->addNew = array($this->zonaFieldName => $this->fields['fieldCiudad']->name);
		
		$data = array();
		
		$data['name'] = $this->name;
		$data['fieldBuscar'] = $this->fields['fieldBuscar'];
		$data['fieldCiudad'] = $this->fields['fieldCiudad'];
		$data['fieldBarrio'] = $this->fields['fieldBarrio'];
		$data['latitud'] = $this->value_latitud;
		$data['longitud'] = $this->value_longitud;
		$data['ajaxUrl'] = "abm/ajaxFieldCallBack/" . get_class($this->getParent()) . "/" . $this->name . '/' . $this->fields['fieldBarrio']->name;
		
		$data['helptext'] = $this->helptext;
		
		parent::render($data);
	}
	
	function validate()
	{
		if ( !$_POST[$this->name . '_latitud'] || !$_POST[$this->name . '_longitud']  )
		{
			$ci =& get_instance();
			//$ci->form_validation->set_error( lang('required'), $this->name );
			$ci->form_validation->set_error( 'requerido, presionar buscar', $this->name );
		}
	}
}