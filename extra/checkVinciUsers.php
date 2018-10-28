<?php

require_once('/var/www/vinci/api/lib/Container.php');

function areRolesConsistent($users, $members) {
    foreach ($users as $user) {
        $email = strtolower($user->email);
        if (array_key_exists($email, $members)) {
            $member = $members[$email];
            if ($member['isBoard']) {
                if ( !in_array( $user->role, array(BOARD, ADMIN, ROOT) ) ) {
                    return false;
                }
            } else if ($member['isTutor']) {
                if ( !in_array( $user->role, array(TUTOR, ADMIN, ROOT) ) ) {
                    return false;
                }
            } else {
                if ( !in_array( $user->role, array(MEMBER, ADMIN, ROOT) ) ) {
                    return false;
                }
            }
            if ($user->firstname !== $member['firstname']) {
                return false;
            }
            if ($user->lastname !== $member['lastname']) {
                return false;
            }
            if ($user->gender !== $member['gender']) {
                return false;
            }
        } else {
            if ( !in_array( $user->role, array(APPLI, FORMER, GUEST) ) ) {
                return false;
            }
        }
    }
    return true;
}

try {

    $container = new Container();
    $container->getRolesPermissions();
    $usersStorage = $container->getUsersStorage();
    $googleContacts = $container->getGoogleContacts();

    $users = $usersStorage->getAllUsers();
    $members = $googleContacts->extractContacts();

    if ( !areRolesConsistent($users, $members) ) {
        echo 'Inconstency detected'."\n";
        $container->getMail()->sendMail(ADMIN_EMAIL, '[VINCI] Some VINCI user are not in sync with official list',
            'Some user doesn\'t have correct attributes.'
        );
    }
    echo 'done'."\n";

} catch (Exception $e) {
    $container->getMail()->sendMail(ADMIN_EMAIL, '[VINCI] Error detected while checking users',
        'An error has occured while checking the VINCI users: '."\n".print_r($e, true)
    );
    throw $e;
}

