<?php

# Debug
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
ini_set('display_errors', 1);

define('ADMIN_EMAIL', '');
define('DEFAULT_TIME_ZONE', 'Europe/Paris');
define('APPLICATION_PATH', '/roamingEditor');
define('PORTAL_APPLICATION_PATH', APPLICATION_PATH.'/portal');

define('MIN_PASSWORD_LENGTH', 8);
define('MAX_PASSWORD_LENGTH', 50);
define('BRUTEFORCE_PERIOD_IN_SECOND', 300);
define('BRUTEFORCE_MAX_NB_ATTEMPTS', 5);
define('REPORT_OLD_LIMIT_DAYS', 62);

define('SESSION_COOKIE_KEY', 'vinciSession');
define('AUTOLOGIN_COOKIE_KEY', 'vinciPersistentLogin');
define('AUTOLOGIN_COOKIE_EXPIRATION', '2678400'); // 31 jours
define('AUTOLOGIN_DB_EXPIRATION', '8035200'); // 93 jours

# Set TimeZone
date_default_timezone_set(DEFAULT_TIME_ZONE);

# Exceptions
class UnauthenticatedException extends Exception { }
class ForbiddenException extends Exception { }
class BadRequestException extends Exception { }
class NotFoundException extends Exception { }

