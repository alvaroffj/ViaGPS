<?php
require_once 'modelo/DeviceGroupMP.php';
require_once 'modelo/DeviceMP.php';
require_once 'modelo/EventDataMP.php';

class CRConsumo {
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
                case 'descargar':
                    $ini = strtotime($_GET["fecha_ini"]." ".$_GET["hrs_ini"].":".$_GET["min_ini"].":00");
                    $fin = strtotime($_GET["fecha_fin"]." ".$_GET["hrs_fin"].":".$_GET["min_fin"].":00");
                    $fini = $_GET["fecha_ini"]." ".$_GET["hrs_ini"].":".$_GET["min_ini"].":00";
                    $ffin = $_GET["fecha_fin"]." ".$_GET["hrs_fin"].":".$_GET["min_fin"].":00";
                    $rep = null;
                    $dev = array();
                    $license = array();
                    $kmPLitro = array();
                    if($_GET["id_device"] == "0") {
                        $gr = $this->dgMP->find($_GET["id_grupo"], $attr);
                        if($gr->accountID == $this->cp->getSession()->get("accountID")) {
                            $de = $this->deMP->fetchByGrupo($_GET["id_grupo"]);
                            foreach($de as $d) {
                                $dev[] = $d->deviceID;
                                $license[$d->deviceID] = $d->licensePlate;
                                $nombre[$d->deviceID] = $d->displayName;
                                $kmPLitro[$d->deviceID] = $d->kmPorLitro;
                            }
                            $rep = $this->edMP->distanciaByDevice($ini, $fin, $dev);
                        }
                    } else {
                        $devAux = $this->deMP->find($_GET["id_device"], array("deviceID", "accountID", "licensePlate", "displayName", "kmPorLitro"));
                        $license[$_GET["id_device"]] = $devAux->licensePlate;
                        $nombre[$_GET["id_device"]] = $devAux->displayName;
                        $kmPLitro[$_GET["id_device"]] = $devAux->kmPorLitro;
                        if($devAux->accountID == $this->cp->getSession()->get("accountID")) {
                            $rep = $this->edMP->distanciaByDevice($ini, $fin, array($_GET["id_device"]));
                        }
                        $dev[0] = $devAux->deviceID;
                    }
                    if($rep != null) {
                        require_once 'Classes/PHPExcel.php';

                        $objPHPExcel = new PHPExcel();
                        $objPHPExcel->getProperties()->setCreator("GPSLine")
                                ->setTitle("Reporte de Consumo " . $ini . " - " . $fin)
                                ->setSubject("Reporte de Consumo " . $ini . " - " . $fin)
                                ->setDescription("Reporte de Consumo " . $ini . " - " . $fin);

                        $objPHPExcel->setActiveSheetIndex(0);
                        $objPHPExcel->getActiveSheet()->setTitle('Consumo');
                        $objReader = PHPExcel_IOFactory::createReader('Excel5');
                        $objPHPExcel = $objReader->load("plantilla.xls");

                        $objPHPExcel->getActiveSheet()
                                ->setCellValueByColumnAndRow(5, 2, 'Reporte de Consumo')
                                ->setCellValueByColumnAndRow(5, 3, utf8_encode('Período de tiempo: ') . $fini . " / ".$ffin);
                        $columnas = array("Fecha", "Vehículo", "Patente", "Consumo estimado (Lt)");
                        $nCol = count($columnas);
                        $rowIni = 7;
                        for($i=0; $i<$nCol; $i++) {
                            $objPHPExcel->getActiveSheet()
                                ->setCellValueByColumnAndRow($i+1, $rowIni-1, utf8_encode($columnas[$i]));
                        }
                        $km = 0;
                        $i = 0;
                        $ltTotal = 0;
                        $auxDevId = 0;
                        foreach ($rep as $r) {
                            $lt = round($r->distancia/$kmPLitro[$r->deviceID]);
                            if($auxDevId == 0) $auxDevId = $r->deviceID;
                            if($auxDevId != $r->deviceID) {
                                $objPHPExcel->getActiveSheet()
                                    ->setCellValueByColumnAndRow(1, $rowIni+$i, "Total")
                                    ->setCellValueByColumnAndRow(2, $rowIni+$i, $nombre[$auxDevId])
                                    ->setCellValueByColumnAndRow(3, $rowIni+$i, $license[$auxDevId])
                                    ->setCellValueByColumnAndRow(4, $rowIni+$i, $ltTotal);
                                $ltTotal = $lt;
                                $auxDevId = $r->deviceID;
                                $i += 2;
                            } else {
                                $ltTotal += $lt;
                            }
                            $objPHPExcel->getActiveSheet()
                                ->setCellValueByColumnAndRow(1, $rowIni+$i, $r->fecha)
                                ->setCellValueByColumnAndRow(2, $rowIni+$i, $nombre[$r->deviceID])
                                ->setCellValueByColumnAndRow(3, $rowIni+$i, $license[$r->deviceID])
                                ->setCellValueByColumnAndRow(4, $rowIni+$i, $lt);
                            $i++;
                        }
                        if($auxDevId != 0) {
                            $objPHPExcel->getActiveSheet()
                                ->setCellValueByColumnAndRow(1, $rowIni+$i, "Total")
                                ->setCellValueByColumnAndRow(2, $rowIni+$i, $nombre[$auxDevId])
                                ->setCellValueByColumnAndRow(3, $rowIni+$i, $license[$auxDevId])
                                ->setCellValueByColumnAndRow(4, $rowIni+$i, $ltTotal);
                        }
                        header('Content-Type: application/vnd.ms-excel');
                        header('Content-Disposition: attachment;filename="reporte_consumo_'.$ini.'_'.$fin.'.xls"');
                        header('Cache-Control: max-age=0');
                        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                        $objWriter->save('php://output');
                    }
                    break;
                case 'reporte':
                    $ini = strtotime($_POST["fecha_ini"]." ".$_POST["hrs_ini"].":".$_POST["min_ini"].":00");
                    $fin = strtotime($_POST["fecha_fin"]." ".$_POST["hrs_fin"].":".$_POST["min_fin"].":00");
                    $rep = null;
                    $dev = array();
                    $license = array();
                    $kmPLitro = array();
                    if($_POST["id_device"] == "0") {
                        $gr = $this->dgMP->find($_POST["id_grupo"], $attr);
                        if($gr->accountID == $this->cp->getSession()->get("accountID")) {
                            $de = $this->deMP->fetchByGrupo($_POST["id_grupo"]);
                            foreach($de as $d) {
                                $dev[] = $d->deviceID;
                                $license[$d->deviceID] = $d->licensePlate;
                                $nombre[$d->deviceID] = $d->displayName;
                                $kmPLitro[$d->deviceID] = $d->kmPorLitro;
                            }
                            $rep = $this->edMP->distanciaByDevice($ini, $fin, $dev);
                        }
                    } else {
                        $devAux = $this->deMP->find($_POST["id_device"], array("deviceID", "accountID", "licensePlate", "displayName", "kmPorLitro"));
                        $license[$_POST["id_device"]] = $devAux->licensePlate;
                        $nombre[$_POST["id_device"]] = $devAux->displayName;
                        $kmPLitro[$_POST["id_device"]] = $devAux->kmPorLitro;
                        if($devAux->accountID == $this->cp->getSession()->get("accountID")) {
                            $rep = $this->edMP->distanciaByDevice($ini, $fin, array($_POST["id_device"]));
                        }
                        $dev[0] = $devAux->deviceID;
                    }
                    if($rep!=null) {
                        $auxDevId = 0;
                        $ltTotal = 0;
                        $txt = "<h2>Reporte</h2>";
                        $txt .= $this->getGrafico($rep, $nombre, $license, $dev, $kmPLitro, $_POST["fecha_ini"]);
                        $txt .= "<table border='0' cellspacing='0' cellpadding='0' width='100%' class='tablarojo' id='reporte'>
                            <thead>
                                <tr>
                                    <th align='center' width='100'>Fecha</th>
                                    <th align='center' width='100'>Veh&iacute;culo</th>
                                    <th align='center' width='100'>Patente</th>
                                    <th align='center' width='100'>Consumo estimado (Lt)</th>
                                </tr>
                            </thead>
                            <tbody>";
                        foreach($rep as $r) {
                            $lt = round($r->distancia/$kmPLitro[$r->deviceID]);
                            if($auxDevId == 0) $auxDevId = $r->deviceID;
                            if($auxDevId != $r->deviceID) {
                                $txt .= "<tr class='total'>";
                                $txt .= "<td align='center'>Total</td>";
                                $txt .= "<td align='center'>".$nombre[$auxDevId]."</td>";
                                $txt .= "<td align='center'>".$license[$auxDevId]."</td>";
                                $txt .= "<td align='center'>".$ltTotal."</td>";
                                $txt .= "</tr>";
                                $ltTotal = $lt;
                                $auxDevId = $r->deviceID;
                            } else {
                                $ltTotal += $lt;
                            }
                            $txt .= "<tr>";
                            $txt .= "<td align='center'>".$r->fecha."</td>";
                            $txt .= "<td align='center'>".$nombre[$r->deviceID]."</td>";
                            $txt .= "<td align='center'>".$license[$r->deviceID]."</td>";
                            $txt .= "<td align='center'>".$lt."</td>";
                            $txt .= "</tr>";
                        }
                        if($auxDevId!=0) {
                            $txt .= "<tr class='total'>";
                            $txt .= "<td align='center'>Total</td>";
                            $txt .= "<td align='center'>".$nombre[$auxDevId]."</td>";
                            $txt .= "<td align='center'>".$license[$auxDevId]."</td>";
                            $txt .= "<td align='center'>".$ltTotal."</td>";
                            $txt .= "</tr>";
                        }

                        $txt .= "</tbody></table>";

                        echo $txt;
                    }
                    break;
            }
        }
    }
    
    function getGrafico($log, $nom, $pat, $dev, $km, $ini) {
        $nDev = count($dev);
        for($j=0; $j<$nDev; $j++) {
            for ($i = 0; $i < $log[0]->DIAS + 1; $i++) {
                $data[$dev[$j]][] = 0;
            }
        }
        
        foreach ($log as $l) {
            $data[$l->deviceID][$l->INDICE] = round($l->distancia/$km[$l->deviceID]);
        }
        $ini = explode("-", $ini);
        $r = "[";
        for ($i = 0; $i < $nDev; $i++) {
            if($i) {
                $r .= ", { 
                    name: '".$nom[$dev[$i]]."', 
                    pointInterval: 24 * 3600 * 1000,
                    pointStart: Date.UTC(".$ini[0].", ".($ini[1]-1).", ".$ini[2]."),
                    data: [";
            } else {
                $r .= "{ 
                    name: '".$nom[$dev[$i]]."', 
                    pointInterval: 24 * 3600 * 1000,
                    pointStart: Date.UTC(".$ini[0].", ".($ini[1]-1).", ".$ini[2]."),
                    data: [";
            }
            for($j=0; $j<$log[0]->DIAS + 1; $j++) {
                if($j) {
                    $r .= ", ".$data[$dev[$i]][$j];
                } else {
                    $r .= $data[$dev[$i]][$j];
                }
            }
            $r .= "]}";
        }
        $r .= "]";
        
        $txt = "<div class='grafico' id='grafico_res'></div>
        <script>
            chartRes = new Highcharts.Chart({
                    chart: {
                        renderTo: 'grafico_res',
                        zoomType: 'x',
                        spacingRight: 20
                    },
                    title: {
                        text: 'Comsumo de combustible'
                    },
                    subtitle: {
                        text: 'Click y arrastrar para acercar'
                    },
                    xAxis: {
                        type: 'datetime',
                        maxZoom: 10 * 24 * 3600000, // fourteen days
                        title: {
                            text: 'Fecha'
                        }
                    },
                    yAxis: {
                        title: {
                            text: 'Litros consumidos'
                        },
                        min: 0.6,
                        startOnTick: false,
                        showFirstLabel: false
                    },
                    tooltip: {
                        shared: true
                    },
                    plotOptions: {
                        line: {
                            dataLabels: {
                               enabled: true
                            },
                            enableMouseTracking: false
                         },
                        area: {
                            fillColor: {
                                linearGradient: [0, 0, 0, 300],
                                stops: [
                                [0, 'rgba(2,0,0,2)'],
                                [1, 'rgba(2,0,0,0)']
                                ]
                            },
                            lineWidth: 1,
                            marker: {
                                enabled: false,
                                states: {
                                    hover: {
                                        enabled: true,
                                        radius: 5
                                    }
                                }
                            },
                            shadow: false,
                            states: {
                                hover: {
                                    lineWidth: 1
                                }
                            }
                        }
                    },

                    series: $r
                });
        </script>";
        return $txt;
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