<?php
/**
 * Ldap Driver
 *
 * Driver for force password change what stored in LDAP database
 *
 * NB: It need a attribute named 'shadowLastChange' in user entry 
 * to store the last changed date. 
 * For some mail solution(eg. iRedmail) provided yet.
 *
 * @version 1.0
 * @author WAINLAKE <michael@wainlake.com>
 *
 */
class ldap_driver
{
    private $debug = false;

    function save($rcmail, $time)
    {
        require_once 'Net/LDAP2.php';
        $this->_debuglog("call ldap_driver.save");

        $userDN = $this->substitute_vars($rcmail->config->get('password_ldap_userDN_mask'));

        if (empty($userDN)) {
            return false;
        }

        switch ($rcmail->config->get('password_ldap_method')) {
            case 'admin':
                $binddn = $rcmail->config->get('password_ldap_adminDN');
                $bindpw = $rcmail->config->get('password_ldap_adminPW');
                break;
            case 'user':
            default:
                $binddn = $userDN;
                $bindpw = $rcmail->decrypt($_SESSION['password']);;
                break;
        }

        $ldapConfig = array(
            'binddn' => $binddn,
            'bindpw' => $bindpw,
            'basedn' => $rcmail->config->get('password_ldap_basedn'),
            'host' => $rcmail->config->get('password_ldap_host'),
            'port' => $rcmail->config->get('password_ldap_port'),
            'starttls' => $rcmail->config->get('password_ldap_starttls'),
            'version' => $rcmail->config->get('password_ldap_version'),
        );

        $ldap = Net_LDAP2::connect($ldapConfig);
        if (is_a($ldap, 'PEAR_Error')) {
            return false;
        }
        $this->_debuglog("ldap connect ready");

        $userEntry = $ldap->getEntry($userDN);
        if (Net_LDAP2::isError($userEntry)) {
            return false;
        }

        $entry['shadowLastChange'] = (int)(strtotime($time) / 86400);
        if (!$userEntry->replace($entry)) {
            return false;
        }

        if (Net_LDAP2::isError($userEntry->update())) {
            $ldap->done();
            return false;
        }

        $this->_debuglog("write to ldap ");
        $this->_debuglog($entry);

        return true;

    }

    function get($rcmail)
    {
        require_once 'Net/LDAP2.php';
        $this->_debuglog("call ldap_driver.get");

        $userDN = $this->substitute_vars($rcmail->config->get('password_ldap_userDN_mask'));

        if (empty($userDN)) {
            return false;
        }

        switch ($rcmail->config->get('password_ldap_method')) {
            case 'admin':
                $binddn = $rcmail->config->get('password_ldap_adminDN');
                $bindpw = $rcmail->config->get('password_ldap_adminPW');
                break;
            case 'user':
            default:
                $binddn = $userDN;
                $bindpw = $rcmail->decrypt($_SESSION['password']);;
                break;
        }

        $ldapConfig = array(
            'binddn' => $binddn,
            'bindpw' => $bindpw,
            'basedn' => $rcmail->config->get('password_ldap_basedn'),
            'host' => $rcmail->config->get('password_ldap_host'),
            'port' => $rcmail->config->get('password_ldap_port'),
            'starttls' => $rcmail->config->get('password_ldap_starttls'),
            'version' => $rcmail->config->get('password_ldap_version'),
        );

        $ldap = Net_LDAP2::connect($ldapConfig);
        if (is_a($ldap, 'PEAR_Error')) {
            return false;
        }
        $this->_debuglog("ldap connect ready");

        $filter = '(mail=' . $_SESSION['username'] . '*)';
        $options = array(
            'scope' => 'sub',
            'attributes' => array('shadowlastchange'),
        );

        $result = $ldap->search($base, $filter, $options);

        if (is_a($result, 'PEAR_Error') || ($result->count() != 1)) {
            return false;
        }

        $userEntry = $result->current();

        $lastchange = 0;
        if ($userEntry->exists('shadowLastChange')) {
            $ldap->done();
            $lastchange = $userEntry->getValue('shadowLastChange') * 86400;
        }

        $this->_debuglog("get from ldap ");
        $this->_debuglog($userEntry);

        return $lastchange;
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

    private function _debuglog($data = null)
    {
        if ($this->debug && !is_null($data)) {
            error_log("Plugin force_password_change Debug: " . print_r($data,true));            ;
        }
    }
}
