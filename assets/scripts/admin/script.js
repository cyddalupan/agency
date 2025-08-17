/* Script */

jQuery(function() {
	$(document).ready(function() { 
        
		$('.alert-modal').each(function() {
			$(this).modal();
		})

		//Bootstrap modal data destroyer
		$('body').on('hidden.bs.modal', '.modal', function () {
		  $(this).removeData('bs.modal');
		});

		//Alert Messages Fade Out
	   	$('div.alert.fadeOut').click(function() {
			$(this).fadeOut('fast');
		}); 
		
		//Live time ago
		if( $('.timeago').length > 0 ) {
			$('.timeago').livequery(function() { $(this).timeago();	});
		}

		$('a#refresh-toggler').on('click', function( event ) {
			event.preventDefault();
			window.location.href = window.location.href;
		});
			
		var countDownTimer = setTimeout(function() {
			countDown();
		}, 1000);
		
		function countDown() {
			var timer = $('.alert .alert-label .timer');
			
			var time = parseInt(timer.html());
			time--;
			timer.html(time);
			
			if(time > 0) {
				setTimeout(function() {countDown()}, 1000);
				return;
			} 
			
			$('div.alert.fadeOut').fadeOut('slow');
		} 
	}); //endOf: $(document).ready
	
}); //endOf: jQuery

var initApplicantSendFunc = function( btnSend, checkboxClass, employer, redirectUrl ) {

	$('input.' + checkboxClass ).on('change', function() {
		if ( $('input.' + checkboxClass + ':checked' ).length == 0 ) {
			btnSend.attr('disabled', 'disabled');
			employer.attr('disabled', 'disabled');
			return;
		}

		btnSend.removeAttr('disabled');
		employer.removeAttr('disabled');
	});
	
	btnSend.on('click', function() {

		var applicants = $('.' + checkboxClass + ':checked');

		if ( applicants.length == 0 ) {
			alert( 'Please select applicants to send.' );
			return;
		}

		if ( ! employer.val() ) {
			alert( 'Please select an employer.' );
			return;
		}

		var form = $('<form>').attr({
			action: siteUrl + 'admin/applicants/send-applicants',
			method: 'post'
		});

		var input = $('<input>').attr({
			type: 'hidden',
			name: 'emp-id'
		});

		input.val( employer.val() );
		input.appendTo( form );

		applicants.each(function() {
			var hidden = $('<input>').attr({
				type: 'hidden',
				name: 'app-id[]'
			});

			hidden.val( $(this).val() );
			hidden.appendTo( form );
		});

		//Redirect url
		var input = $('<input>').attr({
			type: 'hidden',
			name: 'return-url'
		});

		input.val( redirectUrl );
		input.appendTo( form );

		//Prompt
		if ( ! confirm( 'Do you want to line up applicant(s)?' ) ) {
			return;
		}

		//add cyd code
		if(btnSend.selector == '#btn-send-applicant'){
			cyd_employer = $('#employers-selection').val();
			console.log('file location Script admin script.js');
			var cyd_applicants = [];
			$('#cyd_applicant_list input:checked').each(function() {
			    cyd_applicants.push($(this).val());
			});
			$.post( base_url + 'page/multiple_lineup',{applicant_ids:cyd_applicants,employer:cyd_employer},function(cyd_result){
				console.log(cyd_result);
				return false;
			})
			.fail(function() {
			    alert( "error: Please Refresh and try again. If a problem occurs, contact developer." );
			})
			.done(function(result) {
			    form.appendTo( $('body') );
				form.submit();
			});
		}else{
			form.appendTo( $('body') );
			form.submit();
		}
		
	});

};