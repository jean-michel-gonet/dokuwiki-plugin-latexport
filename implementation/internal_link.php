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

	/** The heading level in which the link was found. */
	private $headingLevel;

	/**
	 * Class constructor.
	 * @param link The link, as provided by internallink method.
	 * @param headingLevel The heading level in which the link was found.
	 * @param title The title, as provided by internallink method.
	 */
	function __construct($link, $headingLevel, $title = null) {
		$this->link = $link;
		if ($headingLevel < 2) {
			$this->headingLevel = 2;
		} else {
			$this->headingLevel = $headingLevel;
		}
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

	function getHeadingLevel() {
		return $this->headingLevel;
	}

	function toString() {
		return "$this->title ($this->headingLevel) --- $this->link";
	}
}
