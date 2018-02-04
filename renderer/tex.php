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
require_once DOKU_PLUGIN . 'latexport/renderer/decorator_includer.php';
require_once DOKU_PLUGIN . 'latexport/renderer/decorator_math.php';
require_once DOKU_PLUGIN . 'latexport/renderer/decorator_tables.php';
require_once DOKU_PLUGIN . 'latexport/renderer/decorator_headings.php';

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
	private $archive;

	/**
	 * Keeps track of the recursion level.
	 * Root page has a recursion level 0. Children pages have a level of 1,
	 * grand-children have a level of 2, etc.
	 */
	private $recursionLevel;

	/**
	 * Keeps track of heading level.
	 */
	private $headingLevel;

	/**
	 * List of includes yet to process.
	 */
	private $includes;
	
	/**
	 * Current page ID.
	 */
	private $currentPageId;
		
	/**
	 * Class constructor.
	 */
	function __construct() {
		$this->archive = new ArchiveHelperZip();
		$this->includes = new SplQueue();
		$this->recursionLevel = 0;	
		$this->headingLevel = 0;

		parent::__construct(
			new DecoratorHeadings(
				new DecoratorIncluder($this->includes,
					new DecoratorMath(
						new DecoratorTables(
							new DecoratorPersister($this->archive))))));
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
	function document_start($doNotCareAboutProviedPageId = null, $doNotCareAboutProvidedRecursionLevel = 0) {
		global $ID;

		if (!$this->currentPageId) {
			$this->currentPageId = $ID;
		}

		if ($this->recursionLevel == 0) {
			// Create HTTP headers
			$output_filename = $this->texifyPageId($this->currentPageId, 'zip');
			$headers = array(
					'Content-Type' => $this->archive->getContentType(),
					'Content-Disposition' => 'attachment; filename="'.$output_filename.'";',
					);

			// store the content type headers in metadata
			p_set_metadata($this->currentPageId,array('format' => array('latexport_tex' => $headers) ));

			// Starts the archive:
			$this->archive->startArchive();			
		}
		
		// Starts the document:
		$this->archive->startFile($this->texifyPageId($this->currentPageId));
		$this->decorator->document_start($this->currentPageId, $this->recursionLevel);			
		$this->recursionLevel++;
	}

	/**
	 * Propagates the heading level corrected with base heading level retrieved
	 * from the include link.
	 */
	function header($text, $level, $pos) {
		$this->decorator->header($text, $level + $this->headingLevel, $pos);
	}
	
	/**
	 * Closes the document and processes the gathered includes.
	 */
	function document_end($doNotCareAboutProvidedRecursionLevel = 0){
		$this->decorator->document_end($this->recursionLevel - 1);

		$this->archive->closeFile();

		$this->processIncludes();
		
		$this->recursionLevel--;
		if ($this->recursionLevel == 0) {
			$this->doc = $this->archive->closeArchive();
		}
	}
	
	function processIncludes() {
		while (!$this->includes->isEmpty()) {
			$include = $this->includes->pop();
			$file = wikiFN($include->getLink());
			$this->currentPageId = $include->getLink();
			$this->headingLevel = $include->getHeadingLevel();
			p_cached_output($file, 'latexport_tex');
		}
	}	
}
