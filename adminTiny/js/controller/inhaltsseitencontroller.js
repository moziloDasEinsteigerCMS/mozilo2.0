/*
 moziloCMS Tiny Admin
 by black-night http://www.black-night.org
 License: LGPL
*/
app.controller("InhaltsseitenController", function($scope, $http, $location,$rootScope){
	$scope.debug = true;
	$scope.$emit('LogedInEvent');
	$rootScope.activePage = 'inhaltsseiten';
	$scope.$watch('content',function(newValue, oldValue) {
		if ((newValue !== oldValue) && (newValue !== tinymce.activeEditor.getContent())) {
			tinyMCE.activeEditor.setContent(newValue+' ');	
		}
	});
	$scope.loadPage = function(pageName) {
		$http({
			methode: 'GET',
			url: 'index.php',
			params: {'function':'inhaltsseiten','page':pageName}
		}).success(function(data, status, headers, config) {
			if (data == 'false') {
				$location.path('/');
			}
			$scope.content = data;			
			$scope.pagename = pageName;
			$scope.message = pageName+" - Seite geladen: \n"+JSON.stringify(data, null, 4);
		});	
	};
	$scope.save = function() {
		$http.post('index.php',JSON.stringify({'function':'save',typ: 'inhaltsseite',name: $scope.pagename, value: $scope.content}
	    )).success(function(data, status, headers, config) {
	    	if (data == 'false') {
	    		$location.path('/');
	    	}
	    	$scope.message = "Speichern erfolgreich: \n"+JSON.stringify(data, null, 4);
	    });	
	};
	$http({
		methode: 'GET',
		url: 'index.php',
		params: {'function':'inhaltsseiten'}
	}).success(function(data, status, headers, config) {
		if (data == 'false') {
			$location.path('/');
		}
		$scope.seiten = data;
		$scope.message = "Load all pages: \n"+JSON.stringify(data, null, 4);
	});	
});