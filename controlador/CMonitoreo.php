<?php
require_once 'modelo/DeviceMP.php';
require_once 'modelo/DeviceGroupMP.php';
require_once 'modelo/EventDataMP.php';
require_once 'modelo/PInteresMP.php';
require_once 'modelo/DireccionMP.php';
require_once 'modelo/AlertaLogMP.php';
require_once 'modelo/PoligonoMP.php';
require_once 'modelo/VehicleMP.php';
require_once 'modelo/SensorDeviceMP.php';

class CMonitoreo {
    public $layout;
    public $op;
    protected $deMP;
    protected $dgMP;
    protected $piMP;
    protected $diMP;
    protected $alMP;
    protected $poMP;
    protected $sdMP;
    protected $cp;

    function __construct($cp) {
        $this->cp = $cp;
        $this->deMP = new DeviceMP();
        $this->dgMP = new DeviceGroupMP();
        $this->edMP = new EventDataMP();
        $this->piMP = new PInteresMP();
        $this->diMP = new DireccionMP();
        $this->alMP = new AlertaLogMP();
        $this->poMP = new PoligonoMP();
        $this->veMP = new VehicleMP();
        $this->sdMP = new SensorDeviceMP();
        $this->setGet();
        $this->setOp();
    }

    function getLayout() {
        return $this->layout;
    }

    function setGet() {
        if (isset($_GET["get"])) {
            $attr = array("accountID");
            $this->cp->showLayout = false;
            $this->get = mysql_escape_string($_GET["get"]);
            switch ($this->get) {
                case 'pin_vehiculo':
                    $r = $this->veMP->fetchAll();
                    echo json_encode($r);
                    break;
                case 'sensor':
                    $r = $this->sdMP->fetchByAccount($this->cp->getSession()->get("accountID"));
                    echo json_encode($r);
                    break;
                case 'deviceSensor':
                    if($this->cp->isAdmin() || $this->cp->isSuperAdmin()) {
                        $dev = $this->deMP->fetchByCuenta($this->cp->getSession()->get("accountID"), null, array("deviceID", "displayName"));
                    } else {
                        $dev = $this->deMP->fetchByUser($this->cp->getSession()->get("userID"));
                    }
                    foreach($dev as $d) {
                        $idDev[] = $d->deviceID;
                    }
                    $res = $this->sdMP->fetchByDevices($idDev);
                    foreach($res as $r) {
                        $opAux = $this->sdMP->fetchOpSensor($r->ID_SENSOR);
                        foreach($opAux as $o) {
                            $op[$o->VALOR_SENSOR] = $o->SENSOR_OPCION;
                        }
                        $r->OPCIONES = $op;
                        $out["S".$r->DEVICEID][] = $r;
                    }
                    echo json_encode($out);
                    break;
                case 'device':
                    if($this->cp->isAdmin() || $this->cp->isSuperAdmin()) {
                        $r = $this->edMP->fetchLastByAccount($this->cp->getSession()->get("accountID"));
                    } else {
                        $r = $this->edMP->fetchLastByUser($this->cp->getSession()->get("userID"));
                    }
                    echo json_encode($r);
                    break;
                case 'pinteres':
                    $r = $this->piMP->fetchByCuenta($this->cp->getSession()->get("accountID"), null, true);
                    echo json_encode($r);
                    break;
                case 'direccion':
                    $url->LATITUD = round($_GET["lat"], 5);
                    $url->LONGITUD = round($_GET["lon"], 5);
                    if($url->LATITUD == 0 || $url->LONGITUD == 0) {
                        $url->DIRECCION = "Direcci&oacute;n no valida";
                        echo json_encode($url);
                    } else {
                        $res = $this->diMP->find($url->LATITUD, $url->LONGITUD);
                        if($res != null) {
                            $res->fuente = "InternalBD";
                            echo json_encode($res);
                        } else {
                            $delay = 0;
                            $geocode_pending = true;
                            $urlBase = "http://maps.google.com/maps/api/geocode/xml?";
                            while ($geocode_pending) {
                                $urlRequest = $urlBase . "latlng=$url->LATITUD,$url->LONGITUD&sensor=true&region=CL";
                                $xml = simplexml_load_file($urlRequest) or die("url not loading");
                                $status = $xml->status;
                                if (strcmp($status, "OK") == 0) {
                                    $geocode_pending = false;
                                    $url->DIRECCION = $xml->result[0]->formatted_address."";
                                    $n = count($xml->result[0]->address_component);
                                    for($i=0; $i<$n; $i++) {
                                        $d = $xml->result[0]->address_component[$i];
                                        switch($d->type) {
                                            case 'administrative_area_level_3': //comuna
                                                $url->COMUNA = $d->long_name."";
                                                break;
                                            case 'administrative_area_level_1': //region
                                                $url->REGION = $d->long_name."";
                                                break;
                                            case 'locality':
                                                $url->CIUDAD = $d->long_name."";
                                                break;
                                            case 'country': //pais
                                                $url->PAIS = $d->long_name."";
                                                break;
                                        }
                                    }
                                    $this->diMP->insert($url);
                                } else if (strcmp($status, "620") == 0) {
                                    $delay += 100000;
                                } else {
                                    $geocode_pending = false;
                                }
                                usleep($delay);
                            }
                            $url->fuente = "GoogleMapsApi";
                            echo json_encode($url);
                        }
                    }
                    break;
                case 'alarma':
                    if($this->cp->isAdmin() || $this->cp->isSuperAdmin()) {
                        $device = $this->deMP->fetchByCuenta($this->cp->getSession()->get("accountID"), null, array("deviceID", "displayName"));
                    } else {
                        $device = $this->deMP->fetchByUser($this->cp->getSession()->get("userID"));
                    }

                    $devId = array();
                    foreach($device as $d) {
                        $devId[] = $d->deviceID;
                        $displayName[$d->deviceID] = $d->displayName;
                    }
                    if(isset($_GET["hrs"])) {
//                        echo "hrs<br>";
                        $ini = time()-$_GET["hrs"]*60*60;
                    } else {
                        if($this->cp->getSession()->existe("lastAl")) {
//                            echo "sess<br>";
                            $ini = $this->cp->getSession()->get("lastAl");
                        } else {
                            $ini = time();
                            $this->cp->getSession()->set("lastAl", $ini);
                        }

//                        echo $ini."<br>";
                    }
                    $al = $this->alMP->reporte($ini, null, $devId);
//                    print_r($al);
                    $i = 0;
                    foreach($al as $a) {
                        $a->displayName = $displayName[$a->deviceID];
                        $a->txt = $this->traduceRegla($a);
                        if($i==0) {
                            $this->cp->getSession()->set("lastAl", $a->TIMESTAMP);
                        }
                        $i++;
                    }
                    echo json_encode($al);
                    break;
                default:
                    break;
            }
        }
    }

    function setOp() {
        $this->op = mysql_escape_string($_GET["op"]);
        switch ($this->op) {
            default:
                $this->layout = "vista/monitoreo.phtml";
                if($this->cp->isAdmin() || $this->cp->isSuperAdmin()) {
                    $this->grupos = $this->dgMP->fetchByCuenta($this->cp->getSession()->get("accountID"));
                } else {
                    $this->grupos = $this->dgMP->fetchUserGrupo($this->cp->getSession()->get("userID"));
                }
                $this->sensor = $this->sdMP->fetchByAccount($this->cp->getSession()->get("accountID"));
                break;
        }
    }

    function getDeviceByGrupo($idGr) {
        return $this->deMP->fetchByGrupo($idGr);
    }

    function traduceRegla($regla) {
//        echo "<pre>";
//        print_r($regla);
//        echo "</pre>";
        if($regla->ID_TIPO_REGLA!=4) {
            switch($regla->ID_PARAMETRO) {
                case 1: //velocidad
                    switch($regla->ID_OPERADOR) {
                        case 1:
                            return "Velocidad (".round($regla->speedKPH).") > ".$regla->VALOR_REGLA." (Km/h)";
                            break;
                        case 2:
                            return "Velocidad (".round($regla->speedKPH).") < ".$regla->VALOR_REGLA." (Km/h)";
                            break;
                    }
                    break;
                case 2: //tiempo
                    switch($regla->ID_OPERADOR) {
                        case 1:
                            return "Detenci&oacute;n > ".$regla->VALOR_REGLA." (Min.)";
                            break;
                        case 2:
                            return "Detenci&oacute;n > ".$regla->VALOR_REGLA." (Min.)";
                            break;
                    }
                    break;
                case 3: //geozona
                    $pol = $this->poMP->find($regla->ID_POLIGONO, array("NOM_POLIGONO"));
                    switch($regla->ID_OPERADOR) {
                        case 4:
                            return "Entr&oacute; a <b>".$pol->NOM_POLIGONO."</b>";
                            break;
                        case 5:
                            return "Sali&oacute; de <b>".$pol->NOM_POLIGONO."</b>";
                            break;
                    }
                    break;
                case 4: //geofrontera
                    $pol = $this->poMP->find($regla->ID_POLIGONO, array("NOM_POLIGONO"));
                    switch($regla->ID_OPERADOR) {
                        case 6:
                            return "Cruz&oacute; <b>".$pol->NOM_POLIGONO."</b>";
                            break;
                    }
                    break;
                case 5: //punto de interes
                    $pi = $this->piMP->find($regla->ID_POLIGONO, array("id","name"));
                    switch($regla->ID_OPERADOR) {
                        case 4:
                            return "Entr&oacute; a <b><a onClick=\"centrarPInteres(".$pi->id."); return false;\">".$pi->name."</a></b>";
                            break;
                        case 5:
                            return "Sali&oacute; de <b><a onClick=\"centrarPInteres(".$pi->id."); return false;\">".$pi->name."</a></b>";
                            break;
                    }
                    break;
            }
        } else { //sensores
            $seAux = $this->sdMP->findSensor($regla->ID_PARAMETRO);
//            echo "<pre>";
//            print_r($seAux);
//            echo "</pre>";
            switch($seAux->TIPO_PROCESO_SENSOR ) {
                case 1://binario
                    $opAux = $this->sdMP->fetchOpSensor($seAux->ID_SENSOR, $regla->VALOR_REGLA);
                    return $seAux->NOM_SENSOR." <b>".$opAux->SENSOR_OPCION."</b>";
                    break;
                case 2: //continuo
                    switch($regla->ID_OPERADOR) {
                        case 1:
                            return $seAux->NOM_SENSOR." > ".$regla->VALOR_REGLA." (".$seAux->UNIDAD_SENSOR.")";
                            break;
                        case 2:
                            return $seAux->NOM_SENSOR." < ".$regla->VALOR_REGLA." (".$seAux->UNIDAD_SENSOR.")";
                            break;
                    }
                    break;
                case 3: //continuo
                    $col = $seAux->COLUMNA_SENSOR;
//                    echo $col."<br>";
                    switch($regla->ID_OPERADOR) {
                        case 1:
                            return $seAux->NOM_SENSOR.": ".$regla->$col." > ".$regla->VALOR_REGLA." (".$seAux->UNIDAD_SENSOR.")";
                            break;
                        case 2:
                            return $seAux->NOM_SENSOR.": ".$regla->$col." < ".$regla->VALOR_REGLA." (".$seAux->UNIDAD_SENSOR.")";
                            break;
                    }
                    break;
            }
        }
    }
}
?>