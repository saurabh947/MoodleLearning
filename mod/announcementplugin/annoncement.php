<?php
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
$hostname_annoncement = $CFG->dbhost;
$database_annoncement = $CFG->dbname;
$username_annoncement = $CFG->dbuser;
$password_annoncement = $CFG->dbpass;
$annoncement = mysql_pconnect($hostname_annoncement, $username_annoncement, $password_annoncement) or trigger_error(mysql_error(),E_USER_ERROR); 
?>