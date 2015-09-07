<?php 
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
 mysql_connect($CFG->dbhost,$CFG->dbuser,$CFG->dbpass) or die(mysql_error()); 
 mysql_select_db($CFG->dbname) or die(mysql_error()); 
 
 
 $sqlSelect="SELECT * FROM mdl_vrsspl_subjectmaster";


// $data = mysql_query("SELECT * FROM event_table") 
 $data = mysql_query($sqlSelect) 
 or die(mysql_error()); 
 
 $total_rows = mysql_num_rows($data);
 
 $count = 1;
 
echo "[";


 while($info = mysql_fetch_array($data)) 
 { 
 		  
 	
 
	echo "{";
 	echo "\"id\":\"".$info['id']."\",\"subjectname\":\"".$info['subjectname']."\"";
	echo "}";
	
	//break;
	
	

	if($count < $total_rows)
		echo ",";
	
		$count++;	
 }

 
echo " ]";
 ?> 

