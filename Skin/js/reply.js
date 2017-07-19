App.Reply = {
	SubmitWrite : function(e){
		e.preventDefault();
		$(this).validCheck();

		JCM.ajaxForm(this, function(){
			$('#replyGetForm input[name=page]').val(1);
			App.Reply.GetList();
		});
	},

	ClickPaging : function(e){
		e.preventDefault();
		$('#replyGetForm input[name=page]').val($(this).text());
		App.Reply.GetList();
	},

	ClickReset : function(){
		App.Reply.RemoveFormBox();
	},

	RemoveFormBox : function(){
		var obj = $('#replyFormBox');
		if(obj.hasClass('modifyForm')){
			obj.closest('article').find('div.comment').show();
			obj.remove();
		}
		else obj.remove();
	},

	ClickPwdView : function(e){
		e.preventDefault();
		var seq = $(this).closest('article').attr('data-seq');
		var layer = $('#replyPwdLayer');
		layer.find('form')[0].reset();
		layer.find('input[name=seq]').val(seq);
		layer.show();
	},

	SubmitPwdForm : function(e){
		e.preventDefault();
		var seq = $('#repPwdForm input[name=seq]').val();
		JCM.ajaxForm(this, function(data){
			var btns = '<a href="#" class="answerBtn">답변</a><a href="#" class="modifyBtn">수정</a><a href="#" class="deleteBtn">삭제</a>';
			$('#repArticle'+seq+' .commentText').html(data);
			$('#repArticle'+seq+' .btns').html(btns);
			$('#replyPwdLayer form')[0].reset();
			$('#replyPwdLayer').hide();
		});
	},

	ClickDeleteBtn : function(e){
		e.preventDefault();
		var article = $(this).closest('article');
		if(article.children('.repDeleteForm').length) return;

		App.Reply.RemoveFormBox();
		article.append('<div id="replyFormBox">' + $('#replyDeleteLayer').html() + '</div>');
		var form = article.find('form');

		var seq = $(this).closest('article').attr('data-seq');
		form[0].reset();
		form.find('input[name=seq]').val(seq);
		if($(this).hasClass('myDoc')){
			form.find('.pwdinp').hide();
			form.find('.pwdinp input').attr('disabled', 'disabled');
		}else{
			form.find('.pwdinp').show();
			form.find('.pwdinp input').removeAttr('disabled');
		}
	},

	SubmitDeleteForm : function(e){
		e.preventDefault();
		JCM.ajaxForm(this, function(data){
			App.Reply.GetList();
		});
	},

	ClickModifyBtn : function(e){
		e.preventDefault();
		if($('#replyListContents .repModifyForm').length) return;
		var seq = $(this).closest('article').attr('data-seq');
		var txt = $(this).closest('article').find('.commentText').text();

		var article = $(this).closest('article');

		App.Reply.RemoveFormBox();
		article.append('<div id="replyFormBox" class="modifyForm">' + $('#replyModifyLayer').html() + '</div>');
		article.find('div.comment').hide();

		var form = article.find('form');
		form[0].reset();
		form.find('input[name=seq]').val(seq);
		form.find('textarea[name=comment]').val(txt);
		if($(this).hasClass('myDoc')){
			form.find('.pwdinp').hide();
			form.find('.pwdinp input').attr('disabled', 'disabled');
		}else{
			form.find('.pwdinp').show();
			form.find('.pwdinp input').removeAttr('disabled');
		}
	},

	SubmitModifyForm : function(e){
		e.preventDefault();
		JCM.ajaxForm(this, function(data){
			App.Reply.GetList();
		});
	},

	ClickAnswerBtn : function(e){
		e.preventDefault();
		if($('article.replyAnswer').length) return;
		var article = $(this).closest('article');
		var seq = article.attr('data-seq');
		$('#replyAnswerLayer form')[0].reset();
		$('#replyAnswerLayer input[name=target_seq]').val(seq);

		App.Reply.RemoveFormBox();
		article.after('<article class="replyAnswer" id="replyFormBox">' + $('#replyAnswerLayer').html() + '</article>');
	},

	SubmitAnswerForm : function(e){
		e.preventDefault();
		JCM.ajaxForm(this, function(data){
			App.Reply.GetList();
		});
	},

	EventInit : false,

	Init : function(){

		if(!this.EventInit){
			this.EventInit = true;

			// Write
			$(document).on('submit', '.replyWrite form', App.Reply.SubmitWrite);
			// Paging
			$(document).on('click', '#replyPaging a', App.Reply.ClickPaging);

			$(document).on('click', '#Reply button[type=reset]', App.Reply.ClickReset);
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
		}
	},

	GetList : function(){
		JCM.ajaxForm('#replyGetForm', function(data){
			$('#ReplyList').html(data);
		});
	}
};
