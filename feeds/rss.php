<?php

// Include the necessary functions:
include '../inc/functions.inc.php';
include '../inc/db.inc.php';

// Open database connection:
$db = new PDO(DB_INFO, DB_USER, DB_PASS);

// Load entries:
$e = retrieveEntries($db, 'blog');

// Remove the fulldisplay flag:
array_pop($e);

// Sanitize data as always:
$e = sanitizeData($e);

// Add a content header to ensure proper execution:
header('Content-Type: application/rss+xml');

// Output the XMl declaration:
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";

?>

<rss version="2.0">
	<channel>
		<title>Basic Blog</title>
		<link>http://localhost/simple_blog/</link>
		<description>A basic blogging appliction in PHP and MySQL</description>
		<language>en-us</language>

	<?php 

	// Loop through entries and generate RSS items:
	foreach($e as $e):
		// Escape HTML to avoid errors:
		$entry = htmlentities($e['entries']);

		// Build the URL to the entry:
		$url = 'http://localhost/simple_blog/blog/' . $e['url'];

		// Format the date:
		$date = date(DATE_RSS, strtotime($e['created']))
	?>

		<item>
			<title><?php echo $e['title']; ?></title>
			<description><?php echo $e['entry']; ?></description>
			<link><?php echo $url; ?></link>
			<guid><?php echo $date; ?></guid>
			<pubDate><?php echo $e['created'] ?></pubDate>
		</item>

	<?php endforeach; ?>
	
	</channel>
</rss>