<?php
require_once 'Bd.php';

class SensorDeviceMP {
    protected $_dbTable = "SENSOR_DEVICE";
    protected $_id = "ID_SENSOR_ACCOUNT";
    protected $_bd;

    function __construct() {
        $this->_bd = new Bd();
    }

    public function fetchByDevice($idDevice, $idCuenta) {
        $idDevice = $this->_bd->limpia($idDevice);
        $idCuenta = $this->_bd->limpia($idCuenta);
        
        $sql = "SELECT SD.*, S.NOM_SENSOR, S.TIPO_SENSOR, S.TIPO_PROCESO_SENSOR, S.UNIDAD_SENSOR 
                FROM $this->_dbTable AS SD 
                    INNER JOIN SENSOR_ACCOUNT AS SA
                    INNER JOIN SENSOR AS S 
                ON 
                    SD.DEVICEID = $idDevice 
                    AND SD.ID_SENSOR = S.ID_SENSOR 
                    AND SA.ID_SENSOR = S.ID_SENSOR
                    AND SA.ESTADO_SENSOR_ACCOUNT = 1";
        
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = mysql_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }
    
    function fetchByDevices($dev) {
        $dev = implode(",",$dev);
        
        $sql = "SELECT SD.DEVICEID, SD.ID_SENSOR, SD.COLUMNA_SENSOR, S.NOM_SENSOR, S.TIPO_SENSOR, S.UNIDAD_SENSOR, S.TIPO_PROCESO_SENSOR, S.IN_TABLA, S.IN_DETALLE 
                FROM $this->_dbTable AS SD 
                    INNER JOIN SENSOR_ACCOUNT AS SA
                    INNER JOIN SENSOR AS S 
                ON 
                    SD.DEVICEID IN ( $dev )
                    AND SD.ID_SENSOR = S.ID_SENSOR 
                    AND SA.ID_SENSOR = S.ID_SENSOR
                    AND SA.ESTADO_SENSOR_ACCOUNT = 1";
        
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = mysql_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }
    
    function fetchByAccount($idCuenta) {
        $idCuenta = $this->_bd->limpia($idCuenta);
        
        $sql = "SELECT SA.*, S.NOM_SENSOR, S.TIPO_SENSOR, S.ABR_SENSOR, S.IN_TABLA, S.IN_DETALLE  
                FROM SENSOR_ACCOUNT AS SA
                    INNER JOIN SENSOR AS S 
                ON 
                    SA.ACCOUNTID = $idCuenta 
                    AND SA.ID_SENSOR = S.ID_SENSOR
                    AND SA.ESTADO_SENSOR_ACCOUNT = 1";
        
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = mysql_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }
    
    function findSensor($id, $attr=null) {
        $id = $this->_bd->limpia($id);

        if($attr == null) {
            $sAttr = "*";
        } else {
            $sAttr = implode(",", $attr);
        }

        $sql = "SELECT $sAttr FROM SENSOR AS S INNER JOIN SENSOR_DEVICE AS SD ON S.ID_SENSOR = ".$id." AND SD.ID_SENSOR = S.ID_SENSOR";
        $res = $this->_bd->sql($sql);
        if($res) {
            $row = mysql_fetch_object($res);
            return $row;
        } else return null;
    }
    
    function fetchOpSensor($idSe, $idOp=null) {
        $idSe = $this->_bd->limpia($idSe);
        $idOp = $this->_bd->limpia($idOp);
        
        if($idOp == null) {
            $sql = "SELECT * FROM SENSOR_OPCION WHERE ID_SENSOR = ".$idSe;
        } else {
            $sql = "SELECT * FROM SENSOR_OPCION WHERE ID_SENSOR_OPCION = ".$idOp;
        }
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        $arr = array();
        $i = 0;
        while($row = mysql_fetch_object($res)) {
            $arr[] = $row;
            $i++;
        }
        if($i == 1) return $arr[0];
        return $arr;
    }
}
?>