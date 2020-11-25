<?php

# Permissions
define('P_REGISTER', 'P_REGISTER');
define('P_LOG_IN', 'P_LOG_IN');
define('P_LOG_OUT', 'P_LOG_OUT');
define('P_RESET_PASSWORD', 'P_RESET_PASSWORD');
define('P_CHANGE_PASSWORD', 'P_CHANGE_PASSWORD');
define('P_SEE_PLANNING', 'P_SEE_PLANNING');
define('P_EDIT_PLANNING', 'P_EDIT_PLANNING');
define('P_SAVE_ROAMINGS', 'P_SAVE_ROAMINGS');
define('P_SEE_NAMES', 'P_SEE_NAMES');
define('P_ENROL', 'P_ENROL');
define('P_ENROL_AS_TUTOR', 'P_ENROL_AS_TUTOR');
define('P_GEN_STATS', 'P_GEN_STATS');
define('P_SEE_LAST_REPORT', 'P_SEE_LAST_REPORT');
define('P_EDIT_REPORT', 'P_EDIT_REPORT');
define('P_UPLOAD_REPORT', 'P_UPLOAD_REPORT');
define('P_DELETE_REPORT', 'P_DELETE_REPORT');
define('P_SEE_ALL_REPORT', 'P_SEE_ALL_REPORT');
define('P_SEE_USERS_LIST', 'P_SEE_USERS_LIST');
define('P_ASSIGN_ROLE', 'P_ASSIGN_ROLE');
define('P_SEE_MEETING', 'P_SEE_MEETING');
define('P_EDIT_MEETING', 'P_EDIT_MEETING');
define('P_SEE_REPORT_PHONE', 'P_SEE_REPORT_PHONE');
define('P_CANCEL_ROAMING', 'P_CANCEL_ROAMING');
define('P_SEE_LAST_115_REPORTS', 'P_SEE_LAST_115_REPORTS');
# Roles
define('VISITOR', 'visitor');
define('APPLI', 'appli');
define('FORMER', 'former');
define('GUEST', 'guest');
define('MEMBER', 'member');
define('NIGHT_WATCHER', 'night_watcher');
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
    private $nightWatcher;
    private $tutor;
    private $board;
    private $admin;
    private $root;

    public function __construct() {
        $this->visitor = array( P_REGISTER, P_LOG_IN, P_RESET_PASSWORD );
        $this->appli = array ( P_SEE_PLANNING, P_EDIT_PLANNING, P_SEE_LAST_REPORT, P_SAVE_ROAMINGS, P_SEE_USERS_LIST,
                               P_SEE_MEETING, P_SEE_LAST_115_REPORTS );
        $this->former = array( P_LOG_OUT, P_CHANGE_PASSWORD );
        $this->guest = array_merge(array( P_SEE_PLANNING, P_SEE_NAMES ), $this->former);
        $this->member = array_merge(array( P_SEE_USERS_LIST, P_SEE_MEETING, P_EDIT_PLANNING ), $this->guest);
        $this->nightWatcher = array_merge(array( P_ENROL, P_SEE_LAST_REPORT ), $this->member);
        $this->tutor = array_merge(array( P_EDIT_REPORT, P_ENROL_AS_TUTOR, P_GEN_STATS ), $this->nightWatcher);
        $this->board = array_merge(array( P_EDIT_MEETING ), $this->tutor);
        $this->admin = array_merge(array( P_UPLOAD_REPORT, P_DELETE_REPORT, P_SEE_REPORT_PHONE, P_CANCEL_ROAMING ), $this->board);
        $this->root = array_merge(array( P_SEE_ALL_REPORT, P_ASSIGN_ROLE, P_REGISTER, P_SEE_LAST_115_REPORTS ), $this->admin);
    }

    public function getPermissions($role) {
        switch ($role) {
            case ROOT: return $this->root;
            case ADMIN: return $this->admin;
            case BOARD: return $this->board;
            case TUTOR: return $this->tutor;
            case NIGHT_WATCHER: return $this->nightWatcher;
            case MEMBER: return $this->member;
            case APPLI: return $this->appli;
            case GUEST: return $this->guest;
            case FORMER: return $this->former;
            default: return $this->visitor;
        }
    }
}

