<?php
include_once("modelo/base.php");

class vista {
	private $base;
	
	function __construct() {
		$this->base = new base();
	}
	
	function showHeader() {
		echo $this->base->getHeader();
	}
}
?>
