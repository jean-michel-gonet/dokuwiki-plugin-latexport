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
	
	public function __toString() {
		return $this->command;
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
	
	function __toString() {
		return parent::__toString() + "$level -> \"$text\" ($pos)";
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
	
	function __toString() {
		return parent::__toString().'('.$this->level.')';
	}
}

class CommandCData extends TexCommand {
	public $text;
	
	function __construct($text) {
		parent::__construct("cdata");
		$this->text = $text;
	}

	function __toString() {
		return parent::__toString().'('.$this->text.')';
	}
}

class CommandInternalLink extends TexCommand {
	public $link;
	public $text;
	
	function __construct($link, $title) {
		parent::__construct("listcontent_open");
	}	
	function __toString() {
		return parent::__toString().'('.$this->link.', '.$this->title.')';
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

?>