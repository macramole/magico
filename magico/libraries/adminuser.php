<?php 
/*
 Mâgico
 http://www.parleboo.com
 Copyright 2012 Leandro Garber <leandrogarber@gmail.com>
 Attribution-NonCommercial-ShareAlike 3.0 Unported (CC BY-NC-SA 3.0)
*/

if ( ! defined ( 'BASEPATH' ) ) exit ( 'No direct script access allowed.' );

class AdminUser {
    
	const LOGGED_IN = 1;
	const NOT_LOGGED_IN = 2; 
	const AUTOLOGIN_COOKIE_NAME = 'magico_admin';
	private $ci;
	
	function __construct()
	{
		$this->ci =& get_instance();
	}
	
	/**
	 * Logea al usuario a Mâgico
	 * 
	 * @param String $user
	 * @param String $pass
	 * @param boolean $remember Crea una cookie que después se checkea desde el Master Controller para auto logearse
	 * @param boolean $needMd5 Si el contraseña ya viene procesada con md5 asignar false
	 * @return int Constantes de esta clase LOGGED_IN o NOT_LOGGED_IN más adelante quizás haya otras 
	 */
	function login($user, $pass, $remember, $needMd5 = true)
	{
		if ( $needMd5 )
			$pass = md5($pass);
		
		$sql = "SELECT * FROM admins WHERE user=? AND password=? LIMIT 1";
		$user = $this->ci->db->query($sql,array($user,$pass))->row_array();
		
		if ($user)
		{
			$_SESSION['admin']['id'] = $user['id'];
			
			if ( !self::isRoot() ) //El root tiene permitido todo
			{
				// Asigno los permisos
				$arrPermisos = $this->ci->db->get_where('admins_permisos', array('idAdmin' => $user['id']))->result_array();
				$_SESSION['admin']['permisos'] = array();

				foreach ( $arrPermisos as $permiso )
					$_SESSION['admin']['permisos'][$permiso['title']] = 1;
			}
			
			
			if ($remember)
				setcookie(self::AUTOLOGIN_COOKIE_NAME, base64_encode($user->user.':'.$pass), time()+60*60*24*30, '/'); //30 días
				
			return self::LOGGED_IN;
		}
		else
		{
			return self::NOT_LOGGED_IN;
		}
	}
	
	function getId()
	{
		if ( !self::isLogged() )
			return false;
		
		return $_SESSION['admin']['id'];
	}
	
	function tienePermiso($permiso)
	{
		if ( self::isRoot() )
			return true;
		
		if ( !self::isLogged() )
			return false;
		
		return isset( $_SESSION['admin']['permisos'][$permiso] );
	}
	
	function forceLogin()
	{
		$_SESSION['admin'] = 1;
	}
	
	function cookieCheck()
	{
		if ( $_COOKIE[self::AUTOLOGIN_COOKIE_NAME] )
		{
			$autoLogin = base64_decode($_COOKIE[self::AUTOLOGIN_COOKIE_NAME]);
			list($user, $pass) = explode(':',$autoLogin);
			
			$this->login($user, $pass, false, false);
		}
		
		return self::NOT_LOGGED_IN;
	}
	
	function logout()
	{
		unset($_SESSION['admin']);
		setcookie(self::AUTOLOGIN_COOKIE_NAME,'',time() - 3600, '/');
	}
	
	function isLogged()
	{
		return isset($_SESSION['admin']);
	}
	
	//El id = 1 tiene permitido todo
	function isRoot()
	{
		return ( $_SESSION['admin']['id'] == 1 );
	}
}
?>
