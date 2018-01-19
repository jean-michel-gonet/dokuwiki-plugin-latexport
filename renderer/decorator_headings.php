<?php

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once DOKU_PLUGIN . 'latexport/renderer/decorator.php';

/**
 * Maps the dokuwiki heading structure into a latex document structure
 * The root document:
 * 
 *     H1: The first H1 opens the main matter. The text of header is ignored.
 *         The second H1 opens the appendix. The text of header is ignored.
 *         The third and next H1 are considered chapters in the appendix. Text of header is title of the chapter.
 *     H2: Opens a part. 
 *         The text of header is placed as title of the part. 
 *         Also, H2 following the third or next H1 are considered chapters in the appendix.
 *     H3: Opens a chapter. 
 *         The text of header is placed as title of the chapter.
 *     H4: Opens a section. 
 *         The text of header is placed as title of the section.
 *     H5: Opens a subsection. 
 *         The text of header is placed as title of the part.
 *
 *     Unordered list item starting with a link includes the destination page, 
 *     using the current level of heading as the base level.
 * 
 * In the destination page:
 * 
 *     - The H1 opens a chapter, section, subsection, etc depending on the level of 
 *       heading in the referring page. Text of header is used as title of the heading.
 *     - The H1 never opens a level higher than chapter.
 *     - Lower header levels open a lower level headings.
 *     - Unordered list item starting with a link includes the destination page, 
 *       using the current level of heading as the base level.
 * 
 * Latexport Plugin: Exports to latex
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Jean-Michel Gonet <jmgonet@yahoo.com>
 */
class DecoratorHeadings extends Decorator {
	const FRONT_MATTER     = 900;
	const MAIN_MATTER      = 901;
	const APPENDIX         = 902;

	/**
	 * Internal state of this decorator.
	 */
	private $state;
	
	/**
	 * Class constructor.
	 * @param decorator The next decorator.
	 */	
	function __construct($decorator) {
		parent::__construct($decorator);
		$this->state = DecoratorHeadings::FRONT_MATTER;
	}

	/**
	 * Headers are transformed in part, chapter, section, subsection and subsubsection.
	 */
	function header($text, $level, $pos) {
		error_log("DecoratorHeadings::Header $text, $level, $pos");
		
		switch($level) {
			case 1:
				$this->h1($text, $pos);
				break;

			case 2:
				$this->h2($text, $pos);
				break;

			case 3:
				$this->h3($text, $pos);
				break;

			case 4:
				$this->h4($text, $pos);
				break;

			default:
				$this->h5($text, $pos);
				break;
		}
	}

	/**
	 * Handles H1 level headers.
	 * <ul>
     *   <li>The first H1 opens the main matter. The text of header is ignored.</li>
     *   <li>The second H1 opens the appendix. The text of header is ignored.</li>
     *   <li>The third and next H1 are considered chapters in the appendix. The text is the chapter title.</li>
     * </ul>
	 */
	private function h1($text, $pos) {
		switch($this->state) {
			case DecoratorHeadings::FRONT_MATTER:
				$this->state = DecoratorHeadings::MAIN_MATTER;
				$this->decorator->header($text, 1, $pos);
				break;
				
			case DecoratorHeadings::MAIN_MATTER:
				$this->state = DecoratorHeadings::APPENDIX;
				$this->decorator->header($text, 1, $pos);
				break;
				
			case DecoratorHeadings::APPENDIX:
				$this->decorator->header($text, 3, $pos);
				break;

			default:
				trigger_error("h1 unexpected $this->state");
		}
	}

	/**
	 * Handles H2 level headers.
	 * <ul>
     *   <li>H2 before the main matters are considered chapters.</li>
     *   <li>H2 in the main matter are considered parts.</li>
     *   <li>H2 in the appendix are considered chapters.</li>
     * </ul>
	 */
	private function h2($text, $pos) {
		switch($this->state) {
			case DecoratorHeadings::FRONT_MATTER:
				$this->decorator->header($text, 3, $pos);
				break;
				
			case DecoratorHeadings::MAIN_MATTER:
				$this->decorator->header($text, 2, $pos);
				break;
				
			case DecoratorHeadings::APPENDIX:
				$this->decorator->header($text, 3, $pos);
				break;
				
			default:
				trigger_error("h2 unexpected $this->state");
		}
	}

	private function h3($text, $pos) {
		$this->decorator->header($text, 3, $pos);
	}
	
	private function h4($text, $pos) {
		$this->decorator->header($text, 4, $pos);
	}
	
	private function h5($text, $pos) {
		$this->decorator->header($text, 5, $pos);
	}
		
}
