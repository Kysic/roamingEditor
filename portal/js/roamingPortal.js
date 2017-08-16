
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
    function toLocalIsoDate(date) {
        return date.getFullYear() + '-' + ('0' + (date.getMonth() + 1)).slice(-2) + '-' + ('0' + date.getDate()).slice(-2);
    }
    return {
        humanDate: humanDate,
        humanMonth: humanMonth,
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
    $scope.calendar;
    $scope.roamings;
    $scope.planning;
    $scope.reportsFiles;
    $scope.roamingByFour;
    $scope.editRunning;
    $scope.uploadRunning;
    $scope.showMonth = showMonth;
    $scope.roamingApiEndPoint = roamingApiEndPoint;
    $scope.editRoaming = editRoaming;
    $scope.uploadReport = uploadReport;
    $scope.deleteReport = deleteReport;
    $scope.setPassword = setPassword;
    $scope.logout = logout;
    $scope.hasP = hasP;
    $scope.reportUploadId = reportUploadId;
    $scope.isToday = isToday;
    $scope.isYesterday = isYesterday;
    $scope.isSelectedMonth = isSelectedMonth;
    $scope.isPast = isPast;
    $scope.existsReportFile = existsReportFile;

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

    function populateCalendar() {
        $scope.calendar = []
        var lastDayOfMonth = new Date($scope.month.getFullYear(), $scope.month.getMonth() + 1, 0);
        for (var d = $scope.month; d <= lastDayOfMonth;
                 d = new Date(d.getFullYear(), d.getMonth(), d.getDate() + 1)) {
            $scope.calendar.push(dateUtils.toLocalIsoDate(d));
        }
    }

    function retrieveRoamings() {
        $scope.roamings = {};
        $http.get(roamingApiEndPoint + '/getRoamings.php?'+dateRangeQuerySelector()).then(function (response) {
            if (response.data.status == 'success' && response.data.roamings) {
                var roamingsObject = response.data.roamings;
                Object.keys(roamingsObject).forEach(function(roamingId) {
                    var roaming = roamingsObject[roamingId];
                    roaming.id = roamingId;
                    $scope.roamings[roaming.date] = roaming;
                    if (roaming.teammates.length >= 3 && roaming.teammates[2] != '') {
                        $scope.roamingByFour = true;
                    }
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
                $scope.reportsFiles = response.data.reports;
            }
        }, function (response) {
            if (response.status == 401) {
                $location.path('/login');
            }
        });
    }

    function retrievePlanning() {
        $scope.planning = {};
        $http.get(roamingApiEndPoint + '/getPlanning.php?'+dateRangeQuerySelector()).then(function (response) {
            $scope.planning = response.data;
            for (var i = 0; i < $scope.calendar.length; i++) {
                var day = $scope.calendar[i];
                var roamingTeammates = $scope.planning[day]['teammates'];
                if (roamingTeammates.length >= 3 && roamingTeammates[2] != '') {
                    $scope.roamingByFour = true;
                }
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
        populateCalendar();
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
                $window.open(response.data.editUrl);
            }
            $scope.editRunning = false;
        }, function (response) {
            $scope.editRunning = false;
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
                }).then(function successCallback(response) {
                    if (response.data.status == 'success') {
                        $scope.reportsFiles.push(roamingDate);
                    } else {
                        alert('Upload failed, see console log');
                        console.log(response);
                    }
                    $scope.uploadRunning = false;
                }, function errorCallback(response) {
                    if (response.data.errorMsg) {
                        alert(response.data.errorMsg);
                    } else {
                        alert('Upload failed, see console log');
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
                    $scope.reportsFiles = $scope.reportsFiles.filter(
                        function(item) { 
                            return item !== roamingDate;
                        }
                    );
                }
            });
        }
    }

    function setPassword() {
        $location.path('/setPassword');
    }
    function logout() {
        authService.logout();
    }

    function dateRangeQuerySelector() {
        var c = $scope.calendar;
        return 'from=' + c[0] + '&to=' + c[c.length-1];
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
        var d = new Date(dateStr);
        return d < today;
    }

    function existsReportFile(day) {
        return $scope.reportsFiles.length > 0 && $scope.reportsFiles.indexOf(day) !== -1;
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
            } else if (member.isBoard) {
                user.rightRole = user.role == 'board' || user.role == 'admin' || user.role == 'root';
            }else if (member.isTutor) {
                user.rightRole = user.role == 'tutor' || user.role == 'admin' || user.role == 'root';
            }  else {
                user.rightRole = user.role == 'member' || user.role == 'admin' || user.role == 'root';
            }
        }
        for (var email in $scope.members) {
            if (!hasUserWithMail(email)) {
                var member = $scope.members[email];
                $scope.users.push({
                    'firstname': member.firstname,
                    'lastname': member.lastname,
                    'email': email,
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

