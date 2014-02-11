<?php

/*
 Mâgico
 http://www.parleboo.com
 Copyright 2012 Leandro Garber <leandrogarber@gmail.com>
 Attribution-NonCommercial-ShareAlike 3.0 Unported (CC BY-NC-SA 3.0)
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class TagField extends Field {
	
	public $model_name, $relationTable;
	public $value = array();
	
	/**
	 * Constructor
	 * 
	 * @param string $model_name El model asociado a la tabla con la lista de tags. Debe tener title y id. Es un string !
	 * @param string $relationTable La tabla muchos a muchos asociada
	 * @param string $label
	 * @param string $helptext
	 * @param string $defaultValue 
	 */
	function __construct($model_name, $relationTable, $label = null, $helptext = '', $defaultValue = '') {
		parent::__construct($label, $helptext, $defaultValue);
		
		$this->autoSave = true;
		$this->model_name = $model_name;
		$this->relationTable = $relationTable;
	}
	
	function getTags()
	{
		$ci =& get_instance();
		$model_name = $this->model_name; 
		
		$arrTags = $ci->db->get($model_name::$table)->result_array();
		$tags = array();
		
		if ( count($arrTags) )
		{
			foreach ( $arrTags as $tag )
			{
				$tags[] = $tag['title'];
			}
		}
		
		return $tags;
	}
	
	function save($table, $id)
	{
		$ci =& get_instance();
		$idParent = 'id' . get_class($this->getParent());
		$idTag = 'id' . $this->model_name;
		$contentTypeName = $this->model_name;
		$tagTable = $contentTypeName::$table;
		
		//Primero borro todos y después los agrego	
		$ci->db->delete($this->relationTable, array($idParent => $id));
		
		if ( is_array($_POST[$this->name]) && count($_POST[$this->name]) )
		{
			//Busco los tags que ya existen y agrego los que no
			$in = '';
			foreach ( $_POST[$this->name] as $tag )
				$in .= "'$tag',";
			$in = substr($in, 0, strlen($in) - 1);
			
			$sqlExistingTags = "
				SELECT
					*
				FROM
					$tagTable
				WHERE
					title IN ( $in )
			";
			
			
			
			$arrExistingTags = $ci->db->query($sqlExistingTags)->result_array();
			$arrTags = array();
			
			foreach( $arrExistingTags as $tag )
				$arrTags[$tag['id']] = $tag['title'];
			
			foreach( $_POST[$this->name] as $tag )
			{
				if ( !in_array($tag, $arrTags) )
				{
					$ci->db->insert($tagTable, array('title' => $tag));
					$arrTags[ $ci->db->insert_id() ] = $tag;
				}
			}
			
			$toInsert = array();
			
			foreach( $arrTags as $key => $tag )
				$toInsert[] = array( $idParent => $id, $idTag => $key );
			
			$ci->db->insert_batch($this->relationTable, $toInsert);
		}
	}
	
	function delete($table, $id) 
	{
		$ci =& get_instance();
		$idParent = 'id' . get_class($this->getParent());
		
		$ci->db->delete($this->relationTable, array($idParent => $id));
	}
	
	function setFieldValue($table, $id, $row)
	{
		$ci =& get_instance();
		
		$idParent = 'id' . get_class($this->getParent());
		$idTag = 'id' . $this->model_name;
		$contentTypeName = $this->model_name;
		$tagTable = $contentTypeName::$table;
		
		$sqlSavedTags = "
			SELECT
				title
			FROM
				$tagTable
			INNER JOIN
				{$this->relationTable} ON
				{$this->relationTable}.$idTag = $tagTable.id
			WHERE
				{$this->relationTable}.$idParent = $id
		";
				
		$arrSavedTags = $ci->db->query($sqlSavedTags)->result_array();
		$tags = array();
		
		if ( count($arrSavedTags) )
			foreach ( $arrSavedTags as $tag )
				$tags[] = $tag['title'];
		
		$this->value = $tags;
	}
	
	function render()
	{
		$ci =& get_instance();
		$data = array();
		
		$data['name'] = $this->name;
		$data['value'] = $this->value;
		$data['helptext'] = $this->helptext;
		$data['tags'] = $this->getTags();
		
		parent::render($data);
	}
}

?>
