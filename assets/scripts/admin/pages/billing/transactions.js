/* Script */

jQuery(function() {
	$(document).ready(function() {

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
				url:      siteUrl + 'admin/ajax/billing/approval',
				type:     'POST',
				dataType: 'json',
				data: {
					action: 'approve',
					tId:     tId,
				},
				success: function( response ) {
					window.location.reload();
					return;
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
				url:      siteUrl + 'admin/ajax/billing/approval',
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
					
					window.location.reload();
					return;
				}
			});

			$(this).closes('td').append( btnApprove );
			$(this).remove();
		});


	}); //endOf: $(document).ready
	
}); //endOf: jQuery