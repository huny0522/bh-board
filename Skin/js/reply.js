if(typeof(window._REPLY_LANG) === 'undefined') window._REPLY_LANG = {};
if(typeof(window._REPLY_LANG.answer) === 'undefined') window._REPLY_LANG.answer = '답변';
if(typeof(window._REPLY_LANG.modify) === 'undefined') window._REPLY_LANG.modify = '수정';
if(typeof(window._REPLY_LANG.del) === 'undefined') window._REPLY_LANG.del = '삭제';
if(typeof(window._REPLY_LANG.attachFile) === 'undefined') window._REPLY_LANG.attachFile = '첨부파일';
if(typeof(window._REPLY_LANG.deleteFile) === 'undefined') window._REPLY_LANG.deleteFile = '파일삭제';

var AppReply = {
	SubmitWrite : function(e){
		e.preventDefault();
		var form = this;
		var res = $(this).validCheck();
		if(!res) return;

		JCM.ajaxForm(this, function(){
			$('#replyGetForm input[name=page]').val(1);
			form.reset();
			AppReply.GetList();
		});
	},

	ClickPaging : function(e){
		e.preventDefault();
		$('#replyGetForm input[name=page]').val($(this).text());
		AppReply.GetList();
	},

	ClickReset : function(){
		AppReply.RemoveFormBox();
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
			var btns = '<a href="#" class="answerBtn">' + window._REPLY_LANG.answer + '</a><a href="#" class="modifyBtn">' + window._REPLY_LANG.modify + '</a><a href="#" class="deleteBtn">' + window._REPLY_LANG.del + '</a>';
			$('#repArticle'+seq).attr(data.file_name);
			$('#repArticle'+seq+' .comment').prepend(data.file_html);
			$('#repArticle'+seq+' .commentText').html(data.comment);
			$('#repArticle'+seq+' .btns').html(btns);
			$('#replyPwdLayer form')[0].reset();
			$('#replyPwdLayer').hide();
		});
	},

	ClickRepLayerReset : function(){
		$(this).closest('form')[0].reset();
		$(this).closest('div.repLayer').hide();
	},

	ClickDeleteBtn : function(e){
		e.preventDefault();
		var article = $(this).closest('article');
		if(article.children('.repDeleteForm').length) return;

		AppReply.RemoveFormBox();
		article.find('header').after('<div id="replyFormBox" class="replyWrite replyDelete">' + $('#replyDeleteLayer').html() + '</div>');
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
			AppReply.GetList();
		});
	},

	ClickModifyBtn : function(e){
		e.preventDefault();
		if($('#replyListContents .repModifyForm').length) return;
		var seq = $(this).closest('article').attr('data-seq');
		var txt = $(this).closest('article').find('.commentText').text();

		var article = $(this).closest('article');
		var file_inp = AppReply.GetFileInput(article.attr('data-file-name'));

		AppReply.RemoveFormBox();
		article.append('<div id="replyFormBox" class="replyWrite modifyForm">' + $('#replyModifyLayer').html() + '</div>');
		article.find('div.comment').hide();

		var form = article.find('form');
		form[0].reset();
		form.find('input[name=seq]').val(seq);
		form.find('textarea[name=comment]').val(txt);
		form.find('div.file_inp').remove();
		form.find('.attachFileArea').html('<div class="file_inp">' + file_inp + '</div>');
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
			AppReply.GetList();
		});
	},

	ClickAnswerBtn : function(e){
		e.preventDefault();
		if($('article.replyAnswer').length) return;
		var article = $(this).closest('article');
		var seq = article.attr('data-seq');
		$('#replyAnswerLayer form')[0].reset();
		$('#replyAnswerLayer input[name=target_seq]').val(seq);

		AppReply.RemoveFormBox();
		article.after('<article class="replyWrite replyAnswer" id="replyFormBox">' + $('#replyAnswerLayer').html() + '</article>');
		article.next().find('.attachFileArea').html('<div class="file_inp">' + AppReply.GetFileInput('') + '</div>');
	},

	SubmitAnswerForm : function(e){
		e.preventDefault();
		JCM.ajaxForm(this, function(data){
			AppReply.GetList();
		});
	},

	EventInit : false,

	Init : function(){

		if(!this.EventInit){
			this.EventInit = true;

			// Write
			$(document).on('submit', '.replyWrite form', AppReply.SubmitWrite);
			// Paging
			$(document).on('click', '#replyPaging a', AppReply.ClickPaging);

			$(document).on('click', '#Reply button[type=reset]', AppReply.ClickReset);
			// Secret Reply View Pwd
			$(document).on('click', '#replyListContents a.pwdView', AppReply.ClickPwdView);

			$(document).on('submit', '#repPwdForm', AppReply.SubmitPwdForm);

			$(document).on('click', 'div.repLayer button[type=reset]', AppReply.ClickRepLayerReset);

			// -------------------------------------
			// Delete Reply
			$(document).on('click', '#replyListContents a.deleteBtn', AppReply.ClickDeleteBtn);

			$(document).on('submit', '#repDeleteForm', AppReply.SubmitDeleteForm);

			// -------------------------------------
			// Modify Reply
			$(document).on('click', '#replyListContents a.modifyBtn', AppReply.ClickModifyBtn);

			$(document).on('submit', '#repModifyForm', AppReply.SubmitModifyForm);

			// -------------------------------------
			// Answer Reply
			$(document).on('click', '#replyListContents a.answerBtn', AppReply.ClickAnswerBtn);

			$(document).on('submit', '#repAnswerForm', AppReply.SubmitAnswerForm);

			JCM.fileForm();
		}
	},

	GetList : function(){
		JCM.ajaxForm('#replyGetForm', function(data){
			$('#ReplyList').html(data);
		});
	},

	GetMoreListInitIs : false,

	GetMoreList : function(){
		var article = $('#replyListContents > article');
		if(article.length){
			$('#replyGetMoreForm input[name=lastSeq]').val(article.last().attr('data-seq'));
		}

		JCM.ajaxForm('#replyGetMoreForm', function(data){
			$('#replyListContents').append(data);
		});

		if(!AppReply.GetMoreListInitIs){
			AppReply.GetMoreListInitIs = true;
			$('#replyMoreViewBtn').on('click', function(e){
				e.preventDefault();
				AppReply.GetMoreList();
			});
		}
	},

	GetFileInput : function(fname){
		fname = $.trim(fname);
		var html = '<div class="fileUploadArea2"><input type="hidden" name="file" class="fileUploadInput" value=""><button type="button" class="fileUploadBtn sBtn">' + window._REPLY_LANG.attachFile + '</button>' +
			'<p>' +
			'<span class="fileName">' + fname + '</span>' +
			(fname !== '' ? ' <label class="checkbox"><input type="checkbox" name="del_file_file" value="y"><span>' + window._REPLY_LANG.deleteFile + '</span></label>' : '') +
			' </p>' +
			'</div><script></script>';
		return html;
	}
};
