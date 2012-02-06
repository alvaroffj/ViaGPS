<?php
include_once 'modelo/UsuarioMP.php';

class CLog {
    protected $cp;
    protected $usuMP;
    protected $login;
    protected $layout;

    function __construct($cp) {
        $this->cp = $cp;
        $this->usuMP = new UsuarioMP($this->cp->cuentaData);
        $this->layout = "vista/login.phtml";
        $this->cp->thisLayout = false;
        $this->setDo();
        $this->setOp();
    }

    function logout() {
        $this->cp->getSession()->kill();
        $this->cp->getSession()->salto("index.php");
    }

    function getLayout() {
        return $this->layout;
    }

    function genPass($n=5) {
        $letras = "023456789ABCDEFJHIJKLMNOPQRSTUVWXYZabcdefjhijkmnopqrstuvwxyz";
        $pass = "";
        for($i=0; $i<$n; $i++) {
            $pass .= substr($letras,rand(0,61),1);
        }
        return $pass;
    }

    function checkLogin() {
        $this->login = $this->usuMP->validaCuenta($this->cp->cuenta, $_POST["user"], $_POST["pass"]);
        if($this->login != null) {
            $this->cp->getSession()->set("account", $this->login->accountName);
            $this->cp->getSession()->set("accountID", $this->login->accountID);
            $this->cp->getSession()->set("accountName", $this->login->accountName);
            $this->cp->getSession()->set("user", $this->login->contactName);
            $this->cp->getSession()->set("userName", $this->login->userName);
            $this->cp->getSession()->set("userID", $this->login->userID);
            $this->cp->getSession()->set("roleID", $this->login->roleID);
            $this->cp->getSession()->set("cueNom", $this->cp->cuentaData->NOM_BD);
            $this->cp->getSession()->set("cueBD", $this->cp->cuentaData->NOM_BD);
            $this->cp->getSession()->set("cuePass", $this->cp->cuentaData->PASS_BD);
            $this->cp->getSession()->set("cueBDIP", $this->cp->cuentaData->SERVER_BD_FROM_APP);
            return true;
        } else {
            return false;
        }
    }

    function setDo() {
        if(isset($_GET["do"])) {
            $do = $_GET["do"];
            switch($do) {
                case 'in':
                    $res = new stdClass();
                    if($this->checkLogin()) {
                        $res->ERROR = 0;
                        $res->MENSAJE = "Redireccionando";
                    } else {
                        $res->ERROR = 1;
                        $res->MENSAJE = "Usuario o contraseña incorrectos";
                    }
                    echo json_encode($res);
                    break;
                case 'out':
                    $this->logout();
                    break;
                case 'rec':
                    $this->recuperar();
                    break;
                default:
                    break;
            }
            die();
        }
    }

    function setOp() {
        $op = $_GET["op"];
        switch($op) {
            case 'rec':
                $this->layout = "vista/login_recuperar.phtml";
                break;
            default:
//                echo "default<br>";
                break;
        }
    }
}
?>