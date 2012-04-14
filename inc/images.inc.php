<?php

class ImageHandler 
{
	// The saved images folder:
	public $save_dir;

	// Set the $save_dir once initialized:
	public function _construct($save_dir)
	{
		$this->save_dir = $save_dir;
	}

	/**
	*
	* Resizes/resamples an image uploaded via the web form
	*
	* @param array $upload the array contained in $_FILES
	* @return string the path to the resized uploaded file
	*
	*/
	
	public function processUploadedImage($file)
	{
		// Seperate the uploaded file array:
		list($name, $type, $tmp, $err, $size) = array_values($file);

		// If an error occured, throw a message:
		if($err != UPLOAD_ERR_OK){
			throw new Execption('An error occured with the upload!');
			exit;
		}

		// Check to see that the directory exists:
		$this->checkSaveDir();

		// Create the full path to the image for saving and loading:
		$filepath = $this->save_dir . $name;

		// Store the absolute path to move the image:
		$absolute = $_SERVER['DOCUMENT_ROOT'] . $filepath;

		// Aaaaaand now we'll save the image:
		if(!move_uploaded_file($tmp, $absolute))
		{
			throw new Exception("Couldn't save the uploaded file!");
		}

		return $filepath;
	}

	/**
	*
	*Ensures that the save directory exists
	*
	*Checks for the existence of the uploaded save directory,
	*and creates the directory if it doesn't exist. Creation is
	*recursive.
	*
	*@param void
	*@return void
	*/

	private function checkSaveDir()
	{
		// Determines the path to check:
		$path = $_SERVER['DOCUMENT_ROOT'] . $this->save_dir;

		// Check to see if directory exists:
		if(!is_dir($path))
		{
			// Create Directory
			if(!mkdir($path, 0777, TRUE))
			{
				// If fails, throw execption:
				throw new Exception("Can't create the directory!");
			}
		}
	}

}

?>