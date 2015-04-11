'use strict';

define(function () {

    angular.module('app.order', ['ui.router'])

        .config(function ($stateProvider) {
            $stateProvider
                .state('app.orders', {
                    url: '/orders'
                });
        });

});