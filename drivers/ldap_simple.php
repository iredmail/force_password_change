<?php

/**
 * Simple LDAP Driver
 *
 * Driver for force password change what stored in LDAP database
 * @version 1.0
 * @author WAINLAKE <michael@wainlake.com>
 *
 */
class ldap_simple_driver
{
    private $ds = '';
    private $user_dn = '';

    function save($rcmail, $time)
    {
        if ($this->connect($rcmail)) {
            $ds = $this->ds;
            $user_dn = $this->user_dn;
            $entry['shadowlastchange'] = (int)(strtotime($time) / 86400);

            if (!ldap_modify($ds, $user_dn, $entry)) {
                ldap_unbind($ds);
                return false;
            }
            ldap_unbind($ds);
        }
        return true;
    }

    function get($rcmail)
    {
        if ($this->connect($rcmail)) {
            $ds = $this->ds;
            $basedn = $rcmail->config->get('password_ldap_basedn');
            $filter = '(mail=' . $_SESSION['username'] . ' *)';
            $entry = array("shadowlastchange");

            $sr = ldap_search($ds, $basedn, $filter, $entry);
            $attr = ldap_get_entries($ds, $sr);
            $lastchange = $attr[0]['shadowlastchange'][0];

            ldap_unbind($ds);

            if ($attr["count"] > 0 && !is_null($lastchange) && $lastchange > 0) {
                return $lastchange * 86400;
            }
        }
        return 0;
    }

    function connect($rcmail)
    {

        $ldap_host = $rcmail->config->get('password_ldap_host');
        $ldap_port = $rcmail->config->get('password_ldap_port');

        $ds = ldap_connect($ldap_host, $ldap_port);

        ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, $rcmail->config->get('password_ldap_version'));

        if ($rcmail->config->get('password_ldap_starttls')) {
            if (!ldap_start_tls($ds)) {
                ldap_unbind($ds);
                return false;
            }
        }

        $user_dn = $this->substitute_vars($rcmail->config->get('password_ldap_userDN_mask'));

        if (empty($user_dn)) {
            ldap_unbind($ds);
            return false;
        }

        switch ($rcmail->config->get('password_ldap_method')) {
            case 'admin':
                $binddn = $rcmail->config->get('password_ldap_adminDN');
                $bindpw = $rcmail->config->get('password_ldap_adminPW');
                break;
            case 'user':
            default:
                $binddn = $user_dn;
                $bindpw = $rcmail->decrypt($_SESSION['password']);
                break;
        }

        if (!ldap_bind($ds, $binddn, $bindpw)) {
            ldap_unbind($ds);
            return false;
        }
        $this->ds = $ds;
        $this->user_dn = $user_dn;

        return true;
    }

    static function substitute_vars($str)
    {
        $str = str_replace('%login', $_SESSION['username'], $str);
        $str = str_replace('%l', $_SESSION['username'], $str);

        $parts = explode('@', $_SESSION['username']);

        if (count($parts) == 2) {
            $dc = 'dc=' . strtr($parts[1], array('.' => ',dc='));

            $str = str_replace('%name', $parts[0], $str);
            $str = str_replace('%n', $parts[0], $str);
            $str = str_replace('%dc', $dc, $str);
            $str = str_replace('%domain', $parts[1], $str);
            $str = str_replace('%d', $parts[1], $str);
        }

        return $str;
    }
}
