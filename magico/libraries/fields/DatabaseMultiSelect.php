<?php
/*
 Mâgico
 http://www.parleboo.com
 Copyright 2012 Leandro Garber <leandrogarber@gmail.com>
 Attribution-NonCommercial-ShareAlike 3.0 Unported (CC BY-NC-SA 3.0)
*/
include_once('application/libraries/admin/fields/DatabaseChecklist.php');

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Basicamente es igual a DatabaseChecklist sólo que muestra un select usando Chosen. TODO add new.
 */
class DatabaseMultiSelect extends DatabaseChecklist {
    
	public $addNew = false; //Opcion para agregado rápido. NO IMPLEMENTADO AUN   
	
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
		$data['forcedValue'] = $this->checkForcedValue();
		
		if ( !$data['forcedValue'] )
			$data['addNew'] = $this->addNew;
		else
		{
			$data['addNew'] = false;
			$data['helptext'] = '';
		}
        
        if ( !$this->getParent() instanceof Field )
			$data['ajaxUrl'] = "abm/ajaxFieldCallBack/" . get_class($this->getParent()) . "/" . $this->name;
		else
			$data['ajaxUrl'] = "abm/ajaxFieldCallBack/" . get_class($this->getParent()->getParent()) . "/" . $this->getParent()->name . '/' . $this->name;
		
		
		$ci->load->view('fields/databasemultiselect.php', $data);
	}
}

?>
