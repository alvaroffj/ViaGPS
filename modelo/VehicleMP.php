<?php
require_once 'Bd.php';

class VehicleMP {
    protected $_dbTable = "Vehicle";
    protected $_id = "vehicleID";
    protected $_bd;

    function __construct() {
        $this->_bd = new Bd();
    }

    function fetchAll($attr=null) {
        if($attr == null) {
            $sAttr = "*";
        } else {
            $sAttr = implode(",", $attr);
        }

        $sql = "SELECT $sAttr FROM $this->_dbTable";
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = mysql_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }

    function find($id, $attr = null) {
        $id = $this->_bd->limpia($id);

        if($attr == null) {
            $sAttr = "*";
        } else {
            $sAttr = implode(",", $attr);
        }

        $sql = "SELECT $sAttr FROM $this->_dbTable WHERE $this->_id = $id";
        $res = $this->_bd->sql($sql);
        return mysql_fetch_object($res);
    }
}
?>