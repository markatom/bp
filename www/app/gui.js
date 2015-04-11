'use strict';

define(function () {

    // Directives

    function alertsDir() {
        return {
            restrict: 'E',
            templateUrl: 'app/gui/alerts.html',
            scope: {}
        };
    }

    // Controllers

    function alertsCtrl($scope, alerter) {
        $scope.alerter = alerter;
    }

    // Services

    function Alerter() {
        this.alerts = [];
    }

    Alerter.prototype._add = function (type, text) {
        this.alerts.push({
            type: type,
            text: text
        });
    };

    Alerter.prototype.error = function (text) {
        this._add('danger', text);
    };

    Alerter.prototype.info = function (text) {
        this._add('info', text);
    };

    // Configuration

    angular.module('app.gui', [])

        .service('alerter', Alerter)

        .directive('appAlerts', alertsDir)

        .controller('alerts', alertsCtrl);

});
