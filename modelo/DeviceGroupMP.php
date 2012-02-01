<?php
require_once 'Bd.php';

class DeviceGroupMP {
    protected $_dbTable = "DeviceGroup";
    protected $_id = "groupID";
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

    function fetchByCuenta($idCuenta, $all = false) {
        $idCuenta = $this->_bd->limpia($idCuenta);
        if($all) {
            $sql = "SELECT * FROM $this->_dbTable WHERE accountID = $idCuenta";
        } else {
            $sql = "SELECT * FROM $this->_dbTable WHERE accountID = $idCuenta AND isActive = 1";
        }
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = mysql_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }

    function save($data) {
        $data["nom"] = $this->_bd->limpia($data["nom"]);
        $data["id"] = $this->_bd->limpia($data["id"]);
        $data["accountID"] = $this->_bd->limpia($data["accountID"]);
        $data["desc"] = $this->_bd->limpia($data["desc"]);
        $data["estado"] = $this->_bd->limpia($data["estado"]);
        $now = time("U");
        $sql = "INSERT INTO ".$this->_dbTable." (accountID, displayName, description, lastUpdateTime, creationTime, isActive)
                VALUES (".$data["accountID"].", '".$data["nom"]."', '".$data["desc"]."', ".$now.", ".$now.", ".$data["estado"].")";
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        return mysql_insert_id();
    }

    function update($data) {
        $data["nom"] = $this->_bd->limpia($data["nom"]);
        $data["id"] = $this->_bd->limpia($data["id"]);
        $data["accountID"] = $this->_bd->limpia($data["accountID"]);
        $data["desc"] = $this->_bd->limpia($data["desc"]);
        $data["estado"] = $this->_bd->limpia($data["estado"]);
        $now = time("U");
        $sql = "UPDATE ".$this->_dbTable."
                SET 
                    displayName = '".$data["nom"]."',
                    description = '".$data["desc"]."',
                    lastUpdateTime = ".$now.",
                    isActive = ".$data["estado"]."
                WHERE groupID = ".$data["id"]."
                AND accountID = ".$data["accountID"];
        return $this->_bd->sql($sql);
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

        $sql = "SELECT $sAttr FROM $this->_dbTable WHERE groupID = $id";
        $res = $this->_bd->sql($sql);
        return mysql_fetch_object($res);
    }

    function fetchDevice($idCuenta, $idGrupo, $in, $deInGroup = null) {
        $idCuenta = $this->_bd->limpia($idCuenta);
        $idGrupo = $this->_bd->limpia($idGrupo);
        if($in) {
            $sql = "SELECT D.deviceID, D.vehicleID, D.licensePlate, D.simPhoneNumber, D.imeiNumber, D.displayName
                    FROM DeviceList AS DL INNER JOIN Device AS D
                    ON DL.groupID = $idGrupo
                    AND DL.deviceID = D.deviceID
                    AND D.isActive = 1";
        } else {
            $nDIn = count($deInGroup);
            for($i=0; $i<$nDIn; $i++) {
                if($i==0)
                    $ids = $deInGroup[$i]->deviceID;
                else
                    $ids .= ",".$deInGroup[$i]->deviceID;
            }
            $sql = "SELECT deviceID, vehicleID, licensePlate, simPhoneNumber, imeiNumber, displayName
                    FROM Device
                    WHERE accountID = $idCuenta
                    AND isActive = 1";
            if($nDIn > 0)
                $sql .= " AND deviceID NOT IN ($ids)";
        }
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = mysql_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }

    function addToGrupo($idGrupo, $idDevice) {
        $idGrupo = $this->_bd->limpia($idGrupo);
        $idDevice = $this->_bd->limpia($idDevice);
        $now = time("U");
        $sql = "INSERT INTO DeviceList (deviceID, groupID, creationTime) VALUES ($idDevice, $idGrupo, $now)";
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        return mysql_insert_id();
    }

    function delFromGrupo($idGrupo, $idDevice = null) {
        $idGrupo = $this->_bd->limpia($idGrupo);
        if($idDevice != null) {
            $idDevice = $this->_bd->limpia($idDevice);
            $sql = "DELETE FROM DeviceList WHERE deviceID = $idDevice AND groupID = $idGrupo";
        } else
            $sql = "DELETE FROM DeviceList WHERE groupID = $idGrupo";
//        echo $sql."<br>";
        return $this->_bd->sql($sql);
    }

    function fetchNumDevice($idGrupo) {
        $idGrupo = $this->_bd->limpia($idGrupo);
        $sql = "SELECT COUNT(DL.deviceID) AS TOTAL FROM DeviceList AS DL INNER JOIN Device AS D WHERE DL.groupID = $idGrupo AND DL.deviceID = D.deviceID AND D.isActive = 1";
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        return mysql_fetch_object($res);
    }

    function delete($idGrupo) {
        $idGrupo = $this->_bd->limpia($idGrupo);
        $sql = "DELETE FROM ".$this->_dbTable." WHERE groupID = $idGrupo";
        $res = $this->_bd->sql($sql);
        return mysql_fetch_object($res);
    }

    function desactiva($idGrupo) {
        $idGrupo = $this->_bd->limpia($idGrupo);
        $sql = "UPDATE ".$this->_dbTable."
                SET isActive = 0
                WHERE groupID = $idGrupo";
        $res = $this->_bd->sql($sql);
        return mysql_fetch_object($res);
    }

    function addUserGrupo($idUser, $idGrupo) {
        $idGrupo = $this->_bd->limpia($idGrupo);
        $idUser = $this->_bd->limpia($idUser);
        $now = time("U");
        $sql = "INSERT INTO GroupList (userID, groupID, creationTime) VALUES ($idUser, $idGrupo, $now)";
        $res = $this->_bd->sql($sql);
        return mysql_insert_id();
    }

    function delUserGrupo($idUsGr) {
        $idUsGr = $this->_bd->limpia($idUsGr);
        $sql = "DELETE FROM GroupList WHERE groupListID = $idUsGr";
        return $this->_bd->sql($sql);
    }

    function fetchUserGrupo($idUser = null, $idGrupo = null) {
        $idGrupo = $this->_bd->limpia($idGrupo);
        $idUser = $this->_bd->limpia($idUser);
        if($idGrupo == null) $idGrupo = "GL.groupID";
        if($idUser == null) $idUser = "GL.userID";

        $sql = "SELECT DG.displayName, DG.groupID, GL.groupListID FROM GroupList AS GL INNER JOIN DeviceGroup AS DG ON GL.userID = $idUser AND GL.groupID = $idGrupo AND GL.groupID = DG.groupID AND DG.isActive = 1";
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = mysql_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }

    function findUserGrupo($idUsGr) {
        $idUsGr = $this->_bd->limpia($idUsGr);
        $sql = "SELECT * FROM GroupList WHERE groupListID = $idUsGr";
        $res = $this->_bd->sql($sql);
        return mysql_fetch_object($res);
    }
}
?>