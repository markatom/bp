'use strict';

define(function () {

    // Controllers

    function GridCtrl($scope, clients) {
        $scope.clients = [];
        var sort = {};

        function loadGrid() {
            clients.readAll({sort: sortToString()}).success(function (data) {
                $scope.clients = data;
            });
        }

        function sortToString() {
            return sort.by ? (sort.dir === 'asc' ? '' : '-') + sort.by : null;
        }

        loadGrid();

        $scope.sort = function (name) {
            if (sort.by === name) {
                if (sort.dir === 'asc') {
                    sort.dir = 'desc';
                } else {
                    sort = {};
                }
            } else {
                sort = {by: name, dir: 'asc'};
            }
            loadGrid();
        };

        $scope.indicator = function (name) {
            if (sort.by === name) {
                return sort.dir === 'asc' ? '▼' : '▲';
            }
        };
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
