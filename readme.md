# BHCss
- css processor with php
- css 셀렉터 그룹화
- css 치환 변수
- php를 사용하여 보다 자유로운 css 프로그래밍 
- bhcss.php 작성법은 https://github.com/huny0522/BHCss/blob/master/test/test.bhcss.php 참고

## 기본 사용법
	\BH\BHCss\BHCss::conv(__DIR__.'/../css/mycss.bhcss.php');
	
## 테스트
	test.bat
	
환경변수에 '%BHCSS_HOME%' 를 등록하거나 test.bat, bhcss.bat 파일안에 '%BHCSS_HOME%'을 제거 	
  
## 여백 및 주석 삭제
	\BH\BHCss\BHCss::setNL(false); 
	
## bhcss 파일 내의 인클루드
	BHCss::includeBHCss(__DIR__ . '/sub.bhcss.php');
	
## css 변경 시도 시 다른 파일을 렌더링
> 인클루드 된 파일의 경우 인클루드를 시도한 파일이 변경이 되도록 한다.

	if (BHCss::callParent(__FILE__, array('mycss.bhcss.php'))) {
   	   return;
   	}

## 확장자명 변경
	\BH\BHCss\BHCss::$fileExtension = '.mycss'; 

