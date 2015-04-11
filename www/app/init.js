'use strict';

define(['app/sections', 'app/welcome', 'app/order'], function () {

    angular.bootstrap(document.documentElement, ['app.welcome', 'app.sections', 'app.order']);

});
