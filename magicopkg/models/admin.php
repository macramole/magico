<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin extends MY_Model {
	
	/*** Datos básicos ***/
	public static $name = "Administrador";
	public static $table = "admins";
	public static $hayPaginaIndividual = false;
	
	function __construct($id = null)
	{	
		/*** Fields ***/
		$this->fields['user'] = new Textbox('Usuario');
		$this->fields['password'] = new Textbox('Contraseña','');
		$this->fields['email'] = new Textbox('Email');
		$this->fields['permisos'] = new SimpleChecklist('admins_permisos');
		
		/*** Extras ***/	
		global $CFG;
		$magico_nav = $CFG->item('magico_nav');
		
		foreach ( $magico_nav as $key => $item )
			$arrPermisos[$key] = $item['title'] ? $item['title'] : $key;
		
		$this->fields['permisos']->setValues($arrPermisos);
		//$this->fields['permisos']->defaultChecked = true;
		
		$this->fields['password']->isPassword = true;
		
		if ( !AdminUser::isRoot() || $id == 1 )
		{
			$this->fields['permisos']->disabled = true;
		}
		
		if ( !AdminUser::isRoot() )
		{
			$this->fields['user']->disabled = true;
		}
		
		$this->setListableFields(array('user'));
		
		parent::__construct($id);
		
		if ( $this->getOperation() == self::OPERATION_EDIT )
			$this->fields['password']->helptext = 'Dejar en blanco para no modificar la contraseña';
	}
	
	function save()
	{
		if ( $_POST['password'] )
			$_POST['password'] = md5($_POST['password']);
		else
			$this->fields['password']->disabled = true;
		
		parent::save();
	}
	
	function delete()
	{
		if ( $this->id != 1 && $this->id != AdminUser::getId() )
			parent::delete();
	}
	
	function setFieldsValues()
	{
		parent::setFieldsValues();
		$this->fields['password']->value = '';
	}
	
	function validate()
	{
		parent::validate();
		
		if ( !$this->fields['user']->disabled )
			$this->ci->form_validation->set_rules('user','','required');
		
		$this->ci->form_validation->set_rules('email','','valid_email|required');
		
		if ( !$this->fields['permisos']->disabled )
			$this->ci->form_validation->set_rules('permisos','','required');
		
		if ( $this->getOperation() == self::OPERATION_CREATE || ( $this->getOperation() == self::OPERATION_EDIT && $_POST['password'] ) )
		{
			$this->ci->form_validation->set_rules('password',' ','trim|min_length[6]|required');
		}
		
		if ( $_POST['user'] )
		{
			$user = $this->ci->db->get_where('admins', array('user' => $_POST['user']))->row_array();
			if ( $user && $user['id'] != $this->id )
				$this->ci->form_validation->set_error('existente', 'user');
		}
		
		if ( $_POST['email'] )
		{
			$user = $this->ci->db->get_where('admins', array('email' => $_POST['email']))->row_array();
			if ( $user && $user['id'] != $this->id )
				$this->ci->form_validation->set_error('existente', 'email');
		}
		
		
		if ($this->ci->form_validation->run() == true)
			return null;
		else
		{
			return $this->ci->form_validation->get_error_array();
		}
	}
}
