$(document).ready(function() {
	$('a#btn-print').on('click', function( event ) {

		event.preventDefault(); 

		var options = { 
			mode:		'popup', 
			popClose:	false, 
			extraCss: 	'',
			retainAttr:	['class', 'id', 'style'],
			extraHead:	'<meta charset="utf-8" />,<meta http-equiv="X-UA-Compatible" content="IE=edge"/>'
		};

		$('#wrapper').printArea( options );
	}); 

});
 