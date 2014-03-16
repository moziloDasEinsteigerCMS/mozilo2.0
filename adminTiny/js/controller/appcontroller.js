/*
 moziloCMS Tiny Admin
 by black-night http://www.black-night.org
 License: LGPL
*/
app.controller("AppController", function($scope, $http){
	$scope.loggedin = false;
	$scope.$on('LogedInEvent',function(){
		$http({
			methode: 'GET',
			url: 'index.php',
			params: {'function':'logedin'}
		}).success(function(data, status, headers, config) {
			$scope.loggedin = data;
			if (!data) {
				$location.path('/login');
			}
		});		
	});
	$scope.$watch('activePage',function(newValue, oldValue) {
		if ((newValue !== oldValue)) {
			$scope.cssActiveHome = '';
			$scope.cssActiveInhaltsseiten = '';
			$scope.cssActiveLogout = '';
			if (newValue == 'home') {
				$scope.cssActiveHome = 'active';	
			}else if (newValue == 'inhaltsseiten') {
				$scope.cssActiveInhaltsseiten = 'active';
			}else if (newValue == 'logout') {
				$scope.cssActiveLogout = 'active';
			}	
		}
	});		
});