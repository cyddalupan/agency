/* Script */

jQuery(function() {
	$(document).ready(function() {

		$('a.btn-edit-recruitment-agent').on('click', function( event ) {
			event.preventDefault();
			var aId = $(this).attr('data-recruitment-agent');
			var modal = $( $(this).attr('data-target') );
			
			$.ajax({
				async:		false,
				url:		siteUrl + 'admin/ajax/recruitment_agent/detail/' + aId,
				type: 		'POST',
				dataType:	"json",
				success: 	function(response) {
					document.data = response;
					console.log(response);
					
					modal.find('.modal-title').text( response.agent.agent_first + ' ' + response.agent.agent_last );
					modal.find('input[name="agent[agent_id]"]').val( response.agent.agent_id );
					modal.find('input[name="agent[first]"]').val( response.agent.agent_first );
                    modal.find('input[name="agent[last]"]').val( response.agent.agent_last );
					modal.find('input[name="agent[contacts]"]').val( response.agent.agent_contacts );
					modal.find('input[name="agent[email]"]').val( response.agent.agent_email );
				}
			});
		});
		
	}); //endOf: $(document).ready
	
}); //endOf: jQuery