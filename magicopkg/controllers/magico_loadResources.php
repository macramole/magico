<?php
// DEPRECATED
class magico_loadResources extends CI_Controller
{
	function loadJS()
	{
		if ( !AdminUser::isLogged() )
			exit;
		
		$name = implode('/',func_get_args());
		
		//header("content-type: application/javascript");
		@include( MAGICO_PATH . "js/$name");
	}
	
	function loadCSS()
	{
		if ( !AdminUser::isLogged() )
			exit;
		
		$name = implode('/',func_get_args());
		
		header("content-type: text/css");
		@include( MAGICO_PATH . "css/$name");
	}
}

?>
