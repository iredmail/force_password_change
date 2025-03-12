All credit goes to [@wainlake](https://bitbucket.org/wainlake/force_password_change). Thanks to @ly020044 for the patches.

# Roundcube plugin: Force password change.

This plugin is designed for Roundcube webmail deployed by [iRedMail](https://www.iredmail.org).

## License

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

## How it works

Each time user changed password, Roundcube will update user's password and
the last password change date in SQL database or LDAP.

* For MySQL, MariaDB, PostgreSQL backends: Roundcube is configured to:
    * update new password in SQL database `vmail`, column `mailbox.password`.
    * update password change date in column `mailbox.passwordlastchange`.
* For OpenLDAP or OpenBSD ldapd(8) servers: Roundcube is configured to:
    * update new password in attribute `userPassword` of user object
    * update password change date in attribute `shadowLastChange` (days since
      Jun 1, 1970).

Each time user login to Roundcube webmail, Roundcube will query the password
last change date, if the password hasn't been changed for `90` days
(configurable in plugin config file `config.inc.php`, parameter
`force_password_change_interval`), Roundcube will __ALWAYS__ redirect user to
`Password` page (offered by official Roundcube plugin `password`) until user
changed the password.

## Install and Configuration

### Install

* Copy this plugin folder to the `plugins/` directory inside Roundcube.
* Enable plugin `force_password_change` in Roundcube config file
  `config/config.inc.php`, parameter `$config['plugins'] =`.

__WARNING__: This plugin relies on official `password` plugin, so please make
sure it's enabled too.

### Configuration

Copy `config.inc.php.dist` to `config.inc.php`, update `config.inc.php` to
match your needs.

### Password Drivers

Password drivers are used to query password last change date. Currently, only 3
drivers are supported.

#### Database (sql)

For MySQL, MariaDB, PostgreSQL backends.

#### LDAP (ldap)

For OpenLDAP or OpenBSD ldapd(8) servers.

This driver requires PEAR::Net_LDAP2 package.

#### LDAP (ldap_simple) - no PEAR package required

For OpenLDAP or OpenBSD ldapd(8) servers.

It uses PHP's ldap module functions without the Net_LDAP2 PEAR extension.

#### DEBUG

If you want to debug the dirver, please change 

` private $debug = false; `

to

` private $debug = true; `

in the source file what under drivers folder, and you can find the debug 
contents start with `Plugin force_password_change Debug:` what be showed in your log.
