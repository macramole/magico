<?php
/*
 Mâgico
 http://www.parleboo.com
 Copyright 2012 Leandro Garber <leandrogarber@gmail.com>
 Attribution-NonCommercial-ShareAlike 3.0 Unported (CC BY-NC-SA 3.0)
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * Sirve para agregar campos que no se guardan en la base (son para validar, etc)
 */
class FooTextField extends Textbox {
	
	public $autoSave = true;
	
	/** Este campo se graba por si solo (autoSave = true) **/
	function save($table, $id)
	{
		
	}
	
	/** Este campo se borra por si solo (autoSave = true) **/
	function delete($table, $id)
	{
		
	}
	
	/** Este campo se setea el valor por si solo (autoSave = true) **/
	function setFieldValue($table, $id)
	{
		
	}
	
}