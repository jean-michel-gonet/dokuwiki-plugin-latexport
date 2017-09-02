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
	const GRAPHICSPATH = 'images/';

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
		$this->command('usepackage', 'graphicx');
		$this->command('graphicspath', ' {'.self::GRAPHICSPATH.'} ');
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
		global $ID;
		list($src, $hash) = explode('#', $src, 2);
		resolve_mediaid(getNS($ID), $src, $exists, $this->date_at, true);
		$file   = mediaFN($src);
		$this->command('begin', 'figure', 'ht');
		$this->command('includegraphics', $this->insertImage($file));
		$this->command('caption', $title);
		$this->command('end', 'figure');
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
	 * @param do The command
	 * @param name To be included inside the curly brackets.
	 * @param argument If specified, to be included in square brackets.
	 */
	function command($do, $name, $argument = '') {
		$this->archive->appendContent('\\'.$do.'{'.$name.'}');
		if ($argument) {
			$this->archive->appendContent('['.$argument.']');
		}
		$this->archive->appendContent("\r\n");
	}
	/**
	 * Inserts the specified file.
	 * @param The physical path to the file.
	 * @return The TeX-ified name of the file.
	 */
	function insertImage($filename) {
		$baseFilename = $this->texifyFilename(basename($filename));
		$this->archive->insertContent(self::GRAPHICSPATH.$baseFilename, file_get_contents($filename));
		return $baseFilename;
	}

	/**
	 * Returns a TeX compliant version of the specified file name.
	 * @param filename The filename.
	 * @return A TeX compliant version, with no spaces, and no dot besides the extension.
	 */
	function texifyFilename($filename) {
		$ext = '';
		$extPosition = strrpos($filename, ".");
		if ($extPosition) {
			$ext = substr($filename, $extPosition + 1);
			$filename = substr($filename, 0, -strlen($ext) - 1);
		}
		$texifiedFilename = str_replace(".", "_", $filename);
		$texifiedFilename = str_replace(" ", "_", $texifiedFilename);
		return "$texifiedFilename.$ext";
	}

	/**
	 * Adds simple content to the document.
	 * @param c The content.
	 */
	function content($c) {
		$this->archive->appendContent($c);
	}

	
}
