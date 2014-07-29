<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ExampleModel extends MY_Model {
	
	/*** Datos básicos ***/
	public static $name = "ExampleModel";
	public static $table = "exampleModel";
	public static $returnURL = '/';
	public static $hasPage = false;
	
	function __construct($id = null)
	{
		/*** Fields ***/
		$this->fields['title'] = new Textbox('Nombre');
		$this->fields['texto'] = new TextEditor();
		$this->fields['imagen'] = new FileUpload();
		
		/*** Extras ***/	
		//$this->fields['imagenes']->isImage();
		
		$this->setListableFields(array('title'));
		
		parent::__construct($id);
	}
	
	function validate()
	{
		parent::validate();
		
		$this->ci->form_validation->set_rules('title','','required');
		
		if ($this->ci->form_validation->run() == true)
			return null;
		else
		{
			return $this->ci->form_validation->get_error_array();
		}
	}
}
