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

    // Model

    /**
     * Collects alert messages.
     * @constructor
     */
    function Alerts() {
        this.current = [];
        this.waiting = [];
    }

    /**
     * Shows an alert.
     * @param {string} type
     * @param {string} text
     * @private
     */
    Alerts.prototype._show = function (type, text) {
        this.current.push({
            type: type,
            text: text
        });
    };

    /**
     * Prepares an alert.
     * @param {string} type
     * @param {string} text
     * @private
     */
    Alerts.prototype._prepare = function (type, text) {
        this.waiting.push({
            type: type,
            text: text
        });
    };

    /**
     * Shows an success alert.
     * @param {string} text
     */
    Alerts.prototype.showSuccess = function (text) {
        this._show('success', text);
    };

    /**
     * Shows an error alert.
     * @param {string} text
     */
    Alerts.prototype.showError = function (text) {
        this._show('danger', text);
    };

    /**
     * Shows an info alert.
     * @param {string} text
     */
    Alerts.prototype.showInfo = function (text) {
        this._show('info', text);
    };

    /**
     * Shows an warning alert.
     * @param {string} text
     */
    Alerts.prototype.showWarning = function (text) {
        this._show('warning', text);
    };

    /**
     * Prepares an success alert.
     * @param {string} text
     */
    Alerts.prototype.prepareSuccess = function (text) {
        this._prepare('success', text);
    };

    /**
     * Prepares an error alert.
     * @param {string} text
     */
    Alerts.prototype.prepareError = function (text) {
        this._prepare('danger', text);
    };

    /**
     * Prepares an info alert.
     * @param {string} text
     */
    Alerts.prototype.prepareInfo = function (text) {
        this._prepare('info', text);
    };

    /**
     * Prepares an warning alert.
     * @param {string} text
     */
    Alerts.prototype.prepareWarning = function (text) {
        this._prepare('warning', text);
    };

    /**
     * Removes all alerts.
     */
    Alerts.prototype.clear = function () {
        this.current = [];
    };

    /**
     * Shows waiting alerts.
     */
    Alerts.prototype.shift = function () {
        this.current = this.waiting;
        this.waiting = [];
    };

    // Configuration

    angular.module('app.gui', ['ui.bootstrap'])
        .service('alerts', Alerts)
        .directive('appAlerts', alertsDir)
        .controller('alerts', AlertsCtrl)

        .config(function (datepickerConfig, datepickerPopupConfig) {
            datepickerConfig.showWeeks = false;
            datepickerConfig.startingDay = 1;
            datepickerConfig.formatDay = 'd';
            datepickerConfig.formatMonth = 'MMMM';
            datepickerPopupConfig.showButtonBar = false;
        })

        .run(function ($rootScope, alerts) {
            $rootScope.$on('$stateChangeSuccess', function (event, toState) {
                if (toState.name !== 'loading') {
                    alerts.shift();
                }
            });
        });

});
