<?php
/*
 Mâgico
 http://www.parleboo.com
 Copyright 2012 Leandro Garber <leandrogarber@gmail.com>
 Attribution-NonCommercial-ShareAlike 3.0 Unported (CC BY-NC-SA 3.0)
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once('application/libraries/admin/fields/SimpleSelect.php');

class ForeignModel extends Field {
	
	/**
	 * String del nombre
	 * 
	 * @var String 
	 */
	public $model = null;
	
	/**
	 * Usa imagen en el listado
	 * 
	 * @var boolean 
	 */
	public $withImage = true;
	
	/**
	 * Nombre del foreign field que se usará de title
	 * 
	 * @var String 
	 */
	public $titleField = 'title';
	
	/**
	 * Es ordenable ? (Debe tener el campo weight INT)
	 * 
	 * @var boolean
	 */
	public $sortable = true;
	
	/**
	 * Puede editarse ?
	 *  
	 * @var type 
	 */
	public $noEdit = false;
	
	public $arrValues = array();
	public $autoSave = true;
	
	/**
	 * Constructor. El model debe tener los siguientes fields: title VARCHAR, id<parentName> INT, weight INT
	 * 
	 * @param string $model_name Es un string !
	 * @param type $label
	 * @param type $helptext
	 * @param type $defaultValue 
	 */
	function __construct($model_name, $label = null, $helptext = '', $defaultValue = '')
	{
		parent::__construct($label, $helptext, $defaultValue);
		$this->model = $model_name;
		$this->isForeignKey = $model_name::$table; //Automaticamente sabe que es foreign key		
	}
	
	function render()
	{
		$ci =& get_instance();
		$data = array();
		
		if ( !AdminUser::tienePermiso($this->model) )
			$this->noEdit = true;
		
		$data['name'] = $this->name;
		$data['cssId'] = $this->cssId;
		$data['value'] = $this->value;
		$data['helptext'] = $this->helptext;
		$data['arrValues'] = $this->arrValues;
		$data['model'] = $this->model;
		$data['titleField'] = $this->titleField;
		$data['withImage'] = $this->withImage;
		$data['sortable'] = $this->sortable;
		$data['language'] = $ci->lang->has_multiple_languages() ? $ci->lang->lang_abm() : '';
		$data['noEdit'] = $this->noEdit;
		
		$data['foreignSelect_id'] = sprintf(SimpleSelect::FORCED_ID, 'id' . get_class($this->getParent()));
		$data['foreignSelect_title'] = sprintf(SimpleSelect::FORCED_TITLE, 'id' . get_class($this->getParent()));
		$data['id'] = $this->getParent()->id ? $this->getParent()->id : $this->createFooId();
		$data['title'] = $this->getParent()->fields['title']->value ? $this->getParent()->fields['title']->value : 'Nuevo contenido';
		$data['title'] = urlencode($data['title']);
		
		if ( !$this->getParent() instanceof Field )
			$data['ajaxUrl'] = ( $data['language'] ? $data['language'] . '/' : '' ) . "abm/ajaxFieldCallBack/" . get_class($this->getParent()) . "/" . $this->name;
		else
			$data['ajaxUrl'] = ( $data['language'] ? $data['language'] . '/' : '' ) . "abm/ajaxFieldCallBack/" . get_class($this->getParent()->getParent()) . "/" . $this->getParent()->name . '/' . $this->name;
		
		Field::render($data);
	}
	
	/**
	 * Si el contenido todavía no está creado necesito un id provisorio
	 *  
	 */
	function createFooId()
	{
		return time();
	}
	
	function getDbValues($id)
	{	
		$ci =& get_instance();
		$MY_Model = $this->model;
		$idForeignName = 'id' . get_class($this->getParent());
		$tableForeignName = $MY_Model::$table;
		
		if ( $this->sortable )
			$ci->db->order_by('weight ASC');
		else
			$ci->db->order_by('id ASC');
		
		$where[ $idForeignName ] = $id;
		
		if ( $MY_Model::$i18n )
			$where['language'] = $ci->uri->segment(1);
		
		//Primero agarro los que le pertenecen
		$arrValues = $ci->db->get_where($tableForeignName , $where )->result_array();
		
		if ( $MY_Model::$i18n ) //puede haber rows sin traducir.. que aparezcan como para traducir !
		{
			$sqlTranslateables = "
				SELECT
					*
				FROM
					`$tableForeignName`
				WHERE 
					`$idForeignName` = '$id' AND 
					`language` != '$where[language]' AND 
					`id` NOT IN ( SELECT id FROM `$tableForeignName` WHERE `$idForeignName` = '$id' AND `language` = '$where[language]' )
			";

			$arrValuesTranslateables = $ci->db->query( $sqlTranslateables )->result_array();
			
			foreach( $arrValuesTranslateables as &$row )
			{
				$row['translate'] = true;
				$arrValues[] = $row;
			}
		}
				 
		if ( $this->withImage )
			magico_getImageToArray ($arrValues, $MY_Model::$table, 40, 40);
		
		$this->arrValues = $arrValues;
	}
	
	function save($table, $id)
	{
		if ( $_POST[ $this->name ] != $id )
		{
			$ci =& get_instance();
			$MY_Model = $this->model;
			
			$ci->db->where( array( 'id' . get_class($this->getParent()) => $_POST[ $this->name ] ) );
			$ci->db->update($MY_Model::$table, array( 'id' . get_class($this->getParent()) => $id ));
		}
			
	}
	
	function delete($table, $id) 
	{
		$ci =& get_instance();
		$MY_Model = $this->model;
		
		$rows = $ci->db->get_where($MY_Model::$table,  array( 'id' . get_class($this->getParent()) => $id ) )->result_array();
		
		if ( count($rows) )
		{
			foreach( $rows as $row )
			{
				$tmpModel = new $MY_Model( $row['id'], $row['language'] ? $row['language'] : null );
				$tmpModel->delete();
			}
		}
	}
	
	function setFieldValue($table, $id, $row)
	{
		$this->getDbValues($id);
	}
	
	function ajaxCallBack()
	{
		$ci =& get_instance();
		
		switch ( $_POST['action'] )
		{
			case 'list':
				$this->getDbValues($_POST['id']);
				
				$data['name'] = $this->name;
				$data['arrValues'] = $this->arrValues;
				$data['model'] = $this->model;
				$data['titleField'] = $this->titleField;
				$data['withImage'] = $this->withImage;
				$data['sortable'] = $this->sortable;
				$data['language'] = $ci->lang->has_multiple_languages() ? $ci->uri->segment(1) : '';

				$data['foreignSelect_id'] = sprintf(SimpleSelect::FORCED_ID, 'id' . get_class($this->getParent()));
				$data['foreignSelect_title'] = sprintf(SimpleSelect::FORCED_TITLE, 'id' . get_class($this->getParent()));
				$data['id'] = $_POST['id'];
				$data['title'] = $this->getParent()->fields['title']->value ? $this->getParent()->fields['title']->value : 'Nuevo ' . get_class($this->getParent());
				$data['title'] = urlencode($data['title']);
				$data['noEdit'] = $this->noEdit;
				
				if ( !$this->getParent() instanceof Field )
					$data['ajaxUrl'] = ( $data['language'] ? $data['language'] . '/' : '' ) . "abm/ajaxFieldCallBack/" . get_class($this->getParent()) . "/" . $this->name;
				else
					$data['ajaxUrl'] = ( $data['language'] ? $data['language'] . '/' : '' ) . "abm/ajaxFieldCallBack/" . get_class($this->getParent()->getParent()) . "/" . $this->getParent()->name . '/' . $this->name;
				
				$ci->load->view('fields/foreignmodel_items', $data);
				break;
			case 'delete':
				$MY_Model = $this->model;
				
				$where['id'] = $_POST['id'];
		
				if ( $MY_Model::$i18n )
					$where['language'] = $ci->uri->segment(1);
				
				$ci->db->delete($MY_Model::$table, $where);
				break;
		}
		
		
	}
}

?>
