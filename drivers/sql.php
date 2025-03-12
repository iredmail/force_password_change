<?php
/**
 * SQL Driver
 *
 * Driver for force password change what stored in SQL database
 *
 * NB: It need a column in user table to store the last changed date.
 * It's named 'passwordlastchange' in 'vmail.mailbox' on iRedmail.
 * You can manually modify below SQL command for your requirement.
 *
 * @version 1.0
 * @author WAINLAKE <michael@wainlake.com>
 *
 */
class sql_driver
{
    private $debug = false;

    function save($rcmail, $time)
    {
        $this->_debuglog("call sql_driver.save");
        $dbh = $rcmail->get_dbh();
        $res = $dbh->query('UPDATE vmail.mailbox SET passwordlastchange = NOW() WHERE username = ?', $_SESSION['username']);
        if (!$dbh->is_error()) {
            return true;
        }
        $this->_debuglog("write to sql ");
        $this->_debuglog($time);

        return false;
    }

    function get($rcmail)
    {
        $this->_debuglog("call sql_driver.get");
        $dbh = $rcmail->get_dbh();
        $res = $dbh->query('SELECT passwordlastchange FROM vmail.mailbox WHERE username= ? ', $_SESSION['username']);

        if ($sql_arr = $dbh->fetch_array($res)) {
            return strtotime($sql_arr[0]);
        }
        $this->_debuglog("get from sql ");
        $this->_debuglog($sql_arr);

        return 0;
    }

    private function _debuglog($data = null)
    {
        if ($this->debug && !is_null($data)) {
            rcube::write_log('error', "Plugin force_password_change Debug: " . print_r($data, true));;
        }
    }
}
