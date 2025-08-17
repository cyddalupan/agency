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

    $('a.btn-mark-paid').on('click', function( event ) {

        if ( ! confirm( 'Do you want to mark it as paid?' ) ) {
            event.preventDefault();
            return false;
        }
        
        return true;
    });
});
