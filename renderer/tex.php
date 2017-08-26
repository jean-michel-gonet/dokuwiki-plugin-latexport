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
	$this->doc = '\documentclass{article}
\begin{document}
';
    }

    /**
     * Closes the document
     */
    function document_end(){
	$this->doc .= '\end{document}';
    }
}
