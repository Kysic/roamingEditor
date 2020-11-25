#!/usr/bin/php


<?php

require_once('/var/www/vinci/api/lib/Container.php');

try {

    $container = new Container();
    $roamingsStorage = $container->getRoamingsStorage();
    $spreadsheetsGenerator = $container->getSpreadsheetsGenerator();

    $yesterday = date('Y-m-d', strtotime('yesterday'));
    echo 'Generating report of '.$yesterday."\n";
    $roamingId = $roamingsStorage->getRoamingId($yesterday);
    if ($roamingId <= 0) {
        echo 'No data found for '.$yesterday."\n";
    } else {
        $docId = $spreadsheetsGenerator->getOrCreateDocId($roamingId, 1);
        echo 'Report '.$docId.' for roaming '.$roamingId.' generated'."\n";
    }

    // Clean old roamings report
    $roamingsStorage->cleanPreviousRoamingsVersion();
    $roamingsStorage->deleteOldRoamings();
    $container->getReportFiles()->deleteOldReports();

} catch (Exception $e) {
    $container->getMail()->sendMail(ADMIN_EMAIL, '[VINCI] Error detected while creating the roamingReport',
        'An error has occured while generating today\'s report: '."\n".print_r($e, true)
    );
    throw $e;
}

