/* Script */

jQuery(function() {
	$(document).ready(function() {

        $('.chk-all:checkbox').on('change', function() {
            var btnSubmit = $(this).closest('form').find('button:submit');
            
			if ( $(this).is(':checked') ) {
				$('.chk-applicants').attr('checked', 'checked');
                btnSubmit.removeAttr('disabled');
				return;
			}
			
			$('.chk-applicants').removeAttr('checked');
            btnSubmit.attr('disabled', 'disabled');
		});
        
        $('a.btn-extend-reserved').on('click', function(event) {
			event.preventDefault();
			
			var sId = $(this).attr('data-reservation');
			
			$.ajax({
				async:		false,
				url:		siteUrl+"admin/ajax/reservation/detail/"+sId,
				type:		'POST',
				dataType:	"json",
				data: 		{sId:sId},
				success: 	function(response) {
					console.log(response);
					document.data = response;
					
					var modal = $('#modalExtendReserved');
					
					modal.find('.modal-title').text( response.reservation.applicant_first + ' ' + response.reservation.applicant_last );
					modal.find('input[name="reservation[reservation_id]"]').val( response.reservation.reservation_id );
					modal.find('textarea[name="reservation[remarks]"]').val( response.reservation.reservation_remarks );
				}
			});
			
		});
        
        //Highlight on click
        $('.table-applicants tbody tr').on('click', function() {
            var highlightClass = 'warning';
            
            if ( $(this).hasClass( highlightClass ) ) {
                $(this).removeClass( highlightClass );
                return;
            }
            
            $('.table-applicants tbody tr').removeClass( highlightClass  );
            $(this).addClass( highlightClass  );
        });
        
        $('.table-applicants tbody tr').on('dblclick', function() {
            $(this).find('button.btn-applicant-review').click();
        });
        
        initApplicantSendFunc( $('#btn-send-applicant'), 'applicants-selection', $('#employers-selection') );
        
	}); //endOf: $(document).ready
	
}); //endOf: jQuery