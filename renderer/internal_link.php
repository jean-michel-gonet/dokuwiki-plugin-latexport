<?php

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * Contains all data needed to build an internal link.
 *
 * Latexport Plugin: Exports to latex
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Jean-Michel Gonet <jmgonet@yahoo.com>
 */
class InternalLink {

	/** The internal link. */
	private $link;

	/** The link title. */
	private $title;
	
	/**
	 * Class constructor.
	 * @param link The link, as provided by internallink method.
	 * @param title The title, as provided by internallink method.
	 */
	function __construct($link, $title = null) {
		$this->link = $link;
		$this->title = $title;
	}

	/** The internal link. */
	function getLink() {
		return $this->link;
	}
	
	/** The link title. */
	function getTitle() {
		return $this->title;
	}
	
	function toString() {
		return $this->title." --- ".$this->link;
	}
}
