myApp.controller('employer-resume', ['$scope', function($scope) {

	console.log('employer resume');

	applicant_id = $('.applicant_id').val();
	$.post( base_url + 'page/get_files',{applicant_id:applicant_id},function(jsonresult){	
		//clear value
		$('.cyd_output_files').html('<div class="cyd_back">Back</div>');
		$('.cyd_back').hide(1);
		jQuery.each( jsonresult, function( i, val ) {
			if(val.file_status == 1 && !is_stepup_files(val.file_type)){
				//design
				cyd_html = 
				'<div class="cyd_each_document">' +
					'<h1>' + 
						val.file_type + 
					'</h1>' +
					'<img src="' + 
						base_url + 
						val.file_path +
					'" />' + 

				'</div>';
			  	$('.cyd_output_files').append(cyd_html);
		  	}
		});
	},"json")
	.fail(function() {
	    alert( "Can't Load Files" );
	})
	.done(function() {
		zoom_file();
		back_file();
	});

	function zoom_file(){
	    $('.cyd_each_document').click(function(){
			console.log('function employer/applicant/resume/zoom_file');
	    	$('.cyd_back').show(1);
			$('.cyd_each_document').fadeOut('slow');
			$(this).fadeIn(1);
			$(this).css('width','100%');
			$('.cyd_each_document img').css('width','100%');
		});
	}

	function back_file(){
		$('.cyd_back').click(function(){
			console.log('function employer/applicant/resume/back_file');
	    	$('.cyd_back').hide(1);
			$('.cyd_each_document').fadeIn('slow');
			$('.cyd_each_document').css('width','218px');
			$('.cyd_each_document img').css('width','181px');
		});
	}

}]);