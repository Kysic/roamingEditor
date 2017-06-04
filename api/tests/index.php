<!DOCTYPE html>
<html lang="en">
 <head>
  <meta charset="utf-8">
  <title>RoamingEditor IT tests</title>
 </head>
 <body>
  <h2>RoamingEditor IT tests</h2>
  <pre>
<?php

define('END_POINT', 'http://localhost/roamingEditor');
define('TABLETTE_1_AUTOLOGIN_KEY', '8U8MPr6/ZNo4rIQHU7gvezB7lkU6aYI8LXkHH9Le7ZF2Xf8otJgmHgTiVnJnHr12');

require_once('lib/Sql.php');
require_once('lib/Browser.php');
require_once('lib/auth.php');
require_once('lib/roamings.php');

date_default_timezone_set('Europe/Paris');

cleanTmp();

printTestCase('DB init');
$sql = new Sql();
$sql->reinitItDb();

printTestCase('Signin as member should succeed');
$browser = new Browser();
assertIsVisitor(getSessionUser($browser));
signinAndSetPassword($browser, 'berni@gmail.com', 'berni-password');
assertIsBernard(getSessionUser($browser));

printTestCase('Signin with address not in contacts should be forbidden');
$browser = new Browser();
try {
    signin($browser, 'someone@gmail.com');
    throw new AssertException('signin should have raised an HttpStatusException');
} catch (Exception $e) {
    assertEquals($e->statusCode, 403);
    assertEquals($e->content->status, 'error');
    assertEquals($e->content->errorCode, 403);
    assertEquals($e->content->errorMsg, "Cette adresse mail n'est pas repertoriée dans la liste des contacts du VINCI.");
}
assertIsVisitor(getSessionUser($browser));

printTestCase('Login as member should succeed');
$browser = new Browser();
login($browser, 'berni@gmail.com', 'berni-password');
assertEquals($browser->cookies['vcrPersistentLogin'], '');
assertIsBernard(getSessionUser($browser));

printTestCase('Change password as member should succeed');
setPasswordWhenLogged($browser, 'berni-password-2', 'berni-password-2');
assertIsBernard(getSessionUser($browser));

printTestCase('Logout as member should succeed');
logout($browser);
assertIsVisitor(getSessionUser($browser));

printTestCase('Login as member on three browsers with new password should succeed');
$browser1 = new Browser();
login($browser1, 'berni@gmail.com', 'berni-password-2', true);
$autologin1 = $browser1->cookies['vcrPersistentLogin'];
$browser2 = new Browser();
login($browser2, 'berni@gmail.com', 'berni-password-2', false);
$autologin2 = $browser2->cookies['vcrPersistentLogin'];
$browser3 = new Browser();
login($browser3, 'berni@gmail.com', 'berni-password-2', true);
$autologin3 = $browser3->cookies['vcrPersistentLogin'];
assertIsBernard(getSessionUser($browser1));
assertIsBernard(getSessionUser($browser2));
assertIsBernard(getSessionUser($browser3));
assertNonEquals($autologin1, '');
assertEquals($autologin2, '');
assertNonEquals($autologin3, '');
assertNonEquals($autologin1, $autologin3);

printTestCase('Autologin as appli should succeed');
$appliBrowser = new Browser();
$appliBrowser->cookies['vcrPersistentLogin'] = TABLETTE_1_AUTOLOGIN_KEY;
assertIsTablette1(getSessionUser($appliBrowser));

printTestCase('Autologin as member should succeed');
$browser1 = new Browser();
$browser1->cookies['vcrPersistentLogin'] = $autologin1;
$browser2 = new Browser();
$browser2->cookies['vcrPersistentLogin'] = $autologin2;
$browser3 = new Browser();
$browser3->cookies['vcrPersistentLogin'] = $autologin3;
assertIsBernard(getSessionUser($browser1));
assertIsVisitor(getSessionUser($browser2));
assertIsBernard(getSessionUser($browser3));

printTestCase('Logout as appli should be forbidden');
try {
    logout($appliBrowser);
    throw new AssertException('logout should have raised an HttpStatusException');
} catch (Exception $e) {
    assertEquals($e->statusCode, 403);
    assertEquals($e->content->status, 'error');
    assertEquals($e->content->errorCode, 403);
    assertEquals($e->content->errorMsg, "Vous n'êtes pas autorisé à vous déconnecter du site.");
}
assertIsTablette1(getSessionUser($appliBrowser));

printTestCase('Logout as member should unset autologin');
assertIsBernard(getSessionUser($browser1));
logout($browser1);
assertIsVisitor(getSessionUser($browser1));
assertEquals($browser1->cookies['vcrPersistentLogin'], '');

printTestCase('Autologin should not succeed after logout');
$browser1 = new Browser();
$browser1->cookies['vcrPersistentLogin'] = $autologin1;
assertIsVisitor(getSessionUser($browser1));
assertEquals($browser1->cookies['vcrPersistentLogin'], '');
$browser3 = new Browser();
$browser3->cookies['vcrPersistentLogin'] = $autologin3;
assertIsVisitor(getSessionUser($browser3));
assertEquals($browser3->cookies['vcrPersistentLogin'], '');

printTestCase('Reset password as member should succeed');
$browser = new Browser();
resetPassword($browser, 'berni@gmail.com', 'berni-password');
assertIsBernard(getSessionUser($browser));

printTestCase('Login with previous password should failed');
$browser = new Browser();
try {
    login($browser, 'berni@gmail.com', 'berni-password-2');
    throw new AssertException('login should have raised an HttpStatusException');
} catch (Exception $e) {
    assertEquals($e->statusCode, 400);
    assertEquals($e->content->status, 'error');
    assertEquals($e->content->errorCode, 400);
    assertEquals($e->content->errorMsg, 'Identifiants invalides.');
}
assertIsVisitor(getSessionUser($browser));

printTestCase('Login as member with new password should succeed');
login($browser, 'berni@gmail.com', 'berni-password');
assertIsBernard(getSessionUser($browser));

printTestCase('GetPlanning as appli should succeed');
$twentiethDayOfLastMonth = date('Y-m-d', strtotime('+19 day', strtotime('first day of last month')));
$result = getplanning($appliBrowser, $twentiethDayOfLastMonth);
assertEquals($result, (object) array('tutor'=>'Nicole P','volunteers'=>array('Olivier C', 'Sonia Jb', 'Aude P')));

printTestCase('SaveRoaming as appli should succeed');
$roaming = generateRoamingReport($twentiethDayOfLastMonth, 5);
$result = saveRoaming($appliBrowser, $roaming);

printTestCase('GetRoamings as appli should succeed');
$result = getRoamings($appliBrowser);
assertEquals($result->status, 'success');
$roamings = $result->roamings;
assertEquals($roamings->{'1'}->date, $twentiethDayOfLastMonth);
assertEquals($roamings->{'1'}->version, 5);

printTestCase('GetDocUrl as member should generate docId and succeed');
$result = getDocUrl($browser, 1);
assertEquals($result->status, 'success');
$docId = $result->docId;
$editUrl = $result->editUrl;
$roaming = $browser->get($editUrl);
assertEquals($roaming->date, $twentiethDayOfLastMonth);
assertEquals($roaming->version, 5);

printTestCase('Second GetDocUrl as member should return the same docId');
$result = getDocUrl($browser, 1);
assertEquals($result->status, 'success');
assertEquals($result->docId, $docId);
assertEquals($result->editUrl, $editUrl);





class AssertException extends Exception { }

function assertNonEquals($actual, $expected, $errorMsg = NULL) {
    if ($expected == $actual) {
        if ($errorMsg) {
            throw new AssertException($errorMsg);
        } else {
            throw new AssertException('Expected "'.print_r($actual, true).'" to be "'.print_r($expected, true).'"');
        }
    }
}

function assertEquals($actual, $expected, $errorMsg = NULL) {
    if ($expected != $actual) {
        if ($errorMsg) {
            throw new AssertException($errorMsg);
        } else {
            throw new AssertException('Expected "'.print_r($actual, true).'" to be "'.print_r($expected, true).'"');
        }
    }
}

function assertSuccess($response, $errorMsg = NULL) {
    assertEquals($response->status, 'success', $errorMsg);
}

function printTestCase($testCase) {
    echo "==== ".$testCase." ====\r\n";
}

function cleanTmp() {
    $files = glob('tmp');
    foreach ($files as $file) {
        if(is_file($file)) {
            unlink($file);
        }
    }
}

function assertIsVisitor($user) {
    assertEquals($user->role, 'visitor');
    assertEquals($user->email, '');
    assertEquals($user->firstname, '');
    assertEquals($user->lastname, '');
    assertEquals($user->permissions, array(
                                        'P_SIGN_IN',
                                        'P_LOG_IN',
                                        'P_RESET_PASSWORD'
                                     ));
}

function assertIsBernard($user) {
    assertEquals($user->role, 'member');
    assertEquals($user->email, 'berni@gmail.com');
    assertEquals($user->firstname, 'Bernard');
    assertEquals($user->lastname, 'DUPONT');
    assertEquals($user->permissions, array(
                                        'P_SEE_LAST_REPORT',
                                        'P_EDIT_REPORT',
                                        'P_SEE_PLANNING',
                                        'P_SEE_NAMES',
                                        'P_LOG_OUT',
                                        'P_CHANGE_PASSWORD'
                                     ));
}

function assertIsTablette1($user) {
    assertEquals($user->role, 'appli');
    assertEquals($user->email, 'tablette1@samu-social-grenoble.fr');
    assertEquals($user->firstname, 'tablette');
    assertEquals($user->lastname, '1');
    assertEquals($user->permissions, array(
                                        'P_SEE_PLANNING',
                                        'P_SEE_LAST_REPORT',
                                        'P_SAVE_ROAMINGS'
                                     ));
}

?>
  </pre>
 </body>
</html>

