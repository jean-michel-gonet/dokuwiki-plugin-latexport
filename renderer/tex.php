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
require_once DOKU_PLUGIN . 'latexport/renderer/dumper.php';

/**
 * A façade between doku wiki and the actual tex renderer.
 * Actual renderer is dumper.php or one of its extensions.
 */
class renderer_plugin_latexport_tex extends Doku_Renderer {
	const GRAPHICSPATH = 'images/';

	/** 
	 * To create a compressed archive with all TeX resources needed
	 * to download together.
	 */
	private $archive; 

	/**
	 * Actual tex renderer.
	 */
	private $dumper;

	/**
	 * Flag indicating that an unordered item has open, and it
	 * still has no content.
	 */
	private $bareUnorderedItem;

	/**
	 * Class constructor.
	 */
	function __construct() {
		$this->archive = new ArchiveHelperZip(); 
		$this->bareUnorderedItem = false;
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
	 * Initializes the rendering.
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
		$this->dumper = new Dumper($this->archive);
		$this->dumper = $this->dumper->document_start();
	}

	/**
	 * Headers are transformed in part, chapter, section, subsection and subsubsection.
	 */
	function header($text, $level, $pos) {
		$dumper = $this->dumper->header($text, $level, $pos);
	}

	/**
	 * Open a paragraph.
	 */
	function p_open() {
		$this->dumper = $this->dumper->p_open();
	}

	/**
	 * Renders plain text.
	 */
	function cdata($text) {
		$this->dumper = $this->dumper->cdata($text);
	}

	/**
	 * Close a paragraph.
	 */
	function p_close() {
		$this->dumper = $this->dumper->p_close();
	}

	/**
	 * Start emphasis (italics) formatting
	 */
	function emphasis_open() {
		$this->dumper = $this->dumper->emphasis_open();
	}

	/**
	 * Stop emphasis (italics) formatting
	 */
	function emphasis_close() {
		$this->dumper = $this->dumper->emphasis_close();
	}

	/**
	 * Start strong (bold) formatting
	 */
	function strong_open() {
		$this->dumper = $this->dumper->strong_open();
	}

	/**
	 * Stop strong (bold) formatting
	 */
	function strong_close() {
		$this->dumper = $this->dumper->strong_close();
	}

	/**
	 * Start underline formatting
	 */ 
	function underline_open() {
		$this->dumper = $this->dumper->underline_open();
	}

	/**
	 * Stop underline formatting
	 */
	function underline_close() {
		$this->dumper = $this->dumper->underline_close();
	}

	/**
	 * Render a wiki internal link.
	 * Internal links at the very beginning of an unordered item include
	 * the destination page.
	 * @param string       $link  page ID to link to. eg. 'wiki:syntax'
	 * @param string|array $title name for the link, array for media file
	 */
	function internallink($link, $title = null) {
		$this->dumper = $this->dumper->internallink($link, $title);
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

		$this->dumper = $this->dumper->internalmedia($src, $title, $align, $width,
			$height, $cache, $linking);
	}

	/**
	 * Open an unordered list
	 */
	function listu_open() {
		$this->dumper = $this->dumper->listu_open();
	}
	/**
	 * Open a list item
	 *
	 * @param int $level the nesting level
	 * @param bool $node true when a node; false when a leaf
	 */
	function listitem_open($level,$node=false) {
		$this->dumper = $this->dumper->listitem_open($level, $node);
	}

	/**
	 * Start the content of a list item
	 */
	function listcontent_open() {
		$this->dumper = $this->dumper->listcontent_open();
	}

	/**
	 * Stop the content of a list item
	 */
	function listcontent_close() {
		$this->dumper = $this->dumper->listcontent_close();
	}

	/**
	 * Close an unordered list
	 */
	function listu_close() {
		$this->dumper = $this->dumper->listu_close();
	}

	/**
	 * Receives mathematic formula from Mathjax plugin.
	 * As Mathjax already uses $ or $$ as separator, there is no
	 * need to reprocess.
	 */
	function mathjax_content($formula) {
		$this->dumper = $this->dumper->mathjax_content($formula);
	}

	/**
	 * Closes the document
	 */
	function document_end(){
		$this->dumper = $this->dumper->document_end();

		$this->archive->closeFile();
		$this->doc = $this->archive->closeArchive();
	}
}
