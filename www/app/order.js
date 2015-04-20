'use strict';

define(['app/rest', 'app/gui', 'app/client', 'app/user'], function () {

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

    function FormCtrl($scope, $state, orders, alerts, clients, $q, users, orderDraft) {
        $scope.order = orderDraft.order
            ? orderDraft.order
            : {
                event: {},
                state: {
                    name: 'Zpracovává se',
                    slug: 'processing'
                }
            };
        $scope.sending = false;
        $scope.editation = $state.current.name === 'app.order.edit';
        $scope.loading = $scope.editation;
        $scope.originalState = 'processing';

        if ($scope.editation) {
            orders.read($state.params.id).success(function (data) {
                $scope.order = data;
                $scope.originalState = data.state.slug;
                $scope.loading = false;
            });
        }

        $scope.openDatePicker = function($event) {
            $event.preventDefault();
            $event.stopPropagation();
            $scope.opened = true;
        };

        $scope.loadClients = function (value) {
            return $q(function (resolve) {
                clients.readAll({'filters[fullName]': value})
                    .success(function (data) {
                        resolve(data);
                    });
            });
        };

        $scope.loadUsers = function (value) {
            return $q(function (resolve) {
                users.readAll({'filters[fullName]': value})
                    .success(function (data) {
                        resolve(data);
                    });
            });
        };

        $scope.addClient = function () {
            orderDraft.order = $scope.order;
            $state.go('app.client.add');
        };

        $scope.save = function () {
            if (!$scope.order.client) {
                alert('Vyberte prosím klienta ze seznamu.');
                document.getElementById('client').focus();
                return;
            }

            $scope.sending = true;

            if ($scope.order.event.date instanceof Date) {
                $scope.order.event.date.setHours(2); // fixes https://github.com/angular-ui/bootstrap/issues/2072
                $scope.order.event.date = $scope.order.event.date.toString();
            }

            if ($scope.editation) {
                orders.update($state.params.id, $scope.order)
                    .success(function () {
                        alerts.prepareSuccess('Změny byly úspěšně uloženy.');
                        $state.go('app.order.grid');
                    })
                    .finally(function () {
                        $scope.sending = false;
                    });

            } else {
                orders.create($scope.order)
                    .success(function () {
                        alerts.prepareSuccess('Nová objednávka byla úspěšně vytvořena.');
                        $state.go('app.order.grid');
                    })
                    .finally(function () {
                        $scope.sending = false;
                    });
            }
        };
    }

    // Configuration

    angular.module('app.order', ['ui.router', 'ui.bootstrap', 'app.rest', 'app.gui', 'app.client', 'app.user'])

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

                .state('app.order.edit', {
                    url: '/edit/{id}',
                    templateUrl: 'app/order/form.html',
                    controller: FormCtrl
                })

                .state('app.order.add', {
                    url: '/add',
                    templateUrl: 'app/order/form.html',
                    controller: FormCtrl
                });
        })

        .factory('orders', function (resourceFactory) {
            return resourceFactory.create('api/orders');
        })

        .factory('orderStates', function (resourceFactory) {
            return resourceFactory.create('api/order-states');
        })

        .value('orderDraft', {order: null})

        .run(function ($rootScope, orderDraft) {
            $rootScope.$on('$stateChangeSuccess', function (event, toState) { // draft saved only between order form and client addition
                if (toState.name !== 'app.order.add' && toState.name !== 'app.order.edit' && toState.name !== 'app.client.add') {
                    orderDraft.order = null;
                }
            });
        });

});
