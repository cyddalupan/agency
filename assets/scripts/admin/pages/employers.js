/* Script */

jQuery(function() {
	$(document).ready(function() {
		
		$('a.btn-edit-employer').on('click', function(event) {
			event.preventDefault();
			
			var eId = $(this).attr('data-employer');
			
			$.ajax({
				async:		false,
				url:		siteUrl+"admin/ajax/employers/detail/"+eId,
				type:		'POST',
				dataType:	"json",
				data: 		{eId:eId},
				success: 	function(response) {
					console.log(response);
					document.data = response;

					var modal = $('#modalEditEmployer');
					
					modal.find('.modal-title').text( response.employer.employer_name );
					modal.find('input[name="employer[employer_id]"]').val( response.employer.employer_id );
					modal.find('select[name="employer[country]"]').val( response.employer.employer_country );
					modal.find('input[name="employer[number]"]').val( response.employer.employer_no );
					modal.find('input[name="employer[name]"]').val( response.employer.employer_name );
					modal.find('input[name="employer[address]"]').val( response.employer.employer_address );
					modal.find('input[name="employer[email]"]').val( response.employer.employer_email );
					modal.find('input[name="employer[contact_person]"]').val( response.employer.employer_contact_person );
					modal.find('input[name="employer[contact]"]').val( response.employer.employer_contact );
					modal.find('input[name="employer[user][name]"]').val( response.employer.user_name );
                    
                    modal.find('select[name="employer[source_agency]"]').val( response.employer.employer_source_agency );
                    modal.find('select[name="employer[source_agent]"]').val( response.employer.employer_source_agent );

                    modal.find('input[name="employer[source_agency_commission]"]').val( response.employer.employer_agency_commission );
                    modal.find('input[name="employer[source_agent_commission]"]').val( response.employer.employer_agent_commission );

                    modal.find( 'select[name="employer[source_agency_commission_from]"]').find('option[value="'+ response.employer.employer_agency_commission_from +'"]' ).attr('selected','selected');
                    modal.find( 'select[name="employer[source_agent_commission_from]"]' ).find('option[value="'+ response.employer.employer_agent_commission_from +'"]').attr('selected','selected');
                          
					modal.find('textarea[name="employer[remarks]"]').val( response.employer.employer_remarks );
				}
			});
			
		});
		
		
		
	}); //endOf: $(document).ready
	
}); //endOf: jQuery