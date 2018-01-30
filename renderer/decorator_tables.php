<?php

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once DOKU_PLUGIN . 'latexport/renderer/decorator.php';

class CellSize {
	
	public $colspan;
	
	public $rowspan;
	
	static function makeRow($maxcols) {
		$row = [];
		for ($n = 0; $n < $maxcols; $n++) {
			$row[] = new CellSize();
		}
		return $row;
	}
	
	public function __construct($colspan = 1, $rowspan = 0) {
		$this->colspan = $colspan;
		$this->rowspan = $rowspan;
	}
		
	public function setSize($colspan, $rowspan) {
		$this->colspan = $colspan;
		$this->rowspan = $rowspan;
	}
	
	public function getCols() {
		return $this->colspan;
	}
	
	public function getRows() {
		return $this->rowspan;
	}
	
	
	public function nextCellSize() {
		if ($this->rowspan > 0) {
			return new CellSize($this->colspan, $this->rowspan - 1);
		} else {
			return new CellSize();
		}
	}
	
	public function __toString() {
		return "<c=$this->colspan,r=$this->rowspan>";
	}
}

/**
 * Adapts the tables to latex.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Jean-Michel Gonet <jmgonet@yahoo.com>
 */
class DecoratorTables extends Decorator {

	private $row;

	private $column;

    /**
     * Start a table
     *
     * @param int $maxcols maximum number of columns
     * @param int $numrows NOT IMPLEMENTED
     * @param int $pos     byte position in the original source
     */
    function table_open($maxcols = null, $numrows = null, $pos = null) {
		$this->row = CellSize::makeRow($maxcols); 
		$this->decorator->table_open($maxcols, $numrows, $pos);
    }

    /**
     * Open a table row
     */
    function tablerow_open() {
		error_log("DecoratorTables::tablerow_open");
		$this->column = 0;
		$this->decorator->tablerow_open();
    }

    /**
     * Close a table row
     */
    function tablerow_close() {
		error_log("DecoratorTables::tablerow_close");
		$this->computeNextLine();
		$this->decorator->tablerow_close();
    }


    /**
     * Open a table header cell
     *
     * @param int    $colspan
     * @param string $align left|center|right
     * @param int    $rowspan
     */
    function tableheader_open($colspan = 1, $align = null, $rowspan = 1) {
		error_log("DecoratorTables::tableheader_open($colspan, $align, $rowspan)");
		$numberOfPlaceholders = $this->computePlaceholders($colspan, $rowspan);
		for ($n = 0; $n < $numberOfPlaceholders; $n++) {
			$this->decorator->tableheader_open(1, null, 1);
			$this->decorator->tableheader_close(1, null, 1);
		}
		$this->decorator->tableheader_open($colspan, $align, $rowspan);
    }

    /**
     * Open a table cell
     *
     * @param int    $colspan
     * @param string $align left|center|right
     * @param int    $rowspan
     */
    function tablecell_open($colspan = 1, $align = null, $rowspan = 1) {
		error_log("DecoratorTables::tablecell_open($colspan, $align, $rowspan)");
		$numberOfPlaceholders = $this->computePlaceholders($colspan, $rowspan);
		for ($n = 0; $n < $numberOfPlaceholders; $n++) {
			$this->decorator->tablecell_open(1, null, 1);
			$this->decorator->tablecell_close(1, null, 1);
		}
		$this->decorator->tablecell_open($colspan, $align, $rowspan);
    }

	function computePlaceholders($colspan, $rowspan) {
		error_log("DecoratorTables::computePlaceholders($colspan, $rowspan) - column=$this->column, colspan=$colspan, rowspan=$rowspan");
		$totalNumberOfPlaceholders = 0;
		do {
			$cell = $this->row[$this->column];
			error_log("                 computePlaceholders row[column=$this->column]=".$cell);
			if ($cell->getRows() > 0) {
				$numberOfPlaceholders = $cell->getCols();
				error_log("                 number of place holders: $numberOfPlaceholders");
			} else {
				$numberOfPlaceholders = 0;
			}
			$this->column += $numberOfPlaceholders;
			$totalNumberOfPlaceholders += $numberOfPlaceholders;
		} while ($numberOfPlaceholders > 0);
		$this->row[$this->column]->setSize($colspan, $rowspan);
		$this->column += $colspan;
		return $totalNumberOfPlaceholders;
	}

	function computeNextLine() {
		$row = [];
		$rs = "";
		$nrs = "";
		foreach($this->row as $cell) {
			$nextCell = $cell->nextCellSize();
			$rs.=$cell->__toString();
			$nrs.=$nextCell->__toString();
			$row[] = $nextCell;
		}
		error_log("row: $rs  -->  $nrs");
		$this->row = $row;
		$this->column = 0;
	}
	
}
