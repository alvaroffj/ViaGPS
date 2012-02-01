<?php
require_once 'modelo/DeviceGroupMP.php';
require_once 'modelo/DeviceMP.php';
require_once 'modelo/EventDataMP.php';

class CRVelocidad {
    protected $cp;
    protected $dgMP;
    protected $deMP;

    function  __construct($cp) {
        $this->cp = $cp;
        $this->dgMP = new DeviceGroupMP();
        $this->deMP = new DeviceMP();
        $this->edMP = new EventDataMP();
        $this->ope = array(array("id"=>0, "nom"=>"Mayor a"), array("id"=>1, "nom"=>"Menor a"));
        $this->ope[0] = (object)$this->ope[0];
        $this->ope[1] = (object)$this->ope[1];
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
                case 'mapa':
                    include 'vista/ralarma_mapa.phtml';
                    $info = array("par"=>0, "lat"=>$_GET["lat"], "lon"=>$_GET["lon"]);
                    echo "<div id='info' style='display:none;'>".json_encode($info)."</div>";
                    break;
                case 'descargar':
                    $ini = strtotime($_GET["fecha_ini"]." ".$_GET["hrs_ini"].":".$_GET["min_ini"].":00");
                    $fin = strtotime($_GET["fecha_fin"]." ".$_GET["hrs_fin"].":".$_GET["min_fin"].":00");
                    $fini = $_GET["fecha_ini"]." ".$_GET["hrs_ini"].":".$_GET["min_ini"].":00";
                    $ffin = $_GET["fecha_fin"]." ".$_GET["hrs_fin"].":".$_GET["min_fin"].":00";
                    $rep = null;
                    if($_GET["id_device"] == "0") {
                        $gr = $this->dgMP->find($_GET["id_grupo"], $attr);
                        if($gr->accountID == $this->cp->getSession()->get("accountID")) {
                            $de = $this->deMP->fetchByGrupo($_GET["id_grupo"]);
                            $dev = array();
                            $license = array();
                            foreach($de as $d) {
                                $dev[] = $d->deviceID;
                                $license[$d->deviceID] = $d->licensePlate;
                                $nombre[$d->deviceID] = $d->displayName;
                            }
                            $rep = $this->edMP->velocidadByDevice($ini, $fin, $dev, $_GET["operador"], $_GET["vel"]);
                        }
                    } else {
                        $dev = $this->deMP->find($_GET["id_device"], array("accountID", "licensePlate", "displayName"));
                        $license[$_GET["id_device"]] = $dev->licensePlate;
                        $nombre[$_GET["id_device"]] = $dev->displayName;
                        if($dev->accountID == $this->cp->getSession()->get("accountID")) {
                            $rep = $this->edMP->velocidadByDevice($ini, $fin, array($_GET["id_device"]), $_GET["operador"], $_GET["vel"]);
                        }
                    }
                    if($rep != null) {
                        require_once 'Classes/PHPExcel.php';

                        $objPHPExcel = new PHPExcel();
                        $objPHPExcel->getProperties()->setCreator("GPSLine")
                                ->setTitle("Reporte de Velocidad " . $ini . " - " . $fin)
                                ->setSubject("Reporte de Velocidad " . $ini . " - " . $fin)
                                ->setDescription("Reporte de Velocidad " . $ini . " - " . $fin);

                        $objPHPExcel->setActiveSheetIndex(0);
                        $objPHPExcel->getActiveSheet()->setTitle('Velocidad');
                        $objReader = PHPExcel_IOFactory::createReader('Excel5');
                        $objPHPExcel = $objReader->load("plantilla.xls");

                        $objPHPExcel->getActiveSheet()
                                ->setCellValueByColumnAndRow(5, 2, 'Reporte de Velocidad')
                                ->setCellValueByColumnAndRow(5, 3, utf8_encode('Período de tiempo: ') . $fini . " / ".$ffin);
                        $columnas = array("Fecha", "Vehículo", "Patente", "Latitud", "Longitud", "Velocidad");
                        $nCol = count($columnas);
                        $rowIni = 7;
                        for($i=0; $i<$nCol; $i++) {
                            $objPHPExcel->getActiveSheet()
                                ->setCellValueByColumnAndRow($i+1, $rowIni-1, utf8_encode($columnas[$i]));
                        }
                        $i = 0;
                        foreach ($rep as $r) {
                            $objPHPExcel->getActiveSheet()
                                ->setCellValueByColumnAndRow(1, $rowIni+$i, $r->fecha)
                                ->setCellValueByColumnAndRow(2, $rowIni+$i, $nombre[$r->deviceID])
                                ->setCellValueByColumnAndRow(3, $rowIni+$i, $license[$r->deviceID])
                                ->setCellValueByColumnAndRow(4, $rowIni+$i, $r->latitude)
                                ->setCellValueByColumnAndRow(5, $rowIni+$i, $r->longitude)
                                ->setCellValueByColumnAndRow(6, $rowIni+$i, round($r->speedKPH));
                            $i++;
                        }
                        header('Content-Type: application/vnd.ms-excel');
                        header('Content-Disposition: attachment;filename="reporte_velocidad_'.$ini.'_'.$fin.'.xls"');
                        header('Cache-Control: max-age=0');
                        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                        $objWriter->save('php://output');
//                        echo "lala<br>";
                    }
                    break;
                case 'reporte':
                    $ini = strtotime($_POST["fecha_ini"]." ".$_POST["hrs_ini"].":".$_POST["min_ini"].":00");
                    $fin = strtotime($_POST["fecha_fin"]." ".$_POST["hrs_fin"].":".$_POST["min_fin"].":00");
//                    echo "vel: ".$_POST["operador"];
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
                                $nombre[$d->deviceID] = $d->displayName;
                                $vehicle[$d->deviceID] = $d->vehicleID;
                            }
                            $rep = $this->edMP->velocidadByDevice($ini, $fin, $dev, $_POST["operador"], $_POST["vel"]);
                        }
                    } else {
                        $dev = $this->deMP->find($_POST["id_device"], array("accountID", "licensePlate", "vehicleID", "displayName"));
                        $license[$_POST["id_device"]] = $dev->licensePlate;
                        $nombre[$_POST["id_device"]] = $dev->displayName;
                        $vehicle[$_POST["id_device"]] = $dev->vehicleID;
                        if($dev->accountID == $this->cp->getSession()->get("accountID")) {
                            $rep = $this->edMP->velocidadByDevice($ini, $fin, array($_POST["id_device"]), $_POST["operador"], $_POST["vel"]);
                        }
                    }
                    if($rep != null) {
                        foreach ($rep as $r) {
                            $out[] = array(
                                "licensePlate"=>$license[$r->deviceID],
                                "vehicleID"=>$vehicle[$r->deviceID],
                                "displayName"=>$nombre[$r->deviceID],
                                "fecha"=>$r->fecha,
                                "latitude"=>$r->latitude,
                                "longitude"=>$r->longitude,
                                "encendido"=>$r->encendido,
                                "velocidad"=>round($r->speedKPH),
                                "heading"=>$r->heading
                            );
                        }
                        echo json_encode($out);
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