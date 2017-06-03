<?php

# Debug
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
ini_set('display_errors', 1);

define('ADMIN_EMAIL', '');
define('DEFAULT_TIME_ZONE', 'Europe/Paris');
define('APPLICATION_PATH', '/roamingEditor');

define('MIN_PASSWORD_LENGTH', 6);
define('MAX_PASSWORD_LENGTH', 50);
define('REPORT_OLD_LIMIT_DAYS', 31);

define('AUTOLOGIN_COOKIE_KEY', 'vcrPersistentLogin');
define('AUTOLOGIN_COOKIE_EXPIRATION', '5356800'); // 62 jours

# Set TimeZone
date_default_timezone_set(DEFAULT_TIME_ZONE);

# Exceptions
class UnauthenticatedException extends Exception { }
class ForbiddenException extends Exception { }
class BadRequestException extends Exception { }
class NotFoundException extends Exception { }

