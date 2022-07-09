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

	/**
	 * @var \ConfigDefault
	 */
	private $defCfg;

	public static function GetInstance(){
		$res = new self();
		return $res;
	}

	public function __construct(){
		$this->defCfg = App::$cfg->Def();
		$this->mailer = new Mailer(\BHG::$isDeveloper === true ? 4 : 0);
		App::$data['LogoUrl'] = \Paths::UrlOfUpload().$this->defCfg->emailLogoUrl->value;
		App::$data['HomeUrl'] = _DOMAIN;
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
		App::$data['subject'] = $subject;
		App::$data['body'] = $body;
		$subject = '['.$this->defCfg->siteName->value.'] '.App::$data['subject'];
		$this->SetMailer($subject, $this->SetBody('Default'));
		$this->mailer->Send();
	}

	public function SendMailByEmailCertification($mid, $name, $code){
		App::$data['id'] = $mid;
		App::$data['name'] = $name;
		App::$data['code'] = $code;
		App::$data['subject'] = '이메일 인증';
		$subject = '['.$this->defCfg->siteName->value.'] '.App::$data['subject'];
		$this->SetMailer($subject, $this->SetBody('CertifyEmail'));
		$this->mailer->Send();
	}

	public function SendMailByFindPW($name, $id, $code){
		App::$data['id'] = $id;
		App::$data['code'] = $code;
		App::$data['name'] = $name;
		App::$data['subject'] = '계정 비밀번호 변경 코드';
		$subject = '['.$this->defCfg->siteName->value.'] '.App::$data['subject'];
		$this->SetMailer($subject, $this->SetBody('FindPW'));
		$this->mailer->Send();
	}

	public function SendMailByFindID($name, $id){
		App::$data['id'] = $id;
		App::$data['name'] = $name;
		App::$data['subject'] = '요청하신 아이디입니다.';
		$subject = '['.$this->defCfg->siteName->value.'] '.App::$data['subject'];
		$this->SetMailer($subject, $this->SetBody('FindID'));
		$this->mailer->Send();
	}

	public function SendMailByAnswerAlarm($name, $url, $answerName, $subject, $content){
		App::$data['name'] = $name;
		App::$data['url'] = _DOMAIN . $url;
		App::$data['answerName'] = $answerName;
		App::$data['boardsubject'] = $subject;
		App::$data['content'] = $content;
		App::$data['subject'] = $name . '님께서 작성한 게시물에 답글이 등록되었습니다.';
		$subject = '['.$this->defCfg->siteName->value.'] '.App::$data['subject'];
		$this->SetMailer($subject, $this->SetBody('AnswerAlarm'));
		$this->mailer->Send();
	}

	public function &SetMailer($subject, $body){
		$this->mailer->senderName = $this->defCfg->emailName->value ? $this->defCfg->emailName->value : 'mail@' . $_SERVER['HTTP_HOST'];
		$this->mailer->senderMail = $this->defCfg->sendEmail->value ? $this->defCfg->sendEmail->value : 'mail@' . $_SERVER['HTTP_HOST'];
		$this->mailer->subject = $subject;
		$this->mailer->body = $body;

		return $this->mailer;
	}

	public static function HtmlExtraConvert($html, $dir = '/Email/'){
		if(strlen($html) && file_exists(\Paths::DirOfSkin().$dir.$html)){
			$convertHtml = \Paths::DirOfHtml().$dir.'convert.'.$html;

			// 파일이 수정되었거나 없으면 변환합니다.
			// {?LEFT(category) = 0000}
			// $modifyIs = modifyFileTime(\Paths::DirOfSkin().$dir.$html, 'mall_email');
			// if(!file_exists($convertHtml) || $modifyIs){
				if(!file_exists(\Paths::DirOfHtml().$dir) && !is_dir(\Paths::DirOfHtml().$dir)){
					@mkdir(\Paths::DirOfHtml().$dir, 0777, true);
				}

				$body = file_get_contents(\Paths::DirOfSkin().$dir.$html);

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
					'<?php foreach(BH_Application::$data[\'$1\'] as $item){ ?>',
					'<?php } ?>',

					'<?php echo GetDBText($item[\'$1\']) ?>',
					'<?php echo number_format($item[\'$1\']) ?>',
					'<?php echo BH_Common::Config(\'Default\',\'$1\') ?>',
					'<?php if(isset(BH_Application::$data[\'body\'])) echo BH_Application::$data[\'body\'] ?>',

					'<?php if(isset(BH_Application::$data[\'$1\']) && BH_Application::$data[\'$1\']){ ?>',
					'<?php if(isset(BH_Application::$data[\'$1\'][\'$2\']) && BH_Application::$data[\'$1\'][\'$2\']){ ?>',

					'<?php if(isset(BH_Application::$data[\'$1\'])) echo GetDBText(BH_Application::$data[\'$1\']) ?>',
					'<?php if(isset(BH_Application::$data[\'$1\'][\'$2\'])) echo GetDBText(BH_Application::$data[\'$1\'][\'$2\']) ?>',

					'<?php if(isset(BH_Application::$data[\'$1\'])) echo BH_Application::$data[\'$1\'] ?>',
					'<?php if(isset(BH_Application::$data[\'$1\'][\'$2\'])) echo BH_Application::$data[\'$1\'][\'$2\'] ?>',

					'<?php if(isset(BH_Application::$data[\'$1\'])) echo number_format(GetDBText(BH_Application::$data[\'$1\'])) ?>',
					'<?php if(isset(BH_Application::$data[\'$1\'][\'$2\'])) echo number_format(GetDBText(BH_Application::$data[\'$1\'][\'$2\'])) ?>',

					'<img $1 src="' . _DOMAIN . \Paths::Url() . '$2" $3>',
				);
				file_put_contents($convertHtml, preg_replace($patterns, $replace, $body));
			//}
			return $convertHtml;
		}
		else return false;
	}

	public function SetBody($html){
		if(!isset(App::$data['body'])) App::$data['body'] = '';
		$path = self::HtmlExtraConvert($html.'.html', $this->path);
		if($path !== false){
			ob_start();
			require $path;
			App::$data['body'] = ob_get_clean();
		}
		$path = self::HtmlExtraConvert($this->layout.'.html', $this->path);
		if($path !== false){
			ob_start();
			require $path;
			App::$data['body'] = ob_get_clean();
		}
		return App::$data['body'];
	}
}