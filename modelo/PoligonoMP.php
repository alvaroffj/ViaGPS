<?php
require_once 'Bd.php';

class POLIGONOMP {
//    protected $_dbTable = "ACCION";
//    protected $_id = "ID_ACCION";
    protected $_bd;

    function __construct() {
        $this->_bd = new Bd();
    }

    function fetchByRegla($Regla) {
        if($Regla->ID_PARAMETRO == 3) { //geozona
            $tipoPol = 2;
        } else $tipoPol = 1; //geofrontera
        
        $sql = "SELECT * FROM POLIGONO WHERE ID_POLIGONO = $Regla->ID_POLIGONO AND ID_TIPO_POLIGONO = $tipoPol";
        $res = $this->_bd->sql($sql);
        return mysql_fetch_object($res);
    }

    function fetchPuntos($idPol) {
        $idPol = $this->_bd->limpia($idPol);
        $sql = "SELECT * FROM PUNTO WHERE ID_POLIGONO = $idPol ORDER BY NUM_PUNTO ASC";
        $res = $this->_bd->sql($sql);
        if(mysql_num_rows($res)>1) {
            $arr = array();
            while($row = mysql_fetch_object($res)) {
                $arr[] = $row;
            }
            return $arr;
        } else return mysql_fetch_object($res);
    }

    function savePoligono($data) {
        $data["nom"] = $this->_bd->decode($this->_bd->limpia($data["nom"]));
        $data["estado"] = $this->_bd->limpia($data["estado"]);
        $data["tipo"] = $this->_bd->limpia($data["tipo"]);
        $data["accountID"] = $this->_bd->limpia($data["accountID"]);
        $data["nPuntos"] = $this->_bd->limpia($data["nPuntos"]);
        $sql = "INSERT INTO POLIGONO (ID_TIPO_POLIGONO, accountID, NOM_POLIGONO, ESTADO_POLIGONO) VALUES (".$data["tipo"].", '".$data["accountID"]."', '".$data["nom"]."', ".$data["estado"].")";
        $res = $this->_bd->sql($sql);
        $idPol = mysql_insert_id();

        $sql = "INSERT INTO PUNTO (ID_POLIGONO, LAT_PUNTO, LON_PUNTO, NUM_PUNTO) VALUES ";
        for($i=0; $i<$data["nPuntos"]; $i++) {
            $coord = split(",",$data["p_$i"]);
            if($i==0) {
                $sql .= "($idPol, ".$coord[0].", ".$coord[1].", ".($i+1).")";
            } else {
                $sql .= ", ($idPol, ".$coord[0].", ".$coord[1].", ".($i+1).")";
            }
        }
        
        $res = $this->_bd->sql($sql);
        return $idPol;
    }

    function updatePoligono($data) {
        $data["nom"] = $this->_bd->limpia($data["nom"]);
        $data["estado"] = $this->_bd->limpia($data["estado"]);
        $data["tipo"] = $this->_bd->limpia($data["tipo"]);
        $data["accountID"] = $this->_bd->limpia($data["accountID"]);
        $data["idPol"] = $this->_bd->limpia($data["idPol"]);
        $data["nPuntos"] = $this->_bd->limpia($data["nPuntos"]);

        $sql = "UPDATE POLIGONO SET NOM_POLIGONO = '".$data["nom"]."', ESTADO_POLIGONO = ".$data["estado"]." WHERE ID_POLIGONO = ".$data["idPol"];
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
//        $idPol = mysql_insert_id();

        $sql = "DELETE FROM PUNTO WHERE ID_POLIGONO = ".$data["idPol"];
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        
        $sql = "INSERT INTO PUNTO (ID_POLIGONO, LAT_PUNTO, LON_PUNTO, NUM_PUNTO) VALUES ";
        for($i=0; $i<$data["nPuntos"]; $i++) {
            $coord = split(",",$data["p_$i"]);
            if($i==0) {
                $sql .= "(".$data["idPol"].", ".$coord[0].", ".$coord[1].", ".($i+1).")";
            } else {
                $sql .= ", (".$data["idPol"].", ".$coord[0].", ".$coord[1].", ".($i+1).")";
            }
        }
//        echo $sql."<br>";
        return $this->_bd->sql($sql);
    }

    function fetchByCuenta($idCuenta) {
        $idCuenta = $this->_bd->limpia($idCuenta);
        $sql = "SELECT * FROM POLIGONO WHERE accountID = $idCuenta";
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = mysql_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }

    function fetchByCuentaTipo($idCuenta, $tipo, $activo=false) {
        $idCuenta = $this->_bd->limpia($idCuenta);
        $tipo = $this->_bd->limpia($tipo);
        if($activo) {
            $sql = "SELECT * FROM POLIGONO WHERE accountID = $idCuenta AND ID_TIPO_POLIGONO = $tipo AND ESTADO_POLIGONO = 1";
        } else {
            $sql = "SELECT * FROM POLIGONO WHERE accountID = $idCuenta AND ID_TIPO_POLIGONO = $tipo";
        }
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = mysql_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }

    function find($id, $attr=null) {
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

        $sql = "SELECT $sAttr FROM POLIGONO WHERE ID_POLIGONO = '$id'";
        $res = $this->_bd->sql($sql);
        return mysql_fetch_object($res);
    }

    function desactiva($id) {
        $id = $this->_bd->limpia($id);
        $sql = "UPDATE POLIGONO SET ESTADO_POLIGONO = 0 WHERE ID_POLIGONO = $id";
        return $this->_bd->sql($sql);
    }
}
?>