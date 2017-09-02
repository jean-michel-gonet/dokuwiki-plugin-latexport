<?php
class ArchiveHelperZip {

	/** The temporary file name of the Zip archive. */
	private $temporaryZipFilename;

	/** Holds the Zip archive builder. */
	private $zip;

	/** Name of the file currently being appended into the ZIP archive. */
	private $currentFilename;

	/** Content of the file currently being appended into the ZIP archive. */
	private $currentContent;

	/**
	 * Returns the MIME type of this kind of archive.
	 */
	function getContentType() {
		return "application/zip";
	}	

	/**
	 * Initializes the temporary ZIP archive.
	 */
	function startArchive() {
		$this->temporaryZipFilename = tempnam(sys_get_temp_dir(), "zip");
		$this->zip = new ZipArchive();
		$this->zip->open($this->temporaryZipFilename, ZipArchive::OVERWRITE);
	}

	/**
	 * Starts a new file in the ZIP archive.
	 */
	function startFile($filename) {
		$this->currentFilename = $filename;
		$this->currentContent = "";
	}

	/**
	 * Appends content to current file.
	 */
	function appendContent($content) {
		$this->currentContent .= $content;
	}

	/**
	 * Close current file.
	 */
	function closeFile() {
		$this->zip->addFromString($this->currentFilename, $this->currentContent);
		$this->currentFilename = "";
		$this->currentContent = "";
	}
	/**
	 * Inserts a complete entry.
 	 */
	function insertContent($filename, $content) {
		$this->zip->addFromString($filename, $content);
	}

	/**
	 * Closes the ZIP archive and returns its whole content as a string.
	 * @return Content of the ZIP archive.
	 */
	function closeArchive() {
		$this->zip->close();
		return file_get_contents($this->temporaryZipFilename);
		unlink($this->temporaryZipFilename);
	}

}
