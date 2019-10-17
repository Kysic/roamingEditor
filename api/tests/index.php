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

define('END_POINT', 'http://localhost/');
define('TABLET_1_AUTOLOGIN_KEY', '8U8MPr6/ZNo4rIQHU7gvezB7lkU6aYI8LXkHH9Le7ZF2Xf8otJgmHgTiVnJnHr12');

require_once('lib/Sql.php');
require_once('lib/Browser.php');
require_once('lib/auth.php');
require_once('lib/roamings.php');
require_once('lib/users.php');

date_default_timezone_set('Europe/Paris');

cleanTmpDir();

printTestCase('DB init');
$sql = new Sql();
$sql->reinitItDb();

printTestCase('Register as night watcher should succeed');
$browser = new Browser();
assertIsVisitor(getSessionUser($browser));
registerAndSetPassword($browser, 'berni@gmail.com', 'Berni-Password');
assertIsBernard(getSessionUser($browser));

printTestCase('Register as member should succeed');
$browser = new Browser();
assertIsVisitor(getSessionUser($browser));
registerAndSetPassword($browser, 'soso21@yahoo.fr', 'Soso@215');
assertIsSophie(getSessionUser($browser));

printTestCase('Register with address not in contacts should be forbidden');
$browser = new Browser();
try {
    register($browser, 'someone@gmail.com');
    throw new AssertException('register should have raised an HttpStatusException');
} catch (Exception $e) {
    assertException($e, "Cette adresse mail n'est pas repertoriée dans la liste des contacts du VINCI.", 403);
}
assertIsVisitor(getSessionUser($browser));

printTestCase('Register username should be generated correclty');
$browser = new Browser();
for ($i=0 ; $i<5; $i++) {
    register($browser, 'p'.$i.'@gmail.com');
}

printTestCase('Login as former should succeed');
$browser = new Browser();
login($browser, 'former.user@example.com', 'Former7899');
assertEquals($browser->cookies['vinciPersistentLogin'], '');
assertIsFormer(getSessionUser($browser));

printTestCase('Login as member should succeed');
$browser = new Browser();
login($browser, 'soso21@yahoo.fr', 'Soso@215');
assertEquals($browser->cookies['vinciPersistentLogin'], '');
assertIsMember(getSessionUser($browser));

printTestCase('Login as tutor should succeed');
$browser = new Browser();
login($browser, 'cerise.48@gmail.com', 'cerise.48@gmail.com');
assertEquals($browser->cookies['vinciPersistentLogin'], '');
assertIsTutor(getSessionUser($browser));

printTestCase('Login as night watcher should succeed');
$browser = new Browser();
login($browser, 'berni@gmail.com', 'Berni-Password');
assertEquals($browser->cookies['vinciPersistentLogin'], '');
assertIsBernard(getSessionUser($browser));

printTestCase('Change password as night watcher should succeed');
setPasswordWhenLogged($browser, 'berni-password-2', 'berni-password-2');
assertIsBernard(getSessionUser($browser));

printTestCase('Change to low security password as night watcher should failed');
try {
    setPasswordWhenLogged($browser, 'berni-password', 'berni-password');
    throw new AssertException('logout should have raised an HttpStatusException');
} catch (Exception $e) {
    assertException(
        $e,
        'Le mot de passe doit faire plus de 8 caractères, contenir des minuscules, majuscules, chiffres et/ou caractères spéciaux (au moins 3 éléments parmis les 4).',
        400
    );
}

printTestCase('Change password with bad confirmation as night watcher should failed');
try {
    setPasswordWhenLogged($browser, 'berni-password-2', 'berni-Password-2');
    throw new AssertException('logout should have raised an HttpStatusException');
} catch (Exception $e) {
    assertException($e, 'Le mot de passe et sa confirmation doivent être identiques.', 400);
}

printTestCase('Logout as night watcher should succeed');
assertIsBernard(getSessionUser($browser));
logout($browser);
assertIsVisitor(getSessionUser($browser));

printTestCase('Login as night watcher on three browsers with new password should succeed');
$browser1 = new Browser();
login($browser1, 'berni@gmail.com', 'berni-password-2', true);
$autologinId1 = $browser1->cookies['vinciPersistentLoginId'];
$autologinToken1 = $browser1->cookies['vinciPersistentLoginToken'];
$browser2 = new Browser();
login($browser2, 'berni@gmail.com', 'berni-password-2', false);
$autologinId2 = $browser2->cookies['vinciPersistentLoginId'];
$autologinToken2 = $browser2->cookies['vinciPersistentLoginToken'];
$browser3 = new Browser();
login($browser3, 'berni@gmail.com', 'berni-password-2', true);
$autologinId3 = $browser3->cookies['vinciPersistentLoginId'];
$autologinToken3 = $browser3->cookies['vinciPersistentLoginToken'];
assertIsBernard(getSessionUser($browser1));
assertIsBernard(getSessionUser($browser2));
assertIsBernard(getSessionUser($browser3));
assertNonEquals($autologinId1, '');
assertNonEquals($autologinToken1, '');
assertEquals($autologinId2, '');
assertEquals($autologinToken2, '');
assertNonEquals($autologinId3, '');
assertNonEquals($autologinToken3, '');
assertNonEquals($autologinId1, $autologinId3);
assertNonEquals($autologinToken1, $autologinToken3);

printTestCase('Bad autologin as appli should unset cookies');
$appliBrowser = new Browser();
$appliBrowser->cookies['vinciApplicationId'] = 'tablet@example.com';
$appliBrowser->cookies['vinciApplicationToken'] = 'XXblet1@example.com';
try {
    getSessionUser($appliBrowser);
    throw new AssertException('getSessionUser should have raised an HttpStatusException');
} catch (Exception $e) {
    assertException($e, 'Request rejected', 403);
}
assertEquals($appliBrowser->cookies['vinciApplicationId'], '');
assertEquals($appliBrowser->cookies['vinciApplicationToken'], '');
assertIsVisitor(getSessionUser($appliBrowser));

printTestCase('Login with appli credentials should failed');
$appliBrowser = new Browser();
try {
    login($appliBrowser, 'tablet@example.com', 'tablet1@example.com', false);
    throw new AssertException('login should have raised an HttpStatusException');
} catch (Exception $e) {
    assertException($e, 'Request rejected', 403);
}
assertIsVisitor(getSessionUser($appliBrowser));

printTestCase('Autologin as appli should succeed');
$appliBrowser = new Browser();
$appliBrowser->cookies['vinciApplicationId'] = 'tablet@example.com';
$appliBrowser->cookies['vinciApplicationToken'] = 'tablet1@example.com';
assertIsTablet1(getSessionUser($appliBrowser));
assertEquals($appliBrowser->cookies['vinciApplicationId'], 'tablet@example.com');
assertEquals($appliBrowser->cookies['vinciApplicationToken'], 'tablet1@example.com');

printTestCase('Autologin as night watcher should succeed');
$browser1 = createAutologinBrowser($autologinId1, $autologinToken1);
$browser2 = createAutologinBrowser($autologinId2, $autologinToken2);
$browser3 = createAutologinBrowser($autologinId3, $autologinToken3);
assertIsBernard(getSessionUser($browser1));
assertIsVisitor(getSessionUser($browser2));
assertIsBernard(getSessionUser($browser3));
assertEquals($autologinId1, $browser1->cookies['vinciPersistentLoginId']);
assertNonEquals($autologinToken1, $browser1->cookies['vinciPersistentLoginToken']);
$autologinId1 = $browser1->cookies['vinciPersistentLoginId'];
$autologinToken1 = $browser1->cookies['vinciPersistentLoginToken'];
assertEquals($autologinId3, $browser3->cookies['vinciPersistentLoginId']);
assertNonEquals($autologinToken3, $browser3->cookies['vinciPersistentLoginToken']);
$autologinId3 = $browser3->cookies['vinciPersistentLoginId'];
$autologinToken3 = $browser3->cookies['vinciPersistentLoginToken'];


printTestCase('Logout as appli should be forbidden');
try {
    logout($appliBrowser);
    throw new AssertException('logout should have raised an HttpStatusException');
} catch (Exception $e) {
    assertException($e, 'Vous n\'êtes pas autorisé à vous déconnecter du site.', 403);
}
assertIsTablet1(getSessionUser($appliBrowser));

printTestCase('Logout as night watcher should unset autologin');
assertIsBernard(getSessionUser($browser1));
logout($browser1);
assertEquals($browser1->cookies['vinciPersistentLoginId'], '');
assertIsVisitor(getSessionUser($browser1));

printTestCase('Autologin should not succeed after logout on same browser');
$browser1 = createAutologinBrowser($autologinId1, $autologinToken1);
assertIsVisitor(getSessionUser($browser1));
assertEquals($browser1->cookies['vinciPersistentLoginId'], '');

printTestCase('Autologin should succeed after logout on another browser');
$browser3 = createAutologinBrowser($autologinId3, $autologinToken3);
assertIsBernard(getSessionUser($browser3));
assertEquals($autologinId3, $browser3->cookies['vinciPersistentLoginId']);
assertNonEquals($autologinToken3, $browser3->cookies['vinciPersistentLoginToken']);
$autologinId3_old= $autologinId3;
$autologinToken3_old= $autologinToken3;
$autologinId3 = $browser3->cookies['vinciPersistentLoginId'];
$autologinToken3 = $browser3->cookies['vinciPersistentLoginToken'];

printTestCase('Autologin with old autologin should failed');
$browser3 = createAutologinBrowser($autologinId3_old, $autologinToken3_old);
try {
    getSessionUser($browser3);
    throw new AssertException('getSessionUser should have raised an HttpStatusException');
} catch (Exception $e) {
    assertException($e, 'Request rejected', 403);
}
assertEquals($browser3->cookies['vinciPersistentLoginId'], '');

printTestCase('Autologin with new autologin should failed after bad autlogin attempt');
$browser3 = createAutologinBrowser($autologinId3, $autologinToken3);
assertIsVisitor(getSessionUser($browser3));
assertEquals($browser3->cookies['vinciPersistentLoginId'], '');

printTestCase('Reset password as member should succeed');
$browser = new Browser();
resetPassword($browser, 'berni@gmail.com', 'Berni-Password');
assertIsBernard(getSessionUser($browser));

printTestCase('Login with previous password should failed');
$browser = new Browser();
try {
    login($browser, 'berni@gmail.com', 'berni-password-2');
    throw new AssertException('login should have raised an HttpStatusException');
} catch (Exception $e) {
    assertException($e, 'Identifiants invalides.', 400);
}
assertIsVisitor(getSessionUser($browser));

printTestCase('Login as member with new password should succeed');
login($browser, 'berni@gmail.com', 'Berni-Password');
assertIsBernard(getSessionUser($browser));

printTestCase('GetPlanning as appli should succeed');
$twentiethDayOfLastMonth = date('Y-m-d', strtotime('+19 day', strtotime('first day of last month')));
$result = getplanning($appliBrowser, $twentiethDayOfLastMonth);
assertEquals($result, (object) array('tutor'=>'Nicole P','teammates'=>array('Olivier C', 'Sonia Jb', 'Aude P'),'status'=>'planned-complete'));

printTestCase('GetPlanning on a period should succeed');
$thirdDayOfMonth = date('Y-m-d', strtotime('+2 day', strtotime('first day of this month')));
$fourthDayOfMonth = date('Y-m-d', strtotime('+3 day', strtotime('first day of this month')));
$fifthDayOfMonth = date('Y-m-d', strtotime('+4 day', strtotime('first day of this month')));
$result = getplannings($appliBrowser, $thirdDayOfMonth, $fifthDayOfMonth);
assertEquals($result, (object) array(
        $thirdDayOfMonth => (object) array('tutor'=>'','teammates'=>array('Akim.s', 'Tatiana', 'Amelie G'),'status'=>'unsure'),
        $fourthDayOfMonth => (object) array('tutor'=>'Maraude','teammates'=>array('Annulee', '', ''),'status'=>'canceled'),
        $fifthDayOfMonth => (object) array('tutor'=>'Roland T','teammates'=>array('Annie B', '', ''),'status'=>'planned-uncomplete'),
        'infos' => array('Réunion bureau/tuteurs : Mercredi 14 juin', 'Réunion mensuelle (ouverte à tous) : mercredi 28 juin - 19h'),
        'calendarUrl' => 'https://calendar.google.com/calendar/embed?src='
    ));

printTestCase('nextDaysStatus should succeed');
$browserVisitor = new Browser();
$result = nextDaysStatus($browserVisitor);
assertEquals(count((array)$result), 4);
foreach($result as $key => $value) {
    if ( !preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $key) ) {
        throw new AssertException('Expected date with format yyyy-mm-dd, get '.$key);
    }
    assertEquals(count((array)$value), 1);
    if ( !in_array($value->status, array('canceled', 'unsure', 'planned-uncomplete', 'planned-complete')) ) {
        throw new AssertException('Unexpected status : '.$value->status);
    }
}

printTestCase('SaveRoaming as appli should succeed');
$roaming = generateRoamingReport($twentiethDayOfLastMonth, 5);
$result = saveRoaming($appliBrowser, $roaming);

printTestCase('GetRoamings as appli should succeed');
$result = getRoamings($appliBrowser);
assertEquals($result->status, 'success');
$roamings = $result->roamings;
assertEquals($roamings->{'1'}->date, $twentiethDayOfLastMonth);
assertEquals($roamings->{'1'}->version, 5);

printTestCase('Get all users as root should succeed');
$browserRoot = new Browser();
login($browserRoot, 'Laure.Maitre@example.com', 'Laure.Maitre@example.com');
$result = getUsers($browserRoot);
assertEquals($result->status, 'success');
$usernames = array_map(function ($user) { return $user->username; }, $result->users);
assertEquals($usernames, array(
    'Alexis M',
    'Amina T',
    'Anaële C',
    'Bernard D',
    'Cerise M',
    'Jean D',
    'Laure M',
    'Paul P',
    'Paul Pa',
    'Paul Par',
    'Paul Par 2',
    'Paul Par 3',
    'Sophie D',
    'Tablette 1',
    'User F'
));

printTestCase('Set user role as root should succeed');
setUserRole($browserRoot, 17, 'tutor', $sessionToken = NULL);
$browser = new Browser();
login($browser, 'berni@gmail.com', 'Berni-Password');
assertEquals(getSessionUser($browser)->role, 'tutor');

printTestCase('GetDocUrl as tutor should generate docId and succeed');
$result = getDocUrl($browser, 1);
assertEquals($result->status, 'success');
$docId = $result->docId;
$editUrl = $result->editUrl;
$roaming = $browser->get($editUrl);
assertEquals($roaming->date, $twentiethDayOfLastMonth);
assertEquals($roaming->version, 5);

printTestCase('Second GetDocUrl as tutor should return the same docId');
$result = getDocUrl($browser, 1);
assertEquals($result->status, 'success');
assertEquals($result->docId, $docId);
assertEquals($result->editUrl, $editUrl);




printTestCase('Bruteforce system should forbid to much connexion attempts');
for ($i=0 ; $i<10; $i++) {
    try {
        $browser = new Browser();
        login($browser, 'berni@gmail.com', 'Berni-Password-bad');
    } catch (Exception $e) {
        // Nothing
    }
}
try {
    login($browser, 'berni@gmail.com', 'Berni-Password');
    throw new AssertException('login should have raised an HttpStatusException');
} catch (Exception $e) {
    assertException($e, 'Trop de tentatives depuis cette IP, veuillez réessayer dans un moment.', 403);
}




class AssertException extends Exception { }

function assertNonEquals($actual, $expected, $errorMsg = NULL) {
    if ($expected == $actual) {
        if ($errorMsg) {
            throw new AssertException($errorMsg);
        } else {
            throw new AssertException('Expect non equals to "'.print_r($expected, true));
        }
    }
}

function assertException($exception, $errorMsg, $errorCode) {
    if ($exception instanceof AssertException) {
        throw $exception;
    }
    assertEquals($exception->content->errorMsg, $errorMsg);
    assertEquals($exception->content->status, 'error');
    assertEquals($exception->statusCode, $errorCode);
    assertEquals($exception->content->errorCode, $errorCode);
}

function assertEquals($actual, $expected, $errorMsg = NULL) {
    if ($expected != $actual) {
        if ($errorMsg) {
            throw new AssertException($errorMsg);
        } else {
            throw new AssertException('Expect "'.print_r($expected, true).'", get "'.print_r($actual, true).'"');
        }
    }
}

function assertSuccess($response, $errorMsg = NULL) {
    assertEquals($response->status, 'success', $errorMsg);
}

function printTestCase($testCase) {
    echo "==== ".$testCase." ====\r\n";
}

function cleanTmpDir() {
    $files = glob(__dir__.'/tmp/*');
    foreach ($files as $file) {
        if( is_file($file) ) {
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
                                        'P_REGISTER',
                                        'P_LOG_IN',
                                        'P_RESET_PASSWORD'
                                     ));
}

function assertIsBernard($user) {
    assertEquals($user->email, 'berni@gmail.com');
    assertEquals($user->firstname, 'Bernard');
    assertEquals($user->lastname, 'DUPONT');
    assertEquals($user->username, 'Bernard D');
    assertIsNightWatcher($user);
}
function assertIsSophie($user) {
    assertEquals($user->email, 'soso21@yahoo.fr');
    assertEquals($user->firstname, 'Sophie');
    assertEquals($user->lastname, 'DUPRES');
    assertEquals($user->username, 'Sophie D');
    assertIsMember($user);
}

function assertIsFormer($user) {
    assertEquals($user->role, 'former');
    assertEquals($user->permissions, array(
                                        'P_LOG_OUT',
                                        'P_CHANGE_PASSWORD'
                                     ));
}
function assertIsMember($user) {
    assertEquals($user->role, 'member');
    assertEquals($user->permissions, array(
                                        'P_SEE_USERS_LIST',
                                        'P_SEE_MEETING',
                                        'P_EDIT_PLANNING',
                                        'P_SEE_PLANNING',
                                        'P_SEE_NAMES',
                                        'P_LOG_OUT',
                                        'P_CHANGE_PASSWORD'
                                     ));
}
function assertIsNightWatcher($user) {
    assertEquals($user->role, 'night_watcher');
    assertEquals($user->permissions, array(
                                        'P_ENROL',
                                        'P_SEE_LAST_REPORT',
                                        'P_SEE_USERS_LIST',
                                        'P_SEE_MEETING',
                                        'P_EDIT_PLANNING',
                                        'P_SEE_PLANNING',
                                        'P_SEE_NAMES',
                                        'P_LOG_OUT',
                                        'P_CHANGE_PASSWORD'
                                     ));
}

function assertIsTutor($user) {
    assertEquals($user->role, 'tutor');
    assertEquals($user->permissions, array(
                                        'P_EDIT_REPORT',
                                        'P_ENROL_AS_TUTOR',
                                        'P_GEN_STATS',
                                        'P_ENROL',
                                        'P_SEE_LAST_REPORT',
                                        'P_SEE_USERS_LIST',
                                        'P_SEE_MEETING',
                                        'P_EDIT_PLANNING',
                                        'P_SEE_PLANNING',
                                        'P_SEE_NAMES',
                                        'P_LOG_OUT',
                                        'P_CHANGE_PASSWORD'
                                     ));
}

function assertIsTablet1($user) {
    assertEquals($user->role, 'appli');
    assertEquals($user->email, 'tablet@example.com');
    assertEquals($user->firstname, 'tablette');
    assertEquals($user->lastname, '1');
    assertEquals($user->permissions, array(
                                        'P_SEE_PLANNING',
                                        'P_EDIT_PLANNING',
                                        'P_SEE_LAST_REPORT',
                                        'P_SAVE_ROAMINGS',
                                        'P_SEE_USERS_LIST',
                                        'P_SEE_MEETING'
                                     ));
}

?>
  </pre>
 </body>
</html>

