<?php
/*
 Mâgico
 http://www.parleboo.com
 Copyright 2012 Leandro Garber <leandrogarber@gmail.com>
 Attribution-NonCommercial-ShareAlike 3.0 Unported (CC BY-NC-SA 3.0)
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SimpleChecklist extends SimpleSelect {
	
    public $relationTable = null; //Tabla many to many. Debe tener id, idNombreMY_ModelPadre, title
	public $defaultChecked = false; //En true, checkea todos por default 
	
	function __construct($relationTable, $label = null, $helptext = '')
	{
		parent::__construct($label, $helptext);
        $this->autoSave = true;
        $this->relationTable = $relationTable;
	}
	
    function save($table, $id)
    {
        $ci =& get_instance();
        
        $ci->db->delete( $this->relationTable, array( 'id' . get_class($this->getParent()) => $id ) );
        
        if ( count( $_POST[$this->name] ) )
        {
            foreach( $_POST[$this->name] as $value )
            {
                $data[] = array( 'title' => $value,
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
        
        $arrSelected = $ci->db->get_where( $this->relationTable, array( 'id' . get_class($this->getParent()) => $id ) )->result_array();
        
        foreach ( $this->arrValues as &$value )
        {
            $selected = false;
            foreach ( $arrSelected as $key => $selValue )
            {
                if ( $selValue['title'] == $value['id'] )
                {
                    $selected = true;
                    unset($arrSelected[$key]);
                    break;
                }
            }
            
            $value['selected'] = $selected;
        }
    }
    
    function render()
	{
		
		$data = array();
		
		if ( $this->defaultChecked && $this->getParent()->getOperation() == MY_Model::OPERATION_CREATE )
		{
			foreach ( $this->arrValues as &$value )
				$value['selected'] = true;
		}
		
		$data['name'] = $this->name;
		$data['arrValues'] = $this->arrValues;
		$data['helptext'] = $this->helptext;
		
		Field::render($data);
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
