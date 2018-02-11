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

    public function deleteOldReports() {
        $limitDateTime = new DateTime();
        $limitDateTime->sub(new DateInterval('P125D'));
        $this->deleteCRFolder(CR_DIR.$limitDateTime->format('Y/m'));
        // Delete year directory when limit month is december
        if ( $limitDateTime->format('m') == "12" )  {
            deleteCRFolder(CR_DIR.$limitDateTime->format('Y'));
        }
    }

    private function deleteCRFolder($dir) {
        if (is_dir($dir)) {
            $files = array_diff(scandir($dir), array('.','..'));
            foreach ($files as $file) {
                if ( substr($file, -strlen(CR_EXT)) === CR_EXT ) {
                    unlink($dir.'/'.$file);
                }
            }
            return rmdir($dir);
        }
    }

}

