/* Script */

jQuery(function() {
	$(document).ready(function() {
		
		$('select[name="user[type]"]').select2({
			placeholder: "Select user type",
			allowClear: true
		});

		$('select[name="user[type]"]').on('change', function() {

			if ( $(this).val() == 5 ) {
				$('.employer-account').hide().removeClass('hide').slideDown('fast');
				return;
			}
			
			$('.employer-account').addClass('hide');
		});

		$('a.btn-settings').on('click', function( event ) {
			event.preventDefault();

			var uId = $(this).attr('data-user');

			$.ajax({
				async: false,
				url: siteUrl + 'admin/ajax/users/detail/' + uId,
				type: 'GET',
				dataType: 'json',
				success: function( response ) {
					
					document.data = response;

					var modal = $('#modalSettings');
					var form  = modal.find('form');

					form.find('input[name="user[id]"]').val( uId );
					form.find('input[name="user[fullname]"]').val( response.user.user_fullname );
					form.find('input[name="user[email]"]').val( response.user.user_email );
					form.find('input[name="user[name]"]').val( response.user.user_name );
					form.find('input[name="user[password-old]"]').val( "" );
					form.find('input[name="user[password]"]').val( "");
					form.find('input[name="user[password-2]"]').val( "" );
					form.find('textarea[name="user[remarks]"]').val( response.user.user_remarks );
					
				}
			}); //endOf: AJAX
		});
		 
	}); //endOf: $(document).ready
	
}); //endOf: jQuery