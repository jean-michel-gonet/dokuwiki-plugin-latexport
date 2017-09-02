<?php
/**
 * Latexport Plugin: Exports to latex
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Jean-Michel Gonet <jmgonet@yahoo.com>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once DOKU_PLUGIN . 'latexport/helpers/archive_helper_zip.php';

/**
 * The latex renderer
 */
class renderer_plugin_latexport_tex extends Doku_Renderer {

	/** 
	 * To create a compressed archive with all TeX resources needed
	 * to download together.
	 */
	private $archive; 


	function __construct() {
		$this->archive = new ArchiveHelperZip(); 
	}

	/**
	 * Returns the mode name produced by this renderer.
	 */
	function getFormat(){
		return "latexport";
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
		$output_filename = str_replace(':','-',$ID).'.zip';
		$headers = array(
				'Content-Type' => $this->archive->getContentType(),
				'Content-Disposition' => 'attachment; filename="'.$output_filename.'";',
				);

		// store the content type headers in metadata
		p_set_metadata($ID,array('format' => array('latexport_tex' => $headers) ));

		// Starts the archive:
		$this->archive->startArchive();
		$this->archive->startFile(str_replace(':','-',$ID).'.tex');

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
		$this->content($text);
	}

	/**
	 * Close a paragraph.
	 */
	function p_close() {
		$this->content("\r\n\r\n");
	}
	/**
	 * Start emphasis (italics) formatting
	 */
	function emphasis_open() {
		$this->content("\\emph{");
	}

	/**
	 * Stop emphasis (italics) formatting
	 */
	function emphasis_close() {
		$this->content("}");
	}
	/**
	 * Start strong (bold) formatting
	 */
	function strong_open() {
		$this->content("\\textbf{");	
	}

	/**
	 * Stop strong (bold) formatting
	 */
	function strong_close() {
		$this->content("}");
	}
	/**
	 * Start underline formatting
	 */ 
	function underline_open() {
		$this->content("\\underline{");
	}

	/**
	 * Stop underline formatting
	 */
	function underline_close() {
		$this->content("}");
	}
	/**
	 * Render a wiki internal link
	 *
	 * @param string       $link  page ID to link to. eg. 'wiki:syntax'
	 * @param string|array $title name for the link, array for media file
	 */
	function internallink($link, $title = null) {
		$this->content($title);
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
		$this->content(str_repeat('   ', $level).'\\item ');
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
		$this->content("\r\n");
	}

	/**
	 * Close an unordered list
	 */
	function listu_close() {
		$this->command('end', 'itemize');
	}

	/**
	 * Receives mathematic formula from Mathjax plugin.
	 * As Mathjax already uses $ or $$ as separator, there is no
	 * need to reprocess.
	 */
	function mathjax_content($formula) {
		$this->content("$formula");
	}

	/**
	 * Closes the document
	 */
	function document_end(){
		$this->command('end', 'document');
		$this->archive->closeFile();
		$this->doc = $this->archive->closeArchive();
	}

	/**
	 * Adds a latex command to the document.
	 * @param name Command name.
	 * @param argument To be included in curly brackets.
	 */
	function command($name, $argument) {
		$this->archive->appendContent('\\'.$name.'{'.$argument."}\r\n");
	}

	/**
	 * Adds simple content to the document.
	 * @param c The content.
	 */
	function content($c) {
		$this->archive->appendContent($c);
	}
}
