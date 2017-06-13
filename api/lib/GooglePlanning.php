<?php

require_once(ROAMING_API_DIR.'/conf/google.php');

define('DAY_INDEX', 1);
define('MONTH_INDEX', 2);
define('YEAR_INDEX', 4);
define('TUTOR_INDEX', 2);
define('DIFF_INDEX_TEAMMATE', 2);
define('COMMENT_INDEX', 8);
define('BREAD_INDEX', 10);

class GooglePlanning {

    public function getRoamingOfDate($roamingDate) {
        $dateId = date('Y-m-d', $roamingDate);
        $monthId = date('Y-m', $roamingDate);
        if ( !array_key_exists($monthId, $this->roamingMonthData) ) {
            $this->extractRoamingsOfMonth($monthId);
        }
        $roamingMonthData = $this->roamingMonthData[$monthId];
        if ( array_key_exists($dateId, $roamingMonthData) ) {
            return $roamingMonthData[$dateId];
        } else {
            return array();
        }
    }

    private $roamingMonthData = array();

    private function getTeammateName($data, $teammateIndex) {
        return implode('-', array_map('ucwords', explode('-',
                    strtolower(trim($data[TUTOR_INDEX + ($teammateIndex * DIFF_INDEX_TEAMMATE)]))
               )));
    }
    private function getTeammateGender($data, $teammateIndex) {
        return strtolower(trim($data[TUTOR_INDEX + ($teammateIndex * DIFF_INDEX_TEAMMATE) + 1]));
    }
    private function extractRoamingsOfMonth($monthId) {
        $docId = GooglePlanningRef::getDocId($monthId);
        $sheetId = GooglePlanningRef::getSheetId($monthId);
        $planningUrl = GOOGLE_DOC_URL . $docId . GOOGLE_DOC_CMD_CSV . $sheetId;
        $roamingMonthData = array();
        if (($handle = fopen($planningUrl, 'r')) !== FALSE) {
            if (($data = fgetcsv($handle, 1000, ',')) === FALSE) {
                return $roamingData;
            }
            $roamingMonth = $data[MONTH_INDEX];
            $roamingYear = $data[YEAR_INDEX];
            $roamingDay = 1;
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                //echo '<!--'.implode(';',$data).'-->'."\n";
                /*if (  ( stristr($data[0], 'réunion') !== false || stristr($data[0], 'reunion') !== false )
                      && stristr($data[0], 'mensuelle ') !== false
                   ) {
                    $roamingData['meeting'] = $data[0];
                }*/
                if (count($data) >= 2 && $data[DAY_INDEX] == $roamingDay
                                && checkDate($roamingMonth, $roamingDay, $roamingYear)) {
                    $dateId = date('Y-m-d', mktime(0, 0, 0, $roamingMonth, $roamingDay, $roamingYear));
                    $tutor = $this->getTeammateName($data, 0);
                    $teammates = array();
                    for ($i = 1; $i < 4; $i ++) {
                        array_push($teammates, $this->getTeammateName($data, $i));
                    }
                    /*
                    if (@$data[COMMENT_INDEX]) {
                        $roamingData[$roamingDate]['comment'] = $data[COMMENT_INDEX];
                    }
                    if (@$data[BREAD_INDEX]) {
                        $roamingData[$roamingDate]['bread'] = $data[BREAD_INDEX];
                    }*/
                    $team = array('tutor' => $tutor, 'teammates' => $teammates);
                    $roamingMonthData[$dateId] = $team;
                    $roamingDay++;
                }
            }
            fclose($handle);
        }
        $this->roamingMonthData[$monthId] = $roamingMonthData;
    }

}

