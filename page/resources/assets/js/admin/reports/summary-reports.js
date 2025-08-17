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