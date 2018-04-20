<?php
/**
 * @group plugin_latexport
 * @group plugins
 */

require_once DOKU_PLUGIN . 'latexport/_test/decorator_mock.php';
require_once DOKU_PLUGIN . 'latexport/renderer/decorator_images.php';

class DecoratorImagesTest extends DokuWikiTest {
 	
    protected $pluginsEnabled = array('latexport', 'mathjax');

	private $decoratorMock;

	private $decoratorImages;
	
    public static function setUpBeforeClass(){
        parent::setUpBeforeClass();
	}

	public function setUp() {
		$this->decoratorMock = new DecoratorMock();
		$this->decoratorImages = new DecoratorImages($this->decoratorMock);
    }
	
    public function testCanDisplayASingleImage() {
		return;
		$this->decoratorImages->header("text1", 1, 10);	// This would open the main matter.
		$this->decoratorImages->header("text2", 1, 20);	// This would open the appendix.
		$this->decoratorImages->header("text3", 1, 30);	// This is a chapter in the appendix.
		$this->decoratorImages->header("text4", 1, 40);    // This is a chapter in the appendix.

		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandHeader("text1", 1, 10));
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandHeader("text2", 1, 20));
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandHeader("text3", 3, 30));
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandHeader("text4", 3, 40));

		$this->assertTrue($this->decoratorMock->noCommands(), "Should not have more commands");
    }
}
?>