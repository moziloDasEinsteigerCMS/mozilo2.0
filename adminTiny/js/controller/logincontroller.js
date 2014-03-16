/*
 moziloCMS Tiny Admin
 by black-night http://www.black-night.org
 License: LGPL
*/
app.controller("LoginController", function($scope, $http, $location,$rootScope){
	$scope.$emit('LogedInEvent');	
	$rootScope.activePage = 'login';
	if (!$scope.loggedin) {
		$location.path('/logout');
	}	
	$scope.showerror = false;
	$scope.message = "";
	$scope.submit = function(form) {
		if (typeof($scope.user) != "undefined" && typeof($scope.pw) != "undefined" && $scope.user.length > 0 && $scope.pw.length > 0) {
			$http({
				methode: 'GET',
				url: 'index.php', 
				params: {'function':'login','user':$scope.user,'pw':$scope.pw}
			}).success(function(data, status, headers, config) {
				if (data == 'false') {
					$scope.showerror = true;
					$scope.message = "Username oder Passwort ist falsch.";					
				}else if(data == 'true'){
					$scope.showerror = false;
					$location.path('/home');					
				}
			});
		}
	};
});

app.controller("LogoutController", function($scope, $http,$location,$rootScope){
	$rootScope.activePage = 'logout';
	$http({
		methode: 'GET',
		url: 'index.php',
		params: {'function':'logout'}
	}).success(function(data, status, headers, config) {
		$scope.message = "Abmelden erfolgreich";
		$location.path('/login');
	});
});

app.controller("HomeController", function($scope, $http,$location,$rootScope){
	$scope.$emit('LogedInEvent');
	$rootScope.activePage = 'home';
});