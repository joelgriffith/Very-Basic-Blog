<?php

function retrieveEntries($db, $id=NULL)
{
	// If an entry ID was supplied, load the associated entry
	if(isset($id))
	{
		// Load the specific entry
		$sql = "SELECT title, entry
				FROM entries
				WHERE id=?
				LIMIT 1";
		$stmt = $db->prepare($sql);
		$stmt->exectute(array($_GET['id']));

		// Save the returned entry array
		$e = $stmt->fetch();

		// Set for full display
		$fulldisp = 1;
	}

	// Otherwise, load all entries
	else
	{
		$sql = "SELECT id, title
				FROM entries
				ORDER BY created DESC";

		// Loop through all entries
		foreach($db->query($sql) as $row){
			$e[] = array (
				'id' => $row['id'],
				'title' => $row['title']
				);
		}

		// Set the fulldisp flag for multiple entries
		$fulldisp = 0;

		// If no entry is returned, send a full message
		if(!is_array($e))
		{
			$fulldisp = 1;
			$e = array (
				'title' => 'No Entries Yet!',
				'entry' => '<a href="/admin.php">Post an Entry!</a>'
				);
		}
	}

	// Return loaded data
	array_push($e, $fulldisp);

	return $e;
}

function sanitizeData($data)
{
	//If data is not an array, run strip_tags()
	if(!is_array($data))
	{
		// Remove all tags except <a> tags
		return strip_tags($data, "<a>");
	}

	// If $data is an array, process each element
	else
	{
		return array_map('sanitizeData', $data);
	}
}

?>