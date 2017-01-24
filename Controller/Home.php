<?php

class HomeController extends BH_Controller{
	public function __Init(){

	}

	public function Index(){

		$dbGetList = new BH_DB_GetList(TABLE_POPUP);
		$dbGetList->AddWhere('begin_date <= \''.date('Y-m-d').'\'');
		$dbGetList->AddWhere('end_date >= \''.date('Y-m-d').'\'');
		$dbGetList->AddWhere('enabled=\'y\'');
		$this->_Value['Popup'] = $dbGetList->GetRows();

		$this->_View();
	}
}
