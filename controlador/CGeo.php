<?php
require_once 'modelo/PoligonoMP.php';

class CGeo {

    public $layout;
    public $op;
    protected $poMP;
    protected $cp;

    function __construct($cp) {
        $this->cp = $cp;
        $this->poMP = new PoligonoMP();
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
            $attr = array("accountID");
            $this->cp->showLayout = false;
            $this->do = mysql_escape_string($_GET["do"]);
            switch ($this->do) {
                case 'mod':
//                    print_r($_POST);
                    if (count($_POST) > 0) {
                        $id = $_POST["idPol"];
                        $pol = $this->poMP->find($id, $attr);
                        if($pol->accountID == $this->cp->getSession()->get("accountID")) {
                            if($this->poMP->updatePoligono($_POST)) {
                                $this->cp->getSession()->salto("?sec=configuracion&ssec=geozona&op=mod&id=$id&e=0");
                            } else $this->cp->getSession()->salto("?sec=configuracion&ssec=geozona&op=mod&id=$id&e=1");
                        } else $this->cp->getSession()->salto("?sec=configuracion&ssec=geozona&e=2");
                    }
                    break;
                case 'add':
                    if (count($_POST) > 0) {
                        if($this->poMP->savePoligono($_POST)) {
                            $this->cp->getSession()->salto("?sec=configuracion&ssec=geozona&e=0");
                        } else $this->cp->getSession()->salto("?sec=configuracion&ssec=geozona&op=add&e=1");
                    }
                    break;
                case 'del':
                    if(isset($_GET["id"])) {
                        $pol = $this->poMP->find($_GET["id"], $attr);
                        if($pol->accountID == $this->cp->getSession()->get("accountID")) {
                            if($this->poMP->desactiva($_GET["id"])) {
                                $this->cp->getSession()->salto("?sec=configuracion&ssec=geozona&e=0");
                            } else $this->cp->getSession()->salto("?sec=configuracion&ssec=geozona&e=1");
                        } else $this->cp->getSession()->salto("?sec=configuracion&ssec=geozona&e=2");
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
                    $this->layout = "vista/geozona_form.phtml";
                    $this->tilSec = "Agregar Geo Zona";
                    break;
                case 'mod':
                    $this->layout = "vista/geozona_form.phtml";
                    $this->tilSec = "Editar Geo Zona";
                    if (isset($_GET["id"])) {
                        $this->poligono = $this->poMP->find($_GET["id"]);
                        $this->puntos = $this->poMP->fetchPuntos($this->poligono->ID_POLIGONO);
                    }
                    break;
            }
        } else {
            $this->layout = "vista/geozona.phtml";
            $this->poligonos = $this->poMP->fetchByCuenta($this->cp->getSession()->get("accountID"));
        }
    }

}

?>