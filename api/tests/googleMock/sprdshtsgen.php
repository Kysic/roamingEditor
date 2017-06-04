<?php

$roaming = file_get_contents('php://input');

$docId = uniqid();
copy('roamingReports.pdf', '../tmp/roamingReports-'.$docId.'.pdf');
file_put_contents('../tmp/roamingReports-'.$docId.'.json', $roaming);

header("Content-Type: application/json");
echo json_encode(array(
    'docId' => $docId,
    'docUrl' => 'NOT_USED'
));

