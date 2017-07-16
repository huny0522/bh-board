App.Board = {
	ListInit : function(){
		$(document).on('click', '.passwordView', App.Board.ClickPwdView);

		$(document).on('click', '#secretViewForm button[type=reset]', App.Board.ResetPwdView);
	},

	MoreListInit : function(){
		$(document).on('submit', '#bbsSchForm', App.Board.SubmitSearchForm);

		$(document).on('click', '#moreViewBtn', App.Board.ClickMoreViewBtn);

		$(document).on('click', '.passwordView', App.Board.ClickPwdView);

		$(document).on('click', '#secretViewForm button[type=reset]', App.Board.ResetPwdView);

		App.Board.GetMoreList();

	},

	ClickPwdView : function(e){
		e.preventDefault();
		$('#viewForm').attr('action', this.href);
		$('#secretViewForm').show();
	},

	ResetPwdView : function(e){
		document.querySelector('#viewForm').reset();
		$('#secretViewForm').hide();
	},

	GetMoreList : function(){
		JCM.ajaxForm('#bbsSchForm', function(data){
			var boardList = $('#bhBoardList table.list tbody');
			if(data.lastIs) $('#bhBoardList .moreViewBtn').hide();
			else $('#bhBoardList .moreViewBtn').show();
			if($.trim(data.list) == ''){
				if($.trim(boardList.text()) == '') boardList.html('<p class="nothing">검색된 글이 없습니다.</p>');
				return;
			}
			boardList.append(data.list);
		});
	},

	SubmitSearchForm : function(e){
		e.preventDefault();
		$('#bhBoardList table.list tbody').html('');
		$(this).find('input[name=searchKeyword]').val($(this).find('input[name=searchInput]').val());
		$(this).find('input[name=lastSeq]').val('');
		App.Board.GetMoreList();
	},

	ClickMoreViewBtn : function(e){
		e.preventDefault();
		$('#bhBoardList .lastSeq').val($('#bhBoardList table.list tbody tr').last().attr('data-seq'));
		App.Board.GetMoreList();
	},

	View : {
		Init : function(){
			$(document).on('click', '#deleteArticle', App.Board.View.ClickDeleteBtn);
			$(document).on('click', '#deleteForm button[type=reset]', App.Board.View.ResetDeleteForm);

			$(document).on('click', '#modifyBtn', App.Board.View.ClickModifyBtn);
			$(document).on('click', '#modifyForm button[type=reset]', App.Board.View.ResetModifyForm);
		},

		ClickDeleteBtn : function(e){
			e.preventDefault();
			$('#deleteForm').show();
		},

		ResetDeleteForm : function(e){
			document.querySelector('#deleteForm').reset();
			$('#deleteForm').hide();
		},

		ClickModifyBtn : function(e){
			if($('#modForm').length){
				e.preventDefault();
				$('#modifyForm').show();
			}
		},

		ResetModifyForm : function(e){
			document.querySelector('#modifyForm').reset();
			$('#modifyForm').hide();
		},
	},

	Write : {
		useSE2Is : false,

		Init : function(useSE2){
			this.useSE2Is = useSE2;
			if(useSE2) SE2_paste('MD_content','');

			$(document).on('submit', '#BoardWriteForm', App.Board.Write.SubmitWrite);
		},

		SubmitWrite : function(e){
			if(this.useSE2Is) SE2_update('MD_content');

			var res = $(this).validCheck();
			if(!res){
				e.preventDefault();
				return false;
			}

		},
	},
};
