<?php 
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
 mysql_connect($CFG->dbhost,$CFG->dbuser,$CFG->dbpass) or die(mysql_error()); 
 mysql_select_db($CFG->dbname) or die(mysql_error()); 
 
 
 $sqlSelect="SELECT id, headline,
 DATE_FORMAT(STR_TO_DATE( news_dt, '%d-%m-%Y'), '%d-%m-%Y')
 as news_dt,url,category,archived, date_format(from_unixtime(archived_dt),'%m-%d-%Y') 
 as archived_dt  FROM mdl_vrssnews order by STR_TO_DATE( news_dt, '%d-%m-%Y')  desc";

// $data = mysql_query("SELECT * FROM event_table") 
 $data = mysql_query($sqlSelect) 
 or die(mysql_error()); 
 
 $total_rows = mysql_num_rows($data);
 
 $count = 1;
 
echo "[";


 while($info = mysql_fetch_array($data)) 
 { 
 	
	echo "{";
 	echo "\"id\":\"".$info['id']."\",\"headline\":\"".$info['headline']."\",\"news_dt\":\"".$info['news_dt']."\",\"url\":\"".$info['url']."\",";
	echo "\"category\":\"".$info['category']."\",\"archived\":\"".$info['archived']."\"";
	
		
	echo "}";
	
	//break;
	
	

	if($count < $total_rows)
		echo ",";
	
		$count++;	
 }

 
echo " ]";
 ?> 

