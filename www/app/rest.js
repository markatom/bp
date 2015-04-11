'use strict';

define(function () {

    // Model

    /**
     * Response object with success and error handlers.
     * @constructor
     * @param {HttpPromise} promise
     */
    function Response(promise) {
        this._successHandler = this.constructor.defaultSuccessHandler;
        this._errorHandler = this.constructor.defaultErrorHandler;

        var that = this;
        promise.success(function (data, status, headers) {
            that._successHandler(data, status, headers);
        });
        promise.error(function (data, status, headers) {
            that._errorHandler(data, status, headers);
        });
    }

    /**
     * Default success handler.
     * @static
     * @type {function}
     */
    Response.defaultSuccessHandler = function () { };

    /**
     * Default error handler.
     * @static
     * @type {function}
     */
    Response.defaultErrorHandler = function () { };

    /**
     * Sets a handler which is called when a non-error response received.
     * @param {function} handler
     * @returns {Response}
     */
    Response.prototype.success = function (handler) {
        this._successHandler = handler;
        return this;
    };

    /**
     * Sets a handler which is called if an error occurs or an error response received.
     * @param {function} handler
     * @returns {Response}
     */
    Response.prototype.error = function (handler) {
        this._errorHandler = handler;
        return this;
    };

    /**
     * REST resource with CRUD operations.
     * @constructor
     * @param {$http} $http
     * @param {string} url
     */
    function Resource($http, url) {
        this._$http = $http;
        this._url = url;
    }

    /**
     * Creates a new entity from given data.
     * @param {Object} data
     * @param {Object=} query
     * @returns {Response}
     */
    Resource.prototype.create = function (data, query) {
        return new Response(this._$http.post(this._url, data, {query: query || {}}));
    };

    /**
     * Reads a single entity identified by given id.
     * @param {int|string} id
     * @param {Object=} query
     * @returns {Response}
     */
    Resource.prototype.read = function (id, query) {
        return new Response(this._$http.get(this._url + '/' + id, {query: query || {}}));
    };

    /**
     * Deletes a single entity identified by given id.
     * @param {int|string} id
     * @param {Object=} query
     * @returns {Response}
     */
    Resource.prototype.delete = function (id, query) {
        return new Response(this._$http.delete(this._url + '/' + id, {query: query || {}}));
    };

    /**
     * Factory for REST resources.
     * @constructor
     * @param {$http} $http
     */
    function ResourceFactory($http) {
        this._$http = $http;
    }

    /**
     * Creates a resource for given url.
     * @param {string} url
     * @returns {Resource}
     */
    ResourceFactory.prototype.create = function (url) {
        return new Resource(this._$http, url);
    };

    // Configuration

    angular.module('app.rest', [])

        .run(function (alerts) {
            Response.defaultErrorHandler = function () {
                alerts.error('Při komunikaci se serverem došlo k chybě, zkuste to prosím znovu.')
            };
        })

        .service('resourceFactory', ResourceFactory)
        .value('Response', Response);

});
