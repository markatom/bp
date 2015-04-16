'use strict';

define(function () {

    // Controllers

    function GridCtrl($scope, clients) {
        $scope.clients = [];

        clients.readAll().success(function (data) {
            $scope.clients = data;
        });
    }

    // Configuration

    angular.module('app.client', [])

        .config(function ($stateProvider) {
            $stateProvider
                .state('app.client', {
                    abstract: true,
                    url: '/client',
                    template: '<div ui-view></div>'
                })

                .state('app.client.grid', {
                    url: '/grid',
                    templateUrl: 'app/client/grid.html',
                    controller: GridCtrl
                });
        })

        .factory('clients', function (resourceFactory) {
            return resourceFactory.create('api/clients');
        });

});
