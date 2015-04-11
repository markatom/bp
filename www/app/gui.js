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

    function AlertsCtrl($scope, alerts) {
        $scope.alerts = alerts;
    }

    // Services

    /**
     * Collects alert messages.
     * @constructor
     */
    function Alerts() {
        this.alerts = [];
    }

    /**
     * Adds alert.
     * @param {string} type
     * @param {string} text
     * @private
     */
    Alerts.prototype._add = function (type, text) {
        this.alerts.push({
            type: type,
            text: text
        });
    };

    /**
     * Adds alert of error type.
     * @param {string} text
     */
    Alerts.prototype.error = function (text) {
        this._add('danger', text);
    };

    /**
     * Adds alert of info type.
     * @param {string} text
     */
    Alerts.prototype.info = function (text) {
        this._add('info', text);
    };

    // Configuration

    angular.module('app.gui', [])
        .service('alerts', Alerts)
        .directive('appAlerts', alertsDir)
        .controller('alerts', AlertsCtrl);

});
