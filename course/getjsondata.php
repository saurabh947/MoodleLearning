<?php
require_once('../config.php');

$host=$CFG->dbhost;
$dbname=$CFG->dbname;
$username=$CFG->dbuser;
$password=$CFG->dbpass;

// Connects to your Database 
 if((isset($_POST['courseid'])) && ($_POST['type']=="subjectcount")) // for series chart
{
		 mysql_connect($host,$username,$password) or die(mysql_error()); 
		 mysql_select_db($dbname) or die(mysql_error()); 
		
		
	
		 $sqlSelect="SELECT count(`subjectid`) as 'noofsubject' FROM mdl_vrsspl_course_subject_info WHERE categoryid= ". $_POST['courseid'] ;
		 //echo $sqlSelect;
	
		
		 $data = mysql_query($sqlSelect) 
		 or die(mysql_error()); 
		 
		 $total_rows = mysql_num_rows($data);
		 
		 
		 
		echo "[ ";
		 while($info = mysql_fetch_array($data)) 
		 { 
				
		
			
			echo "{";
			
				echo '"subjectcount":'.$info['noofsubject'];
			
		
		
			echo "}";
			
		
			
			
		
			
		 }
		
		 
		echo " ]";
}


 if(isset($_POST['courseid']) && $_POST['type']=="addcourse") 
{
		 mysql_connect($host,$username,$password) or die(mysql_error()); 
		 mysql_select_db($dbname) or die(mysql_error()); 
		
		
	
		 $sqlSelect="DELETE FROM `mdl_vrsspl_course_subject_info` WHERE `categoryid`=". $_POST['courseid'] ;
		 //echo $sqlSelect;
	
		
		 $data = mysql_query($sqlSelect) 
		 or die(mysql_error()); 
		 
		 
		 
		foreach($_POST['courses'] as $course)
		{
			 $sqlSelect="INSERT INTO mdl_vrsspl_course_subject_info(categoryid, subjectid) 
			 VALUES (" .$_POST['courseid'] . "," . $course . ")";
			  $data = mysql_query($sqlSelect) 
		 or die(mysql_error()); 
		} 
		 
		
		// $total_rows = mysql_num_rows($data);
		 
		 
		 
		
				
		
			echo "[";			
			echo "{";
				echo '"result":"success"';
			echo "}";
			echo " ]";
}





  if(isset($_POST['courseid']) && $_POST['type']=="courecreate") 
{
		 mysql_connect($host,$username,$password) or die(mysql_error()); 
		 mysql_select_db($dbname) or die(mysql_error()); 
		
		
	
		 $sqlSelect="select id,subjectname,(SELECT c.subjectid as subjectid FROM mdl_vrsspl_course_subject_info as c where c.categoryid=". $_POST['courseid'] ." and c.subjectid=sub.id) as subjectid FROM mdl_vrsspl_subjectmaster as sub order by subjectid desc" ;
		 //echo $sqlSelect;
	
		
		 $data = mysql_query($sqlSelect) 
		 or die(mysql_error()); 
		 
		 
		
		 $total_rows = mysql_num_rows($data);
		 
		 $count = 1;
		 
		echo "[ ";
		 while($info = mysql_fetch_array($data)) 
		 { 
				
		
			
			echo "{";
			
			echo "\"id\":\"".$info['id']."\",";
			echo "\"subjectname\":\"".$info['subjectname']."\",";
			echo "\"subjectid\":\"".$info['subjectid']."\"";
		
			echo "}";
			
		
			
			
		
			if($count < $total_rows)
				echo ",";
			
				$count++;	
		 }
		
		 
		echo " ]";
		 
		 
		 
		
				
		
			
}

  if(isset($_POST['courseid']) && $_POST['type']=="batchsubjectcreate") 
{
		 mysql_connect($host,$username,$password) or die(mysql_error()); 
		 mysql_select_db($dbname) or die(mysql_error()); 
		
		
		$sqlSelect="SELECT count(id) as id FROM mdl_vrsspl_batch_subject_info WHERE batchid=".$_POST['batchid'];
		$data = mysql_query($sqlSelect) or die(mysql_error());

		$info = mysql_fetch_array($data);
		
		if($info['id']==0)
		{
	
			 //$sqlSelect="SELECT subjectid FROM mdl_vrsspl_course_subject_info WHERE categoryid=" . $_POST['courseid'];
			 //echo $sqlSelect;
			$sqlSelect="SELECT subjectid,subjectname,startdate FROM mdl_vrsspl_course_subject_info as c 
						inner join mdl_vrsspl_subjectmaster as s on c.subjectid=s.id 
						inner join mdl_vrsspl_batch_master as b on b.categoryid = ".$_POST['batchid']."
						WHERE c.categoryid=" . $_POST['courseid'];
			
			 $data = mysql_query($sqlSelect) 
			 or die(mysql_error()); 
			 
			 while($info = mysql_fetch_array($data)) 
			 {
				$sqlSelect="INSERT INTO `mdl_vrsspl_batch_subject_info`(`batchid`, `subjectid`) VALUES (".$_POST['batchid'].",".$info['subjectid'].")";
				mysql_query($sqlSelect) or die(mysql_error());
				
				$shortname=$info['subjectname'].$_POST['batchid'];
				//echo "shortname" . $shortname;
				
				/*$sqlSelect="INSERT INTO `mdl_course`(`category`, `fullname`, `shortname` , `summaryformat`, `format`, `showgrades`, `newsitems`, `startdate`, `numsections`, `marker`, `maxbytes`, `legacyfiles`, `showreports`, `visible`, `visibleold`, `hiddensections`, `groupmode`, `groupmodeforce`, `defaultgroupingid`, `timecreated`, `timemodified`, `requested`, `enablecompletion`, `completionstartonenrol`, `completionnotify`, `coursedisplay`) 
VALUES (".$_POST['batchid'].",'".$info['subjectname']."','".$shortname."',1,'weeks',1,5,".$info['startdate'].",10,0,2097152,0,0,1,1,0,0,0,0,UNIX_TIMESTAMP(NOW()),UNIX_TIMESTAMP(NOW()),0,0,0,0,0)";*/

				$sqlSelect="INSERT INTO `mdl_course`(`category`, `fullname`, `shortname` , `summaryformat`, `format`, `showgrades`,`sectioncache`,`modinfo`, `newsitems`, `startdate`, `numsections`, `marker`, `maxbytes`, `legacyfiles`, `showreports`, `visible`, `visibleold`, `hiddensections`, `groupmode`, `groupmodeforce`, `defaultgroupingid`, `timecreated`, `timemodified`, `requested`, `enablecompletion`, `completionstartonenrol`, `completionnotify`, `coursedisplay`)  
VALUES (".$_POST['batchid'].",'".$info['subjectname']."','".$shortname."',1,'weeks',1,NULL,NULL,5,".$info['startdate'].",10,0,2097152,0,0,1,1,0,0,0,0,UNIX_TIMESTAMP(NOW()),UNIX_TIMESTAMP(NOW()),0,0,0,0,0)";

			//echo $sqlSelect;
				mysql_query($sqlSelect) or die(mysql_error());
				
			 }
			
			echo "[ ";
			
				echo "{";
						echo "\"success\":\"true\"";
						//echo "success":"true";
				echo "}";
	
			echo " ]";
		}
		else
		{
			echo "[ ";
			
				echo "{";
						echo "\"success\":\"false\"";
						//echo "success:false";
				echo "}";
		
			 
			echo " ]";
		}
}







?>