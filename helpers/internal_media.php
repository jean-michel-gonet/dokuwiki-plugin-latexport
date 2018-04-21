<?php

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * Contains all data needed to represent an internal media.
 *
 * Latexport Plugin: Exports to latex
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Jean-Michel Gonet <jmgonet@yahoo.com>
 */
class InternalMedia {
	/**
	 * @param string $src     media ID
	 * @param string $title   descriptive text
	 * @param string $align   left|center|right
	 * @param int    $width   width of media in pixel
	 * @param int    $height  height of media in pixel
	 * @param string $cache   cache|recache|nocache
	 * @param string $linking linkonly|detail|nolink
	 */
	
	/** Media ID.*/
	private $src;
	
	/** Descriptive text.*/
	private $title;
	
	/** left | center | right */
	private $align;
	
	/** Width of media, in pixels. */
	private $width;
	
	/** Height of media, in pixels. */
	private $height;
	
	/** cache|recache|nocache */
	private $cache;
	
	/** linkonly|detail|nolink */
	private $linking;
	
	/**
	 * Class constructor.
	 * @param string $src     media ID
	 * @param string $title   descriptive text
	 * @param string $align   left|center|right
	 * @param int    $width   width of media in pixel
	 * @param int    $height  height of media in pixel
	 * @param string $cache   cache|recache|nocache
	 * @param string $linking linkonly|detail|nolink
	 */
	function __construct($src, $title, $align, $width, $height, $cache, $linking) {

	 	$this->src = $src;
	 	$this->title = $title;
	 	$this->align = $align;
	 	$this->width = $width;
	 	$this->height = $height;
	 	$this->cache = $cache;
	 	$this->linking = $linking;

	}

	/** Media ID.*/
	function getSrc() {
		return $this->src;
	}
	
	/** Descriptive text.*/
	function getTitle() {
		return $this->title;		
	}
	
	/** left | center | right */
	function getAlign() {
		return $this->align;		
	}
	
	/** Width of media, in pixels. */
	function getWidth() {
		return $this->width;
	}
	
	/** Height of media, in pixels. */
	function getHeight() {
		return $this->height;
	}
	
	/** cache|recache|nocache */
	function getCache() {
		return $this->cache;
	}
	
	/** linkonly|detail|nolink */
	function getLinking() {
		return $this->linking;
	}

	/**
	 * Can be casted into a string.
	 */
	function __toString() {
		return "$this->src ($this->align $this->width x $this->height) '$this->title'";
	}
}
