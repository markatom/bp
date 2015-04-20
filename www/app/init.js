'use strict';

define(['app/sections', 'app/welcome', 'app/order', 'app/user', 'app/client', 'app/order'], function () {

    Date.prototype.toString = function () {
        function pad(number) {
            return number < 10 ? '0' + number : number;
        }

        return this.getFullYear()
            + '-' + pad(this.getMonth() + 1)
            + '-' + pad(this.getDate());
    };

    angular.bootstrap(document.documentElement, ['app.welcome', 'app.sections', 'app.order', 'app.user', 'app.client', 'app.order']);

});
