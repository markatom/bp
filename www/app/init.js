'use strict';

define(['app/sections', 'app/welcome', 'app/order', 'app/user', 'app/client'], function () {

    angular.bootstrap(document.documentElement, ['app.welcome', 'app.sections', 'app.order', 'app.user', 'app.client']);

});
