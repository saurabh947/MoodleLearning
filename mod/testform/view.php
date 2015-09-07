<?php

require_once('../../config.php');
require_once('lib.php');
$PAGE->requires->js('/mod/testform/javascript/jquery.js');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // testform instance ID - it should be named as the first character of the module

if ($id) {
    $cm         = get_coursemodule_from_id('testform', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $testform  = $DB->get_record('testform', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $testform  = $DB->get_record('testform', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $testform->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('testform', $testform->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);

add_to_log($course->id, 'testform', 'view', "view.php?id={$cm->id}", $testform->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/testform/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($testform->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// other things you may want to set - remove if not needed
//$PAGE->set_cacheable(false);
//$PAGE->set_focuscontrol('some-html-id');
//$PAGE->add_body_class('testform-'.$somevar);

// Output starts here
echo $OUTPUT->header();
?>

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta name="keywords" content="jquery,ui,easy,easyui,web">
	<meta name="description" content="easyui help you build your web page easily!">
	<link rel="stylesheet" type="text/css" href="javascript/easyui.css">
	<link rel="stylesheet" type="text/css" href="javascript/icon.css">
	<link rel="stylesheet" type="text/css" href="javascript/demo.css">
	<script type="text/javascript" src="javascript/jquery-1.9.1.js"></script>
	<script type="text/javascript" src="javascript/jquery.easyui.min.js"></script>
	<script type="text/javascript" src="javascript/jquery.edatagrid.js"></script>
	<script type="text/javascript">
		$(function(){
			$('#dg').edatagrid({
				url: 'javascript/get_users.php',
				saveUrl: 'javascript/save_user.php',
				updateUrl: 'javascript/update_user.php',
				destroyUrl: 'javascript/destroy_user.php'
			});
		});
	</script>
</head>
<body>
	<h2>List of Subjects:</h2>
	<div class="demo-info" style="margin-bottom:10px">
		<div class="demo-tip icon-tip">&nbsp;</div>
		<div>Double click a row to begin editing.</div>
	</div>
	
	<table width="576" id="dg" style="width:700px;height:250px" title="Form Entries"
			toolbar="#toolbar" pagination="true" idField="id"
			rownumbers="true" fitColumns="true" singleSelect="true">
		<thead>
			<tr>
				<th field="id" width="203" editor="{type:'validatebox',options:{required:true}}">ID</th>            
				<th field="name" width="203" editor="{type:'validatebox',options:{required:true}}">Name</th>
				<th field="description" width="154" editor="{type:'validatebox',options:{required:true}}">Description</th>
			</tr>
		</thead>
	</table>
	<div id="toolbar">
		<a href="#" class="easyui-linkbutton" iconCls="icon-add" plain="true" onClick="javascript:$('#dg').edatagrid('addRow')">New</a>
		<a href="#" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onClick="javascript:$('#dg').edatagrid('destroyRow')">Destroy</a>
		<a href="#" class="easyui-linkbutton" iconCls="icon-save" plain="true" onClick="javascript:$('#dg').edatagrid('saveRow')">Save</a>
		<a href="#" class="easyui-linkbutton" iconCls="icon-undo" plain="true" onClick="javascript:$('#dg').edatagrid('cancelRow')">Cancel</a>
	</div>
	
</body>
</html>

<?php

	echo $OUTPUT->footer(); 
	