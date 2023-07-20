<?php
namespace PHPSTORM_META
{
	registerArgumentsSet('MenuHelpSetSubmenuFunc', function($category){
		/** @var string $category */
	});
	expectedArguments(\Common\MenuHelp::SetSubmenuFunc(), 0, argumentsSet('MenuHelpSetSubmenuFunc'));

	registerArgumentsSet('MenuHelpSetPrepareFunc', function(&$menu, &$opt){
		/** @var array $menu = ['category' => '', 'title' => '', 'controller' => ''] */
		/** @var array $opt = ['tagName' => '', 'attr' => '', 'class' => '', 'activeClass' => '', 'linkWrapTag' => '', 'isShow' => true] */
	});
	expectedArguments(\Common\MenuHelp::SetPrepareFunc(), 0, argumentsSet('MenuHelpSetPrepareFunc'));

	registerArgumentsSet('MenuHelpSetTitleFunc', function($category){
		/** @var array $category */
	});
	expectedArguments(\Common\MenuHelp::SetTitleFunc(), 0, argumentsSet('MenuHelpSetTitleFunc'));
}

namespace
{
	function mv($str){/** @var BH_Model $Model */ echo $Model->data[$str]->Safe();}
	function mt($str){/** @var BH_Model $Model */ echo $Model->data[$str]->displayName;}
	function mp($str){/** @var BH_Model $Model */ echo $Model->data[$str]->value;}
	function mvn($str){/** @var BH_Model $Model */ echo $Model->data[$str]->Num();}
	function mvr($str){/** @var BH_Model $Model */ echo $Model->data[$str]->SafeRaw();}
	function mvb($str){/** @var BH_Model $Model */ echo $Model->data[$str]->SafeBr();}
	function minp($str){/** @var BH_Model $Model */ echo $Model->HTMLPrintInput($str);}
	function menum($str){/** @var BH_Model $Model */ echo $Model->HTMLPrintEnum($str);}

	define('p', ''); // echo ([code]);
	define('e', ''); // echo ([code]);
	define('ifp', ''); // if(isset([code])) echo ([code]);

	define('v', ''); // echo GetDBText([code]);
	define('ifv', ''); // if(isset([code])) echo GetDBText([code]);

	define('vr', ''); // echo GetDBRaw([code]);
	define('ifvr', ''); // if(isset([code])) echo GetDBRaw([code]);

	define('vb', ''); // echo nl2br(GetDBText([code]));
	define('ifvb', ''); // if(isset([code])) echo nl2br(GetDBText([code]));

	define('vstag', ''); // echo strip_tags(GetDBText([code]));
	define('vbstag', ''); // echo nl2br(GetDBText(strip_tags([code])));

	define('c', ''); // echo BH_Application::URLBase([code]);
	define('a', ''); // echo BH_Application::URLAction([code]);

	define('fqq', ''); // echo \BH_Application::GetFollowQuery([code], \'?\');
	define('fqn', ''); // echo \BH_Application::GetFollowQuery([code], \'&\');

	define('inc', ''); // ReplaceHTMLFile(\Paths::DirOfSkin().[code], \Paths::DirOfHtml().[code]); require \Paths::DirOfHtml().[code];
	define('module', ''); // $_module_path = ViewModuleGetPath([code]); foreach($_module_path as $mp){if($mp['type'] === 'js') echo '<script>' . PHP_EOL; if($mp['id'] === 'js') $moduleId = $mp['id']; require $mp['path']; if($mp['type'] === 'js') echo PHP_EOL . '</script>' . PHP_EOL;}


	class _SessionMember extends BHSession
	{
		public BHSession $muid;
		public BHSession $level;
		public BHSession $mid;
		public BHSession $seq;
	}
}
