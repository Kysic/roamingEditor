<?php

require_once('conf/google.php');

define('DAY_INDEX', 1);
define('MONTH_INDEX', 2);
define('YEAR_INDEX', 4);
define('TUTOR_INDEX', 2);
define('DIFF_INDEX_VOLUNTEER', 2);
define('COMMENT_INDEX', 8);
define('BREAD_INDEX', 10);

function getVolunteerName($data, $volunteerIndex) {
    return implode('-', array_map('ucwords', explode('-',
                strtolower(trim($data[TUTOR_INDEX + ($volunteerIndex * DIFF_INDEX_VOLUNTEER)]))
           )));
}
function getVolunteerGender($data, $volunteerIndex) {
    return strtolower(trim($data[TUTOR_INDEX + ($volunteerIndex * DIFF_INDEX_VOLUNTEER) + 1]));
}
function extractRoamingsOfMonth($roamingMonthDate) {
    global $DOC_REF;
    $monthId = date('Y-m', $roamingMonthDate);
    $docId = $DOC_REF[$monthId][DOC_ID];
    $sheetId = $DOC_REF[$monthId][SHEET_ID];
    $roamingData = array();
    $planningUrl = GOOGLE_DOC_URL . $docId . GOOGLE_DOC_CMD_CSV . $sheetId;
    if (($handle = fopen($planningUrl, 'r')) !== FALSE) {
        if (($data = fgetcsv($handle, 1000, ',')) === FALSE) {
            return $roamingData;
        }
        $roamingMonth = $data[MONTH_INDEX];
        $roamingYear = $data[YEAR_INDEX];
        $roamingDay = 1;
        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            // echo '<!--'.implode(';',$data).'-->'."\n";
            if (  ( stristr($data[0], 'réunion') !== false || stristr($data[0], 'reunion') !== false )
                  && stristr($data[0], 'mensuelle ') !== false
               ) {
                $roamingData['meeting'] = $data[0];
            }
            if (count($data) >= 2 && $data[DAY_INDEX] == $roamingDay
                            && checkDate($roamingMonth, $roamingDay, $roamingYear)) {
                $roamingDate = date('Y-m-d', mktime(0, 0, 0, $roamingMonth, $roamingDay, $roamingYear));
                $roamingData[$roamingDate] = array();
                for ($i = 0; $i < 4; $i ++) {
                    $roamingData[$roamingDate][$i] = array('name' => getVolunteerName($data, $i),
                                                         'gender' => getVolunteerGender($data, $i));
                }
                if (@$data[COMMENT_INDEX]) {
                    $roamingData[$roamingDate]['comment'] = $data[COMMENT_INDEX];
                }
                if (@$data[BREAD_INDEX]) {
                    $roamingData[$roamingDate]['bread'] = $data[BREAD_INDEX];
                }
                $roamingDay++;
            }
        }
        fclose($handle);
    }
    return $roamingData;
}

function getRoamingOfDate($roamingMonthData, $roamingDate) {
    return @$roamingMonthData[date('Y-m-d', $roamingDate)];
}

