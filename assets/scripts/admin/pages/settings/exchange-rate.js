var form = $('#frmExchangeRate');

form.on('submit', function( event ) {

	event.preventDefault();

	form.find('button:submit').text('Saving...');
	 
	$.ajax({
		async: false,
		url:   siteUrl + 'admin/popup/exchange-rate',
		type: 'POST',
		data:  {
			dollar: form.find('input[name="dollar"]').val()
		},
		success: function( response ) {
			if ( response == 'success') {
				alert('Saved!');
				form.find('.modalClose').click();
				return false;
			}

			alert('Server not available. Please try again later.');
			form.find('button:submit').text('Save');
			return false;
		}
	});

});
