<?php
require_once('api/lib/Container.php');

$container = new Container();
$session = $container->getSession();
$validator = $container->getValidator();
$googlePlanning = $container->getGooglePlanning();
try {
    if (!$session->isLoggedIn()) {
        $newLocation = '/portal/#!/login//site:redirectionPlanning.php';
    } else {
        $session->checkHasPermission(P_EDIT_PLANNING);
        if ( !empty($_GET['monthId']) ) {
            $monthId = $_GET['monthId'];
            $validator->validateMonthId($monthId);
        } else {
            $monthId = date('Y-m');
        }
        $newLocation = $googlePlanning->getUrl($monthId);
    }
    header('Location: '.$newLocation);
    echo '<script type="text/javascript">document.location="'.$newLocation.'";</script>';
    echo 'Redirection en cours';
} catch (Exception $e) {
    echo $e->getMessage();
}

