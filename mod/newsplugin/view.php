<?php 

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // newsplugin instance ID - it should be named as the first character of the module

if ($id) {
    $cm         = get_coursemodule_from_id('newsplugin', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $newsplugin  = $DB->get_record('newsplugin', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $newsplugin  = $DB->get_record('newsplugin', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $newsplugin->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('newsplugin', $newsplugin->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);

if (has_capability('mod/newsplugin/detailview.php:read', $context)) 
{
	require_login($course, true, $cm);
}
add_to_log($course->id, 'newsplugin', 'detailed view', "detailview.php?id={$cm->id}", $newsplugin->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/newsplugin/detailview.php', array('id' => $cm->id));
$PAGE->set_title(format_string($newsplugin->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// other things you may want to set - remove if not needed
//$PAGE->set_cacheable(false);
//$PAGE->set_focuscontrol('some-html-id');
//$PAGE->add_body_class('newsplugin-'.$somevar);

// Output starts here
echo $OUTPUT->header();
/*
if ($newsplugin->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('newsplugin', $newsplugin, $cm->id), 'generalbox mod_introbox', 'newspluginintro');
}
*/
/*
if ($course->guest )
{
	echo "not allowed";
}*/

?>

    	
<?php
  
//echo $totalRows_Recordset1;
echo $OUTPUT->heading('News List');
 //echo  "Total reocrds : ".$totalRows_Recordset1;

?>
  <style type="text/css">
  .myAltRowClass { background-color: #DDDDDC; }
  
  </style>  
<link rel="stylesheet" type="text/css" href="http://www.trirand.com/blog/jqgrid/themes/redmond/jquery-ui-1.8.1.custom.css"/>
    <link rel="stylesheet" type="text/css" href="jscss/ui.jqgrid.css"/>
    <script type="text/javascript" src="jscss/jquery.js"></script>
        <script type="text/javascript" src="jscss/jquery-ui-1.8.1.custom.min.js"></script>

        <script type="text/javascript" src="jscss/jquery.layout.js"></script>
        <script type="text/javascript" src="jscss/grid.locale-en.js"></script>
        <script type="text/javascript" src="jscss/ui.multiselect.js"></script>
        <script type="text/javascript" src="jscss/jquery.jqGrid.min.js"></script>
        <script type="text/javascript" src="jscss/jquery.tablednd.js"></script>
        <script type="text/javascript" src="jscss/jquery.contextmenu.js"></script>
        
		<script type="text/javascript">
			// Here we set the altRows option globallly
			//jQuery.extend(jQuery.jgrid.defaults, { altRows:true });
			//$("tr.jqgrow:odd").css("background-color", "#B80A0A");
			//$('#list2 tr:nth-child(even)').css("background", "#FCF9E6");;
			//$('#list2 tr:nth-child(odd)').addClass("myAltRowClass");
			$('#list2 tr:nth-child(even)').removeClass("myAltRowClass");
			$('#list2 tr:nth-child(odd)').addClass("myAltRowClass");

			

		</script>

    <script type="text/javascript">
	
	
	
	function loadgridData()
	{
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
								   editurl:'operation.php',
								   datatype: "local", // Set datatype to detect json data locally
	
	
								   colNames:['ID' , 'Headline', 'News Date','Link','Category', 'Archived'],
								   colModel:[
										{name:'id',index:'id', width:50,search:true,align:"center",hidden:true},
										{name:'headline',index:'headline', width:450,search:true,align:"left",editable:true,
										editrules:{required:true, edithidden:true}},
										{name:'news_dt',index:'news_dt', width:90,search:true,align:"center",editable:true,
										editrules:{required:true, edithidden:true},
										
										formatter: 'date',
										  formatoptions: {
											  srcformat: 'd-m-Y',
											  newformat: 'd-m-Y'
										  },
											  edittype: 'text',
											  editoptions: {
												  size: 12,
												  maxlengh: 12,
												  dataInit: function (element) {
													  $(element).datepicker({ dateFormat: 'dd-mm-yy'})
												  }
											  },
											  editrules: {date:true}},										
										{name:'url',index:'url', width:200,editable:true,
										editrules:{required:false, edithidden:true}},
									
									{name:'category',index:'category', width:85,editable: true, formatter: 'select',
									edittype: 'select', editoptions: {
										value: 'red:Red;yellow:Yellow;green:Green;blue:Blue',
										dataInit: function (elem) {
											setTimeout(function () {
												$(elem).combobox();
												$( "#toggle" ).click(function() {
													$(elem).toggle();
												});
											 }, 50);
										 },
									 }
								 },
								 {name:'archived',index:'my_checkbox',width:70,align:"center", editable:true, 
								edittype:"checkbox", formatter:'checkbox' }
								   ],
								   rowNum: 10,
								   autowidth:false,
								   rownumbers: true,
								   rowList:[10,20],
								   height:250,
								   multiselect:false,
								   pager: '#pager2',
								   sortname: 'reg_date',
								   viewrecords: true,
								   sortorder: "desc",
								   altRows:false,
								   //altClass:'myAltRowClass',
								   caption:"News Data",								   					   
						
								})
						   },
							loadComplete: function() {
								var iCol = getColumnIndexByName($(this),'on'),
									cRows = this.rows.length, iRow, row, className;

								for (iRow=0; iRow<cRows; iRow++) {
									row = this.rows[iRow];
									className = row.className;
									if ($.inArray('jqgrow', className.split(' ')) > 0) {
										var x = $(row.cells[iCol]).children("input:checked");
										if (x.length>0) {
											if ($.inArray('myAltRowClass', className.split(' ')) === -1) {
												row.className = className + ' myAltRowClass';
											}
										}
									}
								}
							}
						   
						  });
						  
		   $grid.jqGrid('navGrid','#pager2',{edit:true,add:true,del:true},
		   	{mtype:"POST",closeAfterEdit:true,reloadAfterSubmit:false},
		    {mtype:"POST",closeAfterAdd:true,reloadAfterSubmit:false}, 
			{mtype:"POST",reloadAfterSubmit:false}, 
			{multipleSearch:true, multipleGroup:true, showQuery: false});
		 // jQuery("#list2").jqGrid('filterToolbar',{stringResult: true,searchOnEnter : false});
		 
   		
	}
	var varName = function(){		 
		 loadgridData();
	};

	var getColumnIndexByName = function(grid, columnName) {
        var cm = grid.jqGrid('getGridParam','colModel'),i=0,l=cm.length;
        for (; i<l; i++) {
            if (cm[i].name===columnName) {
                return i; // return the index
            }
        }
        return -1;
    };
			
$(document).ready( function(){
   // Create JSON data locally   
setInterval(varName, 20000); 
   loadgridData();
});




</script>

<table id="list2"></table>
<div id="pager2"></div>
 <div id="ptoolbar" ></div>

<?php
// Finish the page
echo $OUTPUT->footer();
?>
