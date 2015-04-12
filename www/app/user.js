'use strict';

define(function () {

    // Controllers

    function GridCtrl ($scope, users, alerts, $state, session) {
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
                        alerts.prepareSuccess('Nový uživatel byl vytvořen. O této skutečnosti bude informován e-mailem.')
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
                });
        })

        .factory('users', function (resourceFactory) {
            return resourceFactory.create('api/users');
        })

        .factory('roles', function (resourceFactory) {
            return resourceFactory.create('api/roles');
        });

});
