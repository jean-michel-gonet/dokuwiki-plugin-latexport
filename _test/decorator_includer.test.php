<?php
/**
 * @group plugin_latexport
 * @group plugins
 */

require_once DOKU_PLUGIN . 'latexport/_test/decorator_mock.php';
require_once DOKU_PLUGIN . 'latexport/renderer/decorator_includer.php';

class DecoratorIncluderTest extends DokuWikiTest {
 
 	const LINK = "a:page:id";
 	const TITLE = "A page";

 	const LINK2 = "a:page:id2";
 	const TITLE2 = "A second page";
	
    protected $pluginsEnabled = array('latexport', 'mathjax');
	private $decoratorMock;
	private $includes;
	private $decoratorIncluder;
	
    public static function setUpBeforeClass(){
        parent::setUpBeforeClass();
	}

	public function setUp() {
		$this->includes = new SplQueue();
		$this->decoratorMock = new DecoratorMock();
		$this->decoratorIncluder = new DecoratorIncluder($this->includes, $this->decoratorMock);
    }
	
    public function testInternalLinksAloneInAnUnorderedListItemAreIncludes() {
		$this->decoratorIncluder->listu_open();
		$this->decoratorIncluder->listitem_open(1);
		$this->decoratorIncluder->listcontent_open();
		
		$this->decoratorIncluder->cdata(' ');
		$this->decoratorIncluder->internallink(DecoratorIncluderTest::LINK, DecoratorIncluderTest::TITLE);

		$this->decoratorIncluder->listcontent_close();
		$this->decoratorIncluder->listitem_close();
		$this->decoratorIncluder->listu_close();
		
		$this->assertTrue($this->decoratorMock->noCommands(), "Should not have any command");
		$this->assertEquals($this->includes->count(), 1, "Should have one include");
		
		$link = $this->includes->pop();
		$this->assertEquals($link->getLink(), DecoratorIncluderTest::LINK, "Link is not as expected");
		$this->assertEquals($link->getTitle(), DecoratorIncluderTest::TITLE, "Title is not as expected");
    }

	public function testInternalLinksMixedWithTextInAnUnorderedListItemAreRendered() {
		$this->decoratorIncluder->listu_open();
		$this->decoratorIncluder->listitem_open(1);
		$this->decoratorIncluder->listcontent_open();

		$this->decoratorIncluder->cdata('Follow the link:');
		$this->decoratorIncluder->internallink(DecoratorIncluderTest::LINK, DecoratorIncluderTest::TITLE);

		$this->decoratorIncluder->listcontent_close();
		$this->decoratorIncluder->listitem_close();
		$this->decoratorIncluder->listu_close();
		
		$this->assertEquals($this->includes->count(), 0, "Should not have any include");

		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandListUOpen());
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandListItemOpen(1));
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandListContentOpen());
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandCData('Follow the link:'));
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandInternalLink(DecoratorIncluderTest::LINK, DecoratorIncluderTest::TITLE));
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandListContentClose());
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandListItemClose());
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandListUClose());
	}

	public function testCanRenderAListWithSeveralItems() {
		$this->decoratorIncluder->listu_open();

		$this->decoratorIncluder->listitem_open(1);
		$this->decoratorIncluder->listcontent_open();
		$this->decoratorIncluder->cdata('List item 1');
		$this->decoratorIncluder->listcontent_close();
		$this->decoratorIncluder->listitem_close();

		$this->decoratorIncluder->listitem_open(1);
		$this->decoratorIncluder->listcontent_open();
		$this->decoratorIncluder->cdata('List item 2');
		$this->decoratorIncluder->listcontent_close();
		$this->decoratorIncluder->listitem_close();

		$this->decoratorIncluder->listu_close();
		
		$this->assertEquals($this->includes->count(), 0, "Should not have any include");

		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandListUOpen());

		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandListItemOpen(1));
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandListContentOpen());
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandCData('List item 1'));
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandListContentClose());
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandListItemClose());

		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandListItemOpen(1));
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandListContentOpen());
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandCData('List item 2'));
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandListContentClose());
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandListItemClose());

		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandListUClose());
	}

	public function testCanRenderANestedList() {
		error_log("**********testCanRenderANestedList*******");
		$this->decoratorIncluder->listu_open();
		$this->decoratorIncluder->listitem_open(1);
		$this->decoratorIncluder->listcontent_open();

		$this->decoratorIncluder->listu_open();
		$this->decoratorIncluder->listitem_open(2);
		$this->decoratorIncluder->listcontent_open();
		$this->decoratorIncluder->cdata('List item 2');
		$this->decoratorIncluder->listcontent_close();
		$this->decoratorIncluder->listitem_close();
		$this->decoratorIncluder->listu_close();
		$this->decoratorIncluder->listcontent_close();
		$this->decoratorIncluder->listitem_close();
		$this->decoratorIncluder->listu_close();
		error_log("--------------------------------------------");
		
		$this->assertEquals($this->includes->count(), 0, "Should not have any include");

		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandListUOpen());
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandListItemOpen(1));
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandListContentOpen());

		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandListUOpen());
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandListItemOpen(2));
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandListContentOpen());
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandCData('List item 2'));
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandListContentClose());
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandListItemClose());
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandListUClose());

		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandListContentClose());
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandListItemClose());
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandListUClose());
	}

	public function testStandaloneInternalLinkAndCanBeMixedWithText() {
		$this->decoratorIncluder->listu_open();
		
		$this->decoratorIncluder->listitem_open(1);
		$this->decoratorIncluder->listcontent_open();
		$this->decoratorIncluder->cdata(' ');
		$this->decoratorIncluder->internallink(DecoratorIncluderTest::LINK2, DecoratorIncluderTest::TITLE2);
		$this->decoratorIncluder->listcontent_close();
		$this->decoratorIncluder->listitem_close();

		$this->decoratorIncluder->listitem_open(1);
		$this->decoratorIncluder->listcontent_open();
		$this->decoratorIncluder->cdata('Follow the link:');
		$this->decoratorIncluder->internallink(DecoratorIncluderTest::LINK, DecoratorIncluderTest::TITLE);
		$this->decoratorIncluder->listcontent_close();
		$this->decoratorIncluder->listitem_close();

		$this->decoratorIncluder->listu_close();
		
		$this->assertEquals($this->includes->count(), 1, "Should not have one include");
		$link = $this->includes->pop();
		$this->assertEquals($link->getLink(), DecoratorIncluderTest::LINK2, "Link is not as expected");
		$this->assertEquals($link->getTitle(), DecoratorIncluderTest::TITLE2, "Title is not as expected");

		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandListUOpen());
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandListItemOpen(1));
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandListContentOpen());
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandCData('Follow the link:'));
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandInternalLink(DecoratorIncluderTest::LINK, DecoratorIncluderTest::TITLE));
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandListContentClose());
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandListItemClose());
		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandListUClose());
		
		$this->assertTrue($this->decoratorMock->noCommands(), "Should not have more commands");
		
	}
	
	public function testCanComputeTheHeadingLevelOfLinks() {
		$this->decoratorIncluder->header("Any", 3, 0);
		
		$this->decoratorIncluder->listu_open();

		$this->decoratorIncluder->listitem_open(1);
		$this->decoratorIncluder->listcontent_open();
		$this->decoratorIncluder->cdata(' ');
		$this->decoratorIncluder->internallink(DecoratorIncluderTest::LINK2, DecoratorIncluderTest::TITLE2);
		$this->decoratorIncluder->listcontent_close();
		$this->decoratorIncluder->listitem_close();

		$this->decoratorIncluder->listu_close();
		
		$link = $this->includes->pop();
		$this->assertEquals($link->getHeadingLevel(), 3, "Heading level is not as expected");

		$this->assertEquals($this->decoratorMock->nextCommand(), new CommandHeader("Any", 3, 0));
		
		$this->assertTrue($this->decoratorMock->noCommands(), "Should not have more commands");		
	}
	
}
?>