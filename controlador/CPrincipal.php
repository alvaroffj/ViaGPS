<?php
include_once 'util/session.php';
include_once 'util/paginacion.php';
include_once 'modelo/CuentaMP.php';

class CPrincipal {
    protected $_secName = "Monitoreo";
    protected $_CSec;
    protected $ss;
    protected $usuarioMP;
    protected $cuentaMP;
    public $layout = "vista/layout.phtml";
    public $showLayout = true;
    public $thisLayout = true;
    public $loged = false;
    public $cuenta;
    public $usuario;

    function __construct() {
        date_default_timezone_set("America/Santiago");
        $this->ss = new session();
        $this->cuenta = explode(".", $_SERVER["SERVER_NAME"]);
        $this->cuenta = $this->cuenta[0];
        $this->cuentaMP = new CuentaMP();
        $this->access = array();
        $this->access[0] = array();
        $this->access[0]["log"] = array("visible"=>1);
        $this->access[0]["monitoreo"] = array("visible"=>1);
        $this->access[0]["reporte"] = array("visible"=>1);
        $this->access[0]["configuracion"] = array("visible"=>1,"vehiculo"=>1,"conductor"=>1,"usuario"=>1,"alarma"=>1,"puntointeres"=>1,"geozona"=>1);
        
        $this->access[1] = array();
        $this->access[1]["log"] = array("visible"=>1);
        $this->access[1]["monitoreo"] = array("visible"=>1);
        $this->access[1]["reporte"] = array("visible"=>1);
        $this->access[1]["configuracion"] = array("visible"=>1,"vehiculo"=>1,"conductor"=>1,"usuario"=>1,"alarma"=>1,"puntointeres"=>1,"geozona"=>1);
        
        $this->access[2] = array();
        $this->access[2]["log"] = array("visible"=>1);
        $this->access[2]["monitoreo"] = array("visible"=>1);
        $this->access[2]["reporte"] = array("visible"=>1);
        $this->access[2]["configuracion"] = array("visible"=>0,"vehiculo"=>0,"conductor"=>0,"usuario"=>0,"alarma"=>0,"puntointeres"=>0,"geozona"=>0);
        
        $this->access[3] = array();
        $this->access[3]["log"] = array("visible"=>1);
        $this->access[3]["monitoreo"] = array("visible"=>1);
        $this->access[3]["reporte"] = array("visible"=>1);
        $this->access[3]["configuracion"] = array("visible"=>1,"vehiculo"=>1,"conductor"=>0,"usuario"=>0,"alarma"=>1,"puntointeres"=>1,"geozona"=>1);
        
        if($this->cuentaMP->isActive($this->cuenta) || $this->cuenta == "dev") {
            $this->cuenta = ($this->cuenta == "dev")?"maxximiza":$this->cuenta;
            if ($this->checkLogin()) {
                $this->setSec();
            } else {
                $this->cuentaData = $this->cuentaMP->findByNom($this->cuenta);
                include_once 'CLog.php';
                $this->_CSec = new CLog($this);
            }
        } else {
            $this->layout = "vista/desactivado.phtml";
        }
        
        
        
//        echo "<pre>";
//        print_r($access);
//        echo "</pre>";
    }

    public function getLayout() {
        if($this->thisLayout) return $this->layout;
        else return $this->_CSec->getLayout();
    }

    function getCSec() {
        return $this->_CSec;
    }

    function getSession() {
        return $this->ss;
    }

    function checkLogin() {
        return ($this->ss->existe("accountID") && $this->ss->existe("userID"));
//        return true;
    }

    function error($e) {
        switch($e) {
            case '404':
                $this->showLayout = false;
                echo "error 404<br>";
                break;
        }
    }

    function isAdmin() {
        return ($this->ss->get("roleID") == 1);
    }

    function isSuperAdmin() {
        return ($this->ss->get("roleID") == 0);
    }
    
    function isAllow($sec, $sub = null) {
        if($sub == null)
            return ($this->access[$this->ss->get("roleID")][$sec]["visible"]==1);
        else 
            return ($this->access[$this->ss->get("roleID")][$sec][$sub]==1);
    }

    function setSec() {
        $this->sec = (isset($_GET["sec"]))?$_GET["sec"]:"monitoreo";
        $this->showLayout = true;
        $this->thisLayout = true;
        
        if($this->isAllow($this->sec)) {
            switch($this->sec) {
                case 'log':
                    include_once 'CLog.php';
                    $this->_CSec = new CLog($this);
                    break;
                case 'monitoreo':
                    require_once 'CMonitoreo.php';
                    $this->_CSec = new CMonitoreo($this);
                    break;
                case 'configuracion':
                    include_once 'controlador/CConfiguracion.php';
                    $this->_CSec = new CConfiguracion($this);
                    break;
                case 'reporte':
                    include_once 'controlador/CReporte.php';
                    $this->_CSec = new CReporte($this);
                    break;
            }
        } else {
            $this->ss->salto("/");
        }
    }
}
?>