<?php

require_once('lib/Container.php');

try {

    $container = new Container();
    $validator = $container->getValidator();
    $reportFiles = $container->getReportFiles();
    $roamingsStorage = $container->getRoamingsStorage();
    $session = $container->getSession();
    $json = $container->getJson();

    if ( hash('sha256', @$_POST['apiKey'], false) != '4eaa6a402e3c1b40917170a9bd7f64aad744292bc91ac4264c1958389fd79d57' ) {
        $session->checkLoggedIn();
        $session->checkHasPermission(P_UPLOAD_REPORT);
        $session->checkToken(@$_POST['sessionToken']);
    }

    if (!@$_FILES['report']['tmp_name']) {
        throw new BadRequestException('File not received by server.');
    }
    if (!endsWith($_FILES['report']['name'], '.pdf')) {
        throw new BadRequestException('Only pdf files are accepted.');
    }

    $roamingDate = @$_GET['roamingDate'];
    if ( $roamingDate ) {
        $validator->validateRoamingDate($roamingDate);
    } else {
        $roamingDate = roamingDateFromReportName($_FILES['report']['name']);
    }

    if ($reportFiles->reportFileExists($roamingDate) || $roamingsStorage->reportExistsFor($roamingDate)) {
        throw new BadRequestException('There is already a report for the roaming '.$roamingDate.'.');
    }

    $reportFileUrl = $reportFiles->getFileUrl($roamingDate);
    $reportMonthDir = dirname($reportFileUrl);
    if ( !is_dir($reportMonthDir) ) {
        mkdir($reportMonthDir, 0775, true);
    }
    if ( !@move_uploaded_file($_FILES['report']['tmp_name'], $reportFileUrl) ) {
        throw new Exception('Internal error, unable to move the uploaded file from '.$_FILES['report']['tmp_name'].
                            ' to '.$reportFileUrl);
    }

    $json->returnResult(array(
        'status' => 'success'
    ));

} catch (Exception $e) {
    $json->returnError($e);
}

function roamingDateFromReportName($reportName) {
    $reportDate = false;
    // Regarde si le nom de fichier correspond à l'un des 3 derniers jours
    for ($nbDay = -3 ; $nbDay <= -1; $nbDay++) {
        $currentDate = strtotime($nbDay.' day');
        if ( doesMatchDate($reportName, $currentDate) ) {
            $reportDate = $currentDate;
            break;
        }
    }
    // Si le nom du fichier contient "maraude" ou "CR ", on considere que c'est le compte rendu de la veille
    // On exclut les comptes rendus du comité de veille qui ne sont donc pas des CR de maraudes
    if ( !$reportDate && ( strripos($reportName, 'maraude') !== FALSE
                       || ( strripos($reportName, 'veille') === FALSE &&
                             ( strrpos($reportName, 'CR') !== FALSE || strripos($reportName, 'rendu') !== FALSE )
                          )
                     )
       ) {
        $reportDate = strtotime('yesterday');
    }
    if ( !$reportDate ) {
        throw new BadRequestException('The uploaded file doesn\'t seem to be a roaming report.');
    }
    return date('Y-m-d', $reportDate);
}

function endsWith($haystack, $needle) {
    $length = strlen($needle);
    return $length == 0 || substr($haystack, -$length) === $needle;
}

function doesMatchDate($filename, $date) {
    return preg_match('/^(V5)?[^0-9]*0?'.date('j', $date).'[^0-9]*(0?'.date('n', $date).')?'.
                      '[^0-9]*((20)?'.date('y', $date).')?[^0-9]*$/', $filename);
}

