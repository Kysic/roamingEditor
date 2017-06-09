<?php

function getUsers($browser) {
    return $browser->get(END_POINT.'/api/getUsers.php');
}

function setUserRole($browser, $userId, $role, $sessionToken = NULL) {
    if ( !$sessionToken ) $sessionToken = getSessionToken($browser);
    $response = $browser->post(END_POINT.'/api/setUserRole.php',
    array('userId'=>$userId, 'role'=>$role, 'sessionToken'=>$sessionToken));
    assertSuccess($response, 'setUserRole failed : '.print_r($response, true));
}

