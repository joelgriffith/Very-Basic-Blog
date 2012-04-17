<?php

class ImageHandler 
{
	// The saved images folder:
	public $save_dir;
	public $max_dims;

	// Set the $save_dir and image dimensions once initialized:
	public function _construct($save_dir, $max_dims = array(350, 240))
	{
		$this->save_dir = $save_dir;
		$this->max_dims = $max_dims;
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

		// Generate the resized image:
		$this->doImageResize($tmp);

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
			// Check to see if directory can be made, this will also make the directory:
			if(!mkdir($path, 0777, TRUE))
			{
				// If fails, throw execption:
				throw new Exception("Can't create the directory!");
			
			}
		}
	}

	/**
	*
	* Determines the new dimensions for an image
	*
	*@param string $img the path to upload
	*@return array the new and original image dimensions
	*/
	private function getNewDims($img)
	{
		// Get the dimensions for resizing:
		list($src_w, $src_h) = getimagesize($img);
		$max_w = 350;
		$max_h = 240;

		// Check that the image is bigger than the maximum dimensions:
		if($src_w > $max_w || $src_h > $max_h)
		{
			// Determine the ratio to which the image will be resized:
			$s = min($max_w/$src_w, $max_h/$src_h);
		}
		else
		{
			// If the original is smaller than max_dims, keep it the same:
			$s = 1;
		}

		// Get new dims!
		$new_w = round($src_w * $s);
		$new_h = round($src_h * $s);

		// Return New dimensions:
		return array($new_w, $new_h, $src_w, $src_h);
	}

	/**
	*
	* Determines how to process images
	*
	* Uses the MIME type of the image to determine
	* what image handling function to use. This icreases
	* performance of the script overall.
	*
	* @param string $img the path to the upload
	* @param array the image type-specific function to use.
	*/
	private function getImageFunctions($img)
	{
		$info = getimagesize($img);

		switch($info['mime'])
		{
			case 'image/jpeg':
			case 'image/pjpeg':
				return array('imagecreatefromjpeg', 'imagejpeg');
				break;

			case 'image/gif':
				return array('imagecreatefromgif', 'imagegif');
				break;

			case 'image/png':
				return array('imagecreatefrompng', 'imagepng');
				break;

			default:
				return FALSE;
				break;
		}
	}
	
	/**
	*
	* Generates a "clear" background for resizing.
	*
	* This function generates a "clear" background
	* as opposed to imagecreatetruecolor() which 
	* set's up a "black" image. This looks particularly
	* bad when PNG's are being resized.
	*
	* @param array $max_dims
	* @return void
	*/
	private function imageCreateTransparent($width, $height) 
	{ 
		// Create the blank image with black:
	    $clearImage = imagecreatetruecolor($width, $height);
	    imagesavealpha($clearImage, true);

	    // Select the color black to replace as transparent:
	    $transparent = imagecolorallocatealpha($clearImage, 0, 0, 0, 127);
	    
	    // Run the function to tranform black to clear:
	    imagefill($clearImage, 0, 0, $transparent);
	    return $clearImage;
	}
	

	/**
	*
	* Generates a resampled and resized image
	*
	* Creates and saves a new image based on the new dimensions
	* and image type-specific functions determined by other
	* class methods.
	*
	* @param array $img the path to the upload
	* @return void
	*/
	private function doImageResize($img)
	{
		// Determine the new dimensions:
		$d = $this->getNewDims($img);

		// Determine what functions to use:
		$funcs = $this->getImageFunctions($img);

		// Create the image resources for resampling:
		$src_img = $funcs[0]($img);
		$new_img = $this->imageCreateTransparent($d[0], $d[1]);

		if(imagecopyresampled(
			$new_img, $src_img, 0, 0, 0, 0, $d[0], $d[1], $d[2], $d[3]
			))
			{
				imagedestroy($src_img);
				if($new_img && $funcs[1]($new_img, $img))
				{
					imagedestroy($new_img);
				}
				else
				{
					throw new Exception('Failed to save the new image!');
				}
			}
			else
			{
				throw new Exception('Could not resample the image!');
			}
	}

}

?>