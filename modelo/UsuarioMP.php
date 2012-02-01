<?php
require_once 'Bd.php';

class UsuarioMP {
    protected $_dbTable = "User";
    protected $_id = "userID";
    protected $_bd;

    function __construct($cuentaData = null) {
        if($cuentaData != null) {
            $this->_bd = new Bd($cuentaData->NOM_BD, $cuentaData->PASS_BD, $cuentaData->SERVER_BD_FROM_APP, $cuentaData->NOM_BD);
        } else {
            $this->_bd = new Bd();
        }
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

    function fetchByCuenta($idCuenta, $attr=null) {
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

        $sql = "SELECT U.$sAttr, R.displayName FROM $this->_dbTable AS U INNER JOIN Role AS R ON U.accountID = $idCuenta AND U.roleID = R.roleID";
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
        $res = $this->_bd->sql($sql);
        return mysql_fetch_object($res);
    }

    function insert($data) {
        $data["user"] = $this->_bd->limpia($data["user"]);
        $data["pass"] = md5($this->_bd->limpia($data["pass"]));
        $data["nom"] = $this->_bd->limpia($data["nom"]);
        $data["ema"] = $this->_bd->limpia($data["ema"]);
        $data["rol"] = $this->_bd->limpia($data["rol"]);
        $data["estado"] = $this->_bd->limpia($data["estado"]);
        $data["accountID"] = $this->_bd->limpia($data["accountID"]);

        $sql = "INSERT INTO $this->_dbTable
                    (accountID, roleID, userName, password, contactName, contactEmail, timeZone, isActive) VALUES
                    (".$data["accountID"].", ".$data["rol"].", '".$data["user"]."', '".$data["pass"]."', '".$data["nom"]."', '".$data["ema"]."', '-4', ".$data["estado"].")";
        $res = $this->_bd->sql($sql);
        return mysql_insert_id();
    }

    function update($data) {
        $data["id"] = $this->_bd->limpia($data["id"]);
        $data["user"] = $this->_bd->limpia($data["user"]);
        $data["nom"] = $this->_bd->limpia($data["nom"]);
        $data["ema"] = $this->_bd->limpia($data["ema"]);
        $data["rol"] = $this->_bd->limpia($data["rol"]);
        $data["estado"] = $this->_bd->limpia($data["estado"]);
        $data["accountID"] = $this->_bd->limpia($data["accountID"]);
        if($data["pass"]!="") {
            $data["pass"] = md5($this->_bd->limpia($data["pass"]));
            $sqlPass = "password = '".$data["pass"]."', ";
        } else $sqlPass = "";

        $sql = "UPDATE $this->_dbTable SET
                    roleID = ".$data["rol"].",
                    $sqlPass
                    contactName = '".$data["nom"]."',
                    contactEmail = '".$data["ema"]."',
                    isActive = ".$data["estado"]."
                WHERE userID = ".$data["id"];

//        echo $sql."<br>";
        return $this->_bd->sql($sql);
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

        $sql = "SELECT A.accountID, A.accountName, U.contactName, U.userName, U.userID, U.roleID, A.displayName AS accountName FROM User AS U INNER JOIN Account AS A ON A.accountName = '".$account."' AND A.accountID = U.accountID AND userName = '".$user."' AND U.password = '".$pass."' AND U.isActive = 1";
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        return mysql_fetch_object($res);
    }
}
?>