<?php

/**
 * SQL Driver
 *
 * Driver for force password change what stored in SQL database
 *
 * @version 1.0
 * @author WAINLAKE <michael@wainlake.com>
 *
 */
class sql_driver
{
    function save($rcmail,$time)
    {
        $dbh = $rcmail->get_dbh();
        $res = $dbh->query('UPDATE vmail.mailbox SET passwordlastchange = ? WHERE username = ?', $time, $_SESSION['username']);
        if (!$dbh->is_error()) {
            return true;
        }
        return false;
    }

    function get($rcmail)
    {
        $dbh = $rcmail->get_dbh();
        $res = $dbh->query('SELECT passwordlastchange FROM vmail.mailbox WHERE username= ? ', $_SESSION['username']);

        if ($sql_arr = $dbh->fetch_array($res)) {
            return strtotime($sql_arr[0]);
        }

        return 0;
    }
}