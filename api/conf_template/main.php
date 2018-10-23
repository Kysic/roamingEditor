<?php

# Debug
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
ini_set('display_errors', 1);

define('ADMIN_EMAIL', '');
define('SECRETARIAT_EMAIL', '');
define('PRESIDENT_EMAIL', '');
define('DEFAULT_TIME_ZONE', 'Europe/Paris');
define('APPLICATION_PATH', '/roamingEditor');
define('PORTAL_APPLICATION_PATH', APPLICATION_PATH.'/portal');

define('MIN_PASSWORD_LENGTH', 8);
define('MAX_PASSWORD_LENGTH', 50);
define('BRUTEFORCE_PERIOD_IN_SECOND', 300);
define('BRUTEFORCE_MAX_NB_ATTEMPTS', 5);
define('REPORT_OLD_LIMIT_DAYS', 62);

define('SESSION_COOKIE_KEY', 'vinciSession');
define('AUTOLOGIN_ID_COOKIE_KEY', 'vinciPersistentLoginId');
define('AUTOLOGIN_TOKEN_COOKIE_KEY', 'vinciPersistentLoginToken');
define('AUTOLOGIN_COOKIE_EXPIRATION', '5356800'); // 62 jours
define('AUTOLOGIN_DB_EXPIRATION', '5443200'); // 63 jours
define('APPLICATION_ID_COOKIE_KEY', 'vinciApplicationId');
define('APPLICATION_TOKEN_COOKIE_KEY', 'vinciApplicationToken');

define('TEAMUP_URL', 'https://teamup.com');
define('TEAMUP_SEE_MEETING_ID', '');
define('TEAMUP_EDIT_MEETING_ID', '');

# Set TimeZone
date_default_timezone_set(DEFAULT_TIME_ZONE);

# Exceptions
class UnauthenticatedException extends Exception { }
class ForbiddenException extends Exception { }
class BadRequestException extends Exception { }
class NotFoundException extends Exception { }
class SecurityException extends Exception { }

