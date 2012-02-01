<?php
require_once 'modelo/DeviceMP.php';
require_once 'modelo/DeviceGroupMP.php';
require_once 'modelo/DriverMP.php';
//require_once 'modelo/DeviceListMP.php';

class CVehiculo {
    protected $cp;
    protected $deMP;
    protected $dgMP;
    protected $drMP;

    function  __construct($cp) {
        $this->cp = $cp;
        $this->deMP = new DeviceMP();
        $this->dgMP = new DeviceGroupMP();
        $this->drMP = new DriverMP();
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
            $this->cp->showLayout = false;
            $attr = array("accountID");
            switch($_GET["do"]) {
                case "modConductor":
                    if(isset($_GET["idDev"]) && isset($_GET["idDri"])) {
                        $this->deMP->setDriver($_GET["idDev"], $_GET["idDri"]);
                    }
                    break;
                case "add_to_grupo":
                    if(isset($_GET["id"]) && isset($_GET["id_grupo"])) {
                        $gr = $this->dgMP->find($_GET["id_grupo"], $attr);
                        $de = $this->deMP->find($_GET["id"], $attr);
                        if($this->cp->getSession()->get("accountID") == $gr->accountID) {
                            if($this->cp->getSession()->get("accountID") == $de->accountID){
                                $this->dgMP->addToGrupo($_GET["id_grupo"], $_GET["id"]);
                                $this->cp->getSession()->salto("?sec=configuracion&ssec=vehiculo&op=mod_grupo&id=".$_GET["id_grupo"]."&e=0");
                            } else {
                                $this->cp->getSession()->salto("?sec=configuracion&ssec=vehiculo&op=mod_grupo&id=".$_GET["id_grupo"]."&e=3");
                            }
                        } else {
                            $this->cp->getSession()->salto("?sec=configuracion&ssec=vehiculo");
                        }
                    }
                    break;
                case "del_from_grupo":
                    if(isset($_GET["id"]) && isset($_GET["id_grupo"])) {
                        $gr = $this->dgMP->find($_GET["id_grupo"], $attr);
                        $de = $this->deMP->find($_GET["id"], $attr);
                        if($this->cp->getSession()->get("accountID") == $gr->accountID) {
                            if($this->cp->getSession()->get("accountID") == $de->accountID){
                                $this->dgMP->delFromGrupo($_GET["id_grupo"], $_GET["id"]);
                                $this->cp->getSession()->salto("?sec=configuracion&ssec=vehiculo&op=mod_grupo&id=".$_GET["id_grupo"]."&e=0");
                            } else {
                                $this->cp->getSession()->salto("?sec=configuracion&ssec=vehiculo&op=mod_grupo&id=".$_GET["id_grupo"]."&e=4");
                            }
                        } else {
                            $this->cp->getSession()->salto("?sec=configuracion&ssec=vehiculo");
                        }
                    }
                    break;
                case "add_grupo":
                    if($_POST["accountID"] == $this->cp->getSession()->get("accountID")) {
                        $id = $this->dgMP->save($_POST);
                        if($id) {
                            $this->cp->getSession()->salto("?sec=configuracion&ssec=vehiculo&op=mod_grupo&id=".$id."&e=1");
                        } else {
                            $this->cp->getSession()->salto("?sec=configuracion&ssec=vehiculo&op=add_grupo&e=2");
                        }
                    } else {
                        $this->cp->getSession()->salto("?sec=configuracion&ssec=vehiculo");
                    }
                    break;
                case "mod_grupo":
                    $id = $_POST["id"];
                    if($_POST["accountID"] == $this->cp->getSession()->get("accountID")) {
                        $gr = $this->dgMP->find($id, $attr);
                        if($gr->accountID == $this->cp->getSession()->get("accountID")) {
                            if($this->dgMP->update($_POST)) {
                                $this->cp->getSession()->salto("?sec=configuracion&ssec=vehiculo&op=mod_grupo&id=".$id."&e=0");
                            } else {
                                $this->cp->getSession()->salto("?sec=configuracion&ssec=vehiculo&op=mod_grupo&id=".$id."&e=2");
                            }
                        } else {
                            $this->cp->getSession()->salto("?sec=configuracion&ssec=vehiculo");
                        }
                    } else {
                        $this->cp->getSession()->salto("?sec=configuracion&ssec=vehiculo");
                    }
                    break;
                case "del_grupo":
                    $id = $_GET["id"];
                    $gr = $this->dgMP->find($id, $attr);
                    if($gr->accountID == $this->cp->getSession()->get("accountID")) {
                        $delGr = $this->dgMP->desactiva($id);
//                        $delDeLi = $this->dgMP->delFromGrupo($id);
                        $this->cp->getSession()->salto("?sec=configuracion&ssec=vehiculo");
                    } else {
                        $this->cp->getSession()->salto("?sec=configuracion&ssec=vehiculo");
                    }
                    break;
                case "mod_dev":
                    $de = $this->deMP->find($_POST["id"], $attr);
                    if($de->accountID == $this->cp->getSession()->get("accountID")) {
                        $this->deMP->updateName($_POST["id"], $_POST["nomDev"]);
                        echo $_POST["nomDev"];
                    } else {
                        echo "Hubo un error al guardar";
                    }
                    break;
            }
            die();
        }
    }

    function setOp() {
        if (isset($_GET["op"])) {
            $this->op = mysql_escape_string($_GET["op"]);
            switch ($this->op) {
                case 'add_grupo':
                    $this->layout = "vista/vehiculo_grupo_form.phtml";
                    $this->tilSec = "Nuevo Grupo";
                    break;
                case "mod_grupo":
                    $this->layout = "vista/vehiculo_grupo_form.phtml";
                    $this->tilSec = "Modificar Grupo";
                    $this->obj = $this->dgMP->find($_GET["id"]);
                    if(!$this->obj) $this->cp->getSession()->salto("?sec=configuracion&ssec=vehiculo");
                    if($this->obj->accountID != $this->cp->getSession()->get("accountID")) $this->cp->getSession()->salto("?sec=vehiculo");
                    $this->inGroup = $this->dgMP->fetchDevice($this->cp->getSession()->get("accountID"), $_GET["id"], true);
                    $this->outGroup = $this->dgMP->fetchDevice($this->cp->getSession()->get("accountID"), $_GET["id"], false, $this->inGroup);
                    break;
            }
        } else {
            $this->layout = "vista/vehiculo.phtml";
            $this->grupos = $this->dgMP->fetchByCuenta($this->cp->getSession()->get("accountID"), true);
            $this->device = $this->deMP->fetchByCuenta($this->cp->getSession()->get("accountID"), null, null, true);
            $this->conductores = $this->drMP->fetchByCuenta($this->cp->getSession()->get("accountID"), null, false);
            foreach($this->conductores as $c) {
                $arr[$c->driverID] = $c->displayName;
            }
            $arr[0] = "Sin conductor";
            $this->condSel = $arr;
        }
    }

    function getNumDevice($idGrupo) {
        return $this->dgMP->fetchNumDevice($idGrupo);
    }
}
?>