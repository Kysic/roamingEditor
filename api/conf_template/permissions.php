<?php

require_once('conf/main.php');

# Roles and permissions configuration
define('P_SIGN_IN', 'P_SIGN_IN');
define('P_LOG_IN', 'P_LOG_IN');
define('P_LOG_OUT', 'P_LOG_OUT');
define('P_RESET_PASSWORD', 'P_RESET_PASSWORD');
define('P_CHANGE_PASSWORD', 'P_CHANGE_PASSWORD');
define('P_SEE_PLANNING', 'P_SEE_PLANNING');
define('P_SAVE_ROAMINGS', 'P_SAVE_ROAMINGS');
define('P_SEE_NAMES', 'P_SEE_NAMES');
define('P_SEE_LAST_REPORT', 'P_SEE_LAST_REPORT');
define('P_EDIT_REPORT', 'P_EDIT_REPORT');
define('P_SEE_ALL_REPORT', 'P_SEE_ALL_REPORT');
define('P_SEE_USERS_LIST', 'P_SEE_USERS_LIST');
define('P_ASSIGN_ROLE', 'P_ASSIGN_ROLE');
define('P_GENERATE_PASSWORD_TOKEN', 'P_GENERATE_PASSWORD_TOKEN');
#
define('VISITOR', 'visitor');
define('APPLI', 'appli');
define('FORMER', 'former');
define('GUEST', 'guest');
define('MEMBER', 'member');
define('TUTOR', 'tutor');
define('BOARD', 'board');
define('ADMIN', 'admin');
define('ROOT', 'root');
#
function buildRolesPermissions() {
    $visitor = array( P_SIGN_IN, P_LOG_IN, P_RESET_PASSWORD );
    $appli = array ( P_SEE_PLANNING, P_SAVE_ROAMINGS );
    $former = array( P_LOG_OUT, P_CHANGE_PASSWORD );
    $guest = array_merge($former, array( P_SEE_PLANNING ));
    $member = array_merge($guest, array( P_SEE_NAMES, P_SEE_LAST_REPORT, P_EDIT_REPORT ));
    $tutor = array_merge($member, array(  ));
    $board = array_merge($tutor, array( P_SEE_ALL_REPORT ));
    $admin = array_merge($board, array( P_SEE_USERS_LIST, P_ASSIGN_ROLE ));
    $root = array_merge($admin, array( P_SAVE_ROAMINGS, P_GENERATE_PASSWORD_TOKEN ));
    return array(
        VISITOR => $visitor,
        APPLI => $appli,
        FORMER  => $former,
        GUEST   => $guest,
        MEMBER  => $member,
        TUTOR   => $tutor,
        BOARD   => $board,
        ADMIN   => $admin,
        ROOT    => $root
    );
}

$ROLES_PERMISSIONS = buildRolesPermissions();

?>
