<?php

include_once 'db.inc.php';
include_once 'functions.inc.php';

class Comments
{
	// Database connections:
	public $db;

	// An array for storing comments for retrieval:
	public $comments;

	// Upon instntiation, open DB:
	public function __construct()
	{
		// Open Database:
		$this->db = new PDO(DB_INFO, DB_USER, DB_PASS);
	}

	// Display the form for comments:
	public function showCommentForm($blog_id)
	{
		return <<<FORM
<form action="/simple_blog/inc/update.inc.php" method="post" id="comment-form">
	<fieldset>
		<legend>Post a Comment!</legend>
		<label>Name<input type="text" name="name" maxlength="75" /></label>
		<label>Email<input type="text" name="email" maxlength="150" /></label>
		<label>Comment<textarea rows="10" cols="45" name="comment"></textarea>
		<input type=hidden name="blog_id" value="$blog_id" />
		<input type="submit" name="submit" value="Post Comment" />
		<input type="submit" name="submit" value="Cancel" />
	</fieldset>
</form>
FORM;
	}

	// Sanitize and save comments to DB:
	public function saveComment($p)
	{
		// Use sanitizeComment from functions.inc.php:
		$blog_id = sanitizeComment($p['blog_id']);
		$name = sanitizeComment($p['name']);
		$email = sanitizeComment($p['email']);
		$text = sanitizeComment($p['comment']);
		// Keep formatting of comments and remove extra whitespace:
		$comment = nl2br(trim($text));

		// Generate and prepare the SQL command:
		$sql = "INSERT INTO comments (blog_id, name, email, comment)
				VALUES (?, ?, ?, ?)";

		if($stmt = $this->db->prepare($sql))
		{
			// Execute the SQL, free the used memory, and return TRUE:
			$stmt->execute(array($blog_id, $name, $email, $comment));
			$stmt->closeCursor();
			return TRUE;
		}
		else
		{
			// If something went wrong!
			return FALSE;
		}
	}

	// Load the comments for viewing into memory:
	public function retrieveComments($blog_id)
	{
		// Query the comments for the specific entry:
		$sql = "SELECT id, name, email, comment, date
				FROM comments
				WHERE blog_id=?
				ORDER BY date DESC";
		$stmt = $this->db->prepare($sql);
		$stmt->execute(array($blog_id));

		// Loop through the returned rows:
		while($comment = $stmt->fetch())
		{
			// Store in memory for retrieval later:
			$this->comments[] = $comment;
		}

		// Set up a default response if no comments exist:
		if(empty($this->comments))
		{
			$this->comments[] = array(
				'id' => NULL,
				'name' => NULL,
				'email' => NULL,
				'comment' => 'There are no comments on this entry, yet.',
				'date' => NULL
				);
		}
	}

	// Generates the HTML markup for displaying comments:
	public function showComments($blog_id)
	{
		// Initialize the variable in case no comments exist:
		$dislay = NULL;

		// Load the comments for the entry:
		$this->retrieveComments($blog_id);

		// Loop through the stored comments:
		foreach($this->comments as $c)
		{
			// Prevent empty fields if no content exists:
			if(!empty($c['date']) && !empty($c['name']))
			{
				// Define the date format:
				$format = "F j, Y \a\\t g:iA";

				// Convert the MySQL to a timestamp, then format:
				$date = date($format, strtotime($c['date']));

				// Generate a byline for the comment
				$byline = "<span><strong>$c[name]</strong>[Posted on $date]</span>";

				if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == 1)
				{
					// Generate delete function link
					$admin = "<a href=\"/simple_blog/inc/update.inc.php" 
							. "?action=comment_delete&id=$c[id]\""
							. "class=\"admin\">delete</a>";
				}
				else
				{
					$admin = NULL;
				}
			}
			else
			{
				// No comments exist, set variables to NULL:
				$byline = NULL;
				$admin = NULL;
			}

			// Assemble the pieces into a formatted comment.
			$display .="<p class=\"comment\">$byline$c[comment]$admin</p>";
		}

		// Return all comments as a string
		return $display;
	}

	// Confirm that the user wishes to delete the comment:
	public function confirmDelete($id)
	{
		if(isset($_SERVER['HTTP_REFERER']))
		{
			// Store the entry URL if available:
			$url = $_SERVER['HTTP_REFERER'];
		}

		// Otherwise use the default view:
		else
		{
			$url = '../';
		}

		return <<<FORM
<html>
<head>
<title>Confirm comment delete</title>
<link rel="stylesheet" type="text/css" href="/simple_blog/css/default.css" />
</head>
<body>
<form action="/simple_blog/inc/update.inc.php" method="post">
	<fieldset>
		<legend>Are you sure?</legend>
		<p>Deleting this comment cannot be undone!</p>
		<input type="hidden" name="id" value="$id" />
		<input type="hidden" name="action" value="comment_delete" />
		<input type="hidden" name="url" value="$url" />
		<input type="submit" name="confirm" value="Yes" />
		<input type="submit" name="confirm" value="No" />
	</fieldset>
</form>
</body>
</html>
FORM;
	}

	// Delete the comment with the appropriate $id from the database:
	public function deleteComment($id)
	{
		$sql = "DELETE FROM comments
				WHERE id=?
				LIMIT 1";
		if($stmt = $this->db->prepare($sql))
		{
			// Execute the command, free used memory and return true:
			$stmt->execute(array($id));
			$stmt->closeCursor();
			return TRUE;
		}
		else
		{
			// If something goes wrong, return false:
			return FALSE;
		}
	}
}