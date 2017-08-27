<?php
/**
 * Latexport Plugin: Exports to latex
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Jean-Michel Gonet <jmgonet@yahoo.com>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * The latex renderer
 */
class renderer_plugin_latexport_tex extends Doku_Renderer {

	/**
	 * Returns the format produced by this renderer.
	 */
	function getFormat(){
		return "tex";
	}

	/**
	 * Do not make multiple instances of this class
	 */
	function isSingleton(){
		return true;
	}

	/**
	 * Initialize the rendering
	 */
	function document_start() {
		global $ID;

		// Create HTTP headers
		$output_filename = str_replace(':','-',$ID).'.tex';
		$headers = array(
				'Content-Type' => 'application/x-tex',
				'Content-Disposition' => 'attachment; filename="'.$output_filename.'";',
				);

		// store the content type headers in metadata
		p_set_metadata($ID,array('format' => array('latexport_tex' => $headers) ));

		// Starts the document:
		$this->command('documentclass', 'book');
		$this->command('begin', 'document');
	}

	/**
	 * Headers are transformed in part, chapter, section, subsection and subsubsection.
	 */
	function header($text, $level, $pos) {
		switch($level) {
			case 1:
				$this->command('part', $text);
				break;
			case 2:
				$this->command('chapter', $text);
				break;
			case 3:
				$this->command('section', $text);
				break;
			case 4:
				$this->command('subsection', $text);
				break;
			default:
				$this->command('subsubsection', $text);
				break;
		}
	}

	/**
	 * Open a paragraph.
	 */
	function p_open() {
	}

	/**
	 * Renders plain text.
	 */
	function cdata($text) {
		$this->doc .= $text;
	}

	/**
	 * Close a paragraph.
	 */
	function p_close() {
		$this->doc .= "\r\n\r\n";
	}
	/**
	 * Render a wiki internal link
	 *
	 * @param string       $link  page ID to link to. eg. 'wiki:syntax'
	 * @param string|array $title name for the link, array for media file
	 */
	function internallink($link, $title = null) {
		$this->doc .= $title;
	}
	/**
	 * Open an unordered list
	 */
	function listu_open() {
		$this->command('begin', 'itemize');
	}
	/**
	 * Open a list item
	 *
	 * @param int $level the nesting level
	 * @param bool $node true when a node; false when a leaf
	 */
	function listitem_open($level,$node=false) {
		$this->doc .= str_repeat('   ', $level).'\\item ';
	}
	/**
	 * Start the content of a list item
	 */
	function listcontent_open() {
		// Nothing to do.
	}

	/**
	 * Stop the content of a list item
	 */
	function listcontent_close() {
		$this->doc .= "\r\n";
	}

	/**
	 * Close an unordered list
	 */
	function listu_close() {
		$this->command('end', 'itemize');
	}

	/**
	 * Closes the document
	 */
	function document_end(){
		$this->command('end', 'document');
	}

	/**
	 * Adds a latex command to the document.
	 * @param name Command name.
	 * @param argument To be included in curly brackets.
	 */
	function command($name, $argument) {
		$this->doc .= '\\'.$name.'{'.$argument."}\r\n";
	}
}
