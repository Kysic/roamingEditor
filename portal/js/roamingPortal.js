/* Roaming Editor - License GNU GPL - https://github.com/Kysic/roamingEditor */

var roamingApiEndPoint = '../api';

var roamingPortal = angular.module('roamingPortal', ['ngRoute']);

roamingPortal.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/roamingsList/', {
        templateUrl: 'templates/roamingsList.html',
        controller: 'RoamingListController',
        reloadOnSearch: false
    })
    .when('/login/:email?/:referer?', {
        templateUrl: 'templates/login.html',
        controller: 'LoginController'
    })
    .when('/register/:email?', {
        templateUrl: 'templates/register.html',
        controller: 'RegisterController'
    })
    .when('/resetPassword/:email?', {
        templateUrl: 'templates/resetPassword.html',
        controller: 'ResetPasswordController'
    })
    .when('/setPassword/:userId?/:mailToken?', {
        templateUrl: 'templates/setPassword.html',
        controller: 'SetPasswordController'
    })
    .when('/mailSent/:email?', {
        templateUrl: 'templates/mailSent.html',
        controller: 'MailSentController'
    })
    .when('/users', {
        templateUrl: 'templates/users.html',
        controller: 'UsersController'
    })
    .otherwise({
        redirectTo: '/roamingsList'
    });
}]);

/* Services */
roamingPortal.factory('authService', function ($rootScope, $http) {

    var sessionInfo = { loggedIn: 'unknown', user: {}, sessionToken: '', lastError: '' };

    function updateSessionInfo(responseData) {
        sessionInfo.lastError = '';
        sessionInfo.user = responseData.user;
        sessionInfo.sessionToken = responseData.sessionToken;
        sessionInfo.loggedIn = responseData.isLoggedIn;
    }

    function querySessionInfo() {
        $http.get(roamingApiEndPoint + '/auth.php').then(function (response) {
            updateSessionInfo(response.data);
        });
    }

    function setLastError(responseData, action) {
        if (responseData.errorMsg) {
            sessionInfo.lastError = responseData.errorMsg;
        } else {
            sessionInfo.lastError = 'Réponse du serveur invalide.';
        }
        if (action) {
            $rootScope.$broadcast(action + '_fail');
        }
    }

    function doAuthAction(params) {
        sessionInfo.lastError = '';
        $http.post(roamingApiEndPoint + '/auth.php', params).then(function (response) {
            if (response.data.status == 'success') {
                updateSessionInfo(response.data);
                $rootScope.$broadcast(params.action);
            } else {
                setLastError(response.data, params.action);
            }
        }, function (response) {
            setLastError(response.data, params.action);
        });
    }

    function login(email, password, stayLogged) {
        doAuthAction({
            action: 'login',
            sessionToken: sessionInfo.sessionToken,
            email: email,
            password: password,
            stayLogged: stayLogged
        });
    }

    function logout() {
        doAuthAction({
            action: 'logout',
            sessionToken: sessionInfo.sessionToken
        });
    }

    function register(email) {
        doAuthAction({
            action: 'register',
            sessionToken: sessionInfo.sessionToken,
            email: email
        });
    }

    function resetPassword(email) {
        doAuthAction({
            action: 'resetPassword',
            sessionToken: sessionInfo.sessionToken,
            email: email
        });
    }

    function setPassword(password, passwordConfirm, userId, mailToken) {
        doAuthAction({
            action: 'setPassword',
            sessionToken: sessionInfo.sessionToken,
            password: password,
            passwordConfirm: passwordConfirm,
            userId: userId,
            mailToken: mailToken
        });
    }

    function getSessionInfo() {
        querySessionInfo(); // The session info is updated asynchronously
        return sessionInfo;
    }

    return {
        getSessionInfo: getSessionInfo,
        login: login,
        logout: logout,
        register: register,
        resetPassword: resetPassword,
        setPassword: setPassword
    };
});
roamingPortal.factory('dateUtils', function () {
    var weekDays = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi' ];
    var months = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    function humanDate(date) {
        return weekDays[date.getDay()] + ' ' + date.getDate() + ' ' +  humanMonth(date);
    }
    function humanMonth(date) {
        return months[date.getMonth()] + ' ' + date.getFullYear();
    }
    function toLocalMonthId(date) {
        return date.getFullYear() + '-' + ('0' + (date.getMonth() + 1)).slice(-2);
    }
    function toLocalIsoDate(date) {
        return date.getFullYear() + '-' + ('0' + (date.getMonth() + 1)).slice(-2) + '-' + ('0' + date.getDate()).slice(-2);
    }
    return {
        humanDate: humanDate,
        humanMonth: humanMonth,
        toLocalMonthId: toLocalMonthId,
        toLocalIsoDate: toLocalIsoDate
    };
});

/* Filters */
roamingPortal.filter('humanDate', function(dateUtils) {
    return function(date) {
        var objDate = typeof date === 'string' ? new Date(date) : date;
        return dateUtils.humanDate(objDate);
    };
});
roamingPortal.filter('humanMonth', function(dateUtils) {
    return function(date) {
        var objDate = typeof date === 'string' ? new Date(date) : date;
        return dateUtils.humanMonth(objDate);
    };
});
roamingPortal.filter('capitalize', function() {
    return function(input) {
        return (!!input) ? input.charAt(0).toUpperCase() + input.substr(1).toLowerCase() : '';
    }
});

/* Controllers */
roamingPortal.controller('RoamingListController', function RoamingListController(
$scope, $http, $window, $routeParams, $location, authService, dateUtils) {

    $scope.sessionInfo = authService.getSessionInfo();
    $scope.monthList;
    $scope.month;
    $scope.roamings;
    $scope.roamingByFour;
    $scope.editRunning;
    $scope.uploadRunning;
    $scope.showMonth = showMonth;
    $scope.roamingApiEndPoint = roamingApiEndPoint;
    $scope.editRoaming = editRoaming;
    $scope.editPlanning = editPlanning;
    $scope.uploadReport = uploadReport;
    $scope.deleteReport = deleteReport;
    $scope.enrol = enrol;
    $scope.cancel = cancel;
    $scope.setPassword = setPassword;
    $scope.logout = logout;
    $scope.hasP = hasP;
    $scope.reportUploadId = reportUploadId;
    $scope.isSelectedMonth = isSelectedMonth;
    $scope.isCurrentUser = isCurrentUser;

    $scope.$watch('sessionInfo', function () {
        if ($scope.sessionInfo.loggedIn === false) {
            $location.path('/login');
        }
        // the number of tabs shown differs according to the
        // user permission
        populateMonthList();
    }, true);


    populateMonth();
    populateMonthList();
    showMonth($scope.month)


    function populateMonth() {
        var d = new Date();
        $scope.month = new Date(d.getFullYear(), d.getMonth(), 1);
        if ($routeParams.month && $routeParams.month.match(/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/)) {
            try {
                $scope.month = new Date($routeParams.month);
            } catch (e) {
                console.log('Unable to parse date ' + $routeParams.month + '-01');
            }
        }
    }

    function populateMonthList() {
        $scope.monthList = [];
        var d = new Date();
        var histo = hasP('P_SEE_ALL_REPORT') ? -6 : -1;
        for (i = histo; i <= 2; i++) {
            $scope.monthList.push(new Date(d.getFullYear(), d.getMonth() + i, 1));
        }
    }

    function initRoamings() {
        $scope.roamings = [];
        var lastDayOfMonth = new Date($scope.month.getFullYear(), $scope.month.getMonth() + 1, 0);
        for (var d = $scope.month; d <= lastDayOfMonth;
                 d = new Date(d.getFullYear(), d.getMonth(), d.getDate() + 1)) {
            var roaming = new Object();
            roaming.date = dateUtils.toLocalIsoDate(d);
            roaming.isToday = isToday(d);
            roaming.isYesterday = isYesterday(d);
            roaming.isPast = isPast(d);
            roaming.hasWebReport = false;
            roaming.hasFileReport = false;
            roaming.status = 'unknown';
            $scope.roamings.push(roaming);
        }
    }

    function retrieveRoamings() {
        $http.get(roamingApiEndPoint + '/getRoamings.php?'+dateRangeQuerySelector()).then(function (response) {
            if (response.data.status == 'success' && response.data.roamings) {
                var roamingsObject = response.data.roamings;
                Object.keys(roamingsObject).forEach(function(roamingId) {
                    var roamingReceived = roamingsObject[roamingId];
                    var roaming = findRoamingByDate(roamingReceived.date);
                    roaming.id = roamingId;
                    roaming.tutor = roamingReceived.tutor;
                    roaming.teammates = roamingReceived.teammates;
                    roaming.hasWebReport = true;
                    checkRoamingByFour(roaming);
                });
            }
        }, function (response) {
            if (response.status == 401) {
                $location.path('/login');
            }
        });
    }

    function retrieveReportsFiles() {
        $scope.reportsFiles = {};
        $http.get(roamingApiEndPoint + '/getReportsFiles.php?'+dateRangeQuerySelector()).then(function (response) {
            if (response.data.status == 'success' && response.data.reports) {
                var reports = response.data.reports;
                for (var i = 0; i < reports.length; i++) {
                    var roamingDate = reports[i];
                    var roaming = findRoamingByDate(roamingDate);
                    roaming.hasFileReport = true;
                }
            }
        }, function (response) {
            if (response.status == 401) {
                $location.path('/login');
            }
        });
    }

    function retrievePlanning() {
        $http.get(roamingApiEndPoint + '/getPlanning.php?'+dateRangeQuerySelector()).then(function (response) {
            var planning = response.data;
            for (var i = 0; i < $scope.roamings.length; i++) {
                var roaming = $scope.roamings[i];
                var date = roaming.date;
                if ( !roaming.tutor || !roaming.teammates ) {
                    roaming.status = planning[date]['status'];
                    roaming.tutor = planning[date]['tutor'];
                    roaming.teammates = planning[date]['teammates'];
                }
                checkRoamingByFour(roaming);
            }
        }, function (response) {
            if (response.status == 401) {
                $location.path('/login');
            }
        });
    }

    function showMonth(month) {
        if (month != $scope.month) {
            $scope.month = month;
            $location.search('month', dateUtils.toLocalIsoDate(month));
        }
        $scope.roamingByFour = false;
        initRoamings();
        retrieveRoamings();
        retrieveReportsFiles();
        retrievePlanning();
    }

    function hasP(permission) {
        return $scope.sessionInfo && $scope.sessionInfo.user
                && $scope.sessionInfo.user.permissions && $scope.sessionInfo.user.permissions.indexOf(permission) !== -1;
    }

    function editRoaming(roamingId) {
        $scope.editRunning = true;
        $http.get(roamingApiEndPoint + '/getDocUrl.php?roamingId=' + roamingId).then(function (response) {
            if (response.data.status == 'success' && response.data.editUrl) {
                if (!$window.open(response.data.editUrl)) {
                    window.location = response.data.editUrl;
                }
            }
            $scope.editRunning = false;
        }, function (response) {
            $scope.editRunning = false;
            if (response.status == 401) {
                $location.path('/login');
            }
        });
        
    }

    function editPlanning(date) {
        var monthId = dateUtils.toLocalMonthId(date);
        $http.get(roamingApiEndPoint + '/getPlanningUrl.php?monthId=' + monthId).then(function (response) {
            if (response.data.status == 'success' && response.data.editUrl) {
                if (!$window.open(response.data.editUrl)) {
                    window.location = response.data.editUrl;
                }
            }
        }, function (response) {
            if (response.status == 401) {
                $location.path('/login');
            }
        });
    }

    function reportUploadId(roamingDate) {
        return 'report-' + roamingDate;
    }

    function uploadReport(roamingDate) {
        var file = document.getElementById(reportUploadId(roamingDate)).files[0];
        if (file) {
            var formData = new FormData();
            formData.append('sessionToken', $scope.sessionInfo.sessionToken);
            formData.append('report', file);
            var reader = new FileReader();
            reader.onloadend = function(event) {
                var data = event.target.result;
                $scope.uploadRunning = true;
                $http({
                    method: 'POST',
                    url: roamingApiEndPoint + '/uploadReport.php?roamingDate=' + roamingDate,
                    data: formData,
                    headers: { 'Content-Type': undefined }
                }).then(function success(response) {
                    if (response.data.status == 'success') {
                        var roaming = findRoamingByDate(roamingDate);
                        roaming.hasFileReport = true;
                    } else {
                        alert('L\'ajout du fichier a échoué');
                        console.log(response);
                    }
                    $scope.uploadRunning = false;
                }, function error(response) {
                    if (response.data.errorMsg) {
                        alert(response.data.errorMsg);
                    } else {
                        alert('L\'ajout du fichier a échoué');
                        console.log(response);
                    }
                    $scope.uploadRunning = false;
                });
            }
            reader.readAsBinaryString(file);
        }
    }

    function deleteReport(roamingDate) {
        if ( confirm('Supprimer le compte-rendu du ' + dateUtils.humanDate(new Date(roamingDate)) + ' ?') ) {
            $http.post(
                roamingApiEndPoint + '/rmReport.php',
                {
                    roamingDate: roamingDate,
                    sessionToken: $scope.sessionInfo.sessionToken
                }
            ).then(function (response) {
                if (response.data.status == 'success') {
                    var roaming = findRoamingByDate(roamingDate);
                    roaming.hasFileReport = false;
                }
            });
        }
    }

    function enrol(roaming, position) {
        _enrol(roaming, position, 'enrol', 'S\'inscrire', $scope.sessionInfo.user.username);
    }
    function cancel(roaming, position) {
        _enrol(roaming, position, 'cancel', 'Annuler votre inscription', '');
    }
    function _enrol(roaming, position, action, msg, newUsername) {
        if ( confirm(msg + ' pour la maraude du ' + dateUtils.humanDate(new Date(roaming.date)) + ' ?') ) {
            var prevUsername = getTeammate(roaming, position);
            setTeammate('[en cours]', roaming, position);
            $http.post(
                roamingApiEndPoint + '/enrol.php',
                {
                    action: action,
                    roamingDate: roaming.date,
                    position: position,
                    sessionToken: $scope.sessionInfo.sessionToken
                }
            ).then(function success(response) {
                if (response.data.status == 'success') {
                    setTeammate(newUsername, roaming, position);
                } else {
                    alert(response.data.errorMsg);
                    setTeammate(prevUsername, roaming, position);
                }
            }, function error(response) {
                if (response.data.errorMsg) {
                    alert(response.data.errorMsg);
                } else {
                    alert('Désolé, une erreur est survenue.');
                    console.log(response);
                }
                setTeammate(prevUsername, roaming, position);
            });
        }
    }
    function getTeammate(roaming, position) {
        return position == 0 ? roaming.tutor : roaming.teammates[position-1];
    }
    function setTeammate(teammate, roaming, position) {
        if (position == 0) {
            roaming.tutor = teammate;
        } else {
            roaming.teammates[position-1] = teammate;
        }
    }
    function setPassword() {
        $location.path('/setPassword');
    }
    function logout() {
        authService.logout();
    }

    function dateRangeQuerySelector() {
        var c = $scope.roamings;
        return 'from=' + c[0].date + '&to=' + c[c.length-1].date;
    }

    function isSelectedMonth(date) {
        return date.getMonth() == $scope.month.getMonth() && date.getFullYear() == $scope.month.getFullYear();
    }

    function isToday(dateStr) {
        var today = new Date();
        var d = new Date(dateStr);
        return d.getDate() == today.getDate() && d.getMonth() == today.getMonth() && d.getFullYear() == today.getFullYear();
    }

    function isYesterday(dateStr) {
        var today = new Date();
        var yesterday = new Date(today.getFullYear(), today.getMonth(), today.getDate()-1);
        var d = new Date(dateStr);
        return d.getDate() == yesterday.getDate() && d.getMonth() == yesterday.getMonth() && d.getFullYear() == yesterday.getFullYear();
    }

    function isPast(dateStr) {
        var today = new Date();
        var todayMidnight = new Date(today.getFullYear(), today.getMonth(), today.getDate());
        var d = new Date(dateStr);
        return d < todayMidnight;
    }

    function isCurrentUser(user) {
        return user.toLowerCase() == $scope.sessionInfo.user.username.toLowerCase();
    }

    function findRoamingByDate(date) {
        var roamings = $scope.roamings;
        for (var i = 0; i < roamings.length; i++) {
            var roaming = roamings[i];
            if (roaming.date == date) {
                return roaming;
            }
        }
        return new Object();
    }

    function checkRoamingByFour(roaming) {
        if (roaming.teammates.length >= 3 && roaming.teammates[2] != '') {
            $scope.roamingByFour = true;
        }
    }

});

roamingPortal.controller('LoginController', function LoginController($scope, $routeParams, $location, authService) {

    $scope.stayLogged = true;
    $scope.sessionInfo = authService.getSessionInfo();
    $scope.email = $routeParams.email;
    $scope.login = login;
    $scope.register = register;
    $scope.resetPassword = resetPassword;

    $scope.$watch('sessionInfo', function () {
        if ($scope.sessionInfo.loggedIn === true) {
            if ($routeParams.referer) {
                $location.path('/' + $routeParams.referer);
            } else {
                $location.path('/roamingsList');
            }
        }
    }, true);

    $scope.$on('login_fail', function () {
        $scope.loginInProgress = false;
    });

    function login() {
        $scope.loginInProgress = true;
        authService.login($scope.email, $scope.password, $scope.stayLogged);
    }

    function register() {
        $location.path('/register');
    }

    function resetPassword() {
        $location.path('/resetPassword');
    }

});

roamingPortal.controller('SetPasswordController', function SetPasswordController($scope, $routeParams, $location, authService) {

    $scope.sessionInfo = authService.getSessionInfo();
    if ( ! ($scope.sessionInfo.loggedIn || ($routeParams.userId && $routeParams.mailToken) ) ) {
        $location.path('/login');
    }

    function cleanLocation () {
        $location.search('userId', null);
        $location.search('mailToken', null);
    }

    $scope.setPassword = function () {
        $scope.passwordChangeInProgress = true;
        authService.setPassword($scope.password, $scope.passwordConfirm, $routeParams.userId, $routeParams.mailToken);
    }

    $scope.$on('setPassword', function () {
        cleanLocation();
        $location.path('/roamingsList');
    });

    $scope.$on('setPassword_fail', function () {
        $scope.passwordChangeInProgress = false;
    });

    $scope.cancel = function () {
        cleanLocation();
        $location.path('/roamingsList');
    }

});

roamingPortal.controller('RegisterController', function RegisterController($scope, $routeParams, $location, authService) {

    $scope.sessionInfo = authService.getSessionInfo();

    $scope.email = $routeParams.email;

    $scope.$watch('sessionInfo', function () {
        if ($scope.sessionInfo.loggedIn === true) {
            $location.path('/roamingsList');
        }
    }, true);

    $scope.register = function () {
        $scope.registerInProgress = true;
        authService.register($scope.email);
    }

    $scope.$on('register', function () {
        $location.path('/mailSent/' + $scope.email);
    });

    $scope.$on('register_fail', function () {
        $scope.registerInProgress = false;
    });

    $scope.cancel = function () {
        $location.path('/login');
    }

});

roamingPortal.controller('ResetPasswordController', function ResetPasswordController($scope, $routeParams, $location, authService) {

    $scope.sessionInfo = authService.getSessionInfo();

    $scope.email = $routeParams.email;

    $scope.resetPassword = function () {
        $scope.passwordResetInProgress = true;
        authService.resetPassword($scope.email);
    }

    $scope.$on('resetPassword', function () {
        $location.path('/mailSent/' + $scope.email);
    });

    $scope.$on('resetPassword_fail', function () {
        $scope.passwordResetInProgress = false;
    });

    $scope.cancel = function () {
        $location.path('/login');
    }

});

roamingPortal.controller('MailSentController', function MailSentController($scope, $routeParams, $location) {

    if ($routeParams.email) {
        $scope.email = $routeParams.email;
    } else {
        $scope.email = 'indiquée';
    }

    $scope.goLogin = function () {
        $location.path('/login/' + $scope.email);
    }

});

roamingPortal.controller('UsersController', function UsersController($scope, $http, $routeParams, $location, authService) {

    $scope.roles = ['unregistered', 'appli', 'former', 'guest', 'member', 'tutor', 'board', 'admin', 'root'];
    $scope.sessionInfo = authService.getSessionInfo();
    $scope.hasP = hasP;
    $scope.setRole = setRole;
    $scope.sendInvitation = sendInvitation;
    $scope.dbUsers;
    $scope.members;
    $scope.users;

    retrieveDbUsers();
    retrieveMembers();

    function retrieveDbUsers() {
        $http.get(roamingApiEndPoint + '/getUsers.php').then(function (response) {
            if (response.data.status == 'success' && response.data.users) {
                $scope.dbUsers = response.data.users;
                mergeUsersList();
            }
        }, function (response) {
            if (response.status == 401) {
                $location.path('/login//users');
            }
        });
    }

    function retrieveMembers() {
        $http.get(roamingApiEndPoint + '/getMembers.php').then(function (response) {
            if (response.data.status == 'success' && response.data.members) {
                $scope.members = response.data.members;
                mergeUsersList();
            }
        }, function (response) {
            if (response.status == 401) {
                $location.path('/login//users');
            }
        });
    }

    function hasP(permission) {
        return $scope.sessionInfo && $scope.sessionInfo.user
                && $scope.sessionInfo.user.permissions && $scope.sessionInfo.user.permissions.indexOf(permission) !== -1;
    }

    function mergeUsersList() {
        if ($scope.dbUsers === undefined) {
            return;
        }
        $scope.users = $scope.dbUsers.slice();
        if ($scope.members === undefined) {
            return;
        }
        for (var i = 0, len = $scope.users.length; i < len; i++) {
            var user = $scope.users[i];
            var member = $scope.members[user.email.toLowerCase()];
            if (member === undefined) {
                user.rightRole = user.role == 'appli' || user.role == 'former' || user.role == 'guest';
            } else {
                user.phoneNumber = member.phoneNumber;
                user.address = member.address;
                if (member.isBoard) {
                    user.rightRole = user.role == 'board' || user.role == 'admin' || user.role == 'root';
                }else if (member.isTutor) {
                    user.rightRole = user.role == 'tutor' || user.role == 'admin' || user.role == 'root';
                }  else {
                    user.rightRole = user.role == 'member' || user.role == 'admin' || user.role == 'root';
                }
            }
        }
        if (!hasP('P_ASSIGN_ROLE')) {
            var users = $scope.users;
            for(var i = users.length - 1; i >= 0; i--) {
                var user = users[i];
                if(user.role == 'former' || user.role == 'appli') {
                   users.splice(i, 1);
                }
            }
        }
        for (var email in $scope.members) {
            if (!hasUserWithMail(email)) {
                var member = $scope.members[email];
                $scope.users.push({
                    'firstname': member.firstname,
                    'lastname': member.lastname,
                    'email': email,
                    'phoneNumber': member.phoneNumber,
                    'address': member.address,
                    'role': 'unregistered',
                    'rightRole': true
                });
            }
        }
    }

    function hasUserWithMail(email) {
        for (var i = 0, len = $scope.users.length; i < len; i++) {
            if ($scope.users[i].email.toLowerCase() == email) {
                return true;
            }
        }
        return false;
    }

    function setErrorMsg(responseData) {
        if (responseData.errorMsg) {
            $scope.errorMsg = responseData.errorMsg;
        } else {
            $scope.errorMsg = 'Réponse du serveur invalide.';
        }
        retrieveDbUsers();
    }

    function setRole(user) {
        $scope.setRoleRunning = true;
        $scope.errorMsg = '';
        $http.post(roamingApiEndPoint + '/setUserRole.php',{
            sessionToken: $scope.sessionInfo.sessionToken,
            userId: user.userId,
            role: user.role
        }).then(function (response) {
            if (response.data.status == 'success') {
                $scope.setRoleRunning = false;
                mergeUsersList();
            } else {
                setErrorMsg(response.data);
            }
        }, function (response) {
            setErrorMsg(response.data);
        });
    }

    function sendInvitation(user) {
        authService.register(user.email);
    }

    $scope.$on('register', function () {
        retrieveDbUsers();
    });

});

