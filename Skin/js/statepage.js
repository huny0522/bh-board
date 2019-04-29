var StatePage = {
	_completeKeepAction : [],
	_completeAction : [],
	url : '',
	targetId : 'containerWrap',
	targetElement : null,
	isInit : false,

	/**
	 * 최초 실행
	 */
	Init : function(tid){
		StatePage.isInit = true;
		if(typeof(tid) === 'string') this.targetElement = document.getElementById(tid);
		else this.targetElement = document.getElementById(this.targetId);
		if(!this.targetElement){
			console.log('StatePage Warning : 페이지 컨테이너 객체가 존재하지 않습니다.');
			return;
		}

		// a 태그 기본 액션 설정
		$(document).on('click', 'a', StatePage._LinkAction);

		$(window).on('popstate', function(e) {
			var href = location.href;
			StatePage.Load(href, null, true);
		});

		StatePage.url = location.pathname;
	},

	/**
	 * 해당 주소로 선택된 객체의 페이지를 변경
	 *
	 * @param href
	 * @param obj
	 * @param noPush boolean(true : replaceState, false : pushState) default : false
	 * @public
	 */
	Load : function(href, obj, noPush){
		if(!StatePage.isInit){
			location.href = href;
			return;
		}
		var el = null;
		if(typeof(obj) === 'string') el = document.getElementById(obj);
		else if(typeof(obj) === 'object') el = obj;
		if(!el) el = StatePage.targetElement;

		var linkEl = document.createElement("a");
		linkEl.href = href;

		if(StatePage.url !== '' && StatePage.url === linkEl.pathname + linkEl.search) return;

		JCM.get(href, {hash : linkEl.hash}, function(html){
			$(el).html(html);
			StatePage.url = linkEl.pathname + linkEl.search;
			StatePage._CompleteAction();
			if(noPush) history.replaceState('', '', href);
			else history.pushState('', '', href);
		});
	},

	/**
	 * 페이지 완료 후 1회 실행할 함수를 추가
	 *
	 * @param func
	 * @public
	 */
	AddCompleteAction : function(func){
		StatePage._completeAction.push(func);
	},

	/**
	 * 페이지 완료 후 지속적으로 실행할 함수를 추가
	 *
	 * @param func
	 * @public
	 */
	AddCompleteKeepAction : function(func){
		StatePage._completeKeepAction.push(func);
	},

	/**
	 * a 태그 기본 액션
	 *
	 * @param e
	 * @private
	 */
	_LinkAction : function(e){
		if(!this.hasAttribute('href') || $(this).hasClass('button') || $(this).attr('href') === '#' || $(this).attr('href').substring(0, 11).toLowerCase() === 'javascript:') return;
		e.preventDefault();
		StatePage.Load(this.href, this.hasAttribute('data-target-id') ? document.getElementById($(this).attr('data-target-id')) : null);
	},

	/**
	 * 페이지 완료 후 함수들을 실행
	 *
	 * @private
	 */
	_CompleteAction :function(){
		for(var i = 0; i < StatePage._completeKeepAction.length; i++){
			if(typeof(StatePage._completeKeepAction[i]) === 'function') StatePage._completeKeepAction[i]();
		}
		for(var i = 0; i < StatePage._completeAction.length; i++){
			if(typeof(StatePage._completeAction[i]) === 'function') StatePage._completeAction[i]();
		}
		StatePage._completeAction = [];
	},
};

$(function(){
	StatePage._CompleteAction();
});