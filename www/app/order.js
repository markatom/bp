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

    function TabsCtrl($scope, $state) {
        $scope.id = $state.params.id;
    }

    function MessagesCtrl($scope, $state, messages, $sce, documents, $timeout) {
        $scope.messages = [];

        function show(data) {
            for (var i = 0; i < data.length; i++) {
                data[i].content = $sce.trustAsHtml(data[i].content.replace(/\n/g, '<br>'));
            }
            $scope.messages = data;
        }

        $scope.fetch = function () {
            $scope.fetching = true;
            messages.readAll({'order[id]': $state.params.id}).success(function (data) {
                show(data);
                messages.readAll({'order[id]': $state.params.id, fetch: true}).success(function (data) {
                    show(data);
                }).finally(function () {
                    $scope.fetching = false;
                });
            });
        };

        $scope.fetch();

        $scope.download = function (id) {
            document.body.className = 'downloading';
            documents.read(id, {download: true}).success(function (data) {
                window.location.href = 'api/documents/' + id + '?download=true&token[key]=' + data.token.key;
                $timeout(function () {
                    document.body.className = '';
                }, 100);
            });
        };
    }

    function SendMessageCtrl($scope, $state, messages, alerts, documents, Upload, Response) {
        $scope.message = {
            documents: []
        };
        $scope.availableDocuments = [];

        function loadAvailableDocuments() {
            documents.readAll({'order[id]': $state.params.id}).success(function (data) {
                $scope.availableDocuments = data;
            });
        }
        loadAvailableDocuments();

        $scope.send = function () {
            $scope.sending = true;
            messages.create($scope.message, {'order[id]': $state.params.id}).success(function () {
                alerts.prepareSuccess('Zpráva byla úspěšně odeslána.');
                $state.go('app.order.tabs.message.grid');
            }).finally(function () {
                $scope.sending = false;
            });
        };

        $scope.select = function () {
            $scope.message.documents.push({});
        };

        $scope.remove = function (index) {
            $scope.message.documents.splice(index, 1);
        };

        $scope.upload = function (files) {
            $scope.uploading = true;
            if (files && files.length) {
                for (var i = 0; i < files.length; i++) {
                    var file = files[i];
                    Upload.upload({
                        url: 'api/documents',
                        file: file,
                        params: {
                            'order[id]': $state.params.id
                        }
                    }).success(function (data) {
                        loadAvailableDocuments();
                        $scope.message.documents.push(data);
                    }).error(Response.defaultErrorHandler)
                        .finally(function () {
                            $scope.uploading= false;
                        });
                }
            }
        };
    }

    function DocumentCtrl($scope, $state, documents, Upload, Response, $timeout, alerts) {
        $scope.documents = [];

        function loadGrid() {
            documents.readAll({'order[id]': $state.params.id}).success(function (data) {
                $scope.documents = data;
            });
        }
        loadGrid();

        $scope.download = function (id) {
            document.body.className = 'downloading';
            documents.read(id, {download: true}).success(function (data) {
                window.location.href = 'api/documents/' + id + '?download=true&token[key]=' + data.token.key;
                $timeout(function () {
                    document.body.className = '';
                }, 100);
            });
        };

        $scope.upload = function (files) {
            $scope.uploading = true;
            if (files && files.length) {
                for (var i = 0; i < files.length; i++) {
                    var file = files[i];
                    Upload.upload({
                        url: 'api/documents',
                        file: file,
                        params: {
                            'order[id]': $state.params.id
                        }
                    }).success(function () {
                        loadGrid();
                    }).error(Response.defaultErrorHandler)
                    .finally(function () {
                        $scope.uploading= false;
                    });
                }
            }
        };

        $scope.generate = function (type) {
            $scope.generating = true;
            documents.create('', {
                'order[id]': $state.params.id,
                generate: type
            }).success(function () {
                alerts.clear();
                alerts.showSuccess('Dokument byl úspěšně vygenerován. Překontrolujte prosím údaje, které do něj byly doplněny.');
                loadGrid();
            }).finally(function () {
                $scope.generating = false;
            })
        };
    }

    // Configuration

    angular.module('app.order', ['ui.router', 'ui.bootstrap', 'app.rest', 'app.gui', 'app.client', 'app.user', 'ngFileUpload'])

        .config(function ($stateProvider) {
            $stateProvider
                .state('app.order', {
                    abstract: true,
                    url: '/order',
                    template: '<div ui-view></div>'
                })

                .state('app.order.tabs', {
                    abstract: true,
                    url: '/{id}',
                    templateUrl: 'app/order/tabs.html',
                    controller: TabsCtrl
                })

                .state('app.order.tabs.message', {
                    abstract: true,
                    template: '<div ui-view></div>'
                })

                .state('app.order.tabs.message.grid', {
                    url: '/message',
                    templateUrl: 'app/order/messages.html',
                    controller: MessagesCtrl
                })

                .state('app.order.tabs.message.send', {
                    url: '/message/send',
                    templateUrl: 'app/order/sendMessage.html',
                    controller: SendMessageCtrl
                })

                .state('app.order.tabs.documents', {
                    url: '/document',
                    templateUrl: 'app/order/documents.html',
                    controller: DocumentCtrl
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

        .factory('messages', function (resourceFactory) {
            return resourceFactory.create('api/messages');
        })

        .factory('documents', function (resourceFactory) {
            return resourceFactory.create('api/documents');
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
