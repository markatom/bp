'use strict';

define(function () {

    // Controllers

    function GridCtrl ($scope, users, alerts, session) {
        $scope.users = [];
        $scope.deleting = {};
        $scope.session = session;

        function loadGrid() {
            users.readAll().success(function (data) {
                $scope.users = data;
            });
        }

        loadGrid();

        $scope.delete = function (id) {
            if ($scope.deleting[id]) {
                return;
            }

            if (!confirm('Opravdu chcete smazat uživatele?')) {
                return;
            }

            $scope.deleting[id] = true;

            users.delete(id)
                .success(function () {
                    alerts.clear();
                    alerts.showSuccess('Uživatel byl úspěšně smazán.');
                    loadGrid();
                })
                .finally(function () {
                    delete $scope.deleting[id];
                });
        };
    }

    function FormCtrl($scope, $state, users, roles, alerts, Response) {
        $scope.user = {};
        $scope.sending = false;
        $scope.editation = $state.current.name === 'app.user.edit';

        roles.readAll().success(function (data) {
            $scope.roles = data;
        });

        if ($scope.editation) {
            users.read($state.params.id).success(function (data) {
                $scope.user = data;
            });
        }

        $scope.save = function () {
            if (!(/^("([ !\x23-\x5B\x5D-\x7E]*|\\[ -~])+"|[-a-z0-9!#$%&'*+\/=?^_`{|}~]+(\.[-a-z0-9!#$%&'*+\/=?^_`{|}~]+)*)@([0-9a-z\u00C0-\u02FF\u0370-\u1EFF]([-0-9a-z\u00C0-\u02FF\u0370-\u1EFF]{0,61}[0-9a-z\u00C0-\u02FF\u0370-\u1EFF])?\.)+[a-z\u00C0-\u02FF\u0370-\u1EFF][-0-9a-z\u00C0-\u02FF\u0370-\u1EFF]{0,17}[a-z\u00C0-\u02FF\u0370-\u1EFF]$/i).test($scope.user.email)) {
                alerts.clear();
                alerts.showInfo('E-mailová adresa není ve správném tvaru. Zkontrolujte prosím, zda neobsahuje překlepy.');
                document.getElementById('email').focus();
                return;
            }

            $scope.sending = true;

            if ($scope.editation) {
                users.update($state.params.id, $scope.user)
                    .success(function () {
                        alerts.prepareSuccess('Změny byly úspěšně uloženy.');
                        $state.go('app.user.grid');
                    })
                    .finally(function () {
                        $scope.sending = false;
                    });

            } else {
                users.create($scope.user)
                    .success(function () {
                        alerts.prepareSuccess('Nový uživatel byl vytvořen. O této skutečnosti bude informován e-mailem.');
                        $state.go('app.user.grid');
                    })
                    .error(function (data, code) {
                        if (code === 409) { // conflict
                            alerts.clear();
                            alerts.showWarning('Uživatel s tímto e-mailem je již v systému evidován.');

                        } else {
                            Response.defaultErrorHandler();
                        }
                    })
                    .finally(function () {
                        $scope.sending = false;
                    });
            }
        };
    }

    function ProfileCtrl($scope, users, alerts, session) {
        $scope.saving = false;
        $scope.fullName = session.user.fullName;
        $scope.email = session.user.email;

        $scope.save = function () {
            $scope.saving = true;

            users.update(session.user.id, {
                fullName: $scope.fullName
            }).success(function (data) {
                session.user = data;
                alerts.clear();
                alerts.showSuccess('Změny byly úspěšně uloženy.');

            }).finally(function () {
                $scope.saving = false;
            });
        };
    }

    // Configuration

    angular.module('app.user', ['ui.router', 'app.rest'])

        .config(function ($stateProvider) {
            $stateProvider

                .state('app.user', {
                    abstract: true,
                    url: '/user',
                    template: '<div ui-view></div>'
                })

                .state('app.user.grid', {
                    url: '/grid',
                    templateUrl: 'app/user/grid.html',
                    controller: GridCtrl
                })

                .state('app.user.edit', {
                    url: '/edit/{id}',
                    templateUrl: 'app/user/form.html',
                    controller: FormCtrl
                })

                .state('app.user.add', {
                    url: '/add',
                    templateUrl: 'app/user/form.html',
                    controller: FormCtrl
                })

                .state('app.user.profile', {
                    url: '/profile',
                    templateUrl: 'app/user/profile.html',
                    controller: ProfileCtrl
                });
        })

        .factory('users', function (resourceFactory) {
            return resourceFactory.create('api/users');
        })

        .factory('roles', function (resourceFactory) {
            return resourceFactory.create('api/roles');
        });

});
