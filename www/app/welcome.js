'use strict';

define(['app/rest', 'app/gui', 'app/user'], function () {

    // Controllers

    function signInCtrl ($scope, $http, sessions, session, alerts, $state, $cookies, Response) {
        $scope.signing = false;

        $scope.signIn = function () {
            $scope.signing = true;

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
                    session.start(data.token, data.user);

                    $state.go('app.orders');
                })

                .error(function (error) {
                    switch (error.type) {
                        case 'unknownEmail':
                            alerts.clear();
                            alerts.showError('Uživatel se zadaným e-mailem v systému neexistuje.');
                            break;

                        case 'incorrectPassword':
                            alerts.clear();
                            alerts.showError('Zadáno chybné heslo k uživatelskému účtu.');
                            break;

                        default:
                            Response.defaultErrorHandler();
                    }
                })
                .finally(function () {
                    $scope.signing = false;
                });

            $scope.password = '';
        };
    }

    function passwordFormCtrl($scope, $state, users, alerts, Response, sessions, $http, $cookies, session) {
        $scope.saving = false;

        $scope.setPassword = function () {
            $scope.saving = true;

            users.updateAll({
                password: $scope.password
            }, {
                token: {
                    key: $state.params.token
                }
            }).success(function (user) {
                sessions.create({
                        user: {
                            email: user.email,
                            password: $scope.password
                        }
                    })
                    .success(function (data) { // todo: refactor, code duplicity
                        // set authentication header
                        $http.defaults.headers.common['X-Session-Token'] = data.token;

                        // save token to cookie
                        var expiration = new Date;
                        expiration.setDate(expiration.getDate() + 14); // 14 days
                        $cookies.put('session-token', data.token, {
                            expires: expiration
                        });

                        // set session value
                        session.start(data.token, data.user);

                        $state.go('app.orders');
                    })

            }).error(function (error) {
                if (error.type === 'unknownToken') {
                    alerts.clear();
                    alerts.showError('Heslo se nepodařilo nastavit. Pravděpodobně vypršela platnost tohoto odkazu.');

                } else {
                    Response.defaultErrorHandler();
                }

            }).finally(function () {
                $scope.saving = false;
            });
        };
    }

    // Model

    /**
     * Session service maintaining current session.
     * @constructor
     */
    function Session() {
        this._active = false;
    }

    /**
     * Starts new session.
     * @param {string} token
     * @param {Object} user
     */
    Session.prototype.start = function (token, user) {
        this._active = true;
        this.token = token;
        this.user = user;
    };

    /**
     * Terminates current session.
     */
    Session.prototype.terminate = function () {
        this._active = false;
    };

    /**
     * Returns true if session is active.
     * @returns {boolean}
     */
    Session.prototype.isActive = function () {
        return this._active;
    };

    // Configuration

    angular.module('app.welcome', ['ui.router', 'app.rest', 'app.gui', 'app.user'])

        .config(function ($stateProvider) {
            $stateProvider
                .state('welcome.signIn', {
                    url: '/',
                    templateUrl: 'app/welcome/signIn.html',
                    controller: signInCtrl
                })
                .state('welcome.setPassword', {
                    url: '/set-password/{token}',
                    templateUrl: 'app/welcome/passwordForm.html',
                    controller: passwordFormCtrl
                });
        })

        .factory('sessions', function (resourceFactory) {
            return resourceFactory.create('api/sessions');
        })

        .service('session', Session);

});
