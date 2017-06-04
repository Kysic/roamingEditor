<?php

# Google docs URL
define('GOOGLE_DOC_URL', 'http://localhost'.APPLICATION_PATH.'/api/tests/googleStub/');
define('GOOGLE_DOC_CMD_CSV', '.csv?sheetId=');
define('GOOGLE_DOC_CMD_EDIT', '.html?sheeId=');
define('GOOGLE_DOC_CMD_PDF', '.pdf');

# Google SpreadSheets Generator Script
define('GOOGLE_SPREADSHEETS_GENERATOR', 'http://localhost'.APPLICATION_PATH.'/api/tests/googleStub/sprdshtsgen.php');

# Google docs contacts liste
define('CONTACT_DOC_ID', 'contactDocId');
define('CONTACT_SHEET_ID', 'contactSheetId');

# Google docs roamings subscription
define('DOC_ID', 'DOC_ID');
define('SHEET_ID', 'SHEET_ID');
class GooglePlanningRef {
    private static $DOC_REF = array(
        '2016-01' => array( DOC_ID => 'planningDocId2016', SHEET_ID => 'sheetId01'),
        '2016-02' => array( DOC_ID => 'planningDocId2016', SHEET_ID => 'sheetId02'),
        '2016-03' => array( DOC_ID => 'planningDocId2016', SHEET_ID => 'sheetId03'),
        '2016-04' => array( DOC_ID => 'planningDocId2016', SHEET_ID => 'sheetId04'),
        '2016-05' => array( DOC_ID => 'planningDocId2016', SHEET_ID => 'sheetId05'),
        '2016-06' => array( DOC_ID => 'planningDocId2016', SHEET_ID => 'sheetId06'),
        '2016-07' => array( DOC_ID => 'planningDocId2016', SHEET_ID => 'sheetId07'),
        '2016-08' => array( DOC_ID => 'planningDocId2016', SHEET_ID => 'sheetId08'),
        '2016-09' => array( DOC_ID => 'planningDocId2016', SHEET_ID => 'sheetId09'),
        '2016-10' => array( DOC_ID => 'planningDocId2016', SHEET_ID => 'sheetId10'),
        '2016-11' => array( DOC_ID => 'planningDocId2016', SHEET_ID => 'sheetId11'),
        '2016-12' => array( DOC_ID => 'planningDocId2016', SHEET_ID => 'sheetId12'),

        '2017-01' => array( DOC_ID => 'planningDocId2017', SHEET_ID => 'sheetId01'),
        '2017-02' => array( DOC_ID => 'planningDocId2017', SHEET_ID => 'sheetId02'),
        '2017-03' => array( DOC_ID => 'planningDocId2017', SHEET_ID => 'sheetId03'),
        '2017-04' => array( DOC_ID => 'planningDocId2017', SHEET_ID => 'sheetId04'),
        '2017-05' => array( DOC_ID => 'planningDocId2017', SHEET_ID => 'sheetId05'),
        '2017-06' => array( DOC_ID => 'planningDocId2017', SHEET_ID => 'sheetId06'),
        '2017-07' => array( DOC_ID => 'planningDocId2017', SHEET_ID => 'sheetId07'),
        '2017-08' => array( DOC_ID => 'planningDocId2017', SHEET_ID => 'sheetId08'),
        '2017-09' => array( DOC_ID => 'planningDocId2017', SHEET_ID => 'sheetId09'),
        '2017-10' => array( DOC_ID => 'planningDocId2017', SHEET_ID => 'sheetId10'),
        '2017-11' => array( DOC_ID => 'planningDocId2017', SHEET_ID => 'sheetId11'),
        '2017-12' => array( DOC_ID => 'planningDocId2017', SHEET_ID => 'sheetId12')
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

