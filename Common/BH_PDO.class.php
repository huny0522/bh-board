<?php
/**
 * Bang Hun.
 * 16.07.10
 */

//namespace BH;

class DB{
	const DefaultConnName = 'MY';
	const BIND_NAME = ':BH_PARAMETER';
	/**
	 * @var self
	 */
	private static $Instance;

	/* @var \PDO[] */
	private static $Conn = array();
	private static $ConnName = '';
	private static $ConnectionInfo = array();

	public static $bindNum = 0;

	private function __construct(){
		require _COMMONDIR.'/db.info.php';
	}

	public function __destruct(){
		foreach(self::$Conn as $k => &$v) $v = null;
	}

	public static function &SQL($connName = self::DefaultConnName){
		self::$ConnName = $connName;
		if (!isset(self::$Instance)) self::$Instance = new self();

		if(!isset(self::$Conn[self::$ConnName])){
			if(isset(self::$ConnectionInfo[self::$ConnName])){
				try {
					self::$Conn[self::$ConnName] = new PDO('mysql:host='.self::$ConnectionInfo[self::$ConnName]['hostName'].';dbname='.self::$ConnectionInfo[self::$ConnName]['dbName'], self::$ConnectionInfo[self::$ConnName]['userName'], self::$ConnectionInfo[self::$ConnName]['userPassword']);
				}
				catch(PDOException $e) {
					echo $e->getMessage();
					exit;
				}

				self::$Conn[self::$ConnName]->exec("set names utf8");
			}
			else{ echo('NOT_DEFINE_DB'); exit; }
		}
		return self::$Instance;
	}

	public static function &PDO($connName = self::DefaultConnName){
		self::SQL($connName);
		return self::$Conn[self::$ConnName];
	}

	public function TableExists($table){
		$exists = self::NumRows('SHOW TABLES LIKE \'' . $table . '\'');
		if($exists) return true;
		else return false;
	}

	public function NumRows($qry){
		if(is_string($qry)) $qry = self::Query($qry);
		if($qry === false) return false;

		return $qry->rowCount();
	}

	public function Free($qry){
		if(is_bool($qry)) return;
		$qry->closeCursor();
	}

	public function Query($str, $dieIs = true){
		$res = self::StrToPDO(is_array($str) ? $str : func_get_args());

		$qry = self::$Conn[self::$ConnName]->prepare($res[0]);
		foreach($res[1] as $k => $v){
			$qry->bindParam($k, $v[0], $v[1]);
		}
		if(_DEVELOPERIS === true) $qry->execute() or ($dieIs ? die('ERROR SQL : '.$res[0]) : false);
		else $qry->execute() or ($dieIs ? die('ERROR') : false);
		return $qry;
	}

	public function CCQuery($table, $str){
		if(is_array($str)) $args = $str;
		else{
			$args = func_get_args();
			array_shift($args);
		}

		if(strpos($args[0], '%t') === false) die('ERROR SQL(CC)'.(_DEVELOPERIS === true ? ' : '.$args[0] : ''));
		$args[0] = str_replace('%t', $table, $args[0]);
		$res = self::StrToPDO($args);

		$qry = self::$Conn[self::$ConnName]->prepare($res[0]);
		foreach($res[1] as $k => $v){
			$qry->bindParam($k, $v[0], $v[1]);
		}

		if(_DEVELOPERIS === true) $qry->execute() or die('ERROR SQL : '.$res[0]);
		else $qry->execute() or die('ERROR');
		return $qry;
	}

	public function Fetch($qry){
		if(!isset($qry) || $qry === false || empty($qry)){
			if(_DEVELOPERIS === true) echo 'FETCH ASSOC MESSAGE(DEBUG ON) : <b>query is empty( or null, false).</b><br>';
			return false;
		}
		$string_is = false;
		if(is_string($qry) || is_array($qry)){
			$qry = self::Query(is_array($qry) ? $qry : func_get_args());
			if($qry === false) return false;
			$string_is = true;
		}

		$r = $qry->fetch(PDO::FETCH_ASSOC);
		if($string_is) self::Free($qry);

		return $r;
	}

	public static function &GetConn(){
		return self::$Conn[self::$ConnName];
	}

	public static function &GetQryObj($table){
		$instance = new BH_DB_Get();
		call_user_func_array(array($instance, 'AddTable'), func_get_args());
		return $instance;
	}

	public static function &GetListQryObj($table){
		$instance = new BH_DB_GetList();
		call_user_func_array(array($instance, 'AddTable'), func_get_args());
		return $instance;
	}

	public static function &GetListPageQryObj($table){
		$instance = new BH_DB_GetListWithPage();
		call_user_func_array(array($instance, 'AddTable'), func_get_args());
		return $instance;
	}

	public static function &UpdateQryObj($table){
		$instance = new BH_DB_Update();
		call_user_func_array(array($instance, 'AddTable'), func_get_args());
		return $instance;
	}

	public static function &InsertQryObj($table){
		$instance = new BH_DB_Insert();
		call_user_func_array(array($instance, 'AddTable'), func_get_args());
		return $instance;
	}

	public static function &DeleteQryObj($table){
		$instance = new BH_DB_Delete();
		call_user_func_array(array($instance, 'AddTable'), func_get_args());
		return $instance;
	}

	public static function StrToPDO($args){
		$validateOk = new \BH_Result();
		$validateOk->result = true;

		if(!is_array($args)) $args = func_get_args();

		$bindParam = array();

		$n = sizeof($args);
		if(!$n) return false;
		if($n == 1) return array($args[0], $bindParam);
		else{
			$p = -1;
			$w = $args[0];
			for($i = 1; $i < $n; $i++){
				$p = strpos($w, '%', $p+1);
				$find = false;
				while(!$find && $p !== false && $p < strlen($w)){
					$t = $w[$p+1];
					if($t === 's'){
						if(is_array($args[$i])){
							$bindNames = array();
							foreach($args[$i] as $row){
								$bindParam[self::BIND_NAME.self::$bindNum.'X'] = array($row, \PDO::PARAM_STR);
								$bindNames[] = self::BIND_NAME.self::$bindNum.'X';
								self::$bindNum++;
							}
							$t = implode(',', $bindNames);
						}else{
							$t = self::BIND_NAME.self::$bindNum.'X';
							$bindParam[self::BIND_NAME.self::$bindNum.'X'] = array($args[$i], \PDO::PARAM_STR);
							self::$bindNum++;
						}
						$w = substr_replace($w, $t, $p, 2);
						$p += strlen($t);
						$find = true;
					}
					else if($t === 'f'){
						$res = ValidateFloat($args[$i]);
						if(!$res->result) $validateOk = $res;
						$t = is_array($args[$i]) ? implode(',', $args[$i]) : $args[$i];
						$w = substr_replace($w, $t, $p, 2);
						$p += strlen($t);
						$find = true;
					}
					else if($t === 'd'){
						$res = ValidateInt($args[$i]);
						if(!$res->result) $validateOk = $res;
						$t = is_array($args[$i]) ? implode(',', $args[$i]) : $args[$i];
						$w = substr_replace($w, $t, $p, 2);
						$p += strlen($t);
						$find = true;
					}
					else if($t === '1'){
						$t = is_array($args[$i]) ? implode(',', $args[$i]) : $args[$i];
						$w = substr_replace($w, $t, $p, 2);
						$p += strlen($t);
						$find = true;
					}
					else{
						$p = strpos($w, '%', $p+1);
					}
				}
			}
			$w = str_replace(array('%\s', '%\f', '%\d', '%\1', '%\t'), array('%s', '%f', '%d', '%1', '%t'), $w);
			if($validateOk->result) return array($w, $bindParam);
			else URLReplace(-1, $validateOk->message.(_DEVELOPERIS === true ? '['.$w.']' : ''));
		}
	}
}

class BH_DB_Get{
	public $table = '';
	public $test = false;
	public $sort = '';
	public $group = '';

	protected $query = null;
	protected $having = array();
	protected $where = array();
	protected $key = array();
	protected $connName = '';

	protected $bindParam = array();

	public function  __construct($table = ''){
		if($table !== '') $this->table = $this->StrToPDO(func_get_args());
		$this->connName = DB::DefaultConnName;
	}

	public function __destruct(){
		if($this->query) DB::Sql($this->connName)->Free($this->query);
	}

	public function StrToPDO($args){
		$res = DB::StrToPDO(is_array($args) ? $args : func_get_args());
		if(sizeof($res[1])) $this->bindParam = $this->bindParam + $res[1];
		return $res[0];
	}

	public function &SetConnName($str){
		$this->connName = $str;
		return $this;
	}

	public function &AddTable($str){
		$w = $this->StrToPDO(func_get_args());
		if($w !== false) $this->table .= ' '.$w;
		return $this;
	}

	public function &AddWhere($str){
		$w = $this->StrToPDO(func_get_args());
		if($w !== false) $this->where[] = '('.$w.')';
		return $this;
	}

	public function &AddHaving($str){
		$w = $this->StrToPDO(func_get_args());
		if($w !== false) $this->having[] = '('.$w.')';
		return $this;
	}

	public function &SetKey($str){
		$args = func_get_args();
		foreach($args as $k => $keys){
			if(is_string($keys)){
				if($k) $this->key[] = $keys;
				else $this->key = array($keys);
			}
			else if(is_array($keys)){
				if($k){ foreach($keys as $row) $this->key[] = $row; }
				else $this->key = $keys;
			}
		}
		return $this;
	}

	public function &AddKey($str){
		$args = func_get_args();
		foreach($args as $keys){
			if(is_string($keys)) $this->key[] = $keys;
			else if(is_array($keys)){ foreach($keys as $row) $this->key[] = $row;}
		}
		return $this;
	}

	public function &SetGroup($str){
		$this->group = $str;
		return $this;
	}

	public function &SetSort($str){
		$this->sort = $str;
		return $this;
	}

	public function Get(){
		$where = '';
		if(isset($this->where) && is_array($this->where) && sizeof($this->where)) $where = ' WHERE ' . implode(' AND ', $this->where);
		$having = '';
		if(isset($this->having) && is_array($this->having) && sizeof($this->having)) $having = ' HAVING ' . implode(' AND ', $this->having);

		if(isset($this->key) && is_array($this->key) && sizeof($this->key)) $key = implode(',', $this->key);
		else $key = '*';


		$sql = 'SELECT '.$key.' FROM '.$this->table.' '.$where;
		if($this->group) $sql .= ' GROUP BY ' . $this->group;
		$sql .= $having;
		if($this->sort) $sql .= ' ORDER BY ' . $this->sort;
		$sql .= ' LIMIT 1';
		if($this->test && _DEVELOPERIS === true){
			foreach($this->bindParam as $k => $v) $sql = str_replace($k, '\''.$v[0].'\'', $sql);
			echo $sql;
			exit;
		}

		$sql = $sql2 = trim($sql);
		foreach($this->bindParam as $k => $v) $sql2 .= '['.$v[0].']';

		$this->query = DB::PDO($this->connName)->prepare($sql);
		foreach($this->bindParam as $k => $v) $this->query->bindParam($k, $v[0], $v[1]);

		if($this->query->execute()){
			$row = $this->query->fetch(PDO::FETCH_ASSOC);
			if($row){
				$this->query->closeCursor();
				return $row;
			}
		}
		return false;
	}

	public function &SetTest($bool = false){
		$this->test = $bool;
		return $this;
	}
}

class BH_DB_GetList extends BH_DB_Get{
	public $limit = '';
	protected $pointer = -1;

	public $result = false;
	public $data = array();
	private $RunIs = false;

	public function DrawRows(){
		if(!$this->RunIs) $this->Run();
		while($row = $this->Get()){
			$this->data[]= $row;
		}
	}

	public function &GetRows(){
		if(!$this->RunIs) $this->Run();

		$this->DrawRows();
		return $this->data;
	}

	public function &SetLimit($str){
		$this->limit = $str;
		return $this;
	}

	function &Run(){
		$this->RunIs = true;

		$where = '';
		if(isset($this->where) && is_array($this->where) && sizeof($this->where)){
			$where = ' WHERE ' . implode(' AND ', $this->where);
		}

		$having = '';
		if(isset($this->having) && is_array($this->having) && sizeof($this->having)){
			$having = ' HAVING ' . implode(' AND ', $this->having);
		}

		if(isset($this->key) && is_array($this->key) && sizeof($this->key)){
			$key = implode(',', $this->key);
		}
		else{
			$key = '*';
		}


		$sql = 'SELECT '.$key.' FROM '.$this->table.' '.$where;

		if($this->group) $sql .= ' GROUP BY ' . $this->group;
		$sql .= $having;
		if($this->sort) $sql .= ' ORDER BY ' . $this->sort;
		if($this->limit) $sql .= ' LIMIT ' . $this->limit;

		if($this->test && _DEVELOPERIS === true){
			foreach($this->bindParam as $k => $v) $sql = str_replace($k, '\''.$v[0].'\'', $sql);
			echo $sql;
			exit;
		}

		$sql = $sql2 = trim($sql);
		foreach($this->bindParam as $k => $v) $sql2 .= '['.$v[0].']';

		$this->query = DB::PDO($this->connName)->prepare($sql);
		foreach($this->bindParam as $k => $v) $this->query->bindParam($k, $v[0], $v[1]);

		if($this->query->execute()) $this->result = true;
		else $this->result = false;

		return $this;
	}

	public function Get(){
		if(!$this->RunIs) $this->Run();
		$res = $this->query ? $this->query->fetch(PDO::FETCH_ASSOC) : false;
		return $res;
	}
}

class BH_DB_GetListWithPage extends BH_DB_Get{
	public $articleCount = 10;
	public $limit = '';
	public $page = 1;
	public $pageCount = 10;
	public $pageUrl = '';
	public $CountKey = '';
	public $SubCountKey = array();
	private $RunIs = false;

	// Result
	public $result = false;
	public $countResult = false;
	public $data = array();
	public $totalRecord = '';
	public $beginNum = '';
	public $pageHtml = '';

	public function &SetLimit($str){
		$this->limit = $str;
		return $this;
	}

	public function &SetPage($str){
		$this->page = $str;
		return $this;
	}

	public function &SetPageUrl($str){
		$this->pageUrl = $str;
		return $this;
	}

	public function &SetPageCount($str){
		$this->pageCount = (int)$str;
		return $this;
	}

	public function &SetArticleCount($str){
		$this->articleCount = (int)$str;
		return $this;
	}

	public function &SetSubCountKey($asName, $str){
		$this->SubCountKey[$asName]= $str;
		return $this;
	}

	public function &SetCountKey($str){
		$this->CountKey = $str;
		return $this;
	}

	public function DrawRows(){
		if(!$this->RunIs) $this->Run();
		while($row = $this->Get()){
			$this->data[]= $row;
		}
	}

	public function &GetRows(){
		if(!$this->RunIs) $this->Run();
		$this->DrawRows();
		return $this->data;
	}

	public function &Run(){
		$this->RunIs = true;
		if($this->page < 1) $this->page = 1;
		$nowPage = $this->page - 1;

		$beginPage = $nowPage * $this->articleCount;

		$where = '';
		if(isset($this->where) && is_array($this->where) && sizeof($this->where)){
			$where = ' WHERE ' . implode(' AND ', $this->where);
		}

		$having = '';
		if(isset($this->having) && is_array($this->having) && sizeof($this->having)){
			$having = ' HAVING ' . implode(' AND ', $this->having);
		}

		if(isset($this->key) && is_array($this->key) && sizeof($this->key)){
			$key = implode(',', $this->key);
		}
		else{
			$key = '*';
		}

		$subCnt_sql = '';
		$subCnt_sql2 = '';
		if(sizeof($this->SubCountKey)) foreach($this->SubCountKey as $k=>$v){
			$subCnt_sql .= ', '.$v.' as '.$k;
			$subCnt_sql2 .= ', COUNT('.$k.') as '.$k;
		}

		$sql_cnt = 'SELECT COUNT('.($this->CountKey ? $this->CountKey : '*').') as cnt'.$subCnt_sql.' FROM '.$this->table.' '.$where;
		$sql = 'SELECT '.$key.' FROM '.$this->table.' '.$where;
		if($this->group){
			$sql .= ' GROUP BY ' . $this->group;
			$sql_cnt .= ' GROUP BY ' . $this->group;
		}

		$sql_cnt .= $having;
		$sql .= $having;

		if($this->sort)
			$sql .= ' ORDER BY ' . $this->sort;

		if($this->limit)
			$sql .= ' LIMIT ' . $this->limit;
		else if($this->articleCount)
			$sql .= ' LIMIT '.$beginPage.', ' . $this->articleCount;

		if($this->test && _DEVELOPERIS === true){
			foreach($this->bindParam as $k => $v) $sql = str_replace($k, '\''.$v[0].'\'', $sql);
			echo $sql;
			exit;
		}

		$sql = $sql2 = trim($sql);
		foreach($this->bindParam as $v) $sql2 .= '['.$v[0].']';

		$qry = DB::PDO($this->connName)->prepare($this->group ? 'SELECT COUNT(*) as cnt'.$subCnt_sql2.' FROM ('.$sql_cnt.') AS x' : $sql_cnt);
		foreach($this->bindParam as $k => $v) $qry->bindParam($k, $v[0], $v[1]);
		if($qry->execute()){
			$this->countResult = $qry->fetch(PDO::FETCH_ASSOC);
			$totalRecord = $this->countResult['cnt']; //total값 구함
		}
		else $totalRecord = 0;

		$this->totalRecord = $totalRecord;
		$this->beginNum = $totalRecord - ($nowPage * $this->articleCount);

		$this->query = DB::PDO($this->connName)->prepare($sql);
		foreach($this->bindParam as $k => $v) $this->query->bindParam($k, $v[0], $v[1]);

		if($this->query->execute()){
			$pagedata['articleCount'] = $this->articleCount;
			$pagedata['pageCount'] = $this->pageCount;
			$pagedata['page'] = $this->page ? $this->page : 1;
			$pagedata['pageUrl'] = $this->pageUrl;
			$pagedata['totalRecord'] = $totalRecord;

			$this->pageHtml = $this->SqlGetPage($pagedata);

			$this->result = true;
		}
		else{
			$this->result = false;
		}
		return $this;
	}

	public function GetCountResult(){
		if(!$this->RunIs) $this->Run();
		return $this->countResult;
	}

	public function GetTotalRecord(){
		if(!$this->RunIs) $this->Run();
		return $this->totalRecord;
	}

	public function GetBeginNum(){
		if(!$this->RunIs) $this->Run();
		return $this->beginNum;
	}

	public function &GetPageHtml(){
		if(!$this->RunIs) $this->Run();
		return $this->pageHtml;
	}

	public function Get(){
		if(!$this->RunIs) $this->Run();

		$res = $this->query ? $this->query->fetch(PDO::FETCH_ASSOC) : false;
		return $res;
	}

	/**
	 * @param $pageParams
	 * @return string
	 */
	public static function SqlGetPage($pageParams){
		$linkfirst =(strpos($pageParams['pageUrl'], '?') === false) ? '?page=' : '&page=';

		if(!$pageParams['articleCount']) $pageParams['articleCount'] = 10;

		// 전체페이지
		if(!isset($pageParams['img']))
			$pageParams['img'] = array('first' => 'First', 'prev' => 'Prev', 'back' => 'Back', 'forw' => 'Forward', 'next' => 'Next', 'last' => 'Last');
		if(!$pageParams['totalRecord'])
			return '';
		$nowPage = $pageParams['page'] - 1;

		$pageAll = ceil($pageParams['totalRecord'] / $pageParams['articleCount']);

		// 뷰첫페이지
		$pageFirst = $nowPage - ($nowPage % $pageParams['pageCount']) + 1;
		// 뷰라스트페이지
		$pageLast = $pageFirst + $pageParams['pageCount'] - 1;
		if($pageLast > $pageAll)
			$pageLast = $pageAll;

		// 첫페이지
		$tag = $pageParams['page'] > 1 ? 'a' : 'span';
		$pageHTML = ' <'.$tag.' class="first" href="' . $pageParams['pageUrl'] . $linkfirst. '1" data-page="1">' . $pageParams['img']['first'] . '</'.$tag.'> ';


		// 이전 페이지목록
		$tag = $pageFirst > 1 ? 'a' : 'span';
		$pageHTML .= ' <'.$tag.' class="prev" href="' . $pageParams['pageUrl'] . $linkfirst.  ($pageFirst - 1) . '" data-page="' . ($pageFirst - 1) . '">' . $pageParams['img']['prev'] . '</'.$tag.'> ';

		// 이전 페이지
		$tag = $pageParams['page'] > 1 ? 'a' : 'span';
		$pageHTML .= ' <'.$tag.' class="prevp" href="' . $pageParams['pageUrl'] . $linkfirst. ($pageParams['page'] - 1) . '" data-page="' . ($pageParams['page'] - 1) . '">' . $pageParams['img']['back'] . '</'.$tag.'> ';

		for($i = $pageFirst; $i <= $pageLast; $i++){
			$pageHTML .= ($i == $pageParams['page']) ? ' <strong>' . $i . '</strong> ' : ' <a href="' . $pageParams['pageUrl'] . $linkfirst. $i . '" data-page="' . $i . '">' . $i . '</a> ';
		}

		// 다음 페이지
		$tag = $pageParams['page'] < $pageAll ? 'a' : 'span';
		$pageHTML .= ' <'.$tag.' class="nextp" href="' . $pageParams['pageUrl'] . $linkfirst. ($pageParams['page'] + 1) . '" data-page="' . ($pageParams['page'] + 1) . '">' . $pageParams['img']['forw'] . '</'.$tag.'> ';

		// 다음 페이지목록
		$tag = $pageLast < $pageAll ? 'a' : 'span';
		$pageHTML .= ' <'.$tag.' class="next" href="' . $pageParams['pageUrl'] . $linkfirst. ($pageLast + 1) . '" data-page="' . ($pageLast + 1) . '">' . $pageParams['img']['next'] . '</'.$tag.'> ';

		// 끝 페이지
		$tag = $pageParams['page'] < $pageAll ? 'a' : 'span';
		$pageHTML .= ' <'.$tag.' class="last" href="' . $pageParams['pageUrl'] . $linkfirst. $pageAll . '" data-page="' . $pageAll . '">' . $pageParams['img']['last'] . '</'.$tag.'> ';

		return '<div class="paging">'.$pageHTML.'</div>';
	}
}

class BH_DB_Insert{
	public $table = '';
	public $decrement = '';
	public $data = array();
	public $test = false;
	public $MAXInt = _DBMAXINT;
	private $where = array();
	private $MultiNames = '';
	private $MultiValues = array();
	private $connName = '';
	private $duplicateData = array();

	protected $tableBindParam = array();
	protected $whereBindParam = array();
	protected $bindParam = array();

	public function  __construct($table = ''){
		if($table !== '') $this->table = $this->StrToPDO('table', func_get_args());
		$this->connName = DB::DefaultConnName;
	}

	public function StrToPDO($p, $args){
		$a = $args;
		if(!is_array($a)){
			$a = func_get_args();
			array_shift($a);
		}
		$res = DB::StrToPDO($a);
		if(sizeof($res[1])){
			if($p == 'table') $this->tableBindParam = $this->tableBindParam + $res[1];
			else if($p == 'where') $this->whereBindParam = $this->whereBindParam + $res[1];
			else $this->bindParam = $this->bindParam + $res[1];
		}
		return $res[0];
	}

	public function &SetOnDuplicateData($key, $val){
		$this->duplicateData[$key] = $val;
		return $this;
	}

	public function &SetOnDuplicateDataStr($key, $val){
		$this->duplicateData[$key] = $this->StrToPDO('data','%s', $val);
		return $this;
	}

	public function &SetOnDuplicateDataNum($key, $val){
		$this->duplicateData[$key] = SetDBFloat($val);
		return $this;
	}

	public function &SetConnName($str){
		$this->connName = $str;
		return $this;
	}

	public function &AddTable($str){
		$w = $this->StrToPDO('table', func_get_args());
		if($w !== false) $this->table .= ' '.$w;
		return $this;
	}

	public function &SetDecrementKey($str){
		$this->decrement = $str;
		return $this;
	}

	public function &SetData($key, $val){
		$this->data[$key] = $val;
		return $this;
	}

	public function &SetDataStr($key, $val){
		$this->data[$key] = $this->StrToPDO('data', '%s', $val);
		return $this;
	}

	public function &SetDataNum($key, $val){
		$this->data[$key] = SetDBFloat($val);
		return $this;
	}

	public function &AddWhere($str){
		$w = $this->StrToPDO('where', func_get_args());
		if($w !== false) $this->where[] = '('.$w.')';
		return $this;
	}

	public function &UnsetWhere(){
		unset($this->where);
		return $this;
	}

	public function &MultiAdd(){
		$temp = '';
		$names = '';
		$values = '';
		foreach($this->data as $k => $v){
			if(!$this->MultiNames) $names .= $temp . '`' . $k . '`';
			$values .= $temp . $v;
			$temp = ',';
		}
		if(!$this->MultiNames) $this->MultiNames = $names;
		$this->MultiValues[]= '('.$values.')';
		return $this;
	}

	public function MultiRun(){
		$res = new \BH_Result();
		if(!sizeof($this->data)){
			$res->result = false;
			$res->message = '등록할 자료가 없습니다.';
			return $res;
		}

		$sql = 'INSERT INTO ' . $this->table . '(' . $this->MultiNames . ') VALUES '.implode(',', $this->MultiValues);
		if($this->test && _DEVELOPERIS === true){
			foreach($this->bindParam as $k => $v) $sql = str_replace($k, '\''.$v[0].'\'', $sql);
			echo $sql;
			exit;
		}
		$qry = DB::PDO($this->connName)->prepare($sql);
		foreach($this->bindParam as $k => $v) $qry->bindParam($k, $v[0], $v[1]);
		$res->result = $qry->execute();
		return $res;
	}

	public function Run(){
		$res = new \BH_InsertResult();
		$temp = '';
		$names = '';
		$values = '';
		foreach($this->data as $k => $v){
			$names .= $temp . '`' . $k . '`';
			$values .= $temp . $v;
			$temp = ',';
		}

		if(sizeof($this->duplicateData)){
			$set = array();
			foreach($this->data as $k => $v) $set[]= '`' . $k . '` = ' . $v;
			$duplicateSql = 'ON DUPLICATE KEY UPDATE '.implode(', ', $set);
		}

		if($this->decrement){
			$r = false;
			$cnt = 5;
			while(!$r && $cnt > 0){
				$qry = DB::PDO($this->connName)->prepare('SELECT MIN(`'.$this->decrement.'`) as seq FROM '.$this->table
					. ($this->where ? ' WHERE ' . implode(' AND ', $this->where) : ''));
				foreach($this->tableBindParam as $k => $v) $qry->bindParam($k, $v[0], $v[1]);
				foreach($this->whereBindParam as $k => $v) $qry->bindParam($k, $v[0], $v[1]);
				if(!$qry->execute()){
					$res->result = false;
					return $res;
				}
				else $minseq = $qry->fetch(PDO::FETCH_ASSOC);
				if(!$minseq){
					$res->result = false;
					return $res;
				}
				if(!strlen($minseq['seq'])) $minseq['seq'] = $this->MAXInt;

				$minseq['seq'] --;
				$sql = 'INSERT INTO ' . $this->table . '(' . $names . ', `' . $this->decrement . '`) VALUES (' . $values . ',' . $minseq['seq'] . ')';
				if($this->test && _DEVELOPERIS === true){
					foreach($this->tableBindParam as $k => $v) $qry->bindParam($k, $v[0], $v[1]);
					foreach($this->bindParam as $k => $v) $sql = str_replace($k, '\''.$v[0].'\'', $sql);
					echo $sql;
					exit;
				}
				$qry = DB::PDO($this->connName)->prepare($sql.(isset($duplicateSql) ? ' '.$duplicateSql : ''));
				foreach($this->tableBindParam as $k => $v) $qry->bindParam($k, $v[0], $v[1]);
				foreach($this->bindParam as $k => $v) $qry->bindParam($k, $v[0], $v[1]);
				$r = $qry->execute();
				$cnt --;
			}
			$res->result = $r ? true : false;
			$res->id = $minseq['seq'];
		}
		else{
			$sql = 'INSERT INTO ' . $this->table . '(' . $names . ') VALUES (' . $values . ')'.(isset($duplicateSql) ? ' '.$duplicateSql : '');
			if($this->test && _DEVELOPERIS === true){
				foreach($this->bindParam as $k => $v) $sql = str_replace($k, '\''.$v[0].'\'', $sql);
				echo $sql;
				exit;
			}

			$qry = DB::PDO($this->connName)->prepare($sql);
			foreach($this->bindParam as $k => $v) $qry->bindParam($k, $v[0], $v[1]);
			$res->result = $qry->execute();
			if($res->result) $res->id = DB::PDO($this->connName)->lastInsertId();
		}
		return $res;
	}

	public function &SetTest($bool = false){
		$this->test = $bool;
		return $this;
	}
}

class BH_DB_Update{
	public $table = '';
	public $where = array();
	public $data = array();
	public $test = false;
	public $sort = '';
	private $connName = '';

	protected $bindParam = array();

	public function  __construct($table = ''){
		if($table !== '') $this->table = $this->StrToPDO(func_get_args());
		$this->connName = DB::DefaultConnName;
	}

	public function StrToPDO($args){
		$res = DB::StrToPDO(is_array($args) ? $args : func_get_args());
		if(sizeof($res[1])) $this->bindParam = $this->bindParam + $res[1];
		return $res[0];
	}

	public function &SetConnName($str){
		$this->connName = $str;
		return $this;
	}

	public function &AddTable($str){
		$w = $this->StrToPDO(func_get_args());
		if($w !== false) $this->table .= ' '.$w;
		return $this;
	}

	public function &SetData($key, $val){
		$this->data[$key] = $val;
		return $this;
	}

	public function &SetDataStr($key, $val){
		$this->data[$key] = $this->StrToPDO('%s', $val);
		return $this;
	}

	public function &SetDataNum($key, $val){
		$this->data[$key] = SetDBFloat($val);
		return $this;
	}

	public function &AddWhere($str){
		$w = $this->StrToPDO(func_get_args());
		if($w !== false) $this->where[] = '('.$w.')';
		return $this;
	}

	public function &SetSort($str){
		$this->sort = $str;
		return $this;
	}

	function Run(){
		$res = new \BH_Result();
		$temp = '';
		$set = '';
		foreach($this->data as $k => $v){
			$set .= $temp . '`' . $k . '` = ' . $v;
			$temp = ',';
		}

		if(isset($this->where) && is_array($this->where) && sizeof($this->where)){
			$where = ' WHERE ' . implode(' AND ', $this->where);
		}
		else{
			$res->result = false;
			$res->message = _DEVELOPERIS === true ? 'WHERE 구문이 없습니다.' : 'ERROR #101';
			return $res;
		}

		$sql = 'UPDATE ' . $this->table . ' SET ' . $set . $where;
		if($this->sort) $sql .= ' ORDER BY ' . $this->sort;
		if($this->test && _DEVELOPERIS === true){
			foreach($this->bindParam as $k => $v) $sql = str_replace($k, '\''.$v[0].'\'', $sql);
			echo $sql;
			exit;
		}
		$qry = DB::PDO($this->connName)->prepare($sql);
		foreach($this->bindParam as $k => $v) $qry->bindParam($k, $v[0], $v[1]);
		$res->result = $qry->execute();
		return $res;
	}

	public function &SetTest($bool = false){
		$this->test = $bool;
		return $this;
	}
}

class BH_DB_Delete{
	public $table = '';
	public $test = false;
	private $where = array();
	private $connName = '';

	protected $bindParam = array();

	public function  __construct($table = ''){
		if($table !== '') $this->table = $this->StrToPDO(func_get_args());
		$this->connName = DB::DefaultConnName;
	}

	public function StrToPDO($args){
		$res = DB::StrToPDO(is_array($args) ? $args : func_get_args());
		if(sizeof($res[1])) $this->bindParam = $this->bindParam + $res[1];
		return $res[0];
	}

	public function &SetConnName($str){
		$this->connName = $str;
		return $this;
	}

	public function &AddTable($str){
		$w = $this->StrToPDO(func_get_args());
		if($w !== false) $this->table .= ' '.$w;
		return $this;
	}

	public function &AddWhere($str){
		$w = $this->StrToPDO(func_get_args());
		if($w !== false) $this->where[] = '('.$w.')';
		return $this;
	}

	function Run(){
		$where = '';
		if(isset($this->where) && is_array($this->where) && sizeof($this->where)) $where = ' WHERE ' . implode(' AND ', $this->where);

		$sql = 'DELETE FROM '.$this->table.' '.$where;
		if($this->test && _DEVELOPERIS === true){
			foreach($this->bindParam as $k => $v) $sql = str_replace($k, '\''.$v[0].'\'', $sql);
			echo $sql;
			exit;

		}

		$qry = DB::PDO($this->connName)->prepare($sql);
		foreach($this->bindParam as $k => $v) $qry->bindParam($k, $v[0], $v[1]);
		$res = $qry->execute();
		return $res;
	}

	public function &SetTest($bool = false){
		$this->test = $bool;
		return $this;
	}
}