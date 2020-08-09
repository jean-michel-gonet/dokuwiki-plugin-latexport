<?php
/**
 * @group plugin_latexport
 * @group plugins
 */

require_once DOKU_PLUGIN . 'latexport/_test/decorator_mock.php';
require_once DOKU_PLUGIN . 'latexport/implementation/decorator_headings.php';

class DecoratorHeadingsTest extends DokuWikiTest {

    protected $pluginsEnabled = array('latexport', 'mathjax');

	private $decoratorMock;

	private $decoratorHeadings;

    public static function setUpBeforeClass(){
        parent::setUpBeforeClass();
	}

	public function setUp() {
		$this->decoratorMock = new DecoratorMock();
		$this->decoratorHeadings = new DecoratorHeadings($this->decoratorMock);
    }

    public function testFirstAndSecondH1AreH1AndNextAreH3() {
		$this->decoratorHeadings->header("text1", 1, 10);	// This would open the main matter.
		$this->decoratorHeadings->header("text2", 1, 20);	// This would open the appendix.
		$this->decoratorHeadings->header("text3", 1, 30);	// This is a chapter in the appendix.
		$this->decoratorHeadings->header("text4", 1, 40);   // This is a chapter in the appendix.

		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandHeader("text1", 1, 10));
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandHeader("text2", 1, 20));
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandHeader("text3", 3, 30));
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandHeader("text4", 3, 40));

		$this->assertTrue($this->decoratorMock->noCommands(), "Should not have more commands");
    }

    public function testH2BeforeH1AreChapters() {
		$this->decoratorHeadings->header("text1", 2, 10);	// This is a chapter in the front matter.
		$this->decoratorHeadings->header("text2", 2, 20);	// This is a chapter in the front matter.
		$this->decoratorHeadings->header("text3", 1, 30);	// This opens the main matter.
		$this->decoratorHeadings->header("text4", 2, 40);	// This is a section in the main matter.

		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandHeader("text1", 3, 10));
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandHeader("text2", 3, 20));
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandHeader("text3", 1, 30));
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandHeader("text4", 2, 40));

		$this->assertTrue($this->decoratorMock->noCommands(), "Should not have more commands");
    }

    public function testMinimumLevelIsH5() {
		$this->decoratorHeadings->header("text1", 10, 10);
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandHeader("text1", 5, 10));
    }
}
?>
