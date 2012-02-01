<?php
include_once 'controlador/CPrincipal.php';

$cp = new CPrincipal();
?>
<?php if($cp->showLayout) include $cp->getLayout();?>