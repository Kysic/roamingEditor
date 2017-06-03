<?php

require_once('conf/google.php');

define('DAY_INDEX', 1);
define('MONTH_INDEX', 2);
define('YEAR_INDEX', 4);
define('TUTOR_INDEX', 2);
define('DIFF_INDEX_VOLUNTEER', 2);
define('COMMENT_INDEX', 8);
define('BREAD_INDEX', 10);

class GooglePlanning {

    private function getVolunteerName($data, $volunteerIndex) {
        return implode('-', array_map('ucwords', explode('-',
                    strtolower(trim($data[TUTOR_INDEX + ($volunteerIndex * DIFF_INDEX_VOLUNTEER)]))
               )));
    }
    private function getVolunteerGender($data, $volunteerIndex) {
        return strtolower(trim($data[TUTOR_INDEX + ($volunteerIndex * DIFF_INDEX_VOLUNTEER) + 1]));
    }
    public function extractRoamingsOfMonth($roamingMonthDate) {
        $monthId = date('Y-m', $roamingMonthDate);
        $docId = GooglePlanningRef::getDocId($monthId);
        $sheetId = GooglePlanningRef::getSheetId($monthId);
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
                        $roamingData[$roamingDate][$i] = array('name' => $this->getVolunteerName($data, $i),
                                                             'gender' => $this->getVolunteerGender($data, $i));
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
    public function getRoamingOfDate($roamingMonthData, $roamingDate) {
        return @$roamingMonthData[date('Y-m-d', $roamingDate)];
    }

}

