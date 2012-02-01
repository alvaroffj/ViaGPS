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

    function recuperar() {
        $usu = $this->usuMP->findByUser($_POST["user"]);
        if($usu!=null) {
            include_once '../modelo/class.phpmailer-lite.php';
            $mail = new PHPMailerLite();
            $mail->IsMail();
            $mail->SetFrom("no-reply@jnr.cl", "Mantenedor JNR.cl");
            $mail->Subject = "Recuperar Clave - Mantenedor JNR.cl";
            $mail->AddAddress($usu->EMAIL_USUARIO, "Mantenedor JNR.cl");
            $pass = $this->genPass();
            $body = "El usuario es <b>".$usu->USER_USUARIO."</b> y su nueva contraseña <b>".$pass."</b><br><br>
                - Mantenedor JNR.cl";
            $mail->MsgHTML($body);
            $success = $mail->Send();
            if($success) {
                $this->usuMP->updatePass($usu->ID_USUARIO, $pass);
                $this->cp->getSession()->salto("?sec=log&e=2");
            } else {
                $this->cp->getSession()->salto("?sec=log&op=rec&e=2");
            }
        } else {
            $this->cp->getSession()->salto("?sec=log&op=rec&e=1");
        }
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
            $this->cp->getSession()->salto("?sec=monitoreo");
        } else {
            $this->cp->getSession()->salto("?&e=1");
        }
    }

    function setDo() {
        $do = $_GET["do"];
        switch($do) {
            case 'in':
                $this->checkLogin();
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