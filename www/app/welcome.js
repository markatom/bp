'use strict';

define(['app/rest', 'app/gui', 'app/user'], function () {

    // Controllers

    function SignInCtrl ($scope, $http, sessions, session, alerts, $state, $cookies, Response) {
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

                    $state.go('app.order.grid');
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

    function LostPasswordCtrl($scope, users, alerts, Response) {
        $scope.send = function () {
            $scope.sending = true;

            users.updateAll({}, {
                email: $scope.email,
                'change-password': true
            }).success(function () {
                alerts.clear();
                alerts.showSuccess('Nastavení nového hesla provedete pomocí e-mailu, který Vám byl právě odeslán.');
                $scope.email = '';

            }).error(function (error) {
                if (error.type === 'unknownUser') {
                    alerts.clear();
                    alerts.showError('Žádný uživatel s e-mailovou adresou ' + $scope.email + ' není v systému evidován.');

                } else {
                    Response.defaultErrorHandler();
                }

            }).finally(function () {
                $scope.sending = false;
            });
        };
    }

    function PasswordFormCtrl($scope, $state, users, alerts, Response, sessions, $http, $cookies, session) {
        $scope.saving = false;

        $scope.setPassword = function () {
            if ($scope.password.length < 5) {
                alerts.clear();
                alerts.showInfo('Heslo musí mít alespoň pět znaků.');
                return;
            }

            $scope.saving = true;

            users.updateAll({
                password: $scope.password
            }, {
                'token[key]': $state.params.token
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
                        alerts.prepareSuccess('Heslo bylo úspěšně nastaveno.')
                        $state.go('app.order.grid');
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
                    controller: SignInCtrl
                })

                .state('welcome.setPassword', {
                    url: '/set-password/{token}',
                    templateUrl: 'app/welcome/passwordForm.html',
                    controller: PasswordFormCtrl
                })

                .state('welcome.lostPassword', {
                    url: '/lost-password',
                    templateUrl: 'app/welcome/lostPassword.html',
                    controller: LostPasswordCtrl
                });
        })

        .factory('sessions', function (resourceFactory) {
            return resourceFactory.create('api/sessions');
        })

        .service('session', Session);

});
