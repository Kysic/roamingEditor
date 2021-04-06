<?php

# SQL Server Configuration
define('SQL_TYPE' , 'mysql');
define('SQL_SERVER', 'database');
define('SQL_DATABASE', 'vinciplanning');
define('SQL_USER', 'vinciplanning');
define('SQL_PASSWORD', '03Wtrx7j5Ztn');

# Tables
define('SQL_TABLE_PREFIX', 'it_');
define('SQL_TABLE_USERS', SQL_TABLE_PREFIX.'users');
define('SQL_TABLE_AUTOLOGIN', SQL_TABLE_PREFIX.'autologin');
define('SQL_TABLE_ROAMINGS', SQL_TABLE_PREFIX.'roamings');
define('SQL_TABLE_BRUTEFORCE', SQL_TABLE_PREFIX.'bruteforce');
define('SQL_TABLE_REPORTS', SQL_TABLE_PREFIX.'reports');
