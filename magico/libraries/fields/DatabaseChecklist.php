<?php
/*
 Mâgico
 http://www.parleboo.com
 Copyright 2012 Leandro Garber <leandrogarber@gmail.com>
 Attribution-NonCommercial-ShareAlike 3.0 Unported (CC BY-NC-SA 3.0)
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class DatabaseChecklist extends Field {
	
	public $model = null; //Content asociado (UNA INSTANCIA, no el nombre)
    public $relationTable = null; //Tabla many to many. Debe tener id INT PK, id<RelatedMY_Model> INT, id<ParentMY_Model> INT
	public $addNew = true; //Opcion para agregado rápido.   
    public $arrValues = array();
	public $defaultChecked = false; //En true, checkea todos por default
	
	const FORCED_TITLE = '%s_forced_title';
	const FORCED_ID = '%s_forced_id';
	
	function __construct($model, $relationTable, $label = null, $helptext = '')
	{
		parent::__construct($label, $helptext);
        $this->autoSave = true;
		$this->model = $model;
        $this->relationTable = $relationTable;
		$this->isForeignKey = $model::$table; //Automaticamente sabe que es foreign key
		$this->getDbValues();
	}
	
	function getDbValues()
	{	
        $this->arrValues = $this->model->getList();
	}
    
    function save($table, $id)
    {
        $ci =& get_instance();
        
        $ci->db->delete( $this->relationTable, array( 'id' . get_class($this->getParent()) => $id ) );
        
        if ( count( $_POST[$this->name] ) )
        {
            foreach( $_POST[$this->name] as $value )
            {
                $data[] = array( 'id' . get_class($this->model) => $value,
                                 'id' . get_class($this->getParent()) => $id);
            }
            
            $ci->db->insert_batch( $this->relationTable, $data );
        }
    }
    
    function delete($table, $id) 
    {
        $ci =& get_instance();
        
        $ci->db->delete( $this->relationTable, array( 'id' . get_class($this->getParent()) => $id ) );
    }        
    
    function setFieldValue($table, $id, $row)
    {
        $ci =& get_instance();
        
        $idCheck = 'id' . get_class($this->model);
        
        $arrSelected = $ci->db->get_where( $this->relationTable, array( 'id' . get_class($this->getParent()) => $id ) )->result_array();
        
        foreach ( $this->arrValues as &$value )
        {
            $selected = false;
            foreach ( $arrSelected as $key => $selValue )
            {
                if ( $selValue[$idCheck] == $value['id'] )
                {
                    $selected = true;
                    unset($arrSelected[$key]);
                    break;
                }
            }
            
            $value['selected'] = $selected;
        }
    }
	
	function ajaxCallBack()
	{
        echo json_encode( $this->arrValues );
	}
    
	/**
	 * Override.
	 * Por ahora lo usa el ForeignMY_Model para forzar un elemento que aun no existe
	 */
	protected function checkForcedValue()
	{
		if ( $_GET[$this->name] )
			$this->value = $_GET[$this->name];
		else if ( $_GET[sprintf(self::FORCED_ID, $this->name)] && $_GET[sprintf(self::FORCED_TITLE, $this->name)] )
		{
			return array('id' => $_GET[sprintf(self::FORCED_ID, $this->name)], 'title' => $_GET[sprintf(self::FORCED_TITLE, $this->name)]);
		}
		
		return false;
	}
	
    function render()
	{
		$ci =& get_instance();
		$data = array();
		
		if ( $this->defaultChecked && $this->getParent()->getOperation() == MY_Model::OPERATION_CREATE )
		{
			foreach ( $this->arrValues as &$value )
				$value['selected'] = true;
		}
		
		if ( !$ci->adminuser->tienePermiso(get_class($this->model)) )
			$this->addNew = false;
		
		$data['name'] = $this->name;
		$data['arrValues'] = $this->arrValues;
		$data['helptext'] = $this->helptext;
		$data['addNew'] = $this->addNew;
		$data['model'] = get_class($this->model);
		$data['language'] = $ci->lang->has_multiple_languages() ? $ci->uri->segment(1) : '';
        
        if ( !$this->getParent() instanceof Field )
			$data['ajaxUrl'] = ( $data['language'] ? $data['language'] . '/' : '' ) . "abm/ajaxFieldCallBack/" . get_class($this->getParent()) . "/" . $this->name;
		else
			$data['ajaxUrl'] = ( $data['language'] ? $data['language'] . '/' : '' ) . "abm/ajaxFieldCallBack/" . get_class($this->getParent()->getParent()) . "/" . $this->getParent()->name . '/' . $this->name;
		
		parent::render($data);
	}
    
    public function validate($rules = 'required')
	{	
		if ( count($_POST[$this->name]) == 0 )
		{
			$ci =& get_instance();
			$ci->form_validation->set_error( lang('required'), $this->name );
		}
	}
}

?>
