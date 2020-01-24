<?php

# Google docs URL
define('GOOGLE_DOC_URL', 'http://localhost'.APPLICATION_PATH.'/api/tests/googleMock/mock.php?docId=');
define('GOOGLE_DOC_CMD_CSV', '&type=csv');
define('GOOGLE_DOC_CMD_CSV_GID', '&type=csv&sheetId=');
define('GOOGLE_DOC_CMD_EDIT', '&type=edit');
define('GOOGLE_DOC_CMD_EDIT_GID', '&type=edit&sheetId=');
define('GOOGLE_DOC_CMD_PDF', '&type=pdf&sheetId=');
define('GOOGLE_DOC_CMD_HTML', '&type=html');

# Google SpreadSheets Generator Script
define('GOOGLE_SPREADSHEETS_GENERATOR', 'http://localhost'.APPLICATION_PATH.'/api/tests/googleMock/sprdshtsgen.php');

# Google Planning Enrol/Cancel Script
define('GOOGLE_ENROL_SCRIPT', 'http://localhost'.APPLICATION_PATH.'/api/tests/googleMock/enrol.php');

# Google docs contacts liste
define('CONTACT_DOC_ID', 'contactDocId');
define('CONTACT_ROAMING_SHEET_ID', 'contactRoamingSheetId');
define('CONTACT_OTHER_SHEET_ID', 'contactOtherSheetId');

# Google script to add new member
define('GOOGLE_ADD_MEMBER_SCRIPT', 'http://localhost'.APPLICATION_PATH.'/api/tests/googleMock/addMember.php');

# Google docs roamings subscription
define('DOC_ID', 'DOC_ID');
define('SHEET_ID', 'SHEET_ID');

define('PREVIOUS_YEAR', date('Y')-1);
define('CURRENT_YEAR', date('Y'));
define('NEXT_YEAR', date('Y')+1);

class GooglePlanningRef {
    private static $DOC_REF = array(
        PREVIOUS_YEAR.'-01' => array( DOC_ID => 'planningDocId', SHEET_ID => PREVIOUS_YEAR.'-01'),
        PREVIOUS_YEAR.'-02' => array( DOC_ID => 'planningDocId', SHEET_ID => PREVIOUS_YEAR.'-02'),
        PREVIOUS_YEAR.'-03' => array( DOC_ID => 'planningDocId', SHEET_ID => PREVIOUS_YEAR.'-03'),
        PREVIOUS_YEAR.'-04' => array( DOC_ID => 'planningDocId', SHEET_ID => PREVIOUS_YEAR.'-04'),
        PREVIOUS_YEAR.'-05' => array( DOC_ID => 'planningDocId', SHEET_ID => PREVIOUS_YEAR.'-05'),
        PREVIOUS_YEAR.'-06' => array( DOC_ID => 'planningDocId', SHEET_ID => PREVIOUS_YEAR.'-06'),
        PREVIOUS_YEAR.'-07' => array( DOC_ID => 'planningDocId', SHEET_ID => PREVIOUS_YEAR.'-07'),
        PREVIOUS_YEAR.'-08' => array( DOC_ID => 'planningDocId', SHEET_ID => PREVIOUS_YEAR.'-08'),
        PREVIOUS_YEAR.'-09' => array( DOC_ID => 'planningDocId', SHEET_ID => PREVIOUS_YEAR.'-09'),
        PREVIOUS_YEAR.'-10' => array( DOC_ID => 'planningDocId', SHEET_ID => PREVIOUS_YEAR.'-10'),
        PREVIOUS_YEAR.'-11' => array( DOC_ID => 'planningDocId', SHEET_ID => PREVIOUS_YEAR.'-11'),
        PREVIOUS_YEAR.'-12' => array( DOC_ID => 'planningDocId', SHEET_ID => PREVIOUS_YEAR.'-12'),

        CURRENT_YEAR.'-01' => array( DOC_ID => 'planningDocId', SHEET_ID => CURRENT_YEAR.'-01'),
        CURRENT_YEAR.'-02' => array( DOC_ID => 'planningDocId', SHEET_ID => CURRENT_YEAR.'-02'),
        CURRENT_YEAR.'-03' => array( DOC_ID => 'planningDocId', SHEET_ID => CURRENT_YEAR.'-03'),
        CURRENT_YEAR.'-04' => array( DOC_ID => 'planningDocId', SHEET_ID => CURRENT_YEAR.'-04'),
        CURRENT_YEAR.'-05' => array( DOC_ID => 'planningDocId', SHEET_ID => CURRENT_YEAR.'-05'),
        CURRENT_YEAR.'-06' => array( DOC_ID => 'planningDocId', SHEET_ID => CURRENT_YEAR.'-06'),
        CURRENT_YEAR.'-07' => array( DOC_ID => 'planningDocId', SHEET_ID => CURRENT_YEAR.'-07'),
        CURRENT_YEAR.'-08' => array( DOC_ID => 'planningDocId', SHEET_ID => CURRENT_YEAR.'-08'),
        CURRENT_YEAR.'-09' => array( DOC_ID => 'planningDocId', SHEET_ID => CURRENT_YEAR.'-09'),
        CURRENT_YEAR.'-10' => array( DOC_ID => 'planningDocId', SHEET_ID => CURRENT_YEAR.'-10'),
        CURRENT_YEAR.'-11' => array( DOC_ID => 'planningDocId', SHEET_ID => CURRENT_YEAR.'-11'),
        CURRENT_YEAR.'-12' => array( DOC_ID => 'planningDocId', SHEET_ID => CURRENT_YEAR.'-12'),

        NEXT_YEAR.'-01' => array( DOC_ID => 'planningDocId', SHEET_ID => NEXT_YEAR.'-01'),
        NEXT_YEAR.'-02' => array( DOC_ID => 'planningDocId', SHEET_ID => NEXT_YEAR.'-02'),
        NEXT_YEAR.'-03' => array( DOC_ID => 'planningDocId', SHEET_ID => NEXT_YEAR.'-03'),
        NEXT_YEAR.'-04' => array( DOC_ID => 'planningDocId', SHEET_ID => NEXT_YEAR.'-04'),
        NEXT_YEAR.'-05' => array( DOC_ID => 'planningDocId', SHEET_ID => NEXT_YEAR.'-05'),
        NEXT_YEAR.'-06' => array( DOC_ID => 'planningDocId', SHEET_ID => NEXT_YEAR.'-06'),
        NEXT_YEAR.'-07' => array( DOC_ID => 'planningDocId', SHEET_ID => NEXT_YEAR.'-07'),
        NEXT_YEAR.'-08' => array( DOC_ID => 'planningDocId', SHEET_ID => NEXT_YEAR.'-08'),
        NEXT_YEAR.'-09' => array( DOC_ID => 'planningDocId', SHEET_ID => NEXT_YEAR.'-09'),
        NEXT_YEAR.'-10' => array( DOC_ID => 'planningDocId', SHEET_ID => NEXT_YEAR.'-10'),
        NEXT_YEAR.'-11' => array( DOC_ID => 'planningDocId', SHEET_ID => NEXT_YEAR.'-11'),
        NEXT_YEAR.'-12' => array( DOC_ID => 'planningDocId', SHEET_ID => CURRENT_YEAR.'-12')
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

