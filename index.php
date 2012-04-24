<?php

	session_start();

	// Retrieve necessary files
	include_once 'inc/functions.inc.php';
	include_once 'inc/db.inc.php';

	// Access Database
	$db = new PDO(DB_INFO, DB_USER, DB_PASS);

	// Check for page ID, sanitize data
	if(isset($_GET['page']))
	{
		$page = $_GET['page'];
	}
	else
	{
		$page = 'blog';
	}
	// Check for an entry url in the url
	$url = (isset($_GET['url'])) ? $_GET['url'] : NULL;

	// Load entries
	$e = retrieveEntries($db, $page, $url);

	// Retrieve full display flag from $e array (last item)
	$fulldisp = array_pop($e);

	// Sanitize the data to ensure it's an array
	$e = sanitizeData($e);

	// Check to see if images are in posts:
	function formatImage($img=NULL, $alt=NULL)
	{
		if($img != NULL)
		{
			return '<img src="' . $img . '" alt="' . $alt . '" />';
		}
		else
		{
			return NULL;
		}
	}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="/simple_blog/css/default.css" type="text/css" />
	<link rel="alternate" type="application/rss+xml" title="Basic Blog - RSS 2.0" href="/simple_blog/feeds/rss.php" />
	<title> Simple Blog </title>
</head>

<body>
	<div class="container">
		<h1>Basic Blog</h1>
		
		<div id="nav">
			<ul id="menu">
				<li><a href="/simple_blog/blog/">Blog</a></li>
				<li><a href="/simple_blog/about/">About the Author</a></li>
			</ul>
		</div>

		<?php if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == 1) : ?>
			<p id="control_panel">You are logged in! <a href="/simple_blog/inc/update.inc.php?action=logout">Log out</a>.</p>
		<?php endif; ?>
		
		<div id="entries">
			
			<?php
			
			// Check if full display is needed.
			if ($fulldisp==1)
			{

				// Get the URL if one wasn't passed
				$url = (isset($url)) ? $url : $e['url'];
				if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == 1)
				{
					// Generate Edit/Delete Links
					$admin = adminLinks($page, $url);
				}
				else
				{
					$admin = array('edit' => NULL, 'delete' => NULL);
				}
				// Format the image if one exists:
				$img = formatImage($e['image'], $e['title']);

				if($page=='blog')
				{
					// Load the comments!
					include_once 'inc/comments.inc.php';
					$comments = new Comments();
					$comment_disp = $comments->showComments($e['id']);
					$comment_form = $comments->showCommentForm($e['id']);
				}
				else
				{
					$comments_form = NULL;
				}

			?>
				<h2><?php echo $e['title'] ?></h2>				
				<?php if ($img !== NULL) { ?>
					<p><?php echo $img ?></p>
					<?php } ?>				
				<p><?php echo $e['entry'] ?></p>
				<p>
					<?php echo $admin['edit'] ?>
					<?php if($page == 'blog') echo $admin['delete'] ?>
				</p>
				<?php if ($page == 'blog'): ?>
				<p class="backlink">
					<a href="./">Back to Latest Entries</a>
				</p>
				<h3>Comments for this Post</h3>
				<?php echo $comment_disp, $comment_form; endif; ?>

			<?php
			
			}

			// If full dispaly is 0 and no entry selected, show all.
			else
			{
				// Loop through all entries to post
				foreach($e as $entry){

			?>
				<p>
					<a href="/simple_blog/<?php echo $entry['page'] ?>/<?php echo $entry['url'] ?>">
						<?php echo $entry['title'] ?>
					</a>
				</p>

			<?php 
				}// End Loop 
			} //End Else Statement
			?>

			<p class="backlink">
			<?php
			if($page == 'blog' && isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == 1):
			?>
				<a href="/simple_blog/admin/<?php echo $page ?>">
					Post a New Entry
				</a>
			<?php endif; ?>
			</p>
			<p class="backlink">
				<a href="/simple_blog/feeds/rss.php">
					Subscribe via RSS!
				</a>
			</p>

		</div>

	</div>

</body>

</html>