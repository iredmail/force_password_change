-----------------------------------------------------------------------
 Force password change plugin for Roundcube on iRedmail
 (of course you can migrate it to some others mail system :-) )
 -----------------------------------------------------------------------
 Plugin will automatically redirect to the password changing page if the 
 passoword is not changed over the user_defined time period.
 -----------------------------------------------------------------------
 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 @version @package_version@
 @author WAINLAKE <michael@wainlake.com>
 -----------------------------------------------------------------------

 1. Install
 2. Configuration
 3. Drivers

 1. Install
 ----------------
    * Place this plugin folder into plugins directory of Roundcube
    * Add force_password_change to $config['plugins'] in your Roundcube config

    NB: When downloading the plugin from bitbucket you will need to create a
    directory called force_password_change and place the files in there, ignoring the
    root directory in the downloaded archive


 2. Configuration
 ----------------

    Copy config.inc.php.dist to config.inc.php and set the options as described
    within the file.
    
    NB: It depends the "password" plugin, you must enable it first.

	
 3. Drivers
 ----------------
    It's only support below 3 type drivers for iRedmail mail server.

    3.1  Database (sql)
    -------------------
    It's for MySQL/MariaDB or PgSQL backend of iRedmail.


    3.2  LDAP (ldap)
    ----------------
    It's for LDAP backend of iRedmail.
    Requires PEAR::Net_LDAP2 package.


    3.3 LDAP - no PEAR (ldap_simple)
    -----------------------------------
    It's for LDAP backend of iRedmail.
    It uses directly PHP's ldap module functions without the Net_LDAP2 PEAR extension.
	
-----------------------------------------------------------------------
 Force password change RC插件，基于iRedmail
 -----------------------------------------------------------------------
 在启用本插件后，当用户登录RC网页邮件时，会检查用户最后一次修改密码
 时间，如超过系统定义周期，则会锁定所有操作强制跳转至密码修改页面。
 -----------------------------------------------------------------------

 1. 安装
 2. 配置

 1. 安装
 ----------------
    * 将插件程序目录复制到RC的插件目录
    * 在RC配置文件中，添加force_password_change到参数$config['plugins']以启用插件

    注意： 当从bitbucket发布站点下载时，请本地建一个名为force_password_change目录，将
	压缩包中的文件放入其中即可。


 2. 配置
 ----------------

    复制config.inc.php.dist为config.inc.php，然后根据文中说明修改参数即可。
    
    注意：本插件依赖password插件，请务必先启用。