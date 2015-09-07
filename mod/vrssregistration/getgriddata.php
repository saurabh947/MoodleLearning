<?php 
 // Connects to your Database 
 require_once('reg_config.php');
 
 mysql_connect($hostname_reg,$username_reg,$password_reg) or die(mysql_error()); 
 mysql_select_db($database_reg) or die(mysql_error()); 
 
 $sqlSelect="SELECT r.id as reg_id,date_format(from_unixtime(registration_date),'%D %b %Y')as reg_date, Concat(r.title,' ', first_name,' ', middle_name,' ' , last_name) as student_name ,experience_year,experience_month,experience_type,
 phone_no,email_id, c.college_name,center FROM mdl_vrssstudentregistration as r inner join mdl_vrsscollege as c on r.college_id= c.id 
 order by registration_date";


// $data = mysql_query("SELECT * FROM event_table") 
 $data = mysql_query($sqlSelect) 
 or die(mysql_error()); 
 
 $total_rows = mysql_num_rows($data);
 
 $count = 1;
 
echo "[";


 while($info = mysql_fetch_array($data)) 
 { 
 //get courses
	$innerQuery="select course_name from mdl_vrsscourse  as c inner join mdl_vrssstudentcourse as sc on c.id=sc.course_id where sc.registration_id=" . $info['reg_id'];
	 $innerdata = mysql_query($innerQuery) 
	 or die(mysql_error());
	 $total_innerrows=mysql_num_rows($innerdata);
	 $courselist="";
	 $innercount=1;
	 if(mysql_num_rows($innerdata) != 0)
	 {
		 while($course = mysql_fetch_array($innerdata)) 
		 {			 
			 $courselist=$courselist . (empty($course['course_name'])?"":($course['course_name']));
			 if($innercount<$total_innerrows)
			 	$courselist =$courselist . ", ";
				
			$innercount++;
		 }		 
	 }
	//qulaification
	$innerQuery="select  qualification, branch from mdl_vrssstudentqualification as sq inner join  mdl_vrssqualificationbranch  as qb 
				on sq.qualificationbranch_id=qb.id inner join mdl_vrssqualification  as q on qb.qualification_id= q.id
				inner join mdl_vrssbranch  as b on qb.branch_id=b.id where sq.registration_id=" . $info['reg_id'];
	 $innerdata = mysql_query($innerQuery) 
	 or die(mysql_error());
	 $total_innerrows=mysql_num_rows($innerdata);
	 $qualification="";
	 $innercount=1;
	 if(mysql_num_rows($innerdata) != 0)
	 {
		 while($quali = mysql_fetch_array($innerdata)) 
		 {	
		 
		 		 
			 $qualification=$qualification . (empty($quali['qualification'])?"":($quali['qualification']. " in " . $quali['branch']));
			 if($innercount<$total_innerrows)
			 	$qualification =$qualification . ", ";
				
			$innercount++;
		 }		 
	 }
	  
 
 
 	$exp=($info['experience_year']!=0?$info['experience_year'] . ($info['experience_year']==1?" Year ": " Years ") . 
	($info['experience_month']!=0?$info['experience_month'] . ($info['experience_month']==1? " Month" : " Months" ) : ""):"");
 
	echo "{";
 //	echo "\"student_name\":\"".$info['student_name']."\",\"experience_year\":\"".$info['experience_year']."\",\"experience_month\":\"".$info['experience_month']."\",";
 	echo "\"reg_id\":\"".$info['reg_id']."\",\"student_name\":\"".$info['student_name']."\",\"experience\":\"".$exp."\",\"courses\":\"".$courselist."\",";
	echo "\"reg_date\":\"".$info['reg_date']."\",\"qualification\":\"".$qualification."\",";
	echo "\"center\":\"".$info['center']."\",";
	
  	echo "\"experience_type\":\"".$info['experience_type']."\",\"phone_no\":\"".$info['phone_no']."\",\"email_id\":\"".$info['email_id']."\",\"college_name\":\"".$info['college_name']."\"";
  
	echo "}";
	
	//break;
	
	

	if($count < $total_rows)
		echo ",";
	
		$count++;	
 }

 
echo " ]";
 ?> 

