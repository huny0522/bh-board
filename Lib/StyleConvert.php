<?php
if(_BH_ !== true) exit;

function BH_CSS($path){
	$ex = explode('.', $path);
	$ext = strtolower(array_pop($ex));
	if($ext != 'css2'){
		echo 'CSS 확장자명은 \'.css2\'이어야 합니다.';
	}
	$f = file_get_contents($path);
	$f = str_replace(chr(13), '', $f);
	$explode_f = explode(chr(10), $f);
	$selector = array();
	$openis = false;

	$convCSS = '';
	$afterCSS = '';
	$beforeCSS = '';

	$replaceVar = array();
	$inSelector = '';
	$commentis = false;
	foreach($explode_f as $line){
		if(substr(trim($line), 0, 2) == '//') continue;
		$line = preg_replace('/([^http:|https:|url(|url(\'])\/\/(.*)/', '$1', $line);
		$line = preg_replace('/\s+/', ' ', $line);
		$line = trim($line);

		$start = strpos($line, '{');
		$end = strpos($line, '}');
		$c_start = strpos($line, '/*');
		$c_end = strpos($line, '*/');

		if($c_start !== false){
			$commentis = true;
			if($c_end !== false){
				$commentis = false;
			}
			continue;
		}

		if($c_end !== false){
			$commentis = false;
			continue;
		}

		// 값넣기
		if($commentis){
			$lineExplode = explode(' ', $line);
			if(sizeof($lineExplode) > 2 && strtolower(trim($lineExplode[0])) == '@var'){
				$k = $lineExplode[1];
				array_splice($lineExplode, 0, 2);
				$replaceVar[$k] = implode(' ', $lineExplode);
				uksort($replaceVar, 'mySort');
			}
			continue;
		}

		// 출력
		if(strlen($line) > 3 && substr($line, -3) == '++{'){
			$selector []= substr($line, 0, strlen($line) - 3);
		}
		else if($line == '++}'){
			array_pop($selector);

		}else{
			foreach($replaceVar as $k => $v){
				if(strpos($line, $k) !== false){
					if(substr($k, 0, 2) == '~+'){
						$afterCSS .= $v;
						$line = preg_replace('/'.str_replace('+', '\+', $k).'\s*;\s*/', '', $line);
					}
					else if(substr($k, 0, 2) == '~-'){
						$beforeCSS .= $v;
						$line = preg_replace('/'.str_replace('+', '\+', $k).'\s*;\s*/', '', $line);
					}
					else if(substr($k, 0, 1) == '@'){
						$temp1 = $temp2 = $v;
						if(substr($temp1, -1) != ';') $temp1 .= ';';
						if(substr($temp2, -1) != ':') $temp2 .= ':';
						$line = preg_replace('/\s*'.str_replace('@', '\@', $k).'\s*;/', ' '.$temp1, $line);
						$line = preg_replace('/\s*'.str_replace('@', '\@', $k).'\s*:/', ' '.$temp2, $line);
						$line = preg_replace('/'.str_replace('@', '\@', $k).'/', $v, $line);
					}
					else if(substr($k, 0, 1) == '~'){
						if(substr($v, -1) != ';') $v .= ';';
						$line = preg_replace('/\s*'.str_replace('~', '\~', $k).'\s*;\s*/', ' '.$v, $line);
					}
				}
			}

			if(!$openis){
				if($start !== false){
					$openis = true;
					if(substr($line, 0, 1) != '@') $inSelector .= substr($line, 0, $start);
				}else{
					if(substr($line, 0, 1) != '@') $inSelector .= $line;
				}
				$slt = trim(implode(' ', $selector));
				$space = substr($line, 0, 1) == ':' || substr($line, 0, 1) == '{' ? '' : ' ';
				if(substr($line, 0, 1) == '+'){
					$space = '';
					$line = substr($line, 1, strlen($line));
				}
				if(strlen($line)) $convCSS.= (strlen($slt) ? $slt.$space : '').$line.chr(10);
			}else{
				$convCSS.= $line.chr(10);
			}


			if($openis){
				if($end !== false){
					$openis = false;
					$slt = trim(implode(' ', $selector));
					$inSelector = trim($inSelector);
					if(strlen($afterCSS)) $convCSS .= $slt.(strlen($slt) && strlen($inSelector) ? ' ' : '' ).$inSelector.':after{'.$afterCSS.'}'.chr(10);
					$afterCSS = '';
					if(strlen($beforeCSS)) $convCSS .= $slt.(strlen($slt) && strlen($inSelector) ? ' ' : '' ).$inSelector.':before{'.$beforeCSS.'}'.chr(10);
					$beforeCSS = '';
					$inSelector = '';
				}
			}
		}
	}
	$convCSS = preg_replace(array(
		'/(-\S+-transition)\s*[:]\s*(.*?);\s*/',
		'/(-\S+-transform)\s*[:]\s*(.*?);\s*/',
		'/(-\S+-border-radius)\s*[:]\s*(.*?);\s*/',
		'/(-\S+-box-shadow)\s*[:]\s*(.*?);\s*/',
		'/(-\S+-box-sizing)\s*[:]\s*(.*?);\s*/',
		'/(-\S+-background-size)\s*[:]\s*(.*?);\s*/',
		'/(-\S+-text-overflow)\s*[:]\s*(.*?);\s*/',
	), '', $convCSS);

	$patterns = array(
		'/(border-radius)\s*[:]\s*(.*?);/',
		'/(transition)\s*[:]\s*(.*?);/',
		'/(transform)\s*[:]\s*(.*?);/',
		'/(box-shadow)\s*[:]\s*(.*?);/',
		'/(box-sizing)\s*[:]\s*(.*?);/',
		'/(background-size)\s*[:]\s*(.*?);/',
		'/(text-overflow)\s*[:]\s*(.*?);/',
	);

	$replace = array(
		'border-radius:$2; -webkit-border-radius:$2; -moz-border-radius:$2;',
		'-moz-transition:$2; -webkit-transition:$2; -ms-transition:$2; -o-transition:$2; transition:$2;',
		'-moz-transform:$2; -webkit-transform:$2; -ms-transform:$2; -o-transform:$2; transform:$2;',
		'-webkit-box-shadow:$2; -moz-box-shadow:$2; box-shadow:$2;',
		'-webkit-box-sizing:$2; -moz-box-sizing:$2; box-sizing:$2;',
		'-webkit-background-size:$2; background-size:$2;',
		'-ms-text-overflow:$2; text-overflow:$2;',
	);

	$convCSS = preg_replace($patterns, $replace, $convCSS);

	return $convCSS;
}


function mySort($key1, $key2) {
	$s1 = strlen($key1);
	$s2 = strlen($key2);
	if($s1 == $s2) return 0;

	return $s1 > $s2 ? -1 : 1;
}