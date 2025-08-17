$('a#delete-voucher').on('click', function( event ) {

	if ( ! confirm( 'Do you want to delete this transaction?' ) ) {
		event.preventDefault();
	}

	return true;
});

//--Bootstrap Date Picker--
$('.date-picker').datepicker();