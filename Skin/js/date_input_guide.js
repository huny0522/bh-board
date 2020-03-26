/* -----------------------------------------------------
 *
 *   Input Date
 *   .dateInput .date
 *
 ----------------------------------------------------- */
(function($){
	window.DateInputAll = function(){
		$('.dateInput .date').each(function(){
			DateInput.call(this);
		});
	};

	window.DateInput = function(e){
		if(!$(this).siblings('.before').length) $(this).before('<div class="before"></div>');
		var val = $(this).val();
		var len = val.length;
		if(typeof(e) !== 'undefined' && e.keyCode === 8){
			if(len === 4){
				e.preventDefault();
				val = val.substring(0, 3);
				len = 3;
				$(this).val(val);
			}
			else if(len === 7){
				e.preventDefault();
				val = val.substring(0, 6);
				len = 6;
				$(this).val(val);
			}
		}else{
			var n2 = $(this).val().replace(/[^0-9]/gi, '');
			var n3 = n2;

			if(n2.length >= 5 && parseInt(n2.substring(4,5)) > 1){
				n3 = n2.substring(0, 4) + '1' + n2.substring(5, n2.length);
			}

			if(n2.length >= 7 && parseInt(n2.substring(6,7)) > 3){
				n3 = n2.substring(0, 6) + '3' + n2.substring(7, n2.length);
			}

			if(n2.length >= 6 && parseInt(n2.substring(4,6)) > 12){
				n3 = n2.substring(0,5);
			}

			if(n2.length >=8 && parseInt(n2.substring(6,8)) > 31){
				n3 = n2.substring(0,7);
			}

			if(n3.length >= 6){
				n3 = n3.substring(0, 4) + '-' + n3.substring(4, 6) + '-' + n3.substring(6, n2.length);
			}else if(n3.length >= 4){
				n3 = n3.substring(0, 4) + '-' + n3.substring(4, n2.length);
			}

			if(n3 !== val){
				$(this).val(n3);
				len = n3.length;
			}
		}

		if(len > 10) len = 10;
		var txt = '0000-00-00';
		var newTxt = '';
		for(var i = 0; i < 10; i++){
			if(i < len) newTxt += '<span>' + txt[i] + '</span>';
			else newTxt += txt[i];
		}
		$(this).siblings('.before').html(newTxt);
	};

	$(document).on('keyup mousedown change focus focusout', '.dateInput input.date', function(e){
		window.DateInput.call(this, e);
	});

	$(function(){
		window.DateInputAll();
	});

	$.fn.reset = function(){
		$(this)[0].reset();
		window.DateInputAll();
	};
}($));
