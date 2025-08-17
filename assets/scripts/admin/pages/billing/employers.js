
$(document).ready(function() {
	
    //--Bootstrap Date Picker--
    $('.date-picker').datepicker();

    $('.btn-filter-date').on('click', function( event ) {
    	event.preventDefault();

    	var form = $('.filter-date');

    	if ( form.hasClass('hide')) {
    		form.hide().removeClass('hide');
    		form.slideDown();
    		$(this).remove();
    	}
    });
});
