<?php
class util{
	function __construct() {}
	
	function limpia($n) {
		return mysql_real_escape_string($n);
	}
	
	function code($n) {
		return utf8_encode($n);
	}
	
	function decode($n) {
		return utf8_decode($n);
	}
}
?>
