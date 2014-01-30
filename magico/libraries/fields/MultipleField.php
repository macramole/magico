<?php
/*
 Mâgico
 http://www.parleboo.com
 Copyright 2012 Leandro Garber <leandrogarber@gmail.com>
 Attribution-NonCommercial-ShareAlike 3.0 Unported (CC BY-NC-SA 3.0)
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Luego de crearlo hay que usar el método addField para agregarle campos.
 * El usuario tiene la posiblidad de agregar cuantos rows quiera y todos se guardaran en una tabla (de uno a muchos)
 * 
 * El nombre del campo es el nombre de la tabla
 * El nombre de cada field agregado es el nombre del campo en esta tabla
 * También la tabla debe tener id, id<NombreDelContent> y weight
 * 
 * Por el momento sólo funcionan algunos tipos básicos.
 *   
 */
class MultipleField extends Field {
	
	public $fields;
	public $arrValues;
	public $safeHtml = false;
	public $autoSave = true;
	
	function addField($name, $field)
	{
		$this->fields[$name] = $field;
	}
	
	/****** Campos para auto save ********/
	function save($table, $id)
	{
		$ci =& get_instance();
		$ci->db->delete( $this->name, array('id' . get_class($this->getParent()) => $id) );
		
		reset($this->fields);
		$aField = current($this->fields);
		
		if ( count( $_POST[ $aField->name ] ) > 0 )
		{	
			$arrRows = array();
			
			for ($i = 0; $i < count($_POST[$aField->name]); $i++ )
			{
				$row = array();
				
				foreach ( $this->fields as $fieldName => $field )
				{	
					$row[$fieldName] = $_POST[$field->name][$i];
				}
				
				$row['weight'] = $i;
				$row['id' . get_class($this->getParent())] = $id;
				
				$arrRows[] = $row;
			}
			
			$ci->db->insert_batch($this->name, $arrRows);
		}
	}
	
	function delete($table, $id)
	{
		$ci =& get_instance();
		$ci->db->delete( $this->name, array('id' . get_class($this->getParent()) => $id) );
	}
	
	function setFieldValue($table, $id, $row)
	{
		$ci =& get_instance();
		
		$ci->db->order_by('weight ASC');
		$this->arrValues = $ci->db->get_where($this->name, array('id' . get_class($this->getParent()) => $id))->result_array();
	}
	/*************************************/
	
	function render()
	{
		$data = array();
		
		
		$data['name'] = $this->name;
		$data['value'] = $this->value;
		$data['helptext'] = $this->helptext;
		$data['fields'] = $this->fields;
		$data['arrValues'] = $this->arrValues;
		$data['ajaxUrl'] = "abm/ajaxFieldCallBack/" . get_class($this->getParent()) . "/" . $this->name;
		
		parent::render($data);
	}
	
	function ajaxCallBack() //getRow
	{
		$ci =& get_instance();
		
		$data['fields'] = $this->fields;
		$data['rowNum'] = $_POST['cantFields'];
		
		foreach ( $data['fields'] as $fieldName => $field ) {
			$field->name = $field->name . '[]';
		}
		
		$ci->load->view('admin/fields/multiplefield_field.php', $data);
	}
}

//Helper function
function renderLabel($label)
{
	if ($label)
	{
		echo '<label>';
			echo $label . ':';
		echo '</label>';
	}

}

?>
