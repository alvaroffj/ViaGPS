<?php
require_once 'Bd.php';

class PInteresMP {
    protected $_dbTable = "pinteres";
    protected $_id = "id";
    protected $_bd;

    function __construct() {
        $this->_bd = new Bd();
    }

    function fetchAll() {
        $sql = "SELECT * FROM $this->_dbTable";
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = mysql_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }

    function fetchByCuenta($idCuenta, $attr=null, $active=false) {
        $idCuenta = $this->_bd->limpia($idCuenta);

        if($attr == null) {
            $sAttr = "*";
        } else {
            $sAttr = implode(",", $attr);
        }

        if($active) $q = "AND estado_pinteres = 1";

        $sql = "SELECT $sAttr FROM $this->_dbTable WHERE accountid = $idCuenta $q ORDER BY name ASC";
//        echo $sql."<br>";
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

    function insert($data) {
        $data["lat"] = $this->_bd->limpia($data["lat"]);
        $data["lon"] = $this->_bd->limpia($data["lon"]);
        $data["nom"] = $this->_bd->limpia($data["nom"]);
        $data["dir"] = $this->_bd->limpia($data["dir"]);
        $data["com"] = $this->_bd->limpia($data["com"]);
        $data["reg"] = $this->_bd->limpia($data["reg"]);
        $data["pais"] = $this->_bd->limpia($data["pais"]);
        $data["desc"] = $this->_bd->limpia($data["desc"]);
        $data["rad"] = $this->_bd->limpia($data["rad"]);
        $data["estado"] = $this->_bd->limpia($data["estado"]);
        $data["accountID"] = $this->_bd->limpia($data["accountID"]);

        $sql = "INSERT INTO $this->_dbTable
                    (latitude, longitude, name, accountid, address, comuna, region, pais, descripcion, radio) VALUES
                    (".$data["lat"].", ".$data["lon"].", '".$data["nom"]."', ".$data["accountID"].", '".$data["dir"]."', '".$data["com"]."', '".$data["reg"]."', '".$data["pais"]."', '".$data["desc"]."', ".$data["rad"].")";
        $res = $this->_bd->sql($sql);
        return mysql_insert_id();
    }

    function update($data) {
        $data["lat"] = $this->_bd->limpia($data["lat"]);
        $data["lon"] = $this->_bd->limpia($data["lon"]);
        $data["nom"] = $this->_bd->limpia($data["nom"]);
        $data["dir"] = $this->_bd->limpia($data["dir"]);
        $data["com"] = $this->_bd->limpia($data["com"]);
        $data["reg"] = $this->_bd->limpia($data["reg"]);
        $data["pais"] = $this->_bd->limpia($data["pais"]);
        $data["desc"] = $this->_bd->limpia($data["desc"]);
        $data["rad"] = $this->_bd->limpia($data["rad"]);
        $data["estado"] = $this->_bd->limpia($data["estado"]);
        $data["accountID"] = $this->_bd->limpia($data["accountID"]);

        $sql = "UPDATE $this->_dbTable SET
                    latitude = ".$data["lat"].",
                    longitude = ".$data["lon"].",
                    name = '".$data["nom"]."',
                    address = '".$data["dir"]."',
                    radio = ".$data["rad"].",
                    estado_pinteres = ".$data["estado"]."
                WHERE $this->_id = ".$data["id"];

//        echo $sql."<br>";
        return $this->_bd->sql($sql);
    }

    function desactiva($id) {
        $id = $this->_bd->limpia($id);
        $sql = "UPDATE $this->_dbTable SET estado_pinteres = 0 WHERE $this->_id = $id";
        return $this->_bd->sql($sql);
    }
}
?>