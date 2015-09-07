<?php

$id = intval($_REQUEST['id']);
$name = $_REQUEST['name'];
$description = $_REQUEST['description'];

include 'conn.php';

$sql = "update mdl_form set name='$name',description='$description' where id='$id'";
@mysql_query($sql);
echo json_encode(array(
	'id' => $id,
	'name' => $name,
	'description' => $description
));
?>