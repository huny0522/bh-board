<?php
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
		$line = preg_replace('/([^http:|https:]\s*)\/\/(.*)/', '$1', $line);
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
			}
			continue;
		}

		// 출력
		if(strlen($line) > 2 && substr($line, -2) == '++'){
			$selector []= substr($line, 0, strlen($line) - 2);
		}
		else if($line == '--'){
			array_pop($selector);

		}else{
			foreach($replaceVar as $k => $v){
				if(strpos($line, $k) !== false){
					if(substr($k, 0, 2) == '++'){
						$afterCSS .= $v;
						$line = preg_replace('/'.str_replace('+', '\+', $k).'\s*;\s*/', '', $line);
					}
					else if(substr($k, 0, 2) == '--'){
						$beforeCSS .= $v;
						$line = preg_replace('/'.str_replace('+', '\+', $k).'\s*;\s*/', '', $line);
					}
					else if(substr($k, 0, 1) == '@'){
						$line = preg_replace('/'.str_replace('@', '\@', $k).'\s*/', $v, $line);
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
				if(strlen($line)) $convCSS.= $slt.' '.$line.chr(10);
			}else{
				$convCSS.= $line.chr(10);
			}


			if($openis){
				if($end !== false){
					$openis = false;
					if(strlen($afterCSS)) $convCSS .= (strlen($slt) ? $slt.' ' : '' ).trim($inSelector).':after{'.$afterCSS.'}'.chr(10);
					$afterCSS = '';
					if(strlen($beforeCSS)) $convCSS .= (strlen($slt) ? $slt.' ' : '' ).trim($inSelector).':before{'.$beforeCSS.'}'.chr(10);
					$beforeCSS = '';
					$inSelector = '';
				}
			}
		}
	}

	$patterns = array(
		'/(-\S+-transition)\s*[:]\s*(.*?);\s*/',
		'/(-\S+-transform)\s*[:]\s*(.*?);\s*/',
		'/(-\S+-border-radius)\s*[:]\s*(.*?);\s*/',
		'/([^-][\s*|\{])(border-radius)\s*[:]\s*(.*?);/',
		'/([^-][\s*|\{])(transition)\s*[:]\s*(.*?);/',
		'/([^-][\s*|\{])(transform)\s*[:]\s*(.*?);/',
	);

	$replace = array(
		'',
		'',
		'',
		'$1border-radius:$3; -webkit-border-radius:$3; -moz-border-radius:$3;',
		'$1-moz-transition:$3; -webkit-transition:$3; -ms-transition:$3; -o-transition:$3; transition:$3;',
		'$1-moz-transform:$3; -webkit-transform:$3; -ms-transform:$3; -o-transform:$3; transform:$3;',
	);

	$convCSS = preg_replace($patterns, $replace, $convCSS);

	return $convCSS;
}
