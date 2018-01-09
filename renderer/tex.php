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
require_once DOKU_PLUGIN . 'latexport/renderer/decorator_persister.php';

/**
 * A façade between doku wiki and the actual tex renderer.
 * Actual renderer is decorator.php or one of its extensions.
 */
class renderer_plugin_latexport_tex extends Decorator {
	const GRAPHICSPATH = 'images/';

	/** 
	 * To create a compressed archive with all TeX resources needed
	 * to download together.
	 */
	private static $archive;


	private static $inclusionLevel;

	/**
	 * List of includes yet to process.
	 */
	private $includes;
		
	/**
	 * Class constructor.
	 */
	function __construct() {
		if (!self::$archive) {
			self::$archive = new ArchiveHelperZip();
			self::$inclusionLevel = 0;
		}
	
		$this->includes = new SplQueue();
		parent::__construct(new DecoratorPersister(self::$archive));
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

		error_log("Document start $ID, ".count($this->includes). " documents to process.");
		if (self::$inclusionLevel == 0) {
			// Create HTTP headers
			$output_filename = $this->texifyPageId($ID, 'zip');
			$headers = array(
					'Content-Type' => renderer_plugin_latexport_tex::$archive->getContentType(),
					'Content-Disposition' => 'attachment; filename="'.$output_filename.'";',
					);

			// store the content type headers in metadata
			p_set_metadata($ID,array('format' => array('latexport_tex' => $headers) ));

			// Starts the archive:
			renderer_plugin_latexport_tex::$archive->startArchive();			
		}
		self::$inclusionLevel++;
		
		// Starts the document:
		renderer_plugin_latexport_tex::$archive->startFile($this->texifyPageId($ID));
		$this->decorator->document_start();
	}
	
	/**
	 * Open an unordered list.
	 * Internal links that are alone in an unordered list item are rendered
	 * as sub-document inclusions. To handle this functionality, we decorate 
	 * the tex file with a special layer.
	 */
	function listu_open() {
		$this->decorator = new DecoratorIncluder($this->decorator);
		$this->decorator->listu_open();
	}
	
	/**
	 * Closes the unordered list.
	 * Internal links that are alone in an unordered list item are rendered
	 * as sub-document inclusions. To handle this functionality, we decorate 
	 * the tex file with a special layer.
	 */
	function listu_close() {
		$internalLinks = $this->decorator->getInternalLinks();
		$this->decorator->listu_close();
		$this->decorator = $this->decorator->decorator;
		
		foreach($internalLinks as $internalLink) {
			$this->includes->push($internalLink);
			$this->decorator->input($this->texifyPageId($internalLink->getLink()));
		}
	}

	/**
	 * Closes the document and processes the gathered includes.
	 */
	function document_end(){
		$this->decorator->document_end();

		renderer_plugin_latexport_tex::$archive->closeFile();
		
		$this->processIncludes();
		self::$inclusionLevel--;
		if (self::$inclusionLevel == 0) {
			$this->doc = renderer_plugin_latexport_tex::$archive->closeArchive();
		}
	}
	
	function processIncludes() {
		error_log("Processing includes");
		while (!$this->includes->isEmpty()) {
			$include = $this->includes->pop();
			$file = wikiFN($include->getLink());
			error_log($include->getLink()."==>".$file);
			p_cached_output($file, 'latexport_tex');
		}
	}
	
	/**
	 * Returns a TeX compliant version of the page ID.
	 * @param ID the page ID, or page name.
	 * @param ext The extension. Default value is '.tex'.
	 * @return A TeX compliant version of the page ID, with the specified extension.
	 */
	private function texifyPageId($ID, $ext = 'tex') {
		return str_replace(':','-',$ID).'.'.$ext;
	}
}
