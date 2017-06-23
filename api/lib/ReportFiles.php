<?php

define('CR_DIR', ROAMING_API_DIR.'/../cr/');
define('CR_EXT', '.pdf');

class ReportFiles {

    public function getFileUrl($roamingDate) {
        return CR_DIR.str_replace('-', '/', $roamingDate).CR_EXT;
    }

    public function reportFileExists($roamingDate) {
        return file_exists($this->getFileUrl($roamingDate));
    }

    public function listReports($fromDate, $toDate) {
        $crList = array();
        $periodInterval = new DateInterval('P1D');
        $toDate->add($periodInterval);
        $period = new DatePeriod( $fromDate, $periodInterval, $toDate );
        foreach($period as $date) {
            $d = $date->format('Y-m-d');
            if ($this->reportFileExists($d)) {
                array_push($crList, $d);
            }
        }
        return $crList;
    }

    public function rmReportFile($roamingDate) {
        if ($this->reportFileExists($roamingDate)) {
            if (!@unlink($this->getFileUrl($roamingDate))) {
                throw new Exception('Error while removing cr file for '.$roamingDate.'.');
            }
        }
    }

}

