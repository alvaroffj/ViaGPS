<?php
require_once 'Bd.php';

class ReglaMP {
    protected $_dbTable = "REGLA";
    protected $_id = "ID_REGLA";
    protected $_bd;

    function __construct() {
        $this->_bd = new Bd();
    }

    public function fetchByAlerta($idAlerta) {
        $idAlerta = $this->_bd->limpia($idAlerta);
        $sql = "SELECT * FROM $this->_dbTable WHERE ID_ALERTA = $idAlerta";
        $res = $this->_bd->sql($sql);
        if(mysql_num_rows($res)>1) {
            $arr = array();
            while($row = mysql_fetch_object($res)) {
                $arr[] = $row;
            }
            return $arr;
        } else return mysql_fetch_object($res);
    }

    public function fetchByAlertaFull($idAlerta) {
        $idAlerta = $this->_bd->limpia($idAlerta);
        $sql = "SELECT * FROM REGLA AS R 
                    INNER JOIN TIPO_REGLA AS TR 
                    INNER JOIN OPERADOR AS O 
                    INNER JOIN PARAMETRO AS P 
                ON 
                    R.ID_ALERTA = $idAlerta 
                    AND R.ID_TIPO_REGLA = TR.ID_TIPO_REGLA 
                    AND R.ID_OPERADOR = O.ID_OPERADOR 
                    AND R.ID_PARAMETRO = P.ID_PARAMETRO 
                    AND R.ESTADO_REGLA = 1
                    AND R.ID_TIPO_REGLA <> 4";
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = mysql_fetch_object($res)) {
            $arr[] = $row;
        }
        
        $sql = "SELECT * FROM REGLA AS R 
                    INNER JOIN TIPO_REGLA AS TR 
                    INNER JOIN OPERADOR AS O 
                    INNER JOIN SENSOR AS S 
                ON 
                    R.ID_ALERTA = $idAlerta 
                    AND R.ID_TIPO_REGLA = TR.ID_TIPO_REGLA 
                    AND R.ID_OPERADOR = O.ID_OPERADOR 
                    AND R.ID_PARAMETRO = S.ID_SENSOR 
                    AND R.ESTADO_REGLA = 1
                    AND R.ID_TIPO_REGLA = 4";
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        while($row = mysql_fetch_object($res)) {
            $arr[] = $row;
        }
        
        return $arr;
    }

    public function save($data) {
        $data["Tipo"] = $this->_bd->limpia($data["Tipo"]);
        $data["Parametro"] = $this->_bd->limpia($data["Parametro"]);
        $data["Operador"] = $this->_bd->limpia($data["Operador"]);
        $data["idAlerta"] = $this->_bd->limpia($data["idAlerta"]);
//        switch($data["Parametro"]) {
//            case "1":
//                $data["valor"] = $this->_bd->limpia($data["valorVel"]);
//                $data["poligono"] = "0";
//                break;
//            case "2":
//                $data["valor"] = $this->_bd->limpia($data["valorTiempo"]);
//                $data["poligono"] = "0";
//                break;
//            case "3":
//                $data["poligono"] = $this->_bd->limpia($data["Geozona"]);
//                break;
//            case "4":
//                $data["poligono"] = $this->_bd->limpia($data["Geofrontera"]);
//                break;
//            case "5":
//                $data["poligono"] = $this->_bd->limpia($data["Punto"]);
//                break;
//        }
        if($data["Tipo"]!="4") {
            if($data["Parametro"] == "1" || $data["Parametro"] == "2") {
                $data["poligono"] = "0";
            } else {
                $data["poligono"] = $data["valor"];
                $data["valor"] = "";
            }
        } else {
            $data["poligono"] = "0";
        }
        
        $sql = "SELECT ID_REGLA FROM $this->_dbTable 
                WHERE 
                    ID_ALERTA = ".$data["idAlerta"]."
                    AND ID_PARAMETRO = ".$data["Parametro"]."
                    AND ID_OPERADOR = ".$data["Operador"]."
                    AND ID_TIPO_REGLA = ".$data["Tipo"]."
                    AND ID_POLIGONO = ".$data["poligono"]."
                    AND VALOR_REGLA = '".$data["valor"]."'
                    AND ESTADO_REGLA = 1";
        
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        $n = mysql_num_rows($res);
        if($n == 0) {
            $sql = "INSERT INTO $this->_dbTable (ID_ALERTA, ID_PARAMETRO, ID_OPERADOR, ID_TIPO_REGLA, ID_POLIGONO, VALOR_REGLA) VALUES
                    (".$data["idAlerta"].", ".$data["Parametro"].", ".$data["Operador"].", ".$data["Tipo"].", ".$data["poligono"].",'".$data["valor"]."')";
//            echo $sql."<br>";
            $res = $this->_bd->sql($sql);
        }
    }


    function desactiva($id) {
//        echo "desactiva<br>";
        $id = $this->_bd->limpia($id);
        $sql = "UPDATE $this->_dbTable SET ESTADO_REGLA = 0 WHERE $this->_id = $id";
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
    }

    function find($id) {
        $id = $this->_bd->limpia($id);
        $sql = "SELECT * FROM $this->_dbTable WHERE $this->_id = $id";
        $res = $this->_bd->sql($sql);
        return mysql_fetch_object($res);
    }
    
}
?>