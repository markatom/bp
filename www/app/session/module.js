'use strict';

define(['app/session/controllers/signIn', 'app/session/model/sessions'], function (signInCtrl, sessions) {

    angular.module('app.sessions', ['ui.router'])

        .config(function ($stateProvider) {
            $stateProvider.state('signIn', {
                url: '',
                templateUrl: 'app/account/views/signIn.html',
                controller: signInCtrl
            });
        })

        .factory('sessions', sessions);

});
