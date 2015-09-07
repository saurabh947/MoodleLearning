<?php
 require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
 mysql_connect($CFG->dbhost,$CFG->dbuser,$CFG->dbpass) or die(mysql_error()); 
 mysql_select_db($CFG->dbname) or die(mysql_error()); 
 
 echo "operation " .$_POST['oper'];
 if($_POST['oper']=='del')
 {
  	$sqlSelect="DELETE FROM `mdl_subjectmaster` WHERE id=" . $_POST['id'];
	$data = mysql_query($sqlSelect);
 }
 
 if($_POST['oper']=='add')
 {
	 	
  	  	echo "add..........";
 }
 
 if($_POST['oper']=='edit')
 {
  	echo "edit..........";
 }
 
 /*
 if($_POST['oper']=='add')
 {
    ... ... ...
 }
else if($_POST['oper']=='edit')
 {
    ... ... ...
 }
else if($_POST['oper']=='del')
 {
    ... ... ...
 }
  */
?>