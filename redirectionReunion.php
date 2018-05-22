<?php
require_once('api/lib/Container.php');

$container = new Container();
$session = $container->getSession();
$validator = $container->getValidator();
try {
    if (!$session->isLoggedIn()) {
        $newLocation = '/portal/#!/login//site:'.basename(__FILE__);
    } else if ($session->hasPermission(P_EDIT_MEETING)) {
        $newLocation = TEAMUP_URL.'/'.TEAMUP_EDIT_MEETING_ID.'?'.$_SERVER['QUERY_STRING'];
    } else {
        $session->checkHasPermission(P_SEE_MEETING);
        $newLocation = TEAMUP_URL.'/'.TEAMUP_SEE_MEETING_ID.'?'.$_SERVER['QUERY_STRING'];
    }
    header('Location: '.$newLocation);
    echo '<script type="text/javascript">document.location="'.$newLocation.'";</script>';
    echo 'Redirection en cours';
} catch (Exception $e) {
    echo $e->getMessage();
}

