<?php

require_once('reg_config.php');
//weekly registration
if(isset($_GET['type']) && $_GET['type']==1) 
{
	//$firstday=date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('y')));
//	$monthdays= cal_days_in_month(CAL_GREGORIAN, date('m'), date('y'));	 	
//	$lastdaymonth=date('Y-m-d', mktime(0, 0, 0, date('m'), $monthdays, date('y')));
//
//
// 	$first_day_of_week = date('d-m-Y', strtotime('Last Monday', mktime(0, 0, 0, date('m'), 1, date('y'))));
//	$last_day_of_week = date('d-m-Y', strtotime('Next Sunday', mktime(0, 0, 0, date('m'), 1, date('y'))));	
//	$first_day_of_week=$firstday;
//
//
//	$count=1;
//	while($count<$monthdays)
//	{
//		
//	}
//
//	echo "first " . $first_day_of_week . "</br>";
//	echo "Last " . $last_day_of_week . "</br>";
//	
//	$next_firstday = date('d-m-Y', strtotime("+1 day", strtotime($last_day_of_week)));
//echo "next week 1 " . $next_firstday;
//			
//		$date = "2010-08-12";
//		$d = date_parse($date);
//		echo "month" . $d["month"];




	 //consider daily monthly report
	 $monthdays= cal_days_in_month(CAL_GREGORIAN, date('m'), date('y'));	 
	 //to get 1st day of current month
	 $firstday= date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('y')));
	 $lastday= date('Y-m-d', mktime(0, 0, 0, date('m'), $monthdays, date('y')));


		$first_week_no = date("W", mktime(0, 0, 0, date('m'), 1, date('y')));
		$last_week_no = date("W", mktime(0, 0, 0, date('m'), $monthdays, date('y')));

		mysql_connect($hostname_reg,$username_reg,$password_reg) or die(mysql_error()); 
 mysql_select_db($database_reg) or die(mysql_error()); 

	$sqlquery="SELECT count(id) as regcount,week(date_format(from_unixtime(registration_date),'%Y-%m-%d')) as reg_week from mdl_vrssstudentregistration
			where date_format(from_unixtime(registration_date),'%Y-%m-%d') between '". $firstday . "' and '" . $lastday . "' 
			group by week(date_format(from_unixtime(registration_date),'%Y-%m-%d'))";
			
			
	$data = mysql_query($sqlquery) 
	or die(mysql_error());
	$regids="[";
	$courseids="[";
	$count=1;
	while($res = mysql_fetch_array($data)) 
	 {	
		if($res['reg_week']!=$first_week_no)	
		{
			while($res['reg_week']!=$first_week_no)
			{	
				$regids  =$regids . 0;	
				$courseids = $courseids . '"' . $count . '"';
				$first_week_no++;
				$count++;
				if($res['reg_week']>=$first_week_no)
				{
					$regids  =$regids . ",";
					$courseids = $courseids . ",";						
					
				}
			}
		}
		$regids  =$regids . '"' .$res['regcount']. '"';
		$courseids = $courseids .  '"' . $count . '"';			
				
		$first_week_no++;	
		$count++;
		if($first_week_no <= $last_week_no)
		{
			$regids  =$regids . ",";
			$courseids = $courseids . ",";		
		}
	}
	if($first_week_no<=$last_week_no)	
	{
		while($first_week_no<=$last_week_no)
		{	
			$regids  =$regids . 0;	
			$courseids = $courseids . '"' . $count . '"';
			$first_week_no++;
			$count++;
			
			if($first_week_no<=$last_week_no)
			{
				$regids  =$regids . ",";
				$courseids = $courseids . ",";				
			}
		}
	}
			
	$regids  =$regids . "]";
	$courseids = $courseids . "]";
	echo "[" . $regids . "," . $courseids . "]";
}


//Current month registrations
if(isset($_GET['type']) && $_GET['type']==2) 
{
	mysql_connect($hostname_reg,$username_reg,$password_reg) or die(mysql_error()); 
 mysql_select_db($database_reg) or die(mysql_error()); 
 
	$firstday= date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('y')));
	$monthdays= cal_days_in_month(CAL_GREGORIAN, date('m'), date('y'));
	$count=1;
	$regids="[";
	$courseids="[";
	
	//echo $monthdays;
	while($count<=$monthdays)
	{
		$sqlquery="select count(id) as regcount, date_format(from_unixtime(registration_date),'%e %b') as reg_date   
		from mdl_vrssstudentregistration 	where 
		date_format(from_unixtime(registration_date),'%Y-%m-%d')='" . $firstday . "' group by 
		date_format(from_unixtime(registration_date),'%Y-%m-%d') order by date_format(from_unixtime(registration_date),'%Y-%m-%d')";
	
		$data = mysql_query($sqlquery) 
	 	or die(mysql_error());

		//$showdate=  date("m/d/y", $firstday);
		//date("M j", mktime(0, 0, 0, $firstday('m'), 1,  $firstday('y')));
		//echo date('M j', mktime(0, 0, 0, date('m'), 1, date('y')));
		//echo $showdate;
		$res = mysql_fetch_array($data);		
		$regids  =$regids . '"'.(empty($res['regcount'])?0:$res['regcount']).'"';
		$firstday1= date('d', mktime(0, 0, 0, date('m'), $count));
		$courseids = $courseids . '"' . $firstday1 . '"';	
		
		$count++;
		if($count <=$monthdays)
		{
			$regids  =$regids . ",";
			$courseids = $courseids . ",";
			
			//inc date
			$firstday= date('Y-m-d', mktime(0, 0, 0, date('m'), $count, date('y')));			
		}
				

	}
	$regids  =$regids . "]";
	$courseids = $courseids . "]";
	echo "[" . $regids . "," . $courseids . "]";
}

//yearly/current year registration
if(isset($_GET['type']) && $_GET['type']==3) 
{
mysql_connect($hostname_reg,$username_reg,$password_reg) or die(mysql_error()); 
 mysql_select_db($database_reg) or die(mysql_error()); 
 
	//get current year
	$currentyear= date('y');
	
	//current month
	$currentmonth=date('m');

	
	$monthdays= cal_days_in_month(CAL_GREGORIAN, date('m'), date('y'));
	$count=1;
	$regids="[";
	$courseids="[";
	
	$monthdays= cal_days_in_month(CAL_GREGORIAN, $count, date('y'));
	
	
	while($count<=$currentmonth)
	{
	
	 $monthdays= cal_days_in_month(CAL_GREGORIAN, $count, date('y'));
	 $firstday=date('Y-m-d', mktime(0, 0, 0, $count, 1, date('y')));
	 $lastday= date('Y-m-d', mktime(0, 0, 0, $count, $monthdays, date('y')));
	 
	$sqlquery="SELECT count(id) as regcount,
			month(date_format(from_unixtime(registration_date),'%e %b')) as reg_date from mdl_vrssstudentregistration
			where date_format(from_unixtime(registration_date),'%Y-%m-%d') between '". $firstday . "' and '" . $lastday . "'";
		$data = mysql_query($sqlquery) 
	 	or die(mysql_error());

		//$showdate=  date("m/d/y", $firstday);
		//date("M j", mktime(0, 0, 0, $firstday('m'), 1,  $firstday('y')));
		//echo date('M j', mktime(0, 0, 0, date('m'), 1, date('y')));
		//echo $showdate;
		$res = mysql_fetch_array($data);	
		
		$showdate=date("M", strtotime($firstday));
		//echo $showdate;
		
		$regids  =$regids . '"'.(empty($res['regcount'])?0:$res['regcount']).'"';
		$courseids = $courseids . '"' . $showdate . '"';	

		$count++;
		if($count <= $currentmonth)
		{
			$regids  =$regids . ",";
			$courseids = $courseids . ",";		
		}
				

	}
	$regids  =$regids . "]";
	$courseids = $courseids . "]";
	echo "[" . $regids . "," . $courseids . "]";
}


//GET registrtaion counts
if(isset($_GET['type']) && $_GET['type']==101)
{
	 mysql_connect($hostname_reg,$username_reg,$password_reg) or die(mysql_error()); 
 mysql_select_db($database_reg) or die(mysql_error()); 
 
	$result="[{";


	//Get total years registration
	$sqlquery="SELECT count(id) as regcount, date_format(from_unixtime(registration_date),'%e %b %y')  as reg_date FROM  mdl_vrssstudentregistration";
	$data = mysql_query($sqlquery) 
	or die(mysql_error());

	$res = mysql_fetch_array($data);	
	$result=$result .'"alltime":' . (empty($res['regcount'])?0:$res['regcount']) . ',';
	
	
	//get todays regsitrations
	$sqlquery="SELECT  Count(id) As regcount FROM mdl_vrssstudentregistration 
	where date_format(from_unixtime(registration_date),'%e %b')=date_format(from_unixtime(UNIX_TIMESTAMP(NOW())),'%e %b')";
	$data = mysql_query($sqlquery) 
	or die(mysql_error());

	$res = mysql_fetch_array($data);
	
	$result=$result . '"today":' . (empty($res['regcount'])?0:$res['regcount']) . ',';
	
	//get highest registrtaion	
	$sqlquery="SELECT count(id) as regcount, date_format(from_unixtime(registration_date),'%D %b %Y')  as reg_date FROM  mdl_vrssstudentregistration 
	group by  date_format(from_unixtime(registration_date),'%e %b %y')  order by regcount desc limit 1";
	$data = mysql_query($sqlquery) 
	or die(mysql_error());

	$res = mysql_fetch_array($data);
	
	$result=$result . '"highest":' . (empty($res['regcount'])?0:$res['regcount']) . ',"highestdate":"' . $res['reg_date'] . '"}]';
	echo $result;
		
 
}







//for course wise registration
if(isset($_GET['type']) && $_GET['type']==201)
{

mysql_connect($hostname_reg,$username_reg,$password_reg) or die(mysql_error()); 
 mysql_select_db($database_reg) or die(mysql_error()); 
 
	 
	 $sqlSelect ="select count(registration_id) AS regcount, course_name  from mdl_vrssstudentcourse as s inner join
	  mdl_vrsscourse as c on c.id=s.course_id group by s.course_id";
	
	$data = mysql_query($sqlSelect) 
	 or die(mysql_error());  
	 $total_rows = mysql_num_rows($data);
	  
	 $count = 1;
	$str= "[";
	
	
	$regids="[";
	$courseids="[";
	
	 while($res = mysql_fetch_array($data)) 
	 {
		$str=$str . "{";
//		$str= $str . '"regcount":'.$res['regcount'].'",course":'. $res['course_name'];
//		$str= $str ."}";
//		
//	
//		if($count < $total_rows)
//			$str= $str . ",";
//		
//			$count++;
				
	 
	  	$regids  =$regids . $res['regcount'];
		$courseids = $courseids . '"' . $res['course_name'] . '"';
		
		if($count < $total_rows)
		{
			$regids  =$regids . ",";
			$courseids = $courseids . ",";
			$count++;

		}		
	 }
	
	$regids  =$regids . "]";
	$courseids = $courseids . "]";
	echo "[" . $regids . "," . $courseids . "]";

//	echo '[[6,14,5],["C++","Java","android"]]';
}

if(isset($_GET['type']) && $_GET['type']==100) // for series chart
{
	 mysql_connect($hostname_reg,$username_reg,$password_reg) or die(mysql_error()); 
 mysql_select_db($database_reg) or die(mysql_error()); 


	//get all courses
	$sqlSelect="select * from mdl_vrsscourse";
	 $data = mysql_query($sqlSelect) 
	 or die(mysql_error());  
	 $total_rows = mysql_num_rows($data);
	 //foreach course get the array
	 while($res = mysql_fetch_array($data)) 
	 {
		 $query= "";
	 }
	 

	//for each day find the count
	$count=1;
	$date= date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('y')));
	 while($count<=$monthdays)
	 {
		 $date= date('Y-m-d', mktime(0, 0, 0, date('m'), $count, date('y')));
		 $count++;


	 
		$sqlquery= "select count(registration_id) as regCount, course_id ,
		date_format(from_unixtime(registration_date),'%Y-%m-%d') as reg_date  from mdl_vrssstudentregistration as r 
		inner join mdl_vrssstudentcourse as s on r.id=s.registration_id where reg_date='" . $date . 
		"' group by ann_date, s.course_id order by reg_date";
		
		$data = mysql_query($sqlquery) or die(mysql_error());  
	 	$total_rows = mysql_num_rows($data);
	 	//foreach course get the array
	 	while($res = mysql_fetch_array($data)) 
	 	{

			$query= "";
	 	}
	 		
		
	  		 
	 }
/*	 
	 $sqlSelect="select * from mdl_vrsscourse";
	 $data = mysql_query($sqlSelect) 
	 or die(mysql_error());  
	 $total_rows = mysql_num_rows($data);
	 //foreach course get the array
	 while($res = mysql_fetch_array($data)) 
	 {
		 $query= "";
	 }
	 
	 
	 $sqlSelect ="select count(registration_id) AS regcount, course_name  from mdl_vrssstudentcourse as s inner join
	  mdl_vrsscourse as c on c.id=s.course_id group by s.course_id";
	
	$data = mysql_query($sqlSelect) 
	 or die(mysql_error());  
	 $total_rows = mysql_num_rows($data);
	  
	 $count = 1;
	$str= "[";
	
	
	$regids="[";
	$courseids="[";
	
	 while($res = mysql_fetch_array($data)) 
	 {
		$str=$str . "{";
//		$str= $str . '"regcount":'.$res['regcount'].'",course":'. $res['course_name'];
//		$str= $str ."}";
//		
//	
//		if($count < $total_rows)
//			$str= $str . ",";
//		
//			$count++;
				
	 
	  	$regids  =$regids . $res['regcount'];
		$courseids = $courseids . '"' . $res['course_name'] . '"';
		
		if($count < $total_rows)
		{
			$regids  =$regids . ",";
			$courseids = $courseids . ",";
			$count++;

		}		
	 }
	
	$regids  =$regids . "]";
	$courseids = $courseids . "]";
	echo "[" . $regids . "," . $courseids . "]";

//	echo '[[6,14,5],["C++","Java","android"]]';*/
}

// Connects to your Database 
 if(isset($_POST['view'])) // for series chart
{
		 mysql_connect($hostname_reg,$username_reg,$password_reg) or die(mysql_error()); 
 mysql_select_db($database_reg) or die(mysql_error()); 
		
		if($_POST['view']=='day')
		{ 
		 $sqlSelect="SELECT c.course_name AS 'coursename' , table1 .count 
							FROM mdl_vrsscourse AS c
							LEFT OUTER JOIN ( 
							select  count( r.id ) AS 'count',  registration_date AS rdate, registration_id, course_id  from 
							mdl_vrssstudentregistration AS r
							INNER JOIN mdl_vrssstudentcourse AS sc ON r.id = sc.registration_id
							 WHERE date_format( from_unixtime( registration_date ) , '%e %b %Y' ) = date_format( from_unixtime( 	                               UNIX_TIMESTAMP( NOW( ) ) ) , '%e %b %Y' ) 
							
							GROUP BY course_id
							) as  table1 ON c.id = table1.course_id
							";
		}
		
		elseif ($_POST['view']=='7day')
		{
			
				 $sqlSelect="SELECT c.course_name AS 'coursename' , table1 .count 
							FROM mdl_vrsscourse AS c
							LEFT OUTER JOIN ( 
							select  count( r.id ) AS 'count',  registration_date AS rdate, registration_id, course_id  from 
							mdl_vrssstudentregistration AS r
							INNER JOIN mdl_vrssstudentcourse AS sc ON r.id = sc.registration_id
							 WHERE date_format(from_unixtime(registration_date),'%Y-%m-%d')>= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
							
							GROUP BY course_id
							) as  table1 ON c.id = table1.course_id
							";
			
			/* $sqlSelect="select mdl_vrsscourse.course_name  as 'coursename' ,count(mdl_vrssstudentregistration.id) as 'count' from mdl_vrssstudentregistration
						inner join mdl_vrssstudentcourse on mdl_vrssstudentcourse.registration_id=mdl_vrssstudentregistration.id
						inner join  mdl_vrsscourse on mdl_vrssstudentcourse.course_id=mdl_vrsscourse.id
						where date_format(from_unixtime(registration_date),'%Y-%m-%d')>= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
						group by  mdl_vrsscourse.id";	*/
		}
		
		elseif($_POST['view']=='30day')
		{
			 $sqlSelect="SELECT c.course_name AS 'coursename' , table1 .count 
							FROM mdl_vrsscourse AS c
							LEFT OUTER JOIN ( 
							select  count( r.id ) AS 'count',  registration_date AS rdate, registration_id, course_id  from 
							mdl_vrssstudentregistration AS r
							INNER JOIN mdl_vrssstudentcourse AS sc ON r.id = sc.registration_id
							 WHERE date_format(from_unixtime(registration_date),'%Y-%m-%d')>= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
							
							GROUP BY course_id
							) as  table1 ON c.id = table1.course_id
							";
		}
		else
		{
			$sqlSelect="";
		}
		
		 $data = mysql_query($sqlSelect) 
		 or die(mysql_error()); 
		 
		 $total_rows = mysql_num_rows($data);
		 
		 $count = 1;
		 
		echo "[ ";
		 while($info = mysql_fetch_array($data)) 
		 { 
				
		
			
			echo "{";
			if($info['count']=='')
			{
				echo '"count":'.'0'.",";
			}
			else
			{
				echo '"count":'.$info['count'].",";
			}
			
		
			echo "\"coursename\":\"".$info['coursename']."\"";
		
			echo "}";
			
		
			
			
		
			if($count < $total_rows)
				echo ",";
			
				$count++;	
		 }
		
		 
		echo " ]";
}

?>