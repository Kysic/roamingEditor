<?php

require_once('lib/auth.php');
require_once('lib/json.php');
require_once('db/roamingSql.php');

try {

    checkLoggedIn();
    checkHasPermission(P_SEE_LAST_REPORT);

    $roamings = getRoamings('2000-01-01', '2020-01-01');

    returnResult(array(
        'status' => 'success',
        'roamings' => $roamings
    ));

} catch (Exception $e) {
    returnError($e);
}

?>
