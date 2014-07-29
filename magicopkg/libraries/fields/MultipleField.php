<?php
/*
 Mâgico
 http://www.parleboo.com
 Copyright 2012 Leandro Garber <leandrogarber@gmail.com>
 Attribution-NonCommercial-ShareAlike 3.0 Unported (CC BY-NC-SA 3.0)
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * A field that has multiplefields embeded in rows.
 * Use method addField to add these fields.
 * The user can add as many rows he wants. They will saved in a table (one to many)
 * 
 * Name of the field is the name of the table that will be used (must exist!)
 * Name of each field added via addField is the name of the column in this table
 * The table must also have id, id<ParentModelName> and weight
 * 
 * For now just basic fields are working
 */
class MultipleField extends Field {
	
	public $arrValues;
	public $safeHtml = false;
	public $autoSave = true;
	
	/**
	 * Adds a field. Be carefull not to use a name of field you've already used in the model.
	 * 
	 * @param type $name
	 * @param type $field
	 */
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
		
		if ( isset( $_POST[ $aField->name ] ) && count( $_POST[ $aField->name ] ) > 0 )
		{	
			$arrRows = array();
			
			for ($i = 0; $i < count($_POST[$aField->name]); $i++ )
			{
				$row = array();
				
				foreach ( $this->fields as $fieldName => $field )
				{	
					$row[$fieldName] = isset($_POST[$field->name][$i]) ? $_POST[$field->name][$i] : null;
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
		
		if ( !$this->arrValues )
			$this->arrValues = $ci->db->get_where($this->name, array('id' . get_class($this->getParent()) => $id))->result_array();
	}
	
	function getValues() {
		
		
		return $this->arrValues;
	}
	
	function setDatabaseFields() {
		parent::setDatabaseFields();
		
		$this->table = $this->name;
		
		$this->databaseFields += array(
			'id' . get_class($this->getParent()) => array(
				'type' => 'INT',
				'unsigned' => 'TRUE',
			),
			'weight' => array(
				'type' => 'INT'
			)
		);
		
		foreach( $this->fields as $field ) {
			$this->databaseFields += $field->databaseFields;
		}
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
		
		/*foreach ( $data['fields'] as $fieldName => $field ) {
			$field->name = $field->name . '[]';
		}*/
		
		$ci->load->view('fields/multiplefield_field.php', $data);
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
