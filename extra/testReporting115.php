<?php

require_once('../api/lib/Container.php');

try {

    $container = new Container();

    $container->getReporting115()->extractFromMailFile('/var/www/html/extra/signalements.eml');

    echo $container->getReportsStorage()->getTodaysLast();

} catch (Exception $e) {
    echo $e;
}
