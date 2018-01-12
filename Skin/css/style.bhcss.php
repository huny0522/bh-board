<?php
if(BHCSS !== true) exit;

include 'common.incss.php';
?>

<style>
	/* ---------------------------------------------------------------------
	*	style.css List
	*	0. Common
	*	1. Header & Footer
	*	2. Contents Common
	*	3. Login
	*	4. Register
	*	5. Mypage
	* --------------------------------------------------------------------- */

	/* ---------------------------------------------------------------------
	*	0. Common
	* --------------------------------------------------------------------- */
	html{ font-size:10px;}
	body{ font-size:14px; background:#fff; color:#222;}
	.sBtn{ display:inline-block; padding:0 10px; margin:0 1px; line-height:21px; background:#ccc; color:#222; border-radius:3px;}
	.mBtn{ display:inline-block; width:100px; margin:0 3px; text-align:center; line-height:30px; background:#ccc; color:#222; border-radius:3px;}
	.bBtn{ display:inline-block; width:200px; margin:0 5px; text-align:center; line-height:40px; background:#ccc; color:#222; border-radius:5px;}
	button.sBtn{ line-height:1.1; height:21px;}
	button.mBtn{ line-height:1.1; height:30px;}
	button.bBtn{ line-height:1.1; height:40px;}
	a.btn1, button.btn1{ background:#999; color:#fff;}
	a.btn2, button.btn2{ background:#333; color:#fff;}
	.left{ text-align:left !important;}
	.right{ text-align:right !important;}
	.center{ text-align:center !important;}

	.leftBoardSearch, .left_btn{float:left; margin-top:20px; padding-bottom:20px;}
	.rightBoardSearch, .right_btn{ float:right; margin-top:20px; padding-bottom:20px;}
	.bottomBtn{ margin-top:20px; text-align:center;}
	.moreViewBtn{ margin-top:20px; $tac;}
	.moreViewBtn a{$dib; width:300px; border:1px solid #ccc; line-height:30px; border-radius:5px; background:#f8f8f8;}

	.left_btn + .table, .right_btn + table{ clear:both;}
	.right_btn + .paging, .left_btn + .paging{ padding-top:0;}

	p.alert{ padding-bottom:20px; color:#e00;}

	.modalConfirm{ position:fixed; top:0; left:0; z-index:100; width:100%; height:100%; background:#999; background:rgba(0,0,0,0.1);}
	.modalConfirm form{ position:absolute; top:50%; left:50%; width:300px; padding:20px; margin:-80px 0 0 -150px; border:2px solid #333; background:#fff; text-align:center;}
	.modalConfirm p{ font-weight:700; padding-bottom:15px;}
	.modalConfirm .sPopBtns{ padding-top:1.0rem;}

	.nothing{ text-align:center; color:#888; padding:20px 0;}
	/* ---------------------------------------------------------------------
	*	1. Header & Footer
	* --------------------------------------------------------------------- */
	#wrap{ width:100%; height:100%;}
	#header{ background:#fff; border-bottom:1px solid #ccc; box-shadow:0 2px 2px rgba(0,0,0,0.1);}
	#header_wrap{ position:relative; width:1024px; height:8.0rem; margin:0 auto; padding:0;}
	#header_wrap h1{ position:absolute; top:0; left:0; z-index:2; font-weight:800; font-size:1.8em; line-height:8.0rem; color:#555;}
	#header_wrap h1 i{ margin-right:10px;}
	#gnb{ position:absolute; top:0; right:0; z-index:2; font-size:0.85em; padding:0.7rem 1.5rem; background:#555 url('../images/bg02.png');
		  box-shadow:1px 1px 2px rgba(0,0,0,0.1); border-radius:0 0 6px 6px;}
	#gnb li{ display:inline-block; color:#fff; transition:0.7s;}
	#gnb li:hover{ color:#7bf;}
	#gnb li:before{ content:'|'; margin:0 10px; color:rgba(255,255,255,0.5);}
	#gnb li:first-child:before{ content:none;}


	/* ---------------------------------------------------------------------
	*	2. Contents
	* --------------------------------------------------------------------- */
	#container{ padding-bottom:50px;}
	#container_wrap{ position:relative; width:1024px; margin:0 auto; padding:0;}
	#container_wrap:after{ content:' '; display:block; clear:both;}

	.BH_Popup{ position:absolute; top:0; left:0; z-index:10; border:1px solid #888;}
	.BH_PopupContent{ overflow-y:auto; overflow-x:hidden;}
	.BH_PopupBtns{ line-height:21px; background:#333; color:white; font-size:12px;}
	.BH_PopupBtns a{cursor:pointer;}
	.BH_PopupBtns:after{ content:' '; display:block; clear:both;}
	.BH_PopupTodayClose{float:left; display:block; padding:5px 10px;}
	.BH_PopupClose{float:right; display:block; padding:5px 10px;}

	table.write{width:100%; table-layout:fixed;}
	table.write th{width:150px; padding:10px; border:1px solid #D3D6DF; border-width:1px 0; text-align:left; background:#F2F3F5;}
	table.write td{padding:10px; border:1px solid #D3D6DF; border-width:1px 0; text-align:left;}

	table.list{width:100%; table-layout:fixed; font-size:0.9em;}
	table.list th{width:100px; padding:5px 10px; border:1px solid #D3D6DF; border-width:1px 0; text-align:center; background:#F2F3F5;}
	table.list td{padding:10px; border:1px solid #D3D6DF; border-width:1px 0; text-align:center;}
	table.list a{text-decoration:underline;}
	table.list img{max-width:200px; max-height:100px;}

	table.view{width:100%; table-layout:fixed;}
	table.view th{width:150px; padding:10px; border:1px solid #D3D6DF; border-width:1px 0; text-align:left; background:#F2F3F5;}
	table.view td{padding:10px; border:1px solid #D3D6DF; border-width:1px 0; text-align:left;}

	.paging{clear:both; padding:20px; text-align:center;}
	.paging span, .paging a, .paging strong{display:inline-block; padding:0 10px; border:1px solid #D3D6DF; color:#D3D6DF; line-height:30px; height:30px; overflow:hidden; font-size:13px;}
	.paging a{color:inherit;}
	.paging strong{background:#6EA9AF; color:white; border:0; font-size:16px;}
	.paging .first, .paging .prev, .paging .prevp, .paging .nextp, .paging .next, .paging .last{width:30px;}
	.paging .first:before{content:'\f048'; display:block; height:100%; font-family:'FontAwesome';}
	.paging .prev:before{content:'\f100'; display:block; height:100%; font-family:'FontAwesome';}
	.paging .prevp:before{content:'\f104'; display:block; height:100%; font-family:'FontAwesome';}
	.paging .nextp:before{content:'\f105'; display:block; height:100%; font-family:'FontAwesome';}
	.paging .next:before{content:'\f101'; display:block; height:100%; font-family:'FontAwesome';}
	.paging .last:before{content:'\f051'; display:block; height:100%; font-family:'FontAwesome';}

	.Board{ padding-top:50px;}
	.Board h2{ margin-bottom:20px; font-size:1.8em;}

	.Board ul.category:after{ content:' '; display:block; clear:both;}
	.Board ul.category li{ float:left; padding:1.0rem; border:1px solid #ddd; border-bottom:0; margin-left:-1px; background:#eee;}
	.Board ul.category li.Active{ background:#fff;}

	.BoardView header{ border-top:2px solid #555; border-bottom:1px solid #ccc;}
	.BoardView header div{ padding:1.0rem; border-bottom:1px solid #ddd; background:#f4f4f4;}
	.BoardView header ul{ padding:1.0rem; font-size:0.87em;}
	.BoardView header ul:after{ content:' '; display:block; clear:both;}
	.BoardView header li{ float:right; margin-left:1.5rem;}
	.BoardView header li.mname{ float:left; margin-right:0;}
	.BoardView .contents{ padding:2.0rem 1.0rem;}
	.BoardView .contents img{max-width:100% !important;}

	#Reply{ padding-top:50px;}
	#replyWrite{ margin-bottom:20px; padding:10px; border:1px solid #ddd; background:#fafafa;}
	#replyWrite fieldset.user dl,
	#replyWrite fieldset.user dt,
	#replyWrite fieldset.user dd{ display:inline-block;}
	#replyWrite fieldset.text{ position:relative; padding-right:150px;}
	#replyWrite fieldset.text textarea{ height:100px; margin:0;}
	#replyWrite fieldset.text .btn{ position:absolute; top:0; right:0; width:140px; height:100%;}
	#replyWrite fieldset.text .btn button{ width:100%; height:100%; background:#555; font-size:1.8em; color:#fff; border-radius:0.5rem; text-align:center;}
	#replyWrite div.option{ padding:5px 0 0;}

	#replyListContents{ border-top:2px solid #555; font-size:0.90em;}
	#replyListContents article{ border-bottom:1px solid #ddd;}
	#replyListContents header{ padding:0.7rem; background:#f4f4f4;}
	#replyListContents .btns{float:right;}
	#replyListContents .btns a{ display:inline-block; padding:2px 5px; background:#555; color:#fff; font-size:0.9em; border-radius:2px;}
	#replyListContents .comment{ padding:1.0rem; line-height:1.5em;}
	#replyListContents .comment b{ color:#999; margin-right:10px;}

	.repLayer{ position:fixed; top:0; left:0; z-index:100; width:100%; height:100%; background:#999; background:rgba(0,0,0,0.1);}
	.repLayer form{ position:absolute; top:50%; left:50%; width:40.0rem; padding:2.0rem; margin:-8.0rem 0 0 -15.0rem; border:2px solid #333; background:#fff; text-align:center;}
	.repLayer div.btn{ padding-top:1.0rem;}
	.repLayer .targetContent{ text-align:left; font-size:0.9em;}
	.repLayer textarea{ height:8.0rem;}
	.repLayer fieldset.user{ text-align:left;}
	.repLayer fieldset.user dl, .repLayer fieldset.user dt, .repLayer fieldset.user dd{ display:inline-block;}
	.repLayer fieldset.user dd input{ width:100px;}
	.repLayer fieldset.pwd{ text-align:center;}
	.repLayer fieldset.pwd p{ font-weight:700; padding:1.5rem 0 0.5rem;}

	/* ---------------------------------------------------------------------
	*	3. Login
	* --------------------------------------------------------------------- */
	#contents.login2{ padding-top:20.0rem;}
	#login_wrap2{ width:48.0rem; height:29.0rem; padding:2.0rem; margin:0 auto; border:1px solid #ddd; background:#fff; box-shadow:2px 2px 3px rgba(0,0,0,0.2);}
	#login_wrap2 h2{ padding:1.0rem 2.0rem; font-size:1.8rem; background:#f4f4f4;}
	#login_wrap2 fieldset{ padding-top:2.0rem;}
	#login_wrap2 fieldset:after{ content:' '; display:block; clear:both;}
	#login_wrap2 label{ clear:left; float:left; display:block; width:10.0rem; padding-left:1.0rem; line-height:4.0rem; font-weight:700;}
	#login_wrap2 fieldset span{ float:left; display:block; line-height:4.0rem;}
	#login_wrap2 fieldset span input{ width:10.0rem;}
	#login_wrap2 fieldset span input[type=password]{ width:18.0rem;}
	#login_wrap2 #LoginConfirm{ padding-top:0.5rem;}
	#login_wrap2 #LoginConfirm button{ width:100%; margin:0; font-weight:700; font-size:1.6rem;}
	#login_wrap2 #LoginRemember{ padding:0.5rem 1.0rem; line-height:2.0rem;}
	#login_wrap2 #LoginRemember i{ display:inline-block; width:1.6rem; height:1.6rem; padding:0.2rem; margin-right:0.5rem; border:1px solid #999; border-radius:100%; vertical-align:middle;}
	#login_wrap2 #LoginRemember a.active i:before{ content:' '; display:block; width:100%; height:100%; background:#666; border-radius:100%;}
	#login_wrap2 #link a{ float:left; display:block; margin:1.5rem 1.0rem 0; font-size:1.3rem; text-decoration:underline;}
	#login_wrap2 #link a.regbtn{ float:right;}

	/* ---------------------------------------------------------------------
	*	4. Register
	* --------------------------------------------------------------------- */
	#contents.register{ padding-top:5.0rem;}
	#contents.register h2{ font-size:2.0rem;}
	#contents.register fieldset > .txt{ height:20.0rem; margin-top:1.0rem; border:1px solid #ccc; overflow-y:scroll;}
	#contents.register fieldset > p.chk{ padding:0.5rem; text-align:right;}
	#contents.registerForm{ padding-top:5.0rem;}
	#contents.registerForm h2{ padding-bottom:2.0rem; font-size:2.0rem;}

	/* ---------------------------------------------------------------------
	*	5. Mypage
	* --------------------------------------------------------------------- */
	#lnb{ float:left; width:20.0rem; padding-top:5.0rem; margin-right:3.0rem;}
	#lnb h2{ height:12.0rem; padding-top:4.8rem; border:1px solid #000; font-size:2.0rem; background:#333; text-align:center; color:#fff;}
	#lnb nav{ border:1px solid #ccc; border-width:0 1px;}
	#lnb nav li{ padding:1.5rem 1.0rem; border-bottom:1px solid #ccc;}
	#contents.Mypage{ float:left; width:79.4rem; padding-top:5.0rem;}

	section.MypageMain{ font-size:1.4rem;}
	#contents.Mypage h3{ padding:1.0rem 0; font-size:1.6rem;}
	section.MypageMain p.nothing{ padding:3.0rem; border:1px solid #ccc;}
	section.MypageMain{ margin-bottom:4.0rem;}
	#PasswordForm{ padding-top:5.0rem; text-align:center;}
	#PasswordForm form{ width:40.0rem; padding:3.0rem 0; margin:0 auto; border:1px solid #ccc; box-shadow:2px 2px 3px rgba(0,0,0,0.2);}
	#PasswordForm p{ margin-bottom:3.0rem; color:#888; font-weight:700;}

	#WithdrawForm{ padding:2.0rem 0;}
	#WithdrawForm p{ padding:2.0rem; background:#eee;}
	#WithdrawForm ul{ padding:3.0rem;}
	#WithdrawForm li{ padding:0.5rem 0;}
	#WithdrawForm .reason_etc{ display:table; width:100%;}
	#WithdrawForm .reason_etc label{ display:table-cell; width:10%; margin:0;}
	#WithdrawForm .reason_etc span{ display:table-cell; width:90%;}
	#WithdrawForm .reason_etc span textarea{ margin:0;}

</style>
