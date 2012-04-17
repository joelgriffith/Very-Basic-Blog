<?php

// Include the functions so you can create a URL
include_once 'functions.inc.php';

// Include the image uploader class
include_once 'images.inc.php';

// Checks to make sure user put in all fields and posted
if($_SERVER['REQUEST_METHOD'] == 'POST'
	&& $_POST['submit'] == 'Save Entry'
	&& !empty($_POST['page'])
	&& !empty($_POST['title'])
	&& !empty($_POST['entry']))
{
	// Create a URL to save in the database
	$url = makeURL($_POST['title']);

	// Check to see if an image has been uploaded by the user:
	if(isset($_FILES['image']['tmp_name']))
	{
		try
		{
			// Instantiate the class and set a save path:
			$img = new ImageHandler('/simple_blog/images', array(350,240));

			// Process the file and store the returned path:
			$img_path = $img->processUploadedImage($_FILES['image']);

		}
		catch(Exception $e)
		{
			// If an error occured, output an error message:
			die($e->getMessage());
		}
	}
	else
	{
		// Set the image to NULL to avoid errors:
		$img_path = NULL;
	}

	// Accesses database credentials and connect to database
	include_once 'db.inc.php';
	$db = new PDO (DB_INFO, DB_USER, DB_PASS);

	// If editing, edit the existing entry
	if(!empty($_POST['id']))
	{
		$sql = "UPDATE entries
				SET title = ?, image = ?, entry = ?, url = ?
				WHERE id = ?
				LIMIT 1";
		$stmt = $db->prepare($sql);
		$stmt->execute(
			array(
				$_POST['title'],
				$img_path,
				$_POST['entry'],
				$url,
				$_POST['id']
				)
			);
		$stmt->closeCursor();
	}
	
	// Otherwise, save the post into the database as a new entry.
	else
	{
		$sql = "INSERT INTO entries (page, title, image, entry, url) 
				VALUES (?, ?, ?, ?, ?)";
		$stmt = $db->prepare($sql);
		$stmt->execute(
			array(
				$_POST['page'],
				$_POST['title'],
				$img_path,
				$_POST['entry'], 
				$url
				)
			);
		$stmt->closeCursor();
	}
	// Sanitize that data!
	$page = htmlentities(strip_tags($_POST['page']));

	// Redirect new post!
	header('Location: /simple_blog/' . $page . '/' . $url);
	exit;
}

//If fields were blank or script was run other than posting, return to homepage.
else

{
	header('Location: ../');
}

?>