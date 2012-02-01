<?php
require_once 'modelo/UsuarioMP.php';
require_once 'modelo/RoleMP.php';
require_once 'modelo/DeviceGroupMP.php';

class CUsuario {
    protected $cp;
    protected $usMP;
    protected $roMP;

    function  __construct($cp) {
        $this->cp = $cp;
        $this->usMP = new UsuarioMP();
        $this->roMP = new RoleMP();
        $this->dgMP = new DeviceGroupMP();
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
            $this->cp->cp->showLayout = false;
            $attr = array("accountID");
            switch($_GET["do"]) {
                case "add":
                    if($this->cp->getSession()->get("accountID") == $_POST["accountID"]) {
                        $this->usMP->insert($_POST);
                        $this->cp->getSession()->salto("?sec=configuracion&ssec=usuario");
                    } else {
                        $this->cp->getSession()->salto("?sec=configuracion&ssec=usuario&op=add&e=2");
                    }
                    break;
                case "mod":
                    $id = $_POST["id"];
                    $us = $this->usMP->find($id, $attr);
                    if($us->accountID == $this->cp->getSession()->get("accountID")) {
                        if($this->cp->getSession()->get("accountID") == $_POST["accountID"]) {
                            if($this->usMP->update($_POST)) {
                                $this->cp->getSession()->salto("?sec=configuracion&ssec=usuario&op=mod&id=$id&e=0");
                            } else {
                                $this->cp->getSession()->salto("?sec=configuracion&ssec=usuario&op=mod&id=$id&e=1");
                            }
                        } else {
                            $this->cp->getSession()->salto("?sec=configuracion&ssec=usuario&e=2");
                        }
                    } else $this->cp->getSession()->salto("?sec=configuracion&ssec=usuario&e=1");
                    break;
                case "del":
                    if(isset($_GET["id"])) {
                        $id = $_GET["id"];
                        $us = $this->usMP->find($id, $attr);
                        if($us->accountID == $this->cp->getSession()->get("accountID")) {
                            if($this->usMP->desactiva($id)) {
                                $this->cp->getSession()->salto("?sec=configuracion&ssec=usuario");
                            } else {
                            }
                        } else {
                        }
                    } else {
                    }
                    break;
                case "add_grupo":
                    if(isset($_POST["id_grupo"]) && isset($_POST["id_usuario"])) {
                        $idGr = $_POST["id_grupo"];
                        $idUs = $_POST["id_usuario"];
                        $us = $this->usMP->find($idUs, $attr);
                        $dg = $this->dgMP->find($idGr, array("accountID", "displayName"));
                        if($us->accountID == $this->cp->getSession()->get("accountID")) {
                            if($dg->accountID == $this->cp->getSession()->get("accountID")) {
                                $existe = $this->dgMP->fetchUserGrupo($idUs, $idGr);
                                if(count($existe)>0) {
                                    $r = array("groupID"=>$idGr, "displayName"=>$dg->displayName, "idUsGr"=>$idUsGr, "error"=>1);
                                } else {
                                    $idUsGr = $this->dgMP->addUserGrupo($idUs, $idGr);
                                    if($idUsGr) {
                                        $r = array("groupID"=>$idGr, "displayName"=>$dg->displayName, "idUsGr"=>$idUsGr, "error"=>0);
                                    } else {
                                        $r = array("groupID"=>$idGr, "displayName"=>$dg->displayName, "idUsGr"=>$idUsGr, "error"=>2);
                                    }
                                }
                            } else {
                                $r = array("groupID"=>$idGr, "displayName"=>$dg->displayName, "idUsGr"=>$idUsGr, "error"=>3);
                            }
                        } else {
                            $r = array("groupID"=>$idGr, "displayName"=>$dg->displayName, "idUsGr"=>$idUsGr, "error"=>4);
                        }
                        echo json_encode($r);
                    }
                    break;
                case "del_grupo":
                    if(isset($_POST["idUsGr"])) {
                        $idUsGr = $_POST["idUsGr"];
                        $usGr = $this->dgMP->findUserGrupo($idUsGr);
                        if($usGr) {
                            $us = $this->usMP->find($usGr->userID, $attr);
                            $dg = $this->dgMP->find($usGr->groupID, array("accountID", "displayName"));
                            if($us && $dg) {
                                if($us->accountID == $this->cp->getSession()->get("accountID")) {
                                    if($dg->accountID == $this->cp->getSession()->get("accountID")) {
                                        if($this->dgMP->delUserGrupo($idUsGr)) $r = array("error"=>0);
                                        else $r = array("error"=>1);
                                    } else $r = array("error"=>2);
                                } else $r = array("error"=>3);
                            } else $r = array("error"=>4);
                        } else $r = array("error"=>5);
                    } else $r = array("error"=>6);
                    
                    echo json_encode($r);
                    break;
            }
        }
    }

    function setOp() {
        if (isset($_GET["op"])) {
            $this->op = mysql_escape_string($_GET["op"]);
            switch ($this->op) {
                case 'add':
                    $this->layout = "vista/usuario_form.phtml";
                    $this->tilSec = "Nuevo Usuario";
                    $this->roles = $this->roMP->fetchAll(array("roleID", "DisplayName"));
                    break;
                case "mod":
                    $this->layout = "vista/usuario_form.phtml";
                    $this->tilSec = "Modificar Usuario";
                    $this->obj = $this->usMP->find($_GET["id"]);
                    if(!$this->obj) $this->cp->getSession()->salto("?sec=configuracion&ssec=usuario");
                    if($this->obj->accountID != $this->cp->getSession()->get("accountID")) $this->cp->getSession()->salto("?sec=configuracion&ssec=usuario");
                    $this->roles = $this->roMP->fetchAll(array("roleID", "DisplayName"));
                    $this->grupos = $this->dgMP->fetchByCuenta($this->cp->getSession()->get("accountID"));
                    if($this->obj->roleID == 2) {
                        $this->userGr = $this->dgMP->fetchUserGrupo($_GET["id"]);
                    }
                    break;
            }
        } else {
            $this->layout = "vista/usuario.phtml";
            $this->usuarios = $this->usMP->fetchByCuenta($this->cp->getSession()->get("accountID"));
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