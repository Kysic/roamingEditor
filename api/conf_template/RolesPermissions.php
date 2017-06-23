<?php

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
define('P_UPLOAD_REPORT', 'P_UPLOAD_REPORT');
define('P_DELETE_REPORT', 'P_DELETE_REPORT');
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
class RolesPermissions {
    private $visitor;
    private $appli;
    private $former;
    private $guest;
    private $member;
    private $tutor;
    private $board;
    private $admin;
    private $root;

    public function __construct() {
        $this->visitor = array( P_SIGN_IN, P_LOG_IN, P_RESET_PASSWORD );
        $this->appli = array ( P_SEE_PLANNING, P_SEE_LAST_REPORT, P_SAVE_ROAMINGS );
        $this->former = array( P_LOG_OUT, P_CHANGE_PASSWORD );
        $this->guest = array_merge(array( P_SEE_PLANNING, P_SEE_NAMES ), $this->former);
        $this->member = array_merge(array( P_SEE_LAST_REPORT, P_EDIT_REPORT ), $this->guest);
        $this->tutor = array_merge(array(  ), $this->member);
        $this->board = array_merge(array( P_SEE_ALL_REPORT ), $this->tutor);
        $this->admin = array_merge(array( P_SEE_USERS_LIST, P_ASSIGN_ROLE, P_UPLOAD_REPORT, P_DELETE_REPORT ), $this->board);
        $this->root = array_merge(array( P_GENERATE_PASSWORD_TOKEN ), $this->admin);
    }

    public function getPermissions($role) {
        switch ($role) {
            case APPLI: return $this->appli;
            case FORMER: return $this->former;
            case GUEST: return $this->guest;
            case MEMBER: return $this->member;
            case TUTOR: return $this->tutor;
            case BOARD: return $this->board;
            case ADMIN: return $this->admin;
            case ROOT: return $this->root;
            default: return $this->visitor;
        }
    }
}

