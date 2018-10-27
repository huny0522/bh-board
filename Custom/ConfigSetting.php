<?php
class _ConfigMap
{
	/**
	 * @return ConfigDefault
	 */
	public function Def(){
		return ConfigDefault::GetInstance();
	}

	/**
	 * @return ConfigSystem
	 */
	public function Sys(){
		return ConfigSystem::GetInstance();
	}

	/**
	 * @return ConfigPrivacyText
	 */
	public function Privacy(){
		return ConfigPrivacyText::GetInstance();
	}

	/**
	 * @return ConfigEmailCollector
	 */
	public function EmailCol(){
		return ConfigEmailCollector::GetInstance();
	}

	/**
	 * @return ConfigTermsText
	 */
	public function TermsText(){
		return ConfigTermsText::GetInstance();
	}
}

BH_Application::$cfg = new _ConfigMap();

/**
 * @property _CfgData lat
 * @property _CfgData lng
 * @property _CfgData company
 * @property _CfgData siteName
 * @property _CfgData zipCode
 * @property _CfgData address1
 * @property _CfgData address2
 * @property _CfgData ceo
 * @property _CfgData email
 * @property _CfgData tel
 * @property _CfgData fax
 * @property _CfgData bNumber
 * @property _CfgData oNumber
 * @property _CfgData privacyTel
 * @property _CfgData privacyEmail
 * @property _CfgData csTel
 * @property _CfgData csTelTime
 * @property _CfgData logoUrl
 * @property _CfgData instagramToken
 * @property _CfgData instagramRUri
 * @property _CfgData instagramCID
 * @property _CfgData naverSKey
 * @property _CfgData naverClientId
 * @property _CfgData emailName
 * @property _CfgData sendEmail
 * @property _CfgData emailCer
 * @property _CfgData joinApprove
 * @property _CfgData mailIdAddrSelection
 * @property _CfgData useMailId
 * @property _CfgData htmlEditor
 * @property _CfgData addHead
 * @property _CfgData newIconDay
 * @property _CfgData copyright
 * @property _CfgData faviconPng
 * @property _CfgData noImg
 * @property _CfgData emailLogoUrl
 * @property _CfgData mobileLogoUrl
 * @property _CfgData footLogoUrl
 */
class ConfigDefault extends _ConfigModel
{
	protected function __construct(){
		$this->_code = 'Default';
		// 기본
		$this->lat = _CfgData::GetInstance('lat');
		$this->lng = _CfgData::GetInstance('lng');
		$this->company = _CfgData::GetInstance('company')->SetTitle('회사명');
		$this->siteName = _CfgData::GetInstance('siteName')->SetTitle('사이트명');
		$this->zipCode = _CfgData::GetInstance('zipCode')->SetTitle('우편번호');
		$this->address1 = _CfgData::GetInstance('address1')->SetTitle('주소');
		$this->address2 = _CfgData::GetInstance('address2')->SetTitle('상세주소');
		$this->ceo = _CfgData::GetInstance('ceo')->SetTitle('대표자명');
		$this->email = _CfgData::GetInstance('email')->SetTitle('이메일');
		$this->tel = _CfgData::GetInstance('tel')->SetTitle('연락처');
		$this->fax = _CfgData::GetInstance('fax')->SetTitle('팩스');
		$this->bNumber = _CfgData::GetInstance('bNumber')->SetTitle('사업자등록번호');
		$this->oNumber = _CfgData::GetInstance('oNumber')->SetTitle('통신판매업 신고번호');
		$this->privacyTel = _CfgData::GetInstance('privacyTel')->SetTitle('개인정보책임자 연락처');
		$this->privacyEmail = _CfgData::GetInstance('privacyEmail')->SetTitle('개인정보책임자 이메일');
		$this->csTel = _CfgData::GetInstance('csTel')->SetTitle('통합고객센터');
		$this->csTelTime = _CfgData::GetInstance('csTelTime')->SetTitle('통합고객센터 시간')->SetType(HTMLType::TEXTAREA);
		$this->logoUrl = _CfgData::GetInstance('logoUrl')->SetTitle('로고')->SetType(HTMLType::FILE_IMAGE);
		$this->footLogoUrl = _CfgData::GetInstance('footLogoUrl')->SetTitle('하단로고')->SetType(HTMLType::FILE_IMAGE);
		$this->mobileLogoUrl = _CfgData::GetInstance('mobileLogoUrl')->SetTitle('모바일 로고')->SetType(HTMLType::FILE_IMAGE);
		$this->emailLogoUrl = _CfgData::GetInstance('emailLogoUrl')->SetTitle('이메일 상단로고')->SetType(HTMLType::FILE_IMAGE);
		$this->noImg = _CfgData::GetInstance('noImg')->SetTitle('이미지 없음')->SetType(HTMLType::FILE_IMAGE);
		$this->faviconPng = _CfgData::GetInstance('faviconPng')->SetTitle('Favicon')->SetType(HTMLType::FILE_IMAGE);
		$this->copyright = _CfgData::GetInstance('copyright')->SetTitle('COPYRIGHT');
		$this->newIconDay = _CfgData::GetInstance('newIconDay')->SetTitle('새글 표시기간')->SetDefaultValue(1);
		$this->addHead = _CfgData::GetInstance('addHead')->SetTitle('추가 HEAD')->SetType(HTMLType::TEXTAREA);
		$this->htmlEditor = _CfgData::GetInstance('htmlEditor')->SetTitle('HTML 에디터')->SetType(HTMLType::RADIO)->SetEnumValues(array('smarteditor2' => 'SmartEditor2', 'tinymce' => 'TinyMCE'))->SetDefaultValue('smarteditor2');
		$this->useMailId = _CfgData::GetInstance('useMailId')->SetTitle('메일아이디 사용')->SetType(HTMLType::CHECKBOX)->SetEnumValues(array('y' => '사용'));
		$this->mailIdAddrSelection = _CfgData::GetInstance('mailIdAddrSelection')->SetTitle('메일주소 선택 표시')->SetType(HTMLType::CHECKBOX)->SetEnumValues(array('y' => '메일주소 선택 표시'));
		$this->joinApprove = _CfgData::GetInstance('joinApprove')->SetTitle('가입 즉시 사용')->SetType(HTMLType::RADIO)->SetEnumValues(array('n' => '가입 후 승인 필요', 'y' => '가입즉시 로그인 가능'))->SetDefaultValue('y');
		$this->emailCer = _CfgData::GetInstance('emailCer')->SetTitle('이메일 인증 사용')->SetType(HTMLType::RADIO)->SetEnumValues(array('y' => '이메일 인증 사용', 'n' => '이메일 인증 사용안함'))->SetDefaultValue('y');
		$this->sendEmail = _CfgData::GetInstance('sendEmail')->SetTitle('발송메일주소');
		$this->emailName = _CfgData::GetInstance('emailName')->SetTitle('발송메일표시명');
		$this->naverClientId = _CfgData::GetInstance('naverClientId')->SetTitle('네이버 CLIENT ID');
		$this->naverSKey = _CfgData::GetInstance('naverSKey')->SetTitle('네이버 SECRET KEY');
		$this->instagramCID = _CfgData::GetInstance('instagramCID')->SetTitle('인스타그램 ID');
		$this->instagramRUri = _CfgData::GetInstance('instagramRUri')->SetTitle('인스타그램 Redirect Uri');
		$this->instagramToken = _CfgData::GetInstance('instagramToken')->SetTitle('인스타그램 Access Token');

		$this->GetFileSetting();
	}

}


/**
 * @property _CfgData menuCache
 * @property _CfgData refresh
 */
class ConfigSystem extends _ConfigModel
{

	protected function __construct(){
		$this->_code = 'System';
		// 기본
		$this->refresh = _CfgData::GetInstance('refresh');
		$this->menuCache = _CfgData::GetInstance('menuCache');

		$this->GetFileSetting();
	}
}

/**
 * 개인정보보호정책
 * 내용이 많을 수 있어서 별도로 저장
 *
 * @property _CfgData text
 */
class ConfigPrivacyText extends _ConfigModel
{

	protected function __construct(){
		$this->_code = 'PrivacyText';
		// 기본
		$this->text = _CfgData::GetInstance('text')->SetTitle('개인정보보호정책');

		$this->GetFileSetting();
	}
}

/**
 * 이용약관
 * 내용이 많을 수 있어서 별도로 저장
 *
 * @property _CfgData text
 */
class ConfigTermsText extends _ConfigModel
{

	protected function __construct(){
		$this->_code = 'TermsText';
		// 기본
		$this->text = _CfgData::GetInstance('text')->SetTitle('이용약관');

		$this->GetFileSetting();
	}
}

/**
 * 이메일 수집 거부
 * 내용이 많을 수 있어서 별도로 저장
 *
 * @property _CfgData text
 */
class ConfigEmailCollector extends _ConfigModel
{

	protected function __construct(){
		$this->_code = 'EmailCollector';
		// 기본
		$this->text = _CfgData::GetInstance('text')->SetTitle('이메일 수집 거부');

		$this->GetFileSetting();
	}
}