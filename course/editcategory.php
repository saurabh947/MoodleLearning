<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Page for creating or editing course category name/parent/description.
 * When called with an id parameter, edits the category with that id.
 * Otherwise it creates a new category with default parent from the parent
 * parameter, which may be 0.
 *
 * @package    core
 * @subpackage course
 * @copyright  2007 Nicolas Connault
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../config.php');
require_once('batch/main.php');
require_once('lib.php');
require_once('editcategory_form.php');
 ?>

<html>
<!-- modle windows coding start-->
<link type='text/css' href='modelwindow/css/basic.css' rel='stylesheet' media='screen' />
<!-- IE6 "fix" for the close png image -->
<!--[if lt IE 7]>
<link type='text/css' href='modelwindow/css/basic_ie.css' rel='stylesheet' media='screen' />
<![endif]-->
<head>
<style type="text/css">
	
	.titlearea
	{
		/*width:400px;*/
	}
	
	.coursetitle
	{
		float:left;
		font-weight:bold;
		color:#0CAABF;
		border-bottom:2px dotted #000;
		padding-bottom:5px;
		width:560px;
	}
	
	.buttonlist
	{
		float:right;
	}
	
	#subjectlist
	{	
		margin-top:5px;
		line-height:1.8em;
	}
	
</style>

</head>
		
        
        <!-- preload the images -->
		<div style='display:none'>
			<img src='modelwindow/img/basic/x.png' alt='' />
		</div>
<!-- modle windows coding end-->

</html>

<?php


require_login();

$id = optional_param('id', 0, PARAM_INT);
$itemid = 0; //initalise itemid, as all files in category description has item id 0
$batchid = 0;

if ($id) {
    if (!$category = $DB->get_record('course_categories', array('id' => $id))) {
        print_error('unknowcategory');
    }
	
    $PAGE->set_url('/course/editcategory.php', array('id' => $id));
    $categorycontext = get_context_instance(CONTEXT_COURSECAT, $id);
    $PAGE->set_context($categorycontext);
    require_capability('moodle/category:manage', $categorycontext);
    $strtitle = get_string('editcategorysettings');
    $editorcontext = $categorycontext;
    $title = $strtitle;
    $fullname = $category->name;
} else {
    $parent = required_param('parent', PARAM_INT);
    $PAGE->set_url('/course/editcategory.php', array('parent' => $parent));
    if ($parent) {
        if (!$DB->record_exists('course_categories', array('id' => $parent))) {
            print_error('unknowcategory');
        }
        $context = get_context_instance(CONTEXT_COURSECAT, $parent);
    } else {
        $context = get_system_context();
    }
    $PAGE->set_context($context);
    $category = new stdClass();
    $category->id = 0;
    $category->parent = $parent;
	$courseidnumber=$parent;
	
    require_capability('moodle/category:manage', $context);
    $strtitle = get_string("addnewcategory");
    $editorcontext = $context;
    $itemid = null; //set this explicitly, so files for parent category should not get loaded in draft area.
    $title = "$SITE->shortname: ".get_string('addnewcategory');
    $fullname = $SITE->fullname;
}

$PAGE->set_pagelayout('admin');

$editoroptions = array(
    'maxfiles'  => EDITOR_UNLIMITED_FILES,
    'maxbytes'  => $CFG->maxbytes,
    'trusttext' => true,
    'context'   => $editorcontext
);
$category = file_prepare_standard_editor($category, 'description', $editoroptions, $editorcontext, 'coursecat', 'description', $itemid);
if($category->batch==1)
{
	$batchexsting=$DB->get_record('vrsspl_batch_master', array('categoryid' => $id));
	$batchid=$batchexsting->id;
	$category->batchstart=$batchexsting->startdate;
	$category->batchend=$batchexsting->enddate;	
	
}
$mform = new editcategory_form('editcategory.php', compact('category', 'editoroptions'));

$mform->set_data($category);

if ($mform->is_cancelled()) {
    if ($id) {
        redirect($CFG->wwwroot . '/course/category.php?id=' . $id . '&categoryedit=on');
    } else if ($parent) {
        redirect($CFG->wwwroot .'/course/category.php?id=' . $parent . '&categoryedit=on');
    } else {
        redirect($CFG->wwwroot .'/course/index.php?categoryedit=on');
    }
}
 else if ($data = $mform->get_data()) {
	//print_object($data);
    $newcategory = new stdClass();
    $newcategory->name = $data->name;
    $newcategory->idnumber = $data->idnumber;
    $newcategory->description_editor = $data->description_editor;
    $newcategory->parent = $data->parent; // if $data->parent = 0, the new category will be a top-level category
	$newcategory->batch=$data->batch;

    if (isset($data->theme) && !empty($CFG->allowcategorythemes)) {
        $newcategory->theme = $data->theme;
    }

    if ($id) {
	
	       // Update an existing category.
        $newcategory->id = $category->id;
        if ($newcategory->parent != $category->parent) {
            // check category manage capability if parent changed
            require_capability('moodle/category:manage', get_category_or_system_context((int)$newcategory->parent));
            $parent_cat = $DB->get_record('course_categories', array('id' => $newcategory->parent));
            move_category($newcategory, $parent_cat);

		}

    } else {
        // Create a new category.
        $newcategory->description = $data->description_editor['text'];


        // Don't overwrite the $newcategory object as it'll be processed by file_postupdate_standard_editor in a moment
        $category = create_course_category($newcategory);
        $newcategory->id = $category->id;
        $categorycontext = $category->context;
	        redirect($CFG->wwwroot .'/course/index.php');				
		
		if($data->batch==1)
		{
			$batch = new stdClass();
			$batch->id='';
			$batch->categoryid=$newcategory->id;
			$batch->startdate=$data->batchstart;
			$batch->enddate=$data->batchend;
			$batchObj=new batch();
			$batchObj->addnewbatch($batch);
		}
		
    }
?>

<?php
    $newcategory = file_postupdate_standard_editor($newcategory, 'description', $editoroptions, $categorycontext, 'coursecat', 'description', 0);
    $DB->update_record('course_categories', $newcategory);
	//$categoryid=$newcategory->id;
	
		
		if($newcategory->batch==1)
		{
			echo "batch updated";
			echo "\n$newcategory->batch";
			$batchrecords=new stdclass();
	
			$batchrecords->id=$batchid;
			$batch->categoryid=$newcategory->id;
			$batchrecords->startdate=$data->batchstart;
			$batchrecords->enddate=$data->batchend;
			//print_object($batchrecords);
			$DB->update_record('vrsspl_batch_master', $batchrecords);
		}
		else
		{
			$countid=$DB->get_record_sql('SELECT count(`categoryid`) as count FROM `mdl_vrsspl_batch_master` WHERE `categoryid` = ?', array($newcategory->id));
			if($countid->count==0)
			{
				$DB->get_record_sql('DELETE FROM `mdl_vrsspl_batch_master` WHERE `categoryid` = ?', array($newcategory->id));
			}
		}
		
		
		
    fix_course_sortorder();

	
    //redirect('category.php?id='.$newcategory->id.'&categoryedit=on');
}

// Unfortunately the navigation never generates correctly for this page because technically this page doesn't actually
// exist on the navigation; you get here through the course management page.
// First up we'll try to make the course management page active seeing as that is where the user thinks they are.
// The big prolem here is that the course management page is a common page for both editing users and common users and
// is only added to the admin tree if the user has permission to edit at the system level.
$node = $PAGE->settingsnav->get('root');
if ($node) {
    $node = $node->get('courses');
    if ($node) {
        $node = $node->get('coursemgmt');
    }
}
if ($node) {
    // The course management page exists so make that active.
    $node->make_active();
} else {
    // Failing that we'll override the URL, not as accurate and chances are things
    // won't be 100% correct all the time but should work most times.
    // A common reason to arrive here is having the management capability within only a particular category (not at system level).
    navigation_node::override_active_url(new moodle_url('/course/index.php', array('categoryedit' => 'on')));
}

$PAGE->set_title($title);
$PAGE->set_heading($fullname);
echo $OUTPUT->header();
echo $OUTPUT->heading($strtitle);
$mform->display();
echo $OUTPUT->footer();


/*function courselist()
{
$batchObj=new batch();
				$getallcoursesubject=$batchObj->getallcoursesubject($_GET["id"]);
				
				foreach ($getallcoursesubject as $subject){
					if($subject->subjectid==NULL)
					{
					 echo "<input type='checkbox' name='check_list[]' value='".$subject->id."'>&nbsp;&nbsp;".$subject->subjectname."<br>"  ;
					}
					else
					{
						echo "<input type='checkbox' name='check_list[]' value='".$subject->id."' checked>&nbsp;&nbsp;".$subject->subjectname."<br>"  ;
					}
				}
				
}*/
?>

<script type='text/javascript' src='modelwindow/js/jquery.js'></script>
<script type='text/javascript' src='modelwindow/js/jquery.simplemodal.js'></script>
<script type='text/javascript' src='modelwindow/js/basic.js'></script>
<script type="text/javascript">
	
		 $(document).ready(function(){ 
			//alert('hi');
			//alert(document.getElementsByName('id')[0].value);
			subjectcount();
			
		 });
        jQuery('.closewindow').click(function(e) {
        $.modal.close();
    		});
			
			
		$('.basic').click(function(e){
			
			var courseid=$("#id_parent").val();
			
			var type="courecreate";
			//alert(courseid + 'courseid');
			  $.ajax(
                  {
                   type: "POST",   
                   url: "getjsondata.php",  
                   async:false,
                   data:{courseid : courseid,type : type},
                   dataType:"json",
                   //contentType: "application/json; charset=utf-8",
                   success : function(data)
                   {
					   $('#subjectlist').html('');
					    for(var n=0; n<data.length; n++)
						{
							if(data[n].subjectid=='' )
							{
     						$('#subjectlist').append("<input type='checkbox' name='check_list[]' value='"+data[n].id+"'>&nbsp;&nbsp;"+ data[n]. 	subjectname + "<br/>");	  
							}
							else
							{
								$('#subjectlist').append("<input type='checkbox' name='check_list[]' value='"+data[n].id+"' checked>&nbsp;&nbsp;"+ data[n]. 	subjectname + "<br/>");	  	
							}
    					}
						
				   },
				    error : function()
				   {
						//alert("error");
                      
					}
			});
			
		});	
			
		function subjectcount()
		{
			var courseid=$("#id_parent").val();
			var type="subjectcount";
			//alert(courseid + 'courseid');
			  $.ajax(
                  {
                   type: "POST",   
                   url: "getjsondata.php",  
                   async:false,
                   data:{courseid : courseid,type : type},
                   dataType:"json",
                   //contentType: "application/json; charset=utf-8",
                   success : function(data)
                   {
						//alert(data[0].subjectcount);
						$('#coursecount').html(data[0].subjectcount);
				   },
				    error : function()
				   {
						//alert("error");
                      
					}
			});
		}
			
		$("#id_parent").change(function () {
			//alert('hi');
			subjectcount();
		});
		
		function addsubject()
		{
			var courses = new Array();
			var chk_arr =  document.getElementsByName("check_list[]");
		var chklength = chk_arr.length;
		for(k=0;k< chklength;k++)
		{
			if(chk_arr[k].checked)
			{	
				courses.push(chk_arr[k].value);
			}
		}			
			
			var courseid=$("#id_parent").val();
			var type="addcourse";
			//alert(courseid + 'courseid');
			  $.ajax(
                  {
                   type: "POST",   
                   url: "getjsondata.php",  
                   async:false,
				   data:{courseid : courseid,type : type,courses:courses},
                   //data:"{'courseid='" + courseid, "'addsubject='"addsubject"}",
                   dataType:"json",
                   //contentType: "application/json; charset=utf-8",
                   success : function(data)
                   {
					   	  subjectcount();
					 	  $.modal.close();
				   },
				    error : function()
				   {
						//alert("error");
                      
					}
			});
		}

		var buttons = $('#submitbutton,#startbatch,#cancel');		
		$("#basic-modal-button").click(function(){			

/*
			buttons.not(this).attr('disabled',true);
			$(this).css('display', 'none');
	        $('<img>').attr('src', 'loading.gif').insertAfter($(this));
*/
			var coursebatch=$("#id_batch").val();
			//var batchid=<?php echo $_GET['id']; ?>;
			var batchid=document.getElementsByName('id')[0].value;
			if(coursebatch==0)
			{
				//alert('hi');
				$("#errormessage").text("You need to create a batch first.");
			}
			else
			{
			

			var courseid=$("#id_parent").val();
			var type="batchsubjectcreate";
			//alert(courseid + 'courseid');

			  $.ajax(
                  {
                   type: "POST",   
                   url: "getjsondata.php",  
                   async:false,
                   data:{courseid : courseid,type : type,batchid : batchid},
                   dataType:"json",
                   //contentType: "application/json; charset=utf-8",

					beforeSend: function()
					{
						$('#basic-modal-content').modal();
						$("#simplemodal-container").css("width","220px").css("height","120px");
						$("#simplemodal-container").html("<img src='loading.gif' alt='loading...' /> <br /> <br /> <strong>Please wait while the batch is being created... <br /> You cannot stop the process now.</strong>");

					},

                   	success : function()
                   	{
						$.modal.close();
						alert("Batch has successfully been created!");
//						window.location.href = "http://localhost/moodle/course/index.php";
						
						//alert(data[0].subjectcount);
						//$('#coursecount').html(data[0].subjectcount);
						//alert(data[0].success);
/*						if(data[0].success=="true")
						{
							$("#errormessage").text("Batch is being created, please wait. You cannot stop the process now...");
						}
						else if(data[0].success=="false")
						{
							$("#errormessage").text("Batch is already created, you can't create again.");
						}  */
				   	},

				    error : function()
				   	{
						$.modal.close();
						alert("There was an error while creating batch. Please try again.");
                      
					}
			});

			}
		});		
    
</script>
<div id="basic-modal-content">
			<!--<a href="#" class="closewindow">close</a>-->
            <div class="titlearea">
				<div class="coursetitle">All Course
                
                	<input type="submit" value="Add Subject" name="addsubject" onClick="addsubject();"/>
                    
                </div>
            </div>
			<div id="subjectlist">
			<?php 
				

				/*$batchObj=new batch();
				$getallcoursesubject=$batchObj->getallcoursesubject($_GET["id"]);
				
				foreach ($getallcoursesubject as $subject){
					if($subject->subjectid==NULL)
					{
					 echo "<input type='checkbox' name='check_list[]' value='".$subject->id."'>&nbsp;&nbsp;".$subject->subjectname."<br>"  ;
					}
					else
					{
						echo "<input type='checkbox' name='check_list[]' value='".$subject->id."' checked>&nbsp;&nbsp;".$subject->subjectname."<br>"  ;
					}
				}
				
				*/
			?>
			</div>
		</div>