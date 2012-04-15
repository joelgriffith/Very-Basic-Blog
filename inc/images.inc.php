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
	* @param bool $rename whether or not the image should be renamed
	* @return string the path to the resized uploaded file
	*
	*/
	
	public function processUploadedImage($file, $rename = TRUE)
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

		// Rename the file if flag is set to true:
		if($rename === TRUE) 
		{
			// Retrive the information about the image:
			$img_ext = $this->getImageExtension($type);
			
			$name = $this->renameFile($img_ext);
		}

		// Create the full path to the image for saving and loading:
		$filepath = $this->save_dir . '/simple_blog/images/' . $name;

		// Store the absolute path to move the image:
		$absolute = $_SERVER['DOCUMENT_ROOT'] . '/' . $filepath;

		// Aaaaaand now we'll save the image:
		if(!move_uploaded_file($tmp, $absolute))
		{
			throw new Exception("Couldn't save the uploaded file!");
		}

		return $filepath;
	}

	/**
	*
	* Generate a unique name for the image file
	*
	* Uses the current time and a random number to make
	* a unique name. This will stop file overwrites.
	*
	* @param string $ext the file extension for the upload
	* @return string the new file
	*
	*/
	private function renameFile($ext)
	{
		/*
		* Returns the timestamp and a random number
		*/
		return time() . '_' . mt_rand(1000, 9999) . $ext ;
	}

	/**
	*
	* Determines the file extension type of the image
	*
	* @param string $type the MIME type of the image
	* @return string the extension to be used with the file
	*/
	private function getImageExtension ($type)
	{
		switch($type) {
			case 'image/gif':
			return '.gif';

			case 'image/jpeg':
			case 'image/pjpeg':
			return '.jpg';

			case 'image/png':
			return '.png';

			default:
			throw new Exception('File type is not recognized! Check the image to make sure it\'s jpg, png, or gif.');
		}
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
		$path = $_SERVER['DOCUMENT_ROOT'] . '/' . $this->save_dir . '/simple_blog/images/';

		// Check to see if directory exists:
		if(!is_dir($path))
		{
			// Check to see if directory can be made:
			if(!mkdir($path, 0777, TRUE))
			{
				// If fails, throw execption:
				throw new Exception("Can't create the directory!");
			
			}
		}
	}
}

?>