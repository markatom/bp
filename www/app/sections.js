'use strict';

define(['app/welcome', 'app/gui'], function () {

    function AppCtrl($scope, session, $state, sessions, alerts, $cookies) {
        $scope.session = session;
        $scope.$state = $state;

        $scope.signOut = function () {
            sessions.delete('current').success(function () {
                $cookies.remove('session-token');
                session.terminate();
                alerts.prepareSuccess('Odhlášení proběhlo úspěšně.');
                $state.go('welcome.signIn');
            });
        };
    }

    angular.module('app.sections', ['ui.router', 'app.welcome', 'app.gui', 'ngCookies'])

        .config(function ($stateProvider, $urlRouterProvider) {
            $stateProvider
                .state('welcome', {
                    abstract: true,
                    templateUrl: 'app/sections/welcome.html'
                })
                .state('app', {
                    abstract: true,
                    url: '/app',
                    controller: AppCtrl,
                    templateUrl: 'app/sections/app.html'
                })
                .state('loading', {
                    templateUrl: 'app/sections/loading.html'
                });

            $urlRouterProvider.otherwise('/');
        })

        .run(function ($rootScope, $state, $cookies, session, $view, sessions, alerts, $http) {
            $rootScope.$on('$stateChangeStart', function(event, toState, toParams) {
                if (toState.name === 'loading') {
                    return;
                }

                if (toState.name === 'welcome.setPassword') {
                    session.terminate();
                    $cookies.remove('session-token');
                    return;
                }

                var state = toState.name;
                var params = toParams;
                var section = state.split('.')[0];

                if (session.isActive()) {
                    if (section === 'welcome') {
                        $state.go('app.order.grid');
                        event.preventDefault();
                    }
                    return;
                }

                var token = $cookies.get('session-token');

                if (!token) {
                    if (section === 'app') {
                        alerts.prepareInfo('Přihlaste se, prosím.');
                        $state.go('welcome.signIn');
                        event.preventDefault();
                    }
                    return;
                }

                $state.go('loading');
                $http.defaults.headers.common['X-Session-Token'] = token;

                event.preventDefault();

                sessions.read('current').success(function (data, code) {
                    if (code === 200) { // session is active
                        session.start(data.token, data.user);
                        $state.go(section === 'welcome' ? 'app.order.grid' : state, params);
                        event.preventDefault();

                    } else { // session not found or expired
                        session.terminate();
                        $cookies.remove('session-token');

                        if (section === 'app') {
                            alerts.prepareInfo('Přihlaste se, prosím.');
                        }
                        $state.go(section === 'app' ? 'welcome.signIn' : state, params);
                        event.preventDefault();
                    }
                });
            });
        });

});
