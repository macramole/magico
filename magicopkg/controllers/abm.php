<?php 
/*
 Mâgico
 http://www.parleboo.com
 Copyright 2012 Leandro Garber <leandrogarber@gmail.com>
 Attribution-NonCommercial-ShareAlike 3.0 Unported (CC BY-NC-SA 3.0)
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Abm extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		
		header('Content-Type: text/html; charset=utf-8');
		
		if ( !AdminUser::isLogged() )
			exit;
		
		include_once(MAGICO_PATH_LIB . 'fields/Field.php');
		spl_autoload_register(array($this,'_autoIncludeFields'));
	}
	
	private function _autoIncludeFields($name)
	{
		@include_once(MAGICO_PATH_LIB . "fields/$name.php");
		//@include_once("application/models/$name.php");
	}
	
	/**
	 * Devuelve un objeto del tipo especificado. De esta manera se auto incluyen los content types y fields asociados
	 * 
	 * @param type $type
	 * @param type $id
	 * @return type 
	 */
	private function _returnModel($type, $id = null)
	{	
		$this->load->model($type, true);
		$this->$type->loadId($id);
		return $this->$type;
	}
	
	/*
	 * Para actualizar el orden por ajax
	 */
	public function updateOrder($type, $ids)
	{	
		$this->load->model($type);
		
		$arrIds = explode('_', $ids);
		unset($arrIds[count($arrIds) - 1]);
		
		foreach ($arrIds as $key => $id)
		{
			$this->db->where('id', $id);
			$this->db->update($type::$table, array('weight' => $key) );
		}
		
		echo 'ok';
	}
	
	//
	/**
	 * Los fields pueden hacer uso de ajax también (por POST los datos)
	 * 
	 * @param String $type Nombre del content type
	 * @param String $field Nombre del field
	 * @param String $childField Si ese field es hijo de un field hay que poner como $field el padre y este como hijo //Esto capas debería ser un array porque asi estoy soportando hasta un nivel
	 */
	public function ajaxFieldCallBack($type, $field, $childField = null)
	{		
		$model = $this->_returnModel($type);
		
		if ( !$childField )
			$model->fields[$field]->ajaxCallBack();
		else
			$model->fields[$field]->fields[$childField]->ajaxCallBack();
			//$model->fields[$field]->fields[str_replace ("{$field}_", '', $childField)]->ajaxCallBack();
		
	}
	
	//Llamar por ajax a una función de un model (por POST los datos)
	public function modelFunction($type, $functionName)
	{
		$model = $this->_returnModel($type);
		$model->$functionName();
	}
	
	
	public function listContent($type, $page = null)
	{
		$model = $this->_returnModel($type);
		
		if ($model::$i18n && !$this->lang->has_language())
			redirect( $this->lang->default_lang() . '/' . uri_string () );
		else
			$this->load->view( 'admin/abm_list', array('model' => $model, 'page' => $page) );
	}
	
	public function listToXLS($type)
	{
		$model = $this->_returnModel($type);
		$this->load->library('csvwriter');
		
		$data = array();
		
		$arrList = $model->getList();
		
		foreach ( $arrList as $key => $row )
		{
			unset($row['id']);
			
			if ( $key == 0 )
			{
				$columnNames = array_keys($row);
				$header = array();
				
				foreach( $columnNames as $column )
				{
					if ( isset( $model->fields[$column] ) )
						$header[] = $model->fields[$column]->label;
					else
						$header[] = $column;
				}
				
				$data[] = $header;
			}
			
			$data[] = $row;
		}

		header('Content-Type: text/csv');
		header('Content-Disposition: attachment;filename="listado_' . $type . '.csv"');
		header('Cache-Control: max-age=0');
		
		foreach ($data as $line) {
			$this->csvwriter->addLine($line);
		}
	}
	
	//Muestra el diálogo de creación de contenido
	public function create($model, $id = null)
	{	
		if ( $this->adminuser->tienePermiso($model) || ($model == 'Admin' && $this->adminuser->getId() == $id  ) )
		{
			$model = $this->_returnModel($model, $id);
			
			if ($model::$i18n && !$this->lang->has_language())
				redirect( $this->lang->default_lang() . '/' . uri_string () );
			else
				$this->load->view( 'admin/abm_view', array(
					'model' => $model, 
					'forceLanguage' => 
					isset($_GET['forceLanguage']) ? $_GET['forceLanguage'] : null ));
		}
		else
		{
			$this->load->view( 'admin/abm_view_denied');
		}
	}
	
	// Para que quede el edit en la url
	public function edit($type, $id)
	{
		$this->create($type, $id);
	}
	
	// Guarda el contenido (la función save probablamente deba ser overrideada)
	public function update($type, $id = null)
	{
		if ( $this->adminuser->tienePermiso($type) || ($type == 'Admin' && $this->adminuser->getId() == $id  ) )
		{
			$model = $this->_returnModel($type, $id);
		
			$arrValidate = $model->validate();

			if ( $arrValidate )
			{
				echo json_encode(array('errors' => $arrValidate));
			}
			else
			{
				$model->save();
				echo json_encode(array('returnUrl' => $model->getReturnURL(), 'id' => $model->id ));
			}
		}
		else
		{
			echo 'Acceso Denegado';
		}
		
	}
	
	/**
	 * Guarda un field en particular. Se usa para el CKEditor INLINE. $_POST['data'] tiene la data a modificar.
	 * 
	 * @param type $id
	 * @param type $type
	 * @param type $field 
	 */
	public function updateField($id, $type, $field)
	{
		$model = $this->_returnModel($type, $id);
		$model->saveField($field);
	}
	
	/**
	 * Elimina un contenido
	 * 
	 * @param String $type Nombre del tipo de contenido
	 * @param String $id Id del contenido
	 * @param boolean $force Si este contenido tiene contenidos asociados elimina todos
	 */
	public function delete($type, $id, $force = false)
	{
		$model = $this->_returnModel($type, $id);
		
		if ( !$model::$needsDeleteConfirmation || $force )  {
			$model->delete();
			echo json_encode( array('need_confirmation' => false ) );
		} else {
			echo json_encode( array('need_confirmation' => $model::$needsDeleteConfirmation ) );
		}
	}
	
	public function createModelTable($type) {
		$model = $this->_returnModel($type);
		$model->createTable();
		echo "Successful";
	}
	
	public function addMessage($message)
	{
		$_SESSION['messages'][] = urldecode($message) ; 
	}
	
}
