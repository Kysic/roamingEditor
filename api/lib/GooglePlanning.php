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

    public function getUrl($monthId) {
        $docId = GooglePlanningRef::getDocId($monthId);
        $sheetId = GooglePlanningRef::getSheetId($monthId);
        $planningUrl = GOOGLE_DOC_URL . $docId . GOOGLE_DOC_CMD_EDIT_GID . $sheetId;
        return $planningUrl;
    }

    public function getRoamingOfDate($roamingDate) {
        $roamingMonthData = $this->getRoamingsOfMonth($roamingDate);
        $dateId = date('Y-m-d', $roamingDate);
        if ( array_key_exists($dateId, $roamingMonthData) ) {
            return $roamingMonthData[$dateId];
        } else {
            return array();
        }
    }

    public function getInfos() {
        $infos = array();
        foreach($this->roamingMonthData as $monthData){
            $infos = array_merge($infos, $monthData['infos'] ?? []);
        }
        return array_unique($infos);
    }

    private $roamingMonthData = array();

    private function getRoamingsOfMonth($roamingDate) {
        $monthId = date('Y-m', $roamingDate);
        if ( !array_key_exists($monthId, $this->roamingMonthData) ) {
            $this->extractRoamingsOfMonth($monthId);
            //print_r($this->roamingMonthData);
        }
        return $this->roamingMonthData[$monthId];
    }

    private function getTeammateName($data, $teammateIndex) {
        return implode('-', array_map('ucwords', explode('-',
                    mb_strtolower(trim($data[TUTOR_INDEX + ($teammateIndex * DIFF_INDEX_TEAMMATE)]))
               )));
    }
    private function getTeammateGender($data, $teammateIndex) {
        return strtolower(trim($data[TUTOR_INDEX + ($teammateIndex * DIFF_INDEX_TEAMMATE) + 1]));
    }
    private function extractRoamingsOfMonth($monthId) {
        $docId = GooglePlanningRef::getDocId($monthId);
        $sheetId = GooglePlanningRef::getSheetId($monthId);
        $planningUrl = GOOGLE_DOC_URL . $docId . GOOGLE_DOC_CMD_CSV_GID . $sheetId;
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
                //echo '<!--'.$roamingYear.'-'.$roamingMonth.'-'.$roamingDay.'-->'."\n";
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
                    $roamingMonthData[$dateId] = array(
                        'tutor' => $tutor,
                        'teammates' => $teammates,
                        'status' => $this->getStatus($tutor, $teammates)
                    );
                    $roamingDay++;
                } else if (stripos($data[0], 'réunion') !== false
                            || stripos($data[0], 'reunion') !== false
                            || stripos($data[0], 'pratique') !== false) {
                    if (!array_key_exists('infos', $roamingMonthData)) {
                        $roamingMonthData['infos'] = array();
                    }
                    array_push($roamingMonthData['infos'], $data[0]);
                }
            }
            fclose($handle);
        }
        $this->roamingMonthData[$monthId] = $roamingMonthData;
    }

    private function containsCancel($str) {
        return stristr(@$str, 'annulée') || stristr(@$str, 'annulee');
    }

    private function getStatus($tutor, $teammates) {
        if ($teammates) {
            if ( $this->containsCancel($tutor) ) {
                return 'canceled';
            }
            foreach ($teammates as $teammate) {
                if ( $this->containsCancel($teammate) ) {
                    return 'canceled';
                }
            }
            if ($tutor == '' || ( $teammates[0] == '' && $teammates[1] == '' ) ) {
                return 'unsure';
            }
            if ($teammates[0] == '' || $teammates[1] == '') {
                return 'planned-uncomplete';
            }
            return 'planned-complete';
        } else {
            return 'unknown';
        }
    }

    /** action can be 'enrol' or 'cancel' */
    function enrol($roamingDate, $position, $user, $action) {
        $monthId = date('Y-m', $roamingDate);
        $docId = GooglePlanningRef::getDocId($monthId);
        $sheetId = GooglePlanningRef::getSheetId($monthId);
        $day = date('j', $roamingDate);
        $username = $user->username;
        $gender = $user->gender;
        $scriptUrl = GOOGLE_ENROL_SCRIPT .'?docId=' . urlencode($docId) . '&sheetId=' . urlencode($sheetId) .
                     '&day=' . $day . '&position=' . $position . '&user=' . urlencode($username) .
                     '&gender=' . $gender . '&action=' . $action;
        $opts = array('http' => array('method' => 'GET', 'ignore_errors' => '1'));
        $responseContent = file_get_contents($scriptUrl, false, stream_context_create($opts));
        return json_decode($responseContent);
    }

}

