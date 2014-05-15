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
 * Deben existir los campos 'dirección', 'latitud' y 'longitud' así como 'idZona' y 'idSubzona' que se le establece el nombre desde el constructor (y son opcionales)
 * Integración para agregar zonas y subZonas (se les puede dar el nombre que se quiera, ej: Ciudades y Barrios) 
 * Agregar <script src="https://maps.googleapis.com/maps/api/js?sensor=false" type="text/javascript" charset="utf-8"></script> en master_page, el $LAB no parecería funcionar
 */
class GoogleMapField extends Field {
	
	public $safeHtml = false;
	public $autoSave = true;
	
	public $zonaFieldName, $subZonaFieldName;
	public $fields;
	public $value_latitud, $value_longitud;
    public $queryExtra;
	
	/**
	 * Constructor del field
	 * 
	 * @param string $label
     * @param string $queryExtra String extra que le pongo a la query
	 * @param Object $zonaObj El model de zona ej. Ciudad
	 * @param Object $subZonaObj El model de subzona ej. Barrio
	 * @param string $zonaFieldName El nombre del field para zona ej. idCiudad
	 * @param string $subZonaFieldName  El nombre del field para subzona ej. idBarrio
	 */
	function __construct($label, $queryExtra = null, $zonaObj = null, $subZonaObj = null, $zonaFieldName = null, $subZonaFieldName = null) {
		parent::__construct($label);
		
		$this->zonaFieldName = $zonaFieldName;
		$this->subZonaFieldName = $subZonaFieldName;
		
		$this->fields['fieldBuscar'] = new Textbox();
        
        if ( $zonaObj ) {
            $this->fields['fieldCiudad'] = new DatabaseSelect($zonaObj);
            $this->fields['fieldCiudad']->addNew = true;
        }
        
        if ( $subZonaObj )
            $this->fields['fieldBarrio'] = new DatabaseSelect($subZonaObj,array($this->zonaFieldName => DatabaseSelect::POST_PLACEHOLDER),true);
		
		$this->queryExtra = $queryExtra;
		
		if ( !$_POST['where'] && $subZonaObj )
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
        
        $updateData['direccion'] = $_POST[$this->fields['fieldBuscar']->name];
        $updateData['latitud'] = $_POST[$this->name . '_latitud'];
        $updateData['longitud'] = $_POST[$this->name . '_longitud'];
        
        if ( $this->fields['fieldBarrio'] )
            $updateData[$this->subZonaFieldName] = $_POST[$this->fields['fieldBarrio']->name];
        
        if ( $this->fields['fieldCiudad'] )
            $updateData[$this->zonaFieldName] = $_POST[$this->fields['fieldCiudad']->name];
        
		$ci->db->where(array('id' => $id));
		$ci->db->update($table, $updateData);
	}
	
	function delete($table, $id)
	{
		
	}
	
	function setFieldValue($table, $id, $row)
	{	
		$this->fields['fieldBuscar']->value = $row['direccion'];
        
        if ( $this->fields['fieldBarrio'] )
            $this->fields['fieldBarrio']->value = $row[$this->subZonaFieldName];
        
        if ( $this->fields['fieldCiudad'] )
		$this->fields['fieldCiudad']->value = $row[$this->zonaFieldName];
		
		
		$this->value_latitud = $row['latitud'];
		$this->value_longitud = $row['longitud'];
	}
	/*************************************/
	
	function render()
	{
		if ( $this->fields['fieldBarrio'] )
            $this->fields['fieldBarrio']->addNew = array($this->zonaFieldName => $this->fields['fieldCiudad']->name);
		
		$data = array();
		
		$data['name'] = $this->name;
		$data['fieldBuscar'] = $this->fields['fieldBuscar'];
		$data['fieldCiudad'] = $this->fields['fieldCiudad'];
		$data['fieldBarrio'] = $this->fields['fieldBarrio'];
		$data['latitud'] = $this->value_latitud;
		$data['longitud'] = $this->value_longitud;
        $data['queryExtra'] = $this->queryExtra;
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