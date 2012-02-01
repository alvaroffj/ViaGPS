<?php

require_once 'modelo/AlertaMP.php';
require_once 'modelo/TipoReglaMP.php';
require_once 'modelo/ReglaMP.php';
require_once 'modelo/OperadorMP.php';
require_once 'modelo/ParametroMP.php';
require_once 'modelo/AccionMP.php';
require_once 'modelo/TipoAccionMP.php';
require_once 'modelo/DeviceMP.php';
require_once 'modelo/DeviceGroupMP.php';
require_once 'modelo/AlertaDeviceMP.php';
require_once 'modelo/PoligonoMP.php';
require_once 'modelo/PInteresMP.php';
require_once 'modelo/SensorDeviceMP.php';

class CAlarma {
    public $layout;
    public $op;
    protected $alMP;
    protected $trMP;
    protected $reMP;
    protected $opMP;
    protected $paMP;
    protected $acMP;
    protected $taMP;
    protected $deMP;
    protected $dgMP;
    protected $adMP;
    protected $poMP;
    protected $piMP;
    protected $sdMP;
    protected $cp;

    function __construct($cp) {
        $this->cp = $cp;
        $this->alMP = new AlertaMP();
        $this->trMP = new TipoReglaMP();
        $this->reMP = new ReglaMP();
        $this->opMP = new OperadorMP();
        $this->paMP = new ParametroMP();
        $this->acMP = new AccionMP();
        $this->taMP = new TipoAccionMP();
        $this->adMP = new AlertaDeviceMP();
        $this->deMP = new DeviceMP();
        $this->dgMP = new DeviceGroupMP();
        $this->poMP = new PoligonoMP();
        $this->piMP = new PInteresMP();
        $this->sdMP = new SensorDeviceMP();
        $this->estados = array(array("estado"=>"Inactivo", "id"=>0), array("estado"=>"Activo", "id"=>1));
        $this->estados[0] = (object) $this->estados[0];
        $this->estados[1] = (object) $this->estados[1];

        $this->dias = array(array("id"=>1, "dia"=>"Lunes"), array("id"=>2, "dia"=>"Martes"), array("id"=>3, "dia"=>"Miercoles"), array("id"=>4, "dia"=>"Jueves"), array("id"=>5, "dia"=>"Viernes"), array("id"=>6, "dia"=>"Sabado"), array("id"=>7, "dia"=>"Domingo"));
        $this->dias[0] = (object) $this->dias[0];
        $this->dias[1] = (object) $this->dias[1];
        $this->dias[2] = (object) $this->dias[2];
        $this->dias[3] = (object) $this->dias[3];
        $this->dias[4] = (object) $this->dias[4];
        $this->dias[5] = (object) $this->dias[5];
        $this->dias[6] = (object) $this->dias[6];
        $this->setGet();
        $this->setDo();
        $this->setOp();
    }

    public function getPol($id, $tipo) {
        $i = 0;
        switch($tipo) {
            case 1:
                $n = count($this->polygons);
                while($i<$n && $this->polygons[$i]->ID_POLIGONO != $id) {
                    $i++;
                }
                if($i<$n)
                    return $this->polygons[$i];
                else {
                    return $this->poMP->find($id);
                }
                break;
            case 2:
                $n = count($this->polylines);
                while($i<$n && $this->polylines[$i]->ID_POLIGONO != $id) {
                    $i++;
                }
                if($i<$n)
                    return $this->polylines[$i];
                else {
                    return $this->poMP->find($id);
                }
                break;
            case 3:
                $n = count($this->puntos);
                while($i<$n && $this->puntos[$i]->id != $id) {
                    $i++;
                }
                if($i<$n)
                    return $this->puntos[$i];
                else {
                    return $this->piMP->find($id);
                }
                break;
        }
    }
    
    function getSensor($id, $attr=null) {
        return $this->sdMP->findSensor($id, $attr);
    }
    
    function getOpSensor($idS, $idO) {
        return $this->sdMP->fetchOpSensor($idS, $idO);
    }

    function getLayout() {
        return $this->layout;
    }

    function setGet() {
        if(isset($_GET["get"])) {
            $this->cp->cp->showLayout = false;
            $this->get = mysql_escape_string($_GET["get"]);
            $attr = array("accountID");
            switch($this->get) {
                case 'devByGrupo':
                    if(isset($_GET["id_grupo"])) {
                        $gr = $this->dgMP->find($_GET["id_grupo"], $attr);
                        if($this->cp->getSession()->get("accountID") == $gr->accountID) {
                            echo json_encode($this->deMP->fetchByGrupo($_GET["id_grupo"]));
                        }
                    }
                    break;
                case 'sensores':
                    $r = $this->sdMP->fetchByAccount($this->cp->getSession()->get("accountID"));
                    echo json_encode($r);
                    break;
                case 'sensor':
                    if(isset($_GET["id"])) {
                        $r = $this->sdMP->findSensor($_GET["id"]);
                        echo json_encode($r);
                    }
                    break;
                case 'sensorOp':
                    if(isset($_GET["id"])) {
                        $r = $this->sdMP->fetchOpSensor($_GET["id"]);
                        echo json_encode($r);
                    }
                    break;
            }
        }
    }

    function setDo() {
        if (isset($_GET["do"])) {
            $this->cp->cp->showLayout = false;
            $this->do = mysql_escape_string($_GET["do"]);
            $attr = array("accountID");
            switch ($this->do) {
                case 'del':
                    if(isset($_GET["id"])) {
                        $r = $this->alMP->desactiva($_GET["id"]);
                        $this->cp->getSession()->salto("?sec=configuracion&ssec=alarma");
                    }
                    break;
                case 'mod':
                    if(count($_POST)>0) {
                        $idAlerta = $this->alMP->update($_POST);
                        $this->cp->getSession()->salto("?sec=configuracion&ssec=alarma&op=mod&id=$idAlerta&e=0");
                    }
                    break;
                case 'add':
                    if (count($_POST) > 0) {
                        $_POST["idCuenta"] = $this->cp->getSession()->get("accountID");
                        $_POST["idUsuario"] = $this->cp->getSession()->get("userID");
                        $this->alMP->save($_POST);
                        $idAlerta = mysql_insert_id();
                        $this->cp->getSession()->salto("?sec=configuracion&ssec=alarma&op=mod&id=$idAlerta");
                    }
                    break;
                case 'addRegla':
                    if (count($_POST) > 0) {
//                        echo "<pre>";
//                        print_r($_POST);
//                        echo "</pre>";
                        if($_POST["Tipo"]!=4) {
                            switch($_POST["Parametro"]) {
                                case "3": //geozona
                                    if($_POST["Geozona"] == "0") {
                                        $pol = $this->poMP->fetchByCuentaTipo($this->cp->getSession()->get("accountID"), 2, true);
                                        $id = "ID_POLIGONO";
                                        $valForm = "valor";
                                    } else $pol = -1;
                                    break;
                                case "4": //geofrontera
                                    if($_POST["Geofrontera"] == "0") {
                                        $pol = $this->poMP->fetchByCuentaTipo($this->cp->getSession()->get("accountID"), 1, true);
                                        $id = "ID_POLIGONO";
                                        $valForm = "valor";
                                    } else $pol = -1;
                                    break;
                                case "5": //punto de interes
                                    if($_POST["Punto"] == "0") {
                                        $pol = $this->piMP->fetchByCuenta($this->cp->getSession()->get("accountID"), array("id"), true);
                                        $id = "id";
                                        $valForm = "valor";
                                    } else $pol = -1;
                                    break;
                                default:
                                    $pol = -1;
                                    break;
                            }
                        } else { //en caso que el tipo sea == 4, entonces es un sensor y el parametro es el id del sensor
                            $pol = -1;
                        }
                        if($pol == -1) {
                            $this->reMP->save($_POST);
                        } else {
                            $n = count($pol);
                            for($i = 0; $i<$n; $i++) {
                                $_POST[$valForm] = $pol[$i]->$id;
                                $this->reMP->save($_POST);
                            }
                        }
                        $idAlerta = $_POST["idAlerta"];
                        $this->cp->getSession()->salto("?sec=configuracion&ssec=alarma&op=mod&id=$idAlerta");
                    }
                    break;
                case 'addDevice':
                    if (count($_POST) > 0) {
                        $_POST["idCuenta"] =$this->cp->getSession()->get("accountID");
                        $idAlerta = $_POST["idAlerta"];
                        $gr = $this->dgMP->find($_POST["Grupo"], $attr);
                        $al = $this->alMP->find($_POST["idAlerta"], $attr);
                        if($this->cp->getSession()->get("accountID") == $al->accountID) {
                            if($this->cp->getSession()->get("accountID") == $gr->accountID) {
                                if($_POST["Vehiculo"]!=0) { //un device
                                    $de = $this->deMP->find($_POST["Vehiculo"], $attr);
                                    if($this->cp->getSession()->get("accountID") == $de->accountID) {
                                        $this->adMP->save($_POST["Vehiculo"], $_POST["idAlerta"]);
                                    }
                                } else { //todos los devices del grupo
                                    $de = $this->deMP->fetchByGrupo($_POST["Grupo"]);
//                                    print_r($de);
                                    foreach($de as $d) {
                                        $this->adMP->save($d->deviceID, $_POST["idAlerta"]);
                                    }
                                }
                            }
                        }
                        $this->cp->getSession()->salto("?sec=configuracion&ssec=alarma&op=mod&id=$idAlerta");
                    }
                    break;
                case 'addAccion':
                    if (count($_POST) > 0) {
                        $this->acMP->save($_POST);
                        $idAlerta = $_POST["idAlerta"];
                        $this->cp->getSession()->salto("?sec=configuracion&ssec=alarma&op=mod&id=$idAlerta");
                    }
                    break;
                case 'delRegla':
                    if(isset($_GET["id_regla"])) {
                        $re = $this->reMP->find($_GET["id_regla"]);
                        $al = $this->alMP->find($_GET["id_alerta"], $attr);
                        if($this->cp->getSession()->get("accountID") == $al->accountID) {
                            if($re->ID_ALERTA == $_GET["id_alerta"]) {
                                $this->reMP->desactiva($_GET["id_regla"]);
                            }
                        }
                        $this->cp->getSession()->salto("?sec=configuracion&ssec=alarma&op=mod&id=".$_GET["id_alerta"]);
                    }
                    break;
                case 'delDevice':
                    if(isset($_GET["id_device"])) {
                        $de = $this->deMP->find($_GET["id_device"]);
                        $al = $this->alMP->find($_GET["id_alerta"], $attr);
                        if($this->cp->getSession()->get("accountID") == $de->accountID) {
                            if($this->cp->getSession()->get("accountID") == $al->accountID) {
                                $this->adMP->delete($_GET["id_device"]);
                            }
                        }
                        $this->cp->getSession()->salto("?sec=configuracion&ssec=alarma&op=mod&id=".$_GET["id_alerta"]);
                    }
                    break;
                case 'delAccion':
                    if(isset($_GET["id_accion"])) {
                        $ac = $this->acMP->find($_GET["id_accion"]);
                        $al = $this->alMP->find($_GET["id_alerta"], $attr);
                        if($_GET["id_alerta"] == $ac->ID_ALERTA) {
                            if($this->cp->getSession()->get("accountID") == $al->accountID) {
                                $this->acMP->desactiva($_GET["id_accion"]);
                            }
                        }
                        $this->cp->getSession()->salto("?sec=configuracion&ssec=alarma&op=mod&id=".$_GET["id_alerta"]);
                    }
                    break;
            }
        }
    }

    function setOp() {
        if (isset($_GET["op"])) {
            $this->op = mysql_escape_string($_GET["op"]);
            switch ($this->op) {
                case 'mod':
                    $this->layout = "vista/alarma_regla.phtml";
                    $this->tilSec = "Editar Alarma";
                    if (isset($_GET["id"])) {
                        $this->tipo_regla = $this->trMP->fetchAll();
                        $this->reglas = $this->reMP->fetchByAlertaFull($_GET["id"]);
                        $this->alerta = $this->alMP->find($_GET["id"]);
                        $this->acciones = $this->acMP->fetchByAlertaFull($_GET["id"]);
                        $this->tipo_accion = $this->taMP->fetchAll();
                        $this->grupos = $this->dgMP->fetchByCuenta($this->cp->getSession()->get("accountID"));
                        $this->alerta_device = $this->adMP->fetchByAlerta($_GET["id"]);
                        $this->polygons = $this->poMP->fetchByCuentaTipo($this->cp->getSession()->get("accountID"), 2, true);
                        $this->polylines = $this->poMP->fetchByCuentaTipo($this->cp->getSession()->get("accountID"), 1, true);
                        $this->puntos = $this->piMP->fetchByCuenta($this->cp->getSession()->get("accountID"), null, true);
                        $this->min = range(0,59,15);
                        $this->hrs = range(0,23,1);
                    }
                    break;
            }
        } else {
            $this->layout = "vista/alarma.phtml";
            $this->alertas = $this->alMP->fetchByCuenta($this->cp->getSession()->get("accountID"));
        }
    }
}
?>