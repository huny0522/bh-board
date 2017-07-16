App.Reply = {
	SubmitWrite : function(e){
		e.preventDefault();
		$(this).validCheck();

		JCM.ajaxForm(this, function(data){
			$('#replyGetForm input[name=page]').val(1);
			App.Reply.GetList();
		});
	},

	ClickPaging : function(e){
		e.preventDefault();
		$('#replyGetForm input[name=page]').val($(this).text());
		App.Reply.GetList();
	},

	ClickReset : function(e){
		$(this).closest('form')[0].reset();
		$(this).closest('.repLayer').hide();
	},

	ClickPwdView : function(e){
		e.preventDefault();
		var seq = $(this).closest('article').attr('data-seq');
		$('#replyPwdLayer form')[0].reset();
		$('#replyPwdLayer input[name=seq]').val(seq);
		$('#replyPwdLayer').show();
	},

	SubmitPwdForm : function(e){
		e.preventDefault();
		var seq = $('#repPwdForm input[name=seq]').val();
		JCM.ajaxForm(this, function(data){
			var btns = '<a href="#" class="answerBtn">답변</a><a href="#" class="modifyBtn">수정</a><a href="#" class="deleteBtn">삭제</a>';
			$('#repArticle'+seq+' .commentText').html(data);
			$('#repArticle'+seq+' .btns').html(btns);
			$('#replyPwdLayer').hide();
		});
	},

	ClickDeleteBtn : function(e){
		e.preventDefault();
		var seq = $(this).closest('article').attr('data-seq');
		$('#replyDeleteLayer form')[0].reset();
		$('#replyDeleteLayer input[name=seq]').val(seq);
		if($(this).hasClass('myDoc')){
			$('#replyDeleteLayer .pwdinp').hide();
			$('#replyDeleteLayer .pwdinp input').attr('disabled', 'disabled');
		}else{
			$('#replyDeleteLayer .pwdinp').show();
			$('#replyDeleteLayer .pwdinp input').removeAttr('disabled');
		}

		$('#replyDeleteLayer').show();
	},

	SubmitDeleteForm : function(e){
		e.preventDefault();
		JCM.ajaxForm(this, function(data){
			App.Reply.GetList();
		});
	},

	ClickModifyBtn : function(e){
		e.preventDefault();
		var seq = $(this).closest('article').attr('data-seq');
		var txt = $(this).closest('article').find('.commentText').text();
		$('#replyModifyLayer form')[0].reset();
		$('#replyModifyLayer input[name=seq]').val(seq);
		$('#replyModifyLayer textarea[name=comment]').val(txt);
		if($(this).hasClass('myDoc')){
			$('#replyModifyLayer .pwdinp').hide();
			$('#replyModifyLayer .pwdinp input').attr('disabled', 'disabled');
		}else{
			$('#replyModifyLayer .pwdinp').show();
			$('#replyModifyLayer .pwdinp input').removeAttr('disabled');
		}
		$('#replyModifyLayer').show();
	},

	SubmitModifyForm : function(e){
		e.preventDefault();
		JCM.ajaxForm(this, function(data){
			App.Reply.GetList();
		});
	},

	ClickAnswerBtn : function(e){
		e.preventDefault();
		var article = $(this).closest('article');
		var seq = article.attr('data-seq');
		$('#replyAnswerLayer form')[0].reset();
		$('#replyAnswerLayer input[name=target_seq]').val(seq);

		var comment = article.find('.commentText').html();
		var mname = article.find('header b').text();
		var dt = article.find('header span').text();
		$('#replyAnswerLayer .targetContent p').html(comment);
		$('#replyAnswerLayer header b').html(mname);
		$('#replyAnswerLayer header span').html(dt);

		$('#replyAnswerLayer').show();
	},

	SubmitAnswerForm : function(e){
		e.preventDefault();
		JCM.ajaxForm(this, function(data){
			App.Reply.GetList();
		});
	},

	Init : function(){

		// Write
		$(document).on('submit', '.replyWrite form', App.Reply.SubmitWrite);
		// Paging
		$(document).on('click', '#replyPaging a', App.Reply.ClickPaging);

		$(document).on('click', '.repLayer button[type=reset]', App.Reply.ClickReset);
		// Secret Reply View Pwd
		$(document).on('click', '#replyListContents a.pwdView', App.Reply.ClickPwdView);

		$(document).on('submit', '#repPwdForm', App.Reply.SubmitPwdForm);

		// -------------------------------------
		// Delete Reply
		$(document).on('click', '#replyListContents a.deleteBtn', App.Reply.ClickDeleteBtn);

		$(document).on('submit', '#repDeleteForm', App.Reply.SubmitDeleteForm);

		// -------------------------------------
		// Modify Reply
		$(document).on('click', '#replyListContents a.modifyBtn', App.Reply.ClickModifyBtn);

		$(document).on('submit', '#repModifyForm', App.Reply.SubmitModifyForm);

		// -------------------------------------
		// Answer Reply
		$(document).on('click', '#replyListContents a.answerBtn', App.Reply.ClickAnswerBtn);

		$(document).on('submit', '#repAnswerForm', App.Reply.SubmitAnswerForm);
	},

	GetList : function(){
		JCM.ajaxForm('#replyGetForm', function(data){
			$('#ReplyList').html(data);
		});
	},
};