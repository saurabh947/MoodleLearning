<?php require_once('annoncement.php'); ?>
<?php

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

//echo "editFormAction = ".$editFormAction;


if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}



$currentPage = $_SERVER["PHP_SELF"];

$maxRows_Recordset1 = 10;
$pageNum_Recordset1 = 0;

//echo "pageNum_Recordset1 = ".$_POST['pageNum_Recordset1']."<br />";
if (isset($_POST['pageNum_Recordset1'])) {
  $pageNum_Recordset1 = $_POST['pageNum_Recordset1'];
}
$startRow_Recordset1 = $pageNum_Recordset1 * $maxRows_Recordset1;


//Delete Record=======================================================================
//echo $_POST['delete']."DDD";
//echo $_GET['delete'];
if ((isset($_POST['delete'])) && ($_POST['delete']=="t" ))
{
//echo "Delete";

if ((isset($_POST['d_id'])) && ($_POST['d_id'] != "")) {
  $deleteSQL = sprintf("DELETE FROM mdl_announcement WHERE id=%s",
                       GetSQLValueString($_POST['d_id'], "int"));

 // echo  $deleteSQL;
  mysql_select_db($database_annoncement, $annoncement);
  $Result1 = mysql_query($deleteSQL, $annoncement) or die(mysql_error());
  
  $delte="t";
  
 // echo "total rows = ".$_POST['totalRows_Recordset1']."|".$_POST['pageNum_Recordset1']."|".$_POST['totalPages_Recordset1'];
//  echo $_POST['totalRows_Recordset1'] . "<br/>";
  
  //echo $maxRows_Recordset1;
	if (isset($_POST['totalRows_Recordset1'])) 
	{
		//echo $_POST['totalRows_Recordset1'];
  		$totalRows_Recordset1 = $_POST['totalRows_Recordset1'];
		if($totalRows_Recordset1 - 1 <= $maxRows_Recordset1 )
		{
			
			$startRow_Recordset1=0;
			$pageNum_Recordset1 = 0;
		}
		if(($totalRows_Recordset1 - 1)/$maxRows_Recordset1 <= $_POST['pageNum_Recordset1'])
		{
			$startRow_Recordset1=0;
			$pageNum_Recordset1 = 0;
		}
	}
  
}
//$totalRows_Recordset1= $totalRows_Recordset1 - 1;
}
//====================================================================================


//update records Record archived=======================================================================
if ((isset($_POST['archived'])) && ($_POST['archived']=="t" ))
{

if ((isset($_POST['d_id'])) && ($_POST['d_id'] != "")) {
  $UpdateSQL = sprintf("UPDATE `mdl_announcement` SET `archived`='True',`archived_dt`=UNIX_TIMESTAMP(NOW()) WHERE id=%s",
                       GetSQLValueString($_POST['d_id'], "int"));

 
  mysql_select_db($database_annoncement, $annoncement);
  $Result1 = mysql_query($UpdateSQL, $annoncement) or die(mysql_error());
   $archive='t';
}
}
//====================================================================================


//update records Record for unarchived=======================================================================
if ((isset($_POST['unarchived'])) && ($_POST['unarchived']=="t" ))
{
if ((isset($_POST['d_id'])) && ($_POST['d_id'] != "")) {
  $UpdateSQL = sprintf("UPDATE `mdl_announcement` SET `archived`='False',`archived_dt`=0 WHERE id=%s",
                       GetSQLValueString($_POST['d_id'], "int"));

 
  mysql_select_db($database_annoncement, $annoncement);
  $Result1 = mysql_query($UpdateSQL, $annoncement) or die(mysql_error());
	
	$unarchive='t';
}
}
//====================================================================================

mysql_select_db($database_annoncement, $annoncement);
$query_Recordset1 = "select id,name,date_format(from_unixtime(annoucement_dt),'%e %b %Y') As 'startdate',url,archived from mdl_announcement ORDER BY `annoucement_dt` DESC";
$query_limit_Recordset1 = sprintf("%s LIMIT %d, %d", $query_Recordset1, $startRow_Recordset1, $maxRows_Recordset1);
$Recordset1 = mysql_query($query_limit_Recordset1, $annoncement) or die(mysql_error());
$row_Recordset1 = mysql_fetch_assoc($Recordset1);

/*if (isset($_GET['totalRows_Recordset1'])) {
  $totalRows_Recordset1 = $_GET['totalRows_Recordset1'];
} else {*/
  $all_Recordset1 = mysql_query($query_Recordset1);
  $totalRows_Recordset1 = mysql_num_rows($all_Recordset1);
//}
$totalPages_Recordset1 = ceil($totalRows_Recordset1/$maxRows_Recordset1)-1;

$queryString_Recordset1 = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_Recordset1") == false && 
        stristr($param, "totalRows_Recordset1") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_Recordset1 = "&" . htmlentities(implode("&", $newParams));
  }
}

//$queryString_Recordset1 = sprintf("&totalRows_Recordset1=%d%s", $totalRows_Recordset1, $queryString_Recordset1);
?>
<?php

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // announcementplugin instance ID - it should be named as the first character of the module

if ($id) {
    $cm         = get_coursemodule_from_id('announcementplugin', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $announcementplugin  = $DB->get_record('announcementplugin', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $announcementplugin  = $DB->get_record('announcementplugin', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $announcementplugin->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('announcementplugin', $announcementplugin->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);

if (has_capability('mod/announcementplugin/view.php:read', $context)) 
{
	require_login($course, true, $cm);
}
add_to_log($course->id, 'announcementplugin', 'view', "view.php?id={$cm->id}", $announcementplugin->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/announcementplugin/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($announcementplugin->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// other things you may want to set - remove if not needed
//$PAGE->set_cacheable(false);
//$PAGE->set_focuscontrol('some-html-id');
//$PAGE->add_body_class('announcementplugin-'.$somevar);

// Output starts here
echo $OUTPUT->header();
/*
if ($announcementplugin->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('announcementplugin', $announcementplugin, $cm->id), 'generalbox mod_introbox', 'announcementpluginintro');
}
*/
if ($course->guest )
{
	echo "not allowd";
}
?>


<script type="text/javascript" src="js/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="js/jquery.qtip-1.0.0-rc3.js"></script>
 
<script language="JavaScript">

function first()
	{	
		//alert('first');
		document.getElementById("page").value = 'first';
			document.getElementById("pageNum_Recordset1").value = 0;
			if(document.forms.length>0)
			{
				//document.forms[0].submit();
				//alert("going to submit data...");
				document.form1.submit();
			}
	}

function last()
	{	
		//$totalPages_Recordset1
		//alert('last');
		//totalRows_Recordset1
		//alert(document.getElementById("totalRows_Recordset1").value);
		document.getElementById("pageNum_Recordset1").value = document.getElementById("totalPages_Recordset1").value;
		if(document.forms.length>0)
		{
				//document.forms[0].submit();
				//alert("going to submit data...");
			document.form1.submit();
		}
	}

function previous()
	{	
			document.getElementById("page").value = 'previous';
			document.getElementById("pageNum_Recordset1").value = Number(document.getElementById("pageNum_Recordset1").value) - 1;
			if(document.forms.length>0)
			{
				//document.forms[0].submit();
				//alert("going to submit data...");
				document.form1.submit();
			}
	}


function next()
	{	
		//alert('last');
			document.getElementById("page").value = 'next';
			document.getElementById("pageNum_Recordset1").value = Number(document.getElementById("pageNum_Recordset1").value) + 1;
			if(document.forms.length>0)
			{
				//document.forms[0].submit();
				//alert("going to submit data...");
				document.form1.submit();
			}
	}



function del(id)
	{	
		var where_to= confirm("Do you really want to delete this record?");
 		if (where_to== true)
 		{
		//alert("id"+id);
			document.getElementById("d_id").value = id;
			//alert("id="+document.getElementById("d_id").value);
			document.getElementById("delete").value = "t";
			//alert(document.getElementById("delete").value);
			//alert("form length="+document.forms.length);
			if(document.forms.length>0)
			{
				//document.forms[0].submit();
				//alert("going to submit data...");
				document.form1.submit();
			}
		}
	}
	
	
	function archived(id)
	{	
		var where_to= confirm("Do you really want to archive this record?");
 		if (where_to== true)
 		{
		//alert("id"+id);
			
			document.getElementById("d_id").value = id;
			//alert("id="+document.getElementById("d_id").value);
			document.getElementById("archived").value = "t";
			//alert(document.getElementById("d_id").value);
			//alert(document.getElementById("archived").value);
			if(document.forms.length>0)
			{
				//document.forms[0].submit();
				//alert("going to submit data...");
				document.form1.submit();
			}
		}
		
	}
	
	function unarchived(id)
	{	
		var where_to= confirm("Do you sure to Unarhived record?");
 		if (where_to== true)
 		{
		//alert("id"+id);
			
			document.getElementById("d_id").value = id;
			//alert("id="+document.getElementById("d_id").value);
			document.getElementById("unarchived").value = "t";
			//alert(document.getElementById("d_id").value);
			//alert(document.getElementById("archived").value);
			if(document.forms.length>0)
			{
				//document.forms[0].submit();
				//alert("going to submit data...");
				document.form1.submit();
			}
		}
		
	}
	
	
	
	
	
//-->

</script>
 
<script type="text/javascript">
 	

	
		$(document).ready(function(){
		   $('.my').each(function(){
			   //alert($(this).text());
			   //alert($(this).siblings(".myUrl").val());
			   var tooltip = new String();
			   tooltip += $(this).siblings(".myHead").val();
			   tooltip += "<br /> <b>URL</b> : " + $(this).siblings(".myUrl").val();
			   $(this).qtip({
				     content: {    
									
									text: tooltip
						 
								},
			  //content: {text: $(this).text()},
			    show: { solo: true },
			position: {
           corner: {
               tooltip: 'topMiddle',
               target: 'bottomMiddle'
            },
            adjust: {
               resize: true,
               scroll: true
            }
         },
	
		
		 
            style: { 
				name: 'cream',
				width: { max: 'auto' },
				tip: true,
                padding: 5,
                color: 'black',
                textAlign: 'left',
                border: {
                width: 1,
                radius: 3,
				 classes: 'ui-modal ui-tooltip-light ui-tooltip-rounded'
             },
                tip: 'topMiddle',
			


                classes: { 
                    tooltip: 'ui-widget', 
                    tip: 'ui-widget', 
                    title: 'ui-widget-header', 
                    content: 'ui-widget-content' 
                } 
            } 
			  });
		   });
		});
    </script>
<?php
  
//echo $totalRows_Recordset1;
echo $OUTPUT->heading('Announcement');
 //echo  "Total reocrds : ".$totalRows_Recordset1;

?>
<div><a href="<?php echo 'new_edit_announcement.php?id='.$_GET['id']; ?>">Add Announcement</a></div> <br/>

<table border="1" cellpadding="5" cellspacing="5" style="border-color: rgb(221, 221, 221)" >
 <tr>
<!-- 	<th  style="border-width:1px; border-style:solid; border-color: rgb(221, 221, 

221)">Sr No</th>
-->  
<th  width="150px" style="border-width:1px; border-style:solid; border-color: rgb(221, 221, 

221)">Announcement Date</th>
    <th  width="600px" style="border-width:1px; border-style:solid; border-color: rgb(221, 221, 

221)">Announcement</th>
 <!--<th  width="300px" style="border-width:1px; border-style:solid; border-color: rgb(221, 221, 

221)">URL</th>-->
    <th  width="50px" style="border-width:1px; border-style:solid; border-color: rgb(221, 221, 

221)" align="center">Edit</th>
    <th  width="50px" style="border-width:1px; border-style:solid; border-color: rgb(221, 221, 

221)" align="center">Delete</th>
    <th  width="50px" style="border-width:1px; border-style:solid; border-color: rgb(221, 221, 

221)" align="center">Archived</th>
	
  </tr>
  <?php
	//echo $totalRows_Recordset1;

	if($totalRows_Recordset1 == 0)
	{
		//echo "success";
		?>
  
<tr>
  <td align="center" colspan="6" style="border-width:1px; border-style:solid; border-color: 

rgb(221, 221, 221); font-weight:bold;">No Announcement</td></tr>
        <?php
	}
	else
	{
		  // while ($row_Recordset1 = mysql_fetch_assoc($Recordset1)) 
		  do
		   { 
		   
			if($row_Recordset1['archived']=='True')
			{
				
				?>
                
                <tr style='background-color:#9ed5ef'><?php
			}
			else
			{?>
				<tr>
				<?php
			}
		
  ?>
    <!--<tr style="background-color:#966">-->
     
     
  <!--  <td style="border-width:1px; border-style:solid; border-color:rgb(221, 221, 221)" align="center">-->
    	

		
    <!-- </td>-->
     <td width="150px"  style="border-width:1px; border-style:solid; border-color: 

rgb(221, 221, 221)" align="center" id="<?php echo $row_Recordset1['id'];  ?>">
	   <?php echo $row_Recordset1['startdate']; ?></td>
       
      <td width="600px"  style="border-width:1px; border-style:solid; border-color: rgb(221, 221, 221)">
<input type="hidden" class="myHead" value="<?php echo $row_Recordset1['name']; ?>" />
<input type="hidden" class="myUrl" value="<?php echo $row_Recordset1['url']; ?>" />
      <span class="my">
	  <?php 
	  if(strlen($row_Recordset1['name']) > 60)
	  {
		echo  substr($row_Recordset1['name'],0,60) . '......'; ?>
        
        </span></td>
        <?php 
	  	
      }
	  else
      {
      echo $row_Recordset1['name'];?></span></td>
      <?php }
	  ?>

     <!-- <td width="300px"  style="border-width:1px; border-style:solid; border-color: 

rgb(221, 221, 221)">-->
<?php //echo $row_Recordset1['url']; ?>
<!--</td>-->
       <td width="50px"  style="border-width:1px; border-style:solid; border-color: 
rgb(221, 221, 221)" align="center"><a href="<?php echo 'new_edit_announcement.php?id='.$_GET['id'].'&aid='.$row_Recordset1['id']; ?>">Edit</a></td>
      
        <td width="50px"  style="border-width:1px; border-style:solid; border-color: 
rgb(221, 221, 221)" align="center">
      	
       <a href="javascript:del('<?php echo $row_Recordset1['id']; ?>')">Delete</a></td>
      
         <td width="50px"  style="border-width:1px; border-style:solid; border-color: 

rgb(221, 221, 221)" align="center">
       
       	 <?php
		 	if($row_Recordset1['archived']=='True')
			{?>
				<a href="javascript:unarchived('<?php echo $row_Recordset1['id'] ?>')">
			<?php }
			else
			{?>
				<a href="javascript:archived('<?php echo $row_Recordset1['id'] ?>')">
			<?php }
		 ?>	
        
         <?php if($row_Recordset1['archived']=='True')
		{
			//$flag='t';
			
			echo "Unarchive" ;
		}
		else
		{
			//$flag='f';
			echo "Archive";
		}
		?>
		 
         </a></td>
    </tr>
    <?php }while ($row_Recordset1 = mysql_fetch_assoc($Recordset1)) ; }  ?>
</table>



<table border="0">
  <tr>
    <td><?php if ($pageNum_Recordset1 > 0) { // Show if not first page ?>
        <a href="javascript:first()"><img src="First.gif" /></a>
        <?php } // Show if not first page ?></td>
    <td><?php if ($pageNum_Recordset1 > 0) { // Show if not first page ?>
        <a href="javascript:previous()"><img src="Previous.gif" /></a>
        <?php } // Show if not first page ?></td>
    <td><?php if ($pageNum_Recordset1 < $totalPages_Recordset1) { // Show if not last page ?>
        <a href="javascript:next()"><img src="Next.gif" /></a>
        <?php } // Show if not last page ?></td>
    <td><?php if ($pageNum_Recordset1 < $totalPages_Recordset1) { // Show if not last page ?>
        <a href="javascript:last()"><img src="Last.gif" /></a>
        <?php } // Show if not last page ?></td>
  </tr>
</table>





<?php

	if((isset($delte)) && ($delte="t"))
	 {
		 echo "successfully deleted";
		
	 }
	 else
	 {
		 echo "";
	 }
	 
	  if((isset($archive)) && ($archive="t"))
	 {
		 echo "successfully archived";
		
	 }
	 else
	 {
		 echo "";
	 }
	 
	  if((isset($unarchive)) && ($unarchive="t"))
	 {
		 echo "successfully unarchived";
	
		
	 }
	 else
	 {
		 echo "";
	 }
?>

<?php
// Finish the page
echo $OUTPUT->footer();
?>
<?php
mysql_free_result($Recordset1);
?>
<FORM name="form1" action="<?php echo $editFormAction; ?>" method="POST" enctype="multipart/form-data">
	<input type="hidden" name="MM_insert" value="form1">
	<input type="hidden" name="d_id" id="d_id" value="0" />
	<input type="hidden" name="delete" id="delete" value="f" />
    <input type="hidden" name="archived" id="archived" value="f" />
     <input type="hidden" name="unarchived" id="unarchived" value="f" />
      <input type="text" name="url" id="url" value="" />
      <input type="hidden" name="totalRows_Recordset1" id="totalRows_Recordset1" value="<?php echo $totalRows_Recordset1; ?>"  />
      <input type="hidden" name="pageNum_Recordset1" id="pageNum_Recordset1" value="<?php echo $pageNum_Recordset1; ?>"  />
       <input type="hidden" name="totalPages_Recordset1" id="totalPages_Recordset1" value="<?php echo $totalPages_Recordset1; ?>"  />
      
 	<input type="hidden" name="page" id="page"   />
</FORM>
