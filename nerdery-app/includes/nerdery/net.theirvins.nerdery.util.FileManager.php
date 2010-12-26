<?php
class FileManager {

	/**
	 * Saves the specified file to the target directory.  File data and properties
	 * are taken from an entry in the provided files array
	 * 
	 * @param array $filesArray
	 * @param string $fileKey
	 * @param string $destinationDir
	 * @param string $destinationFile
	 */
	public static function saveFile ($filesArray, $fileKey, $destinationDir, $destinationFile) {
		if ($filesArray[$fileKey]["name"]) {
			if (!file_exists ($destinationDir)) {
				mkdir ($destinationDir);
			}
			$extension = self::getExtensionForMimeType($filesArray, $fileKey);
			$finalFile = $destinationDir . "/" . $destinationFile . "." . $extension;
			//$file = $upload_path;// . $new_cnt . "." . $extension;
			$result = move_uploaded_file ($filesArray[$fileKey]["tmp_name"], $finalFile);

			// TODO update this for GIF, PNG, etc.
			if (self::isResizableImageType($extension)) {
				$ds = new dropShadow ();
				// resize to 800 px wide IF this is a horizontal image (width > height)
				$ds->loadImage ($file);
				if (ImageSX($ds->_imgOrig) > ImageSY ($ds->_imgOrig))
					$ds->resizeToSize (800, 0);
				else if (ImageSX($ds->_imgOrig) > 800)
					$ds->resizeToSize (800, 0);
				$ds->saveFinal ($finalFile);
			}
		}
	}

	/**
	 * Returns true if the specified image type can be resized.
	 * 
	 * @param string $extension
	 */
	public static function isResizableImageType ($extension) {
		return $extension == "bmp" || $extension == "jpg" || $extension == "gif";
	}

	/**
	 * Returns a string extension for a mime type, based on an entry
	 * in the files array
	 * 
	 * @param unknown_type $filesArray
	 * @param unknown_type $fileKey
	 */
	public static function getExtensionForMimeType ($filesArray, $fileKey) {
		if ($filesArray[$fileKey]["type"] == "audio/mpeg")
			$extension = "mp3";
		else if ($filesArray[$fileKey]["type"] == "image/pjpeg")
			$extension = "jpg";
		else if ($filesArray[$fileKey]["type"] == "image/jpeg")
			$extension = "jpg";
		else if ($filesArray[$fileKey]["type"] == "image/jpg")
			$extension = "jpg";
		else if ($filesArray[$fileKey]["type"] == "image/gif")
			$extension = "gif";
		else if ($filesArray[$fileKey]["type"] == "application/x-shockwave-flash")
			$extension = "swf";
		else if ($filesArray[$fileKey]["type"] == "video/avi")
			$extension = "avi";
		else if ($filesArray[$fileKey]["type"] == "video/mpeg")
			$extension = "mpeg";
		else
			$extension = "";

		return $extension;
	}

}