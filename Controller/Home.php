<?php
use \BH_Application as App;
use \BH_Common as CF;

class HomeController{

	public function __construct(){
	}

	public function __init(){
	}

	public function Index(){
		App::$Instance->_View($this);
	}
}
