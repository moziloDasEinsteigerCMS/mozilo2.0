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
		if ((newValue !== oldValue) && (tinymce.activeEditor != null) && (newValue !== tinymce.activeEditor.getContent())) {
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
	$scope.tinymceOptions = {
		language : 'de',
		element_format : 'html',
		height: window.innerHeight - 400,
		content_css: 'css/moziloSyntax.css',
		plugins: "print, code, image, fullscreen, link, contextmenu",
		formats: {
			//An mozilo Syntax anpassen
			bold : {classes: 'contentbold'},			
			italic: {classes: 'contentitalic'},
			underline: {classes: 'contentunderlined'},
			strikethrough: {classes: 'contentstrikethrough'},
			alignleft: {block: 'div', classes: 'alignleft'},
			aligncenter: {block: 'div', classes: 'aligncenter'},
			alignright: {block: 'div', classes: 'alignright'},
			alignjustify: {block: 'div', classes: 'alignjustify'},
			h1: {block: 'h1', classes: 'heading1'},
			h2: {block: 'h2', classes: 'heading2'},
			h3: {block: 'h3', classes: 'heading3'}
		},
		toolbar: 'undo redo | formatselect | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | fontselect fontsizeselect | bullist numlist outdent indent blockquote '+
				 '',
		//ToDo Bilder aus mozilo auslesen und an TinyMCE uebergeben
//	    image_list: [ 
//	                 {title: 'My image 1', value: 'http://www.tinymce.com/my1.gif'}, 
//	                 {title: 'My image 2', value: 'http://www.moxiecode.com/my2.gif'} 
//	                ],				 
        image_advtab: true
        //ToDo Dateiliste aus mozilo auslesen und an TinyMCE uebergeben


	};
});