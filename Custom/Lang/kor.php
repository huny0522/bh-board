<?php
namespace Custom\Lang;

class kor
{
	public static function Lang(){
		return array(
			'MSG_SECRET_ARTICLE' =>'비밀글입니다.',
			'MSG_DELETED_ARTICLE' =>'삭제된 게시물입니다.',
			'MSG_DELETED_REPLY' =>'삭제된 댓글입니다.',
			'MSG_WRONG_CONNECTED' =>'잘못된 접근입니다.',
			'MSG_NO_ARTICLE' =>'존재하지 않는 게시물입니다.',
			'MSG_PAYMENT_WAIT' =>'입금대기중',
			'MSG_PAYMENT_FIN' =>'입금완료',
			'MSG_NO_AUTH' =>'권한이 없습니다.',
			'MSG_NEED_LOGIN' =>'로그인해주시기 바랍니다.',
			'MSG_COMPLETE_MODIFY' =>'수정되었습니다.',
			'MSG_COMPLETE_REGISTER' =>'등록되었습니다.',
			'MSG_COMPLETE_DELETE' =>'삭제되었습니다.',
			'MSG_WRONG_PASSWORD' =>'비밀번호가 일치하지 않습니다.',
			'MSG_NOT_MATCH_PASSWORD' =>'비밀번호가 일치하지 않습니다.',

			'MSG_IMPOSSIBLE_FILE' =>'등록 불가능한 파일입니다.',
			'MSG_FILE_TOO_BIG' =>'업로드한 파일이 제한용량보다 큽니다.(' . ini_get('upload_max_filesize') . ')',
			'MSG_UPLOAD_ERROR' =>'파일 등록 오류',

			// common
			'DEL' =>'삭제',
			'ADD' =>'추가',
			'DEL_FILE' =>'파일삭제',
			'REG_FILE' =>'파일등록',
			'ATTACH_FILE' =>'첨부파일',
			'REG_IMAGE' =>'이미지등록',
			'ANSWER' =>'답변',
			'ALL' =>'전체',
			'PARENT_CATEGORY' =>'상위',
			'RECOMMEND' =>'추천',
			'OPPOSE' =>'반대',
			'REPORT' =>'신고',
			'SCRAP' =>'스크랩',

			// BH_Model.class.php
			'MODEL_NOT_DEFINED_KEY' =>'{key} 키값이 정의되어 있지 않습니다.',
			'MODEL_NOT_DEFINED_ITEM' =>'{item} 항목이 정의되지 않았습니다.',
			'MODEL_NOT_MULTI_FILE_ITEM' =>'{item} 항목이 다중 파일 형식이 아닙니다.',
			'MODEL_WRONG_FILE_TYPE' =>'{item} 항목에 업로드 불가능한 파일을 등록하였습니다.',
			'MODEL_EXCEED_FILE_SIZE' =>'{item} 항목에 파일용량을 초과하였습니다.',
			'MODEL_DO_NOT_ARRAY' =>'{item} 항목에 배열데이터를 사입할 수 없습니다.',
			'MODEL_WRONG_PATH' =>'잘못된 파일경로가 탐지었습니다.',
			'MODEL_FILE_NOT_EXISTS' =>'업로드한 파일이 존재하지 않습니다.',
			'MODEL_ONLY_NUMBER' =>'{item} 항목은 숫자만 입력 가능합니다.',
			'MODEL_NEED_VALUE' =>'{item} 항목에 값이 필요합니다.',
			'MODEL_VALUE_WRONG_TYPE' =>'{item} 형식이 올바르지 않습니다.',
			'MODEL_ONLY_ENG_NUM' =>'{item} 항목은 영문과 숫자만 입력가능합니다.',
			'MODEL_ONLY_ENG_NUM_SPECIAL' =>'{item} 항목은 영문과 숫자, 특수문자만 입력가능합니다.',
			'MODEL_ONLY_ENG' =>'{item} 항목은 영문만 입력가능합니다.',
			'MODEL_OR_MORE' =>'{item} 항목에 {n}이상의 값을 입력하여 주세요.',
			'MODEL_OR_LESS' =>'{item} 항목에 {n}이하의 값을 입력하여 주세요.',
			'MODEL_OR_MORE_LENGTH' =>'{item} 항목은 {n}자 이상 입력하여 주세요.',
			'MODEL_OR_LESS_LENGTH' =>'{item} 항목은 {n}자 이하 입력하여 주세요.',
			'MODEL_REQUIRED' =>'{item} 항목은 필수항목입니다.',
			'MODEL_KEY_NOT_EXISTS' =>'키값이 존재하지 않습니다.',
			'MODEL_KEY_LENGTH_NOT_MATCH' =>'모델의 키의 길이와 인자값의 키의 길이가 동일하지 않습니다.',

			'C_MODEL_NO_PARAM' =>'존재하지 않는 환경설정값입니다.',
			'C_MODEL_MISSING_CODE_NAME' =>'환경설정의 코드명이 빠졌습니다.',

			'MODEL_OVER_FILE_NUMBER' =>'업로드 가능한 파일 수가 초과되었습니다.',

			// common.php
			'TXT_NOT_MEMBER' =>'비회원',
			'TXT_MEMBER' =>'일반회원',
			'TXT_NOT_MANAGER' =>'매니저',
			'TXT_ADMIN' =>'관리자',
			'TXT_SUPER_ADMIN' =>'최고관리자',
			'TXT_FILE_NOT_EXIST' =>'파일이 존재하지 않습니다.',
			'TXT_EMPTY_NUMBER' =>'숫자값이 비어있습니다.',
			'TXT_ONLY_NUMBER_NOT_CHARACTER' =>'숫자가 들아갈 항목에 문자가 들어갈 수 없습니다.',
			'TXT_ONLY_FLOAT_NOT_CHARACTER' =>'숫자(소수)가 들아갈 항목에 문자가 들어갈 수 없습니다.',

			'MODIFIED' =>'수정되었습니다.',
			'DELETED' =>'삭제되었습니다.',
			'NO_DATA' =>'등록된 자료가 없습니다.',

			// Board.php
			'NO_BOARD' =>'게시판이 존재하지 않습니다.',
			'COMPLETE_RESTORE' =>'복구되었습니다.',
			'SELECT_DELETE_POST' =>'삭제할 게시물을 선택하여 주세요.',
			'SELECT_RESTORE_POST' =>'복구할 게시물을 선택하여 주세요.',
			'SELECT_MOVE_POST' =>'이동할 게시물을 선택하여 주세요.',
			'SELECT_MOVE_BOARD' =>'이동할 게시판을 선택하여 주세요.',
			'SELECT_COPY_POST' =>'복사할 게시물을 선택하여 주세요.',
			'SELECT_COPY_BOARD' =>'복사할 게시판을 선택하여 주세요.',
			'POSSIBLE_LAST_POST' =>'마지막으로 본 게시물만 가능합니다.',
			'BLOCKED_USER_POST' =>'차단된 사용자의 글입니다.',
			'IS_SECRET_POST' =>'비밀글입니다.',
			'SECRET_POST' =>'비밀글',
			'NEW_POST' =>'새글',

			// Reply.php
			'REPLY_DELETED_POST' =>'삭제됨',

			// Contents.php
			'ERROR_INSERT' =>'삽입오류',
			'ERROR_DELETE' =>'삭제오류',

			// MyPage.php
			'INPUT_PASSWORD' =>'패스워드를 입력하여 주세요.',
			'ADMIN_NOT_MODIFY' =>'관리자는 관리자페이지에서 수정이 가능합니다.',
			'ERROR_BLOCK_USER' =>'차단 오류',
			'ERROR_CANCEL_BLOCK_USER' =>'차단취소 오류',
			'ADMIN_CANT_WITHDRAW' =>'관리자는 탈퇴가 불가능합니다.',
			'ERROR_WITHDRAW' =>'탈퇴 오류',
			'SUCCESS_WITHDRAW' =>'탈퇴되었습니다. 이용해 주셔서 감사합니다.',

			// Login.php
			'NEED_ID' =>'아이디를 입력하여 주세요.',
			'NEED_EMAIL' =>'이메일을 입력하여 주세요.',
			'NOT_MATCH_MEMBER' =>'일치하는 회원이 없습니다.',
			'NO_AUTH_EMAIL' =>'이메일 인증이 되지 않았습니다. 가입시 입력한 이메일을 확인해주세요.',
			'NOT_APPROVE_ID' =>'승인되지 않은 아이디입니다.',
			'ALREADY_EMAIL' =>'이미 사용중인 이메일입니다.',
			'ALREADY_ID' =>'이미 사용중인 아이디입니다.',
			'ALREADY_NICKNAME' =>'이미 사용중인 닉네임입니다.',
			'REGISTERED_AND_CHECK_EMAIL' =>'등록되었습니다. 이용하시려면 이메일 인증을 해주셔야합니다. 입력하신 이메일을 확인 바랍니다.',
			'SEND_PW_CHANGE_CODE' =>'해당메일({email})로 비밀번호 변경 코드를 발송하였습니다.',
			'NOT_MATCH_MEMBER_BY_INF' =>'해당 정보와 일치하는 회원이 없습니다.',
			'SEND_EMAIL_ID' =>'해당메일({email})로 아이디를 발송하였습니다.',
			'ID_NO_EXIST' =>'해당 아이디가 존재하지 않습니다.',
			'EXPIRED_CODE' =>'기간이 만료된 코드입니다.',
			'NOT_MATCH_PW_CHANGE_CODE' =>'비밀번호 변경 코드가 불일치합니다.',
			'PW_CHANGED' =>'비밀번호가 변경되었습니다.',
			'ERROR_PW_CHANGE' =>'비밀번호 변경 오류',
			'ACCOUNT_NO_REQUEST_CHANGE_PASSWORD' =>'해당 계정은 비밀번호 변경 요청이 없습니다.',
			'ID_OF_CODE_NOT_EXIST' =>'해당 코드의 아이디가 존재하지 않습니다.',
			'ALREADY_AUTH_ID' =>'이미 인증이 완료된 회원입니다.',
			'APPROVED' =>'승인되었습니다.',
			'ERROR_APPROVE_DB' =>'승인 DB 오류',
			'NOT_REGISTERED_AUTH_CODE' =>'이메일 인증코드가 등록되어 있지 않습니다.',

			// Message.php
			'WEEK_MINI_ARRAY' =>array('일', '월', '화', '수', '목', '금', '토'),
			'AM' =>'오전',
			'PM' =>'오후',
			'CANT_SEND_MSG_TO_SELF' =>'자기 자신에게 메세지를 보낼 수 없습니다.',
			'POST_FROM_BLOCKED_USER' =>'차단된 사용자의 글입니다.',
			'NOT_EXISTS_MEMBER' =>'해당 회원이 존재하지 않습니다.',
			'CANT_MODIFY_ALREADY_READ' =>'이미 읽은 페이지로 수정이 불가능합니다.',
			'NOT_FIND_MEMBER' =>'탈퇴하였거나 삭제된 회원입니다.',
			'BLOCKED_USER' =>'차단된 사용자입니다.',
			'INPUT_CONTENTS' =>'내용을 입력하여 주세요.',
			'ERROR_INSERT_DB' =>'DB 삽입 오류',
			'NO_MORE_POST' =>'더이상 데이터가 없습니다.',

			// BH_PDO.class.php
			'ERROR_DECREMENT_KEY' =>'최소설정값 생성 에러',
			'NO_REGISTER_DATA' =>'등록할 자료가 없습니다.',
			'NO_WHERE' =>'WHERE 구문이 없습니다.',

			// ArticleAction.php
			'MEMBER_NUM_HAS_NOT_NUM' =>'회원번호에는 숫자가 들어가야합니다.',
			'POST_NUM_HAS_NOT_NUM' =>'게시물번호에는 숫자가 들어가야합니다.',
			'DUPLICATE_CHECK_IS_REQUIRED' =>'중복체크가 필요합니다.',
			'CANT_CANCEL_AFTER_A_DAY' =>'하루가 지나 취소가 불가능합니다.',
			'ERROR_CANCEL_DB' =>'취소 DB 오류',
			'COMMENT_NO_READ' =>'댓글에는 \'읽음\'기능이 없습니다.',
			'COMMENT_NO_SCRAP' =>'댓글에는 \'스크랩\'기능이 없습니다.',
			'UNKNOWN_TYPE' =>'알수없는 타입입니다.',

			// MenuHelp.php
			'INACCESSIBLE_MENU' =>'접근이 불가능한 메뉴입니다.',
			'MENU_NOT_ACCESSIBLE_BY_RATING' =>'현재 등급으로 접근이 불가능한 메뉴입니다.',

			'PWD_USABLE_ENG_SPECIAL_NUMBER_CHAR' =>'비밀번호 항목은 영문과 숫자, 특수문자만 입력가능합니다.',
			'PWD_NEED_SPECIAL_CHAR' =>'비밀번호에 특수문자를 포함하여 입력하셔야합니다.',
			'PWD_NEED_ENG_CHAR' =>'비밀번호에 영문을 포함하여 입력하셔야합니다.',
			'PWD_NEED_NUMBER_CHAR' =>'비밀번호에 숫자를 포함하여 입력하셔야합니다.',
		);
	}
}

\BH_Application::$lang = kor::Lang();