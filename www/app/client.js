'use strict';

define(['app/rest', 'app/gui'], function () {

    // Controllers

    function GridCtrl($scope, clients, $timeout) {
        $scope.clients = [];
        $scope.filters = {};

        var sort = {};
        var timeoutPromise;

        $scope.$watch('filters', filtersChanged, true);

        var firstTime = true;
        function filtersChanged () {
            if (firstTime) {
                firstTime = false;
                return;
            }
            $scope.loading = true;
            $timeout.cancel(timeoutPromise);
            timeoutPromise = $timeout(loadGrid, 1000);
        }

        function loadGrid() {
            $scope.loading = true;
            var query = filtersToQuery();
            query.sort = sortToString();
            clients.readAll(query).success(function (data) {
                $scope.clients = data;
                $scope.loading = false;
            });
        }

        loadGrid();

        function sortToString() {
            return sort.by ? (sort.dir === 'asc' ? '' : '-') + sort.by : null;
        }

        function filtersToQuery() {
            var params = {};
            for (var name in $scope.filters) {
                params['filters[' + name + ']'] = $scope.filters[name];
            }
            return params;
        }

        $scope.searchClicked = function () {
            if ($scope.search) {
                $scope.filters = {};
            }
            $scope.search = !$scope.search;
        };

        $scope.openDatePicker = function($event) {
            $event.preventDefault();
            $event.stopPropagation();
            $scope.opened = true;
        };

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

    function FormCtrl($scope, $state, clients, alerts) {
        $scope.client = {
            address: {
                country: 'Česká republika' // default value
            }
        };
        $scope.sending = false;
        $scope.editation = $state.current.name === 'app.client.edit';
        $scope.loading = $scope.editation;

        if ($scope.editation) {
            clients.read($state.params.id).success(function (data) {
                $scope.client = data;
                $scope.loading = false;
            });
        }

        $scope.openDatePicker = function($event) {
            $event.preventDefault();
            $event.stopPropagation();
            $scope.opened = true;
        };

        $scope.save = function () {
            $scope.sending = true;

            if ($scope.editation) {
                clients.update($state.params.id, $scope.client)
                    .success(function () {
                        alerts.prepareSuccess('Změny byly úspěšně uloženy.');
                        $state.go('app.client.grid');
                    })
                    .finally(function () {
                        $scope.sending = false;
                    });

            } else {
                clients.create($scope.client)
                    .success(function () {
                        alerts.prepareSuccess('Nový klient byl úspěšně vytvořen.');
                        $state.go('app.client.grid');
                    })
                    .finally(function () {
                        $scope.sending = false;
                    });
            }
        };
    }

    function DetailCtrl($scope, $state, clients) {
        $scope.client = {};
        $scope.loading = true;

        clients.read($state.params.id).success(function (data) {
            $scope.client = data;
            $scope.loading = false;
        });
    }

    // Configuration

    angular.module('app.client', ['ui.router', 'ui.bootstrap', 'app.rest', 'app.gui'])

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
                })

                .state('app.client.edit', {
                    url: '/edit/{id}',
                    templateUrl: 'app/client/form.html',
                    controller: FormCtrl
                })

                .state('app.client.add', {
                    url: '/add',
                    templateUrl: 'app/client/form.html',
                    controller: FormCtrl
                })

                .state('app.client.detail', {
                    url: '/detail/{id}',
                    templateUrl: 'app/client/detail.html',
                    controller: DetailCtrl
                });
        })

        .factory('clients', function (resourceFactory) {
            return resourceFactory.create('api/clients');
        });

});
