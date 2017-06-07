<?php

function getSessionToken($browser) {
    $response = $browser->get(END_POINT.'/api/auth.php');
    return $response->sessionToken;
}
function getSessionUser($browser) {
    $response = $browser->get(END_POINT.'/api/auth.php');
    return $response->user;
}
function signinAndSetPassword($browser, $email, $password, $sessionToken = NULL) {
    if ( !$sessionToken ) $sessionToken = getSessionToken($browser);
    signin($browser, $email, $sessionToken);
    $mail = readMail($email);
    $mailInfo = extractMailTokenReceived($mail);
    $browser->get($mailInfo['setPasswordUrl']);
    setPasswordWithMailToken($browser, $mailInfo['userId'], $mailInfo['mailToken'], $password, $password, $sessionToken);
}
function resetPassword($browser, $email, $password, $sessionToken = NULL) {
    if ( !$sessionToken ) $sessionToken = getSessionToken($browser);
    askPasswordReset($browser, $email, $sessionToken);
    $mail = readMail($email);
    $mailInfo = extractMailTokenReceived($mail);
    $browser->get($mailInfo['setPasswordUrl']);
    setPasswordWithMailToken($browser, $mailInfo['userId'], $mailInfo['mailToken'], $password, $password, $sessionToken);
}
function signin($browser, $email, $sessionToken = NULL) {
    if ( !$sessionToken ) $sessionToken = getSessionToken($browser);
    $response = $browser->post(END_POINT.'/api/auth.php',
        array('action'=>'signin', 'email'=>$email, 'sessionToken'=>$sessionToken));
    assertSuccess($response, 'signin failed : '.print_r($response, true));
}
function askPasswordReset($browser, $email, $sessionToken = NULL) {
    if ( !$sessionToken ) $sessionToken = getSessionToken($browser);
    $response = $browser->post(END_POINT.'/api/auth.php',
        array('action'=>'resetPassword', 'email'=>$email, 'sessionToken'=>$sessionToken));
    assertSuccess($response, 'resetPassword failed : '.print_r($response, true));
}
function setPasswordWithMailToken($browser, $userId, $mailToken, $password, $passwordConfirm, $sessionToken = NULL) {
    if ( !$sessionToken ) $sessionToken = getSessionToken($browser);
    $response = $browser->post(END_POINT.'/api/auth.php',
        array('action'=>'setPassword', 'userId'=>$userId, 'mailToken'=>$mailToken, 'sessionToken'=>$sessionToken,
              'password'=>$password, 'passwordConfirm'=>$passwordConfirm));
    assertSuccess($response, 'setPasswordWithMailToken failed : '.print_r($response, true));
}
function login($browser, $email, $password, $stayLogged = false, $sessionToken = NULL) {
    if ( !$sessionToken ) $sessionToken = getSessionToken($browser);
    $response = $browser->post(END_POINT.'/api/auth.php',
        array('action'=>'login', 'email'=>$email, 'password'=>$password,
              'stayLogged'=>$stayLogged, 'sessionToken'=>$sessionToken));
    assertSuccess($response, 'login failed : '.print_r($response, true));
}
function logout($browser, $sessionToken = NULL) {
    if ( !$sessionToken ) $sessionToken = getSessionToken($browser);
    $response = $browser->post(END_POINT.'/api/auth.php',
        array('action'=>'logout', 'sessionToken'=>$sessionToken));
    assertSuccess($response, 'logout failed : '.print_r($response, true));
}
function setPasswordWhenLogged($browser, $password, $passwordConfirm, $sessionToken = NULL) {
    if ( !$sessionToken ) $sessionToken = getSessionToken($browser);
    $response = $browser->post(END_POINT.'/api/auth.php',
        array('action'=>'setPassword', 'sessionToken'=>$sessionToken,
              'password'=>$password, 'passwordConfirm'=>$passwordConfirm));
    assertSuccess($response, 'setPasswordWhenLogged failed : '.print_r($response, true));
}
function readMail($email) {
    return json_decode(file_get_contents('tmp/mail-'.$email));
}
function extractMailTokenReceived($mail) {
    if (preg_match('/http:\/\/\S*\?userId=([0-9]*)&mailToken=(\S*)/', $mail->body, $matches)) {
        return array('setPasswordUrl'=>$matches[0], 'userId'=>$matches[1], 'mailToken'=>urldecode($matches[2]));
    } else {
        throw AssertException('Unable to extract mailToken from '.print_r($mail, true));
    }
}
