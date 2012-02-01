<?php
require_once 'Bd.php';

class DeviceMP {
    protected $_dbTable = "Device";
    protected $_id = "deviceID";
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

    function fetchByCuenta($idCuenta, $idAlerta=null, $attr=null, $lastPos = false) {
        $idCuenta = $this->_bd->limpia($idCuenta);
        $idAlerta = $this->_bd->limpia($idAlerta);

        if($attr == null) {
            $sAttr = "*";
        } else {
            for($i=0; $i<count($attr); $i++) {
                if($i==0) {
                    $sAttr = $attr[$i];
                } else {
                    $sAttr .= ", ".$attr[$i];
                }
            }
        }
        
        if($idAlerta != null)
            $sql = "SELECT $sAttr FROM $this->_dbTable WHERE accountID = $idCuenta AND deviceID NOT IN (SELECT deviceID AS deviceID FROM ALERTA_DEVICE WHERE ID_ALERTA = $idAlerta)";
        else
            if($lastPos)
                $sql = "SELECT D.deviceID, D.vehicleID, D.licensePlate, D.simPhoneNumber, D.imeiNumber, D.displayName, D.driverID, L.latitude, L.longitude, from_unixtime(L.timestamp, '%d.%m.%Y %H:%i:%s') as fecha
                        FROM $this->_dbTable AS D INNER JOIN LASTEVENTDATA AS L
                        ON D.accountID = $idCuenta AND D.deviceID = L.deviceID AND D.isActive = 1";
            else
                $sql = "SELECT $sAttr FROM $this->_dbTable WHERE accountID = $idCuenta AND isActive = 1";
//        echo $sql;
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
            for($i=0; $i<count($attr); $i++) {
                if($i==0) {
                    $sAttr = $attr[$i];
                } else {
                    $sAttr .= ", ".$attr[$i];
                }
            }
        }

        $sql = "SELECT $sAttr FROM $this->_dbTable WHERE deviceID = $id";
        $res = $this->_bd->sql($sql);
        return mysql_fetch_object($res);
    }
    
    function findByDriver($id, $attr = null) {
        $id = $this->_bd->limpia($id);

        if($attr == null) {
            $sAttr = "*";
        } else {
            for($i=0; $i<count($attr); $i++) {
                if($i==0) {
                    $sAttr = $attr[$i];
                } else {
                    $sAttr .= ", ".$attr[$i];
                }
            }
        }

        $sql = "SELECT $sAttr FROM $this->_dbTable WHERE driverID = $id";
        $res = $this->_bd->sql($sql);
        return mysql_fetch_object($res);
    }

    function fetchByGrupo($idGr) {
        $idGr = $this->_bd->limpia($idGr);
        $sql = "SELECT D.deviceID, D.licensePlate, D.vehicleID, D.displayName, D.kmPorLitro FROM DeviceList AS DL INNER JOIN $this->_dbTable AS D ON DL.groupID = $idGr AND DL.deviceID = D.deviceID AND D.isActive = 1";
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = mysql_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }

    function fetchByUser($idUs) {
        $idUs = $this->_bd->limpia($idUs);

        $sql = "SELECT D.deviceID, D.licensePlate, D.displayName
                FROM GroupList AS GL
                    INNER JOIN DeviceList AS DL
                    INNER JOIN $this->_dbTable AS D
                ON GL.userID = $idUs
                    AND GL.groupID = DL.groupID 
                    AND DL.deviceID = D.deviceID
                    AND D.isActive = 1";

        $res = $this->_bd->sql($sql);

        $arr = array();
        while($row = mysql_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }

    function updateName($id, $name) {
        $id = $this->_bd->limpia($id);
        $name = $this->_bd->limpia($name);

        $sql = "UPDATE $this->_dbTable SET displayName = '".$name."' WHERE ".$this->_id." = ".$id;

        $res = $this->_bd->sql($sql);
    }
    
    function setDriver($idDev, $idDri) {
        $idDev = $this->_bd->limpia($idDev);
        $idDri = $this->_bd->limpia($idDri);
        $ts = date("U");
        
        $row2 = null;
        if($idDri != "0") {
            $sql = "SELECT deviceID FROM $this->_dbTable WHERE driverID = ".$idDri;
            $res = $this->_bd->sql($sql);
            $row2 = mysql_fetch_object($res);
        }
        
        if($row2!=null && $row2->deviceID != $idDev) {
            $sql = "UPDATE $this->_dbTable SET driverID = 0, lastUpdateTime = $ts WHERE deviceID = ".$row2->deviceID;
            $res = $this->_bd->sql($sql);
            $sql = "INSERT INTO LOG_DEVICE_DRIVER (deviceID, driverID, TIMESTAMP) VALUES (".$row2->deviceID.", 0, $ts)";
            $res = $this->_bd->sql($sql);
        }
        
        $sql = "UPDATE $this->_dbTable SET driverID = $idDri WHERE deviceID = $idDev";
        $res = $this->_bd->sql($sql);
        
        $sql = "INSERT INTO LOG_DEVICE_DRIVER (deviceID, driverID, TIMESTAMP) VALUES (".$idDev.", ".$idDri.", $ts)";
        $res = $this->_bd->sql($sql);
    }
}
?>