<?php

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once DOKU_PLUGIN . 'latexport/renderer/decorator.php';

/**
 * Adapts the mathjax expressions to latex.
 * - If a $$ contains any \begin, then it is removed.
 * - Transforms $$ into \begin{equation} and \end{equation}.
 * - Groups multiple $$ into the same block of \begin{gather} \end{gather}.
 * - Leaves inline equations surrounded by $$.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Jean-Michel Gonet <jmgonet@yahoo.com>
 */
class DecoratorMath extends Decorator {
	
	const NOT_IN_EQUATION    = 1000;
	const IN_EQUATION        = 1001;
	
	private $state;
	
	private $equation;

	/**
	 * Class constructor.
	 * @param archive Will receive the content of the document.
	 */
	function __construct($decorator) {
		parent::__construct($decorator);
		$this->state = DecoratorMath::NOT_IN_EQUATION;
	}

	/**
	 * Receives mathematic formula from Mathjax plugin.
	 * Sometimes a formula is split across several calls.
	 * Formula is finished when any other command is called.
	 */
	function mathjax_content($formula) {
		if ($this->state == DecoratorMath::NOT_IN_EQUATION) {
			$this->equation = $formula;
			$this->state = DecoratorMath::IN_EQUATION;
		} else {
			$this->equation = $this->equation.$formula;			
		}
	}

	/**
	 * Sometimes a formula is split across several calls.
	 * Formula is finished when any other command is called.
	 */
	function any_command() {
		if ($this->state == DecoratorMath::IN_EQUATION) {
			$this->decorator->mathjax_content($this->processEquation($this->equation));
			$this->state = DecoratorMath::NOT_IN_EQUATION;
		}
	}
	
	/**
	 * Transforms the equation according to the rendering rules.
	 * - Formula surrounded (inline) by $ ... $ is transformed to \( ... \)
	 * - Formula surrounded by $$ ... $$ or \[ ... \] is surrounded with \begin{equation} ... \end{equation}
	 * - Formula containing any amsmath command is left as is.
	 */
	private function processEquation($equation) {
		if (substr( $equation, 0, 2 ) === "$$" || substr( $equation, 0, 2 ) === '\\[') {
			return $this->processDisplayEquation($equation);
		} else if (substr( $equation, 0, 1 ) === "$") {
			return $this->processInlineEquation($equation);
		} else {
			return $this->processAmsMathEquation($equation);
		}
	}
	
	private function processDisplayEquation($equation) {
		$trimmedEquation = substr($equation, 2, strlen($equation) - 4);
		$trimmedEquation = trim($trimmedEquation);
		$trimmedEquation = $this->removeTagCommand($trimmedEquation);
		return "\\begin{equation}\r\n    $trimmedEquation\r\n\\end{equation}\r\n";
	}

	private function processInlineEquation($equation) {
		$trimmedEquation = trim($this->removeTagCommand($equation), '$ ');
		return '\\('.$trimmedEquation.'\\)';
	}

	private function removeTagCommand($equation) {
		return preg_replace('/\\\\tag\{[^}]+\}/', '', $equation);
	}

	private function processAmsMathEquation($equation) {
		return $equation;
	}
}
