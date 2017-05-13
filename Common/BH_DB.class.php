<?php
/**
 * Bang Hun.
 * 16.07.10
 */
class DB{
	const DefaultConnName = 'MY';
	/**
	 * @var self
	 */
	private static $Instance;
	private static $Conn = array();
	private static $ConnName = 'MY';
	private function __construct(){
	}

	public function __destruct(){
		foreach(self::$Conn as $k => $v){
			mysqli_close($v);
		}
	}

	public static function &SQL($connName = self::DefaultConnName){
		self::$ConnName = $connName;
		if (!isset(self::$Instance)){
			self::$Instance = new self();
		}
		if(!isset(self::$Conn[self::$ConnName])){
			switch(self::$ConnName){
				case self::DefaultConnName:
					require _COMMONDIR.'/db.info.php';
					/** @var array $_DBInfo */
					self::$Conn[self::$ConnName] = mysqli_connect($_DBInfo['hostName'], $_DBInfo['userName'], $_DBInfo['userPassword'], $_DBInfo['dbName']);
					if(!self::$Conn[self::$ConnName]){
						echo('ACCESS_DENIED_DB_CONNECTION');
						exit;
					}
					mysqli_set_charset(self::$Conn[self::$ConnName],'utf8');
				break;
				default:
					echo('NOT_DEFINE_DB');
					exit;
			}
		}
		return self::$Instance;
	}

	public function TableExists($table){
		$exists = self::NumRows('SHOW TABLES LIKE \'' . $table . '\'');
		if($exists) return true;
		else return false;
	}

	public function NumRows($qry){
		if(is_string($qry)) $qry = self::Query($qry);
		if($qry === false) return false;

		try{
			$r = mysqli_num_rows($qry);
			return $r;
		}
		catch(Exception $e){
			if(_DEVELOPERIS === true) echo 'NUMBER ROWS MESSAGE(DEBUG ON) : <b>'. $e->getMessage().'</b><br>';
			return false;
		}
	}

	public function Free($qry){
		if(is_bool($qry)) return;
		mysqli_free_result($qry);
	}

	public function Query($str){
		$sql = StrToSql(is_array($str) ? $str : func_get_args());
		//echo $sql;
		if(_DEVELOPERIS === true) $res = mysqli_query(self::$Conn[self::$ConnName], $sql) or die('ERROR SQL : '.$sql);
		else $res = mysqli_query(self::$Conn[self::$ConnName], $sql) or die('ERROR');
		return $res;
	}

	public function CCQuery($table, $str){
		if(is_array($str)) $args = $str;
		else{
			$args = func_get_args();
			array_shift($args);
		}

		if(strpos($args[0], '%t') === false) die('ERROR SQL(CC)'.(_DEVELOPERIS === true ? ' : '.$args[0] : ''));
		$args[0] = str_replace('%t', $table, $args[0]);
		$sql = trim(StrToSql($args));

		if(_DEVELOPERIS === true) $res = mysqli_query(self::$Conn[self::$ConnName], $sql) or die('ERROR SQL : '.$sql);
		else $res = mysqli_query(self::$Conn[self::$ConnName], $sql) or die('ERROR SQL');

		if($res && (strtolower(substr($sql, 0, 6)) == 'delete' || strtolower(substr($sql, 0, 6)) == 'update' || strtolower(substr($sql, 0, 6)) == 'insert')) \BH_DB_Cache::DelPath($table);

		return $res;
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

		$r = mysqli_fetch_assoc($qry);
		if($string_is) self::Free($qry);

		return $r;
	}

	public static function &GetConn(){
		return self::$Conn[self::$ConnName];
	}

	public static function &GetQryObj($table, $cache = _USE_DB_CACHE){
		$instance = new \BH_DB_Get($table, $cache);
		return $instance;
	}

	public static function &GetListQryObj($table, $cache = _USE_DB_CACHE){
		$instance = new \BH_DB_GetList($table, $cache);
		return $instance;
	}

	public static function &GetListPageQryObj($table, $cache = _USE_DB_CACHE){
		$instance = new \BH_DB_GetListWithPage($table, $cache);
		return $instance;
	}

	public static function &UpdateQryObj($table){
		$instance = new \BH_DB_Update($table);
		return $instance;
	}

	public static function &InsertQryObj($table){
		$instance = new \BH_DB_Insert($table);
		return $instance;
	}

	public static function &DeleteQryObj($table){
		$instance = new \BH_DB_Delete($table);
		return $instance;
	}
}

class BH_DB_Cache{
	public static $DBTableFirst = array();
	public static $ExceptTable = array();
	public static $sqlData = array();
	public static function GetCachePath($table, $fileNm){
		$table = trim($table);
		$folder = '';
		if($table){
			$tx = explode(' ', trim($table));
			foreach($tx as $v){
				foreach(self::$DBTableFirst as $v2){
					if(substr($v, 0, strlen($v2)) == $v2) $folder .= urlencode($v).'+';
				}
			}
		}
		else exit;

		if(!strlen($folder)){
			if(_DEVELOPERIS === true) echo $table.'('.$folder.') - NOT FIND TABLE NAME';
			exit;
		}

		$path = _DATADIR.'/temp/+'.$folder;
		$file = $path.'/'.$fileNm.'.php';
		foreach(self::$ExceptTable as $v){
			if(strpos($table, $v) !== false) return array('result' => false, 'file' => $file);
		}
		if(!file_exists($path) && !is_dir($path)) mkdir($path, 0755, true);
		return array('result' => true, 'file' => $file);
	}

	public static function DelPath($table){
		$tx = explode(' ', $table);
		foreach($tx as $v){
			$v = trim($v);
			if(strlen($v)) findDelTree('+'.$v.'+');
		}
	}
}

class BH_DB_Get{
	public $table = '';
	public $test = false;
	public $sort = '';
	public $group = '';
	private $cache = false;
	private $query = null;
	private $having = array();
	private $where = array();
	private $key = array();
	private $connName = '';

	public function  __construct($table = '', $cache = _USE_DB_CACHE){
		$this->table = $table;
		$this->cache = $cache;
		$this->connName = \DB::DefaultConnName;
	}

	public function __destruct(){
		if($this->query) \DB::Sql($this->connName)->Free($this->query);
	}

	public static function Init($table = '', $cache = _USE_DB_CACHE){
		$instance = new self($table, $cache);
		return $instance;
	}

	public function &SetConnName($str){
		$this->connName = $str;
		return $this;
	}

	public function &AddTable($str){
		$w = StrToSql(func_get_args());
		if($w !== false) $this->where .= $w;
		return $this;
	}

	public function &AddWhere($str){
		$w = StrToSql(func_get_args());
		if($w !== false) $this->where[] = '('.$w.')';
		return $this;
	}

	public function &AddHaving($str){
		$w = StrToSql(func_get_args());
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
				if($k){
					foreach($keys as $row){
						$this->key[] = $row;
					}
				}
				else $this->key = $keys;
			}
		}
		return $this;
	}

	public function &AddKey($str){
		$args = func_get_args();
		foreach($args as $keys){
			if(is_string($keys)) $this->key[] = $keys;
			else if(is_array($keys)){
				foreach($keys as $row){
					$this->key[] = $row;
				}
			}
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

	function Get(){
		$where = '';
		if(isset($this->where) && is_array($this->where) && sizeof($this->where)) $where = ' WHERE ' . implode(' AND ', $this->where);
		$having = '';
		if(isset($this->having) && is_array($this->having) && sizeof($this->having)) $having = ' HAVING ' . implode(' AND ', $this->having);

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
		$sql .= ' LIMIT 1';
		if($this->test && _DEVELOPERIS === true){
			echo $sql;
			exit;
		}

		$sql = trim($sql);
		$sqlFileNm = hash('sha1', $sql);
		// Cache
		if($this->cache){
			$path = \BH_DB_Cache::GetCachePath($this->table, $sqlFileNm);
			$this->cache = $path['result'];
			if(!isset(\BH_DB_Cache::$sqlData['GetData'][$sqlFileNm][$sql])){
				if($path['result'] && file_exists($path['file'])) require_once $path['file'];
			}

			if(isset(\BH_DB_Cache::$sqlData['GetData'][$sqlFileNm][$sql])) return \BH_DB_Cache::$sqlData['GetData'][$sqlFileNm][$sql];
		}

		$this->query = \DB::SQL($this->connName)->Query($sql);
		if($this->query){
			$row = mysqli_fetch_assoc($this->query);
			if($row){
				// Cache
				if($this->cache){
					\BH_DB_Cache::$sqlData['GetData'][$sqlFileNm][$sql] = $row;
					$txt = '<?php \BH_DB_Cache::$sqlData[\'GetData\'][\''.$sqlFileNm.'\'] = '.var_export(\BH_DB_Cache::$sqlData['GetData'][$sqlFileNm], true).';';
					file_put_contents($path['file'], $txt);
					chmod($path['file'], 0700);
				}
				\DB::SQL($this->connName)->Free($this->query);
				$this->query = null;
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

class BH_DB_GetList{
	public $table = '';
	public $limit = '';
	public $sort = '';
	public $group = '';
	public $test = false;
	private $cache = false;
	private $cacheData = array();

	private $pointer = -1;
	private $query = null;
	private $having = array();
	private $where = array();
	private $key = array();
	private $connName = '';

	public $result = false;
	public $data = array();
	private $RunIs = false;

	public function  __construct($table = '', $cache = _USE_DB_CACHE){
		$this->table = $table;
		$this->cache = $cache;
		$this->connName = \DB::DefaultConnName;
	}

	public function __destruct(){
		if($this->query) \DB::Sql($this->connName)->Free($this->query);
	}

	public static function Init($table = '', $cache = _USE_DB_CACHE){
		$instance = new self($table, $cache);
		return $instance;
	}

	public function &SetConnName($str){
		$this->connName = $str;
		return $this;
	}

	public function &AddTable($str){
		$w = StrToSql(func_get_args());
		if($w !== false) $this->where .= $w;
		return $this;
	}

	public function &AddWhere($str){
		$w = StrToSql(func_get_args());
		if($w !== false) $this->where[] = '('.$w.')';
		return $this;
	}

	public function &AddHaving($str){
		$w = StrToSql(func_get_args());
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
				if($k){
					foreach($keys as $row){
						$this->key[] = $row;
					}
				}
				else $this->key = $keys;
			}
		}
		return $this;
	}

	public function &AddKey($str){
		$args = func_get_args();
		foreach($args as $keys){
			if(is_string($keys)) $this->key[] = $keys;
			else if(is_array($keys)){
				foreach($keys as $row){
					$this->key[] = $row;
				}
			}
		}
		return $this;
	}

	public function DrawRows(){
		if(!$this->RunIs) $this->Run();
		if($this->cache){
			$this->data = &$this->cacheData;
			return;
		}
		while($row = $this->Get()){
			$this->data[]= $row;
		}
	}

	public function &GetRows(){
		if(!$this->RunIs) $this->Run();
		if($this->cache) return $this->cacheData;

		$this->DrawRows();
		return $this->data;
	}

	public function &SetGroup($str){
		$this->group = $str;
		return $this;
	}

	public function &SetSort($str){
		$this->sort = $str;
		return $this;
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
			echo $sql;
			exit;
		}

		$sql = trim($sql);
		$sqlFileNm = hash('sha1', $sql);

		// Cache
		if($this->cache){
			$path = \BH_DB_Cache::GetCachePath($this->table, $sqlFileNm);
			$this->cache = $path['result'];
			if(!isset(\BH_DB_Cache::$sqlData['GetListData'][$sqlFileNm][$sql])){
				if($path['result'] && file_exists($path['file'])) require_once $path['file'];
			}

			if(isset(\BH_DB_Cache::$sqlData['GetListData'][$sqlFileNm][$sql])){
				$this->cacheData = &\BH_DB_Cache::$sqlData['GetListData'][$sqlFileNm][$sql];
				$this->result = true;
				return $this;
			}
		}

		$this->query = \DB::SQL($this->connName)->Query($sql);
		if($this->query){
			// Cache
			if($this->cache){
				while($row = mysqli_fetch_assoc($this->query)){
					$this->cacheData[]= $row;
				}

				\BH_DB_Cache::$sqlData['GetListData'][$sqlFileNm][$sql] = $this->cacheData;
				$txt = '<?php \BH_DB_Cache::$sqlData[\'GetListData\'][\''.$sqlFileNm.'\'] = '.var_export(\BH_DB_Cache::$sqlData['GetListData'][$sqlFileNm], true).';';
				file_put_contents($path['file'], $txt);
				chmod($path['file'], 0700);
			}
			$this->result = true;
		}
		else{
			$this->result = false;
		}
		return $this;
	}

	public function Get(){
		if(!$this->RunIs) $this->Run();

		// Cache
		if($this->cache){
			$this->pointer++;
			return isset($this->cacheData[$this->pointer]) ? $this->cacheData[$this->pointer] : false;
		}else{
			$res = $this->query ? mysqli_fetch_assoc($this->query) : false;
			return $res;
		}
	}

	public function &SetTest($bool = false){
		$this->test = $bool;
		return $this;
	}
}

class BH_DB_GetListWithPage{
	public $table = '';
	public $articleCount = 10;
	public $limit = '';
	public $sort = '';
	public $group = '';
	public $test = false;
	public $page = 1;
	public $pageCount = 10;
	public $pageUrl = '';
	public $CountKey = '';
	public $SubCountKey = array();
	private $cache = false;
	private $cacheData = array();

	private $pointer = -1;
	private $query = null;
	private $where = array();
	private $having = array();
	private $key = array();
	private $RunIs = false;
	private $connName = '';

	// Result
	public $result = false;
	public $countResult = false;
	public $data = array();
	public $totalRecord = '';
	public $beginNum = '';
	public $pageHtml = '';

	public function  __construct($table = '', $cache = _USE_DB_CACHE){
		$this->table = $table;
		$this->cache = $cache;
		$this->connName = \DB::DefaultConnName;
	}

	public function __destruct(){
		if($this->query) \DB::Sql($this->connName)->Free($this->query);
	}

	public static function Init($table = '', $cache = _USE_DB_CACHE){
		$instance = new self($table, $cache);
		return $instance;
	}

	public function &SetConnName($str){
		$this->connName = $str;
		return $this;
	}

	public function &AddTable($str){
		$w = StrToSql(func_get_args());
		if($w !== false) $this->where .= $w;
		return $this;
	}

	public function &AddWhere($str){
		$w = StrToSql(func_get_args());
		if($w !== false) $this->where[] = '('.$w.')';
		return $this;
	}

	public function &AddHaving($str){
		$this->having[] = $str;
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
				if($k){
					foreach($keys as $row){
						$this->key[] = $row;
					}
				}
				else $this->key = $keys;
			}
		}
		return $this;
	}

	public function &AddKey($str){
		$args = func_get_args();
		foreach($args as $keys){
			if(is_string($keys)) $this->key[] = $keys;
			else if(is_array($keys)){
				foreach($keys as $row){
					$this->key[] = $row;
				}
			}
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

	public function &SetArticleCOunt($str){
		$this->articleCount = (int)$str;
		return $this;
	}

	public function &SetSubCountKey($str){
		$this->SubCountKey = $str;
		return $this;
	}

	public function &SetCountKey($str){
		$this->CountKey = $str;
		return $this;
	}

	public function DrawRows(){
		if(!$this->RunIs) $this->Run();
		if($this->cache){
			$this->data = &$this->cacheData;
			return;
		}
		while($row = $this->Get()){
			$this->data[]= $row;
		}
	}

	public function &GetRows(){
		if(!$this->RunIs) $this->Run();
		if($this->cache) return $this->cacheData;

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
			echo $sql;
			exit;
		}

		$sql = trim($sql);
		$sqlFileNm = hash('sha1', $sql);

		// Cache
		if($this->cache){
			$path = \BH_DB_Cache::GetCachePath($this->table, $sqlFileNm);
			$this->cache = $path['result'];
			if(!isset(\BH_DB_Cache::$sqlData['GetListWithPage'][$sqlFileNm][$sql])){
				if($path['result'] && file_exists($path['file'])) require_once $path['file'];
			}

			if(isset(\BH_DB_Cache::$sqlData['GetListWithPage'][$sqlFileNm][$sql])){
				$this->totalRecord = $pagedata['totalRecord'] = &\BH_DB_Cache::$sqlData['GetListWithPage'][$sqlFileNm][$sql]['totalRecord'];
				$this->beginNum = &\BH_DB_Cache::$sqlData['GetListWithPage'][$sqlFileNm][$sql]['beginNum'];
				$this->cacheData = &\BH_DB_Cache::$sqlData['GetListWithPage'][$sqlFileNm][$sql]['data'];
				$this->countResult = &\BH_DB_Cache::$sqlData['GetListWithPage'][$sqlFileNm][$sql]['countResult'];
				$pagedata['articleCount'] = $this->articleCount;
				$pagedata['pageCount'] = $this->pageCount;
				$pagedata['page'] = $this->page ? $this->page : 1;
				$pagedata['pageUrl'] = $this->pageUrl;
				$pagedata['totalRecord'] = $this->totalRecord;
				$this->pageHtml = $this->SqlGetPage($pagedata);

				$this->result = true;
				return $this;
			}
		}

		$this->countResult = \DB::SQL($this->connName)->Fetch($this->group ? 'SELECT COUNT(*) as cnt'.$subCnt_sql2.' FROM ('.$sql_cnt.') AS x' : $sql_cnt);
		$totalRecord = $this->countResult['cnt']; //total값 구함
		//\DB::Sql($this->connName)->Free($result_cnt);

		$this->totalRecord = $totalRecord;
		$this->beginNum = $totalRecord - ($nowPage * $this->articleCount);

		$this->query = \DB::SQL($this->connName)->Query($sql);
		if($this->query){
			// Cache
			if($this->cache){
				while($row = mysqli_fetch_assoc($this->query)){
					$this->cacheData[]= $row;
				}

				\BH_DB_Cache::$sqlData['GetListWithPage'][$sqlFileNm][$sql]['beginNum'] = $this->beginNum;
				\BH_DB_Cache::$sqlData['GetListWithPage'][$sqlFileNm][$sql]['totalRecord'] = $this->totalRecord;
				\BH_DB_Cache::$sqlData['GetListWithPage'][$sqlFileNm][$sql]['data'] = $this->cacheData;
				\BH_DB_Cache::$sqlData['GetListWithPage'][$sqlFileNm][$sql]['countResult'] = $this->countResult;
				$txt = '<?php \BH_DB_Cache::$sqlData[\'GetListWithPage\'][\''.$sqlFileNm.'\'] = '.var_export(\BH_DB_Cache::$sqlData['GetListWithPage'][$sqlFileNm], true).';';
				file_put_contents($path['file'], $txt);
				chmod($path['file'], 0700);
			}

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

	public function GetPageHtml(){
		if(!$this->RunIs) $this->Run();
		return $this->pageHtml;
	}

	public function Get(){
		if(!$this->RunIs) $this->Run();

		// Cache
		if($this->cache){
			$this->pointer++;
			return isset($this->cacheData[$this->pointer]) ? $this->cacheData[$this->pointer] : false;
		}else{
			$res = $this->query ? mysqli_fetch_assoc($this->query) : false;
			return $res;
		}
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

	public function &SetTest($bool = false){
		$this->test = $bool;
		return $this;
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

	public function  __construct($table = ''){
		$this->table = $table;
		$this->connName = \DB::DefaultConnName;
	}

	public static function Init($table = ''){
		$instance = new self($table);
		return $instance;
	}

	public function &SetConnName($str){
		$this->connName = $str;
		return $this;
	}

	public function &AddTable($str){
		$w = StrToSql(func_get_args());
		if($w !== false) $this->where .= $w;
		return $this;
	}

	public function &SetDecrementKey($str){
		$this->decrement = $str;
		return $this;
	}

	public function SetData($key, $val){
		$this->data[$key] = $val;
	}

	public function &SetDataStr($key, $val){
		$this->data[$key] = SetDBText($val);
		return $this;
	}

	public function &SetDataNum($key, $val){
		$this->data[$key] = SetDBFloat($val);
		return $this;
	}

	public function &AddWhere($str){
		$w = StrToSql(func_get_args());
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
			return;
		}

		$sql = 'INSERT INTO ' . $this->table . '(' . $this->MultiNames . ') VALUES '.implode(',', $this->MultiValues);
		if($this->test && _DEVELOPERIS === true){
			echo $sql;exit;
		}
		$res->result = \DB::SQL($this->connName)->Query($sql);
		if($res->result) \BH_DB_Cache::DelPath($this->table);
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
		if($this->decrement){
			$r = false;
			$cnt = 5;
			while(!$r && $cnt > 0){
				$minseq = \DB::SQL($this->connName)->Fetch('SELECT MIN(`'.$this->decrement.'`) as seq FROM `'.$this->table.'`'
					. ($this->where ? ' WHERE ' . implode(' AND ', $this->where) : ''));
				if(!$minseq){
					$res->result = false;
					return;
				}
				if(!strlen($minseq['seq'])) $minseq['seq'] = $this->MAXInt;

				$minseq['seq'] --;
				$sql = 'INSERT INTO ' . $this->table . '(' . $names . ', `' . $this->decrement . '`) VALUES (' . $values . ',' . $minseq['seq'] . ')';
				if($this->test && _DEVELOPERIS === true){
					echo $sql;
					exit;
				}
				$r = \DB::SQL($this->connName)->Query($sql);
				$cnt --;
			}
			$res->result = $r ? true : false;
			$res->id = $minseq['seq'];
		}
		else{
			$sql = 'INSERT INTO ' . $this->table . '(' . $names . ') VALUES (' . $values . ')';
			if($this->test && _DEVELOPERIS === true){
				echo $sql;
				exit;
			}

			$res->result = \DB::SQL($this->connName)->Query($sql);
			$res->id = mysqli_insert_id(\DB::SQL($this->connName)->GetConn());
		}

		if($res->result) \BH_DB_Cache::DelPath($this->table);
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

	public function  __construct($table = ''){
		$this->table = $table;
		$this->connName = \DB::DefaultConnName;
	}

	public static function Init($table = ''){
		$instance = new self($table);
		return $instance;
	}

	public function &SetConnName($str){
		$this->connName = $str;
		return $this;
	}

	public function &AddTable($str){
		$w = StrToSql(func_get_args());
		if($w !== false) $this->where .= $w;
		return $this;
	}

	public function &SetData($key, $val){
		$this->data[$key] = $val;
		return $this;
	}

	public function &SetDataStr($key, $val){
		$this->data[$key] = SetDBText($val);
		return $this;
	}

	public function &SetDataNum($key, $val){
		$this->data[$key] = SetDBFloat($val);
		return $this;
	}

	public function &AddWhere($str){
		$w = StrToSql(func_get_args());
		if($w !== false) $this->where[] = '('.$w.')';
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
			echo $sql;
			exit;
		}
		$res->result = \DB::SQL($this->connName)->Query($sql);
		if($res->result) \BH_DB_Cache::DelPath($this->table);
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

	public static function Init($table = ''){
		$instance = new self($table);
		return $instance;
	}

	public function  __construct($table = ''){
		$this->table = $table;
		$this->connName = \DB::DefaultConnName;
	}

	public function &SetConnName($str){
		$this->connName = $str;
		return $this;
	}
	
	public function &AddTable($str){
		$w = StrToSql(func_get_args());
		if($w !== false) $this->where .= $w;
		return $this;
	}

	public function &AddWhere($str){
		$w = StrToSql(func_get_args());
		if($w !== false) $this->where[] = '('.$w.')';
		return $this;
	}

	function Run(){
		$where = '';
		if(isset($this->where) && is_array($this->where) && sizeof($this->where)) $where = ' WHERE ' . implode(' AND ', $this->where);

		$sql = 'DELETE FROM '.$this->table.' '.$where;
		if($this->test && _DEVELOPERIS === true){
			echo $sql;
			exit;
		}

		$res = \DB::SQL($this->connName)->Query($sql);
		if($res) \BH_DB_Cache::DelPath($this->table);
		return $res;
	}

	public function &SetTest($bool = false){
		$this->test = $bool;
		return $this;
	}
}