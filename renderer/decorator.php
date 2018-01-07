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
		$this->decorator->document_start();
	}

	/**
	 * Headers are transformed in part, chapter, section, subsection and subsubsection.
	 */
	function header($text, $level, $pos) {
		$this->decorator->header($text, $level, $pos);
	}

	/**
	 * Open a paragraph.
	 */
	function p_open() {
		$this->decorator->p_open();
	}

	/**
	 * Renders plain text.
	 */
	function cdata($text) {
		$this->decorator->cdata($text);
	}

	/**
	 * Close a paragraph.
	 */
	function p_close() {
		$this->decorator->p_close();
	}

	/**
	 * Start emphasis (italics) formatting
	 */
	function emphasis_open() {
		$this->decorator->emphasis_open();
	}

	/**
	 * Stop emphasis (italics) formatting
	 */
	function emphasis_close() {
		$this->decorator->emphasis_close();
	}

	/**
	 * Start strong (bold) formatting
	 */
	function strong_open() {
		$this->decorator->strong_open();
	}

	/**
	 * Stop strong (bold) formatting
	 */
	function strong_close() {
		$this->decorator->strong_close();
	}

	/**
	 * Start underline formatting
	 */ 
	function underline_open() {
		$this->decorator->underline_open();
	}

	/**
	 * Stop underline formatting
	 */
	function underline_close() {
		$this->decorator->underline_close();
	}

	/**
	 * Render a wiki internal link.
	 * Internal links at the very beginning of an unordered item include
	 * the destination page.
	 * @param string       $link  page ID to link to. eg. 'wiki:syntax'
	 * @param string|array $title name for the link, array for media file
	 */
	function internallink($link, $title = null) {
		$this->decorator->internallink($link, $title);
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

		$this->decorator->internalmedia($src, $title, $align, $width, $height, $cache, $linking);
	}

	/**
	 * Open an unordered list
	 */
	function listu_open() {
		$this->decorator->listu_open();
	}

	/**
	 * Open a list item
	 *
	 * @param int $level the nesting level
	 * @param bool $node true when a node; false when a leaf
	 */
	function listitem_open($level,$node=false) {
		$this->decorator->listitem_open($level, $node);
	}

	/**
	 * Start the content of a list item
	 */
	function listcontent_open() {
		$this->decorator->listcontent_open();
	}

	/**
	 * Stop the content of a list item
	 */
	function listcontent_close() {
		$this->decorator->listcontent_close();
	}

    /**
     * Close a list item
     */
    function listitem_close() {
		$this->decorator->listitem_close();
    }

	/**
	 * Close an unordered list
	 */
	function listu_close() {
		$this->decorator->listu_close();
	}

	/**
	 * Receives mathematic formula from Mathjax plugin.
	 * As Mathjax already uses $ or $$ as separator, there is no
	 * need to reprocess.
	 */
	function mathjax_content($formula) {
		$this->decorator->mathjax_content($formula);
	}

	/**
	 * Closes the document
	 */
	function document_end(){
		$this->decorator->document_end();
	}
}
