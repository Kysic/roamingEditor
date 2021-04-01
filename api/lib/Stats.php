<?php

require_once(ROAMING_API_DIR.'/conf/google.php');

define('IDX_SRC_INTERVENTION', 4);
define('IDX_NB_ADULTS', 5);
define('IDX_NB_CHILDREN', 6);

class Stats {

    public function extractStatsFromRoamingsReports($roamingsDocs) {
        $roamingsStats = array();
        foreach ($roamingsDocs as $roamingDoc) {
            $roamingStats = $this->extractStatsFromRoamingReport($roamingDoc->docId);
            $roamingStats['date'] = $roamingDoc->roamingDate;
            array_push($roamingsStats, $roamingStats);
        }
        return $roamingsStats;
    }

    private function extractStatsFromRoamingReport($docId) {
        $reportCsvUrl = $this->docIdToCsvUrl($docId);
        $csv = array_map('str_getcsv', file($reportCsvUrl));
        $tmplVersion = $csv[4][3];
        if ($tmplVersion == "V3") {
            return $this->extractStatsFromRoamingReportV3($csv);
        } else {
            return $this->extractStatsFromRoamingReportV2($csv);
        }
    }

    private function extractStatsFromRoamingReportV2($csv) {
        $srcInterventions = $this->extractSrcIntervention($csv);
        return array(
            'date' => $csv[0][2],
            'nbVolunteer' => $this->getNbVolunteers($csv[2][2]),
            'nbIntervention' => $csv[5][2],
            'nbAdult' => $csv[7][2],
            'nbChild' => $csv[8][2],
            'nbEncounter' => $csv[6][2],
            'nbBlanket' => $csv[5][7],
            'nbTent' => $csv[6][7],
            'hygiene' => $csv[8][7],
            'src115' => @$srcInterventions['115'],
            'srcRoaming' => @$srcInterventions['Maraude']
        );
    }

    private function extractStatsFromRoamingReportV3($csv) {
        $srcInterventions = $this->extractSrcIntervention($csv);
        $statsCol1Idx = 2;
        $statsCol2Idx = 8;
        $statsCol3Idx = 14;
        return array(
            'date' => $csv[0][2],
            'nbVolunteer' => $this->getNbVolunteers($csv[2][2]),
            'nbIntervention' => $csv[5][$statsCol1Idx],
            'nbEncounter' => $csv[6][$statsCol1Idx],
            'nbAdult' => $csv[7][$statsCol1Idx],
            'nbChild' => $csv[8][$statsCol1Idx],
            'nbFood' => $csv[5][$statsCol2Idx],
            'nbBlanket' => $csv[6][$statsCol2Idx],
            'nbTent' => $csv[7][$statsCol2Idx],
            'hygiene' => $csv[8][$statsCol2Idx],
            'nbAlone' => $csv[5][$statsCol3Idx],
            'nbCouple' => $csv[6][$statsCol3Idx],
            'nbFamily' => $csv[7][$statsCol3Idx],
            'src115' => @$srcInterventions['115'],
            'srcRoaming' => @$srcInterventions['Maraude']
        );
    }

    private function docIdToCsvUrl($docId) {
        return GOOGLE_DOC_URL.$docId.GOOGLE_DOC_CMD_CSV;
    }

    private function extractSrcIntervention($csv) {
        $srcInterventions = array();
        $startLine = 15;
        for ($i = $startLine; $i < count($csv); $i++) {
            if (count($csv[$i]) > 6) {
                if ($csv[$i][IDX_NB_ADULTS] > 0 || $csv[$i][IDX_NB_CHILDREN] > 0) {
                    $srcIntervention = $csv[$i][IDX_SRC_INTERVENTION];
                    if (array_key_exists($srcIntervention, $srcInterventions)) {
                        $srcInterventions[$srcIntervention]++;
                    } else {
                        $srcInterventions[$srcIntervention] = 1;
                    }
                }
            }
        }
        return $srcInterventions;
    }

    private function getNbVolunteers($volunteers) {
        $str = preg_replace('/;|([\s,;]et[\s,;])/', ',', $volunteers);
        $str = preg_replace('/,[\s,]*/', ',', $str);
        $str = preg_replace('/,[^,]*maraude[^,]*,/i', ',', $str);
        return substr_count($str, ',') + 1;
    }

    public function csv_stats($roamingsStats, $humanReadable = false) {
        return $this->render_stats(
            $roamingsStats,
            function ($output, $stats) use ($humanReadable) {
                return $output.$this->row_renderer($stats, ';', $humanReadable ? 15 : 0)."\n";
            }
        );
    }

    public function html_stats($roamingsStats) {
        return '<table>'."\r\n".
            $this->render_stats(
                $roamingsStats,
                function ($output, $stats) {
                    return $output.'<tr><td>'.$this->row_renderer($stats, '</td><td>').'</td></tr>'."\r\n";
                },
                function ($output, $stats) {
                    return $output.'<tr><th>'.$this->row_renderer($stats, '</th><th>').'</th></tr>'."\r\n";
                }
            ).
            '</table>';
    }

    private function render_stats($roamingsStats, $contentRenderer, $headerRenderer = NULL) {
        if (is_null($headerRenderer)) {
            $headerRenderer = $contentRenderer;
        }
        $headers = array(
            'date' => 'Jour',
            'nbVolunteer' =>'Benevoles',
            'nbIntervention' => 'Interventions',
            'nbAdult' => 'Adultes',
            'nbChild' => 'Enfants',
            'nbEncounter' => 'Total personnes',
            'nbAlone' => 'Personnes seules',
            'nbCouple' => 'Couples',
            'nbFamily' => 'Familles',
            'nbFood' => 'Parts alimentaires',
            'nbBlanket' => 'Couvertures',
            'nbTent' => 'Tentes',
            'hygiene' => 'Hygiène',
            'src115' => 'Signalement 115',
            'srcRoaming' => 'Rencontre Maraude'
        );
        $output = call_user_func($headerRenderer, '', $headers);
        $output = array_reduce($roamingsStats, $contentRenderer, $output);
        $output = call_user_func($contentRenderer, $output, $this->getTotal($roamingsStats));
        return $output;
    }

    private function row_renderer($stats, $sep, $minWidth = 0) {
        return implode($sep,
                array_map(
                    function ($column) use ($stats, $minWidth) {
                        return str_pad(@$stats[$column], $minWidth, ' ', STR_PAD_BOTH);
                    },
                    array(
                        'date', 'nbVolunteer', 'nbIntervention', 'nbAdult', 'nbChild', 'nbEncounter',
                        'nbAlone', 'nbCouple', 'nbFamily', 'nbFood', 'nbBlanket', 'nbTent', 'hygiene',
                        'src115', 'srcRoaming'
                    )
                )
            );
    }

    private function getTotal($roamingsStats) {
        $total = array_reduce($roamingsStats, function ($a, $b) {
                $c = array();
                foreach (array_keys($a + $b) as $key) {
                    $c[$key] = $this->toInt(@$a[$key]) + $this->toInt(@$b[$key]);
                }
                return $c;
            }, array());
        $total['date'] = 'total';
        return $total;
    }

    private function toInt($val, $default = 0) {
        return is_numeric($val) ? intval($val) : $default;
    }
}

