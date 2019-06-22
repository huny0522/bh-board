if(typeof(window._CVIEW_LANG) === 'undefined') window._CVIEW_LANG = {};
if(typeof(window._CVIEW_LANG.recommendQ) === 'undefined') window._CVIEW_LANG.recommendQ = '추천하시겠습니까?';
if(typeof(window._CVIEW_LANG.cancelRecommendQ) === 'undefined') window._CVIEW_LANG.cancelRecommendQ = '추천을 취소하시겠습니까?';
if(typeof(window._CVIEW_LANG.opposeQ) === 'undefined') window._CVIEW_LANG.opposeQ = '반대하시겠습니까?';
if(typeof(window._CVIEW_LANG.calcelOpposeQ) === 'undefined') window._CVIEW_LANG.calcelOpposeQ = '반대를 취소하시겠습니까?';
if(typeof(window._CVIEW_LANG.scrapQ) === 'undefined') window._CVIEW_LANG.scrapQ = '스크랩하시겠습니까?';
if(typeof(window._CVIEW_LANG.calcelScrapQ) === 'undefined') window._CVIEW_LANG.calcelScrapQ = '스크랩을 취소하시겠습니까?';
if(typeof(window._CVIEW_LANG.wrongConnected) === 'undefined') window._CVIEW_LANG.wrongConnected = '잘못된 접근입니다.';
if(typeof(window._CVIEW_LANG.recommendedIt) === 'undefined') window._CVIEW_LANG.recommendedIt = '추천되었습니다.';
if(typeof(window._CVIEW_LANG.wasAgainstIt) === 'undefined') window._CVIEW_LANG.wasAgainstIt = '반대하였습니다.';
if(typeof(window._CVIEW_LANG.scrapedIt) === 'undefined') window._CVIEW_LANG.scrapedIt = '스크랩하였습니다.';
if(typeof(window._CVIEW_LANG.recommend) === 'undefined') window._CVIEW_LANG.recommend = '추천';
if(typeof(window._CVIEW_LANG.oppose) === 'undefined') window._CVIEW_LANG.oppose = '반대';
if(typeof(window._CVIEW_LANG.alreadyScratched) === 'undefined') window._CVIEW_LANG.alreadyScratched = '이미 스크랩했습니다.';
if(typeof(window._CVIEW_LANG.actionFailed) === 'undefined') window._CVIEW_LANG.actionFailed = '이 글을 {t1}했습니다.\n{t2}하시려면 {t1} 취소하시기 바랍니다.';
if(typeof(window._CVIEW_LANG.calceld) === 'undefined') window._CVIEW_LANG.calceld = '취소되었습니다.';

var ContentView = {
	EventInit : false,

	Init : function(){
		if(!this.EventInit){
			this.EventInit = true;
			$(document).on('click', 'a.contentActionBtn', ContentView.ContentAction);
		}
	},

	ContentAction : function(e){
		e.preventDefault();
		var obj = this;
		var message = '';
		var type = $(obj).attr('data-type');
		if(type == 'recommend'){
			message = !$(obj).hasClass('already') ? window._CVIEW_LANG.recommendQ : window._CVIEW_LANG.cancelRecommendQ;
		}
		else if(type == 'oppose'){
			message = !$(obj).hasClass('already') ? window._CVIEW_LANG.opposeQ : window._CVIEW_LANG.calcelOpposeQ;
		}
		else if(type == 'scrap'){
			message = !$(obj).hasClass('already') ? window._CVIEW_LANG.scrapQ : window._CVIEW_LANG.calcelScrapQ;
		}
		else{
			CMAlert(window._CVIEW_LANG.wrongConnected);
			return;
		}
		CMConfirm(message, function(){
			if($(obj).hasClass('already')) ContentView.ContentCancelAction(obj, type);
			else ContentView.ContentDoAction(obj, type);
		});
	},

	ContentDoAction : function(obj, type){
		JCM.postWithLoading(obj.href, {type : type}, function(data){
			$(obj).addClass('already');
			if(type == 'recommend'){
				CMAlert(window._CVIEW_LANG.recommendedIt);
			}
			else if(type == 'oppose'){
				CMAlert(window._CVIEW_LANG.wasAgainstIt);
			}
			else if(type == 'scrap'){
				CMAlert(window._CVIEW_LANG.scrapedIt);
			}
		}, function(data){
			var msg = '';
			var msg2 = '';
			if(type == 'recommend') msg2 = window._CVIEW_LANG.recommend;
			else if(type == 'oppose') msg2 = window._CVIEW_LANG.oppose;

			if(data == 'recommend') msg = window._CVIEW_LANG.recommend;
			else if(data == 'oppose') msg = window._CVIEW_LANG.oppose;
			else if(data == 'scrap') CMAlert(window._CVIEW_LANG.alreadyScratched);

			if(msg !== '') CMAlert(window._CVIEW_LANG.actionFailed.replace(/\{t1\}/g, msg).replace(/\{t2\}/g, msg2));
		});
	},

	ContentCancelAction : function(obj, type){
		JCM.postWithLoading($(obj).attr('data-cancel-href'), {type : type}, function(data){
			$(obj).removeClass('already');
			CMAlert(window._CVIEW_LANG.calceld);
		}, function(){
		});
	},

};
