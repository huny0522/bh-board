<?php
if(_BH_ !== true) exit;
class _BH_Node{
	public $selector = false;
	public $data = false;
	/** @var _BH_Node */
	public $Parent = false;
	/** @var _BH_Node */
	public $Next = false;
	public function setChild(){
		$this->data = new _BH_Node();
		$this->data->Parent = &$this;
	}
	public function setNext(){
		$this->Next = new _BH_Node();
		$this->Next->Parent = &$this->Parent;
	}
}

$_CommonCssPath = _SKINDIR.'/css/_common.css2';
$_CommonCssData = '';

function BH_CSS($path){
	$at = array('@charset', '@import', '@namespace');
	$ex = explode('.', $path);
	$ext = strtolower(array_pop($ex));
	if($ext != 'css2'){
		echo 'CSS 확장자명은 \'.css2\'이어야 합니다.';
	}

	if(!strlen($GLOBALS['_CommonCssData']) && file_exists($GLOBALS['_CommonCssPath'])){
		$GLOBALS['_CommonCssData'] = file_get_contents($GLOBALS['_CommonCssPath']);
	}

	$f = chr(13).str_replace(chr(13), '', $GLOBALS['_CommonCssData'].chr(10).file_get_contents($path));
	$pattern = '/(\$.[^;}\n]+?)\s*{\s*(.+?)\s*\}/is';
	preg_match_all($pattern, $f, $matches);
	$replaceVar = array();
	foreach($matches[1] as $k => $v){
		$replaceVar[$v] = $matches[2][$k];
	}
	$f = preg_replace(array($pattern, '/\/\*(.*?)\*\//is'), '', $f);

	$pattern = '/[\n](\$\S+)\s+(.*?)\;/';
	preg_match_all($pattern, $f, $matches);
	foreach($matches[1] as $k => $v){
		$replaceVar[$v] = $matches[2][$k];
	}
	uksort($replaceVar, 'mySort');
	$f = preg_replace($pattern, '', $f);

	// 주석삭제
	$f = preg_replace('/(([^http:|https:|url\(|url\(\']|\n)\/\/|^\/\/)(.*)/', '', $f);

	$CssVar = new _BH_Node();
	$txt = '';
	$flen = strlen($f);
	$p = &$CssVar;

	for($i = 0; $i < $flen; $i++){
		foreach($at as $item){
			if(substr($f, $i, strlen($item)) == $item){
				$findEnd = strpos($f, ';', $i);
				if($findEnd !== false){
					if($p->data !== false){
						$p->setNext();
						$p = &$p->Next;
					}
					$p->data = substr($f, $i, $findEnd - $i + 1);
					$i = $findEnd + 1;
				}
				else{
					if($p->data !== false){
						$p->setNext();
						$p = &$p->Next;
					}
					$p->data = substr($f, $i, $flen - $i);
					$i = $flen;
				}
			}
		}
		if($i >= $flen) break;

		if($f[$i] == '{'){
			$findBegin = strpos($f, '{', $i+1);
			$findEnd = strpos($f, '}', $i);
			if($findEnd === false){
			}
			if($findBegin > $findEnd || $findBegin === false){
				if($p->data !== false){
					$p->setNext();
					$p = &$p->Next;
				}
				$p->selector = trim($txt);
				$p->data = substr($f, $i+1, $findEnd - $i -1);
				//echo $p->selector.' : ['.$p->data.']'.chr(10);
				$i = $findEnd;
			}else{
				if($p->data !== false){
					$p->setNext();
					$p = &$p->Next;
				}
				$p->selector = trim($txt);
				$p->setChild();
				$p = &$p->data;
				// $selector : child;
			}
			$txt = '';
		}else if($f[$i] == '}'){
			if($p->Parent !== false) $p = &$p->Parent;
		}else{
			$txt .= $f[$i];
		}
	}

	$f = convCssNode($CssVar, $replaceVar);


	$f = preg_replace(array(
		'/(-\S+-transition)\s*[:]\s*(.*?);\s*/',
		'/(-\S+-transform)\s*[:]\s*(.*?);\s*/',
		'/(-\S+-border-radius)\s*[:]\s*(.*?);\s*/',
		'/(-\S+-box-shadow)\s*[:]\s*(.*?);\s*/',
		'/(-\S+-box-sizing)\s*[:]\s*(.*?);\s*/',
		'/(-\S+-background-size)\s*[:]\s*(.*?);\s*/',
		'/(-\S+-text-overflow)\s*[:]\s*(.*?);\s*/',
		'/(\S+?)\s*[:]\s*\-(webkit\-|moz\-|o\-)(linear\-|radial\-)gradient\s*\((.*?)\)\s*;/',
	), '', $f);

	$patterns = array(
		'/(border-radius)\s*[:]\s*(.*?);/',
		'/(transition)\s*[:]\s*(.*?);/',
		'/(transform)\s*[:]\s*(.*?);/',
		'/(box-shadow)\s*[:]\s*(.*?);/',
		'/(box-sizing)\s*[:]\s*(.*?);/',
		'/(background-size)\s*[:]\s*(.*?);/',
		'/(text-overflow)\s*[:]\s*(.*?);/',
		'/(\S+?)\s*[:]\s*(linear\-|radial\-)gradient\s*\((.*?)\)\s*;/',
	);

	$replace = array(
		'border-radius:$2; -webkit-border-radius:$2; -moz-border-radius:$2;',
		'-moz-transition:$2; -webkit-transition:$2; -ms-transition:$2; -o-transition:$2; transition:$2;',
		'-moz-transform:$2; -webkit-transform:$2; -ms-transform:$2; -o-transform:$2; transform:$2;',
		'-webkit-box-shadow:$2; -moz-box-shadow:$2; box-shadow:$2;',
		'-webkit-box-sizing:$2; -moz-box-sizing:$2; box-sizing:$2;',
		'-webkit-background-size:$2; background-size:$2;',
		'-ms-text-overflow:$2; text-overflow:$2;',
		'$1:-webkit-$2gradient($3); $1:-moz-$2gradient($3); $1:-o-$2gradient($3); $1:$2gradient($3);',
	);

	$f = preg_replace($patterns, $replace, $f);

	return $f;
}

function convCssNode($node, $replaceVar, $group = array()){
	/** @var $node _BH_Node */
	$f = '';
	if(is_string($node->data)){

		// ---------------------------------------------------------------
		// 변환

		$after = '';
		$before = '';
		foreach($replaceVar as $k => $v){
			if(strpos($node->data, $k) !== false){
				if(substr($k, 0, 3) == '$--'){
					$before = $v;
					$node->data = preg_replace('/'.str_replace(array('$','-'), array('\$', '\-'), $k).'\s*;*\s*/', '', $node->data);
				}
				else if(substr($k, -2) == '--'){
					$after = $v;
					$node->data = preg_replace('/'.str_replace(array('$','-'), array('\$', '\-'), $k).'\s*;*\s*/', '', $node->data);
				}
				else if(substr($k, 0, 1) == '$'){
					//echo 'replace : '.$k.chr(10).'replace css : '.$node->data.chr(10); echo 'v : '.$v.chr(10);
					$temp1 = $temp2 = $v;
					if(substr($temp1, -1) != ';') $temp1 .= ';';
					if(substr($temp2, -1) != ':') $temp2 .= ':';
					$node->data = preg_replace('/\s*'.str_replace('$', '\$', $k).'\s*;/', ' '.$temp1, $node->data);
					$node->data = preg_replace('/\s*'.str_replace('$', '\$', $k).'\s*:/', ' '.$temp2, $node->data);
					$node->data = preg_replace('/'.str_replace('$', '\$', $k).'/', $v, $node->data);
				}
			}
		}

		if(is_array($group) && sizeof($group)){
			$groups = '';
			foreach($group as $k => $v){
				$v = trim($v);
				if(!$k) $groups = $v;
				else if(substr($v, 0, 2) == '++') $groups .= ' '.substr($v, 1);
				else $groups .= ($v[0] == '+' ? substr($v, 1) : ($v[0] == ':' ? '' : ' ').$v);
			}

			$s = explode(',', $node->selector);
			foreach($s as $k => &$v){
				$v = trim($v);
				if(!strlen($v)) $v = $groups;
				else if(substr($v, 0, 2) == '++') $v = $groups.' '.substr($v, 1);
				else $v = $groups.($v[0] == '+' ? substr($v, 1) : ($v[0] == ':' ? '' : ' ').$v);
			}
			$f .= implode(',', $s).'{'.$node->data.'}';
			if(strlen($after)){
				$temp = $s;
				foreach($temp as $k => $v) $temp[$k] = trim($v).':after';
				$f .= implode(',', $temp).'{'.$after.'}';
			}
			if(strlen($before)){
				$temp = $s;
				foreach($temp as $k => $v) $temp[$k] = trim($v).':before';
				$f .= implode(',', $temp).'{'.$before.'}';
			}
			$f .= chr(10);
		}
		else if(strlen($node->selector)){
			$f .= $node->selector.'{'.$node->data.'}';
			if(strlen($after)){
				$f .= $node->selector.':after'.'{'.$after.'}';
			}
			if(strlen($before)){
				$f .= $node->selector.':after'.'{'.$before.'}';
			}
			$f .= chr(10);
		}else{
			$f .= $node->data.chr(10);
		}

		// 변환
		// ---------------------------------------------------------------
		//echo implode(' ', $group).' : '.$node->selector . ' : ' .$node->data.chr(10);
	}else if($node->data !== false){
		$group2 = $group;
		if($node->selector[0] != '@'){
			$group2[]= $node->selector;
			$f .= convCssNode($node->data, $replaceVar, $group2);
		}else{
			$f .= $node->selector.'{'.chr(10);
			$f .= convCssNode($node->data, $replaceVar, $group2);
			$f .= '}'.chr(10);
		}
	}
	if($node->Next){
		$f .= convCssNode($node->Next, $replaceVar, $group);
	}
	return $f;
}

function mySort($key1, $key2) {
	$s1 = strlen($key1);
	$s2 = strlen($key2);
	if($s1 == $s2) return 0;

	return $s1 > $s2 ? -1 : 1;
}
