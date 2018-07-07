<?php

require_once('lib/Container.php');

try {

    $container = new Container();
    $session = $container->getSession();
    $validator = $container->getValidator();
    $json = $container->getJson();
    $roamingsStorage = $container->getRoamingsStorage();
    $spreadsheetsGenerator = $container->getSpreadsheetsGenerator();
    $reportFiles = $container->getReportFiles();

    $session->closeWrite();
    $session->checkLoggedIn();
    $session->checkHasPermission(P_SEE_LAST_REPORT);

    $roamingId = @$_GET['roamingId'];
    if ( !$roamingId ) {
        throw new BadRequestException('Identifiant de maraude (roamingId) attendu.');
    }

    $roamingDate = $roamingsStorage->getDate($roamingId);
    $validator->validateRoamingDate($roamingDate);
    $docId = $spreadsheetsGenerator->getOrCreateDocId($roamingId, $session->getUser()->userId);
    $readUrl = $spreadsheetsGenerator->docIdToReadUrl($docId);

    echo retrieveHtmlContent($readUrl);

} catch (Exception $e) {
    $json->returnError($e);
}

function retrieveHtmlContent($readUrl) {
    $headers = array(
      'http'=>array(
        'method'=>"GET",
        'header'=>"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n".
                  "Accept-language: fr\r\n".
                  "User-agent: Mozilla/5.0 (X11; Linux x86_64; rv:57.0) Gecko/20100101 Firefox/57.0\r\n"
      )
    );
    $htmlContent = file_get_contents($readUrl, false, stream_context_create($headers));
    $htmlContent = preg_replace(
        "-<link href='/static/spreadsheets2/client/css/.*.css' type='text/css' rel='stylesheet'>-",
        "<style type='text/css'>
            #top-bar, .row-header, .column-headers-background, .row-header-wrapper { display: none; visibility: hidden }
        </style>",
        $htmlContent);
    $htmlContent = preg_replace('/minimum-scale=[0-9\.]*,maximum-scale=[0-9\.]*,/',
                            'minimum-scale="0.1",maximum-scale="1.5",', $htmlContent);
    $htmlContent = preg_replace('/#sheets-viewport { overflow: auto; }/', '', $htmlContent);
    $htmlContent = preg_replace('/resize\(\);/', '', $htmlContent);
    $htmlContent = preg_replace('/window.onresize = resize;/', '', $htmlContent);
    return $htmlContent;
}

