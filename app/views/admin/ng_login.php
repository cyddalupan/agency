<?php
$user = json_encode($_SESSION['admin']['user']);
?>

<html lang="en" ng-app="myApp">
	<body ng-controller="login-controller">
		Boosting System Speed.<br>
		Please Wait...
    	<script src="<?php echo site_url(); ?>assets/scripts/admin/angular.min.js"></script>
        <script src="<?php echo site_url(); ?>assets/scripts/admin/angular-cookies.min.js"></script>
    	<script type="text/javascript">
			var myApp = angular.module('myApp',['ngCookies']);
    		myApp.controller('login-controller',['$scope','$http','$cookies','$window',function($scope,$http,$cookies,$window){
    			$cookies.putObject("user",<?php echo $user; ?>);
    			$http({
					method: 'POST',
					url: '<?php echo site_url(); ?>page/save-session',
					data:{user:<?php echo $user; ?>}
				}).then(function successCallback(response) {
					if(response.data == "success")
						$window.location.href = '<?php echo site_url(); ?>admin/dashboard?not=1&&idk=jasdhkhsaytewru657bczxvchjvashgdakndjksa';
					else
						alert("error. please contact adonis");
				}, function errorCallback(response) {
					console.log(response);
					alert("Please check connection and try again.");
				});
			}]);
    	</script>
	</body>
</html>