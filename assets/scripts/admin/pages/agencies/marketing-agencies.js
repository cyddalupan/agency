/* Script */

jQuery(function() {
	$(document).ready(function() {

		$('a.btn-edit-marketing-agency').on('click', function( event ) {
			event.preventDefault();
			var aId = $(this).attr('data-marketing-agency');
			var modal = $( $(this).attr('data-target') );
			
			$.ajax({
				async:		false,
				url:		siteUrl + 'admin/ajax/marketing_agency/detail/' + aId,
				type: 		'POST',
				dataType:	"json",
				success: 	function(response) {
					document.data = response;
					console.log(response);
					
					modal.find('.modal-title').text( response.agency.agency_name );
					modal.find('input[name="agency[agency_id]"]').val( response.agency.agency_id );
					modal.find('input[name="agency[name]"]').val( response.agency.agency_name );
					modal.find('input[name="agency[contact-person]"]').val( response.agency.agency_contact_person );
					modal.find('input[name="agency[contacts]"]').val( response.agency.agency_contacts );
					modal.find('input[name="agency[email]"]').val( response.agency.agency_email );
                    modal.find('input[name="agency[address]"]').val( response.agency.agency_address );
				}
			});
		});
		
	}); //endOf: $(document).ready
	
}); //endOf: jQuery