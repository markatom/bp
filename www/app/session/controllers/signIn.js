'use strict';

define(function () {

    return function ($scope, $http, sessions) {
        $scope.alerts = [];

        $scope.signIn = function () {
            sessions
                .create($scope.email, $scope.password, $scope.longLife)

                .success(function (session) {
                    $http.defaults.headers.common['X-Session-Token'] = session.token;
                    alert('Přihlášen');
                })

                .error(function (error) {
                    switch (error.type) {
                        case 'unknownEmail':
                            $scope.alerts.push({
                                type: 'danger',
                                message: 'Uživatel se zadaným e-mailem v systému neexistuje.'
                            });
                            break;

                        case 'incorrectPassword':
                            $scope.alerts.push({
                                type: 'danger',
                                message: 'Zadáno chybné heslo k uživatelskému účtu.'
                            });
                            break;
                    }
                });

            $scope.password = '';
        };
    };

});
