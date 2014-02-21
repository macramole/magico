<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MY_Form_validation
 *
 * @author macramole
 */
class MY_Form_validation extends CI_Form_validation {
	
	/**
	 * Constructor
	 */
	public function __construct($rules = array())
	{
		$this->CI =& get_instance();

		// Validation rules can be stored in a config file.
		$this->_config_rules = $rules;

		// Automatically load the form helper
		$this->CI->load->helper('form');
		
		//Load the lang file (added by Pârleboo)
		$this->CI->lang->load('form_validation');
		
		// Set the character encoding in MB.
		if (function_exists('mb_internal_encoding'))
		{
			mb_internal_encoding($this->CI->config->item('charset'));
		}

		log_message('debug', "Form Validation Class Initialized");
	}
	
	/**
	 * Add a custom error (added by Pârleboo)
	 */
	function set_error($error = '', $field = 'custom_error')
    {
        if (empty($error))
        {
            return FALSE;
        }
        else
        {
            $this->_error_array[$field] = $error;
            return TRUE;
        }
    }
	
	function get_error_array()
	{
		return $this->_error_array;
	}
}
