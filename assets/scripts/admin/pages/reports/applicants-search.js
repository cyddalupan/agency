//--Bootstrap Date Picker--
$('.date-picker').datepicker();

$('select[name="applicant[status]"]').on('change', function() {
	
	var options = $('.selected-option');

	if( $(this).find('option:selected').text() == 'Selected') {
		options.hide().removeClass('hide').slideDown();
		return;
	}

	options.slideUp(function() {
		$(this).addClass('hide');
	});
});