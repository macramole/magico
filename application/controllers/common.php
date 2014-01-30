<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once('MasterController.php');

class Common extends MasterController
{	
	public function index()
	{
		$this->addContentPage('home');
		$this->show();
	}
}
