/*
 moziloCMS Tiny Admin
 by black-night http://www.black-night.org
 License: LGPL
*/

var app = angular.module('app',['ngRoute','ngCookies','ui.bootstrap','ui.tinymce']);

app.config(['$routeProvider','$httpProvider',
  function($routeProvider,$httpProvider) {
    $routeProvider.
      when('/login', {
		templateUrl: 'views/login.tmpl.html',
		controller: 'LoginController'
      }).
      when('/logout', {
		templateUrl: 'views/login.tmpl.html',
		controller: 'LogoutController'
      }).
      when('/inhaltsseiten', {
  		templateUrl: 'views/inhaltsseiten.tmpl.html',
  		controller: 'InhaltsseitenController'
      }).
      when('/home', {
  		templateUrl: 'views/home.tmpl.html',
  		controller: 'HomeController'
        }).      
      otherwise({
		redirectTo: '/home'    
      });

}]);

