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

printTestCase('Register as member should succeed');
$browser = new Browser();
assertIsVisitor(getSessionUser($browser));
registerAndSetPassword($browser, 'berni@gmail.com', 'Berni-Password');
assertIsBernard(getSessionUser($browser));

printTestCase('Register with address not in contacts should be forbidden');
$browser = new Browser();
try {
    register($browser, 'someone@gmail.com');
    throw new AssertException('register should have raised an HttpStatusException');
} catch (Exception $e) {
    assertException($e, "Cette adresse mail n'est pas repertoriée dans la liste des contacts du VINCI.", 403);
}
assertIsVisitor(getSessionUser($browser));

printTestCase('Login as member should succeed');
$browser = new Browser();
login($browser, 'berni@gmail.com', 'Berni-Password');
assertEquals($browser->cookies['vinciPersistentLogin'], '');
assertIsBernard(getSessionUser($browser));

printTestCase('Change password as member should succeed');
setPasswordWhenLogged($browser, 'berni-password-2', 'berni-password-2');
assertIsBernard(getSessionUser($browser));

printTestCase('Change to low security password as member should failed');
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

printTestCase('Change password with bad confirmation as member should failed');
try {
    setPasswordWhenLogged($browser, 'berni-password-2', 'berni-Password-2');
    throw new AssertException('logout should have raised an HttpStatusException');
} catch (Exception $e) {
    assertException($e, 'Le mot de passe et sa confirmation doivent être identiques.', 400);
}

printTestCase('Logout as member should succeed');
assertIsBernard(getSessionUser($browser));
logout($browser);
assertIsVisitor(getSessionUser($browser));

printTestCase('Login as member on three browsers with new password should succeed');
$browser1 = new Browser();
login($browser1, 'berni@gmail.com', 'berni-password-2', true);
$autologin1 = $browser1->cookies['vinciPersistentLogin'];
$browser2 = new Browser();
login($browser2, 'berni@gmail.com', 'berni-password-2', false);
$autologin2 = $browser2->cookies['vinciPersistentLogin'];
$browser3 = new Browser();
login($browser3, 'berni@gmail.com', 'berni-password-2', true);
$autologin3 = $browser3->cookies['vinciPersistentLogin'];
assertIsBernard(getSessionUser($browser1));
assertIsBernard(getSessionUser($browser2));
assertIsBernard(getSessionUser($browser3));
assertNonEquals($autologin1, '');
assertEquals($autologin2, '');
assertNonEquals($autologin3, '');
assertNonEquals($autologin1, $autologin3);

printTestCase('Autologin as appli should succeed');
$appliBrowser = new Browser();
$appliBrowser->cookies['vinciPersistentLogin'] = TABLET_1_AUTOLOGIN_KEY;
assertIsTablet1(getSessionUser($appliBrowser));

printTestCase('Autologin as member should succeed');
$browser1 = new Browser();
$browser1->cookies['vinciPersistentLogin'] = $autologin1;
$browser2 = new Browser();
$browser2->cookies['vinciPersistentLogin'] = $autologin2;
$browser3 = new Browser();
$browser3->cookies['vinciPersistentLogin'] = $autologin3;
assertIsBernard(getSessionUser($browser1));
assertIsVisitor(getSessionUser($browser2));
assertIsBernard(getSessionUser($browser3));

printTestCase('Logout as appli should be forbidden');
try {
    logout($appliBrowser);
    throw new AssertException('logout should have raised an HttpStatusException');
} catch (Exception $e) {
    assertException($e, 'Vous n\'êtes pas autorisé à vous déconnecter du site.', 403);
}
assertIsTablet1(getSessionUser($appliBrowser));

printTestCase('Logout as member should unset autologin');
assertIsBernard(getSessionUser($browser1));
logout($browser1);
assertIsVisitor(getSessionUser($browser1));
assertEquals($browser1->cookies['vinciPersistentLogin'], '');

printTestCase('Autologin should not succeed after logout');
$browser1 = new Browser();
$browser1->cookies['vinciPersistentLogin'] = $autologin1;
assertIsVisitor(getSessionUser($browser1));
assertEquals($browser1->cookies['vinciPersistentLogin'], '');
$browser3 = new Browser();
$browser3->cookies['vinciPersistentLogin'] = $autologin3;
assertIsVisitor(getSessionUser($browser3));
assertEquals($browser3->cookies['vinciPersistentLogin'], '');

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
        $fifthDayOfMonth => (object) array('tutor'=>'Roland T','teammates'=>array('Annie B', '', ''),'status'=>'planned-uncomplete')
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

printTestCase('Get all users as root should succeed');
$browserRoot = new Browser();
login($browserRoot, 'Laure.Maitre@example.com', 'Laure.Maitre@example.com');
$result = getUsers($browserRoot);
assertEquals($result->status, 'success');
assertEquals(count($result->users), 7);

printTestCase('Set user role as root should succeed');
setUserRole($browserRoot, 15, 'tutor', $sessionToken = NULL);
$browser = new Browser();
login($browser, 'berni@gmail.com', 'Berni-Password');
assertEquals(getSessionUser($browser)->role, 'tutor');




printTestCase('Bruteforce system should forbid to much connexion attempts');
for ($i=0 ; $i<5; $i++) {
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
    assertException($e, 'Trop de tentatives de connexion depuis cette IP, veillez réessayer dans un moment.', 403);
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
    assertEquals($user->role, 'member');
    assertEquals($user->email, 'berni@gmail.com');
    assertEquals($user->firstname, 'Bernard');
    assertEquals($user->lastname, 'DUPONT');
    assertEquals($user->permissions, array(
                                        'P_EDIT_PLANNING',
                                        'P_SEE_LAST_REPORT',
                                        'P_EDIT_REPORT',
                                        'P_SEE_PLANNING',
                                        'P_SEE_NAMES',
                                        'P_LOG_OUT',
                                        'P_CHANGE_PASSWORD'
                                     ));
}

function assertIsTablet1($user) {
    assertEquals($user->role, 'appli');
    assertEquals($user->email, 'tablet1@example.com');
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

