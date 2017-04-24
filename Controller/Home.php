<?php

class HomeController extends \BH_Controller{
	public function __Init(){
	}

	public function Index(){
		//echo _password_hash('12341234');
		$this->_View();
	}
}
