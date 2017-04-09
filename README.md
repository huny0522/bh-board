#BH BOARD 소개
    MVC형태를 띄우면서 최대한 PHP의 속도를 내기 위해 만든 개인 프로젝트입니다.
    클래스 안에서도 외부의 함수들을 많이 포함하고 있습니다.
    약간의 수고로 List, View, Write(Modify) 타입의 간단한 예제 html파일을 생성할 수 있습니다.
    기능은 언제든 추가될 예정이며, 부족한 부분의 많은 의견과 지적, 도움을 받고 싶습니다.

##DB정보입력
/Common/db.info.php

##설치
    설치 및 admin 계정 생성
1. /Common/db.info.php 에서 정보 수정   
2. http://mydomain.com/Install

##주요 파일 및 디렉토리

- index.php 파일

    상수 선언과 파일 인클루드를 담당.

- Class 폴더

    BH BOARD의 핵심 클래스들.

- Common 폴더

    설정파일들이 위치하는 폴더.

- Common/db.info.php 파일

    DB연결 정보.

- Lib 폴더

    라이브러리 함수 모음.

- Controller 폴더

    MVC의 컨트롤러에 해당하는 파일들이 위치하는 폴더.

- Model 폴더

    MVC의 모델에 해당하는 파일들이 위치하는 폴더.

- Skin 폴더

    MVC의 뷰에 해당하는 html파일들이 위치하는 폴더.

- _HTML 폴더

    Skin 폴더에서 만들어진 파일을 변환하여 만들어지는 html 파일들의 폴더.

    개발자 모드에서만 자동으로 생성되고, 개발자 모드가 아닌 클라이언트는 Skin폴더가 아닌 이곳에서 파일을 불러온다. index.php에서 재정의 가능.

- Upload 폴더

    업로드 된 파일들이 위치하는 폴더.

    index.php에서 재정의 가능.



##PHP 임의 단축 변환 태그

    Lib/htmlConvert.php의 ReplaceHtmlFile함수를 변경하여 추가 변경 가능.

- *\<?p. $guide ?>* : 단순 출력

    변환 후 : *\<?php echo $guide; ?>*

- *\<?v. $guide ?>* : HTML태그를 문자로 표현하여 출력.

    변환 후 : *\<?php echo GetDBText($guide); ?>*

- *\<?vb. $guide ?>* : HTML태그를 문자로 표현하고 출력하고 줄바꿈태그를 추가.

    변환 후 : *\<?php echo nl2br(GetDBText($guide)); ?>*

- *\<?vr. $guide ?>* : HTML태그를 그대로 표현하고 스크립트를 제거하여 출력.

    변환 후 : *\<?php echo GetDBRaw($guide); ?>*

- *\<?fn. '' ?>* : &로 시작하는 항상 따라다니는 쿼리스트링

    변환 후 : *\<?php echo $this->GetFollowQuery(’’, '&'); ?>*
    
    this->SetFollowQuery에서 설정한 값.
    
    첫번째 인자는 제외값을 지정.

- *\<?fq. '' ?>* : ?로 시작하는 항상 따라다니는 쿼리스트링

    변환 후 : *\<?php echo $this->GetFollowQuery(‘’, '?'); ?>*
    
    this->SetFollowQuery에서 설정한 값.
    
    첫번째 인자는 제외값을 지정.

- *\<?a. 'action' ?>* : 액션만 변경한 URL을 출력

    변환 후 : *\<?php echo $this->URLAction('action'); ?>*

- *\<?c. ‘controller’ ?>* : 컨트롤을 변경한 URL을 출력

    변환 후 : *\<?php echo $this->URLBase(‘controller’); ?>*

- *\<?inc. $file ?>* : 파일 인클루드

    변환 후 : *\<?php if(_DEVELOPERIS === true) ReplaceHTMLFile(_SKINDIR.$file, _HTMLDIR.$file); require _HTMLDIR.$file; ?>*


##기본적인 상수
- _DIR, _CLASSDIR, _COMMONDIR, _SKINDIR, _HTMLDIR, _UPLOAD_DIRNAME, _UPLOAD_DIR : php 파일 서버경로 위치를 위한 상수
- _URL, _SKINURL, _ADMINURLNAME, _ADMINURL, _IMGURL, _UPLOAD_URL : 웹사이트 절대경로를 위한 상수
- _POSTIS : 서버요청방식 정의. POST일 경우 true.
- _AJAXIS : 서버요청이 AJAX인 경우 true.
- _DEVELOPER_IP : 개발자의 IP.
- _DEVELOPERIS : 개발자모드 스위치. 개발자 모드인 경우 _SKIN폴더에서 *.html 파일을 변환하여 _HTML 폴더에 php가 읽을 수 있는 정상적인 파일을 생성.
- _REMOVE_SPACE : html파일을 변환 할 때 최대한 빈칸을 제거해주는 스위치.

##라이브러리 함수
###SQL 함수 (Lib/common.php)
- SqlConnection : 배열인자[ hostName, userName,userPassword, dbName ]
- SqlFree
- SqlTableExists
- SqlQuery
- SqlNumRows
- SqlFetch : 인자로 쿼리 결과값이나 sql문자열 입력.

###기능함수 (Lib/common.php)
- my_escape_string : mysqli_real_escape_string
- Redirect : 페이지 이동명령(인자값 : 경로, 경고메시지)
- PhoneNumber : 숫자를 폰번호 형식으로 대쉬(-)를 붙여줌.
- KrDate : 날짜를 한국날짜로 변경.(인자값 : 날짜, 보여줄 옵션 ymdhis, 시간 전 표시)
- AutoLinkText : 텍스트에 링크 자동 걸기
- OptionAreaNumber : 지역번호를 option 태그로 가져오기.
- OptionPhoneFirstNumber : 폰번호를 option 태그로 가져오기.
- OptionEmailAddress : 이메일 뒤 주소를 option 태그로 가져오기.
- SelectOption : 배열을 option 태그로 변환.
- StringCut : 문자열 자르기.
- GetLastDay : 해당 윌의 마지막 날 가져오기.
- ToInt : 숫자로 변환
- ToFloat : 소수로 변환
- SetDBTrimText : sql문장을 위해 좌우 여백을 제거한 따옴표로 감싸는 문자를 반환.
- SetDBText : sql문장을 위해 따옴표로 감싸는 문자를 반환.
- SetDBInt : 숫자로 변환. 문자가 포함되어 있을 시 오류.
- SetDBFloat : 소수로 변환. 문자가 포함되어 있을 시 오류.
- GetDBText : html태그를 문자로 표현한 문자열로 변환.
- GetDBRaw : html태그를 그대로 표현하나 스크립트를 제거한 문자열로 변환.
- toBase : 62진법으로 변경.
- to10 : 10진법으로 변경.

###파일 관련 함수 (Lib/FileUpload.php)
- RandomFileName : 랜덤파일명 생성.

- FileUploadArray : 배열로 업로드한 파일 처리.

    인자값
    - $files : 파일 글로벌 변수
    - $possible_ext : 업로드 가능 확장자(배열)
    - $path : 업로드 파일이 위치할 폴더(ex : /data/)

- FileUpload : 파일 업로드 처리.

    인자값
    - $files : 파일 글로벌 변수
    - $possible_ext : 업로드 가능 확장자(배열)
    - $path : 업로드 파일이 위치할 폴더(ex : /data/)

- Thumbnail : 이미지 사이즈 변경.
    
    인자값
    - $source : 파일경로
    - $thumb : 저장경로
    - $width : 넓이값
    - $height : 높이값(기본값 0)

##sql 라이브러리 클래스
###BH_DB_Get
    생성자 인수로 테이블명을 입력.
    ex. $get = new BH_DB_Get(‘my_table’)

- public $table : 테이블명.
- public $test : true 설정 시 쿼리문을 출력.
- function AddWhere($str) : ‘where’ 구문을 만듭니다. 여러 번 호출하여 ‘and’ 로 묶어줍니다.
- function AddHaving($str) : ‘having’ 구문을 만듭니다.
- function SetKey($keys) : sql의 키값을 지정합니다. string값이나 string배열값이 들어갈 수 있습니다.
- function AddKey($keys) : sql의 키값을 추가합니다. string값이나 string배열값이 들어갈 수 있습니다.
- function Get() : mysqli_fetch_assoc를 실행한 결과를 반환합니다.

###BH_DB_GetList
    생성자 인수로 테이블명을 입력.
    ex. $get = new BH_DB_GetList(‘my_table’)

- public $table : 테이블명.
- public $limit : ‘limit’ 구문을 생성.
- public $sort : ‘order by’ 구문을 생성.
- public $group : ‘group by’ 구문을 생성.
- public $test : true 설정 시 쿼리문을 출력.
- (결과값) public $result : 결과 성공여부.
- (결과값) public $data : 결과물의 배열. DrawRows()를 실행필요.
- function AddWhere($str) : ‘where’ 구문을 만듭니다. 여러 번 호출하여 ‘and’ 로 묶어줍니다.
- function AddHaving($str) : ‘having’ 구문을 만듭니다.
- function SetKey($keys) : sql의 키값을 지정합니다. string값이나 string배열값이 들어갈 수 있습니다.
- function AddKey($keys) : sql의 키값을 추가합니다. string값이나 string배열값이 들어갈 수 있습니다.
- function Run() : mysqli_query를 실행.
- function Get() : mysqli_fetch_assoc를 실행한 결과를 반환합니다. Run()이 실행되지 않았을 경우 자동으로 실행.
- function DrawRows() : mysqli_fetch_assoc를 실행한 모든 결과를 $data에 등록합니다.
- function GetRows() : $data를 반환합니다.

###BH_DB_GetListWithPage
    생성자 인수로 테이블명을 입력.
    ex. $get = new BH_DB_GetListWithPage(‘my_table’)

- public $table : 테이블명.
- public $articleCount : 페이지별로 가져올 데이터 개수.
- public $page : 페이지번호.
- public $pageCount : 보여줄 페이지 개수(default : 10).
- public $pageUrl : 페이지링크에 들어갈 url
- public $countKey : 전체 게시물 수를 카운트할 때의 키.
- public $limit : ‘limit’ 구문을 생성.
- public $sort : ‘order by’ 구문을 생성.
- public $group : ‘group by’ 구문을 생성.
- public $test : true 설정 시 쿼리문을 출력.
- (결과값) public $result : 결과 성공여부.
- (결과값) public $totalRecord : 전체 게시물 수.
- (결과값) public $beginNum : 현재페이지 게시물의 상단 번호.
- (결과값) public $pageHtml : 페이지 html 결과.
- (결과값) public $data : 결과물의 배열. DrawRows()를 실행필요.
- function AddWhere($str) : ‘where’ 구문을 만듭니다. 여러 번 호출하여 ‘and’ 로 묶어줍니다.
- function AddHaving($str) : ‘having’ 구문을 만듭니다.
- function SetKey($keys) : sql의 키값을 지정합니다. string값이나 string배열값이 들어갈 수 있습니다.
- function AddKey($keys) : sql의 키값을 추가합니다. string값이나 string배열값이 들어갈 수 있습니다.
- function Run() : mysqli_query를 실행.
- function Get() : mysqli_fetch_assoc를 실행한 결과를 반환합니다. Run()이 실행되지 않았을 경우 자동으로 실행.
- function DrawRows() : mysqli_fetch_assoc를 실행한 모든 결과를 $data에 등록합니다.
- function GetRows() : $data를 반환합니다.

###BH_DB_Insert
    생성자 인수로 테이블명을 입력.
    ex. $get = new BH_DB_Insert(‘my_table’)

- public $table : 테이블명.
- public $decrement : 역순으로 들어갈 키.
- public $MAXInt : 역순으로 들어갈 키값의 최고값.
- public $test : true 설정 시 쿼리문을 출력.
- (결과값) public $result : 결과 성공여부.
- (결과값) public $id : 삽입된 primary key의 키값.
- (결과값) public $message : 에러시의 메시지.
- function SetData($key, $value) : insert구문에 삽입할 키와 값을 추가.
- function AddWhere($str) : 역순으로 들어갈 키를 위해 ‘where’ 구문을 만듭니다. 여러 번 호출하여 ‘and’ 로 묶어줍니다.
- function Run() : mysqli_query를 실행.
- function MultiAdd() : insert 구문의 다중 삽입 추가.
- function MultiRun() : 다중 삽입 된 구문을 실행.

###BH_DB_Update
    생성자 인수로 테이블명을 입력.
    ex. $get = new BH_DB_Update(‘my_table’)

- public $table : 테이블명.
- public $test : true 설정 시 쿼리문을 출력.
- (결과값) public $result : 결과 성공여부.
- (결과값) public $message : 에러시의 메시지.
- function SetData($key, $value) : update구문에 삽입할 키와 값을 추가.
- function AddWhere($str) : ‘where’ 구문을 만듭니다. 여러 번 호출하여 ‘and’ 로 묶어줍니다.
- function Run() : mysqli_query를 실행.

##주요 클래스
###BH_Application 클래스
- public $Controller : 불러올 컨트롤러를 위한 값.
- public $Action : 불러올 컨트롤러의 메쏘드를 위한 값.
- public $ID : 메쏘드에서 사용할 키값.
- public $SubDir : 컨트롤러파일이나 스킨파일의 서브디렉토리.
- public $BaseUrl : 컨트롤러 이전의 서브디렉토리를 포함한 URL.
- public $CtrlUrl : 컨트롤러의 URL.
- public $TID : 게시판이나 컨텐츠, 댓글의 키.
- function run() : 실행.

###BH_Controller 클래스
- public $Controller : BH_Application 클래스의 변수와 동일
- public $Action : BH_Application 클래스의 변수와 동일
- public $ID : BH_Application 클래스의 변수와 동일
- public $TID : BH_Application 클래스의 변수와 동일
- public $Layout : 스킨 레이아웃 지정 변수.
- public $Html : 스킨 html 지정 변수.
- public $_Value : html에 사용을 위한 변수.
- public $_CF : BH_Common 클래스
- function __Init() : BH_Controller를 상속한 클래스에서 생성자용으로 사용.
- function SetFollowQuery(array $ar) : 항상 따라다니는 URL쿼리파라미터의 키를 설정.
- function GetFollowQuery($ar, $begin=’?’) : 항상 따라다니는 URL을 문자열로 반환.
    1. 첫번째 인자 : SetFollowQuery에서 설정한 키 중 제외할 키. 배열이나 콤마(,)로 구분하는 문자열.
    2. 두번째 인자 : 반환할 문자열의 앞에 나올 문자.
- function GetFollowQueryInput($ar) : 항상 따라다니는 URL을 input태그로 반환.
- function _View($model, $Data) : html을 출력.
    1. 첫번째 인자 : html에서 사용할 모델 클래스.
    2. 두번째 인자 : html에서 사용할 데이터.
- function _GetView($model, $Data) : 레이아웃을 제외한 html을 문자열로 반환.
- function JSAdd($js, $idx = 100) : script 경로 추가. 두번째 인자가 작을수록 먼저 출력.
- function JSPrint() : script구문 출력.
- function CSSAdd($css, $idx = 100) : 스타일시트 경로 추가. 두번째 인자가 작을수록 먼저 출력.
- function CSSPrint() : 스타일시트구문 출력.
- function URLAction($action) : 현재 컨트롤러에 $Action 값을 변경 후의 URL 반환.
- function URLBase($controller) : 현재 서브디렉토리에 $Controller 값을 변경 후 URL 반환.

###BH_Common
    공통적으로 사용할 임의의 함수를 위한 클래스.

###BH_ModelData
    모델의 데이터    
- public $Name : 데이터베이스의 키.
- public $Type : 값의 형식.
- public $Required = false : 필수 입력 여부.
- public $DisplayName : 출력표시이름.
- public $ModelErrorMsg : 정상적이지 못한 값일 경우의 에러 메시지.
- public $MinLength = false : 값의 최소 길이.
- public $MaxLength = false : 값의 최대 길이.
- public $MinValue = false : 값의 최소 값.(숫자의 경우)
- public $MaxValue = false : 값의 최대 값.(숫자일 경우)
- public $EnumValues : 열거형 값.
- public $Value : 데이터 값.
- public $DefaultValue : 값이 없을 경우의 기본 값.
- public $HtmlType : 태그형식.
- public $AutoDecrement = false : 현재 키의 자동 역순여부.
- public $ValueIsQuery = false : 값을 쿼리문으로 날릴 때 특수문자를 여과없이 내보낼지 여부.

###BH_Model
    모델 데이터를 담고 컨트롤.
- public $data : BH_ModelData 클래스 배열.
- public $table : 데이터들의 테이블명.
- $Key : 키명 배열.
- $Except : 제외 키.
- $Need : 필수 키.
- $errorMessage
- function __Init() : BH_Model을 상속한 클래스에서 생성자용으로 사용.
- function InitModelData($key, $type, $Required, $DisplayName) : $data에 BH_ModelData데이터를 추가생성.
- function SetPostValues() : POST로 넘어온 값으로 데이터의 값을 설정.
- function GetErrorMessage() : 데이터의 에러메세지들을 배열로 반환.
- function SetDBValues($Values) : 데이터를 설정.
- function GetValue($key) : 해당 키를 가진 데이터의 값을 반환.
- function SetValue($key, $v) : 해당 키를 가진 데이터의 값을 설정. (GetErrorMessage()를 통한 유효성 검사 가능)
- function SetQueryValue($key, $v) : 해당 키를 가진 테이터의 값을 sql쿼리문으로 설정.
- function AddExcept($ar) : 제외 키를 추가.
- function HtmlPrintEnum($Name, $Value) : BH_ModelData에서 해당 키의 문자열을 반환.
- function HtmlPrintLabel($Name, $HtmlAttribute = false) : 출력표시명을 label태그로 출력.
- function HtmlPrintInput($Name, $HtmlAttribute = false) : 해당되는 input, select, textarea태그를 출력.
- function DBInsert() : 가지고 있는 데이터를 DB에 등록.
- function DBUpdate() : 가지고 있는 데이터를 DB에 업데이트.
- function DBGet($keyData) : 키값 인자에 해당하는 데이터를 DB에서 가져와 설정.
- function DBDelete($keyData) : 키값 인자에 해당하는 데이터를 DB에서 삭제.

###BH_Router
    라우팅을 위한 클래스.

    BH_Router 클래스 router메쏘드에서 BH_Application 클래스의 인스턴스 변수를 설정.

##List, View, Write(Modify) html 생성방법.
    사용자 컨트롤러 __Init() 메쏘드에 아래와 같이 코드 작성 후 실행. 생성 확인 후 해당 코드 삭제.

    컨트롤러 클래스명이 TestContoller이고 ‘/skin/Test’ 디렉토리에 모델 클래스명이 ‘MemberModel’일 경우 

require _CLASSDIR.'/BH_HtmlCreate.class.php';

BH_HtmlCreate::Create('Test', 'Member');


