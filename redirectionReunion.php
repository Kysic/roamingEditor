<?php
require_once('api/lib/Container.php');

$container = new Container();
$session = $container->getSession();
$validator = $container->getValidator();
try {
    if (!$session->isLoggedIn()) {
        $newLocation = '/portal/#!/login//site:'.basename(__FILE__);
    } else {
        $session->checkHasPermission(P_SEE_MEETING);
        $newLocation = CALENDAR_PROVIDER_URL.CALENDAR_ID."&mode=AGENDA";
    }
    header('Location: '.$newLocation);
    echo '<script type="text/javascript">document.location="'.$newLocation.'";</script>';
    echo 'Redirection en cours';
} catch (Exception $e) {
    echo $e->getMessage();
}

