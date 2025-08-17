/* Script */

jQuery(function() {
	$(document).ready(function() {
		
		$('a.btn-edit-voucher').on('click', function(event) {
			event.preventDefault();
			
			var voucherId = $(this).attr('data-voucher');
			
			$.ajax({
				async:		false,
				url:		siteUrl+"admin/ajax/voucher/detail/"+voucherId,
				type:		'GET',
				dataType:	"json",
				success: 	function(response) {
					console.log(response);
					document.data = response;

					var voucher = response.voucher;

					var modal = $('#modalEditVoucher');
					
					modal.find('.modal-title').text( 'Voucher# ' + voucher.voucher_number );
					modal.find('input[name="voucher[voucher_id]"]').val( voucher.voucher_id );
					modal.find('input[name="voucher[number]"]').val( voucher.voucher_number );
					modal.find('.voucher-amount').text( parseFloat(voucher.voucher_amount).toFixed(2) );
					modal.find('textarea[name="voucher[remarks]"]').val( voucher.voucher_remarks );

					modal.find('.marketing-agency').text('--');
					modal.find('.marketing-agent').text('--');
					modal.find('.recruitment-agent').text('--');

					if ( voucher.voucher_marketing_agency ) {
						modal.find('.marketing-agency').text( voucher['marketing-agency'].agency_name );
					}

					if ( voucher.voucher_marketing_agent ) {
						modal.find('.marketing-agent').text( voucher['marketing-agent'].agent_first + ' ' + voucher['marketing-agent'].agent_last );
					}

					if ( voucher.voucher_recruitment_agent ) {
						modal.find('.recruitment-agent').text( voucher['recruitment-agent'].agent_first + ' ' + voucher['recruitment-agent'].agent_last );
					}

					var table = modal.find('table');
					var tr    = $('<tr>');
					var td    = $('<td>');

					table.find('tbody').html('');

					$.each( voucher.workers, function( index, worker ) {
						var trTemp = tr.clone().appendTo( table.find('tbody') );

						td.clone().text( worker.applicant_first + ' ' + worker.applicant_last ).appendTo( trTemp );
						td.clone().text( worker.employer_name ).appendTo( trTemp );
						td.clone().text( worker.commission_created ).appendTo( trTemp );

					});

				}
			});
			
		});

		$('.btn-approve').on('click', function( event ) {
			event.preventDefault();

			var tId = $(this).attr('data-transaction');

			var btnUnApprove = $(this).clone()
					.removeClass('btn-approve')
					.addClass('btn-unapprove');

			if ( ! confirm( 'Do you want to approve this transaction?' ) ) {
				return false;
			}

			$.ajax({
				async:    false,
				url:      siteUrl + 'admin/ajax/commissions/approval',
				type:     'POST',
				dataType: 'json',
				data: {
					action: 'approve',
					tId:     tId,
				},
				success: function( response ) {
					window.location.href = window.location.href;
				}
			});

			$(this).closes('td').append( btnUnApprove );
			$(this).remove();
		});

		$('.btn-unapprove').on('click', function( event ) {
			event.preventDefault();

			var tId = $(this).attr('data-transaction');

			var btnApprove = $(this).clone()
					.removeClass('btn-unapprove')
					.addClass('btn-approve');

			if ( ! confirm( 'Do you want to unapprove this transaction?' ) ) {
				return false;
			}

			$.ajax({
				async:    false,
				url:      siteUrl + 'admin/ajax/commissions/approval',
				type:     'POST',
				dataType: 'json',
				data: {
					action: 'unapprove',
					tId:     tId,
				},
				success: function( response ) {
					if ( ! response.status ) {
						alert(response.message);
						return;
					}
					
					window.location.href = window.location.href;
				}
			});

			$(this).closes('td').append( btnApprove );
			$(this).remove();
		});
		 

	}); //endOf: $(document).ready
	
}); //endOf: jQuery