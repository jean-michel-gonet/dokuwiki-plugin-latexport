<?php
/**
 * A mock decorator, handy for unit testing.
 */
require_once DOKU_PLUGIN . 'latexport/renderer/decorator.php';
require_once DOKU_PLUGIN . 'latexport/_test/command.php';

class DecoratorMock extends Decorator {
	
	public $listOfCommands;

	public $recursionLevel;
	
	function __construct() {
		$this->listOfCommands = new SplQueue();
	}

	function nextCommand() {
		if ($this->noCommands()) {
			return false;
		} else {
			return $this->listOfCommands->dequeue();			
		}
	}
	
	function noCommands() {
		return $this->listOfCommands->isEmpty();
	}

	function document_start($recursionLevel = 0) {
		$this->recursionLevel = $recursionLevel;
		// Nothing to do?
	}

	function header($text, $level, $pos) {
		$this->listOfCommands->enqueue(new CommandHeader($text, $level, $pos));
	}

	function p_open() {
		// Nothing to do?
	}

	function cdata($text) {
		$this->listOfCommands->enqueue(new CommandCData($text));
	}

	function p_close() {
		// Nothing to do?
	}

	function emphasis_open() {
		// Nothing to do?
	}

	function emphasis_close() {
		// Nothing to do?
	}

	function strong_open() {
		// Nothing to do?
	}

	function strong_close() {
		// Nothing to do?
	}

	function underline_open() {
		// Nothing to do?
	}

	function underline_close() {
		// Nothing to do?
	}

	function internallink($link, $title = null) {
		$this->listOfCommands->enqueue(new CommandInternalLink($link, $title));
	}

	function input($link) {
		// Nothing to do?
	}

	function internalmedia($src, $title = null, $align = null, $width = null,
			$height = null, $cache = null, $linking = null) {

		// Nothing to do?
	}

    function listo_open() {
		$this->listOfCommands->enqueue(new CommandListOOpen());
    }

    function listo_close() {
		$this->listOfCommands->enqueue(new CommandListOClose());
    }

	function listu_open() {
		$this->listOfCommands->enqueue(new CommandListUOpen());
	}

	function listu_close() {
		$this->listOfCommands->enqueue(new CommandListUClose());
	}

	function listitem_open($level,$node=false) {
		$this->listOfCommands->enqueue(new CommandListItemOpen($level, $node));
	}

	function listcontent_open() {
		$this->listOfCommands->enqueue(new CommandListContentOpen());
	}

	function listcontent_close() {
		$this->listOfCommands->enqueue(new CommandListContentClose());
	}

    function listitem_close() {
		$this->listOfCommands->enqueue(new CommandListItemClose());
    }

	function mathjax_content($formula) {
		// Nothing to do?
	}

	function document_end($recursionLevel = 0){
		// Nothing to do?
	}
}
?>