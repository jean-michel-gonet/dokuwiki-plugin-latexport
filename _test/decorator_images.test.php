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
		$this->decoratorImages->p_open();
		$this->decoratorImages->internalmedia("S1", "Title1", "centered", 10, 20); 
		$this->decoratorImages->p_close();

		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandPOpen());
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandInternalMedia("S1", "Title1", "centered", 10, 20, 0, 1));
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandPClose());

		$this->assertTrue($this->decoratorMock->noCommands(), "Should not have more commands");
    }

    public function testCanDisplayTwoSingleImages() {
		$this->decoratorImages->p_open();
		$this->decoratorImages->internalmedia("S1", "Title1", "centered", 10, 20); 
		$this->decoratorImages->cdata(" x ");
		$this->decoratorImages->internalmedia("S2", "Title2", "22", 12, 22); 
		$this->decoratorImages->p_close();

		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandPOpen());
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandInternalMedia("S1", "Title1", "centered", 10, 20, 0, 1));
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandCData(" x "));
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandInternalMedia("S2", "Title2", "22", 12, 22, 0, 1));
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandPClose());

		$this->assertTrue($this->decoratorMock->noCommands(), "Should not have more commands");
    }
	
	public function testCanGroupTwoImagesSeparatedWithASpace() {
		$this->decoratorImages->p_open();
		$this->decoratorImages->internalmedia("S1", "Title1", "11", 11, 21); 
		$this->decoratorImages->cdata(" ");
		$this->decoratorImages->internalmedia("S2", "Title2", "22", 12, 22); 
		$this->decoratorImages->p_close();

		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandPOpen());
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandCData(" "));
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandInternalMedia("S1", "Title1", "11", 11, 21, 0, 2));
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandInternalMedia("S2", "Title2", "22", 12, 22, 1, 2));
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandPClose());

		$this->assertTrue($this->decoratorMock->noCommands(), "Should not have more commands");
	}
}
?>