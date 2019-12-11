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

	/**
	 * @return ConfigTexts
	 */
	public function Texts(){
		return ConfigTexts::GetInstance();
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
 * @property _CfgData googleServiceAccount
 * @property _CfgData firebaseWebConfig
 */
class ConfigDefault extends _ConfigModel
{
	protected function __Init(){
		$this->_code = 'Default';
		// 기본
		$this->lat = _CfgData::GetInstance();
		$this->lng = _CfgData::GetInstance();
		$this->company = _CfgData::GetInstance()->SetTitle('회사명');
		$this->siteName = _CfgData::GetInstance()->SetTitle('사이트명');
		$this->zipCode = _CfgData::GetInstance()->SetTitle('우편번호');
		$this->address1 = _CfgData::GetInstance()->SetTitle('주소');
		$this->address2 = _CfgData::GetInstance()->SetTitle('상세주소');
		$this->ceo = _CfgData::GetInstance()->SetTitle('대표자명');
		$this->email = _CfgData::GetInstance()->SetTitle('이메일');
		$this->tel = _CfgData::GetInstance()->SetTitle('연락처');
		$this->fax = _CfgData::GetInstance()->SetTitle('팩스');
		$this->bNumber = _CfgData::GetInstance()->SetTitle('사업자등록번호');
		$this->oNumber = _CfgData::GetInstance()->SetTitle('통신판매업 신고번호');
		$this->privacyTel = _CfgData::GetInstance()->SetTitle('개인정보책임자 연락처');
		$this->privacyEmail = _CfgData::GetInstance()->SetTitle('개인정보책임자 이메일');
		$this->csTel = _CfgData::GetInstance()->SetTitle('통합고객센터');
		$this->csTelTime = _CfgData::GetInstance()->SetTitle('통합고객센터 시간')->SetType(HTMLType::TEXTAREA);
		$this->logoUrl = _CfgData::GetInstance()->SetTitle('로고')->SetType(HTMLType::FILE_IMAGE);
		$this->footLogoUrl = _CfgData::GetInstance()->SetTitle('하단로고')->SetType(HTMLType::FILE_IMAGE);
		$this->mobileLogoUrl = _CfgData::GetInstance()->SetTitle('모바일 로고')->SetType(HTMLType::FILE_IMAGE);
		$this->emailLogoUrl = _CfgData::GetInstance()->SetTitle('이메일 상단로고')->SetType(HTMLType::FILE_IMAGE);
		$this->noImg = _CfgData::GetInstance()->SetTitle('이미지 없음')->SetType(HTMLType::FILE_IMAGE);
		$this->faviconPng = _CfgData::GetInstance()->SetTitle('Favicon')->SetType(HTMLType::FILE_IMAGE);
		$this->copyright = _CfgData::GetInstance()->SetTitle('COPYRIGHT');
		$this->newIconDay = _CfgData::GetInstance()->SetTitle('새글 표시기간')->SetDefaultValue(1);
		$this->addHead = _CfgData::GetInstance()->SetTitle('추가 HEAD')->SetType(HTMLType::TEXTAREA);
		$this->htmlEditor = _CfgData::GetInstance()->SetTitle('HTML 에디터')->SetType(HTMLType::RADIO)->SetEnumValues(array('smarteditor2' => 'SmartEditor2', 'tinymce' => 'TinyMCE'))->SetDefaultValue('smarteditor2');
		$this->useMailId = _CfgData::GetInstance()->SetTitle('메일아이디 사용')->SetType(HTMLType::CHECKBOX)->SetEnumValues(array('y' => '사용'));
		$this->mailIdAddrSelection = _CfgData::GetInstance()->SetTitle('메일주소 선택 표시')->SetType(HTMLType::CHECKBOX)->SetEnumValues(array('y' => '메일주소 선택 표시'));
		$this->joinApprove = _CfgData::GetInstance()->SetTitle('가입 즉시 사용')->SetType(HTMLType::RADIO)->SetEnumValues(array('n' => '가입 후 승인 필요', 'y' => '가입즉시 로그인 가능'))->SetDefaultValue('y');
		$this->emailCer = _CfgData::GetInstance()->SetTitle('이메일 인증 사용')->SetType(HTMLType::RADIO)->SetEnumValues(array('y' => '이메일 인증 사용', 'n' => '이메일 인증 사용안함'))->SetDefaultValue('y');
		$this->sendEmail = _CfgData::GetInstance()->SetTitle('발송메일주소');
		$this->emailName = _CfgData::GetInstance()->SetTitle('발송메일표시명');
		$this->naverClientId = _CfgData::GetInstance()->SetTitle('네이버 CLIENT ID');
		$this->naverSKey = _CfgData::GetInstance()->SetTitle('네이버 SECRET KEY');
		$this->instagramCID = _CfgData::GetInstance()->SetTitle('인스타그램 ID');
		$this->instagramRUri = _CfgData::GetInstance()->SetTitle('인스타그램 Redirect Uri');
		$this->instagramToken = _CfgData::GetInstance()->SetTitle('인스타그램 Access Token');
		$this->firebaseWebConfig = _CfgData::GetInstance()->SetTitle('firebase 웹설정 JSON')->SetType(HTMLType::TEXTAREA);
		$this->googleServiceAccount = _CfgData::GetInstance()->SetTitle('구글 서비스 계정 JSON')->SetType(HTMLType::TEXTAREA);

		$this->GetFileSetting();
	}

}


/**
 * @property _CfgData menuCache
 * @property _CfgData refresh
 */
class ConfigSystem extends _ConfigModel
{

	protected function __Init(){
		$this->_code = 'System';
		// 기본
		$this->refresh = _CfgData::GetInstance();
		$this->menuCache = _CfgData::GetInstance();

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

	protected function __Init(){
		$this->_code = 'PrivacyText';
		// 기본
		$this->text = _CfgData::GetInstance()->SetTitle('개인정보보호정책');

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

	protected function __Init(){
		$this->_code = 'TermsText';
		// 기본
		$this->text = _CfgData::GetInstance()->SetTitle('이용약관');

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

	protected function __Init(){
		$this->_code = 'EmailCollector';
		// 기본
		$this->text = _CfgData::GetInstance()->SetTitle('이메일 수집 거부');

		$this->GetFileSetting();
	}
}

/**
 * 이메일 수집 거부
 * 내용이 많을 수 있어서 별도로 저장
 *
 * @property _CfgData pointGuide
 * @property _CfgData privacyText
 * @property _CfgData termsText
 * @property _CfgData emailCollector
 */
class ConfigTexts extends _ConfigModel
{

	protected function __Init(){
		$this->_code = 'Texts';
		// 기본
		$this->emailCollector = _CfgData::GetInstance()->SetTitle('이메일 수집 거부')->SetIsSeparate(true);
		$this->termsText = _CfgData::GetInstance()->SetTitle('이용약관')->SetIsSeparate(true);
		$this->privacyText = _CfgData::GetInstance()->SetTitle('개인정보취급방침')->SetIsSeparate(true);
		$this->pointGuide = _CfgData::GetInstance()->SetTitle('포인트 적립안내')->SetIsSeparate(true);

		$this->GetFileSetting();
	}
}