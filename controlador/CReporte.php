<?php
require_once 'modelo/DeviceGroupMP.php';
require_once 'modelo/DeviceMP.php';
include_once 'util/session.php';
include_once 'util/paginacion.php';

class CReporte {
    protected $_secName = "home";
    protected $_CSec;
    protected $ss;
    protected $dgMP;
    protected $deMP;
    public $showLayout = true;
    public $thisLayout = true;

    function __construct($cp) {
        $this->ss = new session();
        $this->cp = $cp;
        $this->dgMP = new DeviceGroupMP();
        $this->deMP = new DeviceMP();
        $this->setGet();
        $this->setSec();
    }

    public function getLayout() {
        return $this->_CSec->getLayout();
    }

    function getCSec() {
        return $this->_CSec;
    }

    function getSession() {
        return $this->ss;
    }

    function checkAccess() {
        return ($this->ss->get("roleID") == 1);
    }

    function error($e) {
        switch($e) {
            case '404':
                $this->showLayout = false;
                echo "error 404<br>";
                break;
        }
    }

    function setGet() {
        if(isset($_GET["get"])) {
            $this->cp->showLayout = false;
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
            }
        }
    }

    function setSec() {
        $this->sec = $_GET["ssec"];
//        $this->showLayout = true;
//        $this->thisLayout = true;
        if(isset($_GET["ajax"])) {
            $this->cp->thisLayout = false;
        }
        switch($this->sec) {
            case 'auditoria':
                include_once 'controlador/CAuditoria.php';
                $this->tilSec = "Auditoría";
                $this->_CSec = new CAuditoria($this);
                break;
            case 'alarma':
                include_once 'controlador/CRAlarma.php';
                $this->tilSec = "Alarmas";
                $this->_CSec = new CRAlarma($this);
                break;
            case 'detencion':
                include_once 'controlador/CAlarma.php';
                $this->tilSec = "Detenciones";
                $this->_CSec = new CAlarma($this);
                break;
            case 'recorrido':
                include_once 'controlador/CRDistancia.php';
                $this->tilSec = "Kilometros recorridos";
                $this->_CSec = new CRDistancia($this);
                break;
            case 'sensor':
                include_once 'controlador/CPInteres.php';
                $this->tilSec = "Sensores";
                $this->_CSec = new CPInteres($this);
                break;
            case 'velocidad':
                include_once 'controlador/CRVelocidad.php';
                $this->tilSec = "Velocidad";
                $this->_CSec = new CRVelocidad($this);
                break;
            case 'rawdata':
                include_once 'controlador/CRRawdata.php';
                $this->tilSec = "RawData";
                $this->_CSec = new CRRawdata($this);
                break;
            case 'consumo':
                include_once 'controlador/CRConsumo.php';
                $this->tilSec = "Consumo";
                $this->_CSec = new CRConsumo($this);
                break;
            default:
                $this->sec = "auditoria";
                include_once 'controlador/CAuditoria.php';
                $this->tilSec = "Auditoría";
                $this->_CSec = new CAuditoria($this);
                break;
        }
    }

}
?>