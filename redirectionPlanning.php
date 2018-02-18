<?php
require_once('api/lib/Container.php');

$container = new Container();
$session = $container->getSession();
$validator = $container->getValidator();
$googlePlanning = $container->getGooglePlanning();
try {
    $session->checkLoggedIn();
    $session->checkHasPermission(P_EDIT_PLANNING);
    if ( !empty($_GET['monthId']) ) {
        $monthId = $_GET['monthId'];
        $validator->validateMonthId($monthId);
    } else {
        $monthId = date('Y-m');
    }
    $newLocation = $googlePlanning->getUrl($monthId);
} catch (Exception $e) {
    $newLocation = '/portal/#!/login//site:redirectionPlanning.php';
}
header('Location: '.$newLocation);
echo '<script type="text/javascript">document.location="'.$newLocation.'";</script>';
?>
Redirection en cours

