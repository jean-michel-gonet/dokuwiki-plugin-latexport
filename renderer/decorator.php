<?php

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * Base class for a decorator, containing default behavior for all decorators.
 * As a convenience it extends the Doku_Renderer.
 * The base decorator just passes all calls to the next decorator.
 * Each call returns the decorator to use for next call. This allows decorator
 * to create additional layers depending on particular conditions.
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Jean-Michel Gonet <jmgonet@yahoo.com>
 */
class decorator extends Doku_Renderer {

	/**
	 * The next decorator layer.
	 * Should be initialized by the class constructor.
	 */
	protected $decorator;

	/**
	 * Class constructor.
	 * @param decorator The next decorator layer.
	 */
	function __construct($decorator) {
		$this->decorator = $decorator;
	}


	/**
	 * Document start.
	 */
	function document_start() {
		$this->decorator = $this->decorator->document_start();
		return $this;
	}

	/**
	 * Headers are transformed in part, chapter, section, subsection and subsubsection.
	 */
	function header($text, $level, $pos) {
		$decorator = $this->decorator->header($text, $level, $pos);
		return $this;
	}

	/**
	 * Open a paragraph.
	 */
	function p_open() {
		$this->decorator = $this->decorator->p_open();
		return $this;
	}

	/**
	 * Renders plain text.
	 */
	function cdata($text) {
		$this->decorator = $this->decorator->cdata($text);
		return $this;
	}

	/**
	 * Close a paragraph.
	 */
	function p_close() {
		$this->decorator = $this->decorator->p_close();
		return $this;
	}

	/**
	 * Start emphasis (italics) formatting
	 */
	function emphasis_open() {
		$this->decorator = $this->decorator->emphasis_open();
		return $this;
	}

	/**
	 * Stop emphasis (italics) formatting
	 */
	function emphasis_close() {
		$this->decorator = $this->decorator->emphasis_close();
		return $this;
	}

	/**
	 * Start strong (bold) formatting
	 */
	function strong_open() {
		$this->decorator = $this->decorator->strong_open();
		return $this;
	}

	/**
	 * Stop strong (bold) formatting
	 */
	function strong_close() {
		$this->decorator = $this->decorator->strong_close();
		return $this;
	}

	/**
	 * Start underline formatting
	 */ 
	function underline_open() {
		$this->decorator = $this->decorator->underline_open();
		return $this;
	}

	/**
	 * Stop underline formatting
	 */
	function underline_close() {
		$this->decorator = $this->decorator->underline_close();
		return $this;
	}

	/**
	 * Render a wiki internal link.
	 * Internal links at the very beginning of an unordered item include
	 * the destination page.
	 * @param string       $link  page ID to link to. eg. 'wiki:syntax'
	 * @param string|array $title name for the link, array for media file
	 */
	function internallink($link, $title = null) {
		$this->decorator = $this->decorator->internallink($link, $title);
		return $this;
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
			$height = null, $cache = null, $linking = null) {

		$this->decorator = $this->decorator->internalmedia($src, $title, $align, $width, $height, $cache, $linking);
		return $this;
	}

	/**
	 * Open an unordered list
	 */
	function listu_open() {
		$this->decorator = $this->decorator->listu_open();
		return $this;
	}
	/**
	 * Open a list item
	 *
	 * @param int $level the nesting level
	 * @param bool $node true when a node; false when a leaf
	 */
	function listitem_open($level,$node=false) {
		$this->decorator = $this->decorator->listitem_open($level, $node);
		return $this;
	}

	/**
	 * Start the content of a list item
	 */
	function listcontent_open() {
		$this->decorator = $this->decorator->listcontent_open();
		return $this;
	}

	/**
	 * Stop the content of a list item
	 */
	function listcontent_close() {
		$this->decorator = $this->decorator->listcontent_close();
		return $this;
	}

	/**
	 * Close an unordered list
	 */
	function listu_close() {
		$this->decorator = $this->decorator->listu_close();
		return $this;
	}

	/**
	 * Receives mathematic formula from Mathjax plugin.
	 * As Mathjax already uses $ or $$ as separator, there is no
	 * need to reprocess.
	 */
	function mathjax_content($formula) {
		$this->decorator = $this->decorator->mathjax_content($formula);
		return $this;
	}

	/**
	 * Closes the document
	 */
	function document_end(){
		$this->decorator = $this->decorator->document_end();
		return $this;
	}
}
