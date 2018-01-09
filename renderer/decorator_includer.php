<?php

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once DOKU_PLUGIN . 'latexport/renderer/internal_link.php';
require_once DOKU_PLUGIN . 'latexport/renderer/decorator.php';

/**
 * Renders internallinks that are alone in an item of unordered lists as 
 * sub-document inclusions.
 *
 * Latexport Plugin: Exports to latex
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Jean-Michel Gonet <jmgonet@yahoo.com>
 */
class DecoratorIncluder extends Decorator {

	const START                   = 999;
	/** State machine: in a list. */
	const IN_LIST                  = 1000;
	/** State machine: in an item of a list. */
	const IN_ITEM                  = 1001;
	/** State machine: in the content of a list item. */
	const IN_CONTENT               = 1002;
	/** State machine: in the content of a list item, which contains mixed elements */
	const IN_CONTENT_MIXED         = 1003;
	/** State machine: in the content of a list item, which contains only internal links.*/
	const IN_CONTENT_INTERNAL_LINK = 1004;
	/** State machine: in the content of a list item, which contains another list.*/
	const IN_CONTENT_NESTED_LIST   = 1005;

	/** State of the state machine. */
	private $state = START;

	/** Contains the page found in the last processed internal link. */
	private $internalLinkToInclude;
	/** Contains the list of pages to include. */
	private $internalLinks = [];

	/** If mixed content is found, then we'll need to open the list. */
	private $needToOpenList;
	/** If mixed content is found, then we'll need to open the item. */
	private $needToOpenItem;
	/** If mixed content is found, then we'll need to open the content. */
	private $needToOpenContent;
	/** We've found some mixed content in the current item, so remmeber to close content and item. */
	private $mixedContentFoundInThisItem;
	/** We've found some items with mixed content in the current list, so remmeber to close the list. */
	private $mixedContentFoundInSomeItems;
	
	/**
	 * Class constructor.
	 * @param decorator To send further the decorated events.
	 */
	function __construct($decorator) {
		parent::__construct($decorator);
		$this->state = DecoratorIncluder::START;
	}

	/**
	 * Returns the list of internal links to include.
	 */
	function getInternalLinks() {
		return $this->internalLinks;
	}

	/**
	 * Receives the unordered list open notification.
	 * If 
	 */
	function listu_open() {
		switch($this->state) {
			case DecoratorIncluder::START:
				$this->needToOpenList = true;
				$this->mixedContentFoundInSomeItems = false;
				$this->state = DecoratorIncluder::IN_LIST;
				break;

			case DecoratorIncluder::IN_CONTENT:
			case DecoratorIncluder::IN_CONTENT_MIXED:
				$this->decorator->listu_open();
				return $this->decorator;
				
			default:
				trigger_error("listu_open unexpected $this->state");
		}
	}

	/**
	 * Open a list item
	 *
	 * @param int $level the nesting level
	 * @param bool $node true when a node; false when a leaf
	 */
	function listitem_open($level,$node=false) {
		$this->state = DecoratorIncluder::IN_ITEM;
		$this->needToOpenItem = true;
		$this->mixedContentFoundInThisItem = false;
	}

	/**
	 * Start the content of a list item
	 */
	function listcontent_open() {
		$this->state = DecoratorIncluder::IN_CONTENT;
		$this->needToOpenContent = true;
	}

	/**
	 * Stop the content of a list item
	 */
	function listcontent_close() {
		switch($this->state) {
			case DecoratorIncluder::IN_CONTENT_INTERNAL_LINK:
				$this->internalLinks[] = $this->internalLinkToInclude;
				break;

			case DecoratorIncluder::IN_CONTENT_MIXED:
			case DecoratorIncluder::IN_CONTENT:
				$this->decorator->listcontent_close();
				break;
			
			default:
				trigger_error("listcontent_close unexpected - $this->state");
		}
		$this->state = DecoratorIncluder::IN_ITEM;
	}

    /**
     * Close a list item
     */
    function listitem_close() {
		if ($this->mixedContentFoundInThisItem) {
			$this->decorator->listitem_close();
		}
		$this->state = DecoratorIncluder::IN_LIST;
    }

	/**
	 * Close an unordered list
	 */
	function listu_close() {
		if ($this->mixedContentFoundInSomeItems) {
			$this->decorator->listu_close();
		}
	}

	/**
	 * Receives a wiki internal link.
	 * Internal links at the very beginning of an unordered item include
	 * the destination page. If they are in any other position, they are
	 * rendered normally.
	 * @param string       $link  page ID to link to. eg. 'wiki:syntax'
	 * @param string|array $title name for the link, array for media file
	 */
	function internallink($link, $title = null) {
		switch($this->state) {

			case DecoratorIncluder::IN_CONTENT:
				$this->internalLinkToInclude = new InternalLink($link, $title);
				$this->state = DecoratorIncluder::IN_CONTENT_INTERNAL_LINK;
				break;

			case DecoratorIncluder::IN_CONTENT_INTERNAL_LINK:
				$this->internalLinkToInclude = null;
				$this->decorator->internallink($this->internalLinkToInclude->getLink(), $this->internalLinkToInclude->getTitle());
				$this->decorator->internallink($link, $title);
				$this->state = DecoratorIncluder::IN_CONTENT_MIXED;
				break;

			case DecoratorIncluder::IN_CONTENT_MIXED:
			default:
				$this->decorator->internallink($link, $title);
				break;
		}
	}	

	/**
	 * Open a paragraph.
	 */
	function p_open() {
		$this->thereIsMixedContentInThisItem();
		parent::p_open();
	}

	/**
	 * Renders plain text.
	 */
	function cdata($text) {
		// It is very common to place spaces between the unordered list bullet and the content:
		if ($this->state == DecoratorIncluder::IN_CONTENT) {
			// We ignore those whites:
			if (ctype_space($text)) {
				return;
			}
		}
		
		// Any other kind of content is propagated:
		$this->thereIsMixedContentInThisItem();
		parent::cdata($text);					
	}

	/**
	 * Start emphasis (italics) formatting
	 */
	function emphasis_open() {
		$this->thereIsMixedContentInThisItem();
		parent::emphasis_open();
	}

	/**
	 * Start strong (bold) formatting
	 */
	function strong_open() {
		$this->thereIsMixedContentInThisItem();
		parent::strong_open();
	}

	/**
	 * Start underline formatting
	 */ 
	function underline_open() {
		$this->thereIsMixedContentInThisItem();
		parent::underline_open();
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
		$this->thereIsMixedContentInThisItem();
		parent::internalmedia($src, $title, $align, $width, $height, $cache, $linking);
	}


	/**
	 * Receives mathematic formula from Mathjax plugin.
	 * As Mathjax already uses $ or $$ as separator, there is no
	 * need to reprocess.
	 */
	function mathjax_content($formula) {
		$this->thereIsMixedContentInThisItem();
		parent::mathjax_content($formula);
	}

	private function thereIsMixedContentInThisItem() {
		if ($this->needToOpenList) {
			parent::listu_open();
			$this->needToOpenList = false;
		}

		if ($this->needToOpenItem) {
			parent::listitem_open(1);
			$this->needToOpenItem = false;
		}

		if ($this->needToOpenContent) {
			parent::listcontent_open();
			$this->needToOpenContent = false;
		}
		$this->mixedContentFoundInThisItem = true;
		$this->mixedContentFoundInSomeItems = true;
		$this->state = DecoratorIncluder::IN_CONTENT_MIXED;
	}	
}