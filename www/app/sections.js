'use strict';

define(['app/welcome', 'app/gui'], function () {

    //function forceSection($cookies, $state, session, sessions, alerter) {
    //    if (session.token) {
    //        if ($state.includes('welcome')) {
    //            $state.go('app.orders');
    //        }
    //        return;
    //    }
    //
    //    var token = $cookies.get('session-token');
    //
    //    if (!token) {
    //        if ($state.includes('app')) {
    //            alerter.note('Přihlaste se, prosím.');
    //            $state.go('welcome.signIn');
    //        }
    //        return;
    //    }
    //
    //    console.debug($state.current);
    //
    //    var currentState = $state.current.name;
    //    var currentSection = $state.includes('welcome') ? 'welcome' : 'app';
    //
    //    $state.go('loading');
    //
    //    sessions.get('actual').success(function (data, code) {
    //        if (code === 200) { // session is active
    //            session = data;
    //            $state.go(currentSection === 'welcome' ? 'app.orders' : currentState);
    //
    //        } else { // session not found or expired
    //            $state.go(currentSection === 'app' ? 'welcome.signIn' : currentState);
    //        }
    //    });
    //}

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
                    templateUrl: 'app/sections/app.html'
                })
                .state('loading', {
                    templateUrl: 'app/sections/loading.html'
                });

            $urlRouterProvider.otherwise('/');
        })

        .run(function ($rootScope, $state, $cookies, session, $view, sessions, alerts, $http) {
            $rootScope.$on('$stateChangeStart', function(event, toState) {
                if (toState.name === 'loading') {
                    return;
                }

                var state = toState.name;
                var section = state.split('.')[0];

                if (session) {
                    if (section === 'welcome') {
                        $state.go('app.orders');
                        event.preventDefault();
                    }
                    return;
                }

                var token = $cookies.get('session-token');

                if (!token) {
                    if (section === 'app') {
                        alerts.info('Přihlaste se, prosím.');
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
                        session = data;
                        $state.go(section === 'welcome' ? 'app.orders' : state);
                        event.preventDefault();

                    } else { // session not found or expired
                        session = null;
                        $cookies.remove('session-token');

                        if (section === 'app') {
                            alerts.info('Přihlaste se, prosím.');
                        }
                        $state.go(section === 'app' ? 'welcome.signIn' : state);
                        event.preventDefault();
                    }
                });
            });
        });

});
