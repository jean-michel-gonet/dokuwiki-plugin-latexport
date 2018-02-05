<?php

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once DOKU_PLUGIN . 'latexport/renderer/decorator.php';

/**
 * Final tex decorator, takes care of internal media when they're images.
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Jean-Michel Gonet <jmgonet@yahoo.com>
 */
class DecoratorImages extends Decorator {
	
	const GRAPHICSPATH = 'images/';

	/** 
	 * To archive images.
	 */
	private $archive;
	
	/**
	 * Class constructor.
	 * @param archive To place images.
	 * @param decorator The next decorator.
	 */
	function __construct($archive, $decorator) {
		parent::__construct($decorator);
		$this->archive = $archive;
	}

	/**
	 * Render an internal media file
	 *
	 * @param string $src     media ID
	 * @param string $title   descriptive text
	 * @param string $align   left|center|right
	 * @param int    $width   width of media in pixel
	 * @param int    $height  height of media in pixel
	 * @param string $cache   cache|recache|nocache
	 * @param string $linking linkonly|detail|nolink
	 */
	function internalmedia($src, $title = null, $align = null, $width = null, $height = null, $cache = null, $linking = null) {
		$filename = $this->obtainFilename($src);
		if ($this->isPrintable($filename)) {
			list($width, $height) = getimagesize($filename);
			$this->decorator->internalmedia($this->insertImage($filename), $title, $align, $width, $height, $cache, $linking);
		} else {
			$this->decorator->cdata($title);
		}
	}	
	
	/**
	 * Returns true if provided filename's extension is of a printable media.
	 * @param filename String the file name.
	 * @return boolean true if file is printable.
	 */
	function isPrintable($filename) {
		$ext = pathinfo($filename, PATHINFO_EXTENSION);

		switch($ext) {
			case "jpg":
			case "jpeg":
			case "gif":
			case "png":
				return true;

			default:
				return false;
		}
	}

	/**
	 * Obtains the filesystem path to the specified resource.
	 * @param $src String The resource.
	 * @return String The file name.
	 */
	function obtainFilename($src) {
		global $ID;
		list($src, $hash) = explode('#', $src, 2);
		resolve_mediaid(getNS($ID), $src, $exists, $this->date_at, true);
		return mediaFN($src);
	}

	/**
	 * Inserts the specified file.
	 * @param The physical path to the file.
	 * @return The TeX-ified name of the file.
	 */
	function insertImage($filename) {
		$baseFilename = $this->texifyFilename(basename($filename));
		$this->archive->insertContent(self::GRAPHICSPATH.$baseFilename, file_get_contents($filename));
		return $baseFilename;
	}
	
}
