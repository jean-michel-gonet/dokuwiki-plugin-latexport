<?php
/**
 * @group plugin_latexport
 * @group plugins
 */

require_once DOKU_PLUGIN . 'latexport/_test/decorator_mock.php';
require_once DOKU_PLUGIN . 'latexport/renderer/decorator_tables.php';

class DecoratorTablesTest extends DokuWikiTest {
 
	
    protected $pluginsEnabled = array('latexport', 'mathjax');
	private $decoratorMock;
	private $decoratorTables;
	
    public static function setUpBeforeClass(){
        parent::setUpBeforeClass();
	}

	public function setUp() {
		$this->decoratorMock = new DecoratorMock();
		$this->decoratorTables = new DecoratorTables($this->decoratorMock);
    }
	
	public function testInTablesNewLinesAreReplacedByWordWrap() {
		$this->decoratorTables->p_close();
		$this->decoratorTables->table_open(3, 0, 0);
		$this->decoratorTables->p_close();
		$this->decoratorTables->table_close();
		$this->decoratorTables->p_close();
		
		$this->assertEquals(new CommandPClose(),            $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandTableOpen(3, 0, 0),  $this->decoratorMock->nextCommand());		
		$this->assertEquals(new CommandLinebreak(),         $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandTableClose(),        $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandPClose(),            $this->decoratorMock->nextCommand());
	}

    public function testDoesNotChangeAnythingOnTablesWithoutSpanning() {
		$this->decoratorTables->table_open(3, 0, 0);
		
		$this->decoratorTables->tablerow_open();
		$this->decoratorTables->tableheader_open(1, 1, 1);
		$this->decoratorTables->tableheader_close();
		$this->decoratorTables->tableheader_open(1, 1, 1);
		$this->decoratorTables->tableheader_close();
		$this->decoratorTables->tableheader_open(1, 1, 1);
		$this->decoratorTables->tableheader_close();

		$this->decoratorTables->tablerow_close();
		$this->decoratorTables->tablerow_open();
		
		$this->decoratorTables->tablecell_open(1, 1, 1);
		$this->decoratorTables->tablecell_close();
		$this->decoratorTables->tablecell_open(1, 1, 1);
		$this->decoratorTables->tablecell_close();
		$this->decoratorTables->tablecell_open(1, 1, 1);
		$this->decoratorTables->tablecell_close();
		
		$this->decoratorTables->tablerow_close();
		
		$this->decoratorTables->table_close();
		
		$this->assertEquals(new CommandTableOpen(3, 0, 0),       $this->decoratorMock->nextCommand());
		
		$this->assertEquals(new CommandTableRowOpen(),           $this->decoratorMock->nextCommand());
				
		$this->assertEquals(new CommandTableHeaderOpen(1, 1, 1), $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandTableHeaderClose(),       $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandTableHeaderOpen(1, 1, 1), $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandTableHeaderClose(),       $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandTableHeaderOpen(1, 1, 1), $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandTableHeaderClose(),       $this->decoratorMock->nextCommand());
		
		$this->assertEquals(new CommandTableRowClose(),          $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandTableCline(1, 3),         $this->decoratorMock->nextCommand());		
		$this->assertEquals(new CommandTableRowOpen(),           $this->decoratorMock->nextCommand());
		
		$this->assertEquals(new CommandTableCellOpen(1, 1, 1),   $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandTableCellClose(),         $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandTableCellOpen(1, 1, 1),   $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandTableCellClose(),         $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandTableCellOpen(1, 1, 1),   $this->decoratorMock->nextCommand());
	 	$this->assertEquals(new CommandTableCellClose(),         $this->decoratorMock->nextCommand());
		
		$this->assertEquals(new CommandTableRowClose(),          $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandTableCline(1, 3),         $this->decoratorMock->nextCommand());
		
		$this->assertEquals(new CommandTableClose(),             $this->decoratorMock->nextCommand());

		$this->assertTrue($this->decoratorMock->noCommands(), "Should not have more commands");
    }
	
    public function testCanHandleMulticolumn() {
		$this->decoratorTables->table_open(3, 0, 0);
		
		$this->decoratorTables->tablerow_open();
		$this->decoratorTables->tableheader_open(1, 1, 1);
		$this->decoratorTables->tableheader_close();
		$this->decoratorTables->tableheader_open(2, 1, 1);
		$this->decoratorTables->tableheader_close();

		$this->decoratorTables->tablerow_close();
		$this->decoratorTables->tablerow_open();
		
		$this->decoratorTables->tablecell_open(1, 1, 1);
		$this->decoratorTables->tablecell_close();
		$this->decoratorTables->tablecell_open(1, 1, 1);
		$this->decoratorTables->tablecell_close();
		$this->decoratorTables->tablecell_open(1, 1, 1);
		$this->decoratorTables->tablecell_close();
		
		$this->decoratorTables->tablerow_close();
		
		$this->decoratorTables->table_close();
		
		$this->assertEquals(new CommandTableOpen(3, 0, 0),       $this->decoratorMock->nextCommand());
		
		$this->assertEquals(new CommandTableRowOpen(),           $this->decoratorMock->nextCommand());
				
		$this->assertEquals(new CommandTableHeaderOpen(1, 1, 1), $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandTableHeaderClose(),       $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandTableHeaderOpen(2, 1, 1), $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandTableHeaderClose(),       $this->decoratorMock->nextCommand());
		
		$this->assertEquals(new CommandTableRowClose(),          $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandTableCline(1, 3),         $this->decoratorMock->nextCommand());		
		$this->assertEquals(new CommandTableRowOpen(),           $this->decoratorMock->nextCommand());
		
		$this->assertEquals(new CommandTableCellOpen(1, 1, 1),   $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandTableCellClose(),         $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandTableCellOpen(1, 1, 1),   $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandTableCellClose(),         $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandTableCellOpen(1, 1, 1),   $this->decoratorMock->nextCommand());
	 	$this->assertEquals(new CommandTableCellClose(),         $this->decoratorMock->nextCommand());
		
		$this->assertEquals(new CommandTableRowClose(),          $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandTableCline(1, 3),         $this->decoratorMock->nextCommand());		
		
		$this->assertEquals(new CommandTableClose(),             $this->decoratorMock->nextCommand());		
		
		$this->assertTrue($this->decoratorMock->noCommands(), "Should not have more commands");
    }
	
    public function testCanHandleMultirow() {
		
		$this->decoratorTables->table_open(3, 0, 0);
		
		$this->decoratorTables->tablerow_open();
		$this->decoratorTables->tableheader_open(1, 1, 2);
		$this->decoratorTables->tableheader_close();
		$this->decoratorTables->tableheader_open(1, 1, 2);
		$this->decoratorTables->tableheader_close();
		$this->decoratorTables->tableheader_open(1, 1, 1);
		$this->decoratorTables->tableheader_close();

		$this->decoratorTables->tablerow_close();
		$this->decoratorTables->tablerow_open();
		
		$this->decoratorTables->tablecell_open(1, 1, 1);
		$this->decoratorTables->tablecell_close();
		
		$this->decoratorTables->tablerow_close();
		$this->decoratorTables->tablerow_open();
		
		$this->decoratorTables->tablecell_open(1, 1, 1);
		$this->decoratorTables->tablecell_close();
		$this->decoratorTables->tablecell_open(1, 1, 1);
		$this->decoratorTables->tablecell_close();
		$this->decoratorTables->tablecell_open(1, 1, 1);
		$this->decoratorTables->tablecell_close();
		
		$this->decoratorTables->tablerow_close();
		
		$this->decoratorTables->table_close();
		
		
		$this->assertEquals(new CommandTableOpen(3, 0, 0),        $this->decoratorMock->nextCommand());
		
		$this->assertEquals(new CommandTableRowOpen(),            $this->decoratorMock->nextCommand());
				
		$this->assertEquals(new CommandTableHeaderOpen(1, 1, 2),  $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandTableHeaderClose(),        $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandTableHeaderOpen(1, 1, 2),  $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandTableHeaderClose(),        $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandTableHeaderOpen(1, 1, 1),  $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandTableHeaderClose(),        $this->decoratorMock->nextCommand());
		
		$this->assertEquals(new CommandTableRowClose(),           $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandTableCline(3, 3),         $this->decoratorMock->nextCommand());		
		$this->assertEquals(new CommandTableRowOpen(),            $this->decoratorMock->nextCommand());
		
		$this->assertEquals(new CommandTableCellOpen(1, null, 1), $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandTableCellClose(),          $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandTableCellOpen(1, null, 1), $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandTableCellClose(),          $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandTableCellOpen(1, 1, 1),    $this->decoratorMock->nextCommand());
	 	$this->assertEquals(new CommandTableCellClose(),          $this->decoratorMock->nextCommand());
		
		$this->assertEquals(new CommandTableRowClose(),           $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandTableCline(1, 3),         $this->decoratorMock->nextCommand());		
		$this->assertEquals(new CommandTableRowOpen(),            $this->decoratorMock->nextCommand());
		
		$this->assertEquals(new CommandTableCellOpen(1, 1, 1),    $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandTableCellClose(),          $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandTableCellOpen(1, 1, 1),    $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandTableCellClose(),          $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandTableCellOpen(1, 1, 1),    $this->decoratorMock->nextCommand());
	 	$this->assertEquals(new CommandTableCellClose(),          $this->decoratorMock->nextCommand());
		
		$this->assertEquals(new CommandTableRowClose(),           $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandTableCline(1, 3),         $this->decoratorMock->nextCommand());		
		
		$this->assertEquals(new CommandTableClose(),              $this->decoratorMock->nextCommand());		
		
		$this->assertTrue($this->decoratorMock->noCommands(), "Should not have more commands");
    }
	
}
?>