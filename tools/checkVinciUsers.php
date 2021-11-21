#!/usr/bin/php
<?php

require_once('/var/www/vinci/api/lib/Container.php');

function checkConsistency($users, $members) {
    foreach ($users as $user) {
        $email = strtolower($user->email);
        if (array_key_exists($email, $members)) {
            $member = $members[$email];
            if (@$member['isBoard']) {
                if ( !in_array( $user->role, array(BOARD, ADMIN, ROOT) ) ) {
                    return 'role '.$user->role.' of '.$email.' is not compatible with "isBoard"';
                }
            } else if (@$member['isTutor']) {
                if ( !in_array( $user->role, array(TUTOR, ADMIN, ROOT) ) ) {
                    return 'role '.$user->role.' of '.$email.' is not compatible with "isTutor"';
                }
            } else if (@$member['doRoaming']) {
                if ( !in_array( $user->role, array(NIGHT_WATCHER, ADMIN, ROOT) ) ) {
                    return 'role '.$user->role.' of '.$email.' is not compatible with "doRoaming"';
                }
            } else {
                if ( !in_array( $user->role, array(MEMBER, EXTERNAL, BOARD, ADMIN, ROOT) ) ) {
                    return 'role '.$user->role.' of '.$email.' is not compatible with presence in second tab';
                }
            }
            if ($user->firstname !== $member['firstname']) {
                return 'firstname of '.$email.' '.$user->firstname.' != '.$member['firstname'];
            }
            if ($user->lastname !== $member['lastname']) {
                return 'lastname of '.$email.' '.$user->lastname.' != '.$member['lastname'];
            }
            if ($user->gender !== $member['gender']) {
                return 'gender of '.$email.' '.$user->gender.' != '.$member['gender'];
            }
        } else {
            if ( !in_array( $user->role, array(APPLI, EXTERNAL, FORMER, GUEST) ) ) {
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
        // echo 'Inconstency detected : '.$error."\n";
        $container->getMail()->sendMail(ADMIN_EMAIL, '[AMICI] Some AMICI user are not in sync with official list',
            'Some user doesn\'t have correct attributes :'."\n".$error
        );
    }

} catch (Exception $e) {
    $container->getMail()->sendMail(ADMIN_EMAIL, '[AMICI] Error detected while checking users',
        'An error has occured while checking the AMICI users: '."\n".print_r($e, true)
    );
    throw $e;
}

