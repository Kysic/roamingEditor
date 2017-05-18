<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// TODO replace this sleep by a real implementation !
sleep(3);

$response = array();
echo json_encode($response);
?>
