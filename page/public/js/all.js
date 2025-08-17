/**
 * Main JavaScript File
 * Author: Cyd Dalupan (cydmdalupan@gmail.com)
 */

var myApp = angular.module('myApp',['ngCookies','ngFileUpload']);

/*
 * test If file_type is a Stepup File
 */
function is_stepup_files(file_type){
	console.log('function condition/is_stepup_files');

	if(file_type == 'Step Up Files 1')
		return true;
	if(file_type == 'Step Up Files 2')
		return true;
	if(file_type == 'Step Up Files 3')
		return true;
	else
		return false;

}
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
/*
 * Converts 'Date in timestamp' to Age.
 * Works With date_to_timestamp tool
 */
function calculateAge(birthday_timestamp) { // birthday is a date
    var ageDifMs = Date.now() - birthday_timestamp;
    var ageDate = new Date(ageDifMs); // miliseconds from epoch
    return Math.abs(ageDate.getUTCFullYear() - 1970);
}
/*
 * Converts a Datetime Format "1990-08-10" to a TimeStamp 
 * For Easier Compotation of date
 * Example, Getting Age
 * Can be Used in calculateAge() tool
 */
function date_to_timestamp(oldDate){
	myDate=oldDate.split("-");
	var newDate= myDate[1]+"/"+myDate[2]+"/"+myDate[0];
	return (new Date(newDate).getTime());
}
/*
 * simple remove dash on string function
 */
function remove_dash(str){
	return str.replace(/-/g, "");
}

function resizeIframe(obj) {
    obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
}

function isValidDate(dateString) {
	if (dateString != null) {
	  var regEx = /^\d{4}-\d{2}-\d{2}$/;
	  return dateString.match(regEx) != null;
	}else{
		return false;
	}
}
myApp.controller('dashboard', ['$scope','$timeout', function($scope,$timeout) {

	window.onload = function() {
	    $timeout(function(){
	    	$('iframe#dashboardstats').attr('src', base_url+"admin/dashboard/stats");    
	    	$('.dloading').hide(1);
	    },1000);

	    $timeout(function(){
			$('iframe#updatesboard').attr('src', base_url+"admin/dashboard/updatesboard"); 
	    	$('.uloading').hide(1);
	    },2000);
	};
    
}]);
myApp.controller('summary-applied-online', ['$scope','$http', function($scope,$http) {
	
	$scope.applicants = 0;

	$scope.init = function(agent_id)
	{

		$scope.deployed = [];
		$http({
			method: 'GET',
			url: base_url+'page/reports/summary-applied-online/'+agent_id
		}).then(function successCallback(response) {
			$scope.applicants = response.data;
			console.log($scope.applicants);
		});

	};
}]);
myApp.controller('summary-reports', ['$scope','$http', function($scope,$http) {
	
	$scope.totalSelected = 0;

	$scope.totalSelectedPositions = 0;

	$scope.init = function(employer_id)
	{

		$http({
			method: 'GET',
			url: base_url+'page/reports/selected_count_positions/'+employer_id
		}).then(function successCallback(response) {
			$scope.totalSelectedPositions = response.data;

			angular.forEach($scope.totalSelectedPositions, function(value, key) {
				$scope.totalSelected = $scope.totalSelected + value.count;
			});
		});

		$scope.deployed = [];
		$http({
			method: 'GET',
			url: base_url+'page/reports/deployed_with_position/'+employer_id
		}).then(function successCallback(response) {
			$scope.deployed = response.data;
		});

		$scope.lineup = [];
		$http({
			method: 'GET',
			url: base_url+'page/reports/lineup_with_position/'+employer_id
		}).then(function successCallback(response) {
			$scope.lineup = response.data;
		});

	};
}]);

myApp.controller('admin-user', ['$scope', function($scope) {

	console.log('admin user page');
	
	the_u_id = 0;
	$('.cyd_save_user_id').click(function(){
		the_u_id = $(this).attr('data-user');
		console.log('User selected is ' + the_u_id);
	});

	$('.cyd_delete_user').click(function(){
		console.log('function user_delete.js');
		var r = confirm("Are you Sure you want to Delete User?");
		if (r == true) {
		    $.post( base_url + 'page/delete_user',{user_id:the_u_id},function(user_delete_result){
				console.log(user_delete_result);
			})
			.fail(function() {
			    alert( "Server Error, Please Refresh." );
			})
			.done(function() {
			    $('.cyd_hide_me_'+the_u_id).fadeOut('slow');
			});
		}
	});
}]);

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
