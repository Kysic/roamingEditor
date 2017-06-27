<?php

require_once('/var/www/vinci/api/lib/Container.php');

function areRolesConsistent($users, $members) {
    foreach ($users as $user) {
        $email = strtolower($user->email);
        if (array_key_exists($email, $members)) {
            $member = $members[$email];
            if ($member['isTutor']) {
                if ( !in_array( $user->role, array(TUTOR, BOARD, ADMIN, ROOT) ) ) {
                    return false;
                }
            } else {
                if ( !in_array( $user->role, array(MEMBER, BOARD, ADMIN, ROOT) ) ) {
                    return false;
                }
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
        echo 'Invalid role detected'."\n";
        $container->getMail()->sendMail(ADMIN_EMAIL, '[VINCI] Some VINCI user doesn\'t have the right role',
            'Some user doesn\'t have the right role.'
        );
    }
    echo 'done';

} catch (Exception $e) {
    $container->getMail()->sendMail(ADMIN_EMAIL, '[VINCI] Error detected while checking users roles',
        'An error has occured while checking the VINCI users roles: '."\n".print_r($e, true)
    );
    throw $e;
}

