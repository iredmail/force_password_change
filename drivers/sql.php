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
    function save($rcmail,$time)
    {
        _debuglog("call sql_driver.save");
        $dbh = $rcmail->get_dbh();
        $res = $dbh->query('UPDATE vmail.mailbox SET passwordlastchange = ? WHERE username = ?', $time, $_SESSION['username']);
        if (!$dbh->is_error()) {
            return true;
        }
        _debuglog("write to sql ");
        _debuglog($time);

        return false;
    }

    function get($rcmail)
    {
        _debuglog("call sql_driver.get");
        $dbh = $rcmail->get_dbh();
        $res = $dbh->query('SELECT passwordlastchange FROM vmail.mailbox WHERE username= ? ', $_SESSION['username']);

        if ($sql_arr = $dbh->fetch_array($res)) {
            return strtotime($sql_arr[0]);
        }
        _debuglog("get from sql ");
        _debuglog($sql_arr);

        return 0;
    }
}
