'use strict';

define(['app/rest', 'app/gui'], function () {

    // Controllers

    function signInCtrl ($scope, $http, sessions, session, alerts, $state, $cookies, Response) {
        $scope.signIn = function () {
            sessions.create({
                    longLife: $scope.longLife,
                    user: {
                        email: $scope.email,
                        password: $scope.password
                    }
                })

                .success(function (data) {
                    // set authentication header
                    $http.defaults.headers.common['X-Session-Token'] = data.token;

                    // save token to cookie
                    var expiration = new Date;
                    expiration.setDate(expiration.getDate() + 14); // 14 days
                    $cookies.put('session-token', data.token, {
                        expires: expiration
                    });

                    // set session value
                    session = data;

                    $state.go('app.orders');
                })

                .error(function (error) {
                    switch (error.type) {
                        case 'unknownEmail':
                            alerts.error('Uživatel se zadaným e-mailem v systému neexistuje.');
                            break;

                        case 'incorrectPassword':
                            alerts.error('Zadáno chybné heslo k uživatelskému účtu.');
                            break;

                        default:
                            Response.defaultErrorHandler();
                    }
                });

            $scope.password = '';
        };
    }

    // Configuration

    angular.module('app.welcome', ['ui.router', 'app.rest', 'app.gui'])

        .config(function ($stateProvider) {
            $stateProvider.state('welcome.signIn', {
                url: '/',
                templateUrl: 'app/welcome/signIn.html',
                controller: signInCtrl
            });
        })

        .factory('sessions', function (resourceFactory) {
            return resourceFactory.create('api/sessions');
        })

        .value('session', null);

});
