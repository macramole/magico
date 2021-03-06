<?php
/*
 Mâgico
 http://www.parleboo.com
 Copyright 2012 Leandro Garber <leandrogarber@gmail.com>
 Attribution-NonCommercial-ShareAlike 3.0 Unported (CC BY-NC-SA 3.0)
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Clitools extends CI_Controller {

	function __construct() {
		parent::__construct();

		if ( !$this->input->is_cli_request() ) {
			die ("Sorry, CLI access only");
		}
	}

	function index() {
		$this->help();
	}

	function modelToDatabase($model) {
		$this->load->model($model, true);
		$this->$model->createTable();
	}

	function dropModelTables($model) {
		$this->load->model($model, true);
		$this->$model->dropTable();
	}

	function help() {
		echo "Available commands:\n\n";
		echo "modelToDatabase <model>\n";
		echo "dropModelTables <model>\n";
	}

	function test() {
		echo "Is working" . PHP_EOL;
	}
}
