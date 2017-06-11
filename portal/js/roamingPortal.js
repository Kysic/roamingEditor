
var roamingApiEndPoint = '../api';

var roamingPortal = angular.module('roamingPortal', ['ngRoute']);

roamingPortal.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/roamingsList', {
        templateUrl: 'templates/roamingsList.html',
        controller: 'RoamingListController'
    })
    .when('/login/:email?/:referer?', {
        templateUrl: 'templates/login.html',
        controller: 'LoginController'
    })
    .when('/signin/:email?', {
        templateUrl: 'templates/signin.html',
        controller: 'SigninController'
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


roamingPortal.factory('authService', function ($rootScope, $http) {

    var sessionInfo = { loggedIn: false, user: {}, sessionToken: '', lastError: '' };

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

    function signin(email) {
        doAuthAction({
            action: 'signin',
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
        signin: signin,
        resetPassword: resetPassword,
        setPassword: setPassword
    };
});

roamingPortal.controller('RoamingListController', function RoamingListController($scope, $http, $window, $location, authService) {

    $scope.sessionInfo = authService.getSessionInfo();
    retrieveRoamings();

    function retrieveRoamings() {
        $http.get(roamingApiEndPoint + '/getRoamings.php').then(function (response) {
            if (response.data.status == 'success' && response.data.roamings) {
                var roamingsObject = response.data.roamings;
                $scope.roamings = Object.keys(roamingsObject).map(function(roamingId) {
                    var roaming = roamingsObject[roamingId];
                    roaming.id = roamingId;
                    return roaming;
                });
            }
        }, function (response) {
            if (response.status == 401) {
                $location.path('/login');
            }
        });
    }

    $scope.hasP = function (permission) {
        return $scope.sessionInfo && $scope.sessionInfo.user
                && $scope.sessionInfo.user.permissions && $scope.sessionInfo.user.permissions.includes(permission);
    }

    $scope.printRoaming = function (roamingId) {
        $window.open(roamingApiEndPoint + '/getPdf.php?roamingId=' + roamingId);
    }

    $scope.editRoaming = function (roamingId) {
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

    $scope.setPassword = function () {
        $location.path('/setPassword');
    }
    $scope.logout = function () {
        authService.logout();
    }
    $scope.$watch('sessionInfo', function () {
        if (!$scope.sessionInfo.loggedIn) {
            $location.path('/login');
        }
    }, true);

});

roamingPortal.controller('LoginController', function LoginController($scope, $routeParams, $location, authService) {

    $scope.stayLogged = true;
    $scope.sessionInfo = authService.getSessionInfo();

    $scope.email = $routeParams.email;

    $scope.$watch('sessionInfo', function () {
        if ($scope.sessionInfo.loggedIn) {
            if ($routeParams.referer) {
                $location.path('/' + $routeParams.referer);
            } else {
                $location.path('/roamingsList');
            }
        }
    }, true);

    $scope.login = function () {
        $scope.loginInProgress = true;
        authService.login($scope.email, $scope.password, $scope.stayLogged);
    }

    $scope.$on('login_fail', function () {
        $scope.loginInProgress = false;
    });

    $scope.signin = function () {
        $location.path('/signin');
    }

    $scope.resetPassword = function () {
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

roamingPortal.controller('SigninController', function SigninController($scope, $routeParams, $location, authService) {

    $scope.sessionInfo = authService.getSessionInfo();

    $scope.email = $routeParams.email;

    $scope.$watch('sessionInfo', function () {
        if ($scope.sessionInfo.loggedIn) {
            $location.path('/roamingsList');
        }
    }, true);

    $scope.signin = function () {
        $scope.signinInProgress = true;
        authService.signin($scope.email);
    }

    $scope.$on('signin', function () {
        $location.path('/mailSent/' + $scope.email);
    });

    $scope.$on('signin_fail', function () {
        $scope.signinInProgress = false;
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

    $scope.roles = ['appli', 'former', 'guest', 'member', 'tutor', 'board', 'admin', 'root'];
    $scope.sessionInfo = authService.getSessionInfo();
    retrieveUsers();

    function retrieveUsers() {
        $http.get(roamingApiEndPoint + '/getUsers.php').then(function (response) {
            if (response.data.status == 'success' && response.data.users) {
                $scope.users = response.data.users;
            }
        }, function (response) {
            if (response.status == 401) {
                $location.path('/login//users');
            }
        });
    }

    $scope.hasP = function (permission) {
        return $scope.sessionInfo && $scope.sessionInfo.user
                && $scope.sessionInfo.user.permissions && $scope.sessionInfo.user.permissions.includes(permission);
    }

    function setErrorMsg(responseData) {
        if (responseData.errorMsg) {
            $scope.errorMsg = responseData.errorMsg;
        } else {
            $scope.errorMsg = 'Réponse du serveur invalide.';
        }
        retrieveUsers();
    }

    $scope.setRole = function (user) {
        $scope.setRoleRunning = true;
        $scope.errorMsg = '';
        $http.post(roamingApiEndPoint + '/setUserRole.php',{
            sessionToken: $scope.sessionInfo.sessionToken,
            userId: user.userId,
            role: user.role
        }).then(function (response) {
            if (response.data.status == 'success') {
                $scope.setRoleRunning = false;
            } else {
                setErrorMsg(response.data);
            }
        }, function (response) {
            setErrorMsg(response.data);
        });
    }

});

