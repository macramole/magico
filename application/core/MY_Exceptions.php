<?php

class MY_Exceptions extends CI_Exceptions {
	/**
	 * General Error Page
	 *
	 * This function takes an error message as input
	 * (either as a string or an array) and displays
	 * it using the specified template.
	 *
	 * @access	private
	 * @param	string	the heading
	 * @param	string	the message
	 * @param	string	the template name
	 * @return	string
	 */
	function show_error($heading, $message, $template = 'error_general', $status_code = 500)
	{
		//if ( !$ci->input->is_cli_request() ) { COMMENTED BECAUSE IT THROWS ERROR ON INEXISTENT CONTROLLER, using php core function: 
		if ( php_sapi_name() == PHP_SAPI ) {
			return parent::show_error($heading, $message, $template, $status_code);
		} 
		
		$template = 'error_cli';
		$message = implode(PHP_EOL, ( ! is_array($message)) ? array($message) : $message);
		
		if (ob_get_level() > $this->ob_level + 1)
		{
			ob_end_flush();
		}
		ob_start();
		include( MAGICO_PATH.'errors/'.$template.'.php');
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}
}