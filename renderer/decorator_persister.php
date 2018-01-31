<?php

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once DOKU_PLUGIN . 'latexport/renderer/decorator.php';

/**
 * Final tex decorator, takes care of all formatting that does not
 * require state machines, and stores content to the archive.
 * Can add more layers of decorators over it, but this decorator has always to
 * be at the bottom layer.
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Jean-Michel Gonet <jmgonet@yahoo.com>
 */
class DecoratorPersister {
	
	/**
	 * Were we're going to save image files.
	 */
	const GRAPHICSPATH = 'images/';

	/** 
	 * Receives the content of the document.
	 */
	private $archive;
	
	private $matterNumber;
	
	private $pageId;

	/**
	 * Class constructor.
	 * @param archive Will receive the content of the document.
	 */
	function __construct($archive) {
		$this->archive = $archive;
		$this->matterNumber = 0;
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
		$this->appendCommand("input", $link);
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
		$this->appendInlineCommand($command, $scope, $argument);
		$this->archive->appendContent("\r\n");
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
		if ($argument) {
			switch($command) {
				// Some commands have the optional arguments after the curly brackets:
				case 'begin':
				case 'end':
					$text = '\\'.$command.'{'.$scope.'}['.$argument.']';
					break;

				// Most commands have the optional arguments before the curly brackets:
				default:
					$text = '\\'.$command.'['.$argument.']{'.$scope.'}';
					break;
			}
		} 
		// If there is no argument, then there is only one way to express a command...
		else {
			if ($scope) {
				$text = '\\'.$command.'{'.$scope.'}';
			} 
			// ... unless there is no scope:
			else {
				$text = '\\'.$command;
			}
		}
		
		// Let's render the command:
		$this->archive->appendContent("$text");
	}

	/**
	 * Adds simple content to the document.
	 * @param c The content.
	 */
	function appendContent($c) {
		$this->archive->appendContent($c);
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

	//////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////
	//                                                                              //
	//             Handle plugin syntax like mathjax, anchor...                     //
	//                                                                              //
	//////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////
	
	/**
	 * Receives mathematic formula from Mathjax plugin.
	 * As Mathjax already uses latex separators, there is no
	 * need to reprocess.
	 */
	function mathjax_content($formula) {
		$this->appendContent("$formula");
	}

	/**
	 * Receives the anchors from the 'anchor' plugin.
	 * @param string $link The anchor name.
	 * @param string $title The associated text.
	 */
	function anchor($link, $title = null) {
		$this->appendContent(' \\label{'.$this->pageId.':'.$link.'}'.$title);
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
	function document_start($pageId, $recursionLevel) {
		$this->pageId = $pageId;
		if ($recursionLevel == 0) {
			$this->appendCommand('documentclass', 'book');
			$this->appendCommand('usepackage', 'graphicx');
			$this->appendCommand('usepackage', 'fontspec');
			$this->appendCommand('usepackage', 'hyperref');
			$this->appendCommand('usepackage', 'array');
			$this->appendCommand('usepackage', 'soul');
			$this->appendCommand('usepackage', 'csquotes');
			$this->appendCommand('usepackage', 'multirow');
			$this->appendCommand('setmainfont', 'xits', 'Ligatures=TeX');
			$this->appendCommand('graphicspath', ' {'.self::GRAPHICSPATH.'} ');
			$this->appendCommand('begin', 'document');
		}
	}

	/**
	 * Ends the document
	 */
	function document_end($recursionLevel = 0){
		if ($recursionLevel == 0) {
			$this->appendCommand('end', 'document');
		}
	}

    /**
     * Table of content is not rendered in latex.
     */
    function render_TOC() {
		// Do nothing.
    }

    /**
     * TOC items are not rendered in latex.
     */
    function toc_additem($id, $text, $level) {
		// Do nothing.
    }

	/**
	 * Headers are transformed in part, chapter, section, subsection and subsubsection.
	 */
	function header($text, $level, $pos) {
		switch($level) {
			case 1:
				switch($this->matterNumber) {
					case 0:
						$this->appendContent("\\frontmatter\r\n");
						$this->matterNumber = 1;
						break;
					case 1:
						$this->appendContent("\\appendix\r\n");
						$this->matterNumber = 2;
						break;
					default:
						$this->appendCommand('chapter', $text);
						break;
				}
				break;

			case 2:
				$this->appendCommand('part', $text);
				break;
			case 3:
				$this->appendCommand('chapter', $text);
				break;
			case 4:
				$this->appendCommand('section', $text);
				break;
			default:
				$this->appendCommand('subsection', $text);
				break;
		}
	}

    /**
     * Sections are rendered as title-less headers.
     * @param int $level section level (as determined by the previous header)
     */
    function section_open($level) {
		// Nothing to do.
    }

    /**
     * Close the current section
     */
    function section_close() {
		// Nothing to do.
    }

	/**
	 * Renders plain text.
	 */
	function cdata($text) {
		$text = str_replace('#', '\\#', $text);
		$this->appendContent($text);
	}

	/**
	 * Open a paragraph.
	 */
	function p_open() {
		// Nothing to do.
	}

	/**
	 * Close a paragraph.
	 */
	function p_close() {
		$this->appendContent("\r\n\r\n");
	}
	
    /**
     * Create a line break
     */
    function linebreak() {
		$this->appendContent("\r\n\r\n");
    }

    /**
     * Create a horizontal line
     */
    function hr() {
		$this->appendContent("\r\n\r\n\\noindent\\makebox[\\linewidth]{\\rule{\\paperwidth}{0.4pt}}\r\n");
    }

	/**
	 * Start strong (bold) formatting
	 */
	function strong_open() {
		$this->appendContent("\\textbf{");	
	}

	/**
	 * Stop strong (bold) formatting
	 */
	function strong_close() {
		$this->appendContent("}");
	}
	
	/**
	 * Start emphasis (italics) formatting
	 */
	function emphasis_open() {
		$this->appendContent("\\emph{");
	}

	/**
	 * Stop emphasis (italics) formatting
	 */
	function emphasis_close() {
		$this->appendContent("}");
	}

	/**
	 * Start underline formatting
	 */ 
	function underline_open() {
		$this->appendContent("\\underline{");
	}

	/**
	 * Stop underline formatting
	 */
	function underline_close() {
		$this->appendContent("}");
	}
	
    /**
     * Start monospace formatting
     */
    function monospace_open() {
		$this->appendContent("\\texttt{");
    }

    /**
     * Stop monospace formatting
     */
    function monospace_close() {
		$this->appendContent("}");
    }

    /**
     * Start a subscript
     */
    function subscript_open() {
		$this->appendContent("\\textsubscript{");
    }

    /**
     * Stop a subscript
     */
    function subscript_close() {
		$this->appendContent("}");
    }

    /**
     * Start a superscript
     */
    function superscript_open() {
		$this->appendContent("\\textsuperscript{");
    }

    /**
     * Stop a superscript
     */
    function superscript_close() {
		$this->appendContent("}");
    }
	
    /**
     * Start deleted (strike-through) formatting
     */
    function deleted_open() {
		$this->appendContent("\\st{");
    }

    /**
     * Stop deleted (strike-through) formatting
     */
    function deleted_close() {
		$this->appendContent("}");
    }

    /**
     * Start a footnote
     */
    function footnote_open() {
		$this->appendContent("\\footnote{");
    }

    /**
     * Stop a footnote
     */
    function footnote_close() {
		$this->appendContent("}");
    }
	
	/**
	 * Open an unordered list
	 */
	function listu_open() {
		$this->appendCommand('begin', 'itemize');
	}

	/**
	 * Close an unordered list
	 */
	function listu_close() {
		$this->appendCommand('end', 'itemize');
	}
	
    /**
     * Open an ordered list
     */
    function listo_open() {
		$this->appendCommand('begin', 'enumerate');
    }

    /**
     * Close an ordered list
     */
    function listo_close() {
		$this->appendCommand('end', 'enumerate');
    }

	/**
	 * Open a list item
	 *
	 * @param int $level the nesting level
	 * @param bool $node true when a node; false when a leaf
	 */
	function listitem_open($level,$node=false) {
		$this->appendContent(str_repeat('   ', $level).'\\item ');
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
		$this->appendContent("\r\n");
	}
	
    /**
     * Close a list item
     */
    function listitem_close() {
		// Nothing to do.
    }

    /**
     * Output unformatted $text
     *
     * @param string $text
     */
    function unformatted($text) {
        $this->appendCommand("begin", "verbatim");
		$this->appencContent($text);
		$this->appendCommand("end", "verbatim");
    }
    /**
     * Output inline PHP code
     *
     * @param string $text The PHP code
     */
    function php($text) {
		$this->monospace_open();
		$this->cdata($text);
		$this->monospace_close();
    }

    /**
     * Output block level PHP code
     *
     * @param string $text The PHP code
     */
    function phpblock($text) {
		$this->appendCommand("begin", "lstlisting", "language=php");
		$this->appendContent($text);
		$this->appendCommand("end", "lstlisting");
    }

    /**
     * Output raw inline HTML
     *
     * If $conf['htmlok'] is true this should add the code as is to $doc
     *
     * @param string $text The HTML
     */
    function html($text) {
		$this->monospace_open();
		$this->cdata($text);
		$this->monospace_close();
    }

    /**
     * Output raw block-level HTML
     *
     * If $conf['htmlok'] is true this should add the code as is to $doc
     *
     * @param string $text The HTML
     */
    function htmlblock($text) {
		$this->appendCommand("begin", "lstlisting", "language=html");
		$this->appendContent($text);
		$this->appendCommand("end", "lstlisting");
    }

    /**
     * Output preformatted text
     *
     * @param string $text
     */
    function preformatted($text) {
		$this->unformatted($text);
    }

    /**
     * Start a block quote
     */
    function quote_open() {
		$this->appendCommand("begin", "displayquote");
    }

    /**
     * Stop a block quote
     */
    function quote_close() {
		$this->appendCommand("end", "displayquote");
    }

    /**
     * Display text as file content, optionally syntax highlighted
     *
     * @param string $text text to show
     * @param string $lang programming language to use for syntax highlighting
     * @param string $file file path label
     */
    function file($text, $lang = null, $file = null) {
		if ($file) {
			$this->unformatted("--> $file");
		}
		if ($lang) {
			$this->appendCommand("begin", "lstlisting", "language=$lang");			
		} else {
			$this->appendCommand("begin", "lstlisting");
		}
		$this->appendContent($text);
		$this->appendCommand("end", "lstlisting");
    }

    /**
     * Display text as code content, optionally syntax highlighted
     *
     * @param string $text text to show
     * @param string $lang programming language to use for syntax highlighting
     * @param string $file file path label
     */
    function code($text, $lang = null, $file = null) {
		$this->file($text, $lang, $file);
    }

    /**
     * Format an acronym
     * Uses $this->acronyms
     * @param string $acronym
     */
    function acronym($acronym) {
		$this->cdata($acronym);
    }

    /**
     * Format a smiley
     * Uses $this->smiley
     * @param string $smiley
     */
    function smiley($smiley) {
		$this->cdata($smiley);
    }

    /**
     * Format an entity
     * Entities are basically small text replacements
     * @param string $entity
     */
    function entity($entity) {
		$this->cdata($entity);
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
		$this->mathjax_content("\\( $x \\times $y \\)");
    }

    /**
     * Render an opening single quote char (language specific)
     */
    function singlequoteopening() {
		$this->cdata("`");
    }

    /**
     * Render a closing single quote char (language specific)
     */
    function singlequoteclosing() {
		$this->cdata("´");
    }

    /**
     * Render an apostrophe char (language specific)
     */
    function apostrophe() {
		$this->cdata("’");
    }

    /**
     * Render an opening double quote char (language specific)
     */
    function doublequoteopening() {
		$this->cdata("“");
    }

    /**
     * Render an closinging double quote char (language specific)
     */
    function doublequoteclosing() {
		$this->cdata("”");
    }

    /**
     * Render a CamelCase link
     *
     * @param string $link The link name
     * @see http://en.wikipedia.org/wiki/CamelCase
     */
    function camelcaselink($link) {
		$this->externallink($link);
    }

    /**
     * Render a page local link
     *
     * @param string $hash hash link identifier
     * @param string $name name for the link
     */
    function locallink($hash, $name = null) {
		$this->internallink($this->pageId.":".$hash, $title);
    }

	/**
	 * Render a wiki internal link.
	 * Internal links at the very beginning of an unordered item include
	 * the destination page.
	 * @param string       $link  page ID to link to. eg. 'wiki:syntax'
	 * @param string|array $title name for the link, array for media file
	 */
	function internallink($link, $title = null) {
		$this->appendContent($title);
		$this->appendContent(" ");
		$this->appendContent('\\ref{'.str_replace("#", ":", $link).'}');
	}

    /**
     * Render an external link
     *
     * @param string       $link  full URL with scheme
     * @param string|array $title name for the link, array for media file
     */
    function externallink($link, $title = null) {
		$this->appendContent($title.' \\url{'.$link.'}');
    }

    /**
     * Render the output of an RSS feed
     *
     * @param string $url    URL of the feed
     * @param array  $params Finetuning of the output
     */
    function rss($url, $params) {
		// Nothing to do.
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
		$this->externalLink($link, trim($title.' '.$wikiName));
    }

    /**
     * Link to file on users OS
     *
     * @param string       $link  the link
     * @param string|array $title name for the link, array for media file
     */
    function filelink($link, $title = null) {
		// Nothing to do.
    }

    /**
     * Link to windows share
     *
     * @param string       $link  the link
     * @param string|array $title name for the link, array for media file
     */
    function windowssharelink($link, $title = null) {
		// Nothing to do.
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
		$this->appendContent("$name \\href{mailto:$address}{$address} ");
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
	function internalmedia($src, $title = null, $align = null, $width = null, $height = null, $cache = null, $linking = null) {
		global $ID;
		list($src, $hash) = explode('#', $src, 2);
		resolve_mediaid(getNS($ID), $src, $exists, $this->date_at, true);
		$file   = mediaFN($src);
		$this->appendCommand('begin', 'figure', 'ht');
		$this->appendCommand('includegraphics', $this->insertImage($file), 'width=\textwidth');
		$this->appendCommand('caption', $title);
		$this->appendCommand('end', 'figure');
	}

    /**
     * Does not render external media files
     */
    function externalmedia($src, $title = null, $align = null, $width = null, $height = null, $cache = null, $linking = null) {
	   // Nothing to do
    }

    /**
     * Render a link to an internal media file as if it were an internal media.
     *
     * @param string $src     media ID
     * @param string $title   descriptive text
     * @param string $align   left|center|right
     * @param int    $width   width of media in pixel
     * @param int    $height  height of media in pixel
     * @param string $cache   cache|recache|nocache
     */
    function internalmedialink($src, $title = null, $align = null, $width = null, $height = null, $cache = null) {
  		$this->internalmedia($src, $title, $align, $width, $height, $cache);
    }

    /**
     * Render a link to an external media file as a url.
     *
     * @param string $src     media ID
     * @param string $title   descriptive text
     * @param string $align   left|center|right
     * @param int    $width   width of media in pixel
     * @param int    $height  height of media in pixel
     * @param string $cache   cache|recache|nocache
     */
    function externalmedialink($src, $title = null, $align = null, $width = null, $height = null, $cache = null) {
		$this->externallink($src, $title);
    }

    /**
     * Start a table
     *
     * @param int $maxcols maximum number of columns
     * @param int $numrows NOT IMPLEMENTED
     * @param int $pos     byte position in the original source
     */
    function table_open($maxcols = null, $numrows = null, $pos = null) {
		$this->appendCommand("begin", "table", "h");
		$this->appendCommand("begin", "center");
		$this->appendContent("\\begin{tabular}{|".str_repeat("c|", $maxcols)."}\\hline\r\n");
    }

    /**
     * Close a table
     *
     * @param int $pos byte position in the original source
     */
    function table_close($pos = null) {
		$this->appendCommand("end", "tabular");
		$this->appendCommand("end", "center");
		$this->appendCommand("end", "table");
    }

    /**
     * Open a table header
     */
    function tablethead_open() {
		// Nothing to do
    }

    /**
     * Close a table header
     */
    function tablethead_close() {
		// Nothing to do
    }

    /**
     * Open a table body
     */
    function tabletbody_open() {
		// Nothing to do
    }

    /**
     * Close a table body
     */
    function tabletbody_close() {
		// Nothing to do
    }

    /**
     * Open a table footer
     */
    function tabletfoot_open() {
		// Nothing to do
    }

    /**
     * Close a table footer
     */
    function tabletfoot_close() {
		// Nothing to do
    }

	private $firstCellInRow;

    /**
     * Open a table row
     */
    function tablerow_open() {
		$this->appendContent("\r\n");
		$this->firstCellInRow = true;
    }

    /**
     * Close a table row
     */
    function tablerow_close() {
		$this->appendContent("\\\\\r\n");
    }

    /**
     * Open a table header cell
     *
     * @param int    $colspan
     * @param string $align left|center|right
     * @param int    $rowspan
     */
    function tableheader_open($colspan = 1, $align = null, $rowspan = 1) {
		if ($this->firstCellInRow) {
			$this->firstCellInRow = false;			
		} else {
			$this->appendContent(" &\r\n");
		}
		$this->appendContent("    \\multicolumn{".$colspan."}".$this->alignment($align)."{\multirow{".$rowspan."}{*}{\\textbf{");
    }

    /**
     * Close a table header cell
     */
    function tableheader_close() {
		$this->appendContent("}}}");
    }

    /**
     * Open a table cell
     *
     * @param int    $colspan
     * @param string $align left|center|right
     * @param int    $rowspan
     */
    function tablecell_open($colspan = 1, $align = null, $rowspan = 1) {
		if ($this->firstCellInRow) {
			$this->firstCellInRow = false;			
		} else {
			$this->appendContent(" &\r\n");
		}
		$this->appendContent("    \\multicolumn{".$colspan."}".$this->alignment($align)."{\multirow{".$rowspan."}{*}{");
    }

	function alignment($align) {
		switch($align) {
			case "left":
				return "{|l|}";
			case "right":
				return "{|r|}";
			default:
				return "{|c|}";
		}
	}

    /**
     * Close a table cell
     */
    function tablecell_close() {
		$this->appendContent("}}");
    }
	
	function table_cline($start, $end) {
		$this->appendContent("\\cline{".$start." - ".$end."}");
	}
	
}
