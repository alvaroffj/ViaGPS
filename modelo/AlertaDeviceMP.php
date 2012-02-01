<?php
require_once 'Bd.php';

class AlertaDeviceMP {
    protected $_dbTable = "ALERTA_DEVICE";
    protected $_id = "ID_ALERTA_DEVICE";
    protected $_bd;

    function __construct() {
        $this->_bd = new Bd();
    }

    public function fetchByDevice($idDevice, $idCuenta) {
        $idDevice = $this->_bd->limpia($idDevice);
        $idCuenta = $this->_bd->limpia($idCuenta);
        $sql = "SELECT * FROM $this->_dbTable WHERE ID_DEVICE = '$idDevice' AND ID_CUENTA = '$idCuenta'";
        $res = $this->_bd->sql($sql);
        if(mysql_num_rows($res)>1) {
            $arr = array();
            while($row = mysql_fetch_object($res)) {
                $arr[] = $row;
            }
            return $arr;
        } else return mysql_fetch_object($res);
    }

    public function fetchByAlerta($idAlarma) {
        $idAlarma = $this->_bd->limpia($idAlarma);
        $sql = "SELECT AD.*, D.licensePlate, D.displayName FROM $this->_dbTable AS AD INNER JOIN Device AS D ON AD.ID_ALERTA = $idAlarma AND AD.deviceID = D.deviceID AND AD.ESTADO_ALERTA_DEVICE = 1";
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = mysql_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }

    function save($idDev, $idAl) {
        $idDev = $this->_bd->limpia($idDev);
        $idAl = $this->_bd->limpia($idAl);
//        echo "<pre>";
//        print_r($this->_bd);
//        echo "</pre>";
        $sql = "SELECT ID_ALERTA_DEVICE FROM ALERTA_DEVICE WHERE ID_ALERTA = $idAl AND deviceID = $idDev";
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        $n = mysql_num_rows($res);
//        echo "n: ".$n."<br>";
        $row = mysql_fetch_object($res);
//        echo "<pre>";
//        print_r($row);
//        echo "</pre>";
        if($n == 0) {
            $sql = "INSERT INTO ALERTA_DEVICE (ID_ALERTA, deviceID) VALUES ($idAl, $idDev)";
//            echo $sql."<br>";
            return $this->_bd->sql($sql);
        } else return true;
    }

    function desactiva($id) {
//        echo "desactiva<br>";
        $now = Time("U");
        $id = $this->_bd->limpia($id);
        $sql = "UPDATE $this->_dbTable SET ESTADO_ALERTA_DEVICE = 0, LastUpdateTime = '$now' WHERE $this->_id = $id";
//        echo $sql."<br>";
        return $this->_bd->sql($sql);
    }
    
    function delete($id) {
        $id = $this->_bd->limpia($id);
        $sql = "DELETE FROM $this->_dbTable WHERE $this->_id = $id";
//        echo $sql."<br>";
        return $this->_bd->sql($sql);
    }
}
?>