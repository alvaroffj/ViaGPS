<?php
require_once 'modelo/DeviceGroupMP.php';
require_once 'modelo/DeviceMP.php';
require_once 'modelo/EventDataMP.php';

class CRRawdata {
    protected $cp;
    protected $dgMP;
    protected $deMP;

    function  __construct($cp) {
        $this->cp = $cp;
        $this->dgMP = new DeviceGroupMP();
        $this->deMP = new DeviceMP();
        $this->edMP = new EventDataMP();
        $this->setGet();
        $this->setOp();
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
                case 'mapaAni':
                    include 'vista/ralarma_mapa.phtml';
                    break;
                case 'mapa':
                    include 'vista/ralarma_mapa.phtml';
                    $info = array("par"=>0, "lat"=>$_GET["lat"], "lon"=>$_GET["lon"]);
                    echo "<div id='info' style='display:none;'>".json_encode($info)."</div>";
                    break;
                case 'reporte':
                    $ini = strtotime($_POST["fecha_ini"]." ".$_POST["hrs_ini"].":".$_POST["min_ini"].":00");
                    $fin = strtotime($_POST["fecha_fin"]." ".$_POST["hrs_fin"].":".$_POST["min_fin"].":00");
                    $rep = null;
                    if($_POST["id_device"] == "0") {
                        $gr = $this->dgMP->find($_POST["id_grupo"], $attr);
                        if($gr->accountID == $this->cp->getSession()->get("accountID")) {
                            $de = $this->deMP->fetchByGrupo($_POST["id_grupo"]);
                            $dev = array();
                            $license = array();
                            foreach($de as $d) {
                                $dev[] = $d->deviceID;
                                $license[$d->deviceID] = $d->licensePlate;
                            }
                            $rep = $this->edMP->auditoriaByDevice($ini, $fin, $dev);
                        }
                    } else {
                        $dev = $this->deMP->find($_POST["id_device"], array("accountID", "licensePlate"));
                        $license[$_POST["id_device"]] = $dev->licensePlate;
                        if($dev->accountID == $this->cp->getSession()->get("accountID")) {
                            $rep = $this->edMP->auditoriaByDevice($ini, $fin, array($_POST["id_device"]));
                        }
                    }
                    if($rep!=null) {
                        $ani = ($_POST["id_device"] != "0")?"<div id='local-nav'><a href='?sec=reporte&ssec=auditoria&get=mapaAni' id='aniRep'><img src='img/play.png' border=0/>Animar Reporte</a></div>":"";
                        $txt = "<h2>Reporte</h2> $ani
                        <table border='0' cellspacing='0' cellpadding='0' width='100%' class='tablarojo' id='reporte'>
                            <thead>
                                <tr>
                                    <th align='center' width='100'>Veh&iacute;culo</th>
                                    <th align='center' width='150'>Fecha</th>
                                    <th align='center'>RawData</th>
                                </tr>
                            </thead>
                            <tbody>";
                        $km = 0;
                        foreach($rep as $r) {
//                            $km += $r->odometerKM;
//                            $info = array(
//                                "licensePlate"=>$license[$r->deviceID],
//                                "fecha"=>$r->fecha,
//                                "velocidad"=>$r->speedKPH,
//                                "distancia"=>$km,
//                                "lat"=>$r->latitude,
//                                "lon"=>$r->longitude
//                            );
                            
                            $txt .= "<tr>";
                            $txt .= "<td align='center'>".$license[$r->deviceID]."</td>";
                            $txt .= "<td align='center'>".$r->fecha."</td>";
                            $txt .= "<td align='left'>".$r->rawData."</td>";
                            $txt .= "</tr>";
                        }

                        $txt .= "</tbody></table>";

                        echo $txt;
                    }
                    break;
            }
        }
    }

    function setOp() {
        if (isset($_GET["op"])) {
        } else {
            $this->layout = "vista/reporte_base.phtml";
            $this->grupos = $this->dgMP->fetchByCuenta($this->cp->getSession()->get("accountID"));
            $this->min = range(0,59,15);
            $this->hrs = range(0,23,1);
        }
    }

    function getGrupoName($id) {
        $n = count($this->grupos);
        $i = 0;
        while($i<$n && $this->grupos[$i]->groupID != $id) { $i++; }
        return $this->grupos[$i]->displayName;
    }
}
?>