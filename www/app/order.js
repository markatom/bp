'use strict';

define(['app/rest', 'app/gui'], function () {

    // Controllers

    function GridCtrl($scope, orders, orderStates, $timeout) {
        $scope.orders = [];
        $scope.filters = {};
        $scope.states = [];

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
            orders.readAll(query).success(function (data) {
                $scope.orders = data;
                $scope.loading = false;
            });
        }

        loadGrid();

        orderStates.readAll().success(function (data) {
            $scope.states = data;
        });

        function sortToString() {
            return sort.by ? (sort.dir === 'asc' ? '' : '-') + sort.by : null;
        }

        function filtersToQuery() {
            function recursive(filters, params, fullName) {
                fullName = fullName || '';
                if (typeof filters == 'string') {
                    params['filters[' + fullName + ']'] = filters;
                } else {
                    for (var name in filters) {
                        recursive(filters[name], params, fullName === '' ? name : fullName + '.' + name);
                    }
                }
            }
            var params = {};
            recursive($scope.filters, params);
            return params;
        }

        $scope.searchClicked = function () {
            if ($scope.search) {
                $scope.filters = {};
            }
            $scope.search = !$scope.search;
        };

        $scope.openDatePicker = function($event, name) {
            $event.preventDefault();
            $event.stopPropagation();
            $scope[name + 'Opened'] = true;
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

        var rowColors = {
            processing: 'success',
            waiting: 'info',
            postponed: 'warning',
            cancelled: '',
            completed: ''
        };

        $scope.rowColor = function (state) {
            return rowColors[state.slug]
        }
    }

    // Configuration

    angular.module('app.order', ['ui.router', 'ui.bootstrap', 'app.rest', 'app.gui'])

        .config(function ($stateProvider) {
            $stateProvider
                .state('app.order', {
                    abstract: true,
                    url: '/order',
                    template: '<div ui-view></div>'
                })

                .state('app.order.grid', {
                    url: '/grid',
                    templateUrl: 'app/order/grid.html',
                    controller: GridCtrl
                })
        })

        .factory('orders', function (resourceFactory) {
            return resourceFactory.create('api/orders');
        })

        .factory('orderStates', function (resourceFactory) {
            return resourceFactory.create('api/order-states');
        });

});
