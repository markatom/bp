'use strict';

define(['app/sections', 'app/welcome', 'app/order', 'app/user', 'app/client', 'app/order'], function () {

    angular.bootstrap(document.documentElement, ['app.welcome', 'app.sections', 'app.order', 'app.user', 'app.client', 'app.order']);

});
