<?php

date_default_timezone_set('Europe/Paris');

$docId = @$_GET['docId'];
$type = @$_GET['type'];
$sheetId = @$_GET['sheetId'];
if ($docId == 'planningDocId') {
    list($year, $month) = explode('-', $sheetId);
    header('Content-type: text/csv');
    include('planning-csv.php');
} else if ($docId == 'contactDocId') {
    header('Content-type: text/csv');
    include('contact-csv.php');
} else if ($docId) {
    if ($type == 'csv') {
        header('Content-type: text/csv');
        include('roaming-csv.php');
    } else if ($type == 'edit') {
        header('Content-Type: application/json');
        include('../tmp/roamingReports-'.$docId.'.json');
    } else if ($type == 'html') {
        include('../tmp/roamingReports-'.$docId.'.html');
    } else {
        include('../tmp/roamingReports-'.$docId.'.pdf');
    }
}

