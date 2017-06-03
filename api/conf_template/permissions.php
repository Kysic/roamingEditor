<?php

require_once('conf/main.php');

# Permissions
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
# Roles
define('VISITOR', 'visitor');
define('APPLI', 'appli');
define('FORMER', 'former');
define('GUEST', 'guest');
define('MEMBER', 'member');
define('TUTOR', 'tutor');
define('BOARD', 'board');
define('ADMIN', 'admin');
define('ROOT', 'root');
# Mapping between roles and permissions
function buildRolesPermissions() {
    $visitor = array( P_SIGN_IN, P_LOG_IN, P_RESET_PASSWORD );
    $appli = array ( P_SEE_PLANNING, P_SEE_LAST_REPORT, P_SAVE_ROAMINGS );
    $former = array( P_LOG_OUT, P_CHANGE_PASSWORD );
    $guest = array_merge(array( P_SEE_PLANNING, P_SEE_NAMES ), $former);
    $member = array_merge(array( P_SEE_LAST_REPORT, P_EDIT_REPORT ), $guest);
    $tutor = array_merge(array(  ), $member);
    $board = array_merge(array( P_SEE_ALL_REPORT ), $tutor);
    $admin = array_merge(array( P_SEE_USERS_LIST, P_ASSIGN_ROLE ), $board);
    $root = array_merge(array( P_SAVE_ROAMINGS, P_GENERATE_PASSWORD_TOKEN ), $admin);
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

