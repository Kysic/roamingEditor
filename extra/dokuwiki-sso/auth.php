<?php

require_once('/var/www/vinci/api/lib/Container.php');

/**
 * AMICI auth plugin
 * Should be in <dokuwiki>/lib/plugins/vinciSSO/auth.php
 * <dokuwiki>/conf/local.php should contains:
 *  $conf['authtype'] = 'vinciSSO';
 *  $conf['superuser'] = '@root';
 * <dokuwiki>/conf/local.protected.php should contains:
 *  <?php
 *  define('DOKU_SESSION_NAME', 'vinciSession');
 *  define('DOKU_SESSION_PATH', '/');
 */
class auth_plugin_vinciSSO extends DokuWiki_Auth_Plugin {

    private $container;

    public function __construct() {
        parent::__construct();
        global $noFormTokenCheck;
        $noFormTokenCheck = true;
        $this->container = new Container();
        $this->cando['external'] = true;
        $this->cando['logout'] = true;
    }

    public function logOff() {
        try {
            $this->container->getAuth()->logout();
        } catch (Exception $e) {
            msg($e->getMessage(), -1);
        }
    }

    public function trustExternal($user, $pass, $sticky = false) {

        global $USERINFO;
        global $conf;
        global $connection;

        $sticky ? $sticky = true : $sticky = false; //sanity check

        // do the checking here
        $session = $this->container->getSession();
        if (!$session->isLoggedIn()) {
            if (empty($user)) {
                return false;
            }
            try {
                $this->container->getAuth()->login($user, $pass, $sticky);
            } catch (Exception $e) {
                msg($e->getMessage(), -1);
                return false;
            }
        }
        $user = $session->getUser();

        // set the globals if authed
        $USERINFO['name'] = $user->firstname.' '.$user->lastname;
        $USERINFO['mail'] = $user->email;
        $USERINFO['grps'] = array($user->role);
        $_SERVER['REMOTE_USER'] = $user->email;
        $_SESSION[DOKU_COOKIE]['auth']['user'] = $user->email;
        $_SESSION[DOKU_COOKIE]['auth']['pass'] = $pass;
        $_SESSION[DOKU_COOKIE]['auth']['info'] = $USERINFO;
        return true;
    }
}


