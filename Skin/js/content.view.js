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
			message = !$(obj).hasClass('already') ? '추천하시겠습니까?' : '추천을 취소하시겠습니까?';
		}
		else if(type == 'oppose'){
			message = !$(obj).hasClass('already') ? '반대하시겠습니까?' : '반대를 취소하시겠습니까?';
		}
		else if(type == 'scrap'){
			message = !$(obj).hasClass('already') ? '스크랩하시겠습니까?' : '스크랩을 취소하시겠습니까?';
		}
		else{
			CMAlert('잘못된 접근입니다.');
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
				CMAlert('추천되었습니다.');
			}
			else if(type == 'oppose'){
				CMAlert('반대하였습니다.');
			}
			else if(type == 'scrap'){
				CMAlert('스크랩하였습니다.');
			}
		}, function(data){
			var msg = '';
			var msg2 = '';
			if(type == 'recommend') msg2 = '추천';
			else if(type == 'oppose') msg2 = '반대';
			else if(type == 'report') msg2 = '신고';

			if(data == 'recommend') msg = '추천';
			else if(data == 'oppose') msg = '반대';
			else if(data == 'report') msg = '신고';
			else if(data == 'scrap') CMAlert('이미 스크랩했습니다.');

			if(msg !== '') CMAlert('이 글을 ' + msg + '했습니다.\n' + msg2 + '하시려면 ' + msg + ' 취소하시기 바랍니다.');
		});
	},

	ContentCancelAction : function(obj, type){
		JCM.postWithLoading($(obj).attr('data-cancel-href'), {type : type}, function(data){
			$(obj).removeClass('already');
			CMAlert('취소되었습니다.');
		}, function(){
		});
	},

};
