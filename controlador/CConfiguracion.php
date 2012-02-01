<?php
include_once 'util/session.php';
include_once 'util/paginacion.php';

class CConfiguracion {
    protected $_secName = "home";
    protected $_CSec;
    protected $ss;
    protected $usuarioMP;
//    public $layout = "vista/layout.phtml";
    public $showLayout = true;
    public $thisLayout = true;
    public $loged = false;
    public $usuario;

    function __construct($cp) {
        $this->ss = new session();
        $this->cp = $cp;
        if ($this->cp->isAdmin() || $this->cp->isSuperAdmin()) {
            $this->setSec();
        } else {
            $this->ss->salto("index.php");
        }
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
        return ($this->ss->get("roleID") == 1 || $this->ss->get("roleID") == 0);
    }

    function error($e) {
        switch($e) {
            case '404':
                $this->showLayout = false;
                echo "error 404<br>";
                break;
        }
    }

    function setSec() {
        $this->sec = $_GET["ssec"];
        $this->showLayout = true;
        $this->thisLayout = true;
        switch($this->sec) {
            case 'usuario':
                include_once 'controlador/CUsuario.php';
                $this->_CSec = new CUsuario($this);
                break;
            case 'vehiculo':
                include_once 'controlador/CVehiculo.php';
                $this->_CSec = new CVehiculo($this);
                break;
            case 'conductor':
                include_once 'controlador/CConductor.php';
                $this->_CSec = new CConductor($this);
                break;
            case 'alarma':
                include_once 'controlador/CAlarma.php';
                $this->_CSec = new CAlarma($this);
                break;
            case 'geozona':
                include_once 'controlador/CGeo.php';
                $this->_CSec = new CGeo($this);
                break;
            case 'puntointeres':
                include_once 'controlador/CPInteres.php';
                $this->_CSec = new CPInteres($this);
                break;
            default:
                $this->sec = "vehiculo";
                include_once 'controlador/CVehiculo.php';
                $this->_CSec = new CVehiculo($this);
                break;
        }
    }

}
?>