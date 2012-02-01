<?php
class session {
    function __construct() {
        session_start();
    }

    function get($n) {
        return $_SESSION["$n"];
    }

    function set($n, $v) {
        $_SESSION["$n"] = $v;
    }

    function kill() {
        session_destroy();
    }

    function existe($n) {
        if(isset($_SESSION[$n]) and $_SESSION[$n]!= "") {
            return true;
        } else {
            return false;
        }
    }

    function checkLoginSS($s="") {
        $go = true;
        $go = ($go && $this->existe($s));
        return $go;
    }

    function salto($n) {
//        echo "salto: ".$n."<br>";
        header("Location:$n");
        die();
    }
}
?>