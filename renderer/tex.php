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
	private $archive; 
	
	/**
	 * Class constructor.
	 */
	function __construct() {
		$this->archive = new ArchiveHelperZip(); 
		parent::__construct(new DecoratorPersister($this->archive));
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
			$this->decorator->input($internalLink->getLink());
		}
	}

	/**
	 * Closes the document
	 */
	function document_end(){
		$this->decorator->document_end();

		$this->archive->closeFile();
		$this->doc = $this->archive->closeArchive();
	}
}
