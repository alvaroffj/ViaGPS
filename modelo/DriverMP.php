<?php
require_once 'Bd.php';

class DriverMP {
    protected $_dbTable = "Driver";
    protected $_id = "driverID";
    protected $_bd;

    function __construct() {
        $this->_bd = new Bd();
    }

    function fetchAll() {
        $sql = "SELECT * FROM $this->_dbTable AND driverID > 0";
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = mysql_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }

    function fetchByCuenta($idCuenta, $attr=null, $todo = true) {
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

        if($todos) $q = "";
        else $q = "AND isActive = 1";
        
        $sql = "SELECT $sAttr FROM $this->_dbTable WHERE accountID = $idCuenta AND driverID > 0 $q";
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

        $sql = "SELECT $sAttr FROM $this->_dbTable WHERE $this->_id = $id";
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        return mysql_fetch_object($res);
    }

    function insert($data) {
        $data["nom"] = $this->_bd->limpia($data["nom"]);
        $data["rut"] = $this->_bd->limpia($data["rut"]);
        $data["tel"] = $this->_bd->limpia($data["tel"]);
        $data["estado"] = $this->_bd->limpia($data["estado"]);
        $data["accountID"] = $this->_bd->limpia($data["accountID"]);
        $data["vehiculo"] = $this->_bd->limpia($data["vehiculo"]);
        $ts = date("U");
        
        $sql = "INSERT INTO $this->_dbTable
                    (accountID, contactPhone, rut, displayName, lastUpdateTime, creationTime, isActive) VALUES
                    (".$data["accountID"].", '".$data["tel"]."', '".$data["rut"]."', '".$data["nom"]."', $ts, $ts, ".$data["estado"].")";
        $res = $this->_bd->sql($sql);
        $idDri =  mysql_insert_id();
        
        if($data["vehiculo"]>0) {
            $sql = "UPDATE Device SET driverID = $idDri, lastUpdateTime = $ts WHERE deviceID = ".$data["vehiculo"]." AND accountID = ".$data["accountID"];
            $res = $this->_bd->sql($sql);
            $sql = "INSERT INTO LOG_DEVICE_DRIVER (deviceID, driverID, TIMESTAMP) VALUES (".$data["vehiculo"].", $idDri, $ts)";
            $res = $this->_bd->sql($sql);
        }
    }

    function update($data) {
        $data["nom"] = $this->_bd->limpia($data["nom"]);
        $data["id"] = $this->_bd->limpia($data["id"]);
        $data["rut"] = $this->_bd->limpia($data["rut"]);
        $data["tel"] = $this->_bd->limpia($data["tel"]);
        $data["estado"] = $this->_bd->limpia($data["estado"]);
        $data["accountID"] = $this->_bd->limpia($data["accountID"]);
        $ts = date("U");
        
        $sql = "UPDATE $this->_dbTable SET
                    contactPhone = '".$data["tel"]."',
                    rut = '".$data["rut"]."',
                    displayName = '".$data["nom"]."',
                    isActive = ".$data["estado"].",
                    lastUpdateTime = ".$ts."
                WHERE driverID = ".$data["id"];
        $re = $this->_bd->sql($sql);

        $sql = "SELECT driverID FROM Device WHERE deviceID = ".$data["vehiculo"];
        $res = $this->_bd->sql($sql);
        $row = mysql_fetch_object($res);
        
        $sql = "SELECT deviceID FROM Device WHERE driverID = ".$data["id"];
        $res = $this->_bd->sql($sql);
        $row2 = mysql_fetch_object($res);
        
        if($row2!=null && $row2->deviceID != $data["vehiculo"]) {
            $sql = "UPDATE Device SET driverID = 0, lastUpdateTime = $ts WHERE deviceID = ".$row2->deviceID." AND accountID = ".$data["accountID"];
            $res = $this->_bd->sql($sql);
            $sql = "INSERT INTO LOG_DEVICE_DRIVER (deviceID, driverID, TIMESTAMP) VALUES (".$row2->deviceID.", 0, $ts)";
            $res = $this->_bd->sql($sql);
        }
        
        if($row!=null && ($row->driverID == "0" || $row->driverID != $data["id"])) {
            $sql = "UPDATE Device SET driverID = ".$data["id"].", lastUpdateTime = $ts WHERE deviceID = ".$data["vehiculo"]." AND accountID = ".$data["accountID"];
            $res = $this->_bd->sql($sql);
            $sql = "INSERT INTO LOG_DEVICE_DRIVER (deviceID, driverID, TIMESTAMP) VALUES (".$data["vehiculo"].", ".$data["id"].", $ts)";
            $res = $this->_bd->sql($sql);
        }
        
        return $re;
    }

    function desactiva($idUs) {
        $idUs = $this->_bd->limpia($idUs);
        $sql = "UPDATE $this->_dbTable SET isActive = 0 WHERE userID = $idUs";
        return $this->_bd->sql($sql);
    }

    function validaCuenta($account, $user, $pass) {
        $user = $this->_bd->limpia($user);
        $account = $this->_bd->limpia($account);
        $pass = md5($this->_bd->limpia($pass));

        $sql = "SELECT A.accountID, A.accountName, U.contactName, U.userName, U.userID, U.roleID FROM User AS U INNER JOIN Account AS A ON A.accountName = '".$account."' AND A.accountID = U.accountID AND userName = '".$user."' AND U.password = '".$pass."' AND U.isActive = 1";
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        return mysql_fetch_object($res);
    }
}
?>