myApp.controller('online-registration', ['$scope', function($scope) {

	console.log('online registration')

	window.setInterval(function(){
		dateofbirth = $('.dateofbirth').val();

		birthday_timestamp = date_to_timestamp(dateofbirth);
		age = calculateAge(birthday_timestamp);

		$('.cyd_age').val(age);
	}, 3000);

	console.log('function applicant/registration/registration_compute_working_experience');
	window.setInterval(function(){
		workstarted = $('.work-started').val();
		workended = $('.work-ended').val();
		experience = workended - workstarted;
		$('.work-years').val(experience);
		return false;
	}, 3000);

	$('.add-training-button').click(function(){
		$('.add-training-button-row').hide(1);
		$('.extra-training').slideDown('slow');
	});

	console.log('function applicant/registration/registration_show_more_collage()');
	$('.add-collage-button').click(function(){
		$('.add-collage-button-row').fadeOut(1);
		$('.extra-collage').fadeIn('slow');
	});
}]);