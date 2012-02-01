<?php
require_once 'Bd.php';

class DireccionMP {
    protected $_dbTable = "DIRECCION";
    protected $_id = "ID_DIRECCION";
    protected $_bd;

    function __construct() {
        $this->_bd = new Bd("maestra", "6EQyhzvnqR5LKwnX", "10.182.176.250", "maestra");
    }

    function find($lat, $lon, $attr = null) {
        $lat = $this->_bd->limpia($lat);
        $lon = $this->_bd->limpia($lon);

        if($attr == null) {
            $sAttr = "*";
        } else {
            $sAttr = implode(",", $attr);
        }

        $sql = "SELECT $sAttr FROM $this->_dbTable WHERE LATITUD = '$lat' AND LONGITUD = '$lon'";
        $res = $this->_bd->sql($sql);
        return mysql_fetch_object($res);
    }

    function insert($datos) {
        $sql = "INSERT INTO $this->_dbTable (LATITUD, LONGITUD, DIRECCION, COMUNA, CIUDAD, REGION, PAIS) VALUES
                ($datos->LATITUD, $datos->LONGITUD, '$datos->DIRECCION', '$datos->COMUNA', '$datos->CIUDAD', '$datos->REGION', '$datos->PAIS')";
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
    }
}
?>