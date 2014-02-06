<?php
/*
 Mâgico
 http://www.parleboo.com
 Copyright 2012 Leandro Garber <leandrogarber@gmail.com>
 Attribution-NonCommercial-ShareAlike 3.0 Unported (CC BY-NC-SA 3.0)
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once('application/libraries/admin/fields/DatabaseSelect.php');

class CategorySelect extends DatabaseSelect{
	
	function __construct($model, $label = null, $helptext = '', $defaultValue = '')
	{
		parent::__construct($model,null,false,$label,$helptext,$defaultValue);
	}
	
	function getDbValues()
	{	
		$ci =& get_instance();

		$arrList = $ci->db->get( $this->model->table )->result_array();
		$arrListIds = array();
		
		foreach( $arrList as $item )
			$arrListIds[$item['id']] = $item;
		
		foreach( $arrListIds as $key => $item )
		{
			if ( $item['idPadre'] != 0 )
			{
				$arrListIds[$item['idPadre']]['children'][] = $item;
				unset($arrListIds[$key]);
			}
		}
		
		foreach( $arrListIds as $item )
		{
			$this->arrValues[] = $item;
			
			if ( count($item['children']) > 0 )
			{
				foreach( $item['children'] as $child )
				{
					$child['title'] = '-- ' . $child['title'];
					$this->arrValues[] = $child;
				}
			}
		}
	}
}

?>
