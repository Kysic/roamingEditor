<?php


require_once('config.php');
require_once('roaming-google.php');


$now = strtotime('now');
$roamingDate = strtotime("-8 hour", $now);

if ($_GET['roamingDate'] != date('Y-m-d', $roamingDate)) {
    header('HTTP/1.0 403 Forbidden');
    die('Roaming planning access forbidden');
}

$monthId = date('Y-m', $roamingDate);
$docId = $DOC_REF[$monthId][DOC_ID];
$sheetId = $DOC_REF[$monthId][SHEET_ID];
$roamingMonthData = extractRoamingsFrom($docId, $sheetId);
$roamingData = @$roamingMonthData[date('Y-m-d', $roamingDate)];

$volunteers = array();
for ($i = 1; $i <= 3; $i++) {
    if (trim($roamingData[$i]['name']) != '') {
        array_push($volunteers, $roamingData[$i]['name']);
    }
}
$response = array('tutor' => trim($roamingData[0]['name']), 'volunteers' => $volunteers);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
echo json_encode($response);

?>
