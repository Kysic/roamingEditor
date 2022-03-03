<?php

// require_once('/var/www/vinci/api/lib/Container.php');
require_once('/var/www/html/api/lib/Container.php');

try {

    $container = new Container();

    //$container->getReporting115()->extractFromMailFile('signalements.eml');
    $container->getReporting115()->extractFromXlsxFile('signalements.xlsx');
    // $container->getReporting115()->extractFromCsvFile('signalements.csv');

    echo $container->getReportsStorage()->getTodaysLast();

} catch (Exception $e) {
    echo $e;
}
