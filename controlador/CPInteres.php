<?php
require_once 'modelo/PInteresMP.php';

class CPInteres {

    public $layout;
    public $op;
    protected $piMP;
    protected $cp;

    function __construct($cp) {
        $this->cp = $cp;
        $this->piMP = new PInteresMP();
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
        if (isset($_GET["do"])) {
            $attr = array("accountid");
            $this->cp->showLayout = false;
            $this->do = mysql_escape_string($_GET["do"]);
            switch ($this->do) {
                case 'mod':
                    if($this->cp->getSession()->get("accountID") == $_POST["accountID"]) {
                        $pt = $this->piMP->find($_POST["id"], $attr);
                        if($pt->accountid == $this->cp->getSession()->get("accountID")) {
                            if($this->piMP->update($_POST)) {
                                $this->cp->getSession()->salto("?sec=configuracion&ssec=puntointeres&op=mod&id=".$_POST["id"]."&e=0");
                            } else {
                                $this->cp->getSession()->salto("?sec=configuracion&ssec=puntointeres&op=mod&id=".$_POST["id"]."&e=1");
                            }
                        } else {
                            $this->cp->getSession()->salto("?sec=configuracion&ssec=puntointeres");
                        }
                    } else {
                        $this->cp->getSession()->salto("?sec=configuracion&ssec=puntointeres");
                    }
                    break;
                case 'add':
                    if($this->cp->getSession()->get("accountID") == $_POST["accountID"]) {
                        $this->piMP->insert($_POST);
                        if(!isset($_POST["noSalto"]))
                            $this->cp->getSession()->salto("?sec=configuracion&ssec=puntointeres");
                        else {
                            $r = array("error"=>0, "msg"=>"El punto de inter&eacute;s fue agregado correctamente");
                            echo json_encode($r);
                            die();
                        }
                    } else {
                        if(!isset($_POST["noSalto"]))
                            $this->cp->getSession()->salto("?sec=configuracion&ssec=puntointeres&op=add&e=2");
                        else {
                            $r = array("error"=>0, "msg"=>"El punto de inter&eacute;s NO pudo ser agregado");
                            echo json_encode($r);
                            die();
                        }
                    }
                    break;
                case 'del':
                    if(isset($_GET["id"])) {
                        $pt = $this->piMP->find($_GET["id"], $attr);
                        if($pt->accountid == $this->cp->getSession()->get("accountID")) {
                            if($this->piMP->desactiva($_GET["id"])) {
                                $this->cp->getSession()->salto("?sec=configuracion&ssec=puntointeres&e=0");
                            } else $this->cp->getSession()->salto("?sec=configuracion&ssec=puntointeres&e=1");
                        } else $this->cp->getSession()->salto("?sec=configuracion&ssec=puntointeres&e=2");
                    }
                    break;
                default:
                    break;
            }
        }
    }

    function setOp() {
        if (isset($_GET["op"])) {
            $this->op = mysql_escape_string($_GET["op"]);
            switch ($this->op) {
                case 'add':
                    $this->layout = "vista/punto_interes_form.phtml";
                    $this->tilSec = "Agregar punto de inter&eacute;s";
                    break;
                case 'mod':
                    $this->layout = "vista/punto_interes_form.phtml";
                    $this->tilSec = "Editar punto de inter&eacute;s";
                    if (isset($_GET["id"])) {
                        $this->obj = $this->piMP->find($_GET["id"]);
                    }
                    break;
            }
        } else {
            $this->layout = "vista/punto_interes.phtml";
            $this->puntos = $this->piMP->fetchByCuenta($this->cp->getSession()->get("accountID"));
        }
    }

}

?>