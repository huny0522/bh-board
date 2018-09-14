<?php
namespace Controller;

use \BH_Application as App;
use \BH_Common as CM;
use \DB as DB;

class Home{

	public function __construct(){
	}

	public function __init(){
	}

	public function Index(){
		App::$Data['freeBoard'] = CM::GetBoardArticle('board', 'free_board', '', 5);
		App::$Data['notice'] = CM::GetBoardArticle('board', 'notice', '', 5);
		App::$Data['gallery'] = CM::GetBoardArticle('board', 'gallery', '', 5);
		App::View();
	}
}
