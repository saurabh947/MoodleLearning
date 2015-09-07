<?php

$id = $_REQUEST['id'];
$name = $_REQUEST['name'];
$description = $_REQUEST['description'];

include 'conn.php';

$sql = "insert into mdl_form(name,description) values('$name','$description')";
@mysql_query($sql);
echo json_encode(array(
	'id' => mysql_insert_id(),
	'name' => $name,
	'description' => $description
));

?>