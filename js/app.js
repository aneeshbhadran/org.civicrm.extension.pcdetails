(function (angular, $, _) {
	var app = angular.module('campaignDetailsApp', ['ngRoute']);
	var resourceUrl = CRM.resourceUrls['org.civicrm.extension.pcdetails'];

	// Set up routes
    app.config(['$routeProvider', '$httpProvider',
        function ($routeProvider, $httpProvider) {
            $routeProvider.when('/campaigndetails', {
                templateUrl: resourceUrl + '/partials/listing.html',
                controller: 'CampaignDetailsListingCtrl'
            });

            $routeProvider.when('/campaigndetails/new', {
                templateUrl: resourceUrl + '/partials/new.html',
                controller: 'CampaignDetailsNewCtrl'
            });

            $routeProvider.when('/campaigndetails/:id/edit', {
                templateUrl: resourceUrl + '/partials/edit.html',
                controller: 'CampaignDetailsEditCtrl'
            });

            $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';
        }
    ]);



    /**
     * Controller to create a listing of campaign page
     *
     * @ngdoc controller
     * @name CampaignDetailsListingCtrl
     */
    app.controller('CampaignDetailsListingCtrl', ['$scope', '$http', '$log', 'CampaignDetailsApiFactory',
        /**
         * @param $scope
         * @param $http
         * @param $log
         * @param {CampaignDetailsApiFactory} CampaignDetailsApi
         */
        function ($scope, $http, $log, CampaignDetailsApi) {
            CampaignDetailsApi.get('CampaignDetailsActivity',{header : {'Content-Type' : 'application/json; charset=UTF-8'}})
                .success(function (response) {
                    
                    $scope.results = response.values;
                    
                })
                .error(function (response) {
                    // Log the response in case of error - using the $log service is preferable to using console.log()
                    $log.debug(response);
                    CRM.alert('Something went wrong!', '', 'error');
                });
        }
    ]);


    /**
     * A factory to provide helper methods for interacting with the CiviCRM API
     *
     * @ngdoc service
     * @name CampaignDetailsApiFactory
     */
    app.factory('CampaignDetailsApiFactory', ['$http',
        /**
         * @param $http
         */
        function ($http) {
            /**
             * Retrieve record(s)
             *
             * @ngdoc method
             * @name CiviApiFactory#get
             */
            var get = function (entity, data) {
                return post(entity, data, 'get');
            };

            /**
             * Create a record
             *
             * @ngdoc method
             * @name CiviApiFactory#create
             */
            var create = function (entity, data) {
                return post(entity, data, 'create');
            };

            /**
             * Remove (delete) a record
             *
             * @ngdoc method
             * @name CiviApiFactory#remove
             */
            var remove = function (entity, data) {
                return post(entity, data, 'delete');
            };

            /**
             * Send the POST HTTP request to the CiviCRM API
             *
             * @ngdoc function
             * @name CiviApiFactory#post
             * @private
             */
            var post = function (entity, data, action) {
                // If data is not provided, initialise it to an empty object
                data = data || {};

                data.entity = entity;
                data.action = action;
                data.json = 1;
                data.sequential = 1;
                data.magicword = 'sesame';

                var serialisedData = $.param(data);

                var headers = {'Content-type': 'application/x-www-form-urlencoded'};

                // Send an AJAX request to retrieve all sessions
                return $http.post('index.php?q=civicrm/ajax/rest', serialisedData, {headers: headers});
            };

            return {
                get: get,
                create: create,
                remove: remove
            };
        }
    ]);
})(angular, CRM.$, CRM._);