<?php

# SQL Server Configuration
define('SQL_TYPE' , 'mysql');
define('SQL_SERVER', 'docker_database_1');
define('SQL_DATABASE', 'pl12350-freeh_vinciplanning');
define('SQL_USER', 'vinciplanning');
define('SQL_PASSWORD', 'SQL_IT_PASSWORD');

# Tables
define('SQL_TABLE_PREFIX', 'it_');
define('SQL_TABLE_USERS', SQL_TABLE_PREFIX.'users');
define('SQL_TABLE_AUTOLOGIN', SQL_TABLE_PREFIX.'autologin');
define('SQL_TABLE_ROAMINGS', SQL_TABLE_PREFIX.'roamings');

