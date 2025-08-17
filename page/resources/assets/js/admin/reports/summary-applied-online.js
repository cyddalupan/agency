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