<?php

class TexCommand {

	public $command;

	/**
	 * Class constructor.
	 * @param decorator The next decorator layer.
	 */
	function __construct($command) {
		$this->command = $command;
	}
}

class CommandHeader extends TexCommand {
	public $text;
	public $level;
	public $pos;
	
	function __construct($text, $level, $pos) {
		parent::__construct("listu_open");
		$this->text = $text;
		$this->level = $level;
		$this->pos = $pos;
	}
}

class CommandListOOpen extends TexCommand {
	function __construct() {
		parent::__construct("listo_open");
	}
}

class CommandListUOpen extends TexCommand {
	function __construct() {
		parent::__construct("listu_open");
	}
}

class CommandListItemOpen extends TexCommand {
	
	public $level;
	
	function __construct($level) {
		parent::__construct("listitem_open");
		$this->level = $level;
	}
}

class CommandCData extends TexCommand {
	public $text;
	
	function __construct($text) {
		parent::__construct("cdata");
		$this->text = $text;
	}
}

class CommandPOpen extends TexCommand {
	function __construct() {
		parent::__construct("p_open");
	}
}

class CommandPClose extends TexCommand {
	function __construct() {
		parent::__construct("p_close");
	}
}

class CommandLinebreak extends TexCommand {
	function __construct() {
		parent::__construct("linebreak");
	}
}

class CommandInternalLink extends TexCommand {
	public $link;
	public $text;
	
	function __construct($link, $title) {
		parent::__construct("listcontent_open");
	}	
}

class CommandFootnoteOpen extends TexCommand {
	function __construct() {
		parent::__construct("footnote_open");
	}	
}

class CommandFootnoteClose extends TexCommand {
	function __construct() {
		parent::__construct("footnote_close");
	}	
}

class CommandListContentOpen extends TexCommand {
	function __construct() {
		parent::__construct("listcontent_open");
	}	
}

class CommandListContentClose extends TexCommand {
	function __construct() {
		parent::__construct("listcontent_close");
	}
}

class CommandListItemClose extends TexCommand {
	function __construct() {
		parent::__construct("listitem_close");
	}
}

class CommandListUClose extends TexCommand {
	function __construct() {
		parent::__construct("listu_close");
	}
}
class CommandListOClose extends TexCommand {
	function __construct() {
		parent::__construct("listo_close");
	}
}
class CommandMathjaxContent extends TexCommand {
	public $formula;
	
	function __construct($formula) {
		parent::__construct("mathjax_content");
		$this->formula = $formula;
	}
}
class CommandAppendCommand extends TexCommand {
	public $command;
	public $scope;
	public $argument;

	function __construct($command, $scope, $argument = '') {
		parent::__construct("appendCommand");
		$this->command = $command;
		$this->scope = $scope;
		$this->argument = $argument;
	}
}

class CommandTableOpen extends TexCommand {
	private $maxcols = null;
	private $numrows = null;
	private $pos = null;
	
	function __construct($maxcols = null, $numrows = null, $pos = null) {
		parent::__construct("table_open");
		$this->maxcols = $maxcols;
		$this->numrows = $numrows;
		$this->pos = $pos;
	}
}

class CommandTableClose extends TexCommand {
	private $pos = null;
	
	function __construct($pos = null) {
		parent::__construct("table_close");
		$this->pos = $pos;
	}
}

class CommandTableHeadOpen extends TexCommand {
	function __construct() {
		parent::__construct("tablethead_open");
	}
}

class CommandTableHeadClose extends TexCommand {
	function __construct() {
		parent::__construct("tablethead_close");
	}
}

class CommandTableBodyOpen extends TexCommand {
	function __construct() {
		parent::__construct("tabletbody_open");
	}
}

class CommandTableBodyClose extends TexCommand {
	function __construct() {
		parent::__construct("tabletbody_close");
	}
}

class CommandTableFootOpen extends TexCommand {
	function __construct() {
		parent::__construct("tabletfoot_open");
	}
}

class CommandTableFootClose extends TexCommand {
	function __construct() {
		parent::__construct("tabletfoot_close");
	}
}

class CommandTableRowOpen extends TexCommand {
	function __construct() {
		parent::__construct("tablerow_open");
	}
}

class CommandTableRowClose extends TexCommand {
	function __construct() {
		parent::__construct("tablerow_close");
	}
}

class CommandTableHeaderOpen extends TexCommand {
	private $colspan;
	private $align;
	private $rowspan;

	function __construct($colspan = 1, $align = null, $rowspan = 1) {
		parent::__construct("tableheader_open");
		$this->colspan = $colspan;
		$this->align = $align;
		$this->rowspan = $rowspan;
	}
}

class CommandTableHeaderClose extends TexCommand {
	function __construct() {
		parent::__construct("tableheader_close");
	}
}

class CommandTableCellOpen extends TexCommand {
	private $colspan = 1;
	private $align = null;
	private $rowspan = 1;

	function __construct($colspan = 1, $align = null, $rowspan = 1) {
		parent::__construct("tablecell_open");
		$this->colspan = $colspan;
		$this->align = $align;
		$this->rowspan = $rowspan;
	}
}

class CommandTableCellClose extends TexCommand {
	function __construct() {
		parent::__construct("tablecell_close");
	}
}

class CommandTableCline extends TexCommand {
	private $start;
	private $end;

	function __construct($start, $end) {
		parent::__construct("table_cline");
		$this->start = $start;
		$this->end = $end;
	}
}


?>