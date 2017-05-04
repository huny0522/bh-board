<?php
use \BH_Application as App;
use \BH as BH;
class HomeController{
	public function __construct(){
	}

	public function Index(){
		BH::APP()->_View();
	}
}
