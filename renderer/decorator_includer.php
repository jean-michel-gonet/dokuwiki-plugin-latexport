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

	const NOT_IN_LIST              =  999;
	/** State machine: in a list. */
	const IN_LIST                  = 1000;
	/** State machine: in an item of a list. */
	const IN_ITEM                  = 1001;
	/** State machine: in the content of a list item. */
	const IN_CONTENT               = 1002;
	/** State machine: in the content of a list item, which contains mixed elements */
	const IN_ITEM_MIXED            = 1003;
	/** State machine: in the content of a list item, which contains only internal links.*/
	const IN_CONTENT_INTERNAL_LINK = 1004;
	/** State machine: This list is so mixed up that we just render it. */
	const IN_LIST_NESTED           = 1005;

	/** State of the state machine. */
	private $state;
	/** Level of the most recent heading. */
	private $headingLevel = 0;

	/** Contains the page found in the last processed internal link. */
	private $internalLinkToInclude;
	/** The list of internal links in the current list. */
	private $internalLinksToInclude;
	/** A queue of links to include. */
	private $includes;

	/** If mixed content is found, then we'll need to open the list. */
	private $needToOpenList;
	/** If mixed content is found, then we'll need to open the item. */
	private $needToOpenItem;
	/** If mixed content is found, then we'll need to open the content. */
	private $needToOpenContent;
	/** If some items have content, it is necessary to render the listu_close. */
	private $someItemsHaveMixedContent;
	
	
	/** To keep track of nested lists. */
	private $listLevel;
	
	/**
	 * Class constructor.
	 * @param includes A queue of links to include.
	 * @param decorator To send further the decorated events.
	 */
	function __construct($includes, $decorator) {
		parent::__construct($decorator);
		$this->includes = $includes;
		$this->state = DecoratorIncluder::NOT_IN_LIST;
		$this->headingLevel = 0;
		$this->listLevel = 0;
	}
	
	/**
	 * Unordered list item starting with a link, includes the destination page, 
	 * using the current level of heading as the base level.
	 */
	function header($text, $level, $pos) {
		$this->headingLevel = $level;
		$this->decorator->header($text, $level, $pos);
	}

	/**
	 * Receives the unordered list open notification.
	 * If 
	 */
	function listu_open() {
		switch($this->state) {
			case DecoratorIncluder::NOT_IN_LIST:
				$this->listLevel = 1;
				$this->needToOpenList = true;
				$this->state = DecoratorIncluder::IN_LIST;
				$this->internalLinksToInclude = [];
				$this->someItemsHaveMixedContent = false;
				break;

			case DecoratorIncluder::IN_ITEM:	// TODO: Remove this?
			case DecoratorIncluder::IN_CONTENT:
			case DecoratorIncluder::IN_ITEM_MIXED:
				$this->thereIsMixedContentInThisItem();
				$this->decorator->listu_open();
				$this->listLevel++;
				$this->state = DecoratorIncluder::IN_LIST_NESTED;
				break;
				
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
		
		switch($this->state) {
			case DecoratorIncluder::NOT_IN_LIST:
			case DecoratorIncluder::IN_ITEM_MIXED:	// TODO: Remove this?
			case DecoratorIncluder::IN_LIST_NESTED:
				$this->decorator->listitem_open($level, $node);
				break;

			case DecoratorIncluder::IN_LIST:
				$this->state = DecoratorIncluder::IN_ITEM;
				$this->needToOpenItem = true;
				break;

			default:
				trigger_error("listitem_open unexpected - $this->state");
		}
	}

	/**
	 * Start the content of a list item
	 */
	function listcontent_open() {
		switch($this->state) {
			case DecoratorIncluder::NOT_IN_LIST:
			case DecoratorIncluder::IN_ITEM_MIXED:	// TODO: Remove this?
			case DecoratorIncluder::IN_LIST_NESTED:
				$this->decorator->listcontent_open();
				break;

			case DecoratorIncluder::IN_ITEM:
				$this->state = DecoratorIncluder::IN_CONTENT;
				$this->needToOpenContent = true;
				break;
			default:
				trigger_error("listcontent_open unexpected - $this->state");
		}
	}

	/**
	 * Stop the content of a list item
	 */
	function listcontent_close() {
		switch($this->state) {
			case DecoratorIncluder::IN_CONTENT_INTERNAL_LINK:
				$this->internalLinksToInclude[] = $this->internalLinkToInclude;
				$this->state = DecoratorIncluder::IN_ITEM;
				break;

			case DecoratorIncluder::IN_CONTENT:   // TODO: Remove this?
			case DecoratorIncluder::IN_ITEM:      // TODO: Remove this?
				$this->decorator->listcontent_close();
				$this->state = DecoratorIncluder::IN_ITEM;
				break;

			case DecoratorIncluder::IN_ITEM_MIXED:
				$this->decorator->listcontent_close();
				break;
			
			case DecoratorIncluder::NOT_IN_LIST:
			case DecoratorIncluder::IN_LIST_NESTED:
				$this->decorator->listcontent_close();
				break;
			
			default:
				trigger_error("listcontent_close unexpected - $this->state");
		}
	}

    /**
     * Close a list item
     */
    function listitem_close() {
		switch($this->state) {
			case DecoratorIncluder::NOT_IN_LIST:
			case DecoratorIncluder::IN_LIST_NESTED:
				$this->decorator->listitem_close();			
				break;

			case DecoratorIncluder::IN_ITEM_MIXED:
				$this->decorator->listitem_close();			
				$this->state = DecoratorIncluder::IN_LIST;
				break;

			case DecoratorIncluder::IN_ITEM:
				$this->state = DecoratorIncluder::IN_LIST;
				break;
				
			default:
				trigger_error("listitem_close unexpected - $this->state");
		}
    }

	/**
	 * Close an unordered list
	 */
	function listu_close() {
		switch($this->state) {
			case DecoratorIncluder::IN_LIST:
				// Creates an input for each internal link to include, and stores the
				// destination pages in the queue:
				foreach($this->internalLinksToInclude as $internalLink) {
					$this->includes->push($internalLink);
					$this->decorator->input($this->texifyPageId($internalLink->getLink()));
				}
				// Render the list closing if necessary:
				if ($this->someItemsHaveMixedContent) {
					$this->decorator->listu_close();
				}
				// Not in list any more:
				$this->state = DecoratorIncluder::NOT_IN_LIST;			
				$this->listLevel = 0;
				break;
				
			case DecoratorIncluder::IN_LIST_NESTED:
				$this->decorator->listu_close();
				$this->listLevel--;
				if ($this->listLevel == 0) {
					$this->state = DecoratorIncluder::NOT_IN_LIST;			
				}
				break;

			case DecoratorIncluder::NOT_IN_LIST:
				parent::listu_close();
			
			default:
				trigger_error("listu_close unexpected - $this->state");
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
				$this->internalLinkToInclude = new InternalLink($link, $this->headingLevel, $title);
				$this->state = DecoratorIncluder::IN_CONTENT_INTERNAL_LINK;
				break;

			case DecoratorIncluder::IN_CONTENT_INTERNAL_LINK:
				$this->internalLinkToInclude = null;
				$this->decorator->internallink($link, $title);
				$this->state = DecoratorIncluder::IN_ITEM_MIXED;
				break;

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
		$this->someItemsHaveMixedContent = true;
	
		if ($this->state != DecoratorIncluder::NOT_IN_LIST) {
			if ($this->needToOpenList) {
				parent::listu_open();
				$this->needToOpenList = false;
			}

			if ($this->needToOpenItem) {
				parent::listitem_open($this->listLevel);
				$this->needToOpenItem = false;
			}

			if ($this->needToOpenContent) {
				parent::listcontent_open();
				$this->needToOpenContent = false;
			}

			$this->state = DecoratorIncluder::IN_ITEM_MIXED;
		}
	}	
}
