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