<?php
 require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
 mysql_connect($CFG->dbhost,$CFG->dbuser,$CFG->dbpass) or die(mysql_error()); 
 mysql_select_db($CFG->dbname) or die(mysql_error()); 
 
// echo "operation " .$_POST['oper'] . "\n";
 //echo "id  ".$_POST['id'];
 if($_POST['oper']=='del')
 {
	 /* foreach ($_POST as $key => $value)
             echo "Field ".htmlspecialchars($key)." is ".htmlspecialchars($value)."<br>";*/
			 
  	$sqlSelect="DELETE FROM `mdl_vrsspl_subjectmaster` WHERE id=" . $_POST['id'];
	$data = mysql_query($sqlSelect);
	
 }
 
 if($_POST['oper']=='add')
 {
	 foreach ($_POST as $key => $value)
             echo "Field ".htmlspecialchars($key)." is ".htmlspecialchars($value)."<br>";
			 
	
	 $sqlSelect="INSERT INTO `mdl_vrsspl_subjectmaster`(`subjectname`) VALUES ('" . $_POST['subjectname']."')";
	 mysql_query($sqlSelect);
	
	
 }
 
 if($_POST['oper']=='edit')
 {

	 $sqlSelect="UPDATE `mdl_vrsspl_subjectmaster` SET `subjectname`='".$_POST['subjectname']."' WHERE id=".$_POST['id'];
	$data = mysql_query($sqlSelect);
  	
 }
 
 
?>