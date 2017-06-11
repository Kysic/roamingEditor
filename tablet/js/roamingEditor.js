
var roamingApiEndPoint = '../api';

var roamingEditor = angular.module('roamingEditor', ['ngRoute', 'ngCookies']);

roamingEditor.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/roamingsList', {
        templateUrl: 'templates/roamingsList.html',
        controller: 'RoamingListController'
    })
    .when('/roaming/:roamingId', {
        templateUrl: 'templates/roamingEditor.html',
        controller: 'RoamingController'
    })
    .when('/roaming/:roamingId/intervention/:interventionId', {
        templateUrl: 'templates/interventionEditor.html',
        controller: 'InterventionController'
    })
    .when('/debug', {
        templateUrl: 'templates/debug.html',
        controller: 'DebugController'
    })
    .otherwise({
        redirectTo: '/roamingsList'
    });
}]);

roamingEditor.config(['$cookiesProvider', function($cookiesProvider) {
    $cookiesProvider.defaults.path = '/';
}]);


roamingEditor.factory('roamingService', function ($rootScope, $filter, $http, $cookies) {

    var roamings = { };

    loadLocalStorage();
    resynchro();

    function loadLocalStorage() {
        synchronizeApiKeyWithAutoLogin();
        roamings = { };
        Object.keys(localStorage).forEach(function(key) {
            if (key.match(/^roaming_[0-9-]*$/)) {
                try {
                    var roaming = JSON.parse(localStorage.getItem(key));
                    roamings[roaming.date] = roaming;
                } catch (e) {
                    console.log('Unable to restore ' + key + ' from local storage', e);
                }
            }
        });
        filterOldRoamings();
    }

    function synchronizeApiKeyWithAutoLogin() {
        var apiKey = localStorage.getItem('apiKey');
        if ( apiKey ) {
            $cookies.put('vcrPersistentLogin', apiKey);
        }
    }

    function updateRoamingInLocalStorage(roaming) {
        var roamingJson = JSON.stringify(roaming);
        localStorage.setItem('roaming_' + roaming.date, roamingJson);
    }

    function getAllRoamings() {
        return angular.copy(roamings);
    }

    function getRoaming(roamingId) {
        return angular.copy(roamings[roamingId]);
    }

    function deleteRoaming(roamingDate) {
        try {
            delete roamings[roamingDate];
            localStorage.removeItem('roaming_' + roamingDate);
            console.log('Roaming ' + roamingDate + ' deleted');
        } catch (e) {
            console.log('Unable to delete roaming ' + roamingDate);
        }
    }

    function filterOldRoamings() {
        var olderRoamingDateAccepted = new Date(new Date().getTime() - 30 * 24 * 60 * 60 * 1000);
        var olderRoamingIdAccepted = $filter('date')(olderRoamingIdAccepted, 'yyyy-MM-dd');
        for (var roamingDate in roamings) {
            if (roamingDate < olderRoamingIdAccepted) {
                deleteRoaming(roamingDate);
            }
        }
    }

    function resynchro() {
        for (var roamingId in roamings) {
            var roaming = roamings[roamingId];
            if (roaming.synchroStatus != 'SYNCHRONIZED') {
                sendRoamingToRemoteServer(roaming);
            }
        }
    }

    function updateRoaming(roaming) {
        roaming.version = roaming.version ? roaming.version + 1 : 1;
        roamings[roaming.date] = roaming;
        filterOldRoamings();
        updateRoamingInLocalStorage(roaming);
        sendRoamingToRemoteServer(roaming);
    }

    function sendRoamingToRemoteServer(roaming) {
        roaming.synchroStatus = 'IN_PROGRESS';
        notifyRoamingUpdate(roaming);
        $http.post(
            roamingApiEndPoint + '/saveRoaming.php',
            { roaming: roaming },
            { roamingDate: roaming.date, roamingVersion: roaming.version }
        ).then(function (response) {
            if (response.data.status == 'success') {
                updateSynchroStatus(response.config.roamingDate, response.config.roamingVersion, 'SYNCHRONIZED');
            } else {
                console.log('Server error : ' + response.data.errorMsg);
                updateSynchroStatus(response.config.roamingDate, response.config.roamingVersion, 'FAILED');
            }
        }, function (response) {
            updateSynchroStatus(response.config.roamingDate, response.config.roamingVersion, 'FAILED');
        });
    }

    function updateSynchroStatus(roamingDate, roamingVersion, status) {
        var roaming = roamings[roamingDate];
        if (roaming && roaming.version == roamingVersion) {
            roaming.synchroStatus = status;
            updateRoamingInLocalStorage(roaming);
            notifyRoamingUpdate(roaming);
        }
    }

    function notifyRoamingUpdate(roaming) {
            $rootScope.$broadcast('roamingUpdate', roaming);
    }

    function getCurrentRoamingDateId() {
        // between 0h and 8h, the current roaming date is the date of the previous date
        var currentRoamingDate = new Date(new Date().getTime() - 8 * 60 * 60 * 1000);
        return $filter('date')(currentRoamingDate, 'yyyy-MM-dd');
    }

    function deleteAllRoamings() {
        for (var roamingDate in roamings) {
            deleteRoaming(roamingDate);
        }
    }

    return {
        loadLocalStorage: loadLocalStorage,
        getAllRoamings: getAllRoamings,
        getRoaming: getRoaming,
        updateRoaming: updateRoaming,
        getCurrentRoamingDateId: getCurrentRoamingDateId,
        resynchro: resynchro,
        deleteAllRoamings: deleteAllRoamings
    };
});

roamingEditor.directive('ngEnter', function() {
    return function(scope, element, attrs) {
        element.bind("keydown keypress", function(event) {
            if(event.which === 13) {
                scope.$apply(function(){
                    scope.$eval(attrs.ngEnter, {'event': event});
                });
                event.preventDefault();
            }
        });
    };
});

roamingEditor.controller('RoamingListController', function RoamingListController($scope, $location, $filter, roamingService) {

    retrieveRoamings();

    function retrieveRoamings() {
        $scope.roamings = $filter('orderBy')(Object.values(roamingService.getAllRoamings()), '-date');
    }

    $scope.editRoaming = function (roaming) {
        $location.path('/roaming/' + roaming.date);
    }

    $scope.resynchro = function () {
        roamingService.resynchro();
    }

    $scope.createRoaming = function () {
        $location.path('/roaming/' + roamingService.getCurrentRoamingDateId());
    }

    $scope.$on('roamingUpdate', function () {
        retrieveRoamings();
    });

});

roamingEditor.controller('RoamingController',
  function RoamingController($scope, $routeParams, $location, $timeout, $http, $interval, roamingService) {

    if ( ! /^[0-9]{4}-[0-9]{2}-[0-9]{2}$/.test($routeParams.roamingId)) {
        $location.path('/roamingsList');
        return;
    }
    if ($routeParams.roamingId) {
        $scope.roaming = roamingService.getRoaming($routeParams.roamingId);
    }
    if ( !$scope.roaming ) {
        initRoaming();
    }

    function initRoaming() {
        $scope.roaming = {
            date: $routeParams.roamingId,
            tutor: '',
            volunteers: [ '' ],
            vehicle: getVehicleAccordingToRoamingDate($routeParams.roamingId),
            interventions: [ ]
        };
        roamingService.updateRoaming($scope.roaming);
        getTeammates();
    }

    function getVehicleAccordingToRoamingDate(roamingDate) {
        return parseInt(roamingDate.substring(8,10)) % 2 == 0 ? '2' : '1';
    }

    function getTeammates() {
        $http.get(
            roamingApiEndPoint + '/getPlanning.php?roamingDate=' + $routeParams.roamingId
        ).then(function (response) {
            if (response.data.tutor && $scope.roaming.tutor == '') {
                $scope.roaming.tutor = response.data.tutor;
            }
            if (response.data.volunteers && angular.equals($scope.roaming.volunteers, [ '' ])) {
                $scope.roaming.volunteers = response.data.volunteers;
            }
            $scope.updateRoaming();
        });
    }

    $scope.addIntervention = function () {
        $scope.editIntervention(-1);
    }

    $scope.editIntervention = function (interventionIndex) {
        $location.path('/roaming/' + $routeParams.roamingId + '/intervention/' + interventionIndex);
    }

    $scope.deleteIntervention = function (interventionIndex) {
        if (confirm('Etes vous sur de vouloir supprimer cette intervention ?')) {
            $scope.roaming.interventions.splice(interventionIndex, 1);
            $scope.updateRoaming();
       }
    }

    $scope.addVolunteer = function () {
        $scope.roaming.volunteers.push('');
        $scope.updateRoaming();
        // A timeout is required because the field doesn't exist yet
        $timeout(function () {
            var elts = document.querySelectorAll('.volunteerInput');
            if (elts && elts.length > 0) {
                elts[elts.length-1].focus();
            }
        });
    }
    $scope.removeVolunteer = function (volunteerIndex) {
        if (volunteerIndex < $scope.roaming.volunteers.length
            && ($scope.roaming.volunteers[volunteerIndex] == ''
                    || confirm('Etes vous sûr de vouloir enlever ' + $scope.roaming.volunteers[volunteerIndex] + ' ?')
            )
        ) {
            $scope.roaming.volunteers.splice(volunteerIndex, 1);
            if ($scope.roaming.volunteers.length == 0) {
                $scope.roaming.volunteers.push('');
            }
            $scope.updateRoaming();
        }
    }

    $scope.updateRoaming = function () {
        roamingService.updateRoaming($scope.roaming);
    }

    $scope.goBack = function () {
        $location.path('/roamingsList');
    }

    $scope.isEditable = function () {
        return $routeParams.roamingId == roamingService.getCurrentRoamingDateId();
    }
    $interval(function(){
        // Allow to refresh read only status every hour
    },3600000)

});

roamingEditor.controller('InterventionController',
  function InterventionController($scope, $routeParams, $location, $filter, $timeout, roamingService) {

    $scope.sources = ['115', 'Maraude', 'Particulier', 'Direct', 'CHU', 'SemiTag', 'Pompier', 'Police', 'Autre'];

    var roaming;
    var interventionIndex = $routeParams.interventionId;

    if ($routeParams.roamingId) {
        roaming = roamingService.getRoaming($routeParams.roamingId);
    }
    if ( !roaming ) {
        $location.path('/roamingsList');
    }
    if (interventionIndex == -1) {
        $scope.intervention = createEmptyIntervention();
    } else {
        $scope.intervention = roaming.interventions[interventionIndex];
    }
    if ( !$scope.intervention ) {
        $location.path('/roaming/' + $routeParams.roamingId);
    }
    updateFormTimeWithInterventionTime();

    function createEmptyIntervention() {
        return {
            time: $filter('date')(new Date(), 'HH:mm'),
            location: '',
            people: [ '' ],
            source: '115',
            nbAdults: 0,
            nbChildren: 0,
            blankets: 0,
            tents: 0,
            comments: ''
        };
    }

    function updateFormTimeWithInterventionTime() {
        var timeSplited = $scope.intervention.time.split(':');
        var hour = parseInt(timeSplited[0]);
        var minute = Math.round(timeSplited[1] / 5) * 5;
        if (minute >= 60) {
            minute = 0;
            hour += 1;
        }
        $scope.hour = ('00' + hour).slice(-2);
        $scope.minute = ('00' + minute).slice(-2);
    }

    function localisationFinished() {
        $scope.$apply(function() {
            $scope.localisationInProgress = false;
        });
    }

    $scope.localizeMe = function () {
        if (navigator.geolocation) {
            $scope.localisationInProgress = true;
            navigator.geolocation.getCurrentPosition(function (position) {
                try {
                    $scope.$apply(function() {
                        $scope.intervention.latitude = position.coords.latitude;
                        $scope.intervention.longitude = position.coords.longitude;
                    });
                    if (position.coords.accuracy <= 100) {
                        new google.maps.Geocoder().geocode(
                            { 'latLng': new google.maps.LatLng(position.coords.latitude, position.coords.longitude) },
                            function(results, status) {
                                $scope.$apply(function() {
                                    if (status == google.maps.GeocoderStatus.OK && results[0] && results[0].formatted_address) {
                                        if ($scope.intervention.location == '') {
                                            $scope.intervention.location = results[0].formatted_address;
                                        }
                                    }
                                    $scope.localisationInProgress = false;
                                }, function () {
                                    console.log('Unable to determined the address');
                                    localisationFinished();
                                });
                            }
                        );
                    } else {
                        localisationFinished();
                    }
                } catch (e) {
                    console.log('Error while retrieving location', e);
                    localisationFinished();
                }
            }, function () {
                console.log('Unable to retrieve location');
                localisationFinished();
            });
        } else {
            console.log('Geolocalisation not supported');
        }
    }

    $scope.resetLocation = function () {
        if ($scope.intervention.location != '' && confirm('Etes vous sûr de vouloir réinitialiser le lieu de la rencontre ?')) {
            $scope.intervention.location = '';
        }
    }

    $scope.range = function(min, max, step) {
        step = step || 1;
        var input = [];
        for (var i = min; i <= max; i += step) {
            input.push(i);
        }
        return input;
    };

    function filterBlankPeople() {
        for (var i = 0; i < $scope.intervention.people.length; i++) {
            if ($scope.intervention.people[i].trim() == '') {
                $scope.intervention.people.splice(i, 1);
            }
        }
    }

    function formAlmostUntouchedByUser() {
        return $scope.intervention.location.trim() == ''
            && angular.equals($scope.intervention.people, [ '' ])
            && $scope.intervention.comments.trim() == '';
    }

    $scope.addPerson = function () {
        filterBlankPeople();
        $scope.intervention.people.push('');
        // A timeout is required because the field doesn't exist yet
        $timeout(function () {
            var elts = document.querySelectorAll('.personInput');
            if (elts && elts.length > 0) {
                elts[elts.length-1].focus();
            }
        });
    }
    $scope.removePerson = function (personIndex) {
        if (personIndex < $scope.intervention.people.length
            && ($scope.intervention.people[personIndex] == ''
                    || confirm('Etes vous sûr de vouloir enlever ' + $scope.intervention.people[personIndex] + ' ?')
            )
        ) {
            $scope.intervention.people.splice(personIndex, 1);
            if ($scope.intervention.people.length == 0) {
                $scope.intervention.people.push('');
            }
        }
    }

    $scope.cancelInterventionEdit = function () {
        if ( formAlmostUntouchedByUser() || confirm('Etes vous sûr de vouloir annuler cette intervention ?') ) {
            $location.path('/roaming/' + $routeParams.roamingId);
        }
    }

    /** Allow to sort intervention by time with time between 00:00 and 07:59 after time between 08:00 and 23:59 */
    function timeSort(intervention1, intervention2) {
        if ( intervention1.time.localeCompare("08:00") * intervention2.time.localeCompare("08:00") == -1 ) {
            // If one is strictly before 08:00 and the other strictly after 08:00 the result is reversed
            return intervention2.time.localeCompare(intervention1.time);
        } else {
            // Otherwise we just compare the two string
            return intervention1.time.localeCompare(intervention2.time);
        }
    }

    $scope.saveInterventionEdit = function () {
        filterBlankPeople();
        $scope.intervention.time = $scope.hour + ':' + $scope.minute;
        if (interventionIndex == -1) {
            roaming.interventions.push($scope.intervention);
        } else {
            roaming.interventions[interventionIndex] = $scope.intervention;
        }
        roaming.interventions = roaming.interventions.sort(timeSort);
        roamingService.updateRoaming(roaming);
        $location.path('/roaming/' + $routeParams.roamingId);
    }

});


roamingEditor.controller('DebugController', function DebugController($scope, $cookies, $filter, $location, roamingService) {

    loadCurrentConf();

    function loadCurrentConf() {
        $scope.roamingsJSON = $filter('json')(roamingService.getAllRoamings());
        $scope.autoLogin = $cookies.get('vcrPersistentLogin');
        $scope.apiKey = localStorage.getItem('apiKey');
    }

    $scope.goRoamingsList = function () {
        $location.path('/roamingsList');
    }

    $scope.updateApiKey = function () {
        localStorage.setItem('apiKey', $scope.apiKey);
        $cookies.remove('PHPSESSID');
        $cookies.put('vcrPersistentLogin', $scope.apiKey);
        roamingService.loadLocalStorage();
        loadCurrentConf();
    }

    $scope.updateRoamings = function () {
        var roamings = JSON.parse($scope.roamingsJSON);
        roamingService.deleteAllRoamings();
        for (var roamingDate in roamings) {
            roamingService.updateRoaming(roamings[roamingDate]);
        }
        roamingService.loadLocalStorage();
        loadCurrentConf();
    }

});

