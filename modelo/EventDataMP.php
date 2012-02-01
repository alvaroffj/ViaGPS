<?php
require_once 'Bd.php';

class EventDataMP {
    protected $_dbTable = "EventData";
    protected $_bd;

    function __construct() {
        $this->_bd = new Bd();
    }

    function findLast($idDevice) {
        $idDevice = $this->_bd->limpia($idDevice);
        $sql = "SELECT * FROM LASTEVENTDATA WHERE deviceID = '$idDevice'";
        $res = $this->_bd->sql($sql);
        return mysql_fetch_object($res);
    }

    function save($eventData) {
        $eventData->accountID = $this->_bd->limpia($eventData->accountID);
        $eventData->deviceID = $this->_bd->limpia($eventData->deviceID);
        $eventData->statusCode = $this->_bd->limpia($eventData->statusCode);
        $eventData->latitude = $this->_bd->limpia($eventData->latitude);
        $eventData->longitude = $this->_bd->limpia($eventData->longitude);
        $eventData->gpsAge = $this->_bd->limpia($eventData->gpsAge);
        $eventData->speedKPH = $this->_bd->limpia($eventData->speedKPH);
        $eventData->heading = $this->_bd->limpia($eventData->heading);
        $eventData->altitude = $this->_bd->limpia($eventData->altitude);
        $eventData->distanceKM = $this->_bd->limpia($eventData->distanceKM);
        $eventData->odometerKM = $this->_bd->limpia($eventData->odometerKM);
        $eventData->timestamp = $this->_bd->limpia($eventData->timestamp);
        $sql = "INSERT INTO EVENTDATA (accountID, deviceID, timestamp, statusCode, latitude, longitude, gpsAge, speedKPH, heading, altitude, distanceKM, odometerKM) VALUES
                ('$eventData->accountID', '$eventData->deviceID', $eventData->timestamp, $eventData->statusCode, $eventData->latitude, $eventData->longitude, $eventData->gpsAge, $eventData->speedKPH, $eventData->heading, $eventData->altitude, $eventData->distanceKM, $eventData->odometerKM)";
        $this->_bd->sql($sql);
    }

    function updateLast($eventData) {
        $eventData->accountID = $this->_bd->limpia($eventData->accountID);
        $eventData->deviceID = $this->_bd->limpia($eventData->deviceID);
        $eventData->statusCode = $this->_bd->limpia($eventData->statusCode);
        $eventData->latitude = $this->_bd->limpia($eventData->latitude);
        $eventData->longitude = $this->_bd->limpia($eventData->longitude);
        $eventData->gpsAge = $this->_bd->limpia($eventData->gpsAge);
        $eventData->speedKPH = $this->_bd->limpia($eventData->speedKPH);
        $eventData->heading = $this->_bd->limpia($eventData->heading);
        $eventData->altitude = $this->_bd->limpia($eventData->altitude);
        $eventData->distanceKM = $this->_bd->limpia($eventData->distanceKM);
        $eventData->odometerKM = $this->_bd->limpia($eventData->odometerKM);
        $eventData->timestamp = $this->_bd->limpia($eventData->timestamp);
        $sql = "UPDATE LASTEVENTDATA SET
                    timestamp = $eventData->timestamp,
                    statusCode = $eventData->statusCode,
                    latitude = $eventData->latitude,
                    longitude = $eventData->longitude,
                    gpsAge = $eventData->gpsAge,
                    speedKPH = $eventData->speedKPH,
                    heading = $eventData->heading,
                    altitude = $eventData->altitude,
                    distanceKM = $eventData->distanceKM,
                    odometerKM = $eventData->odometerKM
                WHERE
                    accountID = '$eventData->accountID'
                    AND deviceID = '$eventData->deviceID'";
        $this->_bd->sql($sql);
    }

    function fetchLastByUser($id) {
        $sql = "SELECT LE.*, D.licensePlate, D.displayName, D.vehicleID, from_unixtime(LE.timestamp, '%d-%m-%Y %h:%i:%s') as fecha
                FROM GroupList AS GL INNER JOIN DeviceList AS DL INNER JOIN Device AS D INNER JOIN LASTEVENTDATA AS LE
                ON
                GL.userID = $id
                AND GL.groupID = DL.groupID
                AND DL.deviceID = D.deviceID
                AND LE.deviceID = D.deviceID
                AND D.isActive = 1
                GROUP BY DL.DeviceID";
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = mysql_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }

    function fetchLastByAccount($id) {
        $sql = "SELECT LE.*, D.licensePlate, D.driverID, D.displayName, D.vehicleID, from_unixtime(LE.timestamp, '%d.%m.%Y %H:%i:%s') as fecha, DR.displayName AS driverName, DR.contactPhone
                FROM DeviceGroup AS DG 
                    INNER JOIN DeviceList AS DL 
                    INNER JOIN Device AS D 
                    INNER JOIN LASTEVENTDATA AS LE
                    INNER JOIN Driver AS DR
                ON
                DG.accountID = $id
                AND DG.groupID = DL.groupID
                AND DL.deviceID = D.deviceID
                AND LE.deviceID = D.deviceID
                AND D.isActive = 1
                AND D.driverID = DR.driverID
                GROUP BY DL.DeviceID";
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = mysql_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }

    function auditoriaByDevice($ini, $fin, $device, $pag=0) {
        $device = implode(",", $device);
        $sql = "SELECT *, from_unixtime(timestamp, '%d.%m.%Y %H:%i:%s') as fecha FROM $this->_dbTable WHERE timestamp BETWEEN ".$ini." AND ".$fin." AND deviceID IN (".$device.") ORDER BY timestamp ASC";
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = mysql_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }

    function distanciaByDevice($ini, $fin, $device, $pag=0) {
        $device = implode(",", $device);
        $fini = date("Y-m-d", $ini);
        $ffin = date("Y-m-d", $fin);
        $sql = "SELECT deviceID, SUM(odometerKM) AS distancia, from_unixtime(timestamp, '%d-%m-%Y') as fecha, timestamp, DATEDIFF('$ffin', '$fini') AS DIAS, DATEDIFF(from_unixtime(timestamp, '%Y-%m-%d'), '$fini') AS INDICE 
                FROM $this->_dbTable
                WHERE 
                    timestamp BETWEEN ".$ini." AND ".$fin."
                    AND deviceID IN (".$device.") 
                GROUP BY deviceID, fecha 
                ORDER BY deviceID,timestamp ASC";
        
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = mysql_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }

    function velocidadByDevice($ini, $fin, $device, $mayor, $valor) {
        $device = implode(",", $device);
        if($mayor*1 == 0) {
            $vel = " AND speedKPH > $valor";
        } else $vel = " AND speedKPH < $valor";
        
        $sql = "SELECT *, from_unixtime(timestamp, '%d.%m.%Y %H:%i:%s') as fecha FROM $this->_dbTable WHERE timestamp BETWEEN ".$ini." AND ".$fin." AND deviceID IN (".$device.") $vel";
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = mysql_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }
}
?>