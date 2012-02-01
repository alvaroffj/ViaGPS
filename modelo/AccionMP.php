<?php
require_once 'Bd.php';

class AccionMP {
    protected $_dbTable = "ACCION";
    protected $_id = "ID_ACCION";
    protected $_bd;

    function __construct() {
        $this->_bd = new Bd();
    }

    function find($idAccion) {
        $idAccion = $this->_bd->limpia($idAccion);
        $sql = "SELECT * FROM $this->_dbTable WHERE $this->_id = $idAccion";
        $res = $this->_bd->sql($sql);
        return mysql_fetch_object($res);
    }

    function fetchByAlerta($idAlerta) {
        $idAlerta = $this->_bd->limpia($idAlerta);
        $sql = "SELECT * FROM $this->_dbTable WHERE ID_ALERTA = $idAlerta";
        $res = $this->_bd->sql($sql);
        if(mysql_num_rows($res)>1) {
            $arr = array();
            while($row = mysql_fetch_object($res)) {
                $arr[] = $row;
            }
            return $arr;
        } else return mysql_fetch_object($res);
    }

    function fetchByAlertaFull($idAlerta) {
        $idAlerta = $this->_bd->limpia($idAlerta);
        $sql = "SELECT * FROM $this->_dbTable AS A INNER JOIN TIPO_ACCION AS TA ON A.ID_ALERTA = $idAlerta AND TA.ID_TIPO_ACCION = A.ID_TIPO_ACCION AND A.ESTADO_ACCION = 1";
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = mysql_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }

    function save($data) {
        $data["idAlerta"] = $this->_bd->limpia($data["idAlerta"]);
        $data["Accion"] = $this->_bd->limpia($data["Accion"]);
        $data["dest"] = $this->_bd->limpia($data["dest"]);
        $now = Time("U");
        $sql = "INSERT INTO $this->_dbTable (ID_ALERTA, ID_TIPO_ACCION, DEST_ACCION, LastUpdateTime) VALUES
                (".$data["idAlerta"].", ".$data["Accion"].", '".$data["dest"]."', '$now')";
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
    }

    function desactiva($id) {
        $id = $this->_bd->limpia($id);
        $now = Time("U");
        $sql = "UPDATE $this->_dbTable SET ESTADO_ACCION = 0, LastUpdateTime = '$now' WHERE ID_ACCION = $id";
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
    }
}
?>