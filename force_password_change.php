<?php

/**
 * Force password change plugin for Roundcube what be integrated
 * into iRedmail(www.iredmail.org)
 *
 * @version 1.0
 * @author WAINLAKE <michael@wainlake.com>
 *
 * check date of user password last change and redirect to password
 * change page if user didn't change password in special days.
 *
 * Configuration (see config.inc.php.dist)
 **/
class force_password_change extends rcube_plugin
{
    // all task excluding 'logout'
    public $task = '?(?!logout).*';
    public $noframe = true;
    private $newuser = false;
    private $driver;

    function init()
    {
        $rcmail = rcmail::get_instance();
        $this->_load_password_config(false);

        if ($rcmail->config->get('force_password_change', false)) {
            $this->driver = $this->_get_dirver();
            $this->add_hook('user_create', array($this, 'user_create'));
            $this->add_hook('login_after', array($this, 'login_after'));

            if ($_SESSION['plugin.forcepasswordchange'] && $rcmail::get_instance()->task <> 'settings') {
                $this->register_action('plugin.forcepwdchg', array($this, 'redirect'));
                $this->add_hook('render_page', array($this, 'render_page'));
            }
            //elseif (strpos($rcmail->action, 'plugin.password') === 0) {
            // [TODO] compare new password with old password
            //}
            elseif ($_SESSION['plugin.forcepasswordchange'] && strpos($rcmail->action, 'plugin.password-save') === 0) {
                $time = $this->_convertdate();
                $this->_load_password_config(true);
                if ($this->driver->save($rcmail, $time)) {
                    $_SESSION['plugin.forcepwdchg.lastchange'] = strtotime($time);
                    $_SESSION['plugin.forcepasswordchange'] = false;
                }
            }
        }
    }

    function render_page($args)
    {
        $rcmail = rcmail::get_instance();
        $this->_load_password_config(false);
        $this->add_texts('localization');

        $table = new html_table(array('cols' => 1));

        $policy = $this->gettext('policy');
        if (strpos($policy, '%d') !== false) {
            $policy = str_replace('%d', $rcmail->config->get('force_password_change_interval'), $policy);
        }
        $table->add('title', $policy);
        $table->add('', '&nbsp');

        $lastchange = $_SESSION['plugin.forcepwdchg.lastchange'];
        if ($lastchange == 0) $lastchange = 1;
        $lastchange = $this->_convertdate($lastchange) . ' (' . $rcmail->config->get('timezone') . ')';
        $table->add('title', $this->gettext('lastchange').$lastchange);;

        $rcmail->output->add_footer(html::tag(
            'div', array(
                'id' => 'forcepwdchg'
            ),
            $table->show() .
            html::br('', '') .
            html::p('hint', rcube::Q($this->gettext('hint')) .
                html::a(array('href' => $rcmail->url(array('task' => 'settings',
                            'action' => 'plugin.password')),
                        'class' => 'button'),
                    rcube::Q($this->gettext('go'))
                ))));

        $title = rcube::JQ($this->gettext('title'));
        $script = "
        $('#forcepwdchg').show()
          .dialog({
                modal:true,
                resizable:false,
                closeOnEscape:false,
                width:450,
                title:'$title',
              close: function() {
                 location.reload(true);;
             }
          });
        ";

        $rcmail->output->add_script($script, 'docready');
        $this->include_stylesheet('forcepwdchg.css');
    }

    function redirect()
    {
        rcmail::get_instance()->output->send();
    }

    function user_create($args)
    {
        $this->newuser = true;
        return $args;
    }

    function login_after($args)
    {
        $rcmail = rcmail::get_instance();

        $this->_load_password_config(false);
        $interval = $rcmail->config->get('force_password_change_interval', 0);
        $_SESSION['plugin.forcepwdchg.interval'] = $interval;

        $this->_load_password_config(true);

        $lastchange = $this->driver->get($rcmail);
        $_SESSION['plugin.forcepwdchg.lastchange'] = $lastchange;

        $_SESSION['plugin.forcepasswordchange'] = true;
        if (($lastchange + $interval * 86440) >= time()) {
            $_SESSION['plugin.forcepasswordchange'] = false;
        }

        if ($this->newuser) {
            $args['_task'] = 'settings';
            $args['_action'] = 'plugin.password';
            $args['_first'] = 'true';
        }

        return $args;
    }

    private function _get_dirver()
    {
        $this->_load_password_config(true);
        $driver = rcmail::get_instance()->config->get('password_driver', 'sql');
        $class = "{$driver}_driver";
        $file = $this->home . "/drivers/$driver.php";

        if (!file_exists($file)) {
            rcube::raise_error(array(
                'code' => 600,
                'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Force password change plugin: Unable to open driver file ($file)"
            ), true, true);
            return $this->gettext('errortitle');
        }

        include_once $file;

        if (!class_exists($class, false) || !method_exists($class, 'get')) {
            rcube::raise_error(array(
                'code' => 600,
                'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Force password change plugin: Broken driver $driver"
            ), true, true);
            return $this->gettext('errortitle');
        }

        return new $class;
    }

    public function _load_password_config($switch = false)
    {
        if ($switch) {
            $file = $this->home . '/../password/config.inc.php';
            if (!file_exists($file)) {
                rcube::raise_error(array(
                    'code' => 600,
                    'type' => 'php',
                    'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Force password change plugin: Unable to find the config file for plugin password($file)"
                ), true, true);
                return $this->gettext('errortitle');
            } else {
                $this->load_config($file);
            }
        } else {
            $this->load_config();
        }
    }

    private function _convertdate($date = null)
    {
        $rcmail = rcmail::get_instance();
        $this->_load_password_config(false);
        $date_format = $rcmail->config->get('date_format', 'Y-m-d');
        $time_format = $rcmail->config->get('time_format', 'H:i');
        $timezone = $date_format . ' ' . $time_format;

        if (is_null($date)) {
            return $rcmail->format_date(time() - date('Z'), $timezone, true);
        } else {
            return $rcmail->format_date($date + date('Z'), $timezone, true);
        }

    }

}
