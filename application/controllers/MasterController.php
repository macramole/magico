<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Use this class to add some common functionality to your site. Like a navigation menu or whatever you like.
 *  
 */
class MasterController extends MY_Controller {
	
	protected $_pageTitle = 'New website';
	
	public function show($additionalData = array())
	{
		/* Add some code here for example	.
		 * 
		 */
		
		parent::show($additionalData);
	}
}