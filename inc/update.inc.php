<?php

// Include the functions so you can create a URL
include_once 'functions.inc.php';

// Checks to make sure user put in all fields and posted
if($_SERVER['REQUEST_METHOD'] == 'POST'
	&& $_POST['submit'] == 'Save Entry'
	&& !empty($_POST['page'])
	&& !empty($_POST['title'])
	&& !empty($_POST['entry']))
{
	// Create a URL to save in the database
	$url = makeURL($_POST['title']);

	// Accesses database credentials and connect to database
	include_once 'db.inc.php';
	$db = new PDO (DB_INFO, DB_USER, DB_PASS);

	// Save the post into the database
	$sql = "INSERT INTO entries (page, title, entry, url) 
			VALUES (?,?,?,?)";
	$stmt = $db->prepare($sql);
	$stmt->execute(array($_POST['page'], $_POST['title'], $_POST['entry'], $url));
	$stmt->closeCursor();

	// Sanitize that data!
	$page = htmlentities(strip_tags($_POST['page']));

	// Redirect new post!
	header('Location: /simple_blog/'.$page.'/'.$url);
	exit;
}

//If fields were blank or script was run other than posting, return to homepage.
else

{
	header('Location: ../');
}

?>