<?php
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
$hostname_reg = $CFG->dbhost;
$database_reg = $CFG->dbname;
$username_reg = $CFG->dbuser;
$password_reg = $CFG->dbpass;
$reg = mysql_pconnect($hostname_reg, $username_reg, $password_reg) or trigger_error(mysql_error(),E_USER_ERROR); 
?>