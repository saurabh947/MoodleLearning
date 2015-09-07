<?php 
	require_once('annoncement.php'); 
	require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
	require_once(dirname(__FILE__).'/lib.php');
	require_once($CFG->libdir.'/completionlib.php');


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
	add_to_log($course->id, 'announcementplugin', 'new announcement', "new_edit_announcement.php?id={$cm->id}", $announcementplugin->name, $cm->id);

	/// Print the page header
	
	$PAGE->set_url('/mod/announcementplugin/new_edit_announcement.php', array('id' => $cm->id));
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
		echo "not allowed";
	}

	// Replace the following lines with you own code
	
	 
	
	
	
	// Replace the following lines with you own code
	
	$editFormAction = $_SERVER['PHP_SELF'];
	if (isset($_SERVER['QUERY_STRING'])) {
	  $editFormAction .= "?".htmlentities($_SERVER['QUERY_STRING']);
	}
	if(isset($_GET['aid']))
	{ 
		echo $OUTPUT->heading('Edit Announcement');
		$operation='e';
		$aid=$_GET['aid'];  
	}else
	{
		echo $OUTPUT->heading('Add new Announcement');
		$operation='n';
	}
?>

<style type="text/css">
body
{
	 font-family: Arial,Verdana,Helvetica,sans-serif;
}
.clearfix:after {
    clear: both;
    content: ".";
    display: block;
    height: 0;
    min-width: 0;
    visibility: hidden;
}
.mform fieldset {
    border-color: #DDDDDD;
}
.mform fieldset {
    border: 1px solid #DDDDDD;
    margin: 0.7em 0;
    padding: 10px 0;
    width: 100%;
}
</style>

<SCRIPT type="text/javascript">		
function validateForm()
	{
		document.getElementById("lblmessageerror").innerHTML="";
		document.getElementById("lblurlerror").innerHTML="";
		var flag=true;		
				
		var announcement_name=document.getElementById("txtname").value;
		if(announcement_name=="" )
		{
			document.getElementById("lblmessageerror").innerHTML="Required";
			flag=false;
		}
		var string = announcement_name;
		if(string!="" && string.length>100)
		{
			document.getElementById("lblmessageerror").innerHTML="Max. number of characters cannot exceed 100";
			flag=false;
		}
		var validateName;
		if(string!="")
		{
			for (var i=0;i<string.length;i++)
			{
				asciiNum = string.charCodeAt(i);
				if ((asciiNum>=32 && asciiNum<=126))
				{
					validateName= true;   
				}					
				else 
				{
					validateName= false;  
					break;          
				}
			}
			if (validateName==false)
			{
				document.getElementById("lblmessageerror").innerHTML="Please enter valid characters";
				flag=false;   
			}
		}

		var url=document.getElementById("txturl").value;
		if(url!="")
		{
			var urlregex = new RegExp("^(http:\/\/www.|https:\/\/www.|ftp:\/\/www.|www.){1}([0-9A-Za-z]+\.)");
			if(!urlregex.test(url))
			{
				document.getElementById("lblurlerror").innerHTML="Invalid URL";				
				flag=false;
			}			
			if(url.length>2083)
			{
				document.getElementById("lblurlerror").innerHTML="Max. number of characters cannot exceed 2083";
			}
		}		
		return flag;	
	}
</script>

<body>
<?php


	//server side validation
	$formvalid=1;
	$msg="";
	if(isset($_POST['submit']))
	{
		$announcement_name=$_POST[txtname];
		$url=$_POST[txturl];	
		//server side validation
		if($announcement_name=="")
		{
			//print_error('emptymessage');
			$msg= "Message cannot be empty";
			$formvalid=0;
		}		
		//valiadate length
		if(strlen($announcement_name)>100)
		{
			$msg= "Announcement length cannot exceed 100 characters";
			$formvalid=0;
		}
		//url validation
		if($url!="" && $url!=null)
		{
			if(strlen($url)>2083)
			{
				$msgurl= "URL length cannot exceed 2083 characters";
				$formvalid=0;
			}
			if(!(eregi('^(http:\/\/www.|https:\/\/www.|ftp:\/\/www.|www.){1}([0-9A-Za-z]+\.)',  $url)))
			{
				$msgurl= "Invalid URL";
				$formvalid=0;
			}
			//validate message acsii
			$tempstring=$announcement_name;
			for($i=0;$i<strlen($tempstring);$i++)
			{
				$asciiNum = ord($tempstring);
				if ($asciiNum>=32 && $asciiNum<=126)
					continue;  					
				else 
				{
					$msgurl= "Please enter valid characters for announcement";
					$formvalid=0;
					break;
				}
			}
		}//url validn ends
	}

$ALLOWABLE_TAGS = '<a><abbr><acronym><address><article><aside><b><bdo><big><blockquote>
<caption><cite><code><col><colgroup><dd><del><details><dfn><div><dl><dt><em><figcaption><figure><font><h1><h2><h3><h4>
<h5><h6><hgroup><hr><i><img><ins><li><map><mark><menu><meter><ol><p><pre><q><rp><rt><ruby><s><samp><section><small><span>
<strong><style><sub><summary><sup><time><tt><u><ul><var><wbr>';

if(isset($_POST['submit']))
{
	//check whether form data is valid
	if($formvalid==0)
		exit;
	if($operation=="n")
	{
	  $insertSQL = sprintf("INSERT INTO mdl_announcement (id, name, annoucement_dt,archived, archived_dt, url)
					VALUES ('', %s, UNIX_TIMESTAMP(NOW()),'False',0, %s)",
				   GetSQLValueString(strip_tags($_POST['txtname']), "text"),
				   GetSQLValueString($_POST['txturl'], "text"));
		//echo $insertSQL;
		mysql_select_db($database_annoncement, $annoncement);	
		if(!mysql_query($insertSQL, $annoncement))
			die('Error saving record '. mysql_error());
		else
			$add="t";
			
			//redirect("$CFG->wwwroot/mod/announcementplugin/view.php?id=$id");		
	}else if($operation=="e")
	{					   		
		$sql_query=sprintf("UPDATE mdl_announcement set name=%s, url=%s where id=%s", 
		GetSQLValueString(strip_tags($_POST['txtname']),"text"), GetSQLValueString($_POST['txturl'], "text"),  GetSQLValueString($aid, "int"));		
		//echo $sql_query;
		mysql_select_db($database_annoncement, $annoncement);
		if(!mysql_query($sql_query, $annoncement))
			die('Error updating record '. mysql_error());
		else
			$edit="t";
			
	}
}
	if($operation=="e")
	{
		if(!$annoncement)
		{
			die('could not connect'.mysql_error());
		}	
		//connect to my database
		mysql_select_db("moodle1",$annoncement);
							
		$sql_query=sprintf("select * from mdl_announcement where id =%s", GetSQLValueString($aid,"int"));		
		//$sql_query="select * from mdl_announcement where id =" . $aid;	
		mysql_select_db($database_annoncement, $annoncement);
		
		$select_result= mysql_query($sql_query,$annoncement);
		$record= mysql_fetch_array($select_result);
	}
?>
<div>
	<form action="<?php echo $editFormAction; ?>" method="post" onSubmit="return validateForm()">
<!--    	<fieldset  id="general"  class="clearfix">
        <legend class="ftoggler">Your Announcement</legend>-->

            &nbsp;&nbsp;<a href="<?php echo 'view.php?id='.$_GET['id']; ?>">Back to Announcement list</a>
                <table >
                    <tr>
                        <td>						
                        <label font style="color:#AA0000;">Message<font style="color:red;">*</font>
                        
                        
                          <a id="helpicon" title="This is a required field. Upto ASCII 100 characters are allowed" 
                          style="text-decoration: none">
                             <img class="iconhelp"  
                             src="help.gif" >

                             </a>
                        
                        </label>
                            
                        <td><input type="text" id="txtname" name="txtname" maxlength="100" value="<?php  if($operation=="e") echo $record["name"]; ?>" style="width:350px"/></td>
                        <!--<textarea name="txtname" id="txtname"  style="width:350px"><?php if($operation=="e") echo $record["name"]; ?></textarea></td>-->                        
                        <td><label id="lblmessageerror" name="lblmessageerror" font style="color:#AA0000;"><?php if($formvalid==0) echo $msg; ?></label></td>
                        <td><label id="lblmessagevalidn" name="lblmessagevalidn" font style="color:#999;font-size:9px;">Maximum 100 valid ASCII characters allowed</label></td>
                    </tr>
                    <tr>
                        <td><label id="lblurl" value="URL">URL</label>                       
                        
 <a id="helpicon5037256c8d9332" title=" Only valid URL's allowed. Maximum of 2083 characters allowed" style="text-decoration: none">
                             <img class="iconhelp" alt="This " 
                             src="help.gif" 
>
                             </a>
                        
                        
                        
                        </td>
                        <td><input type="text" id="txturl" name="txturl" maxlength="2083" value="<?php if($operation=="e") echo $record["url"]; ?>" style="width:350px"/></td>                        
                        <td><label id= "lblurlerror" name="lblurlerror" font style="color:#AA0000;"><?php if($formvalid==0) echo $msgurl; ?></label></td>
                        <td><label id= "lblurlvalidn" name="lblurlvalidn" font style="color:#999;font-size:9px;">Only valid URL allowed</label></td>
                    </tr>				
                </table>
	
         <!--  </fieldset>-->
        <div>
            &nbsp; <input name="submit" type="submit" value="Save" />
            </div>
            <?php
            if((isset($add)) && ($add="t"))
			{
				echo "<br/>" . "&nbsp;"."Announcement added successfully";	
			}
			else
			{
				echo "";
			}?>
            <?php
            if((isset($edit)) && ($edit="t"))
			{
				echo "<br/>" . "&nbsp;"."Announcement updated successfully";	
			}
			else
			{
				echo "";
			}?>            
            <div  align="right">
            	
            </br>	
            <label id="lblerror" value="Message" style="text-align:right; color:#AA0000;padding-right:30px;">There are required fields in this form marked Required field*.</label>
        </div>			
	</form>
</div>
</body>
</html>
<?php



// Finish the page
echo $OUTPUT->footer();
?>