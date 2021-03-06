'use strict';

define(['app/rest', 'app/gui', 'app/order'], function () {

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

    function FormCtrl($scope, $state, clients, alerts, orderDraft, Response) {
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
            if ($scope.client.email && !(/^("([ !\x23-\x5B\x5D-\x7E]*|\\[ -~])+"|[-a-z0-9!#$%&'*+\/=?^_`{|}~]+(\.[-a-z0-9!#$%&'*+\/=?^_`{|}~]+)*)@([0-9a-z\u00C0-\u02FF\u0370-\u1EFF]([-0-9a-z\u00C0-\u02FF\u0370-\u1EFF]{0,61}[0-9a-z\u00C0-\u02FF\u0370-\u1EFF])?\.)+[a-z\u00C0-\u02FF\u0370-\u1EFF][-0-9a-z\u00C0-\u02FF\u0370-\u1EFF]{0,17}[a-z\u00C0-\u02FF\u0370-\u1EFF]$/i).test($scope.client.email)) {
                alerts.clear();
                alerts.showInfo('E-mailová adresa není ve správném tvaru. Zkontrolujte prosím, zda neobsahuje překlepy.');
                document.getElementById('email').focus();
                return;
            }

            if ($scope.client.telephone && !(/^((00|\+)\d{3})? ?\d{3} ?\d{3} ?\d{3}$/).test($scope.client.telephone)) {
                alerts.clear();
                alerts.showInfo('Telefonní číslo se musí skládat z devíti číslic, před kterými je možné uvést mezinárodní telefonní předvolbu. Trojice číslic je možné oddělit mezerou.');
                document.getElementById('zip').focus();
                return;
            }

            if ($scope.client.address.zip && !(/^(\d{3}) ?(\d{2})$/).test($scope.client.address.zip)) {
                alerts.clear();
                alerts.showInfo('PSČ se musí skládat z pěti číslic. Mezi třetí a čtvrtou číslicí může být mezera.');
                document.getElementById('zip').focus();
                return;
            }

            if (!$scope.client.email && !$scope.client.telephone) {
                alerts.clear();
                alerts.showInfo('Vyplňte prosím alespoň jeden kontaktní údaj (telefon nebo e-mail).');
                document.getElementById('email').focus();
                return;
            }

            $scope.sending = true;

            if ($scope.client.dateOfBirth instanceof Date) {
                $scope.client.dateOfBirth.setHours(2); // fixes https://github.com/angular-ui/bootstrap/issues/2072
                $scope.client.dateOfBirth = $scope.client.dateOfBirth.toString();
            }

            if ($scope.editation) {
                clients.update($state.params.id, $scope.client)
                    .success(function () {
                        alerts.prepareSuccess('Změny byly úspěšně uloženy.');
                        $state.go('app.client.grid');
                    })
                    .error(function (code) {
                        if (code === 409) { // conflict
                            alerts.clear();
                            alerts.showWarning('Klient se stejnými kontaktními údaji je již evidován.');
                        } else {
                            Response.defaultErrorHandler();
                        }
                    })
                    .finally(function () {
                        $scope.sending = false;
                    });

            } else {
                clients.create($scope.client)
                    .success(function (data) {
                        alerts.prepareSuccess('Nový klient byl úspěšně vytvořen.');
                        if (orderDraft.order) {
                            orderDraft.order.client = data;
                            $state.go('app.order.add');
                        } else {
                            $state.go('app.client.grid');
                        }
                    })
                    .error(function (error, code) {
                        if (code === 409) { // conflict
                            alerts.clear();
                            alerts.showWarning('Klient se stejnými kontaktními údaji je již evidován.');
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

    function DetailCtrl($scope, $state, clients) {
        $scope.client = {};
        $scope.loading = true;

        clients.read($state.params.id).success(function (data) {
            $scope.client = data;
            $scope.loading = false;
        });
    }

    // Configuration

    angular.module('app.client', ['ui.router', 'ui.bootstrap', 'app.rest', 'app.gui', 'app.order'])

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
