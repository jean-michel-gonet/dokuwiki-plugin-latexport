<?php

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once DOKU_PLUGIN . 'latexport/renderer/decorator.php';

/**
 * Special tasks for images.
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Jean-Michel Gonet <jmgonet@yahoo.com>
 */
class DecoratorImages extends Decorator {
	
	/**
	 * Class constructor.
	 * @param decorator The next decorator.
	 */
	function __construct($decorator) {
		parent::__construct($decorator);
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
	function internalmedia($src, $title = null, $align = null, $width = null, 
	                       $height = null, $cache = null, $linking = null, $positionInGroup = 0, $totalInGroup = 1) {
		$this->decorator->internalMedia($src, $title, $align, $width, 
		                                $height, $cache, $linking, $positionInGroup, $totalInGroup);
	}	
		
}
