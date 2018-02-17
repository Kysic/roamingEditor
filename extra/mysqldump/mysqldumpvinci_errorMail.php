<?php

require_once('/var/www/vinci/api/lib/Container.php');

$container = new Container();
$container->getMail()->sendMail(ADMIN_EMAIL,
    '[VINCI] Error detected while backing up the database',
    'An error has occured while backing up the VINCI database'
);

