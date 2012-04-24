<?php

// Start session:
session_start();

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
			$img = new ImageHandler('/simple_blog/images/', array(350,240));

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

// If a comment is being posted, handle it here!
else if($_SERVER['REQUEST_METHOD'] == 'POST'
		&& $_POST['submit'] == 'Post Comment')
{
	// Include and instantiate the Comments class:
	include_once 'comments.inc.php';
	$comments = new Comments();

	// Save the Comments:
	if($comments->saveComment($_POST))
	{
		// If available, store the entry the user came from:
		if(isset($_SERVER['HTTP_REFERER']))
		{
			$loc = $_SERVER['HTTP_REFERER'];
		}
		else
		{
			$loc = '../';
		}

		// Send the user back to the entry:
		header('Location: ' . $loc);
		exit;
	}

	// If saving fails, ouput an error message:
	else
	{
		exit('Something went wrong while saving the comment!');
	}
}

// If the delete link was clicked on a comment, confirm it here:
else if($_GET['action'] == 'comment_delete')
{
	// Include the necessary files and instantiate comments class:
	include_once 'comments.inc.php';
	$comments = new Comments();
	echo $comments->confirmDelete($_GET['id']);
	exit;
}

// If the confirmDelete() form was submitted, handle it here:
else if($_SERVER['REQUEST_METHOD'] == 'POST'
		&& $_POST['action'] == 'comment_delete')
{
	// If set, store the URL from which the comment came:
	$loc = isset($_POST['url']) ? $_POST['url'] : '../';

	// If the user clicked "yes" to delete:
	if($_POST['confirm'] == 'Yes')
	{
		// Include and instantiate the comments clase:
		include_once 'comments.inc.php';
		$comments = new Comments();

		// Delete the comment and return to the entry:
		if($comments->deleteComment($_POST['id']))
		{
			header('Location:'.$loc);
			exit;
		}

		// If deleting fails, output an error message:
		else
		{
			exit('Could not delete the comment');
		}
	}

	// If the user clicked No, do nothing and return to the entry:
	else
	{
		header('Location:'.$loc);
		exit;
	}

}

// If user is trying to log in, check here:
else if($_SERVER['REQUEST_METHOD'] == 'POST'
		&& $_POST['action'] == 'login'
		&& !empty($_POST['username'])
		&& !empty($_POST['password']))
{
	// Include the database creds and connect:
	include_once 'db.inc.php';
	$db = new PDO(DB_INFO, DB_USER, DB_PASS);
	$sql = "SELECT COUNT(*) AS num_users
			FROM admin
			WHERE username=?
			AND password=SHA1(?)";
	$stmt = $db->prepare($sql);
	$stmt->execute(array($_POST['username'], $_POST['password']));
	$response = $stmt->fetch();
	if($response['num_users'] > 0)
	{
		$_SESSION['loggedin'] = 1;
	}
	else
	{
		$_SESSION['loggedin'] = NULL;
	}
	header('Location: /simple_blog/');
	exit;
}

// If an admin is being created, it is dealt here:
else if($_SERVER['REQUEST_METHOD'] == 'POST'
		&& $_POST['action'] == 'createuser'
		&& !empty($_POST['username'])
		&& !empty($_POST['password']))
{
	// Include the DB credentials:
	include_once 'db.inc.php';
	$db = new PDO(DB_INFO, DB_USER, DB_PASS);
	$sql = "INSERT INTO admin (username, password)
			VALUES(?, SHA1(?))";
	$stmt = $db->prepare($sql);
	$stmt->execute(array($_POST['username'], $_POST['password']));
	header('Location: /simple_blog/');
	exit;
}

// Logging-out is dealt with here:
else if($_GET['action'] == 'logout')
{
	session_destroy();
	header('Location: ../');
	exit;
}

//If fields were blank or script was run other than posting, return to homepage.
else
{
	header('Location: ../');
}