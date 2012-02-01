<?php
require_once 'Bd.php';

class AlertaLogMP {
    protected $_dbTable = "LOG_ALARMA";
    protected $_id = "ID_LOG_ALARMA";
    protected $_bd;

    function __construct() {
        $this->_bd = new Bd();
    }

    function find($idAlerta) {
        $idAlerta = $this->_bd->limpia($idAlerta);
        $sql = "SELECT * FROM $this->_dbTable WHERE $this->_id = $idAlerta";
        $res = $this->_bd->sql($sql);
        return mysql_fetch_object($res);
    }

    function save($regla, $eventData) {
        $regla->ID_REGLA = $this->_bd->limpia($regla->ID_REGLA);
        $eventData->accountID = $this->_bd->limpia($eventData->accountID);
        $eventData->deviceID = $this->_bd->limpia($eventData->deviceID);
        $eventData->timestamp = $this->_bd->limpia($eventData->timestamp);
        $eventData->statusCode = $this->_bd->limpia($eventData->statusCode);
        
        $sql = "INSERT INTO $this->_dbTable (ID_REGLA, ID_CUENTA, ID_DEVICE, TIMESTAMP, STATUSCODE) VALUES
                ($regla->ID_REGLA, '$eventData->accountID', '$eventData->deviceID', $eventData->timestamp, $eventData->statusCode)";
        $this->_bd->sql($sql);
    }

    function reporte($ini, $fin=null, $device=null) {
        $device = implode(",", $device);
        if($fin != null) {
            $sql = "SELECT AL.*, R.*, ED.*, A.NOM_ALERTA, from_unixtime(ED.timestamp, '%d.%m.%Y %H:%i:%s') as fecha FROM $this->_dbTable AS AL INNER JOIN EventData AS ED INNER JOIN REGLA AS R INNER JOIN ALERTA AS A
                    ON AL.TIMESTAMP = ED.timestamp AND R.ID_REGLA = AL.ID_REGLA
                    AND AL.TIMESTAMP BETWEEN $ini AND $fin AND AL.deviceID IN (".$device.")
                    AND A.ID_ALERTA = R.ID_ALERTA ORDER BY ED.timestamp DESC";
        } else {
            $sql = "SELECT AL.*, R.*, ED.*, A.NOM_ALERTA, from_unixtime(ED.timestamp, '%d.%m.%Y %H:%i:%s') as fecha FROM $this->_dbTable AS AL INNER JOIN EventData AS ED INNER JOIN REGLA AS R INNER JOIN ALERTA AS A
                    ON AL.TIMESTAMP = ED.timestamp AND R.ID_REGLA = AL.ID_REGLA
                    AND AL.TIMESTAMP > $ini AND AL.deviceID IN (".$device.")
                    AND A.ID_ALERTA = R.ID_ALERTA ORDER BY ED.timestamp DESC";
        }
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