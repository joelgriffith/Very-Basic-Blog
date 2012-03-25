<?php
	// Retrieve necessary files
	include_once 'inc/functions.inc.php';
	include_once 'inc/db.inc.php';

	// Access Database
	$db = new PDO(DB_INFO, DB_USER, DB_PASS);

	// Check for an entry ID in the url
	$id = (isset($_GET['id'])) ? (int) $_GET['id'] : NULL;

	// Load entries
	$e = retrieveEntries($db, $id);

	// Retrieve full display flag from $e array (last item)
	$fulldisp = array_pop($e);

	// Sanitize the data to ensure it's an array
	$e = sanitizeData($e);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="/~joelgriffith/blog/css/default.css" type="text/css" />
	<title> Simple Blog </title>
</head>

<body>
	<h1>Basic Blog</h1>

	<div id="entries">
		
		<?php
		
		// Check if full display is needed.
		if ($fulldisp==1)
		{

		?>
			<h2><?php echo $e['title'] ?></h2>
			<p><?php echo $e['entry'] ?></p>
			<p class="backlink">
				<a href="./">Back to Latest Entries</a>
			</p>

		<?php
		
		}

		// If full dispaly is 0 and no entry selected, show all.
		else
		{
			// Loop through all entries to post
			foreach($e as $entry){

		?>
			<p>
				<a href="?id=<?php echo $entry['id'] ?>">
					<?php echo $entry['title'] ?>
				</a>
			</p>

		<?php
			}// End Loop
		} //End Else Statement
		?>

		<p class="backlink">
			<a href="admin.php">Post a New Entry</a>
		</p>

	</div>

</body>

</html>