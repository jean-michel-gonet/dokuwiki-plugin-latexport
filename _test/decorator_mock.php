<?php
/**
 * A mock decorator, handy for unit testing.
 */
require_once DOKU_PLUGIN . 'latexport/renderer/decorator.php';

class DecoratorMock extends Decorator {
	public $recursionLevel;
	
	function __construct($decorator = null) {
		parent::__construct($decorator);
	}
	
	function document_start($recursionLevel = 0) {
		$this->recursionLevel = $recursionLevel;
	}
}
?>