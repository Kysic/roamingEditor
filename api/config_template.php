<?php

# Debug
#error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

# Google docs roamings subscription
define('DOC_ID', 'DOC_ID');
define('SHEET_ID', 'SHEET_ID');
$DOC_REF = array(
    '2017-01' => array( DOC_ID => '', SHEET_ID => ''),
    '2017-02' => array( DOC_ID => '', SHEET_ID => ''),
    '2017-03' => array( DOC_ID => '', SHEET_ID => ''),
    '2017-04' => array( DOC_ID => '', SHEET_ID => ''),
    '2017-05' => array( DOC_ID => '', SHEET_ID => ''),
    '2017-06' => array( DOC_ID => '', SHEET_ID => ''),
    '2017-07' => array( DOC_ID => '', SHEET_ID => ''),
    '2017-08' => array( DOC_ID => '', SHEET_ID => ''),
    '2017-09' => array( DOC_ID => '', SHEET_ID => ''),
    '2017-10' => array( DOC_ID => '', SHEET_ID => ''),
    '2017-11' => array( DOC_ID => '', SHEET_ID => ''),
    '2017-12' => array( DOC_ID => '', SHEET_ID => '')
);

# Google docs URL
define('GOOGLE_DOC_URL', 'https://docs.google.com/spreadsheets/d/');
define('GOOGLE_DOC_CMD_CSV', '/export?format=csv&gid=');
define('GOOGLE_DOC_CMD_EDIT', '/edit?pref=2&pli=1#gid=');

date_default_timezone_set(DEFAULT_TIME_ZONE);

?>
