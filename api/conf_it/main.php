<?php

# Debug
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE); # git-hook-ignore
ini_set('display_errors', 1); # git-hook-ignore

define('ADMIN_EMAIL', 'it-test-admin@samu-social-grenoble.fr');
define('NOREPLY_EMAIL', 'it-test-noreply@samu-social-grenoble.fr');
define('SECRETARIAT_EMAIL', 'it-test-secretariat@samu-social-grenoble.fr');
define('PRESIDENT_EMAIL', 'it-test-president@samu-social-grenoble.fr');
define('PRESIDENT_REPORTING_EMAIL', 'it-test-president-reporting@samu-social-grenoble.fr');
define('DEFAULT_TIME_ZONE', 'Europe/Paris');
define('APPLICATION_PATH', '/');
define('PORTAL_APPLICATION_PATH', APPLICATION_PATH.'/portal');

define('MIN_PASSWORD_LENGTH', 8);
define('MAX_PASSWORD_LENGTH', 50);
define('BRUTEFORCE_PERIOD_IN_SECOND', 300);
define('BRUTEFORCE_MAX_NB_ATTEMPTS', 10);
define('REPORT_OLD_LIMIT_DAYS', 62);

define('SESSION_COOKIE_KEY', 'vinciSession');
define('AUTOLOGIN_ID_COOKIE_KEY', 'vinciPersistentLoginId');
define('AUTOLOGIN_TOKEN_COOKIE_KEY', 'vinciPersistentLoginToken');
define('AUTOLOGIN_COOKIE_EXPIRATION', '5356800'); // 62 jours
define('AUTOLOGIN_DB_EXPIRATION', '5443200'); // 63 jours
define('APPLICATION_ID_COOKIE_KEY', 'vinciApplicationId');
define('APPLICATION_TOKEN_COOKIE_KEY', 'vinciApplicationToken');

define('CALENDAR_PROVIDER_URL', 'https://calendar.google.com/calendar/embed?src=');
define('CALENDAR_ID', '');

# Set TimeZone
date_default_timezone_set(DEFAULT_TIME_ZONE);

