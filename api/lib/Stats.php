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
        return '<table>'.
            $this->render_stats(
                $roamingsStats,
                function ($output, $stats) {
                    return $output.'<tr><td>'.$this->row_renderer($stats, '</td><td>').'</td></tr>';
                },
                function ($output, $stats) {
                    return $output.'<tr><th>'.$this->row_renderer($stats, '</th><th>').'</th></tr>';
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
                        return str_pad($stats[$column], $minWidth, ' ', STR_PAD_BOTH);
                    },
                    array(
                        'date', 'nbVolunteer', 'nbIntervention', 'nbAdult', 'nbChild', 'nbEncounter',
                        'nbBlanket', 'nbTent', 'hygiene', 'src115', 'srcRoaming'
                    )
                )
            );
    }

    private function getTotal($roamingsStats) {
        $total = array_reduce($roamingsStats, function ($a, $b) {
                $c = array();
                foreach (array_keys($a + $b) as $key) {
                    $c[$key] = @$a[$key] + @$b[$key];
                }
                return $c;
            }, array());
        $total['date'] = 'total';
        return $total;
    }
}

