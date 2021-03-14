<?php

require_once('/var/www/vinci/api/lib/Container.php');

try {

    $container = new Container();
    $json = $container->getJson();
    $mail = $container->getMail();

    $mail->sendMail("web-coza2@mail-tester.com", "Subject", "body");

    $json->returnResult(array(
        'status' => 'success'
    ));

} catch (Exception $e) {
    $json->returnError($e);
}
