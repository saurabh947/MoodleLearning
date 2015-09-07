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
			 
  	$sqlSelect="DELETE FROM mdl_vrssnews WHERE id=" . $_POST['id'];
	$data = mysql_query($sqlSelect);
	
 }
 
 if($_POST['oper']=='add')
 {
	 $archived=( $_POST['archived']=="on"?"True":"False");
	 echo $archived . "arch";
	 /*foreach ($_POST as $key => $value)
             echo "Field ".htmlspecialchars($key)." is ".htmlspecialchars($value)."<br>";*/
		//'%m-%d-%Y %h'
		$newsdate= mktime(0, 0, 0, date("m",$_POST["news_dt"])  , date("d",$_POST['news_dt']), date("Y",$_POST['news_dt']));
		//$newsdate = date_format($_POST['news_dt'] ,'%m-%d-%Y %h');
		echo "day" . date("d",$_POST["news_dt"]);
		echo "month" . date("m",$_POST["news_dt"]);
		echo "year" . date("Y",$_POST["news_dt"]);
		echo $newsdate;
	
	$sqlSelect="INSERT INTO mdl_vrssnews(headline,news_dt,url, category, archived) VALUES
	('" . $_POST['headline']."',  '" .  $_POST['news_dt'] . "' , '" . $_POST['url']."','" . $_POST['category']."', 'False')";
	 //mysql_query($sqlSelect);
	echo $sqlSelect;
	return mysql_query($sqlSelect);
			
				
 }
 
 if($_POST['oper']=='edit')
 {
	 $archived=( $_POST['archived']=="on"?"True":"False");
	 if($archived=="True"){
		$sqlSelect="UPDATE mdl_vrssnews SET headline='".$_POST['headline']."',news_dt='" .  $_POST['news_dt'] . "' ,
		url= '" . $_POST['url']."',category= '" . $_POST['category']. "', archived='" . $archived . "' ,
		archived_dt= UNIX_TIMESTAMP(NOW())  WHERE id=".$_POST['id'];
		echo "true" . $sqlSelect;
		$data = mysql_query($sqlSelect);		 
	 }else if($archived=="False"){
		$sqlSelect="UPDATE mdl_vrssnews SET headline='".$_POST['headline']."',news_dt='" .  $_POST['news_dt'] . "' ,
		url= '" . $_POST['url']."',category= '" . $_POST['category']. "', archived='" . $archived . "' ,
		archived_dt= '' WHERE id=".$_POST['id'];
		echo "false" . $sqlSelect;
		$data = mysql_query($sqlSelect);
		
//		$data = mysql_query($sqlSelect);		 
	 }
	 
	  

  	
 }
 
 
?>