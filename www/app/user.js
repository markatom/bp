'use strict';

define(function () {

    // Controllers

    function GridCtrl ($scope, users, $q) {
        $scope.users = [];

        users.readAll().success(function (data) {
            $scope.users = data;
        });
    }

    function FormCtrl($scope, $state, users, roles, alerts) {
        $scope.sending = false;

        roles.readAll().success(function (data) {
            $scope.roles = data;
        });

        if ($state.current.name === 'app.user.edit') {
            users.read($state.params.id).success(function (data) {
                $scope.user = data;
            });
        }

        $scope.save = function () {
            $scope.sending = true;

            users.update($state.params.id, $scope.user).success(function () {
                alerts.success('Změny byly úspěšně uloženy.');
                $state.go('app.user.grid');
            });
        };
    }

    // Configuration

    angular.module('app.user', ['ui.grid', 'ui.router', 'app.rest'])

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
                });
        })

        .factory('users', function (resourceFactory) {
            return resourceFactory.create('api/users');
        })

        .factory('roles', function (resourceFactory) {
            return resourceFactory.create('api/roles');
        });

});
