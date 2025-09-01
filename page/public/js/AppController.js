myApp.controller('site-controller', 
	['$scope','$http','$filter','$location','$cookies','$window','$rootScope','$timeout', 
	function($scope,$http,$filter,$location,$cookies,$window,$rootScope,$timeout) 
	{
	
	/*VARIABLES
	########################################################################
	########################################################################
	########################################################################
	*/

	//main variables
	$scope.base_url = base_url;
	$scope.activePage = "";
	$scope.hide_ci_page = 0;

	//inital value
	$scope.current_page = 1;
	$scope.applicants = 0;
	$scope.employers = 0;
	$scope.countries = 0;
	$scope.positions = 0;
	$scope.noresult = 0;
	$scope.url_status = 'all';
	$scope.user = $cookies.getObject("user");
	$scope.traning_branch_id = 0;
	$scope.isTrainingAdmin = 0;
	$scope.notifmedia = 0;
	$scope.notifstatus = 0;
	$scope.notifencodebranch = 0;
	$scope.log_list = [];

	//search value
	$scope.keyword = "";
	$scope.applicant_position_type = 'null';
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
	$scope.skill = 'all';

	//show and hide parts
	$scope.show_all_applicants = 1;
	$scope.show_applicant_loading = 1;

	/*METHODS
	########################################################################
	########################################################################
	########################################################################
	*/

	/**
	 * change the activePage variable
	 * depends on the button clicked
	 */
	$scope.redirect = function(pageName,id){
		//if no value passed
   		id = typeof id !== 'undefined' ? id : "";

   		$scope.hide_ci_page = 1;

   		$scope.url = pageName+"/"+id;
			$location.path($scope.url);
			$scope.activePage = pageName;
			$scope.activePageId = id;
		
			$scope.current_page = 1;
	}

	/**
	 * used by ng-show
	 * to test if the page is active
	 * by testing if the var activePage
	 * is equal to page using this function
	 */
	$scope.imActive = function(imPageName){
		if(imPageName == $scope.activePage)
			return 1;
		else
			return 0;
	}

	/**
	 * When A file is uploaded to a applicant.
	 * users needs to see the count.
	 * if the session is no longer on the server,
	 * user will be log out
	 */
	$scope.get_notification_count_media = function(){	
		$http({
			method: 'POST',
			url: base_url+'page/get_notification_count_media'
		}).then(function successCallback(response) {
			$scope.notifmedia = response.data;
			$cookies.put('notif_media_count', $scope.notifmedia);
		}, function errorCallback(response) {
			console.log(response);
			alert("Please check connection and try again.");
		});
	}
	
	/**
	 * When A file is uploaded to a applicant.
	 * users needs to see the count.
	 */
	$scope.get_notification_count_status = function(){
		$http({
			method: 'POST',
			url: base_url+'page/get_notification_count_status'
		}).then(function successCallback(response) {
			$scope.notifstatus = response.data;
			$cookies.put("notif_status_count",response.data);
		}, function errorCallback(response) {
			console.log(response);
			alert("Please check connection and try again.");
		});
	}

	/**
	 * Get notification count of applicant with 
	 * branch id and encode by a training admin
	 */
	$scope.get_notification_count_encodebranch = function(){
		$http({
			method: 'POST',
			url: base_url+'page/get_notification_count_encodebranch'
		}).then(function successCallback(response) {
			$scope.notifencodebranch = response.data;
			$cookies.put("notification_count_encodebranch",response.data);
		}, function errorCallback(response) {
			console.log(response);
			alert("Please check connection and try again.");
		});
	}

	/**
	 * User Loging out, removes user session on laravel
	 * and deletes user cookies
	 */
	$scope.logout = function(){
		var cookies = $cookies.getAll();
		angular.forEach(cookies, function (v, k) {
		    $cookies.remove(k);
		});
		$http({
			method: 'POST',
			url: base_url+'page/logout'
		}).then(function successCallback(response) {
			if(response.data == "success")
				$window.location.href = base_url+'admin/dashboard?logout';
		}, function errorCallback(response) {
			console.log(response);
			alert("Please check connection and try again.");
		});
	}

	/**
	 * Showing All applicants with page,
	 * this also handles advance search
	 * ang pages with different applicant status
	 */
	$scope.get_all_applicants = function(){

		//show and hide parts
		$scope.show_all_applicants = 0;
		$scope.show_applicant_loading = 1;
		//get from laravel API
		$http({
			method: 'POST',
			url: base_url+'page/applicants/all-applicants?page='+$scope.current_page,
			data: { 
				'keyword' : $scope.keyword, 
				'applicant_position_type' : $scope.applicant_position_type,
				'branchID' : $scope.traning_branch_id,
				'status' : $scope.url_status,
				'skill' : $scope.skill,
				'isTrainingAdmin' : $scope.isTrainingAdmin,
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
			if(response.data.data.length === 0){
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
				if(value.applicant_photo === '' || value.applicant_photo === 0){
					$scope.applicants.data[key].image = base_url+'assets/images/admin/avatars/no-picture.jpg';	
				}else{
					$scope.applicants.data[key].image = base_url+'files/applicant/'+value.applicant_photo;
				}

				//statusColors
				$scope.applicants.data[key].statusColors = 'label-'+$scope.statuses[value.applicant_status].statusColors;

				//statusText
				$scope.applicants.data[key].statusText = $scope.statuses[value.applicant_status].statusText;

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
			}//pager loop end

			//for selection at bottom
			$scope.get_all_employers();

			//show applicants and hide the loader
			$scope.show_all_applicants = 1;
			$scope.show_applicant_loading = 0;
		});
	}

	/**
	 * Get the Statuses from DB, for re use.
	 * Put it to cookie to avoid reloading
	 */
	$scope.get_status = function(){
		if(angular.isUndefined($cookies.getObject('statuses') )){
			$http({
				method: 'POST',
				url: base_url+'page/get_statuses'
			}).then(function successCallback(response) {
				$scope.statuses = response.data;
				$cookies.putObject('statuses',$scope.statuses);
			}, function errorCallback(response) {
				console.log(response);
				alert("Please check connection and try again.");
			});
		}else{
			$scope.statuses = $cookies.getObject('statuses');
		}
	}

	//click advance seach
	$scope.advance_search = function(){
		$scope.current_page = 1;
		$scope.get_all_applicants();
	}

	//get all employers
	$scope.get_all_employers = function(){
		if(angular.isUndefined($cookies.getObject('employers') )){
			$http({
				method: 'GET',
				url: base_url+'page/applicants/all-employer'
			}).then(function successCallback(response) {
				$scope.employers = response.data;
				$cookies.putObject('employers',$scope.employers);
			});
		}else{
			$scope.employers = $cookies.getObject('employers');
		}
	}

	//get all countries
	$scope.get_country = function(){
		if(angular.isUndefined($cookies.getObject('countries') )){
			$http({
				method: 'GET',
				url: base_url+'page/applicants/all-country'
			}).then(function successCallback(response) {
				$scope.countries = response.data;
				$cookies.putObject('countries',$scope.countries);
			});
		}else{
			$scope.countries = $cookies.getObject('countries');
		}
	}

	//get all position
	$scope.get_position = function(){
		if(angular.isUndefined($cookies.getObject('positions') )){
			$http({
				method: 'GET',
				url: base_url+'page/applicants/all-position'
			}).then(function successCallback(response) {
				$scope.positions = response.data;
				$cookies.putObject('positions',$scope.positions);
			});
		}else{
			$scope.positions = $cookies.getObject('positions');
		}
	}

	//get advance search options
	$scope.show_advce_search = function(){
		
		if(angular.isUndefined($scope.showAdvanceSearch) )
			$scope.showAdvanceSearch = 1;
		else
			$scope.showAdvanceSearch = undefined;
		
		if(angular.isUndefined($scope.countries) )
			$scope.get_country();
		if(angular.isUndefined($scope.positions) )
			$scope.get_position();
	}

	/**
	 * User for Pagination
	 * Change current_page and call the get applicant function
	 */
	$scope.change_page = function(page_to){
		$scope.current_page = page_to;
		$scope.get_all_applicants();
	}

	/**
	 * Just Adding zero in front if applicant ID
	 */
	$scope.str_pad = function(number){
		var str = "" + number;
		var pad = "000000000";
		var ans = pad.substring(0, pad.length - str.length) + str;
		return ans;
	}

	/**
	 * Show Review Page, currently not in use
	 * because the review opens on new tab
	 */
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

	/**
	 * Move user Session data to scope
	 * then, check if status already in cookie
	 * if not get before gettting the applicant list
	 */
	$scope.update_applicant_list = function(){
		//check user type
		if($scope.user.user_type == 12){
			$scope.traning_branch_id = $scope.user.branch_id;
		}

		if($scope.user.user_type == 11){
			$scope.isTrainingAdmin = 1;
		}


		if($scope.activePageId === ''){
			$scope.url_status = 'all';
		}else{
			$scope.url_status = $scope.activePageId;
		}
		
		//
		if(angular.isUndefined($scope.statuses) ){
			$scope.get_status();
			$scope.$watch('statuses', function(newVal, oldVal){
				$scope.get_all_applicants();
			});
		}else{
			$scope.get_all_applicants();
		}
	}

	//Show the list of user logs
	$scope.get_status_notifications = function(){
		$scope.show_notification_loading = 1;
		$scope.log_list = "";
		$http({
			method: 'POST',
			url: base_url+'page/get_status_notifications'
		}).then(function successCallback(response) {
			console.log(response.data);
			$scope.log_list = response.data;
			$scope.show_notification_loading = 0;
			$scope.get_notification_count_status();
		}, function errorCallback(response) {
			console.log(response);
			alert("Please check connection and try again.");
		});
	}

	//Show the list of files uploaded
	$scope.get_media_notifications = function(){
		$scope.show_notification_loading = 1;
		$scope.media_list = "";
		$http({
			method: 'POST',
			url: base_url+'page/get_media_notifications'
		}).then(function successCallback(response) {
			$scope.media_list = response.data;
			$scope.show_notification_loading = 0;
			$scope.get_notification_count_media;
		}, function errorCallback(response) {
			console.log(response);
			alert("Please check connection and try again.");
		});
	}

	/*AUTO LOADS
	########################################################################
	########################################################################
	########################################################################
	*/

	/**
	 * In Case the Cookie is not set, logout the use
	 */
	if(angular.isUndefined($cookies.getObject("user"))){
		$window.location.href = base_url+'admin/dashboard?logout';
	}

	/**
	 * In Case the Settings in not yet in Cookie.
	 * Get Settings
	 */

	if(angular.isUndefined($cookies.getObject("settings"))){
		$http({
			method: 'POST',
			url: base_url+'page/get_settings'
		}).then(function successCallback(response) {
			$cookies.putObject("settings",response.data);
			$scope.settings = $cookies.getObject("settings");
		}, function errorCallback(response) {
			console.log(response);
			alert("Please check connection and try again.");
		});
	}else{
		$scope.settings = $cookies.getObject("settings");
	}

	/**
	 * On Load, Gets The Url Variable.
	 * to find out what page should be shown.
	 * Split the URL to page name, and id variable, if there is.
	 */
	if($location.path() !== ''){
		splitroute = $location.path().split("/");
		$scope.activePage = splitroute[1];
		$scope.activePageId = splitroute[2];
		$scope.hide_ci_page = 1;
	}

	/**
	 * Get Notification Count of "media" to be shown on top
	 * put to cookie to avoid loading 
	 */
	if(angular.isUndefined($cookies.get("notif_media_count"))){
		$scope.get_notification_count_media();
	}else{
		$scope.notifmedia = $cookies.get("notif_media_count");
	}

	/**
	 * Get Notification Count of "status" to be shown on top
	 * put to cookie to avoid loading 
	 */
	if(angular.isUndefined($cookies.get("notif_status_count"))){
		$scope.get_notification_count_status();
	}else{
		$scope.notifstatus = $cookies.get("notif_status_count");
	}

	/**
	 * Get Notification Count of "status" to be shown on top
	 * put to cookie to avoid loading 
	 */
	if(angular.isUndefined($cookies.get("notification_count_encodebranch"))){
		$scope.get_notification_count_encodebranch();
	}else{
		$scope.notifencodebranch = $cookies.get("notification_count_encodebranch");
	}

	/**
	 * Get Media and status notification count, after the page was loaded a long time.
	 * And repeat it every once in a while
	 */
	$scope.get_notification_loop = function(){
		$timeout(function(){
			$scope.get_notification_count_status();
			$timeout(function(){
				$scope.get_notification_count_media();
				$timeout(function(){
					$scope.get_notification_count_encodebranch();	
					if (!$scope.$$phase) {
						$scope.$apply();
					}
					$scope.get_notification_loop();
				},5000);
			},5000);
		},15000);
	}
	
	/**
	 * This is used on all applicants
	 * because the lineup checkbox cannot be used
	 * on selected applicants
	 */
	$scope.show_line_up_checkbox = function(statusText){
		if(statusText == 'Selected'){
			return 0;
		}else{
			return 1;
		}
	}
	
	/**
	 * This is used on all applicants
	 * because the lineup checkbox cannot be used
	 * on selected applicants
	 */
	$scope.hide_line_up_checkbox = function(statusText){
		if(statusText == 'Selected'){
			return 1;
		}else{
			return 0;
		}
	}

	$scope.is_show_repat_date = function(repat_date){
		if(repat_date == '0000-00-00 00:00:00')
			return 0;
		else
			return 1;
	}

	/**
	 * Change User password.
	 * click change password on header
	 * when submit this handles the logic
	 */
	$scope.change_password = function(){
		if(this.newpassword == this.confirmpassword){
			$http({
				method: 'POST',
				url: base_url+'admin/users/change_password/'+this.oldpassword+'/'+this.newpassword,
			}).then(function successCallback(response) {
				if(response.data == 0){
					alert("Incorrect Old Password.");
				}
				if(response.data == 1){
					alert("Password has been changed.");
					$scope.logout();
				}
			}, function errorCallback(response) {
				console.log(response);
				alert("Please check connection and try again.");
			});
		}else{
			alert('New Password and Confirm Password did not match!')
		}
	}

	/**
	 * Test user login every * seconds
	 * to make sure the session on the server still exists
	 */
	$scope.testlogin = function(){
		$http({
			method: 'GET',
			url: base_url+'page/check_login'
		}).then(function successCallback(response) {
			if(response.data == 0)
			{
				alert('Session Expired Please re-login');
				$scope.logout();
			}else{
				console.log('login Test Passed');
				$scope.user = response.data;
				$cookies.putObject("user",$scope.user);
			}
		}, function errorCallback(response) {
			console.log(response);
			alert("Please check connection and try again.");
		});

		$timeout(function(){
			$scope.testlogin();
		},90000);
	}	
	$scope.testlogin();

	/*PAGE INITIALIZER AND LISTENER
	########################################################################
	########################################################################
	########################################################################
	*/

	$scope.$watch('url', function(newVal, oldVal){
		//ON PAGE LOADS
		if($scope.activePage == 'applicants'){
			$scope.update_applicant_list();
		}

		if($scope.activePage == 'stats_notification'){
			$scope.get_status_notifications();
		}

		if($scope.activePage == 'media_notification'){
			$scope.get_media_notifications();
		}

		//VARIABLE LISTENERS
	});

	//auto run	
	$scope.get_notification_loop();

	/**
	 * check if user come back to the page he came before angular load the page.
	 * and hide angular page and show CI page again.
	 */
	$rootScope.$watch(function() { 
		return $location.path(); 
	},
	function(a){  
		if($location.path() === ''){
			$scope.activePage = "";
			$scope.hide_ci_page = 0;
		}
	});


}]);