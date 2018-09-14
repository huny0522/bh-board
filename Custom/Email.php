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
		$this->mailer = new Mailer();
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

	/**
	 * @param string $email
	 * @param string $code
	 * @return \BH_Result
	 */
	public function SendMailByRegCode($email, $code){
		$res = DB::InsertQryObj(TABLE_REGISTER_CODE)
			->SetDataStr('email', $email)
			->SetDataStr('code', $code)
			->SetData('reg_date', 'NOW()')
			->SetDataStr('reg_fin', 'n')
			->Run();

		if(!$res->result) return \BH_Result::Init(false, _DEVELOPERIS === true ? 'DB 생성 실패' : 'Error#601');

		App::$Data['email'] = $email;
		App::$Data['code'] = $code;
		App::$Data['subject'] = '회원가입 코드입니다.';
		$this->AddMail($email, $email);
		$subject = '['.CM::Config('Default', 'SiteName').'] '.App::$Data['subject'];
		$this->SetMailer($subject, $this->SetBody('RegCode'));
		$res = $this->mailer->Send();
		return \BH_Result::Init($res, $res ? '' : 'ERROR#603');
	}

	public function SendMailByOrder(&$order, &$orderItem){
		foreach($orderItem as $k => $v){
			$orderItem[$k]['opt_name'] = str_replace('|', ' > ', $v['opt_name']);
			$orderItem[$k]['image_s'] = _DOMAIN . _UPLOAD_URL . $orderItem[$k]['image_s'];
		}

		App::$Data['order'] = $order;
		App::$Data['orderItem'] = $orderItem;
		App::$Data['subject'] = '주문이 완료되었습니다.';

		App::$Data['order']['order_no'] = Mall::OrderNumber(App::$Data['order']['order_seq']);
		App::$Data['order']['order_step'] = \OrdStep::$Name[App::$Data['order']['step']];
		if(App::$Data['order']['settle_kind'] == 'pg_vbank'){
			App::$Data['order']['bank'] = App::$Data['order']['pg_finance_nm'].' '.
				App::$Data['order']['pg_bank_number'].
				' (예금주 : '.App::$Data['order']['pg_deposit_nm'].')';
		}
		else{
			App::$Data['order']['bank'] = CM::Config('Default', 'bank').' '.CM::Config('Default', 'bankNumber').'(예금주:'.CM::Config('Default', 'bankName').')';
		}

		$subject = '['.CM::Config('Default', 'SiteName').'] '.App::$Data['subject'];
		$this->SetMailer($subject, $this->SetBody('Order'));
		$this->AddMail($order['from_email'], $order['from_name'])->mailer->Send();
	}

	public function &SetMailer($subject, $body){
		$this->mailer->senderName = CM::Config('Default', 'EmailName') ? CM::Config('Default', 'EmailName') : 'mail@' . _DOMAIN;
		$this->mailer->senderMail = CM::Config('Default', 'SendEmail') ? CM::Config('Default', 'Email') : 'mail@' . _DOMAIN;
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