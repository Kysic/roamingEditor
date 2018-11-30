<?php

require_once('lib/Container.php');

try {

    $container = new Container();
    $json = $container->getJson();
    $mail = $container->getMail();

    $mail->sendRegisterToken("web-coza2@mail-tester.com", "Jean", "Dupont", 12, "somethingToTest");

    $json->returnResult(array(
        'status' => 'success'
    ));

} catch (Exception $e) {
    $json->returnError($e);
}
