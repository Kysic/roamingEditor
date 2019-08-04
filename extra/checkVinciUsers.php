<?php

require_once('/var/www/vinci/api/lib/Container.php');

function checkConsistency($users, $members) {
    foreach ($users as $user) {
        $email = strtolower($user->email);
        if (array_key_exists($email, $members)) {
            $member = $members[$email];
            if ($member['isBoard']) {
                if ( !in_array( $user->role, array(BOARD, ADMIN, ROOT) ) ) {
                    return 'role of '.$email;
                }
            } else if ($member['isTutor']) {
                if ( !in_array( $user->role, array(TUTOR, ADMIN, ROOT) ) ) {
                    return 'role of '.$email;
                }
            } else {
                if ( !in_array( $user->role, array(MEMBER, ADMIN, ROOT) ) ) {
                    return 'role of '.$email;
                }
            }
            if ($user->firstname !== $member['firstname']) {
                return 'firstname of '.$email;
            }
            if ($user->lastname !== $member['lastname']) {
                return 'lastname of '.$email;
            }
            if ($user->gender !== $member['gender']) {
                return 'gender of '.$email;
            }
        } else {
            if ( !in_array( $user->role, array(APPLI, FORMER, GUEST) ) ) {
                return 'presence of '.$email;
            }
        }
    }
    return false;
}

try {

    $container = new Container();
    $container->getRolesPermissions();
    $usersStorage = $container->getUsersStorage();
    $googleContacts = $container->getGoogleContacts();

    $users = $usersStorage->getAllUsers();
    $members = $googleContacts->extractContacts();

    $error = checkConsistency($users, $members);
    if ($error) {
        echo 'Inconstency detected : '.$error."\n";
        $container->getMail()->sendMail(ADMIN_EMAIL, '[VINCI] Some VINCI user are not in sync with official list',
            'Some user doesn\'t have correct attributes :'."\n".$error
        );
    }
    echo 'done'."\n";

} catch (Exception $e) {
    $container->getMail()->sendMail(ADMIN_EMAIL, '[VINCI] Error detected while checking users',
        'An error has occured while checking the VINCI users: '."\n".print_r($e, true)
    );
    throw $e;
}

