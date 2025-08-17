/* Script */

jQuery(function() {
	$(document).ready(function() {

		$('a.btn-edit-country').on('click', function( event ) {
			event.preventDefault();
			var cId = $(this).attr('data-country');
			var modal = $( $(this).attr('data-target') );
			
			$.ajax({
				async:		false,
				url:		siteUrl + 'admin/ajax/countries/detail/' + cId,
				type: 		'POST',
				dataType:	"json",
				success: 	function(response) {
					document.data = response;
					console.log(response);
					
					modal.find('.modal-title').text( response.country.country_name );
					modal.find('input[name="country[country_id]"]').val( response.country.country_id );
					modal.find('input[name="country[name]"]').val( response.country.country_name );
					modal.find('input[name="country[abbr]"]').val( response.country.country_abbr );
					modal.find('input[name="country[code]"]').val( response.country.country_code );
				}
			});
		});

		$('a.btn-delete-country').on('click', function( event ) {
			
			event.preventDefault();

			var tr  = $(this).closest( 'tr' );
			var cId = $(this).attr('data-country');

			if ( ! confirm( 'Do you want to delete this country?' ) ) {
				return false;
			}

			$.ajax({
				async:		false,
				url:		siteUrl + 'admin/ajax/countries/delete/' + cId,
				type: 		'POST',
				dataType:	"json",
				success: 	function(response) {

					document.data = response;

					console.log(response);

					if ( response.status == 'success' ) {

						alert( '"' + response.country.country_name + '" country has been deleted.' );

						tr.fadeOut(function() {
							$(this).remove();
						});	
					}				

					return;
				}
			});


		});
		
	}); //endOf: $(document).ready
	
}); //endOf: jQuery