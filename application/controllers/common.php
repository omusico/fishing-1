<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Common extends CI_Controller {
	function index(){
		echo '呵呵';
	}
	
	function build() {
		$this->load->dbforge();
		$this->dbforge->column_cache();
	}
}