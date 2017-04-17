<?php
/**
 * Bang Hun.
 * 16.07.10
 */
class BH_DB_Cache{
	public static $DBTableFirst = array();
	public static $ExceptTable = array();
	public static function GetCachePath($table, $sql){
		$table = trim($table);
		$folder = '';
		if($table){
			$tx = explode(' ', trim($table));
			foreach($tx as $v){
				foreach(self::$DBTableFirst as $v2){
					if(substr($v, 0, strlen($v2)) == $v2) $folder .= urlencode($v).'+';
				}
			}
			$folder = urlencode($table).'+';
		}
		else exit;

		$path = _DATADIR.'/temp/+'.$folder;
		$file = $path.'/'.strlen( $sql).'.php';
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
	public $cache = false;
	private $query = null;
	private $having = array();
	private $where = array();
	private $key = array();

	public function  __construct($table = '', $cache = _USE_DB_CACHE){
		$this->table = $table;
		$this->cache = $cache;
	}

	public function __destruct(){
		if($this->query) SqlFree($this->query);
	}

	public function AddWhere($str){
		$w = StrToSql(func_get_args());
		if($w !== false) $this->where[] = '('.$w.')';
	}

	public function AddHaving($str){
		$w = StrToSql(func_get_args());
		if($w !== false) $this->having[] = '('.$w.')';
	}

	public function SetKey($str){
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
	}

	public function AddKey($str){
		$args = func_get_args();
		foreach($args as $keys){
			if(is_string($keys)) $this->key[] = $keys;
			else if(is_array($keys)){
				foreach($keys as $row){
					$this->key[] = $row;
				}
			}
		}
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
		if($this->test){
			echo $sql;
			exit;
		}

		// Cache
		if($this->cache){
			$path = BH_DB_Cache::GetCachePath($this->table, $sql);
			$this->cache = $path['result'];
			if($path['result'] && file_exists($path['file'])){
				require_once $path['file'];
				/** @var $data */
				if(isset($GetData[$sql])) return $GetData[$sql];
			}
		}

		$this->query = SqlQuery($sql);
		if($this->query){
			$row = mysqli_fetch_assoc($this->query);
			if($row){

				// Cache
				if($this->cache){
					$GetData[$sql] = $row;
					$txt = '<?php $GetData = '.var_export($GetData, true).';';
					file_put_contents($path['file'], $txt);
					chmod($path['file'], 0700);
				}
				return $row;
			}
		}
		return false;
	}
}

class BH_DB_GetList{
	public $table = '';
	public $limit = '';
	public $sort = '';
	public $group = '';
	public $test = false;
	public $cache = false;

	private $pointer = -1;
	private $query = null;
	private $having = array();
	private $where = array();
	private $key = array();

	public $result = false;
	public $data = array();
	private $RunIs = false;

	public function  __construct($table = '', $cache = _USE_DB_CACHE){
		$this->table = $table;
		$this->cache = $cache;
	}

	public function __destruct(){
		if($this->query) SqlFree($this->query);
	}

	public function AddWhere($str){
		$w = StrToSql(func_get_args());
		if($w !== false) $this->where[] = '('.$w.')';
	}

	public function AddHaving($str){
		$w = StrToSql(func_get_args());
		if($w !== false) $this->having[] = '('.$w.')';
	}

	public function SetKey($str){
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
	}

	public function AddKey($str){
		$args = func_get_args();
		foreach($args as $keys){
			if(is_string($keys)) $this->key[] = $keys;
			else if(is_array($keys)){
				foreach($keys as $row){
					$this->key[] = $row;
				}
			}
		}
	}

	public function DrawRows(){
		if(!$this->RunIs) $this->Run();
		if($this->cache) return;
		while($row = $this->Get()){
			$this->data[]= $row;
		}
	}

	public function GetRows(){
		$this->DrawRows();
		return $this->data;
	}

	function Run(){
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

		if($this->test){
			echo $sql;
			exit;
		}

		$sql = trim($sql);

		// Cache
		if($this->cache){
			$path = BH_DB_Cache::GetCachePath($this->table, $sql);
			$this->cache = $path['result'];
			if($path['result'] && file_exists($path['file'])){
				require_once $path['file'];
				if(isset($GetListData[$sql])){
					$this->data = $GetListData[$sql];
					$this->result = true;
					return;
				}
			}
		}

		$this->query = SqlQuery($sql);
		if($this->query){
			// Cache
			if($this->cache){
				while($row = mysqli_fetch_assoc($this->query)){
					$this->data[]= $row;
				}

				$GetListData[$sql] = $this->data;
				$txt = '<?php $GetListData = '.var_export($GetListData, true).';';
				file_put_contents($path['file'], $txt);
				chmod($path['file'], 0700);
			}
			$this->result = true;
		}
		else{
			$this->result = false;
		}
	}

	public function Get(){
		if(!$this->RunIs) $this->Run();

		// Cache
		if($this->cache){
			$this->pointer++;
			return isset($this->data[$this->pointer]) ? $this->data[$this->pointer] : false;
		}else return $this->query ? mysqli_fetch_assoc($this->query) : false;
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
	public $cache = false;

	private $pointer = -1;
	private $query = null;
	private $where = array();
	private $having = array();
	private $key = array();
	private $RunIs = false;

	// Result
	public $result = false;
	public $data = array();
	public $totalRecord = '';
	public $beginNum = '';
	public $pageHtml = '';

	public function  __construct($table = '', $cache = _USE_DB_CACHE){
		$this->table = $table;
		$this->cache = $cache;
	}

	public function __destruct(){
		if($this->query) SqlFree($this->query);
	}

	public function AddWhere($str){
		$w = StrToSql(func_get_args());
		if($w !== false) $this->where[] = '('.$w.')';
	}

	public function AddHaving($str){
		$this->having[] = $str;
	}

	public function SetKey($str){
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
	}

	public function AddKey($str){
		$args = func_get_args();
		foreach($args as $keys){
			if(is_string($keys)) $this->key[] = $keys;
			else if(is_array($keys)){
				foreach($keys as $row){
					$this->key[] = $row;
				}
			}
		}
	}

	public function DrawRows(){
		if(!$this->RunIs) $this->Run();
		if($this->cache) return;
		while($row = $this->Get()){
			$this->data[]= $row;
		}
	}

	public function GetRows(){
		$this->DrawRows();
		return $this->data;
	}

	public function Run(){
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


		$sql_cnt = 'SELECT COUNT('.($this->CountKey ? $this->CountKey : '*').') as cnt FROM '.$this->table.' '.$where;
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

		if($this->test){
			echo $sql;
			exit;
		}

		// Cache
		if($this->cache){
			$path = BH_DB_Cache::GetCachePath($this->table, $sql);
			$this->cache = $path['result'];
			if($path['result'] && file_exists($path['file'])){
				require_once $path['file'];
				if(isset($GetListWithPage[$sql])){
					$this->totalRecord = $pagedata['totalRecord'] = $GetListWithPage[$sql]['totalRecord'];
					$this->beginNum = $GetListWithPage[$sql]['beginNum'];
					$this->data = $GetListWithPage[$sql]['data'];
					$pagedata['articleCount'] = $this->articleCount;
					$pagedata['pageCount'] = $this->pageCount;
					$pagedata['page'] = $this->page ? $this->page : 1;
					$pagedata['pageUrl'] = $this->pageUrl;
					$pagedata['totalRecord'] = $this->totalRecord;
					$this->pageHtml = $this->SqlGetPage($pagedata);

					$this->result = true;
					return;
				}
			}
		}

		$result_cnt = SqlFetch($this->group ? 'SELECT COUNT(*) as cnt FROM ('.$sql_cnt.') AS x' : $sql_cnt);
		$totalRecord = $result_cnt['cnt']; //total값 구함
		//SqlFree($result_cnt);

		$this->totalRecord = $totalRecord;
		$this->beginNum = $totalRecord - ($nowPage * $this->articleCount);

		$this->query = SqlQuery($sql);
		if($this->query){
			// Cache
			if($this->cache){
				while($row = mysqli_fetch_assoc($this->query)){
					$this->data[]= $row;
				}

				if($this->cache){
					$GetListWithPage[$sql]['beginNum'] = $this->beginNum;
					$GetListWithPage[$sql]['totalRecord'] = $this->totalRecord;
					$GetListWithPage[$sql]['data'] = $this->data;
					$txt = '<?php $GetListWithPage = '.var_export($GetListWithPage, true).';';
					file_put_contents($path['file'], $txt);
					chmod($path['file'], 0700);
				}
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
	}

	public function Get(){
		if(!$this->RunIs) $this->Run();

		// Cache
		if($this->cache){
			$this->pointer++;
			return isset($this->data[$this->pointer]) ? $this->data[$this->pointer] : false;
		}else return $this->query ? mysqli_fetch_assoc($this->query) : false;
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

	public $result = false;
	public $id;
	public $message = '';

	public function  __construct($table = ''){
		$this->table = $table;
	}

	public function SetData($key, $val){
		$this->data[$key] = $val;
	}

	public function SetDataStr($key, $val){
		$this->data[$key] = SetDBText($val);
	}

	public function SetDataNum($key, $val){
		$this->data[$key] = SetDBFloat($val);
	}

	public function AddWhere($str){
		$w = StrToSql(func_get_args());
		if($w !== false) $this->where[] = '('.$w.')';
	}

	public function UnsetWhere(){
		unset($this->where);
	}

	public function MultiAdd(){
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
	}

	public function MultiRun(){
		$sql = 'INSERT INTO ' . $this->table . '(' . $this->MultiNames . ') VALUES '.implode(',', $this->MultiValues);
		if($this->test){
			echo $sql;exit;
		}
		$this->result = SqlQuery($sql);
	}


	public function Run(){
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
				$minseq = SqlFetch('SELECT MIN(`'.$this->decrement.'`) as seq FROM `'.$this->table.'`'
					. ($this->where ? ' WHERE ' . implode(' AND ', $this->where) : ''));
				if(!$minseq){
					$this->result = false;
					return;
				}
				if(!strlen($minseq['seq'])) $minseq['seq'] = $this->MAXInt;

				$minseq['seq'] --;
				$sql = 'INSERT INTO ' . $this->table . '(' . $names . ', `' . $this->decrement . '`) VALUES (' . $values . ',' . $minseq['seq'] . ')';
				if($this->test){
					echo $sql;
					exit;
				}
				$r = SqlQuery($sql);
				$cnt --;
			}
			$this->result = $r ? true : false;
			$this->id = $minseq['seq'];
		}
		else{
			$sql = 'INSERT INTO ' . $this->table . '(' . $names . ') VALUES (' . $values . ')';
			if($this->test){
				echo $sql;
				exit;
			}

			$this->result = SqlQuery($sql);
			$this->id = mysqli_insert_id($GLOBALS['_BH_App']->_Conn);
		}

		BH_DB_Cache::DelPath($this->table);
	}
}

class BH_DB_Update{
	public $table = '';
	public $where = array();
	public $data = array();
	public $test = false;
	public $result = false;
	public $sort = '';
	public $message = '';

	public function  __construct($table = ''){
		$this->table = $table;
	}

	public function SetData($key, $val){
		$this->data[$key] = $val;
	}

	public function SetDataStr($key, $val){
		$this->data[$key] = SetDBText($val);
	}

	public function SetDataNum($key, $val){
		$this->data[$key] = SetDBFloat($val);
	}

	public function AddWhere($str){
		$w = StrToSql(func_get_args());
		if($w !== false) $this->where[] = '('.$w.')';
	}

	function Run(){
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
			$this->result = false;
			return;
		}

		$sql = 'UPDATE ' . $this->table . ' SET ' . $set . $where;
		if($this->sort) $sql .= ' ORDER BY ' . $this->sort;
		if($this->test){
			echo $sql;
			exit;
		}
		BH_DB_Cache::DelPath($this->table);
		$this->result = SqlQuery($sql);
	}

}

class BH_DB_Delete{
	public $table = '';
	private $where = array();

	public function  __construct($table = ''){
		$this->table = $table;
	}

	public function AddWhere($str){
		$w = StrToSql(func_get_args());
		if($w !== false) $this->where[] = '('.$w.')';
	}

	function Run(){
		$where = '';
		if(isset($this->where) && is_array($this->where) && sizeof($this->where)) $where = ' WHERE ' . implode(' AND ', $this->where);

		$sql = 'DELETE FROM '.$this->table.' '.$where;
		BH_DB_Cache::DelPath($this->table);
		return SqlQuery($sql);
	}
}