<?php
require_once 'Bd.php';

class CuentaMP {
    protected $_dbTable = "CUENTA";
    protected $_id = "ID_CUENTA";
    protected $_bd;

    function __construct() {
        $this->_bd = new Bd("maestra", "ScAWEFNPxwjzWBmm", "10.179.7.224", "maestra");
    }

    function isActive($nom) {
        $nom = $this->_bd->limpia($nom);
        $sql = "SELECT ESTADO_CUENTA FROM CUENTA WHERE NOM_CUENTA = '$nom'";
        $res = $this->_bd->sql($sql);
        $row = mysql_fetch_object($res);
        if($row)
            return ($row->ESTADO_CUENTA == 1);
        else return false;
    }
    
    function findByNom($nom, $attr=null) {
        $nom = $this->_bd->limpia($nom);
        
        if($attr == null) {
            $sAttr = "*";
        } else {
            $sAttr = implode(",", $attr);
        }
        
        $sql = "SELECT $sAttr FROM CUENTA WHERE NOM_CUENTA = '$nom'";
        $res = $this->_bd->sql($sql);
        $row = mysql_fetch_object($res);
        
        return $row;
    }
}
?>
