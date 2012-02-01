<?php
require_once 'Bd.php';

class AlertaMP {
    protected $_dbTable = "ALERTA";
    protected $_id = "ID_ALERTA";
    protected $_bd;

    function __construct() {
        $this->_bd = new Bd();
    }

    function find($idAlerta, $attr) {
        $idAlerta = $this->_bd->limpia($idAlerta);
        if($attr == null) {
            $sAttr = "*";
        } else {
            $sAttr = implode(",",$attr);
        }
        $sql = "SELECT $sAttr FROM $this->_dbTable WHERE $this->_id = $idAlerta";
        $res = $this->_bd->sql($sql);
        return mysql_fetch_object($res);
    }

    public function fetchByCuenta($idCuenta) {
        $idCuenta = $this->_bd->limpia($idCuenta);
        $sql = "SELECT * FROM $this->_dbTable WHERE accountID = $idCuenta";
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
        $sql = "INSERT INTO $this->_dbTable (accountID, userID, NOM_ALERTA, ESTADO_ALERTA) VALUES ('".$data["idCuenta"]."', '".$data["idUsuario"]."', '".$data["nom"]."', 1)";
        $res = $this->_bd->sql($sql);
    }

    public function update($data) {
        $data["id"] = $this->_bd->limpia($data["id"]);
        $data["idCuenta"] = $this->_bd->limpia($data["idCuenta"]);
        $data["idUsuario"] = $this->_bd->limpia($data["idUsuario"]);
        $data["nom"] = $this->_bd->limpia($data["nom"]);
        $data["estado"] = $this->_bd->limpia($data["estado"]);
        $dias = 7;
        for($i=1; $i<=$dias; $i++) {
            if(!isset($data["dia_".$i])) {
                $data["dia_".$i] = 0;
            }

            $ini = $data["hrs_ini_".$i];
            $fin = $data["hrs_fin_".$i];
            if($ini != $fin) {
                $h[] = $ini."-".$fin; //intervalo
            } else {
                $h[] = "1"; //todo el dia
            }
        }

        $sql = "UPDATE $this->_dbTable
                SET d1 = ".$data["dia_1"].",
                    d2 = ".$data["dia_2"].",
                    d3 = ".$data["dia_3"].",
                    d4 = ".$data["dia_4"].",
                    d5 = ".$data["dia_5"].",
                    d6 = ".$data["dia_6"].",
                    d7 = ".$data["dia_7"].",
                    h1 = '".$h[0]."',
                    h2 = '".$h[1]."',
                    h3 = '".$h[2]."',
                    h4 = '".$h[3]."',
                    h5 = '".$h[4]."',
                    h6 = '".$h[5]."',
                    h7 = '".$h[6]."',
                    NOM_ALERTA = '".$data["nom"]."',
                    ESTADO_ALERTA = ".$data["estado"]."
                WHERE
                    ID_ALERTA = ".$data["id"]."";
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        return $data["id"];
    }

    function desactiva($idAl) {
        $idAl = $this->_bd->limpia($idAl);
        $sql = "UPDATE $this->_dbTable SET ESTADO_ALERTA = 0 WHERE ID_ALERTA = $idAl";
        $res = $this->_bd->sql($sql);
        return $res;
    }
    
}
?>