<?php

date_default_timezone_set('Europe/Paris');

$docId = @$_GET['docId'];
$type = @$_GET['type'];
$sheetId = @$_GET['sheetId'];
if ($docId == 'planningDocId') {
    list($year, $month) = explode('-', $sheetId);
    include('planning-csv.php');
} else if ($docId == 'contactDocId') {
    include('contact-csv.php');
} else if ($docId) {
    if ($type == 'edit') {
        header("Content-Type: application/json");
        include('../tmp/roamingReports-'.$docId.'.json');
    } else {
        include('../tmp/roamingReports-'.$docId.'.pdf');
    }
}

