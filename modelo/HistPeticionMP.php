<?php
require_once 'Bd.php';

class AccionMP {
    protected $_dbTable = "HIST_PETICION";
    protected $_id = "ID_ACCION";
    protected $_bd;

    function __construct() {
        $this->_bd = new Bd();
    }
}
?>