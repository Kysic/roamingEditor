
var roamingEditor = angular.module("roamingEditor", ["ngRoute"]);

roamingEditor.config(function($routeProvider) {
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
});


roamingEditor.factory('roamingService', function ($filter) {
    
    var roamings = { };
    
    // Try to resolve the cache manifest to update file cache if necessary
    try {
        window.applicationCache.update();
    } catch (e) {
        console.log('Unable to update application cache', e);
    }

    loadLocalStorage();

    function loadLocalStorage() {
        var rawRoamings = localStorage.getItem('roamings');
        if (rawRoamings) {
            try {
                roamings = JSON.parse(rawRoamings);
            } catch (e) {
                console.log('Unable to restore local storage', e);
            }
        }
    }

    function updateRoamingsInLocalStorage() {
        var roamingsJson = JSON.stringify(roamings);
        //console.log(new Date() + ' saving roamings to local storage ' + roamingsJson)
        localStorage.setItem('roamings', roamingsJson);
    }

    function getAllRoamings() {
        return angular.copy(roamings);
    }

    function getRoaming(roamingId) {
        return angular.copy(roamings[roamingId]);
    }

    function filterOldRoamings() {
        var olderRoamingDateAccepted = new Date(new Date().getTime() - 30 * 24 * 60 * 60 * 1000);
        var olderRoamingAcceptedId = $filter('date')(olderRoamingDateAccepted, 'yyyy-MM-dd');
        for (var dateId in roamings) {
            if (dateId < olderRoamingAcceptedId) {
                console.log('Roaming ' + dateId + 'deleted');
                delete roamings[dateId];
            }
        }
    }

    function updateRoaming(roaming) {
        roamings[roaming.date] = roaming;
        filterOldRoamings();
        updateRoamingsInLocalStorage();
    }

    return { getAllRoamings: getAllRoamings, getRoaming: getRoaming, updateRoaming: updateRoaming };
});


roamingEditor.controller('RoamingListController', function RoamingListController($scope, $location, $filter, roamingService) {

    $scope.roamings = $filter('orderBy')(Object.values(roamingService.getAllRoamings()), '-date');

    $scope.editRoaming = function (roaming) {
        $location.path('/roaming/' + roaming.date);
    }

    $scope.createRoaming = function () {
        // between 0h and 8h, the current roaming date is the date of the previous date
        var currentRoamingDate = new Date(new Date().getTime() - 8 * 60 * 60 * 1000);
        var roamingDateId = $filter('date')(currentRoamingDate, 'yyyy-MM-dd');
        $location.path('/roaming/' + roamingDateId);
    }

});

roamingEditor.controller('RoamingController', function RoamingController($scope, $routeParams, $location, $http, roamingService) {

    if ( ! /^[0-9]{4}-[0-9]{2}-[0-9]{2}$/.test($routeParams.roamingId)) {
        $location.path('/roamingsList');
        return;
    }
    if ($routeParams.roamingId) {
        $scope.roaming = roamingService.getRoaming($routeParams.roamingId);
    }
    if ( !$scope.roaming ) {
        $scope.roaming = {
            date: $routeParams.roamingId,
            tutor: '',
            volunteers: [ '' ],
            vehicle: getVehicleAccordingToRoamingDate($routeParams.roamingId),
            interventions: [ ]
        };
        $http.get('api/getInfoPlanning.php?roamingDate=' + $routeParams.roamingId).then(function (response) {
            if (response.data.tutor && $scope.roaming.tutor == '') {
                $scope.roaming.tutor = response.data.tutor;
            }
            if (response.data.volunteers && angular.equals($scope.roaming.volunteers, [ '' ])) {
                $scope.roaming.volunteers = response.data.volunteers;
            }
            $scope.updateRoaming();
        });
        roamingService.updateRoaming($scope.roaming);
    }

    function getVehicleAccordingToRoamingDate(roamingDate) {
        return parseInt(roamingDate.substring(8,10)) % 2 == 0 ? '2' : '1';
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

});

roamingEditor.controller('InterventionController', function InterventionController($scope, $routeParams, $location, $filter, roamingService) {

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
        getLocation();
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
            tents: 0
        };
    }

    function getLocation() {
        if (navigator.geolocation) {
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
                                console.log(results);
                                $scope.$apply(function() {
                                    if (status == google.maps.GeocoderStatus.OK && results[0] && results[0].formatted_address) {
                                        if ($scope.intervention.location == '') {
                                            $scope.intervention.location = results[0].formatted_address;
                                        }
                                    }
                                });
                            }
                        );
                    }
                } catch (e) {
                    console.log('Unable to retrieve location', e);
                }
            });
        } else {
            console.log('Geolocalisation not supported');
        }
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

    $scope.range = function(min, max, step) {
        step = step || 1;
        var input = [];
        for (var i = min; i <= max; i += step) {
            input.push(i);
        }
        return input;
    };

    $scope.addPerson = function () {
        $scope.intervention.people.push('');
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
        $location.path('/roaming/' + $routeParams.roamingId);
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


roamingEditor.controller('DebugController', function DebugController($scope, $location, roamingService) {

    $scope.roamings = roamingService.getAllRoamings();

    $scope.reset = function () {
        var roamingsJson = JSON.stringify({});
        localStorage.setItem('roamings', roamingsJson);
    }

});

