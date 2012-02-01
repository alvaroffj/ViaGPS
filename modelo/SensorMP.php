<?php
require_once 'Bd.php';

class SensorMP {
    protected $_dbTable = "SENSOR_ACCOUNT";
    protected $_id = "ID_SENSOR_ACCOUNT";
    protected $_bd;

    function __construct() {
        $this->_bd = new Bd();
    }

    function find($idEmp, $idSen, $idSenAc) {
        $idEmp = $this->_bd->limpia($idEmp);
        $idSen = $this->_bd->limpia($idSen);
        $idSenAc = $this->_bd->limpia($idSenAc);
        $sql = "SELECT * FROM $this->_dbTable WHERE $this->_id = $idSenAc AND ID_SENSOR = $idSen AND ACCOUNTID = $idEmp AND ESTADO_SENSOR_ACCOUNT = 1";
        $res = $this->_bd->sql($sql);
        return mysql_fetch_object($res);
    }

    public function fetchByCuenta($idCuenta) {
        $idCuenta = $this->_bd->limpia($idCuenta);
        $sql = "SELECT * FROM $this->_dbTable WHERE ID_CUENTA = '$idCuenta' AND ESTADO_ALERTA = 1";
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = mysql_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }

    public function save($data) {
        $data["idCuenta"] = $this->_bd->limpia($data["idCuenta"]);
        $data["idUsuario"] = $this->_bd->limpia($data["idUsuario"]);
        $data["nom"] = $this->_bd->limpia($data["nom"]);
        $sql = "INSERT INTO $this->_dbTable (ID_CUENTA, ID_USUARIO, NOM_ALERTA, ESTADO_ALERTA) VALUES ('".$data["idCuenta"]."', '".$data["idUsuario"]."', '".$data["nom"]."', 1)";
        $res = $this->_bd->sql($sql);
    }

    function desactiva($id) {
        $id = $this->_bd->limpia($id);
        $sql = "UPDATE $this->_dbTable SET ESTADO_ALERTA = 0 WHERE $this->_id = $id";
        $res = $this->_bd->sql($sql);
    }
    
    function fetchOpciones($id) {
        $id = $this->_bd->limpia($id);
        
        $sql = "SELECT * FROM SENSOR_OPCION WHERE ID_SENSOR = ".$id;
        
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = mysql_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }
    
    function fetchOpcion($idS, $idO) {
        $idS = $this->_bd->limpia($idS);
        $idO = $this->_bd->limpia($idO);
        
        $sql = "SELECT * FROM SENSOR_OPCION WHERE ID_SENSOR = ".$idS." AND ID_SENSOR_OPCION = ".$idO;
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        return mysql_fetch_object($res);
    }
}
?>