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
	 * Returns the format provided by this renderer.
	 */
	function getFormat() {
		return 'tex';
	}

	/**
	 * Returns a TeX compliant version of the page ID.
	 * @param pageId the page ID, or page name.
	 * @param ext The extension. Default value is '.tex'.
	 * @return A TeX compliant version of the page ID, with the specified extension.
	 */
	protected function texifyPageId($pageId, $ext = 'tex') {
		return str_replace(':','-',$pageId).'.'.$ext;
	}

	/**
	 * Escapes Tex reserved chars.
	 * @param text String The text to escape.
	 * @return String The escaped text.
	 */
	function texifyText($text) {
		$text = str_replace('}', '\\}', $text);
		$text = str_replace('{', '\\{', $text);
		$text = str_replace('%', '\\%', $text);
		$text = str_replace('#', '\\#', $text);
		$text = str_replace('_', '\\_', $text);
		$text = str_replace('&', '\\&', $text);
		$text = str_replace('$', '\\$', $text);
		$text = str_replace('^', '\\^', $text);
		return $text;
	}

	/**
	 * Returns a TeX compliant version of the specified file name.
	 * @param filename The filename.
	 * @return A TeX compliant version, with no spaces, and no dot besides the extension.
	 */
	function texifyFilename($filename) {
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		if ($ext) {
			$filename = substr($filename, 0, -strlen($ext) - 1);
		}
		$texifiedFilename = $this->texifyReference($filename);
		return "$texifiedFilename.$ext";
	}

	/**
	 * Returns a TeX compliant version of the specified reference.
	 * @param filename The reference.
	 * @return A TeX compliant version, with no spaces, and no weird char.
	 */
	function texifyReference($reference) {
		$patterns[ 0] = '/[áâàåä]/ui';
		$patterns[ 1] = '/[ðéêèë]/ui';
		$patterns[ 2] = '/[íîìï]/ui';
		$patterns[ 3] = '/[óôòøõö]/ui';
		$patterns[ 4] = '/[úûùü]/ui';
		$patterns[ 5] = '/æ/ui';
		$patterns[ 6] = '/ç/ui';
		$patterns[ 7] = '/ß/ui';
		$patterns[ 8] = '/\\s/';
		$patterns[ 9] = '/#/';
		$patterns[10] = '/[^A-Za-z0-9\\-:]/';
		$replacements[ 0] = 'a';
		$replacements[ 1] = 'e';
		$replacements[ 2] = 'i';
		$replacements[ 3] = 'o';
		$replacements[ 4] = 'u';
		$replacements[ 5] = 'ae';
		$replacements[ 6] = 'c';
		$replacements[ 7] = 'ss';
		$replacements[ 8] = '-';
		$replacements[ 9] = ':';
		$replacements[10] = '_';

		return preg_replace($patterns, $replacements, $reference);
	}

	//////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////
	//                                                                              //
	//                          Handle latexport syntax.                            //
	//                                                                              //
	//////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////

	/**
	 * Receives a local file to include.
	 * @param $link string Local file to include.
	 */
	function input($link) {
		$this->any_command();
		$this->decorator->input($link);
	}

	/**
	 * To draw an horizontal rule between two rows of a table,
	 * @param $start int The starting column.
	 * @param $end int The ending column.
	 */
	function table_cline($start, $end) {
		$this->any_command();
		$this->decorator->table_cline($start, $end);
	}

	/**
	 * Adds a latex command to the document.
	 * @param command  The command
	 * @param scope    The name of the scope, or the mandatory argument,
	 *                 to be included inside the curly brackets.
	 * @param argument If specified, to be included in square brackets. Depending
	 *                 on the command, square brackets are placed before or after
	 *                 the curly brackets.
	 */
	function appendCommand($command, $scope, $argument = '') {
		$this->any_command();
		$this->decorator->appendCommand($command, $scope, $argument);
	}

	/**
	 * Adds a latex command to the document.
	 * @param command  The command
	 * @param scope    The name of the scope, or the mandatory argument,
	 *                 to be included inside the curly brackets.
	 * @param argument If specified, to be included in square brackets. Depending
	 *                 on the command, square brackets are placed before or after
	 *                 the curly brackets.
	 */
	function appendInlineCommand($command, $scope, $argument = '') {
		$this->any_command();
		$this->decorator->appendInlineCommand($command, $scope, $argument);
	}

	/**
	 * Adds simple content to the document.
	 * @param c The content.
	 */
	function appendContent($c) {
		$this->any_command();
		$this->decorator->appendContent($c);
	}

	/**
	 * Override this if you want to have code for all commands.
	 */
	function any_command() {
		// Do nothing.
	}

	//////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////
	//                                                                              //
	//             Handle plugin syntax like mathjax, anchor...                     //
	//                                                                              //
	//////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////

	/**
	 * Receives mathematic formula from Mathjax plugin.
	 */
	function mathjax_content($formula) {
		$this->any_command();
		$this->decorator->mathjax_content($formula);
	}

	/**
	 * Receives the anchors from the 'anchor' plugin.
	 * @param string $link The anchor name.
	 * @param string $title The associated text.
	 */
	function anchor($link, $title = null) {
		$this->any_command();
		$this->decorator->anchor($link, $title);
	}

	//////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////
	//                                                                              //
	//                      Handle standard dokuwiki syntax                         //
	//                                                                              //
	//////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////

	/**
	 * Starts rendering a new page.
	 * @param string $pageId The identifier of the opening page.
	 * @param int $recursionLevel The level of recursion. When a page includes a page, that's one level of recursion.
	 */
	function document_start($pageId = null, $recursionLevel = 0) {
		$this->any_command();
		$this->decorator->document_start($pageId, $recursionLevel);
	}

	/**
	 * Closes the document
	 */
	function document_end($recursionLevel = 0){
		$this->any_command();
		$this->decorator->document_end($recursionLevel);
	}

    /**
     * Render the Table of Contents
     *
     * @return string
     */
    function render_TOC() {
		$this->any_command();
		$this->decorator->render_TOC();
    }

    /**
     * Add an item to the TOC
     *
     * @param string $id       the hash link
     * @param string $text     the text to display
     * @param int    $level    the nesting level
     */
    function toc_additem($id, $text, $level) {
		$this->any_command();
		$this->decorator->toc_additem($id, $text, $level);
    }

    /**
     * Render a heading
     *
     * @param string $text  the text to display
     * @param int    $level header level
     * @param int    $pos   byte position in the original source
     */
    function header($text, $level, $pos) {
		$this->any_command();
		$this->decorator->header($text, $level, $pos);
    }

    /**
     * Open a new section
     *
     * @param int $level section level (as determined by the previous header)
     */
    function section_open($level) {
		$this->any_command();
		$this->decorator->section_open($level);
    }

    /**
     * Close the current section
     */
    function section_close() {
		$this->any_command();
		$this->decorator->section_close($level);
    }

    /**
     * Render plain text data
     *
     * @param string $text
     */
	function cdata($text) {
		$this->any_command();
		$this->decorator->cdata($text);
	}

	/**
	 * Open a paragraph.
	 */
	function p_open() {
		$this->any_command();
		$this->decorator->p_open();
	}

	/**
	 * Close a paragraph.
	 */
	function p_close() {
		$this->any_command();
		$this->decorator->p_close();
	}

  /**
   * Create a line break
   */
  function linebreak() {
		$this->any_command();
		$this->decorator->linebreak();
  }

  /**
   * Create a horizontal line
   */
  function hr() {
		$this->any_command();
		$this->decorator->hr();
  }

	/**
	 * Start strong (bold) formatting
	 */
	function strong_open() {
		$this->any_command();
		$this->decorator->strong_open();
	}

	/**
	 * Stop strong (bold) formatting
	 */
	function strong_close() {
		$this->any_command();
		$this->decorator->strong_close();
	}

	/**
	 * Start emphasis (italics) formatting
	 */
	function emphasis_open() {
		$this->any_command();
		$this->decorator->emphasis_open();
	}

	/**
	 * Stop emphasis (italics) formatting
	 */
	function emphasis_close() {
		$this->any_command();
		$this->decorator->emphasis_close();
	}

	/**
	 * Start underline formatting
	 */
	function underline_open() {
		$this->any_command();
		$this->decorator->underline_open();
	}

	/**
	 * Stop underline formatting
	 */
	function underline_close() {
		$this->any_command();
		$this->decorator->underline_close();
	}

  /**
   * Start monospace formatting
   */
  function monospace_open() {
		$this->any_command();
		$this->decorator->monospace_open();
  }

  /**
   * Stop monospace formatting
   */
  function monospace_close() {
		$this->any_command();
		$this->decorator->monospace_close();
  }

  /**
   * Start a subscript
   */
  function subscript_open() {
		$this->any_command();
		$this->decorator->subscript_open();
  }

  /**
   * Stop a subscript
   */
  function subscript_close() {
		$this->any_command();
		$this->decorator->subscript_close();
  }

  /**
   * Start a superscript
   */
  function superscript_open() {
		$this->any_command();
		$this->decorator->superscript_open();
  }

  /**
   * Stop a superscript
   */
  function superscript_close() {
		$this->any_command();
		$this->decorator->superscript_close();
  }

  /**
   * Start deleted (strike-through) formatting
   */
  function deleted_open() {
		$this->any_command();
		$this->decorator->deleted_open();
  }

  /**
   * Stop deleted (strike-through) formatting
   */
  function deleted_close() {
		$this->any_command();
		$this->decorator->deleted_close();
  }

  /**
   * Start a footnote
   */
  function footnote_open() {
		$this->any_command();
		$this->decorator->footnote_open();
  }

  /**
   * Stop a footnote
   */
  function footnote_close() {
		$this->any_command();
		$this->decorator->footnote_close();
  }

/**
 * Open an unordered list
 */
function listu_open() {
		$this->any_command();
		$this->decorator->listu_open();
	}

	/**
	 * Close an unordered list
	 */
	function listu_close() {
		$this->any_command();
		$this->decorator->listu_close();
	}

  /**
   * Open an ordered list
   */
  function listo_open() {
		$this->any_command();
		$this->decorator->listo_open();
  }

  /**
   * Close an ordered list
   */
  function listo_close() {
		$this->any_command();
		$this->decorator->listo_close();
  }

	/**
	 * Open a list item
	 *
	 * @param int $level the nesting level
	 * @param bool $node true when a node; false when a leaf
	 */
	function listitem_open($level,$node=false) {
		$this->any_command();
		$this->decorator->listitem_open($level, $node);
	}

	/**
	 * Start the content of a list item
	 */
	function listcontent_open() {
		$this->any_command();
		$this->decorator->listcontent_open();
	}

	/**
	 * Stop the content of a list item
	 */
	function listcontent_close() {
		$this->any_command();
		$this->decorator->listcontent_close();
	}

  /**
   * Close a list item
   */
  function listitem_close() {
		$this->any_command();
		$this->decorator->listitem_close();
  }

  /**
   * Output unformatted $text
   *
   * @param string $text
   */
  function unformatted($text) {
		error_log("decorator.unformatted <$text>");
		$this->any_command();
    $this->decorator->unformatted($text);
  }

  /**
   * Output inline PHP code
   *
   * @param string $text The PHP code
   */
  function php($text) {
		$this->any_command();
		$this->decorator->php($text);
  }

  /**
   * Output block level PHP code
   *
   * @param string $text The PHP code
   */
  function phpblock($text) {
		$this->any_command();
		$this->decorator->phpblock($text);
  }

  /**
   * Output raw inline HTML
   *
   * If $conf['htmlok'] is true this should add the code as is to $doc
   *
   * @param string $text The HTML
   */
  function html($text) {
		$this->any_command();
		$this->decorator->html($text);
  }

  /**
   * Output raw block-level HTML
   *
   * If $conf['htmlok'] is true this should add the code as is to $doc
   *
   * @param string $text The HTML
   */
  function htmlblock($text) {
		$this->any_command();
		$this->decorator->htmlblock($text);
  }

  /**
   * Output preformatted text
   *
   * @param string $text
   */
  function preformatted($text) {
		$this->any_command();
		$this->decorator->preformatted($text);
  }

  /**
   * Start a block quote
   */
  function quote_open() {
		$this->any_command();
		$this->decorator->quote_open();
  }

  /**
   * Stop a block quote
   */
  function quote_close() {
		$this->any_command();
		$this->decorator->quote_close();
  }

  /**
   * Display text as file content, optionally syntax highlighted
   *
   * @param string $text text to show
   * @param string $lang programming language to use for syntax highlighting
   * @param string $file file path label
   */
  function file($text, $lang = null, $file = null) {
		$this->any_command();
		$this->decorator->file($text, $lang, $file);
  }

  /**
   * Display text as code content, optionally syntax highlighted
   *
   * @param string $text text to show
   * @param string $lang programming language to use for syntax highlighting
   * @param string $file file path label
   */
  function code($text, $lang = null, $file = null) {
		$this->any_command();
		$this->decorator->code($text, $lang, $file);
  }

  /**
   * Format an acronym
   *
   * Uses $this->acronyms
   *
   * @param string $acronym
   */
  function acronym($acronym) {
		$this->any_command();
		$this->decorator->acronym($acronym);
  }

  /**
   * Format a smiley
   *
   * Uses $this->smiley
   *
   * @param string $smiley
   */
  function smiley($smiley) {
		$this->any_command();
		$this->decorator->smiley($smiley);
  }

  /**
   * Format an entity
   *
   * Entities are basically small text replacements
   *
   * Uses $this->entities
   *
   * @param string $entity
   */
  function entity($entity) {
		$this->any_command();
		$this->decorator->entity($entity);
  }

  /**
   * Typographically format a multiply sign
   *
   * Example: ($x=640, $y=480) should result in "640×480"
   *
   * @param string|int $x first value
   * @param string|int $y second value
   */
  function multiplyentity($x, $y) {
		$this->any_command();
		$this->decorator->multiplyentity($x, $y);
  }

  /**
   * Render an opening single quote char (language specific)
   */
  function singlequoteopening() {
		$this->any_command();
		$this->decorator->singlequoteopening();
  }

  /**
   * Render a closing single quote char (language specific)
   */
  function singlequoteclosing() {
		$this->any_command();
		$this->decorator->singlequoteclosing();
  }

  /**
   * Render an apostrophe char (language specific)
   */
  function apostrophe() {
		$this->any_command();
		$this->decorator->apostrophe();
  }

  /**
   * Render an opening double quote char (language specific)
   */
  function doublequoteopening() {
		$this->any_command();
		$this->decorator->doublequoteopening();
  }

  /**
   * Render an closinging double quote char (language specific)
   */
  function doublequoteclosing() {
		$this->any_command();
		$this->decorator->doublequoteclosing();
  }

  /**
   * Render a CamelCase link
   *
   * @param string $link The link name
   * @see http://en.wikipedia.org/wiki/CamelCase
   */
  function camelcaselink($link) {
		$this->any_command();
		$this->decorator->camelcaselink($link);
  }

  /**
   * Render a page local link
   *
   * @param string $hash hash link identifier
   * @param string $name name for the link
   */
  function locallink($hash, $name = null) {
		$this->any_command();
		$this->decorator->locallink($hash, $name);
  }

	/**
	 * Render a wiki internal link.
	 * Internal links at the very beginning of an unordered item include
	 * the destination page.
	 * @param string       $link  page ID to link to. eg. 'wiki:syntax'
	 * @param string|array $title name for the link, array for media file
	 */
	function internallink($link, $title = null) {
		$this->any_command();
		$this->decorator->internallink($link, $title);
	}

  /**
   * Render an external link
   *
   * @param string       $link  full URL with scheme
   * @param string|array $title name for the link, array for media file
   */
  function externallink($link, $title = null) {
		$this->any_command();
		$this->decorator->externallink($link, $title);
  }

  /**
   * Render the output of an RSS feed
   *
   * @param string $url    URL of the feed
   * @param array  $params Finetuning of the output
   */
  function rss($url, $params) {
		$this->any_command();
		$this->decorator->rss($link, $title);
  }

  /**
   * Render an interwiki link
   *
   * You may want to use $this->_resolveInterWiki() here
   *
   * @param string       $link     original link - probably not much use
   * @param string|array $title    name for the link, array for media file
   * @param string       $wikiName indentifier (shortcut) for the remote wiki
   * @param string       $wikiUri  the fragment parsed from the original link
   */
  function interwikilink($link, $title = null, $wikiName, $wikiUri) {
		$this->any_command();
		$this->decorator->interwikilink($link, $title, $wikiName, $wikiUri);
  }

  /**
   * Link to file on users OS
   *
   * @param string       $link  the link
   * @param string|array $title name for the link, array for media file
   */
  function filelink($link, $title = null) {
		$this->any_command();
		$this->decorator->filelink($link, $title);
  }

  /**
   * Link to windows share
   *
   * @param string       $link  the link
   * @param string|array $title name for the link, array for media file
   */
  function windowssharelink($link, $title = null) {
		$this->any_command();
		$this->decorator->windowssharelink($link, $title);
  }

  /**
   * Render a linked E-Mail Address
   *
   * Should honor $conf['mailguard'] setting
   *
   * @param string $address Email-Address
   * @param string|array $name name for the link, array for media file
   */
  function emaillink($address, $name = null) {
		$this->any_command();
		$this->decorator->emaillink($address, $name);
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
	 * @param int    $positionInGroup Position of the media in the group.
	 * @param int    $totalInGroup Size of the group of media.
	 */
	function internalmedia($src, $title = null, $align = null, $width = null,
	                       $height = null, $cache = null, $linking = null, $positionInGroup = 1, $totalInGroup = 1) {
		$this->any_command();
		$this->decorator->internalmedia($src, $title, $align, $width, $height, $cache, $linking, $positionInGroup, $totalInGroup);
	}

  /**
   * Render an external media file
   *
   * @param string $src     full media URL
   * @param string $title   descriptive text
   * @param string $align   left|center|right
   * @param int    $width   width of media in pixel
   * @param int    $height  height of media in pixel
   * @param string $cache   cache|recache|nocache
   * @param string $linking linkonly|detail|nolink
   */
  function externalmedia($src, $title = null, $align = null, $width = null,
                         $height = null, $cache = null, $linking = null) {
		$this->any_command();
		$this->decorator->externalmedia($src, $title, $align, $width, $height, $cache, $linking);
  }

  /**
   * Render a link to an internal media file
   *
   * @param string $src     media ID
   * @param string $title   descriptive text
   * @param string $align   left|center|right
   * @param int    $width   width of media in pixel
   * @param int    $height  height of media in pixel
   * @param string $cache   cache|recache|nocache
   */
  function internalmedialink($src, $title = null, $align = null,
                               $width = null, $height = null, $cache = null) {
		$this->any_command();
		$this->decorator->internalmedialink($src, $title, $align, $width, $height, $cache);
  }

  /**
   * Render a link to an external media file
   *
   * @param string $src     media ID
   * @param string $title   descriptive text
   * @param string $align   left|center|right
   * @param int    $width   width of media in pixel
   * @param int    $height  height of media in pixel
   * @param string $cache   cache|recache|nocache
   */
  function externalmedialink($src, $title = null, $align = null,
                             $width = null, $height = null, $cache = null) {
 		$this->any_command();
 		$this->decorator->externalmedialink($src, $title, $align, $width, $height, $cache);
  }

  /**
   * Start a table
   *
   * @param int $maxcols maximum number of columns
   * @param int $numrows NOT IMPLEMENTED
   * @param int $pos     byte position in the original source
   */
  function table_open($maxcols = null, $numrows = null, $pos = null) {
		$this->any_command();
		$this->decorator->table_open($maxcols, $numrows, $pos);
  }

  /**
   * Close a table
   *
   * @param int $pos byte position in the original source
   */
  function table_close($pos = null) {
		$this->any_command();
		$this->decorator->table_close($pos);
  }

  /**
   * Open a table header
   */
  function tablethead_open() {
		$this->any_command();
		$this->decorator->any_command();
  }

  /**
   * Close a table header
   */
  function tablethead_close() {
		$this->any_command();
		$this->decorator->tablethead_close();
  }

  /**
   * Open a table body
   */
  function tabletbody_open() {
		$this->any_command();
		$this->decorator->tabletbody_open();
  }

  /**
   * Close a table body
   */
  function tabletbody_close() {
		$this->any_command();
		$this->decorator->tabletbody_close();
  }

  /**
   * Open a table footer
   */
  function tabletfoot_open() {
		$this->any_command();
		$this->decorator->tabletfoot_open();
  }

  /**
   * Close a table footer
   */
  function tabletfoot_close() {
		$this->any_command();
		$this->decorator->tabletfoot_close();
  }

  /**
   * Open a table row
   */
  function tablerow_open() {
		$this->any_command();
		$this->decorator->tablerow_open();
  }

  /**
   * Close a table row
   */
  function tablerow_close() {
		$this->any_command();
		$this->decorator->tablerow_close();
  }

  /**
   * Open a table header cell
   *
   * @param int    $colspan
   * @param string $align left|center|right
   * @param int    $rowspan
   */
  function tableheader_open($colspan = 1, $align = null, $rowspan = 1) {
		$this->any_command();
		$this->decorator->tableheader_open($colspan, $align, $rowspan);
  }

  /**
   * Close a table header cell
   */
  function tableheader_close() {
		$this->any_command();
		$this->decorator->tableheader_close();
  }

  /**
   * Open a table cell
   *
   * @param int    $colspan
   * @param string $align left|center|right
   * @param int    $rowspan
   */
  function tablecell_open($colspan = 1, $align = null, $rowspan = 1) {
		$this->any_command();
		$this->decorator->tablecell_open($colspan, $align, $rowspan);
  }

  /**
   * Close a table cell
   */
  function tablecell_close() {
		$this->any_command();
		$this->decorator->tablecell_close();
  }
}
