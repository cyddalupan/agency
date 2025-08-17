myApp.controller('dashboard', ['$scope','$timeout', function($scope,$timeout) {

	window.onload = function() {
	    $timeout(function(){
	    	$('iframe#dashboardstats').attr('src', base_url+"admin/dashboard/stats");    
	    	$('.dashboardstats-loading').hide(1);
	    },1000);

	    $timeout(function(){
			$('iframe#updatesboard').attr('src', base_url+"admin/dashboard/updatesboard"); 
	    	$('.updatesboard-loading').hide(1);
	    },2000);
	};
    
}]);