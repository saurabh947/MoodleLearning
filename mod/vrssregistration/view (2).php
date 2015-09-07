<?php 

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // vrssregistration instance ID - it should be named as the first character of the module

if ($id) {
    $cm         = get_coursemodule_from_id('vrssregistration', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $vrssregistration  = $DB->get_record('vrssregistration', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $vrssregistration  = $DB->get_record('vrssregistration', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $vrssregistration->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('vrssregistration', $vrssregistration->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);

if (has_capability('mod/vrssregistration/view.php:read', $context)) 
{
	require_login($course, true, $cm);
}
add_to_log($course->id, 'vrssregistration', 'view', "view.php?id={$cm->id}", $vrssregistration->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/vrssregistration/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($vrssregistration->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// other things you may want to set - remove if not needed
//$PAGE->set_cacheable(false);
//$PAGE->set_focuscontrol('some-html-id');
//$PAGE->add_body_class('vrssregistration-'.$somevar);

// Output starts here
echo $OUTPUT->header();

if ($vrssregistration->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('vrssregistration', $vrssregistration, $cm->id), 'generalbox mod_introbox', 'vrssregistrationintro');
}

if ($course->guest )
{
	echo "not allowed";
}
?>

    	
<?php
  
//echo $totalRows_Recordset1;
echo $OUTPUT->heading('Student Registration Records');
 //echo  "Total reocrds : ".$totalRows_Recordset1;

?>
    
<link rel="stylesheet" type="text/css" href="jscss/jquery-ui-1.8.1.custom.css"/>
    <link rel="stylesheet" type="text/css" href="jscss/ui.jqgrid.css"/>
    <script type="text/javascript" src="jscss/jquery.js"></script>
        <script type="text/javascript" src="jscss/jquery-ui-1.8.1.custom.min.js"></script>

        <script type="text/javascript" src="jscss/jquery.layout.js"></script>
        <script type="text/javascript" src="jscss/grid.locale-en.js"></script>
        <script type="text/javascript" src="jscss/ui.multiselect.js"></script>
        <script type="text/javascript" src="jscss/jquery.jqGrid.min.js"></script>
        <script type="text/javascript" src="jscss/jquery.tablednd.js"></script>
        <script type="text/javascript" src="jscss/jquery.contextmenu.js"></script>
    <script>
$(document).ready( function(){
   // Create JSON data locally   

   $grid= $("#list2");      
   			 $.ajax(
                  {
                   type: "POST",   
                   url: "getgriddata.php",  
                   async:false,
                   dataType:"json",
                   //contentType: "application/json; charset=utf-8",
                   success : function(data)
                   {
					   $grid.jqGrid({       
						   data: data,
						   datatype: "local", // Set datatype to detect json data locally					   
						   
						   colNames:['ID','Date', 'Student Name','Institute','Qualification', 'Courses Apllied For', 'Experience', 'Experience Type','Phone No.','Email Id'],
						   colModel:[
						  		 {name:'reg_id',index:'reg_id', width:50,search:true},
						   		{name:'reg_date',index:'reg_date', width:60,search:true,align:"center"},
							   	{name:'student_name',index:'student_name', width:100,search:true},
							    {name:'college_name',index:'college_name', width:100},
								{name:'qualification',index:'qualification', width:100},
							   	{name:'courses',index:'courses', width:100,search:true},
							   	{name:'experience',index:'experience', width:60},
							   	{name:'experience_type',index:'experience_type', width:100},
							   	{name:'phone_no',index:'phone_no', width:70},
							   	{name:'email_id',index:'email_id', width:80}
							  
						   ],
						   rowNum: 10,
						   autowidth:true,
						   rownumbers: true,
						   rowList:[10,20],
						   height:245,
						   pager: '#pager2',
						   sortname: 'name',
						   viewrecords: true,
						   sortorder: "asc",
						   caption:"Registration Data",
						   
				
				   		})
				   }
				  });
   $grid.jqGrid('navGrid','#pager2',{edit:false,add:false,del:false});
   jQuery("#list2").jqGrid('filterToolbar',{stringResult: true,searchOnEnter : false});
   
});

</script>

<table id="list2"></table>
<div id="pager2"></div>
 <div id="ptoolbar" ></div>

<?php
// Finish the page
echo $OUTPUT->footer();
?>
