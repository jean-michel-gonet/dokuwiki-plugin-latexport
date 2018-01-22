<?php
/**
 * @group plugin_latexport
 * @group plugins
 */

require_once DOKU_PLUGIN . 'latexport/_test/decorator_mock.php';
require_once DOKU_PLUGIN . 'latexport/renderer/decorator_math.php';

class DecoratorMathTest extends DokuWikiTest {
 
	
    protected $pluginsEnabled = array('latexport', 'mathjax');
	private $decoratorMock;
	private $decoratorMath;
	
    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();
	}

	public function setUp() {
		$this->decoratorMock = new DecoratorMock();
		$this->decoratorMath = new DecoratorMath($this->decoratorMock);
    }
	
    public function testPlacesADisplayFormulaWithDollarsIntoBeginAndEndEquation() {
		$formula = "e = m \cdot e^2";
		$this->decoratorMath->mathjax_content("$");
		$this->decoratorMath->mathjax_content("$ $formula");
		$this->decoratorMath->mathjax_content(" $$");
		$this->decoratorMath->cdata("Hey");
		
		$this->assertEquals(new CommandMathjaxContent("\\begin{equation}\r\n    $formula\r\n\\end{equation}\r\n"),
				            $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandCData('Hey'),
		                    $this->decoratorMock->nextCommand());
		
		$this->assertTrue($this->decoratorMock->noCommands(), "Should not have more commands");
    }	

	public function testPlacesADisplayFormulaWithBracketsIntoBeginAndEndEquation() {
		$formula = "e = m \cdot e^2";
		$this->decoratorMath->mathjax_content("\\[");
		$this->decoratorMath->mathjax_content(" $formula");
		$this->decoratorMath->mathjax_content(" \\]");
		$this->decoratorMath->cdata("Hey");
		
		$this->assertEquals(new CommandMathjaxContent("\\begin{equation}\r\n    $formula\r\n\\end{equation}\r\n"),
				            $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandCData('Hey'),
		                    $this->decoratorMock->nextCommand());
		
		$this->assertTrue($this->decoratorMock->noCommands(), "Should not have more commands");		
	}

	public function testPlacesAnInlineFormulaWithDollarBetweenParenthesis() {
		$formula = "e = m \cdot e^2";
		$this->decoratorMath->mathjax_content("$");
		$this->decoratorMath->mathjax_content("$formula");
		$this->decoratorMath->mathjax_content("$");
		$this->decoratorMath->cdata(" Hey");
		
		$this->assertEquals(new CommandMathjaxContent("\\($formula\\)"),
				            $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandCData(' Hey'),
		                    $this->decoratorMock->nextCommand());
		
		$this->assertTrue($this->decoratorMock->noCommands(), "Should not have more commands");		
	}

	public function testPlacesAnInlineFormulaWithParenthesisBetweenParenthesis() {
		$formula = "e = m \cdot e^2";
		$this->decoratorMath->mathjax_content("\\(");
		$this->decoratorMath->mathjax_content("$formula\\");
		$this->decoratorMath->mathjax_content(")");
		$this->decoratorMath->cdata(" Hey");
		
		$this->assertEquals(new CommandMathjaxContent("\\($formula\\)"),
				            $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandCData(' Hey'),
		                    $this->decoratorMock->nextCommand());
		
		$this->assertTrue($this->decoratorMock->noCommands(), "Should not have more commands");		
	}
	
    public function testLeavesAmsmathCommandsUntouched() {
		$formula = "\\begin{align*}
e^x & = 1 + x + \\frac{x^2}{2} + \\frac{x^3}{6} + \\cdots \\
    & = \\sum_{n\geq 0} \\frac{x^n}{n!}
\\end{align*}";
		$this->decoratorMath->mathjax_content($formula);
		$this->decoratorMath->cdata("Hey");
		
		$this->assertEquals(new CommandMathjaxContent($formula),         
		                    $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandCData('Hey'),                     
		                    $this->decoratorMock->nextCommand());

		$this->assertTrue($this->decoratorMock->noCommands(), "Should not have more commands");
    }	

    public function testRemovesTheExplicitTagCommandFromInlineAndDisplay() {
		$this->decoratorMath->mathjax_content("\\[ \\label{coser-b}\\tag{removeme} \\]");
		$this->decoratorMath->cdata("Hey");
		$this->decoratorMath->mathjax_content("$$ \\label{coser-b}\\tag{removeme} $$");
		$this->decoratorMath->cdata("Hey");
		
		$this->assertEquals(new CommandMathjaxContent("\\begin{equation}\r\n    \\label{coser-b}\r\n\\end{equation}\r\n"),
		                    $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandCData('Hey'),                     
		                    $this->decoratorMock->nextCommand());
							
		$this->assertEquals(new CommandMathjaxContent("\\begin{equation}\r\n    \\label{coser-b}\r\n\\end{equation}\r\n"),
		                    $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandCData('Hey'),                     
		                    $this->decoratorMock->nextCommand());
							
		$this->assertTrue($this->decoratorMock->noCommands(), "Should not have more commands");
    }	
	
	public function testKeepsTheExplicitTagCommandFromAmsmath() {
		$this->decoratorMath->mathjax_content("\\begin{align} \\label{coser-b}\\tag{removeme} \\end{align}");
		$this->decoratorMath->cdata("Hey");

		$this->assertEquals(new CommandMathjaxContent("\\begin{align} \\label{coser-b}\\tag{removeme} \\end{align}"),
		                    $this->decoratorMock->nextCommand());
		$this->assertEquals(new CommandCData('Hey'),                     
		                    $this->decoratorMock->nextCommand());
		
		$this->assertTrue($this->decoratorMock->noCommands(), "Should not have more commands");
	}
}
?>