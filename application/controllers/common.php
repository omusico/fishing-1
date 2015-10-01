<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Common extends CI_Controller {
    function index(){
    	$this->load->dbforge();
		$this->dbforge->column_cache();
        $this->load->view('test.html',['title'=>'ok!']);
    }
}