<?php

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once DOKU_PLUGIN . 'latexport/renderer/decorator.php';
require_once DOKU_PLUGIN . 'latexport/helpers/internal_media.php';

/**
 * Can make groups of images if internal media are only separated by spaces.
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Jean-Michel Gonet <jmgonet@yahoo.com>
 */
class DecoratorImages extends Decorator {
	
	private $internalMediaGroup;
	
	private $groupHasInternalMedia;
	
	/**
	 * Class constructor.
	 * @param decorator The next decorator.
	 */
	function __construct($decorator) {
		parent::__construct($decorator);
		$this->internalMediaGroup = [];
		$this->groupHasInternalMedia = FALSE;
	}

	/**
	 * Receives an internal media
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

		// New media are temporarily stored in the list.
		$this->internalMediaGroup[] = new InternalMedia($src, $title, $align, $width, $height, $cache, $linking);
		$this->groupHasInternalMedia = TRUE;
	}	

	/**
	 * Any command different than white text closes the group of media.
	 */
	function any_command() {
		$this->dumpInternalMediaGroup();
	}

	/**
	 * Multiple images can be separated by spaces.
	 */
	function cdata($text) {
		// If text contains only spaces, then we accept more
		// media in the group.
		if (!ctype_space($text)) {
			// Otherwise, we dump the group.
			$this->dumpInternalMediaGroup();
		}
		
		// In any case, propagate the text:
		$this->decorator->cdata($text);					
	}

	/**
	 * Renders all images in the group.
	 * And empties the group.
	 */
	private function dumpInternalMediaGroup() {
		if ($this->groupHasInternalMedia) {
			$positionInGroup = 0;
			$totalInGroup = count($this->internalMediaGroup);
			foreach($this->internalMediaGroup as $internalMedia) {
				$this->decorator->internalmedia(
					$internalMedia->getSrc(),
					$internalMedia->getTitle(),
					$internalMedia->getAlign(),
					$internalMedia->getWidth(),
					$internalMedia->getHeight(),
					$internalMedia->getCache(),
					$internalMedia->getLinking(),
					$positionInGroup ++,
					$totalInGroup);
			}	
		}
		$this->internalMediaGroup = [];
		$this->groupHasInternalMedia = FALSE;
	}		
}
