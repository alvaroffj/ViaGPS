<?php
require_once 'modelo/DriverMP.php';
require_once 'modelo/DeviceMP.php';

class CConductor {
    protected $cp;
    protected $drMP;
    protected $deMP;

    function  __construct($cp) {
        $this->cp = $cp;
        $this->drMP = new DriverMP();
        $this->deMP = new DeviceMP();
        $this->estados = array(array("estado"=>"Inactivo", "id"=>0), array("estado"=>"Activo", "id"=>1));
        $this->estados[0] = (object) $this->estados[0];
        $this->estados[1] = (object) $this->estados[1];
        $this->setDo();
        $this->setOp();
    }

    function getLayout() {
        return $this->layout;
    }

    function setDo() {
        if(isset($_GET["do"])) {
            $this->cp->cp->showLayout = false;
            $attr = array("accountID");
            switch($_GET["do"]) {
                case "add":
                    if($this->cp->getSession()->get("accountID") == $_POST["accountID"]) {
                        $this->drMP->insert($_POST);
                        $this->cp->getSession()->salto("?sec=configuracion&ssec=conductor");
                    } else {
                        $this->cp->getSession()->salto("?sec=configuracion&ssec=conductor&op=add&e=2");
                    }
                    break;
                case "mod":
                    $id = $_POST["id"];
                    $dr = $this->drMP->find($id, $attr);
                    if($dr->accountID == $this->cp->getSession()->get("accountID")) {
                        if($this->cp->getSession()->get("accountID") == $_POST["accountID"]) {
                            if($this->drMP->update($_POST)) {
                                $this->cp->getSession()->salto("?sec=configuracion&ssec=conductor&op=mod&id=$id&e=0");
                            } else {
                                $this->cp->getSession()->salto("?sec=configuracion&ssec=conductor&op=mod&id=$id&e=1");
                            }
                        } else {
                            $this->cp->getSession()->salto("?sec=configuracion&ssec=conductor&e=2");
                        }
                    } else $this->cp->getSession()->salto("?sec=configuracion&ssec=conductor&e=1");
                    break;
                case "del":
                    if(isset($_GET["id"])) {
                        $id = $_GET["id"];
                        $dr = $this->drMP->find($id, $attr);
                        if($dr->accountID == $this->cp->getSession()->get("accountID")) {
                            if($this->drMP->desactiva($id)) {
                                $this->cp->getSession()->salto("?sec=configuracion&ssec=conductor");
                            }
                        }
                    }
                    break;
            }
        }
    }

    function setOp() {
        if (isset($_GET["op"])) {
            $this->op = mysql_escape_string($_GET["op"]);
            switch ($this->op) {
                case 'add':
                    $this->layout = "vista/conductor_form.phtml";
                    $this->tilSec = "Nuevo Conductor";
                    $this->vehiculos = $this->deMP->fetchByCuenta($this->cp->getSession()->get("accountID"), null, null, true);
                    break;
                case "mod":
                    $this->layout = "vista/conductor_form.phtml";
                    $this->tilSec = "Modificar Conductor";
                    $this->obj = $this->drMP->find($_GET["id"]);
                    $this->dev = $this->deMP->findByDriver($_GET["id"], array("deviceID"));
                    $this->vehiculos = $this->deMP->fetchByCuenta($this->cp->getSession()->get("accountID"), null, null, true);
                    if(!$this->obj) $this->cp->getSession()->salto("?sec=configuracion&ssec=conductor");
                    if($this->obj->accountID != $this->cp->getSession()->get("accountID")) $this->cp->getSession()->salto("?sec=configuracion&ssec=conductor");
                    break;
            }
        } else {
            $this->layout = "vista/conductor.phtml";
            $this->usuarios = $this->drMP->fetchByCuenta($this->cp->getSession()->get("accountID"));
        }
    }
}
?>