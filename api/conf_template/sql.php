<?php

# SQL Server Configuration
define('SQL_TYPE' , 'mysql');
define('SQL_SERVER', '');
define('SQL_DATABASE', '');
define('SQL_USER', '');
define('SQL_PASSWORD', '');

# Tables
define('SQL_TABLE_PREFIX', 'vcr_');
define('SQL_TABLE_USERS', SQL_TABLE_PREFIX.'users');
define('SQL_TABLE_AUTOLOGIN', SQL_TABLE_PREFIX.'autologin');
define('SQL_TABLE_ROAMINGS', SQL_TABLE_PREFIX.'roamings');
define('SQL_TABLE_BRUTEFORCE', SQL_TABLE_PREFIX.'bruteforce');
define('SQL_TABLE_REPORTS', SQL_TABLE_PREFIX.'reports');
