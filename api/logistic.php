<?php

require_once('lib/Container.php');

try {

    $container = new Container();
    $session = $container->getSession();
    $validator = $container->getValidator();
    $json = $container->getJson();
    $roamingsStorage = $container->getRoamingsStorage();

    $session->checkLoggedIn();

    $dataObject = json_decode(file_get_contents('php://input'));
    if (!$dataObject) {
        throw new BadRequestException('Unable to parse post body as json.');
    }
    $msg = @$dataObject->msg;
    if (!$msg) {
        throw new BadRequestException('No message found in post body.');
    }

    $container = new Container();
    $emails = array(PRESIDENT_REPORTING_EMAIL, SECRETARIAT_EMAIL, $session->getUser()->email);
    foreach ($emails as $email) {
        $container->getMail()->sendMail($email, '[AMICI] Probleme logistic au local', $msg);
    }

    $json->returnResult(array('status' => 'success'));
} catch (Exception $e) {
    $json->returnError($e);
}

