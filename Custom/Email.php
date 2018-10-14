<?php

namespace Custom;

use \BH_Application as App;
use \BH_Common as CM;
use Common\Mailer;
use \DB;

class Email
{
	public $path = '/Email/';
	private $mailer;
	private $layout = 'Layout';

	public static function GetInstance(){
		$res = new self();
		return $res;
	}

	public function __construct(){
		$this->mailer = new Mailer(_DEVELOPERIS === true ? 4 : 0);
		App::$Data['LogoUrl'] = _UPLOAD_URL.CM::Config('Default', 'EmailLogoUrl');
		App::$Data['HomeUrl'] = _DOMAIN;
	}

	public function &AddMail($email, $name){
		$this->mailer->AddEmail($email, $name);
		return $this;
	}

	public function &SetLayout($fileName){
		$this->layout = $fileName;
		return $this;
	}

	public function SendMailByDefault($subject, $body){
		App::$Data['subject'] = $subject;
		App::$Data['body'] = $body;
		$subject = '['.CM::Config('Default', 'SiteName').'] '.App::$Data['subject'];
		$this->SetMailer($subject, $this->SetBody('Default'));
		$this->mailer->Send();
	}

	public function SendMailByEmailCertification($mid, $name, $code){
		App::$Data['id'] = $mid;
		App::$Data['name'] = $name;
		App::$Data['code'] = $code;
		App::$Data['subject'] = '이메일 인증';
		$subject = '['.CM::Config('Default', 'SiteName').'] '.App::$Data['subject'];
		$this->SetMailer($subject, $this->SetBody('CertifyEmail'));
		$this->mailer->Send();
	}

	public function SendMailByFindPW($name, $id, $code){
		App::$Data['id'] = $id;
		App::$Data['code'] = $code;
		App::$Data['name'] = $name;
		App::$Data['subject'] = '계정 비밀번호 변경 코드';
		$subject = '['.CM::Config('Default', 'SiteName').'] '.App::$Data['subject'];
		$this->SetMailer($subject, $this->SetBody('FindPW'));
		$this->mailer->Send();
	}

	public function SendMailByFindID($name, $id){
		App::$Data['id'] = $id;
		App::$Data['name'] = $name;
		App::$Data['subject'] = '요청하신 아이디입니다.';
		$subject = '['.CM::Config('Default', 'SiteName').'] '.App::$Data['subject'];
		$this->SetMailer($subject, $this->SetBody('FindID'));
		$this->mailer->Send();
	}

	public function SendMailByAnswerAlarm($name, $url, $answerName, $subject, $content){
		App::$Data['name'] = $name;
		App::$Data['url'] = _DOMAIN . $url;
		App::$Data['answerName'] = $answerName;
		App::$Data['boardsubject'] = $subject;
		App::$Data['content'] = $content;
		App::$Data['subject'] = $name . '님께서 작성한 게시물에 답글이 등록되었습니다.';
		$subject = '['.CM::Config('Default', 'SiteName').'] '.App::$Data['subject'];
		$this->SetMailer($subject, $this->SetBody('AnswerAlarm'));
		$this->mailer->Send();
	}

	public function &SetMailer($subject, $body){
		$this->mailer->senderName = CM::Config('Default', 'EmailName') ? CM::Config('Default', 'EmailName') : 'mail@' . _DOMAIN;
		$this->mailer->senderMail = CM::Config('Default', 'SendEmail') ? CM::Config('Default', 'SendEmail') : 'mail@' . _DOMAIN;
		$this->mailer->subject = $subject;
		$this->mailer->body = $body;

		return $this->mailer;
	}

	public static function HtmlExtraConvert($html, $dir = '/Email/'){
		if(strlen($html) && file_exists(_SKINDIR.$dir.$html)){
			$convertHtml = _HTMLDIR.$dir.'convert.'.$html;

			// 파일이 수정되었거나 없으면 변환합니다.
			// {?LEFT(category) = 0000}
			$modifyIs = modifyFileTime(_SKINDIR.$dir.$html, 'mall_email');
			if(!file_exists($convertHtml) || $modifyIs){
				if(!file_exists(_HTMLDIR.$dir) && !is_dir(_HTMLDIR.$dir)){
					@mkdir(_HTMLDIR.$dir, 0777, true);
				}

				$body = file_get_contents(_SKINDIR.$dir.$html);

				$patterns = array(
					'/\<\?.*?\?\>/i',
					'/\<\!--.*?--\>/is',
					'/\{\@LOOP\.([a-zA-Z0-9_]+?)\}/',
					'/\{[\@|\?]\}/',

					'/\{\=ITEM\.([a-zA-Z0-9_]+?)\}/',
					'/\{\=NUM_ITEM\.([a-zA-Z0-9_]+?)\}/',
					'/\{\=INFO\.([a-zA-Z0-9_]+?)\}/',
					'/\{\=BODY\}/',

					'/\{\?\.([a-zA-Z0-9_]+?)\}/',
					'/\{\?\.([a-zA-Z0-9_]+?)\.([a-zA-Z0-9_]+?)\}/',

					'/\{\=DATA\.([a-zA-Z0-9_]+?)\}/',
					'/\{\=DATA\.([a-zA-Z0-9_]+?)\.([a-zA-Z0-9_]+?)\}/',

					'/\{\==DATA\.([a-zA-Z0-9_]+?)\}/',
					'/\{\==DATA\.([a-zA-Z0-9_]+?)\.([a-zA-Z0-9_]+?)\}/',

					'/\{\=NUM_DATA\.([a-zA-Z0-9_]+?)\}/',
					'/\{\=NUM_DATA\.([a-zA-Z0-9_]+?)\.([a-zA-Z0-9_]+?)\}/',

					'/<img\s*\!\s*(.*?)\s*src=\"(.*?)\"(.*?)>/is',
				);

				$replace = array(
					'',
					'',
					'<?php foreach(BH_Application::$Data[\'$1\'] as $item){ ?>',
					'<?php } ?>',

					'<?php echo GetDBText($item[\'$1\']) ?>',
					'<?php echo number_format($item[\'$1\']) ?>',
					'<?php echo BH_Common::Config(\'Default\',\'$1\') ?>',
					'<?php if(isset(BH_Application::$Data[\'body\'])) echo BH_Application::$Data[\'body\'] ?>',

					'<?php if(isset(BH_Application::$Data[\'$1\']) && BH_Application::$Data[\'$1\']){ ?>',
					'<?php if(isset(BH_Application::$Data[\'$1\'][\'$2\']) && BH_Application::$Data[\'$1\'][\'$2\']){ ?>',

					'<?php if(isset(BH_Application::$Data[\'$1\'])) echo GetDBText(BH_Application::$Data[\'$1\']) ?>',
					'<?php if(isset(BH_Application::$Data[\'$1\'][\'$2\'])) echo GetDBText(BH_Application::$Data[\'$1\'][\'$2\']) ?>',

					'<?php if(isset(BH_Application::$Data[\'$1\'])) echo BH_Application::$Data[\'$1\'] ?>',
					'<?php if(isset(BH_Application::$Data[\'$1\'][\'$2\'])) echo BH_Application::$Data[\'$1\'][\'$2\'] ?>',

					'<?php if(isset(BH_Application::$Data[\'$1\'])) echo number_format(GetDBText(BH_Application::$Data[\'$1\'])) ?>',
					'<?php if(isset(BH_Application::$Data[\'$1\'][\'$2\'])) echo number_format(GetDBText(BH_Application::$Data[\'$1\'][\'$2\'])) ?>',

					'<img $1 src="' . _DOMAIN . _URL . '$2" $3>',
				);
				file_put_contents($convertHtml, preg_replace($patterns, $replace, $body));
			}
			return $convertHtml;
		}
		else return false;
	}

	public function SetBody($html){
		if(!isset(App::$Data['body'])) App::$Data['body'] = '';
		$path = self::HtmlExtraConvert($html.'.html', $this->path);
		if($path !== false){
			ob_start();
			require $path;
			App::$Data['body'] = ob_get_clean();
		}
		$path = self::HtmlExtraConvert($this->layout.'.html', $this->path);
		if($path !== false){
			ob_start();
			require $path;
			App::$Data['body'] = ob_get_clean();
		}
		return App::$Data['body'];
	}
}