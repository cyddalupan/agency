/* Script */

jQuery(function() {
	$(document).ready(function() {

		$('.btn-category-photo').on('click', function(event) {
			event.preventDefault();
			$('input[name="category[photo]"]').click();
		});
		
		$('a.btn-category-positions').on('click', function( event ) {
			event.preventDefault();
			var cId = $(this).attr('data-category');

			var modal = $( $(this).attr('data-target') );
			var category = $(this).closest('tr').find('td:nth-child(3)').text();
			
			$.ajax({
				async:		false,
				url:		siteUrl + 'admin/ajax/categories/get-positions/' + cId,
				type: 		'POST',
				dataType:	"json",
				success: 	function(response) {
					document.data = response;
					console.log(response);
					
					modal.find('.modal-title').text( category );
					modal.find('.btn-add-position').attr('data-category', cId);
					
					var table = modal.find('table');
					table.find('tbody').html('');
					
					if ( response.positions.length == 0 ) {
						var tr = $('<tr>');
						var td = $('<td>').attr({
							'colspan': '2',
							'align':   'center'
						})
						.text('-- No positions --')
						.appendTo(tr);
						
						table.find('tbody').append(tr);
						return;
					}
					
					$.each( response.positions, function( index, data ) {
						table.find('tbody').append(function() {
							var tr 		= $('<tr>');
							var colId 	= $('<td>').text( data.position_id );
							
							cyd_input = $('<input>').attr( 'value', data.position_name )
													.attr( 'class', 'auto-save-pos auto-save-pos-'+data.position_id )
													.attr( 'pos-id', data.position_id );

							var colPos 	= $('<td>').append( cyd_input );

							var del = $('<a>').attr({
								'href':          'javascript:void();',
								'role':          'button',
							});

							del.text( 'Delete' );
							del.on('click', function( event ) {
								event.preventDefault();

								if ( ! confirm( 'Do you want to delete this position?' ) ) {
									return false;
								}

								deletePosition( data.position_id, tr );
								return false;
							});

							var colDel 	= $('<td>').append( del );

							var cyd_save = $('<a>').attr({
								'href':          'javascript:void();',
								'role':          'button',
								'class':          'cyd_save_pos cyd_save_pos-'+data.position_id,
							});
							cyd_save.text( 'Save' );
							cyd_save.on('click', function( event ) {
								event.preventDefault();

								$('.cyd_save_pos-'+data.position_id).text('Saving...');
								$('.cyd_save_pos-'+data.position_id).css('color','#000');

								$.post( base_url + 'page/update_pos',{pos_id:data.position_id,pos_val:$('.auto-save-pos-'+data.position_id).val()},function(result){
									console.log(result);

									$('.cyd_save_pos-'+data.position_id).text('Save');
									$('.cyd_save_pos-'+data.position_id).css('color','#428bca');
								})
								.fail(function() {
								    alert( "Error! Try Again, Check internet connection or Contact Developer" );
								});
								
								return false;
							});
							var CydcolSave = 	$('<td>').append( cyd_save );
							
							tr.append(colId).append(colPos).append(CydcolSave).append(colDel);
							return tr;
						});
					});
				}
			});
		});
		
		var deletePosition = function( positionId, el ) {
			$.ajax({
				async:		false,
				url:		siteUrl + 'admin/ajax/positions/delete-position/' + positionId,
				type: 		'POST',
				dataType:	"json",
				success: 	function( response ) {
					document.data = response;

					console.log(response);

					if ( response.status == 'success' ) {

						alert( '"' + response.position.position_name + '" position has been deleted.' );

						el.fadeOut(function() {
							$(this).remove();
						});	
					}				

					return;
				}
			});

		};

		$('a.btn-add-position').on('click', function( event ) {
			event.preventDefault();
			var cId = $(this).attr('data-category');

			var modal    = $( $(this).attr('data-target') );
			var category = $(this).closest('tr').find('td:nth-child(3)').text();
			var form     = modal.find('form');
			
			modal.find('.modal-title').html('Add position under <em>' + category + '</em>');
			
			form.find('input[name="position[category]"]').val(cId);
			
			form.find('button:submit').on('click', function( event ) {
				if ( $.trim(form.find('input[name="position[name]"]').val() ) == "" ) {
					alert( 'Enter position name!' );
					event.preventDefault();
					return;
				}
				
			});
		});
		
		$('a.btn-edit-category').on('click', function( event ) {
			event.preventDefault();
			var cId = $(this).attr('data-category');
			var modal = $( $(this).attr('data-target') );
			
			$.ajax({
				async:		false,
				url:		siteUrl + 'admin/ajax/categories/detail/' + cId,
				type: 		'POST',
				dataType:	"json",
				success: 	function(response) {
					document.data = response;
					console.log(response);
					
					modal.find('.modal-title').text( response.category.category_name );
					modal.find('input[name="category[category_id]"]').val( response.category.category_id );
					modal.find('input[name="category[name]"]').val( response.category.category_name );
				}
			});
		});

		$('a.btn-delete-category').on('click', function( event ) {
			event.preventDefault();

			if ( ! confirm( 'Do you want to delete this category?/' ) ) {
				return false;
			}

			var tr  = $(this).closest('tr');
			var cId = $(this).attr( 'data-category' );

			$.ajax({
				async:		false,
				url:		siteUrl + 'admin/ajax/categories/delete/' + cId,
				type: 		'POST',
				dataType:	"json",
				success: 	function(response) {

					document.data = response;

					console.log(response);

					if ( response.status == 'success' ) {

						alert( '"' + response.category.category_name + '" category has been deleted.' );

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