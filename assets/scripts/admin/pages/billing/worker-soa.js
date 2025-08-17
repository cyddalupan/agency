$(document).ready(function() {

	$('a#btn-change-placement-fee').on('click', function( event ) {
		event.preventDefault();

		var form = $('form#frmEditPlacementFee');

		if ( ! form.is(':visible') ) {
			form.hide().removeClass('hide').slideDown('fast');
			$('input[name="placement-fee[amount]"]').focus();
			return;
		}

		form.slideUp(function() {
			form.hide();
			return;
		});

	});

});