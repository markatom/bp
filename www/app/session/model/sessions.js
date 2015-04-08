'use strict';

define(function () {

    return function ($http) {
        return {
            create: function (email, password, longLife) {
                return $http.post('api/sessions', {
                    user: {
                        email: email,
                        password: password
                    },
                    longLife: longLife
                });
            },

            'delete': function () {
                return $http.delete('api/sessions/current');
            }
        };
    };

});