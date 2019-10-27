/* Roaming Editor - License GNU GPL - https://github.com/Kysic/roamingEditor */

var roamingApiEndPoint = '../api';

var roamingHistoryNbDays = 30;

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
    .when('/donations/:roamingId?', {
        templateUrl: 'templates/donations.html',
        controller: 'DonationsController'
    })
    .when('/logistic/:roamingId?', {
        templateUrl: 'templates/logistic.html',
        controller: 'LogisticController'
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

/* Services */
roamingEditor.factory('roamingService', function ($rootScope, $filter, $http, $cookies, $interval) {

    var roamings = { };
    var resynchroTimer;

    loadLocalStorage();
    resynchro(true);

    // Trigger a resynchro every minute
    var resynchroTimer = $interval(function(){
        resynchro(false);
    },60000);

    function loadLocalStorage() {
        synchronizeApiCredCookies();
        roamings = { };
        Object.keys(localStorage).forEach(function(storageId) {
            if (storageId.match(/^roaming_[0-9-]*$/)) {
                try {
                    var roaming = loadRoamingFromLocalStorage(storageId);
                    roamings[roaming.date] = roaming;
                } catch (e) {
                    console.log('Unable to restore ' + storageId + ' from local storage', e);
                }
            }
        });
        filterOldRoamings();
    }

    function synchronizeApiCredCookies() {
        var apiId = localStorage.getItem('apiId');
        var apiToken = localStorage.getItem('apiToken');
        if ( apiId && apiToken ) {
            var expireDate = new Date();
            expireDate.setDate(expireDate.getDate() + 10 * 365);
            $cookies.put('vinciApplicationId', apiId, {'expires':expireDate});
            $cookies.put('vinciApplicationToken', apiToken, {'expires':expireDate});
        }
    }

    function updateRoamingInLocalStorage(roaming) {
        var roamingJson = JSON.stringify(roaming);
        localStorage.setItem(getLocalStorageId(roaming.date), roamingJson);
    }

    function loadRoamingFromLocalStorage(storageId) {
        var roamingJson = localStorage.getItem(storageId);
        return roamingJson === null ? null : JSON.parse(roamingJson);
    }

    function getLocalStorageId(roamingDate) {
        return 'roaming_' + roamingDate;
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
            localStorage.removeItem(getLocalStorageId(roamingDate));
            console.log('Roaming ' + roamingDate + ' deleted');
        } catch (e) {
            console.log('Unable to delete roaming ' + roamingDate);
        }
    }

    function filterOldRoamings() {
        var olderRoamingDateAccepted = new Date(new Date().getTime() - roamingHistoryNbDays * 24 * 60 * 60 * 1000);
        var olderRoamingIdAccepted = $filter('date')(olderRoamingDateAccepted, 'yyyy-MM-dd');
        for (var roamingDate in roamings) {
            if (roamingDate < olderRoamingIdAccepted) {
                deleteRoaming(roamingDate);
            }
        }
    }

    function resynchro(force) {
        for (var roamingId in roamings) {
            var roaming = roamings[roamingId];
            if (roaming.synchroStatus != 'SYNCHRONIZED') {
                if (force || roaming.synchroStatus != 'IN_PROGRESS') {
                    sendRoamingToRemoteServer(roaming);
                }
            }
        }
    }

    function updateRoaming(roaming) {
        roaming.version = roaming.version ? roaming.version + 1 : 1;
        var lastRoamingInLocalStorage = loadRoamingFromLocalStorage(getLocalStorageId(roaming.date));
        if (lastRoamingInLocalStorage && lastRoamingInLocalStorage.version >= roaming.version) {
            alert('Conflit de modification avec un autre onglet du navigateur.');
            roaming = lastRoamingInLocalStorage;
            roamings[roaming.date] = roaming;
            notifyRoamingUpdate(roaming);
        } else {
            roamings[roaming.date] = roaming;
            filterOldRoamings();
            updateRoamingInLocalStorage(roaming);
            sendRoamingToRemoteServer(roaming);
        }
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
roamingEditor.factory('dateUtils', function () {
    var weekDays = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi' ];
    var months = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    function humanDate(date) {
        return weekDays[date.getDay()] + ' ' + date.getDate() + ' ' +  humanMonth(date);
    }
    function humanMonth(date) {
        return months[date.getMonth()] + ' ' + date.getFullYear();
    }
    return {
        humanDate: humanDate,
        humanMonth: humanMonth
    };
});
roamingEditor.factory('mapService', function () {
    function showInterventionsOnMap(interventions, newWindow) {
        var places = [];
        for (var i in interventions) {
            var int = interventions[i];
            if (int.location) {
                var place = {
                    title: int.people.join(','),
                    address: int.location,
                    color: (int.nbAdults+int.nbChildren)>0?'green':'red'
                };
                places.push(place);
            }
        }
        showPlacesOnMap(places)
    }
    function showPlacesOnMap(places, newWindow) {
        sessionStorage.setItem('mapPlaces', JSON.stringify(places));
        var location = '/map/';
        if (newWindow) {
            window.open(location, 'map');
        } else {
            document.location = location;
        }
    }
    return {
        showInterventionsOnMap: showInterventionsOnMap,
        showPlacesOnMap: showPlacesOnMap
    };
});

/* Directives */
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

/* Filters */
roamingEditor.filter('humanDate', function(dateUtils) {
    return function(date) {
        var objDate = typeof date === 'string' ? new Date(date) : date;
        return dateUtils.humanDate(objDate);
    };
});
roamingEditor.filter('capitalize', function() {
    return function(input) {
        return (!!input) ? input.charAt(0).toUpperCase() + input.substr(1).toLowerCase() : '';
    }
});

/* Controllers */
roamingEditor.controller('RoamingListController', function RoamingListController($scope, $location, $filter, roamingService) {

    retrieveRoamings();

    function retrieveRoamings() {
        var roamingsMap = roamingService.getAllRoamings();
        var roamingsArray = Object.keys(roamingsMap).map(function(key) { return roamingsMap[key] });
        $scope.roamings = $filter('orderBy')(roamingsArray, '-date');
    }

    $scope.editRoaming = function (roaming) {
        $location.path('/roaming/' + roaming.date);
    }

    $scope.resynchro = function () {
        roamingService.resynchro(false);
    }

    $scope.createRoaming = function () {
        $location.path('/roaming/' + roamingService.getCurrentRoamingDateId());
    }

    $scope.goDonations = function () {
        $location.path('/donations');
    }

    $scope.logisticReport = function () {
        $location.path('/logistic');
    }

    $scope.$on('roamingUpdate', function () {
        retrieveRoamings();
    });

});

roamingEditor.controller('RoamingController',
  function RoamingController($scope, $routeParams, $location, $timeout, $http, $interval, $window, roamingService,
                             mapService) {

    $scope.synchroStatus;
    $scope.roaming;
    $scope.addIntervention = addIntervention;
    $scope.reportIntervention = reportIntervention;
    $scope.editIntervention = editIntervention;
    $scope.deleteIntervention = deleteIntervention;
    $scope.addTeammate = addTeammate;
    $scope.removeTeammate = removeTeammate;
    $scope.updateRoaming = updateRoaming;
    $scope.goRoamingList = goRoamingList;
    $scope.goMap = goMap;
    $scope.goMapLocation = goMapLocation;
    $scope.goDonations = goDonations;
    $scope.logisticReport = logisticReport;
    $scope.isEditable = isEditable;

    var refreshTimer;

    if ( ! /^[0-9]{4}-[0-9]{2}-[0-9]{2}$/.test($routeParams.roamingId)) {
        $location.path('/roamingsList');
        return;
    }
    if ($routeParams.roamingId) {
        $scope.roaming = roamingService.getRoaming($routeParams.roamingId);
    }
    if ($scope.roaming) {
      $scope.synchroStatus = $scope.roaming.synchroStatus;
    } else {
      initRoaming();
    }
    if ($scope.roaming.tutor == '' && angular.equals($scope.roaming.teammates, [ '' ])) {
        getTeammates()
    }

    $scope.$on('roamingUpdate', function (event, roaming) {
        if ($scope.roaming.date == roaming.date) {
            $scope.synchroStatus = roaming.synchroStatus;
        }
    });

    refreshTimer = $interval(function(){
        // Allow to refresh read only status every hour
    },3600000);

    $scope.$on('$destroy', function() {
        $interval.cancel(refreshTimer);
    });

    function initRoaming() {
        $scope.roaming = {
            date: $routeParams.roamingId,
            tutor: '',
            teammates: [ '' ],
            vehicle: getVehicleAccordingToRoamingDate($routeParams.roamingId),
            interventions: [ ]
        };
        roamingService.updateRoaming($scope.roaming);
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
            if (response.data.teammates && angular.equals($scope.roaming.teammates, [ '' ])) {
                $scope.roaming.teammates = response.data.teammates;
            }
            $scope.updateRoaming();
        });
    }

    function addIntervention() {
        $scope.editIntervention(-1);
    }

    function reportIntervention(interventionIndex) {
        var intervention = $scope.roaming.interventions[interventionIndex];
        $window.location.href = '/reportForm.php?names=' + intervention.people.join(', ')
            + '&date=' + $scope.roaming.date
            + '&place=' + intervention.location
            + ( intervention.phone ? '&phone=' + intervention.phone : '' )
            + '&observations=' + intervention.comments
            + '&author=' + $scope.roaming.tutor;
    }

    function editIntervention(interventionIndex) {
        $location.path('/roaming/' + $routeParams.roamingId + '/intervention/' + interventionIndex);
    }

    function deleteIntervention(interventionIndex) {
        if (confirm('Etes vous sur de vouloir supprimer cette intervention ?')) {
            $scope.roaming.interventions.splice(interventionIndex, 1);
            $scope.updateRoaming();
       }
    }

    function addTeammate() {
        $scope.roaming.teammates.push('');
        $scope.updateRoaming();
        // A timeout is required because the field doesn't exist yet
        $timeout(function () {
            var elts = document.querySelectorAll('.teammateInput');
            if (elts && elts.length > 0) {
                elts[elts.length-1].focus();
            }
        });
    }
    function removeTeammate(teammateIndex) {
        if (teammateIndex < $scope.roaming.teammates.length
            && ($scope.roaming.teammates[teammateIndex] == ''
                    || confirm('Etes vous sûr de vouloir enlever ' + $scope.roaming.teammates[teammateIndex] + ' ?')
            )
        ) {
            $scope.roaming.teammates.splice(teammateIndex, 1);
            if ($scope.roaming.teammates.length == 0) {
                $scope.roaming.teammates.push('');
            }
            $scope.updateRoaming();
        }
    }

    function updateRoaming() {
        roamingService.updateRoaming($scope.roaming);
    }

    function goRoamingList() {
        $location.path('/roamingsList');
    }

    function goMap() {
        mapService.showInterventionsOnMap($scope.roaming.interventions);
    }

    function goMapLocation(intervention) {
        mapService.showInterventionsOnMap([intervention]);
    }

    function goDonations() {
        $location.path('/donations/' + $scope.roaming.date);
    }

    function logisticReport() {
        $location.path('/logistic/' + $scope.roaming.date);
    }

    function isEditable() {
        return $routeParams.roamingId == roamingService.getCurrentRoamingDateId();
    }

});

roamingEditor.controller('InterventionController',
  function InterventionController($scope, $routeParams, $location, $filter, $timeout, roamingService, mapService) {

    $scope.sources = ['115', 'Maraude', 'Particulier', 'Direct', 'CHU', 'SemiTag', 'Pompier', 'Police', 'Autre'];
    $scope.localizeMe = localizeMe;
    $scope.resetLocation = resetLocation;
    $scope.goMap = goMap;
    $scope.range = range;
    $scope.addPerson = addPerson;
    $scope.removePerson = removePerson;
    $scope.cancelInterventionEdit = cancelInterventionEdit;
    $scope.saveInterventionEdit = saveInterventionEdit;

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
        if (!$scope.intervention.people || $scope.intervention.people.length == 0) {
            $scope.intervention.people = [ '' ];
        }
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
            hygiene: false,
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

    function localizeMe() {
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

    function resetLocation() {
        if ($scope.intervention.location != '' && confirm('Etes vous sûr de vouloir réinitialiser le lieu de la rencontre ?')) {
            $scope.intervention.location = '';
        }
    }

    function goMap() {
        var address = $scope.intervention.location;
        mapService.showPlacesOnMap([{ title: address, address: address }], true);
    }

    function range(min, max, step) {
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

    function addPerson() {
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
    function removePerson(personIndex) {
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

    function cancelInterventionEdit() {
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

    function saveInterventionEdit() {
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

roamingEditor.controller('DonationsController', function DonationsController($scope, $routeParams, $location, roamingService) {

    $scope.donations;
    $scope.goBack = goBack;

    retrieveDonations();

    function retrieveDonations() {
        var roamingsMap = roamingService.getAllRoamings();
        $scope.donations = [];
        for (var roamingDate in roamingsMap) {
            var roaming = roamingsMap[roamingDate];
            for (var intervention in roaming.interventions) {
                var donation = roaming.interventions[intervention];
                if (donation.blankets > 0 || donation.tents > 0 || donation.hygiene) {
                    donation.date = roamingDate;
                    $scope.donations.push(donation);
                }
            }
        }
    }

    function goBack() {
        if ($routeParams.roamingId) {
            $location.path('/roaming/' + $routeParams.roamingId);
        } else {
            $location.path('/roamingsList');
        }
    }

});

roamingEditor.controller('LogisticController', function LogisticController($scope, $routeParams, $location, $http) {

    $scope.goBack = goBack;
    $scope.send = send;
    $scope.msg = '';
    $scope.error = '';
    $scope.sendInProgress = false;

    function send() {
        if (!$scope.msg) {
            $scope.error = 'Erreur, veuillez renseigner un message.';
            return;
        }
        $scope.sendInProgress = true;
        $http.post(
            roamingApiEndPoint + '/logistic.php',
            { msg: $scope.msg }
        ).then(function (response) {
            if (response.data.status == 'success') {
                $scope.error = '';
                $scope.sendInProgress = false;
                alert('Message envoyé');
                goBack();
            } else {
                $scope.error = 'Erreur, impossible d\'envoyer le message.';
                $scope.sendInProgress = false;
            }
        }, function (response) {
            $scope.error = 'Erreur, impossible d\'envoyer le message.';
            $scope.sendInProgress = false;
        });
    }

    function goBack() {
        if ($routeParams.roamingId) {
            $location.path('/roaming/' + $routeParams.roamingId);
        } else {
            $location.path('/roamingsList');
        }
    }

});

roamingEditor.controller('DebugController', function DebugController($scope, $cookies, $filter, $location, roamingService) {

    loadCurrentConf();

    $scope.goRoamingsList = function () {
        $location.path('/roamingsList');
    }

    $scope.updateApiCreds = function () {
        localStorage.setItem('apiId', $scope.apiId);
        localStorage.setItem('apiToken', $scope.apiToken);
        $cookies.remove('vinciSession');
        var expireDate = new Date();
        expireDate.setDate(expireDate.getDate() + 10 * 365);
        $cookies.put('vinciApplicationId', $scope.apiId, {'expires':expireDate});
        $cookies.put('vinciApplicationToken', $scope.apiToken, {'expires':expireDate});
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

    function loadCurrentConf() {
        $scope.roamingsJSON = $filter('json')(roamingService.getAllRoamings());
        $scope.apiId = localStorage.getItem('apiId');
    }

});

