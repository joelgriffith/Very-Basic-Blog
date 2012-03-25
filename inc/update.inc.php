<?php

// Checks to make sure user put in all fields and posted
if($_SERVER['REQUEST_METHOD'] == 'POST'
	&& $_POST['submit'] == 'Save Entry'
	&& !empty($_POST['title'])
	&& !empty($_POST['entry']))
{
	// Accesses database credentials and connect to database
	include_once 'db.inc.php';
	$db = new PDO (DB_INFO, DB_USER, DB_PASS);

	// Save the post into the database
	$sql = "INSERT INTO entries (title, entry) VALUES (?,?)";
	$stmt = $db->prepare($sql);
	$stmt->execute(array($_POST['title'], $_POST['entry']));
	$stmt->closeCursor();

	// Get the ID of the entry we just made
	$id_obj = $db->query("SELECT LAST_INSERT_ID()");
	$id = $id_obj->fetch();
	$id_obj->closeCursor();

	// Redirect new post!
	header('Location: ../?id=' .$id[0]);
	exit;
}

//If fields were blank or script was run other than posting, return to homepage.
else

{
	header('Location: ../admin.php');
}

?>