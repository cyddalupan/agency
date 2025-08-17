myApp.controller('all-applicants', ['$scope','$http','$filter', function($scope,$http,$filter) {
	
	//inital value
	$scope.current_page = 1;
	$scope.applicants = 0;
	$scope.employers = 0;
	$scope.countries = 0;
	$scope.positions = 0;
	$scope.noresult = 0;

	//search value
	$scope.keyword = searchKeyword;
	$scope.search_country = 0;
	$scope.search_position = 0;
	$scope.search_employer = 0;
	$scope.search_status = 111;
	$scope.search_gender = 'any';
	$scope.search_date_from = '';
	$scope.search_date_to = '';
	$scope.search_age_from = '';
	$scope.search_age_to = '';
	$scope.search_salary_from = '';
	$scope.search_salary_to = '';

	//show and hide parts
	$scope.show_all_applicants = 1;
	$scope.show_applicant_loading = 1;

	$scope.get_all_applicants = function(){

		//show and hide parts
		$scope.show_all_applicants = 0;
		$scope.show_applicant_loading = 1;
		console.log(branchID);
		//get from laravel API
		$http({
			method: 'POST',
			url: base_url+'page/applicants/all-applicants?page='+$scope.current_page,
			data: { 
				'keyword' : $scope.keyword, 
				'branchID' : branchID,
				'status' : status,
				'isTrainingAdmin' : isTrainingAdmin,
				'search_country' : $scope.search_country,
				'search_position' : $scope.search_position,
				'search_employer' : $scope.search_employer,
				'search_status' : $scope.search_status,
				'search_gender' : $scope.search_gender,
				'search_date_from' : $filter('date')($scope.search_date_from , 'yyyy-MM-dd'),
				'search_date_to' : $filter('date')($scope.search_date_to , 'yyyy-MM-dd'),
				'search_age_from' : $scope.search_age_from,
				'search_age_to' : $scope.search_age_to,
				'search_salary_from' : $scope.search_salary_from,
				'search_salary_to' : $scope.search_salary_to,
			}
		}).then(function successCallback(response) {
			if(response.data.data.length == 0){
				$scope.noresult = 1;
			}else{
				$scope.noresult = 0;
			}

			//get response
			$scope.applicants = response.data;

			//convert output data
			angular.forEach($scope.applicants.data, function(value, key) {

				/**
				 * convert appicant photo
				 * add default image for blank and 0 image
				 * or show real image path
				 */
				if(value.applicant_photo == '' || value.applicant_photo == 0){
					$scope.applicants.data[key].image = base_url+'assets/images/admin/avatars/no-picture.jpg';	
				}else{
					$scope.applicants.data[key].image = base_url+'files/applicant/'+value.applicant_photo;
				}

				//inserting multiple lineup
				$scope.applicants.data[key].employer = '';
				angular.forEach(value.multiple_lineup, function(ml_value, ml_key) {
					$scope.applicants.data[key].employer += ml_value.employer[0].employer_name+', ';
				});

				//statusColors
				$scope.applicants.data[key].statusColors = 'label-'+statusColors[value.applicant_status];

				//statusColors
				$scope.applicants.data[key].statusText = statusText[value.applicant_status];

				/**
				 * loop all experience of current applicant
				 * get how many months is the difference from work experience
				 * deduct year and multiply by 12, deduct month and add to year
				 * then devide by 12 and get the remaining
				 */
				experience_total_dateDiffInMonth = 0;
				angular.forEach(value.applicant_experiences, function(experience_value, experience_key){
					//check if experience_to is blank of not a date.
					if ( isValidDate(experience_value.experience_to) ) {
						check_experience_to = experience_value.experience_to
					}else{
						check_experience_to = new Date();
					}
					experience_dateDiffInyear = ($filter('date')(check_experience_to, 'yyyy') - $filter('date')(experience_value.experience_from, 'yyyy'));
					experience_dateDiffInMonth = ($filter('date')(check_experience_to, 'MM') - $filter('date')(experience_value.experience_from, 'MM'));
					experience_total_dateDiffInMonth = (experience_total_dateDiffInMonth + ((experience_dateDiffInyear * 12) + (experience_dateDiffInMonth)));
				});
				experience_date_years = Math.floor(experience_total_dateDiffInMonth/12);
				experience_date_month = experience_total_dateDiffInMonth % 12;
				$scope.applicants.data[key].experience = experience_date_years+" Year(s) And "+experience_date_month+" Month(s)";

				if(value.hit_status == 'hit'){
					$scope.applicants.data[key].showDeny = 1;
				}else{
					$scope.applicants.data[key].showDeny = 0;
				}
			});//applicant foreach end
			console.log($scope.applicants);

			//pager
			$scope.pages = [];
			for (var i = 0; i < $scope.applicants.last_page; i++) {
				pagecount = (i+1);
				$scope.pages[i] = [];
				$scope.pages[i].pagenumber = pagecount;
				if(pagecount == $scope.applicants.current_page)
					$scope.pages[i].class = "disabled active";
				else
					$scope.pages[i].class = "";
			};//pager loop end
			console.log($scope.pages);


			//show applicants and hide the loader
			$scope.show_all_applicants = 1;
			$scope.show_applicant_loading = 0;
		});
	}

	//run the function
	$scope.get_all_applicants();

	//click advance seach
	$scope.advance_search = function(){
		$scope.current_page = 1;
		$scope.get_all_applicants();
	}

	//get all employers
	$http({
		method: 'GET',
		url: base_url+'page/applicants/all-employer'
	}).then(function successCallback(response) {
		$scope.employers = response.data;
		console.log($scope.employers);
	});

	//get all countries
	$scope.get_country = function(){
		$http({
			method: 'GET',
			url: base_url+'page/applicants/all-country'
		}).then(function successCallback(response) {
			$scope.countries = response.data;
			console.log($scope.countries);
		});
	}

	//get all position
	$scope.get_position = function(){
		$http({
			method: 'GET',
			url: base_url+'page/applicants/all-position'
		}).then(function successCallback(response) {
			$scope.positions = response.data;
			console.log($scope.positions);
		});
	}

	//get advance search options
	$scope.show_advance_search = function(){
		$scope.get_country();
		$scope.get_position();
	}

	$scope.change_page = function(page_to){
		$scope.current_page = page_to;
		$scope.get_all_applicants();
	}

	$scope.str_pad = function(number){
		var str = "" + number;
		var pad = "000000000";
		var ans = pad.substring(0, pad.length - str.length) + str;
		return ans;
	}

	$scope.showcydreview = function(href){
	    $.ajax({
	        async:false,
	        url: base_url+'admin/applicants/review/'+href,
	        type:'GET',
	        cache:false,
	        dataType: 'html',
	        success:function( response ) {
	             $('#modalApplicantReview').find('.modal-body').html( response );
	        }
	    });
	}
}]);

function isValidDate(dateString) {
	if (dateString != null) {
	  var regEx = /^\d{4}-\d{2}-\d{2}$/;
	  return dateString.match(regEx) != null;
	}else{
		return false;
	}
}