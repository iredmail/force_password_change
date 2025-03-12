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

# Roundcube插件：定期强制修改邮箱密码。

本插件当前默认用于iRedmail邮件系统，如有需要用于其他邮件系统，可参考
以下内容自行修改你的Roundcube设置使用。

## 工作原理

每次用户通过Roundcube修改邮箱密码，系统都会在SQL或LDAP记录本次修改密码时间。

* SQL版本，包括MySQL、MariaDB、PostgreSQL：
    * 在数据库 `vmail`中`mailbox.password`栏位记录用户密码；
    * 在栏位 `mailbox.passwordlastchange`记录用户修改密码时间。
* LDAP版本，包括OpenLDAP or OpenBSD ldapd(8) 服务器：
    * 在user对象中属性栏位 `userPassword` 记录用户密码；
    * 在属性栏位 `shadowLastChange`用户修改密码时间 (距1970/6/1的天数)。

当用户登录Roundcube网页邮箱时，会检查用户最后一次修改密码时间，如超过系统设定
周期(通过插件参数文件 `config.inc.php`中参数`force_password_change_interval`定
义)，则会中断所有操作强制跳转至密码修改页面。

## 安装配置

### 安装

* 将插件程序目录复制到Roundcube插件目录`plugins/`。
* 修改Roundcube配置文件`config/config.inc.php`中参数`$config['plugins'] =`，添加 `'force_password_change'` 启用插件。

    __提醒__: 本插件需依赖于系统自带 `password` 插件，请务必同时启用。

### 配置

复制`config.inc.php.dist`为`config.inc.php`，然后根据需要修改参数即可。


### 插件驱动

插件驱动用于查询及更新密码最新更新日期，目前仅支持以下3种。

#### 数据库 (sql)

用户MySQL，MariaDB，PostgreSQL 后台。

#### LDAP (ldap)

用于OpenLDAP或OpenBSD ldapd(8) 服务。

本驱动依赖PEAR::Net_LDAP2包。

#### LDAP (ldap_simple) - 不依赖PEAR包

用于OpenLDAP或OpenBSD ldapd(8) 服务。

使用PHP的ldap模块功能，不需要依赖Net_LDAP2 PEAR扩展。

#### 调试

如果要针对不同驱动进行调试，可以修改drivers目录下对应驱动文件以下参数，将

` private $debug = false; `

改为：

` private $debug = true; `

然后你就可以在服务的日志中看到以`Plugin force_password_change Debug:`开头的调试内容。
