<?php

# Google docs URL
define('GOOGLE_DOC_URL', 'https://docs.google.com/spreadsheets/d/');
define('GOOGLE_DOC_CMD_CSV', '/export?format=csv');
define('GOOGLE_DOC_CMD_CSV_GID', '/export?format=csv&gid=');
define('GOOGLE_DOC_CMD_EDIT', '/edit?pref=2&pli=1');
define('GOOGLE_DOC_CMD_EDIT_GID', '/edit?pref=2&pli=1#gid=');
define('GOOGLE_DOC_CMD_PDF', '/export?format=pdf');

# Google SpreadSheets Generator Script
define('GOOGLE_SPREADSHEETS_GENERATOR', '');

# Google docs contacts liste
define('CONTACT_DOC_ID', '');
define('CONTACT_SHEET_ID', '');

# Google docs roamings subscription
define('DOC_ID', 'DOC_ID');
define('SHEET_ID', 'SHEET_ID');
class GooglePlanningRef {
    private static $DOC_REF = array(
        '2016-01' => array( DOC_ID => '', SHEET_ID => ''),
        '2016-02' => array( DOC_ID => '', SHEET_ID => ''),
        '2016-03' => array( DOC_ID => '', SHEET_ID => ''),
        '2016-04' => array( DOC_ID => '', SHEET_ID => ''),
        '2016-05' => array( DOC_ID => '', SHEET_ID => ''),
        '2016-06' => array( DOC_ID => '', SHEET_ID => ''),
        '2016-07' => array( DOC_ID => '', SHEET_ID => ''),
        '2016-08' => array( DOC_ID => '', SHEET_ID => ''),
        '2016-09' => array( DOC_ID => '', SHEET_ID => ''),
        '2016-10' => array( DOC_ID => '', SHEET_ID => ''),
        '2016-11' => array( DOC_ID => '', SHEET_ID => ''),
        '2016-12' => array( DOC_ID => '', SHEET_ID => ''),

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
        '2017-12' => array( DOC_ID => '', SHEET_ID => ''),

        '2018-01' => array( DOC_ID => '', SHEET_ID => ''),
        '2018-02' => array( DOC_ID => '', SHEET_ID => ''),
        '2018-03' => array( DOC_ID => '', SHEET_ID => ''),
        '2018-04' => array( DOC_ID => '', SHEET_ID => ''),
        '2018-05' => array( DOC_ID => '', SHEET_ID => ''),
        '2018-06' => array( DOC_ID => '', SHEET_ID => ''),
        '2018-07' => array( DOC_ID => '', SHEET_ID => ''),
        '2018-08' => array( DOC_ID => '', SHEET_ID => ''),
        '2018-09' => array( DOC_ID => '', SHEET_ID => ''),
        '2018-10' => array( DOC_ID => '', SHEET_ID => ''),
        '2018-11' => array( DOC_ID => '', SHEET_ID => ''),
        '2018-12' => array( DOC_ID => '', SHEET_ID => '')
    );
    public static function exists($monthId) {
        array_key_exists($monthId, self::$DOC_REF);
    }
    public static function getDocId($monthId) {
        return self::$DOC_REF[$monthId][DOC_ID];
    }
    public static function getSheetId($monthId) {
        return self::$DOC_REF[$monthId][SHEET_ID];
    }
}

