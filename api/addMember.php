<?php

/*
echo '{
    "member": {
        "gender": "M",
        "firstname": "MyFirstname",
        "surname": "MySurname",
        "birthday": "1/01/1901",
        "email": "myfirst.mysur@mailer.com",
        "phoneNumber": "0601020304",
        "address": "12 chemin Perdu, 38000 GRENOBLE"
    },
    "message": "MyMessage"
}' | curl -v -d @- https://vinci/api/addMember.php
*/

require_once('lib/Container.php');
require_once(ROAMING_API_DIR.'/conf/google.php');

try {

    $container = new Container();
    $json = $container->getJson();

    $memberSubscription = json_decode(file_get_contents('php://input'));
    if (!$memberSubscription) {
        throw new BadRequestException('Unable to parse post body as json.');
    }
    $member = $memberSubscription->member;
    $scriptUrl = GOOGLE_ADD_MEMBER_SCRIPT .
                 '?gender=' . urlencode($member->gender) .
                 '&firstname=' . urlencode($member->firstname) .
                 '&surname=' . urlencode($member->surname) .
                 '&birthday=' . urlencode($member->birthday) .
                 '&email=' . urlencode($member->email) .
                 '&phoneNumber=' . urlencode($member->phoneNumber) .
                 '&address=' . urlencode($member->address) .
                 '&message=' . urlencode($memberSubscription->message);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $scriptUrl);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // follow redirects
    curl_setopt($ch, CURLOPT_AUTOREFERER, 1); // set referer on redirect
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return content on exec
    $responseContent = curl_exec($ch);
    curl_close($ch);

    if (!$responseContent) {
        throw new InternalException('Unable to join remote server');
    }
    $response = json_decode($responseContent);
    if (!$response) {
        throw new InternalException('Unable to understand remote server response');
    }
    $json->returnResult($response);

} catch (Exception $e) {
    $json->returnError($e);
}

