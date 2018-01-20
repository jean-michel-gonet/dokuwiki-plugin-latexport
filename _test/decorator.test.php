<?php
/**
 * @group plugin_latexport
 * @group plugins
 */
require_once DOKU_PLUGIN . 'latexport/_test/decorator_mock.php';

class decorator_test extends DokuWikiTest {
 
    protected $pluginsEnabled = array('latexport', 'mathjax');
	private $decoratorMock;
	private $decorator;
	
    public static function setUpBeforeClass(){
        parent::setUpBeforeClass();
	}

	public function setUp() {
		$this->decoratorMock = new DecoratorMock();
		$this->decorator = new Decorator($this->decoratorMock);
    }
 
    public function testDocumentStartPropagatesRecursionLevel() {
		$this->decorator->document_start("xx", 3);
		$this->assertEquals(3, $this->decoratorMock->recursionLevel, "Recursion level");
    }
}
?>